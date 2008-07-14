<?php
/*
Plugin Name: LightBox
Version: 1.0
Plugin URI: http://flatpress.sf.net
Description: Lightbox overlays using <a href="http://www.digitalia.be/software/slimbox">SlimBox</a> requires <a href="http://mootools.net">Mootools</a> (jsutils plugin) 
Author: NoWhereMan
Author URI: http://www.flatpress.org/
*/


// if PLUGINS_DIR is not fp-plugins please edit res/lightbox.js

function plugin_lightbox_setup() {
	return function_exists('plugin_jsutils_head')? 1:-1;
}

function plugin_lightbox_head() {

	$pdir=plugin_geturl('lightbox');
	echo <<<LBOXHEAD
	<!-- start of lightbox -->
	<link rel="stylesheet" type="text/css" href="{$pdir}res/slimbox.css" />
	<!-- end of lightbox -->
LBOXHEAD;
}
add_action('wp_head', 'plugin_lightbox_head');

function plugin_lightbox_footer() {

	$pdir=plugin_geturl('lightbox');
	echo <<<LBOXHEAD
	<!-- start of lightbox -->
	<script type="text/javascript" src="{$pdir}res/slimbox.js"></script>
	<!-- end of lightbox -->
LBOXHEAD;
}
add_action('wp_footer', 'plugin_lightbox_footer');

function plugin_lightbox_hook($popup, $abspath) {
	global $lightbox_rel;
	// the other $popup is just dropped
	return $lightbox_rel? "rel=\"lightbox[$lightbox_rel]\"" : ' rel="lightbox"';
}
add_action('bbcode_img_popup', 'plugin_lightbox_hook', 5, 2);

?>
