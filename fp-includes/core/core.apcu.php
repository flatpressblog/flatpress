<?php
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
?>
