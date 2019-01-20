<?php
/*
Plugin Name: jQuery
Version: 2.0.1
Plugin URI: http://www.vdfn.altervista.org/
Description: Provides <a href="http://jquery.com/" title="jQuery">jQuery</a> locally.
Author: Piero VDFN
Author URI: http://www.vdfn.altervista.org/
JQuery and JQueryUI version bump by Arvid Zimmermann
*/

## Original author: NoWhereMan (http://www.nowhereland.it)

add_action('wp_head', 'plugin_jquery_head', 0);


function plugin_jquery_head() {

	$pdir=plugin_geturl('jquery');
	echo <<<JSUTILS
	<!-- start of jsUtils -->
	<script type="text/javascript" src="{$pdir}res/jquery/1.10.2/jquery.min.js"></script>
	<script type="text/javascript" src="{$pdir}res/jqueryui/1.10.3/jquery-ui.min.js"></script>
	<!-- end of jsUtils -->
JSUTILS;
}

?>
