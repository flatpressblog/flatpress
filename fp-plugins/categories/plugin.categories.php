<?php

/*
Plugin Name: Categories
Plugin URI: http://www.nowhereland.it/
Type: Block
Description: Lists your categories in a widget.
Author: NoWhereMan
Version: 1.0
Author URI: http://www.nowhereland.it/
*/ 

function plugin_categories_widget() {
	
	global $smarty;

	// set this to true if you want show the number
	// of categories for each category; please notice:
	// not cheap on the server, it should be cached
	// somewhere else
	
	// default: disabled
	
	$smarty->assign('categories_showcount', false);
	
	// load plugin strings
	// they're located under plugin.PLUGINNAME/lang/LANGID/
	$lang = lang_load('plugin:categories');
	
	$entry['subject'] = $lang['plugin']['categories']['subject'];
	$entry['content'] = $smarty->fetch('plugin:categories/widget');

	return $entry;
}

register_widget('categories', 'Categories', 'plugin_categories_widget');

?>
