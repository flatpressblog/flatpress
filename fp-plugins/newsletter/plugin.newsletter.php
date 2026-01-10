<?php
/**
 * Plugin Name: Newsletter
 * Version: 1.7.3
 * Plugin URI: https://flatpress.org
 * Author: FlatPress
 * Author URI: https://flatpress.org
 * Description: Offers a newsletter subscription widget, stores email addresses in compliance with GDPR and sends the newsletter monthly. <a href="./fp-plugins/newsletter/doc_newsletter.txt" title="Instructions" target="_blank">[Instructions]</a>
 */

// Preventing direct access
defined('FP_CONTENT') or exit;

global $fp_config;

// Constants
if (!defined('PLUGIN_NEWSLETTER_DIR')) {
	define('PLUGIN_NEWSLETTER_DIR', FP_CONTENT . 'plugin_newsletter/');
}
if (!defined('PLUGIN_NEWSLETTER_KEY')) {
	define('PLUGIN_NEWSLETTER_KEY', $fp_config ['general'] ['blogid']);
}

if (!defined('NEWSLETTER_BLOCKLIST_URL')) {
	/**
	 * Remote source of the blocklist
	 * Contains a list of disposable and temporary email address domains to register dummy users or to prevent spam/abuse.
	 * https://github.com/disposable-email-domains/disposable-email-domains/blob/main/LICENSE.txt
	 *
	 * CC0 1.0 Universal (CC0 1.0)
	 * Public Domain Dedication
	 * No Copyright
	 */
	define('NEWSLETTER_BLOCKLIST_URL', 'https://raw.githubusercontent.com/disposable-email-domains/disposable-email-domains/refs/heads/main/disposable_email_blocklist.conf');
}

if (!defined('FP_NEWSLETTER_DEFAULT_OPTIONS')) {
	// Number of subscribers sent per day
	define('FP_NEWSLETTER_DEFAULT_OPTIONS', ['batch_size' => 30]);

	/**
	 * Loads the saved newsletter options or uses the defaults.
	 *
	 * @return array ['batch_size' => int]
	 */
	function plugin_newsletter_get_options() {
		global $fp_config;
		$cfg = $fp_config ['plugins'] ['newsletter'] ?? [];
		// Cast all values as int
		return array_merge(FP_NEWSLETTER_DEFAULT_OPTIONS, array_map('intval', $cfg));
	}
}

// Stack size from configuration or default
$newsletter_opts = plugin_newsletter_get_options();
if (!defined('PLUGIN_NEWSLETTER_BATCH_SIZE')) {
	define('PLUGIN_NEWSLETTER_BATCH_SIZE', (int)$newsletter_opts ['batch_size']);
}

function plugin_newsletter_setup() {
	return function_exists('plugin_lastentries_widget') ? 1 : -2;
}

// Copy bundled static pages (only once) into content/static/
$staticLang = isset($fp_config ['locale'] ['lang']) ? $fp_config ['locale'] ['lang'] : 'en-us';
if ($staticLang) {
	// Where the plugin’s lang files live
	$srcDir  = dirname(__FILE__) . '/static/' . $staticLang . '/';
	// Where your site expects its static pages
	$destDir = FP_CONTENT . 'content/static/';

	if (is_dir($srcDir)) {
		// Only copy if not already present
		foreach (array('throttle-limit.txt', 'check-your-email.txt', 'invalid-email.txt', 'invalid-token.txt', 'subscription-confirmed.txt', 'unsubscribe-success.txt', 'legal-notice.txt', 'privacy-policy.txt') as $file) {
			$src = $srcDir . $file;
			$dest = $destDir . $file;
			if (file_exists($src) && !file_exists($dest)) {
				$tmp = $dest . '.tmp';
				@copy($src, $tmp);
				@chmod($tmp, FILE_PERMISSIONS);
				@rename($tmp, $dest);
			}
		}
	}
}

/**
 * Performs an atomic read/write operation.
 * Locks the file exclusively, reads all lines, passes them to
 * $callback, and writes the result back.
 *
 * @param string $file Path to the file
 * @param callable $callback Function(array $lines): array $newLines
 * @return bool true on success, false on failure
 */
function plugin_newsletter_rmw_file(string $file, callable $callback): bool {
	$dir = dirname($file);
	if ($dir && !is_dir($dir)) {
		$mode = DIR_PERMISSIONS;
		@mkdir($dir, $mode, true);
	}

	$fp = @fopen($file, 'c+');
	if (!$fp || !flock($fp, LOCK_EX)) {
		if ($fp) {
			fclose($fp);
		}
		return false;
	}

	// Determine file size
	$stat = fstat($fp);
	$size = $stat ['size'] ?? 0;
	// Read full content
	rewind($fp);
	$content = $size > 0 ? fread($fp, $size) : '';
	// Split into lines and remove empty ones
	$lines = $content === '' ? [] : array_filter(explode("\n", str_replace(["\r\n","\r"], "\n", rtrim($content, "\r\n"))), 'strlen');

	$newLines = $callback($lines);
	rewind($fp);
	ftruncate($fp, 0);
	fwrite($fp, implode(PHP_EOL, $newLines) . (count($newLines) ? PHP_EOL : ''));
	fflush($fp);
	flock($fp, LOCK_UN);
	fclose($fp);
	return true;
}

/**
 * Read atomically: Shared lock, then return rows.
 *
 * @param string $file
 * @return string[] Array of lines or [] in case of error/not available
 */
function plugin_newsletter_read_lines(string $file): array {
	if (!file_exists($file)) {
		return [];
	}
	$fp = @fopen($file, 'r');
	if (!$fp || !flock($fp, LOCK_SH)) {
		if ($fp) {
			fclose($fp);
		}
		return [];
	}
	$stat = fstat($fp);
	$size = $stat ['size'] ?? 0;
	$content = $size > 0 ? fread($fp, $size) : '';
	flock($fp, LOCK_UN);
	fclose($fp);
	if ($content === '') {
		return [];
	}
	// Normiere auf "\n" und splitte
	$normalized = str_replace(["\r\n", "\r"], "\n", rtrim($content, "\r\n"));
	return array_filter(explode("\n", $normalized), 'strlen');
}


/**
 * Fetch a URL with broad hosting compatibility.
 * Prefers cURL (works even if allow_url_fopen is off), then falls back to streams.
 *
 * @param string      $url
 * @param int|null    $httpCode Filled with the HTTP status code when available
 * @param string|null $error    Filled with a short error description when available
 * @return string|null Response body on success, null on failure
 */
function plugin_newsletter_http_get(string $url, ?int &$httpCode = null, ?string &$error = null): ?string {
	$httpCode = null;
	$error = null;

	// Prefer cURL if available
	if (function_exists('curl_init')) {
		$ch = curl_init($url);
		if ($ch !== false) {
			curl_setopt_array($ch, [
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_MAXREDIRS => 5,
				CURLOPT_CONNECTTIMEOUT => 5,
				CURLOPT_TIMEOUT => 10,
				CURLOPT_USERAGENT => 'FlatPress Newsletter Plugin',
				CURLOPT_HTTPHEADER => [
					'Accept: text/plain, */*;q=0.8'
				],
				CURLOPT_SSL_VERIFYPEER => true,
				CURLOPT_SSL_VERIFYHOST => 2,
			]);

			$data = curl_exec($ch);
			$errno = curl_errno($ch);
			$errstr = curl_error($ch);
			$code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if (!is_php85_plus()) {
				curl_close($ch);
			}

			if ($errno === 0 && is_string($data) && $data !== '') {
				$httpCode = $code;
				if ($code >= 200 && $code < 300) {
					return $data;
				}
				$error = 'HTTP ' . $code;
				return null;
			}

			$httpCode = $code ?: null;
			$error = $errno ? ('cURL error ' . $errno . ': ' . $errstr) : ('HTTP ' . $code);
			// continue to stream fallback
		}
	}

	// Fallback: streams (requires allow_url_fopen)
	if (ini_get('allow_url_fopen')) {
		$context = stream_context_create([
			'http' => [
				'method' => 'GET',
				'timeout' => 10,
				'follow_location' => 1,
				'max_redirects' => 5,
				'header' => "User-Agent: FlatPress Newsletter Plugin\r\nAccept: text/plain, */*;q=0.8\r\n",
			],
			'ssl' => [
				'verify_peer' => true,
				'verify_peer_name' => true,
			],
		]);

		/** @var array<int, string> $http_response_header */
		$http_response_header = [];

		$data = @file_get_contents($url, false, $context);

		// Determine final HTTP code (after redirects, if any)
		/** @var array<int, string> $headers */
		$headers = $http_response_header;
		foreach ($headers as $h) {
			if (preg_match('#^HTTP/[\d.]+\s+(\d{3})#', $h, $m)) {
				$httpCode = (int) $m[1];
			}
		}

		if ($data !== false && $data !== '') {
			if ($httpCode === null || ($httpCode >= 200 && $httpCode < 300)) {
				return $data;
			}
			$error = 'HTTP ' . $httpCode;
			return null;
		}

		$error = $httpCode !== null ? ('HTTP ' . $httpCode) : 'stream error';
		return null;
	}

	$error = 'allow_url_fopen disabled and cURL not available';
	return null;
}


// Initialization on every page request
plugin_newsletter_init();

/**
 * Initializes directory, processes forms and checks dispatch date
 */
function plugin_newsletter_init() {
	if (!is_dir(PLUGIN_NEWSLETTER_DIR)) {
		@mkdir(PLUGIN_NEWSLETTER_DIR, DIR_PERMISSIONS, true);
	}

	$sub_file = PLUGIN_NEWSLETTER_DIR . 'subscribers.txt';
	$date_file = PLUGIN_NEWSLETTER_DIR . 'next-send-date.txt';
	$pending_file = PLUGIN_NEWSLETTER_DIR . 'pending.txt';
	$offset_file = PLUGIN_NEWSLETTER_DIR . 'batch-offset.txt';
	$manual_flag_file = PLUGIN_NEWSLETTER_DIR . 'manual-flag.txt';

	// Honeypot throttle: cleanup expired blocks at most once per 24 h
	$blocked_file = PLUGIN_NEWSLETTER_DIR . 'blocked-ips.txt';
	$last_clean_file = PLUGIN_NEWSLETTER_DIR . 'blocked-ips-last-clean.txt';

	// Only retains IPs < 24 hours
	if (!file_exists($last_clean_file) || filemtime($last_clean_file) < time() - 24 * 3600) {
		plugin_newsletter_rmw_file($blocked_file, function(array $lines): array {
			$filtered = [];
			foreach ($lines as $line) {
				// Extract timestamp only
				list(, $ts) = explode('|', $line, 2);
				if (time() - (int)$ts < 24 * 3600) {
					$filtered [] = $line;
				}
			}
			return $filtered;
		});
		touch($last_clean_file);
	}

	// Daily cleanup for pending
	$expiry = 24 * 3600; // 24 hours
	$lines = plugin_newsletter_read_lines($pending_file);
	$now = time();
	// Only keeps entries < $expiry
	plugin_newsletter_rmw_file($pending_file, function(array $lines) use ($expiry, $now): array {
		$filtered = [];
		foreach ($lines as $line) {
			[,, $ts] = explode('|', $line, 3);
			if ($now - (int)$ts < $expiry) {
				$filtered [] = $line;
			}
		}
		return $filtered;
	});

	// Admin: Delete a subscriber via POST, CSRF-protected
	if ($_SERVER ['REQUEST_METHOD'] === 'POST' && isset($_POST ['newsletter_delete'])) {
		// Check CSRF token
		if (!hash_equals($_SESSION ['newsletter_admin_csrf_token'] ?? '', $_POST ['csrf_token'] ?? '')) {
			header('HTTP/1.1 400 Bad Request');
			exit('Invalid CSRF token');
		}
		plugin_newsletter_handle_admin_delete($_POST ['newsletter_delete']);
		exit;
	}

	// Admin: Sends the newsletter immediately to all subscribers
	if ($_SERVER ['REQUEST_METHOD'] === 'POST' && isset($_POST ['newsletter_send_all'])) {

		// No manual dispensing during a running batch
		$offsetLines = plugin_newsletter_read_lines($offset_file);
		$offset = (int) trim($offsetLines [0] ?? '0');

		// Determine total number of subscribers
		$subs = plugin_newsletter_read_lines($sub_file);
		$total = count($subs);

		// If offset >0 and <total, another batch is still running.
		if ($offset > 0 && $offset < $total) {
			// Cancel
			header('Location: ' . $_SERVER ['REQUEST_URI']);
			exit;
		}

		// Create a flag indicating manual batch dispatch is in progress
		if (!file_exists($manual_flag_file)) {
			file_put_contents($manual_flag_file, 'manual', LOCK_EX);
		}

		// Check CSRF token (Admin "Send all")
		if (!hash_equals($_SESSION ['newsletter_admin_csrf_token'] ?? '', $_POST ['csrf_token'] ?? '')) {
			header('HTTP/1.1 400 Bad Request');
			exit('Invalid CSRF token');
		}

		// Send and then redirect back with a success flag
		plugin_newsletter_send_all($sub_file);
		$redirect = isset($_SERVER ['HTTP_REFERER']) ? $_SERVER ['HTTP_REFERER'] : $_SERVER ['REQUEST_URI'];
		$separator = (strpos($redirect, '?') !== false) ? '&' : '?';
		header('Location: ' . $redirect . $separator . 'success=1');
		exit;
	}

	// Double opt-in: confirmation via link
	if (isset($_GET ['newsletter_action'])) {
		if ($_GET ['newsletter_action'] === 'unsubscribe' && isset($_GET ['email'])) {
			plugin_newsletter_handle_unsubscribe($_GET ['email']);
		} elseif ($_GET ['newsletter_action'] === 'confirm' && isset($_GET ['email']) && isset($_GET ['token'])) {
			plugin_newsletter_handle_confirm();
		}
	}

	if ($_SERVER ['REQUEST_METHOD'] === 'POST' && isset($_POST ['newsletter_submit'])) {
		plugin_newsletter_handle_subscribe();
	}

	plugin_newsletter_check_and_send($date_file, $sub_file);
}

/**
 * Processes the confirmation link (double opt-in).
 */
function plugin_newsletter_handle_confirm() {

	$email = $_GET ['email'] ?? '';
	$token = $_GET ['token'] ?? '';
	if (!filter_var($email, FILTER_VALIDATE_EMAIL) || empty($token)) {
		header('Location: ' . BLOG_BASEURL . '?page=invalid-token');
		exit;
	}

	$pending_file = PLUGIN_NEWSLETTER_DIR . 'pending.txt';
	if (!file_exists($pending_file)) {
		header('Location: ' . BLOG_BASEURL . '?page=invalid-token');
		exit;
	}

	$lines = plugin_newsletter_read_lines($pending_file);
	$remaining = [];
	$confirmed = false;
	$now = time();
	$expiry = 24 * 3600; // 24 hours in seconds
	foreach ($lines as $line) {
		list($enc_email, $token_line, $timestamp) = explode('|', $line);
		$dec_email = plugin_newsletter_decrypt($enc_email);

		// Check process
		if ($now - (int)$timestamp > $expiry) {
			// Expired: simply remove from pending
			continue;
		}

		// Check token & e-mail
		if (!$confirmed && $dec_email === $email && hash_equals($token_line, $token)) {
			// Transfer to final list
			$sub_file = PLUGIN_NEWSLETTER_DIR . 'subscribers.txt';
			// Add confirmed subscriber
			plugin_newsletter_rmw_file($sub_file, function(array $lines) use ($enc_email, $timestamp): array {
				$lines [] = $enc_email . '|' . $timestamp;
				return $lines;
			});
			$confirmed = true;
		} else {
			// Still outstanding
			$remaining [] = $line;
		}
	}

	// Update pending list - overwrite pending list with remaining entries
	plugin_newsletter_rmw_file($pending_file, function(array $lines) use ($remaining): array {
		return $remaining;
	});

	// Forwarding depending on the result
	if ($confirmed) {
		header('Location: ' . BLOG_BASEURL . '?page=subscription-confirmed');
	} else {
		header('Location: ' . BLOG_BASEURL . '?page=invalid-token');
	}
	exit;
}

/**
 * Newsletter-Widget
 */
function plugin_newsletter_widget(){
	// Generate CSRF token and save in session (frontend scope)
	$token = bin2hex(random_bytes(32));
	$_SESSION ['newsletter_widget_csrf_token'] = $token;

	// Honeypot: Hiding the widget for blocked IPs
	$blocked_file = PLUGIN_NEWSLETTER_DIR . 'blocked-ips.txt';
	$ip = plugin_newsletter_get_client_ip();
	foreach (plugin_newsletter_read_lines($blocked_file) as $line) {
		list($blocked_ip, $ts) = explode('|', $line);
		if ($blocked_ip === $ip && time() - (int)$ts < 24 * 3600) {
			return ['subject' => '', 'content' => ''];
		}
	}

	// Dynamic honeypot field name to evade simple bots
	$hp_field = 'name_' . substr(hash('sha256', session_id()), 0, 8);
	$_SESSION ['newsletter_hp_field'] = $hp_field;

	$lang = lang_load('plugin:newsletter');
	$html = '<form method="post" action="' . htmlspecialchars(BLOG_BASEURL, ENT_QUOTES) . '">' . //
		'<input type="hidden" name="newsletter_widget_csrf_token" value="' . $token . '"><ul>' . //
		// Honeypot field, hidden from real users
		'<li style="display: none; visibility: hidden; height:0; overflow: hidden;"><input type="text" name="' . $hp_field . '" value="" autocomplete="off" tabindex="-1"></li>' . //
		'<li><input type="email" name="newsletter_email" placeholder="' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['input_email_placeholder'], ENT_QUOTES).'" required></li>' . //
		'<li><label><input type="checkbox" name="newsletter_privacy" required> <a href="' . htmlspecialchars(BLOG_BASEURL.'?page=privacy-policy', ENT_QUOTES).'" target="_blank" rel="noopener">' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['accept_privacy_policy'], ENT_QUOTES) . '</a></label></li>' . //
		'<li><input type="submit" name="newsletter_submit" style="cursor: pointer;" value="' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['button'], ENT_QUOTES) . '"></li>' . //
		'</ul>' . //
		'</form>';
	return ['subject' => $lang ['plugin'] ['newsletter'] ['subject'], 'content' => $html];
}
register_widget('newsletter', 'Newsletter', 'plugin_newsletter_widget');


/**
 * Selects encryption method (OpenSSL or Sodium)
 */
function plugin_newsletter_get_crypto() {
	if (function_exists('openssl_encrypt') && function_exists('openssl_decrypt')) {
		$method = 'openssl';
	} elseif (function_exists('sodium_crypto_secretbox')) {
		$method = 'sodium';
	} else {
		throw new Exception('[Newsletter-plugin] No supported encryption library available.');
	}
	$key = PLUGIN_NEWSLETTER_KEY;
	if ($method === 'sodium') {
		$key = hash('sha256', $key, true);
	}
	return ['method' => $method, 'key' => $key];
}

/**
 * Encrypted e-mail address
 */
function plugin_newsletter_encrypt($plaintext) {
	$crypto = plugin_newsletter_get_crypto();
	if ($crypto ['method'] === 'openssl') {
		$iv_len = openssl_cipher_iv_length('AES-256-CBC');
		$iv = openssl_random_pseudo_bytes($iv_len);
		$cipher = openssl_encrypt($plaintext, 'AES-256-CBC', $crypto ['key'], OPENSSL_RAW_DATA, $iv);
		return base64_encode($iv . $cipher);
	} else {
		$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
		$cipher = sodium_crypto_secretbox($plaintext, $nonce, $crypto ['key']);
		return base64_encode($nonce . $cipher);
	}
}

/**
 * Decrypted e-mail address
 */
function plugin_newsletter_decrypt($data) {
	$crypto = plugin_newsletter_get_crypto();
	$bin = base64_decode($data);
	if ($crypto ['method'] === 'openssl') {
		$iv_len = openssl_cipher_iv_length('AES-256-CBC');
		$iv = substr($bin, 0, $iv_len);
		$cipher = substr($bin, $iv_len);
		return openssl_decrypt($cipher, 'AES-256-CBC', $crypto ['key'], OPENSSL_RAW_DATA, $iv);
	} else {
		$nonce = substr($bin, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
		$cipher = substr($bin, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
		$plain = sodium_crypto_secretbox_open($cipher, $nonce, $crypto ['key']);
		return $plain === false ? null : $plain;
	}
}

/**
 * Determines the real client IP for rate limiting and Honeypot check
 */
function plugin_newsletter_get_client_ip(): string {
	$ipSources = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP'];
	foreach ($ipSources as $hdr) {
		if (!empty($_SERVER [$hdr])) {
			// X-Forwarded-For can be a list: take the first entry
			$ip = $hdr === 'HTTP_X_FORWARDED_FOR' ? explode(',', $_SERVER [$hdr])[0] : $_SERVER [$hdr];
			$ip = trim($ip);
			if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
				return $ip;
			}
		}
	}
	// Fallback to remote addr (local, proxy or similar)
	return $_SERVER ['REMOTE_ADDR'] ?? '';
}

/**
 * Validates an email address for newsletter subscription.
 */
function plugin_newsletter_is_valid_email(string $email): bool {
	// Cleanup + IDN-Support
	$email = trim($email);
	if (strpos($email, '@') === false) {
		return false;
	}
	list($local, $domain) = explode('@', $email, 2);
	if (function_exists('idn_to_ascii')) {
		$asciiDomain = idn_to_ascii($domain, IDNA_DEFAULT, defined('INTL_IDNA_VARIANT_UTS46') ? INTL_IDNA_VARIANT_UTS46 : IDNA_DEFAULT);
		if ($asciiDomain === false) {
			return false;
		}
		$email = $local . '@' . $asciiDomain;
	}

	// Quick basic check incl. EAI domains according to Punycode
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		return false;
	}

	// RFC-5322/6530-6533 Regex + length limits
	$rfcRegex = '/^(?=.{1,254}$)(?=.{1,64}@)[\p{L}\p{N}.!#$%&\'*+\/=?^_`{|}~\-]+@(?:(?!-)[A-Za-z0-9-]{1,63}(?<!-)\.)+[A-Za-z]{2,63}$/xu';
	if (!preg_match($rfcRegex, $email)) {
		return false;
	}

	$logFile = PLUGIN_NEWSLETTER_DIR . 'failed-emails.txt';

	// DNS lookup for mail hosts
	[, $domain] = explode('@', $email, 2);

	// Prepare cache file, cleanup marker and current time
	$cacheFile = CACHE_DIR . 'newsletter-dns-cache.txt';
	$markerFile = PLUGIN_NEWSLETTER_DIR . 'dns-cleanup-marker.txt';
	$now = time();

	// Last cleanup timestamp (0 if never)
	$markerLines = plugin_newsletter_read_lines($markerFile);
	$lastRun = (int) ($markerLines [0] ?? '0');

	// Monthly cleaning of the DNS-cache: once after the 28th, not more than once a month
	if (date('j') >= 28 && date('Ym', $lastRun) !== date('Ym')) {
		$linesCache = plugin_newsletter_read_lines($cacheFile);
		$cacheTmp = [];
		foreach ($linesCache as $lineCache) {
			list($d, $st, $exp) = explode('|', $lineCache, 3) + [null, null, null];
			$cacheTmp [$d] = ['status' => $st, 'expires' => (int)$exp];
		}
		$subFile = PLUGIN_NEWSLETTER_DIR . 'subscribers.txt';
		$subsLines = plugin_newsletter_read_lines($subFile);
		$subDomains = [];
		foreach ($subsLines as $encEmail) {
			$emailDec = plugin_newsletter_decrypt(trim($encEmail));
			if (strpos($emailDec, '@') !== false) {
				list(, $dSub) = explode('@', $emailDec, 2);
				$subDomains [$dSub] = true;
			}
		}
		// Remove cache entries for domains no longer in subscribers
		foreach ($cacheTmp as $dTmp => $infoTmp) {
			if (!isset($subDomains [$dTmp])) {
				unset($cacheTmp [$dTmp]);
			}
		}
		if ($fpCh = @fopen($cacheFile, 'w')) {
			flock($fpCh, LOCK_EX);
			foreach ($cacheTmp as $dCh => $infoCh) {
				fwrite($fpCh, $dCh . '|' . $infoCh ['status'] . '|' . $infoCh ['expires'] . PHP_EOL);
			}
			flock($fpCh, LOCK_UN);
			fclose($fpCh);
		}
		unset($subsLines, $subDomains, $emailDec, $dSub);
		unset($linesCache, $cacheTmp);

		// Update cleanup marker to prevent rerun this month
		file_put_contents($markerFile, $now, LOCK_EX);
	}

	// Load existing cache
	$cache = [];
	foreach (plugin_newsletter_read_lines($cacheFile) as $lineCache) {
		list($d, $st, $exp) = explode('|', $lineCache, 3) + [null, null, null];
		$cache [$d] = ['status' => $st, 'expires' => (int)$exp];
	}

	// Check cached entry
	$fromCache = false;
	if (isset($cache [$domain]) && $cache [$domain] ['expires'] > $now) {
		if ($cache [$domain] ['status'] === 'valid') {
			$mailHosts = [$domain];
			$fromCache = true;
		} else {
			// Negative cache: domain known invalid
			return false;
		}
	}

	if (!$fromCache) {
		$mailHosts = [];
		$weights = [];

		// MX records via getmxrr()
		if (function_exists('getmxrr') && getmxrr($domain, $mailHosts, $weights)) {
			array_multisort($weights, $mailHosts);
		} else {
			// Fallback: dns_get_record(), distinction DNS error vs. no record
			$mxRecords = dns_get_record($domain, DNS_MX);
			if ($mxRecords === false) {
				// Temporary DNS error (SERVFAIL/Timeout): valid for the time being, without logging
				return true;
			}
			if (!empty($mxRecords)) {
				foreach ($mxRecords as $mx) {
					$mailHosts [] = $mx ['target'];
					$weights [] = $mx ['pri'];
				}
				array_multisort($weights, $mailHosts);
			// Last fallback option: A-Record or AAAA-Record
			} elseif (checkdnsrr($domain, 'A') || checkdnsrr($domain, 'AAAA')) {
				$mailHosts [] = $domain;
			} else {
				// No mail host found - logging with 3 error threshold in 96 days
				$now = time();
				$logFile = PLUGIN_NEWSLETTER_DIR . 'failed-emails.txt';
				$windowSeconds = 96 * 86400; // 96 days
				$maxFailures = 3; // Permanently invalid from the 3rd error

				// Collect old log entries in the 96-day window
				$recent = [];
				foreach (plugin_newsletter_read_lines($logFile) as $line) {
					list($enc, $ts) = explode('|', $line, 2) + [1 => 0];
					if ($now - (int)$ts < $windowSeconds) {
						$recent [] = ['enc' => $enc, 'ts' => (int)$ts];
					}
				}

				// Count DNS failures for this address
				$sameCount = 0;
				foreach ($recent as $entry) {
					if (plugin_newsletter_decrypt($entry ['enc']) === $email) {
						$sameCount++;
					}
				}

				// Error below the threshold: Add entry and leave valid
				if ($sameCount + 1 < $maxFailures) {
					$recent [] = ['enc' => plugin_newsletter_encrypt($email), 'ts' => $now];
					$lines = array_map(function($e) {
						return $e ['enc'] . '|' . $e ['ts'];
					}, $recent);
					plugin_newsletter_rmw_file($logFile, function(array $_) use ($lines): array {
						return $lines;
					});
					return true;
				}

				// After the 3rd error: permanently invalid
				return false;
			}
		}

		// Update DNS cache entry
		if (!empty($mailHosts)) {
			// Positive result: cache up to 84 days
			$ttlMax = 84 * 86400;
			$expiresDomain = $now + $ttlMax;
			$cache [$domain] = ['status' => 'valid', 'expires' => $expiresDomain];
		} else {
			// Negative result: cache for 7 days
			$expiresDomain = $now + (7 * 86400);
			$cache [$domain] = ['status' => 'invalid', 'expires' => $expiresDomain];
		}
		// Write back cache file
		if ($fpWs = @fopen($cacheFile, 'w')) {
			flock($fpWs, LOCK_EX);
			foreach ($cache as $dWs => $infoWs) {
				fwrite($fpWs, $dWs . '|' . $infoWs ['status'] . '|' . $infoWs ['expires'] . PHP_EOL);
			}
			flock($fpWs, LOCK_UN);
			fclose($fpWs);
		}

		// If successful, clean up any previously logged entries for this address
		if (!empty($mailHosts)) {
			$lines = plugin_newsletter_read_lines($logFile);
			plugin_newsletter_rmw_file($logFile, function(array $lines) use ($email): array {
				return array_filter($lines, function(string $enc) use ($email): bool {
					return plugin_newsletter_decrypt(trim($enc)) !== $email;
				});
			});
		}

	}
	// If at least one entry exists, the address is considered valid
	return !empty($mailHosts);
}

/**
 * Sanitizes and validates the sender e-mail used for the newsletter.
 * - Removes control characters and CRLF to prevent header/parameter injection.
 * - Extracts a plain address from "Name <email>" formats.
 * - Provides a safe fallback if the configured address is invalid.
 */
function plugin_newsletter_sanitize_from_email($from) {
	$from = (string) $from;

	// Remove CRLF and other control chars (defense-in-depth)
	$from = preg_replace('/[\x00-\x1F\x7F]+/', '', $from);
	$from = trim($from);

	// Extract address from "Name <email@domain>" if present
	if (preg_match('/<([^>]+)>/', $from, $m)) {
		$from = trim($m [1]);
	}

	if ($from !== '' && filter_var($from, FILTER_VALIDATE_EMAIL)) {
		return $from;
	}

	// Fallback: derive a domain from blog URL / host
	$host = '';
	if (defined('BLOG_BASEURL')) {
		$host = parse_url(BLOG_BASEURL, PHP_URL_HOST);
	}
	if (!$host) {
		$host = $_SERVER ['HTTP_HOST'] ?? $_SERVER ['SERVER_NAME'] ?? '';
	}
	$host = preg_replace('/:\d+$/', '', (string) $host);
	$host = strtolower((string) $host);
	$host = preg_replace('/[^a-z0-9.\-]/', '', $host);

	// Only use a real-looking host; otherwise pick a reserved invalid TLD.
	if ($host === '' || strpos($host, '.') === false) {
		$host = 'example.invalid';
	}

	return 'noreply@' . $host;
}

/**
 * Best-effort mail wrapper for maximum compatibility across hosters.
 * - On Windows: ignore additional parameters (no sendmail flags).
 * - On Unix: try setting envelope sender via "-f", fallback without params if blocked.
 */
function plugin_newsletter_mail(string $to, string $subject, string $message, string $headers, string $fromEmail): bool {
	$fromEmail = plugin_newsletter_sanitize_from_email($fromEmail);

	// Windows uses SMTP; sendmail flags are not applicable.
	if (stripos(PHP_OS, 'WIN') === 0) {
		return (bool) @mail($to, $subject, $message, $headers);
	}

	// Try with envelope sender; some hosters block 5th param -> fallback.
	$ok = @mail($to, $subject, $message, $headers, '-f' . $fromEmail);
	if (!$ok) {
		$ok = @mail($to, $subject, $message, $headers);
	}
	return (bool) $ok;
}

/**
 * Handles new subscriber sign-ups: encrypts and stores their data.
 */
function plugin_newsletter_handle_subscribe() {
	global $fp_config;
	$lang = lang_load('plugin:newsletter');

	// Validate CSRF token (frontend scope)
	if (!isset($_POST ['newsletter_widget_csrf_token']) || !isset($_SESSION ['newsletter_widget_csrf_token']) || $_POST ['newsletter_widget_csrf_token'] !== $_SESSION ['newsletter_widget_csrf_token']) {
		die(htmlspecialchars($lang ['plugin'] ['newsletter'] ['csrf_error'], ENT_QUOTES));
	}

	// IP throttling: check immediately whether this IP is already blocked
	$blocked_file = PLUGIN_NEWSLETTER_DIR . 'blocked-ips.txt';
	$blocked = plugin_newsletter_read_lines($blocked_file);
	$ip = plugin_newsletter_get_client_ip();
	foreach (plugin_newsletter_read_lines($blocked_file) as $line) {
		list($blocked_ip, $ts) = explode('|', $line);
		// 24 hour lock
		if ($blocked_ip === $ip && time() - (int)$ts < 24 * 3600) {
			header('Location: '.BLOG_BASEURL.'?page=throttle-limit');
			exit;
		}
	}

	// Honeypot check: trap bots and throttle for 24 h
	$hp_field = $_SESSION ['newsletter_hp_field'] ?? '';
	$hp = $_POST [$hp_field] ?? '';
	if (!empty($hp)) {
		$blocked_file = PLUGIN_NEWSLETTER_DIR . 'blocked-ips.txt';
		$ip = plugin_newsletter_get_client_ip();

		// Write blocking entry
		plugin_newsletter_rmw_file($blocked_file, function(array $lines) use ($ip): array {
			$new = [];
			// Keep what is less than 24 hours old
			foreach ($lines as $l) {
				list($line_ip, $ts) = explode('|', $l);
				if (time() - (int)$ts < 24 * 3600) {
					$new [] = $l;
				}
			}
			// Append new block entry
			$new [] = $ip . '|' . time();
			return $new;
		});

		// Update last-clean marker
		$last_clean_file = PLUGIN_NEWSLETTER_DIR . 'blocked-ips-last-clean.txt';
		touch($last_clean_file);

		// Remove honeypot session so that the bot does not use the field repeatedly
		unset($_SESSION ['newsletter_hp_field']);
		header('Location: ' . BLOG_BASEURL . '?page=throttle-limit');
		exit;
	}

	// Use token only once (frontend)
	unset($_SESSION ['newsletter_widget_csrf_token']);

	// Validate e-mail and consent
	$email = trim($_POST ['newsletter_email'] ?? '');
	$consent = isset($_POST ['newsletter_privacy']);
	if (!plugin_newsletter_is_valid_email($email)) {
		header('Location: ' . BLOG_BASEURL . '?page=invalid-email');
		exit;
	}
	// Check consent
	if (!$consent) {
		header('Location: ' . BLOG_BASEURL . '?page=invalid-email');
		exit;
	}

	// Block disposable and temporary email domains
	$domain = substr(strrchr($email, '@'), 1);
	$blockfile = PLUGIN_NEWSLETTER_DIR . 'disposable-email-blocklist.txt';
	$blocked = array_map('strtolower', plugin_newsletter_read_lines($blockfile));
	if (in_array(strtolower($domain), $blocked, true)) {
		header('Location: ' . BLOG_BASEURL . '?page=invalid-email');
		exit;
	}

	if (!user_loggedin()) {
		// Rate limiting: max. 3 subscription attempts per IP and day
		$ip = plugin_newsletter_get_client_ip();
		$today = date('Y-m-d');
		// Initialize the counter array in the session
		if (!isset($_SESSION ['newsletter_rate_limit'])) {
			$_SESSION ['newsletter_rate_limit'] = [];
		}
		// Key from date and IP
		$key = $today . '_' . $ip;
		if (!isset($_SESSION ['newsletter_rate_limit'] [$key])) {
			$_SESSION ['newsletter_rate_limit'] [$key] = 0;
		}
		// Forward on ≥ 3 attempts
		if ($_SESSION ['newsletter_rate_limit'] [$key] >= 3) {
			header('Location: ' . BLOG_BASEURL . '?page=throttle-limit');
			exit;
		}
		// Count this attempt
		$_SESSION ['newsletter_rate_limit'] [$key]++;
	}

	// Check if the user is already subscribed
	$sub_file = PLUGIN_NEWSLETTER_DIR . 'subscribers.txt';
	$sub_lines = plugin_newsletter_read_lines($sub_file);
	foreach ($sub_lines as $sub_line) {
		list($sub_data) = explode('|', $sub_line, 2);
		if (plugin_newsletter_decrypt($sub_data) === $email) {
			// Already confirmed – redirect to confirmation page
			header('Location: ' . BLOG_BASEURL . '?page=subscription-confirmed');
			exit;
		}
	}

	// Determine FlatPress character set
	$charset = strtoupper($fp_config ['locale'] ['charset'] ?? 'UTF-8');
	$allowed_charsets = ['UTF-8', 'ISO-8859-1', 'ISO-8859-5', 'ISO-8859-9', 'ISO-8859-15', 'Windows-1252', 'Shift_JIS', 'EUC-JP','GB2312'];
	if (!in_array($charset, $allowed_charsets)) {
		$charset = 'UTF-8';
	}

	// Generate token for the confirmation e-mail
	$token = bin2hex(random_bytes(16));
	$encrypted = plugin_newsletter_encrypt($email);
	$time = time();

	// Write to pending list
	$pending_file = PLUGIN_NEWSLETTER_DIR . 'pending.txt';
	if (!file_exists($pending_file)) {
		touch($pending_file);
	}
	plugin_newsletter_rmw_file($pending_file, function(array $lines) use ($encrypted, $token, $time): array {
		$lines [] = $encrypted . '|'. $token .'|'. $time;
		return $lines;
	});

	// Send confirmation e-mail
	$from = plugin_newsletter_sanitize_from_email($fp_config ['general'] ['email'] ?? '');
	$confirm_link = BLOG_BASEURL . '?newsletter_action=confirm&email=' . urlencode($email) . '&token=' . $token;
	$subject_text = $lang ['plugin'] ['newsletter'] ['confirm_subject'] . ' - ' . $fp_config ['general'] ['title'];
	$subject = '=?' . $charset . '?B?' . base64_encode($subject_text). '?=';
	$body = '<p>' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['confirm_greeting'], ENT_QUOTES) . '</p>' . //
		'<p><a href="' . htmlspecialchars($confirm_link, ENT_QUOTES) . '">' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['confirm_link_text'], ENT_QUOTES) . '</a></p>' . //
		'<p>' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['confirm_ignore'], ENT_QUOTES) . '</p>' . //
		'<p><a href="' . BLOG_BASEURL . '?page=legal-notice">' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['legal_notice'], ENT_QUOTES) . '</a> | ' . //
		'<a href="' . BLOG_BASEURL . '?page=privacy-policy">' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['privacy_policy'], ENT_QUOTES) . '</a></p>';
	$headers = 'Date: ' . date('r') . "\r\n" . //
		'MIME-Version: 1.0' . "\r\n" . //
		'Content-Type: text/html; charset="' . $charset . '"' . "\r\n" . //
		'From: ' . $from . "\r\n";
	plugin_newsletter_mail($email, $subject, $body, $headers, $from);

	// Forwarding to information page
	header('Location: ' . BLOG_BASEURL . '?page=check-your-email');
	exit;
}

/**
 * Removes subscribers when they click on the unsubscribe link
 */
function plugin_newsletter_handle_unsubscribe($encodedEmail) {
	$file = PLUGIN_NEWSLETTER_DIR . 'subscribers.txt';
	if (!file_exists($file)) {
		header('Location: ' . BLOG_BASEURL);
		exit;
	}

	plugin_newsletter_rmw_file($file, function(array $lines) use ($encodedEmail): array {
		$email = urldecode($encodedEmail);
		$keep = [];
		foreach ($lines as $line) {
			list($data) = explode('|', $line, 2);
			if (plugin_newsletter_decrypt($data) !== $email) {
				$keep [] = $line;
			}
		}
		return $keep;
	});

	header('Location: ' . BLOG_BASEURL . '?page=unsubscribe-success');
	exit;
}

/**
 * Removes a subscriber via the admin panel
 */
function plugin_newsletter_handle_admin_delete($encodedEmail) {
	$file = PLUGIN_NEWSLETTER_DIR . 'subscribers.txt';
	if (!file_exists($file)) {
		// Back to the admin panel in case of errors
		$redirect = isset($_SERVER ['HTTP_REFERER']) ? $_SERVER ['HTTP_REFERER'] : $_SERVER ['REQUEST_URI'];
		header('Location: ' . $redirect);
		exit;
	}

	plugin_newsletter_rmw_file($file, function(array $lines) use ($encodedEmail): array {
		$email = urldecode($encodedEmail);
		$keep = [];
		foreach ($lines as $line) {
			list($data, $time) = explode('|', $line, 2);
			if (plugin_newsletter_decrypt($data) !== $email) {
				$keep [] = $line;
			}
		}
		return $keep;
	});

	$redirect = isset($_SERVER ['HTTP_REFERER']) ? $_SERVER ['HTTP_REFERER'] : $_SERVER ['REQUEST_URI'];
	header('Location: ' . $redirect);
	exit;
}

/**
 * Replaces a cron job to send the monthly newsletter at 03:00 AM.
 */
function plugin_newsletter_check_and_send($dateFile, $subFile) {
	$tz = date_default_timezone_get();
	$now = new DateTime('now', new DateTimeZone($tz));

	// Configure batch dispatch
	$batchSize = PLUGIN_NEWSLETTER_BATCH_SIZE;
	$offsetFile = PLUGIN_NEWSLETTER_DIR . 'batch-offset.txt';

	// Create offset file if not available
	if (!file_exists($offsetFile)) {
		// Initialize with 0
		plugin_newsletter_rmw_file($offsetFile, function(array $lines): array {
			return ['0'];
		});
	}

	$offLines = plugin_newsletter_read_lines($offsetFile);
	$offset = (int) ($offLines [0] ?? '0');

	// Start of month: handle manual vs. auto dispatch
	// (but if a manual send is in progress, defer auto by one month)
	if ($now->format('j') === '1') {
		$offset = (int) (plugin_newsletter_read_lines($offsetFile)[0] ?? '0');
		$subs = plugin_newsletter_read_lines($subFile);
		$total = count($subs);
		$isManual = is_file(PLUGIN_NEWSLETTER_DIR . 'manual-flag.txt');

		// Manual dispatch still in progress - auto-dispatch postponed by one month
		if ($isManual && $offset > 0 && $offset < $total) {
			$next = (clone $now)->modify('first day of next month')->setTime(3, 0, 0);
			// Save next dispatch
			plugin_newsletter_rmw_file($dateFile, function(array $_) use ($next): array {
				return [$next->format('Y-m-d H:i:s')];
			});
			return;
		}

		// No batch running (new or complete) - Delete offset
		if ($offset === 0 || $offset >= $total) {
			@unlink($offsetFile);
			// Remove flag after completing manual dispatch
			if ($isManual) {
				@unlink(PLUGIN_NEWSLETTER_DIR . 'manual-flag.txt');
			}
		}
		// Ongoing partial batches (offset >0 <total) retain their next-day deadline.
	}

	// First entry: next dispatch on the 1st of the next month at 03:00
	if (!file_exists($dateFile)) {
		touch($dateFile);
		$time = (clone $now)->setTime(3, 0, 0);
		if ($now < $time) {
			$next = $time;
		} else {
			$next = (clone $now)->modify('first day of next month')->setTime(3, 0, 0);
		}
		plugin_newsletter_rmw_file($dateFile, function(array $_) use ($next): array {
			return [$next->format('Y-m-d H:i:s')];
		});
	}

	// Check and send
	$stored = trim((plugin_newsletter_read_lines($dateFile)[0] ?? ''));
	$sendTime = DateTime::createFromFormat('Y-m-d H:i:s', $stored, new DateTimeZone($tz));
	if ($now >= $sendTime) {
		// Prepare newsletter content
		global $fp_config;
		$lang = lang_load('plugin:newsletter');
		$title = $fp_config ['general'] ['title'];

		// Last entries
		if (function_exists('plugin_lastentries_widget')) {
			$entries = plugin_lastentries_widget();
			// Small CSS styling - Get raw HTML from the widget
			$entries_content = $entries ['content'];
			$entries_content = preg_replace('/<ul([^>]*)>/i', '<ul$1 style="list-style-type: none;">', $entries_content);
			$entries_content = preg_replace('/<li([^>]*)>/i', '<li$1 style="list-style: none;">', $entries_content);
		} else {
			$entries_content = '<p>' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['no_entries'] ?? '') . '</p>';
		}

		// Latest comments
		if (function_exists('plugin_lastcomments_widget')) {
			$comments = plugin_lastcomments_widget();
			// Small CSS styling - Get raw HTML from the widget
			$comments_content = $comments ['content'];
			$comments_content = preg_replace('/<ul([^>]*)>/i', '<ul$1 style="list-style-type: none;">', $comments_content);
			$comments_content = preg_replace('/<li([^>]*)>/i', '<li$1 style="list-style: none;">', $comments_content);
		} else {
			$comments_content = '<p>' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['no_comments'] ?? '') . '</p>';
		}

		// Determine FlatPress character set
		$charset = strtoupper($fp_config ['locale'] ['charset'] ?? 'UTF-8');
		$allowed_charsets = ['UTF-8', 'ISO-8859-1', 'ISO-8859-5', 'ISO-8859-9', 'ISO-8859-15', 'Windows-1252', 'Shift_JIS', 'EUC-JP','GB2312'];
		if (!in_array($charset, $allowed_charsets)) {
			$charset = 'UTF-8';
		}

		$subject_text = $title . ' - ' . $lang ['plugin'] ['newsletter'] ['subject'];
		$encoded_subject = '=?' . $charset . '?B?' . base64_encode($subject_text) . '?=';
		$from = plugin_newsletter_sanitize_from_email($fp_config ['general'] ['email'] ?? '');
		$headers = 'Date: ' . date('r') . "\r\n";
		$headers .= 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-Type: text/html; charset=' . $charset . "\r\n";
		$headers .= 'From: ' . $from . "\r\n";

		// Perform batch dispatch
		$subscribers = plugin_newsletter_read_lines($subFile);
		$total = count($subscribers);
		// Only get the next batch
		$batch = array_slice($subscribers, $offset, $batchSize);
		foreach ($batch as $line) {
			list($data) = explode('|', $line);
			$email = plugin_newsletter_decrypt($data);

			// Monthly validation of subscriber e-mail addresses (syntax, and DNS fallback)
			if (!plugin_newsletter_is_valid_email($email)) {
				// Removing invalid subscribers from the subscriber list
				plugin_newsletter_rmw_file($subFile, function(array $lines) use ($line): array {
					return array_filter($lines, function($l) use ($line) {
						return $l !== $line;
					});
				});

				// Log removal
				$bounced_log = PLUGIN_NEWSLETTER_DIR . 'bounced-log.txt';
				if (!file_exists($bounced_log)) {
					touch($bounced_log);
				}
				file_put_contents($bounced_log, date('[Y-m-d H:i] ') . 'removed: ' . $email . "\n", FILE_APPEND | LOCK_EX);
				// Skip e-mail dispatch
				continue;
			}

			$unsubscribe = BLOG_BASEURL . '?newsletter_action=unsubscribe&email=' . urlencode($email);
			$body = '<h1>' . htmlspecialchars($title) . ' - ' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['subject']) . '</h1>';
			$body .= '<h2>' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['last_entries']) . '</h2>' . $entries_content;
			$body .= '<h2>' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['last_comments']) . '</h2>' . $comments_content;
			$body .= '<p><a href="' . htmlspecialchars($unsubscribe) . '">' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['unsubscribe']) . '</a></p>';
			$body .= '<p><a href="' . BLOG_BASEURL . '?page=legal-notice">' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['legal_notice'], ENT_QUOTES) . '</a> | <a href="' . BLOG_BASEURL . '?page=privacy-policy">' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['privacy_policy'], ENT_QUOTES) . '</a></p>';
			plugin_newsletter_mail($email, $encoded_subject, $body, $headers, $from);
		}

		// Update offset
		$offset += count($batch);
		file_put_contents($offsetFile, (string)$offset, LOCK_EX);

		// Schedule next dispatch
		if ($offset < $total) {
			// Next day at 03:00 AM (calculate relative to now, not the old broadcast date)
			$next = (clone $now)->modify('+1 day')->setTime(3, 0, 0);
		} else {
			// All sent: Delete offset and plan next month
			@unlink($offsetFile);
			$next = (clone $now)->modify('first day of next month')->setTime(3, 0, 0);
		}
		// Set date
		plugin_newsletter_rmw_file($dateFile, function(array $_) use ($next): array {
			return [$next->format('Y-m-d H:i:s')];
		});
	}
}

/**
 * Processes and sends the newsletter to all subscribers.
 */
function plugin_newsletter_send_all($subFile) {
	global $fp_config;
	$lang = lang_load('plugin:newsletter');
	$title = $fp_config ['general'] ['title'];

	// Last entries
	if (function_exists('plugin_lastentries_widget')) {
		$entries = plugin_lastentries_widget();
		// Small CSS styling - Get raw HTML from the widget
		$entries_content = $entries ['content'];
		$entries_content = preg_replace('/<ul([^>]*)>/i', '<ul$1 style="list-style-type: none;">', $entries_content);
		$entries_content = preg_replace('/<li([^>]*)>/i', '<li$1 style="list-style: none;">', $entries_content);
	} else {
		$entries_content = '<p>' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['no_entries'] ?? '') . '</p>';
	}

	// Latest comments
	if (function_exists('plugin_lastcomments_widget')) {
		$comments = plugin_lastcomments_widget();
		// Small CSS styling - Get raw HTML from the widget
		$comments_content = $comments ['content'];
		$comments_content = preg_replace('/<ul([^>]*)>/i', '<ul$1 style="list-style-type: none;">', $comments_content);
		$comments_content = preg_replace('/<li([^>]*)>/i', '<li$1 style="list-style: none;">', $comments_content);
	} else {
		$comments_content = '<p>' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['no_comments'] ?? '') . '</p>';
	}

	// Determine FlatPress character set
	$charset = strtoupper($fp_config ['locale'] ['charset'] ?? 'UTF-8');
	$allowed_charsets = ['UTF-8', 'ISO-8859-1', 'ISO-8859-5', 'ISO-8859-9', 'ISO-8859-15', 'Windows-1252', 'Shift_JIS', 'EUC-JP','GB2312'];
	if (!in_array($charset, $allowed_charsets)) {
		$charset = 'UTF-8';
	}

	$subject_text = $title . ' - ' . $lang ['plugin'] ['newsletter'] ['subject'];
	$encoded_subject = '=?' . $charset . '?B?' . base64_encode($subject_text) . '?=';
	$from = plugin_newsletter_sanitize_from_email($fp_config ['general'] ['email'] ?? '');

	$headers = '';
	$headers .= 'Date: ' . date('r') . "\r\n";
	$headers .= 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-Type: text/html; charset="' . $charset . '"' . "\r\n";
	$headers .= 'From: ' . $from . "\r\n";

	// MANUAL BATCHED DISPATCH START
	$batchSize  = PLUGIN_NEWSLETTER_BATCH_SIZE;
	$offsetFile = PLUGIN_NEWSLETTER_DIR . 'batch-offset.txt';
	$dateFile = PLUGIN_NEWSLETTER_DIR . 'next-send-date.txt';

	// Init offset
	if (!file_exists($offsetFile)) {
		file_put_contents($offsetFile, '0', LOCK_EX);
	}
	$offsetLines = plugin_newsletter_read_lines($offsetFile);
	$offset = (int) trim($offsetLines [0] ?? '0');

	// Load all subscribers, then slice out next batch
	$allSubs = plugin_newsletter_read_lines($subFile);
	$total = count($allSubs);
	$batch = array_slice($allSubs, $offset, $batchSize);

	// Send only this batch
	foreach ($batch as $line) {
		list($data) = explode('|', $line);
		$email = plugin_newsletter_decrypt($data);

		// Validation of subscriber e-mail addresses (syntax and DNS fallback)
		if (!plugin_newsletter_is_valid_email($email)) {
			// Remove from subscriber list
			$allSubs = plugin_newsletter_read_lines($subFile);
			$filtered = array_filter($allSubs, function($l) use ($line) {
				return $l !== $line;
			});
			file_put_contents($subFile, implode(PHP_EOL, $filtered) . PHP_EOL, LOCK_EX);

			// Log distance
			$bounced_log = PLUGIN_NEWSLETTER_DIR . 'bounced-log.txt';
			if (!file_exists($bounced_log)) {
				touch($bounced_log);
			}
			file_put_contents($bounced_log, date('[Y-m-d H:i] ') . 'removed: ' . $email . "\n", FILE_APPEND | LOCK_EX);

			// Skip e-mail dispatch
			continue;
		}

		$unsubscribe = BLOG_BASEURL . '?newsletter_action=unsubscribe&email=' . urlencode($email);
		$body = '<h1>' . htmlspecialchars($title) . ' - ' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['subject']) . '</h1>';
		$body .= '<h2>' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['last_entries']) . '</h2>' . $entries_content;
		$body .= '<h2>' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['last_comments']) . '</h2>' . $comments_content;
		$body .= '<p><a href="' . htmlspecialchars($unsubscribe) . '">' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['unsubscribe']) . '</a></p>';
		$body .= '<p><a href="' . BLOG_BASEURL . '?page=legal-notice">' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['legal_notice'], ENT_QUOTES) . '</a> | <a href="' . BLOG_BASEURL . '?page=privacy-policy">' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['privacy_policy'], ENT_QUOTES) . '</a></p>';
		plugin_newsletter_mail($email, $encoded_subject, $body, $headers, $from);
	}

	// Persist new offset
	$offset += count($batch);
	file_put_contents($offsetFile, (string)$offset, LOCK_EX);

	// Schedule next manual batch (or reset for auto)
	if ($offset < $total) {
		// Next day 03:00 am
		$next = (new DateTime())->modify('+1 day')->setTime(3, 0, 0);
	} else {
		// All manual batches done: clean up and schedule auto for next month
		@unlink($offsetFile);
		// Clear manual batch dispatch flag
		@unlink(PLUGIN_NEWSLETTER_DIR . 'manual-flag.txt');
		$next = (new DateTime())->modify('first day of next month')->setTime(3, 0, 0);
	}

	if (!file_exists($dateFile)) {
		touch($dateFile);
	}
	// Set date
	plugin_newsletter_rmw_file($dateFile, function(array $_) use ($next): array {
		return [$next->format('Y-m-d H:i:s')];
	});
}

if (class_exists('AdminPanelAction')) {

	/**
	 * Admin panel for Newsletter setup.
	 */
	class admin_plugin_newsletter extends AdminPanelAction {

		var $langres = 'plugin:newsletter';

		/**
		 * Initializes this panel.
		 */
		function setup() {
			global $lang;

			// Current batch size for the template output
			$opts = plugin_newsletter_get_options();
			$this->smarty->assign('batch_size', $opts ['batch_size']);

			// Read subscribers
			$file = PLUGIN_NEWSLETTER_DIR . 'subscribers.txt';
			$subscribers = array();

			$lines = plugin_newsletter_read_lines($file);

			// Current shipping status: We only display icons when a run is active (offset > 0).
			$offsetLines = plugin_newsletter_read_lines(PLUGIN_NEWSLETTER_DIR . 'batch-offset.txt');
			$offset = (int) trim($offsetLines [0] ?? '0');
			$total = count($lines);

			foreach ($lines as $idx => $line) {
				list($data, $time) = explode('|', $line, 2);
				$email = plugin_newsletter_decrypt($data);
				$dt = new DateTime();
				$dt->setTimestamp((int)$time);
				$row = array('email' => $email, 'email_encoded' => urlencode($email), 'date' => $dt->format('Y-m-d'), 'time' => $dt->format('H:i:s'));
				// Only set status when running (offset > 0); NO icon when offset == 0.
				if ($offset > 0) {
					$row ['status'] = ($offset >= $total || $idx < $offset) ? 'sent' : 'queued';
				}
				$subscribers [] = $row;
			}

			$this->smarty->assign('subscribers', $subscribers);

			// Generate CSRF token for deletion/send-all forms (admin scope) and transfer to template
			if (empty($_SESSION ['newsletter_admin_csrf_token'])) {
				$_SESSION ['newsletter_admin_csrf_token'] = bin2hex(random_bytes(32));
			}
			$this->smarty->assign('batch_pending', false);
			$this->smarty->assign('subscribers_remaining', 0);
			// Default batch type (not displayed when no pending)
			$this->smarty->assign('batch_type', $lang ['admin'] ['plugin'] ['newsletter'] ['send_type_monthly']);
			$this->smarty->assign('newsletter_csrf_token', $_SESSION ['newsletter_admin_csrf_token']);

			// If we're coming back after sending, display success message
			if (isset($_GET ['success'])) {
				$this->smarty->assign('success', (int)$_GET ['success']);
			}

			// Check whether a staggered dispatch is currently running and determine remaining subscribers
			$subFile = PLUGIN_NEWSLETTER_DIR . 'subscribers.txt';
			$offsetFile = PLUGIN_NEWSLETTER_DIR . 'batch-offset.txt';
			$offsetLines = plugin_newsletter_read_lines($offsetFile);
			$offset = (int) trim($offsetLines [0] ?? '0');
			$subs = plugin_newsletter_read_lines($subFile);
			$total = count($subs);
			// If partial shipments have already been made and not all have been sent yet
			if ($offset > 0 && $offset < $total) {
				$this->smarty->assign('batch_pending', true);
				$this->smarty->assign('subscribers_remaining', $total - $offset);
				// Assign batch type for template: manual if flag exists, else monthly
				$manualFlag = is_file(PLUGIN_NEWSLETTER_DIR . 'manual-flag.txt');
				$batchTypeKey = $manualFlag ? 'send_type_manual' : 'send_type_monthly';
				$this->smarty->assign('batch_type', $lang ['admin'] ['plugin'] ['newsletter'] [$batchTypeKey]);
			}

			// Template for the admin panel
			$this->smarty->assign('admin_resource', "plugin:newsletter/admin.plugin.newsletter");
		}

		function main() {
			if (!function_exists('plugin_lastentries_widget')) {
				$this->smarty->assign('success', -2);
			}
		}

		/**
		 * Handle the settings form submission and stay on this panel.
		 *
		 * @param array|null $data
		 * @return int
		 */
		function onsubmit($data = null) {
			if (isset($_POST ['newsletter_save_settings'])) {
				// Only accept digits, otherwise default
				$bs = (isset($_POST ['newsletter_batch_size']) && ctype_digit($_POST ['newsletter_batch_size'])) ? (int)$_POST ['newsletter_batch_size'] : FP_NEWSLETTER_DEFAULT_OPTIONS ['batch_size'];
				plugin_addoption('newsletter', 'batch_size', $bs);
				plugin_saveoptions('newsletter');
				// Success message via errorlist.tpl (Index 2)
				$this->smarty->assign('success', 2);
			}
			// Reload and prepare for template
			$opts = plugin_newsletter_get_options();
			$this->smarty->assign('batch_size', $opts ['batch_size']);
			return 0;
		}

	}
	admin_addpanelaction('plugin', 'newsletter', true);
}

/**
 * Update the blocklist from the remote source once a month from the 25th day.
 *
 * @return void
 */
function plugin_newsletter_maybe_update_blocklist(): void {

	$remote = NEWSLETTER_BLOCKLIST_URL;

	// Local path to the blocklist
	$local = PLUGIN_NEWSLETTER_DIR . 'disposable-email-blocklist.txt';

	// Used to throttle failed attempts (prevents log spam on strict hosts)
	$attemptFile = PLUGIN_NEWSLETTER_DIR . 'disposable-email-blocklist.last_attempt.txt';
	$now = time();

	$mode = '';

	// Decide whether we should download now (avoid network calls on every request)
	// Blocklist file does not yet exist? - Obtain immediately from Remote
	if (!file_exists($local)) {
		$mode = 'initial';
		// Retry at most once per hour if it fails
		if (file_exists($attemptFile) && ($now - (int) @filemtime($attemptFile) < 3600)) {
			return;
		}
	} else {

		// Only continue after the 25th day of the month
		if ((int) date('j') < 25) {
			return;
		}

		// Already updated this month?
		$mtime = @filemtime($local);
		if ($mtime && date('Y-m', $mtime) === date('Y-m')) {
			return;
		}

		$mode = 'monthly';
		// Retry at most once per day if it fails
		if (file_exists($attemptFile) && ($now - (int) @filemtime($attemptFile) < 86400)) {
			return;
		}
	}

	// Mark attempt
	@touch($attemptFile);

	$httpCode = null;
	$err = null;
	$remoteUsed = $remote;

	$data = plugin_newsletter_http_get($remote, $httpCode, $err);

	// Fallback for some hosts: try the canonical raw URL without /refs/heads/
	if ($data === null && strpos($remote, '/refs/heads/') !== false) {
		$alt = preg_replace('#/refs/heads/([^/]+)/#', '/$1/', $remote, 1);
		if (is_string($alt) && $alt !== '' && $alt !== $remote) {
			$remoteUsed = $alt;
			$data = plugin_newsletter_http_get($alt, $httpCode, $err);
		}
	}

	if (!is_string($data) || $data === '') {
		$detail = $err ?: 'unknown error';
		trigger_error(sprintf('[Newsletter plugin] The blocklist URL "%s" could not be fetched (%s).', $remoteUsed, $detail), E_USER_WARNING);
		return;
	}

	$lines = explode("\n", rtrim($data, "\n"));
	plugin_newsletter_rmw_file($local, function(array $_) use ($lines): array {
		return $lines;
	});
	@chmod($local, FILE_PERMISSIONS);

	// Keep previous behaviour: do not clean subscribers on first download
	if ($mode === 'initial') {
		return;
	}

	// Remove subscribers with blocklist domains
	$subsFile = PLUGIN_NEWSLETTER_DIR . 'subscribers.txt';
	$blocklist = array_map('strtolower', plugin_newsletter_read_lines($local));
	plugin_newsletter_rmw_file($subsFile, function(array $lines) use ($blocklist): array {
		// Only keep subscriptions whose domain is not on the block list
		return array_filter($lines, function(string $line) use ($blocklist): bool {
			list($enc) = explode('|', $line, 2);
			$email = plugin_newsletter_decrypt($enc);
			$domain = strtolower(substr(strrchr($email, '@'), 1));
			return !in_array($domain, $blocklist, true);
		});
	});
}


// Plugin file is loaded with every request - Trigger update check
plugin_newsletter_maybe_update_blocklist();
?>
