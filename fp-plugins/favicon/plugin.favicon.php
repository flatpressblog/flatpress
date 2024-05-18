<?php

/*
 * Plugin Name: FavIcon
 * Version: 1.1.0
 * Plugin URI: https://www.flatpress.org
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 * Description: Adds a favicon to FlatPress. Part of the standard distribution.
 */
function plugin_favicon_head() {
	// http://realfavicongenerator.net
	echo '
		<!-- BOF FavIcon -->
		<link rel="apple-touch-icon" sizes="180x180" href="' . plugin_geturl('favicon') . 'imgs/apple-touch-icon.png">
		<link rel="apple-touch-icon-precomposed" sizes="180x180" href="' . plugin_geturl('favicon') . 'imgs/apple-touch-icon-precomposed.png">
		<link rel="apple-touch-icon" sizes="152x152" href="' . plugin_geturl('favicon') . 'imgs/apple-touch-icon-152x152.png">
		<link rel="apple-touch-icon-precomposed" sizes="152x152" href="' . plugin_geturl('favicon') . 'imgs/apple-touch-icon-152x152-precomposed.png">
		<link rel="apple-touch-icon" sizes="120x120" href="' . plugin_geturl('favicon') . 'imgs/apple-touch-icon-120x120.png">
		<link rel="apple-touch-icon-precomposed" sizes="120x120" href="' . plugin_geturl('favicon') . 'imgs/apple-touch-icon-120x120-precomposed.png">
		<link rel="apple-touch-icon" sizes="76x76" href="' . plugin_geturl('favicon') . 'imgs/apple-touch-icon-76x76.png">
		<link rel="apple-touch-icon-precomposed" sizes="76x76" href="' . plugin_geturl('favicon') . 'imgs/apple-touch-icon-76x76-precomposed.png">
		<link rel="apple-touch-icon" sizes="60x60" href="' . plugin_geturl('favicon') . 'imgs/apple-touch-icon-60x60.png">
		<link rel="apple-touch-icon-precomposed" sizes="60x60" href="' . plugin_geturl('favicon') . 'imgs/apple-touch-icon-60x60-precomposed.png">
		<link rel="icon" type="image/png" sizes="192x192"  href="' . plugin_geturl('favicon') . 'imgs/android-chrome-192x192.png">
		<link rel="icon" type="image/png" sizes="32x32" href="' . plugin_geturl('favicon') . 'imgs/favicon-32x32.png">
		<link rel="icon" type="image/png" sizes="16x16" href="' . plugin_geturl('favicon') . 'imgs/favicon-16x16.png">
		<link rel="manifest" href="' . plugin_geturl('favicon') . 'res/site.webmanifest">
		<link rel="mask-icon" href="' . plugin_geturl('favicon') . 'imgs/safari-pinned-tab.svg" color="#aa4142">
		<link rel="shortcut icon" href="' . plugin_geturl('favicon') . 'imgs/favicon.ico">
		<meta name="msapplication-TileColor" content="#da532c">
		<meta name="msapplication-config" content="' . plugin_geturl('favicon') . 'res/browserconfig.xml">
		<meta name="msapplication-TileImage" content="' . plugin_geturl('favicon') . 'imgs/mstile-70x70.png">
		<meta name="theme-color" content="#b77b7b">
		<!-- EOF FavIcon -->
		';
}

add_action('wp_head', 'plugin_favicon_head');
?>
