<?php
/**
 * Plugin Name: SEO Meta Tag Info
 * Version: 2.2.5
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

// Dynamic definition of allowed characters
$unicode_blocks = [
	'\p{L}', // Letter
	'\p{N}', // Number
	'\p{P}', // Punctuation
	'\p{Zs}', // Separator, space
	'\p{M}', // Mark
];

// Additional permitted characters (e.g. emojis, special characters)
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

// prepend the blog title to description and keywords
global $fp_config;
$prepend_description = $fp_config ['general'] ['title'] . ' - ';
$prepend_keywords = $fp_config ['general'] ['title'] . ', ';

// IMPORTANT: migrate data from previous version
// Note: If a metatags.ini exists for both
// an published entry and a draft entry
// the published entry version will be kept.
// set 'SEOMETA_MIGRATE_DATA' to 'true' then
// create a new entry to force data migration.
if (!defined('SEOMETA_MIGRATE_DATA')) {
	define('SEOMETA_MIGRATE_DATA', false);
}

// generate Open Graph <meta property="og:".. true/false
if (!defined('SEOMETA_GEN_OPEN_GRAPH')) {
	define('SEOMETA_GEN_OPEN_GRAPH', true);
}

// generate pretty titles e.g.
// 'Blog Title - Archive - 2011/06' or
// 'Blog Title - Category - Something Cool'
if (!defined('SEOMETA_GEN_TITLE')) {
	define('SEOMETA_GEN_TITLE', true);
}

// generate <meta name='title'.. true/false
if (!defined('SEOMETA_GEN_TITLE_META')) {
	define('SEOMETA_GEN_TITLE_META', true);
}

// Before the crawler selects any image, we give it the style/ theme preview
// generate <meta property="og:image".. true/false
if (!defined('SEOMETA_GEN_IMAGE_META')) {
	define('SEOMETA_GEN_IMAGE_META', true);
}

// generate <link rel="canonical".. true/false
if (!defined('SEOMETA_GEN_CANONICAL')) {
	define('SEOMETA_GEN_CANONICAL', true);
}

// force comments to point at page (canonical)
// e.g.
// /yy/mm/dd/mypage/comments/ => /yy/mm/dd/mypage/
// /yy/mm/dd/mypage/comments/#comments => /yy/mm/dd/mypage/
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
	$theme = $fp_config ['general'] ['theme'] ?? '';
	$style = isset($fp_config ['general'] ['style']) && is_string($fp_config ['general'] ['style']) ? trim($fp_config ['general'] ['style']) : '';
	$styleSegment = ($style !== '') ? ($style . '/') : '';
	$previewImage = $BLOG_BASEURL . 'fp-interface/themes/' . $theme . '/' . $styleSegment . 'preview.png';

	echo '
	<!-- beginning of SEO Metatag Info -->' . "\n";

	if (SEOMETA_GEN_TITLE_META) {
		$metatitle = apply_filters('wp_title', $fp_config ['general'] ['title'], trim($string ['sep']));
		echo '	<meta name="title" content="' . htmlspecialchars($metatitle, ENT_QUOTES, $charset) . '">' . "\n";
		if (SEOMETA_GEN_OPEN_GRAPH) {
			echo '	<meta property="og:title" content="' . htmlspecialchars($metatitle, ENT_QUOTES, $charset) . '">' . "\n";
		}
	}

	if (SEOMETA_GEN_IMAGE_META) {
		// The minimum permitted image size is 200 x 200 pixels.
		// The size of the image file must not exceed 8 MB.
		// Meh, the recommended aspect ratio is 1.91:1 otherwise parts will be cut off
		if (SEOMETA_GEN_OPEN_GRAPH) {
			echo '	<meta property="og:image" content="'. $previewImage . '">' . "\n";
			echo '	<meta property="og:image:url" content="'. $previewImage . '">' . "\n";
			echo '	<meta property="og:image:width" content="800">' . "\n";
			echo '	<meta property="og:image:height" content="600">' . "\n";
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
	echo '	<meta name="description" content="' . $encoded_description . '">' . "\n";
	echo '	<meta name="keywords" content="' . $prepend_keywords . $seo_keywords . '">' . "\n";
	if (SEOMETA_GEN_OPEN_GRAPH) {
		echo '	<meta property="og:description" content="' . $encoded_description . '">' . "\n";
	}
	if (is_single()) {
		echo '	<meta name="author" content="' . $fp_config ['general'] ['author'] . '">' . "\n";
		if (SEOMETA_GEN_OPEN_GRAPH) {
			echo '	<meta property="og:type" content="article">' . "\n";
		}
	} else {
		if (SEOMETA_GEN_OPEN_GRAPH) {
			echo '	<meta property="og:type" content="website">' . "\n";
		}
	}
	if (SEOMETA_GEN_OPEN_GRAPH) {
		echo '	<meta property="og:locale" content="' . $lang . '">' . "\n";
		echo '	<meta property="og:site_name" content="' . $site_title . '">' . "\n";
	}

	if ($count > 0) {
		echo '	<meta name="robots" content="';
		$data = ($seo_noindex !== '0') ? 'NOINDEX,' : '';
		$data .= ($seo_nofollow !== '0' ? 'NOFOLLOW,' : '');
		$data .= ($seo_noarchive !== '0' ? 'NOARCHIVE,' : '');
		$data .= ($seo_nosnippet !== '0' ? 'NOSNIPPET,' : '');
		$data = substr($data, 0, -1); // remove trailing comma
		echo $data;
		echo '">' . "\n";
	}
	if (SEOMETA_GEN_CANONICAL) {
		$url = currentPageURL();
		if (SEOMETA_HIDECOMMENTS === true) {
			$url = preg_replace('/#comments/', '', $url);
			$url = preg_replace('/comments\//', '', $url);
		}
		echo '	<link rel="canonical" href="' . $url . '">' . "\n";
		if (SEOMETA_GEN_OPEN_GRAPH) {
			echo '	<meta property="og:url" content="' . $url . '">' . "\n";
		}
	}
	echo '	<!-- end of SEO Metatag Info -->' . "\n";
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
 *			the category ID
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

		if (!file_exists($file_meta)) {
			return;
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
	if (defined('ADMIN_PANEL')) {
		return;
	}

	if (SEOMETA_GEN_TITLE) {
		add_filter('wp_title', 'makePageTitle', 10, 2);
	}
}

add_action('init', 'plugin_seometataginfo_init');


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

// The SEO robots.txt admin panel
// The root directory must be defined in the server configuration file!
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
