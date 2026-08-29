<?php
/**
 * Plugin Name: PrettyURLs
 * Version: 3.0.6
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
define('PRETTYURLS_CAPABILITY_PROBE', '__flatpress_prettyurls_probe__');

/**
 * Return whether PrettyURLs should use title-based URL slugs.
 *
 * The constant can be defined by local configuration before the plugin is
 * loaded, so use constant() instead of relying on the default value inferred
 * from this file.
 *
 * @return bool
 */
function plugin_prettyurls_titles_enabled() {
	return defined('PRETTYURLS_TITLES') && (bool)constant('PRETTYURLS_TITLES');
}

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

		if (isset($post) && plugin_prettyurls_titles_enabled()) {
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

	function lastcomments_feed_link($str, $type) {
		return $this->baseurl . "lastcomments/feed/" . $type . "/";
	}

	function lastcomments_feed_link_rss($str) {
		return $this->lastcomments_feed_link($str, 'rss2');
	}

	function lastcomments_feed_link_atom($str) {
		return $this->lastcomments_feed_link($str, 'atom');
	}

	function staticlink($str, $id) {
		if (!static_isvalid($id)) {
			return $str;
		}

		return $this->baseurl . $id . "/";
	}

	function categorylink($str, $catid) {
		if (plugin_prettyurls_titles_enabled()) {
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

		if (plugin_prettyurls_titles_enabled()) {
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
		if (!isset($matches [1]) || !static_isvalid($matches [1])) {
			return isset($matches [0]) ? $matches [0] : '';
		}

		$this->fp_params ['page'] = $matches [1];
		$this->status = 2;
		return '';
	}

	function handle_entry($matches) {
		// the cache contains (md5'ed) sanitized entry names, so we have to sanitize before handling it
		$sanitizedtitle = sanitize_title($matches [1]);

		if (!plugin_prettyurls_titles_enabled()) {
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

	function handle_feed_lastcomments($matches) {
		$type = isset($matches [2]) ? $matches [2] : 'rss2';
		if ($type !== 'rss2' && $type !== 'atom') {
			$type = 'rss2';
		}
		// Map to lastcomments feed names expected by plugin_lastcomments
		$this->fp_params ['feed'] = 'lastcomments-' . $type;
		$_GET ['feed'] = 'lastcomments-' . $type;
	}

	/**
	 * Return true only for positive rewrite signals that are specific enough to
	 * prove FlatPress front-controller routing. REDIRECT_URL alone is deliberately
	 * ignored because shared hosts may set it for unrelated platform rewrites.
	 *
	 * @return bool
	 */
	private function server_rewrite_active() {
		$req = isset($_SERVER ['REQUEST_URI']) ? (string) $_SERVER ['REQUEST_URI'] : '';
		$sn = isset($_SERVER ['SCRIPT_NAME']) ? (string) $_SERVER ['SCRIPT_NAME'] : '';

		// Marker set by the FlatPress-generated Apache configuration when mod_rewrite is active.
		if ((!empty($_SERVER ['FLATPRESS_PRETTYURLS']) && $_SERVER ['FLATPRESS_PRETTYURLS'] === '1')
			|| (!empty($_SERVER ['REDIRECT_FLATPRESS_PRETTYURLS']) && $_SERVER ['REDIRECT_FLATPRESS_PRETTYURLS'] === '1')) {
			return true;
		}

		// IIS URL Rewrite / ISAPI_Rewrite.
		if (!empty($_SERVER ['IIS_WasUrlRewritten']) && $_SERVER ['IIS_WasUrlRewritten'] == '1') {
			return true;
		}
		if (!empty($_SERVER ['HTTP_X_REWRITE_URL'])) {
			$isIIS = !empty($_SERVER ['SERVER_SOFTWARE']) && stripos((string) $_SERVER ['SERVER_SOFTWARE'], 'IIS') !== false;
			if ($isIIS && substr($sn, -9) === 'index.php') {
				return true;
			}
		}

		/**
		 * Generic live proof: a routed path without index.php reached index.php.
		 * DirectoryIndex alone cannot satisfy request_has_route_path().
		 */
		if ($req !== '' && $sn !== '' && strpos($req, 'index.php') === false && substr($sn, -9) === 'index.php' && $this->request_has_route_path()) {
			return true;
		}

		return false;
	}

	private function request_has_route_path() {
		$req = isset($_SERVER ['REQUEST_URI']) ? (string) $_SERVER ['REQUEST_URI'] : '';
		if ($req === '') {
			return false;
		}
		$reqPath = (string) parse_url($req, PHP_URL_PATH);
		if ($reqPath === '') {
			$reqPath = $req;
		}
		$base = defined('BLOG_ROOT') ? (string) BLOG_ROOT : '';
		$base = rtrim($base, '/');
		$reqNorm = rtrim($reqPath, '/');
		if ($reqNorm === '' || $reqNorm === $base) {
			return false;
		}
		if ($base !== '' && strpos($reqNorm . '/', $base . '/') !== 0) {
			return false;
		}
		return true;
	}

	private function server_can_pathinfo() {
		if (!empty($_SERVER ['PATH_INFO']) || !empty($_SERVER ['ORIG_PATH_INFO'])) {
			return true;
		}
		$fix = @ini_get('cgi.fix_pathinfo');
		if ($fix !== false && (string) $fix === '0') {
			return false;
		}
		return false;
	}

	private function server_pathinfo_selectable() {
		if ($this->server_can_pathinfo()) {
			return true;
		}
		$fix = @ini_get('cgi.fix_pathinfo');
		$sapi = PHP_SAPI;
		$isFastCgi = strpos($sapi, 'cgi') !== false || strpos($sapi, 'fpm') !== false;
		return !($fix !== false && (string) $fix === '0' && !$isFastCgi);
	}

	/**
	 * Preview of mode availability and positively detected server capabilities.
	 * The can_* values control whether a mode may be selected. The detected_*
	 * values are stricter and are used only for the green capability indicators
	 * in the admin UI. Pretty remains manually selectable even when rewrite
	 * support cannot be proven automatically (important for NGINX).
	 *
	 * HTTP Get is also verified by an authenticated same-origin browser probe so
	 * every green check has the same meaning: the tested URL form actually
	 * reaches FlatPress on this web host.
	 *
	 * @return array{can_pretty:bool,can_pathinfo:bool,can_get:bool,detected_pretty:bool,detected_pathinfo:bool,detected_get:bool}
	 */
	public function modes_capabilities_preview() {
		return array(
			'can_pretty' => true,
			'can_pathinfo' => $this->server_pathinfo_selectable(),
			'can_get' => true,
			/**
			 * Only positive request-time evidence is shown immediately. The admin
			 * page performs same-origin browser probes to verify capabilities that
			 * cannot be proven from the current admin.php request alone.
			 */
			'detected_pretty' => $this->server_rewrite_active(),
			'detected_pathinfo' => $this->server_can_pathinfo(),
			/**
			 * The normal admin request does not prove that the ?u=/... URL form
			 * survives the web-server stack. The browser probe verifies it.
			 */
			'detected_get' => false,
		);
	}

	/**
	 * Verify that a browser capability probe reached FlatPress through the mode
	 * it is testing. This deliberately relies on the actual web-server request
	 * instead of inferring support from .htaccess existence, REDIRECT_URL, SAPI,
	 * or cgi.fix_pathinfo alone.
	 *
	 * @param string $mode pathinfo|get|pretty
	 * @return bool
	 */
	public function capability_probe_matches($mode) {
		$mode = strtolower((string)$mode);
		$scriptName = (string)($_SERVER ['SCRIPT_NAME'] ?? '');
		if (substr($scriptName, -9) !== 'index.php') {
			return false;
		}

		$probeSegment = '/' . PRETTYURLS_CAPABILITY_PROBE . '/';
		if ($mode === 'pathinfo') {
			$pathInfos = array(
				(string)($_SERVER ['PATH_INFO'] ?? ''),
				(string)($_SERVER ['ORIG_PATH_INFO'] ?? '')
			);
			foreach ($pathInfos as $pathInfo) {
				if ($pathInfo === '') {
					continue;
				}
				$normalized = '/' . trim(str_replace('\\', '/', $pathInfo), '/') . '/';
				if ($normalized === $probeSegment || substr($normalized, -strlen('/index.php' . $probeSegment)) === '/index.php' . $probeSegment) {
					return true;
				}
			}
			return false;
		}

		if ($mode === 'get') {
			$requestUri = (string)($_SERVER ['REQUEST_URI'] ?? '');
			$requestPath = (string)parse_url($requestUri, PHP_URL_PATH);
			$root = rtrim((string)BLOG_ROOT, '/');
			$expectedPath = ($root === '' ? '' : $root) . '/';
			$u = isset($_GET ['u']) ? '/' . trim(str_replace('\\', '/', (string)$_GET ['u']), '/') . '/' : '';
			return $requestPath === $expectedPath && $u === $probeSegment;
		}

		if ($mode === 'pretty') {
			$requestUri = (string)($_SERVER ['REQUEST_URI'] ?? '');
			$requestPath = (string)parse_url($requestUri, PHP_URL_PATH);
			$root = rtrim((string)BLOG_ROOT, '/');
			$expected = $root . $probeSegment;
			return $requestPath === $expected;
		}

		return false;
	}

	/**
	 * Serve the authenticated same-origin capability probe used by the plugin
	 * admin page. It is read-only and intentionally returns no page content.
	 *
	 * @return void
	 */
	public function serve_capability_probe() {
		$mode = isset($_GET ['prettyurls_probe']) ? strtolower((string)$_GET ['prettyurls_probe']) : '';
		if ($mode !== 'pathinfo' && $mode !== 'get' && $mode !== 'pretty') {
			return;
		}

		$loggedIn = function_exists('user_loggedin') && user_loggedin();
		$matches = $loggedIn && $this->capability_probe_matches($mode);
		if (!headers_sent()) {
			header('Cache-Control: no-store, no-cache, must-revalidate');
			header('Pragma: no-cache');
			header('Content-Type: text/plain; charset=UTF-8');
			if ($matches) {
				http_response_code(200);
			} else {
				http_response_code(404);
			}
		}
		if ($matches) {
			echo 'flatpress-prettyurls-probe:' . $mode;
		}
		exit;
	}

	/**
	 * Automatic mode detector.
	 * Returns: 3=Pretty, 1=PATH_INFO, 2=GET.
	 */
	function auto_mode_detect() {
		$sn = isset($_SERVER ['SCRIPT_NAME']) ? (string) $_SERVER ['SCRIPT_NAME'] : '';
		$sw = isset($_SERVER ['SERVER_SOFTWARE']) ? (string) $_SERVER ['SERVER_SOFTWARE'] : '';
		$flags = implode('|', array(
			(!empty($_SERVER ['FLATPRESS_PRETTYURLS']) && $_SERVER ['FLATPRESS_PRETTYURLS'] === '1') ? 'fp1' : 'fp0',
			(!empty($_SERVER ['REDIRECT_FLATPRESS_PRETTYURLS']) && $_SERVER ['REDIRECT_FLATPRESS_PRETTYURLS'] === '1') ? 'rfp1' : 'rfp0',
			(!empty($_SERVER ['IIS_WasUrlRewritten']) && $_SERVER ['IIS_WasUrlRewritten'] == '1') ? 'iis1' : 'iis0',
			!empty($_SERVER ['HTTP_X_REWRITE_URL']) ? 'xrw1' : 'xrw0',
			!empty($_SERVER ['REDIRECT_URL']) ? 'redir1' : 'redir0',
			!empty($_SERVER ['PATH_INFO']) ? 'pi1' : 'pi0',
			!empty($_SERVER ['ORIG_PATH_INFO']) ? 'opi1' : 'opi0',
			(!empty($_SERVER ['PHP_SELF']) && strpos((string) $_SERVER ['PHP_SELF'], 'index.php/') !== false) ? 'ps1' : 'ps0',
			((isset($_SERVER ['REQUEST_URI']) && strpos((string) $_SERVER ['REQUEST_URI'], 'index.php/') !== false)) ? 'ru1' : 'ru0',
			isset($_SERVER ['REQUEST_URI']) ? (string)$_SERVER ['REQUEST_URI'] : '',
			$sn,
			$sw
		));
		$gen = (int) plugin_getoptions('prettyurls', 'apcu_gen');
		if ($gen < 1) {
			$gen = 1;
		}
		$key = 'prettyurls:auto:v4:g' . $gen . ':' . md5($flags);
		static $reqCache = array();
		if (isset($reqCache [$key])) {
			return (int) $reqCache [$key];
		}
		if (function_exists('is_apcu_on') && is_apcu_on() && function_exists('apcu_get')) {
			$ok = false;
			$val = apcu_get('prettyurls:' . $key, $ok);
			if ($ok) {
				$reqCache [$key] = (int) $val;
				return (int) $val;
			}
		}

		$mode = $this->server_rewrite_active() ? 3 : ($this->server_can_pathinfo() ? 1 : 2);
		$reqCache [$key] = (int) $mode;
		if (function_exists('is_apcu_on') && is_apcu_on() && function_exists('apcu_set')) {
			apcu_set('prettyurls:' . $key, (int) $mode, 120);
		}
		return (int) $mode;
	}

	/**
	 * The admin/non-index preview intentionally uses the same detector as the
	 * frontend. One implementation prevents the UI and generated links from
	 * disagreeing about Auto mode.
	 *
	 * @return int
	 */
	function auto_mode_detect_preview() {
		return (int) $this->auto_mode_detect();
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

		/**
		 * For non-index requests, e.g. contact.php or search.php, the preview uses the exact same conservative
		 * detector as the frontend so generated links cannot disagree with Auto mode.
		 * Explicitly configured modes (1/2/3) are always retained unchanged.
		 */
		if ($opt === null || $opt === 0) {
			$isIndexRequest = defined('MOD_INDEX');
			$scriptName = isset($_SERVER ['SCRIPT_NAME']) ? (string) $_SERVER ['SCRIPT_NAME'] : '';
			if (!$isIndexRequest || substr($scriptName, -9) !== 'index.php') {
				$opt = (int) $this->auto_mode_detect_preview();
			} else {
				$opt = (int) $this->auto_mode_detect();
			}
		} else {
			// Explicit modes are authoritative. Pretty must remain usable on NGINX and other servers without .htaccess.
			$opt = (int) $opt;
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

		if (plugin_prettyurls_titles_enabled()) {
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
			/**
			 * utils_error(404);
			 */

			$url = preg_replace_callback('|^/comments|', array(
				&$this,
				'handle_comment'
			), $url);
		}

		$url = preg_replace_callback('|^/lastcomments/feed(/([^/]*))?|', array(
			&$this,
			'handle_feed_lastcomments'
		), $url);

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
			if (plugin_prettyurls_titles_enabled()) {
				$title = sanitize_title($caption);
			} else {
				$title = $id;
			}
			$url = $this->baseurl . "20" . $date ['y'] . "/" . $date ['m'] . "/" . $date ['d'] . "/" . $title . "/";

			if (!empty($this->fp_params ['comments'])) {
				$url .= "comments/";
			}

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
		/**
		 * === Cross-mode canonicalization for common routes ===
		 * Routes: page/N, paged/N, category/NAME, tag/NAME, archive[s]/YYYY(/MM)?, static/SLUG, entry/SLUG
		 * Redirect only if there are no extra query params (besides 'u' in GET style).
		 */
		$plugin_prettyurls = isset($GLOBALS ['plugin_prettyurls']) ? $GLOBALS ['plugin_prettyurls'] : null;
		$opt = 0;
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
					// LastComments feeds
					'!^/lastcomments/feed/(?:rss2|atom)/?$!i',
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
				if (is_string($qry) && $qry !== '') {
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
		if (!isset($plugin_prettyurls->baseurl) && $this->get_mode() == 0) {
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
		$x = null;

		// Canonicalize legacy feed queries (?x=feed:{rss2|atom}) to mode-specific URL
		if ($has_x && isset($_GET ['x']) && is_string($_GET ['x'])) {
			$x = (string) $_GET ['x'];
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
			} elseif (strpos($x, 'cat:') === 0 && isset($plugin_prettyurls) && method_exists($plugin_prettyurls, 'categorylink')) {
				// Legacy category queries (?x=cat:{id}) to mode-specific category URL
				$cid_raw = substr($x, 4);
				if ($cid_raw !== '' && ctype_digit($cid_raw)) {
					$cid = (int) $cid_raw;
					if ($cid > 0) {
						// Uses categorylink() to correctly handle Pretty/PathInfo/HTTP-GET
						$target = $plugin_prettyurls->categorylink('', $cid);
					}
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
			$rawId = (string) $_GET ['page'];
			if (!static_isvalid($rawId)) {
				return;
			}

			$id = preg_replace('/[^A-Za-z0-9_-]/', '', $rawId);
			if ($id === '' || !static_isvalid($id)) {
				return;
			}
			// Build via staticlink() to respect all modes
			$target = $plugin_prettyurls->staticlink('', $id);
		} else {
			// Entry cases (?entry= or ?x=entry:)
			if ($has_entry || ($has_x && is_string($x) && strpos($x, 'entry:') === 0)) {
				if ($has_x && is_string($x) && strpos($x, 'entry:') === 0) {
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
$plugin_prettyurls->serve_capability_probe();
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
add_filter('plugin_lastcomments_rss_link', array(
	&$plugin_prettyurls,
	'lastcomments_feed_link_rss'
), 0, 1);
add_filter('plugin_lastcomments_atom_link', array(
	&$plugin_prettyurls,
	'lastcomments_feed_link_atom'
), 0, 1);
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

if (plugin_prettyurls_titles_enabled()) {
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

			/**
			 * Assign selectable modes and positively detected server capabilities.
			 * Green checks must describe host capabilities, not the mode chosen by Auto.
			 */
			$can_pretty = true;
			$can_pathinfo = true;
			$can_get = true;
			$detected_pretty = ($auto_mode_index === 3);
			$detected_pathinfo = ($auto_mode_index === 1);
			$detected_get = false;
			if (isset($plugin_prettyurls) && is_object($plugin_prettyurls) && method_exists($plugin_prettyurls, 'modes_capabilities_preview')) {
				$caps = (array) $plugin_prettyurls->modes_capabilities_preview();
				$can_pretty = !empty($caps ['can_pretty']);
				$can_pathinfo = !empty($caps ['can_pathinfo']);
				$can_get = !empty($caps ['can_get']);
				$detected_pretty = !empty($caps ['detected_pretty']);
				$detected_pathinfo = !empty($caps ['detected_pathinfo']);
				$detected_get = !empty($caps ['detected_get']);
			}
			$this->smarty->assign('can_pretty', (bool) $can_pretty);
			$this->smarty->assign('can_pathinfo', (bool) $can_pathinfo);
			$this->smarty->assign('can_get', (bool) $can_get);
			$this->smarty->assign('detected_pretty', (bool) $detected_pretty);
			$this->smarty->assign('detected_pathinfo', (bool) $detected_pathinfo);
			$this->smarty->assign('detected_get', (bool) $detected_get);

			$random_hex = RANDOM_HEX;
			$capability_probe_script_url = BLOG_BASEURL . 'fp-plugins/prettyurls/res/capability-probe.js';
			$capability_probe_script_url = utils_asset_ver($capability_probe_script_url, SYSTEM_VER);
			$this->smarty->assign('random_hex', $random_hex);
			$this->smarty->assign('check_icon_url', BLOG_BASEURL . 'fp-plugins/prettyurls/res/check-green.svg');
			$this->smarty->assign('capability_probe_pathinfo_url', BLOG_BASEURL . 'index.php/' . PRETTYURLS_CAPABILITY_PROBE . '/?prettyurls_probe=pathinfo');
			$this->smarty->assign('capability_probe_get_url', BLOG_BASEURL . '?u=/' . PRETTYURLS_CAPABILITY_PROBE . '/&prettyurls_probe=get');
			$this->smarty->assign('capability_probe_pretty_url', BLOG_BASEURL . PRETTYURLS_CAPABILITY_PROBE . '/?prettyurls_probe=pretty');
			$this->smarty->assign('capability_probe_script_url', $capability_probe_script_url);

			$blogroot = BLOG_ROOT;
			$f = ABS_PATH . '.htaccess';
			$txt = io_load_file($f);
			if (!$txt) {

				$txt = '
# BEGIN FlatPress PrettyURLs
AddType application/x-httpd-php .php
Options -Indexes

<IfModule mod_headers.c>
	Header unset X-Powered-By
</IfModule>

<IfModule mod_rewrite.c>
	RewriteEngine On
	RewriteBase ' . $blogroot . '
	RewriteRule ^ - [E=FLATPRESS_PRETTYURLS:1]

	RewriteRule ^\.htaccess$ - [F]

	RewriteRule ^\.setup\.php$ - [F,L]
	RewriteRule ^\.setup/ - [F,L]

	RewriteRule ^sitemap\.xml$ ' . $blogroot . 'sitemap.php [L]
	RewriteRule ^sitemap$ ' . $blogroot . 'sitemap.php [L]

	RewriteRule ^index\.php/' . PRETTYURLS_CAPABILITY_PROBE . '/$ - [L]

	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteCond %{REQUEST_FILENAME} !-d

	RewriteRule . ' . $blogroot . 'index.php [L]
</IfModule>
# END FlatPress PrettyURLs';
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

				// Invalidate APCu-backed caches that embed absolute URLs
				if (function_exists('plugin_archives_cache_bump')) {
					plugin_archives_cache_bump();
				}
				if (function_exists('plugin_calendar_cache_bump')) {
					plugin_calendar_cache_bump();
				}
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
