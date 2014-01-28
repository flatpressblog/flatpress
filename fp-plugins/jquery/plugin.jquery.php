<?php
/*
Plugin Name: jQuery
Version: 2.0
Plugin URI: http://www.vdfn.altervista.org/
Description: provides <a href="http://jquery.com/" title="jQuery">jQuery</a>
Author: Piero VDFN
Author URI: http://www.vdfn.altervista.org/
 	<!--<script src="{$pdir}res/jquery-2.0.3.min.js"></script>-->
	<!--<script src="{$pdir}res/jquery-ui-1.10.3.custom.min.js"></script>-->
*/

## Original author: NoWhereMan (http://www.nowhereland.it)

add_action('wp_head', 'plugin_jquery_head', 0);


function plugin_jquery_head() {

	$pdir=plugin_geturl('jquery');
	echo <<<JSUTILS
	<!-- start of jsUtils -->
	<script src="{$pdir}res/jquery-2.0.3.min.js"></script>
	<script src="{$pdir}res/jquery-ui-1.10.3.custom.min.js"></script>
	<!--<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>-->
	<!--<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>-->
	<!-- end of jsUtils -->
JSUTILS;
}

?>
