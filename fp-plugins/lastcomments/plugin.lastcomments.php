<?php
/*
 * Plugin Name: LastComments
 * Type: Block
 * Version: 1.1.0
 * Plugin URI: https://www.flatpress.org
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 * Description: Adds a widget, RSS and Atom feed that displays the latest comments. Part of the standard distribution.
 */
define('LASTCOMMENTS_CACHE_FILE', CACHE_DIR . 'lastcomments.tmp');
define('LASTCOMMENTS_MAX', 8);

add_action('comment_post', 'plugin_lastcomments_cache', 0, 2);

function plugin_lastcomments_widget() {
	if (false === ($f = io_load_file(LASTCOMMENTS_CACHE_FILE)) || empty(unserialize($f))) {
		// No comments in cache
		$list = array();
	} else {
		// If file exists and its correctly read, we get the stored list
		// (it is stored in encoded form)
		$list = unserialize($f);
	}

	$content = '<ul class="last-comments">';

	// Add string translation

	// Load plugin strings
	// They're located under plugin.PLUGINNAME/lang/LANGID/
	$lang = lang_load('plugin:lastcomments');

	$update = false;

	if ($count = count($list)) {
		while ($arr = array_pop($list)) {
			$q = new FPDB_Query(array(
				'id' => $arr ['entry']
			), null);
			// First element of the array is dropped, as it is the ID, which
			// we already know
			@list (, $entry) = $q->getEntry();

			if (!$entry) {
				$count--;
				$update = true;
				continue;
			}

			$content .= "<li>
				<blockquote class=\"comment-quote\" cite=\"comments.php?entry=" . $arr ['entry'] . "#" . $arr ['id'] . "\">
				" . $arr ['content'] . "
				<p><a href=\"" . get_comments_link($arr ['entry']) . "#" . $arr ['id'] . "\">" . $arr ['name'] . " - " . $entry ['subject'] . "</a></p>
				</blockquote></li>\n";
		}
		$subject = $lang ['plugin'] ['lastcomments'] ['last'] . ' ' . $count . ' ' . $lang ['plugin'] ['lastcomments'] ['comments'];
	}

	if (!$count) {
		if ($update) {
			fs_delete(LASTCOMMENTS_CACHE_FILE);
		}
		$content .= '<li>' . $lang ['plugin'] ['lastcomments'] ['no_comments'] . '</li>';
		$subject = $lang ['plugin'] ['lastcomments'] ['no_new_comments'];
	}

	$content .= '</ul>';

	$entry ['subject'] = $subject;
	$entry ['content'] = $content;

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
 * @param $entryid string
 *			entry id i.e. entryNNNNNN-NNNNNN
 * @param $comment array
 *			where $comment[0] is $commentid i.e. commentNNNNNN-NNNNNN
 *			and $comment[1] is the actual content array
 */
function plugin_lastcomments_cache($entryid, $comment) {

	// Max num of chars per comment
	$CHOP_AT = 32;

	list ($id, $content) = $comment;

	comment_clean($content);

	if (false === ($f = io_load_file(LASTCOMMENTS_CACHE_FILE))) {
		// No comments in cache
		$list = array();
	} else {
		// If file exists and its correctly read, we get the stored list
		// (it is stored in encoded form)
		$list = unserialize($f);

		if (count($list) + 1 > LASTCOMMENTS_MAX) {
			// Comments are more than allowed maximum:
			// we delete the last in queue.
			array_shift($list);
		}
	}

	if (strlen($content ['content']) > $CHOP_AT) {
		$string = substr($content ['content'], 0, $CHOP_AT) . ' [...]';
	} else {
		$string = $content ['content'];
	}

	array_push($list, array(
		'name' => $content ['name'],
		'email' => $content ['email'] ?? 'john@doe.org',
		'content' => $string,
		'id' => $id,
		'entry' => $entryid,
		'date' => $content ['date'] ?? time()
	));

	return io_write_file(LASTCOMMENTS_CACHE_FILE, serialize($list));
}

register_widget('lastcomments', 'LastComments', 'plugin_lastcomments_widget');

/**
 * Function: plugin_lastcomments_rss
 *
 * Prepares the list of the latest comments to be used in RSS or Atom feeds.
 *
 * This function retrieves comments from a cache file. If the cache is unavailable
 * or corrupted, it falls back to the `comment_parse` function to retrieve the
 * required data. The retrieved data is then processed and assigned to the
 * Smarty variable `lastcomments_list`, which is used in feed templates.
 *
 * Returns:
 *   bool: Indicates whether comments were successfully retrieved and assigned.
 */
function plugin_lastcomments_rss() {
	global $smarty, $fp_config;

	if (false === ($f = io_load_file(LASTCOMMENTS_CACHE_FILE))) {
		$list = array();
	} else {
		$list = unserialize($f);
		if (!$list || !is_array($list)) {
			$list = array();
		}
	}

	$newlist = array();
	foreach ($list as $c) {
		if (!isset($c ['name']) || !isset($c ['content']) || !isset($c ['date'])) {
			// Fallback if data is missing in the cache
			$c = comment_parse($c ['entry'], $c ['id']); 
		}

		if ($c) {
			$c ['link'] = get_comments_link($c ['entry']);
			$c ['date'] = date('r', $c ['date']);

			$newlist [] = $c;
		}
	}

	$smarty->assign('lastcomments_list', $newlist);

	// Return whether comments are found
	return !empty($newlist);
}

function plugin_lastcomments_rss_link() {
	return apply_filters('plugin_lastcomments_rss_link', plugin_lastcomments_def_rss_link());
}

function plugin_lastcomments_def_rss_link() {
	return BLOG_BASEURL . '?feed=lastcomments-rss2';
}

function plugin_lastcomments_atom_link() {
	return apply_filters('plugin_lastcomments_atom_link', plugin_lastcomments_def_atom_link());
}

function plugin_lastcomments_def_atom_link() {
	return BLOG_BASEURL . '?feed=lastcomments-atom';
}

add_action('wp_head', 'plugin_lastcomments_rsshead');

function plugin_lastcomments_rsshead() {
	if (plugin_lastcomments_rss()) {
		echo '
			<link rel="alternate" type="application/rss+xml" title="Get last Comments RSS 2.0 Feed" href="' . plugin_lastcomments_rss_link() . '">' . //
			'
			<link rel="alternate" type="application/rss+xml" title="Get last Comments Atom Feed" href="' . plugin_lastcomments_atom_link() . '">
		';
	}
}

add_action('init', 'plugin_lastcomments_rssinit');

/**
 * Function: plugin_lastcomments_rssinit
 *
 * Handles the initialization of RSS and Atom feeds for the latest comments.
 *
 * This function checks the `$_GET['feed']` parameter to determine if an RSS or Atom feed
 * is requested. If no comments are available, a 404 header is sent. Otherwise, the relevant
 * feed template is loaded and populated using Smarty with data from the comment cache.
 *
 * Feeds Supported:
 *   - RSS 2.0: Displayed using the `plugin.lastcomments-feed` template.
 *   - Atom: Displayed using the `plugin.lastcomments-atom` template.
 */
function plugin_lastcomments_rssinit() {
	global $smarty, $fp_config;

	if (isset($_GET ['feed']) && ($_GET ['feed'] == 'lastcomments-rss2' || $_GET ['feed'] == 'lastcomments-atom')) {

		if (!plugin_lastcomments_rss()) {
			// No comments available
			header("HTTP/1.0 404 Not Found");
			echo 'No comments available for the RSS feed.';
			exit();
		}

		$smarty->assign('fp_config', $fp_config);

		$smarty->assign('flatpress', array(
			'lang' => $fp_config ['locale'] ['lang'] ?? '',
			'title' => $fp_config ['general'] ['title'] ?? '',
			'www' => $fp_config ['general'] ['www'] ?? '',
			'subtitle' => $fp_config ['general'] ['subtitle'] ?? '',
			'author' => $fp_config ['general'] ['author'] ?? '',
			'email' => $fp_config ['general'] ['email'] ?? '',
		));

		$smarty->assign('rss_link', plugin_lastcomments_rss_link());
		$smarty->assign('atom_link', plugin_lastcomments_atom_link());

		$smarty->registerPlugin('modifier', 'date', 'date');
		$smarty->registerPlugin('modifier', 'date_rfc3339', 'theme_smarty_modifier_date_rfc3339');
		$smarty->registerPlugin('modifier', 'cmnt', 'smarty_modifier_cmnt');

		if ($_GET['feed'] == 'lastcomments-rss2') {
			$smarty->display('plugin:lastcomments/plugin.lastcomments-feed');
		} elseif ($_GET['feed'] == 'lastcomments-atom') {
			$smarty->display('plugin:lastcomments/plugin.lastcomments-atom');
		}

		exit();
	}
}

/**
 * Function: smarty_modifier_cmnt
 *
 * A custom Smarty modifier used to modify or transform specific data within the template.
 *
 * This function allows applying transformations to template variables, such as generating
 * comment links. The modifier is registered globally in Smarty and can be used in feed
 * templates to dynamically generate URLs or apply other operations.
 *
 * Parameters:
 *   - $string: The input string or data.
 *   - $modifier_name: The name of the modifier to apply. For example, 'comments_link'.
 *
 * Returns:
 *   - Transformed string based on the specified modifier.
 */
function smarty_modifier_cmnt($string, $modifier_name) {
	if ($modifier_name === 'comments_link') {
		return get_comments_link($string);
	}
	return $string;
}
?>
