<?php

/*
 * Plugin Name: LightBox2
 * Version: 2.0
 * Plugin URI: https://www.flatpress.org
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 * Description: Lightbox overlays using <a href="http://www.digitalia.be/software/slimbox2">SlimBox 2</a> requires <a href="http://jquery.com" title="jQuery">jQuery</a> (modified jsutils plugin). Part of the standard distribution.
 */

// # Original author: NoWhereMan (http://www.nowhereland.it)

// if PLUGINS_DIR is not fp-plugins please edit res/lightbox.js
function plugin_lightbox2_setup() {
	return function_exists('plugin_jquery_head') ? 1 : -1;
}

function plugin_lightbox2_head() {
	$pdir = plugin_geturl('lightbox2');
	echo '
		<!-- start of lightbox -->
		<link rel="stylesheet" type="text/css" href="' . $pdir . 'res/slimbox2.css" />
		<!-- end of lightbox -->';
}
add_action('wp_head', 'plugin_lightbox2_head');

function plugin_lightbox2_footer() {
	$pdir = plugin_geturl('lightbox2');
	echo '
		<!-- start of lightbox -->
		<script src="' . $pdir . 'res/slimbox2.js"></script>
		<!-- end of lightbox -->';
}
add_action('wp_footer', 'plugin_lightbox2_footer');

function plugin_lightbox2_hook($popup, $abspath) {
	global $lightbox_rel;
	// the other $popup is just dropped
	return $lightbox_rel ? "rel=\"lightbox[$lightbox_rel]\"" : ' rel="lightbox"';
}
add_action('bbcode_img_popup', 'plugin_lightbox2_hook', 5, 2);
