<?php
/**
 * Plugin Name: LastComments
 * Type: Block
 * Version: 1.1.2
 * Plugin URI: https://www.flatpress.org
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 * Description: Adds a widget, RSS and Atom feed that displays the latest comments. Part of the standard distribution.
 */
define('LASTCOMMENTS_CACHE_FILE', CACHE_DIR . 'lastcomments.tmp');
define('LASTCOMMENTS_MAX', 8);
define('LASTCOMMENTS_TRUNCATE_LENGTH', 64);

/**
 * Function: plugin_lastcomments_widget
 *
 * Purpose:
 * This function generates the "Last Comments" widget for the FlatPress plugin.
 * It retrieves a list of recent comments from a cache file and builds an HTML list for display.
 * The function also handles updates to the cache file that tracks the count of comments, minimizing
 * unnecessary writes by only updating when the count changes.
 *
 * Workflow:
 * 1. Loads the cached comment count from `lastcomments_count.tmp`.
 * 2. Reads and unserializes the list of cached comments from `LASTCOMMENTS_CACHE_FILE`.
 * 3. Initializes the HTML content for the "Last Comments" widget.
 * 4. Iterates through the list of comments:
 *    - Queries the database for the corresponding entry.
 *    - Adds valid comments to the widget's HTML content.
 *    - Decrements the count for invalid or missing entries.
 * 5. If no comments are available, displays a "No Comments" message.
 * 6. Updates the `lastcomments_count.tmp` cache file only if the comment count has changed.
 *
 * Parameters:
 * None.
 *
 * Returns:
 * An array containing:
 * - `subject`: The widget's title.
 * - `content`: The HTML list of recent comments or a message indicating no comments.
 */
function plugin_lastcomments_widget() {

	$cache_file = CACHE_DIR . 'lastcomments_count.tmp';
	// Load the saved value of the comments from the cache file
	$cached_count = (int) io_load_file($cache_file);

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

	// Initialize number of last comments
	$count = count($list);

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

			// Truncate comment for widget display
			$truncatedContent = comment_for_widget($arr ['content'], LASTCOMMENTS_TRUNCATE_LENGTH);

			$content .= '<li>
				<blockquote class="comment-quote" cite="comments.php?entry=' . $arr ['entry'] . '#' . $arr ['id'] . '">' . //
				'<p><a href="' . get_comments_link($arr ['entry']) . '#' . $arr ['id'] . '">' . wp_specialchars($entry ['subject']) . '</a></p>' . //
				'<strong>' . wp_specialchars($arr ['name']) . ':</strong> ' . wp_specialchars($truncatedContent) . //
				'</blockquote></li>' . "\n";
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

	// Write number of comments only if the value has changed
	if ($count !== $cached_count) {
		io_write_file($cache_file, $count);
	}

	return $entry;
}

/**
 * Processes a comment for widget display, including truncation and BBCode conversion.
 *
 * @param string $content The raw comment content.
 * @param int $maxLength The maximum length for the visible text.
 * @return string The processed and truncated comment content.
 */
function comment_for_widget($content, $maxLength) {
	$cleanedText = clean_cmnt($content);
	return truncate_with_bbcode($cleanedText, $maxLength);
}

/**
 * Removes BBCode from text while preserving visible text and basic formatting.
 *
 * @param string $text The text containing BBCode.
 * @return string The cleaned text without BBCode and HTML tags.
 */
function clean_cmnt($text) {
	$text = remove_bb_code($text);
	return strip_tags($text);
}

function remove_bb_code($text) {
	// Remove all BBCode tags, except [...]
	$pattern = '/\[(?!\.\.\.)([^\]]+)\]/';
	return preg_replace($pattern, '', $text);
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
	$CHOP_AT = 64;

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

	array_push($list, array(
		'name' => $content ['name'],
		'email' => $content ['email'] ?? '',
		'url' => $content ['url'] ?? '',
		'content' => $content ['content'] ?? '',
		'id' => $id,
		'entry' => $entryid,
		'date' => $content ['date'] ?? time()
	));

	return io_write_file(LASTCOMMENTS_CACHE_FILE, serialize($list));
}

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
			$q = new FPDB_Query(array(
				'id' => $c ['entry']
			), null);
			@list (, $entry) = $q->getEntry();
			$c ['subject'] = $entry ['subject'] ?? 'Untitled Post';

			$c ['link'] = get_comments_link($c ['entry']);
			$c ['date'] = date('r', $c ['date']);

			$c ['formatted_date'] = strftime_replacement($fp_config ['locale'] ['dateformat'], $c ['date']);
			$c ['formatted_time'] = strftime_replacement($fp_config ['locale'] ['timeformat'], $c ['date']);

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

/**
 * Function: plugin_lastcomments_rsshead
 *
 * Purpose:
 * This function generates the `<link>` tags for the RSS 2.0 and Atom 1.0 feeds
 * of the "Last Comments" plugin. These tags are added to the `<head>` section
 * of the HTML output to provide links to the feeds for news aggregators or feed readers.
 * The function also dynamically includes the total number of recent comments in the feed title,
 * based on a cached count stored in `lastcomments_count.tmp`.
 *
 * Workflow:
 * 1. Loads the cached number of recent comments from the temporary file `lastcomments_count.tmp`.
 * 2. Retrieves the translated strings for the "Last Comments" plugin using `lang_load`.
 * 3. Calls `plugin_lastcomments_rss()` to ensure the RSS/Atom feed data is available.
 *    - If the feed data is not available, no `<link>` tags are output.
 * 4. Outputs `<link>` tags for both RSS 2.0 and Atom 1.0 feeds, dynamically including:
 *    - The number of recent comments (`$lastcomments_count`).
 *    - Translated feed titles.
 *    - Feed URLs.
 *
 * Parameters:
 * None.
 *
 * Notes:
 * - Relies on the existence of `lastcomments_count.tmp` for the cached comment count.
 * - The function uses `plugin_lastcomments_rss()` to check if feed data is available
 *   before adding the `<link>` tags.
 * - Requires translation strings for RSS and Atom feed titles to be defined
 *   under the "plugin:lastcomments" namespace.
 *
 */
function plugin_lastcomments_rsshead() {

	// Temporary file for the number of recent comments
	$cache_file = CACHE_DIR . 'lastcomments_count.tmp';
	// Read the number from the cache
	$lastcomments_count = (int) io_load_file($cache_file);

	$lang = lang_load('plugin:lastcomments');

	// Ugly solution for #517: Type checking and validation for language strings
	$last_key = $lang ['plugin'] ['lastcomments'] ['last'] ?? '';
	$comments_key = $lang ['plugin'] ['lastcomments'] ['comments'] ?? '';

	if (is_array($last_key)) {
		$last_key = implode(', ', $last_key);
	}

	if (is_array($comments_key)) {
		$comments_key = implode(', ', $comments_key);
	}

	if (plugin_lastcomments_rss()) {
		echo '
			<link rel="alternate" type="application/rss+xml" title="' . htmlspecialchars($last_key) . ' ' . $lastcomments_count . ' ' . htmlspecialchars($comments_key) . ' | RSS 2.0" href="' . plugin_lastcomments_rss_link() . '">' . //
			'
			<link rel="alternate" type="application/atom+xml" title="' . htmlspecialchars($last_key) . ' ' . $lastcomments_count . ' ' . htmlspecialchars($comments_key) . ' | Atom 1.0" href="' . plugin_lastcomments_atom_link() . '">
		';
	}
}

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

		$lang = lang_load('plugin:lastcomments');

		$cache_file = CACHE_DIR . 'lastcomments_count.tmp';
		$lastcomments_count = (int) io_load_file($cache_file);
		$dynamic_title = $lang ['plugin'] ['lastcomments'] ['last'] . ' ' . $lastcomments_count . ' ' . $lang ['plugin'] ['lastcomments'] ['comments'];

		// Register all Smarty modifier functions used by the feed-templates
		if (!isset($smarty->registered_plugins['modifier']['date'])) {
			$smarty->registerPlugin('modifier', 'date', 'date');
		}
		if (!isset($smarty->registered_plugins['modifier']['date_rfc3339'])) {
			$smarty->registerPlugin('modifier', 'date_rfc3339', 'theme_smarty_modifier_date_rfc3339');
		}
		if (!isset($smarty->registered_plugins['modifier']['fix_encoding_issues'])) {
			// This modifier converts characters such as Ã¤ to ä or &#8220; to “. See core.language.php
			$smarty->registerPlugin('modifier', 'fix_encoding_issues', 'fix_encoding_issues');
		}
		if (function_exists('BBCode')) {
			register_modifier_bbcode();
		}
		$smarty->registerPlugin('modifier', 'cmnt', 'smarty_modifier_cmnt');
		$smarty->registerPlugin('modifier', 'remove_bb_code', 'remove_bb_code');

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
		$smarty->assign('dynamic_title', $dynamic_title);

		if ($_GET ['feed'] == 'lastcomments-rss2') {
			header('Content-Type: application/rss+xml; charset=' . $fp_config ['locale'] ['charset']);
			$smarty->display('plugin:lastcomments/plugin.lastcomments-feed');
		} elseif ($_GET ['feed'] == 'lastcomments-atom') {
			header('Content-Type: application/atom+xml; charset=' . $fp_config ['locale'] ['charset']);
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

/**
 * Registers the BBCode modifier in Smarty.
 *
 * This function checks whether the `bbcode` modifier is already registered in the
 * global `$smarty` instance. If it is not registered, it ensures that the BBCode
 * parser is properly initialized and then registers the `BBCode` function as a 
 * Smarty modifier under the name `bbcode`.
 *
 * Usage:
 * - Call this function during plugin initialization or before rendering templates
 *   that require BBCode-to-HTML transformation.
 *
 * Important:
 * - The `ensure_bbcode_init()` function is called internally to ensure that the BBCode
 *   parser is initialized before registration.
 * - This function prevents duplicate registrations by checking the `registered_plugins`
 *   array in the global `$smarty` instance.
 */
function register_modifier_bbcode() {
	global $smarty;

	if (isset($smarty->registered_plugins['modifier']['bbcode'])) {
		return;
	}

	ensure_bbcode_init();
	$smarty->registerPlugin('modifier', 'bbcode', 'BBCode');
}

/**
 * Ensures the BBCode parser is properly initialized.
 *
 * This function checks whether the BBCode parser has already been initialized
 * by verifying the `BBCODE_INIT_DONE` constant. If not, it calls `plugin_bbcode_init()`
 * to initialize the parser.
 *
 * Usage:
 * - Call this function before attempting to use the BBCode parser or registering
 *   the BBCode modifier in Smarty.
 *
 * Important:
 * - This function does not return the BBCode parser instance. If you need the
 *   parser instance, you should directly call `plugin_bbcode_init()`.
 */
function ensure_bbcode_init() {
	if (!defined('BBCODE_INIT_DONE') || !BBCODE_INIT_DONE) {
		plugin_bbcode_init();
	}
}

/**
 * Shortens the visible text with BBCode and removes incomplete BBCode tags.
 *
 * @param string $content The complete text with BBCode and HTML.
 * @param int $maxLength Maximum visible length of the text.
 * @return string The shortened text with complete BBCode and HTML.
 */
function truncate_with_bbcode($content, $maxLength) {
	// Extract visible text without BBCode and HTML
	$plainText = strip_tags(remove_bb_code($content));

	// If visible text is longer than permitted, shorten it
	if (strlen($plainText) > $maxLength) {
		$visibleText = substr($plainText, 0, $maxLength);
		$htmlSafeContent = '';
		$visibleCharCount = 0;
		$insideTag = false;
		$insideBBCode = false;
		$bbcodeStack = [];

		// Run through the original text and build the shortened HTML/BBCode text
		for ($i = 0; $i < strlen($content); $i++) {
			$char = $content [$i];

			if ($char === '<') {
				// Start of an HTML tag
				$insideTag = true;
			} elseif ($char === '>') {
				// End of an HTML tag
				$insideTag = false;
			} elseif ($char === '[') {
				// Start of a BBCode tag
				$insideBBCode = true;
			} elseif ($char === ']') {
				// End of a BBCode tag
				$insideBBCode = false;

				// Process BBCode tag
				$bbcodeTag = substr($content, strrpos($htmlSafeContent, '['));
				if (strpos($bbcodeTag, '/') === 1) {
					// Closing tag, remove from stack
					$tag = trim($bbcodeTag, '[]/');
					if (($key = array_search($tag, $bbcodeStack)) !== false) {
						unset($bbcodeStack [$key]);
					}
				} else {
					// Opening tag, add to stack
					$tag = trim($bbcodeTag, '[]');
					$bbcodeStack [] = $tag;
				}
			}

			if (!$insideTag && !$insideBBCode && $char !== '<' && $char !== '>') {
				$visibleCharCount++;
			}

			$htmlSafeContent .= $char;

			// End the setup when visible characters reach the limit
			if ($visibleCharCount >= $maxLength) {
				break;
			}
		}

		// Remove incomplete BBCode tags
		foreach (array_reverse($bbcodeStack) as $unmatchedTag) {
			$htmlSafeContent = preg_replace('/\[' . preg_quote($unmatchedTag, '/') . '[^\]]*\]/', '', $htmlSafeContent);
		}

		return $htmlSafeContent . ' [...]';
	}

	// No shortening required
	return $content;
}

add_action('comment_post', 'plugin_lastcomments_cache', 0, 2);
add_action('init', 'plugin_lastcomments_rssinit');
add_action('wp_head', 'plugin_lastcomments_rsshead');
register_widget('lastcomments', 'LastComments', 'plugin_lastcomments_widget');
?>
