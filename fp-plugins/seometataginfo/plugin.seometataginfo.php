<?php
/**
 * Plugin Name: SEO Meta Tag Info
 * Version: 2.3.1
 * Plugin URI: https://www.flatpress.org
 * Description: This plugin allows editing of SEO meta tags description, keywords and robots for Entries, Statics and Categories. Part of the standard distribution. <a href="./fp-plugins/seometataginfo/doc_seometataginfo.txt" title="Anleitung" target="_blank">[Instructions]</a>
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 */

// SEE 'readme.txt' for information
require ('inc/hw-helpers.php');
require ('inc/class.iniparser.php');
require ('inc/migrate_data.php');

global $seo_default;
global $prepend_description;
global $prepend_keywords;

/**
 * Dynamic definition of allowed characters
 */
$unicode_blocks = [
	'\p{L}', // Letter
	'\p{N}', // Number
	'\p{P}', // Punctuation
	'\p{Zs}', // Separator, space
	'\p{M}', // Mark
];

/**
 * Additional permitted characters (e.g. emojis, special characters)
 */
$extra_chars = '-_,©®✓✔➤➔→➥▶⇒➨★❤♥✘✖✆✈⚠️☎️✉👍😄😃😉😊😁😏😍😎😆😂😐😳😮😵😢😣😟😠🔍☕❗❓';

$allowed_characters_regex = '/[^' . implode('', $unicode_blocks) . preg_quote($extra_chars, '/') . ']/u';

$seo_default = array(
	'description' => '', // the page description
	'keywords' => '', // keywords comma seperated e.g. "a,c d,c"
	'noindex' => '0', // "0" or "1"
	'nofollow' => '0', // "0" or "1"
	'noarchive' => '0', // "0" or "1"
	'nosnippet' => '0' // "0" or "1"
);

/**
 * prepend the blog title to description and keywords
 */
global $fp_config;
if ((!isset($fp_config) || !is_array($fp_config)) && isset($GLOBALS ['EARLY_FP_CONFIG']) && is_array($GLOBALS ['EARLY_FP_CONFIG'])) {
	$fp_config = $GLOBALS ['EARLY_FP_CONFIG'];
}
$prepend_description = $fp_config ['general'] ['title'] . ' - ';
$prepend_keywords = $fp_config ['general'] ['title'] . ', ';

/**
 * IMPORTANT: migrate data from previous version
 * Note: If a metatags.ini exists for both
 * an published entry and a draft entry
 * the published entry version will be kept.
 * set 'SEOMETA_MIGRATE_DATA' to 'true' then
 * create a new entry to force data migration.
 */
if (!defined('SEOMETA_MIGRATE_DATA')) {
	define('SEOMETA_MIGRATE_DATA', false);
}

/**
 * generate Open Graph <meta property="og:".. true/false
 */
if (!defined('SEOMETA_GEN_OPEN_GRAPH')) {
	define('SEOMETA_GEN_OPEN_GRAPH', true);
}

/**
 * generate pretty titles e.g.
 *   'Blog Title - Archive - 2011/06' or
 *   'Blog Title - Category - Something Cool'
 */
if (!defined('SEOMETA_GEN_TITLE')) {
	define('SEOMETA_GEN_TITLE', true);
}

/**
 * generate <meta name='title'.. true/false
 */
if (!defined('SEOMETA_GEN_TITLE_META')) {
	define('SEOMETA_GEN_TITLE_META', true);
}

/**
 * Before the crawler selects any image, we give it the style/ theme preview
 * generate <meta property="og:image".. true/false
 */
if (!defined('SEOMETA_GEN_IMAGE_META')) {
	define('SEOMETA_GEN_IMAGE_META', true);
}

if (!defined('SEOMETA_OGIMAGE_TARGET_WIDTH')) {
	define('SEOMETA_OGIMAGE_TARGET_WIDTH', 1200);
}

if (!defined('SEOMETA_OGIMAGE_TARGET_HEIGHT')) {
	define('SEOMETA_OGIMAGE_TARGET_HEIGHT', 630);
}

if (!defined('SEOMETA_OGIMAGE_QUERY_VAR')) {
	define('SEOMETA_OGIMAGE_QUERY_VAR', 'seometa_ogimage');
}

if (!defined('SEOMETA_OGIMAGE_FALLBACK_RELATIVE_PATH')) {
	define('SEOMETA_OGIMAGE_FALLBACK_RELATIVE_PATH', 'fp-plugins/seometataginfo/imgs/og-image.png');
}

if (!defined('SEOMETA_OGIMAGE_INFO_APCU_TTL')) {
	define('SEOMETA_OGIMAGE_INFO_APCU_TTL', max(60, (int)($_ENV ['FP_APCU_IO_TTL'] ?? 3600)));
}

if (!defined('SEOMETA_OGIMAGE_BINARY_APCU_TTL')) {
	define('SEOMETA_OGIMAGE_BINARY_APCU_TTL', max(60, (int)($_ENV ['FP_APCU_IO_TTL'] ?? 3600)));
}

if (!defined('SEOMETA_OGIMAGE_BINARY_APCU_MAX_BYTES')) {
	define('SEOMETA_OGIMAGE_BINARY_APCU_MAX_BYTES', 1572864);
}

// generate <link rel="canonical".. true/false
if (!defined('SEOMETA_GEN_CANONICAL')) {
	define('SEOMETA_GEN_CANONICAL', true);
}

/**
 * force comments to point at page (canonical)
 * e.g.
 *   /yy/mm/dd/mypage/comments/ => /yy/mm/dd/mypage/
 *   /yy/mm/dd/mypage/comments/#comments => /yy/mm/dd/mypage/
 */
if (!defined('SEOMETA_HIDECOMMENTS')) {
	define('SEOMETA_HIDECOMMENTS', true);
}

// define storage
define('SEOMETA_DIR', CONTENT_DIR . 'seometa/');
define('SEOMETA_DEFAULT_DIR', SEOMETA_DIR . 'default/');
define('SEOMETA_ENTRY_DIR', SEOMETA_DIR . 'entries/');
define('SEOMETA_STATIC_DIR', SEOMETA_DIR . 'statics/');
define('SEOMETA_CATEGORY_DIR', SEOMETA_DIR . 'categories/');
define('SEOMETA_TAG_DIR', SEOMETA_DIR . 'tags/');
define('SEOMETA_ARCHIVE_DIR', SEOMETA_DIR . 'archives/');

/**
 * This part of the plugin has to interface with
 * the entry admin panel.
 *
 * It saves the meta data of entries/pages.
 */
if (defined('SYSTEM_VER') && version_compare(SYSTEM_VER, '0.1010', '>=') && defined('MOD_ADMIN_PANEL')) {

	/**
	 * plugin_description_entry class
	 *
	 * This class is used to interface the seo meta tag
	 * with the entry editor.
	 */
	class plugin_seometatags_entry {

		/**
		 * "simple" function
		 *
		 * This function adds the Description field to editor
		 */
		function simple() {
			global $fp_config;
			$charset = strtoupper($fp_config ['locale'] ['charset'] ?? 'UTF-8');

			if (!file_exists(SEOMETA_DEFAULT_DIR . 'metatags.ini')) {
				$metatags = "[meta]\n";
				$metatags .= "description=\n";
				$metatags .= "keywords=\n";
				$metatags .= "noindex=0\n";
				$metatags .= "nofollow=0\n";
				$metatags .= "noarchive=0\n";
				$metatags .= "nosnippet=0\n";
				@io_write_file(SEOMETA_DEFAULT_DIR . 'metatags.ini', $metatags);
			}

			if ((isset($_REQUEST ['entry']) && empty($_REQUEST ['entry'])) && (isset($_REQUEST ['timestamp']) && !empty($_REQUEST ['timestamp']))) {
				$id = bdb_idfromtime(BDB_ENTRY, $_REQUEST ['timestamp']);
				$file_meta = SEOMETA_ENTRY_DIR . $id . '_metatags.ini';
			} elseif (!empty($_REQUEST ['entry'])) {
				$id = $_REQUEST ['entry'];
				$file_meta = SEOMETA_ENTRY_DIR . $id . '_metatags.ini';
			} elseif (!empty($_REQUEST ['page'])) {
				$id = $_REQUEST ['page'];
				$file_meta = SEOMETA_STATIC_DIR . $id . '_metatags.ini';
			} elseif (isset($_REQUEST ['p'])) {
				// new blog entry or static page
				$file_meta = SEOMETA_DEFAULT_DIR . 'metatags.ini';
			} else {
				$file_meta = SEOMETA_DEFAULT_DIR . 'metatags.ini';
			}

			if (!file_exists($file_meta)) {
				@io_write_file($file_meta, '');
			}

			$cfg = new iniParser($file_meta);
			$old_desc = $cfg->get('meta', 'description') != false ? wp_specialchars(trim($cfg->get('meta', 'description'))) : (!empty($_REQUEST ['pl_description']) ? $_REQUEST ['pl_description'] : '');
			$old_keywords = $cfg->get('meta', 'keywords') != false ? wp_specialchars(trim($cfg->get('meta', 'keywords'))) : (!empty($_REQUEST ['pl_keywords']) ? $_REQUEST ['pl_keywords'] : '');
			$old_noindex = $cfg->get('meta', 'noindex') != false ? wp_specialchars(trim($cfg->get('meta', 'noindex'))) : (!empty($_REQUEST ['pl_noindex']) ? $_REQUEST ['pl_noindex'] : '0');
			$old_nofollow = $cfg->get('meta', 'nofollow') != false ? wp_specialchars(trim($cfg->get('meta', 'nofollow'))) : (!empty($_REQUEST ['pl_nofollow']) ? $_REQUEST ['pl_nofollow'] : '0');
			$old_noarchive = $cfg->get('meta', 'noarchive') != false ? wp_specialchars(trim($cfg->get('meta', 'noarchive'))) : (!empty($_REQUEST ['pl_noarchive']) ? $_REQUEST ['pl_noarchive'] : '0');
			$old_nosnippet = $cfg->get('meta', 'nosnippet') != false ? wp_specialchars(trim($cfg->get('meta', 'nosnippet'))) : (!empty($_REQUEST ['pl_nosnippet']) ? $_REQUEST ['pl_nosnippet'] : '0');

			$lang = lang_load('plugin:seometataginfo');
			$string = $lang ['admin'] ['plugin'] ['seometataginfo'];

			echo '<fieldset id="plugin_seometataginfo">';
			echo '	<legend>' . $string ['legend_desc'] . '</legend>';
			echo '	<p>' . $string ['description'] . '</p>';
			echo '	<div>';
			echo '		<input id="pl_file_meta" type="hidden" name="pl_file_meta" value="' . $file_meta . '">';
			echo '		<p><label for="pl_description">' . $string ['input_desc'] . '</label>';
			echo '			<input placeholder="' . $lang ['admin'] ['plugin'] ['seometataginfo'] ['sample_desc'] . '" class="maxsize" id="pl_description" type="text" name="pl_description" value="' . htmlspecialchars($old_desc, ENT_QUOTES, $charset) . '"></p>';
			echo '		<p><label for="pl_keywords">' . $string ['input_keywords'] . '</label>';
			echo '			<input placeholder="' . $lang ['admin'] ['plugin'] ['seometataginfo'] ['sample_keywords'] . '" class="maxsize" id="pl_keywords" type="text" name="pl_keywords" value="' . htmlspecialchars($old_keywords, ENT_QUOTES, $charset) . '"></p>';
			echo '		<p>';
			$checked = ($old_noindex === '1') ? ' checked="yes"' : '';
			echo '		<label for="pl_noindex">' . $string ['input_noindex'] . '</label>';
			echo '			<input style="vertical-align: middle; margin: 0px 10px 0px 0px; cursor: pointer;" id="pl_noindex" type="checkbox"' . $checked . ' name="pl_noindex" value="1">';
			$checked = ($old_nofollow === '1') ? ' checked="yes"' : '';
			echo '		<label for="pl_nofollow">' . $string ['input_nofollow'] . '</label>';
			echo '			<input style="vertical-align: middle; margin: 0px 10px 0px 0px; cursor: pointer;" id="pl_nofollow" type="checkbox"' . $checked . ' name="pl_nofollow" value="1">';
			$checked = ($old_noarchive === '1') ? ' checked="yes"' : '';
			echo '		<label for="pl_noarchive">' . $string ['input_noarchive'] . '</label>';
			echo '			<input style="vertical-align: middle; margin: 0px 10px 0px 0px; cursor: pointer;" id="pl_noarchive" type="checkbox"' . $checked . ' name="pl_noarchive" value="1">';
			$checked = ($old_nosnippet === '1') ? ' checked="yes"' : '';
			echo '		<label for="pl_nosnippet">' . $string ['input_nosnippet'] . '</label>';
			echo '			<input style="vertical-align: middle; margin: 0px 10px 0px 0px; cursor: pointer;" id="pl_nosnippet" type="checkbox"' . $checked . ' name="pl_nosnippet" value="1">';
			echo '		</p>';
			echo '	</div>';
			echo '</fieldset>';

			return true;
		}

		function sanitizeSeoField($input) {
			global $allowed_characters_regex;

			if (!is_string($input)) {
				return '';
			}

			$input = preg_replace([
				'/<[^>]*>/',
				'/&lt;|&gt;/',
				'/\bon\w+\s*=\s*["\'][^"\']*["\']/i'
			], '', htmlspecialchars_decode($input, ENT_QUOTES));

			if (!empty($allowed_characters_regex)) {
				$input = preg_replace($allowed_characters_regex, '', $input);
			}

			return trim($input);
		}

		function do_save() {
			global $allowed_characters_regex;

			if (empty($_POST ['pl_file_meta'])) {
				return;
			}

			$sanitize = function($field) {
				return isset($_POST [$field]) ? $this->sanitizeSeoField($_POST [$field]) : '';
			};

			$metatags = 'description=' . (isset($_POST ['pl_description']) ? $this->sanitizeSeoField($_POST ['pl_description']) : '') . "\n";
			$metatags .= 'keywords=' . (isset($_POST ['pl_keywords']) ? $this->sanitizeSeoField($_POST ['pl_keywords']) : '') . "\n";
			$metatags .= 'noindex=' . (isset($_POST ['pl_noindex']) ? trim($_POST ['pl_noindex']) : '0') . "\n";
			$metatags .= 'nofollow=' . (isset($_POST ['pl_nofollow']) ? trim($_POST ['pl_nofollow']) : '0') . "\n";
			$metatags .= 'noarchive=' . (isset($_POST ['pl_noarchive']) ? trim($_POST ['pl_noarchive']) : '0') . "\n";
			$metatags .= 'nosnippet=' . (isset($_POST ['pl_nosnippet']) ? trim($_POST ['pl_nosnippet']) : '0') . "\n";

			if (isset($allowed_characters_regex) && !empty($allowed_characters_regex)) {
				$metatags = preg_replace($allowed_characters_regex, '', $metatags);
			}

			if ($_POST ['pl_file_meta'] !== SEOMETA_DEFAULT_DIR . 'metatags.ini') {
				// Existing blog entry or static page (got file name already)
				@io_write_file($_POST ['pl_file_meta'], "[meta]\n" . $metatags);
			} elseif ($_REQUEST ['p'] === 'entry') {
				// This was a new blog entry
				$new_id = bdb_idfromtime(BDB_ENTRY, $_POST ['timestamp']);
				$file_meta = SEOMETA_ENTRY_DIR . $new_id . '_metatags.ini';
				@io_write_file($file_meta, "[meta]\n" . $metatags);
			} elseif ($_REQUEST ['p'] === 'static') {
				// This was a new static page
				$new_id = $_REQUEST ['id'];
				$file_meta = SEOMETA_STATIC_DIR . $new_id . '_metatags.ini';
				@io_write_file($file_meta, "[meta]\n" . $metatags);
			}
			// @io_write_file(SEOMETA_DIR."log.txt", print_r($_REQUEST, true).print_r($_POST, true));
		}

		/**
		 * "post" function
		 *
		 * This function is called by submit button hook.
		 * It adds $this->save to publish_post hook.
		 */
		function post($data) {
			add_filter('publish_post', array(
				&$this,
				'save'
			), 10, 2);
			return $data;
		}

		/**
		 * "save" function
		 *
		 * This function save the description to a file.
		 */
		function save($id, $arr) {
			$this->do_save();
			return true;
		}

		/**
		 * "save" function
		 *
		 * This function save the description to a file.
		 */
		function save_static($title) {
			$this->do_save();
			return ($title);
		}

		/**
		 * plugin_description_entry function (constructor)
		 *
		 * This function adds some functions to filters system.
		 */
		function __construct() {
			// add_filter('simple_edit_form', array( // is already used by the tag-plugin and is not intended for static pages
			add_filter('simple_metatag_info', array(
				&$this,
				'simple'
			));
			add_filter('admin_entry_write_onsave', array(
				&$this,
				'post'
			));
			add_filter('admin_entry_write_onsavecontinue', array(
				&$this,
				'post'
			));
			add_filter('title_save_pre', array(
				&$this,
				'save_static'
			));
		}

	}

	# Init the class
	if (SEOMETA_MIGRATE_DATA) {
		migrate_old(); // migrate old data from previous version of plugin
	}
	new plugin_seometatags_entry();
}

function output_metatags($seo_desc, $seo_keywords, $seo_noindex, $seo_nofollow, $seo_noarchive, $seo_nosnippet) {
	global $prepend_description;
	global $prepend_keywords;
	global $fp_params, $fp_config;
	$lang = lang_load('plugin:seometataginfo');
	$string = $lang ['plugin'] ['seometataginfo'];
	$charset = strtoupper($fp_config ['locale'] ['charset'] ?? 'UTF-8');

	$lang = $fp_config ['locale'] ['lang'] ?? '';
	$site_title = $fp_config ['general'] ['title'] ?? '';
	$BLOG_BASEURL = $fp_config ['general'] ['www'] ?? '';
	$ogImageMeta = array('url' => '', 'secure_url' => '', 'mime' => '', 'width' => 0, 'height' => 0);
	if (SEOMETA_GEN_IMAGE_META && SEOMETA_GEN_OPEN_GRAPH) {
		$ogImageMeta = seometataginfo_get_og_image_meta($BLOG_BASEURL);
	}

	echo '
		<!-- BOF SEO Metatag Info -->' . "\n";

	if (SEOMETA_GEN_TITLE_META) {
		$metatitle = apply_filters('wp_title', $fp_config ['general'] ['title'], trim($string ['sep']));
		echo '		<meta name="title" content="' . htmlspecialchars($metatitle, ENT_QUOTES, $charset) . '">' . "\n";
		if (SEOMETA_GEN_OPEN_GRAPH) {
			echo '		<meta property="og:title" content="' . htmlspecialchars($metatitle, ENT_QUOTES, $charset) . '">' . "\n";
		}
	}

	if (SEOMETA_GEN_IMAGE_META && SEOMETA_GEN_OPEN_GRAPH && !empty($ogImageMeta ['url'])) {
		echo '		<meta property="og:image" content="' . htmlspecialchars($ogImageMeta ['url'], ENT_QUOTES, $charset) . '">' . "\n";
		echo '		<meta property="og:image:url" content="' . htmlspecialchars($ogImageMeta ['url'], ENT_QUOTES, $charset) . '">' . "\n";
		if (!empty($ogImageMeta ['secure_url'])) {
			echo '		<meta property="og:image:secure_url" content="' . htmlspecialchars($ogImageMeta ['secure_url'], ENT_QUOTES, $charset) . '">' . "\n";
		}
		if (!empty($ogImageMeta ['mime'])) {
			echo '		<meta property="og:image:type" content="' . htmlspecialchars($ogImageMeta ['mime'], ENT_QUOTES, $charset) . '">' . "\n";
		}
		echo '		<meta property="og:image:alt" content="' . htmlspecialchars(($site_title !== '' ? $site_title : 'Preview'), ENT_QUOTES, $charset) . '">' . "\n";
		if (!empty($ogImageMeta ['width'])) {
			echo '		<meta property="og:image:width" content="' . (int)$ogImageMeta ['width'] . '">' . "\n";
		}
		if (!empty($ogImageMeta ['height'])) {
			echo '		<meta property="og:image:height" content="' . (int)$ogImageMeta ['height'] . '">' . "\n";
		}
	}

	$count = 0;
	$count += ($seo_noindex !== '0') ? 1 : 0;
	$count += ($seo_nofollow !== '0') ? 1 : 0;
	$count += ($seo_noarchive !== '0') ? 1 : 0;
	$count += ($seo_nosnippet !== '0') ? 1 : 0;

	// make description unique by adding a page# when paging
	$pagenum = '';
	if (is_paging()) {
		$pagenum = $string ['sep'] . '(' . $string ['pagenum'] . $fp_params ['paged'] . ')';
	}
	// make description unique by adding a comments on comments page
	$comment = '';
	if (is_comments()) {
		$comment = $string ['sep'] . '(' . $string ['comments'] . ')';
	}

	$final_description = trim($seo_desc) === '' ? $fp_config ['general'] ['title'] : $fp_config ['general'] ['title'] . ' - ' . $seo_desc . $comment . $pagenum;
	$encoded_description = htmlspecialchars($final_description, ENT_QUOTES, $charset);

	# Now write the tags
	echo '		<meta name="description" content="' . $encoded_description . '">' . "\n";
	echo '		<meta name="keywords" content="' . $prepend_keywords . $seo_keywords . '">' . "\n";
	if (SEOMETA_GEN_OPEN_GRAPH) {
		echo '		<meta property="og:description" content="' . $encoded_description . '">' . "\n";
	}
	$article_published_time = '';
	$article_section = '';
	$article_tags = array();
	if (is_single()) {
		$article_published_time = seometataginfo_get_article_published_time();
		$article_section = seometataginfo_get_article_section();
		$article_tags = seometataginfo_get_article_tags();
		echo '		<meta name="author" content="' . $fp_config ['general'] ['author'] . '">' . "\n";
		if (SEOMETA_GEN_OPEN_GRAPH) {
			echo '		<meta property="og:type" content="article">' . "\n";
			echo '		<meta property="article:author" content="' . $fp_config ['general'] ['author'] . '">' . "\n";
			if ($article_published_time !== '') {
				echo '		<meta property="article:published_time" content="' . htmlspecialchars($article_published_time, ENT_QUOTES, $charset) . '">' . "\n";
			}
			if ($article_section !== '') {
				echo '		<meta property="article:section" content="' . htmlspecialchars($article_section, ENT_QUOTES, $charset) . '">' . "\n";
			}
			if (!empty($article_tags)) {
				foreach ($article_tags as $article_tag) {
					echo '		<meta property="article:tag" content="' . htmlspecialchars($article_tag, ENT_QUOTES, $charset) . '">' . "\n";
				}
			}
		}
	} else {
		if (SEOMETA_GEN_OPEN_GRAPH) {
			echo '		<meta property="og:type" content="website">' . "\n";
		}
	}
	if (SEOMETA_GEN_OPEN_GRAPH) {
		echo '		<meta property="og:locale" content="' . $lang . '">' . "\n";
		echo '		<meta property="og:site_name" content="' . $site_title . '">' . "\n";
	}

	if ($count > 0) {
		echo '		<meta name="robots" content="';
		$data = ($seo_noindex !== '0') ? 'NOINDEX,' : '';
		$data .= ($seo_nofollow !== '0' ? 'NOFOLLOW,' : '');
		$data .= ($seo_noarchive !== '0' ? 'NOARCHIVE,' : '');
		$data .= ($seo_nosnippet !== '0' ? 'NOSNIPPET,' : '');
		$data = substr($data, 0, -1); // remove trailing comma
		echo $data;
		echo '">' . "\n";
	}
	if (SEOMETA_GEN_CANONICAL) {
		$url = seometataginfo_build_public_url($BLOG_BASEURL);
		if (SEOMETA_HIDECOMMENTS === true) {
			$url = preg_replace('/#comments/', '', $url);
			$url = preg_replace('/comments\//', '', $url);
		}
		echo '		<link rel="canonical" href="' . htmlspecialchars($url, ENT_QUOTES, $charset) . '">' . "\n";
		if (SEOMETA_GEN_OPEN_GRAPH) {
			echo '		<meta property="og:url" content="' . htmlspecialchars($url, ENT_QUOTES, $charset) . '">' . "\n";
		}
	}
	echo '		<!-- EOF SEO Metatag Info -->' . "\n";
}

function seometataginfo_append_query_args($url, $args) {
	$url = is_string($url) ? trim($url) : '';
	if ($url === '') {
		return '';
	}

	$fragment = '';
	$fragmentPos = strpos($url, '#');
	if ($fragmentPos !== false) {
		$fragment = substr($url, $fragmentPos);
		$url = substr($url, 0, $fragmentPos);
	}

	$query = http_build_query(is_array($args) ? $args : array(), '', '&', PHP_QUERY_RFC3986);
	if ($query === '') {
		return $url . $fragment;
	}

	return $url . (strpos($url, '?') === false ? '?' : '&') . $query . $fragment;
}

function seometataginfo_get_runtime_config() {
	global $fp_config;

	if (isset($fp_config) && is_array($fp_config) && !empty($fp_config)) {
		return $fp_config;
	}

	if (isset($GLOBALS ['EARLY_FP_CONFIG']) && is_array($GLOBALS ['EARLY_FP_CONFIG'])) {
		return $GLOBALS ['EARLY_FP_CONFIG'];
	}

	return array();
}

function seometataginfo_apcu_available() {
	return function_exists('is_apcu_on') && is_apcu_on();
}

function seometataginfo_normalize_cache_ttl($ttl, $defaultTtl) {
	$ttl = (int)$ttl;
	$defaultTtl = (int)$defaultTtl;
	if ($ttl < 1) {
		$ttl = $defaultTtl;
	}
	if ($ttl < 1) {
		$ttl = 3600;
	}
	return $ttl;
}

function seometataginfo_get_og_image_binary_apcu_max_bytes() {
	if (!defined('SEOMETA_OGIMAGE_BINARY_APCU_MAX_BYTES')) {
		return 1572864;
	}

	$maxBytes = (int)constant('SEOMETA_OGIMAGE_BINARY_APCU_MAX_BYTES');
	if ($maxBytes < 0) {
		return 0;
	}

	return $maxBytes;
}

function seometataginfo_build_image_info($baseUrl, $relativePath, $absolutePath, $mime, $width, $height, $type, $mtime, $sizeBytes) {
	return array(
		'relative_path' => (string)$relativePath,
		'absolute_path' => (string)$absolutePath,
		'url' => seometataginfo_url_join($baseUrl, $relativePath),
		'mime' => (string)$mime,
		'width' => (int)$width,
		'height' => (int)$height,
		'type' => (int)$type,
		'mtime' => (int)$mtime,
		'size_bytes' => (int)$sizeBytes,
	);
}

function seometataginfo_get_image_info_apcu_key($absolutePath, $mtime, $sizeBytes) {
	return 'seometa:og:imageinfo:v1:' . md5((string)$absolutePath . '|' . (string)$mtime . '|' . (string)$sizeBytes);
}

function seometataginfo_get_og_image_binary_cache_key($imageInfo, $targetWidth, $targetHeight) {
	if (!is_array($imageInfo)) {
		return '';
	}

	$absolutePath = isset($imageInfo ['absolute_path']) ? (string)$imageInfo ['absolute_path'] : '';
	$type = isset($imageInfo ['type']) ? (int)$imageInfo ['type'] : 0;
	$mtime = isset($imageInfo ['mtime']) ? (int)$imageInfo ['mtime'] : 0;
	$sizeBytes = isset($imageInfo ['size_bytes']) ? (int)$imageInfo ['size_bytes'] : 0;
	if ($absolutePath === '' || $type < 1) {
		return '';
	}

	return 'seometa:og:imagebin:v1:' . md5($absolutePath . '|' . $type . '|' . $mtime . '|' . $sizeBytes . '|' . (int)$targetWidth . 'x' . (int)$targetHeight);
}

function seometataginfo_get_cached_og_image_binary($imageInfo, $targetWidth, $targetHeight) {
	if (!seometataginfo_apcu_available()) {
		return null;
	}

	$key = seometataginfo_get_og_image_binary_cache_key($imageInfo, $targetWidth, $targetHeight);
	if ($key === '') {
		return null;
	}

	$hit = false;
	$cached = apcu_get($key, $hit);
	if (!$hit || !is_array($cached)) {
		return null;
	}

	$body = isset($cached ['body']) ? $cached ['body'] : null;
	$mime = isset($cached ['mime']) ? (string)$cached ['mime'] : '';
	if (!is_string($body) || $body === '' || $mime === '') {
		return null;
	}

	return array(
		'body' => $body,
		'mime' => $mime,
	);
}

function seometataginfo_store_og_image_binary_cache($imageInfo, $targetWidth, $targetHeight, $body, $mime) {
	if (!seometataginfo_apcu_available()) {
		return false;
	}

	$body = is_string($body) ? $body : '';
	$mime = is_string($mime) ? $mime : '';
	if ($body === '' || $mime === '') {
		return false;
	}

	$maxBytes = seometataginfo_get_og_image_binary_apcu_max_bytes();
	if ($maxBytes !== 0 && strlen($body) > $maxBytes) {
		return false;
	}

	$key = seometataginfo_get_og_image_binary_cache_key($imageInfo, $targetWidth, $targetHeight);
	if ($key === '') {
		return false;
	}

	$ttl = seometataginfo_normalize_cache_ttl(SEOMETA_OGIMAGE_BINARY_APCU_TTL, 3600);
	return @apcu_set($key, array('body' => $body, 'mime' => $mime), $ttl);
}

function seometataginfo_send_image_content_headers($mime, $contentLength) {
	$mime = is_string($mime) ? trim($mime) : '';
	if ($mime !== '') {
		@header('Content-Type: ' . $mime);
	}
	if (function_exists('header_remove')) {
		@header_remove('Content-Length');
	}
	$contentLength = (int)$contentLength;
	if ($contentLength > 0) {
		@header('Content-Length: ' . $contentLength);
	}
}

function seometataginfo_output_binary_image($body, $mime, $imageInfo, $targetWidth, $targetHeight) {
	$body = is_string($body) ? $body : '';
	$mime = is_string($mime) ? trim($mime) : '';
	if ($body === '' || $mime === '' || !is_array($imageInfo)) {
		return false;
	}

	seometataginfo_send_image_cache_headers($imageInfo, (int)$targetWidth, (int)$targetHeight);
	seometataginfo_send_image_content_headers($mime, strlen($body));
	echo $body;
	return true;
}

function seometataginfo_capture_image_resource_output($image, $imageInfo) {
	if ((!is_object($image) && !is_resource($image)) || !is_array($imageInfo)) {
		return null;
	}

	$type = isset($imageInfo ['type']) ? (int)$imageInfo ['type'] : 0;
	if ($type !== 2 && $type !== 3) {
		return null;
	}

	ob_start();
	$ok = false;
	if ($type === 3 && function_exists('imagepng')) {
		$ok = imagepng($image);
	} elseif ($type === 2 && function_exists('imagejpeg')) {
		$ok = imagejpeg($image, null, 90);
	}
	$body = ob_get_clean();
	if (!$ok || !is_string($body) || $body === '') {
		return null;
	}
	return $body;
}

/**
 * Reads validated image metadata for a relative file path.
 *
 * @param string $baseUrl
 * @param string $relativePath
 * @return array<string,mixed>
 */
function seometataginfo_get_supported_image_info($baseUrl, $relativePath) {
	static $local = array();

	$imageInfo = seometataginfo_build_image_info($baseUrl, '', '', '', 0, 0, 0, 0, 0);
	$relativePath = is_string($relativePath) ? trim($relativePath) : '';
	if ($relativePath === '') {
		return $imageInfo;
	}

	$absolutePath = ABS_PATH . ltrim($relativePath, '/');
	$cacheFingerprint = '';
	$st = @stat($absolutePath);
	if (!is_array($st) || !is_file($absolutePath) || !is_readable($absolutePath)) {
		return $imageInfo;
	}

	$mtime = (int)($st ['mtime'] ?? 0);
	$sizeBytes = (int)($st ['size'] ?? 0);
	$cacheFingerprint = $absolutePath . '|' . $mtime . '|' . $sizeBytes . '|' . (string)$baseUrl;
	if (isset($local [$cacheFingerprint]) && is_array($local [$cacheFingerprint])) {
		return $local [$cacheFingerprint];
	}

	$cachedMeta = null;
	if (seometataginfo_apcu_available()) {
		$hit = false;
		$cachedMeta = apcu_get(seometataginfo_get_image_info_apcu_key($absolutePath, $mtime, $sizeBytes), $hit);
		if ($hit && is_array($cachedMeta)) {
			$width = isset($cachedMeta ['width']) ? (int)$cachedMeta ['width'] : 0;
			$height = isset($cachedMeta ['height']) ? (int)$cachedMeta ['height'] : 0;
			$type = isset($cachedMeta ['type']) ? (int)$cachedMeta ['type'] : 0;
			$mime = isset($cachedMeta ['mime']) ? (string)$cachedMeta ['mime'] : '';
			if ($width > 0 && $height > 0 && in_array($type, array(2, 3), true) && $mime !== '') {
				return $local [$cacheFingerprint] = seometataginfo_build_image_info(
					$baseUrl,
					$relativePath,
					$absolutePath,
					$mime,
					$width,
					$height,
					$type,
					$mtime,
					$sizeBytes
				);
			}
		}
	}

	$sizeInfo = @getimagesize($absolutePath);
	if (!is_array($sizeInfo) || empty($sizeInfo [0]) || empty($sizeInfo [1]) || empty($sizeInfo [2])) {
		return $imageInfo;
	}

	$type = (int)$sizeInfo [2];
	$mime = isset($sizeInfo ['mime']) && is_string($sizeInfo ['mime']) ? trim($sizeInfo ['mime']) : '';
	if (!in_array($type, array(2, 3), true)) {
		return $imageInfo;
	}
	if ($mime === '') {
		$mime = ($type === 2) ? 'image/jpeg' : 'image/png';
	}

	$imageInfo = seometataginfo_build_image_info(
		$baseUrl,
		$relativePath,
		$absolutePath,
		$mime,
		(int)$sizeInfo [0],
		(int)$sizeInfo [1],
		$type,
		$mtime,
		$sizeBytes
	);
	$local [$cacheFingerprint] = $imageInfo;

	if (seometataginfo_apcu_available()) {
		@apcu_set(
			seometataginfo_get_image_info_apcu_key($absolutePath, $mtime, $sizeBytes),
			array(
				'mime' => $mime,
				'width' => (int)$sizeInfo [0],
				'height' => (int)$sizeInfo [1],
				'type' => $type,
			),
			seometataginfo_normalize_cache_ttl(SEOMETA_OGIMAGE_INFO_APCU_TTL, 3600)
		);
	}

	return $imageInfo;
}

/**
 * Returns metadata for the currently active theme preview image.
 *
 * The SEO plugin historically used the active theme/style preview image for og:image.
 * We keep that source as the primary candidate, but community themes/styles may not
 * ship a preview image. In that case a bundled plugin fallback image is used.
 *
 * @param string $baseUrl
 * @return array<string,mixed>
 */
function seometataginfo_get_theme_preview_image_info($baseUrl) {
	static $cache = array();

	$config = seometataginfo_get_runtime_config();
	$theme = isset($config ['general'] ['theme']) && is_string($config ['general'] ['theme']) ? trim($config ['general'] ['theme']) : '';
	$style = isset($config ['general'] ['style']) && is_string($config ['general'] ['style']) ? trim($config ['general'] ['style']) : '';
	$cacheKey = $theme . '|' . $style . '|' . (string)$baseUrl;

	if (isset($cache [$cacheKey]) && is_array($cache [$cacheKey])) {
		return $cache [$cacheKey];
	}

	$cache [$cacheKey] = array(
		'relative_path' => '',
		'absolute_path' => '',
		'url' => '',
		'mime' => '',
		'width' => 0,
		'height' => 0,
		'type' => 0,
		'mtime' => 0,
		'size_bytes' => 0,
	);

	if ($theme === '') {
		return $cache [$cacheKey];
	}

	$candidates = array();
	if ($style !== '') {
		$candidates [] = 'fp-interface/themes/' . $theme . '/' . $style . '/preview.png';
		$candidates [] = 'fp-interface/themes/' . $theme . '/' . $style . '/preview.jpg';
		$candidates [] = 'fp-interface/themes/' . $theme . '/' . $style . '/preview.jpeg';
	}
	$candidates [] = 'fp-interface/themes/' . $theme . '/preview.png';
	$candidates [] = 'fp-interface/themes/' . $theme . '/preview.jpg';
	$candidates [] = 'fp-interface/themes/' . $theme . '/preview.jpeg';

	foreach ($candidates as $relativePath) {
		$imageInfo = seometataginfo_get_supported_image_info($baseUrl, $relativePath);
		if (!empty($imageInfo ['absolute_path'])) {
			$cache [$cacheKey] = $imageInfo;
			break;
		}
	}

	return $cache [$cacheKey];
}

function seometataginfo_get_plugin_fallback_image_info($baseUrl) {
	static $cache = array();

	$cacheKey = (string)$baseUrl;
	if (!isset($cache [$cacheKey]) || !is_array($cache [$cacheKey])) {
		$cache [$cacheKey] = seometataginfo_get_supported_image_info($baseUrl, SEOMETA_OGIMAGE_FALLBACK_RELATIVE_PATH);
	}

	return $cache [$cacheKey];
}

function seometataginfo_get_og_image_source_info($baseUrl) {
	$imageInfo = seometataginfo_get_theme_preview_image_info($baseUrl);
	if (!empty($imageInfo ['absolute_path'])) {
		return $imageInfo;
	}

	return seometataginfo_get_plugin_fallback_image_info($baseUrl);
}

function seometataginfo_can_transform_og_image($imageInfo) {
	if (!is_array($imageInfo) || empty($imageInfo ['absolute_path']) || empty($imageInfo ['type'])) {
		return false;
	}

	if (!function_exists('imagecreatetruecolor') || !function_exists('imagecopyresampled') || !function_exists('imagefilledrectangle')) {
		return false;
	}

	$type = (int)$imageInfo ['type'];
	if ($type === 2) {
		return function_exists('imagecreatefromjpeg') && function_exists('imagejpeg');
	}
	if ($type === 3) {
		return function_exists('imagecreatefrompng') && function_exists('imagepng');
	}

	return false;
}

function seometataginfo_build_og_image_url($baseUrl, $imageInfo) {
	$entryPoint = seometataginfo_url_join($baseUrl, 'index.php');
	if ($entryPoint === '') {
		return '';
	}

	$version = '';
	if (is_array($imageInfo) && !empty($imageInfo ['mtime'])) {
		$version = (string)(int)$imageInfo ['mtime'];
	}

	return seometataginfo_append_query_args($entryPoint, array(
		SEOMETA_OGIMAGE_QUERY_VAR => '1',
		'v' => $version
	));
}

/**
 * Returns the effective og:image metadata for the current request.
 *
 * When GD is available we publish a dynamic 1200x630 endpoint that renders
 * the transformed image fully in memory. Otherwise we keep the original image.
 *
 * @param string $baseUrl
 * @return array<string,mixed>
 */
function seometataginfo_get_og_image_meta($baseUrl) {
	$imageInfo = seometataginfo_get_og_image_source_info($baseUrl);
	if (empty($imageInfo ['url'])) {
		return array(
			'url' => '',
			'secure_url' => '',
			'mime' => '',
			'width' => 0,
			'height' => 0,
		);
	}

	if (seometataginfo_can_transform_og_image($imageInfo)) {
		$dynamicUrl = seometataginfo_build_og_image_url($baseUrl, $imageInfo);
		if ($dynamicUrl !== '') {
			return array(
				'url' => $dynamicUrl,
				'secure_url' => (stripos($dynamicUrl, 'https://') === 0) ? $dynamicUrl : '',
				'mime' => (string)$imageInfo ['mime'],
				'width' => (int)SEOMETA_OGIMAGE_TARGET_WIDTH,
				'height' => (int)SEOMETA_OGIMAGE_TARGET_HEIGHT,
			);
		}
	}

	$url = (string)$imageInfo ['url'];
	return array(
		'url' => $url,
		'secure_url' => (stripos($url, 'https://') === 0) ? $url : '',
		'mime' => (string)$imageInfo ['mime'],
		'width' => (int)$imageInfo ['width'],
		'height' => (int)$imageInfo ['height'],
	);
}

function seometataginfo_is_og_image_request() {
	if (!isset($_GET [SEOMETA_OGIMAGE_QUERY_VAR])) {
		return false;
	}

	$value = $_GET [SEOMETA_OGIMAGE_QUERY_VAR];
	if (is_array($value)) {
		return false;
	}

	$value = trim((string)$value);
	return in_array($value, array('', '1', 'true', 'yes'), true);
}

function seometataginfo_send_status($code) {
	$code = (int)$code;
	if (function_exists('http_response_code')) {
		http_response_code($code);
		return;
	}

	$protocol = isset($_SERVER ['SERVER_PROTOCOL']) && is_string($_SERVER ['SERVER_PROTOCOL']) && $_SERVER ['SERVER_PROTOCOL'] !== '' ? $_SERVER ['SERVER_PROTOCOL'] : 'HTTP/1.1';
	if ($code === 304) {
		@header($protocol . ' 304 Not Modified');
	} elseif ($code === 404) {
		@header($protocol . ' 404 Not Found');
	}
}

function seometataginfo_send_image_cache_headers($imageInfo, $targetWidth, $targetHeight) {
	if (!is_array($imageInfo)) {
		return;
	}

	$mtime = !empty($imageInfo ['mtime']) ? (int)$imageInfo ['mtime'] : time();
	$etagSource = (string)$mtime . '|' . (string)$targetWidth . 'x' . (string)$targetHeight . '|' . (string)($imageInfo ['absolute_path'] ?? '');
	$etag = 'W/"' . sha1($etagSource) . '"';

	if (function_exists('header_remove')) {
		@header_remove('Content-Type');
	}
	@header('Cache-Control: public, max-age=86400');
	@header('ETag: ' . $etag);
	@header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $mtime) . ' GMT');

	$ifNoneMatch = isset($_SERVER ['HTTP_IF_NONE_MATCH']) ? trim((string)$_SERVER ['HTTP_IF_NONE_MATCH']) : '';
	$ifModifiedSince = isset($_SERVER ['HTTP_IF_MODIFIED_SINCE']) ? trim((string)$_SERVER ['HTTP_IF_MODIFIED_SINCE']) : '';

	if ($ifNoneMatch !== '' && $ifNoneMatch === $etag) {
		seometataginfo_send_status(304);
		exit;
	}

	if ($ifModifiedSince !== '') {
		$since = strtotime($ifModifiedSince);
		if ($since !== false && $since >= $mtime) {
			seometataginfo_send_status(304);
			exit;
		}
	}
}

function seometataginfo_output_image_file($imageInfo) {
	if (!is_array($imageInfo) || empty($imageInfo ['absolute_path']) || empty($imageInfo ['mime'])) {
		return false;
	}

	seometataginfo_send_image_cache_headers($imageInfo, (int)($imageInfo ['width'] ?? 0), (int)($imageInfo ['height'] ?? 0));
	seometataginfo_send_image_content_headers((string)$imageInfo ['mime'], (int)($imageInfo ['size_bytes'] ?? 0));
	readfile($imageInfo ['absolute_path']);
	return true;
}

function seometataginfo_image_create_from_file($imageInfo) {
	if (!is_array($imageInfo) || empty($imageInfo ['absolute_path']) || empty($imageInfo ['type'])) {
		return null;
	}

	$type = (int)$imageInfo ['type'];
	if ($type === 2 && function_exists('imagecreatefromjpeg')) {
		return @imagecreatefromjpeg($imageInfo ['absolute_path']);
	}
	if ($type === 3 && function_exists('imagecreatefrompng')) {
		return @imagecreatefrompng($imageInfo ['absolute_path']);
	}

	return null;
}

function seometataginfo_output_image_resource($image, $imageInfo) {
	if (!is_object($image) && !is_resource($image)) {
		return false;
	}
	if (!is_array($imageInfo) || empty($imageInfo ['type'])) {
		return false;
	}

	$type = (int)$imageInfo ['type'];
	if ($type === 3 && function_exists('imagepng')) {
		@header('Content-Type: image/png');
		imagepng($image);
		return true;
	}

	if ($type === 2 && function_exists('imagejpeg')) {
		@header('Content-Type: image/jpeg');
		imagejpeg($image, null, 90);
		return true;
	}

	return false;
}

function seometataginfo_destroy_image_resource(&$image) {
	if ($image === null) {
		return;
	}

	if ((is_resource($image) || is_object($image)) && function_exists('imagedestroy') && (!function_exists('is_php85_plus') || !is_php85_plus())) {
		imagedestroy($image);
	}
	$image = null;
}

function seometataginfo_render_og_image($imageInfo, $targetWidth, $targetHeight) {
	$targetWidth = max(1, (int)$targetWidth);
	$targetHeight = max(1, (int)$targetHeight);

	$cachedBinary = seometataginfo_get_cached_og_image_binary($imageInfo, $targetWidth, $targetHeight);
	if (is_array($cachedBinary) && isset($cachedBinary ['body'], $cachedBinary ['mime'])) {
		return seometataginfo_output_binary_image($cachedBinary ['body'], $cachedBinary ['mime'], $imageInfo, $targetWidth, $targetHeight);
	}

	$source = seometataginfo_image_create_from_file($imageInfo);
	if ($source === null) {
		return false;
	}

	$sourceWidth = !empty($imageInfo ['width']) ? (int)$imageInfo ['width'] : 0;
	$sourceHeight = !empty($imageInfo ['height']) ? (int)$imageInfo ['height'] : 0;
	if ($sourceWidth < 1 || $sourceHeight < 1) {
		seometataginfo_destroy_image_resource($source);
		return false;
	}

	$canvas = @imagecreatetruecolor($targetWidth, $targetHeight);
	if (!$canvas) {
		seometataginfo_destroy_image_resource($source);
		return false;
	}

	imagealphablending($canvas, true);
	imagesavealpha($canvas, false);
	$background = imagecolorallocate($canvas, 255, 255, 255);
	imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $background);

	$scale = min($targetWidth / $sourceWidth, $targetHeight / $sourceHeight);
	$destWidth = max(1, (int)round($sourceWidth * $scale));
	$destHeight = max(1, (int)round($sourceHeight * $scale));
	$destX = (int)floor(($targetWidth - $destWidth) / 2);
	$destY = (int)floor(($targetHeight - $destHeight) / 2);

	$copied = imagecopyresampled($canvas, $source, $destX, $destY, 0, 0, $destWidth, $destHeight, $sourceWidth, $sourceHeight);
	seometataginfo_destroy_image_resource($source);
	if (!$copied) {
		seometataginfo_destroy_image_resource($canvas);
		return false;
	}

	$body = null;
	$mime = isset($imageInfo ['mime']) ? (string)$imageInfo ['mime'] : '';
	if (seometataginfo_apcu_available()) {
		$body = seometataginfo_capture_image_resource_output($canvas, $imageInfo);
		if (is_string($body) && $body !== '') {
			seometataginfo_store_og_image_binary_cache($imageInfo, $targetWidth, $targetHeight, $body, $mime);
			$result = seometataginfo_output_binary_image($body, $mime, $imageInfo, $targetWidth, $targetHeight);
			seometataginfo_destroy_image_resource($canvas);
			return $result;
		}
	}

	seometataginfo_send_image_cache_headers($imageInfo, $targetWidth, $targetHeight);
	$result = seometataginfo_output_image_resource($canvas, $imageInfo);
	seometataginfo_destroy_image_resource($canvas);

	return $result;
}

function seometataginfo_serve_og_image() {
	$config = seometataginfo_get_runtime_config();
	$baseUrl = isset($config ['general'] ['www']) ? $config ['general'] ['www'] : '';
	$imageInfo = seometataginfo_get_og_image_source_info($baseUrl);
	if (empty($imageInfo ['absolute_path']) || empty($imageInfo ['mime'])) {
		seometataginfo_send_status(404);
		return;
	}

	while (ob_get_level() > 0) {
		ob_end_clean();
	}

	if (seometataginfo_can_transform_og_image($imageInfo)) {
		$served = seometataginfo_render_og_image($imageInfo, (int)SEOMETA_OGIMAGE_TARGET_WIDTH, (int)SEOMETA_OGIMAGE_TARGET_HEIGHT);
		if ($served) {
			exit;
		}
	}

	if (seometataginfo_output_image_file($imageInfo)) {
		exit;
	}

	seometataginfo_send_status(404);
	exit;
}

/**
 * Joins a base URL with a relative path, guaranteeing exactly one slash.
 */
function seometataginfo_url_join($baseUrl, $path) {
	$baseUrl = is_string($baseUrl) ? trim($baseUrl) : '';
	$path = is_string($path) ? trim($path) : '';
	if ($baseUrl === '') {
		return $path;
	}
	return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
}

/**
 * Builds a stable, public-facing URL for canonical/og:url.
 *
 * Prefer the configured base URL (general.www) over SERVER_NAME to behave
 * correctly behind proxies and on shared hosting, and strip common tracking
 * parameters that would otherwise create duplicate URLs for social scrapers.
 */
function seometataginfo_build_public_url($baseUrl) {
	$baseUrl = is_string($baseUrl) ? trim($baseUrl) : '';
	$url = currentPageURL();
	$req = isset($_SERVER ['REQUEST_URI']) ? (string)$_SERVER ['REQUEST_URI'] : '';

	if ($baseUrl !== '' && preg_match('~^https?://~i', $baseUrl) && $req !== '') {
		$baseUrlNoSlash = rtrim($baseUrl, '/');
		$basePath = parse_url($baseUrlNoSlash, PHP_URL_PATH);
		$basePath = is_string($basePath) ? rtrim($basePath, '/') : '';

		$reqPath = $req;
		$reqQuery = '';
		$posQ = strpos($req, '?');
		if ($posQ !== false) {
			$reqPath = substr($req, 0, $posQ);
			$reqQuery = substr($req, $posQ);
		}

		$relPath = $reqPath;
		if ($basePath !== '' && $basePath !== '/' && strpos($reqPath, $basePath) === 0) {
			$relPath = substr($reqPath, strlen($basePath));
		}
		$relPath = ltrim($relPath, '/');
		$url = $baseUrlNoSlash . '/' . $relPath . $reqQuery;
	}

	return seometataginfo_strip_tracking_params($url);
}

/**
 * Resolves the current single-entry context to a stable entry ID and parsed entry data.
 *
 * The function prefers the currently queried entry from the FlatPress query object,
 * then falls back to the requested entry parameter and finally parses the entry file
 * if necessary. The result is cached for the duration of the request.
 *
 * @return array{id:string,entry:array}
 */
function seometataginfo_get_current_single_entry_data() {
	global $fpdb, $fp_params;

	static $cache = null;

	if ($cache !== null) {
		return $cache;
	}

	$cache = array(
		'id' => '',
		'entry' => array()
	);

	if (!is_single()) {
		return $cache;
	}

	if (isset($fpdb) && is_object($fpdb) && method_exists($fpdb, 'getQuery')) {
		$q = &$fpdb->getQuery();
		if (is_object($q) && method_exists($q, 'peekEntry')) {
			$peek = @$q->peekEntry();
			if (is_array($peek) && isset($peek [0])) {
				$cache ['id'] = is_string($peek [0]) ? $peek [0] : '';
				$cache ['entry'] = (isset($peek [1]) && is_array($peek [1])) ? $peek [1] : array();
			}
		}
	}

	if ($cache ['id'] === '' && !empty($fp_params ['entry']) && is_string($fp_params ['entry'])) {
		$cache ['id'] = $fp_params ['entry'];
	}

	if (empty($cache ['entry']) && $cache ['id'] !== '' && function_exists('entry_parse')) {
		$parsed = entry_parse($cache ['id']);
		if (is_array($parsed)) {
			$cache ['entry'] = $parsed;
		}
	}

	return $cache;
}

/**
 * Builds a category path from top-level category to leaf category.
 *
 * FlatPress stores hierarchical categories in categories.txt. For nested
 * categories we export a readable path using a stable separator so the
 * Open Graph section remains understandable outside the CMS context.
 *
 * @param string|int $category_id
 *   The FlatPress category ID.
 * @param string $separator
 *   String used between nested category labels.
 * @return string
 *   The resolved category path or an empty string when no valid category exists.
 */
function seometataginfo_get_category_path($category_id, $separator = '/') {
	$category_id = is_scalar($category_id) ? trim((string)$category_id) : '';
	if ($category_id === '' || !preg_match('/^\d+$/', $category_id)) {
		return '';
	}

	if (!function_exists('entry_categories_get') || !function_exists('entry_categories_list')) {
		return '';
	}

	$defs = entry_categories_get('defs');
	$parents = entry_categories_list();

	if (!is_array($defs) || !isset($defs [$category_id]) || !is_array($parents)) {
		return '';
	}

	$path = array();
	$seen = array();
	$current = $category_id;

	while ($current !== '' && $current !== '0' && !isset($seen [$current])) {
		$seen [$current] = true;

		if (!isset($defs [$current]) || trim((string)$defs [$current]) === '') {
			break;
		}

		array_unshift($path, trim((string)$defs [$current]));

		if (!isset($parents [$current])) {
			break;
		}

		$parent = trim((string)$parents [$current]);
		if ($parent === '' || $parent === '0' || $parent === $current) {
			break;
		}

		$current = $parent;
	}

	return !empty($path) ? implode($separator, $path) : '';
}

/**
 * Returns the Open Graph article section for the current single entry/comments view.
 *
 * Because Open Graph expects a single high-level section string, the plugin uses
 * the first real FlatPress category assigned to the entry and expands nested
 * categories to a readable hierarchy path.
 */
function seometataginfo_get_article_section() {
	$data = seometataginfo_get_current_single_entry_data();
	$entry = isset($data ['entry']) && is_array($data ['entry']) ? $data ['entry'] : array();

	if (empty($entry ['categories']) || !is_array($entry ['categories'])) {
		return '';
	}

	foreach ($entry ['categories'] as $category_id) {
		$category_id = is_scalar($category_id) ? trim((string)$category_id) : '';
		if ($category_id === '' || !preg_match('/^\d+$/', $category_id) || $category_id === '0') {
			continue;
		}

		$section = seometataginfo_get_category_path($category_id);
		if ($section !== '') {
			return $section;
		}
	}

	return '';
}

/**
 * Returns whether the tag plugin is installed and enabled for the current request.
 */
function seometataginfo_is_tag_plugin_enabled() {
	global $fp_plugins;

	if (!is_array($fp_plugins) || !in_array('tag', $fp_plugins, true)) {
		return false;
	}

	if (function_exists('plugin_exists') && !plugin_exists('tag')) {
		return false;
	}

	return true;
}

/**
 * Returns the current article tags from the active FlatPress tag plugin.
 *
 * The Open Graph protocol models article:tag as an array of strings, so callers
 * should emit one meta tag per returned item.
 *
 * @return string[]
 */
function seometataginfo_get_article_tags() {
	global $plugin_tag;

	if (!is_single() || !seometataginfo_is_tag_plugin_enabled()) {
		return array();
	}

	$data = seometataginfo_get_current_single_entry_data();
	$entry_id = isset($data ['id']) ? trim((string)$data ['id']) : '';
	$entry = isset($data ['entry']) && is_array($data ['entry']) ? $data ['entry'] : array();
	$tags = array();

	if (isset($plugin_tag) && is_object($plugin_tag) && isset($plugin_tag->entry_class) && is_object($plugin_tag->entry_class)) {
		$entry_class = $plugin_tag->entry_class;

		if ($entry_id !== '' && method_exists($entry_class, 'entryTags')) {
			$tags = $entry_class->entryTags($entry_id);
		} elseif (!empty($entry ['content']) && is_string($entry ['content']) && method_exists($entry_class, 'tag_list')) {
			$before = isset($entry_class->tags) && is_array($entry_class->tags) ? $entry_class->tags : array();
			$entry_class->tag_list($entry ['content']);
			$tags = isset($entry_class->tags) && is_array($entry_class->tags) ? $entry_class->tags : array();
			$entry_class->tags = $before;
		}
	}

	if (!is_array($tags) || empty($tags)) {
		return array();
	}

	$clean = array();
	foreach ($tags as $tag) {
		if (!is_scalar($tag)) {
			continue;
		}
		$tag = trim((string)$tag);
		if ($tag === '') {
			continue;
		}
		$clean [$tag] = $tag;
	}

	return array_values($clean);
}

/**
 * Returns the published time of the current single entry/comments view in ISO 8601.
 *
 * Prefer the explicit DATE field stored with the entry; fall back to the entry ID
 * timestamp if needed so older or incomplete content still gets valid metadata.
 */
function seometataginfo_get_article_published_time() {
	$data = seometataginfo_get_current_single_entry_data();
	$entry_id = isset($data ['id']) ? (string)$data ['id'] : '';
	$entry = isset($data ['entry']) && is_array($data ['entry']) ? $data ['entry'] : array();

	if (isset($entry ['date']) && is_numeric($entry ['date']) && function_exists('date_iso8601')) {
		$published = date_iso8601($entry ['date']);
		if ($published !== '') {
			return $published;
		}
	}

	if ($entry_id !== '' && function_exists('date_id_to_iso8601')) {
		return date_id_to_iso8601($entry_id);
	}

	return '';
}

/**
 * Removes common tracking parameters (fbclid/utm/gclid/...) from an URL.
 */
function seometataginfo_strip_tracking_params($url) {
	if (!is_string($url) || $url === '') {
		return $url;
	}
	$parts = parse_url($url);
	if (!$parts || empty($parts ['query'])) {
		return $url;
	}

	$query = array();
	parse_str((string)$parts ['query'], $query);
	$stripKeys = array(
		'fbclid',
		'gclid',
		'yclid',
		'mc_cid',
		'mc_eid',
		'igshid',
		'_hsenc',
		'_hsmi',
		'utm_source',
		'utm_medium',
		'utm_campaign',
		'utm_term',
		'utm_content',
	);
	foreach ($stripKeys as $k) {
		if (array_key_exists($k, $query)) {
			unset($query [$k]);
		}
	}

	$newQuery = http_build_query($query);
	$rebuilt = '';
	if (!empty($parts ['scheme'])) {
		$rebuilt .= $parts ['scheme'] . '://';
	}
	if (!empty($parts ['user'])) {
		$rebuilt .= $parts ['user'];
		if (!empty($parts ['pass'])) {
			$rebuilt .= ':' . $parts ['pass'];
		}
		$rebuilt .= '@';
	}
	if (!empty($parts ['host'])) {
		$rebuilt .= $parts ['host'];
	}
	if (!empty($parts ['port'])) {
		$rebuilt .= ':' . $parts ['port'];
	}
	$rebuilt .= isset($parts ['path']) ? $parts ['path'] : '';
	if ($newQuery !== '') {
		$rebuilt .= '?' . $newQuery;
	}
	if (!empty($parts ['fragment'])) {
		$rebuilt .= '#' . $parts ['fragment'];
	}
	return $rebuilt;
}

function process_meta($file_meta, $type, $id, $sep) {
	global $fp_params;
	global $seo_default;

	if (empty($seo_default ['description'])) {
		$seo_default ['description'] = $type . $sep . $id;
	}
	if (empty($seo_default ['keywords'])) {
		$seo_default ['keywords'] = $type;
	}
	if (!isset($seo_default ['noindex'])) {
		$seo_default ['noindex'] = '0';
	}
	if (!isset($seo_default ['nofollow'])) {
		$seo_default ['nofollow'] = '0';
	}
	if (!isset($seo_default ['noarchive'])) {
		$seo_default ['noarchive'] = '0';
	}
	if (!isset($seo_default ['nosnippet'])) {
		$seo_default ['nosnippet'] = '0';
	}

	if (!file_exists($file_meta)) {
		$metatags = "[meta]\n";
		$metatags .= "description=" . $seo_default ['description'] . "\n";
		$metatags .= "keywords=" . $seo_default ['keywords'] . "\n";
		$metatags .= "noindex=" . $seo_default ['noindex'] . "\n";
		$metatags .= "nofollow=" . $seo_default ['nofollow'] . "\n";
		$metatags .= "noarchive=" . $seo_default ['noarchive'] . "\n";
		$metatags .= "nosnippet=" . $seo_default ['nosnippet'] . "\n";
		@io_write_file($file_meta, $metatags);
	}

	$cfg = new iniParser($file_meta);
	$seo_desc = $cfg->get('meta', 'description') != false ? wp_specialchars(trim($cfg->get('meta', 'description'))) : $seo_default ['description'];
	$seo_keywords = $cfg->get('meta', 'keywords') != false ? wp_specialchars(trim($cfg->get('meta', 'keywords'))) : $seo_default ['keywords'];
	$seo_noindex = $cfg->get('meta', 'noindex') != false ? wp_specialchars(trim($cfg->get('meta', 'noindex'))) : $seo_default ['noindex'];
	$seo_nofollow = $cfg->get('meta', 'nofollow') != false ? wp_specialchars(trim($cfg->get('meta', 'nofollow'))) : $seo_default ['nofollow'];
	$seo_noarchive = $cfg->get('meta', 'noarchive') != false ? wp_specialchars(trim($cfg->get('meta', 'noarchive'))) : $seo_default ['noarchive'];
	$seo_nosnippet = $cfg->get('meta', 'nosnippet') != false ? wp_specialchars(trim($cfg->get('meta', 'nosnippet'))) : $seo_default ['nosnippet'];
	output_metatags($seo_desc, $seo_keywords, $seo_noindex, $seo_nofollow, $seo_noarchive, $seo_nosnippet);
}

function process_tag_meta() {
	global $fp_params;
	$lang = lang_load('plugin:seometataginfo');
	$string = $lang ['plugin'] ['seometataginfo'];

	$file_meta = SEOMETA_TAG_DIR . 'tag-' . $fp_params ['tag'] . '_metatags.ini';
	$type = $string ['tag'];
	$id = $fp_params ['tag'];
	$sep = $string ['sep'];
	process_meta($file_meta, $type, $id, $sep);
}

function process_archive_meta() {
	global $fp_params;
	$lang = lang_load('plugin:seometataginfo');
	$string = $lang ['plugin'] ['seometataginfo'];

	$file_meta = SEOMETA_ARCHIVE_DIR . 'archive-20' . (!empty($fp_params ['y']) ? $fp_params ['y'] : '') . (!empty($fp_params ['m']) ? '-' . $fp_params ['m'] : '') . (!empty($fp_params ['d']) ? '-' . $fp_params ['d'] : '') . '_metatags.ini';
	$type = $string ['archive'];
	$id = '20' . (!empty($fp_params ['y']) ? $fp_params ['y'] : '') . (!empty($fp_params ['m']) ? '/' . $fp_params ['m'] : '') . (!empty($fp_params ['d']) ? '/' . $fp_params ['d'] : '');
	$sep = $string ['sep'];
	process_meta($file_meta, $type, $id, $sep);
}

function process_category_meta() {
	global $fp_params;
	$lang = lang_load('plugin:seometataginfo');
	$string = $lang ['plugin'] ['seometataginfo'];

	// check if requested category exists
	if (!seometa_category_id_exists($fp_params ['cat'])) {
		return;
	}

	$file_meta = SEOMETA_CATEGORY_DIR . 'cat-' . $fp_params ['cat'] . '_metatags.ini';
	$type = $string ['category'];
	$id = get_category_name($fp_params ['cat']);
	$sep = $string ['sep'];
	process_meta($file_meta, $type, $id, $sep);
}

/**
 * Checks if the category with the given ID exists
 *
 * @param int $cat_id
 *   the category ID
 * @return boolean <code>true</code> if the category exists; <code>false</code> otherwise
 */
function seometa_category_id_exists($cat_id) {
	// if we don't have a categories config, there's nothing to check!
	if (!file_exists(CONTENT_DIR . 'categories.txt')) {
		return false;
	}

	// get all categories from config file
	$cats = trim(io_load_file(CONTENT_DIR . 'categories.txt'));
	// check each known category
	$catConfigLines = explode("\n", $cats);
	foreach ($catConfigLines as $currentLine) {
		$data = explode(':', $currentLine);
		// category ID resides in index 1
		if (array_key_exists(1, $data) && trim($data [1]) == $cat_id) {
			return true;
		}
	}
	// no matching category found!
	return false;
}

/**
 * Per-request cache for entry meta to avoid duplicate INI reading.
 */
function seometataginfo_cache_set($id, $desc, $keys) {
	if (!is_string($id) || $id === '') {
		return;
	}
	if (!isset($GLOBALS ['seometataginfo_entry_cache']) || !is_array($GLOBALS ['seometataginfo_entry_cache'])) {
		$GLOBALS ['seometataginfo_entry_cache'] = array();
	}
	$GLOBALS ['seometataginfo_entry_cache'] [$id] = array((string)$desc, (string)$keys);
}

function seometataginfo_cache_get($id) {
	if (!isset($GLOBALS ['seometataginfo_entry_cache']) || !is_array($GLOBALS ['seometataginfo_entry_cache'])) {
		return null;
	}
	return isset($GLOBALS ['seometataginfo_entry_cache'] [$id]) ? $GLOBALS ['seometataginfo_entry_cache'] [$id] : null;
}

/**
 * Ensures that a metatags.ini file exists.
 *
 * If a page-specific file is missing, copy defaults from default/metatags.ini.
 * This prevents missing OpenGraph tags on pages where the admin UI has not
 * explicitly created metatags yet (notably the blog home page).
 */
function seometataginfo_ensure_metafile(&$file_meta) {
	if (!is_string($file_meta) || $file_meta === '') {
		return;
	}

	$dir = dirname($file_meta);
	if (!is_dir($dir)) {
		@mkdir($dir, DIR_PERMISSIONS, true);
	}

	if (file_exists($file_meta)) {
		return;
	}

	$defaultFile = SEOMETA_DEFAULT_DIR . 'metatags.ini';
	if (!is_dir(SEOMETA_DEFAULT_DIR)) {
		@mkdir(SEOMETA_DEFAULT_DIR, DIR_PERMISSIONS, true);
	}

	if (!file_exists($defaultFile)) {
		$metatags = "[meta]\n";
		$metatags .= "description=\n";
		$metatags .= "keywords=\n";
		$metatags .= "noindex=0\n";
		$metatags .= "nofollow=0\n";
		$metatags .= "noarchive=0\n";
		$metatags .= "nosnippet=0\n";
		@io_write_file($defaultFile, $metatags);
	}

	$src = file_exists($defaultFile) ? io_load_file($defaultFile) : '';
	if (!is_string($src) || trim($src) === '') {
		$src = "[meta]\n";
		$src .= "description=\n";
		$src .= "keywords=\n";
		$src .= "noindex=0\n";
		$src .= "nofollow=0\n";
		$src .= "noarchive=0\n";
		$src .= "nosnippet=0\n";
	}
	@io_write_file($file_meta, $src);
}

function plugin_seometataginfo_head($file_meta) {
	global $fpdb, $fp_params, $fp_config, $smarty;

	if (defined('ADMIN_PANEL')) {
		return;
	}

	if (is_tag()) { // Am I in a tag?
		process_tag_meta();
	} elseif (is_archive()) { // Am I in a archive?
		process_archive_meta();
	} elseif (is_category()) { // Am I in a category?
		process_category_meta();
	} else {
		if (is_single()) {
			# Search for the description
			$id = $fp_params ['entry'];
			$file_meta = SEOMETA_ENTRY_DIR . $id . '_metatags.ini';
		} else {
			if (is_blog_page()) {
				$id = 'blog';
				$file_meta = SEOMETA_STATIC_DIR . $id . '_metatags.ini';
			} elseif (is_contact()) { // check if contact form
				$file_meta = SEOMETA_STATIC_DIR . 'contact_metatags.ini';
			} elseif (!empty($fp_params ['page'])) { // check if ordinary static page
				$id = $fp_params ['page'];
				$file_meta = SEOMETA_STATIC_DIR . $id . '_metatags.ini';
			} elseif (is_static_home()) { // must be Home Page?
				$id = $fp_config ['general'] ['startpage'];
				$file_meta = SEOMETA_STATIC_DIR . $id . '_metatags.ini';
			}
		}

		/**
		 * Ensure we always output SEO/OpenGraph meta tags.
		 * If a page-specific metatags.ini is missing, fall back to defaults and
		 * create the file so it can be customized later.
		 */
		seometataginfo_ensure_metafile($file_meta);
		if (!is_string($file_meta) || $file_meta === '' || !file_exists($file_meta)) {
			$file_meta = SEOMETA_DEFAULT_DIR . 'metatags.ini';
			seometataginfo_ensure_metafile($file_meta);
			if (!file_exists($file_meta)) {
				// Last-resort fallback: still output basic meta tags without touching the filesystem.
				output_metatags('', '', '0', '0', '0', '0');
				return;
			}
		}

		$cfg = new iniParser($file_meta);
		$seo_desc = $cfg->get('meta', 'description') != false ? wp_specialchars(trim($cfg->get('meta', 'description'))) : '';
		$seo_keywords = $cfg->get('meta', 'keywords') != false ? wp_specialchars(trim($cfg->get('meta', 'keywords'))) : '';
		$seo_noindex = $cfg->get('meta', 'noindex') != false ? wp_specialchars(trim($cfg->get('meta', 'noindex'))) : '0';
		$seo_nofollow = $cfg->get('meta', 'nofollow') != false ? wp_specialchars(trim($cfg->get('meta', 'nofollow'))) : '0';
		$seo_noarchive = $cfg->get('meta', 'noarchive') != false ? wp_specialchars(trim($cfg->get('meta', 'noarchive'))) : '0';
		$seo_nosnippet = $cfg->get('meta', 'nosnippet') != false ? wp_specialchars(trim($cfg->get('meta', 'nosnippet'))) : '0';
		if (is_single() && isset($id)) {
			seometataginfo_cache_set($id, $seo_desc, $seo_keywords);
		}
		output_metatags($seo_desc, $seo_keywords, $seo_noindex, $seo_nofollow, $seo_noarchive, $seo_nosnippet);
	}
}

add_action('wp_head', 'plugin_seometataginfo_head', 1);

function makePageTitle($title, $sep) {
	global $fp_params;
	$lang = lang_load('plugin:seometataginfo');
	$string = $lang ['plugin'] ['seometataginfo'];

	$page_title = '';

	if (is_contact()) {
		$page_title = $string ['contact'];
	} elseif (is_static_home() && empty($fp_params ['page'])) {
		$page_title = $string ['home'];
	} elseif (is_blog_home()) {
		$page_title = $string ['blog_home'];
	} elseif (is_blog_page()) {
		$page_title = $string ['blog_page'];
	} elseif (is_tag()) {
		$page_title = $string ['tag'] . $string ['sep'] . $fp_params ['tag'];
	} elseif (is_archive()) {
		if (is_archive_year()) {
			$page_title = $string ['archive'] . $string ['sep'] . '20' . $fp_params ['y'];
		} elseif (is_archive_month()) {
			$page_title = $string ['archive'] . $string ['sep'] . '20' . $fp_params ['y'] . '/' . $fp_params ['m'];
		} elseif (is_archive_day()) {
			$page_title = $string ['archive'] . $string ['sep'] . '20' . $fp_params ['y'] . '/' . $fp_params ['m'] . '/' . $fp_params ['d'];
		}
	} elseif (is_category()) {
		$page_title = $string ['category'] . $string ['sep'] . get_category_name($fp_params ['cat']);
	} elseif (is_search()) {
		$page_title = $fp_params ['q'];
	}

	if (is_paging()) {
		$page_title .= $string ['sep'] . $string ['pagenum'] . $fp_params ['paged'];
	}

	return $title . (!empty($page_title) ? ' ' . $sep . ' ' . $page_title : '');
}

function plugin_seometataginfo_init() {
	if (!defined('ADMIN_PANEL') && seometataginfo_is_og_image_request()) {
		seometataginfo_serve_og_image();
	}

	if (defined('ADMIN_PANEL')) {
		return;
	}

	if (SEOMETA_GEN_TITLE) {
		add_filter('wp_title', 'makePageTitle', 10, 2);
	}
}

add_action('init', 'plugin_seometataginfo_init');


/**
 * Returns an admin string for the SEO Meta Tag Info plugin with safe fallbacks.
 *
 * This prevents template warnings when a translation pack does not provide
 * a bundled key yet (for example after a plugin update).
 *
 * @param string $key
 * @param string $default
 * @return string
 */
function seometataginfo_get_admin_string($key, $default = '') {
	static $cache = array();

	$key = is_string($key) ? trim($key) : '';
	if ($key === '') {
		return is_string($default) ? $default : '';
	}

	if (array_key_exists($key, $cache)) {
		return $cache [$key];
	}

	$value = '';

	$lang = lang_load('plugin:seometataginfo');
	if (isset($lang ['admin'] ['plugin'] ['seometataginfo'] [$key]) && is_string($lang ['admin'] ['plugin'] ['seometataginfo'] [$key])) {
		$value = $lang ['admin'] ['plugin'] ['seometataginfo'] [$key];
	}

	if ($value === '' && is_string($default)) {
		$value = $default;
	}

	$cache [$key] = $value;
	return $value;
}

/**
 * SEO robots.txt part
 */
define('ROBOTS_PATHINFO', !file_exists($_SERVER ['DOCUMENT_ROOT'] . '/robots.txt'));

// File existance check
function plugin_seometatags_setup() {
	if (file_exists($_SERVER ['DOCUMENT_ROOT'] . '/robots.txt')) {
		return 1;
	}

	if (!is_writable($_SERVER ['DOCUMENT_ROOT'])) {
		return -2;
	}

	return 1;
}

/**
 * The SEO robots.txt admin panel
 * The root directory must be defined in the server configuration file!
 */
if (class_exists('AdminPanelAction') && !empty($_SERVER ['DOCUMENT_ROOT'])) {

	class admin_plugin_seometataginfo extends AdminPanelAction {

		var $lang = 'plugin:seometataginfo';

		function setup() {
			$this->smarty->assign('admin_resource', 'plugin:seometataginfo/admin.plugin.seometataginfo');
			$blogroot = BLOG_ROOT;
			$blogbase = BLOG_BASEURL;
			$f = $_SERVER ['DOCUMENT_ROOT'] . '/robots.txt';
			if (file_exists(ABS_PATH . '.htaccess')) {
				$sitemap = 'Sitemap: ' . $blogbase . 'sitemap.xml';
			} else {
				$sitemap = '';
			}
			$txt = io_load_file($f);
			if (!$txt) {

				$txt = '
User-Agent: *

# Be careful with these settings related to FlatPress
# ===================================================

Disallow: ' . $blogroot . 'admin.php
Disallow: ' . $blogroot . 'login.php
Disallow: ' . $blogroot . 'setup.php

Disallow: ' . $blogroot . 'admin/
Disallow: ' . $blogroot . 'setup/

Disallow: ' . $blogroot . 'fp-content/attachs/

' . $sitemap . '
';
			}

			$this->smarty->assign('cantsave', (!is_writable($_SERVER ['DOCUMENT_ROOT']) || (file_exists($f) && !is_writable($f))));
			$this->smarty->assign('robots_cantsave', seometataginfo_get_admin_string('cantsave', 'You can\'t edit this file, because it\'s not <strong>writable</strong>. You can give writing permission or copy and paste to a file and then upload manually.'));
			$this->smarty->assign('robots', $txt);
		}

		function onsubmit($data = null) {
			global $fp_config;

			if (isset($_POST ['saveopt'])) {
				if (plugin_saveoptions()) {
					$this->smarty->assign('success', 2);
				} else {
					$this->smarty->assign('success', -2);
				}
			}

			if (isset($_POST ['robots-submit'])) {
				if (!empty($_POST ['robots']) && io_write_file($_SERVER ['DOCUMENT_ROOT'] . '/robots.txt', $_POST ['robots'])) {
					$this->smarty->assign('success', 1);
				} else {
					$this->smarty->assign('success', -1);
				}
			}

			return 2;
		}

	}

	admin_addpanelaction('plugin', 'seometataginfo', true);
}

// Establish compatibility with older Smarty 4.
function seometataginfo_assign_defaults() {
	if (isset($GLOBALS ['smarty']) && is_object($GLOBALS ['smarty']) && method_exists($GLOBALS ['smarty'], 'assign')) {
		$GLOBALS ['smarty']->assign('seo_desc', '');
		$GLOBALS ['smarty']->assign('seo_keywords', '');
	}
}
add_action('init', 'seometataginfo_assign_defaults', 0);

/**
 * Make per-entry SEO meta available to Smarty templates.
 * Memorization reduces double reads within a request.
 * Exposes: $seo_desc, $seo_keywords for the current {entry}-block.
 * For output in the template, e.g. in entry-default.tpl:
 *   {if $seo_desc}<i>{$seo_desc|escape}</i>{/if}
 *   {if $seo_keywords}<i>{$seo_keywords|escape}</i>{/if}
 */
function seometataginfo_assign_entry_vars($id) {
	if (!is_string($id) || $id === '') {
		return;
	}

	// Pro-request memoization: id => [desc, keys]
	static $memo = array();
	$data = isset($memo [$id]) ? $memo [$id] : null;
	if ($data === null) {
		$data = seometataginfo_cache_get($id);
	}
	if ($data !== null) {
		$memo [$id] = $data;
		if (isset($GLOBALS ['smarty']) && is_object($GLOBALS ['smarty']) && method_exists($GLOBALS ['smarty'], 'assign')) {
			$GLOBALS ['smarty']->assign('seo_desc', $data [0]);
			$GLOBALS ['smarty']->assign('seo_keywords', $data [1]);
		}
		return;
	}
	$file_meta = SEOMETA_ENTRY_DIR . $id . '_metatags.ini';

	// Ensure $smarty is usable
	if (!isset($GLOBALS ['smarty']) || !is_object($GLOBALS ['smarty']) || !method_exists($GLOBALS ['smarty'], 'assign')) {
		return;
	}

	$smarty = $GLOBALS ['smarty'];
	if (!is_readable($file_meta)) {
		// Provide empty default so templates can fallback cleanly
		$memo [$id] = array('', '');
		$smarty->assign('seo_desc', '');
		$smarty->assign('seo_keywords', '');
		return;
	}
	$cfg = new iniParser($file_meta);
	$descRaw = $cfg->get('meta', 'description');
	$keysRaw = $cfg->get('meta', 'keywords');
	$seo_desc = $descRaw !== false ? wp_specialchars(trim((string)$descRaw)) : '';
	$seo_keywords = $keysRaw !== false ? wp_specialchars(trim((string)$keysRaw)) : '';
	seometataginfo_cache_set($id, $seo_desc, $seo_keywords);
	$memo [$id] = array($seo_desc, $seo_keywords);
	$smarty->assign('seo_desc', $seo_desc);
	$smarty->assign('seo_keywords', $seo_keywords);
}

// Bind during entry rendering so {$seo_desc} and {$seo_keywords} is available inside {entry} blocks
add_action('entry_block', 'seometataginfo_assign_entry_vars', 0);
?>
