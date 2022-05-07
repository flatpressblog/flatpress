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
	$pdir = plugin_geturl('jquery');
	echo '
		<!-- start of jsUtils -->
		<script src="' . $pdir . 'res/jquery/3.6/jquery-3.6.0.min.js"></script>
		<script src="' . $pdir . 'res/jqueryui/1.13.0/jquery-ui.min.js"></script>
		<!-- end of jsUtils -->';
}

?>