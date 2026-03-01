<?php
// core.connenction.php
// Adding security and HTTPS support

/**
 * OWASP - Browser Cache - How can the browser cache be used in attacks?
 * https://www.owasp.org/index.php/OWASP_Application_Security_FAQ#How_can_the_browser_cache_be_used_in_attacks.3F
 *
 * http://stackoverflow.com/questions/13640109/how-to-prevent-browser-cache-for-php-site
 */
if (PHP_SAPI !== 'cli' && PHP_SAPI !== 'phpdbg' && !headers_sent()) {
	header('Expires: Sun, 01 Jan 2015 00:00:00 GMT');
	header('Cache-Control: no-store, no-cache, must-revalidate');
	header('Pragma: no-cache');
	header('Expires: 0');
	/**
	 * http://de.wikipedia.org/wiki/Liste_der_HTTP-Headerfelder
	 */
	header('X-Frame-Options: SAMEORIGIN');
	header('X-XSS-Protection: 1; mode=block');
	header('X-Content-Type-Options: nosniff');
}

//
// End of send header
//

function ip_in_cidrs(string $ip, array $cidrs): bool {
	// Local per-request cache (order-insensitive)
	static $local = [];
	if ($ip === '') {
		return false;
	}
	$norm = [];
	foreach ($cidrs as $c) {
		if ($c !== '' && $c !== null) {
			$norm [] = trim((string)$c);
		}
	}
	if ($norm) {
		sort($norm, SORT_STRING);
		$norm = array_values(array_unique($norm));
	}
	$key = $ip . '|' . sha1(implode(',', $norm));
	if (isset($local [$key])) {
		return $local [$key];
	}
	// APCu hot-cache across requests
	$apcu_on = function_exists('is_apcu_on') ? is_apcu_on() : false;

	$apcu_key = $apcu_on ? ('fp:net:in_cidrs:' . $key) : null;
	if ($apcu_on) {
		$hit = false;
		$val = apcu_get($apcu_key, $hit);
		if ($hit) {
			$local [$key] = (bool)$val;
			return $local [$key];
		}
	}
	$binIp = inet_pton($ip);
	if ($binIp === false) {
		$local [$key] = false;
		if ($apcu_on) {
			@apcu_set($apcu_key, false, 3600);
		}
		return false;
	}
	foreach ($norm as $cidr) {
		if (strpos($cidr, '/') === false) {
			if ($ip === $cidr) {
				$local [$key] = true;
				if ($apcu_on) {
					@apcu_set($apcu_key, true, 3600);
				}
				return true;
			}
			continue;
		}
		list($net, $mask) = explode('/', $cidr, 2);
		$mask = (int) $mask;
		$binNet = inet_pton($net);
		if ($binNet === false) {
			continue;
		}
		$len = strlen($binIp); // IPv4 vs IPv6
		$max = $len * 8;
		if ($mask < 0 || $mask > $max) {
			continue;
		}
		$bytes = intdiv($mask, 8);
		$bits = $mask % 8;
		if ($bytes && substr($binIp, 0, $bytes) !== substr($binNet, 0, $bytes)) {
			continue;
		}
		if ($bits) {
			$ipByte = ord($binIp [$bytes]);
			$netByte = ord($binNet [$bytes]);
			$maskByte = 0xFF << (8 - $bits) & 0xFF;
			if (($ipByte & $maskByte) !== ($netByte & $maskByte)) {
				continue;
			}
		}
		$local [$key] = true;
		if ($apcu_on) {
			@apcu_set($apcu_key, true, 3600);
		}
		return true;
	}
	$local [$key] = false;
	if ($apcu_on) {
		@apcu_set($apcu_key, false, 3600);
	}
	return false;
}

/**
 * Checks if FlatPress is called via HTTPS.
 * Detects HTTPS at the origin or via proxy using defensive multi-signal heuristics; no proxy lists to maintain.
 * @param array<int,string> $trustedProxies Optional CIDR/IPs as trusted; may be empty.
 * @return bool <code>true</code> when FlatPress is called via HTTPS; <code>false</code> otherwise.
 */
function is_https(array $trustedProxies = []) {
	// Local per-request cache keyed by relevant server vars and proxies
	static $local = [];
	$ttl = max(0, (int)($_ENV ['FP_HTTPS_CACHE_TTL'] ?? 120));

	// CLI/DBG is never HTTPS
	if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
		return false;
	}

	// Normalize proxies for key and evaluation
	$norm = [];
	foreach ($trustedProxies as $c) {
		if ($c !== '' && $c !== null) {
			$norm [] = trim((string)$c);
		}
	}
	if ($norm) {
		sort($norm, SORT_STRING);
		$norm = array_values(array_unique($norm));
	}

	$parts = [
		$_SERVER ['HTTPS'] ?? null,
		$_SERVER ['REQUEST_SCHEME'] ?? null,
		$_SERVER ['REMOTE_ADDR'] ?? null,
		$_SERVER ['HTTP_FORWARDED'] ?? null,
		$_SERVER ['HTTP_X_FORWARDED_PROTO'] ?? null,
		$_SERVER ['HTTP_X_FORWARDED_SSL'] ?? null,
		$_SERVER ['HTTP_X_FORWARDED_SCHEME'] ?? null,
		$_SERVER ['HTTP_FRONT_END_HTTPS'] ?? null,
		$_SERVER ['HTTP_X_ARR_SSL'] ?? null,
		$_SERVER ['HTTP_CF_VISITOR'] ?? null,
		$_SERVER ['HTTP_X_FORWARDED_PORT'] ?? null,
		$_SERVER ['SERVER_PORT'] ?? null,
		$norm,
	];
	$key = sha1(json_encode($parts));
	if (isset($local [$key])) {
		return $local [$key];
	}

	// Check APCu securely and host-agnostically
	$apcu_on = function_exists('is_apcu_on') ? is_apcu_on() : false;

	$apcu_key = $apcu_on ? ('fp:https:v2:' . $key) : null;
	if ($apcu_on) {
		$hit = false;
		$val = apcu_get($apcu_key, $hit);
		if ($hit) {
			$local [$key] = (bool)$val;
			return $local [$key];
		}
	}

	// Directly at Origin: reliable server signals
	$https = (string)($_SERVER ['HTTPS'] ?? '');
	if ($https !== '' && strcasecmp($https, 'off') !== 0) {
		$local [$key] = true; // 'on', '1', etc.
		if ($apcu_on) {
			@apcu_set($apcu_key, true, $ttl);
		} return true;
	}
	if (!empty($_SERVER ['REQUEST_SCHEME']) && strcasecmp($_SERVER ['REQUEST_SCHEME'], 'https') === 0) {
		$local [$key] = true;
		if ($apcu_on) {
			@apcu_set($apcu_key, true, $ttl);
		}
		return true;
	}

	/**
	 * Proxy signals
	 * 1) Trusted-Gate: List OR private/loopback REMOTE_ADDR.
	 * 2) Additionally: Public proxy heuristics if multiple independent signals are present.
	 */
	$remote = $_SERVER ['REMOTE_ADDR'] ?? '';
	$ip_is_private = static function ($ip): bool {
		// false => private/loopback/reserved
		return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
	};
	$remote_valid = filter_var($remote, FILTER_VALIDATE_IP) !== false;
	$trusted = $norm ? ($remote_valid && ip_in_cidrs($remote, $norm)) : ($remote_valid && $ip_is_private($remote));

	// Joint reading of all proxy-related headers
	$fw = $_SERVER ['HTTP_FORWARDED'] ?? ''; // RFC 7239: Forwarded: proto=https
	$xfp = strtolower((string)($_SERVER ['HTTP_X_FORWARDED_PROTO'] ?? '')); // Reverse proxy or load balancer
	$xps = (int)($_SERVER ['HTTP_X_FORWARDED_PORT'] ?? 0);
	$xssl = strtolower((string)($_SERVER ['HTTP_X_FORWARDED_SSL'] ?? '')); // Other headers that could be used with proxies
	$xs = strtolower((string)($_SERVER ['HTTP_X_FORWARDED_SCHEME'] ?? ''));
	$feh = strtolower((string)($_SERVER ['HTTP_FRONT_END_HTTPS'] ?? '')); // IIS
	$arr = !empty($_SERVER ['HTTP_X_ARR_SSL']); // Azure
	$cfv = (strpos((string)($_SERVER ['HTTP_CF_VISITOR'] ?? ''), '"scheme":"https"') !== false); // Cloudflare

	// RFC 7239 proto=https OR XFP contains https
	$proto_https = (($fw && preg_match('/(^|[;,\\s])proto\\s*=\\s*https(\\b|$)/i', $fw)) || in_array('https', array_map('trim', explode(',', $xfp)), true));
	// Strong indications
	$strong_hint = ($xps === 443) || ($xssl === 'on') || ($xs === 'https') || ($feh === 'on') || $arr || $cfv;

	// Trusted-Gate
	if ($trusted && ($proto_https || $strong_hint)) {
		$local [$key] = true;
		if ($apcu_on) {
			@apcu_set($apcu_key, true, $ttl);
		}
		return true;
	}

	/**
	 * Public proxy heuristics without list:
	 *   Only accept if there are multiple independent signals.
	 *   At least two HTTPS indicators OR one HTTPS indicator + proxy identity.
	 */
	if (!$trusted) {
		$https_hints =
			(int)$proto_https +
			(int)($xps === 443) +
			(int)($xssl === 'on') +
			(int)($xs === 'https') +
			(int)($feh === 'on') +
			(int)$arr +
			(int)$cfv;

		$proxy_identity =
			(int)isset($_SERVER ['HTTP_VIA']) +
			(int)isset($_SERVER ['HTTP_CF_RAY']) +
			(int)isset($_SERVER ['HTTP_CF_CONNECTING_IP']) +
			(int)isset($_SERVER ['HTTP_X_FORWARDED_FOR']) +
			(int)isset($_SERVER ['HTTP_X_AZURE_REF']) +
			(int)isset($_SERVER ['HTTP_X_ARR_LOG_ID']) +
			(int)isset($_SERVER ['HTTP_FASTLY_CLIENT_IP']) +
			(int)isset($_SERVER ['HTTP_X_FASTLY_REQUEST_ID']);

		if ($https_hints >= 2 || ($https_hints >= 1 && $proxy_identity >= 1)) {
			$local [$key] = true;
			if ($apcu_on) {
				@apcu_set($apcu_key, true, $ttl);
			}
			return true;
		}
	}

	// Port 443 on Origin has a weaker signal, but often helps.
	if ((int)($_SERVER ['SERVER_PORT'] ?? 0) === 443) {
		$local [$key] = true;
		if ($apcu_on) {
			@apcu_set($apcu_key, true, $ttl);
		}
		return true;
	}

	$local [$key] = false;
	if ($apcu_on) {
		@apcu_set($apcu_key, false, $ttl);
	}
	return false;
}

if (isset($_SERVER ['HTTPS'])) {
	$_SERVER ['HTTPS'] = htmlspecialchars($_SERVER ['HTTPS'], ENT_QUOTES, "UTF-8");
}

// Supports Apache and IIS
$scheme = '';
if (is_https()) {
	// HTTPS enabled
	$scheme = "https://";
} else {
	// HTTP only
	$scheme = "http://";
}

// Compatibility with ISS
$_SERVER ["REQUEST_URI"] = htmlspecialchars($_SERVER ["REQUEST_URI"] ?? '', ENT_QUOTES, "UTF-8");
if ($_SERVER ["REQUEST_URI"] === '') {
	$_SERVER ['REQUEST_URI'] = $scheme . 'localhost/flatpress/';
}

/**
 * @param string $path
 * @return bool
 */
function is_absolute_path($path) {
	$path = (string)$path;
	if ($path === '') {
		return false;
	}
	// Unix or UNC
	if ($path [0] === '/' || $path [0] === '\\') {
		return true;
	}
	// Windows drive letter
	// Use a non-slash delimiter because this pattern needs to match both backslash ('\\') and slash ('/').
	return (bool)preg_match('~^[A-Za-z]:[\\\\/]~', $path);
}

/**
 * Resolve a file path relative to ABS_PATH.
 * @param string $path
 * @return string
 */
function resolve_abspath($path) {
	$path = (string)$path;
	if ($path === '') {
		return '';
	}
	$path = str_replace('\\\\', '/', $path);
	if (is_absolute_path($path)) {
		return $path;
	}
	$base = defined('ABS_PATH') ? (string)ABS_PATH : '';
	$base = str_replace('\\\\', '/', $base);
	if ($base !== '' && substr($base, -1) !== '/') {
		$base .= '/';
	}
	return $base . ltrim($path, '/');
}

/**
 * Load settings.conf.php-like file into a local scope and return $fp_config.
 * @param string $file
 * @return array|null
 */
function load_fp_config_file($file) {
	$file = (string)$file;
	if ($file === '' || !is_file($file) || !is_readable($file)) {
		return null;
	}
	/** @var mixed $fp_config */
	$fp_config = null;
	/** @noinspection PhpIncludeInspection */
	include $file;
	if (!is_array($fp_config) || $fp_config === []) {
		return null;
	}
	/** @var array $fp_config */
	return $fp_config;
}

/**
 * Normalize and validate a configured base URL.
 * Returns '' if invalid.
 *
 * @param string $url
 * @return string
 */
function normalize_baseurl($url) {
	$url = trim((string)$url);
	if ($url === '') {
		return '';
	}
	// Reject control chars
	if (preg_match('/[\x00-\x1F\x7F]/', $url)) {
		return '';
	}
	$parts = @parse_url($url);
	if (!is_array($parts)) {
		return '';
	}
	$scheme = strtolower((string)($parts ['scheme'] ?? ''));
	if ($scheme !== 'http' && $scheme !== 'https') {
		return '';
	}
	$host = (string)($parts ['host'] ?? '');
	if ($host === '') {
		return '';
	}

	/**
	 * Reject characters that could break HTML attributes or headers.
	 * Use a non-slash delimiter because we explicitly reject '/' in the host.
	 */
	if (preg_match('~[\s\x00-\x1F\x7F<>"\'`\\\\/]~u', $host)) {
		return '';
	}

	$port = (int)($parts ['port'] ?? 0);
	if ($port < 0 || $port > 65535) {
		$port = 0;
	}
	$path = (string)($parts ['path'] ?? '/');
	if ($path === '') {
		$path = '/';
	}
	if ($path [0] !== '/') {
		$path = '/' . $path;
	}
	if (substr($path, -1) !== '/') {
		$path .= '/';
	}
	$host_for_url = $host;
	if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
		$host_for_url = '[' . $host . ']';
	}
	$base = $scheme . '://' . $host_for_url;
	if ($port > 0) {
		$base .= ':' . $port;
	}
	return $base . $path;
}

/**
 * Strictly validate and normalize a request host for safe use in URLs.
 * Returns a safe fallback host if invalid.
 *
 * @return string
 */
function canonical_request_host() {
	$raw = (string)($_SERVER ['HTTP_HOST'] ?? ($_SERVER ['SERVER_NAME'] ?? ''));
	$raw = trim($raw);
	if ($raw === '') {
		return 'localhost';
	}

	/**
	 * Reject control chars and obvious breakers early
	 * Use a non-slash delimiter because we explicitly reject '/' in the host.
	 */
	if (preg_match('~[\x00-\x1F\x7F\s<>"\'`\\\\/]~', $raw)) {
		return 'localhost';
	}

	$port = '';
	$host = $raw;

	// Bracketed IPv6: [::1]:8080
	if ($host [0] === '[') {
		if (!preg_match('/^\[([^\]]+)\](?::(\d{1,5}))?$/', $host, $m)) {
			return 'localhost';
		}
		$ip = $m [1];
		if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
			return 'localhost';
		}
		if (isset($m [2]) && $m [2] !== '') {
			$p = (int)$m [2];
			if ($p >= 1 && $p <= 65535) {
				$port = ':' . $p;
			}
		}
		return '[' . $ip . ']' . $port;
	}

	// Unbracketed IPv6 (non-standard but seen): ::1
	if (substr_count($host, ':') > 1) {
		if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
			return 'localhost';
		}
		return '[' . $host . ']';
	}

	// host:port
	if (strpos($host, ':') !== false) {
		$pos = strrpos($host, ':');
		$maybe_port = substr($host, $pos + 1);
		$maybe_host = substr($host, 0, $pos);
		if ($maybe_host !== '' && ctype_digit($maybe_port)) {
			$p = (int)$maybe_port;
			if ($p >= 1 && $p <= 65535) {
				$port = ':' . $p;
				$host = $maybe_host;
			}
		}
	}

	$host = strtolower($host);
	// IPv4
	if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
		return $host . $port;
	}

	// Hostname: accept common DNS name chars plus underscore for compatibility.
	if (!preg_match('/^[a-z0-9][a-z0-9._-]{0,251}[a-z0-9]$/', $host)) {
		// allow single-label like 'localhost'
		if (!preg_match('/^[a-z0-9][a-z0-9._-]{0,253}$/', $host)) {
			return 'localhost';
		}
	}
	if (strpos($host, '..') !== false) {
		return 'localhost';
	}
	return $host . $port;
}

/**
 * Strictly validate and normalize the server host (SERVER_NAME + optional SERVER_PORT)
 * for safe use in URLs.
 *
 * @return string
 */
function canonical_server_host() {
	$raw = (string)($_SERVER ['SERVER_NAME'] ?? '');
	$raw = trim($raw);
	if ($raw === '') {
		return 'localhost';
	}
	// Reject control chars and obvious breakers early
	if (preg_match('~[\x00-\x1F\x7F\s<>"\'`\\/]~', $raw)) {
		return 'localhost';
	}

	$host = $raw;

	// SERVER_NAME may be a bracketed IPv6 literal: [::1]
	if (isset($host [0]) && $host [0] === '[') {
		if (!preg_match('/^\[([^\]]+)\](?::(\d{1,5}))?$/', $host, $m)) {
			return 'localhost';
		}
		$ip = $m [1];
		if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
			return 'localhost';
		}
		// Ignore any embedded port; SERVER_PORT is appended below.
		$host = '[' . $ip . ']';

	// SERVER_NAME may be an unbracketed IPv6 literal
	} elseif (substr_count($host, ':') > 1) {
		if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
			return 'localhost';
		}
		$host = '[' . $host . ']';
	} else {
		$host = strtolower($host);
		// IPv4
		if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
			// Hostname: accept common DNS name chars plus underscore for compatibility.
			if (!preg_match('/^[a-z0-9][a-z0-9._-]{0,251}[a-z0-9]$/', $host)) {
				// allow single-label like 'localhost'
				if (!preg_match('/^[a-z0-9][a-z0-9._-]{0,253}$/', $host)) {
					return 'localhost';
				}
			}
			if (strpos($host, '..') !== false) {
				return 'localhost';
			}
		}
	}

	$port = (int)($_SERVER ['SERVER_PORT'] ?? 0);
	if ($port < 1 || $port > 65535) {
		$port = 0;
	}
	// Only append non-default ports
	if ($port > 0) {
		$default = is_https() ? 443 : 80;
		if ($port !== $default) {
			$host .= ':' . $port;
		}
	}

	return $host;
}

/**
 * Read the configured canonical base URL from settings.conf.php (general['www']).
 * Returns '' if not available/invalid.
 *
 * @return string
 */
function configured_blog_baseurl() {
	// Per-request local cache by config file signature
	static $local = [];

	if (!defined('CONFIG_FILE')) {
		return '';
	}
	$conf = resolve_abspath((string)CONFIG_FILE);
	if ($conf === '' || !is_file($conf) || !is_readable($conf)) {
		return '';
	}

	// Configuration may change at any time: validate cached values via lightweight stat signature.
	$st = @stat($conf);
	$mtime = 0;
	$size = 0;
	if (is_array($st)) {
		$mtime = (int)($st ['mtime'] ?? 0);
		$size = (int)($st ['size'] ?? 0);
	}
	$sig = (string)$mtime . ':' . (string)$size;

	if (isset($local [$conf]) && is_array($local [$conf]) && ($local [$conf] ['sig'] ?? '') === $sig) {
		if (isset($local [$conf] ['cfg']) && is_array($local [$conf] ['cfg'])) {
			$GLOBALS['EARLY_FP_CONFIG'] = $local[$conf]['cfg'];
		}
		return (string)($local [$conf] ['www'] ?? '');
	}

	// Optional APCu cache across requests (namespaced via core.fileio.php apcu_get/apcu_set)
	$apcu_on = function_exists('is_apcu_on') ? is_apcu_on() : false;
	$apcu_key = $apcu_on ? ('config:settings:' . sha1($conf)) : null;
	if ($apcu_on) {
		$hit = false;
		$cached = apcu_get($apcu_key, $hit);
		if ($hit && is_array($cached) && ($cached ['sig'] ?? '') === $sig) {
			$www = (string)($cached ['www'] ?? '');
			$cfg_cached = $cached ['cfg'] ?? null;
			if (is_array($cfg_cached)) {
				$GLOBALS ['EARLY_FP_CONFIG'] = $cfg_cached;
			}
			$local [$conf] = ['sig' => $sig, 'www' => $www, 'cfg' => (is_array($cfg_cached) ? $cfg_cached : null)];
			return $www;
		}
	}

	$cfg = load_fp_config_file($conf);
	if (!is_array($cfg)) {
		return '';
	}
	$www = (string)($cfg ['general'] ['www'] ?? '');
	$www = normalize_baseurl($www);
	// Make the parsed config available for later config_load() (optional perf + reliability).
	$GLOBALS ['EARLY_FP_CONFIG'] = $cfg;

	$local [$conf] = ['sig' => $sig, 'www' => $www, 'cfg' => $cfg];
	if ($apcu_on) {
		// TTL is only a memory pressure hint; signature validation ensures freshness.
		@apcu_set($apcu_key, ['sig' => $sig, 'www' => $www, 'cfg' => $cfg], 3600);
	}
	return $www;
}

// Define BLOG_BASEURL here (preferred) to avoid Host header injection.
if (!defined('BLOG_BASEURL')) {
	$blog_root = defined('BLOG_ROOT') ? (string)BLOG_ROOT : '/';
	$cfg_url = configured_blog_baseurl();
	if ($cfg_url !== '') {
		define('BLOG_BASEURL', $cfg_url);
		define('BLOG_BASEURL_TRUSTED', true);
	} else {
		// Fall back to SERVER_NAME/SERVER_PORT to avoid Host header poisoning.
		define('BLOG_BASEURL', $scheme . canonical_server_host() . $blog_root);
		define('BLOG_BASEURL_TRUSTED', false);
	}
}

?>
