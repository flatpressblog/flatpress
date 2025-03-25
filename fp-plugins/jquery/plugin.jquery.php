<?php
/*
 * Plugin Name: jQuery
 * Version: 2.2.1
 * Plugin URI: https://www.flatpress.org
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 * Description: Provides <a href="https://jquery.com/" title="jQuery">jQuery</a> and <a href="https://jqueryui.com/" title="jQueryUI">jQueryUI</a> locally. Part of the standard distribution.
 */
add_action('wp_head', 'plugin_jquery_head', 0);

function plugin_jquery_head() {
	$random_hex = RANDOM_HEX;

	$pdir = plugin_geturl('jquery');
	echo '
		<!-- start of jsUtils -->
		<script nonce="' . $random_hex . '" src="' . $pdir . 'res/jquery/3.7.1/jquery-3.7.1.js"></script>
		<script nonce="' . $random_hex . '" src="' . $pdir . 'res/jqueryui/1.14.1/jquery-ui.js"></script>
		<!-- end of jsUtils -->';
}

?>
