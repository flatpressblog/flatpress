<?php
/*
 * Plugin Name: GDPR Video embed 
 * Plugin URI: https://www.flatpress.org
 * Description: Simple two-click solution for GDPR-compliant embedding of YouTube and Vimeo videos. Part of the standard distribution.
 * Author: FlatPress
 * Version: 1.0
 * Author URI: https://www.flatpress.org
 */
function plugin_gdprvideoembed_setup() {
	return function_exists('plugin_bbcode_head') ? 1 : -1;
}

function plugin_gdprvideoembed_head() {

	global $lang;
	lang_load('plugin:gdprvideoembed');
	$random_hex = RANDOM_HEX; // Outputs a nonce for inline scripts.
	$plugindir = plugin_geturl('gdprvideoembed');

	echo '
		<!-- BOF GDPR Video embed -->
		<link rel="stylesheet" type="text/css" href="' . $plugindir . 'res/gdpr-video-embed.css.php">
		<script nonce="' . $random_hex . '">
			/**
			 * GDPR-Video-Embed
			 */
			(function () {
				// Config
				var text = {
					youtube: \'<strong>' . $lang ['plugin'] ['gdprvideoembed'] ['head_youtube'] . '</strong>' . //
						'<div>' . $lang ['plugin'] ['gdprvideoembed'] ['hint_youtube'] . '</div>' . //
						'<a class="video-link" href="https://youtu.be/%id%" rel="noopener" target="_blank" ' . //
							'title="' . $lang ['plugin'] ['gdprvideoembed'] ['link_title_youtube'] . '">' . $lang ['plugin'] ['gdprvideoembed'] ['link'] . ': https://youtu.be/%id%</a>' . //
						'<button title="' . $lang ['plugin'] ['gdprvideoembed'] ['button_title'] . '">' . $lang ['plugin'] ['gdprvideoembed'] ['button'] . '</button>\',
					vimeo: \'<strong>' . $lang ['plugin'] ['gdprvideoembed'] ['head_vimeo'] . '</strong>' . //
						'<div>' . $lang ['plugin'] ['gdprvideoembed'] ['hint_vimeo'] . '</div>' . //
						'<a class="video-link" href="https://vimeo.com/%id%" rel="noopener" target="_blank" ' . //
							'title="' . $lang ['plugin'] ['gdprvideoembed'] ['link_title_vimeo'] . '">' . $lang ['plugin'] ['gdprvideoembed'] ['link'] . ': https://vimeo.com/%id%</a>' . //
						'<button title="' . $lang ['plugin'] ['gdprvideoembed'] ['button_title'] . '">' . $lang ['plugin'] ['gdprvideoembed'] ['button'] . '</button>\'
				};
				window.video_iframes = [];
				document.addEventListener("DOMContentLoaded", function () {
					var video_frame, responsive_bbcode_video, video_platform, video_src, video_id, video_w, video_h;
					for (var i = 0, max = window.frames.length - 1; i <= max; i += 1) {
						video_frame = document.getElementsByTagName(\'iframe\')[0];
						video_src = video_frame.src || video_frame.dataset.src;

						// Only process video iframes [youtube|vimeo]
						if (video_src.match(/youtube|vimeo/) == null) {
							continue;
						}

						video_iframes.push(video_frame);
						video_w = video_frame.getAttribute(\'width\');
						video_h = video_frame.getAttribute(\'height\');
						responsive_bbcode_video = document.createElement(\'article\');

						// Prevent iframes from loading remote content
						if (!!video_frame.src) {
							if (typeof (window.frames[0].stop) === \'undefined\') {
								setTimeout(function () {
									window.frames[0].execCommand(\'Stop\');
								}, 1000);
							} else {
								setTimeout(function () {
									window.frames[0].stop();
								}, 1000);
							}
						}
						video_platform = video_src.match(/vimeo/) == null ? \'youtube\' : \'vimeo\';
						video_id = video_src.match(/(embed|video)\/([^?\s]*)/)[2];
						responsive_bbcode_video.setAttribute(\'class\', \'video-responsive_bbcode_video\');
						responsive_bbcode_video.setAttribute(\'data-index\', i);
						if (video_w && video_h) {
							responsive_bbcode_video.setAttribute(\'style\', \'width: \' + video_w + \'px; height: \' + video_h + \'px\');
						}
						responsive_bbcode_video.innerHTML = text[video_platform].replace(/\%id\%/g, video_id);
						video_frame.parentNode.replaceChild(responsive_bbcode_video, video_frame);
						document.querySelectorAll(\'.video-responsive_bbcode_video button\')[i].addEventListener(\'click\', function () {
							var video_frame = this.parentNode,
								index = video_frame.dataset.index;
							if (!!video_iframes[index].dataset.src) {
								video_iframes[index].src = video_iframes[index].dataset.src;
								video_iframes[index].removeAttribute(\'data-src\');
							}
							// video_iframes[index].src = video_iframes[index].src.replace(/www\.youtube\.com/, \'www.youtube-nocookie.com\');
							video_frame.parentNode.replaceChild(video_iframes[index], video_frame);
						}, false);
					}
				});
			})();
		</script>
		<!-- EOF GDPR Video embed -->
	';
}

add_action('wp_head', 'plugin_gdprvideoembed_head', 0);
?>
