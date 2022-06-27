<?php

/**
 * Plugin Name: Gallery captions
 * Version: 1.0
 * Plugin URI: https://www.flatpress.org
 * Description: Manages image captions for gallery images; part of the standard distribution.
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 */

// the minimum FlatPress version required
const PLUGIN_GALLERYCAPTIONS_MINFPVERSION = '1.3';

// include the plugin's PHP files
if (class_exists('AdminPanelAction')) {
	include_once dirname(__FILE__) . '/admin_uploader_gallerycaptions.class.php';

	// check if FP instance has the gallery functions
	if (!function_exists('gallery_read_images')) {
		global $smarty;
		$smarty->append('error', 'Sorry, you need FlatPress version ' . PLUGIN_GALLERYCAPTIONS_MINFPVERSION . ' or later in order to run the Gallery captions plugin.');
		// FIXME: Deactivate plugin
	}
}
