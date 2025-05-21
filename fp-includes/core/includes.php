<?php

	// includes.php
	// this is just a list of all the standard includes
	
	require_once INCLUDES_DIR.'core.utils.php';
	utils_checksmarty();
	require(SMARTY_DIR . 'Smarty.class.php');
	$smarty = new Smarty;
	$_FP_SMARTY =& $smarty;


	// WordPress plugin system
	require_once INCLUDES_DIR.'core.wp-plugin-interface.php';
	require_once INCLUDES_DIR.'core.wp-functions.php';
	//require_once INCLUDES_DIR.'core.wp-options.php';
	require_once INCLUDES_DIR.'core.wp-formatting.php';
	require_once INCLUDES_DIR.'core.wp-default-filters.php';
	
	
	require_once INCLUDES_DIR.'core.filesystem.php';
	require_once INCLUDES_DIR.'core.fileio.php';
	require_once INCLUDES_DIR.'core.cache.php';
	require_once INCLUDES_DIR.'core.blogdb.php';
	require_once INCLUDES_DIR.'core.bplustree.class.php';


	require_once INCLUDES_DIR.'core.administration.php';
	require_once INCLUDES_DIR.'core.widgets.php';
	require_once INCLUDES_DIR.'core.comment.php';
	require_once INCLUDES_DIR.'core.config.php';
	require_once INCLUDES_DIR.'core.date.php';
	require_once INCLUDES_DIR.'core.entry.php';
	require_once INCLUDES_DIR.'core.static.php';
	require_once INCLUDES_DIR.'core.draft.php';
	
	require_once INCLUDES_DIR.'core.fpdb.class.php';
	
	require_once INCLUDES_DIR.'core.language.php';
	require_once INCLUDES_DIR.'core.plugins.php';
	require_once INCLUDES_DIR.'core.session.php';
	require_once INCLUDES_DIR.'core.cookie.php';
	require_once INCLUDES_DIR.'core.system.php';
	require_once INCLUDES_DIR.'core.theme.php';
	// require_once INCLUDES_DIR.'core.layout.php';
	require_once INCLUDES_DIR.'core.users.php';
	
?>
