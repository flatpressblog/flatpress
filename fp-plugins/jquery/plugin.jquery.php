<?php
/*
 * Plugin Name: jQuery
 * Version: 2.2
 * Plugin URI: https://www.flatpress.org
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 * Description: Provides <a href="http://jquery.com/" title="jQuery">jQuery</a> locally. Part of the standard distribution.
 */
add_action('wp_head', 'plugin_jquery_head', 0);

function plugin_jquery_head() {
	$pdir = plugin_geturl('jquery');
	echo '
		<!-- start of jsUtils -->
		<script type="text/javascript" src="' . $pdir . 'res/jquery/3.6/jquery-3.6.0.min.js"></script>
		<script type="text/javascript" src="' . $pdir . 'res/jqueryui/1.13.0/jquery-ui.min.js"></script>
		<!-- end of jsUtils -->';
}

?>