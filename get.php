<?php
/*
 * Plugin Name: BBcode
 * Module: get.php
 * Function: BBCODE_USE_FILEWRAPPER
 * Purpose: Mask the path of the attachs dir
 * Change-Date: 24.08.2024, by FKM
 */
require_once 'defaults.php';

// load language file
require_once CONFIG_DIR . 'settings.conf.php';
$langId = $fp_config ['locale'] ['lang'];
$langFile = ABS_PATH . PLUGINS_DIR . 'bbcode/lang/lang.' . $langId . '.php';

if (!file_exists($langFile)) {
	$langFile = ABS_PATH . PLUGINS_DIR . 'bbcode/lang/lang.en-us.php';
	$langId =  'en-us';
}
require_once $langFile;

$lang = $lang ['plugin'] ['bbcode'];

/*
 * Getfile main part
 */
$downloadFile = urldecode($_GET ["f"]);

// make sure the files outside of the download directory are not accessed
if (startsWith($downloadFile, '..') || startsWith($downloadFile, '/') || substr_count($downloadFile, '..') > 0) {
	error_403($downloadFile);
	die;
}

// files are assumed to be in fp-content/attachs directory
$download = ABS_PATH . ATTACHS_DIR . $downloadFile;

if (file_exists($download)) {

	// if the file cannot be read show 403 error
	if (!is_readable($download)) {
		error_403($langId, $lang, $downloadFile);
		die;
	}

	// set some response headers to indicate that a file is being sent
	header('Content-type: application/force-download');
	header('Content-disposition: attachment; filename=' . basename($downloadFile));
	header('Content-Transfer-Encoding: Binary');
	header('Content-length: ' . filesize($download));

readfile($download); // send file to browser

} else {
	error_404($langId, $lang, $downloadFile);
	die;
}

function startsWith($haystack, $needle) {
	return !strncmp($haystack, $needle, strlen($needle));
}

function error_403($langId, $lang, $downloadFile) {
	header('HTTP/1.0 403 Forbidden');
	header('Content-type: text/html; charset=utf-8');
	echo '<!DOCTYPE HTML>' . //
		'<html xmlns="http://www.w3.org/1999/xhtml" lang="' . $langId . '">' . //
		'<head><title>' . $lang ['error_403'] . '</title></head>' . //
		'<body>' . //
		'<h1>' . $lang ['error_403'] . '</h1>' . //
		'<p><strong>' . $lang ['not_send'] . '<br>' . $lang ['file'] . ': ' . //
		'<span style="color:#FF0000">' . $downloadFile . '</span>' . //
		'</strong></p>' . //
		'<p><small><span style="color:#9c9c9c">' . $lang ['report_error_1'] . ' <a href="contact.php">' . $lang ['report_error_2'] . '</a>, ' . $lang ['blog_search_1'] . ' <a href="search.php">' . $lang ['blog_search_2'] . '</a> ' . $lang ['start_page_1'] . ' <a href="index.php">' . $lang ['start_page_2'] . '</a> .</span></small></p>' . //
		'</body>' . //
		'</html>';
}

function error_404($langId, $lang, $downloadFile,) {
	header('HTTP/1.0 404 Not Found');
	header('Content-type: text/html; charset=utf-8');
	echo '<!DOCTYPE HTML>' . //
		'<html xmlns="http://www.w3.org/1999/xhtml" lang="' . $langId . '">' . //
		'<head><title>' . $lang ['error_404'] . '</title></head>' . //
		'<body>' . //
		'<h1>' . $lang ['error_404'] . '</h1>' . //
		'<p><strong>' . $lang ['not_found'] . '<br>' . $lang ['file'] . ': ' . //
		'<span style="color:#FF0000">' . $downloadFile . '</span>' . //
		'</strong></p>' . //
		'<p><small><span style="color:#9c9c9c">' . $lang ['report_error_1'] . ' <a href="contact.php">' . $lang ['report_error_2'] . '</a>, ' . $lang ['blog_search_1'] . ' <a href="search.php">' . $lang ['blog_search_2'] . '</a> ' . $lang ['start_page_1'] . ' <a href="index.php">' . $lang ['start_page_2'] . '</a> .</span></small></p>' . //
		'</body>' . //
		'</html>';
}
?>
