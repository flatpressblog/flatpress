<?php
/*
 * Plugin Name: jQuery
 * Version: 2.2
 * Plugin URI: https://www.flatpress.org
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 * Description: Provides <a href="https://jquery.com/" title="jQuery">jQuery</a> and <a href="https://jqueryui.com/" title="jQueryUI">jQueryUI</a> locally. Part of the standard distribution.
 */
add_action('wp_head', 'plugin_jquery_head', 0);

function plugin_jquery_head() {
	global $fp_config;
	$random_hex = $fp_config ['plugins'] ['fpprotect'] ['random_hex'];

	$pdir = plugin_geturl('jquery');
	echo '
		<!-- start of jsUtils -->
		<script nonce="' . $random_hex . '" src="' . $pdir . 'res/jquery/3.6.1/jquery-3.6.1.min.js"></script>
		<script nonce="' . $random_hex . '" src="' . $pdir . 'res/jqueryui/1.13.2/jquery-ui.min.js"></script>
		<!-- end of jsUtils -->';
}

?>