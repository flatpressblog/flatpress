<?php
/**
 * Plugin Name: Gallery captions
 * Version: 1.0.1
 * Plugin URI: https://www.flatpress.org
 * Description: Manages image captions for gallery images; part of the standard distribution.
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 */

// The minimum FlatPress version required
define('PLUGIN_GALLERYCAPTIONS_MINFPVERSION', '1.3');

// Cleanup and comparison of versions
function checkFlatPressVersion($minVersion) {

	if (!defined('SYSTEM_VER')) {
		return false;
	}

	// SYSTEM_VER cleanup (e.g. "1.4-dev" -> "1.4")
	preg_match('/^(\d+\.\d+)/', SYSTEM_VER, $matches);
	if (!isset($matches [1])) {
		// No suitable version found
		return false;
	}
	$cleanVersion = $matches [1];

	// Comparison: Current version >= minimum version?
	return version_compare($cleanVersion, $minVersion, '>=');
}

// Check that the FlatPress version is correct, the class AdminPanelAction and that core.gallery.php is available
if (checkFlatPressVersion(PLUGIN_GALLERYCAPTIONS_MINFPVERSION) && function_exists('gallery_read_images') && class_exists('AdminPanelAction')) {
	require_once (dirname(__FILE__) . '/admin_uploader_gallerycaptions.class.php');
} else {
	if (class_exists('AdminPanelAction')) {
		global $smarty;
		$smarty->append('error', 'Sorry, you need FlatPress version ' . PLUGIN_GALLERYCAPTIONS_MINFPVERSION . ' or later in order to run the Gallery captions plugin.');
	}
}
?>
