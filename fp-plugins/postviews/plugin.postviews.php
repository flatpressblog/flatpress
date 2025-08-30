<?php
/**
 * Plugin Name: PostViews
 * Version: 1.0.1
 * Plugin URI: https://www.flatpress.org
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 * Description: Counts and displays entry views. Part of the standard distribution.
 */

// Plugin now only provides counting logic
// The implementation is in fp-smartyplugins/function.views.php and prefilter.postviews_assign.php
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
?>
