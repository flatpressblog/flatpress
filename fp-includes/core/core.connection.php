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
 * Normalize comma-separated forwarded scheme values.
 * Returns an empty string when the value is missing, invalid, or ambiguous.
 *
 * @param string $value
 * @return string
 */
function fp_connection_uniform_forwarded_scheme($value) {
	$value = strtolower(trim((string)$value));
	if ($value === '') {
		return '';
	}
	$scheme = '';
	foreach (explode(',', $value) as $token) {
		$token = trim($token, " \t\n\r\0\x0B\"");
		if ($token !== 'http' && $token !== 'https') {
			return '';
		}
		if ($scheme !== '' && $scheme !== $token) {
			return '';
		}
		$scheme = $token;
	}
	return $scheme;
}

/**
 * Read the externally forwarded scheme without trusting it yet.
 * Conflicting proxy headers are marked ambiguous and are never accepted as a
 * single-signal trust decision.
 *
 * @return array{scheme:string,ambiguous:bool}
 */
function fp_connection_forwarded_scheme_info() {
	$schemes = array();
	$ambiguous = false;

	$xfpRaw = (string)($_SERVER ['HTTP_X_FORWARDED_PROTO'] ?? '');
	if (trim($xfpRaw) !== '') {
		$xfp = fp_connection_uniform_forwarded_scheme($xfpRaw);
		if ($xfp === '') {
			$ambiguous = true;
		} else {
			$schemes [] = $xfp;
		}
	}

	$forwarded = (string)($_SERVER ['HTTP_FORWARDED'] ?? '');
	if ($forwarded !== '') {
		$matches = array();
		if (preg_match_all('/(?:^|[;,]\s*)proto\s*=\s*"?([a-z][a-z0-9+.-]*)"?/i', $forwarded, $matches) > 0) {
			$forwardedScheme = '';
			foreach ($matches [1] as $candidate) {
				$candidate = strtolower((string)$candidate);
				if ($candidate !== 'http' && $candidate !== 'https') {
					$ambiguous = true;
					continue;
				}
				if ($forwardedScheme !== '' && $forwardedScheme !== $candidate) {
					$ambiguous = true;
				}
				$forwardedScheme = $candidate;
			}
			if ($forwardedScheme !== '') {
				$schemes [] = $forwardedScheme;
			}
		}
	}

	$xfsRaw = (string)($_SERVER ['HTTP_X_FORWARDED_SCHEME'] ?? '');
	if (trim($xfsRaw) !== '') {
		$xfs = fp_connection_uniform_forwarded_scheme($xfsRaw);
		if ($xfs === '') {
			$ambiguous = true;
		} else {
			$schemes [] = $xfs;
		}
	}

	$scheme = '';
	foreach ($schemes as $candidate) {
		if ($scheme !== '' && $scheme !== $candidate) {
			$ambiguous = true;
		}
		$scheme = $candidate;
	}

	if ($ambiguous) {
		$scheme = '';
	}

	return array('scheme' => $scheme, 'ambiguous' => $ambiguous);
}

/**
 * Read X-Forwarded-Port when every hop reports the same valid port.
 *
 * @return array{port:int,ambiguous:bool}
 */
function fp_connection_forwarded_port_info() {
	$raw = trim((string)($_SERVER ['HTTP_X_FORWARDED_PORT'] ?? ''));
	if ($raw === '') {
		return array('port' => 0, 'ambiguous' => false);
	}
	$port = 0;
	foreach (explode(',', $raw) as $token) {
		$token = trim($token);
		if ($token === '' || !ctype_digit($token)) {
			return array('port' => 0, 'ambiguous' => true);
		}
		$candidate = (int)$token;
		if ($candidate < 1 || $candidate > 65535) {
			return array('port' => 0, 'ambiguous' => true);
		}
		if ($port !== 0 && $port !== $candidate) {
			return array('port' => 0, 'ambiguous' => true);
		}
		$port = $candidate;
	}
	return array('port' => $port, 'ambiguous' => false);
}

/**
 * Return whether REMOTE_ADDR belongs to an explicitly trusted proxy or, when
 * no list is supplied, to a private/loopback/reserved intermediary.
 *
 * @param array<int,string> $trustedProxies
 * @return bool
 */
function fp_connection_remote_proxy_trusted(array $trustedProxies) {
	$remote = (string)($_SERVER ['REMOTE_ADDR'] ?? '');
	if (filter_var($remote, FILTER_VALIDATE_IP) === false) {
		return false;
	}
	if (!empty($trustedProxies)) {
		return ip_in_cidrs($remote, $trustedProxies);
	}
	return filter_var($remote, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
}

/**
 * Number of independent proxy identity signals supplied by common CDNs,
 * reverse proxies and load balancers.
 *
 * @return int
 */
function fp_connection_proxy_identity_count() {
	return (int)isset($_SERVER ['HTTP_VIA']) + (int)isset($_SERVER ['HTTP_CF_RAY']) + (int)isset($_SERVER ['HTTP_CF_CONNECTING_IP']) + (int)isset($_SERVER ['HTTP_X_FORWARDED_FOR']) + (int)isset($_SERVER ['HTTP_X_AZURE_REF']) + (int)isset($_SERVER ['HTTP_X_ARR_LOG_ID']) + (int)isset($_SERVER ['HTTP_FASTLY_CLIENT_IP']) + (int)isset($_SERVER ['HTTP_X_FASTLY_REQUEST_ID']);
}

/**
 * Compare the configured canonical host with SERVER_NAME. This deliberately
 * does not trust HTTP_HOST for proxy trust decisions.
 *
 * @return bool
 */
function fp_connection_configured_host_matches_server() {
	$configured = configured_blog_baseurl();
	if ($configured === '') {
		return false;
	}
	$parts = @parse_url($configured);
	if (!is_array($parts) || empty($parts ['host'])) {
		return false;
	}
	$configuredHost = strtolower(trim((string)$parts ['host'], '[]'));
	$serverHost = function_exists('canonical_server_name') ? canonical_server_name() : (string)($_SERVER ['SERVER_NAME'] ?? '');
	$serverHost = strtolower(trim((string)$serverHost, '[]'));
	return $configuredHost !== '' && $serverHost !== '' && $configuredHost === $serverHost;
}

/**
 * Return whether the saved public URL explicitly authorizes use of forwarded
 * scheme headers for this installation.
 *
 * @return bool
 */
function configured_trust_forwarded_scheme() {
	configured_blog_baseurl();
	$cfg = $GLOBALS ['EARLY_FP_CONFIG'] ?? null;
	return is_array($cfg) && isset($cfg ['general']) && is_array($cfg ['general']) && !empty($cfg ['general'] ['trust_forwarded_scheme']);
}

/**
 * Return whether the configured canonical URL agrees with a forwarded scheme.
 *
 * @param string $scheme
 * @return bool
 */
function fp_connection_configured_scheme_matches($scheme) {
	$configured = configured_blog_baseurl();
	$parts = $configured !== '' ? @parse_url($configured) : false;
	return is_array($parts) && strtolower((string)($parts ['scheme'] ?? '')) === strtolower((string)$scheme);
}

/**
 * Build the effective public request context while keeping origin/backend
 * transport details separate from externally visible scheme and port.
 *
 * Forwarded headers are accepted only when the proxy is trusted, when multiple
 * independent proxy signals corroborate the request, or when the installation
 * explicitly persisted forwarded-scheme trust during setup/configuration.
 * $allowForwardedCandidate is reserved for setup/migration URL proposals; it
 * must never be used for cookie or redirect security decisions.
 *
 * @param array<int,string> $trustedProxies
 * @param bool $allowForwardedCandidate
 * @return array{scheme:string,port:int,source:string,forwarded:bool,trusted:bool}
 */
function fp_public_request_context(array $trustedProxies = array(), $allowForwardedCandidate = false) {
	$serverPort = (int)($_SERVER ['SERVER_PORT'] ?? 0);
	if ($serverPort < 1 || $serverPort > 65535) {
		$serverPort = 0;
	}

	$https = strtolower(trim((string)($_SERVER ['HTTPS'] ?? '')));
	$requestScheme = strtolower(trim((string)($_SERVER ['REQUEST_SCHEME'] ?? '')));
	$directHttps = ($https !== '' && $https !== 'off') || $requestScheme === 'https' || $serverPort === 443;

	/**
	 * Read forwarded transport metadata before deciding the public port. A TLS
	 * terminating proxy may expose HTTPS to the browser while PHP still sees
	 * REQUEST_SCHEME=http and SERVER_PORT=80. Conversely, untrusted forwarded
	 * headers must not override a normal direct HTTPS origin.
	 */
	$schemeInfo = fp_connection_forwarded_scheme_info();
	$portInfo = fp_connection_forwarded_port_info();
	$forwardedScheme = (string)$schemeInfo ['scheme'];
	$forwardedPort = (int)$portInfo ['port'];
	$ambiguous = !empty($schemeInfo ['ambiguous']) || !empty($portInfo ['ambiguous']);

	$xssl = strtolower((string)($_SERVER ['HTTP_X_FORWARDED_SSL'] ?? ''));
	$feh = strtolower((string)($_SERVER ['HTTP_FRONT_END_HTTPS'] ?? ''));
	$arr = !empty($_SERVER ['HTTP_X_ARR_SSL']);
	$cfv = strpos((string)($_SERVER ['HTTP_CF_VISITOR'] ?? ''), '"scheme":"https"') !== false;
	$strongHttps = ($forwardedPort === 443) || ($xssl === 'on') || ($feh === 'on') || $arr || $cfv;
	$conflictingHttps = ($forwardedScheme === 'http' && $strongHttps);

	$httpsHints = (int)($forwardedScheme === 'https') + (int)($forwardedPort === 443) + (int)($xssl === 'on') + (int)($feh === 'on') + (int)$arr + (int)$cfv;

	$trustedRemote = fp_connection_remote_proxy_trusted($trustedProxies);
	$proxyIdentity = fp_connection_proxy_identity_count();
	$configuredTrust = configured_trust_forwarded_scheme() && $forwardedScheme !== '' && fp_connection_configured_host_matches_server() && fp_connection_configured_scheme_matches($forwardedScheme);

	$source = '';
	$trusted = false;
	if (!$ambiguous && !$conflictingHttps) {
		if ($trustedRemote && ($forwardedScheme !== '' || $strongHttps)) {
			$source = 'trusted-proxy';
			$trusted = true;
		} elseif ($httpsHints >= 2 || ($httpsHints >= 1 && $proxyIdentity >= 1)) {
			$source = 'proxy-heuristic';
			$trusted = true;
		} elseif ($configuredTrust) {
			$source = 'configured-proxy';
			$trusted = true;
		} elseif ($allowForwardedCandidate && $forwardedScheme !== '') {
			$source = 'forwarded-candidate';
		}
	}

	if ($directHttps) {
		$publicPort = $serverPort > 0 ? $serverPort : 443;

		/**
		 * HTTPS may already be signalled by the hosting stack even though the
		 * backend connection itself is HTTP. Only a forwarded context that passed
		 * the trust/candidate policy may replace the backend port. This keeps
		 * genuine direct HTTPS on ports such as 8443 intact while correctly
		 * normalizing HTTPS-over-backend-HTTP setups to public 443.
		 */
		$forwardedPublicHttps = $forwardedScheme === 'https' || ($forwardedScheme === '' && $strongHttps);
		$backendLooksHttp = $requestScheme === 'http' || $serverPort === 80;
		if ($source !== '' && $forwardedPublicHttps && ($forwardedPort > 0 || $backendLooksHttp)) {
			$publicPort = $forwardedPort > 0 ? $forwardedPort : 443;
			return array(
				'scheme' => 'https',
				'port' => $publicPort,
				'source' => $source,
				'forwarded' => true,
				'trusted' => $trusted
			);
		}

		return array(
			'scheme' => 'https',
			'port' => $publicPort,
			'source' => 'origin',
			'forwarded' => false,
			'trusted' => true
		);
	}

	if ($source !== '') {
		$publicScheme = $forwardedScheme !== '' ? $forwardedScheme : 'https';
		$publicPort = $forwardedPort > 0 ? $forwardedPort : ($publicScheme === 'https' ? 443 : 80);
		return array(
			'scheme' => $publicScheme,
			'port' => $publicPort,
			'source' => $source,
			'forwarded' => true,
			'trusted' => $trusted
		);
	}

	$publicScheme = ($requestScheme === 'https') ? 'https' : 'http';
	$publicPort = $serverPort > 0 ? $serverPort : ($publicScheme === 'https' ? 443 : 80);
	return array(
		'scheme' => $publicScheme,
		'port' => $publicPort,
		'source' => 'origin',
		'forwarded' => false,
		'trusted' => true
	);
}

/**
 * Checks if the externally visible FlatPress request uses HTTPS.
 *
 * @param array<int,string> $trustedProxies Optional CIDR/IPs as trusted; may be empty.
 * @return bool <code>true</code> when FlatPress is called via HTTPS; <code>false</code> otherwise.
 */
function is_https(array $trustedProxies = array()) {
	static $local = array();
	$ttl = max(0, (int)($_ENV ['FP_HTTPS_CACHE_TTL'] ?? 120));

	if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
		return false;
	}

	$norm = array();
	foreach ($trustedProxies as $c) {
		$c = trim((string)$c);
		if ($c !== '') {
			$norm [] = $c;
		}
	}
	if ($norm) {
		sort($norm, SORT_STRING);
		$norm = array_values(array_unique($norm));
	}

	$configured = configured_blog_baseurl();
	$configuredTrust = configured_trust_forwarded_scheme();
	$parts = array(
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
		$configured,
		$configuredTrust,
		$norm
	);
	$key = sha1((string)json_encode($parts));
	if (array_key_exists($key, $local)) {
		return (bool)$local [$key];
	}

	$apcuOn = function_exists('is_apcu_on') ? is_apcu_on() : false;
	$apcuKey = $apcuOn ? ('fp:https:v4:' . $key) : null;
	if ($apcuOn) {
		$hit = false;
		$cached = apcu_get($apcuKey, $hit);
		if ($hit) {
			$local [$key] = (bool)$cached;
			return (bool)$local [$key];
		}
	}

	$context = fp_public_request_context($norm, false);
	$result = ((string)$context ['scheme'] === 'https');
	$local [$key] = $result;
	if ($apcuOn) {
		@apcu_set($apcuKey, $result, $ttl);
	}
	return $result;
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

// Preserve the raw request URI for redirects/headers before HTML-escaping $_SERVER['REQUEST_URI']
$GLOBALS ['RAW_REQUEST_URI'] = (string)($_SERVER ['REQUEST_URI'] ?? '');

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
 * Required by system_guessbaseurl() in core.system.php
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
		if (isset($m [2])) {
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
 * Strictly validate and normalize SERVER_NAME without attaching a transport
 * port. IPv6 literals are returned bracketed for direct URL use.
 *
 * @return string
 */
function canonical_server_name() {
	$raw = trim((string)($_SERVER ['SERVER_NAME'] ?? ''));
	if ($raw === '') {
		return 'localhost';
	}
	if (preg_match('~[\x00-\x1F\x7F\s<>"\'`\\/]~', $raw)) {
		return 'localhost';
	}

	$host = $raw;
	if (isset($host [0]) && $host [0] === '[') {
		if (!preg_match('/^\[([^\]]+)\](?::\d{1,5})?$/', $host, $m)) {
			return 'localhost';
		}
		$ip = $m [1];
		if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
			return 'localhost';
		}
		return '[' . $ip . ']';
	}

	if (substr_count($host, ':') > 1) {
		if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
			return 'localhost';
		}
		return '[' . $host . ']';
	}

	if (strpos($host, ':') !== false) {
		$pos = strrpos($host, ':');
		$maybePort = substr($host, $pos + 1);
		$maybeHost = substr($host, 0, $pos);
		if ($maybeHost !== '' && ctype_digit($maybePort)) {
			$host = $maybeHost;
		}
	}

	$host = strtolower($host);
	if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
		return $host;
	}
	if (!preg_match('/^[a-z0-9][a-z0-9._-]{0,251}[a-z0-9]$/', $host)) {
		if (!preg_match('/^[a-z0-9][a-z0-9._-]{0,253}$/', $host)) {
			return 'localhost';
		}
	}
	if (strpos($host, '..') !== false) {
		return 'localhost';
	}
	return $host;
}

/**
 * Remove an optional numeric port from a previously validated host string.
 *
 * @param string $host
 * @return string
 */
function fp_connection_host_without_port($host) {
	$host = trim((string)$host);
	if ($host === '') {
		return '';
	}
	if ($host [0] === '[') {
		$end = strpos($host, ']');
		return $end === false ? '' : substr($host, 0, $end + 1);
	}
	if (substr_count($host, ':') === 1) {
		$pos = strrpos($host, ':');
		$maybePort = substr($host, $pos + 1);
		if ($maybePort !== '' && ctype_digit($maybePort)) {
			return substr($host, 0, $pos);
		}
	}
	return $host;
}

/**
 * Append a public port only when it differs from the scheme default.
 *
 * @param string $host
 * @param string $scheme
 * @param int $port
 * @return string
 */
function fp_connection_host_with_public_port($host, $scheme, $port) {
	$host = fp_connection_host_without_port($host);
	$scheme = strtolower((string)$scheme);
	$port = (int)$port;
	if ($host === '') {
		return 'localhost';
	}
	$default = $scheme === 'https' ? 443 : 80;
	if ($port >= 1 && $port <= 65535 && $port !== $default) {
		$host .= ':' . $port;
	}
	return $host;
}

/**
 * Strictly validate and normalize the server host for public URL generation.
 * The externally visible port comes from the public request context, not
 * blindly from the backend SERVER_PORT.
 *
 * @return string
 */
function canonical_server_host() {
	$context = fp_public_request_context(array(), false);
	return fp_connection_host_with_public_port(
		canonical_server_name(),
		(string)$context ['scheme'],
		(int)$context ['port']
	);
}

/**
 * Build the externally visible current URL from the canonical FlatPress origin
 * and the raw request URI. Plugins should use this instead of re-reading
 * SERVER_PORT or X-Forwarded-* independently.
 *
 * @param string|null $requestUri
 * @return string
 */
function fp_current_public_url($requestUri = null) {
	$base = defined('BLOG_BASEURL') ? normalize_baseurl((string)BLOG_BASEURL) : '';
	$parts = $base !== '' ? @parse_url($base) : false;

	if (is_array($parts) && !empty($parts ['scheme']) && !empty($parts ['host'])) {
		$scheme = strtolower((string)$parts ['scheme']);
		$host = (string)$parts ['host'];
		$port = (int)($parts ['port'] ?? 0);
		$hostForUrl = filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false ? '[' . $host . ']' : $host;
		$origin = $scheme . '://' . $hostForUrl;
		$default = $scheme === 'https' ? 443 : 80;
		if ($port > 0 && $port !== $default) {
			$origin .= ':' . $port;
		}
	} else {
		$context = fp_public_request_context(array(), false);
		$scheme = (string)$context ['scheme'];
		$origin = $scheme . '://' . canonical_server_host();
	}

	if ($requestUri === null) {
		$requestUri = (string)($GLOBALS ['RAW_REQUEST_URI'] ?? ($_SERVER ['REQUEST_URI'] ?? '/'));
	}
	$requestUri = trim(str_replace(array("\r", "\n"), '', (string)$requestUri));
	if ($requestUri === '') {
		$requestUri = '/';
	}
	if (preg_match('~^https?://~i', $requestUri)) {
		$requestParts = @parse_url($requestUri);
		if (is_array($requestParts)) {
			$requestUri = (string)($requestParts ['path'] ?? '/');
			if (isset($requestParts ['query']) && $requestParts ['query'] !== '') {
				$requestUri .= '?' . $requestParts ['query'];
			}
		}
	}
	if ($requestUri === '' || $requestUri [0] !== '/') {
		$requestUri = '/' . ltrim($requestUri, '/');
	}
	return $origin . $requestUri;
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
			$GLOBALS ['EARLY_FP_CONFIG'] = $local [$conf] ['cfg'];
		}
		return (string)($local [$conf] ['www'] ?? '');
	}

	// Optional APCu cache across requests (namespaced via core.apcu.php apcu_get/apcu_set)
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

/**
 * Decide whether the current setup/configuration request should persist trust
 * in forwarded scheme headers for the supplied canonical URL.
 *
 * Trust is stored only when the browser-visible URL scheme is corroborated by
 * one unambiguous forwarded scheme while the backend itself reports a different
 * transport. This turns the setup/admin confirmation into an explicit trust
 * decision instead of globally trusting arbitrary X-Forwarded-Proto headers.
 *
 * @param string $url
 * @return bool
 */
function fp_should_trust_forwarded_scheme_for_url($url) {
	$url = normalize_baseurl($url);
	if ($url === '') {
		return false;
	}
	$parts = @parse_url($url);
	if (!is_array($parts)) {
		return false;
	}
	$urlScheme = strtolower((string)($parts ['scheme'] ?? ''));
	$urlHost = strtolower(trim((string)($parts ['host'] ?? ''), '[]'));
	if ($urlScheme !== 'http' && $urlScheme !== 'https') {
		return false;
	}

	$schemeInfo = fp_connection_forwarded_scheme_info();
	if (!empty($schemeInfo ['ambiguous']) || (string)$schemeInfo ['scheme'] !== $urlScheme) {
		return false;
	}

	$serverHost = strtolower(trim(canonical_server_name(), '[]'));
	if ($urlHost === '' || $serverHost === '' || $urlHost !== $serverHost) {
		return false;
	}

	/**
	 * Re-evaluate the request as a setup/configuration candidate. Persist trust
	 * only when forwarded transport metadata actually contributed to the public
	 * context confirmed by the administrator. This also covers stacks that set
	 * HTTPS=on while forwarding to PHP over HTTP port 80.
	 */
	$context = fp_public_request_context(array(), true);
	if (empty($context ['forwarded']) || (string)$context ['scheme'] !== $urlScheme) {
		return false;
	}

	$urlPort = (int)($parts ['port'] ?? 0);
	if ($urlPort === 0) {
		$urlPort = $urlScheme === 'https' ? 443 : 80;
	}
	return (int)$context ['port'] === $urlPort;
}

/**
 * Build a safe public base URL proposal for first-time setup or explicit
 * migration. Forwarded scheme/port values may be used as an untrusted proposal
 * because the administrator still confirms the displayed URL before it is
 * persisted. SERVER_NAME remains the host source to avoid Host-header poisoning.
 *
 * @param string|null $root
 * @param bool $preferRequestHost Use a validated request host during explicit migration.
 * @return string
 */
function fp_setup_baseurl_candidate($root = null, $preferRequestHost = false) {
	$context = fp_public_request_context(array(), true);
	$scheme = (string)$context ['scheme'];
	$port = (int)$context ['port'];

	$host = canonical_server_name();
	if ($preferRequestHost) {
		$requestHost = canonical_request_host();
		$requestHost = fp_connection_host_without_port($requestHost);
		if ($requestHost !== '' && $requestHost !== 'localhost') {
			$host = $requestHost;
		}
	}
	$host = fp_connection_host_with_public_port($host, $scheme, $port);

	if ($root === null) {
		$root = defined('BLOG_ROOT') ? (string)BLOG_ROOT : '/';
	}
	$root = (string)$root;
	if ($root === '') {
		$root = '/';
	}
	if ($root [0] !== '/') {
		$root = '/' . $root;
	}
	if (substr($root, -1) !== '/') {
		$root .= '/';
	}

	$url = normalize_baseurl($scheme . '://' . $host . $root);
	if ($url !== '') {
		return $url;
	}
	return 'http://localhost/';
}

/**
 * Returns true when an existing installation intentionally entered migration mode:
 * settings.conf.php exists, but the setup lock marker is missing.
 *
 * This deliberately does not compare host names. DNS aliases, DynDNS, CDN CNAMEs,
 * split-DNS and reverse proxies are valid operating modes and must not trigger
 * migration automatically.
 *
 * @return bool
 */
function fp_setup_migration_mode() {
	static $active = null;
	if ($active !== null) {
		return $active;
	}
	if (!defined('CONFIG_FILE') || !defined('LOCKFILE')) {
		return $active = false;
	}

	$config = resolve_abspath((string)CONFIG_FILE);
	$lock = resolve_abspath((string)LOCKFILE);

	return $active = ($config !== '' && is_file($config) && $lock !== '' && !is_file($lock));
}

/**
 * Derive the currently requested public base URL for the explicit migration mode.
 *
 * The request host is used only in migration mode and only as a temporary runtime
 * value. It is not persisted unless an authenticated admin saves the config form.
 * canonical_request_host() validates the host syntax and falls back safely.
 *
 * @return string
 */
function fp_setup_migration_current_baseurl() {
	return fp_setup_baseurl_candidate(defined('BLOG_ROOT') ? (string)BLOG_ROOT : '/', true);
}

/**
 * Normalize a filesystem path for prefix checks and marker payloads.
 *
 * @param string $path
 * @return string
 */
function fp_setup_migration_normalize_path($path) {
	$path = str_replace('\\', '/', (string)$path);
	return rtrim($path, '/');
}

/**
 * Return the cache marker path used to avoid repeated expensive directory scans
 * within one migration on the same filesystem location.
 *
 * @return string
 */
function fp_setup_migration_cache_marker() {
	return resolve_abspath((string)CACHE_DIR . '%%migration.cache-cleared');
}

/**
 * Build the cache-clear context for the current migration.
 *
 * @return string
 */
function fp_setup_migration_cache_context() {
	$config = defined('CONFIG_FILE') ? resolve_abspath((string)CONFIG_FILE) : '';
	$stat = ($config !== '' && is_file($config)) ? @stat($config) : false;
	$mtime = is_array($stat) ? (int)($stat ['mtime'] ?? 0) : 0;
	$size = is_array($stat) ? (int)($stat ['size'] ?? 0) : 0;

	return sha1(fp_setup_migration_normalize_path((string)ABS_PATH) . '|' . $config . '|' . $mtime . '|' . $size);
}

/**
 * Safely clear contents of a FlatPress cache-like directory without following
 * symlinks and without deleting the base directory itself.
 *
 * @param string $directory
 * @return bool
 */
function fp_setup_migration_clear_directory($directory) {
	$path = resolve_abspath((string)$directory);
	if ($path === '' || !is_dir($path) || is_link($path)) {
		return false;
	}

	$contentRoot = resolve_abspath((string)FP_CONTENT);
	$contentRootReal = realpath($contentRoot);
	$pathReal = realpath($path);
	if ($contentRootReal === false || $pathReal === false) {
		return false;
	}

	$contentRootNorm = fp_setup_migration_normalize_path($contentRootReal);
	$pathNorm = fp_setup_migration_normalize_path($pathReal);
	if ($pathNorm === $contentRootNorm || strpos($pathNorm . '/', $contentRootNorm . '/') !== 0) {
		return false;
	}

	$ok = true;
	try {
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($pathReal, FilesystemIterator::SKIP_DOTS),
			RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ($iterator as $item) {
			/** @var SplFileInfo $item */
			$itemPath = $item->getPathname();
			if ($item->isLink() || $item->isFile()) {
				if (!@unlink($itemPath) && file_exists($itemPath)) {
					$ok = false;
				}
				continue;
			}
			if ($item->isDir()) {
				if (!@rmdir($itemPath) && is_dir($itemPath)) {
					$ok = false;
				}
			}
		}
	} catch (Exception $e) {
		return false;
	}

	return $ok;
}

/**
 * Clear cache and compile artifacts when an existing installation enters
 * explicit migration mode by deleting %%setup.lock.
 *
 * @return bool
 */
function fp_setup_migration_clear_artifacts() {
	if (!fp_setup_migration_mode()) {
		return false;
	}

	$marker = fp_setup_migration_cache_marker();
	$context = fp_setup_migration_cache_context();
	if ($marker !== '' && is_file($marker)) {
		$oldContext = @file_get_contents($marker);
		if (is_string($oldContext) && trim($oldContext) === $context) {
			return true;
		}
	}

	$cacheOk = fp_setup_migration_clear_directory((string)CACHE_DIR);
	$compileOk = fp_setup_migration_clear_directory((string)COMPILE_DIR);

	if ($marker !== '') {
		$markerDir = dirname($marker);
		if (!is_dir($markerDir)) {
			@mkdir($markerDir, DIR_PERMISSIONS, true);
		}
		@file_put_contents($marker, $context . "\n", LOCK_EX);
		@chmod($marker, FILE_PERMISSIONS);
	}

	if (function_exists('apcu_delete_key')) {
		@apcu_delete_key('config:settings:' . sha1(resolve_abspath((string)CONFIG_FILE)));
	}

	return $cacheOk || $compileOk;
}

/**
 * Mark the explicit migration as completed after settings.conf.php was saved.
 *
 * @return bool
 */
function fp_setup_migration_write_lockfile() {
	if (!defined('LOCKFILE')) {
		return false;
	}
	$lock = resolve_abspath((string)LOCKFILE);
	if ($lock === '') {
		return false;
	}
	$dir = dirname($lock);
	if (!is_dir($dir) && !@mkdir($dir, DIR_PERMISSIONS, true) && !is_dir($dir)) {
		return false;
	}
	$result = @file_put_contents($lock, 'locked', LOCK_EX);
	if ($result === false) {
		return false;
	}
	@chmod($lock, FILE_PERMISSIONS);

	if (is_file($lock)) {
		$marker = function_exists('fp_setup_migration_cache_marker') ? fp_setup_migration_cache_marker() : '';
		if ($marker !== '' && is_file($marker)) {
			@unlink($marker);
		}
		return true;
	}

	return false;
}

/**
 * Define BLOG_BASEURL here (preferred) to avoid Host header injection.
 */
if (!defined('BLOG_BASEURL')) {
	$blog_root = defined('BLOG_ROOT') ? (string)BLOG_ROOT : '/';
	$cfg_url = configured_blog_baseurl();

	if (fp_setup_migration_mode()) {
		$migration_url = fp_setup_migration_current_baseurl();
		define('FP_SETUP_MIGRATION_MODE', true);
		define('BLOG_BASEURL', $migration_url);
		define('BLOG_BASEURL_TRUSTED', false);
		define('BLOG_BASEURL_MIGRATION_CANDIDATE', $migration_url);

		$cfg_file = resolve_abspath((string)CONFIG_FILE);
		$migration_cfg = load_fp_config_file($cfg_file);
		if (is_array($migration_cfg)) {
			if (!isset($migration_cfg ['general']) || !is_array($migration_cfg ['general'])) {
				$migration_cfg ['general'] = array();
			}
			$migration_cfg ['general'] ['www'] = $migration_url;
			$GLOBALS ['EARLY_FP_CONFIG'] = $migration_cfg;
		}

		fp_setup_migration_clear_artifacts();
	} elseif ($cfg_url !== '') {
		define('BLOG_BASEURL', $cfg_url);
		define('BLOG_BASEURL_TRUSTED', true);
	} else {
		/**
		 * First-installation/runtime fallback: build a safe public URL candidate from SERVER_NAME.
		 * Forwarded scheme/port values may influence only this non-persisted proposal.
		 */
		define('BLOG_BASEURL', fp_setup_baseurl_candidate($blog_root, false));
		define('BLOG_BASEURL_TRUSTED', false);
	}
}
if (!defined('BLOG_BASEURL_TRUSTED')) {
	$configuredForTrust = configured_blog_baseurl();
	$normalizedDefinedBase = defined('BLOG_BASEURL') ? normalize_baseurl((string)BLOG_BASEURL) : '';
	define('BLOG_BASEURL_TRUSTED', $configuredForTrust !== '' && $normalizedDefinedBase === $configuredForTrust);
}
if (!defined('FP_SETUP_MIGRATION_MODE')) {
	define('FP_SETUP_MIGRATION_MODE', false);
}

/**
 * Return whether canonical HTTPS enforcement is applicable to a base URL.
 * Keeping this policy separate makes it explicit that a first-installation
 * URL proposal is not yet trusted configuration.
 *
 * @param string $baseUrl
 * @param bool $baseUrlTrusted
 * @param bool $requestIsHttps
 * @return bool
 */
function fp_https_redirect_required($baseUrl, $baseUrlTrusted, $requestIsHttps) {
	if (!$baseUrlTrusted || $requestIsHttps) {
		return false;
	}
	$parts = @parse_url((string)$baseUrl);
	return is_array($parts)
		&& strtolower((string)($parts ['scheme'] ?? '')) === 'https'
		&& (string)($parts ['host'] ?? '') !== '';
}

/**
 * Enforce HTTPS when a trusted, configured BLOG_BASEURL is HTTPS.
 * This is a canonical upgrade redirect (HTTP -> HTTPS) only.
 *
 * The HTTPS decision is centralized in is_https()/fp_public_request_context();
 * no second proxy-header parser is maintained here. This prevents redirect and
 * cookie security decisions from disagreeing on TLS-terminating infrastructures.
 */
function enforce_https_if_configured(): void {
	if (fp_setup_migration_mode()) {
		return;
	}
	if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg' || headers_sent() || !defined('BLOG_BASEURL')) {
		return;
	}

	/**
	 * A first-installation URL is only an administrator-visible proposal. It
	 * must never trigger a canonical redirect before setup has persisted the
	 * URL and, where needed, explicit forwarded-scheme trust. Otherwise a TLS
	 * terminating host that exposes only X-Forwarded-Proto can redirect the
	 * HTTPS setup request to itself indefinitely.
	 */
	$baseUrlTrusted = defined('BLOG_BASEURL_TRUSTED') && BLOG_BASEURL_TRUSTED === true;
	$requestIsHttps = is_https();
	if (!fp_https_redirect_required((string)BLOG_BASEURL, $baseUrlTrusted, $requestIsHttps)) {
		return;
	}

	$parts = @parse_url((string)BLOG_BASEURL);
	if (!is_array($parts)) {
		return;
	}

	$host = (string)($parts ['host'] ?? '');
	if ($host === '') {
		return;
	}
	$port = (int)($parts ['port'] ?? 0);
	$pathBase = (string)($parts ['path'] ?? '/');
	if ($pathBase === '') {
		$pathBase = '/';
	}
	if ($pathBase [0] !== '/') {
		$pathBase = '/' . $pathBase;
	}
	if (substr($pathBase, -1) !== '/') {
		$pathBase .= '/';
	}

	$reqUri = (string)($GLOBALS ['RAW_REQUEST_URI'] ?? ($_SERVER ['REQUEST_URI'] ?? '/'));
	$reqUri = trim(str_replace(array("\r", "\n"), '', $reqUri));
	if ($reqUri === '') {
		$reqUri = '/';
	}
	if (preg_match('~^https?://~i', $reqUri)) {
		$requestParts = @parse_url($reqUri);
		if (is_array($requestParts)) {
			$reqUri = (string)($requestParts ['path'] ?? '/');
			if ($reqUri === '') {
				$reqUri = '/';
			}
			if (isset($requestParts ['query']) && $requestParts ['query'] !== '') {
				$reqUri .= '?' . $requestParts ['query'];
			}
		}
	}
	if ($reqUri [0] !== '/') {
		$reqUri = '/' . ltrim($reqUri, '/');
	}

	$targetPath = strpos($reqUri, $pathBase) === 0 ? $reqUri : $pathBase;
	$hostForUrl = filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false ? '[' . $host . ']' : $host;
	$target = 'https://' . $hostForUrl;
	if ($port > 0 && $port !== 443) {
		$target .= ':' . $port;
	}
	$target .= $targetPath;

	$method = strtoupper((string)($_SERVER ['REQUEST_METHOD'] ?? 'GET'));
	$status = ($method === 'GET' || $method === 'HEAD') ? 301 : 307;
	header('Location: ' . $target, true, $status);
	exit;
}

enforce_https_if_configured();
?>
