<?php
/**
 * Returns current view count and increments on single view pages.
 * {views [id=...] [assign=...]}
 * Uses plugin_postviews_calc() if the plugin is active, otherwise empty output.
 */
function smarty_function_views($params, $smarty) {
	// No plugin active -> empty
	if (!function_exists('plugin_postviews_calc')) {
		if (!empty($params ['assign'])) {
			$smarty->assign($params ['assign'], '');
			return '';
		}
		return '';
	}

	// 1) explicit param
	$id = null;
	if (!empty($params ['id']) && is_string($params ['id'])) {
		$id = $params ['id'];
	}
	// 2) Smarty template vars
	if (!$id && method_exists($smarty, 'getTemplateVars')) {
		$tmp = $smarty->getTemplateVars('id');
		if (is_string($tmp) && $tmp !== '') {
			$id = $tmp;
		}
	}
	// 3) FPDB query context
	global $fpdb;
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
		if (!empty($params ['assign'])) {
			$smarty->assign($params ['assign'], '');
		}
		return '';
	}

	$q = isset($fpdb) && is_object($fpdb) ? $fpdb->getQuery() : null;
	$single = is_object($q) && isset($q->single) ? $q->single : false;

	$v = plugin_postviews_calc($id, $single);
	if (!empty($params ['assign'])) {
		$smarty->assign($params ['assign'], $v); return '';
	}
	return $v;
}
?>
