<?php
/*
Plugin Name: FavIcon
Plugin URI: http://www.flatpress.org/
Description: Adds a favicon to FlatPress
Author: NoWhereMan
Version: 1.0
Author URI: http://www.nowhereland.it/
*/ 
 
function plugin_favicon_head() {
	echo '<link rel="shortcut icon" href="' .  plugin_geturl('favicon') .'imgs/fplogo.gif" />';
}
 
add_action('wp_head', 'plugin_favicon_head');
 
?>