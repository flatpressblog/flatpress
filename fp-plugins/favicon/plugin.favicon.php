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
	// your file *must* be named favicon.ext, 
	// where ext is an image extension (such as gif, png, ico...)
	echo '<link rel="shortcut icon" href="' .  
		plugin_geturl('favicon') .'imgs/favicon.gif" />';
}
 
add_action('wp_head', 'plugin_favicon_head');
 
?>
