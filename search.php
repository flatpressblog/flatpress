<?php
if (!defined('MOD_INDEX')) {
	// Example of use
	require_once 'defaults.php';
	require_once (INCLUDES_DIR . 'includes.php');

	system_init();
	search_main();
	search_display();
}

// BOF Search FileCache TTL index helpers
function search_cache_index_path() {
	$dir = CACHE_DIR;
	if (!is_dir($dir)) {
		@mkdir($dir, DIR_PERMISSIONS, true);
	}
	return $dir . 'search.cache-ttl.json';
}

function search_cache_index_load() {
	$idxFile = search_cache_index_path();
	if (!is_readable($idxFile)) {
		return array('entries' => array());
	}
	$raw = io_load_file($idxFile);
	if ($raw === false || $raw === null) {
		return array('entries' => array());
	}
	$data = @json_decode($raw, true);
	if (!is_array($data) || !isset($data ['entries']) || !is_array($data ['entries'])) {
		return array('entries' => array());
	}
	return $data;
}

function search_cache_index_save($data) {
	$idxFile = search_cache_index_path();
	$tmp = $idxFile . '.tmp';
	$fp = @fopen($tmp, 'wb');
	if (!$fp) {
		return false;
	}
	@flock($fp, LOCK_EX);
	@fwrite($fp, json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
	@flock($fp, LOCK_UN);
	@fclose($fp);
	@rename($tmp, $idxFile);
	return true;
}

function search_cache_index_prune($ttl = 900, $limit = 50) {
	$dir = CACHE_DIR;
	if (!is_dir($dir)) {
		return 0;
	}
	$now = time();
	$data = search_cache_index_load();
	$entries = isset($data ['entries']) && is_array($data ['entries']) ? $data ['entries'] : array();
	$removed = 0;
	foreach ($entries as $key => $meta) {
		if ($removed >= $limit) {
			break;
		}
		$ts = isset($meta ['ts']) ? (int)$meta ['ts'] : 0;
		$ttl_meta = isset($meta ['ttl']) ? (int)$meta ['ttl'] : $ttl;
		if ($ts <= 0) {
			continue;
		}
		if (($now - $ts) > $ttl_meta) {
			$file = isset($meta ['file']) ? $meta ['file'] : '';
			if ($file) {
				$path = $dir . $file;
				if (is_file($path)) {
					@unlink($path);
				}
			}
			unset($entries [$key]);
			$removed++;
		}
	}
	if ($removed > 0) {
		$data ['entries'] = $entries;
		search_cache_index_save($data);
	}
	return $removed;
}

function search_cache_index_add($filename, $ttl = 900) {
	$data = search_cache_index_load();
	if (!isset($data ['entries']) || !is_array($data ['entries'])) {
		$data ['entries'] = array();
	}
	$data ['entries'] [$filename] = array('ts' => time(), 'ttl' => (int)$ttl, 'file' => $filename);
	search_cache_index_save($data);
}

// EOF Search FileCache TTL index helpers


// BOF Search Cache Helpers (APCu + file fallback)
/**
 * Cheap invalidation key: max mtime of content directories (1–2 levels).
 * Avoids scanning all files. Updates when entries, comments or static change.
 */
function search_content_rev_fast() {
	// APCu-backed rev cache (5s) to reduce filesystem scans
	if (is_apcu_on()) {
		$rev_cached = @apcu_get('fp:search:rev');
		if ($rev_cached !== false) {
			return $rev_cached;
		}
	}

	static $fp_rev_cache = null;
	if ($fp_rev_cache !== null) {
		return $fp_rev_cache;
	}
	$max = 0;
	$dirs = array();
	$base = rtrim(CONTENT_DIR, '/');
	$dirs [] = $base;
	$dirs [] = $base . '/static';
	// One and two levels under content, e.g., yy/mm and comments
	foreach (glob($base . '/*', GLOB_ONLYDIR) ?: array() as $d1) {
		$dirs [] = $d1;
		foreach (glob($d1 . '/*', GLOB_ONLYDIR) ?: array() as $d2) {
			$dirs [] = $d2;
			foreach (glob($d2 . '/*', GLOB_ONLYDIR) ?: array() as $d3) {
				$dirs [] = $d3;
				$comm = $d3 . '/comments';
				if (is_dir($comm)) {
					$dirs [] = $comm;
				}
			}
		}
	}
	foreach ($dirs as $d) {
		$t = @filemtime($d);
		if ($t && $t > $max) {
			$max = $t;
		}
	}
	if (!$max) {
		$max = time();
	}
	$fp_rev_cache = (string)$max;
	$rev = $fp_rev_cache;

	// Store computed rev in APCu for 5 seconds
	if (is_apcu_on()) {
		@apcu_set('fp:search:rev', $rev, 5);
	}

	return $rev;
}

function search_cache_key($kw, $params, $fts, $rev) {
	$norm = array(
		'kw' => strtolower((string)$kw),
		'cats' => isset($params ['cats']) ? (array)$params ['cats'] : array(),
		'full' => ($fts === 'yes') ? 1 : 0,
		// Include date in cache key
		'y' => isset($params ['y']) ? (string)$params ['y'] : '',
		'm' => isset($params ['m']) ? (string)$params ['m'] : '',
		'd' => isset($params ['d']) ? (string)$params ['d'] : '',

		'start' => isset($params ['start']) ? (int)$params ['start'] : 0,
		'count' => isset($params ['count']) ? (int)$params ['count'] : -1
	);

	$hash = sha1(json_encode($norm));
	return "fp:search:v" . $rev . ":" . $hash;
}

function search_cache_get($key) {
	// APCu first
	if (is_apcu_on()) {
		$ok = false;
		$val = apcu_get($key, $ok);
		if ($ok && is_array($val)) {
			return $val;
		}
	}
	// File fallback only when APCu is OFF
	if (!is_apcu_on()) {
		$file = CACHE_DIR . 'search-' . sha1($key) . '.json';
		if (is_readable($file)) {
			$j = io_load_file($file);
			if ($j !== false) {
				$val = json_decode($j, true);
				if (is_array($val)) {
					return $val;
				}
			}
		}
	}
	return null;
}

function search_cache_set($key, $val) {
	if (!is_array($val)) {
		return;
	}
	// APCu
	if (is_apcu_on()) {
		@apcu_set($key, $val, 900);
	}
	// File fallback
	if (!is_apcu_on()) {
		$file = CACHE_DIR . 'search-' . sha1($key) . '.json';
		@file_put_contents($file, json_encode($val, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
	}
}
/* --- End Search Cache Helpers --- */

function search_title($title, $sep) {
	global $lang;
	return $title . " " . $sep . " " . $lang ['search'] ['head'];
}

function search_display() {
	global $smarty;
	theme_init($smarty);

	$smarty->display('default.tpl');

	unset($smarty);

	do_action('shutdown');
}

function search_main() {
	global $lang, $smarty;

	// register Smarty modifier functions
	$smarty->registerPlugin('modifier', 'function_exists', 'function_exists');
	$smarty->registerPlugin('modifier', 'is_numeric', 'is_numeric');
	if (!isset($smarty->registered_plugins ['modifier'] ['fix_encoding_issues'])) {
		// This modifier converts characters such as Ã¤ to ä or &#8220; to “. See core.language.php
		$smarty->registerPlugin('modifier', 'fix_encoding_issues', 'fix_encoding_issues');
	}

	add_action('wp_title', 'search_title', 0, 2);

	if (empty($_GET)) {
		// display form
		$title = $lang ['search'] ['head'];
		$content = "shared:search.tpl";
	} else {
		// validate
		if (isset($_GET ['q']) && $kw = trim($_GET ['q'])) {
			$title = $lang ['search'] ['head'];
			$content = "shared:search_results.tpl";

			$kw = strtolower($kw);
			search_do($kw);
		} else {
			$smarty->assign('error', $lang ['search'] ['error'] ['keywords']);
			$title = $lang ['search'] ['headres'];
			$content = "shared:search.tpl";
		}
	}

	$smarty->assign(array(
		'subject' => $title,
		'content' => $content
	));
	return 'default.tpl';
}

function search_do($keywords) {
	// Only run Prune if the index is older than 15 seconds.
	if (!is_apcu_on()) {
		$search_idx = search_cache_index_path();
		if (mt_rand(1, max(2, 900)) === 1) {
			search_cache_index_prune(900, 50);
		}
	}

	global $smarty, $srchresults;

	// get parameters

	$srchkeywords = $keywords;

	$params = array();
	$params ['start'] = 0;
	$params ['count'] = -1;

	(!empty($_GET ['Date_Day'])) && ($_GET ['Date_Day'] != '--') ? $params ['d'] = $_GET ['Date_Day'] : null;
	isset($_GET ['Date_Month']) && ($_GET ['Date_Month'] != '--') ? $params ['m'] = $_GET ['Date_Month'] : null;
	!empty($_GET ['Date_Year']) && ($_GET ['Date_Year'] != '--') ? $params ['y'] = substr($_GET ['Date_Year'], 2) : null;

	// isset($_GET['cats'])? $params = $_GET['cats']: null;
	isset($_GET ['cats']) ? $params ['cats'] = $_GET ['cats'] : null;

	$params ['fullparse'] = false;

	if (!empty($_GET ['stype']) && $_GET ['stype'] == 'full') {
		$params ['fullparse'] = true;
		$fts = "yes";
	} else {
		$params ['fullparse'] = false;
		$fts = "no";
	}

	$srchparams = $params;

	// BOF caching fast-path
	$search_rev = search_content_rev_fast();
	$search_key = search_cache_key(isset($srchkeywords) ? $srchkeywords : $keywords, $params, ((!empty($params['fullparse']) && $params['fullparse']) ? 'yes' : 'no'), $search_rev);
	$search_cached = search_cache_get($search_key);
	if (is_array($search_cached)) {
		$list = $search_cached;
		// Register Smarty plugins only once
		if (!isset($smarty->registered_plugins['block'] ['search_result_block'])) {
			$smarty->registerPlugin('block', 'search_result_block', 'smarty_search_results_block');
			$smarty->registerPlugin('block', 'search_result', 'smarty_search_result');
		}
		if (!$list) {
			$smarty->assign('noresults', true);
		}
		$srchresults = $list;
		return;
	}
	// EOF caching fast-path

	$list = array();

	$q = new FPDB_Query($params, null);

	while ($q->hasMore()) {

		list ($id, $e) = $q->getEntry();

		$match = false;

		if ($keywords == '*') {
			$match = true;
		} else {
			$subj = isset($e ['subject']) ? $e ['subject'] : '';
			$match = (stripos($subj, $keywords) !== false);
			if (!$match && ($fts === "yes")) {
				$cont = isset($e ['content']) ? $e ['content'] : '';
				$match = (stripos($cont, $keywords) !== false);
			}
		}

		if ($match) {
			$list [$id] = $e;
		}
	}

	if (!isset($smarty->registered_plugins ['block'] ['search_result_block'])) {
		$smarty->registerPlugin('block', 'search_result_block', 'smarty_search_results_block');
		$smarty->registerPlugin('block', 'search_result', 'smarty_search_result');
	}

	if (!$list) {
		$smarty->assign('noresults', true);
	}

	// Store into cache with final state and normalized parameters
	$kw = isset($srchkeywords) ? $srchkeywords : $keywords;
	$params ['start'] = isset($params ['start']) ? (int)$params ['start'] : 0;
	$params ['count'] = isset($params ['count']) ? (int)$params ['count'] : -1;
	if (isset($params ['cats'])) {
		$cats = array_map('strval', (array)$params ['cats']);
		sort($cats, SORT_STRING);
		$params ['cats'] = $cats;
	}
	$is_full = (($fts === 'yes') || (!empty($params ['fullparse']) && $params ['fullparse']));
	$search_rev = search_content_rev_fast();
	$search_key = search_cache_key($kw, $params, $is_full ? 'yes' : 'no', $search_rev);
	search_cache_set($search_key, $list);
	// Update TTL index for file cache
	if (!is_apcu_on()) {
		$search_hash = sha1($search_key);
		$search_file = 'search-' . $search_hash . '.json';
		search_cache_index_add($search_file, 900);
	}

	$srchresults = $list;
}

function smarty_search_results_block($params, $content, &$smarty, &$repeat) {
	global $srchresults;

	if ($srchresults) {
		return $content;
	}
}

function smarty_search_result($params, $content, &$smarty, &$repeat) {
	global $srchresults, $post;
	$repeat = false;

	// check if we have at least one more search result
	// (current pointer position must not be after the last element)
	if (current($srchresults)) {
		// get the current search result details
		$id = key($srchresults);
		$e = $srchresults [$id];
		// assign values to template
		$smarty->assign('id', $id);
		$post = $e;
		$smarty->assign($e);
		$repeat = true;
		// advance pointer to next search result element
		next($srchresults);
	}
	return $content;
}
?>
