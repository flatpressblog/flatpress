<?php
/**
 * Simulation for the FlatPress newsletter plugin.
 *
 * Run from the blog root:
 *   php simulate_newsletter_plugin.php
 *
 * The script bootstraps a minimal FlatPress-like environment, includes the
 * actual plugin file, and tests the real helper functions used by subscription,
 * confirmation, DNS-cache cleanup, and EAI-aware e-mail validation.
 */

declare(strict_types=1);

error_reporting(E_ALL);

/**
 * Writes one simulation output line in CLI and browser contexts.
 */
function sim_write_line(string $message, bool $isError = false): void {
	$lineEnding = PHP_SAPI === 'cli' ? PHP_EOL : "<br>\n";

	if (PHP_SAPI === 'cli') {
		$stream = @fopen($isError ? 'php://stderr' : 'php://stdout', 'wb');
		if (is_resource($stream)) {
			fwrite($stream, $message . PHP_EOL);
			fclose($stream);
			return;
		}
	}

	echo htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . $lineEnding;
}

/**
 * Stops the simulation with a readable failure in CLI and browser contexts.
 */
function sim_fail(string $message, int $exitCode = 1): void {
	sim_write_line('FAIL: ' . $message, true);
	exit($exitCode);
}

$baseDir = __DIR__;
$testRoot = sys_get_temp_dir() . '/flatpress_newsletter_sim_' . getmypid() . '/';

function sim_rrmdir(string $dir): void {
	if (!is_dir($dir)) {
		return;
	}

	$items = scandir($dir);
	if ($items === false) {
		return;
	}

	foreach ($items as $item) {
		if ($item === '.' || $item === '..') {
			continue;
		}

		$path = $dir . DIRECTORY_SEPARATOR . $item;
		if (is_dir($path)) {
			sim_rrmdir($path);
		} else {
			@unlink($path);
		}
	}

	@rmdir($dir);
}

sim_rrmdir($testRoot);
mkdir($testRoot . 'content/static/', 0777, true);
mkdir($testRoot . 'cache/', 0777, true);
mkdir($testRoot . 'plugin_newsletter/', 0777, true);

// Prevent the plugin's blocklist bootstrap from trying a network request in a
// clean simulation environment.
file_put_contents($testRoot . 'plugin_newsletter/disposable-email-blocklist.txt', "blocked.invalid\n", LOCK_EX);

define('FP_CONTENT', $testRoot);
define('CACHE_DIR', $testRoot . 'cache/');
define('BLOG_BASEURL', 'https://example.test/');
define('DIR_PERMISSIONS', 0777);
define('FILE_PERMISSIONS', 0666);

$fp_config = [
	'general' => [
		'blogid' => 'newsletter-simulation-key',
		'title' => 'Simulation Blog',
		'email' => 'admin@example.test',
	],
	'locale' => [
		'lang' => 'en-us',
	],
	'plugins' => [
		'newsletter' => [
			'batch_size' => 30,
		],
	],
];

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

function register_widget(string $id, string $name, $callback): void {
}

function user_loggedin(): bool {
	return true;
}

function lang_load(string $resource): array {
	return [
		'plugin' => [
			'newsletter' => [
				'csrf_error' => 'Invalid CSRF token',
				'confirm_subject' => 'Please confirm',
				'confirm_greeting' => 'Hello',
				'confirm_link_text' => 'Confirm',
				'confirm_ignore' => 'Ignore this message',
				'legal_notice' => 'Legal notice',
				'privacy_policy' => 'Privacy policy',
				'input_email_placeholder' => 'email@example.org',
				'accept_privacy_policy' => 'Privacy policy',
				'button' => 'Subscribe',
				'subject' => 'Newsletter',
				'no_entries' => 'No entries',
				'no_comments' => 'No comments',
				'last_entries' => 'Last entries',
				'last_comments' => 'Last comments',
				'unsubscribe' => 'Unsubscribe',
			],
		],
	];
}

require $baseDir . '/fp-plugins/newsletter/plugin.newsletter.php';

function sim_reset_storage(): void {
	sim_rrmdir(PLUGIN_NEWSLETTER_DIR);
	mkdir(PLUGIN_NEWSLETTER_DIR, 0777, true);
	file_put_contents(PLUGIN_NEWSLETTER_DIR . 'disposable-email-blocklist.txt', "blocked.invalid\n", LOCK_EX);

	if (!is_dir(CACHE_DIR)) {
		mkdir(CACHE_DIR, 0777, true);
	}
	file_put_contents(CACHE_DIR . 'newsletter-dns-cache.txt', '', LOCK_EX);
}

function sim_assert_true(bool $condition, string $message): void {
	if (!$condition) {
		sim_fail($message);
	}

	sim_write_line('OK: ' . $message);
}

function sim_assert_same($expected, $actual, string $message): void {
	if ($expected !== $actual) {
		sim_write_line('FAIL: ' . $message, true);
		sim_write_line('Expected: ' . var_export($expected, true), true);
		sim_write_line('Actual:   ' . var_export($actual, true), true);
		exit(1);
	}

	sim_write_line('OK: ' . $message);
}

/**
 * @return array<int, string>
 */
function sim_read(string $file): array {
	return plugin_newsletter_read_lines($file);
}

/**
 * @return array<int, string>
 */
function sim_decrypted_emails(string $file): array {
	$result = [];
	foreach (sim_read($file) as $line) {
		$decoded = plugin_newsletter_decrypt_subscriber_email($line);
		if (is_string($decoded)) {
			$result[] = $decoded;
		}
	}

	return $result;
}

function sim_count_email(string $file, string $email): int {
	$count = 0;
	foreach (sim_decrypted_emails($file) as $storedEmail) {
		if (plugin_newsletter_email_matches($storedEmail, $email)) {
			$count++;
		}
	}

	return $count;
}

function sim_pending_token_count(string $file, string $email): int {
	$count = 0;
	foreach (sim_read($file) as $line) {
		$parts = explode('|', $line, 3);
		if (count($parts) < 3) {
			continue;
		}

		$decoded = plugin_newsletter_decrypt($parts[0]);
		if (plugin_newsletter_email_matches($decoded, $email)) {
			$count++;
		}
	}

	return $count;
}

function sim_pending_has_token(string $file, string $email, string $token): bool {
	foreach (sim_read($file) as $line) {
		$parts = explode('|', $line, 3);
		if (count($parts) < 3) {
			continue;
		}

		$decoded = plugin_newsletter_decrypt($parts[0]);
		if (plugin_newsletter_email_matches($decoded, $email) && hash_equals($parts[1], $token)) {
			return true;
		}
	}

	return false;
}

/**
 * @param array<int, string> $domains
 */
function sim_seed_valid_dns_cache(array $domains): void {
	$expires = time() + 86400;
	$lines = [];
	foreach ($domains as $domain) {
		$normalized = plugin_newsletter_normalize_domain($domain);
		if ($normalized !== null) {
			$lines[] = $normalized . '|valid|' . $expires;
		}
	}

	file_put_contents(CACHE_DIR . 'newsletter-dns-cache.txt', implode(PHP_EOL, $lines) . PHP_EOL, LOCK_EX);
}

/**
 * @return array<string, string>
 */
function sim_dns_cache_map(): array {
	$result = [];
	foreach (sim_read(CACHE_DIR . 'newsletter-dns-cache.txt') as $line) {
		$parts = explode('|', $line, 3);
		if (count($parts) === 3) {
			$result[$parts[0]] = $parts[1];
		}
	}

	return $result;
}

function sim_write_subscriber(string $file, string $email, int $timestamp = 0): void {
	if ($timestamp === 0) {
		$timestamp = time();
	}

	file_put_contents($file, plugin_newsletter_encrypt($email) . '|' . $timestamp . PHP_EOL, FILE_APPEND | LOCK_EX);
}

function sim_line_count(string $file): int {
	return count(plugin_newsletter_read_lines($file));
}

function sim_file_contains(string $file, string $needle): bool {
	if (!file_exists($file)) {
		return false;
	}

	$content = file_get_contents($file);
	return is_string($content) && strpos($content, $needle) !== false;
}

function sim_session_string(string $key): string {
	$value = $_SESSION[$key] ?? '';
	return is_string($value) ? $value : '';
}

/**
 * Adds a PHP CLI binary candidate to the list.
 *
 * @param array<int,string> $candidates
 */
function sim_add_php_binary_candidate(array &$candidates, string $candidate): void {
	$candidate = trim($candidate);
	if ($candidate === '') {
		return;
	}

	$candidates[] = $candidate;
}

/**
 * Returns whether the file name can be a PHP CLI binary.
 */
function sim_php_binary_name_can_be_cli(string $candidate): bool {
	$baseName = strtolower(basename($candidate));

	if ($baseName === 'php' || $baseName === 'php.exe') {
		return true;
	}

	if (strpos($baseName, 'php-cgi') === 0 || strpos($baseName, 'phpdbg') === 0) {
		return false;
	}

	return preg_match('/^php[0-9][0-9.]*([_-]?cli)?(\.exe)?$/', $baseName) === 1;
}

/**
 * Verifies that a candidate really starts a CLI PHP process.
 */
function sim_php_binary_is_cli_usable(string $candidate): bool {
	if ($candidate !== 'php' && !is_file($candidate)) {
		return false;
	}

	if (!sim_php_binary_name_can_be_cli($candidate)) {
		return false;
	}

	if (!function_exists('proc_open')) {
		return false;
	}

	$command = escapeshellarg($candidate) . ' -r ' . escapeshellarg('echo PHP_SAPI;');
	$descriptors = [
		0 => ['pipe', 'r'],
		1 => ['pipe', 'w'],
		2 => ['pipe', 'w'],
	];

	$process = proc_open($command, $descriptors, $pipes);
	if (!is_resource($process)) {
		return false;
	}

	fclose($pipes[0]);
	$output = stream_get_contents($pipes[1]);
	fclose($pipes[1]);
	$errorOutput = stream_get_contents($pipes[2]);
	fclose($pipes[2]);

	$code = proc_close($process);

	return $code === 0 && trim(is_string($output) ? $output : '') === 'cli' && trim(is_string($errorOutput) ? $errorOutput : '') === '';
}

/**
 * Finds a CLI PHP binary even when the simulation itself is started through
 * Apache/XAMPP, where PHP_BINARY may point to httpd.exe or php-cgi.exe.
 */
function sim_find_php_cli_binary(): string {
	$candidates = [];

	if (PHP_SAPI === 'cli') {
		sim_add_php_binary_candidate($candidates, PHP_BINARY);
	}

	if (defined('PHP_BINDIR')) {
		sim_add_php_binary_candidate($candidates, PHP_BINDIR . DIRECTORY_SEPARATOR . (PHP_OS_FAMILY === 'Windows' ? 'php.exe' : 'php'));
		sim_add_php_binary_candidate($candidates, PHP_BINDIR . DIRECTORY_SEPARATOR . 'php');
	}

	if (ini_get('extension_dir') !== false) {
		$extensionDir = (string) ini_get('extension_dir');
		if ($extensionDir !== '') {
			sim_add_php_binary_candidate($candidates, dirname($extensionDir) . DIRECTORY_SEPARATOR . (PHP_OS_FAMILY === 'Windows' ? 'php.exe' : 'php'));
		}
	}

	if (getenv('PHPRC') !== false) {
		$phpRc = (string) getenv('PHPRC');
		sim_add_php_binary_candidate($candidates, rtrim($phpRc, '\\/') . DIRECTORY_SEPARATOR . (PHP_OS_FAMILY === 'Windows' ? 'php.exe' : 'php'));
	}

	if (defined('PHP_BINARY')) {
		sim_add_php_binary_candidate($candidates, PHP_BINARY);
		sim_add_php_binary_candidate($candidates, dirname(PHP_BINARY) . DIRECTORY_SEPARATOR . (PHP_OS_FAMILY === 'Windows' ? 'php.exe' : 'php'));

		$xamppRoot = dirname(dirname(dirname(PHP_BINARY)));
		sim_add_php_binary_candidate($candidates, $xamppRoot . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . (PHP_OS_FAMILY === 'Windows' ? 'php.exe' : 'php'));
	}

	// Last resort: allow the operating system PATH to resolve php.
	sim_add_php_binary_candidate($candidates, 'php');

	$seen = [];
	foreach ($candidates as $candidate) {
		$key = strtolower($candidate);
		if (isset($seen[$key])) {
			continue;
		}
		$seen[$key] = true;

		if (sim_php_binary_is_cli_usable($candidate)) {
			return $candidate;
		}
	}

	sim_fail('could not locate a usable CLI PHP binary for isolated request simulations.');
	return 'php';
}

/**
 * @return array{0:int,1:string,2:string}
 */
function sim_run_php_child(string $scriptFile): array {
	if (!function_exists('proc_open')) {
		sim_fail('proc_open is required for request-order simulations.');
	}

	$phpBinary = sim_find_php_cli_binary();
	$command = escapeshellarg($phpBinary) . ' ' . escapeshellarg($scriptFile);
	$descriptors = [
		0 => ['pipe', 'r'],
		1 => ['pipe', 'w'],
		2 => ['pipe', 'w'],
	];

	$process = proc_open($command, $descriptors, $pipes);
	if (!is_resource($process)) {
		sim_fail('could not start child PHP process.');
	}

	fclose($pipes[0]);
	$stdout = stream_get_contents($pipes[1]);
	fclose($pipes[1]);
	$stderr = stream_get_contents($pipes[2]);
	fclose($pipes[2]);
	$code = proc_close($process);

	return [
		$code,
		is_string($stdout) ? $stdout : '',
		is_string($stderr) ? $stderr : '',
	];
}

/**
 * @param array{0:int,1:string,2:string} $result
 */
function sim_assert_child_success(array $result, string $message): void {
	if ($result[0] !== 0) {
		sim_write_line('FAIL: ' . $message, true);
		sim_write_line('Exit code: ' . (string) $result[0], true);
		if ($result[1] !== '') {
			sim_write_line('Child stdout: ' . trim($result[1]), true);
		}
		if ($result[2] !== '') {
			sim_write_line('Child stderr: ' . trim($result[2]), true);
		}
		exit(1);
	}

	sim_write_line('OK: ' . $message);
}

/**
 * Creates a minimal FlatPress-like request script for child-process simulations.
 *
 * @param array<string, string> $extraDefines
 * @param string                $afterRequireCode PHP code executed after the plugin has been included.
 */
function sim_write_child_request(string $scriptFile, string $root, string $pluginFile, string $requestBootstrap, array $extraDefines = [], string $afterRequireCode = ''): void {
	$defineCode = '';
	foreach ($extraDefines as $name => $value) {
		$defineCode .= "if (!defined('" . $name . "')) { define('" . $name . "', " . var_export($value, true) . "); }\n";
	}

	$code = <<<'PHP'
<?php
declare(strict_types=1);

error_reporting(E_ALL);

$root = __ROOT__;
$pluginFile = __PLUGIN__;

if (!is_dir($root . 'content/static/')) {
	mkdir($root . 'content/static/', 0777, true);
}
if (!is_dir($root . 'cache/')) {
	mkdir($root . 'cache/', 0777, true);
}

define('FP_CONTENT', $root);
define('CACHE_DIR', $root . 'cache/');
define('BLOG_BASEURL', 'https://example.test/');
define('DIR_PERMISSIONS', 0777);
define('FILE_PERMISSIONS', 0666);
__EXTRA_DEFINES__

$fp_config = [
	'general' => [
		'blogid' => 'newsletter-child-key',
		'title' => 'Simulation Blog',
		'email' => 'admin@example.test',
	],
	'locale' => [
		'lang' => 'en-us',
	],
	'plugins' => [
		'newsletter' => [
			'batch_size' => 30,
		],
	],
];

$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['REQUEST_URI'] = '/admin.php?p=plugin&action=newsletter';

if (!function_exists('is_php85_plus')) {
	function is_php85_plus(): bool {
		return PHP_VERSION_ID >= 80500;
	}
}

function register_widget(string $id, string $name, $callback): void {
}

function user_loggedin(): bool {
	return true;
}

function lang_load(string $resource): array {
	return [
		'plugin' => [
			'newsletter' => [
				'csrf_error' => 'Invalid CSRF token',
				'confirm_subject' => 'Please confirm',
				'confirm_greeting' => 'Hello',
				'confirm_link_text' => 'Confirm',
				'confirm_ignore' => 'Ignore this message',
				'legal_notice' => 'Legal notice',
				'privacy_policy' => 'Privacy policy',
				'input_email_placeholder' => 'email@example.org',
				'accept_privacy_policy' => 'Privacy policy',
				'button' => 'Subscribe',
				'subject' => 'Newsletter',
				'no_entries' => 'No entries',
				'no_comments' => 'No comments',
				'last_entries' => 'Last entries',
				'last_comments' => 'Last comments',
				'unsubscribe' => 'Unsubscribe',
			],
		],
	];
}

__REQUEST_BOOTSTRAP__

require $pluginFile;

__AFTER_REQUIRE__
PHP;

	$code = str_replace(
		['__ROOT__', '__PLUGIN__', '__EXTRA_DEFINES__', '__REQUEST_BOOTSTRAP__', '__AFTER_REQUIRE__'],
		[var_export($root, true), var_export($pluginFile, true), $defineCode, $requestBootstrap, $afterRequireCode],
		$code
	);

	file_put_contents($scriptFile, $code, LOCK_EX);
}

$pendingFile = PLUGIN_NEWSLETTER_DIR . 'pending.txt';
$subFile = PLUGIN_NEWSLETTER_DIR . 'subscribers.txt';
$now = time();

sim_write_line('Newsletter plugin simulation');

// Scenario 1: a second subscription request replaces the older pending token.
sim_reset_storage();
$email = 'User@Example.COM';
$otherEmail = 'other@example.com';
plugin_newsletter_store_pending_token_once($pendingFile, plugin_newsletter_encrypt($email), 'token-old', $now - 60, $email);
plugin_newsletter_store_pending_token_once($pendingFile, plugin_newsletter_encrypt($otherEmail), 'token-other', $now - 30, $otherEmail);
plugin_newsletter_store_pending_token_once($pendingFile, plugin_newsletter_encrypt($email), 'token-new', $now, $email);

sim_assert_true(sim_pending_token_count($pendingFile, $email) === 1, 'only one pending token remains for the same e-mail address');
sim_assert_true(sim_pending_has_token($pendingFile, $email, 'token-new'), 'the newest pending token is kept');
sim_assert_true(!sim_pending_has_token($pendingFile, $email, 'token-old'), 'the older pending token is invalidated');
sim_assert_true(sim_pending_token_count($pendingFile, $otherEmail) === 1, 'pending entries for other addresses are preserved');

// Scenario 2: the invalidated old token cannot subscribe the address.
$confirmedOld = plugin_newsletter_confirm_pending_token($pendingFile, $subFile, $email, 'token-old');
sim_assert_true($confirmedOld === false, 'an invalidated old token is rejected');
sim_assert_true(sim_count_email($subFile, $email) === 0, 'rejected old token does not create a subscriber');

// Scenario 3: the newest token confirms exactly once and removes pending rows for that address.
$confirmedNew = plugin_newsletter_confirm_pending_token($pendingFile, $subFile, $email, 'token-new');
sim_assert_true($confirmedNew === true, 'the newest token confirms successfully');
sim_assert_true(sim_count_email($subFile, $email) === 1, 'confirmed address is stored exactly once');
sim_assert_true(sim_pending_token_count($pendingFile, $email) === 0, 'pending rows for confirmed address are removed');
sim_assert_true(sim_pending_token_count($pendingFile, $otherEmail) === 1, 'unrelated pending rows remain after confirmation');

// Scenario 4: replaying the same token does not duplicate subscribers.
$confirmedReplay = plugin_newsletter_confirm_pending_token($pendingFile, $subFile, $email, 'token-new');
sim_assert_true($confirmedReplay === false, 'a replayed confirmation token is rejected');
sim_assert_true(sim_count_email($subFile, $email) === 1, 'replayed token does not duplicate subscriber rows');

// Scenario 5: legacy duplicate pending rows are healed by the first valid confirmation.
sim_reset_storage();
plugin_newsletter_store_pending_token_once($pendingFile, plugin_newsletter_encrypt($otherEmail), 'token-other', $now, $otherEmail);
file_put_contents($pendingFile, plugin_newsletter_encrypt($email) . '|legacy-a|' . ($now - 20) . PHP_EOL, FILE_APPEND | LOCK_EX);
file_put_contents($pendingFile, plugin_newsletter_encrypt($email) . '|legacy-b|' . ($now - 10) . PHP_EOL, FILE_APPEND | LOCK_EX);
$confirmedLegacy = plugin_newsletter_confirm_pending_token($pendingFile, $subFile, $email, 'legacy-b');
sim_assert_true($confirmedLegacy === true, 'one valid legacy duplicate token confirms successfully');
sim_assert_true(sim_count_email($subFile, $email) === 1, 'legacy duplicate pending rows create only one subscriber');
sim_assert_true(sim_pending_token_count($pendingFile, $email) === 0, 'all legacy pending rows for the confirmed address are removed');
sim_assert_true(sim_pending_token_count($pendingFile, $otherEmail) === 1, 'legacy cleanup keeps unrelated pending rows');

// Scenario 6: already duplicated subscribers for the same address are reduced to one row on the next confirmation.
sim_reset_storage();
file_put_contents($subFile, plugin_newsletter_encrypt($email) . '|' . ($now - 100) . PHP_EOL, LOCK_EX);
file_put_contents($subFile, plugin_newsletter_encrypt($email) . '|' . ($now - 90) . PHP_EOL, FILE_APPEND | LOCK_EX);
plugin_newsletter_store_pending_token_once($pendingFile, plugin_newsletter_encrypt($email), 'token-dedupe-existing', $now, $email);
$confirmedExisting = plugin_newsletter_confirm_pending_token($pendingFile, $subFile, $email, 'token-dedupe-existing');
sim_assert_true($confirmedExisting === true, 'confirmation succeeds when subscriber already exists');
sim_assert_true(sim_count_email($subFile, $email) === 1, 'existing duplicate subscriber rows are reduced to one for that address');

// Scenario 7: domain comparison is normalized but the local part remains exact.
sim_reset_storage();
$upperDomain = 'person@Example.COM';
$lowerDomain = 'person@example.com';
$differentLocalCase = 'Person@example.com';
plugin_newsletter_store_pending_token_once($pendingFile, plugin_newsletter_encrypt($upperDomain), 'token-domain-1', $now, $upperDomain);
plugin_newsletter_store_pending_token_once($pendingFile, plugin_newsletter_encrypt($lowerDomain), 'token-domain-2', $now, $lowerDomain);
sim_assert_true(sim_pending_token_count($pendingFile, $lowerDomain) === 1, 'domain casing is normalized for duplicate detection');
plugin_newsletter_store_pending_token_once($pendingFile, plugin_newsletter_encrypt($differentLocalCase), 'token-local-case', $now, $differentLocalCase);
sim_assert_true(sim_pending_token_count($pendingFile, $differentLocalCase) === 1, 'local-part case can remain a separate mailbox identity');

// Scenario 8: EAI local parts are accepted by the fallback even when PHP filter_var cannot validate them.
sim_reset_storage();
sim_seed_valid_dns_cache(['example.com']);
$eaiEmail = 'δοκιμή@example.com';
$prepared = plugin_newsletter_prepare_email_for_validation($eaiEmail);
sim_assert_true(is_array($prepared), 'EAI address with Unicode local part passes syntax preparation');
sim_assert_same('example.com', $prepared['domain'], 'EAI validation keeps DNS checks on normalized ASCII domain');
sim_assert_true($prepared['is_eai'] === true, 'EAI fallback marks Unicode local parts');
sim_assert_true(plugin_newsletter_is_valid_email($eaiEmail), 'EAI address with cached valid domain is accepted');
plugin_newsletter_store_pending_token_once($pendingFile, plugin_newsletter_encrypt($eaiEmail), 'token-eai', $now, $eaiEmail);
$confirmedEai = plugin_newsletter_confirm_pending_token($pendingFile, $subFile, $eaiEmail, 'token-eai');
sim_assert_true($confirmedEai === true, 'EAI address can be confirmed with the same validation semantics');
sim_assert_true(sim_count_email($subFile, $eaiEmail) === 1, 'EAI confirmed address is stored exactly once');

// Scenario 9: invalid local parts remain rejected by the fallback.
sim_assert_true(!plugin_newsletter_is_valid_email('.dot-start@example.com'), 'local part starting with dot is rejected');
sim_assert_true(!plugin_newsletter_is_valid_email('double..dot@example.com'), 'local part with consecutive dots is rejected');
sim_assert_true(!plugin_newsletter_is_valid_email('space name@example.com'), 'local part with spaces is rejected');
sim_assert_true(!plugin_newsletter_is_valid_email('missing-domain@'), 'missing domain is rejected');

// Scenario 10: DNS-cache cleanup reads only the encrypted e-mail column of subscriber rows.
sim_reset_storage();
$expires = time() + 86400;
file_put_contents($subFile, plugin_newsletter_encrypt('member@Example.COM') . '|' . ($now - 100) . PHP_EOL, LOCK_EX);
file_put_contents($subFile, plugin_newsletter_encrypt('other@keep.example') . '|' . ($now - 90) . PHP_EOL, FILE_APPEND | LOCK_EX);
file_put_contents(
	CACHE_DIR . 'newsletter-dns-cache.txt',
	'EXAMPLE.COM|valid|' . $expires . PHP_EOL .
	'keep.example|valid|' . $expires . PHP_EOL .
	'stale.example|valid|' . $expires . PHP_EOL,
	LOCK_EX
);
plugin_newsletter_cleanup_dns_cache(
	CACHE_DIR . 'newsletter-dns-cache.txt',
	PLUGIN_NEWSLETTER_DIR . 'dns-cleanup-marker.txt',
	$subFile,
	strtotime('2026-05-28 03:00:00')
);
$cacheMap = sim_dns_cache_map();
sim_assert_true(isset($cacheMap['example.com']), 'DNS cleanup keeps normalized domain from encrypted subscriber row');
sim_assert_true(isset($cacheMap['keep.example']), 'DNS cleanup keeps unrelated subscribed domain');
sim_assert_true(!isset($cacheMap['stale.example']), 'DNS cleanup removes cache domain without subscribers');

// Scenario 11: subscriber row helper does not decrypt timestamps as part of the ciphertext.
$subscriberRows = sim_read($subFile);
sim_assert_true(isset($subscriberRows[0]), 'subscriber row exists for helper test');
sim_assert_same('member@Example.COM', plugin_newsletter_decrypt_subscriber_email($subscriberRows[0]), 'subscriber helper extracts encrypted e-mail column before decrypting');

// Scenario 12: expired or malformed pending rows cannot be confirmed and are cleaned up.
sim_reset_storage();
file_put_contents($pendingFile, "malformed-pending-row\n", LOCK_EX);
file_put_contents($pendingFile, plugin_newsletter_encrypt($email) . '|expired-token|' . ($now - 25 * 3600) . PHP_EOL, FILE_APPEND | LOCK_EX);
plugin_newsletter_store_pending_token_once($pendingFile, plugin_newsletter_encrypt($otherEmail), 'token-still-valid', $now, $otherEmail);
$confirmedExpired = plugin_newsletter_confirm_pending_token($pendingFile, $subFile, $email, 'expired-token');
sim_assert_true($confirmedExpired === false, 'expired pending token is rejected');
sim_assert_true(sim_pending_token_count($pendingFile, $email) === 0, 'expired and malformed pending rows are removed');
sim_assert_true(sim_pending_token_count($pendingFile, $otherEmail) === 1, 'valid pending row survives pending cleanup');

// Scenario 13: manual dispatch preparation respects running batches and writes the flag only when allowed.
sim_reset_storage();
sim_write_subscriber($subFile, 'batch-a@example.com', $now - 100);
sim_write_subscriber($subFile, 'batch-b@example.com', $now - 90);
$offsetFile = PLUGIN_NEWSLETTER_DIR . 'batch-offset.txt';
$manualFlagFile = PLUGIN_NEWSLETTER_DIR . 'manual-flag.txt';
file_put_contents($offsetFile, '1', LOCK_EX);
$runningPrepared = plugin_newsletter_prepare_manual_dispatch($offsetFile, $subFile, $manualFlagFile);
sim_assert_true($runningPrepared === false, 'running batch prevents a new manual dispatch');
sim_assert_true(!file_exists($manualFlagFile), 'running batch does not create manual-flag.txt');
file_put_contents($offsetFile, '0', LOCK_EX);
$manualPrepared = plugin_newsletter_prepare_manual_dispatch($offsetFile, $subFile, $manualFlagFile);
sim_assert_true($manualPrepared === true, 'idle subscriber list can prepare a manual dispatch');
sim_assert_true(file_exists($manualFlagFile), 'manual-flag.txt is created after the dispatch is allowed');

// Scenario 14: a direct send-now batch removes invalid subscriber rows and logs them.
sim_reset_storage();
sim_seed_valid_dns_cache(['example.com']);
sim_write_subscriber($subFile, 'valid@example.com', $now - 100);
sim_write_subscriber($subFile, 'not-an-email-address', $now - 90);
file_put_contents(PLUGIN_NEWSLETTER_DIR . 'batch-offset.txt', '1', LOCK_EX);
plugin_newsletter_send_now($subFile);
sim_assert_true(sim_count_email($subFile, 'valid@example.com') === 1, 'send-now keeps unprocessed valid subscribers while cleaning the active batch');
sim_assert_true(sim_count_email($subFile, 'not-an-email-address') === 0, 'send-now removes invalid subscribers from the active batch');
sim_assert_true(sim_file_contains(PLUGIN_NEWSLETTER_DIR . 'bounced-log.txt', 'not-an-email-address'), 'send-now logs removed invalid subscribers');

// Scenario 15: sender and subject sanitizing removes header-injection vectors.
$sanitizedNamedFrom = plugin_newsletter_sanitize_from_email('Simulation <sender@example.test>');
sim_assert_same('sender@example.test', $sanitizedNamedFrom, 'sender sanitizer extracts plain address from display-name format');
$sanitizedInjectedFrom = plugin_newsletter_sanitize_from_email("sender@example.test\r\nBcc: victim@example.test");
sim_assert_same('noreply@example.test', $sanitizedInjectedFrom, 'sender sanitizer falls back after CRLF injection');
$encodedSubject = plugin_newsletter_encode_subject("Newsletter\r\nBcc: victim@example.test", 'UTF-8');
sim_assert_true(strpos($encodedSubject, "\r") === false && strpos($encodedSubject, "\n") === false, 'subject encoder strips CRLF before encoding');

// Scenario 16: the widget creates CSRF and honeypot state and hides itself for blocked IPs.
sim_reset_storage();
unset($_SESSION['newsletter_widget_csrf_token'], $_SESSION['newsletter_hp_field']);
$widget = plugin_newsletter_widget();
$widgetCsrfToken = sim_session_string('newsletter_widget_csrf_token');
$widgetHoneypotField = sim_session_string('newsletter_hp_field');
sim_assert_true($widgetCsrfToken !== '', 'widget creates a front-end CSRF token');
sim_assert_true($widgetHoneypotField !== '', 'widget creates a dynamic honeypot field name');
sim_assert_true(strpos($widget['content'], 'newsletter_widget_csrf_token') !== false, 'widget renders the CSRF field');
sim_assert_true(strpos($widget['content'], 'newsletter_privacy') !== false, 'widget renders the privacy-consent checkbox');
file_put_contents(PLUGIN_NEWSLETTER_DIR . 'blocked-ips.txt', plugin_newsletter_get_client_ip() . '|' . time() . PHP_EOL, LOCK_EX);
$blockedWidget = plugin_newsletter_widget();
sim_assert_same('', $blockedWidget['content'], 'widget returns empty content for a currently blocked IP');

// Scenario 17: a valid front-end subscription request writes one pending row.
$subscribeRoot = sys_get_temp_dir() . '/flatpress_newsletter_subscribe_valid_' . getmypid() . '/';
sim_rrmdir($subscribeRoot);
mkdir($subscribeRoot . 'plugin_newsletter/', 0777, true);
mkdir($subscribeRoot . 'cache/', 0777, true);
file_put_contents($subscribeRoot . 'plugin_newsletter/disposable-email-blocklist.txt', "blocked.invalid\n", LOCK_EX);
file_put_contents($subscribeRoot . 'cache/newsletter-dns-cache.txt', 'allowed.example|valid|' . (time() + 86400) . PHP_EOL, LOCK_EX);
$subscribeScript = $subscribeRoot . 'request.php';
sim_write_child_request(
	$subscribeScript,
	$subscribeRoot,
	$baseDir . '/fp-plugins/newsletter/plugin.newsletter.php',
	'$_SERVER[\'REQUEST_METHOD\'] = \'POST\';' . PHP_EOL .
	'$_SESSION[\'newsletter_widget_csrf_token\'] = \'frontend-token\';' . PHP_EOL .
	'$_SESSION[\'newsletter_hp_field\'] = \'hp_test\';' . PHP_EOL .
	'$_POST[\'newsletter_submit\'] = \'1\';' . PHP_EOL .
	'$_POST[\'newsletter_widget_csrf_token\'] = \'frontend-token\';' . PHP_EOL .
	'$_POST[\'newsletter_email\'] = \'reader@allowed.example\';' . PHP_EOL .
	'$_POST[\'newsletter_privacy\'] = \'1\';' . PHP_EOL
);
$subscribeResult = sim_run_php_child($subscribeScript);
sim_assert_child_success($subscribeResult, 'valid front-end subscription request exits without a PHP fatal error');
sim_assert_same(1, sim_line_count($subscribeRoot . 'plugin_newsletter/pending.txt'), 'valid front-end subscription writes exactly one pending row');
sim_rrmdir($subscribeRoot);

// Scenario 18: consent and honeypot protections prevent pending writes.
$consentRoot = sys_get_temp_dir() . '/flatpress_newsletter_subscribe_consent_' . getmypid() . '/';
sim_rrmdir($consentRoot);
mkdir($consentRoot . 'plugin_newsletter/', 0777, true);
mkdir($consentRoot . 'cache/', 0777, true);
file_put_contents($consentRoot . 'plugin_newsletter/disposable-email-blocklist.txt', "blocked.invalid\n", LOCK_EX);
file_put_contents($consentRoot . 'cache/newsletter-dns-cache.txt', 'allowed.example|valid|' . (time() + 86400) . PHP_EOL, LOCK_EX);
$consentScript = $consentRoot . 'request.php';
sim_write_child_request(
	$consentScript,
	$consentRoot,
	$baseDir . '/fp-plugins/newsletter/plugin.newsletter.php',
	'$_SERVER[\'REQUEST_METHOD\'] = \'POST\';' . PHP_EOL .
	'$_SESSION[\'newsletter_widget_csrf_token\'] = \'frontend-token\';' . PHP_EOL .
	'$_SESSION[\'newsletter_hp_field\'] = \'hp_test\';' . PHP_EOL .
	'$_POST[\'newsletter_submit\'] = \'1\';' . PHP_EOL .
	'$_POST[\'newsletter_widget_csrf_token\'] = \'frontend-token\';' . PHP_EOL .
	'$_POST[\'newsletter_email\'] = \'reader@allowed.example\';' . PHP_EOL
);
$consentResult = sim_run_php_child($consentScript);
sim_assert_child_success($consentResult, 'missing privacy consent request exits without a PHP fatal error');
sim_assert_true(!file_exists($consentRoot . 'plugin_newsletter/pending.txt') || sim_line_count($consentRoot . 'plugin_newsletter/pending.txt') === 0, 'missing privacy consent does not write pending rows');
sim_rrmdir($consentRoot);

$honeypotRoot = sys_get_temp_dir() . '/flatpress_newsletter_honeypot_' . getmypid() . '/';
sim_rrmdir($honeypotRoot);
mkdir($honeypotRoot . 'plugin_newsletter/', 0777, true);
mkdir($honeypotRoot . 'cache/', 0777, true);
file_put_contents($honeypotRoot . 'plugin_newsletter/disposable-email-blocklist.txt', "blocked.invalid\n", LOCK_EX);
file_put_contents($honeypotRoot . 'cache/newsletter-dns-cache.txt', 'allowed.example|valid|' . (time() + 86400) . PHP_EOL, LOCK_EX);
$honeypotScript = $honeypotRoot . 'request.php';
sim_write_child_request(
	$honeypotScript,
	$honeypotRoot,
	$baseDir . '/fp-plugins/newsletter/plugin.newsletter.php',
	'$_SERVER[\'REQUEST_METHOD\'] = \'POST\';' . PHP_EOL .
	'$_SESSION[\'newsletter_widget_csrf_token\'] = \'frontend-token\';' . PHP_EOL .
	'$_SESSION[\'newsletter_hp_field\'] = \'hp_test\';' . PHP_EOL .
	'$_POST[\'newsletter_submit\'] = \'1\';' . PHP_EOL .
	'$_POST[\'newsletter_widget_csrf_token\'] = \'frontend-token\';' . PHP_EOL .
	'$_POST[\'newsletter_email\'] = \'reader@allowed.example\';' . PHP_EOL .
	'$_POST[\'newsletter_privacy\'] = \'1\';' . PHP_EOL .
	'$_POST[\'hp_test\'] = \'bot-filled-this\';' . PHP_EOL
);
$honeypotResult = sim_run_php_child($honeypotScript);
sim_assert_child_success($honeypotResult, 'honeypot request exits without a PHP fatal error');
sim_assert_true(file_exists($honeypotRoot . 'plugin_newsletter/blocked-ips.txt'), 'honeypot request records the blocked IP');
sim_assert_true(!file_exists($honeypotRoot . 'plugin_newsletter/pending.txt') || sim_line_count($honeypotRoot . 'plugin_newsletter/pending.txt') === 0, 'honeypot request does not write pending rows');
sim_rrmdir($honeypotRoot);

// Scenario 19: unsubscribe removes the matching address and keeps unrelated subscribers.
$unsubscribeRoot = sys_get_temp_dir() . '/flatpress_newsletter_unsubscribe_' . getmypid() . '/';
sim_rrmdir($unsubscribeRoot);
mkdir($unsubscribeRoot . 'plugin_newsletter/', 0777, true);
file_put_contents($unsubscribeRoot . 'plugin_newsletter/disposable-email-blocklist.txt', "blocked.invalid\n", LOCK_EX);
$unsubscribeScript = $unsubscribeRoot . 'request.php';
sim_write_child_request(
	$unsubscribeScript,
	$unsubscribeRoot,
	$baseDir . '/fp-plugins/newsletter/plugin.newsletter.php',
	'$_SERVER[\'REQUEST_METHOD\'] = \'GET\';' . PHP_EOL,
	[],
	'$subFile = PLUGIN_NEWSLETTER_DIR . \'subscribers.txt\';' . PHP_EOL .
	'file_put_contents($subFile, plugin_newsletter_encrypt(\'remove@example.com\') . \'|100\' . PHP_EOL, LOCK_EX);' . PHP_EOL .
	'file_put_contents($subFile, plugin_newsletter_encrypt(\'keep@example.com\') . \'|100\' . PHP_EOL, FILE_APPEND | LOCK_EX);' . PHP_EOL .
	'plugin_newsletter_handle_unsubscribe(urlencode(\'remove@example.com\'));' . PHP_EOL
);
$unsubscribeResult = sim_run_php_child($unsubscribeScript);
sim_assert_child_success($unsubscribeResult, 'unsubscribe request exits without a PHP fatal error');
sim_assert_same(1, sim_line_count($unsubscribeRoot . 'plugin_newsletter/subscribers.txt'), 'unsubscribe removes only the matching subscriber row');
sim_rrmdir($unsubscribeRoot);

// Scenario 20: monthly blocklist refresh removes newly blocked subscribers and keeps unreadable legacy rows.
$monthlyBlocklistRoot = sys_get_temp_dir() . '/flatpress_newsletter_blocklist_monthly_' . getmypid() . '/';
sim_rrmdir($monthlyBlocklistRoot);
mkdir($monthlyBlocklistRoot . 'remote/', 0777, true);
mkdir($monthlyBlocklistRoot . 'plugin_newsletter/', 0777, true);
$monthlyRemoteBlocklist = $monthlyBlocklistRoot . 'remote/disposable.conf';
file_put_contents($monthlyRemoteBlocklist, "example.com\n", LOCK_EX);
$monthlyBlocklistScript = $monthlyBlocklistRoot . 'request.php';
sim_write_child_request(
	$monthlyBlocklistScript,
	$monthlyBlocklistRoot,
	$baseDir . '/fp-plugins/newsletter/plugin.newsletter.php',
	'$_SERVER[\'REQUEST_METHOD\'] = \'GET\';' . PHP_EOL,
	[
		'NEWSLETTER_BLOCKLIST_URL' => $monthlyRemoteBlocklist,
	],
	'$subFile = PLUGIN_NEWSLETTER_DIR . \'subscribers.txt\';' . PHP_EOL .
	'file_put_contents($subFile, plugin_newsletter_encrypt(\'blocked@example.com\') . \'|100\' . PHP_EOL, LOCK_EX);' . PHP_EOL .
	'file_put_contents($subFile, plugin_newsletter_encrypt(\'keep@allowed.example\') . \'|100\' . PHP_EOL, FILE_APPEND | LOCK_EX);' . PHP_EOL .
	'file_put_contents($subFile, \'legacy-unreadable-row|100\' . PHP_EOL, FILE_APPEND | LOCK_EX);' . PHP_EOL .
	'$local = PLUGIN_NEWSLETTER_DIR . \'disposable-email-blocklist.txt\';' . PHP_EOL .
	'file_put_contents($local, "old.invalid\n", LOCK_EX);' . PHP_EOL .
	'@touch($local, strtotime(\'2026-04-01 00:00:00\'));' . PHP_EOL .
	'@unlink(PLUGIN_NEWSLETTER_DIR . \'disposable-email-blocklist.last_attempt.txt\');' . PHP_EOL .
	'plugin_newsletter_maybe_update_blocklist(true);' . PHP_EOL
);
$monthlyBlocklistResult = sim_run_php_child($monthlyBlocklistScript);
sim_assert_child_success($monthlyBlocklistResult, 'monthly blocklist refresh request exits without a PHP fatal error');
sim_assert_same(2, sim_line_count($monthlyBlocklistRoot . 'plugin_newsletter/subscribers.txt'), 'monthly blocklist cleanup removes blocked subscribers and preserves readable/unreadable keep rows');
sim_assert_true(sim_file_contains($monthlyBlocklistRoot . 'plugin_newsletter/disposable-email-blocklist.txt', 'example.com'), 'monthly blocklist refresh writes the downloaded blocklist');
sim_rrmdir($monthlyBlocklistRoot);

// Scenario 21: invalid admin CSRF must not leave a manual dispatch flag behind.
$csrfRoot = sys_get_temp_dir() . '/flatpress_newsletter_admin_csrf_' . getmypid() . '/';
sim_rrmdir($csrfRoot);
mkdir($csrfRoot . 'plugin_newsletter/', 0777, true);
file_put_contents($csrfRoot . 'plugin_newsletter/disposable-email-blocklist.txt', "blocked.invalid\n", LOCK_EX);
$csrfScript = $csrfRoot . 'request.php';
sim_write_child_request(
	$csrfScript,
	$csrfRoot,
	$baseDir . '/fp-plugins/newsletter/plugin.newsletter.php',
	'$_SERVER[\'REQUEST_METHOD\'] = \'POST\';' . PHP_EOL .
	'$_SESSION[\'newsletter_admin_csrf_token\'] = \'expected-token\';' . PHP_EOL .
	'$_POST[\'newsletter_send_now\'] = \'1\';' . PHP_EOL .
	'$_POST[\'csrf_token\'] = \'wrong-token\';' . PHP_EOL
);
$csrfResult = sim_run_php_child($csrfScript);
sim_assert_child_success($csrfResult, 'invalid admin CSRF request exits without a PHP fatal error');
sim_assert_true(!file_exists($csrfRoot . 'plugin_newsletter/manual-flag.txt'), 'invalid admin CSRF does not create manual-flag.txt');
sim_rrmdir($csrfRoot);

// Scenario 22: a first subscription request can use the freshly bootstrapped blocklist.
$blocklistRoot = sys_get_temp_dir() . '/flatpress_newsletter_blocklist_bootstrap_' . getmypid() . '/';
sim_rrmdir($blocklistRoot);
mkdir($blocklistRoot . 'remote/', 0777, true);
mkdir($blocklistRoot . 'cache/', 0777, true);
$remoteBlocklist = $blocklistRoot . 'remote/disposable.conf';
file_put_contents($remoteBlocklist, "example.com\n", LOCK_EX);
file_put_contents($blocklistRoot . 'cache/newsletter-dns-cache.txt', 'example.com|valid|' . (time() + 86400) . PHP_EOL, LOCK_EX);
$blocklistScript = $blocklistRoot . 'request.php';
sim_write_child_request(
	$blocklistScript,
	$blocklistRoot,
	$baseDir . '/fp-plugins/newsletter/plugin.newsletter.php',
	'$_SERVER[\'REQUEST_METHOD\'] = \'POST\';' . PHP_EOL .
	'$_SESSION[\'newsletter_widget_csrf_token\'] = \'frontend-token\';' . PHP_EOL .
	'$_SESSION[\'newsletter_hp_field\'] = \'hp_test\';' . PHP_EOL .
	'$_POST[\'newsletter_submit\'] = \'1\';' . PHP_EOL .
	'$_POST[\'newsletter_widget_csrf_token\'] = \'frontend-token\';' . PHP_EOL .
	'$_POST[\'newsletter_email\'] = \'first@example.com\';' . PHP_EOL .
	'$_POST[\'newsletter_privacy\'] = \'1\';' . PHP_EOL,
	[
		// Use a plain local filesystem path instead of a file:// URI. Windows
		// paths such as C:\\path\\file are not valid when naively prefixed
		// with file://, while file_get_contents() supports local paths directly.
		'NEWSLETTER_BLOCKLIST_URL' => $remoteBlocklist,
	]
);
$blocklistResult = sim_run_php_child($blocklistScript);
sim_assert_child_success($blocklistResult, 'first subscription blocklist request exits without a PHP fatal error');
$localBlocklist = $blocklistRoot . 'plugin_newsletter/disposable-email-blocklist.txt';
sim_assert_true(file_exists($localBlocklist), 'blocklist is bootstrapped before subscription handling');
sim_assert_true(strpos((string)file_get_contents($localBlocklist), 'example.com') !== false, 'bootstrapped blocklist contains the remote domain');
sim_assert_true(!file_exists($blocklistRoot . 'plugin_newsletter/pending.txt') || count(plugin_newsletter_read_lines($blocklistRoot . 'plugin_newsletter/pending.txt')) === 0, 'blocked first-request address is not written to pending.txt');
sim_rrmdir($blocklistRoot);

sim_rrmdir($testRoot);

sim_write_line('All newsletter plugin simulations passed.');
