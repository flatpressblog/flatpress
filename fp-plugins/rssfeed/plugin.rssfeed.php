<?php

/*
 * Plugin Name: RSS-Feed
 * Version: 1.0.1
 * Plugin URI: https://www.flatpress.org
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 * Description: Enables a <a href="./admin.php?p=widgets" title="Widget menu">widget</a> with a subscribe button. Part of the standard distribution.
 */


function plugin_rssfeed_head() { // stytesheet-file
  $pdir = plugin_geturl('rssfeed');
	
	echo '
	<!-- start of RSS-Feed-Button Stylesheet -->
	<link rel="stylesheet" type="text/css" href="' . $pdir . 'res/rssfeed.css">
	<!-- end of RSS-Feed Stylesheet  -->
	';
}

function plugin_rssfeed_widget() {

  $lang = lang_load('plugin:rssfeed');
  $baseurl = BLOG_BASEURL;
  $pdir = plugin_geturl('rssfeed');
  
	$widget = array();
	$widget ['subject'] = $lang ['plugin'] ['rssfeed'] ['subject'];
	
	$widget ['content'] = '
		<ul>
			<li>
				<form action="' . $baseurl . 'rss.php" class="button-center" target="_blank"> 
					<button type="submit" class="button-subscribe">' . $lang ['plugin'] ['rssfeed'] ['subscribe'] . '<img class="rss-img" src="' . $pdir . 'img/rss.png" alt="RSS"></button> 
				</form> 
			</li>
		</ul>
	';
	
	return $widget;
}
	
register_widget('rssfeed', 'RSS-Feed', 'plugin_rssfeed_widget'); // rssfeed-widget
add_action('wp_head', 'plugin_rssfeed_head'); // stytesheet-file

?>
