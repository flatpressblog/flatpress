<?php
/**
 * Plugin Name: Media Manager
 * Version: 2.0.2
 * Plugin URI: https://www.flatpress.org
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 * Description: Manage uploaded files and photo galleries. Part of the standard distribution.
 */

// FIXME: Add a config option in the plugin panel to set this value
define('ITEMSPERPAGE', 50);

require_once plugin_getdir('mediamanager') . '/inc/usage-index.php';

if (class_exists('AdminPanelAction')) {
	include (plugin_getdir('mediamanager') . '/panels/panel.mediamanager.file.php');
}

// Maintain the regenerable Media Manager usage index only after successful
// entry commits. Preview/content_save_pre paths must not invalidate it.
add_action('entry_saved', 'mediamanager_usage_on_entry_saved', 10, 4);
add_action('entry_deleted', 'mediamanager_usage_on_entry_deleted', 10, 2);
?>
