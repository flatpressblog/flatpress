<?php

/**
 * add entry panel
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
		'updates' => false
	);

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
