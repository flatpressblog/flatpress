<?php
// includes.php
// This is just a list of all the standard includes
require_once INCLUDES_DIR . 'core.utils.php';

// Smarty initialisieren
utils_checksmarty();
require_once SMARTY_DIR . 'Smarty.class.php';
$smarty = new Smarty();
$_FP_SMARTY = &$smarty;

// Add plugin dir for FlatPress-specific Smarty plugins
$smarty->addPluginsDir(FP_SMARTYPLUGINS_DIR);

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
