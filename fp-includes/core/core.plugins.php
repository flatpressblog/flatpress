<?php
// define('PLUG_BLOCK', 'block');
class plugin_indexer extends fs_filelister {

	var $_varname = 'fp_plugins';

	var $_enabledlist = null;
	var $enabledlist = null;

	var $_directory = PLUGINS_DIR;

	/**
	 * Initialize plugin indexer paths and enabled list location.
	 */
	function __construct() {
		$this->_enabledlist = CONFIG_DIR . 'plugins.conf.php';
		parent::__construct();
	}

	/**
	 * Detect valid plugin folder (plugin.<id>.php) and add it to the list.
	 */
	function _checkFile($directory, $file) {
		static $local = array();
		$k = $directory . '/' . $file;
		if (!isset($local [$k])) {
			$hit = false;
			$dirM = @file_exists($directory) ? @filemtime($directory) : 0;
			$plugDir = $directory . '/' . $file;
			$plugM = @file_exists($plugDir) ? @filemtime($plugDir) : 0;
			$plugFile = $plugDir . '/plugin.' . $file . '.php';
			$plugFileM = @file_exists($plugFile) ? @filemtime($plugFile) : 0;
			$key = 'fp:plugins:checkfile:v2:' . md5($directory . '|' . (string)$dirM . '|' . $file . '|' . (string)$plugM . '|' . (string)$plugFileM);
			$val = apcu_get($key, $hit);
			if ($hit) {
				$local [$k] = (bool) $val;
			} else {
				$f = $directory . '/' . $file;
				$local [$k] = (is_dir($f) && @file_exists($f . '/plugin.' . $file . '.php'));
				apcu_set($key, $local [$k], 0);
			}
		}
		if ($local [$k]) {
			array_push($this->_list, $file);
		}
		return 0;
	}

	/**
	 * @param $checkonly bool if false will load all the plugins,
	 * if true will check if the plugin exist
	 */
	function getEnableds($checkonly) {
		$lang = &$GLOBALS ['lang'];
		$errors = array();

		$conf = $this->_enabledlist;
		$var = $this->_varname;
		$confMtime = @file_exists($conf) ? @filemtime($conf) : 0;
		$hit = false;
		$key = 'fp:plugins:enableds:list:v1:' . (string) $confMtime;
		$list = apcu_get($key, $hit);
		if ($hit && is_array($list)) {
			$this->enabledlist = $list;
			$$var = $list;
		} else {
			if (!file_exists($conf)) {
				return false;
			}
			include ($conf);
			$this->enabledlist = $$var;
			apcu_set($key, $$var, 0);
		}

		// Expose enabled plugin IDs globally for plugins that need to check activation state
		$GLOBALS [$var] = $$var;

		foreach ($$var as $plugin) {
			$e = plugin_load($plugin, $checkonly);
			if ($e) {
				$errors [] = $e;
			}
		}

		return $errors;
	}

}

/**
 * Load all enabled plugins once per request. Returns array of load errors.
 */
function plugin_loadall($check = false) {

	// avoid double work within one request
	static $memo = array();
	$k = $check ? '1' : '0';
	if (isset($memo [$k])) {
		return $memo [$k];
	}

	// this is done during init process
	// all the plugin are loaded
	$pluginlister = new plugin_indexer();
	$enab = $pluginlister->getEnableds($check);

	include_once (INCLUDES_DIR . 'core.wp-pluggable-funcs.php');

	$memo [$k] = $enab;
	return $enab;
}

/**
 * Return the list of discovered plugin IDs (cached).
 */
function plugin_get($id = null) {
	static $local = null;
	if ($local !== null) {
		return $local;
	}
	$hit = false;
	// versioned by PLUGINS_DIR mtime
	$dirM = @file_exists(PLUGINS_DIR) ? @filemtime(PLUGINS_DIR) : 0;
	$key = 'fp:plugins:list:v1:' . (string) $dirM;
	$list = apcu_get($key, $hit);
	if ($hit && is_array($list)) {
		$local = $list;
		return $local;
	}
	$pluginlister = new plugin_indexer();
	$local = $pluginlister->getList();
	apcu_set($key, $local, 0);
	return $local;
}

/**
 * Report whether a plugin has been loaded in this request.
 */
function plugin_loaded($id) {
	if (file_exists(PLUGINS_DIR . $id . '/plugin.' . $id . ".php")) {
		return true;
	}

	return false;
}

/**
 * Load a single plugin by ID. Returns falsy on success or a localized error string.
 */
function plugin_load($plugin, $checkonly = true, $langload = true) {
	global $lang;

	$errno = 0;
	$errors = false;

	if (file_exists($f = PLUGINS_DIR . $plugin . "/plugin." . $plugin . ".php")) {
		$errno = 1; // 1 means exists
	} elseif (file_exists($f = PLUGINS_DIR . $plugin . "/" . $plugin . ".php")) {
		$errno = 2; // 2 means exists but filename is oldstyle
	}

	if ($errno > 0) {
		ob_start();
		include_once ($f);
		ob_end_clean();
	}

	if ($langload) {
		@lang_load("plugin:" . $plugin);
	}

	if ($checkonly) {
		$func = "plugin_" . $plugin . "_setup";

		if (is_callable($func)) {
			$errno = $func();
		}

		if ($errno <= 0) {

			if (isset($lang ['plugin'] [$plugin] ['errors'] [$errno])) {
				$errors = "[<strong>" . $plugin . "</strong>] " . $lang ['plugin'] [$plugin] ['errors'] [$errno];
			} elseif ($errno < 0) {
				$errors = "[<strong>" . $plugin . "</strong>] " . sprintf($lang ['admin'] ['plugin'] ['errors'] ['generic'], $errno);
			} else {
				$errors = "[<strong>" . $plugin . "</strong>] " . $lang ['admin'] ['plugin'] ['errors'] ['notfound'];
			}
		}
	}

	return $errors;
}

/**
 * Check if plugin file exists under PLUGINS_DIR.
 */
function plugin_exists($id) {
	static $local = array();
	if (isset($local [$id])) {
		return $local [$id];
	}
	$hit = false;
	$plugDir = PLUGINS_DIR . $id;
	$plugFile = $plugDir . '/plugin.' . $id . '.php';
	$dirM = @file_exists(PLUGINS_DIR) ? @filemtime(PLUGINS_DIR) : 0;
	$plugDirM = @file_exists($plugDir) ? @filemtime($plugDir) : 0;
	$plugFileM = @file_exists($plugFile) ? @filemtime($plugFile) : 0;
	$key = 'fp:plugin:exists:v2:' . md5((string)$dirM . '|' . (string)$plugDirM . '|' . (string)$plugFileM . '|' . $id);
	$val = apcu_get($key, $hit);
	if ($hit) {
		return $local [$id] = (bool) $val;
	}
	$res = file_exists($plugFile);
	$local [$id] = $res;
	apcu_set($key, $res, 0);
	return $res;
}

/**
 * Execute a hook across loaded plugins; passes arguments through to handlers.
 */
function plugin_do($id, $type = null) {
	$entry = null;
	if (file_exists($f = PLUGINS_DIR . 'plugin.' . $id . ".php")) {
		include_once ($f);
	} else {
		return false;
	}
}

/**
 * Ensure a plugin is available; load it if needed. Returns boolean success.
 */
function plugin_require($id) {
	return !plugin_loaded($id);
	/*
	 * global $smarty;
	 * $smarty->trigger_error("A plugin required <strong>$id</strong> to be loaded to work properly, but $id ".
	 * "does not appear to be loaded. Maybe the plugins have been loaded in the wrong sequence. ".
	 * "Check your <a href=\"admin.php?p=plugins\">plugin config</a> in the control panel");
	 */
}

/**
 * Get absolute filesystem path to a plugin directory. Trailing slash included.
 */
function plugin_getdir($id) {
	static $local = array();
	if (isset($local[$id])) { return $local[$id]; }
	$hit = false;
	$key = 'fp:plugin:dir:v2:' . md5(PLUGINS_DIR) . ':' . $id;
	$val = apcu_get($key, $hit);
	if ($hit && is_string($val)) {
		return $local [$id] = $val;
	}
	$dir = PLUGINS_DIR . $id . '/';
	$local [$id] = $dir;
	apcu_set($key, $dir, 0);
	return $dir;
}

/**
 * Get public URL to a plugin directory based on BLOG_BASEURL.
 */
function plugin_geturl($id) {
	static $local = array();
	if (isset($local [$id])) {
		return $local [$id];
	}
	$hit = false;
	$key = 'fp:plugin:url:v2:' . md5(BLOG_BASEURL) . ':' . md5(PLUGINS_DIR) . ':' . $id;
	$val = apcu_get($key, $hit);
	if ($hit && is_string($val)) {
		return $local [$id] = $val;
	}
	$url = BLOG_BASEURL . PLUGINS_DIR . $id . '/';
	$local [$id] = $url;
	apcu_set($key, $url, 0);
	return $url;
}

/**
 * Load a plugin's options array from storage; returns defaults if missing.
 * Plugin options system might change.
 */
function plugin_getoptions($plugin, $key = null) {
	global $fp_config;

	if ($key && isset($fp_config ['plugins'] [$plugin] [$key])) {
		return $fp_config ['plugins'] [$plugin] [$key];
	}

	return isset($fp_config ['plugins'] [$plugin]) ? $fp_config ['plugins'] [$plugin] : null;
}

/**
 * Add a default option value if not set for the plugin.
 */
function plugin_addoption($plugin, $key, $val) {
	global $fp_config;
	if (!isset($fp_config ['plugins'])) {
		$fp_config ['plugins'] = array();
	}
	if (!isset($fp_config ['plugins'] [$plugin])) {
		$fp_config ['plugins'] [$plugin] = array();
	}

	return $fp_config ['plugins'] [$plugin] [$key] = $val;
}

/**
 * Persist a plugin's options array to disk. Returns boolean success.
 */
function plugin_saveoptions($null = null) {
	return config_save();
}

/**
 * Smarty helper: outputs the filesystem path for the given plugin id.
 */
function smarty_function_plugin_getdir($params, &$smarty) {
	if (!isset($params ['plugin'])) { // todo complete here
		$smarty->trigger_error('You must set plugin= parameter to a valid id!');
	}
	$id = $params ['plugin'];
	return plugin_getdir($id);
}

function plugin_sanitize_metadata_url($url) {
	$url = trim((string) $url);
	$url = trim($url, "\"' \t\n\r\0\x0B");
	if ($url === '') {
		return '';
	}
	$url = html_entity_decode($url, ENT_QUOTES, 'UTF-8');

	// Block control characters.
	if (preg_match('/[\x00-\x1F\x7F]/', $url)) {
		return '';
	}

	// Allow safe relative URLs.
	if (preg_match('~^(?:/|\./|\.\./|\?|#)~', $url)) {
		return $url;
	}

	$parts = parse_url($url);
	if ($parts === false || !isset($parts ['scheme'])) {
		return '';
	}

	$scheme = strtolower((string) $parts ['scheme']);
	if (!in_array($scheme, array('http', 'https', 'mailto'), true)) {
		return '';
	}

	return $url;
}

function plugin_escape_metadata_html($value) {
	return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function plugin_sanitize_metadata_attr_identifier($value) {
	$value = trim((string) $value);
	if ($value === '') {
		return '';
	}

	return preg_replace('/[^A-Za-z0-9_\-:]/', '', $value);
}

function plugin_sanitize_metadata_attr_tokens($value) {
	$value = trim((string) $value);
	if ($value === '') {
		return '';
	}

	$value = preg_replace('/[^A-Za-z0-9_\-:\s]/', '', $value);
	$value = preg_replace('/\s+/', ' ', $value);

	return trim((string) $value);
}

function plugin_render_sanitized_metadata_nodes($nodes, $allowed_tags) {
	$html = '';

	foreach ($nodes as $node) {
		$html .= plugin_render_sanitized_metadata_node($node, $allowed_tags);
	}

	return $html;
}

function plugin_render_sanitized_metadata_node($node, $allowed_tags) {
	if ($node->nodeType === XML_TEXT_NODE || $node->nodeType === XML_CDATA_SECTION_NODE) {
		return plugin_escape_metadata_html($node->nodeValue);
	}

	if ($node->nodeType !== XML_ELEMENT_NODE) {
		return '';
	}

	$tag = strtolower($node->nodeName);
	$drop_tags = array(
		'script' => true,
		'style' => true,
		'iframe' => true,
		'object' => true,
		'embed' => true,
		'svg' => true,
		'math' => true,
		'form' => true,
		'input' => true,
		'button' => true,
		'textarea' => true,
		'select' => true,
		'option' => true
	);
	if (isset($drop_tags [$tag])) {
		return '';
	}
	if (!isset($allowed_tags [$tag])) {
		return plugin_render_sanitized_metadata_nodes($node->childNodes, $allowed_tags);
	}

	$attrs = '';
	if ($tag === 'a') {
		$href = plugin_sanitize_metadata_url($node->getAttribute('href'));
		if ($href !== '') {
			$attrs .= ' href="' . plugin_escape_metadata_html($href) . '"';
		}

		$title = trim((string) $node->getAttribute('title'));
		if ($title !== '') {
			$attrs .= ' title="' . plugin_escape_metadata_html($title) . '"';
		}

		$id = plugin_sanitize_metadata_attr_identifier($node->getAttribute('id'));
		if ($id !== '') {
			$attrs .= ' id="' . plugin_escape_metadata_html($id) . '"';
		}

		$class = plugin_sanitize_metadata_attr_tokens($node->getAttribute('class'));
		if ($class !== '') {
			$attrs .= ' class="' . plugin_escape_metadata_html($class) . '"';
		}

		$target = strtolower(trim((string) $node->getAttribute('target')));
		if (in_array($target, array('_blank', '_self', '_parent', '_top'), true)) {
			$attrs .= ' target="' . plugin_escape_metadata_html($target) . '"';
		}

		$rel = plugin_sanitize_metadata_attr_tokens($node->getAttribute('rel'));
		if ($target === '_blank') {
			$rels = preg_split('/\s+/', $rel, -1, PREG_SPLIT_NO_EMPTY);
			if (!is_array($rels)) {
				$rels = array();
			}
			if (!in_array('noopener', $rels, true)) {
				$rels [] = 'noopener';
			}
			if (!in_array('noreferrer', $rels, true)) {
				$rels [] = 'noreferrer';
			}
			$rel = trim(implode(' ', array_unique($rels)));
		}
		if ($rel !== '') {
			$attrs .= ' rel="' . plugin_escape_metadata_html($rel) . '"';
		}
	} elseif ($tag === 'span') {
		$id = plugin_sanitize_metadata_attr_identifier($node->getAttribute('id'));
		if ($id !== '') {
			$attrs .= ' id="' . plugin_escape_metadata_html($id) . '"';
		}

		$class = plugin_sanitize_metadata_attr_tokens($node->getAttribute('class'));
		if ($class !== '') {
			$attrs .= ' class="' . plugin_escape_metadata_html($class) . '"';
		}
	}

	if ($tag === 'br') {
		return '<br>';
	}

	return '<' . $tag . $attrs . '>' . plugin_render_sanitized_metadata_nodes($node->childNodes, $allowed_tags) . '</' . $tag . '>';
}

function plugin_sanitize_metadata_html($html) {
	$html = trim((string) $html);
	if ($html === '') {
		return '';
	}

	$allowed_tags = array(
		'a' => true,
		'br' => true,
		'b' => true,
		'strong' => true,
		'i' => true,
		'em' => true,
		'code' => true,
		'kbd' => true,
		'samp' => true,
		'span' => true
	);

	if (!class_exists('DOMDocument')) {
		$html = preg_replace('~<(script|style|iframe|object|embed|svg|math|form|button|textarea|select)\b[^>]*>.*?</\1\s*>~is', '', $html);
		$html = preg_replace('~<(input)\b[^>]*>~i', '', $html);
		$html = strip_tags($html, '<a><br><b><strong><i><em><code><kbd><samp><span>');
		$html = preg_replace('~<br\s*/?>~i', '<br>', $html);
		$html = preg_replace('~<(b|strong|i|em|code|kbd|samp|span)(?:\s[^>]*)?>~i', '<$1>', $html);
		$html = preg_replace('~</(b|strong|i|em|code|kbd|samp|span)\s*>~i', '</$1>', $html);
		$html = preg_replace_callback('~<a\b([^>]*)>~i', function ($matches) {
			$attrs = '';
			$raw_attrs = (string) $matches [1];
			$attr_names = array('href', 'title', 'id', 'class', 'target', 'rel');

			foreach ($attr_names as $attr_name) {
				if (!preg_match('~\b' . preg_quote($attr_name, '~') . '\s*=\s*("([^"]*)"|\'([^\']*)\'|([^\s>]+))~i', $raw_attrs, $attr_match)) {
					continue;
				}

				$value = '';
				if (isset($attr_match [2]) && $attr_match [2] !== '') {
					$value = $attr_match [2];
				} elseif (isset($attr_match [3]) && $attr_match [3] !== '') {
					$value = $attr_match [3];
				} elseif (isset($attr_match [4])) {
					$value = $attr_match [4];
				}

				if ($attr_name === 'href') {
					$value = plugin_sanitize_metadata_url($value);
				} elseif ($attr_name === 'title') {
					$value = trim((string) $value);
				} elseif ($attr_name === 'id') {
					$value = plugin_sanitize_metadata_attr_identifier($value);
				} else {
					$value = plugin_sanitize_metadata_attr_tokens($value);
				}

				if ($attr_name === 'target' && !in_array(strtolower($value), array('_blank', '_self', '_parent', '_top'), true)) {
					$value = '';
				}

				if ($value === '') {
					continue;
				}

				$attrs .= ' ' . $attr_name . '="' . plugin_escape_metadata_html($value) . '"';
			}

			if (preg_match('~\btarget\s*=\s*("([^"]*)"|\'([^\']*)\'|([^\s>]+))~i', $raw_attrs, $target_match)) {
				$target = '';
				if (isset($target_match [2]) && $target_match [2] !== '') {
					$target = strtolower($target_match [2]);
				} elseif (isset($target_match [3]) && $target_match [3] !== '') {
					$target = strtolower($target_match [3]);
				} elseif (isset($target_match [4])) {
					$target = strtolower($target_match [4]);
				}

				if ($target === '_blank' && strpos($attrs, ' rel=') === false) {
					$attrs .= ' rel="noopener noreferrer"';
				}
			}

			return '<a' . $attrs . '>';
		}, $html);

		return $html;
	}

	$old_errors = libxml_use_internal_errors(true);
	$dom = new DOMDocument('1.0', 'UTF-8');
	$loaded = $dom->loadHTML('<?xml encoding="UTF-8"><div>' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
	libxml_clear_errors();
	libxml_use_internal_errors($old_errors);

	if (!$loaded) {
		return strip_tags($html);
	}

	$container = $dom->getElementsByTagName('div')->item(0);
	if (!$container) {
		return strip_tags($html);
	}

	return plugin_render_sanitized_metadata_nodes($container->childNodes, $allowed_tags);
}

/**
 * Read plugin metadata (name, description, author, version) from its info source.
 */
function plugin_getinfo($plugin) {
	static $local = array();
	$plugfile = plugin_getdir($plugin) . 'plugin.' . $plugin . '.php';
	$mt = @file_exists($plugfile) ? (int) @filemtime($plugfile) : 0;
	$sig = $plugin . ':' . $mt;
	if (isset($local [$sig])) {
		return $local [$sig];
	}
	$apcu_on = function_exists('is_apcu_on') ? is_apcu_on() : false;
	$key = $apcu_on ? ('fp:plugin:info:v2:' . $sig) : null;
	if ($key) {
		$hit = false;
		$val = apcu_get($key, $hit);
		if ($hit && is_array($val)) {
			return $local [$sig] = $val;
		}
	}

	$plugin_data = (string) io_load_file($plugfile);
	preg_match('|Plugin Name:(.*)|i', $plugin_data, $m_name);
	preg_match('|Plugin URI:(.*)|i', $plugin_data, $m_puri);
	preg_match('|Description:(.*)|i', $plugin_data, $m_desc);
	preg_match('|Author:(.*)|i', $plugin_data, $m_author);
	preg_match('|Author URI:(.*)|i', $plugin_data, $m_auri);

	$version = '';
	if (preg_match('|Version:(.*)|i', $plugin_data, $m_ver)) {
		$version = sanitize_text_field($m_ver [1]);
	}

	$name = isset($m_name [1]) ? sanitize_text_field($m_name [1]) : $plugin;
	$description = isset($m_desc [1]) ? plugin_sanitize_metadata_html($m_desc [1]) : '';
	$author_text = isset($m_author [1]) ? sanitize_text_field($m_author [1]) : '';
	$author_uri = isset($m_auri [1]) ? plugin_sanitize_metadata_url($m_auri [1]) : '';
	$plugin_uri = isset($m_puri [1]) ? plugin_sanitize_metadata_url($m_puri [1]) : '';

	$title = plugin_escape_metadata_html($name);
	if ($plugin_uri !== '' && $name !== '') {
		// '" title="'.__('Visit plugin homepage').'">'.
		$title = '<a href="' . plugin_escape_metadata_html($plugin_uri) . '">' . plugin_escape_metadata_html($name) . '</a>';
	}

	$author = plugin_escape_metadata_html($author_text);
	if ($author_uri !== '' && $author_text !== '') {
		// . '" title="'.__('Visit author homepage').
		$author = '<a href="' . plugin_escape_metadata_html($author_uri) . '">' . plugin_escape_metadata_html($author_text) . '</a>';
	}

	$out = array(
		'name' => $name,
		'title' => $title,
		'description' => $description,
		'author' => $author,
		'version' => $version,
	);
	if ($key) {
		@apcu_set($key, $out, 0);
	}
	$local [$sig] = $out;
	return $out;
}

$smarty->registerPlugin('function', 'plugin_getdir', 'smarty_function_plugin_getdir');
?>
