<?php
/**
 * This file integrates the plugin into the entry editor.
 */

if(!class_exists('plugin_commentcenter')) {
	die('Don\'t try to hack us.');
}

/**
 * This function is called by the simple_edit_form hook to add
 * the Commentcenter options on the entry editor.
 */
function plugin_commentcenter_editor() {
	// Just on existent entries
	if(empty($_REQUEST['entry'])) {
		return;
	}

	global $smarty, $lang, $action;
	$entry=$_REQUEST['entry'];
	$plugin=&$GLOBALS['plugin_commentcenter'];
	$plang=&$lang['admin']['entry']['commentcenter'];
	$arr=$smarty->get_template_vars('post');
	$panel_url=$smarty->get_template_vars('panel_url');

	$plugin->loadPolicies();
	$do=$plugin->behavoirFromPolicies($entry, @$arr['categories']);
	$do='simple_'.$do;

	$oldact=$action;
	$action='commentcenter';
	$policies=admin_filter_action($panel_url, 'commentcenter');
	$manage=admin_filter_command($policies, 'manage', $entry);
	$action=$oldact;

	echo "<fieldset id=\"commentcenter\">\n";
	echo "<legend>Comment Center</legend>\n<ul>\n<li>";
	echo "<a href=\"{$policies}\" title=\"{$plang['simple_edit']}\">";
	echo "{$plang['simple_pre']}{$plang[$do]}</a></li>\n";
	echo "<li><a href=\"{$manage}\">{$plang['simple_manage']}</a></li>\n";
	echo "\n</ul></fieldset>\n";
}
add_filter('simple_edit_form', 'plugin_commentcenter_editor');