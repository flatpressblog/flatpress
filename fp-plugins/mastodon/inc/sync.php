<?php
/**
 * Mastodon sync declarations. Loaded by plugin.mastodon.php.
 * Runtime constants and registrations remain in the main plugin file.
 */

/**
 * Convert a FlatPress-adjusted timestamp into a stable date key.
 * @param int $timestamp
 * @return string
 */
function plugin_mastodon_timestamp_date_key($timestamp) {
	$timestamp = (int) $timestamp;
	if ($timestamp <= 0) {
		return '';
	}
	return gmdate('Y-m-d', $timestamp);
}

/**
 * Determine the date key of a local FlatPress entry or comment.
 * @param array<string, mixed> $item
 * @param string $fallbackId
 * @return string
 */
function plugin_mastodon_local_item_date_key($item, $fallbackId = '') {
	$item = is_array($item) ? $item : array();
	if (isset($item ['date']) && is_numeric($item ['date'])) {
		return plugin_mastodon_timestamp_date_key((int) $item ['date']);
	}
	$fallbackId = trim((string) $fallbackId);
	if ($fallbackId !== '' && function_exists('date_from_id')) {
		$fallbackTimestamp = date_from_id($fallbackId);
		if (is_array($fallbackTimestamp) && isset($fallbackTimestamp ['time'])) {
			$fallbackTimestamp = $fallbackTimestamp ['time'];
		}
		if (is_numeric($fallbackTimestamp)) {
			return plugin_mastodon_timestamp_date_key((int) $fallbackTimestamp);
		}
	}
	return '';
}

/**
 * Determine the date key of a remote Mastodon status.
 * @param array<string, mixed> $remoteStatus
 * @return string
 */
function plugin_mastodon_remote_status_date_key($remoteStatus) {
	$remoteStatus = is_array($remoteStatus) ? $remoteStatus : array();
	foreach (array('created_at', 'published', 'date', 'timestamp', 'edited_at') as $field) {
		if (empty($remoteStatus [$field])) {
			continue;
		}
		$value = trim((string) $remoteStatus [$field]);
		if ($value === '') {
			continue;
		}
		$timestamp = plugin_mastodon_parse_iso_timestamp($value);
		if ($timestamp > 0) {
			return plugin_mastodon_timestamp_date_key(plugin_mastodon_timestamp_to_flatpress_time($timestamp));
		}
		if (preg_match('/^(\d{4}-\d{2}-\d{2})$/', $value, $matches)) {
			return $matches [1];
		}
	}
	return '';
}

/**
 * Normalize a date/datetime string to a sync-start date key.
 * @param string $value
 * @return string
 */
function plugin_mastodon_datetime_date_key($value) {
	$value = trim((string) $value);
	if ($value === '') {
		return '';
	}
	if (preg_match('/^(\d{4}-\d{2}-\d{2})$/', $value, $matches)) {
		return plugin_mastodon_normalize_sync_start_date($matches [1]);
	}
	$timestamp = plugin_mastodon_parse_iso_timestamp($value);
	if ($timestamp <= 0) {
		$timestamp = @strtotime($value);
		if ($timestamp === false) {
			return '';
		}
	}
	return plugin_mastodon_timestamp_date_key(plugin_mastodon_timestamp_to_flatpress_time((int) $timestamp));
}

/**
 * Determine whether a content date passes the configured sync start date.
 * @param array<string, string> $options
 * @param string $dateKey
 * @return bool
 */
function plugin_mastodon_date_matches_sync_start($options, $dateKey) {
	$startDate = plugin_mastodon_normalize_sync_start_date(isset($options ['sync_start_date']) ? $options ['sync_start_date'] : '');
	if ($startDate === '') {
		return true;
	}
	$dateKey = plugin_mastodon_normalize_sync_start_date($dateKey);
	if ($dateKey === '') {
		return false;
	}
	return strcmp($dateKey, $startDate) >= 0;
}

/**
 * Return the FlatPress-local date key that starts the automatic scheduled sync window.
 * @param array<string, string> $options
 * @param int|null $timestamp
 * @return string
 */
function plugin_mastodon_scheduled_window_start_date($options, $timestamp = null) {
	$days = (int) plugin_mastodon_normalize_scheduled_window_days(isset($options ['sync_scheduled_window_days']) ? $options ['sync_scheduled_window_days'] : '14');
	$timestamp = $timestamp === null ? time() : (int) $timestamp;
	if (isset($GLOBALS ['plugin_mastodon_test_now']) && is_numeric($GLOBALS ['plugin_mastodon_test_now'])) {
		$timestamp = (int) $GLOBALS ['plugin_mastodon_test_now'];
	}
	$localTimestamp = $timestamp + plugin_mastodon_fp_timeoffset_seconds();
	return gmdate('Y-m-d', $localTimestamp - ($days * 86400));
}

/**
 * Determine whether a date is inside the manual lower bound and, for scheduled runs, the automatic window.
 * @param array<string, string> $options
 * @param string $dateKey
 * @param bool $force
 * @return bool
 */
function plugin_mastodon_date_matches_content_window($options, $dateKey, $force) {
	if (!plugin_mastodon_date_matches_sync_start($options, $dateKey)) {
		return false;
	}
	if ($force) {
		return true;
	}
	$dateKey = plugin_mastodon_normalize_sync_start_date($dateKey);
	if ($dateKey === '') {
		return false;
	}
	$windowStart = plugin_mastodon_scheduled_window_start_date($options);
	return $windowStart === '' || strcmp($dateKey, $windowStart) >= 0;
}

/**
 * Determine whether a local FlatPress item should be synchronized.
 * @param array<string, string> $options
 * @param array<string, mixed> $item
 * @param string $fallbackId
 * @return bool
 */
function plugin_mastodon_local_item_matches_sync_start($options, $item, $fallbackId = '') {
	return plugin_mastodon_date_matches_sync_start($options, plugin_mastodon_local_item_date_key($item, $fallbackId));
}

/**
 * Determine whether a local FlatPress item is inside the active content synchronization window.
 * @param array<string, string> $options
 * @param array<string, mixed> $item
 * @param string $fallbackId
 * @param bool $force
 * @return bool
 */
function plugin_mastodon_local_item_matches_content_window($options, $item, $fallbackId, $force) {
	return plugin_mastodon_date_matches_content_window($options, plugin_mastodon_local_item_date_key($item, $fallbackId), $force);
}

/**
 * Determine whether a remote Mastodon status should be synchronized.
 * @param array<string, string> $options
 * @param array<string, mixed> $remoteStatus
 * @return bool
 */
function plugin_mastodon_remote_status_matches_sync_start($options, $remoteStatus) {
	return plugin_mastodon_date_matches_sync_start($options, plugin_mastodon_remote_status_date_key($remoteStatus));
}

/**
 * Determine whether a remote Mastodon status is inside the active content synchronization window.
 * @param array<string, string> $options
 * @param array<string, mixed> $remoteStatus
 * @param bool $force
 * @return bool
 */
function plugin_mastodon_remote_status_matches_content_window($options, $remoteStatus, $force) {
	return plugin_mastodon_date_matches_content_window($options, plugin_mastodon_remote_status_date_key($remoteStatus), $force);
}

/**
 * Determine whether a synchronized mapping should participate in the deletion follow-up for the current sync start date.
 * @param array<string, string> $options
 * @param array<string, mixed> $meta
 * @param string $localFallbackId
 * @return bool
 */
function plugin_mastodon_mapping_matches_sync_start($options, $meta, $localFallbackId = '') {
	$startDate = plugin_mastodon_normalize_sync_start_date(isset($options ['sync_start_date']) ? $options ['sync_start_date'] : '');
	if ($startDate === '') {
		return true;
	}
	$meta = is_array($meta) ? $meta : array();
	$source = isset($meta ['source']) ? strtolower(trim((string) $meta ['source'])) : '';
	$localDateKey = '';
	if (!empty($meta ['local_date_key'])) {
		$localDateKey = plugin_mastodon_normalize_sync_start_date((string) $meta ['local_date_key']);
	}
	if ($localDateKey === '') {
		$localDateKey = plugin_mastodon_local_item_date_key(array(), $localFallbackId);
	}
	$remoteDateKey = '';
	if (!empty($meta ['remote_date_key'])) {
		$remoteDateKey = plugin_mastodon_normalize_sync_start_date((string) $meta ['remote_date_key']);
	}
	if ($remoteDateKey === '') {
		$remoteDateKey = plugin_mastodon_datetime_date_key(isset($meta ['remote_updated_at']) ? (string) $meta ['remote_updated_at'] : '');
	}
	if ($source === 'remote' && $remoteDateKey !== '') {
		return plugin_mastodon_date_matches_sync_start($options, $remoteDateKey);
	}
	if ($source === 'local' && $localDateKey !== '') {
		return plugin_mastodon_date_matches_sync_start($options, $localDateKey);
	}
	if ($localDateKey !== '') {
		return plugin_mastodon_date_matches_sync_start($options, $localDateKey);
	}
	if ($remoteDateKey !== '') {
		return plugin_mastodon_date_matches_sync_start($options, $remoteDateKey);
	}
	return false;
}

/**
 * Return the most stable date key available for a synchronized mapping.
 *
 * The deletion sync uses this for the automatic scheduled window only when the
 * local object still exists and the run is not forced. Local deletions still
 * remain governed by the manual sync start date, so older local deletions do
 * not leave orphaned Mastodon statuses behind.
 *
 * @param array<string, mixed> $meta
 * @param string $localFallbackId
 * @return string
 */
function plugin_mastodon_mapping_effective_date_key($meta, $localFallbackId = '') {
	$meta = is_array($meta) ? $meta : array();
	$source = isset($meta ['source']) ? strtolower(trim((string) $meta ['source'])) : '';
	$localDateKey = '';
	if (!empty($meta ['local_date_key'])) {
		$localDateKey = plugin_mastodon_normalize_sync_start_date((string) $meta ['local_date_key']);
	}
	if ($localDateKey === '') {
		$localDateKey = plugin_mastodon_local_item_date_key(array(), $localFallbackId);
	}
	$remoteDateKey = '';
	if (!empty($meta ['remote_date_key'])) {
		$remoteDateKey = plugin_mastodon_normalize_sync_start_date((string) $meta ['remote_date_key']);
	}
	if ($remoteDateKey === '') {
		$remoteDateKey = plugin_mastodon_datetime_date_key(isset($meta ['remote_updated_at']) ? (string) $meta ['remote_updated_at'] : '');
	}
	if ($source === 'remote' && $remoteDateKey !== '') {
		return $remoteDateKey;
	}
	if ($source === 'local' && $localDateKey !== '') {
		return $localDateKey;
	}
	if ($localDateKey !== '') {
		return $localDateKey;
	}
	if ($remoteDateKey !== '') {
		return $remoteDateKey;
	}
	return '';
}

/**
 * Determine whether an existing local mapping should be remotely checked for deletion in this run.
 *
 * @param array<string, string> $options
 * @param array<string, mixed> $meta
 * @param string $localFallbackId
 * @param bool $force
 * @return bool
 */
function plugin_mastodon_mapping_matches_deletion_lookup_window($options, $meta, $localFallbackId, $force) {
	if (!plugin_mastodon_mapping_matches_sync_start($options, $meta, $localFallbackId)) {
		return false;
	}
	if ($force) {
		return true;
	}
	$dateKey = plugin_mastodon_mapping_effective_date_key($meta, $localFallbackId);
	if ($dateKey === '') {
		return true;
	}
	return plugin_mastodon_date_matches_content_window($options, $dateKey, false);
}

/**
 * Return mapping keys ordered after a saved deletion cursor.
 *
 * @param array<int, string> $keys
 * @param string $cursor
 * @return array<int, string>
 */
function plugin_mastodon_mapping_keys_after_cursor($keys, $cursor) {
	$keys = is_array($keys) ? array_values(array_map('strval', $keys)) : array();
	sort($keys, SORT_STRING);
	$cursor = (string) $cursor;
	if ($cursor === '' || empty($keys)) {
		return $keys;
	}
	$position = array_search($cursor, $keys, true);
	if ($position === false) {
		return $keys;
	}
	return array_slice($keys, ((int) $position) + 1);
}

/**
 * Parse an ISO date/time string into FlatPress date format.
 * @param string $value
 * @return string
 */
function plugin_mastodon_parse_iso_datetime($value) {
	$value = trim((string) $value);
	if ($value === '') {
		return '';
	}
	try {
		$date = new DateTime($value, new DateTimeZone('UTC'));
		$date->setTimezone(new DateTimeZone('UTC'));
		return $date->format('Y-m-d H:i:s');
	} catch (Exception $e) {
		return '';
	}
}

/**
 * Parse an ISO date/time value into a Unix timestamp.
 * @param string $value
 * @return int
 */
function plugin_mastodon_parse_iso_timestamp($value) {
	$value = trim((string) $value);
	if ($value === '') {
		return 0;
	}
	if (ctype_digit($value)) {
		return (int) $value;
	}
	try {
		$date = new DateTime($value, new DateTimeZone('UTC'));
		$date->setTimezone(new DateTimeZone('UTC'));
		return (int) $date->format('U');
	} catch (Exception $e) {
		$timestamp = @strtotime($value . ' UTC');
		return $timestamp === false ? 0 : (int) $timestamp;
	}
}

/**
 * Resolve the best timestamp for a remote Mastodon status.
 * @param array<string, mixed> $remoteStatus
 * @return int
 */
function plugin_mastodon_remote_status_timestamp($remoteStatus) {
	$remoteStatus = is_array($remoteStatus) ? $remoteStatus : array();
	foreach (array('created_at', 'published', 'date', 'timestamp', 'edited_at') as $field) {
		if (empty($remoteStatus [$field])) {
			continue;
		}
		$timestamp = plugin_mastodon_parse_iso_timestamp($remoteStatus [$field]);
		if ($timestamp > 0) {
			return plugin_mastodon_timestamp_to_flatpress_time($timestamp);
		}
	}
	return date_time();
}

/**
 * Return the normalized visibility of a remote Mastodon status.
 * @param array<string, mixed> $remoteStatus
 * @return string
 */
function plugin_mastodon_remote_status_visibility($remoteStatus) {
	$remoteStatus = is_array($remoteStatus) ? $remoteStatus : array();
	if (empty($remoteStatus ['visibility'])) {
		return '';
	}
	return strtolower(trim((string) $remoteStatus ['visibility']));
}

/**
 * Determine whether a remote Mastodon status may be imported.
 * @param array<string, mixed> $remoteStatus
 * @return bool
 */
function plugin_mastodon_remote_status_is_importable($remoteStatus) {
	$visibility = plugin_mastodon_remote_status_visibility($remoteStatus);
	if ($visibility === 'direct' || $visibility === 'private') {
		return false;
	}
	return true;
}

/**
 * Return the comment fields that may contain a parent reference.
 * @return array<int, string>
 */
function plugin_mastodon_comment_parent_fields() {
	return array('replyto', 'reply_to', 'parent', 'parent_id', 'in_reply_to', 'in_reply_to_id', 'replytoid', 'reply_to_id', 'inreplyto');
}

/**
 * Normalize a stored local comment parent identifier.
 * @param string $value
 * @return string
 */
function plugin_mastodon_normalize_comment_parent_id($value) {
	$value = trim((string) $value);
	if ($value === '') {
		return '';
	}
	if (preg_match('/^comment\d{6}-\d{6}$/', $value)) {
		return $value;
	}
	return '';
}

/**
 * Detect the local parent comment identifier from comment data.
 * @param string $entryId
 * @param array<string, mixed> $comment
 * @return string
 */
function plugin_mastodon_detect_local_comment_parent_id($entryId, $comment) {
	$comment = is_array($comment) ? $comment : array();
	foreach (plugin_mastodon_comment_parent_fields() as $field) {
		if (empty($comment [$field])) {
			continue;
		}
		$candidate = plugin_mastodon_normalize_comment_parent_id($comment [$field]);
		if ($candidate !== '' && comment_exists($entryId, $candidate)) {
			return $candidate;
		}
	}
	return '';
}

/**
 * Resolve the remote reply target for a local comment export.
 * @param array<string, mixed> $state
 * @param string $entryId
 * @param array<string, mixed> $comment
 * @param string $defaultRemoteId
 * @return array{remote_id:string, parent_comment_id:string}
 */
function plugin_mastodon_resolve_comment_reply_target($state, $entryId, $comment, $defaultRemoteId) {
	$parentCommentId = plugin_mastodon_detect_local_comment_parent_id($entryId, $comment);
	if ($parentCommentId !== '') {
		$parentMeta = plugin_mastodon_state_get_comment_meta($state, $entryId, $parentCommentId);
		if (!empty($parentMeta ['remote_id'])) {
			return array(
				'remote_id' => (string) $parentMeta ['remote_id'],
				'parent_comment_id' => $parentCommentId
			);
		}
	}
	return array('remote_id' => (string) $defaultRemoteId, 'parent_comment_id' => $parentCommentId);
}

/**
 * Determine whether a local parent comment should be exported before its child reply.
 * @param array<string, string> $options
 * @param array<string, mixed> $state
 * @param string $entryId
 * @param string $parentCommentId
 * @return bool
 */
function plugin_mastodon_local_comment_parent_export_pending($options, $state, $entryId, $parentCommentId) {
	$entryId = trim((string) $entryId);
	$parentCommentId = trim((string) $parentCommentId);
	if ($entryId === '' || $parentCommentId === '' || !comment_exists($entryId, $parentCommentId)) {
		return false;
	}

	$parentMeta = plugin_mastodon_state_get_comment_meta($state, $entryId, $parentCommentId);
	if (!empty($parentMeta ['remote_id'])) {
		return false;
	}
	if (!empty($parentMeta ['source']) && strtolower((string) $parentMeta ['source']) === 'remote') {
		return false;
	}

	$parentComment = comment_parse($entryId, $parentCommentId);
	if (!$parentComment || !is_array($parentComment)) {
		return false;
	}
	$parentComment ['id'] = $parentCommentId;
	if (!plugin_mastodon_local_comment_export_allowed($state, $entryId, $parentCommentId, $parentMeta, $parentComment)) {
		return false;
	}
	return plugin_mastodon_local_item_matches_sync_start($options, $parentComment, $parentCommentId);
}

/**
 * List local FlatPress comment identifiers by scanning the entry comment directory directly.
 * @param string $entryId
 * @return array<int, string>
 */
function plugin_mastodon_list_local_comment_ids($entryId) {
	$entryId = trim((string) $entryId);
	if ($entryId === '' || !defined('BDB_COMMENT')) {
		return array();
	}
	$dir = bdb_idtofile($entryId, BDB_COMMENT);
	if (!is_string($dir) || $dir === '' || !@is_dir($dir)) {
		return array();
	}

	$commentIds = array();
	$handle = @opendir($dir);
	if (!$handle) {
		return array();
	}
	while (($file = readdir($handle)) !== false) {
		if ($file [0] === '.') {
			continue;
		}
		if (!fnmatch('comment*' . EXT, $file)) {
			continue;
		}
		$commentId = basename($file, EXT);
		if (preg_match('/^comment\d{6}-\d{6}$/', $commentId)) {
			$commentIds [] = $commentId;
		}
	}
	@closedir($handle);
	sort($commentIds, SORT_STRING);
	return array_values(array_unique($commentIds));
}

/**
 * Build a change-detection hash for a FlatPress entry.
 * @param array<string, mixed> $entry
 * @return string
 */
function plugin_mastodon_entry_hash($entry) {
	$subject = isset($entry ['subject']) ? (string) $entry ['subject'] : '';
	$content = isset($entry ['content']) ? (string) $entry ['content'] : '';
	$mediaSignature = plugin_mastodon_entry_media_signature($content);
	return sha1($subject . "\n" . $content . "\n" . $mediaSignature);
}

/**
 * Build a change-detection hash for a FlatPress comment.
 * @param array<string, mixed> $comment
 * @return string
 */
function plugin_mastodon_comment_hash($comment) {
	$name = isset($comment ['name']) ? (string) $comment ['name'] : '';
	$content = isset($comment ['content']) ? (string) $comment ['content'] : '';
	$parent = '';
	foreach (array('replyto', 'reply_to', 'parent', 'parent_id', 'in_reply_to', 'in_reply_to_id', 'replytoid', 'reply_to_id', 'inreplyto') as $parentKey) {
		if (!empty($comment [$parentKey])) {
			$parent = trim((string) $comment [$parentKey]);
			break;
		}
	}
	return sha1($name . "\n" . $content . "\n" . $parent);
}

/**
 * Determine whether a FlatPress content-tree path segment is a two-digit date segment.
 * @param string $value
 * @return bool
 */
function plugin_mastodon_is_two_digit_path_segment($value) {
	return preg_match('/^\d{2}$/', (string) $value) === 1;
}

/**
 * Convert a FlatPress two-digit year segment into the full year used by PHP's legacy date parser.
 * @param string $year
 * @return int
 */
function plugin_mastodon_two_digit_year_to_full_year($year) {
	$year = (int) $year;
	return $year >= 70 ? 1900 + $year : 2000 + $year;
}

/**
 * Return the last date key that can occur inside a FlatPress YY/MM content directory.
 * @param string $year
 * @param string $month
 * @return string
 */
function plugin_mastodon_entry_month_end_date_key($year, $month) {
	if (!plugin_mastodon_is_two_digit_path_segment($year) || !plugin_mastodon_is_two_digit_path_segment($month)) {
		return '';
	}
	$monthNumber = (int) $month;
	if ($monthNumber < 1 || $monthNumber > 12) {
		return '';
	}
	$timestamp = gmmktime(12, 0, 0, $monthNumber + 1, 0, plugin_mastodon_two_digit_year_to_full_year($year));
	if ($timestamp === false) {
		return '';
	}
	return gmdate('Y-m-d', $timestamp);
}

/**
 * Determine whether a FlatPress YY/MM directory can contain scheduled-sync entry candidates.
 * @param array<string, string> $options
 * @param string $year
 * @param string $month
 * @return bool
 */
function plugin_mastodon_entry_month_may_match_scheduled_window($options, $year, $month) {
	$monthEnd = plugin_mastodon_entry_month_end_date_key($year, $month);
	if ($monthEnd === '') {
		return true;
	}
	if (!plugin_mastodon_date_matches_sync_start($options, $monthEnd)) {
		return false;
	}
	$windowStart = plugin_mastodon_scheduled_window_start_date($options);
	return $windowStart === '' || strcmp($monthEnd, $windowStart) >= 0;
}

/**
 * Collect direct entry files from one canonical FlatPress YY/MM content directory.
 * @param string $monthDir
 * @param string $year
 * @param string $month
 * @param array<int, string> $files
 * @return void
 */
function plugin_mastodon_collect_entry_files_from_month($monthDir, $year, $month, &$files) {
	if (!plugin_mastodon_is_two_digit_path_segment($year) || !plugin_mastodon_is_two_digit_path_segment($month)) {
		return;
	}
	$items = @scandir($monthDir);
	if (!is_array($items)) {
		return;
	}
	$pattern = '/^entry' . preg_quote($year . $month, '/') . '\d{2}-\d{6}\.txt$/';
	$monthDir = rtrim((string) $monthDir, '/\\');
	foreach ($items as $item) {
		if (!preg_match($pattern, $item)) {
			continue;
		}
		$path = $monthDir . DIRECTORY_SEPARATOR . $item;
		if (!is_file($path)) {
			continue;
		}
		$entryId = basename($item, EXT);
		$date = function_exists('date_from_id') ? date_from_id($entryId) : array();
		if (!is_array($date) || !isset($date ['y'], $date ['m'], $date ['time']) || $date ['time'] === false || (string) $date ['y'] !== $year || (string) $date ['m'] !== $month) {
			continue;
		}
		$files [] = $path;
	}
}

/**
 * Legacy fallback for non-canonical content trees.
 * @param string $dir
 * @param array<int, string> $files
 * @return void
 */
function plugin_mastodon_collect_entry_files_legacy($dir, &$files) {
	$items = @scandir($dir);
	if (!is_array($items)) {
		return;
	}
	foreach ($items as $item) {
		if ($item === '.' || $item === '..') {
			continue;
		}
		$path = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $item;
		if (is_dir($path)) {
			$directoryName = basename($path);
			if ($directoryName === 'drafts' || $directoryName === 'static' || $directoryName === 'seometa' || $directoryName === 'comments') {
				continue;
			}
			plugin_mastodon_collect_entry_files_legacy($path, $files);
		} elseif (preg_match('/^entry\d{6}-\d{6}\.txt$/', $item)) {
			$files [] = $path;
		}
	}
}

/**
 * Collect canonical entry files directly from FlatPress YY/MM content directories.
 * @param string $dir
 * @param array<int, string> $files
 * @return void
 */
function plugin_mastodon_collect_entry_files($dir, &$files) {
	$root = rtrim((string) $dir, '/\\');
	$items = @scandir($root);
	if (!is_array($items)) {
		return;
	}
	$foundFlatPressMonth = false;
	foreach ($items as $year) {
		if (!plugin_mastodon_is_two_digit_path_segment($year)) {
			continue;
		}
		$yearDir = $root . DIRECTORY_SEPARATOR . $year;
		if (!is_dir($yearDir)) {
			continue;
		}
		$months = @scandir($yearDir);
		if (!is_array($months)) {
			continue;
		}
		foreach ($months as $month) {
			if (!plugin_mastodon_is_two_digit_path_segment($month)) {
				continue;
			}
			$monthDir = $yearDir . DIRECTORY_SEPARATOR . $month;
			if (!is_dir($monthDir)) {
				continue;
			}
			$foundFlatPressMonth = true;
			plugin_mastodon_collect_entry_files_from_month($monthDir, $year, $month, $files);
		}
	}
	if (!$foundFlatPressMonth) {
		plugin_mastodon_collect_entry_files_legacy($root, $files);
	}
}

/**
 * Collect scheduled-sync entry candidates directly from relevant FlatPress YY/MM content directories.
 * @param string $dir
 * @param array<string, string> $options
 * @param array<int, string> $files
 * @return void
 */
function plugin_mastodon_collect_scheduled_entry_files($dir, $options, &$files) {
	$root = rtrim((string) $dir, '/\\');
	$items = @scandir($root);
	if (!is_array($items)) {
		return;
	}
	$foundFlatPressMonth = false;
	foreach ($items as $year) {
		if (!plugin_mastodon_is_two_digit_path_segment($year)) {
			continue;
		}
		$yearDir = $root . DIRECTORY_SEPARATOR . $year;
		if (!is_dir($yearDir)) {
			continue;
		}
		$months = @scandir($yearDir);
		if (!is_array($months)) {
			continue;
		}
		foreach ($months as $month) {
			if (!plugin_mastodon_is_two_digit_path_segment($month)) {
				continue;
			}
			$monthDir = $yearDir . DIRECTORY_SEPARATOR . $month;
			if (!is_dir($monthDir)) {
				continue;
			}
			$foundFlatPressMonth = true;
			if (!plugin_mastodon_entry_month_may_match_scheduled_window($options, $year, $month)) {
				continue;
			}
			plugin_mastodon_collect_entry_files_from_month($monthDir, $year, $month, $files);
		}
	}
	if (!$foundFlatPressMonth) {
		plugin_mastodon_collect_entry_files_legacy($root, $files);
	}
}

/**
 * Append existing dirty entry files by canonical FlatPress ID.
 * @param array<string, bool> $dirtyEntryLookup
 * @param array<int, string> $files
 * @return void
 */
function plugin_mastodon_add_dirty_entry_files($dirtyEntryLookup, &$files) {
	foreach (array_keys($dirtyEntryLookup) as $entryId) {
		$entryId = trim((string) $entryId);
		if ($entryId === '' || !preg_match('/^entry\d{6}-\d{6}$/', $entryId)) {
			continue;
		}
		$file = function_exists('entry_exists') ? entry_exists($entryId) : false;
		if (is_string($file) && $file !== '') {
			$files [] = $file;
		}
	}
}

/**
 * Collect local entry files for a local-to-remote synchronization pass; scheduled runs add all dirty parents without a hard cap.
 * @param array<string, string> $options
 * @param array<string, bool> $dirtyEntryLookup
 * @param bool $force
 * @return array<int, string>
 */
function plugin_mastodon_collect_entry_files_for_sync($options, $dirtyEntryLookup, $force) {
	$files = array();
	if ((bool) $force) {
		plugin_mastodon_collect_entry_files(CONTENT_DIR, $files);
	} else {
		plugin_mastodon_collect_scheduled_entry_files(CONTENT_DIR, $options, $files);
		plugin_mastodon_add_dirty_entry_files($dirtyEntryLookup, $files);
	}
	$files = array_values(array_unique($files));
	sort($files, SORT_STRING);
	return $files;
}

/**
 * Resolve the best timestamp for a local FlatPress item.
 * @param array<string, mixed> $item
 * @param string $fallbackId
 * @return int
 */
function plugin_mastodon_local_item_timestamp($item, $fallbackId = '') {
	$item = is_array($item) ? $item : array();
	if (isset($item ['date']) && is_numeric($item ['date'])) {
		return (int) $item ['date'];
	}
	$fallbackId = trim((string) $fallbackId);
	if ($fallbackId !== '' && function_exists('date_from_id')) {
		$fallbackTimestamp = date_from_id($fallbackId);
		if (is_array($fallbackTimestamp) && isset($fallbackTimestamp ['time'])) {
			$fallbackTimestamp = $fallbackTimestamp ['time'];
		}
		if (is_numeric($fallbackTimestamp)) {
			return (int) $fallbackTimestamp;
		}
	}
	return 0;
}

/**
 * Compare local FlatPress entries for Mastodon export order.
 *
 * Note: Mastodon sorts posts by the time they are created on Mastodon. Exporting older local
 * entries first ensures that newer local entries are posted later and therefore stay above older
 * ones in the Mastodon timeline after a batch synchronization.
 *
 * @param array{id:string, entry:array<string, mixed>, timestamp:int} $left
 * @param array{id:string, entry:array<string, mixed>, timestamp:int} $right
 * @return int
 */
function plugin_mastodon_compare_local_entries_for_export($left, $right) {
	$leftTimestamp = isset($left ['timestamp']) && is_numeric($left ['timestamp']) ? (int) $left ['timestamp'] : 0;
	$rightTimestamp = isset($right ['timestamp']) && is_numeric($right ['timestamp']) ? (int) $right ['timestamp'] : 0;
	if ($leftTimestamp < $rightTimestamp) {
		return -1;
	}
	if ($leftTimestamp > $rightTimestamp) {
		return 1;
	}
	$leftId = isset($left ['id']) ? (string) $left ['id'] : '';
	$rightId = isset($right ['id']) ? (string) $right ['id'] : '';
	return strcmp($leftId, $rightId);
}

/**
 * Increment a simulation-only local-entry parse counter when enabled.
 * @param string $entryId
 * @return void
 */
function plugin_mastodon_test_note_local_entry_parse($entryId) {
	if (!isset($GLOBALS ['plugin_mastodon_test_local_entry_parse_count'])) {
		return;
	}
	$GLOBALS ['plugin_mastodon_test_local_entry_parse_count'] = (int) $GLOBALS ['plugin_mastodon_test_local_entry_parse_count'] + 1;
}

/**
 * Return entry identifiers that are explicitly queued by dirty entry/comment hooks.
 * @param array<string, mixed> $state
 * @return array<string, bool>
 */
function plugin_mastodon_dirty_entry_id_lookup($state, $includeDirtyComments = true) {
	$lookup = array();
	if (isset($state ['dirty_entries']) && is_array($state ['dirty_entries'])) {
		foreach (array_keys($state ['dirty_entries']) as $entryId) {
			$entryId = trim((string) $entryId);
			if ($entryId !== '') {
				$lookup [$entryId] = true;
			}
		}
	}
	if ((bool) $includeDirtyComments && isset($state ['dirty_comments']) && is_array($state ['dirty_comments'])) {
		foreach ($state ['dirty_comments'] as $dirtyComment) {
			if (is_array($dirtyComment) && !empty($dirtyComment ['entry_id'])) {
				$entryId = trim((string) $dirtyComment ['entry_id']);
				if ($entryId !== '') {
					$lookup [$entryId] = true;
				}
			}
		}
	}
	return $lookup;
}

/**
 * Return entry ids whose comment shards may be needed by the current content-sync workset.
 * @param array<string, string> $options
 * @param array<string, mixed> $state
 * @param bool $force
 * @return array<int, string>
 */
function plugin_mastodon_content_sync_comment_entry_ids($options, $state, $force) {
	$syncCommentsAndReplies = plugin_mastodon_should_sync_comments_and_replies($options);
	$dirtyEntryLookup = plugin_mastodon_dirty_entry_id_lookup($state, $syncCommentsAndReplies);
	$files = plugin_mastodon_collect_entry_files_for_sync($options, $dirtyEntryLookup, $force);
	$ids = array();
	foreach ($files as $file) {
		$entryId = basename((string) $file, EXT);
		if ($entryId !== '' && plugin_mastodon_should_parse_local_entry_for_sync($options, $dirtyEntryLookup, $entryId, $force)) {
			$ids[$entryId] = true;
		}
	}
	if ($syncCommentsAndReplies) {
		foreach (plugin_mastodon_state_pending_recheck_entry_ids($state) as $entryId) {
			$ids[$entryId] = true;
		}
	}
	ksort($ids, SORT_STRING);
	return array_keys($ids);
}

/**
 * Determine whether a local entry file should be parsed during a scheduled local-to-remote pass.
 * @param array<string, string> $options
 * @param array<string, bool> $dirtyEntryLookup
 * @param string $entryId
 * @param bool $force
 * @return bool
 */
function plugin_mastodon_should_parse_local_entry_for_sync($options, $dirtyEntryLookup, $entryId, $force) {
	$entryId = trim((string) $entryId);
	if ($entryId === '') {
		return false;
	}
	if ((bool) $force) {
		return true;
	}
	if (isset($dirtyEntryLookup [$entryId])) {
		return true;
	}
	return plugin_mastodon_date_matches_content_window($options, plugin_mastodon_local_item_date_key(array(), $entryId), false);
}

/**
 * List local FlatPress entries for a local-to-remote synchronization pass.
 *
 * Manual full synchronizations keep the legacy repair behavior and scan every entry. Scheduled
 * synchronizations parse only active-window entries plus entries queued by post-success dirty hooks.
 *
 * @param array<string, string> $options
 * @param array<string, mixed> $state
 * @param bool $force
 * @return array<string, array<string, mixed>>
 */
function plugin_mastodon_list_local_entries_for_sync($options, $state, $force) {
	$dirtyEntryLookup = plugin_mastodon_dirty_entry_id_lookup($state, plugin_mastodon_should_sync_comments_and_replies($options));
	$files = plugin_mastodon_collect_entry_files_for_sync($options, $dirtyEntryLookup, $force);
	$entryRecords = array();
	foreach ($files as $file) {
		$id = basename($file, EXT);
		if (!plugin_mastodon_should_parse_local_entry_for_sync($options, $dirtyEntryLookup, $id, $force)) {
			continue;
		}
		plugin_mastodon_test_note_local_entry_parse($id);
		$entry = entry_parse($id);
		if (!$entry || !is_array($entry)) {
			continue;
		}
		if (isset($entry ['categories']) && is_array($entry ['categories']) && in_array('draft', $entry ['categories'], true)) {
			continue;
		}
		$entryRecords [] = array(
			'id' => $id,
			'entry' => $entry,
			'timestamp' => plugin_mastodon_local_item_timestamp($entry, $id)
		);
	}
	usort($entryRecords, 'plugin_mastodon_compare_local_entries_for_export');
	$entries = array();
	foreach ($entryRecords as $entryRecord) {
		$entryId = (string) $entryRecord ['id'];
		if ($entryId === '') {
			continue;
		}
		$entries [$entryId] = $entryRecord ['entry'];
	}
	return $entries;
}

/**
 * List local FlatPress entries ordered for export.
 * @return array<string, array<string, mixed>>
 */
function plugin_mastodon_list_local_entries() {
	$files = array();
	plugin_mastodon_collect_entry_files(CONTENT_DIR, $files);
	sort($files, SORT_STRING);
	$entryRecords = array();
	foreach ($files as $file) {
		$id = basename($file, EXT);
		plugin_mastodon_test_note_local_entry_parse($id);
		$entry = entry_parse($id);
		if (!$entry || !is_array($entry)) {
			continue;
		}
		if (isset($entry ['categories']) && is_array($entry ['categories']) && in_array('draft', $entry ['categories'], true)) {
			continue;
		}
		$entryRecords [] = array(
			'id' => $id,
			'entry' => $entry,
			'timestamp' => plugin_mastodon_local_item_timestamp($entry, $id)
		);
	}
	usort($entryRecords, 'plugin_mastodon_compare_local_entries_for_export');
	$entries = array();
	foreach ($entryRecords as $entryRecord) {
		$entryId = (string) $entryRecord ['id'];
		if ($entryId === '') {
			continue;
		}
		$entries [$entryId] = $entryRecord ['entry'];
	}
	return $entries;
}

/**
 * Import a remote Mastodon status into FlatPress as an entry.
 * @param array<string, string> $options
 * @param array<string, mixed> $state
 * @param array<string, mixed> $remoteStatus
 * @return bool|string|array<string, mixed>
 */
function plugin_mastodon_import_remote_entry(&$options, &$state, $remoteStatus) {
	$GLOBALS ['plugin_mastodon_media_import_error'] = '';
	if (!plugin_mastodon_should_import_remote_to_local($options)) {
		plugin_mastodon_log('Skipping remote entry import because Mastodon-to-FlatPress import is disabled');
		return false;
	}
	$remoteId = isset($remoteStatus ['id']) ? (string) $remoteStatus ['id'] : '';
	if ($remoteId === '' || !plugin_mastodon_remote_status_is_importable($remoteStatus)) {
		return false;
	}
	/**
	 * A retained entry mapping belongs to deletion reconciliation when its local file is gone.
	 * Check before media downloads and hash comparison, including legacy/manual deletions without a hook.
	 */
	if (isset($state ['entries_remote'] [$remoteId])) {
		$mappedLocalId = (string) $state ['entries_remote'] [$remoteId];
		if (!entry_exists($mappedLocalId)) {
			plugin_mastodon_state_queue_local_entry_deletion($state, $mappedLocalId);
			plugin_mastodon_log('Skipping remote status ' . $remoteId . ' because mapped local entry ' . $mappedLocalId . ' is missing; preserving its mapping for deletion synchronization');
			return false;
		}
	}
	$inReplyToRemoteId = isset($remoteStatus ['in_reply_to_id']) ? trim((string) $remoteStatus ['in_reply_to_id']) : '';
	if (!plugin_mastodon_should_import_synced_comments_as_entries($options) && isset($state ['comments_remote'] [$remoteId]) && is_array($state ['comments_remote'] [$remoteId])) {
		$commentRef = $state ['comments_remote'] [$remoteId];
		plugin_mastodon_log('Skipping remote status ' . $remoteId . ' as entry import because it already maps to local comment ' . (isset($commentRef ['entry_id']) ? (string) $commentRef ['entry_id'] : '') . '/' . (isset($commentRef ['comment_id']) ? (string) $commentRef ['comment_id'] : '') . ' and import_synced_comments_as_entries is disabled');
		return false;
	}
	if (!plugin_mastodon_should_import_synced_comments_as_entries($options) && $inReplyToRemoteId !== '') {
		plugin_mastodon_log('Skipping remote reply status ' . $remoteId . ' as entry import because import_synced_comments_as_entries is disabled; reply will be handled via thread context import when possible (parent remote id ' . $inReplyToRemoteId . ')');
		return false;
	}

	$importFingerprint = sha1((string) json_encode($remoteStatus));
	if (isset($state ['entries_remote'] [$remoteId])) {
		$existingId = (string) $state ['entries_remote'] [$remoteId];
		$existingMeta = plugin_mastodon_state_get_entry_meta($state, $existingId);
		$existingEntry = entry_parse($existingId);
		if (is_array($existingEntry) && !empty($existingMeta ['import_fingerprint']) && $existingMeta ['import_fingerprint'] === $importFingerprint && !empty($existingMeta ['hash']) && $existingMeta ['hash'] === plugin_mastodon_entry_hash($existingEntry)) {
			return $existingId;
		}
	}
	$content = plugin_mastodon_mastodon_html_to_flatpress(isset($remoteStatus ['content']) ? $remoteStatus ['content'] : '');
	$remoteTags = plugin_mastodon_remote_status_tags($remoteStatus);
	if (plugin_mastodon_tag_plugin_active()) {
		$content = plugin_mastodon_strip_trailing_mastodon_hashtag_footer($content, $remoteTags);
	}
	$mediaBbcode = plugin_mastodon_build_imported_media_bbcode($options, $remoteStatus);
	if (!empty($GLOBALS ['plugin_mastodon_media_import_error'])) {
		$state ['last_error'] = (string) $GLOBALS ['plugin_mastodon_media_import_error'];
		return false;
	}
	if ($mediaBbcode !== '') {
		$content = trim($content . $mediaBbcode);
	}
	$subject = plugin_mastodon_guess_subject($content);
	$author = '';
	if (!empty($remoteStatus ['account'] ['display_name'])) {
		$author = plugin_mastodon_html_entity_decode(strip_tags($remoteStatus ['account'] ['display_name']));
	}
	if ($author === '' && !empty($remoteStatus ['account'] ['acct'])) {
		$author = '@' . $remoteStatus ['account'] ['acct'];
	}
	if ($author === '') {
		$author = 'Mastodon';
	}

	$url = isset($remoteStatus ['url']) ? (string) $remoteStatus ['url'] : '';
	$footer = plugin_mastodon_imported_status_footer_bbcode($url);
	$entryContent = trim((string) $content);
	if (plugin_mastodon_tag_plugin_active()) {
		$tagBbcode = plugin_mastodon_build_flatpress_tag_bbcode($remoteTags);
		if ($tagBbcode !== '') {
			$entryContent = $entryContent === '' ? $tagBbcode : $entryContent . "\n\n" . $tagBbcode;
		}
	}
	if ($footer !== '') {
		$entryContent = $entryContent === '' ? $footer : $entryContent . "\n" . $footer;
	}
	$entry = array(
		'version' => system_ver(),
		'subject' => $subject,
		'content' => $entryContent,
		'author' => $author,
		'date' => plugin_mastodon_remote_status_timestamp($remoteStatus)
	);
	$hash = plugin_mastodon_entry_hash($entry);
	$remoteUpdatedAt = isset($remoteStatus ['edited_at']) && $remoteStatus ['edited_at'] ? plugin_mastodon_parse_iso_datetime($remoteStatus ['edited_at']) : plugin_mastodon_parse_iso_datetime(isset($remoteStatus ['created_at']) ? $remoteStatus ['created_at'] : '');

	if (isset($state ['entries_remote'] [$remoteId])) {
		$localId = $state ['entries_remote'] [$remoteId];
		$currentMeta = plugin_mastodon_state_get_entry_meta($state, $localId);
		if (!empty($currentMeta ['hash']) && $currentMeta ['hash'] === $hash) {
			return $localId;
		}
		$localEntryFile = entry_exists($localId);
		if ($localEntryFile && !plugin_mastodon_should_update_local_from_remote($options)) {
			plugin_mastodon_log('Skipping remote update for existing local entry ' . $localId . ' because update_local_from_remote is disabled');
			return $localId;
		}
		$existing = entry_parse($localId);
		if (is_array($existing) && !empty($existing ['date'])) {
			$entry ['date'] = $existing ['date'];
		}
		plugin_mastodon_local_write_guard_enter();
		try {
			$result = entry_save($entry, $localId);
		} finally {
			plugin_mastodon_local_write_guard_leave();
		}
		if (is_string($result) && $result !== '') {
			plugin_mastodon_state_set_entry_mapping($state, $result, $remoteId, 'remote', $hash, $url, $remoteUpdatedAt, plugin_mastodon_local_item_date_key($entry, $result), plugin_mastodon_remote_status_date_key($remoteStatus));
			$state ['entries'] [$result] ['import_fingerprint'] = $importFingerprint;
			$state ['content_stats'] ['updated_entries']++;
			return $result;
		}
		return false;
	}

	plugin_mastodon_local_write_guard_enter();
	try {
		$result = entry_save($entry, null);
	} finally {
		plugin_mastodon_local_write_guard_leave();
	}
	if (is_string($result) && $result !== '') {
		plugin_mastodon_state_set_entry_mapping($state, $result, $remoteId, 'remote', $hash, $url, $remoteUpdatedAt, plugin_mastodon_local_item_date_key($entry, $result), plugin_mastodon_remote_status_date_key($remoteStatus));
		$state ['entries'] [$result] ['import_fingerprint'] = $importFingerprint;
		$state ['content_stats'] ['imported_entries']++;
		return $result;
	}
	return false;
}

/**
 * Resolve the author and body that should be quoted for an imported Mastodon reply.
 * @param array<string, mixed> $state
 * @param string $entryId
 * @param string $parentRemoteId
 * @param array<string, array<string, mixed>> $contextStatuses
 * @return array{author:string,content:string}
 */
function plugin_mastodon_imported_reply_quote_payload($state, $entryId, $parentRemoteId, $contextStatuses = array()) {
	$entryId = (string) $entryId;
	$parentRemoteId = (string) $parentRemoteId;
	if ($entryId === '' || $parentRemoteId === '') {
		return array('author' => '', 'content' => '');
	}

	$payload = array('author' => '', 'content' => '');
	if (isset($state ['comments_remote'] [$parentRemoteId]) && is_array($state ['comments_remote'] [$parentRemoteId])) {
		$ref = $state ['comments_remote'] [$parentRemoteId];
		if (!empty($ref ['entry_id']) && (string) $ref ['entry_id'] === $entryId && !empty($ref ['comment_id'])) {
			$parentComment = comment_parse($entryId, (string) $ref ['comment_id']);
			if (is_array($parentComment)) {
				$payload ['author'] = isset($parentComment ['name']) ? trim((string) $parentComment ['name']) : '';
				$payload ['content'] = plugin_mastodon_strip_leading_quote_block(isset($parentComment ['content']) ? (string) $parentComment ['content'] : '');
			}
			$parentMeta = plugin_mastodon_state_get_comment_meta($state, $entryId, (string) $ref ['comment_id']);
			$parentSource = !empty($parentMeta ['source']) ? strtolower((string) $parentMeta ['source']) : '';
			if ($parentSource !== 'local' && isset($contextStatuses [$parentRemoteId]) && is_array($contextStatuses [$parentRemoteId])) {
				$payload ['author'] = plugin_mastodon_remote_status_author_label($contextStatuses [$parentRemoteId]);
				$payload ['content'] = trim((string) plugin_mastodon_mastodon_html_to_flatpress(isset($contextStatuses [$parentRemoteId] ['content']) ? $contextStatuses [$parentRemoteId] ['content'] : ''));
			}
		}
	}

	if (($payload ['author'] === '' && $payload ['content'] === '') && isset($contextStatuses [$parentRemoteId]) && is_array($contextStatuses [$parentRemoteId])) {
		$payload ['author'] = plugin_mastodon_remote_status_author_label($contextStatuses [$parentRemoteId]);
		$payload ['content'] = trim((string) plugin_mastodon_mastodon_html_to_flatpress(isset($contextStatuses [$parentRemoteId] ['content']) ? $contextStatuses [$parentRemoteId] ['content'] : ''));
	}

	return $payload;
}

/**
 * Build an optional BBCode quote block for an imported Mastodon reply.
 * @param array<string, string> $options
 * @param array<string, mixed> $state
 * @param string $entryId
 * @param string $parentCommentId
 * @param string $parentRemoteId
 * @param array<string, array<string, mixed>> $contextStatuses
 * @return string
 */
function plugin_mastodon_build_imported_reply_quote($options, $state, $entryId, $parentCommentId, $parentRemoteId, $contextStatuses = array()) {
	if (!plugin_mastodon_should_quote_imported_reply_parent($options)) {
		return '';
	}
	$entryId = (string) $entryId;
	$parentCommentId = trim((string) $parentCommentId);
	$parentRemoteId = trim((string) $parentRemoteId);
	if ($entryId === '' || $parentCommentId === '' || $parentRemoteId === '') {
		return '';
	}
	$payload = plugin_mastodon_imported_reply_quote_payload($state, $entryId, $parentRemoteId, $contextStatuses);
	$author = isset($payload ['author']) ? trim((string) $payload ['author']) : '';
	$content = isset($payload ['content']) ? trim((string) $payload ['content']) : '';
	if ($author === '' && $content === '') {
		return '';
	}
	$quoteLines = array();
	if ($author !== '') {
		$format = plugin_mastodon_lang_string('reply_quote_author_format', '%s wrote:');
		$quoteLines [] = strpos($format, '%s') !== false ? sprintf($format, $author) : rtrim((string) $format) . ' ' . $author;
	}
	if ($content !== '') {
		$quoteLines [] = $content;
	}
	$quoteBody = trim(implode("\n", $quoteLines));
	if ($quoteBody === '') {
		return '';
	}
	return "[quote]\n" . $quoteBody . "\n[/quote]";
}

/**
 * Import a remote Mastodon reply into FlatPress as a comment.
 * @param array<string, string> $options
 * @param array<string, mixed> $state
 * @param string $entryId
 * @param array<string, mixed> $remoteComment
 * @param string $parentCommentId
 * @param string $inReplyToRemoteId
 * @param array<string, array<string, mixed>> $contextStatuses
 * @return bool|string
 */
function plugin_mastodon_import_remote_comment(&$options, &$state, $entryId, $remoteComment, $parentCommentId = '', $inReplyToRemoteId = '', $contextStatuses = array()) {
	if (!plugin_mastodon_should_sync_comments_and_replies($options)) {
		return false;
	}
	if (!plugin_mastodon_should_import_remote_to_local($options)) {
		plugin_mastodon_log('Skipping remote reply import because Mastodon-to-FlatPress import is disabled');
		return false;
	}
	$remoteId = isset($remoteComment ['id']) ? (string) $remoteComment ['id'] : '';
	if ($remoteId === '' || $entryId === '' || !plugin_mastodon_remote_status_is_importable($remoteComment)) {
		return false;
	}
	plugin_mastodon_state_load_comment_shard_into($state, $entryId);
	if (isset($state ['comments_remote'] [$remoteId]) && is_array($state ['comments_remote'] [$remoteId]) && !empty($state ['comments_remote'] [$remoteId] ['entry_id'])) {
		plugin_mastodon_state_load_comment_shard_into($state, (string) $state ['comments_remote'] [$remoteId] ['entry_id']);
	}
	if (plugin_mastodon_state_has_comment_tombstone($state, $remoteId)) {
		plugin_mastodon_log('Skipping remote reply ' . $remoteId . ' because it was deleted locally earlier and is protected by a tombstone');
		return false;
	}
	if (plugin_mastodon_protect_missing_local_exported_comment_by_remote_id($state, $remoteId)) {
		plugin_mastodon_log('Skipping remote reply ' . $remoteId . ' because its exported FlatPress comment is missing and has been protected for deletion synchronization');
		return false;
	}
	if ($inReplyToRemoteId !== '' && plugin_mastodon_state_has_comment_tombstone($state, $inReplyToRemoteId)) {
		plugin_mastodon_log('Skipping remote reply ' . $remoteId . ' because the parent reply ' . $inReplyToRemoteId . ' was deleted locally and must not be re-imported');
		return false;
	}
	$content = plugin_mastodon_mastodon_html_to_flatpress(isset($remoteComment ['content']) ? $remoteComment ['content'] : '');
	$quoteBlock = plugin_mastodon_build_imported_reply_quote($options, $state, $entryId, $parentCommentId, $inReplyToRemoteId, $contextStatuses);
	if ($quoteBlock !== '') {
		$content = trim($quoteBlock . ($content !== '' ? "\n\n" . ltrim((string) $content) : ''));
	}
	$name = '';
	if (!empty($remoteComment ['account'] ['display_name'])) {
		$name = plugin_mastodon_html_entity_decode(strip_tags($remoteComment ['account'] ['display_name']));
	}
	if ($name === '' && !empty($remoteComment ['account'] ['acct'])) {
		$name = '@' . $remoteComment ['account'] ['acct'];
	}
	if ($name === '') {
		$name = 'Mastodon';
	}
	$comment = array(
		'version' => system_ver(),
		'loggedin' => '0',
		'name' => $name,
		'url' => !empty($remoteComment ['account'] ['url']) ? (string) $remoteComment ['account'] ['url'] : '',
		'content' => $content,
		'date' => plugin_mastodon_remote_status_timestamp($remoteComment)
	);
	if ($parentCommentId !== '') {
		$comment ['replyto'] = $parentCommentId;
	}
	$hash = plugin_mastodon_comment_hash($comment);
	$remoteUpdatedAt = isset($remoteComment ['edited_at']) && $remoteComment ['edited_at'] ? plugin_mastodon_parse_iso_datetime($remoteComment ['edited_at']) : plugin_mastodon_parse_iso_datetime(isset($remoteComment ['created_at']) ? $remoteComment ['created_at'] : '');

	if (isset($state ['comments_remote'] [$remoteId]) && is_array($state ['comments_remote'] [$remoteId])) {
		$ref = $state ['comments_remote'] [$remoteId];
		$refEntryId = isset($ref ['entry_id']) ? trim((string) $ref ['entry_id']) : '';
		$refCommentId = isset($ref ['comment_id']) ? trim((string) $ref ['comment_id']) : '';
		if ($refEntryId === '' || $refCommentId === '') {
			plugin_mastodon_log('Skipping remote reply ' . $remoteId . ' because its reverse comment mapping is incomplete');
			return false;
		}
		$currentMeta = plugin_mastodon_state_get_comment_meta($state, $refEntryId, $refCommentId);
		$file = comment_exists($refEntryId, $refCommentId);
		if (!$file) {
			$mappingSource = isset($currentMeta ['source']) ? strtolower(trim((string) $currentMeta ['source'])) : '';
			if ($mappingSource === 'remote' && entry_exists($refEntryId)) {
				plugin_mastodon_state_set_comment_tombstone($state, $remoteId, $refEntryId, $refCommentId, 'local_deleted_imported_remote_ignored');
				plugin_mastodon_state_remove_pending_comment_remote_recheck($state, $refEntryId, $refCommentId);
				plugin_mastodon_state_remove_comment_mapping($state, $refEntryId, $refCommentId);
				plugin_mastodon_log('Protected missing imported remote reply ' . $remoteId . ' as a local ignore decision at the import boundary');
			} else {
				plugin_mastodon_log('Skipping remote reply ' . $remoteId . ' because it is already reserved by missing local comment mapping ' . $refEntryId . '/' . $refCommentId);
			}
			return false;
		}
		if (!empty($currentMeta ['hash']) && $currentMeta ['hash'] === $hash && (empty($currentMeta ['parent_comment_id']) || $currentMeta ['parent_comment_id'] === (string) $parentCommentId) && (empty($currentMeta ['in_reply_to_remote_id']) || $currentMeta ['in_reply_to_remote_id'] === (string) $inReplyToRemoteId)) {
			return $refCommentId;
		}
		if (!plugin_mastodon_should_update_local_from_remote($options)) {
			plugin_mastodon_log('Skipping remote update for existing local comment ' . $refEntryId . '/' . $refCommentId . ' because update_local_from_remote is disabled');
			return $refCommentId;
		}
		$existing = comment_parse($refEntryId, $refCommentId);
		if (is_array($existing) && !empty($existing ['date'])) {
			$comment ['date'] = $existing ['date'];
		}
		$stored = array_change_key_case($comment, CASE_UPPER);
		plugin_mastodon_io_write_file($file, utils_kimplode($stored));
		$mappingSource = !empty($currentMeta ['source']) ? (string) $currentMeta ['source'] : 'remote';
		if (!plugin_mastodon_state_set_comment_mapping($state, $refEntryId, $refCommentId, $remoteId, $mappingSource, $hash, isset($remoteComment ['url']) ? (string) $remoteComment ['url'] : '', $remoteUpdatedAt, $parentCommentId, $inReplyToRemoteId, plugin_mastodon_local_item_date_key($comment, $refCommentId), plugin_mastodon_remote_status_date_key($remoteComment))) {
			plugin_mastodon_log('Skipping remote reply update for ' . $remoteId . ' because the comment mapping could not be stored without a conflict');
			return false;
		}
		$state ['content_stats'] ['updated_local_comments']++;
		return $refCommentId;
	}

	plugin_mastodon_local_write_guard_enter();
	try {
		$result = comment_save($entryId, $comment);
	} finally {
		plugin_mastodon_local_write_guard_leave();
	}
	if (is_string($result) && $result !== '') {
		if (!plugin_mastodon_state_set_comment_mapping($state, $entryId, $result, $remoteId, 'remote', $hash, isset($remoteComment ['url']) ? (string) $remoteComment ['url'] : '', $remoteUpdatedAt, $parentCommentId, $inReplyToRemoteId, plugin_mastodon_local_item_date_key($comment, $result), plugin_mastodon_remote_status_date_key($remoteComment))) {
			plugin_mastodon_local_write_guard_enter();
			try {
				comment_delete($entryId, $result);
			} finally {
				plugin_mastodon_local_write_guard_leave();
			}
			plugin_mastodon_log('Removed newly imported local comment ' . $entryId . '/' . $result . ' because remote status ' . $remoteId . ' is already mapped elsewhere');
			return false;
		}
		$state ['content_stats'] ['imported_comments']++;
		return $result;
	}
	return false;
}

/**
 * Import one remote reply directly from a mention notification when its parent is already mapped locally.
 * @param array<string, string> $options
 * @param array<string, mixed> $state
 * @param array<string, mixed> $status
 * @param string $refreshedRemoteEntryId Remote entry id whose thread was refreshed/imported.
 * @return bool
 */
function plugin_mastodon_import_remote_notification_reply(&$options, &$state, $status, &$refreshedRemoteEntryId = '') {
	if (!plugin_mastodon_should_import_remote_to_local($options) || !plugin_mastodon_should_sync_comments_and_replies($options)) {
		return false;
	}
	$statusId = isset($status ['id']) ? (string) $status ['id'] : '';
	$parentRemoteId = isset($status ['in_reply_to_id']) ? (string) $status ['in_reply_to_id'] : '';
	$refreshedRemoteEntryId = '';
	if ($statusId === '' || $parentRemoteId === '' || isset($state ['comments_remote'] [$statusId])) {
		return false;
	}
	if (!plugin_mastodon_remote_status_is_importable($status) || !plugin_mastodon_remote_status_matches_sync_start($options, $status)) {
		return false;
	}
	if (isset($state ['entries_remote'] [$parentRemoteId])) {
		$entryId = (string) $state ['entries_remote'] [$parentRemoteId];
		if ($entryId !== '' && entry_exists($entryId)) {
			$refreshedRemoteEntryId = $parentRemoteId;
			return plugin_mastodon_import_remote_comment($options, $state, $entryId, $status, '', $parentRemoteId, array($statusId => $status)) !== false;
		}
	}
	if (isset($state ['comments_remote'] [$parentRemoteId]) && is_array($state ['comments_remote'] [$parentRemoteId])) {
		$parentRef = $state ['comments_remote'] [$parentRemoteId];
		$entryId = isset($parentRef ['entry_id']) ? (string) $parentRef ['entry_id'] : '';
		$commentId = isset($parentRef ['comment_id']) ? (string) $parentRef ['comment_id'] : '';
		if ($entryId !== '' && $commentId !== '' && entry_exists($entryId)) {
			$refreshedRemoteEntryId = isset($state ['entries'] [$entryId] ['remote_id']) ? (string) $state ['entries'] [$entryId] ['remote_id'] : '';
			return plugin_mastodon_import_remote_comment($options, $state, $entryId, $status, $commentId, $parentRemoteId, array($statusId => $status)) !== false;
		}
	}
	return false;
}

/**
 * Import a notification reply through its context when the direct parent is not mapped yet.
 * @param array<string, string> $options
 * @param array<string, mixed> $state
 * @param array<string, mixed> $status
 * @param array<string, mixed> $context
 * @param string $refreshedRemoteEntryId Remote entry id whose context was imported.
 * @return bool
 */
function plugin_mastodon_import_remote_notification_context(&$options, &$state, $status, $context, &$refreshedRemoteEntryId = '') {
	if (!plugin_mastodon_should_import_remote_to_local($options) || !plugin_mastodon_should_sync_comments_and_replies($options)) {
		return false;
	}
	$statusId = isset($status ['id']) ? (string) $status ['id'] : '';
	$refreshedRemoteEntryId = '';
	if ($statusId === '' || !plugin_mastodon_remote_status_is_importable($status) || !plugin_mastodon_remote_status_matches_sync_start($options, $status)) {
		return false;
	}
	$ancestors = isset($context ['ancestors']) && is_array($context ['ancestors']) ? array_values($context ['ancestors']) : array();
	$descendants = isset($context ['descendants']) && is_array($context ['descendants']) ? array_values($context ['descendants']) : array();
	$rootRemoteId = '';
	$entryId = '';
	$rootIndex = -1;
	foreach ($ancestors as $index => $ancestor) {
		if (!is_array($ancestor) || empty($ancestor ['id'])) {
			continue;
		}
		$ancestorId = (string) $ancestor ['id'];
		if (isset($state ['entries_remote'] [$ancestorId])) {
			$mappedEntryId = (string) $state ['entries_remote'] [$ancestorId];
			if ($mappedEntryId !== '' && entry_exists($mappedEntryId)) {
				$rootRemoteId = $ancestorId;
				$entryId = $mappedEntryId;
				$rootIndex = (int) $index;
				break;
			}
		}
	}
	if ($rootRemoteId === '' || $entryId === '') {
		return false;
	}
	$combined = array();
	for ($index = $rootIndex + 1; $index < count($ancestors); $index++) {
		if (isset($ancestors [$index]) && is_array($ancestors [$index])) {
			$combined [] = $ancestors [$index];
		}
	}
	$combined [] = $status;
	foreach ($descendants as $descendant) {
		if (is_array($descendant)) {
			$combined [] = $descendant;
		}
	}
	$refreshedRemoteEntryId = $rootRemoteId;
	plugin_mastodon_import_remote_context_descendants($options, $state, $entryId, $rootRemoteId, array('descendants' => $combined));
	return isset($state ['comments_remote'] [$statusId]);
}

/**
 * Process mention notifications as prioritized hints for replies on older synchronized threads.
 * @param array<string, string> $options
 * @param array<string, mixed> $state
 * @param int $contextBudget
 * @return array<string, mixed> Contains used_contexts and refreshed_context_ids.
 */
function plugin_mastodon_process_remote_reply_notifications(&$options, &$state, $contextBudget) {
	$resultSummary = array('used_contexts' => 0, 'refreshed_context_ids' => array());
	if (!plugin_mastodon_should_sync_comments_and_replies($options)) {
		return $resultSummary;
	}
	if (!plugin_mastodon_should_import_remote_to_local($options)) {
		return $resultSummary;
	}
	if (!plugin_mastodon_should_poll_reply_notifications($options)) {
		return $resultSummary;
	}
	$contextBudget = max(0, (int) $contextBudget);
	$sinceId = isset($state ['last_remote_notification_id']) ? (string) $state ['last_remote_notification_id'] : '';
	$result = plugin_mastodon_fetch_reply_notifications($options, $sinceId);
	if (empty($result ['ok'])) {
		plugin_mastodon_log('Skipping reply-notification hints because notifications could not be fetched');
		return $resultSummary;
	}
	$items = isset($result ['items']) && is_array($result ['items']) ? $result ['items'] : array();
	$maxProcessedNotificationId = $sinceId;
	$usedContexts = 0;
	$refreshedContextIds = array();
	foreach ($items as $notification) {
		if (!is_array($notification)) {
			continue;
		}
		$notificationId = isset($notification ['id']) ? (string) $notification ['id'] : '';
		$type = isset($notification ['type']) ? (string) $notification ['type'] : '';
		$status = isset($notification ['status']) && is_array($notification ['status']) ? $notification ['status'] : array();
		if ($notificationId === '') {
			continue;
		}
		$processed = false;
		if ($type !== 'mention' || empty($status ['id']) || empty($status ['in_reply_to_id'])) {
			$processed = true;
		} elseif (isset($state ['comments_remote'] [(string) $status ['id']])) {
			$processed = true;
		} elseif (!plugin_mastodon_remote_status_is_importable($status) || !plugin_mastodon_remote_status_matches_sync_start($options, $status)) {
			$processed = true;
		} else {
			$refreshedRemoteEntryId = '';
			if (plugin_mastodon_import_remote_notification_reply($options, $state, $status, $refreshedRemoteEntryId)) {
				if ($refreshedRemoteEntryId !== '') {
					$refreshedContextIds [$refreshedRemoteEntryId] = true;
				}
				$processed = true;
			} elseif ($usedContexts < $contextBudget) {
				$context = plugin_mastodon_fetch_status_context($options, (string) $status ['id']);
				$usedContexts++;
				if (plugin_mastodon_import_remote_notification_context($options, $state, $status, $context, $refreshedRemoteEntryId) && $refreshedRemoteEntryId !== '') {
					$refreshedContextIds [$refreshedRemoteEntryId] = true;
				}
				$processed = true;
			}
		}
		if ($processed && ($maxProcessedNotificationId === '' || strcmp($notificationId, $maxProcessedNotificationId) > 0)) {
			$maxProcessedNotificationId = $notificationId;
		}
	}
	if ($maxProcessedNotificationId !== $sinceId) {
		$state ['last_remote_notification_id'] = $maxProcessedNotificationId;
	}
	$resultSummary ['used_contexts'] = $usedContexts;
	$resultSummary ['refreshed_context_ids'] = array_keys($refreshedContextIds);
	return $resultSummary;
}

/**
 * Import remote Mastodon replies from a fetched thread context.
 * @param array<string, string> $options
 * @param array<string, mixed> $state
 * @param string $entryId
 * @param string $statusId
 * @param array<string, mixed> $context
 * @return void
 */
function plugin_mastodon_import_remote_context_descendants(&$options, &$state, $entryId, $statusId, $context) {
	if (!plugin_mastodon_should_sync_comments_and_replies($options)) {
		plugin_mastodon_log('Skipping remote thread context import because comment/reply synchronization is disabled');
		return;
	}
	if (!plugin_mastodon_should_import_remote_to_local($options)) {
		plugin_mastodon_log('Skipping remote thread context import because Mastodon-to-FlatPress import is disabled');
		return;
	}
	$entryId = (string) $entryId;
	$statusId = (string) $statusId;
	if ($entryId === '' || $statusId === '' || empty($context ['descendants']) || !is_array($context ['descendants'])) {
		return;
	}
	plugin_mastodon_state_load_comment_shard_into($state, $entryId);

	$pending = array_values($context ['descendants']);
	$contextStatuses = array();
	foreach ($context ['descendants'] as $contextStatus) {
		if (is_array($contextStatus) && !empty($contextStatus ['id'])) {
			$contextStatuses [(string) $contextStatus ['id']] = $contextStatus;
		}
	}
	$importedRemoteIds = array();
	$blockedRemoteIds = array();
	$guard = 0;
	while (!empty($pending) && $guard < 50) {
		$guard++;
		$remaining = array();
		$progress = false;
		foreach ($pending as $descendant) {
			plugin_mastodon_extend_time_limit(120);
			if (!is_array($descendant) || empty($descendant ['id'])) {
				continue;
			}
			$descendantId = (string) $descendant ['id'];
			if (plugin_mastodon_state_has_comment_tombstone($state, $descendantId)) {
				$blockedRemoteIds [$descendantId] = true;
				$progress = true;
				plugin_mastodon_log('Skipping remote reply ' . $descendantId . ' because it was deleted locally earlier and is protected by a tombstone');
				continue;
			}
			if (!plugin_mastodon_remote_status_is_importable($descendant)) {
				$blockedRemoteIds [$descendantId] = true;
				$progress = true;
				plugin_mastodon_log('Skipping non-public remote reply ' . $descendantId . ' with visibility ' . plugin_mastodon_remote_status_visibility($descendant));
				continue;
			}
			if (!plugin_mastodon_remote_status_matches_sync_start($options, $descendant)) {
				$blockedRemoteIds [$descendantId] = true;
				$progress = true;
				plugin_mastodon_log_skip('remote_reply_before_sync_start', 'remote reply', 'remote replies', $descendantId, 'because they are older than the configured sync start date');
				continue;
			}
			$parentRemoteId = isset($descendant ['in_reply_to_id']) ? (string) $descendant ['in_reply_to_id'] : '';
			if ($parentRemoteId !== '' && plugin_mastodon_state_has_comment_tombstone($state, $parentRemoteId)) {
				$blockedRemoteIds [$descendantId] = true;
				$progress = true;
				plugin_mastodon_log('Skipping remote reply ' . $descendantId . ' because the parent reply ' . $parentRemoteId . ' is protected by a tombstone');
				continue;
			}
			if ($parentRemoteId !== '' && isset($blockedRemoteIds [$parentRemoteId])) {
				$blockedRemoteIds [$descendantId] = true;
				$progress = true;
				plugin_mastodon_log('Skipping remote reply ' . $descendantId . ' because parent reply ' . $parentRemoteId . ' is not importable');
				continue;
			}
			$parentCommentId = '';
			$canImportNow = ($parentRemoteId === '' || $parentRemoteId === $statusId);
			if (!$canImportNow && isset($state ['comments_remote'] [$parentRemoteId]) && is_array($state ['comments_remote'] [$parentRemoteId])) {
				$parentRef = $state ['comments_remote'] [$parentRemoteId];
				if (!empty($parentRef ['entry_id']) && (string) $parentRef ['entry_id'] === $entryId && !empty($parentRef ['comment_id'])) {
					$parentCommentId = (string) $parentRef ['comment_id'];
					$canImportNow = true;
				}
			}
			if (!$canImportNow && isset($importedRemoteIds [$parentRemoteId])) {
				$parentCommentId = (string) $importedRemoteIds [$parentRemoteId];
				$canImportNow = true;
			}
			if (!$canImportNow) {
				$remaining [] = $descendant;
				continue;
			}
			$commentId = plugin_mastodon_import_remote_comment($options, $state, $entryId, $descendant, $parentCommentId, $parentRemoteId, $contextStatuses);
			if ($commentId) {
				$importedRemoteIds [$descendantId] = $commentId;
				$progress = true;
			} else {
				$remaining [] = $descendant;
			}
		}
		if (!$progress) {
			foreach ($remaining as $descendant) {
				if (empty($descendant ['id'])) {
					continue;
				}
				$parentRemoteId = isset($descendant ['in_reply_to_id']) ? (string) $descendant ['in_reply_to_id'] : '';
				plugin_mastodon_import_remote_comment($options, $state, $entryId, $descendant, '', $parentRemoteId, $contextStatuses);
			}
			$remaining = array();
		}
		$pending = $remaining;
	}
}

/**
 * Return the maximum number of known synchronized threads checked for replies per content sync run.
 * @return int
 */
function plugin_mastodon_old_thread_context_rotation_limit($options = array()) {
	if (isset($GLOBALS ['plugin_mastodon_test_old_thread_context_rotation_limit']) && is_numeric($GLOBALS ['plugin_mastodon_test_old_thread_context_rotation_limit'])) {
		return max(0, (int) $GLOBALS ['plugin_mastodon_test_old_thread_context_rotation_limit']);
	}
	$options = is_array($options) ? $options : array();
	$limit = isset($options ['old_thread_context_limit']) ? plugin_mastodon_normalize_old_thread_context_limit($options ['old_thread_context_limit']) : (string) PLUGIN_MASTODON_OLD_THREAD_CONTEXT_ROTATION_LIMIT;
	return (int) $limit;
}

/**
 * Collect known synchronized entry threads that should have their Mastodon reply context refreshed.
 * @param array<string, mixed> $state
 * @param array<int, string> $skipRemoteIds
 * @param array<string, string> $options
 * @param bool $force
 * @return array<string, string>
 */
function plugin_mastodon_collect_known_entry_context_targets(&$state, $skipRemoteIds = array(), $options = array(), $force = false, $limit = null) {
	$targets = array();
	if (!plugin_mastodon_should_import_remote_to_local($options)) {
		return $targets;
	}
	if (!plugin_mastodon_should_check_old_thread_replies($options)) {
		return $targets;
	}
	$limit = $limit === null ? plugin_mastodon_old_thread_context_rotation_limit($options) : (int) $limit;
	if ($limit <= 0) {
		return $targets;
	}
	$skipLookup = array();
	if (is_array($skipRemoteIds)) {
		foreach ($skipRemoteIds as $skipRemoteId) {
			$skipRemoteId = (string) $skipRemoteId;
			if ($skipRemoteId !== '') {
				$skipLookup [$skipRemoteId] = true;
			}
		}
	}
	if (empty($state ['entries']) || !is_array($state ['entries'])) {
		return $targets;
	}
	$candidates = array();
	foreach ($state ['entries'] as $localEntryId => $meta) {
		$localEntryId = (string) $localEntryId;
		if ($localEntryId === '' || !is_array($meta) || empty($meta ['remote_id'])) {
			continue;
		}
		$remoteId = (string) $meta ['remote_id'];
		if (isset($skipLookup [$remoteId]) || !entry_exists($localEntryId)) {
			continue;
		}
		if (!plugin_mastodon_mapping_matches_sync_start($options, $meta, $localEntryId)) {
			plugin_mastodon_log('Skipping context refresh for synchronized entry ' . $localEntryId . ' because its mapping is older than the configured sync start date');
			continue;
		}
		$candidates [$remoteId] = $localEntryId;
	}
	if (empty($candidates)) {
		return $targets;
	}
	ksort($candidates, SORT_STRING);
	$remoteIds = array_keys($candidates);
	$cursor = isset($state ['old_thread_context_cursor']) ? (string) $state ['old_thread_context_cursor'] : '';
	$start = 0;
	if ($cursor !== '') {
		foreach ($remoteIds as $index => $remoteId) {
			if (strcmp((string) $remoteId, $cursor) > 0) {
				$start = (int) $index;
				break;
			}
			if ($index === count($remoteIds) - 1) {
				$start = 0;
			}
		}
	}
	$count = count($remoteIds);
	$checked = 0;
	for ($offset = 0; $offset < $count && $checked < $limit; $offset++) {
		$remoteId = (string) $remoteIds [($start + $offset) % $count];
		$targets [$remoteId] = $candidates [$remoteId];
		$state ['old_thread_context_cursor'] = $remoteId;
		$checked++;
	}
	return $targets;
}

/**
 * Synchronize remote Mastodon content into FlatPress.
 * @param array<string, string> $options
 * @param array<string, mixed> $state
 * @param bool $force
 * @return bool
 */
function plugin_mastodon_sync_remote_to_local(&$options, &$state, $force = true) {
	plugin_mastodon_extend_time_limit(180);
	$force = (bool) $force;
	if (!plugin_mastodon_should_import_remote_to_local($options)) {
		plugin_mastodon_log('Skipping Mastodon-to-FlatPress content synchronization because remote import is disabled');
		return true;
	}
	$verify = plugin_mastodon_verify_credentials($options);
	if (!$verify ['ok'] || empty($verify ['json'] ['id'])) {
		$rateLimitError = plugin_mastodon_rate_limit_state_error();
		$state ['last_error'] = $rateLimitError !== '' ? $rateLimitError : 'verify_credentials_failed';
		return false;
	}

	$accountId = (string) $verify ['json'] ['id'];
	$sinceId = isset($state ['last_remote_status_id']) ? (string) $state ['last_remote_status_id'] : '';
	$statuses = plugin_mastodon_fetch_account_statuses($options, $accountId, $sinceId);
	$syncCommentsAndReplies = plugin_mastodon_should_sync_comments_and_replies($options);
	$refreshedContextIds = array();

	$maxRemoteId = $sinceId;
	foreach ($statuses as $status) {
		plugin_mastodon_extend_time_limit(120);
		if (!is_array($status) || empty($status ['id'])) {
			continue;
		}
		$statusId = (string) $status ['id'];
		if ($maxRemoteId === '' || strcmp($statusId, $maxRemoteId) > 0) {
			$maxRemoteId = $statusId;
		}
		if (!plugin_mastodon_remote_status_is_importable($status)) {
			plugin_mastodon_log('Skipping non-public remote status ' . $statusId . ' with visibility ' . plugin_mastodon_remote_status_visibility($status));
			continue;
		}
		if (!plugin_mastodon_remote_status_matches_content_window($options, $status, $force)) {
			plugin_mastodon_log_skip('remote_status_outside_content_window', 'remote status', 'remote statuses', $statusId, 'because they are outside the active synchronization date window');
			continue;
		}
		$entryId = plugin_mastodon_import_remote_entry($options, $state, $status);
		if (!empty($GLOBALS ['plugin_mastodon_media_import_error'])) {
			$state ['last_error'] = (string) $GLOBALS ['plugin_mastodon_media_import_error'];
			return false;
		}
		if (!$entryId) {
			continue;
		}
		if ($syncCommentsAndReplies) {
			$context = plugin_mastodon_fetch_status_context($options, $statusId);
			plugin_mastodon_import_remote_context_descendants($options, $state, $entryId, $statusId, $context);
			$refreshedContextIds [$statusId] = true;
		}
	}

	if ($syncCommentsAndReplies) {
		$contextLimit = plugin_mastodon_old_thread_context_rotation_limit($options);
		$notificationSummary = plugin_mastodon_process_remote_reply_notifications($options, $state, $contextLimit);
		$notificationContextRequests = isset($notificationSummary ['used_contexts']) ? (int) $notificationSummary ['used_contexts'] : 0;
		if (!empty($notificationSummary ['refreshed_context_ids']) && is_array($notificationSummary ['refreshed_context_ids'])) {
			foreach ($notificationSummary ['refreshed_context_ids'] as $refreshedContextId) {
				$refreshedContextId = (string) $refreshedContextId;
				if ($refreshedContextId !== '') {
					$refreshedContextIds [$refreshedContextId] = true;
				}
			}
		}
		$contextTargets = plugin_mastodon_collect_known_entry_context_targets($state, array_keys($refreshedContextIds), $options, $force, max(0, $contextLimit - $notificationContextRequests));
		foreach ($contextTargets as $remoteEntryId => $localEntryId) {
			plugin_mastodon_extend_time_limit(120);
			$context = plugin_mastodon_fetch_status_context($options, $remoteEntryId);
			plugin_mastodon_import_remote_context_descendants($options, $state, $localEntryId, $remoteEntryId, $context);
		}
	}

	if ($maxRemoteId !== '') {
		$state ['last_remote_status_id'] = $maxRemoteId;
	}
	$rateLimitError = plugin_mastodon_rate_limit_state_error();
	if ($rateLimitError !== '') {
		$state ['last_error'] = $rateLimitError;
		return false;
	}
	return true;
}

/**
 * Synchronize local FlatPress content to Mastodon.
 * @param array<string, string> $options
 * @param array<string, mixed> $state
 * @param bool $force
 * @return bool
 */
function plugin_mastodon_sync_local_to_remote(&$options, &$state, $force = true) {
	plugin_mastodon_extend_time_limit(180);
	$force = (bool) $force;
	$charLimit = plugin_mastodon_instance_character_limit($options);
	$mediaLimit = plugin_mastodon_instance_media_limit($options);
	$entries = plugin_mastodon_list_local_entries_for_sync($options, $state, $force);
	$syncCommentsAndReplies = plugin_mastodon_should_sync_comments_and_replies($options);
	if (!$syncCommentsAndReplies && isset($state ['dirty_comments']) && is_array($state ['dirty_comments'])) {
		$state ['dirty_comments'] = array();
	}
	$hadFailure = false;
	foreach ($entries as $entryId => $entry) {
		plugin_mastodon_extend_time_limit(120);
		plugin_mastodon_state_load_comment_shard_into($state, $entryId);
		$meta = plugin_mastodon_state_get_entry_meta($state, $entryId);
		$entrySource = !empty($meta ['source']) ? strtolower((string) $meta ['source']) : 'local';
		$skipEntryStatusSync = ($entrySource === 'remote');
		$entryMatchesSyncStart = plugin_mastodon_local_item_matches_sync_start($options, $entry, $entryId);
		$entryMatchesContentWindow = plugin_mastodon_local_item_matches_content_window($options, $entry, $entryId, $force);
		if ($skipEntryStatusSync) {
			plugin_mastodon_log('Skipping local entry status export for remote-sourced entry ' . $entryId . ' while still evaluating local FlatPress comments for reply export');
		} elseif ($entryMatchesSyncStart && (!$entryMatchesContentWindow && !empty($meta ['remote_id']) && (!empty($meta ['hash']) && $meta ['hash'] !== plugin_mastodon_entry_hash($entry)))) {
			plugin_mastodon_state_set_dirty_entry($state, $entryId, plugin_mastodon_entry_hash($entry));
			plugin_mastodon_log('Queued older changed local entry ' . $entryId . ' for synchronization outside the scheduled window');
		}
		$entryQueuedDirty = plugin_mastodon_state_has_dirty_entry($state, $entryId);
		if ($skipEntryStatusSync) {
			// comments are still handled below
		} elseif ($entryMatchesSyncStart && ($entryMatchesContentWindow || $entryQueuedDirty)) {
			$hash = plugin_mastodon_entry_hash($entry);
			$text = plugin_mastodon_build_entry_status_text($entryId, $entry, $charLimit);
			if ($text === '') {
				plugin_mastodon_state_remove_dirty_entry($state, $entryId);
				continue;
			}

			$mediaItems = plugin_mastodon_collect_local_entry_media($entry);
			$mediaPlan = plugin_mastodon_prepare_entry_media_sync_plan($options, $meta, $mediaItems, $mediaLimit);
			$mediaIds = isset($mediaPlan ['media_ids']) && is_array($mediaPlan ['media_ids']) ? $mediaPlan ['media_ids'] : array();
			$mediaAttributes = isset($mediaPlan ['media_attributes']) && is_array($mediaPlan ['media_attributes']) ? $mediaPlan ['media_attributes'] : array();
			$effectiveMediaItems = isset($mediaPlan ['media_items']) && is_array($mediaPlan ['media_items']) ? $mediaPlan ['media_items'] : array();
			$mediaAttachmentSignature = isset($mediaPlan ['attachment_signature']) ? (string) $mediaPlan ['attachment_signature'] : '';
			$mediaDescriptionSignature = isset($mediaPlan ['description_signature']) ? (string) $mediaPlan ['description_signature'] : '';
			$resolvedRemoteMedia = isset($mediaPlan ['remote_media']) && is_array($mediaPlan ['remote_media']) ? $mediaPlan ['remote_media'] : array();
			$uploadedNewMedia = false;
			if (!empty($mediaPlan ['skipped'])) {
				plugin_mastodon_log('Skipped ' . (int) $mediaPlan ['skipped'] . ' local media attachment(s) because Mastodon accepts either multiple images or one audio/video attachment per status, with an instance limit of ' . $mediaLimit . ' attachment(s).');
			}

			if (($mediaPlan ['mode'] === 'upload') && !empty($effectiveMediaItems)) {
				$upload = plugin_mastodon_upload_media_items($options, $effectiveMediaItems, count($effectiveMediaItems));
				if (!$upload ['ok']) {
					$hadFailure = true;
					$state ['last_error'] = 'local_entry_media_upload_failed: ' . $entryId . ' (' . $upload ['error'] . ')';
					plugin_mastodon_log('Local entry media upload failed for ' . $entryId . ': ' . $upload ['error']);
					continue;
				}
				$mediaIds = isset($upload ['media_ids']) && is_array($upload ['media_ids']) ? $upload ['media_ids'] : array();
				$resolvedRemoteMedia = plugin_mastodon_remote_media_descriptors_from_media_ids($mediaIds, $effectiveMediaItems, $options);
				$uploadedNewMedia = !empty($mediaIds);
			}

			if (!empty($meta ['remote_id'])) {
				if (!empty($meta ['hash']) && $meta ['hash'] === $hash) {
					plugin_mastodon_state_remove_dirty_entry($state, $entryId);
				} else {
					$updated = plugin_mastodon_update_status($options, $meta ['remote_id'], $text, $mediaIds, $mediaAttributes);
					if ($updated ['ok'] && !empty($updated ['json'] ['id'])) {
						plugin_mastodon_state_set_entry_mapping($state, $entryId, $meta ['remote_id'], 'local', $hash, isset($updated ['json'] ['url']) ? $updated ['json'] ['url'] : '', plugin_mastodon_parse_iso_datetime(isset($updated ['json'] ['edited_at']) ? $updated ['json'] ['edited_at'] : ''), plugin_mastodon_local_item_date_key($entry, $entryId), plugin_mastodon_remote_status_date_key(isset($updated ['json']) && is_array($updated ['json']) ? $updated ['json'] : array()));
						$updatedRemoteMedia = plugin_mastodon_remote_media_descriptors_from_status(isset($updated ['json']) && is_array($updated ['json']) ? $updated ['json'] : array());
						if (!empty($updatedRemoteMedia)) {
							$resolvedRemoteMedia = $updatedRemoteMedia;
						}
						plugin_mastodon_state_set_entry_media_meta($state, $entryId, $resolvedRemoteMedia, $mediaAttachmentSignature, $mediaDescriptionSignature);
						plugin_mastodon_state_remove_dirty_entry($state, $entryId);
						$state ['content_stats'] ['updated_remote_entries']++;
					} else {
						if ($uploadedNewMedia && !empty($mediaIds)) {
							plugin_mastodon_cleanup_uploaded_media($options, $mediaIds);
						}
						$hadFailure = true;
						$state ['last_error'] = 'local_entry_update_failed: ' . $entryId . ' (' . plugin_mastodon_response_error_message($updated) . ')';
						plugin_mastodon_log('Local entry update failed for ' . $entryId . ': ' . plugin_mastodon_response_error_message($updated));
						continue;
					}
				}
			} else {
				$created = plugin_mastodon_create_status($options, $text, '', $mediaIds);
				if ($created ['ok'] && !empty($created ['json'] ['id'])) {
					plugin_mastodon_state_set_entry_mapping($state, $entryId, $created ['json'] ['id'], 'local', $hash, isset($created ['json'] ['url']) ? $created ['json'] ['url'] : '', plugin_mastodon_parse_iso_datetime(isset($created ['json'] ['created_at']) ? $created ['json'] ['created_at'] : ''), plugin_mastodon_local_item_date_key($entry, $entryId), plugin_mastodon_remote_status_date_key(isset($created ['json']) && is_array($created ['json']) ? $created ['json'] : array()));
					$createdRemoteMedia = plugin_mastodon_remote_media_descriptors_from_status(isset($created ['json']) && is_array($created ['json']) ? $created ['json'] : array());
					if (!empty($createdRemoteMedia)) {
						$resolvedRemoteMedia = $createdRemoteMedia;
					}
					plugin_mastodon_state_set_entry_media_meta($state, $entryId, $resolvedRemoteMedia, $mediaAttachmentSignature, $mediaDescriptionSignature);
					plugin_mastodon_state_remove_dirty_entry($state, $entryId);
					$state ['content_stats'] ['exported_entries']++;
					$meta = plugin_mastodon_state_get_entry_meta($state, $entryId);
				} else {
					if ($uploadedNewMedia && !empty($mediaIds)) {
						plugin_mastodon_cleanup_uploaded_media($options, $mediaIds);
					}
					$hadFailure = true;
					$state ['last_error'] = 'local_entry_export_failed: ' . $entryId . ' (' . plugin_mastodon_response_error_message($created) . ')';
					plugin_mastodon_log('Local entry export failed for ' . $entryId . ': ' . plugin_mastodon_response_error_message($created));
					continue;
				}
			}
		} else {
			plugin_mastodon_log_skip('local_entry_outside_content_window', 'local entry', 'local entries', $entryId, 'because they are outside the active synchronization date window');
		}

		$entryMeta = plugin_mastodon_state_get_entry_meta($state, $entryId);
		$entryRemoteId = !empty($entryMeta ['remote_id']) ? $entryMeta ['remote_id'] : '';
		if ($entryRemoteId === '') {
			continue;
		}
		if (!$syncCommentsAndReplies) {
			plugin_mastodon_state_remove_dirty_comments_for_entry($state, $entryId);
			continue;
		}

		$commentIds = plugin_mastodon_list_local_comment_ids($entryId);
		$pendingComments = array();
		foreach ($commentIds as $commentId) {
			plugin_mastodon_extend_time_limit(120);
			$comment = comment_parse($entryId, $commentId);
			if (!$comment || !is_array($comment)) {
				continue;
			}
			$comment ['id'] = (string) $commentId;
			$comment ['public_url'] = plugin_mastodon_public_comment_url($entryId, $entry, $commentId);
			$commentMeta = plugin_mastodon_state_get_comment_meta($state, $entryId, $commentId);
			if (!empty($commentMeta ['source']) && $commentMeta ['source'] === 'remote') {
				continue;
			}
			if (!plugin_mastodon_local_comment_export_allowed($state, $entryId, $commentId, $commentMeta, $comment)) {
				plugin_mastodon_state_remove_dirty_comment($state, $entryId, $commentId);
				plugin_mastodon_log_skip('local_comment_missing_mastodon_optin', 'local comment', 'local comments', $entryId . '/' . $commentId, 'because the visitor did not opt in to Mastodon/Fediverse publishing');
				continue;
			}
			if (!plugin_mastodon_local_item_matches_sync_start($options, $comment, $commentId)) {
				plugin_mastodon_log_skip('local_comment_before_sync_start', 'local comment', 'local comments', $entryId . '/' . $commentId, 'because they are older than the configured sync start date');
				continue;
			}
			$commentHash = plugin_mastodon_comment_hash($comment);
			$commentQueuedDirty = plugin_mastodon_state_has_dirty_comment($state, $entryId, $commentId);
			if (!plugin_mastodon_local_item_matches_content_window($options, $comment, $commentId, $force)) {
				if (!empty($commentMeta ['remote_id']) && !empty($commentMeta ['hash']) && $commentMeta ['hash'] !== $commentHash) {
					plugin_mastodon_state_set_dirty_comment($state, $entryId, $commentId, $commentHash);
					$commentQueuedDirty = true;
					plugin_mastodon_log('Queued older changed local comment ' . $entryId . '/' . $commentId . ' for synchronization outside the scheduled window');
				}
				if (!$commentQueuedDirty) {
					plugin_mastodon_log_skip('local_comment_outside_content_window', 'local comment', 'local comments', $entryId . '/' . $commentId, 'because they are outside the active synchronization date window');
					continue;
				}
			}
			$pendingComments [] = array(
				'comment_id' => (string) $commentId,
				'comment' => $comment
			);
		}
		$commentGuard = 0;
		while (!empty($pendingComments) && $commentGuard < 50) {
			$commentGuard++;
			$remainingComments = array();
			$processedComments = false;
			foreach ($pendingComments as $commentRecord) {
				plugin_mastodon_extend_time_limit(120);
				$commentId = (string) $commentRecord ['comment_id'];
				$comment = $commentRecord ['comment'];
				if ($commentId === '') {
					$processedComments = true;
					continue;
				}
				$parentCommentId = plugin_mastodon_detect_local_comment_parent_id($entryId, $comment);
				if ($parentCommentId !== '' && plugin_mastodon_local_comment_parent_export_pending($options, $state, $entryId, $parentCommentId)) {
					$remainingComments [] = $commentRecord;
					continue;
				}
				$processedComments = true;
				$commentMeta = plugin_mastodon_state_get_comment_meta($state, $entryId, $commentId);
				if (!plugin_mastodon_local_comment_export_allowed($state, $entryId, $commentId, $commentMeta, $comment)) {
					plugin_mastodon_state_remove_dirty_comment($state, $entryId, $commentId);
					plugin_mastodon_log_skip('local_comment_missing_mastodon_optin', 'local comment', 'local comments', $entryId . '/' . $commentId, 'because the visitor did not opt in to Mastodon/Fediverse publishing');
					$processedComments = true;
					continue;
				}
				$commentHash = plugin_mastodon_comment_hash($comment);
				$text = plugin_mastodon_build_comment_status_text($entryId, $entry, $comment, $charLimit);
				if ($text === '') {
					continue;
				}
				$replyTarget = plugin_mastodon_resolve_comment_reply_target($state, $entryId, $comment, $entryRemoteId);
				$replyToRemoteId = !empty($replyTarget ['remote_id']) ? (string) $replyTarget ['remote_id'] : $entryRemoteId;
				$parentCommentId = !empty($replyTarget ['parent_comment_id']) ? (string) $replyTarget ['parent_comment_id'] : '';
				if (!empty($commentMeta ['remote_id'])) {
					if (!empty($commentMeta ['hash']) && $commentMeta ['hash'] === $commentHash && ((isset($commentMeta ['parent_comment_id']) ? (string) $commentMeta ['parent_comment_id'] : '') === $parentCommentId) && ((isset($commentMeta ['in_reply_to_remote_id']) ? (string) $commentMeta ['in_reply_to_remote_id'] : '') === $replyToRemoteId)) {
						plugin_mastodon_state_remove_dirty_comment($state, $entryId, $commentId);
						continue;
					}
					$updated = plugin_mastodon_update_status($options, $commentMeta ['remote_id'], $text, array());
					if ($updated ['ok'] && !empty($updated ['json'] ['id'])) {
						if (plugin_mastodon_state_set_comment_mapping($state, $entryId, $commentId, $commentMeta ['remote_id'], 'local', $commentHash, isset($updated ['json'] ['url']) ? $updated ['json'] ['url'] : '', plugin_mastodon_parse_iso_datetime(isset($updated ['json'] ['edited_at']) ? $updated ['json'] ['edited_at'] : ''), $parentCommentId, $replyToRemoteId, plugin_mastodon_local_item_date_key($comment, $commentId), plugin_mastodon_remote_status_date_key(isset($updated ['json']) && is_array($updated ['json']) ? $updated ['json'] : array()))) {
							plugin_mastodon_state_remove_dirty_comment($state, $entryId, $commentId);
							plugin_mastodon_state_remove_comment_reply_optin($state, $entryId, $commentId);
							$state ['content_stats'] ['updated_remote_comments']++;
						} else {
							$hadFailure = true;
							$state ['last_error'] = 'local_comment_mapping_conflict: ' . $entryId . '/' . $commentId;
							plugin_mastodon_log('Local comment update completed remotely, but its mapping was rejected because remote status ' . (string) $commentMeta ['remote_id'] . ' is already assigned to another local comment');
						}
					} else {
						$hadFailure = true;
						$state ['last_error'] = 'local_comment_update_failed: ' . $entryId . '/' . $commentId . ' (' . plugin_mastodon_response_error_message($updated) . ')';
						plugin_mastodon_log('Local comment update failed for ' . $entryId . '/' . $commentId . ': ' . plugin_mastodon_response_error_message($updated));
					}
				} else {
					$created = plugin_mastodon_create_status($options, $text, $replyToRemoteId, array());
					if ($created ['ok'] && !empty($created ['json'] ['id'])) {
						if (plugin_mastodon_state_set_comment_mapping($state, $entryId, $commentId, $created ['json'] ['id'], 'local', $commentHash, isset($created ['json'] ['url']) ? $created ['json'] ['url'] : '', plugin_mastodon_parse_iso_datetime(isset($created ['json'] ['created_at']) ? $created ['json'] ['created_at'] : ''), $parentCommentId, $replyToRemoteId, plugin_mastodon_local_item_date_key($comment, $commentId), plugin_mastodon_remote_status_date_key(isset($created ['json']) && is_array($created ['json']) ? $created ['json'] : array()))) {
							plugin_mastodon_state_remove_dirty_comment($state, $entryId, $commentId);
							plugin_mastodon_state_remove_comment_reply_optin($state, $entryId, $commentId);
							$state ['content_stats'] ['exported_comments']++;
						} else {
							$hadFailure = true;
							$state ['last_error'] = 'local_comment_mapping_conflict: ' . $entryId . '/' . $commentId;
							plugin_mastodon_log('Local comment export returned remote status ' . (string) $created ['json'] ['id'] . ', but its mapping was rejected because that status is already assigned to another local comment');
						}
					} else {
						$hadFailure = true;
						$state ['last_error'] = 'local_comment_export_failed: ' . $entryId . '/' . $commentId . ' (' . plugin_mastodon_response_error_message($created) . ')';
						plugin_mastodon_log('Local comment export failed for ' . $entryId . '/' . $commentId . ': ' . plugin_mastodon_response_error_message($created));
					}
				}
			}
			if (!$processedComments) {
				break;
			}
			$pendingComments = $remainingComments;
		}
		if (!empty($pendingComments)) {
			foreach ($pendingComments as $commentRecord) {
				$commentId = (string) $commentRecord ['comment_id'];
				$parentCommentId = plugin_mastodon_detect_local_comment_parent_id($entryId, $commentRecord ['comment']);
				plugin_mastodon_log('Deferred local comment export for ' . $entryId . '/' . $commentId . ' because parent comment ' . $parentCommentId . ' is not synchronized yet');
			}
		}
	}

	$rateLimitError = plugin_mastodon_rate_limit_state_error();
	if ($rateLimitError !== '' && !$hadFailure) {
		$state ['last_error'] = $rateLimitError;
	}
	return !$hadFailure && $rateLimitError === '';
}

/**
 * Determine whether the scheduled synchronization is currently due.
 *
 * @param array<string, string> $options
 * @param array<string, mixed> $state
 * @param int $timestamp
 * @return bool
 */
function plugin_mastodon_sync_due($options, $state, $timestamp) {
	$timestamp = (int) $timestamp;
	if (empty($state ['last_run'])) {
		return true;
	}
	$syncTime = plugin_mastodon_normalize_sync_time(isset($options ['sync_time']) ? $options ['sync_time'] : '');
	list($hour, $minute) = array_map('intval', explode(':', $syncTime, 2));
	$target = gmmktime($hour, $minute, 0, (int) gmdate('m', $timestamp), (int) gmdate('d', $timestamp), (int) gmdate('Y', $timestamp));
	if ($timestamp < $target) {
		return false;
	}
	$lastRun = plugin_mastodon_parse_iso_timestamp((string) $state ['last_run']);
	if ($lastRun <= 0) {
		return true;
	}
	return gmdate('Y-m-d', $lastRun) !== gmdate('Y-m-d', $timestamp);
}

/**
 * Run a full synchronization cycle.
 *
 * The first flag controls whether the run is triggered explicitly and should
 * bypass the daily due check. The second flag controls whether automatic
 * scheduled windows are ignored. Keeping both concepts separate allows a
 * manual admin run to behave like the scheduled run while still offering an
 * explicit full check.
 *
 * @param bool $force
 * @param bool|null $fullWindow
 * @return array<string, mixed>
 */
function plugin_mastodon_run_sync($force, $fullWindow = null) {
	plugin_mastodon_extend_time_limit(180);
	$options = plugin_mastodon_get_options();
	$state = plugin_mastodon_state_read(array());
	$force = (bool) $force;
	$fullWindow = $fullWindow === null ? $force : (bool) $fullWindow;

	if ($options ['instance_url'] === '') {
		$state ['last_error'] = 'missing_instance_url';
		plugin_mastodon_state_write($state);
		return array('ok' => false, 'state' => $state, 'message' => 'missing_instance_url');
	}
	if ($options ['access_token'] === '') {
		$state ['last_error'] = 'missing_access_token';
		plugin_mastodon_state_write($state);
		return array('ok' => false, 'state' => $state, 'message' => 'missing_access_token');
	}
	if (!$force && !plugin_mastodon_sync_due($options, $state, time())) {
		return array('ok' => true, 'state' => $state, 'message' => 'not_due');
	}
	if (!$force && plugin_mastodon_sync_guard_active('content')) {
		return array('ok' => true, 'state' => $state, 'message' => 'sync_cooldown');
	}
	if (!$force) {
		plugin_mastodon_sync_guard_mark('content', 'scheduled_content_sync');
	}

	plugin_mastodon_ensure_state_dir();
	$lockHandle = @fopen(PLUGIN_MASTODON_LOCK_FILE, 'c+');
	if (!$lockHandle) {
		$state ['last_error'] = 'lock_open_failed';
		plugin_mastodon_state_write($state);
		return array('ok' => false, 'state' => $state, 'message' => 'lock_open_failed');
	}
	plugin_mastodon_apply_file_permissions(PLUGIN_MASTODON_LOCK_FILE);
	if (!@flock($lockHandle, LOCK_EX | LOCK_NB)) {
		return array('ok' => true, 'state' => $state, 'message' => 'sync_locked');
	}

	plugin_mastodon_rate_limit_guard_start('content_sync');
	plugin_mastodon_download_budget_start();
	plugin_mastodon_refresh_instance_if_due($options);
	$orphanedPendingOptinsRemoved = plugin_mastodon_state_prune_orphaned_pending_comment_reply_optins($state);
	if ($orphanedPendingOptinsRemoved > 0) {
		plugin_mastodon_log('Removed ' . $orphanedPendingOptinsRemoved . ' orphaned CommentCenter Mastodon opt-in grant(s)');
	}
	$state ['last_error'] = '';
	$state ['content_stats'] = plugin_mastodon_default_state() ['content_stats'];

	plugin_mastodon_refresh_profile_cache_for_sync($options);

	plugin_mastodon_state_load_content_sync_comment_workset($options, $state, $fullWindow);
	$protectedDeletedExportedComments = plugin_mastodon_protect_locally_deleted_exported_comments($options, $state);
	if ($protectedDeletedExportedComments > 0) {
		plugin_mastodon_log('Protected ' . $protectedDeletedExportedComments . ' locally deleted exported FlatPress comment mapping(s) from stale Mastodon context re-imports before content synchronization');
	}

	$okRemote = plugin_mastodon_sync_remote_to_local($options, $state, $fullWindow);
	$okLocal = false;
	if ($okRemote) {
		$okLocal = plugin_mastodon_sync_local_to_remote($options, $state, $fullWindow);
	}

	plugin_mastodon_log_flush_skip_summaries();

	if ($okRemote && $okLocal) {
		$completionTimestamp = time();
		$state ['last_run'] = gmdate('Y-m-d H:i:s', $completionTimestamp);
		plugin_mastodon_state_schedule_content_deletions($state, plugin_mastodon_should_run_deletion_sync($options), $completionTimestamp);
		$state ['last_error'] = '';
		plugin_mastodon_log('Synchronization completed successfully');
	} elseif ($state ['last_error'] === '') {
		$state ['last_error'] = 'sync_failed';
		plugin_mastodon_log('Synchronization failed');
	}

	$stateWriteOk = plugin_mastodon_state_write_with_last_error($state, 'state_write_failed_after_sync', true);
	if (!$stateWriteOk && $okRemote && $okLocal) {
		if ($state ['last_error'] === '') {
			$state ['last_error'] = 'state_write_failed_after_sync';
		}
		plugin_mastodon_log('Synchronization completed but state.json could not be persisted: ' . $state ['last_error']);
	}
	@flock($lockHandle, LOCK_UN);
	@fclose($lockHandle);
	plugin_mastodon_rate_limit_guard_stop();

	return array('ok' => ($okRemote && $okLocal && $stateWriteOk), 'state' => $state, 'message' => $state ['last_error'] === '' ? 'ok' : $state ['last_error']);
}

/**
 * Run the scheduled synchronization when the current request is due.
 * @return void
 */
function plugin_mastodon_maybe_sync() {
	if (PHP_SAPI === 'cli') {
		return;
	}
	if (isset($_SERVER ['REQUEST_METHOD']) && strtoupper($_SERVER ['REQUEST_METHOD']) === 'POST') {
		return;
	}

	$options = plugin_mastodon_get_options();
	if ($options ['instance_url'] === '' || $options ['access_token'] === '') {
		return;
	}
	$state = plugin_mastodon_scheduler_state_read();
	$job = plugin_mastodon_scheduled_job($options, $state, time());
	if ($job === 'deletion') {
		plugin_mastodon_run_deletion_sync(false);
	} elseif ($job === 'content') {
		plugin_mastodon_run_sync(false);
	}
}
?>
