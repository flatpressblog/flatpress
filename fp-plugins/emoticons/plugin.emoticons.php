<?php
/*
 * Plugin Name: Emoticons
 * Version: 1.1.0
 * Plugin URI: https://flatpress.org
 * Description: Allows use of emoticons. Part of the standard distribution.
 * Author: FlatPress
 * Author URI: https://flatpress.org
 */
// assigns markdown to HTML Entity
global $plugin_emoticons;
$plugin_emoticons = array(
	':smile:' => '&#128516;',
	':smiley:' => '&#128515;',
	':wink:' => '&#128521;',
	':blush:' => '&#128522;',
	':grin:' => '&#128513;',
	':smirk:' => '&#128527;',
	':heart_eyes:' => '&#128525;',
	':sunglasses:' => '&#128526;',
	':laughing:' => '&#128518;',
	':neutral_face:' => '&#128528;',
	':flushed:' => '&#128563;',
	':dizzy_face:' => '&#128565;',
	':cry:' => '&#128546;',
	':persevere:' => '&#128547;',
	':worried:' => '&#128543;',
	':hushed:' => '&#128558;',
	':mag:' => '&#128269;',
	':hot_beverage:' => '&#9749;',
	':exclamation:' => '&#10071;',
	':question:' => '&#10067;'
);

// outputs the editor toolbar
function plugin_emoticons() {
	global $plugin_emoticons;
	if (!count($plugin_emoticons))
		return true;
	echo '<div class="emoticons">';
	foreach ($plugin_emoticons as $text => $emoticon) {
		echo '<a href="#content" title="' . htmlentities($text) . '" onclick="emoticons(unescape(\'' . urlencode($text) . '\')); return false;">';
		echo $emoticon;
		echo '</a> ';
	}
	echo '</div>';
	return true;
}

// replaces the text with an utf-8 emoticon
function plugin_emoticons_filter ($emostring) {
	global $plugin_emoticons;

	foreach ($plugin_emoticons as $text => $emoticon) {
		$emostring = str_replace(
			$text,
			$emoticon,
			$emostring
		);
	}
	return $emostring;
}

// css file
function plugin_emoticons_head() {
	$pdir = plugin_geturl('emoticons');
	echo '
		<!-- BOF Emoticons -->
		<link rel="stylesheet" type="text/css" href="' . $pdir . 'res/emoticons.css">
		<script src="' . plugin_geturl('emoticons') . 'res/emoticons.js"></script>
		<!-- EOF Emoticons -->';
}

// register emoticon head
add_action('wp_head', 'plugin_emoticons_head', 10);
// register editor toolbar
add_filter('simple_toolbar_form', 'plugin_emoticons',);
// register to the hook
add_filter('the_content','plugin_emoticons_filter');
// register for emoticon in comment
add_filter('comment_text','plugin_emoticons_filter');
// register for the excerpt of a post
add_filter('the_excerpt', 'plugin_emoticons_filter');
?>
