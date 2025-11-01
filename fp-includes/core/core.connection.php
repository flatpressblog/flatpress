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
?>
