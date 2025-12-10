<?php
$fp_plugins = array(

	// to disable put // or # before the plugin name
	// remove it to enable :)

	'jquery', // needed by ligthbox2 (quite heavy in size)
	'thumb', // creates thumbnails adding scale=NN% to [img] tags :)
	'bbcode', // bbcode-style formatting; if you disable this
		// you'll loose some features, but you will be able to use html
		// as a default
	'accessibleantispam',
	'qspam', // quick spam filter
	'adminarea',
	'archives',
	// 'calendar', //time consuming, not really recommended :p
	'lastcomments', // cache-based last-comments block
	'lastentries',
	'prettyurls', // PrettyURLs with NGINX? see https://wiki.flatpress.org/res:plugins:prettyurls#nginx
	'categories',
	'searchbox',
	'blockparser',
	'readmore',
	'favicon',
	'postviews', // Counts and displays entry views
	'commentcenter', // including Akismet interface
	'mediamanager',
	'datechanger', // Lets you change the publish date for (new) entries.
	'seometataginfo', // Makes it easier to find with search engines and post on social media
	'feed', // Activates the RSS and Atom feed widget
	'emoticons', // Activates an emoticons toolbar for entries and static pages
	'support', // Provides the FlatPress admin and the community with all relevant data in case of problems.
	'gallerycaptions',
	'photoswipe',
	'fpprotect', // Hardens your blog with additional features in the HTTP response header.
		// Removes the htaccess editor from the PrettyURLs plugin.
	'gdprvideoembed', // Simple two-click solution for GDPR-compliant embedding of YouTube, Facebook and Vimeo videos.
	'storage'
);
?>
