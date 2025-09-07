<?php
// includes.php
// This is just a list of all the standard includes
require_once INCLUDES_DIR . 'core.filesystem.php';
require_once INCLUDES_DIR . 'core.fileio.php';
require_once INCLUDES_DIR . 'core.smarty.php';

// Smarty without Composer: Load PSR-4 stub – automatically finds/fetches the latest stub
utils_checksmarty();

// Namespace class
$smarty = new \Smarty\Smarty();

// Legacy alias for historical plugins/core code.
$_FP_SMARTY = &$smarty;

// Register FlatPress-specific Smarty plugins explicitly (Smarty 5; no addPluginsDir)
fp_register_fp_plugins($smarty, FP_SMARTYPLUGINS_DIR);

$includes = [
	// WordPress plugin system
	'core.wp-plugin-interface.php',
	'core.wp-functions.php',
	// 'core.wp-options.php',
	'core.wp-formatting.php',
	'core.wp-default-filters.php',

	'core.utils.php',
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
