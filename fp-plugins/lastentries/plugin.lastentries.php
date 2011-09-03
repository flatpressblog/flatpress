<?php
/*
Plugin Name: LastEntries
Plugin URI: http://www.nowhereland.it/
Type: Block
Description: LastEntries plugin. Part of the standard distribution ;)
Author: NoWhereMan
Version: 1.0
Author URI: http://www.nowhereland.it/
*/ 

function plugin_lastentries_widget() {
	
	global $fpdb;
	
	// load plugin strings
	// they're located under plugin.PLUGINNAME/lang/LANGID/
	$lang = lang_load('plugin:lastentries');
	
	$num = 10;
	####################
	
	/*
	$queryId = $fpdb->query("fullparse:false,start:0,count:$num");
	$fpdb->doquery($queryId);
	
	$fpdb->getQuery
	*/
	
	$q = new FPDB_Query(array('fullparse'=>false,'start'=>0,'count'=>$num), null);
	
	$string = '<ul>';
	
	
	$count = 0;
	
	while ($q->hasmore()) {
		
		list($id, $entry) = $q->getEntry();
		
		$link = get_permalink($id);
			
		$string .='<li>';
		$admin = BLOG_BASEURL . "admin.php?p=entry&amp;entry=";
		if (user_loggedin()) // if loggedin prints a "edit" link
			$string .= "<a href=\"{$admin}{$id}\">[".$lang['plugin']['lastentries']['edit']."]</a>";
		$string .= "<a href=\"{$link}\">{$entry['subject']}</a></li>\n";

		$count++;
	}
	
	if ($string == '<ul>'){
		$string .= '<li><a href="admin.php?p=entry&amp;action=write">'.$lang['plugin']['lastentries']['add_entry'].'</a></li>';
		$subject = $lang['plugin']['lastentries']['no_entries'];
	} else $subject = $lang['plugin']['lastentries']['subject_before_count'] . $count .  $lang['plugin']['lastentries']['subject_after_count'];	
	
	$string .= '</ul>';

	$widget = array();
	$widget['subject'] = $subject;
	$widget['content'] = $string;
	
	return $widget;
}

register_widget('lastentries', 'LastEntries', 'plugin_lastentries_widget');

?>
