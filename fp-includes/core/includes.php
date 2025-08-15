<?php
// includes.php
// This is just a list of all the standard includes
require_once INCLUDES_DIR . 'core.smarty.php';
require_once INCLUDES_DIR . 'core.utils.php';

// Smarty 5 without Composer: Load PSR-4 stub – automatically finds/pulls the stub
utils_checksmarty('5.5.1');

// Namespace class in v5
$smarty = new \Smarty\Smarty();

$_FP_SMARTY = &$smarty;

// FlatPress custom resources: Classes are NOT located in the Smarty namespace -> load explicitly
require_once FP_SMARTYPLUGINS_DIR . 'resource.admin.php';
require_once FP_SMARTYPLUGINS_DIR . 'resource.plugin.php';
require_once FP_SMARTYPLUGINS_DIR . 'resource.shared.php';

// In Smarty 5, registration is done exclusively via objects.
$smarty->registerResource('admin',  new \Smarty_Resource_Admin());
$smarty->registerResource('plugin', new \Smarty_Resource_Plugin());
$smarty->registerResource('shared', new \Smarty_Resource_Shared());

// Register FlatPress-specific Smarty plugins explicitly (Smarty 5; no addPluginsDir)
fp_register_fp_plugins($smarty, FP_SMARTYPLUGINS_DIR);

$includes = [
	// WordPress plugin system
	'core.wp-plugin-interface.php',
	'core.wp-functions.php',
	// 'core.wp-options.php',
	'core.wp-formatting.php',
	'core.wp-default-filters.php',

	'core.filesystem.php',
	'core.fileio.php',
	'core.cache.php',
	'core.blogdb.php',
	'core.bplustree.class.php',

	'core.administration.php',
	'core.widgets.php',
	'core.comment.php',
	'core.config.php',
	'core.date.php',
	'core.entry.php',
	'core.static.php',
	'core.draft.php',

	'core.fpdb.class.php',

	'core.language.php',
	'core.plugins.php',
	'core.cookie.php',
	'core.system.php',
	'core.theme.php',
	// 'core.layout.php',
	'core.users.php',
	'core.gallery.php',
];

foreach ($includes as $file) {
	require_once INCLUDES_DIR . $file;
}
?>
