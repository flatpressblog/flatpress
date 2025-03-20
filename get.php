<?php
/**
 * Plugin Name: BBcode
 * Module: get.php
 * Function: BBCODE_USE_FILEWRAPPER
 * Purpose: Mask the path of the attachs dir
 * Change-Date: 23.11.2024, by FKM
 */
require_once 'defaults.php';

// Load language file
$settings_file = CONFIG_DIR . 'settings.conf.php';
if (file_exists($settings_file)) {
	require_once($settings_file);
}
$langId = $fp_config ['locale'] ['lang'];
$langFile = ABS_PATH . PLUGINS_DIR . 'bbcode/lang/lang.' . $langId . '.php';

if (!file_exists($langFile)) {
	$langFile = ABS_PATH . PLUGINS_DIR . 'bbcode/lang/lang.en-us.php';
	$langId = 'en-us';
}
require_once $langFile;

$lang = $lang ['plugin'] ['bbcode'];

// Load the validation
require_once ABS_PATH . PLUGINS_DIR . 'bbcode/inc/isValidFileDownloadUrl.php';

// Define allowed directory
define('ALLOWED_DIR', ABS_PATH . ATTACHS_DIR);

/**
 * Get main part
 */

// Decode and sanitize the file parameter
$downloadFile = urldecode($_GET ['f'] ?? '');

// Validate the file name
if (empty($downloadFile) || !isValidFileDownloadUrl($downloadFile)) {
	error_403($langId, $lang, $downloadFile);
	die;
}

// Remove directory traversal attempts and normalize the path
$sanitizedFile = basename($downloadFile);
$downloadPath = ALLOWED_DIR . '/' . $sanitizedFile;

// Ensure the file exists
if (!file_exists($downloadPath)) {
	error_404($langId, $lang, $sanitizedFile);
	die;
}

// Normalize and check the path to prevent directory traversal
$realDownloadPath = realpath($downloadPath);
$normalizedAllowedDir = str_replace('\\', '/', realpath(ALLOWED_DIR));
$normalizedDownloadPath = str_replace('\\', '/', $realDownloadPath);

if ($realDownloadPath === false || strpos($normalizedDownloadPath, $normalizedAllowedDir) !== 0) {
	error_403($langId, $lang, $sanitizedFile);
	die;
}

// Ensure the file is readable
if (!is_readable($realDownloadPath)) {
	error_403($langId, $lang, $sanitizedFile);
	die;
}

// Serve the file with appropriate headers
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($realDownloadPath) . '"');
header('Content-Length: ' . filesize($realDownloadPath));
readfile($realDownloadPath);

// Functions for error handling
function error_403($langId, $lang, $downloadFile) {
	header('HTTP/1.0 403 Forbidden');
	header('Content-type: text/html; charset=utf-8');
	echo '<!DOCTYPE HTML>' . //
		'<html xmlns="http://www.w3.org/1999/xhtml" lang="' . $langId . '">' . //
		'<head><title>' . $lang ['error_403'] . '</title></head>' . //
		'<body>' . //
		'<h1>' . $lang ['error_403'] . '</h1>' . //
		'<p><strong>' . $lang ['not_send'] . '<br>' . $lang ['file'] . ': ' . //
		'<span style="color:#FF0000">' . htmlspecialchars($downloadFile) . '</span>' . //
		'</strong></p>' . //
		'<p><small><span style="color:#9c9c9c">' . $lang ['report_error_1'] . ' <a href="contact.php">' . $lang ['report_error_2'] . '</a>, ' . $lang ['blog_search_1'] . ' <a href="search.php">' . $lang ['blog_search_2'] . '</a> ' . $lang ['start_page_1'] . ' <a href="index.php">' . $lang ['start_page_2'] . '</a> .</span></small></p>' . //
		'</body>' . //
		'</html>';
}

function error_404($langId, $lang, $downloadFile) {
	header('HTTP/1.0 404 Not Found');
	header('Content-type: text/html; charset=utf-8');
	echo '<!DOCTYPE HTML>' . //
		'<html xmlns="http://www.w3.org/1999/xhtml" lang="' . $langId . '">' . //
		'<head><title>' . $lang ['error_404'] . '</title></head>' . //
		'<body>' . //
		'<h1>' . $lang ['error_404'] . '</h1>' . //
		'<p><strong>' . $lang ['not_found'] . '<br>' . $lang ['file'] . ': ' . //
		'<span style="color:#FF0000">' . htmlspecialchars($downloadFile) . '</span>' . //
		'</strong></p>' . //
		'<p><small><span style="color:#9c9c9c">' . $lang ['report_error_1'] . ' <a href="contact.php">' . $lang ['report_error_2'] . '</a>, ' . $lang ['blog_search_1'] . ' <a href="search.php">' . $lang ['blog_search_2'] . '</a> ' . $lang ['start_page_1'] . ' <a href="index.php">' . $lang ['start_page_2'] . '</a> .</span></small></p>' . //
		'</body>' . //
		'</html>';
}
?>
