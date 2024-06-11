<?php
/*
 * Plugin Name: FavIcon
 * Version: 1.1.0
 * Plugin URI: https://www.flatpress.org
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 * Description: Adds favicons for search engines, mobile devices or browsers to FlatPress. Part of the standard distribution. <a href="./fp-plugins/favicon/doc_favicon.txt" title="More information" target="_blank">[More information]</a>
 */

// Google Search, for example, only supports one favicon per website, whereby a website is defined by the host name.
// If an icon in the main directory, do not load!!!
$httpHost = $_SERVER ['HTTP_HOST'];
$requestUri = $_SERVER ['REQUEST_URI'];
if (strpos($httpHost, 'favicon') || strpos($requestUri, 'favicon')) {
	http_response_code(404);
}

if (strpos($httpHost, 'apple-touch-icon') || strpos($requestUri, 'apple-touch-icon')) {
	http_response_code(404);
}

function plugin_favicon_head() {
	// FlatPress icon set
	echo '
		<!-- BOF FavIcon -->
		' . // Smartphone iOS Safari
		'<link rel="apple-touch-icon" sizes="180x180" href="' . plugin_geturl('favicon') . 'imgs/apple-touch-icon-180x180.png">' . //
		'<link rel="apple-touch-icon" sizes="152x152" href="' . plugin_geturl('favicon') . 'imgs/apple-touch-icon-152x152.png">' . //
		'<link rel="apple-touch-icon" sizes="120x120" href="' . plugin_geturl('favicon') . 'imgs/apple-touch-icon-120x120.png">' . //
		'<link rel="apple-touch-icon" sizes="76x76" href="' . plugin_geturl('favicon') . 'imgs/apple-touch-icon-76x76.png">' . //
		'<link rel="apple-touch-icon" sizes="60x60" href="' . plugin_geturl('favicon') . 'imgs/apple-touch-icon-60x60.png">' . //
			// Smartphone Android Chrome
		'<link rel="icon" type="image/png" sizes="256x256" href="' . plugin_geturl('favicon') . 'imgs/android-chrome-256x256.png">' . // For Android home screen
		'<link rel="icon" type="image/png" sizes="192x192" href="' . plugin_geturl('favicon') . 'imgs/android-chrome-192x192.png">' . //
		'<link rel="icon" type="image/png" sizes="32x32" href="' . plugin_geturl('favicon') . 'imgs/favicon-32x32.png">' . //
		'<link rel="icon" type="image/png" sizes="16x16" href="' . plugin_geturl('favicon') . 'imgs/favicon-16x16.png">' . //
		'<link rel="manifest" href="' . plugin_geturl('favicon') . 'imgs/site.webmanifest.json.php">' . // This file must be located in the imgs directory!
			// Mac OS Safari
		'<link rel="mask-icon" href="' . plugin_geturl('favicon') . 'imgs/safari-pinned-tab.svg" color="#aa4142">' . // Mask icon for Safari pinned tabs
			// Classic/, desktop browsers
		'<link rel="icon" sizes="16x16 32x32 48x48" href="' . plugin_geturl('favicon') . 'imgs/favicon.ico">' . // Multilayer icon
		'<link rel="icon" sizes="48x48" href="' . plugin_geturl('favicon') . 'imgs/favicon-48x48.ico">' . // Highest resolution icon
		'<link rel="icon" sizes="32x32" href="' . plugin_geturl('favicon') . 'imgs/favicon-32x32.ico">' . //
		'<link rel="icon" sizes="16x16" href="' . plugin_geturl('favicon') . 'imgs/favicon-16x16.ico">' . //
			// Windows 10 or higher
		'<meta name="msapplication-TileColor" content="#b77b7b">' . //
		'<meta name="msapplication-config" content="' . plugin_geturl('favicon') . 'imgs/browserconfig.xml.php">' . // This file must be located in the imgs directory!
		'<meta name="msapplication-TileImage" content="' . plugin_geturl('favicon') . 'imgs/mstile-144x144.png">' . //
		'<meta name="theme-color" content="#b77b7b">
		<!-- EOF FavIcon -->
		';
}

add_action('wp_head', 'plugin_favicon_head');
?>
