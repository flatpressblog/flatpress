<?php

/*
 * Plugin Name: FavIcon
 * Version: 1.0
 * Plugin URI: https://www.flatpress.org
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 * Description: Adds a favicon to FlatPress. Part of the standard distribution.
 */
function plugin_favicon_head() {
	// your file *must* be named favicon.ico
	// and be a ICO file (not a renamed png, jpg, gif, etc...)
	// or it won't work in IE
	echo '<link rel="shortcut icon" href="' . plugin_geturl('favicon') . 'imgs/favicon.ico" />';
}

add_action('wp_head', 'plugin_favicon_head');

?>
