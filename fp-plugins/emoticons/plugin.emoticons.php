<?php
/*
 * Plugin Name: Emoticons
 * Version: 1.1.1
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

// outputs the editor toolbar
function plugin_emoticons() {
	global $fp_config, $plugin_emoticons;
	$random_hex = $fp_config ['plugins'] ['fpprotect'] ['random_hex'];

	if (!count($plugin_emoticons)) {
		return true;
	}
	echo '
		<!-- BOF Emoticons -->
		<div class="emoticons">';
	foreach ($plugin_emoticons as $emoText => $emoticon) {

		$elementById = randomChar(8);
		echo '
		<script nonce="' . $random_hex . '">
			if (document.getElementById(\'' . $elementById . '\')) { // Button already available?
				BTN_' . $elementById . '(); // Call the registration function
			} else { // Register as EventHandler
				document.addEventListener(\'DOMContentLoaded\', BTN_' . $elementById . ');
			}
			// Registration function
			function BTN_' . $elementById . '() {
				const em = document.getElementById(\'' . $elementById . '\');
				if (em) {
					document.getElementById(\'' . $elementById . '\').addEventListener(\'click\', onClick_' . $elementById . ', false);
				}
			}
			// Replacement for href onclick HTML method
			function onClick_' . $elementById . '() {
				emoticons(unescape(\'' . urlencode($emoText) . '\')); return false;
			}
		</script>
		<a href="#!" title="' . htmlentities($emoText) . '" id="' . $elementById . '">';
		echo $emoticon;
		echo '</a>
		';

	}
	echo '
		</div>
		<!-- EOF Emoticons -->
		';
	return true;
}


// generates a random string for elementById with $length characters
function randomChar($length = 10) {
	return substr(str_shuffle(str_repeat(implode('', range('a','z')), $length)), 0, $length);
}

// replaces the text with an utf-8 emoticon
function plugin_emoticons_filter ($emostring) {
	global $plugin_emoticons;

	foreach ($plugin_emoticons as $text => $emoticon) {
		$emostring = str_replace(
			$text,
			// Is better for screen readers
			'<span role="img" aria-label="Emoji ' . htmlentities($text) . '">' . $emoticon . '</span>',
			$emostring
		);
	}
	return $emostring;
}

// css file
function plugin_emoticons_head() {
	global $fp_config;
	$random_hex = $fp_config ['plugins'] ['fpprotect'] ['random_hex'];
	$pdir = plugin_geturl('emoticons');
	echo '
		<!-- BOF Emoticons -->
		<link rel="stylesheet" type="text/css" href="' . $pdir . 'res/emoticons.css">
		<script nonce="' . $random_hex . '" src="' . plugin_geturl('emoticons') . 'res/emoticons.js"></script>
		<!-- EOF Emoticons -->';
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
