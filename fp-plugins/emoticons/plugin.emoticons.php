<?php
/*
 * Plugin Name: Emoticons
 * Version: 1.1.2
 * Plugin URI: https://flatpress.org
 * Description: Allows use of emoticons. Part of the standard distribution.
 * Author: FlatPress
 * Author URI: https://flatpress.org
 */

global $plugin_emoticons;
// Assigns markdown to HTML Entity
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
	':joy:' => '&#128514;',
	':neutral_face:' => '&#128528;',
	':flushed:' => '&#128563;',
	':hushed:' => '&#128558;',
	':dizzy_face:' => '&#128565;',
	':cry:' => '&#128546;',
	':persevere:' => '&#128547;',
	':worried:' => '&#128543;',
	':angry:' => '&#128544;',
	':mag:' => '&#128269;',
	':hot_beverage:' => '&#9749;',
	':exclamation:' => '&#10071;',
	':question:' => '&#10067;'
);

// Outputs the editor toolbar
function plugin_emoticons() {
	global $plugin_emoticons;

	if (!count($plugin_emoticons)) {
		return true;
	}

	echo '
		<!-- BOF Emoticons -->
		<div class="emoticons">';

	// Buttons mit gespeicherten IDs ausgeben
	foreach ($plugin_emoticons as $emoText => $emoticon) {
		$elementById = emoticon_id($emoText);
		echo '
			<button type="button" style="font-size: 12px; vertical-align: middle;" title="' . htmlentities($emoText) . '" id="' . $elementById . '">';
		echo $emoticon;
		echo '</button>';
	}
	echo '
		</div>
		<!-- EOF Emoticons -->';

	return true;
}

// Replaces the text with an utf-8 emoticon
function plugin_emoticons_filter ($emostring) {
	global $plugin_emoticons;

	foreach ($plugin_emoticons as $text => $emoticon) {
		$emostring = str_replace(
			$text,
			'<span role="img" aria-label="Emoji ' . htmlentities($text) . '">' . $emoticon . '</span>',
			$emostring
		);
	}
	return $emostring;
}

// Css and js file
function plugin_emoticons_head() {
	global $plugin_emoticons;
	$random_hex = RANDOM_HEX;
	$pdir = plugin_geturl('emoticons');

	$buttonData = [];
	foreach ($plugin_emoticons as $emoText => $emoticon) {
		$elementById = emoticon_id($emoText);
		$buttonData[] = [
			'id' => $elementById,
			'text' => $emoText,
			'icon' => $emoticon,
		];
	}

	echo '
		<!-- BOF Emoticons -->
		<link rel="stylesheet" type="text/css" href="' . $pdir . 'res/emoticons.css">
		<script nonce="' . $random_hex . '" src="' . $pdir . 'res/emoticons.js"></script>
		<script nonce="' . $random_hex . '">
			/**
			 * Emoticons Plugin
			 */
			const buttonData = ' . json_encode($buttonData) . ';
			document.addEventListener("DOMContentLoaded", function() {
				registerEmoticonButtons(buttonData);
			});
		</script>
		<!-- EOF Emoticons -->
	';
}

// Generates an ID for each emoticon
function emoticon_id($text) {
	return rtrim(strtr(base64_encode($text), '+/', '-_'), '=');
}

// register emoticon head
add_action('wp_head', 'plugin_emoticons_head', 10);
// register editor toolbar
add_filter('simple_toolbar_form', 'plugin_emoticons');
// register to the hook
add_filter('the_content','plugin_emoticons_filter');
// register for emoticon in comment
add_filter('comment_text','plugin_emoticons_filter');
// register for the excerpt of a post
add_filter('the_excerpt', 'plugin_emoticons_filter');
?>
