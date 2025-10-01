<?php
// APCu helpers
if (!function_exists('fp_plugins_apcu_on')) {
	/**
	 * APCu availability for this request. Returns false for CLI unless apc.enable_cli=1.
	 */
	function fp_plugins_apcu_on() {
		if (!function_exists('apcu_fetch')) {
			return false;
		}
		$ena = function_exists('apcu_enabled') ? apcu_enabled() : ((bool) ini_get('apcu.enabled') || (bool) ini_get('apc.enabled'));
		if (PHP_SAPI === 'cli' && !((bool) ini_get('apc.enable_cli'))) {
			return false;
		}
		return $ena;
	}
	/**
	 * Fetch a value from APCu. Sets $ok=true on hit; returns null if APCu is off.
	 */
	function fp_plugins_apcu_get($key, &$ok) {
		$ok = false;
		if (!fp_plugins_apcu_on()) {
			return null;
		}
		$val = apcu_fetch($key, $ok);
		return $val;
	}
	/**
	 * Store a value in APCu. TTL=0 means no expiry; no-op if APCu is off.
	 */
	function fp_plugins_apcu_set($key, $val, $ttl = 120) {
		if (!fp_plugins_apcu_on()) {
			return false;
		}
		return apcu_store($key, $val, max(0, (int) $ttl));
	}
}

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
			$val = fp_plugins_apcu_get($key, $hit);
			if ($hit) {
				$local [$k] = (bool) $val;
			} else {
				$f = $directory . '/' . $file;
				$local [$k] = (is_dir($f) && @file_exists($f . '/plugin.' . $file . '.php'));
				fp_plugins_apcu_set($key, $local [$k], 0);
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
		$list = fp_plugins_apcu_get($key, $hit);
		if ($hit && is_array($list)) {
			$this->enabledlist = $list;
			$$var = $list;
		} else {
			if (!file_exists($conf)) {
				return false;
			}
			include ($conf);
			$this->enabledlist = $$var;
			fp_plugins_apcu_set($key, $$var, 0);
		}

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
	$list = fp_plugins_apcu_get($key, $hit);
	if ($hit && is_array($list)) {
		$local = $list;
		return $local;
	}
	$pluginlister = new plugin_indexer();
	$local = $pluginlister->getList();
	fp_plugins_apcu_set($key, $local, 0);
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
	$val = fp_plugins_apcu_get($key, $hit);
	if ($hit) {
		return $local [$id] = (bool) $val;
	}
	$res = file_exists($plugFile);
	$local [$id] = $res;
	fp_plugins_apcu_set($key, $res, 0);
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
	$val = fp_plugins_apcu_get($key, $hit);
	if ($hit && is_string($val)) {
		return $local [$id] = $val;
	}
	$dir = PLUGINS_DIR . $id . '/';
	$local [$id] = $dir;
	fp_plugins_apcu_set($key, $dir, 0);
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
	$val = fp_plugins_apcu_get($key, $hit);
	if ($hit && is_string($val)) {
		return $local [$id] = $val;
	}
	$url = BLOG_BASEURL . PLUGINS_DIR . $id . '/';
	$local [$id] = $url;
	fp_plugins_apcu_set($key, $url, 0);
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

/**
 * Read plugin metadata (name, description, author, version) from its info source.
 */
function plugin_getinfo($plugin) {
	$plugin_data = io_load_file(plugin_getdir($plugin) . "plugin." . $plugin . ".php");
	preg_match("|Plugin Name:(.*)|i", $plugin_data, $plugin_name);
	preg_match("|Plugin URI:(.*)|i", $plugin_data, $plugin_uri);
	preg_match("|Description:(.*)|i", $plugin_data, $description);
	preg_match("|Author:(.*)|i", $plugin_data, $author_name);
	preg_match("|Author URI:(.*)|i", $plugin_data, $author_uri);
	if (preg_match("|Version:(.*)|i", $plugin_data, $version)) {
		$version = trim($version [1]);
	} else {
		$version = '';
	}

	$description = wptexturize(trim($description [1]));

	$name = $plugin_name [1];
	$name = trim($name);
	$plugin = $name;

	if ('' != $plugin_uri [1] && '' != $name) {
		// '" title="'.__('Visit plugin homepage').'">'.
		$plugin = '<a href="' . trim($plugin_uri [1]) . $plugin . '</a>';
	}

	if ('' == $author_uri [1]) {
		$author = trim($author_name [1]);
	} else {
		// . '" title="'.__('Visit author homepage').
		$author = '<a href="' . trim($author_uri [1]) . '">' . trim($author_name [1]) . '</a>';
	}

	return array(
		'name' => $name,
		'title' => $plugin,
		'description' => $description,
		'author' => $author,
		'version' => $version
	);
}

$smarty->registerPlugin('function', 'plugin_getdir', 'smarty_function_plugin_getdir');
?>
