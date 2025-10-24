<?php
/*
 * Plugin Name: Media Manager
 * Version: 1.0.2
 * Plugin URI: https://www.flatpress.org
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 * Description: Manage uploaded files and photo galleries. Part of the standard distribution.
 */

// FIXME: Add a config option in the plugin panel to set this value
define('ITEMSPERPAGE', 50);

function mediamanager_updateUseCountArr(&$files, $fupd) {
	// Expand target quantity: all images in affected galleries
	$targets = is_array($fupd) ? $fupd : array();
	$galleriesTouched = array(); // lc gallery names
	foreach ($targets as $fid) {
		if (!isset($files [$fid])) {
			continue;
		}
		$t = isset($files [$fid] ['type']) ? $files [$fid] ['type'] : '';
		if ($t === 'images' && !empty($files [$fid] ['relpath']) && strpos($files [$fid] ['relpath'], '/') !== false) {
			$galleriesTouched [strtolower(substr($files [$fid] ['relpath'], 0, strpos($files [$fid] ['relpath'], '/')))] = true;
		} elseif ($t === 'gallery' && !empty($files [$fid] ['name'])) {
			$galleriesTouched [strtolower($files [$fid] ['name'])] = true;
		}
	}
	if ($galleriesTouched) {
		foreach ($files as $fid => $info) {
			if (!isset($info ['type']) || $info ['type'] !== 'images') {
				continue;
			}
			$rel = isset($info ['relpath']) ? $info ['relpath'] : '';
			if ($rel !== '' && strpos($rel, '/') !== false) {
				$g = strtolower(substr($rel, 0, strpos($rel, '/')));
				if (isset($galleriesTouched [$g])) {
					$targets [] = $fid;
				}
			}
		}
		// uniq
		$targets = array_values(array_unique($targets, SORT_REGULAR));
	}

	// Nothing to update -> skip costly entry scan
	if (empty($targets)) {
		return;
	}

	// Lookup tables: one-time
	$imgMap = array(); // relpath(lower) => [fileIds]
	$galToImg = array(); // gallery(lower) => [image fileIds]
	$galItems = array(); // gallery(lower) => gallery fileId
	foreach ($targets as $fid) {
		if (!isset($files [$fid] ['usecount'])) {
			$files [$fid] ['usecount'] = 0;
		}
		$type = isset($files [$fid] ['type']) ? $files [$fid] ['type'] : '';
		if ($type === 'images') {
			$rel = (isset($files [$fid] ['relpath']) && $files [$fid] ['relpath'] !== '') ? $files [$fid] ['relpath'] : $files [$fid] ['name'];
			$k = strtolower($rel);
			$imgMap [$k] [] = $fid;
			if (strpos($rel, '/') !== false) {
				$g = strtolower(substr($rel, 0, strpos($rel, '/')));
				$galToImg [$g] [] = $fid;
			}
		} elseif ($type === 'gallery') {
			$galItems [strtolower($files [$fid] ['name'])] = $fid;
		}
	}

	// Once over all entries
	$q = new FPDB_Query(array('start' => 0, 'count' => -1, 'fullparse' => true), null);
	// [img=images/<relpath>], with optional additional attributes
	$reImg = '/\\[\\s*img\\b[^\\]]*?=\\s*images\\/([^\\s\\]"]+)/i';
	// [gallery="images/<gallery>"/...] allows quotation marks, optional slash, and subsequent attributes
	$reGal = '/\\[\\s*gallery\\b[^\\]]*?images\\/([^\\/"\\]]+)/i';
	while ($q->hasMore()) {
		list($entryId, $e) = $q->getEntry();
		if (empty($e ['content'])) continue;
		$c = $e ['content'];
		// Per entry: prevents double counting of the same file
		$counted = array();

		// Direct image uses (once per unique relpath)
		if ($imgMap && preg_match_all($reImg, $c, $m)) {
			$rels = array_unique($m [1]);
			foreach ($rels as $rel) {
				$k = strtolower($rel);
				if (!isset($imgMap [$k])) {
					continue;
				}
				foreach ($imgMap [$k] as $fid) {
					if (isset($counted [$fid])) {
						continue;
					}
					$files [$fid] ['usecount']++;
					$counted [$fid] = true;
				}
			}
		}
		// Gallery uses (once per unique gallery); only count files that have not yet been counted in this entry
		if (($galToImg || $galItems) && preg_match_all($reGal, $c, $mg)) {
			$gals = array_unique($mg[1]);
			foreach ($gals as $g) {
				$g = strtolower($g);
				if (isset($galItems [$g]) && !isset($counted [$galItems [$g]])) {
					$files [$galItems [$g]] ['usecount']++;
					$counted [$galItems [$g]] = true;
				}
				if (isset($galToImg [$g])) {
					foreach ($galToImg [$g] as $fid) {
						if (isset($counted [$fid])) {
							continue;
						}
						$files [$fid] ['usecount']++;
						$counted [$fid] = true;
					}
				}
			}
		}
	}

	// Persistence: only usecount (key = relpath, fallback name)
	$usecount = array();
	foreach ($files as $fid => $info) {
		if (!isset($info ['name'], $info ['usecount'])) {
			continue;
		}
		$key = (!empty($info ['relpath'])) ? $info ['relpath'] : $info ['name'];
		$usecount [$key] = (int)$info ['usecount'];
	}
	if (!empty($usecount)) {
		$opts = plugin_getoptions('mediamanager');
		$old = (isset($opts['usecount']) && is_array($opts ['usecount'])) ? $opts ['usecount'] : array();
		$new = $old;
		foreach ($usecount as $k => $v) {
			$new [$k] = $v;
		}
		if ($new !== $old) {
			plugin_addoption('mediamanager', 'usecount', $new);
			plugin_saveoptions('mediamanager');
		}
	}
}

if (class_exists('AdminPanelAction')) {
	include (plugin_getdir('mediamanager') . '/panels/panel.mediamanager.file.php');
}

/**
 * Invalidate count on entry save and delete
 */
function mediamanager_invalidatecount($arg) {
	plugin_addoption('mediamanager', 'usecount', array());
	plugin_saveoptions('mediamanager');
	return $arg;
}
add_filter('delete_post', 'mediamanager_invalidatecount', 1);
add_filter('content_save_pre', 'mediamanager_invalidatecount', 1);
?>
