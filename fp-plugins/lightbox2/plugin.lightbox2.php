<?php
/*
Plugin Name: LightBox2 
Version: 2.0
Plugin URI: http://www.vdfn.altervista.org/
Description: Lightbox overlays using <a href="http://www.digitalia.be/software/slimbox2">SlimBox 2</a> requires <a href="http://jquery.com" title="jQuery">jQuery</a> (modified jsutils plugin) 
Author: Piero VDFN
Author URI: http://www.vdfn.altervista.org/
*/

## Original author: NoWhereMan (http://www.nowhereland.it)

// if PLUGINS_DIR is not fp-plugins please edit res/lightbox.js

function plugin_lightbox2_setup() {
	return function_exists('plugin_jquery_head')? 1:-1;
}

function plugin_lightbox2_head() {

	$pdir=plugin_geturl('lightbox2');
	echo <<<LBOXHEAD
	<!-- start of lightbox -->
	<link rel="stylesheet" type="text/css" href="{$pdir}res/slimbox2.css" />
	<!-- end of lightbox -->
LBOXHEAD;
}
add_action('wp_head', 'plugin_lightbox2_head');

function plugin_lightbox2_footer() {

	$pdir=plugin_geturl('lightbox2');
	echo <<<LBOXHEAD
	<!-- start of lightbox -->
	<script type="text/javascript" src="{$pdir}res/slimbox2.js"></script>
	<!-- end of lightbox -->
LBOXHEAD;
}
add_action('wp_footer', 'plugin_lightbox2_footer');

function plugin_lightbox2_hook($popup, $abspath) {
	global $lightbox_rel;
	// the other $popup is just dropped
	return $lightbox_rel? "rel=\"lightbox[$lightbox_rel]\"" : ' rel="lightbox"';
}
add_action('bbcode_img_popup', 'plugin_lightbox2_hook', 5, 2);

