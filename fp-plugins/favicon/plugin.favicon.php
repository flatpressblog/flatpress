<?php
/*
 * Plugin Name: FavIcon
 * Version: 1.1.0
 * Plugin URI: https://www.flatpress.org
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 * Description: Adds favicons for search engines, mobile devices or browsers to FlatPress. Part of the standard distribution. <a href="./fp-plugins/favicon/doc_favicon.txt" title="More information" target="_blank">[More information]</a>
 */

function plugin_favicon_head() {

	// Google Search, for example, only supports one favicon per website, whereby a website is defined by the host name.
	// If an icon is in the main directory, do not load it, but redirect it temporarily!!!
	redir_favicon();

	// Indicates the version of the symbol. Increase it by one when you change the image ($v = '?v=3', $v = '?v=4', etc.).
	// The browser will then immediately display the latest version.
	$v = '?v=2';

	echo '
		<!-- BOF FavIcon -->' . //

		// Smartphone iOS Safari
		'<link rel="apple-touch-icon" sizes="180x180" href="' . plugin_geturl('favicon') . 'imgs/apple-touch-icon.png' . $v . '">' . //
		'<link rel="apple-touch-icon" sizes="152x152" href="' . plugin_geturl('favicon') . 'imgs/apple-touch-icon-152x152.png' . $v . '">' . //
		'<link rel="apple-touch-icon" sizes="120x120" href="' . plugin_geturl('favicon') . 'imgs/apple-touch-icon-120x120.png' . $v . '">' . //
		'<link rel="apple-touch-icon" sizes="76x76" href="' . plugin_geturl('favicon') . 'imgs/apple-touch-icon-76x76.png' . $v . '">' . //
		'<link rel="apple-touch-icon" sizes="60x60" href="' . plugin_geturl('favicon') . 'imgs/apple-touch-icon-60x60.png' . $v . '">' . //
		'<link rel="apple-touch-icon-precomposed" sizes="180x180" href="' . plugin_geturl('favicon') . 'imgs/apple-touch-icon-precomposed.png' . $v . '">' . //

		// Smartphone Android Chrome
		'<link rel="icon" type="image/png" sizes="256x256" href="' . plugin_geturl('favicon') . 'imgs/android-chrome-256x256.png' . $v . '">' . // For Android home screen
		'<link rel="icon" type="image/png" sizes="192x192" href="' . plugin_geturl('favicon') . 'imgs/android-chrome-192x192.png' . $v . '">' . //
		'<link rel="icon" type="image/png" sizes="32x32" href="' . plugin_geturl('favicon') . 'imgs/favicon-32x32.png' . $v . '">' . //
		'<link rel="icon" type="image/png" sizes="16x16" href="' . plugin_geturl('favicon') . 'imgs/favicon-16x16.png' . $v . '">' . //
		'<link rel="manifest" href="' . plugin_geturl('favicon') . 'imgs/site.webmanifest.json.php' . $v . '">' . // This file must be located in the imgs directory!

		// Mac OS Safari
		'<link rel="mask-icon" href="' . plugin_geturl('favicon') . 'imgs/safari-pinned-tab.svg' . $v . '" color="#aa4142">' . // Mask icon for Safari pinned tabs

		// Classic/, desktop browsers
		'<link rel="icon" sizes="16x16 32x32 48x48" href="' . plugin_geturl('favicon') . 'favicon.ico' . $v . '">' . // FlatPress multilayer icon

		// Windows 10 or higher
		'<meta name="msapplication-TileColor" content="#b77b7b">' . //
		'<meta name="msapplication-config" content="' . plugin_geturl('favicon') . 'imgs/browserconfig.xml.php' . $v . '">' . // This file must be located in the imgs directory!
		'<meta name="msapplication-TileImage" content="' . plugin_geturl('favicon') . 'imgs/mstile-144x144.png' . $v . '">' . //

		'<meta name="theme-color" content="#b77b7b">' . // Specify a color for the browser toolbar and the status bar on mobile devices
		'<!-- EOF FavIcon -->
		';
}

function redir_favicon() {

	$requestUri = $_SERVER ['REQUEST_URI'];
	$favicons = array (
		'favicon.ico',
		'apple-touch-icon.png',
		'apple-touch-icon-precomposed.png'
	);

	foreach($favicons as $favicon) {
		if (strpos($requestUri, $favicon) !== false) {
			http_response_code(307); // Temporary Redirect
			header('Location: ' . plugin_geturl('favicon') . 'imgs/' . $favicon);
			return true;
		}
	}

	return false;
}


add_action('wp_head', 'plugin_favicon_head');
?>
