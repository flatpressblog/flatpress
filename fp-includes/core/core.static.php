<?php
define('STATIC_DIR', CONTENT_DIR . 'static/');

class static_indexer extends fs_filelister {

	var $_directory = STATIC_DIR;

	function _checkfile($directory, $file) {
		array_push($this->_list, basename($file, EXT));
		return 0;
	}

}

function static_getlist() {
	global $fp_config;

	$dir = STATIC_DIR;
	if (!@is_dir($dir)) {
		return array();
	}

	// Naturalsort flag from configuration (default: true)
	$nat = isset($fp_config ['staticlist'] ['naturalsort']) ? $fp_config ['staticlist'] ['naturalsort'] : true;
	$natFlag = $nat ? 'true' : 'false';

	// Request local memoization by directory signature + sort mode
	static $local = array();
	clearstatcache(true, $dir);
	$mt = @filemtime($dir);
	$sz = ($mt !== false) ? (int) @filesize($dir) : 0;
	$sig = (($mt !== false) ? $mt : 'na') . ':' . $sz . ':' . $natFlag;
	if (isset($local [$sig])) {
		return $local [$sig];
	}

	// Optional: APCu cache, key contains directory signature and sort mode
	$apcu_on = function_exists('is_apcu_on') ? is_apcu_on() : false;
	$key = ($apcu_on && $mt !== false) ? ('fp:statics:list:' . $mt . ':' . $sz . ':' . $natFlag) : null;
	if ($key) {
		$hit = false;
		$val = apcu_get($key, $hit);
		if ($hit && is_array($val)) {
			$local [$sig] = $val;
			return $val;
		}
	}

	$obj = new static_indexer();
	$list = $obj->getList();

	if ($nat) {
		// Natural sorting
		natsort($list);
		$list = array_values($list);
	}

	$local [$sig] = $list;
	if ($key) {
		@apcu_set($key, $list, 600);
	}

	return $list;
}

function static_parse($id) {
	if (!static_isvalid($id)) {
		return false;
	}

	if ($fname = static_exists($id)) {
		$entry = io_load_file($fname);
		return (utils_kexplode($entry));
	}
	return array();
}

function static_isvalid($id) {
	return preg_match('![^./\\\\]+!', $id);
}

function static_save($entry, $id, $oldid = null) {
	if (!static_isvalid($id)) {
		return false;
	}

	$fname = STATIC_DIR . $id . EXT;

	$entry ['content'] = apply_filters('content_save_pre', $entry ['content']);
	$entry ['subject'] = apply_filters('title_save_pre', $entry ['subject']);

	$str = utils_kimplode($entry);

	if (io_write_file($fname, $str)) {
		if ($oldid && $id != $oldid && $fname = static_exists($oldid)) {
			$succ = static_delete($oldid);
			return ($succ !== false && $succ !== 2);
		}
		return true;
	}
	return false;
}

function static_exists($id) {
	if (!static_isvalid($id)) {
		return false;
	}

	$fname = STATIC_DIR . $id . EXT;

	if (file_exists($fname)) {
		return $fname;
	}

	return false;
}

function static_delete($id) {
	if (!static_isvalid($id)) {
		return false;
	}

	return fs_delete(STATIC_DIR . $id . EXT);
}

function smarty_block_statics($params, $content, &$smarty, &$repeat) {
	global $fpdb;

	/*
	 * $show = false;
	 *
	 * if (isset($params['alwaysshow']) && $params['alwaysshow']) {
	 * return $content;
	 * }
	 */
	return $content;
}

function smarty_block_static($params, $content, &$smarty, &$repeat) {
	global $fpdb;
	static $pointer = 0;

	// clean old variables

	$smarty->assign(array(
		'subject' => '',
		'content' => '',
		'date' => '',
		'author' => '',
		'version' => '',
		'id' => ''
	));

	if ($arr = $smarty->getTemplateVars('static_page')) {
		$smarty->assign('id', $smarty->getTemplateVars('static_id'));
		if (THEME_LEGACY_MODE) {
			theme_entry_filters($arr);
		}
		$smarty->assign($arr);
		return $content;
	}

	if (isset($params ['content']) && is_array($params ['content']) && $params ['content']) {
		// foreach ($params['entry'] as $k => $val)
		$smarty->assign($params ['content']);
		return $content;
	}

	if (isset($params ['alwaysshow']) && $params ['alwaysshow']) {
		return $content;
	}

	$list = $smarty->getTemplateVars('statics');

	if (isset($list [$pointer])) {
		// foreach ($entry as $k => $val)
		$smarty->assign(static_parse($list [$pointer]));
		$smarty->assign('id', $list [$pointer]);

		$pointer++;

		$repeat = true;
	} else {
		$repeat = false;
	}

	return $content;
}

$smarty->registerPlugin('block', 'statics', 'smarty_block_statics');
$smarty->registerPlugin('block', 'static_block', 'smarty_block_statics');
$smarty->registerPlugin('block', 'static', 'smarty_block_static');

?>
