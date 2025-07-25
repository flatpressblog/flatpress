<?php
/**
 * Plugin Name: Newsletter
 * Version: 1.6.2
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
		return array_merge(
			FP_NEWSLETTER_DEFAULT_OPTIONS,
			array_map('intval', $cfg)
		);
	}
}

// Stack size from configuration or default
$newsletter_opts = plugin_newsletter_get_options();
define('PLUGIN_NEWSLETTER_BATCH_SIZE', (int)$newsletter_opts ['batch_size']);

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
				@copy($src, $dest);
				@chmod($dest, FILE_PERMISSIONS);
			}
		}
	}
}

// Initialization on every page request
plugin_newsletter_init();

/**
 * Initializes directory, processes forms and checks dispatch date
 */
function plugin_newsletter_init() {
	if (!is_dir(PLUGIN_NEWSLETTER_DIR)) {
		mkdir(PLUGIN_NEWSLETTER_DIR, DIR_PERMISSIONS, true);
	}
	$sub_file = PLUGIN_NEWSLETTER_DIR . 'subscribers.txt';
	$date_file = PLUGIN_NEWSLETTER_DIR . 'next-send-date.txt';
	$pending_file = PLUGIN_NEWSLETTER_DIR . 'pending.txt';

	// Create file for pending (unconfirmed) subscriptions, if not available
	if (!file_exists($pending_file)) {
		touch($pending_file);
	}

	// Admin: Delete a subscriber via POST, CSRF-protected
	if ($_SERVER ['REQUEST_METHOD'] === 'POST' && isset($_POST ['newsletter_delete'])) {
		// Check CSRF token
		if (!hash_equals($_SESSION ['newsletter_csrf_token'] ?? '', $_POST ['csrf_token'] ?? '')) {
			header('HTTP/1.1 400 Bad Request');
			exit('Invalid CSRF token');
		}
		plugin_newsletter_handle_admin_delete($_POST ['newsletter_delete']);
		exit;
	}

	// Admin: Sends the newsletter immediately to all subscribers
	if ($_SERVER ['REQUEST_METHOD'] === 'POST' && isset($_POST ['newsletter_send_all'])) {

		// Create a flag indicating manual batch dispatch is in progress
		$manualFlagFile = PLUGIN_NEWSLETTER_DIR . 'manual-flag.txt';
		if (!file_exists($manualFlagFile)) {
			file_put_contents($manualFlagFile, 'manual', LOCK_EX);
		}

		// Check CSRF token
		if (!hash_equals($_SESSION ['newsletter_csrf_token'] ?? '', $_POST ['csrf_token'] ?? '')) {
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

	$lines = file($pending_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
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
			file_put_contents(PLUGIN_NEWSLETTER_DIR . 'subscribers.txt', $enc_email . '|' . $timestamp . PHP_EOL, FILE_APPEND | LOCK_EX);
			$confirmed = true;
		} else {
			// Still outstanding
			$remaining [] = $line;
		}
	}

	// Update pending list
	file_put_contents($pending_file, implode(PHP_EOL, $remaining) . (count($remaining) ? PHP_EOL : ''), LOCK_EX);

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
	// Generate CSRF token and save in session
	$token = bin2hex(random_bytes(32));
	$_SESSION ['newsletter_csrf_token'] = $token;
	$lang = lang_load('plugin:newsletter');
	$html = '<form method="post" action="' . htmlspecialchars(BLOG_BASEURL, ENT_QUOTES) . '">' . //
		'<input type="hidden" name="newsletter_csrf_token" value="' . $token . '"><ul>' . //
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
 * Determines the real client IP for rate limiting
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
 * Saves new subscribers (encrypted) and forwards them
 */
function plugin_newsletter_handle_subscribe() {
	global $fp_config;
	$lang = lang_load('plugin:newsletter');

	// Validate CSRF token
	if (!isset($_POST ['newsletter_csrf_token']) || !isset($_SESSION ['newsletter_csrf_token']) || $_POST ['newsletter_csrf_token'] !== $_SESSION ['newsletter_csrf_token']) {
		die(htmlspecialchars($lang ['plugin'] ['newsletter'] ['csrf_error'], ENT_QUOTES));
	}
	// Use token only once
	unset($_SESSION ['newsletter_csrf_token']);

	// Validate e-mail and consent
	$email = trim($_POST ['newsletter_email'] ?? '');
	$consent = isset($_POST ['newsletter_privacy']);
	if (!$consent || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
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
		// Forward on ≥3 attempts
		if ($_SESSION ['newsletter_rate_limit'] [$key] >= 3) {
			header('Location: ' . BLOG_BASEURL . '?page=throttle-limit');
			exit;
		}
		// Count this attempt
		$_SESSION ['newsletter_rate_limit'] [$key]++;
	}

	// Check if the user is already subscribed
	$sub_file = PLUGIN_NEWSLETTER_DIR . 'subscribers.txt';
	if (file_exists($sub_file)) {
		$sub_lines = file($sub_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		foreach ($sub_lines as $sub_line) {
			list($sub_data) = explode('|', $sub_line, 2);
			if (plugin_newsletter_decrypt($sub_data) === $email) {
				// Already confirmed – redirect to confirmation page
				header('Location: ' . BLOG_BASEURL . '?page=subscription-confirmed');
				exit;
			}
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
	file_put_contents(PLUGIN_NEWSLETTER_DIR . 'pending.txt', $encrypted . '|' . $token . '|' . $time . PHP_EOL, FILE_APPEND | LOCK_EX);

	// Send confirmation e-mail
	$from = $fp_config ['general'] ['email'];
	$confirm_link = BLOG_BASEURL . '?newsletter_action=confirm&email=' . urlencode($email) . '&token=' . $token;
	$subject_text = $lang ['plugin'] ['newsletter'] ['confirm_subject'] . ' - ' . $fp_config ['general'] ['title'];
	$subject = '=?' . $charset . '?B?' . base64_encode($subject_text). '?=';
	$body = '<p>' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['confirm_greeting'], ENT_QUOTES) . '</p>' . //
		'<p><a href="' . htmlspecialchars($confirm_link, ENT_QUOTES) . '">' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['confirm_link_text'], ENT_QUOTES) . '</a></p>' . //
		'<p>' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['confirm_ignore'], ENT_QUOTES) . '</p>' . //
		'<p><a href="' . BLOG_BASEURL . '?page=legal-notice">' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['legal_notice'], ENT_QUOTES) . '</a> ' . //
		'<a href="' . BLOG_BASEURL . '?page=privacy-policy">' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['privacy_policy'], ENT_QUOTES) . '</a></p>';
	$headers = 'Date: ' . date('r') . "\r\n" . //
		'MIME-Version: 1.0' . "\r\n" . //
		'Content-Type: text/html; charset="' . $charset . '"' . "\r\n" . //
		'From: ' . $from . "\r\n";
	@mail($email, $subject, $body, $headers, '-f ' . $from);

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
	$lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	$keep = [];
	$email = urldecode($encodedEmail);
	foreach ($lines as $line) {
		list($data) = explode('|', $line);
		$dec = plugin_newsletter_decrypt($data);
		if ($dec !== $email) {
			$keep [] = $line;
		}
	}
	file_put_contents($file, implode(PHP_EOL, $keep) . PHP_EOL, LOCK_EX);
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
	$lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	$keep = array();
	$email = urldecode($encodedEmail);
	foreach ($lines as $line) {
		list($data, $time) = explode('|', $line, 2);
		$dec = plugin_newsletter_decrypt($data);
		if ($dec !== $email) {
			$keep [] = $line;
		}
	}
	file_put_contents($file, implode(PHP_EOL, $keep) . PHP_EOL, LOCK_EX);
	$redirect = isset($_SERVER ['HTTP_REFERER']) ? $_SERVER ['HTTP_REFERER'] : $_SERVER ['REQUEST_URI'];
	header('Location: ' . $redirect);
	exit;
}

/**
 * Cron replacement
 * Checks next-send-date.txt and sends monthly at 03:00 a.m.
 */
function plugin_newsletter_check_and_send($dateFile, $subFile) {
	$tz = date_default_timezone_get();
	$now = new DateTime('now', new DateTimeZone($tz));

	// Configure batch dispatch
	$batchSize = PLUGIN_NEWSLETTER_BATCH_SIZE;
	$offsetFile = PLUGIN_NEWSLETTER_DIR . 'batch-offset.txt';

	// Create offset file if not available
	if (!file_exists($offsetFile)) {
		file_put_contents($offsetFile, '0', LOCK_EX);
	}
	$offset = (int) trim(@file_get_contents($offsetFile) ?: '0');

	// Reset offset at the beginning of each month for auto dispatch
	// (but if a manual send is in progress, defer auto by one month)
	if ($now->format('j') === '1' && file_exists($offsetFile)) {
		// Inspect current offset vs total subscribers
		$manOffset = (int) trim(@file_get_contents($offsetFile) ?: '0');
		if (file_exists($subFile) && is_readable($subFile)) {
			$manSubs = file($subFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		} else {
			$manSubs = [];
		}
		$manTotal = count($manSubs);
		if ($manOffset === 0 || $manOffset >= $manTotal) {
			// No batches pending - clear for new auto run
			@unlink($offsetFile);
		} else {
			// Manual send still in progress - push next auto to next month
			$next = (clone $now)->modify('first day of next month')->setTime(3, 0, 0);
			file_put_contents($dateFile, $next->format('Y-m-d H:i:s'), LOCK_EX);
			// Skip any auto dispatch today
			return;
		}
	}

	// If today is the 1st of the month, delete all pending entries
	if ($now->format('j') === '1') {
		plugin_newsletter_purge_pending();
	}

	// First entry: next dispatch on the 1st of the next month at 03:00
	if (!file_exists($dateFile)) {
		$time = (clone $now)->setTime(3, 0, 0);
		if ($now < $time) {
			$next = $time;
		} else {
			$next = (clone $now)->modify('first day of next month')->setTime(3, 0, 0);
		}
		file_put_contents($dateFile, $next->format('Y-m-d H:i:s'), LOCK_EX);
	}

	// Check and send
	$stored = trim(@file_get_contents($dateFile));
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
		$from = $fp_config ['general'] ['email'];
		$headers = 'Date: ' . date('r') . "\r\n";
		$headers .= 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-Type: text/html; charset=' . $charset . "\r\n";
		$headers .= 'From: ' . $from . "\r\n";

		// Perform batch dispatch
		if (file_exists($subFile) && is_readable($subFile)) {
			$subscribers = file($subFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		} else {
			$subscribers = [];
		}
		$total = count($subscribers);
		// Only get the next batch
		$batch = array_slice($subscribers, $offset, $batchSize);
		foreach ($batch as $line) {
			list($data) = explode('|', $line);
			$email = plugin_newsletter_decrypt($data);
			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				continue;
			}
			$unsubscribe = BLOG_BASEURL . '?newsletter_action=unsubscribe&email=' . urlencode($email);
			$body = '<h1>' . htmlspecialchars($title) . ' - ' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['subject']) . '</h1>';
			$body .= '<h2>' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['last_entries']) . '</h2>' . $entries_content;
			$body .= '<h2>' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['last_comments']) . '</h2>' . $comments_content;
			$body .= '<p><a href="' . htmlspecialchars($unsubscribe) . '">' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['unsubscribe']) . '</a></p>';
			$body .= '<p><a href="' . BLOG_BASEURL . '?page=legal-notice">' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['legal_notice'], ENT_QUOTES) . '</a> <a href="' . BLOG_BASEURL . '?page=privacy-policy">' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['privacy_policy'], ENT_QUOTES) . '</a></p>';
			@mail($email, $encoded_subject, $body, $headers, '-f ' . $from);
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
		file_put_contents($dateFile, $next->format('Y-m-d H:i:s'), LOCK_EX);
	}
}

/**
 * Deletes all pending (unconfirmed) registrations.
 * Is called once a month on the 1st of the month via plugin_newsletter_check_and_send().
 */
function plugin_newsletter_purge_pending() {
	$file = PLUGIN_NEWSLETTER_DIR . 'pending.txt';
	if (!file_exists($file)) {
		return;
	}
	// Remove all entries
	file_put_contents($file, '', LOCK_EX);
}

/**
 * Reads out subscribers, decrypts, builds newsletters and sends out
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
	$from = $fp_config ['general'] ['email'];

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
	$offset = (int) trim(file_get_contents($offsetFile) ?: '0');

	// Load all subscribers, then slice out next batch
	if (is_readable($subFile)) {
		$allSubs = file($subFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	} else {
		$allSubs = [];
	}
	$total = count($allSubs);
	$batch = array_slice($allSubs, $offset, $batchSize);

	// Send only this batch
	foreach ($batch as $line) {
		list($data) = explode('|', $line);
		$email = plugin_newsletter_decrypt($data);
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			continue;
		}
		$unsubscribe = BLOG_BASEURL . '?newsletter_action=unsubscribe&email=' . urlencode($email);
		$body = '<h1>' . htmlspecialchars($title) . ' - ' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['subject']) . '</h1>';
		$body .= '<h2>' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['last_entries']) . '</h2>' . $entries_content;
		$body .= '<h2>' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['last_comments']) . '</h2>' . $comments_content;
		$body .= '<p><a href="' . htmlspecialchars($unsubscribe) . '">' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['unsubscribe']) . '</a></p>';
		$body .= '<p><a href="' . BLOG_BASEURL . '?page=legal-notice">' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['legal_notice'], ENT_QUOTES) . '</a> <a href="' . BLOG_BASEURL . '?page=privacy-policy">' . htmlspecialchars($lang ['plugin'] ['newsletter'] ['privacy_policy'], ENT_QUOTES) . '</a></p>';
		@mail($email, $encoded_subject, $body, $headers, '-f ' . $from);
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
	file_put_contents($dateFile, $next->format('Y-m-d H:i:s'), LOCK_EX);
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
			if (file_exists($file)) {
				$lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
				foreach ($lines as $line) {
					list($data, $time) = explode('|', $line, 2);
					$email = plugin_newsletter_decrypt($data);
					$dt = new DateTime();
					$dt->setTimestamp((int)$time);
					$subscribers [] = array('email' => $email, 'email_encoded' => urlencode($email), 'date' => $dt->format('Y-m-d'), 'time' => $dt->format('H:i:s'));
				}
			}
			$this->smarty->assign('subscribers', $subscribers);

			// Generate CSRF token for deletion forms and transfer to template
			if (empty($_SESSION ['newsletter_csrf_token'])) {
				$_SESSION ['newsletter_csrf_token'] = bin2hex(random_bytes(32));
			}
			$this->smarty->assign('batch_pending', false);
			$this->smarty->assign('subscribers_remaining', 0);
			// Default batch type (not displayed when no pending)
			$this->smarty->assign('batch_type', $lang ['admin'] ['plugin'] ['newsletter'] ['send_type_monthly']);
			$this->smarty->assign('newsletter_csrf_token', $_SESSION ['newsletter_csrf_token']);

			// If we're coming back after sending, display success message
			if (isset($_GET ['success'])) {
				$this->smarty->assign('success', (int)$_GET ['success']);
			}

			// Check whether a staggered dispatch is currently running and determine remaining subscribers
			$subFile = PLUGIN_NEWSLETTER_DIR . 'subscribers.txt';
			$offsetFile = PLUGIN_NEWSLETTER_DIR . 'batch-offset.txt';
			if (file_exists($offsetFile) && file_exists($subFile)) {
				$offset = (int) trim(@file_get_contents($offsetFile));
				$subs = file($subFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
				$total  = count($subs);
				// If partial shipments have already been made and not all have been sent yet
				if ($offset > 0 && $offset < $total) {
					$this->smarty->assign('batch_pending', true);
					$this->smarty->assign('subscribers_remaining', $total - $offset);
					// Assign batch type for template: manual if flag exists, else monthly
					$manualFlag = is_file(PLUGIN_NEWSLETTER_DIR . 'manual-flag.txt');
					$batchTypeKey = $manualFlag ? 'send_type_manual' : 'send_type_monthly';
					$this->smarty->assign('batch_type', $lang ['admin'] ['plugin'] ['newsletter'] [$batchTypeKey]);
				}
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
?>
