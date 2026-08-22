<?php
/**
 * Incremental Media Manager usage index.
 *
 * The index is a regenerable runtime artifact. Entry files remain the source
 * of truth. A compact JSON file provides the portable cache layer; FlatPress'
 * APCu helpers add an optional instance-namespaced hot cache.
 *
 * @package FlatPress
 */

if (!defined('MEDIAMANAGER_USAGE_INDEX_VERSION')) {
	define('MEDIAMANAGER_USAGE_INDEX_VERSION', 1);
}
if (!defined('MEDIAMANAGER_USAGE_APCU_TTL')) {
	define('MEDIAMANAGER_USAGE_APCU_TTL', 600);
}

/**
 * Logical APCu key. core.apcu.php adds the per-instance fp:<namespace>: prefix.
 *
 * @return string
 */
function mediamanager_usage_apcu_key() {
	return 'mediamanager:usage-index:v' . MEDIAMANAGER_USAGE_INDEX_VERSION;
}

/**
 * @return string
 */
function mediamanager_usage_cache_file() {
	return CACHE_DIR . 'mediamanager.useindex.json';
}

/**
 * @return string
 */
function mediamanager_usage_lock_file() {
	return CACHE_DIR . 'mediamanager.useindex.lock';
}

/**
 * @return string
 */
function mediamanager_usage_dirty_file() {
	return CACHE_DIR . 'mediamanager.useindex.dirty';
}

/**
 * @param int $generation
 * @return array
 */
function mediamanager_usage_empty_index($generation = 0) {
	return array(
		'version' => MEDIAMANAGER_USAGE_INDEX_VERSION,
		'generation' => max(0, (int)$generation),
		'direct_images' => array(),
		'galleries' => array(),
		'gallery_explicit' => array(),
		'image_gallery_overlap' => array(),
		'entries' => array()
	);
}

/**
 * Normalize one media path or gallery key for the same case-insensitive
 * matching semantics that the legacy Media Manager used.
 *
 * @param mixed $value
 * @return string
 */
function mediamanager_usage_normalize_key($value) {
	$key = str_replace('\\', '/', (string)$value);
	return strtolower($key);
}

/**
 * Normalize an entry contribution stored in the JSON cache.
 *
 * @param mixed $summary
 * @return array{images:array<int,string>,galleries:array<int,string>}
 */
function mediamanager_usage_normalize_entry_summary($summary) {
	$images = array();
	$galleries = array();

	if (is_array($summary)) {
		if (isset($summary ['images']) && is_array($summary ['images'])) {
			foreach ($summary ['images'] as $key) {
				$key = mediamanager_usage_normalize_key($key);
				if ($key !== '') {
					$images [$key] = true;
				}
			}
		}
		if (isset($summary ['galleries']) && is_array($summary ['galleries'])) {
			foreach ($summary ['galleries'] as $key) {
				$key = mediamanager_usage_normalize_key($key);
				if ($key !== '') {
					$galleries [$key] = true;
				}
			}
		}
	}

	$images = array_keys($images);
	$galleries = array_keys($galleries);
	sort($images, SORT_STRING);
	sort($galleries, SORT_STRING);

	return array(
		'images' => $images,
		'galleries' => $galleries
	);
}

/**
 * Extract direct image and explicit gallery references from one entry.
 *
 * Both lists contain unique, lowercase values. This deliberately mirrors the
 * historical Media Manager regex and case-insensitive matching behavior.
 *
 * @param mixed $content
 * @return array{images:array<int,string>,galleries:array<int,string>}
 */
function mediamanager_usage_extract_entry($content) {
	$content = is_string($content) ? $content : '';
	$images = array();
	$galleries = array();

	if ($content === '' || stripos($content, 'images/') === false) {
		return array(
			'images' => array(),
			'galleries' => array()
		);
	}

	$reImg = "/\\[\\s*img\\b[^\\]]*?=\\s*[\"']?images\\/([^\\s\\]\"']+)/iu";
	$reGal = "/\\[\\s*gallery\\b[^\\]]*?=\\s*[\"']?images\\/([^\\s\\]\\/\"']+)/iu";

	if (preg_match_all($reImg, $content, $matches)) {
		foreach ($matches [1] as $rel) {
			$rel = mediamanager_usage_normalize_key($rel);
			if ($rel !== '') {
				$images [$rel] = true;
			}
		}
	}

	if (preg_match_all($reGal, $content, $matches)) {
		foreach ($matches [1] as $gallery) {
			$gallery = mediamanager_usage_normalize_key($gallery);
			if ($gallery !== '') {
				$galleries [$gallery] = true;
			}
		}
	}

	$imageKeys = array_keys($images);
	$galleryKeys = array_keys($galleries);
	sort($imageKeys, SORT_STRING);
	sort($galleryKeys, SORT_STRING);

	return array(
		'images' => $imageKeys,
		'galleries' => $galleryKeys
	);
}

/**
 * @param array{images:array<int,string>,galleries:array<int,string>} $summary
 * @return bool
 */
function mediamanager_usage_summary_is_empty($summary) {
	return empty($summary ['images']) && empty($summary ['galleries']);
}

/**
 * Expand one per-entry contribution into the four aggregate sets.
 *
 * @param array{images:array<int,string>,galleries:array<int,string>} $summary
 * @return array<string,array<string,bool>>
 */
function mediamanager_usage_expand_entry_summary($summary) {
	$directImages = array();
	$explicitGalleries = array();
	$usedGalleries = array();
	$overlap = array();

	foreach ($summary ['galleries'] as $gallery) {
		$explicitGalleries [$gallery] = true;
		$usedGalleries [$gallery] = true;
	}

	foreach ($summary ['images'] as $rel) {
		$directImages [$rel] = true;
		$slash = strpos($rel, '/');
		if ($slash === false) {
			continue;
		}

		$gallery = substr($rel, 0, $slash);
		if ($gallery === '') {
			continue;
		}

		$usedGalleries [$gallery] = true;
		if (isset($explicitGalleries [$gallery])) {
			$overlap [$rel] = true;
		}
	}

	return array(
		'direct_images' => $directImages,
		'galleries' => $usedGalleries,
		'gallery_explicit' => $explicitGalleries,
		'image_gallery_overlap' => $overlap
	);
}

/**
 * Apply a set delta to one aggregate counter map.
 *
 * @param array<string,int> $counter
 * @param array<string,bool> $oldSet
 * @param array<string,bool> $newSet
 * @return void
 */
function mediamanager_usage_apply_counter_delta(&$counter, $oldSet, $newSet) {
	foreach ($oldSet as $key => $unused) {
		if (isset($newSet [$key])) {
			continue;
		}
		$current = isset($counter [$key]) ? (int)$counter [$key] : 0;
		$current--;
		if ($current > 0) {
			$counter [$key] = $current;
		} else {
			unset($counter [$key]);
		}
	}

	foreach ($newSet as $key => $unused) {
		if (isset($oldSet [$key])) {
			continue;
		}
		$counter [$key] = isset($counter [$key]) ? ((int)$counter [$key] + 1) : 1;
	}
}

/**
 * Replace one entry contribution in an already-valid index.
 *
 * @param array $index
 * @param string $entryId
 * @param array{images:array<int,string>,galleries:array<int,string>} $newSummary
 * @return bool True when aggregate data changed.
 */
function mediamanager_usage_replace_entry(&$index, $entryId, $newSummary) {
	$entryId = (string)$entryId;
	$oldSummary = isset($index ['entries'] [$entryId]) ? mediamanager_usage_normalize_entry_summary($index ['entries'] [$entryId]) : array('images' => array(), 'galleries' => array());
	$newSummary = mediamanager_usage_normalize_entry_summary($newSummary);

	if ($oldSummary == $newSummary) {
		return false;
	}

	$oldExpanded = mediamanager_usage_expand_entry_summary($oldSummary);
	$newExpanded = mediamanager_usage_expand_entry_summary($newSummary);

	foreach (array('direct_images', 'galleries', 'gallery_explicit', 'image_gallery_overlap') as $bucket) {
		mediamanager_usage_apply_counter_delta(
			$index [$bucket],
			$oldExpanded [$bucket],
			$newExpanded [$bucket]
		);
	}

	if (mediamanager_usage_summary_is_empty($newSummary)) {
		unset($index ['entries'] [$entryId]);
	} else {
		$index ['entries'] [$entryId] = $newSummary;
	}

	return true;
}

/**
 * @param mixed $counter
 * @return array<string,int>
 */
function mediamanager_usage_normalize_counter($counter) {
	$out = array();
	if (!is_array($counter)) {
		return $out;
	}

	foreach ($counter as $key => $value) {
		$key = mediamanager_usage_normalize_key($key);
		$value = (int)$value;
		if ($key !== '' && $value > 0) {
			$out [$key] = $value;
		}
	}
	ksort($out, SORT_STRING);
	return $out;
}

/**
 * Normalize and validate a decoded JSON index.
 *
 * @param mixed $data
 * @return array|null
 */
function mediamanager_usage_normalize_index($data) {
	if (!is_array($data) || !isset($data ['version']) || (int)$data ['version'] !== MEDIAMANAGER_USAGE_INDEX_VERSION || !isset($data ['direct_images'], $data ['galleries'], $data ['gallery_explicit'], $data ['image_gallery_overlap'], $data ['entries']) || !is_array($data ['entries'])) {
		return null;
	}

	$index = mediamanager_usage_empty_index(isset($data ['generation']) ? (int)$data ['generation'] : 0);
	$index ['direct_images'] = mediamanager_usage_normalize_counter($data ['direct_images']);
	$index ['galleries'] = mediamanager_usage_normalize_counter($data ['galleries']);
	$index ['gallery_explicit'] = mediamanager_usage_normalize_counter($data ['gallery_explicit']);
	$index ['image_gallery_overlap'] = mediamanager_usage_normalize_counter($data ['image_gallery_overlap']);

	foreach ($data ['entries'] as $entryId => $summary) {
		$summary = mediamanager_usage_normalize_entry_summary($summary);
		if (!mediamanager_usage_summary_is_empty($summary)) {
			$index ['entries'] [(string)$entryId] = $summary;
		}
	}
	ksort($index ['entries'], SORT_STRING);

	return $index;
}

/**
 * Fast structural validation for APCu payloads produced by this code.
 *
 * @param mixed $data
 * @return bool
 */
function mediamanager_usage_index_has_valid_shape($data) {
	return is_array($data) && isset($data ['version']) && (int)$data ['version'] === MEDIAMANAGER_USAGE_INDEX_VERSION && isset($data ['direct_images'], $data ['galleries'], $data ['gallery_explicit'], $data ['image_gallery_overlap'], $data ['entries']) && is_array($data ['direct_images']) && is_array($data ['galleries']) && is_array($data ['gallery_explicit']) && is_array($data ['image_gallery_overlap']) && is_array($data ['entries']);
}

/**
 * Return a lightweight signature for the atomically replaced JSON file.
 *
 * inode/device are included where available. The index generation is read from
 * the small JSON header as a portable fallback for filesystems with coarse
 * timestamp resolution or no useful inode value.
 *
 * @param string $file
 * @return string|null
 */
function mediamanager_usage_file_signature($file) {
	$stat = @stat($file);
	if (!is_array($stat)) {
		return null;
	}

	$generation = 'unknown';
	$handle = @fopen($file, 'rb');
	if (is_resource($handle)) {
		$head = @fread($handle, 512);
		@fclose($handle);
		if (preg_match('/"generation"\s*:\s*([0-9]+)/', $head, $match)) {
			$generation = $match [1];
		}
	}

	$parts = array(
		isset($stat ['dev']) ? (string)$stat ['dev'] : '0',
		isset($stat ['ino']) ? (string)$stat ['ino'] : '0',
		isset($stat ['mtime']) ? (string)$stat ['mtime'] : '0',
		isset($stat ['ctime']) ? (string)$stat ['ctime'] : '0',
		isset($stat ['size']) ? (string)$stat ['size'] : '0',
		$generation
	);
	return implode(':', $parts);
}

/**
 * Read the portable JSON layer, using APCu as an optional front cache.
 *
 * @param bool $ignoreDirty Used only while holding the write lock.
 * @return array|null
 */
function mediamanager_usage_read_index($ignoreDirty = false) {
	$file = mediamanager_usage_cache_file();
	if (!$ignoreDirty && is_file(mediamanager_usage_dirty_file())) {
		return null;
	}

	$signature = mediamanager_usage_file_signature($file);
	if ($signature === null) {
		apcu_delete_key(mediamanager_usage_apcu_key());
		return null;
	}

	$hit = false;
	$cached = apcu_get(mediamanager_usage_apcu_key(), $hit);
	if ($hit && is_array($cached) && isset($cached ['signature'], $cached ['index']) && $cached ['signature'] === $signature && mediamanager_usage_index_has_valid_shape($cached ['index'])) {
		return $cached ['index'];
	}

	$json = io_load_file_uncached($file, true);
	if (!is_string($json) || $json === '') {
		apcu_delete_key(mediamanager_usage_apcu_key());
		return null;
	}

	$decoded = json_decode($json, true);
	$index = mediamanager_usage_normalize_index($decoded);
	if ($index === null) {
		apcu_delete_key(mediamanager_usage_apcu_key());
		return null;
	}

	apcu_set(
		mediamanager_usage_apcu_key(),
		array('signature' => $signature, 'index' => $index),
		MEDIAMANAGER_USAGE_APCU_TTL
	);
	return $index;
}

/**
 * Persist the index atomically and refresh the instance-namespaced APCu layer.
 *
 * @param array $index
 * @return bool
 */
function mediamanager_usage_write_index($index) {
	$index = mediamanager_usage_normalize_index($index);
	if ($index === null) {
		return false;
	}

	$json = json_encode($index, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	if (!is_string($json)) {
		return false;
	}
	$json .= "\n";

	$file = mediamanager_usage_cache_file();
	if (!io_write_file($file, $json, array('fsync' => true))) {
		apcu_delete_key(mediamanager_usage_apcu_key());
		return false;
	}

	clearstatcache(true, $file);
	$signature = mediamanager_usage_file_signature($file);
	if ($signature !== null) {
		apcu_set(
			mediamanager_usage_apcu_key(),
			array('signature' => $signature, 'index' => $index),
			MEDIAMANAGER_USAGE_APCU_TTL
		);
	} else {
		apcu_delete_key(mediamanager_usage_apcu_key());
	}

	return true;
}

/**
 * @return resource|false
 */
function mediamanager_usage_lock() {
	if (!fs_mkdir(CACHE_DIR)) {
		return false;
	}

	$lock = @fopen(mediamanager_usage_lock_file(), 'c');
	if (!is_resource($lock)) {
		return false;
	}
	if (!@flock($lock, LOCK_EX)) {
		@fclose($lock);
		return false;
	}
	return $lock;
}

/**
 * @param mixed $lock
 * @return void
 */
function mediamanager_usage_unlock($lock) {
	if (!is_resource($lock)) {
		return;
	}
	@flock($lock, LOCK_UN);
	@fclose($lock);
}

/**
 * Mark the index as not safe for readers while a committed entry change is
 * being folded in. The token prevents one concurrent writer from clearing a
 * newer writer's dirty marker.
 *
 * @return string|false
 */
function mediamanager_usage_mark_dirty() {
	apcu_delete_key(mediamanager_usage_apcu_key());

	try {
		$token = bin2hex(random_bytes(12));
	} catch (\Throwable $e) {
		$token = sha1(uniqid('', true) . mt_rand());
	}

	$payload = $token . "\n";
	if (io_write_file(mediamanager_usage_dirty_file(), $payload, array('fsync' => true))) {
		return $token;
	}

	/**
	 * If the marker itself cannot be written, prefer a cold cache over stale
	 * data. Failure to remove a no-longer-writable cache is still non-fatal to
	 * the entry write; entry data remains the source of truth.
	 */
	@unlink(mediamanager_usage_cache_file());
	apcu_delete_key(mediamanager_usage_apcu_key());
	return false;
}

/**
 * Remove only the dirty marker that belongs to this writer/rebuilder.
 *
 * @param string|false $token
 * @return void
 */
function mediamanager_usage_clear_dirty($token) {
	if (!is_string($token) || $token === '') {
		return;
	}
	$file = mediamanager_usage_dirty_file();
	$current = @file_get_contents($file);
	if (is_string($current) && trim($current) === $token) {
		@unlink($file);
	}
}

/**
 * Read the current dirty token. Used by recovery rebuilds so they only clear
 * the marker they actually recovered.
 *
 * @return string|false
 */
function mediamanager_usage_current_dirty_token() {
	$current = @file_get_contents(mediamanager_usage_dirty_file());
	if (!is_string($current)) {
		return false;
	}
	$current = trim($current);
	return $current !== '' ? $current : false;
}

/**
 * Build the complete index with one full entry pass and no comments.
 *
 * @param int $generation
 * @return array
 */
function mediamanager_usage_rebuild_index($generation = 0) {
	$index = mediamanager_usage_empty_index($generation);
	$query = new FPDB_Query(
		array(
			'start' => 0,
			'count' => -1,
			'fullparse' => false
		),
		null
	);

	while ($query->hasMore()) {
		$entry = $query->getEntry();
		if (!is_array($entry) || !isset($entry [0])) {
			continue;
		}

		$entryId = (string)$entry [0];
		$parsed = entry_parse($entryId);
		if (!is_array($parsed)) {
			continue;
		}

		$content = isset($parsed ['content']) ? $parsed ['content'] : '';
		$summary = mediamanager_usage_extract_entry($content);
		if (mediamanager_usage_summary_is_empty($summary)) {
			continue;
		}

		mediamanager_usage_replace_entry($index, $entryId, $summary);
	}

	$index ['generation'] = max(0, (int)$generation) + 1;
	return $index;
}

/**
 * Drop the historical plugin option after the new runtime index was safely
 * persisted. This moves regenerable data out of settings.conf.php.
 *
 * Failure is harmless: the old option is ignored by the new code.
 *
 * @return void
 */
function mediamanager_usage_cleanup_legacy_config() {
	global $fp_config;
	static $attempted = false;

	if ($attempted) {
		return;
	}
	$attempted = true;

	if (!isset($fp_config ['plugins'] ['mediamanager']) || !is_array($fp_config ['plugins'] ['mediamanager']) || !array_key_exists('usecount', $fp_config ['plugins'] ['mediamanager'])) {
		return;
	}

	unset($fp_config ['plugins'] ['mediamanager'] ['usecount']);
	if (function_exists('plugin_saveoptions')) {
		@plugin_saveoptions('mediamanager');
	}
}

/**
 * Return a valid index. Missing/corrupt/dirty state causes one locked rebuild.
 *
 * If locking or persistence is unavailable, a correct in-memory rebuild is
 * still returned for this request; no entry write is ever made dependent on
 * the cache.
 *
 * @return array
 */
function mediamanager_usage_get_index() {
	$index = mediamanager_usage_read_index(false);
	if ($index !== null) {
		mediamanager_usage_cleanup_legacy_config();
		return $index;
	}

	$lock = mediamanager_usage_lock();
	if ($lock === false) {
		return mediamanager_usage_rebuild_index(0);
	}

	try {
		$dirtyToken = mediamanager_usage_current_dirty_token();
		$index = mediamanager_usage_read_index(false);
		if ($index !== null) {
			mediamanager_usage_cleanup_legacy_config();
			return $index;
		}

		$previous = mediamanager_usage_read_index(true);
		$generation = is_array($previous) && isset($previous ['generation']) ? (int)$previous ['generation'] : 0;

		$index = mediamanager_usage_rebuild_index($generation);
		if (mediamanager_usage_write_index($index)) {
			mediamanager_usage_clear_dirty($dirtyToken);
			mediamanager_usage_cleanup_legacy_config();
		}
		return $index;
	} finally {
		mediamanager_usage_unlock($lock);
	}
}

/**
 * Apply a committed entry state to the existing index.
 *
 * Per-entry contributions make this operation idempotent. If another request
 * rebuilt the index after the entry commit but before this hook acquired the
 * lock, replace_entry() observes the already-current contribution and does
 * not double-count it.
 *
 * @param string $entryId
 * @param mixed $content
 * @return void
 */
function mediamanager_usage_commit_entry($entryId, $content) {
	$summary = mediamanager_usage_extract_entry($content);
	$file = mediamanager_usage_cache_file();

	// Before the first Media Manager rebuild there is nothing to update. Avoid
	// turning an entry save into an O(N) operation.
	if (!is_file($file)) {
		apcu_delete_key(mediamanager_usage_apcu_key());
		return;
	}

	$dirtyToken = mediamanager_usage_mark_dirty();
	$lock = mediamanager_usage_lock();
	if ($lock === false) {
		return;
	}

	try {
		$index = mediamanager_usage_read_index(true);
		if ($index === null) {
			return;
		}

		if (!mediamanager_usage_replace_entry($index, (string)$entryId, $summary)) {
			mediamanager_usage_clear_dirty($dirtyToken);
			return;
		}

		$index ['generation'] = isset($index ['generation']) ? ((int)$index ['generation'] + 1) : 1;
		if (mediamanager_usage_write_index($index)) {
			mediamanager_usage_clear_dirty($dirtyToken);
			mediamanager_usage_cleanup_legacy_config();
		}
	} finally {
		mediamanager_usage_unlock($lock);
	}
}

/**
 * Remove one committed entry contribution.
 *
 * @param string $entryId
 * @return void
 */
function mediamanager_usage_remove_entry($entryId) {
	$file = mediamanager_usage_cache_file();
	if (!is_file($file)) {
		apcu_delete_key(mediamanager_usage_apcu_key());
		return;
	}

	$dirtyToken = mediamanager_usage_mark_dirty();
	$lock = mediamanager_usage_lock();
	if ($lock === false) {
		return;
	}

	try {
		$index = mediamanager_usage_read_index(true);
		if ($index === null) {
			return;
		}

		$empty = array('images' => array(), 'galleries' => array());
		if (!mediamanager_usage_replace_entry($index, (string)$entryId, $empty)) {
			mediamanager_usage_clear_dirty($dirtyToken);
			return;
		}

		$index ['generation'] = isset($index ['generation']) ? ((int)$index ['generation'] + 1) : 1;
		if (mediamanager_usage_write_index($index)) {
			mediamanager_usage_clear_dirty($dirtyToken);
			mediamanager_usage_cleanup_legacy_config();
		}
	} finally {
		mediamanager_usage_unlock($lock);
	}
}

/**
 * Materialize the Media Manager display count for one filesystem item.
 *
 * For an image inside a gallery this is a set union:
 * direct-image entries U explicit-gallery entries.
 *
 * @param array $index
 * @param string $relpath
 * @param string $type
 * @return int
 */
function mediamanager_usage_count_for($index, $relpath, $type) {
	if (!mediamanager_usage_index_has_valid_shape($index)) {
		return 0;
	}

	$key = mediamanager_usage_normalize_key($relpath);
	if ($key === '') {
		return 0;
	}

	if ($type === 'gallery') {
		return isset($index ['galleries'] [$key]) ? (int)$index ['galleries'] [$key] : 0;
	}
	if ($type !== 'images') {
		return 0;
	}

	$direct = isset($index ['direct_images'] [$key]) ? (int)$index ['direct_images'] [$key] : 0;
	$slash = strpos($key, '/');
	if ($slash === false) {
		return $direct;
	}

	$gallery = substr($key, 0, $slash);
	$viaGallery = isset($index ['gallery_explicit'] [$gallery]) ? (int)$index ['gallery_explicit'] [$gallery] : 0;
	$overlap = isset($index ['image_gallery_overlap'] [$key]) ? (int)$index ['image_gallery_overlap'] [$key] : 0;

	return max(0, $direct + $viaGallery - $overlap);
}

/**
 * @param array $index
 * @return array<string,bool>
 */
function mediamanager_usage_used_galleries($index) {
	$used = array();
	if (!mediamanager_usage_index_has_valid_shape($index)) {
		return $used;
	}

	foreach ($index ['galleries'] as $gallery => $count) {
		if ((int)$count > 0) {
			$used [(string)$gallery] = true;
		}
	}
	return $used;
}

/**
 * Successful entry-save hook.
 *
 * @param mixed $entryId
 * @param mixed $entry
 * @param mixed $oldEntry
 * @param mixed $isUpdate
 * @return void
 */
function mediamanager_usage_on_entry_saved($entryId, $entry, $oldEntry = array(), $isUpdate = false) {
	$content = is_array($entry) && isset($entry ['content']) ? $entry ['content'] : '';
	mediamanager_usage_commit_entry((string)$entryId, $content);
}

/**
 * Successful entry-delete hook.
 *
 * @param mixed $entryId
 * @param mixed $oldEntry
 * @return void
 */
function mediamanager_usage_on_entry_deleted($entryId, $oldEntry = array()) {
	mediamanager_usage_remove_entry((string)$entryId);
}
?>
