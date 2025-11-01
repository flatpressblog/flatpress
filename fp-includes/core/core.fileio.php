<?php

// fileio.php
// low-level io-handling functions

/**
 * Atomic write to a temp file in the same directory, then rename in place.
 * Readers see old or new version, never partial.
 * @return bool
 */
function io_write_file($filename, $data) {
	$oldUmask = @umask(0);
	$dir = dirname($filename);
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
	@umask($oldUmask);
	return true;
}

/**
 * Returns the FlatPress APCu namespace ID for this instance, or “” if APCu is disabled.
 * Random, stored under deterministic bootstrap key (sha1(ABS_PATH)).
 */
function apcu_ns(): string {
	static $ns = null;
	if ($ns !== null) {
		return $ns;
	}
	if (!is_apcu_on()) {
		return $ns = '';
	}
	$base = defined('ABS_PATH') ? (string) ABS_PATH : (string) __DIR__;
	$bootstrapKey = 'fp:ns:' . sha1($base);
	$hit = false;
	$id = apcu_fetch($bootstrapKey, $hit);
	if (!$hit || !is_string($id) || !preg_match('/^[0-9a-f]{16,64}$/', $id)) {
		try {
			$id = bin2hex(random_bytes(12));
		} catch (\Throwable $e) {
			$id = bin2hex(openssl_random_pseudo_bytes(12));
		}
		@apcu_store($bootstrapKey, $id, 0);
	}
	if (!defined('FP_APCU_NS')) {
		@define('FP_APCU_NS', $id);
	}
	$GLOBALS ['FP_APCU_NS'] = $id;
	return $ns = $id;
}

/**
 * Builds an instance-prefixed APCu key: fp:<ID>:<key>
 */
function apcu_key($key): string {
	$ns = apcu_ns();
	if ($ns === '') {
		return (string) $key;
	}
	return 'fp:' . $ns . ':' . (string) $key;
}

/**
 * Increment with instance prefix.
 */
function apcu_incr($key, $step = 1, &$success = null) {
	if (!is_apcu_on()) {
		if ($success !== null) {
			$success = false;
		}
		return false;
	}
	return apcu_inc(apcu_key((string)$key), (int)$step, $success);
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
	if ($on) {
		apcu_ns();
	}
	return $on;
}

/**
 * Fetch from APCu with instance prefix.
 * 2-Arg form: sets $ok=true on hit; 1-Arg form: same as apcu_fetch($key).
 * @param string $key
 * @param bool $ok
 * @return mixed|null
 */
function apcu_get($key, &$ok = null) {
	if (!is_apcu_on()) {
		if ($ok !== null) {
			$ok = false;
			return null;
		}
		return false;
	}
	if ($ok !== null) {
		return apcu_fetch(apcu_key((string) $key), $ok);
	}
	// One-Arg Form
	return apcu_fetch(apcu_key((string) $key));
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
	return apcu_store(apcu_key((string) $key), $val, $ttl);
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
