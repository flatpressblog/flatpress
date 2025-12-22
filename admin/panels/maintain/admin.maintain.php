<?php

/**
 * add maintain panel
 *
 * Type:
 * Name:
 * Date:
 * Purpose:
 * Input:
 *
 * @author NoWhereMan <real_nowhereman at users dot sf dot com>
 *
 */

/* utility class */
class tpl_deleter extends fs_filelister {

	function __construct() {

		// $this->smarty = $GLOBALS['_FP_SMARTY'];
		$this->_directory = CACHE_DIR;
		parent::__construct();
	}

	function _checkFile($directory, $file) {
		if ($file != CACHE_FILE) {
			array_push($this->_list, $file);
			fs_delete($directory . "/" . $file);
		}
		// trigger_error($file, E_USER_NOTICE);
		return 0;
	}

}

class s_entry_crawler extends fs_filelister {

	var $_directory = CONTENT_DIR;
	var $index;

	function __construct() {
		$this->index = entry_init();
		parent::__construct();
	}

	function _checkFile($directory, $file) {
		$f = $directory . "/" . $file;
		if (is_dir($f) && ctype_digit($file)) {
			return 1;
		}

		if (fnmatch('entry*' . EXT, $file)) {
			$id = basename($file, EXT);
			$arr = entry_parse($id, true);

			echo "[POST] " . $id . " => " . $arr['subject'] . "\n";
			$this->index->add($id, $arr);

			return 0;
		}
	}

}

/**
 * ******************
 */
class admin_maintain extends AdminPanel {

	var $panelname = 'maintain';

	var $actions = array(
		'default' => false,
		'updates' => false,
		'apcu' => false
	);

	function __construct(&$smarty) {
		parent::__construct($smarty);

		// Expose APCu availability flag to templates of this panel
		if (function_exists('is_apcu_on') && is_apcu_on()) {
			$this->smarty->assign('apcu_available', true);
		} else {
			$this->smarty->assign('apcu_available', false);
		}
	}

}

class admin_maintain_apcu extends AdminPanelAction {

	// POST event triggered by the button
	var $events = array(
		'apcu_clear_fp'
	);

	function onapcu_clear_fp($data = null) {
		$cleared = 0;
		$available = function_exists('is_apcu_on') && is_apcu_on();

		$method = 'none';

		$canDelete = $available && function_exists('apcu_delete');
		$pattern = '/^fp:/';

		if ($canDelete) {

			// Preferred: APCUIterator if available (efficient for large caches)
			if (class_exists('APCUIterator')) {

				$format = 0;
				if (defined('APC_ITER_KEY')) {
					$format = APC_ITER_KEY;
				} elseif (defined('APC_ITER_ALL')) {
					$format = APC_ITER_ALL;
				}

				$it = null;
				try {
					$it = new APCUIterator($pattern, $format, 200);
				} catch (\Throwable $e) {
					$it = null;
				}

				if ($it instanceof APCUIterator) {

					$method = 'iterator';
					$batch = array();

					foreach ($it as $entry) {

						if (!is_array($entry)) {
							continue;
						}

						$key = '';
						if (isset($entry ['key'])) {
							$key = (string) $entry ['key'];
						} elseif (isset($entry ['info'])) {
							$key = (string) $entry ['info'];
						} elseif (isset($entry ['name'])) {
							$key = (string) $entry ['name'];
						}

						if ($key === '' || strpos($key, 'fp:') !== 0) {
							continue;
						}

						$batch [] = $key;

						if (count($batch) >= 200) {

							$failed = @apcu_delete($batch);

							if (is_array($failed)) {
								$cleared += max(0, count($batch) - count($failed));
							} elseif ($failed) {
								$cleared += count($batch);
							}

							$batch = array();
						}

					}

					// Flush the remainder
					if (!empty($batch)) {

						$failed = @apcu_delete($batch);

						if (is_array($failed)) {
							$cleared += max(0, count($batch) - count($failed));
						} elseif ($failed) {
							$cleared += count($batch);
						}

					}

				}

			}

			// Fallback: go through cache_list (slower, but works without APCUIterator)
			if ($method === 'none' && function_exists('apcu_cache_info')) {

				$cache = @apcu_cache_info(false); // user cache only

				if (is_array($cache) && !empty($cache ['cache_list']) && is_array($cache ['cache_list'])) {

					$method = 'cache_info';
					$batch = array();

					foreach ($cache ['cache_list'] as $entry) {

						if (!is_array($entry)) {
							continue;
						}

						// Usual structure: 'info' contains the variable name,
						// Some builds use 'key' or 'name' – we take all into account.
						$key = '';
						if (isset($entry ['info'])) {
							$key = (string) $entry ['info'];
						} elseif (isset($entry ['key'])) {
							$key = (string) $entry ['key'];
						} elseif (isset($entry ['name'])) {
							$key = (string) $entry ['name'];
						}

						if ($key === '' || strpos($key, 'fp:') !== 0) {
							continue;
						}

						$batch [] = $key;

						if (count($batch) >= 200) {

							$failed = @apcu_delete($batch);

							if (is_array($failed)) {
								$cleared += max(0, count($batch) - count($failed));
							} elseif ($failed) {
								$cleared += count($batch);
							}

							$batch = array();
						}

					}

					// Flush the remainder
					if (!empty($batch)) {

						$failed = @apcu_delete($batch);

						if (is_array($failed)) {
							$cleared += max(0, count($batch) - count($failed));
						} elseif ($failed) {
							$cleared += count($batch);
						}

					}

				}

			}

		}

		// Last resort: clear the whole APCu user cache to guarantee fp:* is removed.
		// This is useful on hosters that disable cache iteration/introspection functions.
		if ($method === 'none' && $available && function_exists('apcu_clear_cache')) {

			// Try to estimate how many FlatPress entries exist, if possible.
			// (If the hoster disabled introspection APIs, this will stay 0.)
			if ($cleared === 0) {

				if (class_exists('APCUIterator')) {

					$format = 0;
					if (defined('APC_ITER_KEY')) {
						$format = APC_ITER_KEY;
					} elseif (defined('APC_ITER_ALL')) {
						$format = APC_ITER_ALL;
					}

					$it = null;
					try {
						$it = new APCUIterator($pattern, $format, 200);
					} catch (\Throwable $e) {
						$it = null;
					}

					if ($it instanceof APCUIterator) {
						foreach ($it as $entry) {
							if (!is_array($entry)) {
								continue;
							}

							$key = '';
							if (isset($entry ['key'])) {
								$key = (string) $entry ['key'];
							} elseif (isset($entry ['info'])) {
								$key = (string) $entry ['info'];
							} elseif (isset($entry ['name'])) {
								$key = (string) $entry ['name'];
							}

							if ($key !== '' && strpos($key, 'fp:') === 0) {
								$cleared++;
							}
						}
					}

				} elseif (function_exists('apcu_cache_info')) {

					$cache = @apcu_cache_info(false);
					if (is_array($cache) && !empty($cache ['cache_list']) && is_array($cache ['cache_list'])) {
						foreach ($cache ['cache_list'] as $entry) {
							if (!is_array($entry)) {
								continue;
							}

							$key = '';
							if (isset($entry ['info'])) {
								$key = (string) $entry ['info'];
							} elseif (isset($entry ['key'])) {
								$key = (string) $entry ['key'];
							} elseif (isset($entry ['name'])) {
								$key = (string) $entry ['name'];
							}

							if ($key !== '' && strpos($key, 'fp:') === 0) {
								$cleared++;
							}
						}
					}

				}

			}

			$method = 'clear_cache';
			@apcu_clear_cache();
		}

		// Store the result in the session so that it can be displayed after the redirect.
		sess_add('apcu_clear_result', array('done' => true, 'cleared' => $cleared, 'method' => $method));

		// Status code for shared:errorlist.tpl:
		//  1  = successfully deleted (at least one entry) or full cache cleared
		//  2  = no entry found with "fp:"
		// -1 = APCu not available / error
		if (!$available || $method === 'none') {
			$status = -1;
		} elseif ($cleared > 0 || $method === 'clear_cache') {
			$status = 1;
		} else {
			$status = 2;
		}

		$this->smarty->assign('success', $status);

		// Redirect to the current action (apcu) so that POST is gone
		return PANEL_REDIRECT_CURRENT;
	}

	function main() {
		$available = function_exists('is_apcu_on') && is_apcu_on();

		// Flag for the template
		$this->smarty->assign('apcu_available', $available);

		// Read out the result of any previous deletion process
		$clear_result = sess_remove('apcu_clear_result');
		if (is_array($clear_result)) {
			$this->smarty->assign('apcu_clear_result', $clear_result);
		}

		if (!$available || !function_exists('apcu_cache_info') || !function_exists('apcu_sma_info')) {
			$this->smarty->assign('apcu_status', -1);
			$this->smarty->assign('apcu', array());
			return;
		}

		$sma = @apcu_sma_info();
		$cache = @apcu_cache_info(false); // User-Cache

		$shm_size_ini = @ini_get('apc.shm_size');

		$num_seg = isset($sma ['num_seg']) ? (int) $sma ['num_seg'] : 0;
		$seg_size = isset($sma ['seg_size']) ? (int) $sma ['seg_size'] : 0;
		$avail_mem = isset($sma ['avail_mem']) ? (float) $sma ['avail_mem'] : 0.0;
		$memory_type = isset($sma ['memory_type']) ? trim((string) $sma ['memory_type']) : '';

		$total_mem = ($num_seg > 0 && $seg_size > 0) ? (float) $num_seg * $seg_size : 0.0;
		$used_mem = ($total_mem > 0) ? max(0.0, $total_mem - $avail_mem) : 0.0;

		$num_slots = isset($cache ['num_slots']) ? (int) $cache ['num_slots'] : 0;
		$num_hits = isset($cache ['num_hits']) ? (int) $cache ['num_hits'] : 0;
		$num_misses = isset($cache ['num_misses']) ? (int) $cache ['num_misses'] : 0;

		$hit_rate = 0.0;
		$hit_rate_valid = false;
		if (($num_hits + $num_misses) > 0) {
			$hit_rate = $num_hits / ($num_hits + $num_misses);
			$hit_rate_valid = true;
		}

		$free_pct = null;
		$used_pct = null;
		if ($total_mem > 0) {
			$free_pct = ($avail_mem / $total_mem) * 100.0;
			$used_pct = ($used_mem / $total_mem) * 100.0;
		}

		// Human-readable sizes
		$fmtBytes = function ($bytes) {
			$bytes = (float) $bytes;
			if ($bytes <= 0) {
				return '0 B';
			}
			$units = array('B', 'KiB', 'MiB', 'GiB', 'TiB');
			$i = 0;
			while ($bytes >= 1024 && $i < count($units) - 1) {
				$bytes /= 1024;
				$i++;
			}
			return sprintf('%.1f %s', $bytes, $units [$i]);
		};

		// Heuristics: good if hit rate >= 85% and some free memory available
		$status = 'good';
		$status_code = 1;
		if (!$hit_rate_valid || $hit_rate < 0.85 || ($free_pct !== null && $free_pct < 5.0)) {
			$status = 'bad';
			$status_code = -1;
		}

		$hit_rate_percent = $hit_rate_valid ? $hit_rate * 100.0 : null;

		$apcu = array(
			'shm_size_ini' => (string) $shm_size_ini,
			'memory_type' => $memory_type,
			'num_seg' => $num_seg,
			'seg_size' => $seg_size,
			'total_mem' => $total_mem,
			'avail_mem' => $avail_mem,
			'used_mem' => $used_mem,
			'num_slots' => $num_slots,
			'num_hits' => $num_hits,
			'num_misses' => $num_misses,
			'hit_rate' => $hit_rate_percent,
			'free_pct' => $free_pct,
			'used_pct' => $used_pct,
			'status' => $status,
			'total_mem_str' => $total_mem > 0 ? $fmtBytes($total_mem) : '',
			'used_mem_str' => $used_mem > 0 ? $fmtBytes($used_mem) : '',
			'avail_mem_str' => $avail_mem > 0 ? $fmtBytes($avail_mem) : '',
		);

		if ($apcu ['hit_rate'] !== null) {
			$apcu ['hit_rate_str'] = sprintf('%.1f', $apcu ['hit_rate']);
		} else {
			$apcu ['hit_rate_str'] = '';
		}

		if ($free_pct !== null) {
			$apcu ['free_pct_str'] = sprintf('%.1f', $free_pct);
			$apcu ['used_pct_str'] = sprintf('%.1f', $used_pct);
		} else {
			$apcu ['free_pct_str'] = '';
			$apcu ['used_pct_str'] = '';
		}

		$this->smarty->assign('apcu_status', $status_code);
		$this->smarty->assign('apcu', $apcu);
	}
}

class admin_maintain_updates extends AdminPanelAction {

	// URL to fetch the latest version infos
	var $web = 'http://flatpress.org/fp/VERSION';

	// URL to the latest final release
	var $fpweb = 'https://github.com/flatpressblog/flatpress';

	// URL to the latest dev release
	var $sfweb = 'https://github.com/flatpressblog/flatpress/releases';

	function main() {
		$success = -1;
		$ver = array(
			'stable' => 'unknown',
			'unstable' => 'unknown',
			'notice' => ''
		);

		// retrieve content of update file
		$file = utils_geturl($this->web);

		if (!$file ['errno'] && $file ['http_code'] < 400) {
			$ver = utils_kexplode($file ['content']);
			if (!isset($ver ['stable'])) {
				$success = -1;
			} elseif (system_ver_compare($ver ['stable'], SYSTEM_VER)) {
				$success = 1;
			} else {
				$success = 2;
			}
		} else {
			$success = -1;
		}

		$this->smarty->assign('stableversion', $ver ['stable']);
		$this->smarty->assign('unstableversion', $ver ['unstable']);
		$this->smarty->assign('notice', $ver ['notice']);
		$this->smarty->assign('fpweb', $this->fpweb);
		$this->smarty->assign('sfweb', $this->sfweb);
		$this->smarty->assign('success', $success);
	}

}

class admin_maintain_default extends AdminPanelAction {

	var $commands = array(
		'do'
	);

	function dodo($do) {
		switch ($do) {
			case 'rebuild':
				{
					if (substr(INDEX_DIR, -1) == '/') {
						$oldidx = substr(INDEX_DIR, 0, -1);
					}

					$movedir = $oldidx . time();

					header('Content-Type: text/plain');
					echo "ENTERING LOWRES MODE\n\n";

					if (file_exists(INDEX_DIR)) {
						echo "BACKUP INDEX to " . $movedir . "\n";
						$ret = @rename($oldidx, $movedir);

						if (!$ret && is_file($oldidx)) {
							@unlink($movedir);
							clearstatcache(true, $movedir);
							usleep(20000);
							$mv = @rename($oldidx, $movedir) ?: (@copy($oldidx, $movedir) && @unlink($oldidx));
						}

						if (!$ret) {
							die("Cannot backup old index. STOP. \nDid you just purge the cache? If so, the index was in use to create a new cache. This is done now, please simply reload the current page.");
						}
					}
					fs_mkdir(INDEX_DIR);

					new s_entry_crawler();
					exit("\nDONE \nPlease, select the back arrow in your browser");

					return PANEL_NOREDIRECT;
				}
			case 'restorechmods':
				{
					$files = restore_chmods();

					$this->smarty->assign('files', $files);

					$overall_success = count($files) === 0 ? 1 : -1;
					$this->smarty->assign('success', $overall_success);

					return PANEL_NOREDIRECT;
				}
			case 'purgetplcache':
				{
					clearstatcache();
					if (function_exists('opcache_reset') && ini_get('opcache.enable') && ini_get('opcache.enable_cli')) {
						// Called to ensure that all cached PHP scripts are up-to-date.
						opcache_reset();
					}

					$tpldel = new tpl_deleter();
					unset($tpldel);

					$this->smarty->caching = false;
					$this->smarty->clearAllCache();
					$this->smarty->clearCompiledTemplate();
					$this->smarty->compile_check = true;
					$this->smarty->force_compile = true;
					$this->smarty->assign('success', 1);

					if (!file_exists(CACHE_DIR)) {
						fs_mkdir(CACHE_DIR);
					}

					if (!file_exists(COMPILE_DIR)) {
						fs_mkdir(COMPILE_DIR);
					}

					if (function_exists('opcache_reset') && ini_get('opcache.enable') && ini_get('opcache.enable_cli')) {
						// Ensures that all changes to the Smarty cache are also reflected in the OPcache.
						opcache_reset();
					}

					// rebuilds the list of recent comments if LastComments plugin is active
					if (function_exists('plugin_lastcomments_cache')) {
						$coms = Array();

						$q = new FPDB_Query(array(
							'fullparse' => false,
							'start' => 0,
							'count' => -1
						), null);
						while ($q->hasmore()) {
							list ($id, $e) = $q->getEntry();
							$obj = new comment_indexer($id);
							foreach ($obj->getList() as $value) {
								$coms [$value] = $id;
							}
							ksort($coms);
							$coms = array_slice($coms, -LASTCOMMENTS_MAX);
						}
						foreach ($coms as $cid => $eid) {
							$c = comment_parse($eid, $cid);
							plugin_lastcomments_cache($eid, array(
								$cid,
								$c
							));
						}
					}

					return PANEL_NOREDIRECT;
				}
			case 'phpinfo':
				{
					ob_start();
					phpinfo();
					$info = ob_get_contents();
					ob_end_clean();

					$this->smarty->assign('phpinfo', preg_replace('%^.*<body>(.*)</body>.*$%ms', '<div id="phpinfo">$1<div>', $info));
				}

				return PANEL_NOREDIRECT;
		}
	}

	function main() {
	}

}

?>
