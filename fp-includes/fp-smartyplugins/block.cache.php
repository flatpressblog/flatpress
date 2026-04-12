<?php
/**
 * Smarty {cache}{/cache} block plugin
 *
 * Purpose:
 *   Cache rendered block output even when global Smarty output caching is
 *   disabled. When APCu is available, FlatPress stores fragment payloads in
 *   RAM first. Otherwise it falls back to CACHE_DIR on the filesystem.
 *
 * Supported attributes:
 *   - id / key      Optional explicit cache key. Recommended for repeated
 *                   or conditionally rendered cache blocks.
 *   - ttl / lifetime
 *                   Lifetime in seconds. Default: 3600. Values <= 0 mean
 *                   "no time-based expiry" (the cache is still invalidated
 *                   when the template source changes).
 *   - enabled       Boolean-ish flag. If false, the block is rendered live.
 *   - vary          Optional extra value that becomes part of the cache key.
 *   - group         Optional logical namespace for the cache file path.
 *   - vary_login / vary_logged_in
 *                   Boolean-ish flag. Default: true. Set to false for
 *                   fragments that are identical for guests and admins.
 *   - vary_request / vary_route
 *                   Boolean-ish flag. Default: false. Set to true for
 *                   fragments whose output depends on the current request
 *                   path or query string, e.g. filtered feeds.
 *
 * Notes:
 *   - Anonymous blocks are supported. Their cache key is derived from the
 *     current template plus a per-render slot number. For repeated or
 *     conditionally rendered blocks, use id=... to guarantee a stable key.
 *   - The cache varies automatically by current FlatPress locale.
 *   - By default it also varies by login state to avoid leaking admin-only
 *     output. This can be disabled per block via vary_login=false (or
 *     vary_logged_in=false) for truly public fragments.
 *   - Request-based variance can be enabled per block via
 *     vary_request=true (or vary_route=true).
 *   - Storage backend selection is automatic: APCu when available,
 *     filesystem fallback otherwise.
 */

/**
 * @return array<string,mixed>
 */
function &fp_smarty_cache_runtime_store() {
	static $store = [
		'stacks' => [],
		'slots' => [],
		'callback_registered' => [],
	];
	return $store;
}

/**
 * @param mixed $value
 * @param bool $default
 * @return bool
 */
function fp_smarty_cache_bool($value, $default = true) {
	if ($value === null) {
		return (bool) $default;
	}
	if (is_bool($value)) {
		return $value;
	}
	if (is_int($value) || is_float($value)) {
		return ((int) $value) !== 0;
	}
	if (is_string($value)) {
		$v = strtolower(trim($value));
		if ($v === '' || $v === '0' || $v === 'false' || $v === 'off' || $v === 'no') {
			return false;
		}
		if ($v === '1' || $v === 'true' || $v === 'on' || $v === 'yes') {
			return true;
		}
	}
	return (bool) $default;
}

/**
 * @param mixed $value
 * @param int $default
 * @return int
 */
function fp_smarty_cache_ttl($value, $default = 3600) {
	if ($value === null || $value === '') {
		return (int) $default;
	}
	if (is_int($value)) {
		return $value;
	}
	if (is_float($value)) {
		return (int) $value;
	}
	if (is_string($value) && preg_match('/^-?\d+$/', trim($value))) {
		return (int) trim($value);
	}
	return (int) $default;
}

/**
 * @param mixed $value
 * @return string
 */
function fp_smarty_cache_stringify($value) {
	if (is_scalar($value) || $value === null) {
		return (string) $value;
	}
	return serialize($value);
}

/**
 * @param object|null $template
 * @return string
 */
function fp_smarty_cache_template_handle($template) {
	if (!is_object($template)) {
		return 'template:none';
	}
	if (function_exists('spl_object_id')) {
		return 'template:' . (string) spl_object_id($template);
	}
	return 'template:' . spl_object_hash($template);
}

/**
 * @param object|null $template
 * @return void
 */
function fp_smarty_cache_reset_template_state($template) {
	$store = &fp_smarty_cache_runtime_store();
	$key = fp_smarty_cache_template_handle($template);
	unset($store ['slots'] [$key], $store ['stacks'] [$key]);
}

/**
 * @param object|null $template
 * @return void
 */
function fp_smarty_cache_register_render_callbacks($template) {
	if (!is_object($template)) {
		return;
	}
	if (!isset($template->startRenderCallbacks) || !is_array($template->startRenderCallbacks)) {
		return;
	}
	if (!isset($template->endRenderCallbacks) || !is_array($template->endRenderCallbacks)) {
		return;
	}

	$store = &fp_smarty_cache_runtime_store();
	$key = fp_smarty_cache_template_handle($template);
	if (!empty($store ['callback_registered'] [$key])) {
		return;
	}

	$template->startRenderCallbacks [] = static function($tpl): void {
		fp_smarty_cache_reset_template_state($tpl);
	};
	$template->endRenderCallbacks [] = static function($tpl): void {
		fp_smarty_cache_reset_template_state($tpl);
	};

	$store ['callback_registered'] [$key] = true;
}

/**
 * @param object|null $template
 * @return int
 */
function fp_smarty_cache_next_slot($template) {
	$store = &fp_smarty_cache_runtime_store();
	$key = fp_smarty_cache_template_handle($template);
	if (!isset($store ['slots'] [$key])) {
		$store ['slots'] [$key] = 0;
	}
	$store ['slots'] [$key]++;
	return (int) $store ['slots'] [$key];
}

/**
 * @param object|null $template
 * @param array<string,mixed> $state
 * @return void
 */
function fp_smarty_cache_push_state($template, array $state) {
	$store = &fp_smarty_cache_runtime_store();
	$key = fp_smarty_cache_template_handle($template);
	if (!isset($store ['stacks'] [$key]) || !is_array($store ['stacks'] [$key])) {
		$store ['stacks'] [$key] = [];
	}
	$store ['stacks'] [$key] [] = $state;
}

/**
 * @param object|null $template
 * @return array<string,mixed>|null
 */
function fp_smarty_cache_pop_state($template) {
	$store = &fp_smarty_cache_runtime_store();
	$key = fp_smarty_cache_template_handle($template);
	if (empty($store ['stacks'] [$key]) || !is_array($store ['stacks'] [$key])) {
		return null;
	}
	$state = array_pop($store ['stacks'] [$key]);
	if (empty($store ['stacks'] [$key])) {
		unset($store ['stacks'] [$key]);
	}
	return is_array($state) ? $state : null;
}

/**
 * @param mixed $template
 * @return array<string,mixed>
 */
function fp_smarty_cache_template_meta($template) {
	$meta = [
		'resource' => '',
		'source_uid' => '',
		'source_name' => '',
		'source_type' => '',
		'source_timestamp' => 0,
		'compile_id' => '',
	];

	if (!is_object($template)) {
		return $meta;
	}

	if (isset($template->template_resource) && is_scalar($template->template_resource)) {
		$meta ['resource'] = (string) $template->template_resource;
	}
	if (isset($template->compile_id) && is_scalar($template->compile_id)) {
		$meta ['compile_id'] = (string) $template->compile_id;
	}

	$source = null;
	if (method_exists($template, 'getSource')) {
		$source = $template->getSource();
	} elseif (isset($template->source)) {
		$source = $template->source;
	}

	if (is_object($source)) {
		if (isset($source->uid) && is_scalar($source->uid)) {
			$meta ['source_uid'] = (string) $source->uid;
		}
		if (isset($source->name) && is_scalar($source->name)) {
			$meta ['source_name'] = (string) $source->name;
		}
		if (isset($source->type) && is_scalar($source->type)) {
			$meta ['source_type'] = (string) $source->type;
		}
		if (isset($source->timestamp) && is_numeric($source->timestamp)) {
			$meta ['source_timestamp'] = (int) $source->timestamp;
		}
	}

	return $meta;
}

/**
 * @param mixed $template
 * @return array<string,mixed>
 */
function fp_smarty_cache_runtime_vary($template, array $params = []) {
	$locale = '';
	if (isset($GLOBALS ['fp_config']) && is_array($GLOBALS ['fp_config'])) {
		if (isset($GLOBALS ['fp_config'] ['locale']) && is_array($GLOBALS ['fp_config'] ['locale'])) {
			$locale = (string) ($GLOBALS ['fp_config'] ['locale'] ['lang'] ?? '');
		}
	}

	$varyLogin = true;
	if (array_key_exists('vary_login', $params)) {
		$varyLogin = fp_smarty_cache_bool($params ['vary_login'], true);
	} elseif (array_key_exists('vary_logged_in', $params)) {
		$varyLogin = fp_smarty_cache_bool($params ['vary_logged_in'], true);
	}

	$vary = [
		'locale' => $locale,
		'compile_id' => is_object($template) && isset($template->compile_id) ? (string) $template->compile_id : '',
	];

	$varyRequest = false;
	if (array_key_exists('vary_request', $params)) {
		$varyRequest = fp_smarty_cache_bool($params ['vary_request'], false);
	} elseif (array_key_exists('vary_route', $params)) {
		$varyRequest = fp_smarty_cache_bool($params ['vary_route'], false);
	}

	if ($varyRequest) {
		$requestUri = '';
		if (isset($_SERVER ['REQUEST_URI']) && is_string($_SERVER ['REQUEST_URI'])) {
			$requestUri = (string) $_SERVER ['REQUEST_URI'];
		} elseif (isset($_SERVER ['QUERY_STRING']) && is_string($_SERVER ['QUERY_STRING']) && $_SERVER ['QUERY_STRING'] !== '') {
			$requestUri = '?' . (string) $_SERVER ['QUERY_STRING'];
		}
		$vary ['request_uri'] = $requestUri;
	}

	if ($varyLogin) {
		$loggedIn = false;
		if (function_exists('user_loggedin')) {
			$loggedIn = (bool) user_loggedin();
		}
		$vary ['logged_in'] = $loggedIn ? '1' : '0';
	}

	return $vary;
}

/**
 * @return string
 */
function fp_smarty_cache_backend() {
	if (function_exists('is_apcu_on') && is_apcu_on()) {
		return 'apcu';
	}
	return 'file';
}

/**
 * @param mixed $params
 * @param mixed $template
 * @return array<string,mixed>
 */
function fp_smarty_cache_build_state($params, $template) {
	$params = is_array($params) ? $params : [];
	fp_smarty_cache_register_render_callbacks($template);

	$enabled = fp_smarty_cache_bool($params ['enabled'] ?? true, true);
	$ttl = fp_smarty_cache_ttl($params ['ttl'] ?? ($params ['lifetime'] ?? null), 3600);
	$group = isset($params ['group']) ? preg_replace('/[^A-Za-z0-9_.-]+/', '-', fp_smarty_cache_stringify($params ['group'])) : 'default';
	if (!is_string($group) || $group === '') {
		$group = 'default';
	}

	$meta = fp_smarty_cache_template_meta($template);

	if (isset($params ['id']) && $params ['id'] !== '') {
		$slot = 'id:' . fp_smarty_cache_stringify($params ['id']);
	} elseif (isset($params ['key']) && $params ['key'] !== '') {
		$slot = 'key:' . fp_smarty_cache_stringify($params ['key']);
	} else {
		$slot = 'slot:' . (string) fp_smarty_cache_next_slot($template);
	}

	$keyPayload = [
		'group' => $group,
		'slot' => $slot,
		'vary' => isset($params ['vary']) ? $params ['vary'] : null,
		'auto_vary' => fp_smarty_cache_runtime_vary($template, $params),
		'template' => [
			'resource' => $meta ['resource'],
			'source_name' => $meta ['source_name'],
			'source_type' => $meta ['source_type'],
			'source_timestamp' => $meta ['source_timestamp'],
			'compile_id' => $meta ['compile_id'],
		],
	];
	$hash = sha1(serialize($keyPayload));

	$cacheRoot = defined('CACHE_DIR') ? CACHE_DIR : sys_get_temp_dir();
	$cacheRoot = rtrim((string) $cacheRoot, '/\\');
	$file = $cacheRoot . DIRECTORY_SEPARATOR . 'smarty-block-cache' . DIRECTORY_SEPARATOR . $group . DIRECTORY_SEPARATOR . substr($hash, 0, 2) . DIRECTORY_SEPARATOR . $hash . '.cache.php';
	$apcuKey = 'smarty:block:' . $group . ':' . $hash;

	return [
		'enabled' => $enabled,
		'ttl' => $ttl,
		'group' => $group,
		'slot' => $slot,
		'hash' => $hash,
		'backend' => fp_smarty_cache_backend(),
		'apcu_key' => $apcuKey,
		'file' => $file,
		'template_stamp' => (int) $meta ['source_timestamp'],
	];
}

/**
 * @param mixed $raw
 * @return array<string,mixed>|null
 */
function fp_smarty_cache_decode_payload($raw) {
	if (is_array($raw)) {
		return $raw;
	}
	if (is_string($raw) && $raw !== '') {
		$payload = @unserialize($raw);
		if (is_array($payload)) {
			return $payload;
		}
	}
	return null;
}

/**
 * @param array<string,mixed> $state
 * @return void
 */
function fp_smarty_cache_delete(array $state) {
	$backend = isset($state ['backend']) ? (string) $state ['backend'] : 'file';
	if ($backend === 'apcu') {
		$key = isset($state ['apcu_key']) ? (string) $state ['apcu_key'] : '';
		if ($key === '') {
			return;
		}
		if (function_exists('apcu_delete_key')) {
			apcu_delete_key($key);
			return;
		}
		if (function_exists('apcu_delete') && function_exists('apcu_key') && function_exists('is_apcu_on') && is_apcu_on()) {
			@apcu_delete(apcu_key($key));
		}
		return;
	}

	$file = isset($state ['file']) ? (string) $state ['file'] : '';
	if ($file !== '') {
		@unlink($file);
	}
}

/**
 * @param array<string,mixed> $state
 * @param mixed $raw
 * @return string|null
 */
function fp_smarty_cache_validate_payload(array $state, $raw) {
	$payload = fp_smarty_cache_decode_payload($raw);
	if (!is_array($payload)) {
		return null;
	}

	$created = isset($payload ['created']) ? (int) $payload ['created'] : 0;
	$ttl = isset($payload ['ttl']) ? (int) $payload ['ttl'] : (int) ($state ['ttl'] ?? 3600);
	$cachedTemplateStamp = isset($payload ['template_stamp']) ? (int) $payload ['template_stamp'] : 0;
	$currentTemplateStamp = isset($state ['template_stamp']) ? (int) $state ['template_stamp'] : 0;

	if ($currentTemplateStamp > 0 && $cachedTemplateStamp > 0 && $cachedTemplateStamp !== $currentTemplateStamp) {
		fp_smarty_cache_delete($state);
		return null;
	}

	if ($ttl > 0 && $created > 0 && ($created + $ttl) < time()) {
		fp_smarty_cache_delete($state);
		return null;
	}

	if (!isset($payload ['content']) || !is_string($payload ['content'])) {
		fp_smarty_cache_delete($state);
		return null;
	}

	return $payload ['content'];
}

/**
 * @param array<string,mixed> $state
 * @return string|null
 */
function fp_smarty_cache_read(array $state) {
	$backend = isset($state ['backend']) ? (string) $state ['backend'] : 'file';

	if ($backend === 'apcu') {
		$key = isset($state ['apcu_key']) ? (string) $state ['apcu_key'] : '';
		if ($key === '' || !function_exists('apcu_get')) {
			return null;
		}
		$ok = false;
		$raw = apcu_get($key, $ok);
		if (!$ok) {
			return null;
		}
		return fp_smarty_cache_validate_payload($state, $raw);
	}

	$file = isset($state ['file']) ? (string) $state ['file'] : '';
	if ($file === '' || !is_file($file)) {
		return null;
	}

	$raw = function_exists('io_load_file') ? io_load_file($file) : @file_get_contents($file);
	if (!is_string($raw) || $raw === '') {
		return null;
	}

	return fp_smarty_cache_validate_payload($state, $raw);
}

/**
 * @param array<string,mixed> $state
 * @param string $content
 * @return bool
 */
function fp_smarty_cache_write(array $state, $content) {
	$payload = [
		'created' => time(),
		'ttl' => (int) ($state ['ttl'] ?? 3600),
		'template_stamp' => (int) ($state ['template_stamp'] ?? 0),
		'content' => (string) $content,
	];
	$backend = isset($state ['backend']) ? (string) $state ['backend'] : 'file';

	if ($backend === 'apcu') {
		$key = isset($state ['apcu_key']) ? (string) $state ['apcu_key'] : '';
		if ($key === '' || !function_exists('apcu_set')) {
			return false;
		}
		$ttl = (int) ($state ['ttl'] ?? 3600);
		if ($ttl < 0) {
			$ttl = 0;
		}
		return (bool) apcu_set($key, $payload, $ttl);
	}

	$file = isset($state ['file']) ? (string) $state ['file'] : '';
	if ($file === '') {
		return false;
	}

	$serialized = serialize($payload);

	if (function_exists('io_write_file')) {
		return (bool) io_write_file($file, $serialized);
	}

	$dir = dirname($file);
	if (!is_dir($dir) && function_exists('fs_mkdir')) {
		if (!fs_mkdir($dir)) {
			return false;
		}
	}
	return @file_put_contents($file, $serialized, LOCK_EX) !== false;
}

/**
 * Smarty block handler for {cache}{/cache}
 *
 * @param array<string,mixed> $params
 * @param string|null $content
 * @param mixed $template
 * @param bool $repeat
 * @return string
 */
function smarty_block_cache($params, $content, $template, &$repeat) {
	if ($content === null) {
		$state = fp_smarty_cache_build_state($params, $template);

		if (empty($state ['enabled'])) {
			return '';
		}

		$cached = fp_smarty_cache_read($state);
		if (is_string($cached)) {
			$repeat = false;
			return $cached;
		}

		fp_smarty_cache_push_state($template, $state);
		return '';
	}

	$state = fp_smarty_cache_pop_state($template);
	if (!is_array($state) || empty($state ['enabled'])) {
		return (string) $content;
	}

	$content = (string) $content;
	fp_smarty_cache_write($state, $content);
	return $content;
}
?>
