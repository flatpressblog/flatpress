<?php
/**
 * Plugin Name: PrettyURLs
 * Version: 3.0.2
 * Plugin URI: https://www.flatpress.org
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 * Description: Enables SEO friendly, pretty URLs (<a href="./fp-plugins/prettyurls/doc_prettyurls.txt" title="More information" target="_blank">via htaccess or nginx-config</a>). Part of the standard distribution.
 */

/**
 * Place where the index is stored
 */
if (!defined('PRETTYURLS_TITLES')) {
	define('PRETTYURLS_TITLES', true);
}
define('PRETTYURLS_PATHINFO', !file_exists(ABS_PATH . '.htaccess'));
define('PRETTYURLS_CACHE', CACHE_DIR . '%%prettyurls-index.tmp');
define('PRETTYURLS_CATS', CACHE_DIR . '%%prettyurls-cats.tmp');

/**
 * File existance check
 */

// memo
// register_plugin_setup('plugin_id', 'setup_func');
function plugin_prettyurls_setup() {
	if (file_exists(ABS_PATH . '.htaccess')) {
		return 1;
	}

	if (!is_writable(ABS_PATH)) {
		return -2;
	}

	return 1;
}

class Plugin_PrettyURLs {

	var $index = array();

	var $status = 0;

	var $date_handled = false;

	var $categories = null;

	var $baseurl = null;

	var $mode = null;

	var $fp_params;

	function categories($force = true) {
		// $force === true: rebuild from master data and update cache file
		// $force === false: load from cache file if available
		if ($this->categories && !$force) {
			return;
		}

		if ($force || !file_exists(PRETTYURLS_CATS)) {
			$d = entry_categories_get('defs');
			$list = array();
			foreach ($d as $k => $v) {
				$list [$k] = sanitize_title($v);
			}
			io_write_file(PRETTYURLS_CATS, serialize($list));
		} else {
			$f = io_load_file(PRETTYURLS_CATS);
			$list = $f !== false ? @unserialize($f) : array();
			if (!is_array($list)) {
				$list = array();
			}
		}
		$this->categories = $list;
	}

	function md5($id, $title) {
		$date = date_from_id($id);
		if (isset($date ['y'], $date ['m'], $date ['d'])) {
			return md5($date ['y'] . $date ['m'] . $date ['d'] . $title);
		}
	}

	function permalink($str, $id) {
		global $fpdb, $post;

		if (isset($post) && PRETTYURLS_TITLES) {
			$title = sanitize_title($post ['subject']);
		} else {
			$title = $id;
		}
		$date = date_from_id($id);
		// yeah, hackish, I know...

		return isset($date ['y'], $date ['m'], $date ['d']) ? $this->baseurl . "20" . $date ['y'] . "/" . $date ['m'] . "/" . $date ['d'] . "/" . $title . "/" : $this->baseurl . $title . "/";
	}

	function commentlink($str, $id) {
		$link = $this->permalink($str, $id);
		return $link . "comments/";
	}

	function feedlink($str, $type) {
		return $this->baseurl . "feed/" . $type . "/";
	}

	function commentsfeedlink($str, $type, $id) {
		$link = $this->commentlink($str, $id);
		return $link . "feed/" . $type . "/";
	}

	function staticlink($str, $id) {
		return $this->baseurl . $id . "/";
	}

	function categorylink($str, $catid) {
		if (PRETTYURLS_TITLES) {
			if (@$this->categories [$catid]) {
				return $this->baseurl . "category/" . $this->categories[$catid] . "/";
			} else {
				return $str;
			}
		} else {
			return $this->baseurl . "category/" . $catid . "/";
		}
	}

	function yearlink($str, $y) {
		return $this->baseurl . "20" . $y . "/";
	}

	function monthlink($str, $y, $m) {
		return $this->yearlink($str, $y) . $m . "/";
	}

	function daylink($str, $y, $m, $d) {
		return $this->monthlink($str, $y, $m) . $d . "/";
	}

	function cache_create() {
		$this->index = array();

		/**
		 * $o =& entry_init();
		 *
		 * $entries = $o->getList();
		 */

		$o = new FPDB_Query(array(
			'start' => 0,
			'count' => -1,
			'fullparse' => false
		), null);

		while ($o->hasMore()) {
			list ($id, $contents) = $o->getEntry();
			$date = date_from_id($id);
			echo $contents ['subject'], "\n";
			$md5 = md5(sanitize_title($contents ['subject']));
			$this->index [$date ['y']] [$date ['m']] [$date ['d']] [$md5] = $id;
		}

		$this->cache_save();
		io_write_file(PRETTYURLS_CACHE, 'dummy');
	}

	function handle_categories($matches) {
		if (!$this->categories) {
			return;
		}

		// $this->categories contains sanitized category names, so we have to sanitize before the search
		$sanitizedtitle = sanitize_title($matches [1]);

		if (PRETTYURLS_TITLES) {
			if ($c = array_search($sanitizedtitle, $this->categories)) {
				$this->fp_params ['cat'] = $c;
			} else {
				return $matches [0];
			}
		} else {
			$this->fp_params ['cat'] = $sanitizedtitle;
		}
	}

	/**
	 * named matches are not supported here
	 */
	function handle_date($matches) {
		$this->fp_params ['y'] = $matches [1];
		if (isset($matches [3])) {
			$this->fp_params ['m'] = $matches [3];
		}
		if (isset($matches [5])) {
			$this->fp_params ['d'] = $matches [5];
		}

		$this->date_handled = true;
	}

	function handle_static($matches) {
		$this->fp_params ['page'] = $matches [1];
		$this->status = 2;
	}

	function handle_entry($matches) {
		// the cache contains (md5'ed) sanitized entry names, so we have to sanitize before handling it
		$sanitizedtitle = sanitize_title($matches [1]);

		if (!PRETTYURLS_TITLES) {
			$this->fp_params ['entry'] = $sanitizedtitle;
			return;
		}

		// Ensure 'y', 'm', and 'd' keys exist in $this->fp_params before accessing them
		if (!isset($this->fp_params ['y'], $this->fp_params ['m'], $this->fp_params ['d'])) {
			// If any of the keys are missing, create a fake entry and stop further processing
			$this->fp_params ['entry'] = 'a';
			return;
		}

		// Retrieve the cache if all keys exist and check for the entry
		if ($this->cache_get($this->fp_params ['y'], $this->fp_params ['m'], $this->fp_params ['d'], md5($sanitizedtitle))) {
			// Check if the required keys exist in the cache index
			$y = $this->fp_params ['y'];
			$m = $this->fp_params ['m'];
			$d = $this->fp_params ['d'];
			$hash = md5($sanitizedtitle);

			if (isset($this->index [$y] [$m] [$d] [$hash])) {
				$this->fp_params ['entry'] = $this->index [$y] [$m] [$d] [$hash];
			} else {
				// If the hash key does not exist, set a fake entry
				$this->fp_params ['entry'] = 'a';
			}
		} else {
			// If the cache_get returns false, set a fake entry
			$this->fp_params ['entry'] = 'a';
		}
	}

	function handle_page($matches) {
		$this->fp_params ['paged'] = $matches [1];
		$this->status = 2;
	}

	function handle_comment($matches) {
		$this->fp_params ['comments'] = true;
	}

	function handle_feed($matches) {
		$this->fp_params ['feed'] = isset($matches [2]) ? $matches [2] : 'rss2';
	}

	private function server_rewrite_active() {
		$req = isset($_SERVER ['REQUEST_URI']) ? (string) $_SERVER ['REQUEST_URI'] : '';
		$sn = isset($_SERVER ['SCRIPT_NAME']) ? (string) $_SERVER ['SCRIPT_NAME'] : '';
		// Real redirection active when index.php is executed but not present in the request URI
		if ($req !== '' && $sn !== '' && strpos($req, 'index.php') === false && substr($sn, -9) === 'index.php') {
			return true; // vHost/server rewrite (e.g., nginx try_files, Apache vHost)
		}
		// IIS URL Rewrite / ISAPI_Rewrite
		if (!empty($_SERVER ['IIS_WasUrlRewritten']) && $_SERVER ['IIS_WasUrlRewritten'] == '1') {
			return true;
		}
		// Only trust HTTP_X_REWRITE_URL as a rewrite signal on IIS and when index.php is the executing script.
		if (!empty($_SERVER ['HTTP_X_REWRITE_URL'])) {
			$isIIS = !empty($_SERVER ['SERVER_SOFTWARE']) && stripos((string) $_SERVER ['SERVER_SOFTWARE'], 'IIS') !== false;
			if ($isIIS && substr($sn, -9) === 'index.php') {
				return true;
			}
		}
		// Server sets REDIRECT_URL during rewriting
		if (!empty($_SERVER ['REDIRECT_URL'])) {
			return true;
		}
		return false;
	}

	private function server_can_pathinfo() {
		// Real server signals (also via ProxyFCGISetEnvIf/SetEnv)
		if (!empty($_SERVER ['PATH_INFO']) || !empty($_SERVER ['ORIG_PATH_INFO'])) {
			return true;
		}
		// Without explicit PATH_INFO and with cgi.fix_pathinfo=0: not available
		$fix = @ini_get('cgi.fix_pathinfo');
		if ($fix !== false && (string) $fix === '0') {
			return false;
		}
		return false;
	}

	/**
	 * Locks or unlocks the radios
	 * Preview of server capabilities outside the admin area.
	 * Delivers: can_pretty (Rewrite), can_pathinfo, can_get.
	 */
	public function modes_capabilities_preview() {
		$htPath = rtrim(ABS_PATH, "/\\") . DIRECTORY_SEPARATOR . '.htaccess';
		$hasHt = is_file($htPath);

		$bak = array(
			'REQUEST_URI' => isset($_SERVER ['REQUEST_URI']) ? $_SERVER ['REQUEST_URI'] : null,
			'SCRIPT_NAME' => isset($_SERVER ['SCRIPT_NAME']) ? $_SERVER ['SCRIPT_NAME'] : null,
			'PATH_INFO' => isset($_SERVER ['PATH_INFO']) ? $_SERVER ['PATH_INFO'] : null,
			'ORIG_PATH_INFO' => isset($_SERVER ['ORIG_PATH_INFO']) ? $_SERVER ['ORIG_PATH_INFO'] : null,
			'PHP_SELF' => isset($_SERVER ['PHP_SELF']) ? $_SERVER ['PHP_SELF'] : null,
			'QUERY_STRING' => isset($_SERVER ['QUERY_STRING']) ? $_SERVER ['QUERY_STRING'] : null,
			'IIS_WasUrlRewritten' => isset($_SERVER ['IIS_WasUrlRewritten']) ? $_SERVER ['IIS_WasUrlRewritten'] : null,
			'HTTP_X_REWRITE_URL' => isset($_SERVER ['HTTP_X_REWRITE_URL']) ? $_SERVER ['HTTP_X_REWRITE_URL'] : null,
			'REDIRECT_URL' => isset($_SERVER ['REDIRECT_URL']) ? $_SERVER ['REDIRECT_URL'] : null,
		 );

		$root = rtrim(BLOG_ROOT, '/');

		// Test rewrite capability (request without index.php)
		$_SERVER ['REQUEST_URI'] = $root . '/';
		$_SERVER ['SCRIPT_NAME'] = $root . '/index.php';
		unset($_SERVER ['PATH_INFO'], $_SERVER ['ORIG_PATH_INFO']);
		$_SERVER ['PHP_SELF'] = $_SERVER ['SCRIPT_NAME'];
		$_SERVER ['QUERY_STRING'] = '';
		unset($_SERVER ['IIS_WasUrlRewritten'], $_SERVER ['HTTP_X_REWRITE_URL'], $_SERVER ['REDIRECT_URL']);
		$can_pretty = $this->server_rewrite_active();

		// Path info:
		$fix = @ini_get('cgi.fix_pathinfo');
		$can_pathinfo = false;
		if (!($fix !== false && (string)$fix === '0')) {
			// Evaluate live environment
			if (!empty($bak ['PATH_INFO']) || !empty($bak ['ORIG_PATH_INFO'])) {
				$can_pathinfo = true;
			} else {
				// Fallback: REQUEST_URI begins with base and has additional segments
				$base = defined('BLOG_ROOT') ? BLOG_ROOT : '';
				if (substr($base, -10) === '/index.php') {
					// Safely remove '/index.php'
					$base = substr($base, 0, -10);
				}
				$baseNorm = rtrim((string)$base, '/');

				if (!empty($bak ['REQUEST_URI'])) {
					$req = (string)$bak ['REQUEST_URI'];
					// Remove query string
					$qpos = strpos($req, '?');
					if ($qpos !== false) {
						$req = substr($req, 0, $qpos);
					}
					$reqNorm = rtrim($req, '/');

					if ($baseNorm === '' || strpos($reqNorm, $baseNorm) === 0) {
						$suffix = substr($reqNorm, strlen($baseNorm));
						// Additional segments present? (e.g., ‘/page’ or ‘/page/1’)
						if ($suffix !== '' && $suffix [0] === '/' && strlen($suffix) > 1) {
							$can_pathinfo = true;
						}
					}
				}
			}
		}

		// Restore
		foreach ($bak as $k => $v) {
			if ($v === null) {
				unset($_SERVER [$k]);
			} else {
				$_SERVER [$k] = $v;
			}
		}

		return array(
			'can_pretty' => (bool) $can_pretty,
			'can_pathinfo' => (bool) $can_pathinfo,
			'can_get' => true,
		);
	}

	/**
	 * Auto mode detection with request cache and optional APCu.
	 * Returns: 3=Pretty, 1=PATH_INFO, 2=GET
	 */
	function auto_mode_detect() {
		$htPath = rtrim(ABS_PATH, "/\\") . DIRECTORY_SEPARATOR . '.htaccess';
		$hasHt = is_file($htPath);
		$sn = isset($_SERVER ['SCRIPT_NAME']) ? (string) $_SERVER ['SCRIPT_NAME'] : '';
		$sw = isset($_SERVER ['SERVER_SOFTWARE']) ? (string) $_SERVER ['SERVER_SOFTWARE'] : '';
		$flags = implode('|', array(
			$hasHt ? 'ht1' : 'ht0',
			(!empty($_SERVER ['IIS_WasUrlRewritten']) && $_SERVER ['IIS_WasUrlRewritten'] == '1') ? 'iis1' : 'iis0',
			!empty($_SERVER ['HTTP_X_REWRITE_URL']) ? 'xrw1' : 'xrw0',
			!empty($_SERVER ['REDIRECT_URL']) ? 'redir1' : 'redir0',
			!empty($_SERVER ['PATH_INFO']) ? 'pi1' : 'pi0',
			!empty($_SERVER ['ORIG_PATH_INFO']) ? 'opi1' : 'opi0',
			(!empty($_SERVER ['PHP_SELF']) && strpos((string) $_SERVER ['PHP_SELF'], 'index.php/') !== false) ? 'ps1' : 'ps0',
			((isset($_SERVER ['REQUEST_URI']) && strpos((string) $_SERVER ['REQUEST_URI'], 'index.php/') !== false)) ? 'ru1' : 'ru0',
			$sn,
			$sw
		));
		// Version/namespace prefix for separating different implementations
		$gen = (int) plugin_getoptions('prettyurls', 'apcu_gen');
		if ($gen < 1) {
			$gen = 1;
		}
		$key = 'prettyurls:auto:v3:g' . $gen . ':' . md5($flags);
		static $reqCache = array();
		if (isset($reqCache [$key])) {
			return (int) $reqCache [$key];
		}
		// Only use APCu via Core wrapper with FP namespace
		if (function_exists('is_apcu_on') && is_apcu_on() && function_exists('apcu_get')) {
			$ok = false;
			$val = apcu_get(apcu_key('prettyurls', $key), $ok);
			if ($ok) {
				$reqCache [$key] = (int) $val;
				return (int) $val;
			}
		}
		$mode = ($this->server_rewrite_active() ? 3 : ($this->server_can_pathinfo() ? 1 : 2));
		$reqCache [$key] = (int) $mode;
		if (function_exists('is_apcu_on') && is_apcu_on() && function_exists('apcu_set')) {
			// Keep TTL small; namespacing is done in core.fileio.php via apcu_key()
			apcu_set(apcu_key('prettyurls', $key), (int) $mode, 120);
		}
		return (int) $mode;
	}

	/**
	 * Preview: automatic mode specifically for calling index.php without any additional parameters.
	 * Uses a minimally manipulated SERVER environment and then restores it.
	 * Returns: 3=Pretty, 1=PATH_INFO, 2=GET
	 */
	function auto_mode_detect_preview() {
		$htPath = rtrim(ABS_PATH, "/\\") . DIRECTORY_SEPARATOR . '.htaccess';
		$hasHt = is_file($htPath);
		// Save original
		$bak = array(
			'REQUEST_URI' => isset($_SERVER ['REQUEST_URI']) ? $_SERVER ['REQUEST_URI'] : null,
			'SCRIPT_NAME' => isset($_SERVER ['SCRIPT_NAME']) ? $_SERVER ['SCRIPT_NAME'] : null,
			'PATH_INFO' => isset($_SERVER ['PATH_INFO']) ? $_SERVER ['PATH_INFO'] : null,
			'ORIG_PATH_INFO' => isset($_SERVER ['ORIG_PATH_INFO']) ? $_SERVER ['ORIG_PATH_INFO'] : null,
			'PHP_SELF' => isset($_SERVER ['PHP_SELF']) ? $_SERVER ['PHP_SELF'] : null,
			'QUERY_STRING' => isset($_SERVER ['QUERY_STRING']) ? $_SERVER ['QUERY_STRING'] : null,
			'IIS_WasUrlRewritten' => isset($_SERVER ['IIS_WasUrlRewritten']) ? $_SERVER ['IIS_WasUrlRewritten'] : null,
			'HTTP_X_REWRITE_URL' => isset($_SERVER ['HTTP_X_REWRITE_URL']) ? $_SERVER ['HTTP_X_REWRITE_URL'] : null,
			'REDIRECT_URL' => isset($_SERVER ['REDIRECT_URL']) ? $_SERVER ['REDIRECT_URL'] : null,
		);
		$root = rtrim(BLOG_ROOT, '/');
		// Simulate index call without additional parameters
		$_SERVER ['REQUEST_URI'] = $root . '/';
		$_SERVER ['SCRIPT_NAME'] = $root . '/index.php';
		unset($_SERVER ['PATH_INFO'], $_SERVER ['ORIG_PATH_INFO']);
		$_SERVER ['PHP_SELF'] = $_SERVER ['SCRIPT_NAME'];
		$_SERVER ['QUERY_STRING'] = '';
		unset($_SERVER ['IIS_WasUrlRewritten'], $_SERVER ['HTTP_X_REWRITE_URL'], $_SERVER ['REDIRECT_URL']);
		// Determine mode
		$mode = ($hasHt && $this->server_rewrite_active()) ? 3 : ($this->server_can_pathinfo() ? 1 : 2);
		// Reset environment
		foreach ($bak as $k => $v) {
			if ($v === null) {
				unset($_SERVER [$k]);
			} else {
				$_SERVER [$k] = $v;
			}
		}
		return (int) $mode;
	}

	/**
	 * Request-local memo for PrettyURLs mode
	 */
	private function get_mode() {
		if ($this->mode !== null) {
			return (int) $this->mode;
		}
		$opt = plugin_getoptions('prettyurls', 'mode');
		$this->mode = (int) $opt;
		return $this->mode;
	}

	function get_url() {
		$baseurl = BLOG_BASEURL;
		$opt = $this->get_mode();
		$reqUri = isset($_SERVER ['REQUEST_URI']) ? (string)$_SERVER ['REQUEST_URI'] : '';
		$rootLen = strlen(BLOG_ROOT);
		$url = ($rootLen > 0) ? substr($reqUri, $rootLen - 1) : $reqUri;

		$urllenght = strlen($url);

		if (isset($_SERVER ['PATH_INFO'])) {
			$pathinfo = $_SERVER ['PATH_INFO'];
		} else {
			$pathinfo = '';
		}

		$htPath = rtrim(ABS_PATH, "/\\") . DIRECTORY_SEPARATOR . '.htaccess';
		$hasHt = is_file($htPath);

		/**
		 * Initially, neither PATH_INFO nor Pretty is set in Automatic mode,
		 * because although we check whether the web server is capable,
		 * we cannot reliably check whether all conditions are actually met.
		 */

		// If not configured or automatic, check htaccess
		if ($opt === null || $opt === 0) {
			// If htaccess exists, then Pretty (3), otherwise HTTP Get (2)
			$opt = $hasHt ? 3 : 2;
		}

		// Resolve effective mode once, then apply mapping
		if ($opt === null || $opt === 0 || $opt === 3) {
			$opt = (int) $this->auto_mode_detect();
		}

		switch ($opt) {
			case 1:
				$baseurl .= 'index.php/';
				if ($urllenght < 2) {
					$url = "/";
				} else {
					// Path Info
					$url = $pathinfo !== '' ? $pathinfo : '/';
				}
				break;
			case 2:
				// HTTP Get
				$baseurl .= '?u=/';
				$url = isset($_GET ['u']) ? (string)$_GET ['u'] : '';
				break;
			case 3:
				// Pretty: do nothing, it's BLOG_BASEURL
				break;
		}

		$this->baseurl = $baseurl;
		$this->mode = $opt;

		return $url;
	}

	/**
	 * here is where the real work is done.
	 *
	 * First we load the cache if exists;
	 *
	 * We check then if the GET request contains a 'title'
	 * if so, we'll need date and time to construct the md5 sum
	 * with which we index the cache array
	 *
	 * If that entry exists, we set $_GET['entry'] to that ID,
	 * so that FlatPress can find it where it is expected
	 *
	 */
	function cache_init() {
		global $fp_params;

		$this->fp_params = &$fp_params;
		$url = $this->get_url();

		if (!is_string($url)) {
			$url = '';
		}

		if (PRETTYURLS_TITLES) {
			// if ($f = io_load_file(PRETTYURLS_CACHE))
			$this->index = array(); // unserialize($f);

			if (!file_exists(PRETTYURLS_CACHE)) {
				$this->cache_create();
			}

			$this->categories(false);
		}

		if (!defined('MOD_INDEX')) {
			return;
		}

		// removes querystrings
		if (false !== $i = strpos($url, '?')) {
			$url = substr($url, 0, $i);
		}

		// removes anchors
		if (false !== $i = strpos($url, '#')) {
			$url = substr($url, 0, $i);
		}

		if (strrpos($url, '/') != (strlen($url) - 1)) {
			$url .= '/';
		}

		if ($url == '/') {
			return;
		}

		// date
		$url = preg_replace_callback('!^/[0-9]{2}(?P<y>[0-9]{2})(/(?P<m>[0-9]{2})(/(?P<d>[0-9]{2}))?)?!', array(
			&$this,
			'handle_date'
		), $url);

		if (!$this->date_handled) {
			// static page
			$url = preg_replace_callback('|^/([a-zA-Z0-9_-]+)/$|', array(
				&$this,
				'handle_static'
			), $url);
			if ($this->status == 2) {
				return $this->check_url($url);
			}
		}

		$url = preg_replace_callback('{category/([^/]+)/}', array(
			&$this,
			'handle_categories'
		), $url);

		$url = preg_replace_callback('|page/([0-9]+)/$|', array(
			&$this,
			'handle_page'
		), $url);
		if ($this->status == 2) {
			return $this->check_url($url);
		}

		if ($this->date_handled) {
			$url = preg_replace_callback('|^/([^/]+)|', array(
				&$this,
				'handle_entry'
			), $url);
			// if status = 2
			/*
			 * utils_error(404);
			 */

			$url = preg_replace_callback('|^/comments|', array(
				&$this,
				'handle_comment'
			), $url);
		}

		$url = preg_replace_callback('|^/feed(/([^/]*))?|', array(
			&$this,
			'handle_feed'
		), $url);

		$this->check_url($url);
	}

	function check_url($url) {
		if (!empty($url) && $url != '/') {
			$this->fp_params = array(
				'entry' => 'entry000000-000000'
			);
			$url = apply_filters('prettyurls_unhandled_url', $url);
		}
	}

	function cache_delete_elem($id, $date) {

		// is this a title change?
		if (false !== ($ids = $this->cache_get($date ['y'], $date ['m'], $date ['d']))) {
			$hash = array_search($id, $ids);
		} else {
			return;
		}

		if ($hash) {
			unset($this->index [$date ['y']] [$date ['m']] [$date ['d']] [$hash]);

			if (empty($this->index [$date ['y']] [$date ['m']] [$date ['d']])) {
				unset($this->index [$date ['y']] [$date ['m']] [$date ['d']]);

				if (empty($this->index [$date ['y']] [$date ['m']])) {
					unset($this->index [$date ['y']] [$date ['m']]);

					if (empty($this->index [$date ['y']])) {
						unset($this->index [$date ['y']]);
					}
				}
			}
		}

		$this->cache_save();
	}

	function cache_add($id, $arr) {
		$date = date_from_id($id);
		$title = sanitize_title($arr ['subject']);

		$this->cache_delete_elem($id, $date);

		if (!isset($date ['y'], $date ['m'], $date ['d'])) {
			return false;
		}

		if (!isset($this->index [$date ['y']] [$date ['m']]) || $this->index [$date ['y']] [$date ['m']] === false) {
			// Add year and month keys to index, if not present already
			$this->index [$date ['y']] [$date ['m']] = [];
		}

		$this->index [$date ['y']] [$date ['m']] [$date ['d']] [md5($title)] = $id;

		$this->cache_save();

		return true;
	}

	function cache_get($y, $m, $d = null, $h = null) {
		if (!isset($this->index [$y] [$m])) {
			$s = @io_load_file(PRETTYURLS_CACHE . $y . $m);
			$this->index [$y] [$m] = $s ? unserialize($s) : false;
		}

		if (is_null($d)) {
			return $this->index [$y] [$m];
		}

		if (is_null($h)) {
			return isset($this->index [$y] [$m] [$d]) ? $this->index [$y] [$m] [$d] : false;
		}

		if (isset($this->index [$y] [$m] [$d])) {
			return isset($this->index [$y] [$m] [$d] [$h]);
		} else {
			return false;
		}
	}

	function cache_delete($id) {
		$date = date_from_id($id);
		$this->cache_delete_elem($id, $date);
		$this->cache_save();
	}

	function cache_save() {
		if ($this->index) {
			foreach ($this->index as $year => $months) {
				foreach ($months as $month => $days) {
					io_write_file(PRETTYURLS_CACHE . $year . $month, serialize($days));
				}
			}
		}

		return true;
	}

	function nextprevlink($nextprev, $v) {
		global $fpdb;
		$q = &$fpdb->getQuery();

		list ($caption, $id) = call_user_func(array(
			&$q,
			'get' . $nextprev
		));

		if (!$id) {
			return array();
		}

		if ($q->single) {
			$date = date_from_id($id);
			if (PRETTYURLS_TITLES) {
				$title = sanitize_title($caption);
			} else {
				$title = $id;
			}
			$url = $this->baseurl . "20" . $date ['y'] . "/" . $date ['m'] . "/" . $date ['d'] . "/" . $title . "/";

			if ($v > 0) {
				$caption = $caption . ' &raquo; ';
			} else {
				$caption = ' &laquo; ' . $caption;
			}

			return array(
				$caption,
				$url
			);
		}

		// else, we build a complete url

		$l = $this->baseurl;

		$cid = $this->fp_params ['category'] ?? ($this->fp_params ['cat'] ?? null);
		if (is_numeric($cid)) {
			$l = $this->categorylink($l, $cid);
		}

		$y = isset($this->fp_params ['y']) ? (string) $this->fp_params ['y'] : '';
		$m = isset($this->fp_params ['m']) ? (string) $this->fp_params ['m'] : '';
		$d = isset($this->fp_params ['d']) ? (string) $this->fp_params ['d'] : '';

		if ($y !== '') {
			$l = $this->yearlink($l, $y);
			if ($m !== '') {
				$l = $this->monthlink($l, $y, $m);
				if ($d !== '') {
					$l = $this->daylink($l, $y, $m, $d);
				}
			}
		}

		$page = 1;
		if (!empty($this->fp_params ['paged']) && (int) $this->fp_params ['paged'] > 1) {
			$page = (int) $this->fp_params ['paged'];
		}
		$page += (int) $v;
		if ($page > 0) {
			$l .= 'page/' . $page . '/';
		}

		return array(
			$caption,
			$l
		);
	}

	/**
	 * Unified 301 canonical redirect function
	 *
	 * Canonicalizes frontend requests across PrettyURLs modes and redirects to one canonical style per mode.
	 *
	 * Behavior
	 * --------
	 * - Runs in frontend only (returns early when MOD_INDEX is not defined).
	 * - Detects the incoming URL "style" and its route suffix:
	 *     GET style:              ?u=/...
	 *     Path-Info style:        /index.php/..., or PATH_INFO
	 *     Pretty style:           /...
	 * - Redirects with HTTP 301 if the detected style does not match the selected mode:
	 *     Mode 1 (Path-Info)      BLOG_BASEURL/index.php{suffix}
	 *     Mode 2 (HTTP-Get)       BLOG_BASEURL/?u={suffix}
	 *     Mode 3 (Pretty)         BLOG_BASEURL/{suffix}
	 * - Redirects only when there are no extra query parameters (except key 'u' in GET style).
	 * - Normalizes {suffix} to a single leading and trailing slash and collapses duplicate slashes.
	 * - Guarded to avoid loops; redirects only when target style differs from the current style.
	 *
	 * Supported routes (suffix patterns)
	 * ----------------------------------
	 * - Pagination:                /page/{n}/, /paged/{n}/
	 * - Category & Tag:            /category/{name}/, /tag/{name}/
	 * - Archives:                  /archives/{YYYY}/, /archives/{YYYY}/{MM}/, /archive/{YYYY}/, /archive/{YYYY}/{MM}/
	 * - Bare date archives:        /{YYYY}/, /{YYYY}/{MM}/, optional /{YYYY}/{MM}/{DD}/
	 * - Dated permalinks:          /{YYYY}/{MM}/{DD}/{slug}/
	 * - Global feeds:              /feed/(rss2|atom)/
	 * - Entry comments feeds:      /{YYYY}/{MM}/{DD}/{slug}/comments/feed/(rss2|atom)/
	 * - Static pages and entries:  /static/{slug}/, /entry/{slug}/
	 * - Single-segment static page /{slug}/
	 */
	function prettyurls_redirect_canonical() {
		if (!defined('MOD_INDEX')) {
			return;
		}
		// === Cross-mode canonicalization for common routes ===
		// Routes: page/N, paged/N, category/NAME, tag/NAME, archive[s]/YYYY(/MM)?, static/SLUG, entry/SLUG
		// Redirect only if there are no extra query params (besides 'u' in GET style).
		$plugin_prettyurls = isset($GLOBALS ['plugin_prettyurls']) ? $GLOBALS ['plugin_prettyurls'] : null;
		if ($plugin_prettyurls && isset($plugin_prettyurls->mode)) {
			$opt = (int)$plugin_prettyurls->mode;
		}

		$req = isset($_SERVER ['REQUEST_URI']) ? (string)$_SERVER ['REQUEST_URI'] : '';
		$path = $req !== '' ? (string)parse_url($req, PHP_URL_PATH) : '';
		$qry = $req !== '' ? (string)parse_url($req, PHP_URL_QUERY) : '';
		$style = '';
		$suffix = '';

		// BOF Helper: extract route suffix from a given path (BLOG_ROOT aware)
		$extract_suffix = function($pth) {
			$root = rtrim(BLOG_ROOT, '/');
			$pp = $pth;
			if ($root !== '' && strpos($pp, $root) === 0) {
				$pp = substr($pp, strlen($root));
				if ($pp === false) {
					$pp = '';
				}
			}
			if ($pp === '') {
				$pp = $pth;
			}
			$pp = preg_replace('!/{2,}!', '/', $pp);

			static $rx = null;
			if ($rx === null) {
				$rx = array(
					// Pagination
					'!^/(?:page|paged)/([0-9]+)/?$!i',
					// Post-specific comment feeds
					'!^/[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/comments/feed/(?:rss2|atom)/?$!i',
					// Global feeds
					'!^/feed/(?:rss2|atom)/?$!i',
					// Date-based entry permalinks
					'!^/[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/?$!i',
					// Bare date archives: /YYYY/ und /YYYY/MM/ and optional /YYYY/MM/DD/
					'!^/[0-9]{4}(?:/[0-9]{1,2}(?:/[0-9]{1,2})?)?/?$!i',
					// "archives"-Routes
					'!^/(?:archive|archives)/([0-9]{4})(?:/([0-9]{1,2}))?/?$!i',
					// Taxonomies
					'!^/category/([^/]+)/?$!i',
					'!^/tag/([^/]+)/?$!i',
					// Static pages and entries
					'!^/static/([^/]+)/?$!i',
					'!^/entry/([^/]+)/?$!i',
					// Single-segment static page slugs
					'!^/([A-Za-z0-9_-]+)/?$!i',
				);
			}

			foreach ($rx as $r) {
				if (preg_match($r, $pp)) {
					return rtrim($pp, '/') . '/';
				}
			}
			return '';
		};

		// Detect incoming style and suffix
		if (isset($_GET ['u']) && is_string($_GET ['u'])) {
			$u = (string)$_GET ['u'];
			$cand = $extract_suffix($u);
			if ($cand !== '') {
				$style = 'get';
				$suffix = $cand;
			}
		}
		if ($style === '' && !empty($_SERVER ['PATH_INFO'])) {
			$pi = (string)$_SERVER ['PATH_INFO'];
			$cand = $extract_suffix($pi);
			if ($cand !== '') {
				$style = 'pathinfo';
				$suffix = $cand;
			}
		}
		if ($style === '' && is_string($path) && strpos($path, '/index.php/') !== false) {
			$after = substr($path, strpos($path, '/index.php/') + 10);
			if ($after !== '' && $after [0] !== '/') {
				$after = '/' . $after;
			}
			$cand = $extract_suffix($after);
			if ($cand !== '') {
				$style = 'pathinfo';
				$suffix = $cand;
			}
		}
		if ($style === '' && is_string($path)) {
			$cand = $extract_suffix($path);
			if ($cand !== '') {
				$style = 'pretty';
				$suffix = $cand;
			}
		}

		if ($style !== '') {
			// Normalize suffix to single leading/trailing slash
			if ($suffix !== '') {
				$suffix = '/' . trim($suffix, '/');
				$suffix = preg_replace('!/{2,}!', '/', $suffix) . '/';
				$suffix = preg_replace('!/+$!', '/', $suffix);
			}
			// No redirect if there are extra params (besides 'u' in GET style)
			$extra = false;
			if ($style === 'get') {
				foreach (array_keys($_GET) as $k) {
					if ($k !== 'u') {
						$extra = true;
						break;
					}
				}
			} else {
				if (is_string($qry) && $qry !== '' && $qry !== null) {
					$extra = true;
				}
			}
			if (!$extra) {
				$target = '';
				if ($opt === 1 && $style !== 'pathinfo') {
					$target = BLOG_BASEURL . 'index.php' . $suffix;
				} elseif ($opt === 2 && $style !== 'get') {
					$target = BLOG_BASEURL . '?u=' . $suffix;
				} elseif ($opt === 3 && $style !== 'pretty') {
					$target = BLOG_BASEURL . ltrim($suffix, '/');
				}
				if ($target !== '' && !headers_sent()) {
					if (!defined('PRETTYURLS_CANONICAL_REDIRECT_RAN')) {
						define('PRETTYURLS_CANONICAL_REDIRECT_RAN', true);
					}
					header('Location: ' . $target, true, 301);
					exit();
				}
			}
		}
		// EOF Helper

		if (defined('PRETTYURLS_CANONICAL_REDIRECT_RAN')) {
			return;
		}

		// Resolve baseurl for current mode (Auto/Pretty/Path Info/HTTP Get)
		global $plugin_prettyurls;
		if (isset($plugin_prettyurls) && method_exists($plugin_prettyurls, 'get_url')) {
			if (!isset($plugin_prettyurls->baseurl) || !isset($plugin_prettyurls->mode)) {
				$plugin_prettyurls->get_url(); // sets $plugin_prettyurls->baseurl and ->mode
			}
		}
		$base = isset($plugin_prettyurls->baseurl) ? $plugin_prettyurls->baseurl : BLOG_BASEURL;

		// Never assume Pretty base when unresolved
		if ((!isset($plugin_prettyurls->baseurl) || $plugin_prettyurls->baseurl === null) && $this->get_mode() == 0) {
			$auto = method_exists($plugin_prettyurls,'auto_mode_detect_preview') ? (int)$plugin_prettyurls->auto_mode_detect_preview() : (int)$plugin_prettyurls->auto_mode_detect();
			if ($auto === 1) {
				$base = BLOG_BASEURL . 'index.php/'; // Path Info
			} elseif ($auto === 2) {
				$base = BLOG_BASEURL . '?u=/'; // HTTP Get
			} else {
				$base = BLOG_BASEURL; // Pretty
			}
		}

		// Canonicalize bare index.php to mode-specific base (Pretty:/  PathInfo:/index.php/  GET:?u=/)
		$req = isset($_SERVER ['REQUEST_URI']) ? (string) $_SERVER ['REQUEST_URI'] : '';
		$path = $req !== '' ? (string) parse_url($req, PHP_URL_PATH) : '';
		$qry = $req !== '' ? (string) parse_url($req, PHP_URL_QUERY) : '';
		$idx = rtrim(BLOG_ROOT, '/').'/index.php';
		$method = isset($_SERVER ['REQUEST_METHOD']) ? (string) $_SERVER ['REQUEST_METHOD'] : 'GET';
		// Only if no further parameters follow index.php
		if ($method === 'GET' && $path === $idx && ($qry === '' || $qry === null)) {
			if (!headers_sent()) {
				define('PRETTYURLS_CANONICAL_REDIRECT_RAN', true);
				header('Location: ' . $base, true, 301);
				exit();
			}
		}

		$has_x = isset($_GET ['x']) && is_string($_GET ['x']);
		$has_entry = isset($_GET ['entry']) && is_string($_GET ['entry']);
		$has_page = isset($_GET ['page']) && is_string($_GET ['page']); // static page id
		$has_paged = isset($_GET ['paged']) && (is_string($_GET ['paged']) || is_numeric($_GET ['paged'])); // pagination
		$sum = ($has_x ? 1 : 0) + ($has_entry ? 1 : 0) + ($has_page ? 1 : 0) + ($has_paged ? 1 : 0);
		// Require exactly one of them
		if ($sum !== 1) {
			return;
		}
		// Ensure it is the only query parameter
		foreach (array_keys($_GET) as $k) {
			if (($has_x && $k !== 'x') || ($has_entry && $k !== 'entry') || ($has_page && $k !== 'page') || ($has_paged && $k !== 'paged')) {
				return;
			}
		}

		$target = null;

		// Canonicalize legacy feed queries (?x=feed:{rss2|atom}) to mode-specific URL
		if ($has_x && isset($_GET ['x']) && is_string($_GET ['x'])) {
			$x = $_GET['x'];
			if ($x === 'feed:rss2' || $x === 'feed:atom') {
				// Uniform construction: $base is already mode-specific
				$type = ($x === 'feed:rss2') ? 'rss2' : 'atom';
				$target = $base . 'feed/' . $type . '/';
				$current = utils_geturlstring();
				if ($current !== $target && !headers_sent()) {
					define('PRETTYURLS_CANONICAL_REDIRECT_RAN', true);
					header('Location: ' . $target, true, 301);
					exit();
				}
			}
		}

		if ($has_paged) {
			$pn = (int) $_GET ['paged'];
			if ($pn < 1) {
				return;
			}
			$target = $base . 'page/' . $pn . '/';
		} elseif ($has_page) {
			$id = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $_GET ['page']);
			if ($id === '') {
				return;
			}
			// Build via staticlink() to respect all modes
			$target = $plugin_prettyurls->staticlink('', $id);
		} else {
			// Entry cases (?entry= or ?x=entry:)
			if ($has_x) {
				$x = $_GET ['x'];
				if (strpos($x, ';') !== false) {
					return;
				}
				// Flags present (comments, feed, …)
				if (!preg_match('/^entry:(entry[0-9]{6}-[0-9]{6})$/', $x, $m)) {
					return;
				}
				$id = $m [1];
			} else {
				// ?entry=
				$id = (string) $_GET ['entry'];
				if (!preg_match('/^entry[0-9]{6}-[0-9]{6}$/', $id)) {
					return;
				}
			}
			// Build canonical permalink
			if (!function_exists('entry_parse') || !function_exists('date_from_id') || !function_exists('sanitize_title') || !function_exists('utils_geturlstring')) {
				return;
			}
			$entry = entry_parse($id);
			if (!is_array($entry) || empty($entry ['subject'])) {
				return;
			}
			$date = date_from_id($id);
			if (!isset($date ['y'], $date ['m'], $date ['d'])) {
				return;
			}
			$slug = sanitize_title($entry ['subject']);
			$target = $base . '20' . $date ['y'] . '/' . $date ['m'] . '/' . $date ['d'] . '/' . $slug . '/';
		}
		$current = utils_geturlstring();
		if ($target && $current !== $target && !headers_sent()) {
			define('PRETTYURLS_CANONICAL_REDIRECT_RAN', true);
			header('Location: ' . $target, true, 301);
			exit();
		}
	}

}

global $plugin_prettyurls;
$plugin_prettyurls = new Plugin_PrettyURLs();
$plugin_prettyurls->categories(false);

if (!defined('MOD_ADMIN_PANEL')) {

	if (!function_exists('get_nextpage_link')) :

		function get_nextpage_link() {
			global $plugin_prettyurls;
			return $plugin_prettyurls->nextprevlink('NextPage', 1);
		}

		function get_prevpage_link() {
			global $plugin_prettyurls;
			return $plugin_prettyurls->nextprevlink('PrevPage', -1);
		}

	endif;

}

add_filter('post_link', array(
	&$plugin_prettyurls,
	'permalink'
), 0, 2);
add_filter('comments_link', array(
	&$plugin_prettyurls,
	'commentlink'
), 0, 2);
add_filter('feed_link', array(
	&$plugin_prettyurls,
	'feedlink'
), 0, 2);
add_filter('post_comments_feed_link', array(
	&$plugin_prettyurls,
	'commentsfeedlink'
), 0, 3);
add_filter('category_link', array(
	&$plugin_prettyurls,
	'categorylink'
), 0, 2);
add_filter('page_link', array(
	&$plugin_prettyurls,
	'staticlink'
), 0, 2);

// date related functions
add_filter('year_link', array(
	&$plugin_prettyurls,
	'yearlink'
), 0, 2);
add_filter('month_link', array(
	&$plugin_prettyurls,
	'monthlink'
), 0, 3);
add_filter('day_link', array(
	&$plugin_prettyurls,
	'daylink'
), 0, 4);

if (PRETTYURLS_TITLES) {
	add_filter('publish_post', array(
		&$plugin_prettyurls,
		'cache_add'
	), 5, 2);
	add_filter('delete_post', array(
		&$plugin_prettyurls,
		'cache_delete'
	));
	add_action('update_categories', array(
		&$plugin_prettyurls,
		'categories'
	));
}

add_filter('init', array(
	&$plugin_prettyurls,
	'cache_init'
));

add_filter('init', array(
	&$plugin_prettyurls,
	'prettyurls_redirect_canonical'
), 11);

if (class_exists('AdminPanelAction')) {

	class admin_plugin_prettyurls extends AdminPanelAction {

		var $langres = 'plugin:prettyurls';

		var $_config = array(
			'mode' => 0
		);

		function assign_config_to_template() {
			global $plugin_prettyurls;

			$this->_config ['mode'] = plugin_getoptions('prettyurls', 'mode');
			$this->smarty->assign('pconfig', $this->_config);

			// Provide auto mode for index.php (preview) and icon URL to the template
			$auto_mode_index = 0;
			if (isset($plugin_prettyurls) && is_object($plugin_prettyurls)) {
				if (method_exists($plugin_prettyurls, 'auto_mode_detect_preview')) {
					// Preview: Automatic mode specifically for calling index.php outside the admin area
					$auto_mode_index = (int) $plugin_prettyurls->auto_mode_detect_preview();
				} elseif (method_exists($plugin_prettyurls, 'auto_mode_detect')) {
					// Auto mode detection
					$auto_mode_index = (int) $plugin_prettyurls->auto_mode_detect();
				}
			}
			$this->smarty->assign('auto_mode_index', (int) $auto_mode_index);

			// Assign capabilities outside the admin area to the template
			$can_pretty = false;
			$can_pathinfo = false;
			$can_get = true;
			if (isset($plugin_prettyurls) && is_object($plugin_prettyurls) && method_exists($plugin_prettyurls, 'modes_capabilities_preview')) {
				$caps = (array) $plugin_prettyurls->modes_capabilities_preview();
				$can_pretty = !empty($caps ['can_pretty']);
				$can_pathinfo = !empty($caps ['can_pathinfo']);
				$can_get = !empty($caps ['can_get']);
			} else {
				// Fallback: derive only from auto_mode_index
				$can_get = true;
				$can_pretty = ($auto_mode_index === 3);
				$can_pathinfo = ($auto_mode_index === 1);
			}
			$this->smarty->assign('can_pretty', (bool) $can_pretty);
			$this->smarty->assign('can_pathinfo', (bool) $can_pathinfo);
			$this->smarty->assign('can_get', (bool) $can_get);

			$this->smarty->assign('check_icon_url', BLOG_BASEURL . 'fp-plugins/prettyurls/res/check-green.svg');
			$blogroot = BLOG_ROOT;
			$f = ABS_PATH . '.htaccess';
			$txt = io_load_file($f);
			if (!$txt) {

				$txt = '
AddType application/x-httpd-php .php
Options -Indexes

<IfModule mod_headers.c>
	Header unset X-Powered-By
</IfModule>

<IfModule mod_rewrite.c>
	RewriteEngine On
	RewriteBase ' . $blogroot . '

	RewriteRule ^\.htaccess$ - [F]

	RewriteRule ^sitemap\.xml$ ' . $blogroot . 'sitemap.php [L]
	RewriteRule ^sitemap$ ' . $blogroot . 'sitemap.php [L]

	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteCond %{REQUEST_FILENAME} !-d

	RewriteRule . ' . $blogroot . 'index.php [L]
</IfModule>';
			}

			$this->smarty->assign('cantsave', (!is_writable(ABS_PATH) || (file_exists($f) && !is_writable($f))));
			$this->smarty->assign('htaccess', $txt);
		}

		function setup() {
			$this->smarty->assign('admin_resource', 'plugin:prettyurls/admin.plugin.prettyurls');
			$this->assign_config_to_template();
		}

		function main() {
			// Process the form once it has been submitted
			if ($_SERVER ['REQUEST_METHOD'] === 'POST') {
				// Use the onsubmit method to save the configuration
				$this->onsubmit();
			}
			// Render template
			$this->assign_config_to_template();
		}

		function onsubmit($data = null) {
			$bumpGen = false;

			// Settings: selected mode
			if (isset($_POST ['saveopt'])) {
				$mode = isset($_POST ['mode']) ? (int) $_POST ['mode'] : 0;
				if (!in_array($mode, array(0, 1, 2, 3), true)) {
					$mode = 0;
				} else {
					// Prevent explicit PATH_INFO mode on environments that clearly do not support it reliably
					if ($mode === 1) {
						$fix = @ini_get('cgi.fix_pathinfo');
						$noPathInfo = empty($_SERVER ['PATH_INFO']) && empty($_SERVER ['ORIG_PATH_INFO']);
						$sapi = PHP_SAPI;
						$isFastCgi = (strpos($sapi, 'cgi') !== false) || (strpos($sapi, 'fpm') !== false);
						if ($fix !== false && (string) $fix === '0' && $noPathInfo && !$isFastCgi) {
							$mode = 0;
							$this->smarty->assign('prettyurls_mode_forced_auto', true);
						}
					}
				}

				plugin_addoption('prettyurls', 'mode', $mode);
				$this->smarty->assign('success', 2);
				$bumpGen = true;
			}

			// .htaccess editor
			if (isset($_POST ['htaccess-submit'])) {
				if (!empty($_POST ['htaccess']) && io_write_file(ABS_PATH . '.htaccess', $_POST ['htaccess'])) {
					$this->smarty->assign('success', 1);
					// Only bump when write succeeded, because rewrite behavior may have changed
					$bumpGen = true;
				} else {
					$this->smarty->assign('success', -1);
				}
			}

			// Apply APCu generation bump once per request if something relevant changed
			if ($bumpGen) {
				$gen = (int) plugin_getoptions('prettyurls', 'apcu_gen');
				plugin_addoption('prettyurls', 'apcu_gen', ($gen < 1) ? 1 : ($gen + 1));
				plugin_saveoptions('prettyurls');
			}

			// Refill template after changes
			$this->assign_config_to_template();

			// No redirection to plugin overview or default action
			return PANEL_NOREDIRECT; // that is, 0
		}

	}

	admin_addpanelaction('plugin', 'prettyurls', true);
}
?>
