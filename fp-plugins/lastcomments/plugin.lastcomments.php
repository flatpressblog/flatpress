<?php
/*
Plugin Name: LastComments
Plugin URI: http://www.nowhereland.it/
Type: Block
Description: LastComments plugin. Part of the standard distribution ;)
Author: NoWhereMan
Version: 1.0
Author URI: http://www.nowhereland.it/
*/ 

define('LASTCOMMENTS_CACHE_FILE', CACHE_DIR  . 'lastcomments.tmp');
define('LASTCOMMENTS_MAX', 8);

add_action('comment_post', 'plugin_lastcomments_cache', 0, 2);

function plugin_lastcomments_widget() {

	if (false===($f = io_load_file(LASTCOMMENTS_CACHE_FILE))) {
		// no comments in cache
		$list = array();	
	} else {
		// if file exists and its correctly read, we get the stored list
		// (it is stored in encoded form)
		$list = unserialize($f);
	}
	
	$content = '<ul class="last-comments">';
			
	// cimangi Aggiunta traduzione stringhe

	// load plugin strings
	// they're located under plugin.PLUGINNAME/lang/LANGID/
	$lang = lang_load('plugin:lastcomments');

	if ($count = count($list)) {
		while ($arr = array_pop($list)) {
			theme_comments_filters($arr, $id);
			
			
			$q = new FPDB_Query(array('id' => $arr['entry']), null);
			// first element of the array is dropped, as it is the ID, which
			// we already know
			@list(, $entry) = $q->getEntry($query);
			
			if (!$entry){
				$count--;
					$update = true;
				continue;
			}

			$content .=	
			"<li>
			<blockquote class=\"comment-quote\" cite=\"comments.php?entry={$arr['entry']}#{$arr['id']}\">
			{$arr['content']}
			<p><a href=\"".get_comments_link($arr['entry']).
			"#{$arr['id']}\">{$arr['name']} - {$entry['subject']}</a></p>
			</blockquote></li>\n";
		}
		$subject = $lang['plugin']['lastcomments']['last'] . ' ' . $count . ' '. $lang['plugin']['lastcomments']['comments'];
	}
	
	if (!$count) {
		if ($update)
			fs_delete(LASTCOMMENTS_CACHE_FILE);
		$content .= '<li>' . $lang['plugin']['lastcomments']['no_comments'] .'</li>';
		$subject = $lang['plugin']['lastcomments']['no_new_comments'];
	}	
		
	$content .= '</ul>';

	$entry['subject'] = $subject;
	$entry['content'] = $content;
	
	return $entry;
}

/**
 * function plugin_lastcomments_cache
 * 
 * comment cache is a reverse queue; we put
 * element on the top, and we delete elements
 * from bottom; this is because the output
 * string is created reading queuing from top to bottom.
 * All this headache stuff just to say that
 * in the end the widget will show up elements ordered
 * from newer to older :P
 * 
 * @param $entryid string entry id i.e. entryNNNNNN-NNNNNN
 * @param $comment array where $comment[0] is $commentid i.e. commentNNNNNN-NNNNNN
 *			 and $comment[1] is the actual content array 
 */
 
 
function plugin_lastcomments_cache($entryid, $comment) {

	// max num of chars per comment
	$CHOP_AT = 30;
	

	list($id, $content) = $comment;
	
	comment_clean($content);
	
	
	if (false===($f = io_load_file(LASTCOMMENTS_CACHE_FILE))) {
		// no comments in cache
		$list = array();	
	} else {
		// if file exists and its correctly read, we get the stored list
		// (it is stored in encoded form)
		$list = unserialize($f);
		
		if (count($list)+1 > LASTCOMMENTS_MAX) {
			// comments are more than allowed maximum:
			// we delete the last in queue.  
			array_shift($list);
		}
	}
	
	if (strlen($content['content']) > $CHOP_AT) {		
		$string = substr($content['content'], 0, $CHOP_AT) . '...';
	} else {$string = $content['content'];}
	
	array_push($list, array('name'=>$content['name'],
					'content'=>$string, 
					'id'=> $id,
					'entry'=> $entryid)	
					);
	
	return io_write_file(LASTCOMMENTS_CACHE_FILE, serialize($list));
	
	
}

register_widget('lastcomments', 'LastComments', 'plugin_lastcomments_widget');

function plugin_lastcomments_rss () {
	global $smarty;
	
	
	if (false===($f = io_load_file(LASTCOMMENTS_CACHE_FILE))) {
		// no comments in cache
		$list = array();	
	} else {
		// if file exists and its correctly read, we get the stored list
		// (it is stored in encoded form)
		$list = unserialize($f);
	}
	
	$newlist = array();
	foreach($list as $c) {
		$newlist[] = comment_parse($list['entryid'], $list['id']);
	}
	
	$smarty->assign('lastcomments_list', $newlist);
}

/*

function plugin_lastcomments_def_rss_link() {
	return BLOG_BASEURL . "?feed=lastcomments-rss2";
}

function plugin_lastcomments_rss_link() {
	return apply_filters('plugin_lastcomments_rss_link', '');
}

add_action('wp_head', 'plugin_lastcomments_rsshead');
function plugin_lastcomments_rsshead() {
	echo "\n<link rel=\"alternate\" type=\"application/rss+xml\" title=\"Get RSS 2.0 Feed\" href=\"".
			plugin_lastcomments_rss_link() 
		."\" />\n";
}

add_action('init', 'plugin_lastcomments_rssinit');
function plugin_lastcomments_rssinit() {
	global $smarty;
	
	if (isset($_GET['feed']) && $_GET['feed']=='lastcomments-rss2') {
		$smarty->display('plugin:lastcomments/plugin.lastcomments-feed');
		exit();
	}
}

*/

?>
