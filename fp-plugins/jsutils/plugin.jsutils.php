<?php
/*
Plugin Name: jsUtils
Version: 1.0
Plugin URI: http://flatpress.sf.net
Description: jsUtils, provides <a href="http://mootools.net/">mootools</a>
Author: NoWhereMan
Author URI: http://flatpress.sf.net
*/


add_action('wp_head', 'plugin_jsutils_head', 0);


function plugin_jsutils_head() {

	$pdir=plugin_geturl('jsutils');
	echo <<<JSUTILS
	<!-- start of jsUtils -->
	<script type="text/javascript" src="{$pdir}res/mootools-1.2.4-with-1.1-classes.txt"></script>
	<script type="text/javascript" src="{$pdir}res/mootools-1.1-to-1.2-upgrade-helper.txt"></script>
	<!-- end of jsUtils -->
JSUTILS;
}

?>
