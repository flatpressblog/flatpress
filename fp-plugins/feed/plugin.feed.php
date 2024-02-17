<?php

/*
 * Plugin Name: RSS and Atom Feed
 * Version: 1.0.0
 * Plugin URI: https://www.flatpress.org
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 * Description: Activates feed buttons in the <a href="./admin.php?p=widgets" title="Widget menu">widget menu</a>. Part of the standard distribution.
 */


function plugin_feed_head() { // stytesheet-file
	$pdir = plugin_geturl('feed');

	echo '
	<!-- BOF Feed-Button Stylesheet -->
	<link rel="stylesheet" type="text/css" href="' . $pdir . 'res/feed.css">
	<!-- EOF Feed Stylesheet  -->
	';
}

function plugin_feed_widget() {

	$lang = lang_load('plugin:feed');
	$baseurl = BLOG_BASEURL;
	$imgdir = plugin_geturl('feed');

	$widget ['subject'] = $lang ['plugin'] ['feed'] ['subject'];

	$rss = $lang ['plugin'] ['feed'] ['rss'];
	$atom = $lang ['plugin'] ['feed'] ['atom'];

	$widget ['content'] = '
		<!-- BOF Feed-Buttons -->
		<ul>
			<li>
				<a href="' . $baseurl . '?x=feed:rss2" title="' . $rss . '" target="_blank"><img class="feed-widget" src="' . $imgdir . 'img/rss.png" alt="RSS"></a>
				<a href="' . $baseurl . '?x=feed:atom" title="' . $atom . '" target="_blank"><img class="feed-widget" src="' . $imgdir . 'img/atom.png" alt="Atom"></a>
			</li>
		</ul>
		<!-- EOF Feed-Buttons -->
	';

	return $widget;
}

register_widget('feed', 'RSS and Atom Feed', 'plugin_feed_widget'); // feed-widget
add_action('wp_head', 'plugin_feed_head'); // stytesheet-file
?>
