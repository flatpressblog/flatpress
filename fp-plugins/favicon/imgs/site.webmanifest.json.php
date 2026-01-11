<?php
declare(strict_types=1);

header('Content-Type: application/manifest+json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once dirname(__FILE__, 4) . '/defaults.php';
require_once dirname(__FILE__, 4) . '/fp-includes/core/core.system.php';

$fp_config = array();
$settingsFile = rtrim(ABS_PATH, "/\\") . '/' . FP_CONTENT . 'config/settings.conf.php';
if (is_file($settingsFile)) {
	include $settingsFile;
}

$title = $fp_config ['general'] ['title'] ?? '';
$subtitle = $fp_config ['general'] ['subtitle'] ?? '';

$host = parse_url(BLOG_BASEURL, PHP_URL_HOST);
if (!is_string($host) || $host === '') {
	$host = 'FlatPress';
}

if (!is_string($title) || $title === '') {
	$title = $host;
}

$shortName = $title;
if (!is_string($subtitle)) {
	$subtitle = '';
}

$description = ($subtitle !== '') ? $subtitle : $title;

$baseUrl = BLOG_BASEURL;
if (!is_string($baseUrl) || $baseUrl === '') {
	$baseUrl = '';
}
$baseUrl = rtrim($baseUrl, "/") . "/";

$pluginsDir = defined('PLUGINS_DIR') ? (string)PLUGINS_DIR : 'fp-plugins/';
$pluginsDir = trim($pluginsDir, "/\\") . '/';
$pluginRel = $pluginsDir . 'favicon/imgs/';

$siteBase = $baseUrl;
if (substr($baseUrl, -strlen($pluginRel)) === $pluginRel) {
	$siteBase = substr($baseUrl, 0, -strlen($pluginRel));
}
$siteBase = rtrim($siteBase, "/") . "/";

$startUrl = $siteBase;
$scope = $siteBase;

$imgUrlBase = $siteBase . $pluginRel;
$imgFsBase = rtrim (ABS_PATH, "/\\") . '/' . PLUGINS_DIR . 'favicon/imgs/';

$assetVer = function (string $filename) use ($imgFsBase): string {
	$file = $imgFsBase . $filename;
	if (is_file($file)) {
		$mtime = @filemtime($file);
		if (is_int($mtime) && $mtime > 0) {
			return '?v=' . rawurlencode((string)$mtime);
		}
	}
	if (defined('SYSTEM_VER')) {
		return '?v=' . rawurlencode((string)SYSTEM_VER);
	}
	return '';
};

$manifest = array(
	'name' => $title,
	'short_name' => $shortName,
	'description' => $description,
	'start_url' => $startUrl,
	'scope' => $scope,
	'id' => $startUrl,
	'display' => 'standalone',
	'theme_color' => '#b77b7b',
	'background_color' => '#b77b7b',
	'icons' => array(
		array(
			'src' => $imgUrlBase . 'android-chrome-192x192.png' . $assetVer('android-chrome-192x192.png'),
			'sizes' => '192x192',
			'type' => 'image/png'
		),
		array(
			'src' => $imgUrlBase . 'android-chrome-256x256.png' . $assetVer('android-chrome-256x256.png'),
			'sizes' => '256x256',
			'type' => 'image/png'
		),
		array(
			'src' => $imgUrlBase . 'android-chrome-512x512.png' . $assetVer('android-chrome-512x512.png'),
			'sizes' => '512x512',
			'type' => 'image/png'
		)
	),
	'screenshots' => array(
		array(
			'src' => $imgUrlBase . 'android-chrome-320x320.png' . $assetVer('android-chrome-320x320.png'),
			'sizes' => '320x320',
			'type' => 'image/png',
			'form_factor' => 'narrow'
		),
		array(
			'src' => $imgUrlBase . 'android-chrome-320x320.png' . $assetVer('android-chrome-320x320.png'),
			'sizes' => '320x320',
			'type' => 'image/png',
			'form_factor' => 'wide'
		)
	)
);

echo json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
