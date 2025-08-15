<?php
/**
 * Plugin Name: jQuery
 * Version: 2.2.1
 * Plugin URI: https://www.flatpress.org
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 * Description: Provides <a href="https://jquery.com/" title="jQuery">jQuery</a> and <a href="https://jqueryui.com/" title="jQueryUI">jQueryUI</a> locally. Part of the standard distribution.
 */
function plugin_jquery_head() {
	$random_hex = RANDOM_HEX;
	$pdir = plugin_geturl('jquery');
	$css = utils_asset_ver($pdir . 'res/jqueryui/1.14.1/jquery-ui.css', SYSTEM_VER);
	$js = utils_asset_ver($pdir . 'res/jquery/3.7.1/jquery-3.7.1.js', SYSTEM_VER);
	$jsUi = utils_asset_ver($pdir . 'res/jqueryui/1.14.1/jquery-ui.js', SYSTEM_VER);

	echo '
		<!-- start of jsUtils -->
		<link rel="stylesheet" href="' . $css . '">
		<script nonce="' . $random_hex . '" src="' . $js . '"></script>
		<script nonce="' . $random_hex . '" src="' . $jsUi . '"></script>
		<!-- end of jsUtils -->
	';
}

add_action('wp_head', 'plugin_jquery_head', 0);
?>
