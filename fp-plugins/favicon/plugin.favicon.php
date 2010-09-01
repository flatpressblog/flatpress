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
	// your file *must* be named favicon.ico 
	// and be a ICO file (not a renamed png, jpg, gif, etc...)
	// or it won't work in IE
	echo '<link rel="shortcut icon" href="' .  
		plugin_geturl('favicon') .'imgs/favicon.ico" />';
}
 
add_action('wp_head', 'plugin_favicon_head');
 
?>
