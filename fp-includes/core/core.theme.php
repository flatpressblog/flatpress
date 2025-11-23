<?php
/**
 * Loads and initializes theme settings.
 *
 * @global array $fp_config
 * @global array $theme
 * @global string $FLATPRESS
 * @return array{
 *   name: string,
 *   author: string,
 *   www: string,
 *   version: float|int,
 *   default_style: null|string,
 *   style: array{style_def: string, style_admin: string},
 *   admin_custom_interf: bool
 * }
 */
function theme_loadsettings() {
	global $fp_config, $theme, $FLATPRESS;

	$theme = array(
		// name of the theme
		'name' => 'theme',
		// author of the theme
		'author' => 'anonymous',
		// theme website
		'www' => 'https://www.flatpress.org',
		// fp version
		'version' => -1,
		// default style (must be in res/ dir
		'default_style' => null,
		'style' => array(

			'style_def' => 'style.css',
			// default style for admin panel (usually it's the same of the theme)
			'style_admin' => 'style.css'
		),

		// if false a default css is used to style some elements of the panel
		// if true, we'll suppose these elements are already styled in your own css's
		'admin_custom_interf' => false
	);

	if (!defined('THE_THEME')) {
		define('THE_THEME', $fp_config ['general'] ['theme']);
	}

	// backward compatibility:
	$conf1 = THEMES_DIR . THE_THEME . '/theme_conf.php';

	// new naming convention. Yeah, I know, just an underscore
	// instead of the dot, so? It is more "consistent" :D
	$conf2 = THEMES_DIR . THE_THEME . '/theme.conf.php';

	ob_start();

	if (file_exists($conf2)) {
		include ($conf2);
	} elseif (file_exists($conf1)) {
		include ($conf1);
	}

	if (!defined('THEME_LEGACY_MODE')) {
		/** @phpstan-ignore-next-line */
		if ($theme ['version'] < 0.702) {
			define('THEME_LEGACY_MODE', true);
			theme_register_default_widgetsets();
		} else {
			define('THEME_LEGACY_MODE', false);

			if ($theme ['default_style']) {

				if (!isset($fp_config ['general'] ['style'])) {
					$fp_config ['general'] ['style'] = $theme ['default_style'];
				}

				include(THEMES_DIR . THE_THEME . "/" . $fp_config ['general'] ['style'] . "/style.conf.php");

				$theme ['style'] = $style;
			} else {

				$theme ['style'] = array(

					'style_def' => $theme ['style_def'] ? $theme ['style_def'] : 'style.css',
					'style_admin' => $theme ['style_admin'] ? $theme ['style_admin'] : 'style.css'
				);
			}
		}

		// no widgets registered, load default set
		if (!get_registered_widgets()) {
			theme_register_default_widgetsets();
		}
	}

	ob_end_clean();

	return $theme;
}

/**
 * Register the default widgetset slots used by themes.
 * @return void
 */
function theme_register_default_widgetsets() {
	register_widgetset('left');
	register_widgetset('right');
	register_widgetset('top');
	register_widgetset('bottom');
}

/**
 * Return the theme directory path with trailing slash or '' if not found.
 * @param string $id Theme identifier.
 * @return string Filesystem path (base-relative) or empty string.
 */
function theme_getdir($id = THE_THEME) {
	return theme_exists($id);
}

/**
 * Check if a theme exists and return its directory with trailing slash.
 * @param string $id Theme identifier.
 * @return string Path when found, '' otherwise.
 */
function theme_exists($id) {
	// quick fix for win
	static $cache = array();
	if (isset($cache [$id])) {
		return $cache [$id];
	}
	$f = THEMES_DIR . ($id);
	if (file_exists($f)) {
		return $cache [$id] = $f . '/';
	}
	return $cache [$id] = '';
}

/**
 * Returns the absolute theme style directory with trailing slash if it exists, otherwise ''.
 * @param string $id Style identifier (subdirectory name).
 * @param string $themeid Theme identifier (default THE_THEME).
 * @return string Absolute style path or empty string when not found.
 */
function theme_style_exists($id, $themeid = THE_THEME) {
	static $cache = array();
	$key = (string)$themeid . '|' . (string)$id;
	if (isset($cache [$key])) {
		return $cache [$key];
	}
	$base = theme_exists($themeid);
	if (!$base) {
		return $cache [$key] = '';
	}
	// Normalize and build style directory path
	$dir = rtrim($base, "/\\") . '/' . ltrim((string)$id, "/\\");
	if (is_dir($dir)) {
		// Prefer directories that actually contain style markers
		if (file_exists($dir . '/style.conf.php') || file_exists($dir . '/res/style.css') || file_exists($dir . '/style.css')) {
			return $cache [$key] = $dir . '/';
		}
		// Still accept the directory if markers are missing
		return $cache [$key] = $dir . '/';
	}
	return $cache [$key] = '';
}

/**
 * Build the public URL to the theme root with trailing slash.
 * @param string $id Theme identifier.
 * @return string Absolute URL.
 */
function theme_geturl($id = THE_THEME) {
	static $cache = array();
	if (isset($cache [$id])) {
		return $cache [$id];
	}
	return $cache [$id] = BLOG_BASEURL . THEMES_DIR . $id . '/';
}

/**
 * Build the public URL to a style subdirectory within a theme.
 * @param string $style Style identifier (subdirectory).
 * @param string $id Theme identifier.
 * @return string Absolute URL ending with '/'.
 */
function theme_style_geturl($style, $id = THE_THEME) {
	static $cache = array();
	$key = $id . '|' . $style;
	if (isset($cache [$key])) {
		return $cache [$key];
	}
	return $cache [$key] = theme_geturl($id) . $style . '/';
}

/**
 * List available themes by scanning the themes directory.
 * @return array<int,string> Sorted list of theme IDs.
 */
function theme_list() {
	static $cache = null;
	if (is_array($cache)) {
		return $cache;
	}
	$dir = THEMES_DIR;
	$dh = @opendir($dir);
	$files = array();
	if ($dh) {
		while (false !== ($filename = readdir($dh))) {
			if (!fs_is_directorycomponent($filename)) {
				$files [] = $filename;
			}
		}
		closedir($dh);
	}
	sort($files);
	return $cache = $files;
}

/**
 * Output standard <head> meta and feed links for the front end.
 * @return void
 */
function theme_wp_head() {
	global $fp_config, $lang;

	echo '
		<!-- FP STD HEADER -->
		<meta name="generator" content="FlatPress ' . system_ver() . '">
		<link rel="alternate" type="application/rss+xml" title="' . $lang ['main'] ['entries'] . ' | RSS 2.0" href="' . theme_feed_link('rss2') . '">
		<link rel="alternate" type="application/atom+xml" title="' . $lang ['main'] ['entries'] . ' | Atom 1.0" href="' . theme_feed_link('atom') . '">
		<!-- EOF FP STD HEADER -->
	';
}

/**
 * Output the theme stylesheet <link> tag(s) for the active theme.
 * @return void
 */
function theme_head_stylesheet() {
	global $fp_config, $theme;

	$charset = strtoupper($fp_config ['locale'] ['charset'] ?? 'UTF-8');

	echo '
		<!-- FP STD STYLESHEET -->';

	$css = defined('MOD_ADMIN_PANEL') ? $theme ['style'] ['style_admin'] : $theme ['style'] ['style_def'];
	$substyle = '/' . (isset($fp_config ['general'] ['style']) ? $fp_config ['general'] ['style'] . '/' : '');
	$base = BLOG_BASEURL . THEMES_DIR . THE_THEME . $substyle . 'res/';

	$raw_screen = $base . $css;
	if (function_exists('utils_asset_ver')) {
		$explicit = isset($theme ['version']) ? (string)$theme ['version'] : null;
		$href_screen = utils_asset_ver($raw_screen, $explicit);
	} else {
		$href_screen = $raw_screen . ((strpos($raw_screen, '?') === false) ? '?' : '&') . 'v=' . (defined('SYSTEM_VER') ? rawurlencode(SYSTEM_VER) : time());
	}
	echo '
		<link media="screen" href="' . htmlspecialchars($href_screen, ENT_QUOTES, $charset) . '" type="text/css" rel="stylesheet">';

	if (!empty(@$theme ['style'] ['style_print'])) {
		$raw_print = $base . @$theme ['style'] ['style_print'];
		if (function_exists('utils_asset_ver')) {
			$href_print = utils_asset_ver($raw_print);
		} else {
			$href_print = $raw_print . ((strpos($raw_print, '?') === false) ? '?' : '&') . 'v=' . (defined('SYSTEM_VER') ? rawurlencode(SYSTEM_VER) : time());
		}
		echo '<link media="print" href="' . htmlspecialchars($href_print, ENT_QUOTES, $charset) . '" type="text/css" rel="stylesheet">';
	}

	echo '
		<!-- FP STD STYLESHEET -->
	';
}

/**
 * Output admin panel CSS <link> tag into the head section.
 * @return void
 */
function admin_head_action() {
	global $fp_config, $theme;

	$charset = strtoupper($fp_config ['locale'] ['charset'] ?? 'UTF-8');
	$raw = BLOG_BASEURL . 'admin/res/admin.css';
	if (function_exists('utils_asset_ver')) {
		$href = utils_asset_ver($raw);
	} else {
		$href = $raw . ((strpos($raw, '?') === false) ? '?' : '&') . 'v=' . (defined('SYSTEM_VER') ? rawurlencode(SYSTEM_VER) : time());
	}
	echo '<link media="screen" href="' . htmlspecialchars($href, ENT_QUOTES, $charset) . '" type="text/css" rel="stylesheet">';
}

add_filter('admin_head', 'admin_head_action');

add_action('wp_head', 'theme_wp_head');
add_action('wp_head', 'theme_head_stylesheet');

/**
 * Fire WordPress-compatible 'wp_head' and, when in admin, 'admin_head' hooks.
 * @return void
 */
function get_wp_head() {
	do_action('wp_head');
	if (class_exists('AdminPanel')) {
		do_action('admin_head');
	}
}

$smarty->registerPlugin('function', 'header', 'get_wp_head');

/**
 * Output the configured footer HTML.
 * @return void
 */
function theme_wp_footer() {
	global $fp_config;
	echo $fp_config ['general'] ['footer'];
}

add_action('wp_footer', 'theme_wp_footer');

/**
 * Fire WordPress-compatible 'wp_footer' hook.
 * @return void
 */
function get_wp_footer() {
	do_action('wp_footer');
}

$smarty->registerPlugin('function', 'footer', 'get_wp_footer');

/**
 * Send the Content-Type header with the configured charset.
 * @return void
 */
function theme_charset() {
	global $fp_config;
	$charset = strtoupper($fp_config ['locale'] ['charset']);
	header('Content-Type: text/html; charset=' . $charset);
}

add_action('init', 'theme_charset');

/**
 * Initializes the theme.
 *
 * @param Smarty $smarty Smarty template engine instance
 * @param object|null $layout The layout instance (optional)
 * @return void
 */
function theme_init(&$smarty, $layout = null) { /* &$mode */
	global $fp_config, $lang, $theme, $fp_params;

	// avoid compiled tpl collision (i.e. change theme without this and cry)
	$smarty->setCompileId(md5($fp_config ['general'] ['theme']));
	$smarty->setTemplateDir([ABS_PATH . THEMES_DIR . $fp_config ['general'] ['theme'] . '/']);

	$loggedin = user_loggedin();

	$flatpress = $fp_config ['general'];
	// retained for compatibility
	// todo: ugly, clean this up
	// smarty has constant facilities included ^_^
	// $flatpress['FP_INTERFACE'] = FP_INTERFACE;
	// $flatpress['BLOGURL'] = BLOG_BASEURL;

	$flatpress ['loggedin'] = $loggedin;

	if ($loggedin) {
		$flatpress ['user'] = user_get();
	}

	// useful shorthand for themes
	// e.g. {$flatpress.themeurl}imgs/myimage.png

	if (isset($fp_config ['general'] ['style'])) {
		$themeurl = theme_style_geturl($fp_config ['general'] ['style']);
	} else {
		$themeurl = theme_geturl();
	}

	$flatpress ['themeurl'] = $themeurl;

	$flatpress ['params'] = $fp_params;

	$flatpress_upper = array_change_key_case($flatpress, CASE_UPPER);

	$flatpress = array_merge($flatpress, $flatpress_upper);

	$smarty->assign('flatpress', $flatpress);

	$smarty->assign('lang', $lang);

	$smarty->assign('blogtitle', $fp_config ['general'] ['title']);

	$smarty->assign('pagetitle', apply_filters('wp_title', "", '&laquo;'));

	$smarty->assign('fp_config', $fp_config);

	$smarty->registerPlugin('modifier', 'tag', 'theme_apply_filters_wrapper');
	$smarty->registerPlugin('modifier', 'link', 'theme_apply_filters_link_wrapper');
	$smarty->registerPlugin('modifier', 'filed', 'theme_entry_categories');

	if (!isset($fp_params ['feed']) || empty($fp_params ['feed'])) {
		$smarty->registerPlugin('modifier', 'date_format_daily', 'theme_smarty_modifier_date_format_daily');
		$smarty->registerPlugin('modifier', 'date_format', 'theme_date_format');
	}

	$smarty->registerPlugin('modifier', 'date_rfc3339', 'theme_smarty_modifier_date_rfc3339');

	$smarty->registerPlugin('function', 'action', 'theme_smarty_function_action');

	if (!isset($smarty->registered_plugins['modifier']['fix_encoding_issues'])) {
		// This modifier converts characters such as Ã¤ to ä or &#8220; to “. See core.language.php
		$smarty->registerPlugin('modifier', 'fix_encoding_issues', 'fix_encoding_issues');
	}

	do_action('theme_init');
}

/**
 * Smarty block plugin: passthrough for the page container.
 * @param array<string,mixed> $params Block parameters.
 * @param string|null $content Captured block content or null on open.
 * @return string|null Content to output or null on open.
 */
function smarty_block_page($params, $content) {
	return $content;
}

$smarty->registerPlugin('block', 'page', 'smarty_block_page');

/**
 * Apply a filter swapping ($hook, $var, ...rest) for Smarty modifier usage.
 * @param mixed $var Value to filter.
 * @param string $hook Filter hook name.
 * @return mixed Filtered value.
 */
function theme_apply_filters_wrapper($var, $hook) {
	$args = func_get_args();
	$tmp = $args [0];
	$args [0] = $args [1];
	$args [1] = $tmp;
	return call_user_func_array('apply_filters', $args);
}

/**
 * Apply a link filter with reordered arguments for template use.
 * Moves the id to the end and injects an empty string for compatibility.
 * @param mixed $var First argument (usually the id).
 * @param string $hook Filter hook name.
 * @return mixed Filter result.
 */
function theme_apply_filters_link_wrapper($var, $hook) {
	// MODIFIER: id, type, feed
	// FILTER: type, oldlink, feed, id
	$args = func_get_args();

	// delete id
	$id = $args [0];
	unset($args [0]);
	// put it at the end
	$args [] = $id;

	// insert empty string between type and feed
	array_splice($args, 1, 0, '');
	return call_user_func_array('apply_filters', $args);
}

/**
 * Smarty function: trigger a FlatPress-style action by name.
 * @param array<string,mixed> $params Must contain 'hook'.
 * @param \Smarty_Internal_Template $smarty Template instance.
 * @return void
 */
function theme_smarty_function_action($params, $smarty) {
	if (isset($params ['hook'])) {
		do_action($params ['hook']);
	}
}

/**
 * Format a date/time using the configured locale defaults.
 * Accepts timestamp or parseable string; falls back to now.
 * @param string|int|null $string Timestamp or strtotime()-parseable string.
 * @param string|null $format Optional date format string.
 * @param string|int $default_date Fallback when $string is empty.
 * @return string Formatted date/time.
 */
function theme_date_format($string, $format = null, $default_date = '') {
	$timestamp = null;

	if ($string) {
		// Conversion to timestamp, if string
		$timestamp = is_numeric($string) ? $string : strtotime($string); // smarty_make_timestamp($string);
	} elseif ($default_date != '') {
		$timestamp = is_numeric($default_date) ? $default_date : strtotime($default_date); // smarty_make_timestamp($default_date);
	}

	// If no valid timestamp is available, the current time is used
	if ($timestamp === null || $timestamp === false) {
		$timestamp = date_time();
	}

	// Use default format if no format is specified
	if (is_null($format)) {
		global $fp_config;
		$format = $fp_config ['locale'] ['timeformat'];
	}

	return date_strformat($format, $timestamp);
}

/**
 * Format a date once per day; returns '' for repeated same-day calls.
 * @param string|int|null $string Timestamp or parseable string.
 * @param string|null $format Optional format; defaults to locale date.
 * @param string|int $default_date Fallback when $string is empty.
 * @return string Empty string or formatted date when day changes.
 */
function theme_smarty_modifier_date_format_daily($string, $format = null, $default_date = '') {
	global $THEME_CURRENT_DAY, $lang, $fp_config;

	if (is_null($format)) {
		$format = $fp_config ['locale'] ['dateformat'];
	}

	$current_day = theme_date_format($string, $format, $default_date);

	if (!isset($THEME_CURRENT_DAY) || $THEME_CURRENT_DAY != $current_day) {
		$THEME_CURRENT_DAY = $current_day;

		return $current_day;
	}

	return '';
}

/**
 * Get date in RFC3339
 * For example used in XML/Atom
 *
 * @param string|int|null $timestamp The timestamp to be formatted. Defaults to null.
 * @return string date in RFC3339
 * @author Boris Korobkov
 * @see http://tools.ietf.org/html/rfc3339 http://it.php.net/manual/en/function.date.php#75757
 */
function theme_smarty_modifier_date_rfc3339($timestamp = null) {
	if ($timestamp === null) {
		$timestamp = date_time();
	}

	$date = date('Y-m-d\TH:i:s', $timestamp);

	$matches = array();
	if (preg_match('/^([\-+])(\d{2})(\d{2})$/', date('O', $timestamp), $matches)) {
		$date .= $matches [1] . $matches [2] . ':' . $matches [3];
	} else {
		$date .= 'Z';
	}
	return $date;
}

// {{{ permalink, commentlink, staticlink: filters

add_filter('feed_link', 'theme_def_feed_link', 0, 2);

/**
 * Default feed link builder used by the 'feed_link' filter.
 * @param string $str Ignored input string.
 * @param string $type Feed type, e.g., 'rss2' or 'atom'.
 * @return string Absolute feed URL.
 */
function theme_def_feed_link($str, $type) {
	return BLOG_BASEURL . '?x=feed:' . $type;
}

/**
 * Return the feed link after applying the 'feed_link' filter.
 * @param string $feed Feed type, e.g., 'rss2' or 'atom'.
 * @return string Absolute feed URL.
 */
function theme_feed_link($feed = 'rss2') {
	return apply_filters('feed_link', '', $feed);
}

add_filter('post_comments_feed_link', 'theme_def_feed_comments_link', 0, 3);

/**
 * Default comments feed link builder used by 'post_comments_feed_link'.
 * @param string $str Ignored input string.
 * @param string $feed Feed type.
 * @param string|int $id Entry identifier.
 * @return string Absolute comments feed URL.
 */
function theme_def_feed_comments_link($str, $feed, $id) {
	return BLOG_BASEURL . '?x=entry:' . $id . ';comments:1;feed:' . $feed;
}

/**
 * Return the comments feed link after applying the filter.
 * @param string $feed Feed type.
 * @param string|int $id Entry identifier.
 * @return string Absolute comments feed URL.
 */
function theme_comments_feed_link($feed, $id) {
	if (empty($feed)) {
		$feed = 'rss2';
	}
	return apply_filters('post_comments_feed_link', '', $feed, $id);
}

add_filter('post_link', 'theme_def_permalink', 0, 2);

/**
 * Default permalink builder used by the 'post_link' filter.
 * @param string $str Ignored input string.
 * @param string|int $id Entry identifier.
 * @return string Absolute permalink URL.
 */
function theme_def_permalink($str, $id) {
	return BLOG_BASEURL . '?x=entry:' . $id;
}

/**
 * Return the permalink after applying the 'post_link' filter.
 * @param string|int $id Entry identifier.
 * @return string Absolute permalink URL.
 */
function get_permalink($id) {
	return apply_filters('post_link', '', $id);
}

add_filter('comments_link', 'theme_def_commentlink', 0, 2);

/**
 * Default comments link builder used by the 'comments_link' filter.
 * @param string $str Ignored input string.
 * @param string|int $id Entry identifier.
 * @return string Absolute comments page URL.
 */
function theme_def_commentlink($str, $id) {
	return BLOG_BASEURL . '?x=entry:' . $id . ';comments:1';
}

/**
 * Return the comments link after applying the 'comments_link' filter.
 * @param string|int $id Entry identifier.
 * @return string Absolute comments page URL.
 */
function get_comments_link($id) {
	return apply_filters('comments_link', '', $id);
}

add_filter('page_link', 'theme_def_staticlink', 0, 2);

/**
 * Default static page link builder used by the 'page_link' filter.
 * @param string $str Ignored input string.
 * @param string|int $id Static page identifier.
 * @return string Absolute static page URL.
 */
function theme_def_staticlink($str, $id) {
	return BLOG_BASEURL . '?page=' . $id;
}

/**
 * Return the static page link after applying the 'page_link' filter.
 * @param string|int $id Static page identifier.
 * @return string Absolute static page URL.
 */
function theme_staticlink($id) {
	return apply_filters('page_link', '', $id);
}

add_filter('category_link', 'theme_def_catlink', 0, 2);

/**
 * Default category link builder used by the 'category_link' filter.
 * @param string $str Ignored input string.
 * @param string|int $catid Category identifier.
 * @return string Absolute category URL.
 */
function theme_def_catlink($str, $catid) {
	return BLOG_BASEURL . '?x=cat:' . $catid;
}

/**
 * Return the category link after applying the 'category_link' filter.
 * @param string|int $catid Category identifier.
 * @return string Absolute category URL.
 */
function get_category_link($catid) {
	return apply_filters('category_link', '', $catid);
}

/**
 * Build a link to the yearly archive.
 * @param int|string $year Four-digit year.
 * @return string Absolute archive URL.
 */
function get_year_link($year) {
	$year = (string)$year;
	return wp_specialchars(apply_filters('year_link', BLOG_BASEURL . '?x=y:' . str_pad($year, 2, '0', STR_PAD_LEFT), $year));
}

/**
 * Build a link to the monthly archive.
 * @param int|string $year Four-digit year.
 * @param int|string $month One- or two-digit month.
 * @return string Absolute archive URL.
 */
function get_month_link($year, $month) {
	$year = (string)$year;
	$month = (string)$month;
	return wp_specialchars(apply_filters('month_link', BLOG_BASEURL . '?x=y:' . str_pad($year, 2, '0', STR_PAD_LEFT) . ';m:' . str_pad($month, 2, '0', STR_PAD_LEFT), $year, $month));
}

/**
 * Build a link to the daily archive.
 * @param int|string $year Four-digit year.
 * @param int|string $month One- or two-digit month.
 * @param int|string $day One- or two-digit day.
 * @return string Absolute archive URL.
 */
function get_day_link($year, $month, $day) {
	$year = (string)$year;
	$month = (string)$month;
	$day = (string)$day;
	return wp_specialchars(apply_filters('day_link', BLOG_BASEURL . '?x=y:' . str_pad($year, 2, '0', STR_PAD_LEFT) . ';m:' . str_pad($month, 2, '0', STR_PAD_LEFT) . ';d:' . str_pad($day, 2, '0', STR_PAD_LEFT), $year, $month, $day));
}

/**
 * Return a localized comments label for a given count.
 * @param int $count Number of comments.
 * @return string Human-readable label.
 */
function theme_entry_commentcount($count) {
	global $lang;
	switch ($count) {
		case 0:
			return $comments = $lang ['main'] ['nocomments'];
		case 1:
			return $comments = $lang ['main'] ['comment'];
		default:
			return $comments = $count . ' ' . $lang ['main'] ['comments'];
	}
}
add_filter('comments_number', 'theme_entry_commentcount');

/**
 * Render entry categories as text or linked list.
 * @param array<int,string> $cats Category IDs assigned to the entry.
 * @param bool $link Whether to link category names.
 * @param string $separator Separator string between items.
 * @return string|null Rendered list or null when none.
 */
function theme_entry_categories($cats, $link = true, $separator = ', '): ?string {
	if (!is_array($cats) || !$cats) {
		return null;
	}
	static $defsCache = null;
	static $urlCache = array();

	if ($defsCache === null) {
		$defs = entry_categories_get('defs');
		$defsCache = is_array($defs) ? $defs : array();
	}
	if (!$defsCache) {
	 return null;
	}

	$keys = array_fill_keys(array_map('strval', $cats), true);
	$selected = array_intersect_key($defsCache, $keys);
	if (!$selected) {
		return null;
	}

	$filed = array();
	if ($link) {
		foreach ($selected as $id => $name) {
			$sid = (string)$id;
			if (!isset($urlCache[$sid])) {
				$urlCache [$sid] = (string) get_category_link($id);
			}
			$filed [] = '<a href="' . $urlCache [$sid] . '">' . (string)$name . '</a>';
		}
	} else {
		foreach ($selected as $name) {
			$filed [] = (string)$name;
		}
	}
	return $filed ? implode((string)$separator, $filed) : null;
}

/**
 * This is called only in legacy mode
 */

/**
 * Returns theme-specific filters for rendering entries.
 * Keys are hook names (e.g., 'post_link', 'post_date'); values are callables.
 * The map is consumed by the filter registrar during theme init.
 * @return array<string, callable> Hook→callback map for entry output.
 */
function &theme_entry_filters(&$contentarr, $id = null) {
	$contentarr ['subject'] = apply_filters('the_title', $contentarr ['subject']);

	$contentarr ['content'] = apply_filters('the_content', $contentarr ['content']);

	if (isset($contentarr ['comments'])) {
		$contentarr ['commentcount'] = $contentarr ['comments'];
		$contentarr ['comments'] = apply_filters('comments_number', $contentarr ['commentcount']);
	}

	$contentarr ['permalink'] = get_permalink($id);

	$contentarr ['commentlink'] = get_comments_link($id);
	return $contentarr;
}

/**
 * Returns theme-specific filters for rendering comments.
 * Keys are hook names (e.g., 'comments_link', 'comment_date'); values are callables.
 * The map is consumed by the filter registrar during theme init.
 * @return array<string, callable> Hook→callback map for comment output.
 */
function &theme_comments_filters(&$contentarr, $key) {
	$contentarr ['name'] = apply_filters('comment_author_name', $contentarr ['name']);
	if (isset($contentarr ['email'])) {
		$contentarr ['email'] = apply_filters('comment_author_email', $contentarr ['email']);
		$contentarr ['mailto'] = 'mailto:' . $contentarr ['email'];
	}
	if (!isset($contentarr ['url']))
		$contentarr ['url'] = '#';
	$contentarr ['timestamp'] = $contentarr ['date'];
	$contentarr ['content'] = apply_filters('comment_text', $contentarr ['content']);

	return $contentarr;
}

?>
