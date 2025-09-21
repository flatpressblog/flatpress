<?php
// core.connenction.php
// Adding security and HTTPS support

/**
 * OWASP - Browser Cache - How can the browser cache be used in attacks?
 * https://www.owasp.org/index.php/OWASP_Application_Security_FAQ#How_can_the_browser_cache_be_used_in_attacks.3F
 *
 * http://stackoverflow.com/questions/13640109/how-to-prevent-browser-cache-for-php-site
 */
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

//
// End of send header
//

function ip_in_cidrs(string $ip, array $cidrs): bool {
	if ($ip === '') {
		return false;
	}
	$binIp = inet_pton($ip);
	if ($binIp === false) {
		return false;
	}
	foreach ($cidrs as $cidr) {
		if (strpos($cidr, '/') === false) {
			if ($ip === $cidr) {
				return true;
			}
			continue;
		}
		list($net, $mask) = explode('/', $cidr, 2);
		$binNet = inet_pton($net);
		if ($binNet === false) {
			continue;
		}
		$mask = (int)$mask;
		$bytes = intdiv($mask, 8);
		$bits = $mask % 8;
		if (strlen($binIp) !== strlen($binNet)) { // IPv4 vs IPv6
			continue;
		}
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
		return true;
	}
	return false;
}

/**
 * Checks if FlatPress is called via HTTPS.
 *
 * @return boolean <code>true</code> when FlatPress is called via HTTPS; <code>false</code> otherwise.
 */
function is_https(array $trustedProxies = []) {
	// CLI/DBG is never HTTPS
	if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
		return false;
	}

	// Directly at Origin: reliable server signals
	$https = (string)($_SERVER ['HTTPS'] ?? '');
	if ($https !== '' && strcasecmp($https, 'off') !== 0) {
		return true; // 'on', '1', etc.
	}
	if (!empty($_SERVER ['REQUEST_SCHEME']) && strcasecmp($_SERVER ['REQUEST_SCHEME'], 'https') === 0) {
		return true;
	}

	// Behind proxy: only if the proxy is trusted
	$remote  = $_SERVER ['REMOTE_ADDR'] ?? '';
	$trusted = $trustedProxies ? ip_in_cidrs($remote, $trustedProxies) : false;
	if ($trusted) {
		// RFC 7239: Forwarded: proto=https
		$fw = $_SERVER ['HTTP_FORWARDED'] ?? '';
		if ($fw && preg_match('/(^|[;,\\s])proto\\s*=\\s*https(\\b|$)/i', $fw)) {
			return true;
		}
		// X-Forwarded-Proto can be a list: "https, http"
		$xproto = $_SERVER ['HTTP_X_FORWARDED_PROTO'] ?? '';
		if ($xproto) {
			foreach (explode(',', strtolower($xproto)) as $tok) {
				if (trim($tok) === 'https') {
					return true;
				}
			}
		}
		if (!empty($_SERVER ['HTTP_X_FORWARDED_SSL']) && strcasecmp($_SERVER ['HTTP_X_FORWARDED_SSL'], 'on') === 0) {
			return true;
		}
		if (!empty($_SERVER ['HTTP_X_FORWARDED_SCHEME']) && strcasecmp($_SERVER ['HTTP_X_FORWARDED_SCHEME'], 'https') === 0) {
			return true;
		}
		if (!empty($_SERVER ['HTTP_FRONT_END_HTTPS']) && strcasecmp($_SERVER ['HTTP_FRONT_END_HTTPS'], 'on') === 0) {
			return true; // IIS/ARR
		}
		if (!empty($_SERVER ['HTTP_X_ARR_SSL'])) {
			return true; // Azure ARR
		}
		if (!empty($_SERVER ['HTTP_CF_VISITOR']) && stripos($_SERVER ['HTTP_CF_VISITOR'], '"scheme":"https"') !== false) {
			return true; // Cloudflare
		}
		if (!empty($_SERVER ['HTTP_X_FORWARDED_PORT']) && (int)$_SERVER ['HTTP_X_FORWARDED_PORT'] === 443) {
			return true;
		}
	}

	// Port 443 on Origin has a weaker signal, but often helps.
	if ((int)($_SERVER ['SERVER_PORT'] ?? 0) === 443) {
		return true;
	}

	return false;
}

if (isset($_SERVER ['HTTPS'])) {
	$_SERVER ['HTTPS'] = htmlspecialchars($_SERVER ['HTTPS'], ENT_QUOTES, "UTF-8");
}

// Supports Apache and IIS
$serverport = '';
if (is_https()) {
	// HTTPS enabled
	$serverport = "https://";
} else {
	// HTTP only
	$serverport = "http://";
}

// Compatibility with ISS
$_SERVER ["REQUEST_URI"] = htmlspecialchars($_SERVER ["REQUEST_URI"] ?? '', ENT_QUOTES, "UTF-8");
if ($_SERVER ["REQUEST_URI"] === '') {
	$_SERVER ['REQUEST_URI'] = $serverport . 'localhost/flatpress/';
}
?>
