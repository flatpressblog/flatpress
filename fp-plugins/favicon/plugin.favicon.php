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
	global $fp_config;
	$charset = strtoupper($fp_config ['locale'] ['charset'] ?? 'UTF-8');

	// Google Search, for example, only supports one favicon per website, whereby a website is defined by the host name.
	// If an icon is in the main directory, do not load it, but redirect it temporarily!!!
	redir_favicon();

	// Indicates the version of the symbol.
	// The browser will then immediately display the latest version.
	$p = plugin_geturl('favicon') . 'imgs/';
	$ver = function (string $url, $force = null): string {
		if (function_exists('utils_asset_ver')) {
			return utils_asset_ver($url, $force);
		}
		$sep = (strpos($url, '?') === false) ? '?' : '&';
		$v = ($force !== null) ? (string)$force : (defined('SYSTEM_VER') ? (string)SYSTEM_VER : (string)time());
		return $url . $sep . 'v=' . rawurlencode($v);
	};
	$e = function (string $s) use ($charset): string {
		return htmlspecialchars($s, ENT_QUOTES, $charset);
	};

	$apple180 = $ver($p . 'apple-touch-icon.png');
	$apple152 = $ver($p . 'apple-touch-icon-152x152.png');
	$apple120 = $ver($p . 'apple-touch-icon-120x120.png');
	$apple76 = $ver($p . 'apple-touch-icon-76x76.png');
	$apple60 = $ver($p . 'apple-touch-icon-60x60.png');
	$applePre = $ver($p . 'apple-touch-icon-precomposed.png');
	$and256 = $ver($p . 'android-chrome-256x256.png');// For Android home screen
	$and192 = $ver($p . 'android-chrome-192x192.png');
	$png32 = $ver($p . 'favicon-32x32.png');
	$png16 = $ver($p . 'favicon-16x16.png');
	$icoMulti = $ver($p . 'favicon.ico'); // FlatPress multilayer icon
	$safari = $ver($p . 'safari-pinned-tab.svg'); // Mask icon for Safari pinned tabs
	$manifest = $ver($p . 'site.webmanifest.json.php', defined('SYSTEM_VER') ? SYSTEM_VER : null); // This file must be located in the imgs directory!
	$msconfig = $ver($p . 'browserconfig.xml.php', defined('SYSTEM_VER') ? SYSTEM_VER : null);
	$mstile = $ver($p . 'mstile-144x144.png');

	echo '
		<!-- BOF FavIcon -->
		' . //

		// Smartphone iOS Safari
		'<link rel="apple-touch-icon" sizes="180x180" href="' . $e($apple180) . '">' . //
		'<link rel="apple-touch-icon" sizes="152x152" href="' . $e($apple152) . '">' . //
		'<link rel="apple-touch-icon" sizes="120x120" href="' . $e($apple120) . '">' . //
		'<link rel="apple-touch-icon" sizes="76x76" href="' . $e($apple76)  . '">' . //
		'<link rel="apple-touch-icon" sizes="60x60" href="' . $e($apple60)  . '">' . //
		'<link rel="apple-touch-icon-precomposed" sizes="180x180" href="' . $e($applePre) . '">' . //

		// Smartphone Android Chrome
		'<link rel="icon" type="image/png" sizes="256x256" href="' . $e($and256) . '">' . //
		'<link rel="icon" type="image/png" sizes="192x192" href="' . $e($and192) . '">' . //
		'<link rel="icon" type="image/png" sizes="32x32" href="' . $e($png32) . '">' . //
		'<link rel="icon" type="image/png" sizes="16x16" href="' . $e($png16) . '">' . //
		'<link rel="manifest" href="' . $e($manifest) . '">' . //

		// Mac OS Safari
		'<link rel="mask-icon" href="' . $e($safari) . '" color="#aa4142">' . //

		// Classic/desktop browsers
		'<link rel="icon" sizes="16x16 32x32 48x48" href="' . $e($icoMulti) . '">' . //

		// Windows 10 or higher
		'<meta name="msapplication-TileColor" content="#b77b7b">' . //
		'<meta name="msapplication-config" content="' . $e($msconfig) . '">' . //
		'<meta name="msapplication-TileImage" content="' . $e($mstile) . '">' . //

		'<meta name="theme-color" content="#b77b7b">' . // Specify a color for the browser toolbar and the status bar on mobile devices

		'
		<!-- EOF FavIcon -->
		';
}

function redir_favicon() {

	$favicons = array (
		'favicon.ico',
		'apple-touch-icon.png',
		'apple-touch-icon-precomposed.png'
	);

	if (isset ($_SERVER ['REQUEST_URI'])) { // Check whether the server array element is set
		$requestUri = $_SERVER ['REQUEST_URI'];
	} else {
		$requestUri = false;
	}

	foreach ($favicons as $favicon) {
		if (strpos($requestUri, $favicon) !== false) {
			@http_response_code(307); // Temporary Redirect
			@header('Location: ' . plugin_geturl('favicon') . 'imgs/' . $favicon);
			return true;
		}
	}

	return false;
}


add_action('wp_head', 'plugin_favicon_head');
?>
