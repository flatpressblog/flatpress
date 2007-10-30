<?php

/*
Plugin Name: AdminArea
Plugin URI: http://www.nowhereland.it/
Type: Block
Description: AdminArea plugin. Part of the standard distribution ;)
Author: NoWhereMan
Version: 1.0
Author URI: http://www.nowhereland.it/
*/ 

function plugin_categories_widget() {
	
	global $smarty;
	
	// load plugin strings
	// they're located under plugin.PLUGINNAME/lang/LANGID/
	$lang = lang_load('plugin:categories');
	
	$entry['subject'] = $lang['plugin']['categories']['subject'];
	$entry['content'] = $smarty->fetch('plugin:categories/widget');

	return $entry;
}

register_widget('categories', 'Categories', 'plugin_categories_widget');

?>
