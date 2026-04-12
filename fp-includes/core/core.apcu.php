<?php
/**
 * Returns whether an environment flag is truthy.
 *
 * Accepted truthy values: 1, true, on, yes.
 *
 * @param string $name
 * @param bool $default
 * @return bool
 */
function fp_apcu_env_flag($name, $default = false): bool {
	$value = getenv((string) $name);
	if ($value === false && isset($_ENV [(string) $name])) {
		$value = $_ENV [(string) $name];
	}
	if ($value === false || $value === null) {
		return (bool) $default;
	}
	if (is_bool($value)) {
		return $value;
	}
	if (is_int($value) || is_float($value)) {
		return ((int) $value) !== 0;
	}
	if (is_string($value)) {
		$value = strtolower(trim($value));
		if ($value === '' || $value === '0' || $value === 'false' || $value === 'off' || $value === 'no') {
			return false;
		}
		if ($value === '1' || $value === 'true' || $value === 'on' || $value === 'yes') {
			return true;
		}
	}
	return (bool) $default;
}

/**
 * Returns the FlatPress APCu namespace ID for this instance, or '' if APCu is disabled.
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
 *
 * @param string $key
 * @param int $step
 * @param bool $success
 * @return int|false
 */
function apcu_incr($key, $step = 1, &$success = null) {
	if (!is_apcu_on()) {
		if ($success !== null) {
			$success = false;
		}
		return false;
	}
	return apcu_inc(apcu_key((string) $key), (int) $step, $success);
}

/**
 * Delete a value from APCu with instance prefix.
 *
 * @param string $key
 * @return bool
 */
function apcu_delete_key($key): bool {
	if (!is_apcu_on()) {
		return false;
	}
	return (bool) apcu_delete(apcu_key((string) $key));
}

/**
 * APCu availability for this request.
 *
 * In CLI/phpdbg, APCu is considered off unless one of these is true:
 *   - the runtime enables apc.enable_cli
 *   - FP_APCU_ENABLE_CLI=1 is present in the environment
 *
 * The environment override exists so FlatPress can simulate APCu-backed code
 * paths in automated CLI tests even on systems without APCu CLI support.
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
	if ($on && in_array(PHP_SAPI, ['cli', 'phpdbg'], true) && !((bool) @ini_get('apc.enable_cli')) && !fp_apcu_env_flag('FP_APCU_ENABLE_CLI', false)) {
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
 *
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
	return apcu_fetch(apcu_key((string) $key));
}

/**
 * Store a value in APCu. TTL=0 means no expiry; no-op if APCu is off.
 *
 * @param string $key
 * @param mixed $val
 * @param int $ttl
 * @return bool
 */
function apcu_set($key, $val, $ttl = 120): bool {
	if (!is_apcu_on()) {
		return false;
	}
	$ttl = (int) $ttl;
	if ($ttl < 0) {
		$ttl = 0;
	}
	return (bool) apcu_store(apcu_key((string) $key), $val, $ttl);
}
?>
