<?php
/*
 * Plugin Name: Media Manager
 * Version: 1.0.1
 * Plugin URI: https://www.flatpress.org
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 * Description: Manage uploaded files and photo galleries. Part of the standard distribution.
 */

// FIXME: Add a config option in the plugin panel to set this value
define('ITEMSPERPAGE', 50);

function mediamanager_updateUseCountArr(&$files, $fupd) {
	$params = array();
	$params ['start'] = 0;
	$params ['count'] = -1;
	$params ['fullparse'] = true;
	$q = new FPDB_Query($params, null);

	while ($q->hasMore()) {
		list($entryId, $e) = $q->getEntry();
		if (!empty($e ['content'])) {
			foreach ($fupd as $fileId) {
				if (!isset($files [$fileId] ['usecount'])) {
					$files [$fileId] ['usecount'] = 0;
				}

				$searchterm = ($files [$fileId] ['type'] == 'gallery')
					? "[gallery=images/" . $files [$fileId] ['name']
					: $files [$fileId] ['type'] . "/" . $files [$fileId] ['name'];

				if (strpos($e ['content'], $searchterm) !== false) {
					$files [$fileId] ['usecount']++;
				}
			}
		}
	}

	$usecount = array();
	foreach ($files as $info) {
		if (isset($info ['name'], $info ['usecount'])) {
			$usecount [$info ['name']] = $info ['usecount'];
		}
	}

	if (!empty($usecount)) {
		plugin_addoption('mediamanager', 'usecount', $usecount);
		plugin_saveoptions('mediamanager');
	}
}

if (class_exists('AdminPanelAction')) {
	include (plugin_getdir('mediamanager') . '/panels/panel.mediamanager.file.php');
}

/* invalidate count on entry save and delete */
function mediamanager_invalidatecount($arg) {
	plugin_addoption('mediamanager', 'usecount', array());
	plugin_saveoptions('mediamanager');
	return $arg;
}
add_filter('delete_post', 'mediamanager_invalidatecount', 1);
add_filter('content_save_pre', 'mediamanager_invalidatecount', 1);
