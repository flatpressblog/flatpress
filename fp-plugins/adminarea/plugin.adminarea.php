<?php
/*
Plugin Name: AdminArea
Plugin URI: http://www.nowhereland.it/
Description: AdminArea plugin. Part of the standard distribution ;)
Author: NoWhereMan
Version: 1.0
Author URI: http://www.nowhereland.it/
*/ 

function plugin_adminarea_widget() {

	// load plugin strings
	// they're located under plugin.PLUGINNAME/lang/LANGID/
	$lang = lang_load('plugin:adminarea');
	$baseurl = BLOG_BASEURL;
	
	if ($user = user_loggedin()) {
		$userid = $user['userid'];
		$string = <<<END
		<p>{$lang['plugin']['adminarea']['welcome']} <strong>{$userid}</strong> !</p>
		<ul>
		<li><a href="{$baseurl}admin.php">{$lang['plugin']['adminarea']['admin_panel']}</a></li>
		<li><a href="{$baseurl}admin.php?p=entry&amp;action=write">{$lang['plugin']['adminarea']['add_entry']}</a></li>
		<li><a href="{$baseurl}login.php?do=logout">{$lang['plugin']['adminarea']['logout']}</a></li>
		</ul>
END;
	} else
		$string = '<ul><li><a href="'.$baseurl.'login.php">Login</a></li></ul>';
	
	$entry['subject'] = $lang['plugin']['adminarea']['subject'];
	$entry['content'] = $string;

	return $entry;
}

register_widget('adminarea', 'AdminArea', 'plugin_adminarea_widget');

?>
