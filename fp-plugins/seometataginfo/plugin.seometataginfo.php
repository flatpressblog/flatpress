<?php
/*
 * Plugin Name: SEO Meta Tag Info
 * Version: 2.2.3
 * Plugin URI: https://www.flatpress.org
 * Description: This plugin allows editing of SEO meta tags description, keywords and robots for Entries, Statics and Categories. Part of the standard distribution. <a href="./fp-plugins/seometataginfo/doc_seometataginfo.txt" title="Anleitung" target="_blank">[Instructions]</a>
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 */

// SEE 'readme.txt' for information
require ("inc/hw-helpers.php");
require ("inc/class.iniparser.php");
require ("inc/migrate_data.php");

global $keep_char;
global $seo_default;
global $prepend_description;
global $prepend_keywords;

// IMPORTANT: For non LATIN-1 countries
// ADD additional characters that you want to allow in your meta tags here
$keep_char = "€ƒ†‡‰™ŠŒŽ‘’“”•–—˜™š›œžŸ¡¢£¤¥¦ª§¨©ª«¬®¯°±²³´µ¶·¸¹º»¼½¾¿ÀÁÂÃÄÅÆÇČĎÈÉÊËĞÌÍÎİÏĲÐŇÑÒÓÔÕÖŘŞŤ×ØÙÚÛŰÜŮÝÞßàáâãäåăæçďčèéêëſğìíîïıĳðňñòóôõöřşțť÷øùúûűüůýþÿ✓✔➤➔→➥▶⇒➨★❤♥✘✖✆✈";

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
define('SEOMETA_MIGRATE_DATA', false);

// generate pretty titles e.g.
// 'Blog Title - Archive - 2011/06' or
// 'Blog Title - Category - Something Cool'
define('SEOMETA_GEN_TITLE', true);

// generate <meta name='title'.. true/false
define('SEOMETA_GEN_TITLE_META', true);

// generate <link rel="canonical".. true/false
define('SEOMETA_GEN_CANONICAL', true);

// force comments to point at page (canonical)
// e.g.
// /yy/mm/dd/mypage/comments/ => /yy/mm/dd/mypage/
// /yy/mm/dd/mypage/comments/#comments => /yy/mm/dd/mypage/
define('SEOMETA_HIDECOMMENTS', true);

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
if (version_compare(SYSTEM_VER, '0.1010', '>=') == 1 && defined('MOD_ADMIN_PANEL')) {

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

			if (!empty($file_meta) && !file_exists($file_meta))
				@io_write_file($file_meta, '');

			$cfg = new iniParser($file_meta);
			$old_desc = $cfg->get("meta", "description") != false ? wp_specialchars(trim($cfg->get("meta", "description"))) : (!empty($_REQUEST ['pl_description']) ? $_REQUEST ['pl_description'] : '');
			$old_keywords = $cfg->get("meta", "keywords") != false ? wp_specialchars(trim($cfg->get("meta", "keywords"))) : (!empty($_REQUEST ['pl_keywords']) ? $_REQUEST ['pl_keywords'] : '');
			$old_noindex = $cfg->get("meta", "noindex") != false ? wp_specialchars(trim($cfg->get("meta", "noindex"))) : (!empty($_REQUEST ['pl_noindex']) ? $_REQUEST ['pl_noindex'] : '0');
			$old_nofollow = $cfg->get("meta", "nofollow") != false ? wp_specialchars(trim($cfg->get("meta", "nofollow"))) : (!empty($_REQUEST ['pl_nofollow']) ? $_REQUEST ['pl_nofollow'] : '0');
			$old_noarchive = $cfg->get("meta", "noarchive") != false ? wp_specialchars(trim($cfg->get("meta", "noarchive"))) : (!empty($_REQUEST ['pl_noarchive']) ? $_REQUEST ['pl_noarchive'] : '0');
			$old_nosnippet = $cfg->get("meta", "nosnippet") != false ? wp_specialchars(trim($cfg->get("meta", "nosnippet"))) : (!empty($_REQUEST ['pl_nosnippet']) ? $_REQUEST ['pl_nosnippet'] : '0');

			$lang = lang_load('plugin:seometataginfo');
			$string = $lang ['admin'] ['plugin'] ['seometataginfo'];

			echo "\n<fieldset id=\"plugin_seometataginfo\">\n";
			echo " <legend>{$string['legend_desc']}</legend>\n";
			echo "  <p>{$string['description']}</p>\n";
			echo "  <div>\n";
			echo '    <input id="pl_file_meta" type="hidden" name="pl_file_meta" value="' . $file_meta . '">' . "\n";
			echo "    <p><label for=\"pl_description\">{$string['input_desc']}</label>\n";
			echo '       <input placeholder="' . $lang ['admin'] ['plugin'] ['seometataginfo'] ['sample_desc'] . '" class="maxsize" id="pl_description" type="text" name="pl_description" value="' . $old_desc . '"></p>' . "\n";
			echo "    <p><label for=\"pl_keywords\">{$string['input_keywords']}</label>\n";
			echo '       <input placeholder="' . $lang ['admin'] ['plugin'] ['seometataginfo'] ['sample_keywords'] . '" class="maxsize" id="pl_keywords" type="text" name="pl_keywords" value="' . $old_keywords . '"></p>' . "\n";
			echo "    <p><br>\n";
			$checked = ($old_noindex === "1") ? ' checked="yes"' : '';
			echo "    <label for=\"pl_noindex\">{$string['input_noindex']}</label>\n";
			echo '       <input style="vertical-align: middle; margin: 0px 10px 0px 0px; cursor: pointer;" id="pl_noindex" type="checkbox"' . $checked . ' name="pl_noindex" value="1"' . '>';
			$checked = ($old_nofollow === "1") ? ' checked="yes"' : '';
			echo "    <label for=\"pl_nofollow\">{$string['input_nofollow']}</label>\n";
			echo '       <input style="vertical-align: middle; margin: 0px 10px 0px 0px; cursor: pointer;" id="pl_nofollow" type="checkbox"' . $checked . ' name="pl_nofollow" value="1"' . '>';
			$checked = ($old_noarchive === "1") ? ' checked="yes"' : '';
			echo "    <label for=\"pl_noarchive\">{$string['input_noarchive']}</label>\n";
			echo '       <input style="vertical-align: middle; margin: 0px 10px 0px 0px; cursor: pointer;" id="pl_noarchive" type="checkbox"' . $checked . ' name="pl_noarchive" value="1"' . '>';
			$checked = ($old_nosnippet === "1") ? ' checked="yes"' : '';
			echo "    <label for=\"pl_nosnippet\">{$string['input_nosnippet']}</label>\n";
			echo '       <input style="vertical-align: middle; margin: 0px 10px 0px 0px; cursor: pointer;" id="pl_nosnippet" type="checkbox"' . $checked . ' name="pl_nosnippet" value="1"' . '>';
			echo '    </p>' . "\n";
			echo "  </div>\n";
			echo "</fieldset>\n";

			return true;
		}

		function do_save() {
			if (empty($_POST ['pl_file_meta']))
				return;
			global $keep_char;
			$metatags = "description=" . (isset($_POST ['pl_description']) ? trim($_POST ['pl_description']) : "") . "\n";
			$metatags .= "keywords=" . (isset($_POST ['pl_keywords']) ? trim($_POST ['pl_keywords']) : "") . "\n";
			$metatags .= "noindex=" . (isset($_POST ['pl_noindex']) ? trim($_POST ['pl_noindex']) : "0") . "\n";
			$metatags .= "nofollow=" . (isset($_POST ['pl_nofollow']) ? trim($_POST ['pl_nofollow']) : "0") . "\n";
			$metatags .= "noarchive=" . (isset($_POST ['pl_noarchive']) ? trim($_POST ['pl_noarchive']) : "0") . "\n";
			$metatags .= "nosnippet=" . (isset($_POST ['pl_nosnippet']) ? trim($_POST ['pl_nosnippet']) : "0") . "\n";
			$metatags = preg_replace("/[^0-9a-zA-Z- =,\r\n" . $keep_char . "]/", "", $metatags);

			if (!empty($_POST ['pl_file_meta']) && $_POST ['pl_file_meta'] !== SEOMETA_DEFAULT_DIR . 'metatags.ini')
				// existing blog entry or static page (got file name already)
				@io_write_file($_POST ['pl_file_meta'], "[meta]\n" . $metatags);
			elseif ($_REQUEST ['p'] === "entry") {
				// this was a new blog entry
				$new_id = bdb_idfromtime(BDB_ENTRY, $_POST ['timestamp']);
				$file_meta = SEOMETA_ENTRY_DIR . $new_id . '_metatags.ini';
				@io_write_file($file_meta, "[meta]\n" . $metatags);
			} elseif ($_REQUEST ['p'] === "static") {
				// this was a new static page
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
	if (SEOMETA_MIGRATE_DATA)
		migrate_old(); // migrate old data from previous version of plugin
	new plugin_seometatags_entry();
}

function output_metatags($seo_desc, $seo_keywords, $seo_noindex, $seo_nofollow, $seo_noarchive, $seo_nosnippet) {
	global $prepend_description;
	global $prepend_keywords;
	global $fp_params, $fp_config;
	$lang = lang_load('plugin:seometataginfo');
	$string = $lang ['plugin'] ['seometataginfo'];

	echo '
	<!-- beginning of SEO Metatag Info -->' . "\n";

	if (SEOMETA_GEN_TITLE_META) {
		$metatitle = apply_filters('wp_title', $fp_config ['general'] ['title'], trim($string ['sep']));
		echo "\n" . '    <meta name="title" content="' . $metatitle . '">' . "\n";
	}

	$count = 0;
	$count += ($seo_noindex !== '0') ? 1 : 0;
	$count += ($seo_nofollow !== '0') ? 1 : 0;
	$count += ($seo_noarchive !== '0') ? 1 : 0;
	$count += ($seo_nosnippet !== '0') ? 1 : 0;

	// make description unique by adding a page# when paging
	$pagenum = "";
	if (is_paging()) {
		$pagenum = $string ['sep'] . "(" . $string ['pagenum'] . $fp_params ['paged'] . ")";
	}
	// make description unique by adding a comments on comments page
	$comment = "";
	if (is_comments()) {
		$comment = $string ['sep'] . "(" . $string ['comments'] . ")";
	}

	# Now write the tags
	echo '    <meta name="description" content="' . $prepend_description . $seo_desc . $comment . $pagenum . '">' . "\n";
	echo '    <meta name="keywords" content="' . $prepend_keywords . $seo_keywords . '">' . "\n";
	echo '    <meta name="author" content="' . $fp_config ['general'] ['author'] . '">' . "\n";

	if ($count > 0) {
		echo '    <meta name="robots" content="';
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
		echo '    <link rel="canonical" href="' . $url . '">' . "\n";
	}
	echo '    <!-- end of SEO Metatag Info -->' . "\n";
}

function process_meta($file_meta, $type, $id, $sep) {
	global $fp_params;
	global $seo_default;

	if (empty($seo_default ['description']))
		$seo_default ['description'] = $type . $sep . $id;
	if (empty($seo_default ['keywords']))
		$seo_default ['keywords'] = $type;
	if (!isset($seo_default ['noindex']))
		$seo_default ['noindex'] = '0';
	if (!isset($seo_default ['nofollow']))
		$seo_default ['nofollow'] = '0';
	if (!isset($seo_default ['noarchive']))
		$seo_default ['noarchive'] = '0';
	if (!isset($seo_default ['nosnippet']))
		$seo_default ['nosnippet'] = '0';

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
	$seo_desc = $cfg->get("meta", "description") != false ? wp_specialchars(trim($cfg->get("meta", "description"))) : $seo_default ['description'];
	$seo_keywords = $cfg->get("meta", "keywords") != false ? wp_specialchars(trim($cfg->get("meta", "keywords"))) : $seo_default ['keywords'];
	$seo_noindex = $cfg->get("meta", "noindex") != false ? wp_specialchars(trim($cfg->get("meta", "noindex"))) : $seo_default ['noindex'];
	$seo_nofollow = $cfg->get("meta", "nofollow") != false ? wp_specialchars(trim($cfg->get("meta", "nofollow"))) : $seo_default ['nofollow'];
	$seo_noarchive = $cfg->get("meta", "noarchive") != false ? wp_specialchars(trim($cfg->get("meta", "noarchive"))) : $seo_default ['noarchive'];
	$seo_nosnippet = $cfg->get("meta", "nosnippet") != false ? wp_specialchars(trim($cfg->get("meta", "nosnippet"))) : $seo_default ['nosnippet'];
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
	$id = "20" . (!empty($fp_params ['y']) ? $fp_params ['y'] : '') . (!empty($fp_params ['m']) ? '/' . $fp_params ['m'] : '') . (!empty($fp_params ['d']) ? '/' . $fp_params ['d'] : '');
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
 *        	the category ID
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

function plugin_seometataginfo_head($file_meta) {
	global $fpdb, $fp_params, $fp_config, $smarty;

	if (defined('ADMIN_PANEL'))
		return;

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

		if (!file_exists($file_meta))
			return;

		$cfg = new iniParser($file_meta);
		$seo_desc = $cfg->get("meta", "description") != false ? wp_specialchars(trim($cfg->get("meta", "description"))) : '';
		$seo_keywords = $cfg->get("meta", "keywords") != false ? wp_specialchars(trim($cfg->get("meta", "keywords"))) : '';
		$seo_noindex = $cfg->get("meta", "noindex") != false ? wp_specialchars(trim($cfg->get("meta", "noindex"))) : '0';
		$seo_nofollow = $cfg->get("meta", "nofollow") != false ? wp_specialchars(trim($cfg->get("meta", "nofollow"))) : '0';
		$seo_noarchive = $cfg->get("meta", "noarchive") != false ? wp_specialchars(trim($cfg->get("meta", "noarchive"))) : '0';
		$seo_nosnippet = $cfg->get("meta", "nosnippet") != false ? wp_specialchars(trim($cfg->get("meta", "nosnippet"))) : '0';
		output_metatags($seo_desc, $seo_keywords, $seo_noindex, $seo_nofollow, $seo_noarchive, $seo_nosnippet);
	}
}

add_action('wp_head', 'plugin_seometataginfo_head', 1);

function makePageTitle($title, $sep) {
	global $fp_params;
	$lang = lang_load('plugin:seometataginfo');
	$string = $lang ['plugin'] ['seometataginfo'];

	$page_title = "";

	if (is_contact()) {
		$page_title = $string ['contact'];
	} elseif (is_static_home() && empty($fp_params ['page'])) {
		$page_title = $string ['home'];
	} elseif (is_blog_home()) {
		$page_title = $string ['blog_home'];
	} elseif (is_blog_page()) {
		$page_title = $string ['blog_page'];
	} elseif (is_tag()) {
		$page_title = $string ['tag'] . $string ['sep'] . $fp_params ["tag"];
	} elseif (is_archive()) {
		if (is_archive_year()) {
			$page_title = $string ['archive'] . $string ['sep'] . '20' . $fp_params ["y"];
		} elseif (is_archive_month()) {
			$page_title = $string ['archive'] . $string ['sep'] . '20' . $fp_params ["y"] . '/' . $fp_params ["m"];
		} elseif (is_archive_day()) {
			$page_title = $string ['archive'] . $string ['sep'] . '20' . $fp_params ["y"] . '/' . $fp_params ["m"] . '/' . $fp_params ["d"];
		}
	} elseif (is_category()) {
		$page_title = $string ['category'] . $string ['sep'] . get_category_name($fp_params ['cat']);
	} elseif (is_search()) {
		$page_title = $fp_params ['q'];
	}

	if (is_paging()) {
		$page_title .= $string ['sep'] . $string ['pagenum'] . $fp_params ['paged'];
	}

	return "$title" . (!empty($page_title) ? " $sep $page_title" : "");
}

function plugin_seometataginfo_init() {
	if (defined('ADMIN_PANEL'))
		return;

	if (SEOMETA_GEN_TITLE)
		add_filter('wp_title', 'makePageTitle', 10, 2);
}

add_action('init', 'plugin_seometataginfo_init');

?>
