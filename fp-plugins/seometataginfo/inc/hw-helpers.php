<?php
if (!function_exists('is_single')) {

	function is_single() {
		global $fp_params;
		return (!empty($fp_params ['entry']));
	}
}

if (!function_exists('is_comments')) {

	function is_comments() {
		global $fp_params;
		return (isset($fp_params ['comments']));
	}
}

if (!function_exists('is_static')) {

	function is_static() {
		global $fp_params, $fp_config;
		return (!empty($fp_params ['page']) || (empty($fp_params) && !empty($fp_config ['general'] ['startpage'])));
	}
}

if (!function_exists('is_static_home')) {

	function is_static_home() {
		global $fp_params, $fp_config;
		return ((empty($fp_params ['page']) && empty($fp_params) && !empty($fp_config ['general'] ['startpage'])) || (!empty($fp_params ['page']) && !empty($fp_config ['general'] ['startpage']) && $fp_params ['page'] === $fp_config ['general'] ['startpage']));
	}
}

if (!function_exists('is_blog_home')) {

	function is_blog_home() {
		global $fp_params, $fp_config;
		return ((count(array_filter($fp_params)) === 1 && !empty($fp_params ['paged']) && $fp_params ['paged'] == 1) || (empty($fp_params) && empty($fp_config ['general'] ['startpage'])));
	}
}

if (!function_exists('is_blog_page')) {

	function is_blog_page() {
		global $fp_params, $fp_config;
		return ((count(array_filter($fp_params)) === 1 && !empty($fp_params ['paged']) && $fp_params ['paged'] >= 1) || (empty($fp_params) && empty($fp_config ['general'] ['startpage'])));
	}
}

if (!function_exists('is_paging')) {

	function is_paging() {
		global $fp_params;
		return (!empty($fp_params ['paged']) && $fp_params ['paged'] >= 2);
	}
}

if (!function_exists('is_category')) {

	function is_category() {
		global $fp_params;
		return (!empty($fp_params ['cat']) && !is_tag());
	}
}

if (!function_exists('is_tag')) {

	function is_tag() {
		global $fp_params;
		return (!empty($fp_params ['tag']));
	}
}

if (!function_exists('is_feed')) {

	function is_feed() {
		global $fp_params;
		return (!empty($fp_params ['feed']));
	}
}

if (!function_exists('is_search')) {

	function is_search() {
		global $fp_params;
		return (!empty($_GET ['q']));
	}
}

if (!function_exists('is_contact')) {

	function is_contact() {
		global $smarty;
		// check if contact form
		$scriptName = $smarty->getTemplateVars('SCRIPT_NAME');
		$result = (!empty($scriptName) && strpos($scriptName, 'contact.php'));
		return $result;
	}
}

if (!function_exists('is_archive')) {

	function is_archive() {
		global $fp_params;
		return (!is_single() && !is_static() && !is_category() && !is_tag() && (!empty($fp_params ['y']) || !empty($fp_params ['m']) || !empty($fp_params ['d'])));
	}
}

if (!function_exists('is_archive_year')) {

	function is_archive_year() {
		global $fp_params;
		return (is_archive() && (!empty($fp_params ['y']) && (empty($fp_params ['m']) && empty($fp_params ['d']))));
	}
}
if (!function_exists('is_archive_month')) {

	function is_archive_month() {
		global $fp_params;
		return (is_archive() && ((!empty($fp_params ['y']) && !empty($fp_params ['m'])) && empty($fp_params ['d'])));
	}
}
if (!function_exists('is_archive_day')) {

	function is_archive_day() {
		global $fp_params;
		return (is_archive() && (!empty($fp_params ['y']) && !empty($fp_params ['m']) && !empty($fp_params ['d'])));
	}
}

if (!function_exists('get_category_name')) {

	function get_category_name($catid) {
		$category_names = entry_categories_get('defs');
		return (!empty($category_names [$catid]) ? $category_names [$catid] : "");
	}
}

if (!function_exists('pathinfo_filename')) {

	function pathinfo_filename($file) { // file.name.ext, returns file.name
		if (defined('PATHINFO_FILENAME')) {
			return pathinfo($file, PATHINFO_FILENAME);
		}
		if (strstr($file, '.')) {
			return substr($file, 0, strrpos($file, '.'));
		}
	}
}

if (!function_exists('currentPageURL')) {

	function currentPageURL() {

		$protocol = is_https() ? "https" : "http";

		$port = $_SERVER ['SERVER_PORT'];

		if (!empty($_SERVER ['HTTP_X_FORWARDED_PORT'])) {
			$port = $_SERVER ['HTTP_X_FORWARDED_PORT'];
		}

		$portString = (!in_array($port, ["80", "443"])) ? ":" . $port : "";

		$curpageURL = $protocol . "://" . $_SERVER ["SERVER_NAME"] . $portString . $_SERVER ["REQUEST_URI"];

		return $curpageURL;
	}
}

// removes files and non-empty directories
if (!function_exists('rrmdir')) {

	function rrmdir($dir) {
		if (is_dir($dir)) {
			$files = scandir($dir);
			foreach ($files as $file) {
				if ($file != "." && $file != "..") {
					rrmdir($dir . "/" . $file);
				}
			}
			rmdir($dir);
		} else if (file_exists($dir)) {
			unlink($dir);
		}
	}
}
// copies files and non-empty directories
if (!function_exists('rcopy')) {

	function rcopy($src, $dst) {
		if (file_exists($dst)) {
			rrmdir($dst);
		}
		if (is_dir($src)) {
			mkdir($dst);
			$files = scandir($src);
			foreach ($files as $file) {
				if ($file != "." && $file != "..") {
					rcopy($src . "/" . $file, $dst . "/" . $file);
				}
			}
		} else if (file_exists($src)) {
			copy($src, $dst);
		}
	}
}

function is_empty_dir($dir) {
	if ($dh = @opendir($dir)) {
		while ($file = readdir($dh)) {
			if ($file != '.' && $file != '..') {
				closedir($dh);
				return false;
			}
		}
		closedir($dh);
		return true;
	} else {
		return false; // whatever the reason is : no such dir, not a dir, not readable
	}
}

// debug
if (!function_exists('echoPre')) {

	function echoPre($value, $print = true) {
		$output = '';
		if ($value) {
			$output .= "<pre>";
			$output .= print_r($value, true);
			$output .= "</pre><br>";
			if ($print) {
				echo $output;
			} else {
				return $output;
			}
		}
		return false;
	}
}

?>
