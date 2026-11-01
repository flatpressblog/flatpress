<?php
/**
 * Mastodon runtime declarations. Loaded by plugin.mastodon.php.
 * Runtime constants and registrations remain in the main plugin file.
 */

/**
 * Return a value from the request-local plugin cache.
 *
 * @param string $bucket Cache bucket name.
 * @param string $key Cache entry key.
 * @param-out bool $hit Whether the requested cache entry was found.
 * @return mixed
 */
function plugin_mastodon_runtime_cache_get($bucket, $key, &$hit = false) {
	$bucket = (string) $bucket;
	$key = (string) $key;
	$hit = false;
	if (!isset($GLOBALS ['plugin_mastodon_runtime_cache']) || !is_array($GLOBALS ['plugin_mastodon_runtime_cache'])) {
		$GLOBALS ['plugin_mastodon_runtime_cache'] = array();
	}
	if (!isset($GLOBALS ['plugin_mastodon_runtime_cache'] [$bucket]) || !is_array($GLOBALS ['plugin_mastodon_runtime_cache'] [$bucket])) {
		return null;
	}
	if (array_key_exists($key, $GLOBALS ['plugin_mastodon_runtime_cache'] [$bucket])) {
		$hit = true;
		return $GLOBALS ['plugin_mastodon_runtime_cache'] [$bucket] [$key];
	}
	return null;
}

/**
 * Store a value in the request-local plugin cache.
 * @param string $bucket
 * @param string $key
 * @param mixed $value
 * @return mixed
 */
function plugin_mastodon_runtime_cache_set($bucket, $key, $value) {
	$bucket = (string) $bucket;
	$key = (string) $key;
	if (!isset($GLOBALS ['plugin_mastodon_runtime_cache']) || !is_array($GLOBALS ['plugin_mastodon_runtime_cache'])) {
		$GLOBALS ['plugin_mastodon_runtime_cache'] = array();
	}
	if (!isset($GLOBALS ['plugin_mastodon_runtime_cache'] [$bucket]) || !is_array($GLOBALS ['plugin_mastodon_runtime_cache'] [$bucket])) {
		$GLOBALS ['plugin_mastodon_runtime_cache'] [$bucket] = array();
	}
	$GLOBALS ['plugin_mastodon_runtime_cache'] [$bucket] [$key] = $value;
	return $value;
}

/**
 * Clear one request-local plugin cache bucket or the complete cache.
 * @param string $bucket
 * @return void
 */
function plugin_mastodon_runtime_cache_clear($bucket = '') {
	$bucket = (string) $bucket;
	if ($bucket === '') {
		unset($GLOBALS ['plugin_mastodon_runtime_cache']);
		return;
	}
	if (isset($GLOBALS ['plugin_mastodon_runtime_cache'] [$bucket])) {
		unset($GLOBALS ['plugin_mastodon_runtime_cache'] [$bucket]);
	}
}

/**
 * Return the FlatPress configuration, preferring the early-loaded core cache.
 * @return array<string, mixed>
 */
function plugin_mastodon_fp_config() {
	$cached = plugin_mastodon_runtime_cache_get('core', 'fp_config', $hit);
	if ($hit && is_array($cached)) {
		return $cached;
	}

	$config = array();
	if (isset($GLOBALS ['EARLY_FP_CONFIG']) && is_array($GLOBALS ['EARLY_FP_CONFIG'])) {
		$config = $GLOBALS ['EARLY_FP_CONFIG'];
	} else {
		global $fp_config;
		if (isset($fp_config) && is_array($fp_config)) {
			$config = $fp_config;
		} elseif (function_exists('config_load')) {
			$loaded = config_load();
			if (is_array($loaded)) {
				$config = $loaded;
			}
		}
	}

	if (!isset($GLOBALS ['EARLY_FP_CONFIG']) && $config !== array()) {
		$GLOBALS ['EARLY_FP_CONFIG'] = $config;
	}
	if (!isset($GLOBALS ['fp_config']) && $config !== array()) {
		$GLOBALS ['fp_config'] = $config;
	}
	return plugin_mastodon_runtime_cache_set('core', 'fp_config', $config);
}

/**
 * Read a nested FlatPress configuration value.
 * @param list<string> $path
 * @param mixed $default
 * @return mixed
 */
function plugin_mastodon_fp_config_value(array $path, $default = null) {
	$config = plugin_mastodon_fp_config();
	$cursor = $config;
	foreach ($path as $segment) {
		if (!is_array($cursor) || !array_key_exists($segment, $cursor)) {
			return $default;
		}
		$cursor = $cursor [$segment];
	}
	return $cursor;
}

/**
 * Return the configured FlatPress time offset in seconds.
 * @return int
 */
function plugin_mastodon_fp_timeoffset_seconds() {
	$offset = plugin_mastodon_fp_config_value(array('locale', 'timeoffset'), 0);
	if (!is_numeric($offset)) {
		return 0;
	}
	return (int) round(((float) $offset) * 3600);
}

/**
 * Format the configured FlatPress time offset as a UTC label for admin users.
 * @return string
 */
function plugin_mastodon_fp_timeoffset_label() {
	$seconds = plugin_mastodon_fp_timeoffset_seconds();
	$sign = $seconds < 0 ? '-' : '+';
	$seconds = abs($seconds);
	$hours = (int) floor($seconds / 3600);
	$minutes = (int) floor(($seconds % 3600) / 60);
	return sprintf('UTC%s%02d:%02d', $sign, $hours, $minutes);
}

/**
 * Convert a real Unix timestamp into FlatPress's offset-adjusted timestamp model.
 * @param int|float|string $timestamp
 * @return int
 */
function plugin_mastodon_timestamp_to_flatpress_time($timestamp) {
	if (!is_numeric($timestamp)) {
		return 0;
	}
	$timestamp = (int) $timestamp;
	if ($timestamp <= 0) {
		return 0;
	}
	return $timestamp + plugin_mastodon_fp_timeoffset_seconds();
}

/**
 * Convert a HH:MM time value into minutes after midnight.
 * @param string $time
 * @return int
 */
function plugin_mastodon_sync_time_to_minutes($time) {
	$time = plugin_mastodon_normalize_sync_time($time);
	list($hour, $minute) = array_map('intval', explode(':', $time, 2));
	return ($hour * 60) + $minute;
}

/**
 * Convert minutes after midnight into a normalized HH:MM time value.
 * @param int $minutes
 * @return string
 */
function plugin_mastodon_minutes_to_sync_time($minutes) {
	$minutes = (int) $minutes;
	$minutes = $minutes % 1440;
	if ($minutes < 0) {
		$minutes += 1440;
	}
	$hour = (int) floor($minutes / 60);
	$minute = $minutes % 60;
	return sprintf('%02d:%02d', $hour, $minute);
}

/**
 * Convert the stored UTC synchronization time to the FlatPress-local admin time.
 * @param string $time
 * @return string
 */
function plugin_mastodon_sync_time_utc_to_local($time) {
	$minutes = plugin_mastodon_sync_time_to_minutes($time);
	$offsetMinutes = (int) round(plugin_mastodon_fp_timeoffset_seconds() / 60);
	return plugin_mastodon_minutes_to_sync_time($minutes + $offsetMinutes);
}

/**
 * Convert a FlatPress-local admin synchronization time back to stored UTC time.
 * @param string $time
 * @return string
 */
function plugin_mastodon_sync_time_local_to_utc($time) {
	$minutes = plugin_mastodon_sync_time_to_minutes($time);
	$offsetMinutes = (int) round(plugin_mastodon_fp_timeoffset_seconds() / 60);
	return plugin_mastodon_minutes_to_sync_time($minutes - $offsetMinutes);
}

/**
 * Format a stored UTC timestamp for the admin panel using FlatPress local time and configured formats.
 * @param string $value
 * @return string
 */
function plugin_mastodon_format_admin_datetime($value) {
	$value = trim((string) $value);
	if ($value === '') {
		return '';
	}
	$timestamp = plugin_mastodon_parse_iso_timestamp($value);
	if ($timestamp <= 0) {
		return $value;
	}
	$localTimestamp = (int) $timestamp + plugin_mastodon_fp_timeoffset_seconds();
	$dateFormat = plugin_mastodon_fp_config_value(array('locale', 'dateformat'), '%Y-%m-%d');
	$timeFormat = plugin_mastodon_fp_config_value(array('locale', 'timeformat'), '%H:%M:%S');
	$format = trim((string) $dateFormat . ' ' . (string) $timeFormat);
	if ($format === '') {
		$format = '%Y-%m-%d %H:%M:%S';
	}
	$previousTimezone = date_default_timezone_get();
	date_default_timezone_set('UTC');
	try {
		if (function_exists('date_strformat')) {
			$formatted = date_strformat($format, $localTimestamp);
		} else {
			$formatted = gmdate('Y-m-d H:i:s', $localTimestamp);
		}
	} catch (Exception $exception) {
		$formatted = gmdate('Y-m-d H:i:s', $localTimestamp);
	}
	date_default_timezone_set($previousTimezone);
	return trim((string) $formatted) . ' (' . plugin_mastodon_fp_timeoffset_label() . ')';
}

/**
 * Read a file through the FlatPress I/O layer when available.
 * @param string $file
 * @param mixed $prestat
 * @return string|false
 */
function plugin_mastodon_io_read_file($file, $prestat = null) {
	if (isset($GLOBALS ['plugin_mastodon_test_file_reads']) && is_array($GLOBALS ['plugin_mastodon_test_file_reads'])) {
		$GLOBALS ['plugin_mastodon_test_file_reads'] [] = (string) $file;
	}
	if (function_exists('io_load_file')) {
		return io_load_file($file, $prestat);
	}
	return @file_get_contents($file);
}

/**
 * Read a file without FlatPress request-local caches.
 * @param string $file
 * @param bool $assumeExists
 * @return string|false
 */
function plugin_mastodon_io_read_file_uncached($file, $assumeExists = false) {
	if (isset($GLOBALS ['plugin_mastodon_test_uncached_file_reads']) && is_array($GLOBALS ['plugin_mastodon_test_uncached_file_reads'])) {
		$GLOBALS ['plugin_mastodon_test_uncached_file_reads'] [] = (string) $file;
	}
	if (function_exists('io_load_file_uncached')) {
		return io_load_file_uncached($file, (bool) $assumeExists);
	}
	if (!$assumeExists && !@file_exists($file)) {
		return false;
	}
	clearstatcache(true, $file);
	return @file_get_contents($file);
}

/**
 * Return the FlatPress file permission mode for runtime files.
 * @return int
 */
function plugin_mastodon_file_permissions_mode() {
	return defined('FILE_PERMISSIONS') ? (int) constant('FILE_PERMISSIONS') : 0644;
}

/**
 * Apply FlatPress FILE_PERMISSIONS to a runtime file.
 * @param string $file
 * @return bool
 */
function plugin_mastodon_apply_file_permissions($file) {
	if (!@file_exists($file) || !@is_file($file)) {
		return false;
	}
	return @chmod($file, plugin_mastodon_file_permissions_mode());
}

/**
 * Write a file through the FlatPress I/O layer when available.
 * @param string $file
 * @param string $payload
 * @return bool
 */
function plugin_mastodon_io_write_file($file, $payload) {
	if (function_exists('io_write_file')) {
		$written = (bool) io_write_file($file, $payload);
		if ($written) {
			plugin_mastodon_apply_file_permissions($file);
		}
		return $written;
	}
	if (@file_put_contents($file, $payload) === false) {
		return false;
	}
	plugin_mastodon_apply_file_permissions($file);
	return true;
}

/**
 * Append to a file without re-reading and rewriting the complete payload.
 * @param string $file
 * @param string $payload
 * @return bool
 */
function plugin_mastodon_io_append_file($file, $payload) {
	$payload = (string) $payload;
	$dir = dirname((string) $file);
	if (function_exists('fs_mkdir')) {
		if (!fs_mkdir($dir)) {
			return false;
		}
	} elseif (!is_dir($dir) && !@mkdir($dir, DIR_PERMISSIONS, true)) {
		return false;
	}

	$lockFile = (string) $file . '.lock';
	$lockHandle = @fopen($lockFile, 'c+');
	if ($lockHandle) {
		plugin_mastodon_apply_file_permissions($lockFile);
		if (@flock($lockHandle, LOCK_EX)) {
			plugin_mastodon_io_rotate_file_if_needed($file, strlen($payload));
			$written = @file_put_contents($file, $payload, FILE_APPEND | LOCK_EX);
			if ($written !== false) {
				plugin_mastodon_apply_file_permissions($file);
			}
			@flock($lockHandle, LOCK_UN);
			@fclose($lockHandle);
			return $written !== false;
		}
		@fclose($lockHandle);
	}

	plugin_mastodon_io_rotate_file_if_needed($file, strlen($payload));
	$written = @file_put_contents($file, $payload, FILE_APPEND | LOCK_EX);
	if ($written !== false) {
		plugin_mastodon_apply_file_permissions($file);
	}
	return $written !== false;
}

/**
 * Return the maximum sync.log size before rotation.
 * @return int
 */
function plugin_mastodon_log_max_bytes() {
	if (isset($GLOBALS ['plugin_mastodon_test_log_max_bytes']) && is_numeric($GLOBALS ['plugin_mastodon_test_log_max_bytes'])) {
		return max(0, (int) $GLOBALS ['plugin_mastodon_test_log_max_bytes']);
	}
	return max(0, (int) PLUGIN_MASTODON_LOG_MAX_BYTES);
}

/**
 * Return the number of retained sync.log rotation files.
 * @return int
 */
function plugin_mastodon_log_rotate_files() {
	if (isset($GLOBALS ['plugin_mastodon_test_log_rotate_files']) && is_numeric($GLOBALS ['plugin_mastodon_test_log_rotate_files'])) {
		return max(0, (int) $GLOBALS ['plugin_mastodon_test_log_rotate_files']);
	}
	return max(0, (int) PLUGIN_MASTODON_LOG_ROTATE_FILES);
}

/**
 * Rotate an append-only log file when the next append would exceed the size cap.
 * @param string $file
 * @param int $appendBytes
 * @return void
 */
function plugin_mastodon_io_rotate_file_if_needed($file, $appendBytes) {
	$maxBytes = plugin_mastodon_log_max_bytes();
	if ($maxBytes <= 0) {
		return;
	}
	clearstatcache(true, $file);
	$currentSize = @is_file($file) ? (int) @filesize($file) : 0;
	if ($currentSize <= 0 || ($currentSize + max(0, (int) $appendBytes)) <= $maxBytes) {
		return;
	}

	$rotateFiles = plugin_mastodon_log_rotate_files();
	if ($rotateFiles <= 0) {
		@unlink($file);
		return;
	}

	$oldest = $file . '.' . $rotateFiles;
	if (@is_file($oldest)) {
		@unlink($oldest);
	}
	for ($index = $rotateFiles - 1; $index >= 1; $index--) {
		$source = $file . '.' . $index;
		$target = $file . '.' . ($index + 1);
		if (@is_file($source)) {
			@rename($source, $target);
			plugin_mastodon_apply_file_permissions($target);
		}
	}
	if (@is_file($file)) {
		@rename($file, $file . '.1');
		plugin_mastodon_apply_file_permissions($file . '.1');
	}
}

/**
 * Check whether shared APCu caching is available for the plugin.
 * @return bool
 */
function plugin_mastodon_apcu_enabled() {
	return function_exists('is_apcu_on') && is_apcu_on();
}

/**
 * Build the plugin APCu key suffix passed to the FlatPress APCu wrappers.
 * @param string $suffix
 * @return string
 */
function plugin_mastodon_apcu_cache_key($suffix) {
	return 'mastodon:' . (string) $suffix;
}

/**
 * Fetch a value from APCu through the FlatPress namespace helper.
 * @param string $suffix
 * @param bool|null $hit
 * @param-out bool|null $hit
 * @return mixed
 */
function plugin_mastodon_apcu_fetch($suffix, &$hit = null) {
	$hit = false;
	if (!plugin_mastodon_apcu_enabled() || !function_exists('apcu_get')) {
		return null;
	}
	return apcu_get(plugin_mastodon_apcu_cache_key($suffix), $hit);
}

/**
 * Store a value in APCu through the FlatPress namespace helper.
 * @param string $suffix
 * @param mixed $value
 * @param int $ttl
 * @return bool
 */
function plugin_mastodon_apcu_store($suffix, $value, $ttl) {
	if (!plugin_mastodon_apcu_enabled() || !function_exists('apcu_set')) {
		return false;
	}
	$ttl = max(0, (int) $ttl);
	return (bool) apcu_set(plugin_mastodon_apcu_cache_key($suffix), $value, $ttl);
}

/**
 * Delete a value from APCu through the FlatPress namespace helper.
 * @param string $suffix
 * @return bool
 */
function plugin_mastodon_apcu_delete($suffix) {
	if (!plugin_mastodon_apcu_enabled() || !function_exists('apcu_delete_key')) {
		return false;
	}
	return (bool) @apcu_delete_key(plugin_mastodon_apcu_cache_key($suffix));
}

/**
 * Return the legacy APCu key that used to hold a full last-known-good state fallback.
 * @return string
 */
function plugin_mastodon_state_fallback_key() {
	return 'state:last_known_good:v1';
}

/**
 * Remove the legacy full-state APCu fallback instead of storing large mapping arrays.
 * @param array<string, mixed> $state
 * @return bool
 */
function plugin_mastodon_state_fallback_store($state) {
	plugin_mastodon_apcu_delete(plugin_mastodon_state_fallback_key());
	return false;
}

/**
 * Full-state APCu fallback is intentionally disabled to avoid multi-MiB APCu entries.
 * @return array<string, mixed>
 */
function plugin_mastodon_state_fallback_read() {
	plugin_mastodon_apcu_delete(plugin_mastodon_state_fallback_key());
	return array();
}

/**
 * Normalize the cooldown guard kind.
 * @param string $kind
 * @return string
 */
function plugin_mastodon_sync_guard_kind($kind) {
	$kind = trim((string) $kind);
	return $kind === 'deletion' ? 'deletion' : 'content';
}

/**
 * Return the APCu key used for a sync cooldown guard.
 * @param string $kind
 * @return string
 */
function plugin_mastodon_sync_guard_apcu_key($kind) {
	return 'sync_guard:' . plugin_mastodon_sync_guard_kind($kind) . ':v1';
}

/**
 * Return true when one guard entry is still active.
 * @param mixed $entry
 * @param int $now
 * @return bool
 */
function plugin_mastodon_sync_guard_entry_active($entry, $now) {
	return is_array($entry) && isset($entry ['expires_at']) && (int) $entry ['expires_at'] > (int) $now;
}

/**
 * Store one cooldown guard in APCu.
 * @param string $kind
 * @param array<string, mixed> $entry
 * @param int $now
 * @return bool
 */
function plugin_mastodon_sync_guard_apcu_store($kind, $entry, $now) {
	$ttl = isset($entry ['expires_at']) ? ((int) $entry ['expires_at'] - (int) $now) : PLUGIN_MASTODON_COOLDOWN_TTL;
	if ($ttl < 1) {
		return false;
	}
	return plugin_mastodon_apcu_store(plugin_mastodon_sync_guard_apcu_key($kind), $entry, $ttl);
}

/**
 * Read and prune the file-backed cooldown guard.
 * @param int $now
 * @return array<string, array<string, mixed>>
 */
function plugin_mastodon_sync_guard_file_read($now) {
	$prestat = plugin_mastodon_file_prestat(PLUGIN_MASTODON_GUARD_FILE);
	if (empty($prestat ['exists'])) {
		return array();
	}
	$json = plugin_mastodon_io_read_file_uncached(PLUGIN_MASTODON_GUARD_FILE, true);
	if (!is_string($json) || trim($json) === '') {
		return array();
	}
	$data = json_decode($json, true);
	if (!is_array($data)) {
		return array();
	}
	$guards = array();
	foreach (array('content', 'deletion') as $kind) {
		if (isset($data [$kind]) && plugin_mastodon_sync_guard_entry_active($data [$kind], $now)) {
			$guards [$kind] = $data [$kind];
		}
	}
	return $guards;
}

/**
 * Write the file-backed cooldown guard.
 * @param array<string, array<string, mixed>> $guards
 * @return bool
 */
function plugin_mastodon_sync_guard_file_write($guards) {
	plugin_mastodon_ensure_state_dir();
	$payload = array('version' => 1);
	foreach (array('content', 'deletion') as $kind) {
		if (isset($guards [$kind]) && is_array($guards [$kind])) {
			$payload [$kind] = $guards [$kind];
		}
	}
	$json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	if (!is_string($json)) {
		return false;
	}
	return plugin_mastodon_io_write_file(PLUGIN_MASTODON_GUARD_FILE, $json . PHP_EOL);
}

/**
 * Return true when a recent scheduled sync/deletion pass should cool down.
 * @param string $kind
 * @param int|null $now
 * @return bool
 */
function plugin_mastodon_sync_guard_active($kind, $now = null) {
	$kind = plugin_mastodon_sync_guard_kind($kind);
	$now = $now === null ? time() : (int) $now;

	$hit = false;
	$apcuEntry = plugin_mastodon_apcu_fetch(plugin_mastodon_sync_guard_apcu_key($kind), $hit);
	if ($hit && plugin_mastodon_sync_guard_entry_active($apcuEntry, $now)) {
		return true;
	}

	$guards = plugin_mastodon_sync_guard_file_read($now);
	if (isset($guards [$kind]) && plugin_mastodon_sync_guard_entry_active($guards [$kind], $now)) {
		plugin_mastodon_sync_guard_apcu_store($kind, $guards [$kind], $now);
		return true;
	}
	return false;
}

/**
 * Mark a short cooldown guard for a scheduled sync/deletion pass.
 * @param string $kind
 * @param string $reason
 * @param int|null $now
 * @return bool
 */
function plugin_mastodon_sync_guard_mark($kind, $reason, $now = null) {
	$kind = plugin_mastodon_sync_guard_kind($kind);
	$now = $now === null ? time() : (int) $now;
	$entry = array(
		'started_at' => $now,
		'expires_at' => $now + PLUGIN_MASTODON_COOLDOWN_TTL,
		'reason' => trim((string) $reason)
	);
	$apcuOk = plugin_mastodon_sync_guard_apcu_store($kind, $entry, $now);
	$guards = plugin_mastodon_sync_guard_file_read($now);
	$guards [$kind] = $entry;
	$fileOk = plugin_mastodon_sync_guard_file_write($guards);
	return $apcuOk || $fileOk;
}

/**
 * Clear one sync cooldown guard.
 * @param string $kind
 * @param int|null $now
 * @return bool
 */
function plugin_mastodon_sync_guard_clear($kind, $now = null) {
	$kind = plugin_mastodon_sync_guard_kind($kind);
	$now = $now === null ? time() : (int) $now;
	plugin_mastodon_apcu_delete(plugin_mastodon_sync_guard_apcu_key($kind));
	$guards = plugin_mastodon_sync_guard_file_read($now);
	if (isset($guards [$kind])) {
		unset($guards [$kind]);
	}
	return plugin_mastodon_sync_guard_file_write($guards);
}

/**
 * Return the default Mastodon API budgets for one synchronization run.
 * @return array<string, int>
 */
function plugin_mastodon_rate_limit_default_budgets() {
	$budgets = array(
		'requests' => (int) PLUGIN_MASTODON_RUN_REQUEST_BUDGET,
		'media_uploads' => (int) PLUGIN_MASTODON_RUN_MEDIA_UPLOAD_BUDGET,
		'deletes' => (int) PLUGIN_MASTODON_RUN_DELETE_BUDGET,
		'remote_remaining_floor' => (int) PLUGIN_MASTODON_RATE_LIMIT_REMAINING_FLOOR
	);
	if (isset($GLOBALS ['plugin_mastodon_test_rate_limit_budgets']) && is_array($GLOBALS ['plugin_mastodon_test_rate_limit_budgets'])) {
		foreach ($budgets as $key => $value) {
			if (isset($GLOBALS ['plugin_mastodon_test_rate_limit_budgets'] [$key]) && is_numeric($GLOBALS ['plugin_mastodon_test_rate_limit_budgets'] [$key])) {
				$budgets [$key] = (int) $GLOBALS ['plugin_mastodon_test_rate_limit_budgets'] [$key];
			}
		}
	}
	foreach ($budgets as $key => $value) {
		$budgets [$key] = max(0, (int) $value);
	}
	return $budgets;
}

/**
 * Return the persistent Mastodon API window budgets across synchronization runs.
 * @return array<string, int>
 */
function plugin_mastodon_rate_limit_window_budgets() {
	$budgets = array(
		'media_uploads' => (int) PLUGIN_MASTODON_WINDOW_MEDIA_UPLOAD_BUDGET,
		'media_uploads_ttl' => (int) PLUGIN_MASTODON_WINDOW_MEDIA_UPLOAD_TTL,
		'deletes' => (int) PLUGIN_MASTODON_WINDOW_DELETE_BUDGET,
		'deletes_ttl' => (int) PLUGIN_MASTODON_WINDOW_DELETE_TTL,
		'status_pages' => (int) PLUGIN_MASTODON_WINDOW_STATUS_PAGE_BUDGET,
		'status_pages_ttl' => (int) PLUGIN_MASTODON_WINDOW_STATUS_PAGE_TTL
	);
	if (isset($GLOBALS ['plugin_mastodon_test_rate_limit_window_budgets']) && is_array($GLOBALS ['plugin_mastodon_test_rate_limit_window_budgets'])) {
		foreach ($budgets as $key => $value) {
			if (isset($GLOBALS ['plugin_mastodon_test_rate_limit_window_budgets'] [$key]) && is_numeric($GLOBALS ['plugin_mastodon_test_rate_limit_window_budgets'] [$key])) {
				$budgets [$key] = (int) $GLOBALS ['plugin_mastodon_test_rate_limit_window_budgets'] [$key];
			}
		}
	}
	foreach ($budgets as $key => $value) {
		$budgets [$key] = max(0, (int) $value);
	}
	return $budgets;
}

/**
 * Return persistent window configuration for one request kind.
 * @param string $kind
 * @return array{key:string,budget:int,ttl:int,reason:string}
 */
function plugin_mastodon_rate_limit_window_config($kind) {
	$budgets = plugin_mastodon_rate_limit_window_budgets();
	if ($kind === 'media_upload') {
		return array('key' => 'media_uploads', 'budget' => $budgets ['media_uploads'], 'ttl' => $budgets ['media_uploads_ttl'], 'reason' => 'rate_limit_window_media_upload_budget_exhausted');
	}
	if ($kind === 'status_delete') {
		return array('key' => 'deletes', 'budget' => $budgets ['deletes'], 'ttl' => $budgets ['deletes_ttl'], 'reason' => 'rate_limit_window_delete_budget_exhausted');
	}
	if ($kind === 'status_page') {
		return array('key' => 'status_pages', 'budget' => $budgets ['status_pages'], 'ttl' => $budgets ['status_pages_ttl'], 'reason' => 'rate_limit_window_status_page_budget_exhausted');
	}
	return array('key' => '', 'budget' => 0, 'ttl' => 0, 'reason' => '');
}

/**
 * Normalize one persistent rate-limit window entry.
 * @param mixed $entry
 * @param int $now
 * @return array<string, mixed>
 */
function plugin_mastodon_rate_limit_window_entry($entry, $now) {
	$entry = is_array($entry) ? $entry : array();
	$startedAt = isset($entry ['started_at']) && is_numeric($entry ['started_at']) ? (int) $entry ['started_at'] : 0;
	$expiresAt = isset($entry ['expires_at']) && is_numeric($entry ['expires_at']) ? (int) $entry ['expires_at'] : 0;
	$used = isset($entry ['used']) && is_numeric($entry ['used']) ? (int) $entry ['used'] : 0;
	if ($startedAt <= 0 || $expiresAt <= $now || $used < 0) {
		return array();
	}
	return array(
		'started_at' => $startedAt,
		'expires_at' => $expiresAt,
		'used' => $used
	);
}

/**
 * Read the persistent rate-limit windows.
 * @param int|null $now
 * @return array<string, array<string, mixed>>
 */
function plugin_mastodon_rate_limit_window_read($now = null) {
	$now = $now === null ? time() : (int) $now;
	$prestat = plugin_mastodon_file_prestat(PLUGIN_MASTODON_RATE_LIMIT_WINDOW_FILE);
	if (empty($prestat ['exists'])) {
		return array();
	}
	$json = plugin_mastodon_io_read_file_uncached(PLUGIN_MASTODON_RATE_LIMIT_WINDOW_FILE, true);
	if (!is_string($json) || trim($json) === '') {
		return array();
	}
	$data = json_decode($json, true);
	if (!is_array($data)) {
		return array();
	}
	$source = isset($data ['windows']) && is_array($data ['windows']) ? $data ['windows'] : $data;
	$windows = array();
	foreach (array('media_uploads', 'deletes', 'status_pages') as $key) {
		if (isset($source [$key])) {
			$entry = plugin_mastodon_rate_limit_window_entry($source [$key], $now);
			if ($entry !== array()) {
				$windows [$key] = $entry;
			}
		}
	}
	return $windows;
}

/**
 * Persist the current rate-limit windows.
 * @param array<string, array<string, mixed>> $windows
 * @return bool
 */
function plugin_mastodon_rate_limit_window_write($windows) {
	plugin_mastodon_ensure_state_dir();
	$payload = array('version' => 1, 'windows' => array());
	foreach (array('media_uploads', 'deletes', 'status_pages') as $key) {
		if (isset($windows [$key]) && is_array($windows [$key])) {
			$payload ['windows'] [$key] = array(
				'started_at' => isset($windows [$key] ['started_at']) ? (int) $windows [$key] ['started_at'] : 0,
				'expires_at' => isset($windows [$key] ['expires_at']) ? (int) $windows [$key] ['expires_at'] : 0,
				'used' => isset($windows [$key] ['used']) ? (int) $windows [$key] ['used'] : 0
			);
		}
	}
	$json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	if (!is_string($json)) {
		return false;
	}
	return plugin_mastodon_io_write_file(PLUGIN_MASTODON_RATE_LIMIT_WINDOW_FILE, $json . PHP_EOL);
}

/**
 * Reserve one item from a persistent cross-run Mastodon rate-limit window.
 * @param string $kind
 * @param int|null $now
 * @return string Empty string means the request may proceed.
 */
function plugin_mastodon_rate_limit_window_acquire($kind, $now = null) {
	$config = plugin_mastodon_rate_limit_window_config($kind);
	if ($config ['key'] === '' || $config ['budget'] <= 0 || $config ['ttl'] <= 0) {
		return '';
	}
	$now = $now === null ? time() : (int) $now;
	$windows = plugin_mastodon_rate_limit_window_read($now);
	$key = $config ['key'];
	$entry = isset($windows [$key]) ? plugin_mastodon_rate_limit_window_entry($windows [$key], $now) : array();
	if ($entry === array()) {
		$entry = array(
			'started_at' => $now,
			'expires_at' => $now + $config ['ttl'],
			'used' => 0
		);
	}
	if ((int) $entry ['used'] >= (int) $config ['budget']) {
		$GLOBALS ['plugin_mastodon_rate_limit_window_block'] = array(
			'key' => $key,
			'used' => (int) $entry ['used'],
			'budget' => (int) $config ['budget'],
			'expires_at' => (int) $entry ['expires_at']
		);
		return $config ['reason'];
	}
	$entry ['used'] = ((int) $entry ['used']) + 1;
	$windows [$key] = $entry;
	if (!plugin_mastodon_rate_limit_window_write($windows)) {
		$GLOBALS ['plugin_mastodon_rate_limit_window_block'] = array(
			'key' => $key,
			'used' => (int) $entry ['used'],
			'budget' => (int) $config ['budget'],
			'expires_at' => (int) $entry ['expires_at']
		);
		return 'rate_limit_window_write_failed';
	}
	$GLOBALS ['plugin_mastodon_rate_limit_window_last'] = array(
		'key' => $key,
		'used' => (int) $entry ['used'],
		'budget' => (int) $config ['budget'],
		'expires_at' => (int) $entry ['expires_at']
	);
	return '';
}

/**
 * Clear persistent rate-limit windows, mainly for tests and recovery tooling.
 * @return bool
 */
function plugin_mastodon_rate_limit_window_clear() {
	if (@file_exists(PLUGIN_MASTODON_RATE_LIMIT_WINDOW_FILE)) {
		return @unlink(PLUGIN_MASTODON_RATE_LIMIT_WINDOW_FILE);
	}
	return true;
}

/**
 * Start a per-run Mastodon API rate-limit guard.
 * @param string $scope
 * @return void
 */
function plugin_mastodon_rate_limit_guard_start($scope) {
	unset($GLOBALS ['plugin_mastodon_rate_limit_window_block']);
	unset($GLOBALS ['plugin_mastodon_rate_limit_window_last']);
	$budgets = plugin_mastodon_rate_limit_default_budgets();
	$GLOBALS ['plugin_mastodon_rate_limit_guard'] = array(
		'active' => true,
		'scope' => trim((string) $scope),
		'requests_budget' => $budgets ['requests'],
		'requests_used' => 0,
		'media_uploads_budget' => $budgets ['media_uploads'],
		'media_uploads_used' => 0,
		'deletes_budget' => $budgets ['deletes'],
		'deletes_used' => 0,
		'remote_remaining_floor' => $budgets ['remote_remaining_floor'],
		'remote_limit' => null,
		'remote_remaining' => null,
		'remote_reset' => '',
		'blocked_reason' => '',
		'blocked_logged' => false
	);
}

/**
 * Stop the current per-run Mastodon API rate-limit guard while keeping its summary inspectable.
 * @return void
 */
function plugin_mastodon_rate_limit_guard_stop() {
	if (!isset($GLOBALS ['plugin_mastodon_rate_limit_guard']) || !is_array($GLOBALS ['plugin_mastodon_rate_limit_guard'])) {
		return;
	}
	$GLOBALS ['plugin_mastodon_rate_limit_guard'] ['active'] = false;
}

/**
 * Return whether a per-run Mastodon API rate-limit guard is active.
 * @return bool
 */
function plugin_mastodon_rate_limit_guard_active() {
	return isset($GLOBALS ['plugin_mastodon_rate_limit_guard']) && is_array($GLOBALS ['plugin_mastodon_rate_limit_guard']) && !empty($GLOBALS ['plugin_mastodon_rate_limit_guard'] ['active']);
}

/**
 * Return the current rate-limit guard summary.
 * @return array<string, mixed>
 */
function plugin_mastodon_rate_limit_guard_summary() {
	return isset($GLOBALS ['plugin_mastodon_rate_limit_guard']) && is_array($GLOBALS ['plugin_mastodon_rate_limit_guard']) ? $GLOBALS ['plugin_mastodon_rate_limit_guard'] : array();
}

/**
 * Normalize a response header map for rate-limit checks.
 * @param mixed $headers
 * @return array<string, string>
 */
function plugin_mastodon_rate_limit_headers($headers) {
	$normalized = array();
	if (!is_array($headers)) {
		return $normalized;
	}
	foreach ($headers as $name => $value) {
		$name = strtolower(trim((string) $name));
		if ($name === '') {
			continue;
		}
		$normalized [$name] = trim((string) $value);
	}
	return $normalized;
}

/**
 * Determine whether a Mastodon API request consumes one of the stricter per-run budgets.
 * @param string $method
 * @param string $target
 * @return string
 */
function plugin_mastodon_rate_limit_request_kind($method, $target) {
	$method = strtoupper(trim((string) $method));
	$target = (string) $target;
	$path = $target;
	if (preg_match('#^https?://#i', $path)) {
		$parts = parse_url($path);
		$path = isset($parts ['path']) ? (string) $parts ['path'] : '';
	}
	if ($method === 'POST' && preg_match('#/api/v[12]/media/?$#', $path)) {
		return 'media_upload';
	}
	if (($method === 'DELETE' && preg_match('#/api/v1/statuses/[^/?]+#', $path)) || ($method === 'POST' && preg_match('#/api/v1/statuses/[^/?]+/unreblog#', $path))) {
		return 'status_delete';
	}
	if ($method === 'GET' && preg_match('#/api/v1/accounts/[^/]+/statuses/?$#', $path)) {
		return 'status_page';
	}
	return 'request';
}

/**
 * Mark the current rate-limit guard as blocked.
 * @param string $reason
 * @return string
 */
function plugin_mastodon_rate_limit_block($reason) {
	$reason = trim((string) $reason);
	if ($reason === '') {
		$reason = 'rate_limit_budget_exhausted';
	}
	if (isset($GLOBALS ['plugin_mastodon_rate_limit_guard']) && is_array($GLOBALS ['plugin_mastodon_rate_limit_guard'])) {
		$GLOBALS ['plugin_mastodon_rate_limit_guard'] ['blocked_reason'] = $reason;
		if (empty($GLOBALS ['plugin_mastodon_rate_limit_guard'] ['blocked_logged'])) {
			$summary = plugin_mastodon_rate_limit_guard_summary();
			$message = 'Mastodon rate-limit guard stopped synchronization: ' . $reason;
			if (!empty($summary)) {
				$message .= ' (scope=' . (isset($summary ['scope']) ? (string) $summary ['scope'] : 'unknown')
					. ', requests=' . (isset($summary ['requests_used']) ? (int) $summary ['requests_used'] : 0) . '/' . (isset($summary ['requests_budget']) ? (int) $summary ['requests_budget'] : 0)
					. ', media_uploads=' . (isset($summary ['media_uploads_used']) ? (int) $summary ['media_uploads_used'] : 0) . '/' . (isset($summary ['media_uploads_budget']) ? (int) $summary ['media_uploads_budget'] : 0)
					. ', deletes=' . (isset($summary ['deletes_used']) ? (int) $summary ['deletes_used'] : 0) . '/' . (isset($summary ['deletes_budget']) ? (int) $summary ['deletes_budget'] : 0);
				if (isset($summary ['remote_remaining'])) {
					$message .= ', remote_remaining=' . (int) $summary ['remote_remaining'];
				}
				if (!empty($summary ['remote_reset'])) {
					$message .= ', remote_reset=' . (string) $summary ['remote_reset'];
				}
				if (isset($GLOBALS ['plugin_mastodon_rate_limit_window_block']) && is_array($GLOBALS ['plugin_mastodon_rate_limit_window_block']) && !empty($GLOBALS ['plugin_mastodon_rate_limit_window_block'] ['key'])) {
					$window = $GLOBALS ['plugin_mastodon_rate_limit_window_block'];
					$message .= ', window=' . (string) $window ['key']
						. ' ' . (isset($window ['used']) ? (int) $window ['used'] : 0) . '/' . (isset($window ['budget']) ? (int) $window ['budget'] : 0);
					if (!empty($window ['expires_at'])) {
						$message .= ' reset=' . gmdate('Y-m-d H:i:s', (int) $window ['expires_at']) . ' UTC';
					}
				}
				$message .= ')';
			}
			plugin_mastodon_log($message);
			$GLOBALS ['plugin_mastodon_rate_limit_guard'] ['blocked_logged'] = true;
		}
	}
	return $reason;
}

/**
 * Reserve budget for one Mastodon API request.
 * @param string $method
 * @param string $target
 * @return string Empty string means the request may proceed.
 */
function plugin_mastodon_rate_limit_acquire($method, $target) {
	if (!plugin_mastodon_rate_limit_guard_active()) {
		return '';
	}
	$guard = &$GLOBALS ['plugin_mastodon_rate_limit_guard'];
	$kind = plugin_mastodon_rate_limit_request_kind($method, $target);
	if (!empty($guard ['blocked_reason'])) {
		$blockedReason = (string) $guard ['blocked_reason'];
		if (!(($blockedReason === 'rate_limit_media_upload_budget_exhausted' && $kind !== 'media_upload') || ($blockedReason === 'rate_limit_window_media_upload_budget_exhausted' && $kind !== 'media_upload') || ($blockedReason === 'rate_limit_delete_budget_exhausted' && $kind !== 'status_delete') || ($blockedReason === 'rate_limit_window_delete_budget_exhausted' && $kind !== 'status_delete') || ($blockedReason === 'rate_limit_window_status_page_budget_exhausted' && $kind !== 'status_page'))) {
			return $blockedReason;
		}
	}
	$remaining = isset($guard ['remote_remaining']) && is_numeric($guard ['remote_remaining']) ? (int) $guard ['remote_remaining'] : null;
	$floor = isset($guard ['remote_remaining_floor']) ? max(0, (int) $guard ['remote_remaining_floor']) : 0;
	if ($remaining !== null && $remaining <= $floor) {
		return plugin_mastodon_rate_limit_block('rate_limit_remote_remaining_low');
	}
	if (isset($guard ['requests_budget']) && (int) $guard ['requests_budget'] > 0 && (int) $guard ['requests_used'] >= (int) $guard ['requests_budget']) {
		return plugin_mastodon_rate_limit_block('rate_limit_request_budget_exhausted');
	}
	if ($kind === 'media_upload' && isset($guard ['media_uploads_budget']) && (int) $guard ['media_uploads_budget'] > 0 && (int) $guard ['media_uploads_used'] >= (int) $guard ['media_uploads_budget']) {
		return plugin_mastodon_rate_limit_block('rate_limit_media_upload_budget_exhausted');
	}
	if ($kind === 'status_delete' && isset($guard ['deletes_budget']) && (int) $guard ['deletes_budget'] > 0 && (int) $guard ['deletes_used'] >= (int) $guard ['deletes_budget']) {
		return plugin_mastodon_rate_limit_block('rate_limit_delete_budget_exhausted');
	}
	$persistentReason = plugin_mastodon_rate_limit_window_acquire($kind);
	if ($persistentReason !== '') {
		return plugin_mastodon_rate_limit_block($persistentReason);
	}
	$guard ['requests_used'] = isset($guard ['requests_used']) ? ((int) $guard ['requests_used']) + 1 : 1;
	if ($kind === 'media_upload') {
		$guard ['media_uploads_used'] = isset($guard ['media_uploads_used']) ? ((int) $guard ['media_uploads_used']) + 1 : 1;
	}
	if ($kind === 'status_delete') {
		$guard ['deletes_used'] = isset($guard ['deletes_used']) ? ((int) $guard ['deletes_used']) + 1 : 1;
	}
	return '';
}

/**
 * Update the current per-run guard from Mastodon rate-limit response headers.
 * @param array<string, mixed> $response
 * @return void
 */
function plugin_mastodon_rate_limit_observe_response($response) {
	if (!plugin_mastodon_rate_limit_guard_active()) {
		return;
	}
	$response = is_array($response) ? $response : array();
	$headers = plugin_mastodon_rate_limit_headers(isset($response ['headers']) ? $response ['headers'] : array());
	if (isset($headers ['x-ratelimit-limit']) && is_numeric($headers ['x-ratelimit-limit'])) {
		$GLOBALS ['plugin_mastodon_rate_limit_guard'] ['remote_limit'] = (int) $headers ['x-ratelimit-limit'];
	}
	if (isset($headers ['x-ratelimit-remaining']) && is_numeric($headers ['x-ratelimit-remaining'])) {
		$GLOBALS ['plugin_mastodon_rate_limit_guard'] ['remote_remaining'] = (int) $headers ['x-ratelimit-remaining'];
	}
	if (isset($headers ['x-ratelimit-reset'])) {
		$GLOBALS ['plugin_mastodon_rate_limit_guard'] ['remote_reset'] = (string) $headers ['x-ratelimit-reset'];
	}
	if ((isset($response ['code']) ? (int) $response ['code'] : 0) === 429) {
		plugin_mastodon_rate_limit_block('rate_limit_remote_exhausted');
	}
}

/**
 * Return the current rate-limit block reason, if any.
 * @return string
 */
function plugin_mastodon_rate_limit_blocked_reason() {
	if (!isset($GLOBALS ['plugin_mastodon_rate_limit_guard']) || !is_array($GLOBALS ['plugin_mastodon_rate_limit_guard'])) {
		return '';
	}
	return !empty($GLOBALS ['plugin_mastodon_rate_limit_guard'] ['blocked_reason']) ? (string) $GLOBALS ['plugin_mastodon_rate_limit_guard'] ['blocked_reason'] : '';
}

/**
 * Build a synthetic API response for locally blocked Mastodon requests.
 * @param string $reason
 * @return array<string, mixed>
 */
function plugin_mastodon_rate_limit_blocked_response($reason) {
	$reason = plugin_mastodon_rate_limit_block($reason);
	$body = json_encode(array('error' => $reason));
	return array(
		'ok' => false,
		'code' => 429,
		'headers' => array(),
		'body' => is_string($body) ? $body : '',
		'error' => $reason
	);
}

/**
 * Return the rate-limit reason that should be written to sync state, if any.
 * @return string
 * @phpstan-impure
 */
function plugin_mastodon_rate_limit_state_error() {
	$reason = plugin_mastodon_rate_limit_blocked_reason();
	return $reason !== '' ? $reason : '';
}

/**
 * Read a cheap file metadata snapshot for cache validation.
 * @param string $path
 * @return array{exists:bool,mt:int|null,sz:int|null}
 */
function plugin_mastodon_file_prestat($path) {
	$path = (string) $path;
	clearstatcache(true, $path);
	if (!@file_exists($path)) {
		return array('exists' => false, 'mt' => null, 'sz' => null);
	}
	$mtime = @filemtime($path);
	$size = @filesize($path);
	return array(
		'exists' => true,
		'mt' => ($mtime === false ? null : (int) $mtime),
		'sz' => ($size === false ? null : (int) $size)
	);
}

/**
 * Convert a file metadata snapshot into a stable cache signature.
 * @param array{exists:bool,mt:int|null,sz:int|null} $prestat
 * @return string
 */
function plugin_mastodon_file_prestat_signature($prestat) {
	if (empty($prestat ['exists'])) {
		return 'missing';
	}
	return (isset($prestat ['mt']) ? (string) $prestat ['mt'] : 'na') . ':' . (isset($prestat ['sz']) ? (string) $prestat ['sz'] : 'na');
}

/**
 * Append a line to the plugin sync log.
 * @param string $message
 * @return void
 */
function plugin_mastodon_log($message) {
	plugin_mastodon_ensure_state_dir();
	$line = '[' . gmdate('Y-m-d H:i:s') . ' UTC] ' . trim((string) $message) . PHP_EOL;
	plugin_mastodon_io_append_file(PLUGIN_MASTODON_LOG_FILE, $line);
}

/**
 * Aggregate high-volume skip messages until the current sync phase ends.
 * @param string $bucket
 * @param string $singular
 * @param string $plural
 * @param string $itemId
 * @param string $reason
 * @return void
 */
function plugin_mastodon_log_skip($bucket, $singular, $plural, $itemId, $reason) {
	$key = (string) $bucket;
	if ($key === '') {
		plugin_mastodon_log('Skipping ' . (string) $singular . ' ' . (string) $itemId . ' ' . (string) $reason);
		return;
	}
	if (!isset($GLOBALS ['plugin_mastodon_skip_log_summaries']) || !is_array($GLOBALS ['plugin_mastodon_skip_log_summaries'])) {
		$GLOBALS ['plugin_mastodon_skip_log_summaries'] = array();
	}
	if (!isset($GLOBALS ['plugin_mastodon_skip_log_summaries'] [$key]) || !is_array($GLOBALS ['plugin_mastodon_skip_log_summaries'] [$key])) {
		$GLOBALS ['plugin_mastodon_skip_log_summaries'] [$key] = array(
			'count' => 0,
			'singular' => (string) $singular,
			'plural' => (string) $plural,
			'first' => '',
			'last' => '',
			'reason' => trim((string) $reason)
		);
	}
	$GLOBALS ['plugin_mastodon_skip_log_summaries'] [$key] ['count'] = (int) $GLOBALS ['plugin_mastodon_skip_log_summaries'] [$key] ['count'] + 1;
	if ((string) $GLOBALS ['plugin_mastodon_skip_log_summaries'] [$key] ['first'] === '') {
		$GLOBALS ['plugin_mastodon_skip_log_summaries'] [$key] ['first'] = (string) $itemId;
	}
	$GLOBALS ['plugin_mastodon_skip_log_summaries'] [$key] ['last'] = (string) $itemId;
}

/**
 * Flush aggregated skip log messages.
 * @return void
 */
function plugin_mastodon_log_flush_skip_summaries() {
	if (!isset($GLOBALS ['plugin_mastodon_skip_log_summaries']) || !is_array($GLOBALS ['plugin_mastodon_skip_log_summaries'])) {
		return;
	}
	$summaries = $GLOBALS ['plugin_mastodon_skip_log_summaries'];
	$GLOBALS ['plugin_mastodon_skip_log_summaries'] = array();
	foreach ($summaries as $summary) {
		if (!is_array($summary)) {
			continue;
		}
		$count = isset($summary ['count']) ? (int) $summary ['count'] : 0;
		if ($count <= 0) {
			continue;
		}
		$label = $count === 1 ? (string) $summary ['singular'] : (string) $summary ['plural'];
		$reason = trim(isset($summary ['reason']) ? (string) $summary ['reason'] : '');
		$first = isset($summary ['first']) ? (string) $summary ['first'] : '';
		$last = isset($summary ['last']) ? (string) $summary ['last'] : '';
		$range = '';
		if ($first !== '') {
			$range = $first === $last ? ' (item: ' . $first . ')' : ' (first: ' . $first . ', last: ' . $last . ')';
		}
		plugin_mastodon_log('Skipped ' . $count . ' ' . $label . ($reason !== '' ? ' ' . $reason : '') . $range);
	}
}

/**
 * Best-effort refresh/increase of the PHP execution time budget for long-running Mastodon work.
 *
 * The helper never lowers an existing higher or unlimited execution limit. It primarily protects
 * shared FlatPress web requests from hitting the default 30-second PHP timeout on platforms where
 * real elapsed wall time is counted (notably Windows/IIS) while remaining a no-op on hosts that
 * disallow runtime timeout changes.
 *
 * @param int $minimumSeconds
 * @return int
 */
function plugin_mastodon_extend_time_limit($minimumSeconds = 60) {
	$minimumSeconds = max(0, (int) $minimumSeconds);
	$currentValue = @ini_get('max_execution_time');
	$currentLimit = is_numeric($currentValue) ? (int) $currentValue : 0;
	$targetLimit = $minimumSeconds;
	if ($currentLimit === 0) {
		$targetLimit = 0;
	} elseif ($currentLimit > $targetLimit) {
		$targetLimit = $currentLimit;
	}
	if (isset($GLOBALS ['plugin_mastodon_test_timeout_calls']) && is_array($GLOBALS ['plugin_mastodon_test_timeout_calls'])) {
		$GLOBALS ['plugin_mastodon_test_timeout_calls'] [] = array(
			'minimum' => $minimumSeconds,
			'current' => $currentLimit,
			'target' => $targetLimit
		);
	}
	if (function_exists('set_time_limit')) {
		@set_time_limit($targetLimit);
	}
	if (function_exists('ini_set')) {
		@ini_set('max_execution_time', (string) $targetLimit);
	}
	return $targetLimit;
}
?>
