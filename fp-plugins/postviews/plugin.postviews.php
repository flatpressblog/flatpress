<?php
/*
 * Plugin Name: PostViews
 * Version: 1.0
 * Plugin URI: https://www.flatpress.org
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 * Description: Counts and displays entry views. Part of the standard distribution.
 */

// This seems a bit hackish; please fix if possible.
// The action hook seems to come "to late", the 'views' variable assignment in plugin_postviews_do() does not have effect on the (already parsed?) template.
// For now, smarty_block_entry() in core.fpdb.class.php takes care of calling plugin_postviews_do() early enough.
// add_action('entry_block', 'plugin_postviews_do');

function plugin_postviews_calc($id, $calc) {
	$dir = entry_dir($id);
	if (!$dir)
		return;

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

function plugin_postviews_do($smarty, $id) {
	global $fpdb;
	
	$q = $fpdb->getQuery();
	$calc = $q->single;

	$v = plugin_postviews_calc($id, $calc);
	$smarty->assign('views', $v);
}

?>
