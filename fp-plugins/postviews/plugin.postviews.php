<?php
/*
Plugin Name: PostViews
Plugin URI: http://www.nowhereland.it/
Description: PostViews plugin.
Author: NoWhereMan
Version: 1.0
Author URI: http://www.nowhereland.it/
*/ 

add_action('entry_block', 'plugin_postviews_do');

function plugin_postviews_calc($id, $calc) {

	$dir = entry_dir($id);
	if (!$dir) return;
	
	$f = $dir . '/view_counter' .EXT;
	
	$v = io_load_file($f);
	
	if ($v===false){
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

function plugin_postviews_do($id) {
	
	global $fpdb, $smarty;
	
	$q = $fpdb->getQuery();
	$calc = $q->single;
	
	$v = plugin_postviews_calc($id, $calc);
	
	$smarty->assign('views', $v);

}



?>
