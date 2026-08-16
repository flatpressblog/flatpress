<?php
/**
 * Generates the XML sitemap for search engines.
 * Original author: Igor Kromin - https://www.igorkromin.net
 * Extended by: Tongara
 *
 * Image discovery uses FlatPress' shared content-media resolver. The sitemap
 * remains Sitemap Protocol 0.9 and adds Google's image sitemap namespace.
 *
 * See also:
 * https://www.igorkromin.net/index.php/2013/02/18/adding-a-google-compatible-sitemap-to-flatpress/
 * https://www.igorkromin.net/index.php/2014/06/04/add-flatpress-static-page-links-to-the-sitemapphp-site-map-generator/
 * https://forum.flatpress.org/viewtopic.php?f=4&t=126
 * https://www.sitemaps.org/protocol.html
 * https://developers.google.com/search/docs/crawling-indexing/sitemaps/image-sitemaps
 */

/**
 * Escape one XML text value using the sitemap's UTF-8 encoding.
 *
 * @param mixed $value
 * @return string
 */
function sitemap_xml_escape($value) {
	return htmlspecialchars((string)$value, ENT_QUOTES | ENT_XML1, 'UTF-8');
}

/**
 * Render one sitemap <url> node.
 *
 * @param string $loc
 * @param string $lastmod
 * @param string $imageLoc Original image URL, never a generated thumbnail
 * @param string $changefreq
 * @param string $priority
 * @return string
 */
function sitemap_render_url($loc, $lastmod, $imageLoc = '', $changefreq = '', $priority = '') {
	$xml = "<url>\n";
	$xml .= '<loc>' . sitemap_xml_escape($loc) . "</loc>\n";

	if ($lastmod !== '') {
		$xml .= '<lastmod>' . sitemap_xml_escape($lastmod) . "</lastmod>\n";
	}
	if ($changefreq !== '') {
		$xml .= '<changefreq>' . sitemap_xml_escape($changefreq) . "</changefreq>\n";
	}
	if ($priority !== '') {
		$xml .= '<priority>' . sitemap_xml_escape($priority) . "</priority>\n";
	}
	if ($imageLoc !== '') {
		$xml .= "<image:image>\n";
		$xml .= '<image:loc>' . sitemap_xml_escape($imageLoc) . "</image:loc>\n";
		$xml .= "</image:image>\n";
	}

	$xml .= "</url>\n";
	return $xml;
}

require_once ('defaults.php');
require_once (INCLUDES_DIR . 'includes.php');

if (function_exists('system_init')) {
	system_init();
} else {
	plugin_loadall();
}

$fp_config = isset($fp_config) && is_array($fp_config) ? $fp_config : (function_exists('config_load') ? config_load() : array());

header('Content-Type: text/xml; charset=utf-8');
$offset = isset($fp_config ['locale'] ['timeoffset']) ? (float)$fp_config ['locale'] ['timeoffset'] : 0.0;

// Sitemap Protocol 0.9 with the image sitemap extension namespace.
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ' . 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

// Public front page: configured static start page or the first entry-stream window.
$frontImage = content_media_get_frontpage_image_meta(BLOG_BASEURL);
$frontImageUrl = isset($frontImage ['url']) && is_string($frontImage ['url']) ? $frontImage ['url'] : '';
echo sitemap_render_url(BLOG_BASEURL, date('c'), $frontImageUrl, 'daily', '1.0');

// Blog entries. Use raw entry data so sitemap generation never renders media or
// creates thumbnails as a side effect.
$q = new FPDB_Query(array(
	'start' => 0,
	'count' => -1,
	'fullparse' => false
), null);

while ($q->hasMore()) {
	$couplet = &$q->getEntry();
	if (!is_array($couplet) || empty($couplet [0])) {
		break;
	}

	$id = $couplet [0];
	$entry = entry_parse($id);
	if (!is_array($entry)) {
		continue;
	}

	if (isset($entry ['lastupdate'])) {
		$lastmod = (int)$entry ['lastupdate'] - (int)(60 * 60 * $offset);
	} else {
		$lastmod = isset($entry ['date']) ? ((int)$entry ['date'] - (int)(60 * 60 * $offset)) : entry_idtotime($id);
	}

	$loc = get_permalink($id);
	$image = isset($entry ['content']) ? content_media_find_first_image_meta((string)$entry ['content'], BLOG_BASEURL, false) : content_media_empty_image_meta();
	$imageUrl = isset($image ['url']) && is_string($image ['url']) ? $image ['url'] : '';

	echo sitemap_render_url($loc, date('c', $lastmod), $imageUrl);
}

// Static pages. theme_staticlink() lets PrettyURLs supply the same public URL
// format used by the frontend instead of forcing the legacy ?page= form.
$statics = static_getlist();
foreach ($statics as $currentstatic) {
	$currentStaticData = static_parse($currentstatic);
	if (!is_array($currentStaticData)) {
		continue;
	}

	$loc = theme_staticlink($currentstatic);

	// If current static has no date, retain the historical "now" fallback.
	if (array_key_exists('date', $currentStaticData)) {
		$d = (int)$currentStaticData ['date'] - (int)(60 * 60 * $offset);
	} else {
		$d = time();
	}

	$image = isset($currentStaticData ['content'])
		? content_media_find_first_image_meta((string)$currentStaticData ['content'], BLOG_BASEURL, false)
		: content_media_empty_image_meta();
	$imageUrl = isset($image ['url']) && is_string($image ['url']) ? $image ['url'] : '';

	echo sitemap_render_url($loc, date('c', $d), $imageUrl);
}

echo '</urlset>';
?>
