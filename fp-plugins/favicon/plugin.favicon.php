<?php
/**
 * Plugin Name: FavIcon
 * Version: 1.1.1
 * Plugin URI: https://www.flatpress.org
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 * Description: Adds favicons for search engines, mobile devices or browsers to FlatPress. Part of the standard distribution. <a href="./fp-plugins/favicon/doc_favicon.txt" title="More information" target="_blank">[More information]</a>
 */

function plugin_favicon_head() {
	global $fp_config;
	$charset = strtoupper($fp_config ['locale'] ['charset'] ?? 'UTF-8');
	$title = $fp_config ['general'] ['title'] ?? '';

	// Indicates the version of the symbol.
	// The browser will then immediately display the latest version.
	$p = plugin_geturl('favicon') . 'imgs/';
	$imgFsBase = __DIR__ . '/imgs/';
	$assetForce = function (string $filename) use ($imgFsBase) {
		$file = $imgFsBase . $filename;
		if (is_file($file)) {
			$mtime = @filemtime($file);
			if (is_int($mtime) && $mtime > 0) {
				return (string)$mtime;
			}
		}
		if (defined('SYSTEM_VER')) {
			return (string)SYSTEM_VER;
		}
		return null;
	};
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

	$apple180 = $ver($p . 'apple-touch-icon.png', $assetForce('apple-touch-icon.png'));
	$apple152 = $ver($p . 'apple-touch-icon-152x152.png', $assetForce('apple-touch-icon-152x152.png'));
	$apple120 = $ver($p . 'apple-touch-icon-120x120.png', $assetForce('apple-touch-icon-120x120.png'));
	$apple76 = $ver($p . 'apple-touch-icon-76x76.png', $assetForce('apple-touch-icon-76x76.png'));
	$apple60 = $ver($p . 'apple-touch-icon-60x60.png', $assetForce('apple-touch-icon-60x60.png'));
	$applePre = $ver($p . 'apple-touch-icon-precomposed.png', $assetForce('apple-touch-icon-precomposed.png'));
	$and512 = $ver($p . 'android-chrome-512x512.png', $assetForce('android-chrome-512x512.png')); // For Android home screen
	$and320 = $ver($p . 'android-chrome-320x320.png', $assetForce('android-chrome-320x320.png'));
	$and256 = $ver($p . 'android-chrome-256x256.png', $assetForce('android-chrome-256x256.png'));
	$and192 = $ver($p . 'android-chrome-192x192.png', $assetForce('android-chrome-192x192.png'));
	$png96 = $ver($p . 'favicon-96x96.png', $assetForce('favicon-96x96.png'));
	$png32 = $ver($p . 'favicon-32x32.png', $assetForce('favicon-32x32.png'));
	$png16 = $ver($p . 'favicon-16x16.png', $assetForce('favicon-16x16.png'));
	$icoMulti = $ver($p . 'favicon.ico', $assetForce('favicon.ico')); // FlatPress multilayer icon
	$svg320 = $ver($p . 'favicon.svg', $assetForce('favicon.svg'));
	$safari = $ver($p . 'safari-pinned-tab.svg', $assetForce('safari-pinned-tab.svg')); // Mask icon for Safari pinned tabs
	$manifest = $ver($p . 'site.webmanifest.json.php', defined('SYSTEM_VER') ? SYSTEM_VER : null); // This file must be located in the imgs directory!
	$msconfig = $ver($p . 'browserconfig.xml.php', defined('SYSTEM_VER') ? SYSTEM_VER : null);
	$mstile = $ver($p . 'mstile-144x144.png', $assetForce('mstile-144x144.png'));

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
		'<link rel="icon" type="image/png" sizes="512x512" href="' . $e($and512) . '">' . //
		'<link rel="icon" type="image/png" sizes="320x320" href="' . $e($and320) . '">' . //
		'<link rel="icon" type="image/png" sizes="256x256" href="' . $e($and256) . '">' . //
		'<link rel="icon" type="image/png" sizes="192x192" href="' . $e($and192) . '">' . //
		'<link rel="icon" type="image/png" sizes="96x96" href="' . $e($png96) . '">' . //
		'<link rel="icon" type="image/png" sizes="32x32" href="' . $e($png32) . '">' . //
		'<link rel="icon" type="image/png" sizes="16x16" href="' . $e($png16) . '">' . //
		'<link rel="manifest" href="' . $e($manifest) . '">' . //

		// Mac OS Safari
		'<link rel="icon" type="image/svg+xml" href="' . $e($svg320) . '">' . //
		'<link rel="mask-icon" href="' . $e($safari) . '" color="#aa4142">' . //

		// Classic/desktop browsers
		'<link rel="icon" sizes="16x16 32x32 48x48" href="' . $e($icoMulti) . '">' . //

		// Windows 10 or higher
		'<meta name="msapplication-TileColor" content="#b77b7b">' . //
		'<meta name="msapplication-config" content="' . $e($msconfig) . '">' . //
		'<meta name="msapplication-TileImage" content="' . $e($mstile) . '">' . //

		'<meta name="theme-color" content="#b77b7b">' . // Specify a color for the browser toolbar and the status bar on mobile devices
		'<meta name="apple-mobile-web-app-title" content="' . $e($title) . '">' . //

		'
		<!-- EOF FavIcon -->
		';
}

function redir_favicon(): void {
	$favicons = array(
		'favicon.ico',
		'apple-touch-icon.png',
		'apple-touch-icon-precomposed.png'
	);

	if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
		return;
	}

	$requestUri = $_SERVER ['REQUEST_URI'] ?? '';
	if (!is_string($requestUri) || $requestUri === '') {
		return;
	}

	$path = parse_url($requestUri, PHP_URL_PATH);
	if (!is_string($path) || $path === '') {
		return;
	}

	// Avoid redirect loops when the icon is requested from the plugin's own directory.
	$pluginsDir = defined('PLUGINS_DIR') ? (string)PLUGINS_DIR : 'fp-plugins/';
	$pluginsDir = trim($pluginsDir, "/\\");
	$needleA = '/' . $pluginsDir . '/favicon/imgs/';
	$needleB = $pluginsDir . '/favicon/imgs/';
	if (strpos($path, $needleA) !== false || strpos($path, $needleB) !== false) {
		return;
	}

	$basename = basename($path);
	if ($basename === '' || !in_array($basename, $favicons, true)) {
		return;
	}

	if (headers_sent()) {
		return;
	}

	$target = plugin_geturl('favicon') . 'imgs/' . $basename;
	header('Location: ' . $target, true, 301);
	exit;
}

add_action('init', 'redir_favicon', 0);
add_action('wp_head', 'plugin_favicon_head');
?>
