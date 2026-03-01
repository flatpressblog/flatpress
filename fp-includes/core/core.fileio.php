<?php

// fileio.php
// low-level io-handling functions

/**
 * Atomic write to a temp file in the same directory, then rename in place.
 * Readers see old or new version, never partial.
 * @return bool
 */
function io_write_file($filename, $data, $options = []) {
	$oldUmask = @umask(0);
	$dir = dirname($filename);

	$options = is_array($options) ? $options : [];
	$doFsync = !empty($options ['fsync']);
	$invalidateOpcache = !empty($options ['invalidate_opcache']);

	if (!fs_mkdir($dir)) {
		@umask($oldUmask);
		return false;
	}

	$tmp = $dir . '/.' . basename($filename) . '.' . bin2hex(random_bytes(6)) . '.tmp';
	$f = @fopen($tmp, 'wb');
	if (!$f) {
		@umask($oldUmask);
		return false;
	}
	stream_set_write_buffer($f, 0);

	$len = strlen($data); $pos = 0;
	while ($pos < $len) {
		$n = fwrite($f, substr($data, $pos));
		if ($n === false) {
			fclose($f); 
			@unlink($tmp);
			@umask($oldUmask);
			return false;
		}
		$pos += $n;
	}
	$ok = fflush($f);
	if ($ok && $doFsync && function_exists('fsync')) {
		@fsync($f);
	}
	fclose($f);
	if (!$ok) {
		@unlink($tmp);
		@umask($oldUmask);
		return false;
	}

	$mv = @rename($tmp, $filename);
	if (!$mv && is_file($tmp)) {
		@unlink($filename);
		$mv = @rename($tmp, $filename) ?: (@copy($tmp, $filename) && @unlink($tmp));
	}
	if (!$mv) {
		@unlink($tmp);
		@umask($oldUmask);
		return false;
	}

	@chmod($filename, FILE_PERMISSIONS);
	if ($invalidateOpcache && function_exists('opcache_invalidate')) {
		@opcache_invalidate($filename, true);
	}
	@umask($oldUmask);
	return true;
}

function io_load_file_uncached($filename, $assume_exists = false) {
	if (!$assume_exists && !file_exists($filename)) {
		return false;
	}

	if (function_exists('set_time_limit')) {
		@set_time_limit(0);
	}
	@ini_set('max_execution_time', '0');

	if (function_exists('file_get_contents')) {
		// Suppress warnings to keep callers warning-free (consistent with prior file_exists guard).
		return @file_get_contents($filename);
	}

	$f = fopen($filename, "r");
	if ($f) {
		if (!flock($f, LOCK_SH)) {
			return -1;
		}
		$contents = fread($f, filesize($filename));
		flock($f, LOCK_UN);
		fclose($f);
		// Returns contents as string on success
		return ($contents);
	}

	// trigger_error("io_load_file: $filename does not exists", E_USER_ERROR);
	return false;
}

/**
 * Cached file read for current request. Optional APCu hotcache.
 * Always falls back cleanly to io_load_file_uncached().
 */
function io_load_file($filename, $prestat = null) {
	static $cache = array();
	static $meta = array();

	$exists = null;
	$mt = null;
	$sz = null;

	if (is_array($prestat)) {
		$exists = (bool) ($prestat ['exists'] ?? true);
		$mt = $prestat ['mt'] ?? null;
		$sz = $prestat ['sz'] ?? null;
	} else {
		clearstatcache(true, $filename);
		$exists = @file_exists($filename);
	}

	if (!$exists) {
		return false;
	}

	// Stat only if needed. Many hot paths (entry_parse) already have a signature.
	if ($mt === null) {
		$mt = @filemtime($filename);
	}
	if ($sz === null) {
		$sz = ($mt !== false) ? (int) @filesize($filename) : 0;
	}

	$sig = ($mt !== false ? $mt : 'na') . ':' . $sz;

	if (isset($cache [$filename]) && isset($meta [$filename]) && $meta [$filename] === $sig) {
		return $cache [$filename];
	}

	// Check APCu securely and host-agnostically
	$apcu_on = is_apcu_on();

	if ($apcu_on && $mt !== false) {
		// Stabilizes collisions < 1s
		$key = 'fp:io:' . $filename . ':' . $mt . ':' . $sz;

		$hit = false;
		$val = apcu_get($key, $hit);
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
				apcu_set($key, $val, $ttl);
			}
			$cache [$filename] = $val;
			$meta [$filename] = $sig;
		}
		return $val;
	}

	$contents = io_load_file_uncached($filename, true);
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
