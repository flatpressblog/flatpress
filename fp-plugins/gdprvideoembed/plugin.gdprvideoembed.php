<?php
/*
 * Plugin Name: GDPR Video embed
 * Plugin URI: https://www.flatpress.org
 * Description: Simple two-click solution for GDPR-compliant embedding of YouTube, Facebook and Vimeo videos. Part of the standard distribution.
 * Author: FlatPress
 * Version: 1.1.1
 * Author URI: https://www.flatpress.org
 */
function plugin_gdprvideoembed_setup() {
	return function_exists('plugin_bbcode_head') ? 1 : -1;
}

function plugin_gdprvideoembed_head() {

	global $lang;
	lang_load('plugin:gdprvideoembed');

	// Ensure that the language variables are correctly available as arrays
	$lang_plugin = isset($lang ['plugin'] ['gdprvideoembed']) && is_array($lang ['plugin'] ['gdprvideoembed']) ? $lang ['plugin'] ['gdprvideoembed'] : [];

	// Language variables with fallback values and type checking
	$link = isset($lang_plugin ['link']) && is_string($lang_plugin ['link']) ? $lang_plugin ['link'] : 'Link';
	$button_title = isset($lang_plugin ['button_title']) && is_string($lang_plugin ['button_title']) ? $lang_plugin ['button_title'] : 'Button Title';
	$button = isset($lang_plugin ['button']) && is_string($lang_plugin ['button']) ? $lang_plugin ['button'] : 'Accept';

	$head_youtube = isset($lang_plugin ['head_youtube']) && is_string($lang_plugin ['head_youtube']) ? $lang_plugin ['head_youtube'] : 'YouTube Video';
	$hint_youtube = isset($lang_plugin ['hint_youtube']) && is_string($lang_plugin ['hint_youtube']) ? $lang_plugin ['hint_youtube'] : 'To view this video, please accept cookies.';
	$link_title_youtube = isset($lang_plugin ['link_title_youtube']) && is_string($lang_plugin ['link_title_youtube']) ? $lang_plugin ['link_title_youtube'] : 'Open YouTube video';

	$head_vimeo = isset($lang_plugin ['head_vimeo']) && is_string($lang_plugin ['head_vimeo']) ? $lang_plugin ['head_vimeo'] : 'Vimeo Video';
	$hint_vimeo = isset($lang_plugin ['hint_vimeo']) && is_string($lang_plugin ['hint_vimeo']) ? $lang_plugin ['hint_vimeo'] : 'To view this video, please accept cookies.';
	$link_title_vimeo = isset($lang_plugin ['link_title_vimeo']) && is_string($lang_plugin ['link_title_vimeo']) ? $lang_plugin ['link_title_vimeo'] : 'Open Vimeo video';

	$head_facebook = isset($lang_plugin ['head_facebook']) && is_string($lang_plugin ['head_facebook']) ? $lang_plugin ['head_facebook'] : 'Facebook Video';
	$hint_facebook = isset($lang_plugin ['hint_facebook']) && is_string($lang_plugin ['hint_facebook']) ? $lang_plugin ['hint_facebook'] : 'To view this video, please accept cookies.';
	$link_title_facebook = isset($lang_plugin ['link_title_facebook']) && is_string($lang_plugin ['link_title_facebook']) ? $lang_plugin ['link_title_facebook'] : 'Open Facebook video';

	// Outputs a nonce for inline scripts.
	$random_hex = RANDOM_HEX;
	$plugindir = plugin_geturl('gdprvideoembed');

	$css = utils_asset_ver($plugindir . 'res/gdpr-video-embed.css.php', SYSTEM_VER);
	$js = utils_asset_ver($plugindir . 'res/gdpr-video-embed.js', SYSTEM_VER);

	echo '
		<!-- BOF GDPR Video embed -->
		<link rel="stylesheet" type="text/css" href="' . $css . '">
		<script nonce="' . $random_hex . '" src="' . $js . '"></script>
		<script nonce="' . $random_hex . '">
			/**
			 * GDPR-Video-Embed | dynamic part
			 */
			window.gdprConfig = {
				text: {
					youtube: \'<strong>' . $head_youtube . '</strong>' . //
						'<div>' . $hint_youtube . '</div>' . //
						'<a class="video-link" href="https://youtu.be/%id%" rel="noopener" target="_blank" ' . //
						'title="' . $link_title_youtube . '">' . $link . ': https://youtu.be/%id%</a>' . //
						'<button title="' . $button_title . '">' . $button . '</button>\',
					vimeo: \'<strong>' . $head_vimeo . '</strong>' . //
						'<div>' . $hint_vimeo . '</div>' . //
						'<a class="video-link" href="https://vimeo.com/%id%" rel="noopener" target="_blank" ' . //
						'title="' . $link_title_vimeo . '">' . $link . ': https://vimeo.com/%id%</a>' . //
						'<button title="' . $button_title . '">' . $button . '</button>\',
					facebook: \'<strong>' . $head_facebook . '</strong>' . //
						'<div>' . $hint_facebook . '</div>' . //
						'<a class="video-link" href="%video_url%" rel="noopener" target="_blank" ' . //
						'title="' . $link_title_facebook . '">' . $link . ': %video_url%</a>' . //
						'<button title="' . $button_title . '">' . $button . '</button>\'
				}
			};
		</script>
		<!-- EOF GDPR Video embed -->
	';
}

add_action('wp_head', 'plugin_gdprvideoembed_head', 0);
?>
