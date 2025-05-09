<?php
/*
 * Plugin Name: SearchBox
 * Version: 1.0
 * Plugin URI: https://www.flatpress.org
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 * Description: Adds a search box widget. Part of the standard distribution.
 */

/**
 * If not defined, default to false.
 */
if (!defined('SEARCHBOX_BIG')) {
	define('SEARCHBOX_BIG', false);
}

/**
 * Generate the search box widget HTML
 *
 * @return array{
 *     subject: string,
 *     content: string
 * }
 */
function plugin_searchbox_widget() {
	global $lang;

	$url = BLOG_BASEURL . 'search.php';

	$content = '<ul><li>
		<form method="get" action="' . $url . '">
		<input type="hidden" name="stype" value="full">';

	if (SEARCHBOX_BIG) {
		$content .= "<p><a href=\"" . $url . "\">" . $lang ['search'] ['moreopts'] . "</a></p>";
	}

	$content .= '<p><input type="text" name="q"></p>';

	if (SEARCHBOX_BIG) {
		$content .= '<p><label><input type="radio" ' . //
					'name="stype" value="titles" checked="checked">' . $lang ['search'] ['onlytitles'] . '</label><br>' . '<label><input type="radio" name="stype" value="full">' . $lang ['search'] ['fulltext'] . '</label></p>';
	}

	$content .= '<div class="buttonbar"><p><input name="search" type="submit" value="' . $lang ['search'] ['submit'] . '"> </p></div>
		</form>
		</li></ul>';

	return array(
		'subject' => $lang ['search'] ['head'],
		'content' => $content
	);
}

register_widget('searchbox', 'SearchBox', 'plugin_searchbox_widget');

?>
