<?php
/**
 * Generates the XML sitemap for search engines.
 * Original author: Igor Kromin - https://www.igorkromin.net
 * Extended by: Tongara
 *
 * See also:
 * https://www.igorkromin.net/index.php/2013/02/18/adding-a-google-compatible-sitemap-to-flatpress/
 * https://www.igorkromin.net/index.php/2014/06/04/add-flatpress-static-page-links-to-the-sitemapphp-site-map-generator/
 * https://forum.flatpress.org/viewtopic.php?f=4&t=126
 */
require_once ('defaults.php');
require_once (INCLUDES_DIR . 'includes.php');

if (function_exists('system_init')) {
	system_init();
} else {
	plugin_loadall();
}

header('Content-Type: text/xml; charset=utf-8');
error_reporting(E_ALL);
$offset = $fp_config ['locale'] ['timeoffset'];

// prepare XML head
echo '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<url> 
<loc>' . BLOG_BASEURL . '</loc>
<lastmod>' . date("c") . '</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>';
// XML head done

// we start with the blog entries
$q = new FPDB_Query(array(
	'start' => 0,
	'count' => -1,
	'fullparse' => true
), null);
while ($q->hasMore()) {
	list ($id, $e) = $q->getEntry();
	if (isset($e ['lastupdate'])) {
		$lastmod = $e ['lastupdate'] - (60 * 60 * $offset);
	} else {
		$lastmod = $e ['date'] - (60 * 60 * $offset);
	}
	$loc = get_permalink($id);

	echo '<url>
<loc>' . $loc . '</loc>
<lastmod>' . date("c", $lastmod) . '</lastmod>
</url>
';
}
// done with the entries

// now the static pages
$statics = static_getlist();
foreach ($statics as $currentstatic) {
	$currentStaticData = static_parse($currentstatic);
	$loc = BLOG_BASEURL . '?page=' . $currentstatic;

	// If current static has no date, use timestamp of now
	if (array_key_exists('date', $currentStaticData)) {
		$d = $currentStaticData ['date'] - (60 * 60 * $offset);
	} else {
		$d = time();
	}

	$d = date('c', $d);
	echo '<url>
<loc>' . $loc . '</loc>
<lastmod>' . $d . '</lastmod>
</url>';
}
// done with the static pages

echo '</urlset>';