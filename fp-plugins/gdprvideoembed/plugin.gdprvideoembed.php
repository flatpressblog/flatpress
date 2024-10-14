<?php
/*
 * Plugin Name: GDPR Video embed
 * Plugin URI: https://www.flatpress.org
 * Description: Simple two-click solution for GDPR-compliant embedding of YouTube, Facebook and Vimeo videos. Part of the standard distribution.
 * Author: FlatPress
 * Version: 1.1.0
 * Author URI: https://www.flatpress.org
 */
function plugin_gdprvideoembed_setup() {
	return function_exists('plugin_bbcode_head') ? 1 : -1;
}

function plugin_gdprvideoembed_head() {

	global $lang;
	lang_load('plugin:gdprvideoembed');

	// Outputs a nonce for inline scripts.
	$random_hex = RANDOM_HEX;
	$plugindir = plugin_geturl('gdprvideoembed');

	echo '
		<!-- BOF GDPR Video embed -->
		<link rel="stylesheet" type="text/css" href="' . $plugindir . 'res/gdpr-video-embed.css.php">
		<script nonce="' . $random_hex . '" src="' . $plugindir . 'res/gdpr-video-embed.js"></script>
		<script nonce="' . $random_hex . '">
			/**
			 * GDPR-Video-Embed | dynamic part
			 */
			window.gdprConfig = {
				text: {
					youtube: \'<strong>' . $lang ['plugin'] ['gdprvideoembed'] ['head_youtube'] . '</strong>' . //
						'<div>' . $lang ['plugin'] ['gdprvideoembed'] ['hint_youtube'] . '</div>' . //
						'<a class="video-link" href="https://youtu.be/%id%" rel="noopener" target="_blank" ' . //
						'title="' . $lang ['plugin'] ['gdprvideoembed'] ['link_title_youtube'] . '">' . $lang ['plugin'] ['gdprvideoembed'] ['link'] . ': https://youtu.be/%id%</a>' . //
						'<button title="' . $lang ['plugin'] ['gdprvideoembed'] ['button_title'] . '">' . $lang ['plugin'] ['gdprvideoembed'] ['button'] . '</button>\',

					vimeo: \'<strong>' . $lang ['plugin'] ['gdprvideoembed'] ['head_vimeo'] . '</strong>' . //
						'<div>' . $lang ['plugin'] ['gdprvideoembed'] ['hint_vimeo'] . '</div>' . //
						'<a class="video-link" href="https://vimeo.com/%id%" rel="noopener" target="_blank" ' . //
						'title="' . $lang ['plugin'] ['gdprvideoembed'] ['link_title_vimeo'] . '">' . $lang ['plugin'] ['gdprvideoembed'] ['link'] . ': https://vimeo.com/%id%</a>' . //
						'<button title="' . $lang ['plugin'] ['gdprvideoembed'] ['button_title'] . '">' . $lang ['plugin'] ['gdprvideoembed'] ['button'] . '</button>\',

					facebook: \'<strong>' . $lang ['plugin'] ['gdprvideoembed'] ['head_facebook'] . '</strong>' . //
						'<div>' . $lang ['plugin'] ['gdprvideoembed'] ['hint_facebook'] . '</div>' . //
						'<a class="video-link" href="%video_url%" rel="noopener" target="_blank" ' . //
						'title="' . $lang ['plugin'] ['gdprvideoembed'] ['link_title_facebook'] . '">' . $lang ['plugin'] ['gdprvideoembed'] ['link'] . ': %video_url%</a>' . //
						'<button title="' . $lang ['plugin'] ['gdprvideoembed'] ['button_title'] . '">' . $lang ['plugin'] ['gdprvideoembed'] ['button'] . '</button>\'
				}
			};
		</script>
		<!-- EOF GDPR Video embed -->
	';
}

add_action('wp_head', 'plugin_gdprvideoembed_head', 0);
?>
