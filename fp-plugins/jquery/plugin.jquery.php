<?php
/*
 * Plugin Name: jQuery
 * Version: 2.1
 * Plugin URI: https://www.flatpress.org
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 * Description: Provides <a href="http://jquery.com/" title="jQuery">jQuery</a> locally. Part of the standard distribution.
 */

// # Original author: NoWhereMan (http://www.nowhereland.it)
add_action('wp_head', 'plugin_jquery_head', 0);

function plugin_jquery_head() {
	$pdir = plugin_geturl('jquery');
	echo '
		<!-- start of jsUtils -->
		<script type="text/javascript" src="' . $pdir . 'res/jquery/3.5.1/jquery-3.5.1.min.js"></script>
		<script type="text/javascript" src="' . $pdir . 'res/jqueryui/1.12.1/jquery-ui.min.js"></script>
		<!-- end of jsUtils -->';
}

?>