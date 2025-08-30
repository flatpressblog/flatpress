<?php
/**
 * Plugin Name: PostViews
 * Version: 1.0.1
 * Plugin URI: https://www.flatpress.org
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 * Description: Counts and displays entry views. Part of the standard distribution.
 */
function plugin_postviews_calc($id, $calc) {
	$dir = entry_dir($id);
	if (!$dir) {
		return;
	}

	$f = $dir . '/view_counter' . EXT;

	$v = io_load_file($f);

	if ($v === false) {
		$v = 0;
	} elseif ($v < 0) {
		// file was locked. Do not increase views.
		// actually on file locks system should hang, so
		// this should never happen
		$v = 0;
		$calc = false;
	}

	if ($calc && !user_loggedin()) {
		$v++;
		io_write_file($f, $v);
	}

	return $v;
}

/**
 * Smarty: Registration of the {views} function.
 * Hooked into 'init' so $smarty is available.
 */
function register_smarty_postviews() {
	static $done = false;
	if ($done) {
		return;
	}
	global $smarty;
	if (!isset($smarty) || !is_object($smarty) || !method_exists($smarty, 'registerPlugin')) {
		return;
	}
	$smarty->registerPlugin('function', 'views', 'smarty_function_views'); // {views}
	if (method_exists($smarty, 'registerFilter')) {
		$smarty->registerFilter('pre', 'postviews_prefilter_assign_views');
	}
	$done = true;
}
add_filter('init', 'register_smarty_postviews');

/**
 * Smarty prefilter:
 * If a template contains "{$views}", inject a one-time assignment
 * so "{$views}" works without Core hooks.
 * Runs at compile time only.
 */
function postviews_prefilter_assign_views($tpl_source, $template) {
	// No {$views} -> no change
	if (strpos($tpl_source, '{$views}') === false) {
		return $tpl_source;
	}
	// Avoid double injection
	if (strpos($tpl_source, '{views id=$id assign=views}') !== false) {
		return $tpl_source;
	}
	// prepend silent assignment; {views assign=...} returns ''
	return '{views id=$id assign=views}' . "\n" . $tpl_source;
}

/**
 * Returns current view count and increments on single view pages.
 */
function smarty_function_views($params, $smarty) {
	global $fpdb;
	$id = null;

	// 1) explicit param
	if (isset($params ['id']) && is_string($params ['id']) && $params ['id'] !== '') {
		$id = $params ['id'];
	}

	// 2) Smarty template vars
	if (!$id && isset($smarty) && is_object($smarty) && method_exists($smarty, 'getTemplateVars')) {
		$id = $smarty->getTemplateVars('id');
		if (!is_string($id) || $id === '') {
			$id = null;
		}
	}

	// 3) FPDB query context
	if (!$id && isset($fpdb) && is_object($fpdb) && method_exists($fpdb, 'getQuery')) {
		$q = $fpdb->getQuery();
		if (is_object($q)) {
			if (method_exists($q, 'getCurrentId')) {
				$cid = $q->getCurrentId();
				if (is_string($cid) && $cid !== '') {
					$id = $cid;
				}
			}
			if (!$id && method_exists($q, 'getLastEntry')) {
				$last = $q->getLastEntry();
				if (is_array($last) && isset($last [0]) && is_string($last [0]) && $last [0] !== '') {
					$id = $last [0];
				}
			}
		}
	}

	if (!$id) {
		return '';
	}

	$q = isset($fpdb) && is_object($fpdb) ? $fpdb->getQuery() : null;
	$calc = is_object($q) && isset($q->single) ? $q->single : false;

	$v = plugin_postviews_calc($id, $calc);

	if (isset($params ['assign']) && is_string($params ['assign']) && $params ['assign'] !== '') {
		$smarty->assign($params ['assign'], $v);
		return '';
	}
	return $v;
}

?>
