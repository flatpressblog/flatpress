<?php

class comment_indexer extends fs_filelister {

	function __construct($id) {
		$f = bdb_idtofile($id, BDB_COMMENT); // todo change
		$this->_directory = $f;
		parent::__construct();
		// substr(bdb_idtofile($id), -strlen(EXT));
	}

	function _checkFile($directory, $file) {
		$f = $directory . "/" . $file;
		if (fnmatch('comment*' . EXT, $file)) {
			array_push($this->_list, basename($file, EXT));
			return 0;
		}
		return;
	}

	// overrides parent method to return sorted results
	function getList() {
		sort($this->_list);
		return parent::getList();
	}

}

/**
 * Small on-disk cache for comment counts to avoid directory scans on hosts without APCu.
 * Stored outside the comments directory to not affect directory mtime.
 */
function comment_count_cachefile($id) {
	// CACHE_DIR is defined in defaults.php; keep path stable across platforms.
	$dir = (defined('CACHE_DIR') ? CACHE_DIR : '');
	if ($dir && function_exists('fs_mkdir')) {
		@fs_mkdir($dir);
	} elseif ($dir) {
		@mkdir($dir, DIR_PERMISSIONS, true);
	}
	// Entry IDs are safe filename components (entryYYMMDD-HHMMSS).
	return $dir . $id . '.txt';
}

function comment_count_scan_dir($dir) {
	$count = 0;
	$dh = @opendir($dir);
	if (!$dh) {
		return 0;
	}
	while (($file = readdir($dh)) !== false) {
		// Fast path: only comment*.EXT files.
		if ($file [0] === '.') {
			continue;
		}
		if (fnmatch('comment*' . EXT, $file)) {
			$count++;
		}
	}
	closedir($dh);
	return $count;
}

/**
 * Returns comment count for entry $id without building/sorting the full list.
 */
function comment_getcount($id) {
	$dir = bdb_idtofile($id, BDB_COMMENT);
	if (!@is_dir($dir)) {
		return 0;
	}

	static $local = array(), $meta = array();
	clearstatcache(true, $dir);
	$mt = @filemtime($dir);
	if ($mt === false) {
		return 0;
	}
	$sig = (string) $mt;

	if (isset($local [$id]) && (($meta [$id] ?? null) === $sig)) {
		return (int) $local [$id];
	}

	$apcu_on = function_exists('is_apcu_on') ? is_apcu_on() : false;
	if ($apcu_on) {
		$key = 'fp:comments:count:' . $id . ':' . $mt;
		$hit = false;
		$val = apcu_get($key, $hit);
		if ($hit && is_int($val)) {
			$local [$id] = $val;
			$meta [$id] = $sig;
			return $val;
		}
	}

	$cachefile = comment_count_cachefile($id);
	if ($cachefile && is_string($cachefile) && @is_file($cachefile)) {
		$raw = @file_get_contents($cachefile);
		if (is_string($raw) && $raw !== '') {
			$raw = trim($raw);
			$parts = explode(':', $raw, 2);
			if (count($parts) === 2 && $parts [0] === (string) $mt) {
				$cnt = (int) $parts [1];
				$local [$id] = $cnt;
				$meta [$id] = $sig;
				if ($apcu_on) {
					@apcu_set('fp:comments:count:' . $id . ':' . $mt, $cnt, 300);
				}
				return $cnt;
			}
		}
	}

	$cnt = comment_count_scan_dir($dir);
	$local [$id] = $cnt;
	$meta [$id] = $sig;

	if ($cachefile) {
		@io_write_file($cachefile, $mt . ':' . $cnt);
	}
	if ($apcu_on) {
		@apcu_set('fp:comments:count:' . $id . ':' . $mt, $cnt, 300);
	}
	return $cnt;
}

/**
 * function bdb_get_comments
 *
 * <p>On success returns an array containing the comment <b>IDs</b>, associated to
 * the entry ID in $id</p>
 * <p>On failure returns false</p>
 *
 * @param string $id
 *        	string formatted like "prefixYYMMDD-HHMMSS.EXT"
 * @return mixed
 *
 * @see bdb_idtofile()
 */
function comment_getlist($id) {
	$dir = bdb_idtofile($id, BDB_COMMENT);
	if (!@is_dir($dir)) {
		return array();
	}

	static $local = array(), $meta = array();
	clearstatcache(true, $dir);
	$mt = @filemtime($dir);
	$sig = (($mt !== false) ? (string) $mt : 'na');

	if (isset($local [$id]) && (($meta [$id] ?? null) === $sig)) {
		return $local [$id];
	}

	$apcu_on = function_exists('is_apcu_on') ? is_apcu_on() : false;
	$key = null;
	if ($apcu_on && $mt !== false) {
		$key = 'fp:comments:list:' . $id . ':' . $mt;
		$hit = false;
		$val = apcu_get($key, $hit);
		if ($hit && is_array($val)) {
			$local [$id] = $val;
			$meta [$id] = $sig;
			return $val;
		}
	}

	$obj = new comment_indexer($id);
	$list = $obj->getList();
	$local [$id] = $list;
	$meta [$id] = $sig;

	if ($key) {
		@apcu_set($key, $list, 300);
	}

	return $list;
}

function comment_parse($entryid, $id) {
	$f = comment_exists($entryid, $id);

	if (!$f) {
		return false;
	}

	$fc = io_load_file($f);
	$arr = utils_kexplode($fc);

	// $arr['EMAIL'] = apply_filters('comment_email', $arr['EMAIL']);
	// hackish: dash to underscore for ip-address :( todo: clean this up here or somewhere else
	// $arr['ip_address'] = $arr['ip-address'];
	return array_change_key_case($arr, CASE_LOWER);
}

function comment_exists($entryid, $id) {
	if (!preg_match('|^comment[0-9]{6}-[0-9]{6}$|', $id)) {
		return false;
	}
	$f = entry_exists($entryid);
	if (!$f) {
		return false;
	}

	$f2 = substr($f, 0, -strlen(EXT)) . '/comments/' . $id . EXT;
	if (!file_exists($f2)) {
		return false;
	}

	return $f2;
}

function comment_clean(&$arr) {
	$arr ['name'] = strip_tags(apply_filters('pre_comment_author_name', stripslashes($arr ['name'])));
	if (isset($arr ['email'])) {
		$arr ['email'] = strip_tags(apply_filters('pre_comment_author_email', $arr ['email']));
	}
	if (isset($arr ['url'])) {
		$arr ['url'] = strip_tags(apply_filters('pre_comment_author_url', $arr ['url']));
	}
	$arr ['content'] = strip_tags(apply_filters('pre_comment_content', $arr ['content']));
	return $arr;
}

/**
 * function bdb_save_comment
 *
 * <p>Saves the content of the $comment array, associating it to the entry-ID $id.</p>
 * <p>$comment must be formatted as the one returned by {@link bdb_parse_entry()}.</p>
 * <p>Returns the comment ID on success, or false on failure.</p>
 *
 * @param string $id string formatted like "prefixYYMMDD-HHMMSS"
 * @param array $comment array formatted as the one returned by {@link bdb_parse_entry()}
 * @return string|false
 *
 * @see bdb_parse_entry()
 */
function comment_save($id, $comment) {
	comment_clean($comment);

	$comment = array_change_key_case($comment, CASE_UPPER);

	$comment_dir = bdb_idtofile($id, BDB_COMMENT);
	$entryid = $id;

	if (!isset($comment ['DATE'])) {
		$comment ['DATE'] = date_time();
	}
	$comment_id = bdb_idfromtime(BDB_COMMENT, $comment ['DATE']);
	$f = $comment_dir . $comment_id . EXT;
	$str = utils_kimplode($comment);
	if (io_write_file($f, $str)) {
		// Invalidate cached comment count for this entry (file cache + APCu).
		@unlink(comment_count_cachefile($entryid));
		do_action('comment_save', $entryid, $comment_id);
		return $comment_id;
	}

	return false;
}

/**
 * function comment_delete
 *
 * <p>Deletes the $id comment</p>
 * <p>Returns true on success, or false on failure</p>
 *
 * @param string $id
 *        	string formatted like "entryYYMMDD-HHMMSS"
 * @param string $comment_id
 *        	string representig comment id as in "commentYYMMDD-HHMMSS"
 * @return bool
 *
 * @see entry_delete()
 */
function comment_delete($id, $comment_id) {
	// Pre-delete event
	do_action('comment_delete', $id, $comment_id);
	$comment_dir = bdb_idtofile($id, BDB_COMMENT);
	$f = $comment_dir . $comment_id . EXT;
	$ok = fs_delete($f);
	if ($ok) {
		// Post-delete event for cache invalidation
		do_action('comment_deleted', $id, $comment_id);
		@unlink(comment_count_cachefile($id));
	}
	return $ok;
}

function dummy_comment($val) {
	return $val;
}

add_filter('comment_validate', 'dummy_comment');

?>
