<?php
/**
 * Plugin Name: Emoticons
 * Version: 1.1.3
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

/**
 * Replaces the text with an utf-8 emoticon (title-safe; no HTML markup)
 * Used for entry titles, static page titles and feeds.
 */
function plugin_emoticons_filter_title($titlestring, $sep = null) {
	global $plugin_emoticons;

	if (!is_string($titlestring) || $titlestring === '') {
		return $titlestring;
	}

	foreach ($plugin_emoticons as $text => $emoticon) {
		$titlestring = str_replace($text, $emoticon, $titlestring);
	}

	return $titlestring;
}

// Css and js file
function plugin_emoticons_head() {
	global $plugin_emoticons, $buttonData;
	$random_hex = RANDOM_HEX;
	$pdir = plugin_geturl('emoticons');
	$css = utils_asset_ver($pdir . 'res/emoticons.css', SYSTEM_VER);
	$js = utils_asset_ver($pdir . 'res/emoticons.js', SYSTEM_VER);

	$buttonData = [];
	foreach ($plugin_emoticons as $emoText => $emoticon) {
		$elementById = emoticon_id($emoText);
		$buttonData [] = [
			'id' => $elementById,
			'text' => $emoText,
			'icon' => $emoticon,
		];
	}

	echo '
		<!-- BOF Emoticons -->
		<link rel="stylesheet" type="text/css" href="' . $css . '">
		<script nonce="' . $random_hex . '" src="' . $js . '" defer></script>
		<!-- EOF Emoticons -->
	';
}

function plugin_emoticons_footer() {
	global $buttonData;
	$random_hex = RANDOM_HEX;

	echo '
		<!-- BOF Emoticons -->
		<script nonce="' . $random_hex . '">
			/**
			 * Emoticons Plugin
			 */
			const buttonData = ' . json_encode($buttonData) . ';
			document.addEventListener(\'DOMContentLoaded\', function() {
				const existingEmoIds = buttonData.map(item => item.id);
				const allEmoIdsExist = existingEmoIds.every(id => document.getElementById(id) !== null);
				if (allEmoIdsExist) {
					registerEmoticonButtons(buttonData);
				}
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
// register emoticon footer
add_action('wp_footer', 'plugin_emoticons_footer', 10);
// register editor toolbar
add_filter('simple_toolbar_form', 'plugin_emoticons');
// register to the hook
add_filter('the_content','plugin_emoticons_filter');
// register for emoticon in comment
add_filter('comment_text','plugin_emoticons_filter');
// register for the excerpt of a post
add_filter('the_excerpt', 'plugin_emoticons_filter');
// register for emoticon in entry/static titles (HTML body + feeds)
add_filter('the_title', 'plugin_emoticons_filter_title', 20, 1);
// register for emoticon in HTML <title> tag (runs after SEO plugins that build the title)
add_filter('wp_title', 'plugin_emoticons_filter_title', 20, 2);
?>
