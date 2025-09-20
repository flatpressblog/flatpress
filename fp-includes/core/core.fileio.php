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
	$apcu_on = function_exists('apcu_enabled') ? apcu_enabled() : (function_exists('apcu_fetch') && (bool) ini_get('apcu.enabled') || (bool) ini_get('apc.enabled'));

	// CLI usually off; only allow if explicitly enabled
	if ($apcu_on && PHP_SAPI === 'cli' && !((bool) ini_get('apc.enable_cli'))) {
		$apcu_on = false;
	}

	if ($apcu_on) {
		$mt = @filemtime($filename);
		if ($mt !== false) {
			// Stabilizes collisions <1s
			$sz  = (int) @filesize($filename);
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
				// TTL unnecessary, key changes with mtime/size
				apcu_store($key, $val);
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
