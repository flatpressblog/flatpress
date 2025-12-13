<?php
/**
 * Plugin Name: Media Manager
 * Version: 2.0.1
 * Plugin URI: https://www.flatpress.org
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 * Description: Manage uploaded files and photo galleries. Part of the standard distribution.
 */

// FIXME: Add a config option in the plugin panel to set this value
define('ITEMSPERPAGE', 50);

function mediamanager_updateUseCountArr(&$files, $fupd) {

	// Global: gallery usage counts per entry from first pass
	if (!isset($GLOBALS ['mm_gal_entry_count'])) {
		$GLOBALS ['mm_gal_entry_count'] = array();
	}
	if (!isset($GLOBALS ['mm_gal_count_built'])) {
		$GLOBALS ['mm_gal_count_built'] = false;
	}

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

	// Short-circuit galleries pass using per-entry counts from first pass
	if (!empty($galItems) && !empty($GLOBALS ['mm_gal_entry_count'])) {
		foreach ($GLOBALS ['mm_gal_entry_count'] as $g => $n) {
			if (isset($galItems [$g])) {
				$files [$galItems [$g]] ['usecount'] += (int)$n;
			}
		}
		// Mark built, to avoid re-adding
		$GLOBALS['mm_gal_count_built'] = true;
		// Skip entry scanning for galleries
		goto MM_PERSIST;
	}
	// Once over all entries
	$q = new FPDB_Query(array('start' => 0, 'count' => -1, 'fullparse' => true), null);

	// IMG: [img="images/<relpath>" ...] or [img=images/<relpath> ...]
	// - optional " or ' after '='
	// - Path ends before space, ], " or '
	// - additional attributes allowed
	$reImg = "/\\[\\s*img\\b[^\\]]*?=\\s*[\"']?images\\/([^\\s\\]\"']+)/iu";

	// GALLERY: [gallery=\"images/<folder>/\" ...] or without Quotes/Slash
	// - optional " or ' after '='
	// - optional trailing slash
	// - additional attributes allowed
	$reGal = "/\\[\\s*gallery\\b[^\\]]*?=\\s*[\"']?images\\/([^\\s\\]\\/\"']+)/iu";

	while ($q->hasMore()) {
		list($entryId, $e) = $q->getEntry();
		if (empty($e ['content'])) {
			continue;
		}
		$c = $e ['content'];
		$counted = array();
		$countedGalFromImg = array();

		// Direct image uses (once per unique relpath)
		if ($imgMap && (stripos($c,'images/')!==false) && preg_match_all($reImg, $c, $m)) {
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

		// Fallback: if images are linked but not part of $targets, still count their gallery once per entry
		if (!empty($galItems) && preg_match_all($reImg, $c, $m)) {
			$rels = array_unique($m [1]);
			foreach ($rels as $rel) {
				$p = strpos($rel, '/');
				if ($p === false) {
					continue;
				}
				$g = strtolower(substr($rel, 0, $p));
				if (isset($galItems [$g]) && !isset($counted [$galItems [$g]]) && !isset($countedGalFromImg [$g])) {
					$files [$galItems [$g]] ['usecount']++;
					$counted [$galItems [$g]] = true;
					$countedGalFromImg [$g] = true;
				}
			}
		}

		// Gallery uses (once per unique gallery); only count files that have not yet been counted in this entry
		if (($galToImg || $galItems) && (stripos($c,'images/')!==false) && preg_match_all($reGal, $c, $mg)) {
			$gals = array_unique($mg[1]);
			// Accumulate per-entry gallery counts (files-pass)
			if (empty($galItems) && !$GLOBALS ['mm_gal_count_built']) {
				foreach ($gals as $gcount) {
					$gcount = strtolower($gcount);
					$GLOBALS ['mm_gal_entry_count'] [$gcount] = isset($GLOBALS ['mm_gal_entry_count'] [$gcount]) ? ($GLOBALS ['mm_gal_entry_count'] [$gcount]+1) : 1;
				}
			}

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

	MM_PERSIST:
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
