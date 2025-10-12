<?php

// fileio.php
// low-level io-handling functions

/**
 * Atomic write to a temp file in the same directory, then rename in place.
 * Readers see old or new version, never partial.
 * @return bool
 */
function io_write_file($filename, $data) {
	@umask(0);
	$dir = dirname($filename);
	if (!fs_mkdir($dir)) {
		return false;
	}
	$tmp = $dir . '/.' . basename($filename) . '.' . bin2hex(random_bytes(6)) . '.tmp';
	$f = @fopen($tmp, 'wb');
	if (!$f) {
		return false;
	}
	stream_set_write_buffer($f, 0);
	$len = strlen($data);
	$w = fwrite($f, $data);
	$ok = ($w === $len) && fflush($f);
	fclose($f);
	if (!$ok) {
		@unlink($tmp);
		return false;
	}
	if (!@rename($tmp, $filename)) {
		@unlink($tmp);
		return false;
	}
	@chmod($filename, FILE_PERMISSIONS);
	return true;
}

/**
 * APCu availability for this request. CLI/phpdbg -> false, except apc.enable_cli=1.
 */
function is_apcu_on(): bool {
	static $on = null;
	if ($on !== null) {
		return $on;
	}
	if (!function_exists('apcu_fetch')) {
		return $on = false;
	}
	if (function_exists('apcu_enabled')) {
		$on = @apcu_enabled();
	} else {
		$on = (bool) @ini_get('apc.enabled');
	}
	if ($on && in_array(PHP_SAPI, ['cli', 'phpdbg'], true) && !((bool) @ini_get('apc.enable_cli'))) {
		$on = false;
	}
	return $on;
}

/**
 * Fetch a value from APCu. Sets $ok=true on hit; returns null if APCu is off.
 * @param string $key
 * @param bool $ok
 * @return mixed|null
 */
function apcu_get($key, &$ok) {
	$ok = false;
	if (!is_apcu_on()) {
		return null;
	}
	return apcu_fetch((string) $key, $ok);
}

/**
 * Store a value in APCu. TTL=0 means no expiry; no-op if APCu is off.
 * @param string $key
 * @param mixed $val
 * @param int $ttl
 */
function apcu_set($key, $val, $ttl = 120) {
	if (!is_apcu_on()) {
		return false;
	}
	$ttl = (int) $ttl;
	if ($ttl < 0) {
		$ttl = 0;
	}
	return apcu_store((string) $key, $val, $ttl);
}

function io_load_file_uncached($filename) {
	if (file_exists($filename)) {
		if (function_exists('file_get_contents')) {
			return file_get_contents($filename);
		}

		$f = fopen($filename, "r");
		if ($f) {
			if (!flock($f, LOCK_SH)) {
				return -1;
			}
			$contents = fread($f, filesize($filename));
			flock($f, LOCK_UN);
			fclose($f);

			// returns contents as string on success
			return ($contents);
		}
	}
	// trigger_error("io_load_file: $filename does not exists", E_USER_ERROR);
	return false;
}

/**
 * Cached file read for current request. Optional APCu hotcache.
 * Always falls back cleanly to io_load_file_uncached().
 */
function io_load_file($filename) {
	static $cache = array();
	static $meta = array();

	clearstatcache(true, $filename);

	$exists = @file_exists($filename);
	$mt = $exists ? @filemtime($filename) : false;
	$sz = $exists ? (int) @filesize($filename) : 0;
	$sig = ($mt !== false ? $mt : 'na') . ':' . $sz;

	if (isset($cache [$filename]) && isset($meta [$filename]) && $meta [$filename] === $sig) {
		return $cache [$filename];
	}

	// Check APCu securely and host-agnostically
	$apcu_on = is_apcu_on();

	if ($apcu_on) {
		$mt = @filemtime($filename);
		if ($mt !== false) {
			// Stabilizes collisions < 1s
			$sz = (int) @filesize($filename);
			$key = 'fp:io:' . $filename . ':' . $mt . ':' . $sz;

			$hit = false;
			$val = apcu_fetch($key, $hit);
			if ($hit) {
				$cache [$filename] = $val;
				$meta [$filename] = $sig;
				return $val;
			}

			$val = io_load_file_uncached($filename);
			if ($val !== false && $val !== null) {
				/**
				 * Load scenario: 3,000 posts × 5 comments (see FlatPress Bulk Content Generator).
				 * Search (search.php) touches most entries and dominates memory.
				 * Peak calculation:
				 *   fp:io:*
				 *      3,000 × ~1.93 KiB + 15,000 × ~0.52 KiB ≈ 13.3 MiB
				 *   fp:entry:parsed:* (TTL 600 s, e.g. after search)
				 *      3,000 × ~1.98 KiB ≈ 6.0 MiB
				 *   Plugins/Smarty/other: ~2–3 MiB
				 * Worst-case simultaneously ≈ 21–23 MiB < 32 MiB; headroom ≈ 9–11 MiB covers fragmentation.
				 * Note: APCu is a shared pool per FPM pool (apc.shm_size), not per child process.
				 */
				$ttl = max(0, (int) ($_ENV ['FP_APCU_IO_TTL'] ?? 7200)); // Removed from cache after 2 hours
				$max = max(0, (int) ($_ENV ['FP_APCU_IO_MAX_BYTES'] ?? 32768)); // 32 KiB - prevents fat items
				// TTL unnecessary, key changes with mtime/size
				if (strlen($val) <= $max) {
					apcu_store($key, $val, $ttl);
				}
				$cache [$filename] = $val;
				$meta [$filename] = $sig;
			}
			return $val;
		}
	}

	$contents = io_load_file_uncached($filename);
	if ($contents !== false && $contents !== null) {
		$cache [$filename] = $contents;
		$meta [$filename] = $sig;
	}
	return $contents;
}

function io_delete_file($filename) {
	if (!file_exists($filename)) {
		return false;
	}
	return @unlink($filename);
}
?>
