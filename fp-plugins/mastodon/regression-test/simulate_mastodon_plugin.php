<?php
/**
 * This regression test comprehensively checks the current state of the Mastodon plugin, using fixtures and mock responses.
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

/**
 * Convert a CLI/query/environment boolean flag into a stable true/false value.
 * @param mixed $value
 * @return bool
 */
function simulate_truthy_flag_value($value) {
	if (!is_string($value) && !is_numeric($value) && !is_bool($value)) {
		return false;
	}
	$value = strtolower(trim((string) $value));
	return $value !== '' && $value !== '0' && $value !== 'false' && $value !== 'off' && $value !== 'no';
}

/**
 * Return a value as mixed for simulation paths where an included PHP file mutates local scope.
 *
 * PHPStan cannot infer variables populated by dynamic language-file includes. This tiny identity
 * helper keeps the runtime behavior unchanged while documenting the intentionally dynamic value.
 *
 * @param mixed $value
 * @return mixed
 */
function simulate_mastodon_as_mixed($value) {
	return $value;
}

/**
 * Decide whether the simulation should print compact per-test details.
 * @return bool
 */
function simulate_summary_output_requested() {
	$summaryEnv = getenv('SIMULATE_MASTODON_SUMMARY');
	if (simulate_truthy_flag_value($summaryEnv)) {
		return true;
	}
	if (PHP_SAPI === 'cli') {
		$cliArguments = isset($_SERVER ['argv']) ? (array) $_SERVER ['argv'] : array();
		if (in_array('--summary', $cliArguments, true)) {
			return true;
		}
	}
	return isset($_GET ['summary']) && simulate_truthy_flag_value($_GET ['summary']);
}

$GLOBALS ['simulate_mastodon_summary_output'] = simulate_summary_output_requested();
$GLOBALS ['simulate_mastodon_result_counts'] = array(
	'OK' => 0,
	'FAIL' => 0,
	'WARN' => 0,
	'SKIP' => 0
);

/**
 * Return a UTF-8-safe prefix where mbstring is available, with a byte-safe fallback.
 * @param string $text
 * @param int $limit
 * @return string
 */
function simulate_text_prefix($text, $limit) {
	$text = (string) $text;
	$limit = max(0, (int) $limit);
	if (function_exists('mb_substr')) {
		return (string) mb_substr($text, 0, $limit, 'UTF-8');
	}
	return substr($text, 0, $limit);
}

/**
 * Build a compact detail string for summary output.
 * @param string $details
 * @return string
 */
function simulate_short_test_details($details) {
	$details = (string) $details;
	if ($details === '') {
		return '';
	}
	$byteLength = strlen($details);
	$decoded = json_decode($details, true);
	if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
		$keys = array_keys($decoded);
		$isList = $keys === range(0, count($keys) - 1);
		if ($isList) {
			return 'json list items=' . count($decoded) . ', bytes=' . $byteLength;
		}
		$keyNames = array();
		foreach (array_slice($keys, 0, 6) as $key) {
			$keyNames [] = (string) $key;
		}
		$more = count($keys) > 6 ? ', ...' : '';
		return 'json keys=' . implode(',', $keyNames) . $more . ', bytes=' . $byteLength;
	}

	$singleLine = trim((string) preg_replace('/\s+/', ' ', $details));
	if (strlen($singleLine) <= 180) {
		return $singleLine;
	}
	return simulate_text_prefix($singleLine, 140) . '... bytes=' . $byteLength;
}

/**
 * Format details according to the currently selected output mode.
 * @param string $details
 * @return string
 */
function simulate_format_test_details($details) {
	$details = (string) $details;
	if ($details === '') {
		return '';
	}
	if (!empty($GLOBALS ['simulate_mastodon_summary_output'])) {
		return simulate_short_test_details($details);
	}
	return $details;
}

/**
 * Print a test result line and update the final summary counters.
 * @param string $status
 * @param string $name
 * @param string $details
 * @return void
 */
function simulate_print_test_status($status, $name, $details = '') {
	$status = strtoupper((string) $status);
	if (!isset($GLOBALS ['simulate_mastodon_result_counts'] [$status])) {
		$GLOBALS ['simulate_mastodon_result_counts'] [$status] = 0;
	}
	$GLOBALS ['simulate_mastodon_result_counts'] [$status]++;

	echo '[' . $status . '] ' . $name;
	$details = simulate_format_test_details((string) $details);
	if ($details !== '') {
		echo ' - ' . $details;
	}
	echo PHP_EOL;
}

/**
 * Format the final summary block.
 * @param int $exitCode
 * @return string
 */
function simulate_format_final_summary($exitCode) {
	$counts = isset($GLOBALS ['simulate_mastodon_result_counts']) && is_array($GLOBALS ['simulate_mastodon_result_counts'])
		? $GLOBALS ['simulate_mastodon_result_counts']
		: array();

	$lines = array('Exit-code: ' . (int) $exitCode);
	foreach (array('OK', 'FAIL', 'WARN', 'SKIP') as $status) {
		$lines [] = '[' . $status . ']: ' . (isset($counts [$status]) ? (int) $counts [$status] : 0);
	}
	return implode(PHP_EOL, $lines) . PHP_EOL;
}

/**
 * Print the final summary block.
 * @param int $exitCode
 * @return void
 */
function simulate_print_final_summary($exitCode) {
	echo simulate_format_final_summary($exitCode);
}

/**
 * Print and return a test result line.
 * @param string $name
 * @param bool $condition
 * @param string $details
 * @return bool
 */
function test_result($name, $condition, $details = '') {
	simulate_print_test_status($condition ? 'OK' : 'FAIL', $name, $details);
	return (bool) $condition;
}

/**
 * Print a non-fatal warning for environment-dependent regression coverage.
 *
 * Warnings keep the simulation process successful, but they explicitly mark
 * coverage that should be repeated in a better-resourced environment.
 *
 * @param string $name
 * @param string $details
 * @return bool
 */
function test_warn($name, $details = '') {
	simulate_print_test_status('WARN', $name, $details);
	return true;
}

/**
 * Print an intentional skip for optional or unsupported regression coverage.
 *
 * @param string $name
 * @param string $details
 * @return bool
 */
function test_skip($name, $details = '') {
	simulate_print_test_status('SKIP', $name, $details);
	return true;
}

/**
 * Return the comment-shard read ids recorded by plugin test instrumentation.
 * @return array<int, string>
 */
function simulate_mastodon_comment_shard_read_ids() {
	$ids = array();
	if (!isset($GLOBALS ['plugin_mastodon_test_comment_shard_read_ids']) || !is_array($GLOBALS ['plugin_mastodon_test_comment_shard_read_ids'])) {
		return $ids;
	}
	foreach ($GLOBALS ['plugin_mastodon_test_comment_shard_read_ids'] as $entryId) {
		$ids [] = (string) $entryId;
	}
	return $ids;
}

/**
 * Load one Mastodon plugin language file in isolation and return its admin plugin entries.
 *
 * This mirrors FlatPress' early admin-language phase where panelstrings are resolved
 * before the plugin-specific admin assignment function can add runtime fallbacks.
 *
 * @param string $file
 * @return array<string, mixed>
 */
function simulate_mastodon_read_admin_plugin_language_entries($file) {
	if (!is_file($file)) {
		return array();
	}

	$lang = array();
	include $file;
	$lang = simulate_mastodon_as_mixed($lang);

	if (!is_array($lang)) {
		return array();
	}

	$adminStrings = (isset($lang ['admin']) && is_array($lang ['admin'])) ? $lang ['admin'] : array();
	$pluginStrings = (isset($adminStrings ['plugin']) && is_array($adminStrings ['plugin'])) ? $adminStrings ['plugin'] : array();

	return $pluginStrings;
}

/**
 * Load one Mastodon plugin language file in isolation and return its public widget entries.
 *
 * @param string $file
 * @return array<string, mixed>
 */
function simulate_mastodon_read_widget_language_entries($file) {
	if (!is_file($file)) {
		return array();
	}

	$lang = array();
	include $file;
	$lang = simulate_mastodon_as_mixed($lang);

	if (!is_array($lang)) {
		return array();
	}

	$pluginStrings = (isset($lang ['plugin']) && is_array($lang ['plugin'])) ? $lang ['plugin'] : array();
	$widgetStrings = (isset($pluginStrings ['mastodon']) && is_array($pluginStrings ['mastodon'])) ? $pluginStrings ['mastodon'] : array();

	return $widgetStrings;
}

/**
 * Minimal Smarty-like collector for admin assignment tests.
 */
class SimulateSmartyCollector {

	/** @var array<string, mixed> */
	public $assigned = array();

	/**
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function assign($name, $value) {
		$this->assigned [(string) $name] = $value;
	}
}

/**
 * Delete a file tree recursively.
 * @param string $path
 * @return void
 */
function simulate_delete_recursive($path) {
	if (!file_exists($path) && !is_link($path)) {
		return;
	}
	if (is_file($path) || is_link($path)) {
		@unlink($path);
		return;
	}
	$items = scandir($path);
	if (is_array($items)) {
		foreach ($items as $item) {
			if ($item === '.' || $item === '..') {
				continue;
			}
			simulate_delete_recursive($path . DIRECTORY_SEPARATOR . $item);
		}
	}
	@rmdir($path);
}

/**
 * Check a file permission mode when the platform exposes POSIX-style mode bits.
 * @param string $path
 * @param int $expectedMode
 * @return bool
 */
function simulate_file_mode_matches($path, $expectedMode) {
	clearstatcache(true, $path);
	if (!is_file($path)) {
		return false;
	}
	if (DIRECTORY_SEPARATOR === '\\') {
		return true;
	}
	$perms = @fileperms($path);
	if ($perms === false) {
		return false;
	}
	return (($perms & 0777) === (int) $expectedMode);
}

/**
 * Move a pending deletion synchronization past its delay window for follow-up simulations.
 * @return void
 */
function simulate_allow_pending_deletion_sync() {
	$state = plugin_mastodon_state_read();
	if (!empty($state ['deletions_pending'])) {
		$state ['deletions_not_before'] = gmdate('Y-m-d H:i:s', time() - 1);
		plugin_mastodon_state_write($state);
	}
}

/**
 * Copy a file tree recursively while skipping configured paths.
 * @param string $source
 * @param string $target
 * @param string $sourceRoot
 * @param array<int, string> $excludedRelativePaths
 * @return void
 */
function simulate_copy_recursive($source, $target, $sourceRoot, $excludedRelativePaths) {
	if (!file_exists($source) && !is_link($source)) {
		return;
	}

	$relative = ltrim(str_replace('\\', '/', substr($source, strlen($sourceRoot))), '/');
	foreach ($excludedRelativePaths as $excludedPath) {
		if ($relative === $excludedPath || strpos($relative, $excludedPath . '/') === 0) {
			return;
		}
	}

	if (is_link($source)) {
		$linkTarget = readlink($source);
		if ($linkTarget !== false) {
			$dir = dirname($target);
			if (!is_dir($dir)) {
				mkdir($dir, 0777, true);
			}
			@symlink($linkTarget, $target);
		}
		return;
	}

	if (is_file($source)) {
		$dir = dirname($target);
		if (!is_dir($dir)) {
			mkdir($dir, 0777, true);
		}
		if (!@copy($source, $target)) {
			$data = @file_get_contents($source);
			if ($data !== false) {
				file_put_contents($target, $data);
			}
		}
		return;
	}

	if (!is_dir($target)) {
		mkdir($target, 0777, true);
	}
	$items = scandir($source);
	if (!is_array($items)) {
		return;
	}
	foreach ($items as $item) {
		if ($item === '.' || $item === '..') {
			continue;
		}
		simulate_copy_recursive(
			$source . DIRECTORY_SEPARATOR . $item,
			$target . DIRECTORY_SEPARATOR . $item,
			$sourceRoot,
			$excludedRelativePaths
		);
	}
}

/**
 * Decide whether the simulation sandbox should include live blog content.
 *
 * The default is intentionally false so regression tests on production-like shared-hosting
 * instances do not copy thousands of entry and comment files before the first assertion.
 * @return bool
 */
function simulate_sandbox_should_include_live_content() {
	$includeLiveContent = getenv('SIMULATE_MASTODON_INCLUDE_LIVE_CONTENT');
	if (is_string($includeLiveContent) && $includeLiveContent !== '' && strtolower($includeLiveContent) !== 'false' && $includeLiveContent !== '0') {
		return true;
	}
	return simulate_request_flag('include-live-content');
}

/**
 * Create an isolated sandbox copy of the FlatPress instance.
 *
 * Note: The sandbox deliberately skips volatile cache/index artefacts and, by default, live
 * `fp-content/content` data. The regression suite seeds its own deterministic content fixtures.
 * Passing true as the second argument or using `--include-live-content` / `SIMULATE_MASTODON_INCLUDE_LIVE_CONTENT=1`
 * enables the old copy behavior for explicit live-content smoke tests.
 * @param string $sourceRoot
 * @param bool|null $includeLiveContent
 * @return string
 */
function simulate_prepare_sandbox($sourceRoot, $includeLiveContent = null) {
	$sourceRoot = rtrim($sourceRoot, DIRECTORY_SEPARATOR);
	$sandboxRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'flatpress-mastodon-sim-' . str_replace('.', '-', uniqid('', true));
	$excludedRelativePaths = array(
		'.git',
		'.github',
		'fp-content/index',
		'fp-content/compile',
		'fp-content/cache'
	);
	if ($includeLiveContent === null) {
		$includeLiveContent = simulate_sandbox_should_include_live_content();
	}
	if (!$includeLiveContent) {
		$excludedRelativePaths [] = 'fp-content/content';
	}

	simulate_copy_recursive($sourceRoot, $sandboxRoot, $sourceRoot, $excludedRelativePaths);

	foreach (array('fp-content/index', 'fp-content/compile', 'fp-content/cache', 'fp-content/content', 'fp-content/plugin_mastodon') as $relativeDir) {
		$absoluteDir = $sandboxRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativeDir);
		if (!is_dir($absoluteDir)) {
			mkdir($absoluteDir, 0777, true);
		}
	}

	return $sandboxRoot;
}

/**
 * Read a boolean request flag from CLI arguments or the query string.
 * @param string $name
 * @return bool
 */
function simulate_request_flag($name) {
	$name = (string) $name;
	if (PHP_SAPI === 'cli') {
		$cliArguments = isset($_SERVER ['argv']) ? (array) $_SERVER ['argv'] : array();
		if (in_array('--' . $name, $cliArguments, true)) {
			return true;
		}
	}
	return isset($_GET [$name]) && $_GET [$name] !== '0';
}

/**
 * Seed sandbox plugin options from the configured live plugin options.
 * @param mixed $configuredOptions
 * @return array<string, string>
 */
function simulate_seed_options_from_config($configuredOptions) {
	$options = plugin_mastodon_default_options();
	if (!is_array($configuredOptions)) {
		$configuredOptions = array();
	}

	foreach ($options as $key => $defaultValue) {
		if (!empty($configuredOptions [$key])) {
			$options [$key] = $configuredOptions [$key];
		}
	}

	$options ['instance_url'] = plugin_mastodon_normalize_instance_url($options ['instance_url']);
	if ($options ['instance_url'] === '') {
		$options ['instance_url'] = 'https://mastodon.example';
	}
	if ($options ['client_id'] === '') {
		$options ['client_id'] = 'client123';
	}
	if ($options ['client_secret'] === '') {
		$options ['client_secret'] = 'secret123';
	}
	$options ['access_token'] = '';

	return $options;
}

/**
 * Write a FlatPress-style serialized key/value data file.
 * @param string $path
 * @param mixed $data
 * @return void
 */
function simulate_write_serialized_kv_file($path, $data) {
	$dir = dirname($path);
	if (!is_dir($dir)) {
		mkdir($dir, 0777, true);
	}
	$serialized = utils_kimplode(array_change_key_case($data, CASE_UPPER));
	file_put_contents($path, $serialized);
	clearstatcache(true, $path);
}

/**
 * Write a FlatPress entry fixture directly without firing post-success hooks.
 * @param string $entryId
 * @param array<string, mixed> $entry
 * @return void
 */
function simulate_write_entry_fixture($entryId, $entry) {
	$entryFile = entry_dir($entryId, true) . $entryId . EXT;
	simulate_write_serialized_kv_file($entryFile, $entry);
}

/**
 * Write a FlatPress comment fixture directly without firing post-success hooks.
 * @param string $entryId
 * @param string $commentId
 * @param array<string, mixed> $comment
 * @return void
 */
function simulate_write_comment_fixture($entryId, $commentId, $comment) {
	$commentFile = bdb_idtofile($entryId, BDB_COMMENT) . $commentId . EXT;
	simulate_write_serialized_kv_file($commentFile, $comment);
}

/**
 * Delete a direct FlatPress entry fixture and its comment directory.
 * @param string $entryId
 * @return void
 */
function simulate_delete_entry_fixture($entryId) {
	$entryDir = entry_dir($entryId);
	if (is_string($entryDir) && $entryDir !== '') {
		simulate_delete_recursive($entryDir);
	}
	$entryFile = entry_dir($entryId, true) . $entryId . EXT;
	if (is_file($entryFile)) {
		@unlink($entryFile);
	}
}

/**
 * Return sorted entry ids selected by the Mastodon local-to-remote sync candidate list.
 * @param array<string, string> $options
 * @param array<string, mixed> $state
 * @param bool $force
 * @return array<int, string>
 */
function simulate_entry_ids_for_sync($options, $state, $force) {
	$entries = plugin_mastodon_list_local_entries_for_sync($options, $state, $force);
	$ids = array_keys(is_array($entries) ? $entries : array());
	sort($ids, SORT_STRING);
	return $ids;
}

/**
 * Delete all entry fixtures in a list.
 * @param array<int, string> $entryIds
 * @return void
 */
function simulate_delete_entry_fixtures($entryIds) {
	foreach ($entryIds as $entryId) {
		simulate_delete_entry_fixture((string) $entryId);
	}
}

/**
 * Write an arbitrary sandbox fixture file.
 * @param string $path
 * @param string $contents
 * @return void
 */
function simulate_write_fixture_file($path, $contents) {
	$dir = dirname($path);
	if (!is_dir($dir)) {
		mkdir($dir, 0777, true);
	}
	file_put_contents($path, $contents);
	clearstatcache(true, $path);
}

/**
 * Check whether a parsed Mastodon request carries the expected language code.
 * @param array<string, mixed> $request
 * @param string $expectedLanguage
 * @return bool
 */
function simulate_request_uses_language($request, $expectedLanguage) {
	$actualLanguage = isset($request ['language']) ? (string) $request ['language'] : '';
	$expectedLanguage = trim((string) $expectedLanguage);
	if ($expectedLanguage === '') {
		return $actualLanguage === '';
	}
	return $actualLanguage === $expectedLanguage;
}

function simulate_import_quote_block($author, $content) {
	$author = trim((string) $author);
	$content = trim((string) $content);
	$quoteLines = array();
	if ($author !== '') {
		$format = plugin_mastodon_lang_string('reply_quote_author_format', '%s wrote:');
		$quoteLines [] = strpos($format, '%s') !== false ? sprintf($format, $author) : rtrim((string) $format) . ' ' . $author;
	}
	if ($content !== '') {
		$quoteLines [] = $content;
	}
	$quoteBody = trim(implode("\n", $quoteLines));
	return $quoteBody === '' ? '' : "[quote]\n" . $quoteBody . "\n[/quote]";
}

/**
 * Assign a parsed form value into a nested request-body array.
 * @param mixed $target
 * @param array<int, string> $tokens
 * @param mixed $value
 * @return void
 */
function simulate_assign_http_value(&$target, $tokens, $value) {
	$tokens = is_array($tokens) ? array_values($tokens) : array();
	if (empty($tokens)) {
		$target = $value;
		return;
	}
	$token = (string) array_shift($tokens);
	if ($token === '') {
		if (!is_array($target)) {
			$target = array();
		}
		if (empty($tokens)) {
			$target [] = $value;
			return;
		}
		$nextToken = (string) $tokens [0];
		$index = count($target) - 1;
		if ($index < 0 || !isset($target [$index]) || !is_array($target [$index]) || ($nextToken !== '' && array_key_exists($nextToken, $target [$index]))) {
			$target [] = array();
			$index = count($target) - 1;
		}
		simulate_assign_http_value($target [$index], $tokens, $value);
		return;
	}

	if (!is_array($target)) {
		$target = array();
	}
	if (empty($tokens)) {
		$target [$token] = $value;
		return;
	}
	if (!isset($target [$token]) || !is_array($target [$token])) {
		$target [$token] = array();
	}
	simulate_assign_http_value($target [$token], $tokens, $value);
}

/**
 * Parse an x-www-form-urlencoded request body recorded by the Mastodon HTTP test harness.
 * @param array<string, mixed> $request
 * @return array<string, mixed>
 */
function simulate_parse_http_request_body($request) {
	$body = (!empty($request ['body']) && is_string($request ['body'])) ? (string) $request ['body'] : '';
	if ($body === '') {
		return array();
	}

	$parsed = array();
	foreach (explode('&', $body) as $pair) {
		if ($pair === '') {
			continue;
		}
		$segments = explode('=', $pair, 2);
		$key = urldecode(isset($segments [0]) ? (string) $segments [0] : '');
		$value = urldecode(isset($segments [1]) ? (string) $segments [1] : '');
		if ($key === '') {
			continue;
		}
		$tokens = array();
		if (preg_match('/^([^\[]+)/', $key, $matches)) {
			$tokens [] = $matches [1];
		}
		if (preg_match_all('/\[([^\]]*)\]/', $key, $matches)) {
			foreach ($matches [1] as $segment) {
				$tokens [] = $segment;
			}
		}
		if (empty($tokens)) {
			continue;
		}
		simulate_assign_http_value($parsed, $tokens, $value);
	}
	$parsed ['__raw_body'] = $body;
	return is_array($parsed) ? $parsed : array();
}

/**
 * Return the first recorded HTTP request that matches the given method and URL fragment.
 * @param array<int, array<string, mixed>> $requests
 * @param string $method
 * @param string $urlFragment
 * @return array<string, mixed>
 */
function simulate_first_http_request($requests, $method, $urlFragment) {
	$method = strtoupper((string) $method);
	$urlFragment = (string) $urlFragment;
	if (!is_array($requests)) {
		return array();
	}
	foreach ($requests as $request) {
		if (!is_array($request) || empty($request ['method']) || empty($request ['url'])) {
			continue;
		}
		if (strtoupper((string) $request ['method']) !== $method) {
			continue;
		}
		if ($urlFragment !== '' && strpos((string) $request ['url'], $urlFragment) === false) {
			continue;
		}
		return $request;
	}
	return array();
}

/**
 * Return recorded HTTP requests from the Mastodon HTTP test harness.
 * @return array<int, array<string, mixed>>
 */
function simulate_recorded_http_requests() {
	$requests = isset($GLOBALS ['plugin_mastodon_test_http_requests']) && is_array($GLOBALS ['plugin_mastodon_test_http_requests']) ? $GLOBALS ['plugin_mastodon_test_http_requests'] : array();
	$recordedRequests = array();
	foreach ($requests as $request) {
		if (is_array($request)) {
			$recordedRequests [] = $request;
		}
	}
	return $recordedRequests;
}

/**
 * Return recorded multipart HTTP requests from the Mastodon HTTP test harness.
 * @return array<int, array<string, mixed>>
 */
function simulate_multipart_http_requests() {
	$requests = simulate_recorded_http_requests();
	$multipartRequests = array();
	foreach ($requests as $request) {
		if (!is_array($request)) {
			continue;
		}
		if (empty($request ['content_type']) || (string) $request ['content_type'] !== 'multipart/form-data') {
			continue;
		}
		$multipartRequests [] = $request;
	}
	return $multipartRequests;
}

/**
 * Return recorded timeout budget calls from the Mastodon timeout test harness.
 * @return array<int, array<string, mixed>>
 */
function simulate_timeout_calls() {
	$calls = isset($GLOBALS ['plugin_mastodon_test_timeout_calls']) && is_array($GLOBALS ['plugin_mastodon_test_timeout_calls']) ? $GLOBALS ['plugin_mastodon_test_timeout_calls'] : array();
	$recordedCalls = array();
	foreach ($calls as $call) {
		if (is_array($call)) {
			$recordedCalls [] = $call;
		}
	}
	return $recordedCalls;
}

/**
 * Count recorded uncached file reads for a concrete path.
 * @param string $path
 * @return int
 */
function simulate_count_uncached_reads_for_path($path) {
	$path = (string) $path;
	$reads = isset($GLOBALS ['plugin_mastodon_test_uncached_file_reads']) && is_array($GLOBALS ['plugin_mastodon_test_uncached_file_reads']) ? $GLOBALS ['plugin_mastodon_test_uncached_file_reads'] : array();
	$count = 0;
	foreach ($reads as $readPath) {
		if ((string) $readPath === $path) {
			$count++;
		}
	}
	return $count;
}

/**
 * Count recorded FlatPress cached file reads for a concrete path.
 * @param string $path
 * @return int
 */
function simulate_count_file_reads_for_path($path) {
	$path = (string) $path;
	$reads = isset($GLOBALS ['plugin_mastodon_test_file_reads']) && is_array($GLOBALS ['plugin_mastodon_test_file_reads']) ? $GLOBALS ['plugin_mastodon_test_file_reads'] : array();
	$count = 0;
	foreach ($reads as $readPath) {
		if ((string) $readPath === $path) {
			$count++;
		}
	}
	return $count;
}

/**
 * Return the local entry parse count recorded by the Mastodon sync test harness.
 * @return int
 */
function simulate_local_entry_parse_count() {
	return isset($GLOBALS ['plugin_mastodon_test_local_entry_parse_count']) ? (int) $GLOBALS ['plugin_mastodon_test_local_entry_parse_count'] : 0;
}

/**
 * Create a large synthetic Mastodon state without creating content files on disk.
 * @param int $entryCount
 * @param int $commentsPerEntry
 * @return array<string, mixed>
 */
function simulate_build_large_mastodon_state($entryCount, $commentsPerEntry) {
	$entryCount = max(0, (int) $entryCount);
	$commentsPerEntry = max(0, (int) $commentsPerEntry);
	$state = plugin_mastodon_default_state();
	$state ['last_run'] = date('Y-m-d H:i:s');
	$state ['last_deletion_run'] = date('Y-m-d H:i:s');
	$state ['deletions_pending'] = 0;
	for ($entryIndex = 1; $entryIndex <= $entryCount; $entryIndex++) {
		$entryId = 'entry-large-' . str_pad((string) $entryIndex, 5, '0', STR_PAD_LEFT);
		$entryRemoteId = 'remote-entry-' . $entryIndex;
		$state ['entries'] [$entryId] = array(
			'remote_id' => $entryRemoteId,
			'source' => 'local',
			'hash' => sha1($entryId),
			'remote_url' => 'https://mastodon.example/@flatpress/' . $entryRemoteId,
			'remote_updated_at' => '2026-03-13 03:00:00',
			'local_date_key' => '2026-03-13',
			'remote_date_key' => '2026-03-13'
		);
		$state ['entries_remote'] [$entryRemoteId] = $entryId;
		for ($commentIndex = 1; $commentIndex <= $commentsPerEntry; $commentIndex++) {
			$commentId = 'comment-large-' . str_pad((string) $commentIndex, 2, '0', STR_PAD_LEFT);
			$commentKey = plugin_mastodon_state_comment_key($entryId, $commentId);
			$commentRemoteId = 'remote-comment-' . $entryIndex . '-' . $commentIndex;
			$state ['comments'] [$commentKey] = array(
				'entry_id' => $entryId,
				'comment_id' => $commentId,
				'remote_id' => $commentRemoteId,
				'source' => 'local',
				'hash' => sha1($commentKey),
				'remote_url' => 'https://mastodon.example/@flatpress/' . $commentRemoteId,
				'remote_updated_at' => '2026-03-13 03:00:00',
				'parent_comment_id' => '',
				'in_reply_to_remote_id' => $entryRemoteId,
				'local_date_key' => '2026-03-13',
				'remote_date_key' => '2026-03-13'
			);
			$state ['comments_remote'] [$commentRemoteId] = array(
				'entry_id' => $entryId,
				'comment_id' => $commentId
			);
		}
	}
	return $state;
}

/**
 * Convert a PHP memory-limit shorthand value to bytes.
 *
 * @param string $value
 * @return int -1 for unlimited, 0 for unparsable/disabled, or a positive byte count
 */
function simulate_memory_limit_to_bytes($value) {
	$value = trim((string) $value);
	if ($value === '') {
		return 0;
	}
	if ($value === '-1') {
		return -1;
	}
	$unit = strtolower(substr($value, -1));
	$number = $value;
	if ($unit === 'g' || $unit === 'm' || $unit === 'k') {
		$number = substr($value, 0, -1);
	}
	if (!is_numeric($number)) {
		return 0;
	}
	$bytes = (float) $number;
	if ($unit === 'g') {
		$bytes *= 1024 * 1024 * 1024;
	} elseif ($unit === 'm') {
		$bytes *= 1024 * 1024;
	} elseif ($unit === 'k') {
		$bytes *= 1024;
	}
	if ($bytes <= 0) {
		return 0;
	}
	return (int) floor($bytes);
}

/**
 * Format a byte count for simulation diagnostics.
 *
 * @param int $bytes
 * @return string
 */
function simulate_format_bytes($bytes) {
	$bytes = (int) $bytes;
	if ($bytes < 0) {
		return 'unlimited';
	}
	if ($bytes >= 1024 * 1024 * 1024) {
		return round($bytes / (1024 * 1024 * 1024), 2) . 'G';
	}
	if ($bytes >= 1024 * 1024) {
		return round($bytes / (1024 * 1024), 2) . 'M';
	}
	if ($bytes >= 1024) {
		return round($bytes / 1024, 2) . 'K';
	}
	return (string) $bytes . 'B';
}

/**
 * Detect common CI environments without depending on one specific provider.
 *
 * @return bool
 */
function simulate_running_in_ci() {
	$ciVariables = array(
		'CI',
		'CONTINUOUS_INTEGRATION',
		'GITHUB_ACTIONS',
		'GITLAB_CI',
		'JENKINS_URL',
		'BUILDKITE',
		'CIRCLECI',
		'TRAVIS',
		'APPVEYOR',
		'TEAMCITY_VERSION',
		'TF_BUILD'
	);
	foreach ($ciVariables as $variable) {
		$value = getenv($variable);
		if (is_string($value) && $value !== '' && strtolower($value) !== 'false' && $value !== '0') {
			return true;
		}
	}
	return false;
}

/**
 * Return whether a memory limit is sufficient for the heavy 3000x10 state test.
 *
 * The guard intentionally runs before the large state is built. The heavy test
 * materializes a large PHP array and then a pretty-printed JSON string, so a
 * nominal 128M shared-hosting limit can fail with a fatal error even though the
 * compact scheduler-state logic itself is correct.
 *
 * @param int $minimumLimitBytes
 * @param int $minimumFreeBytes
 * @param int $ciTargetLimitBytes
 * @param array<string, mixed> $details
 * @return string One of "run", "warn", or "skip"
 */
function simulate_large_state_memory_decision($minimumLimitBytes, $minimumFreeBytes, $ciTargetLimitBytes, &$details) {
	$details = array(
		'initial_memory_limit' => ini_get('memory_limit'),
		'effective_memory_limit' => ini_get('memory_limit'),
		'minimum_limit' => simulate_format_bytes($minimumLimitBytes),
		'minimum_free' => simulate_format_bytes($minimumFreeBytes),
		'current_usage' => simulate_format_bytes(memory_get_usage(true)),
		'ci' => simulate_running_in_ci() ? 1 : 0,
		'raise_attempted' => 0,
		'raise_target' => simulate_format_bytes($ciTargetLimitBytes)
	);

	$isCi = simulate_running_in_ci();
	$limitBytes = simulate_memory_limit_to_bytes((string) ini_get('memory_limit'));
	if ($isCi && $limitBytes !== -1 && $limitBytes < $minimumLimitBytes) {
		$disableRaise = getenv('SIMULATE_MASTODON_DISABLE_MEMORY_RAISE');
		if (is_string($disableRaise) && $disableRaise !== '' && strtolower($disableRaise) !== 'false' && $disableRaise !== '0') {
			$details ['raise_disabled_by_environment'] = 1;
		} else {
			$details ['raise_attempted'] = 1;
			@ini_set('memory_limit', (string) ceil($ciTargetLimitBytes / (1024 * 1024)) . 'M');
			$details ['effective_memory_limit'] = ini_get('memory_limit');
			$limitBytes = simulate_memory_limit_to_bytes((string) ini_get('memory_limit'));
		}
	}

	$details ['effective_memory_limit'] = ini_get('memory_limit');
	$details ['current_usage'] = simulate_format_bytes(memory_get_usage(true));

	if ($limitBytes === -1) {
		return 'run';
	}

	if ($limitBytes <= 0) {
		return $isCi ? 'skip' : 'warn';
	}

	$freeBytes = $limitBytes - memory_get_usage(true);
	$details ['estimated_free'] = simulate_format_bytes($freeBytes);
	if ($limitBytes >= $minimumLimitBytes && $freeBytes >= $minimumFreeBytes) {
		return 'run';
	}

	return $isCi ? 'skip' : 'warn';
}

/**
 * Create the regression-test fixtures expected by the simulation.
 * @return void
 */
function simulate_seed_regression_fixtures() {
	$singleImageRelativePath = 'images/mastodon-sim/single-image.jpg';
	$galleryRelativeDir = 'images/mastodon-sim/gallery';
	$singleImageBinary = base64_decode('/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBxAQEBUQEBAVFRUVFRUVFRUVFRUVFRUVFRUWFhUVFRUYHSggGBolHRUVITEhJSkrLi4uFx8zODMsNygtLisBCgoKDg0OGhAQGi0fHyUtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLf/AABEIAAEAAQMBIgACEQEDEQH/xAAXAAEBAQEAAAAAAAAAAAAAAAAAAQID/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAwDAQACEAMQAAAB9A//xAAXEAEAAwAAAAAAAAAAAAAAAAABABEh/9oACAEBAAEFAm0f/8QAFBEBAAAAAAAAAAAAAAAAAAAAEP/aAAgBAwEBPwEf/8QAFBEBAAAAAAAAAAAAAAAAAAAAEP/aAAgBAgEBPwEf/8QAGhAAAwADAQAAAAAAAAAAAAAAAQIRIQAxQf/aAAgBAQAGPwK2u0W8h//EABgQAQEBAQEAAAAAAAAAAAAAAAERACEx/9oACAEBAAE/IZbR1yM7Gf/aAAwDAQACAAMAAAAQPw//xAAVEQEBAAAAAAAAAAAAAAAAAAAQIf/aAAgBAwEBPxBf/8QAFBEBAAAAAAAAAAAAAAAAAAAAEP/aAAgBAgEBPxAf/8QAGRABAAIDAAAAAAAAAAAAAAAAAREhMUFh/9oACAEBAAE/EF5gIBxW8C8iK0VJ/9k=', true);
	$galleryImageBinary = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jz6kAAAAASUVORK5CYII=', true);

	simulate_write_fixture_file(ABS_PATH . FP_CONTENT . 'images/mastodon-sim/single-image.jpg', $singleImageBinary !== false ? $singleImageBinary : 'single-image');
	simulate_write_fixture_file(ABS_PATH . FP_CONTENT . $galleryRelativeDir . '/01-gallery.png', $galleryImageBinary !== false ? $galleryImageBinary : 'gallery-01');
	simulate_write_fixture_file(ABS_PATH . FP_CONTENT . $galleryRelativeDir . '/02-gallery.png', $galleryImageBinary !== false ? $galleryImageBinary : 'gallery-02');
	simulate_write_fixture_file(ABS_PATH . FP_CONTENT . $galleryRelativeDir . '/03-gallery.png', $galleryImageBinary !== false ? $galleryImageBinary : 'gallery-03');
	simulate_write_fixture_file(ABS_PATH . FP_CONTENT . $galleryRelativeDir . '/04-gallery.png', $galleryImageBinary !== false ? $galleryImageBinary : 'gallery-04');
	simulate_write_fixture_file(ABS_PATH . FP_CONTENT . $galleryRelativeDir . '/05-gallery.png', $galleryImageBinary !== false ? $galleryImageBinary : 'gallery-05');
	simulate_write_fixture_file(ABS_PATH . FP_CONTENT . 'attachs/mastodon-sim/demo-audio.mp3', 'simulated-mp3-data');
	simulate_write_fixture_file(ABS_PATH . FP_CONTENT . 'attachs/mastodon-sim/demo-video.mp4', 'simulated-mp4-data');
	simulate_write_fixture_file(ABS_PATH . FP_CONTENT . 'images/mastodon-sim/video-poster.png', $galleryImageBinary !== false ? $galleryImageBinary : 'poster');

	$welcomeEntryId = 'entry260410-161431';
	$welcomeEntryPath = entry_dir($welcomeEntryId) . EXT;
	simulate_write_serialized_kv_file($welcomeEntryPath, array(
		'VERSION' => 'fp-1.5 RC2',
		'SUBJECT' => 'Willkommen bei FlatPress!',
		'CONTENT' => "Das ist ein Beispiel-Beitrag. Er zeigt dir einige Funktionen von FlatPress [url=https://www.flatpress.org]flatpress.org[/url].\n\nDas \"more\"-Element erlaubt es dir, vom Anriss des Beitrags zum kompletten Artikel zu springen.\n\nTextformatierung\n\nIn FlatPress formatierst du deine Inhalte mit BBCode [url=https://wiki.flatpress.org/doc:plugins:bbcode]wiki.flatpress.org/doc:plugins:bbcode[/url] (Bulletin-Board-Code).",
		'AUTHOR' => 'Frank Hochmuth',
		'DATE' => mktime(23, 14, 16, 3, 5, 2026)
	));

	$welcomeCommentId = 'comment260412-001722';
	$welcomeCommentPath = substr($welcomeEntryPath, 0, -strlen(EXT)) . '/comments/' . $welcomeCommentId . EXT;
	simulate_write_serialized_kv_file($welcomeCommentPath, array(
		'NAME' => 'Frank',
		'EMAIL' => 'frank@example.invalid',
		'URL' => '',
		'CONTENT' => "Eine URL: [url=https://wiki.flatpress.org/doc:faq]FAQ[/url]\n\nEin Emoticon: :smile:",
		'DATE' => mktime(20, 42, 8, 3, 13, 2026)
	));

	$imageEntryId = 'entry260314-133022';
	$imageEntryPath = entry_dir($imageEntryId) . EXT;
	simulate_write_serialized_kv_file($imageEntryPath, array(
		'VERSION' => 'fp-1.5 RC2',
		'SUBJECT' => 'Beitrag mit einem einzelnen Bild',
		'CONTENT' => "Lorem ipsum dolor sit amet, consetetur sadipscing elitr.\n\n[img=" . 'images/mastodon-sim/single-image.jpg' . " width=180 title=\"Single image fixture\"]\n\nLorem ipsum dolor sit amet, consetetur sadipscing elitr.",
		'AUTHOR' => 'Frank Hochmuth',
		'DATE' => mktime(13, 30, 22, 3, 14, 2026)
	));

	$galleryEntryId = 'entry260314-150101';
	$galleryEntryPath = entry_dir($galleryEntryId) . EXT;
	simulate_write_serialized_kv_file($galleryEntryPath, array(
		'VERSION' => 'fp-1.5 RC2',
		'SUBJECT' => 'Beitrag mit einer Galerie',
		'CONTENT' => "Lorem ipsum dolor sit amet, consetetur sadipscing elitr.\n\n[gallery=" . $galleryRelativeDir . " width=180]\n\nLorem ipsum dolor sit amet, consetetur sadipscing elitr.",
		'AUTHOR' => 'Frank Hochmuth',
		'DATE' => mktime(15, 1, 1, 3, 14, 2026)
	));
}

$sourceRoot = __DIR__;
$sandboxRoot = simulate_prepare_sandbox($sourceRoot);
register_shutdown_function(function () use ($sandboxRoot) {
	simulate_delete_recursive($sandboxRoot);
});

$simRoot = $sandboxRoot;
chdir($simRoot);
if (!headers_sent()) {
	ob_start();
}

$_SERVER ['HTTP_HOST'] = $_SERVER ['HTTP_HOST'] ?? 'localhost';
$_SERVER ['SCRIPT_NAME'] = $_SERVER ['SCRIPT_NAME'] ?? '/index.php';
$_SERVER ['REQUEST_METHOD'] = $_SERVER ['REQUEST_METHOD'] ?? 'GET';
$_SERVER ['REQUEST_URI'] = $_SERVER ['REQUEST_URI'] ?? '/';
$_SERVER ['HTTP_ACCEPT'] = $_SERVER ['HTTP_ACCEPT'] ?? 'text/html';

require_once $simRoot . '/defaults.php';
require_once $simRoot . '/' . INCLUDES_DIR . 'includes.php';

if (function_exists('system_init')) {
	system_init();
}

require_once $simRoot . '/fp-plugins/mastodon/plugin.mastodon.php';
$simulateDefaultRateLimitWindowBudgets = array(
	'media_uploads' => 1000,
	'media_uploads_ttl' => 1800,
	'deletes' => 1000,
	'deletes_ttl' => 1800,
	'status_pages' => 1000,
	'status_pages_ttl' => 900
);
$GLOBALS ['plugin_mastodon_test_rate_limit_window_budgets'] = $simulateDefaultRateLimitWindowBudgets;
plugin_mastodon_rate_limit_window_clear();
if (file_exists($simRoot . '/fp-plugins/audiovideo/audiovideoplugin.class.php')) {
	require_once $simRoot . '/fp-plugins/audiovideo/audiovideoplugin.class.php';
}
if (file_exists($simRoot . '/fp-plugins/tag/plugin.tag.php')) {
	require_once $simRoot . '/fp-plugins/tag/plugin.tag.php';
}

/**
 * Reproduce a Mastodon self-reply to an exported FlatPress comment and verify that it is imported only as a FlatPress comment.
 * @param bool $force
 * @return array<string, mixed>
 */
function simulate_run_exported_comment_self_reply_import_case($force, $quoteImportedReplyParent = null) {
	$force = (bool) $force;
	$quoteImportedReplyParent = $quoteImportedReplyParent === null ? null : (bool) $quoteImportedReplyParent;
	$configuredOptions = plugin_mastodon_get_options();
	$options = simulate_seed_options_from_config($configuredOptions);
	$options ['sync_start_date'] = '';
	$options ['update_local_from_remote'] = '0';
	$options ['import_synced_comments_as_entries'] = '0';
	$options ['old_thread_reply_check'] = '1';
	if ($quoteImportedReplyParent !== null) {
		$options ['quote_imported_reply_parent'] = $quoteImportedReplyParent ? '1' : '0';
	}
	$options ['delete_sync_enabled'] = '0';
	$options ['access_token'] = 'token123';
	plugin_mastodon_save_options($options);
	plugin_mastodon_runtime_cache_clear();

	simulate_delete_recursive(ABS_PATH . FP_CONTENT . 'content');
	@mkdir(ABS_PATH . FP_CONTENT . 'content', 0777, true);
	simulate_delete_recursive(ABS_PATH . FP_CONTENT . 'images/mastodon');
	plugin_mastodon_state_write(plugin_mastodon_default_state());

	$instanceUrl = plugin_mastodon_normalize_instance_url($options ['instance_url']);
	$entry = array(
		'version' => system_ver(),
		'subject' => 'Imported Mastodon root for comment self-reply import',
		'content' => 'This entry represents a remote Mastodon top-level status that already exists as a FlatPress entry.',
		'author' => 'Simulation',
		'date' => strtotime('2027-01-10 10:00:00 UTC')
	);
	$entryId = entry_save($entry, null);
	$entry = entry_parse($entryId);
	$comment = array(
		'version' => system_ver(),
		'name' => 'FlatPress Local',
		'content' => 'Previously exported FlatPress comment',
		'date' => strtotime('2027-01-10 10:05:00 UTC')
	);
	$commentId = comment_save($entryId, $comment);
	$comment = comment_parse($entryId, $commentId);
	$comment ['id'] = (string) $commentId;

	$state = plugin_mastodon_default_state();
	$remoteRootStatus = array(
		'id' => '900',
		'created_at' => '2027-01-10T10:00:00Z'
	);
	$remoteExportedComment = array(
		'id' => '901',
		'created_at' => '2027-01-10T10:05:00Z'
	);
	plugin_mastodon_state_set_entry_mapping($state, $entryId, '900', 'remote', plugin_mastodon_entry_hash($entry), $instanceUrl . '/@flatpress/900', plugin_mastodon_parse_iso_datetime('2027-01-10T10:00:00Z'), plugin_mastodon_local_item_date_key($entry, $entryId), plugin_mastodon_remote_status_date_key($remoteRootStatus));
	plugin_mastodon_state_set_comment_mapping($state, $entryId, $commentId, '901', 'local', plugin_mastodon_comment_hash($comment), $instanceUrl . '/@flatpress/901', plugin_mastodon_parse_iso_datetime('2027-01-10T10:05:00Z'), '', '900', plugin_mastodon_local_item_date_key($comment, $commentId), plugin_mastodon_remote_status_date_key($remoteExportedComment));
	plugin_mastodon_state_write($state);

	$remoteReplyStatus = array(
		'id' => '902',
		'visibility' => 'public',
		'in_reply_to_id' => '901',
		'in_reply_to_account_id' => 'acct1',
		'created_at' => '2027-01-10T10:06:00Z',
		'content' => '<p>Remote self reply to exported FlatPress comment</p>',
		'url' => $instanceUrl . '/@flatpress/902',
		'account' => array(
			'id' => 'acct1',
			'acct' => 'flatpress',
			'display_name' => 'FlatPress Bot',
			'url' => $instanceUrl . '/@flatpress'
		)
	);
	$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
	$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
		'GET ' . $instanceUrl . '/api/v1/accounts/verify_credentials' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('id' => 'acct1', 'username' => 'flatpress'))
		),
		'GET ' . $instanceUrl . '/api/v1/accounts/acct1/statuses?limit=40&exclude_reblogs=true&exclude_replies=true' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array($remoteReplyStatus))
		),
		'GET ' . $instanceUrl . '/api/v1/statuses/900/context' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('descendants' => array($remoteReplyStatus)))
		),
		'GET ' . $instanceUrl . '/api/v2/instance' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array(
				'version' => '4.3.0-sim',
				'api_versions' => array('mastodon' => 6),
				'configuration' => array(
					'statuses' => array(
						'max_characters' => 500,
						'characters_reserved_per_url' => 23
					),
					'media_attachments' => array(
						'image_size_limit' => 1048576,
						'image_matrix_limit' => 16777216,
						'video_size_limit' => 41943040,
						'video_frame_rate_limit' => 60,
						'video_matrix_limit' => 2304000,
						'max_description_length' => 1500,
						'max_remote_url_size' => 2048,
						'supported_mime_types' => array('image/jpeg'),
						'max_media_attachments' => 4
					)
				)
			))
		)
	);

	$result = plugin_mastodon_run_sync($force);
	$state = plugin_mastodon_state_read();
	$importedCommentRef = isset($state ['comments_remote'] ['902']) && is_array($state ['comments_remote'] ['902']) ? $state ['comments_remote'] ['902'] : array();
	$importedComment = !empty($importedCommentRef ['comment_id']) ? comment_parse($importedCommentRef ['entry_id'], $importedCommentRef ['comment_id']) : array();
	$importedEntryId = isset($state ['entries_remote'] ['902']) ? (string) $state ['entries_remote'] ['902'] : '';
	$importedEntry = $importedEntryId !== '' ? entry_parse($importedEntryId) : array();

	return array(
		'result' => $result,
		'state' => $state,
		'entry_id' => $entryId,
		'local_comment_id' => $commentId,
		'imported_comment_ref' => $importedCommentRef,
		'imported_comment' => $importedComment,
		'imported_entry_id' => $importedEntryId,
		'imported_entry' => $importedEntry,
		'http_requests' => simulate_recorded_http_requests()
	);
}

function simulate_run_exported_comment_external_reply_import_case($force, $importSyncedCommentsAsEntries, $quoteImportedReplyParent = null) {
	$force = (bool) $force;
	$importSyncedCommentsAsEntries = (bool) $importSyncedCommentsAsEntries;
	$quoteImportedReplyParent = $quoteImportedReplyParent === null ? null : (bool) $quoteImportedReplyParent;
	$configuredOptions = plugin_mastodon_get_options();
	$options = simulate_seed_options_from_config($configuredOptions);
	$options ['sync_start_date'] = '';
	$options ['update_local_from_remote'] = '0';
	$options ['import_synced_comments_as_entries'] = $importSyncedCommentsAsEntries ? '1' : '0';
	$options ['old_thread_reply_check'] = '1';
	if ($quoteImportedReplyParent !== null) {
		$options ['quote_imported_reply_parent'] = $quoteImportedReplyParent ? '1' : '0';
	}
	$options ['delete_sync_enabled'] = '0';
	$options ['access_token'] = 'token123';
	plugin_mastodon_save_options($options);
	plugin_mastodon_runtime_cache_clear();

	simulate_delete_recursive(ABS_PATH . FP_CONTENT . 'content');
	@mkdir(ABS_PATH . FP_CONTENT . 'content', 0777, true);
	simulate_delete_recursive(ABS_PATH . FP_CONTENT . 'images/mastodon');
	plugin_mastodon_state_write(plugin_mastodon_default_state());

	$instanceUrl = plugin_mastodon_normalize_instance_url($options ['instance_url']);
	$entry = array(
		'version' => system_ver(),
		'subject' => 'Imported Mastodon root for external comment reply import',
		'content' => 'This entry represents a remote Mastodon top-level status that already exists as a FlatPress entry.',
		'author' => 'Simulation',
		'date' => strtotime('2027-02-11 09:00:00 UTC')
	);
	$entryId = entry_save($entry, null);
	$entry = entry_parse($entryId);
	$comment = array(
		'version' => system_ver(),
		'name' => 'FlatPress Local',
		'content' => 'Previously exported FlatPress comment for external reply import',
		'date' => strtotime('2027-02-11 09:05:00 UTC')
	);
	$commentId = comment_save($entryId, $comment);
	$comment = comment_parse($entryId, $commentId);
	$comment ['id'] = (string) $commentId;

	$state = plugin_mastodon_default_state();
	$remoteRootStatus = array(
		'id' => '910',
		'created_at' => '2027-02-11T09:00:00Z'
	);
	$remoteExportedComment = array(
		'id' => '911',
		'created_at' => '2027-02-11T09:05:00Z'
	);
	plugin_mastodon_state_set_entry_mapping($state, $entryId, '910', 'remote', plugin_mastodon_entry_hash($entry), $instanceUrl . '/@flatpress/910', plugin_mastodon_parse_iso_datetime('2027-02-11T09:00:00Z'), plugin_mastodon_local_item_date_key($entry, $entryId), plugin_mastodon_remote_status_date_key($remoteRootStatus));
	plugin_mastodon_state_set_comment_mapping($state, $entryId, $commentId, '911', 'local', plugin_mastodon_comment_hash($comment), $instanceUrl . '/@flatpress/911', plugin_mastodon_parse_iso_datetime('2027-02-11T09:05:00Z'), '', '910', plugin_mastodon_local_item_date_key($comment, $commentId), plugin_mastodon_remote_status_date_key($remoteExportedComment));
	plugin_mastodon_state_write($state);

	$remoteReplyStatus = array(
		'id' => '912',
		'visibility' => 'public',
		'in_reply_to_id' => '911',
		'in_reply_to_account_id' => 'acct1',
		'created_at' => '2027-02-11T09:06:00Z',
		'content' => '<p>Remote reply from another Mastodon account to the exported FlatPress comment</p>',
		'url' => $instanceUrl . '/@alice/912',
		'account' => array(
			'id' => 'acct9',
			'acct' => 'alice@example.net',
			'display_name' => 'Alice Example',
			'url' => 'https://example.net/@alice'
		)
	);

	$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
	$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
		'GET ' . $instanceUrl . '/api/v1/accounts/verify_credentials' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('id' => 'acct1', 'username' => 'flatpress'))
		),
		'GET ' . $instanceUrl . '/api/v1/accounts/acct1/statuses?limit=40&exclude_reblogs=true&exclude_replies=true' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array())
		),
		'GET ' . $instanceUrl . '/api/v1/statuses/910/context' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('descendants' => array($remoteReplyStatus)))
		),
		'GET ' . $instanceUrl . '/api/v2/instance' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array(
				'version' => '4.3.0-sim',
				'api_versions' => array('mastodon' => 6),
				'configuration' => array(
					'statuses' => array(
						'max_characters' => 500,
						'characters_reserved_per_url' => 23
					),
					'media_attachments' => array(
						'image_size_limit' => 1048576,
						'image_matrix_limit' => 16777216,
						'video_size_limit' => 41943040,
						'video_frame_rate_limit' => 60,
						'video_matrix_limit' => 2304000,
						'max_description_length' => 1500,
						'max_remote_url_size' => 2048,
						'supported_mime_types' => array('image/jpeg'),
						'max_media_attachments' => 4
					)
				)
			))
		)
	);

	$result = plugin_mastodon_run_sync($force);
	$state = plugin_mastodon_state_read();
	$importedCommentRef = isset($state ['comments_remote'] ['912']) && is_array($state ['comments_remote'] ['912']) ? $state ['comments_remote'] ['912'] : array();
	$importedComment = !empty($importedCommentRef ['comment_id']) ? comment_parse($importedCommentRef ['entry_id'], $importedCommentRef ['comment_id']) : array();
	$importedEntryId = isset($state ['entries_remote'] ['912']) ? (string) $state ['entries_remote'] ['912'] : '';
	$importedEntry = $importedEntryId !== '' ? entry_parse($importedEntryId) : array();

	return array(
		'result' => $result,
		'state' => $state,
		'entry_id' => $entryId,
		'local_comment_id' => $commentId,
		'imported_comment_ref' => $importedCommentRef,
		'imported_comment' => $importedComment,
		'imported_entry_id' => $importedEntryId,
		'imported_entry' => $importedEntry,
		'http_requests' => simulate_recorded_http_requests()
	);
}

/**
 * Reproduce a Mastodon reply-to-reply import and verify optional quote insertion.
 * @param bool $force
 * @param bool|null $quoteImportedReplyParent
 * @return array<string, mixed>
 */
function simulate_run_remote_reply_to_reply_quote_case($force, $quoteImportedReplyParent = null) {
	$force = (bool) $force;
	$quoteImportedReplyParent = $quoteImportedReplyParent === null ? null : (bool) $quoteImportedReplyParent;
	$configuredOptions = plugin_mastodon_get_options();
	$options = simulate_seed_options_from_config($configuredOptions);
	$options ['sync_start_date'] = '';
	$options ['update_local_from_remote'] = '0';
	$options ['import_synced_comments_as_entries'] = '0';
	$options ['old_thread_reply_check'] = '1';
	if ($quoteImportedReplyParent !== null) {
		$options ['quote_imported_reply_parent'] = $quoteImportedReplyParent ? '1' : '0';
	}
	$options ['delete_sync_enabled'] = '0';
	$options ['access_token'] = 'token123';
	plugin_mastodon_save_options($options);
	plugin_mastodon_runtime_cache_clear();

	simulate_delete_recursive(ABS_PATH . FP_CONTENT . 'content');
	@mkdir(ABS_PATH . FP_CONTENT . 'content', 0777, true);
	simulate_delete_recursive(ABS_PATH . FP_CONTENT . 'images/mastodon');
	plugin_mastodon_state_write(plugin_mastodon_default_state());

	$instanceUrl = plugin_mastodon_normalize_instance_url($options ['instance_url']);
	$entry = array(
		'version' => system_ver(),
		'subject' => 'Imported Mastodon root for reply quote import',
		'content' => 'Root entry for nested Mastodon reply import quote test.',
		'author' => 'Simulation',
		'date' => strtotime('2027-03-15 12:00:00 UTC')
	);
	$entryId = entry_save($entry, null);
	$entry = entry_parse($entryId);

	$state = plugin_mastodon_default_state();
	$remoteRootStatus = array(
		'id' => '940',
		'created_at' => '2027-03-15T12:00:00Z'
	);
	plugin_mastodon_state_set_entry_mapping($state, $entryId, '940', 'remote', plugin_mastodon_entry_hash($entry), $instanceUrl . '/@flatpress/940', plugin_mastodon_parse_iso_datetime('2027-03-15T12:00:00Z'), plugin_mastodon_local_item_date_key($entry, $entryId), plugin_mastodon_remote_status_date_key($remoteRootStatus));
	plugin_mastodon_state_write($state);

	$remoteParentReply = array(
		'id' => '941',
		'visibility' => 'public',
		'in_reply_to_id' => '940',
		'in_reply_to_account_id' => 'acct1',
		'created_at' => '2027-03-15T12:05:00Z',
		'content' => '<p>Parent Mastodon reply from Alice</p>',
		'url' => $instanceUrl . '/@alice/941',
		'account' => array(
			'id' => 'acct9',
			'acct' => 'alice@example.net',
			'display_name' => 'Alice Example',
			'url' => 'https://example.net/@alice'
		)
	);
	$remoteChildReply = array(
		'id' => '942',
		'visibility' => 'public',
		'in_reply_to_id' => '941',
		'in_reply_to_account_id' => 'acct9',
		'created_at' => '2027-03-15T12:06:00Z',
		'content' => '<p>Child Mastodon reply from Bob</p>',
		'url' => $instanceUrl . '/@bob/942',
		'account' => array(
			'id' => 'acct10',
			'acct' => 'bob@example.org',
			'display_name' => 'Bob Example',
			'url' => 'https://example.org/@bob'
		)
	);

	$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
	$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
		'GET ' . $instanceUrl . '/api/v1/accounts/verify_credentials' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('id' => 'acct1', 'username' => 'flatpress'))
		),
		'GET ' . $instanceUrl . '/api/v1/accounts/acct1/statuses?limit=40&exclude_reblogs=true&exclude_replies=true' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array())
		),
		'GET ' . $instanceUrl . '/api/v1/statuses/940/context' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('descendants' => array($remoteParentReply, $remoteChildReply)))
		),
		'GET ' . $instanceUrl . '/api/v2/instance' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array(
				'version' => '4.3.0-sim',
				'api_versions' => array('mastodon' => 6),
				'configuration' => array(
					'statuses' => array(
						'max_characters' => 500,
						'characters_reserved_per_url' => 23
					),
					'media_attachments' => array(
						'image_size_limit' => 1048576,
						'image_matrix_limit' => 16777216,
						'video_size_limit' => 41943040,
						'video_frame_rate_limit' => 60,
						'video_matrix_limit' => 2304000,
						'max_description_length' => 1500,
						'max_remote_url_size' => 2048,
						'supported_mime_types' => array('image/jpeg'),
						'max_media_attachments' => 4
					)
				)
			))
		)
	);

	$result = plugin_mastodon_run_sync($force);
	$state = plugin_mastodon_state_read();
	$parentCommentRef = isset($state ['comments_remote'] ['941']) && is_array($state ['comments_remote'] ['941']) ? $state ['comments_remote'] ['941'] : array();
	$parentComment = !empty($parentCommentRef ['comment_id']) ? comment_parse($parentCommentRef ['entry_id'], $parentCommentRef ['comment_id']) : array();
	$childCommentRef = isset($state ['comments_remote'] ['942']) && is_array($state ['comments_remote'] ['942']) ? $state ['comments_remote'] ['942'] : array();
	$childComment = !empty($childCommentRef ['comment_id']) ? comment_parse($childCommentRef ['entry_id'], $childCommentRef ['comment_id']) : array();

	return array(
		'result' => $result,
		'state' => $state,
		'entry_id' => $entryId,
		'parent_comment_ref' => $parentCommentRef,
		'parent_comment' => $parentComment,
		'child_comment_ref' => $childCommentRef,
		'child_comment' => $childComment,
		'http_requests' => simulate_recorded_http_requests()
	);
}

/**
 * Reproduce local deletion of an imported external Mastodon reply before the next content sync sees it again.
 * @param bool $editRemoteBeforeNextSync
 * @return array<string, mixed>
 */
function simulate_run_deleted_imported_remote_comment_tombstone_case($editRemoteBeforeNextSync) {
	$editRemoteBeforeNextSync = (bool) $editRemoteBeforeNextSync;
	$configuredOptions = plugin_mastodon_get_options();
	$options = simulate_seed_options_from_config($configuredOptions);
	$options ['sync_start_date'] = '';
	$options ['sync_scheduled_window_days'] = '14';
	$options ['update_local_from_remote'] = '1';
	$options ['import_synced_comments_as_entries'] = '0';
	$options ['quote_imported_reply_parent'] = '1';
	$options ['old_thread_reply_check'] = '1';
	$options ['delete_sync_enabled'] = '1';
	$options ['access_token'] = 'token123';
	plugin_mastodon_save_options($options);
	plugin_mastodon_runtime_cache_clear();

	simulate_delete_recursive(ABS_PATH . FP_CONTENT . 'content');
	@mkdir(ABS_PATH . FP_CONTENT . 'content', 0777, true);
	simulate_delete_recursive(ABS_PATH . FP_CONTENT . 'images/mastodon');
	plugin_mastodon_state_write(plugin_mastodon_default_state());

	$instanceUrl = plugin_mastodon_normalize_instance_url($options ['instance_url']);
	$instanceResponse = array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			'version' => '4.5.0-sim',
			'api_versions' => array('mastodon' => 7),
			'configuration' => array(
				'statuses' => array(
					'max_characters' => 500,
					'characters_reserved_per_url' => 23
				),
				'media_attachments' => array(
					'image_size_limit' => 1048576,
					'image_matrix_limit' => 16777216,
					'video_size_limit' => 41943040,
					'video_frame_rate_limit' => 60,
					'video_matrix_limit' => 2304000,
					'max_description_length' => 1500,
					'max_remote_url_size' => 2048,
					'supported_mime_types' => array('image/jpeg'),
					'max_media_attachments' => 4
				)
			)
		))
	);
	$entry = array(
		'version' => system_ver(),
		'subject' => 'Imported reply local delete root',
		'content' => 'Root entry for imported reply local deletion testing.',
		'author' => 'Simulation',
		'date' => strtotime('2027-05-01 08:00:00 UTC')
	);
	$entryId = entry_save($entry, null);
	$entry = entry_parse($entryId);

	$localCommentOne = array(
		'version' => system_ver(),
		'name' => 'FlatPress Local One',
		'content' => 'First exported FlatPress comment.',
		'date' => strtotime('2027-05-02 08:00:00 UTC')
	);
	$localCommentOneId = comment_save($entryId, $localCommentOne);
	$localCommentOne = comment_parse($entryId, $localCommentOneId);
	$localCommentOne ['id'] = (string) $localCommentOneId;

	$localCommentTwo = array(
		'version' => system_ver(),
		'name' => 'FlatPress Local Two',
		'content' => 'Second exported FlatPress comment.',
		'date' => strtotime('2027-05-02 08:05:00 UTC')
	);
	$localCommentTwoId = comment_save($entryId, $localCommentTwo);
	$localCommentTwo = comment_parse($entryId, $localCommentTwoId);
	$localCommentTwo ['id'] = (string) $localCommentTwoId;

	$remoteRootStatus = array(
		'id' => '9900',
		'visibility' => 'public',
		'created_at' => '2027-05-01T08:00:00Z',
		'content' => '<p>Root entry for imported reply local deletion testing.</p>',
		'url' => $instanceUrl . '/@flatpress/9900',
		'account' => array(
			'id' => 'acct1',
			'acct' => 'flatpress',
			'display_name' => 'FlatPress Bot',
			'url' => $instanceUrl . '/@flatpress'
		)
	);
	$remoteCommentOne = array(
		'id' => '9901',
		'visibility' => 'public',
		'in_reply_to_id' => '9900',
		'in_reply_to_account_id' => 'acct1',
		'created_at' => '2027-05-02T08:00:00Z',
		'content' => '<p>First exported FlatPress comment.</p>',
		'url' => $instanceUrl . '/@flatpress/9901',
		'account' => array(
			'id' => 'acct1',
			'acct' => 'flatpress',
			'display_name' => 'FlatPress Bot',
			'url' => $instanceUrl . '/@flatpress'
		)
	);
	$remoteCommentTwo = array(
		'id' => '9902',
		'visibility' => 'public',
		'in_reply_to_id' => '9900',
		'in_reply_to_account_id' => 'acct1',
		'created_at' => '2027-05-02T08:05:00Z',
		'content' => '<p>Second exported FlatPress comment.</p>',
		'url' => $instanceUrl . '/@flatpress/9902',
		'account' => array(
			'id' => 'acct1',
			'acct' => 'flatpress',
			'display_name' => 'FlatPress Bot',
			'url' => $instanceUrl . '/@flatpress'
		)
	);
	$remoteExternalReply = array(
		'id' => '9903',
		'visibility' => 'public',
		'in_reply_to_id' => '9901',
		'in_reply_to_account_id' => 'acct1',
		'created_at' => '2027-05-03T08:00:00Z',
		'content' => '<p>External reply to the first FlatPress comment.</p>',
		'url' => 'https://example.net/@alice/9903',
		'account' => array(
			'id' => 'acct9',
			'acct' => 'alice@example.net',
			'display_name' => 'Alice Example',
			'url' => 'https://example.net/@alice'
		)
	);

	$state = plugin_mastodon_default_state();
	plugin_mastodon_state_set_entry_mapping($state, $entryId, '9900', 'local', plugin_mastodon_entry_hash($entry), $instanceUrl . '/@flatpress/9900', plugin_mastodon_parse_iso_datetime('2027-05-01T08:00:00Z'), plugin_mastodon_local_item_date_key($entry, $entryId), plugin_mastodon_remote_status_date_key($remoteRootStatus));
	plugin_mastodon_state_set_comment_mapping($state, $entryId, $localCommentOneId, '9901', 'local', plugin_mastodon_comment_hash($localCommentOne), $instanceUrl . '/@flatpress/9901', plugin_mastodon_parse_iso_datetime('2027-05-02T08:00:00Z'), '', '9900', plugin_mastodon_local_item_date_key($localCommentOne, $localCommentOneId), plugin_mastodon_remote_status_date_key($remoteCommentOne));
	plugin_mastodon_state_set_comment_mapping($state, $entryId, $localCommentTwoId, '9902', 'local', plugin_mastodon_comment_hash($localCommentTwo), $instanceUrl . '/@flatpress/9902', plugin_mastodon_parse_iso_datetime('2027-05-02T08:05:00Z'), '', '9900', plugin_mastodon_local_item_date_key($localCommentTwo, $localCommentTwoId), plugin_mastodon_remote_status_date_key($remoteCommentTwo));
	plugin_mastodon_state_write($state);

	$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
	$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
		'GET ' . $instanceUrl . '/api/v1/accounts/verify_credentials' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('id' => 'acct1', 'username' => 'flatpress'))
		),
		'GET ' . $instanceUrl . '/api/v1/accounts/acct1/statuses?limit=40&exclude_reblogs=true&exclude_replies=true' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array())
		),
		'GET ' . $instanceUrl . '/api/v1/statuses/9900/context' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('descendants' => array($remoteCommentOne, $remoteCommentTwo, $remoteExternalReply)))
		),
		'GET ' . $instanceUrl . '/api/v2/instance' => $instanceResponse
	);
	$initialSyncResult = plugin_mastodon_run_sync(true);
	$stateAfterInitialSync = plugin_mastodon_state_read();
	$importedReplyRef = isset($stateAfterInitialSync ['comments_remote'] ['9903']) && is_array($stateAfterInitialSync ['comments_remote'] ['9903']) ? $stateAfterInitialSync ['comments_remote'] ['9903'] : array();
	$importedReplyCommentId = !empty($importedReplyRef ['comment_id']) ? (string) $importedReplyRef ['comment_id'] : '';
	$importedReplyExistsBeforeDelete = $importedReplyCommentId !== '' && comment_exists($entryId, $importedReplyCommentId);

	if ($importedReplyCommentId !== '') {
		comment_delete($entryId, $importedReplyCommentId);
	}
	$stateAfterLocalDelete = plugin_mastodon_state_read();

	$remoteReplySeenAgain = $remoteExternalReply;
	if ($editRemoteBeforeNextSync) {
		$remoteReplySeenAgain ['content'] = '<p>External reply was edited after the FlatPress admin deleted the imported comment.</p>';
		$remoteReplySeenAgain ['edited_at'] = '2027-05-03T10:00:00Z';
	}

	$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
	$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
		'GET ' . $instanceUrl . '/api/v1/accounts/verify_credentials' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('id' => 'acct1', 'username' => 'flatpress'))
		),
		'GET ' . $instanceUrl . '/api/v1/accounts/acct1/statuses?limit=40&exclude_reblogs=true&exclude_replies=true&since_id=9900' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array())
		),
		'GET ' . $instanceUrl . '/api/v1/statuses/9900/context' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('descendants' => array($remoteCommentOne, $remoteCommentTwo, $remoteReplySeenAgain)))
		),
		'GET ' . $instanceUrl . '/api/v2/instance' => $instanceResponse
	);
	$nextContentSyncResult = plugin_mastodon_run_sync(true);
	$stateAfterNextContentSync = plugin_mastodon_state_read();
	$importedReplyExistsAfterNextSync = $importedReplyCommentId !== '' && comment_exists($entryId, $importedReplyCommentId);
	$reimportedReplyRef = isset($stateAfterNextContentSync ['comments_remote'] ['9903']) && is_array($stateAfterNextContentSync ['comments_remote'] ['9903']) ? $stateAfterNextContentSync ['comments_remote'] ['9903'] : array();

	return array(
		'initial_sync_result' => $initialSyncResult,
		'state_after_initial_sync' => $stateAfterInitialSync,
		'entry_id' => $entryId,
		'local_comment_one_id' => $localCommentOneId,
		'local_comment_two_id' => $localCommentTwoId,
		'imported_reply_ref' => $importedReplyRef,
		'imported_reply_comment_id' => $importedReplyCommentId,
		'imported_reply_exists_before_delete' => $importedReplyExistsBeforeDelete,
		'state_after_local_delete' => $stateAfterLocalDelete,
		'next_content_sync_result' => $nextContentSyncResult,
		'state_after_next_content_sync' => $stateAfterNextContentSync,
		'imported_reply_exists_after_next_sync' => $importedReplyExistsAfterNextSync,
		'reimported_reply_ref' => $reimportedReplyRef,
		'requests_after_next_content_sync' => simulate_recorded_http_requests()
	);
}

/**
 * Reproduce a legacy/pending deletion-sync state for a locally deleted imported remote reply.
 * @return array<string, mixed>
 */
function simulate_run_deleted_imported_remote_comment_deletion_sync_cleanup_case() {
	$configuredOptions = plugin_mastodon_get_options();
	$options = simulate_seed_options_from_config($configuredOptions);
	$options ['sync_start_date'] = '';
	$options ['sync_scheduled_window_days'] = '14';
	$options ['update_local_from_remote'] = '1';
	$options ['delete_sync_enabled'] = '1';
	$options ['access_token'] = 'token123';
	plugin_mastodon_save_options($options);
	plugin_mastodon_runtime_cache_clear();

	simulate_delete_recursive(ABS_PATH . FP_CONTENT . 'content');
	@mkdir(ABS_PATH . FP_CONTENT . 'content', 0777, true);
	simulate_delete_recursive(ABS_PATH . FP_CONTENT . 'images/mastodon');
	plugin_mastodon_state_write(plugin_mastodon_default_state());

	$instanceUrl = plugin_mastodon_normalize_instance_url($options ['instance_url']);
	$entry = array(
		'version' => system_ver(),
		'subject' => 'Imported reply deletion sync cleanup root',
		'content' => 'Root entry for imported reply deletion-sync cleanup testing.',
		'author' => 'Simulation',
		'date' => strtotime('2027-05-04 08:00:00 UTC')
	);
	$entryId = entry_save($entry, null);
	$entry = entry_parse($entryId);
	$importedComment = array(
		'version' => system_ver(),
		'loggedin' => '0',
		'name' => 'Alice Example',
		'url' => 'https://example.net/@alice',
		'content' => 'Imported external reply that was deleted locally before cleanup.',
		'date' => strtotime('2027-05-04 08:05:00 UTC')
	);
	$importedCommentId = comment_save($entryId, $importedComment);
	$importedComment = comment_parse($entryId, $importedCommentId);
	$importedComment ['id'] = (string) $importedCommentId;

	$remoteRootStatus = array(
		'id' => '9910',
		'created_at' => '2027-05-04T08:00:00Z'
	);
	$remoteImportedReply = array(
		'id' => '9911',
		'created_at' => '2027-05-04T08:05:00Z'
	);

	$state = plugin_mastodon_default_state();
	plugin_mastodon_state_set_entry_mapping($state, $entryId, '9910', 'local', plugin_mastodon_entry_hash($entry), $instanceUrl . '/@flatpress/9910', plugin_mastodon_parse_iso_datetime('2027-05-04T08:00:00Z'), plugin_mastodon_local_item_date_key($entry, $entryId), plugin_mastodon_remote_status_date_key($remoteRootStatus));
	plugin_mastodon_state_set_comment_mapping($state, $entryId, $importedCommentId, '9911', 'remote', plugin_mastodon_comment_hash($importedComment), 'https://example.net/@alice/9911', plugin_mastodon_parse_iso_datetime('2027-05-04T08:05:00Z'), '', '9910', plugin_mastodon_local_item_date_key($importedComment, $importedCommentId), plugin_mastodon_remote_status_date_key($remoteImportedReply));
	plugin_mastodon_state_set_deletions_pending($state, true, 'full', '');
	plugin_mastodon_state_write($state);

	$commentFile = comment_exists($entryId, $importedCommentId);
	if ($commentFile) {
		@unlink($commentFile);
	}

	$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
	$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
		'GET ' . $instanceUrl . '/api/v1/statuses/9910' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('id' => '9910'))
		)
	);
	simulate_allow_pending_deletion_sync();
	$deletionSyncResult = plugin_mastodon_run_deletion_sync(true);
	$stateAfterDeletionSync = plugin_mastodon_state_read();
	$requests = simulate_recorded_http_requests();
	$deleteRequests = array();
	foreach ($requests as $request) {
		if (is_array($request) && isset($request ['method']) && strtoupper((string) $request ['method']) === 'DELETE') {
			$deleteRequests [] = $request;
		}
	}

	return array(
		'deletion_sync_result' => $deletionSyncResult,
		'entry_id' => $entryId,
		'imported_comment_id' => $importedCommentId,
		'state_after_deletion_sync' => $stateAfterDeletionSync,
		'requests_after_deletion_sync' => $requests,
		'delete_requests_after_deletion_sync' => $deleteRequests
	);
}

/**
 * Reproduce deletion-sync follow-up handling for imported descendants of a deleted exported FlatPress comment.
 * @param bool $force
 * @param bool $staleChildLookup
 * @return array<string, mixed>
 */
function simulate_run_exported_comment_descendant_deletion_case($force, $staleChildLookup) {
	$force = (bool) $force;
	$staleChildLookup = (bool) $staleChildLookup;
	$configuredOptions = plugin_mastodon_get_options();
	$options = simulate_seed_options_from_config($configuredOptions);
	$options ['sync_start_date'] = '';
	$options ['update_local_from_remote'] = '0';
	$options ['import_synced_comments_as_entries'] = '0';
	$options ['quote_imported_reply_parent'] = '1';
	$options ['old_thread_reply_check'] = '1';
	$options ['delete_sync_enabled'] = '1';
	$options ['access_token'] = 'token123';
	plugin_mastodon_save_options($options);
	plugin_mastodon_runtime_cache_clear();

	simulate_delete_recursive(ABS_PATH . FP_CONTENT . 'content');
	@mkdir(ABS_PATH . FP_CONTENT . 'content', 0777, true);
	simulate_delete_recursive(ABS_PATH . FP_CONTENT . 'images/mastodon');
	plugin_mastodon_state_write(plugin_mastodon_default_state());

	$instanceUrl = plugin_mastodon_normalize_instance_url($options ['instance_url']);
	$entry = array(
		'version' => system_ver(),
		'subject' => 'Deletion follow-up test root',
		'content' => 'Root entry for exported-comment descendant deletion follow-up testing.',
		'author' => 'Simulation',
		'date' => strtotime('2027-04-20 08:00:00 UTC')
	);
	$entryId = entry_save($entry, null);
	$entry = entry_parse($entryId);
	$localComment = array(
		'version' => system_ver(),
		'name' => 'FlatPress Local',
		'content' => 'Exported FlatPress parent comment that will be deleted locally.',
		'date' => strtotime('2027-04-20 08:05:00 UTC')
	);
	$localCommentId = comment_save($entryId, $localComment);
	$localComment = comment_parse($entryId, $localCommentId);
	$localComment ['id'] = (string) $localCommentId;

	$state = plugin_mastodon_default_state();
	$remoteRootStatus = array(
		'id' => '960',
		'created_at' => '2027-04-20T08:00:00Z'
	);
	$remoteParentStatus = array(
		'id' => '961',
		'visibility' => 'public',
		'in_reply_to_id' => '960',
		'in_reply_to_account_id' => 'acct1',
		'created_at' => '2027-04-20T08:05:00Z',
		'content' => '<p>Exported FlatPress parent comment that will be deleted locally.</p>',
		'url' => $instanceUrl . '/@flatpress/961',
		'account' => array(
			'id' => 'acct1',
			'acct' => 'flatpress',
			'display_name' => 'FlatPress Bot',
			'url' => $instanceUrl . '/@flatpress'
		)
	);
	$remoteChildStatus = array(
		'id' => '962',
		'visibility' => 'public',
		'in_reply_to_id' => '961',
		'in_reply_to_account_id' => 'acct1',
		'created_at' => '2027-04-20T08:06:00Z',
		'content' => '<p>Imported descendant reply from another Mastodon account.</p>',
		'url' => 'https://example.net/@alice/962',
		'account' => array(
			'id' => 'acct9',
			'acct' => 'alice@example.net',
			'display_name' => 'Alice Example',
			'url' => 'https://example.net/@alice'
		)
	);
	plugin_mastodon_state_set_entry_mapping($state, $entryId, '960', 'remote', plugin_mastodon_entry_hash($entry), $instanceUrl . '/@flatpress/960', plugin_mastodon_parse_iso_datetime('2027-04-20T08:00:00Z'), plugin_mastodon_local_item_date_key($entry, $entryId), plugin_mastodon_remote_status_date_key($remoteRootStatus));
	plugin_mastodon_state_set_comment_mapping($state, $entryId, $localCommentId, '961', 'local', plugin_mastodon_comment_hash($localComment), $instanceUrl . '/@flatpress/961', plugin_mastodon_parse_iso_datetime('2027-04-20T08:05:00Z'), '', '960', plugin_mastodon_local_item_date_key($localComment, $localCommentId), plugin_mastodon_remote_status_date_key($remoteParentStatus));
	plugin_mastodon_state_write($state);

	$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
	$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
		'GET ' . $instanceUrl . '/api/v1/accounts/verify_credentials' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('id' => 'acct1', 'username' => 'flatpress'))
		),
		'GET ' . $instanceUrl . '/api/v1/accounts/acct1/statuses?limit=40&exclude_reblogs=true&exclude_replies=true' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array())
		),
		'GET ' . $instanceUrl . '/api/v1/statuses/960/context' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('descendants' => array($remoteChildStatus)))
		),
		'GET ' . $instanceUrl . '/api/v2/instance' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array(
				'version' => '4.5.0-sim',
				'api_versions' => array('mastodon' => 7),
				'configuration' => array(
					'statuses' => array(
						'max_characters' => 500,
						'characters_reserved_per_url' => 23
					),
					'media_attachments' => array(
						'image_size_limit' => 1048576,
						'image_matrix_limit' => 16777216,
						'video_size_limit' => 41943040,
						'video_frame_rate_limit' => 60,
						'video_matrix_limit' => 2304000,
						'max_description_length' => 1500,
						'max_remote_url_size' => 2048,
						'supported_mime_types' => array('image/jpeg'),
						'max_media_attachments' => 4
					)
				)
			))
		)
	);
	$initialSyncResult = plugin_mastodon_run_sync($force);
	$stateAfterInitialSync = plugin_mastodon_state_read();
	$childRefAfterInitialSync = isset($stateAfterInitialSync ['comments_remote'] ['962']) && is_array($stateAfterInitialSync ['comments_remote'] ['962']) ? $stateAfterInitialSync ['comments_remote'] ['962'] : array();
	$childCommentId = !empty($childRefAfterInitialSync ['comment_id']) ? (string) $childRefAfterInitialSync ['comment_id'] : '';

	comment_delete($entryId, $localCommentId);

	$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
	$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
		'GET ' . $instanceUrl . '/api/v1/statuses/960' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('id' => '960'))
		),
		'DELETE ' . $instanceUrl . '/api/v1/statuses/961?delete_media=1' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('id' => '961'))
		),
		'GET ' . $instanceUrl . '/api/v1/statuses/962' => $staleChildLookup ? array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('id' => '962'))
		) : array(
			'ok' => false,
			'code' => 404,
			'body' => json_encode(array('error' => 'Record not found'))
		)
	);
	simulate_allow_pending_deletion_sync();
	$deletionPassOne = plugin_mastodon_run_deletion_sync(true);
	$stateAfterDeletionPassOne = plugin_mastodon_state_read();
	$childExistsAfterDeletionPassOne = ($childCommentId !== '') ? (bool) comment_exists($entryId, $childCommentId) : false;

	$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
	$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
		'GET ' . $instanceUrl . '/api/v1/accounts/verify_credentials' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('id' => 'acct1', 'username' => 'flatpress'))
		),
		'GET ' . $instanceUrl . '/api/v1/accounts/acct1/statuses?limit=40&exclude_reblogs=true&exclude_replies=true&since_id=960' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array())
		),
		'GET ' . $instanceUrl . '/api/v1/statuses/960/context' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('descendants' => array($remoteParentStatus, $remoteChildStatus)))
		),
		'GET ' . $instanceUrl . '/api/v2/instance' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array(
				'version' => '4.5.0-sim',
				'api_versions' => array('mastodon' => 7),
				'configuration' => array(
					'statuses' => array(
						'max_characters' => 500,
						'characters_reserved_per_url' => 23
					),
					'media_attachments' => array(
						'image_size_limit' => 1048576,
						'image_matrix_limit' => 16777216,
						'video_size_limit' => 41943040,
						'video_frame_rate_limit' => 60,
						'video_matrix_limit' => 2304000,
						'max_description_length' => 1500,
						'max_remote_url_size' => 2048,
						'supported_mime_types' => array('image/jpeg'),
						'max_media_attachments' => 4
					)
				)
			))
		)
	);
	$staleContentSyncResult = plugin_mastodon_run_sync($force);
	$stateAfterStaleContentSync = plugin_mastodon_state_read();
	$reimportedParentRef = isset($stateAfterStaleContentSync ['comments_remote'] ['961']) && is_array($stateAfterStaleContentSync ['comments_remote'] ['961']) ? $stateAfterStaleContentSync ['comments_remote'] ['961'] : array();

	$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
	$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
		'GET ' . $instanceUrl . '/api/v1/statuses/960' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('id' => '960'))
		),
		'GET ' . $instanceUrl . '/api/v1/statuses/962' => array(
			'ok' => false,
			'code' => 404,
			'body' => json_encode(array('error' => 'Record not found'))
		)
	);
	simulate_allow_pending_deletion_sync();
	$deletionPassTwo = plugin_mastodon_run_deletion_sync(true);
	$stateAfterDeletionPassTwo = plugin_mastodon_state_read();
	$childExistsAfterDeletionPassTwo = ($childCommentId !== '') ? (bool) comment_exists($entryId, $childCommentId) : false;

	return array(
		'initial_sync_result' => $initialSyncResult,
		'initial_child_ref' => $childRefAfterInitialSync,
		'entry_id' => $entryId,
		'local_comment_id' => $localCommentId,
		'child_comment_id' => $childCommentId,
		'deletion_pass_one' => $deletionPassOne,
		'state_after_deletion_pass_one' => $stateAfterDeletionPassOne,
		'child_exists_after_deletion_pass_one' => $childExistsAfterDeletionPassOne,
		'stale_content_sync_result' => $staleContentSyncResult,
		'state_after_stale_content_sync' => $stateAfterStaleContentSync,
		'reimported_parent_ref' => $reimportedParentRef,
		'deletion_pass_two' => $deletionPassTwo,
		'state_after_deletion_pass_two' => $stateAfterDeletionPassTwo,
		'child_exists_after_deletion_pass_two' => $childExistsAfterDeletionPassTwo
	);
}

/**
 * Reproduce the exact content-sync-before-deletion-sync sequence for one deleted exported FlatPress comment with a remote descendant reply.
 * @param bool $force
 * @param bool $staleChildLookup
 * @return array<string, mixed>
 */
function simulate_run_exported_comment_descendant_content_before_deletion_case($force, $staleChildLookup) {
	$force = (bool) $force;
	$staleChildLookup = (bool) $staleChildLookup;
	$configuredOptions = plugin_mastodon_get_options();
	$options = simulate_seed_options_from_config($configuredOptions);
	$options ['sync_start_date'] = '';
	$options ['update_local_from_remote'] = '0';
	$options ['import_synced_comments_as_entries'] = '0';
	$options ['quote_imported_reply_parent'] = '1';
	$options ['old_thread_reply_check'] = '1';
	$options ['delete_sync_enabled'] = '1';
	$options ['access_token'] = 'token123';
	plugin_mastodon_save_options($options);
	plugin_mastodon_runtime_cache_clear();

	simulate_delete_recursive(ABS_PATH . FP_CONTENT . 'content');
	@mkdir(ABS_PATH . FP_CONTENT . 'content', 0777, true);
	simulate_delete_recursive(ABS_PATH . FP_CONTENT . 'images/mastodon');
	plugin_mastodon_state_write(plugin_mastodon_default_state());

	$instanceUrl = plugin_mastodon_normalize_instance_url($options ['instance_url']);
	$entry = array(
		'version' => system_ver(),
		'subject' => 'Content-before-deletion follow-up test root',
		'content' => 'Root entry for exported-comment descendant content-before-deletion testing.',
		'author' => 'Simulation',
		'date' => strtotime('2027-04-22 08:00:00 UTC')
	);
	$entryId = entry_save($entry, null);
	$entry = entry_parse($entryId);
	$localComment = array(
		'version' => system_ver(),
		'name' => 'FlatPress Local',
		'content' => 'Exported FlatPress parent comment that will be deleted locally before deletion sync.',
		'date' => strtotime('2027-04-22 08:05:00 UTC')
	);
	$localCommentId = comment_save($entryId, $localComment);
	$localComment = comment_parse($entryId, $localCommentId);
	$localComment ['id'] = (string) $localCommentId;

	$state = plugin_mastodon_default_state();
	$remoteRootStatus = array(
		'id' => '9810',
		'created_at' => '2027-04-22T08:00:00Z'
	);
	$remoteParentStatus = array(
		'id' => '9811',
		'visibility' => 'public',
		'in_reply_to_id' => '9810',
		'in_reply_to_account_id' => 'acct1',
		'created_at' => '2027-04-22T08:05:00Z',
		'content' => '<p>Exported FlatPress parent comment that will be deleted locally before deletion sync.</p>',
		'url' => $instanceUrl . '/@flatpress/9811',
		'account' => array(
			'id' => 'acct1',
			'acct' => 'flatpress',
			'display_name' => 'FlatPress Bot',
			'url' => $instanceUrl . '/@flatpress'
		)
	);
	$remoteChildStatus = array(
		'id' => '9812',
		'visibility' => 'public',
		'in_reply_to_id' => '9811',
		'in_reply_to_account_id' => 'acct1',
		'created_at' => '2027-04-22T08:06:00Z',
		'content' => '<p>Imported descendant reply that should stay pending after the parent delete.</p>',
		'url' => 'https://example.net/@alice/9812',
		'account' => array(
			'id' => 'acct9',
			'acct' => 'alice@example.net',
			'display_name' => 'Alice Example',
			'url' => 'https://example.net/@alice'
		)
	);
	plugin_mastodon_state_set_entry_mapping($state, $entryId, '9810', 'remote', plugin_mastodon_entry_hash($entry), $instanceUrl . '/@flatpress/9810', plugin_mastodon_parse_iso_datetime('2027-04-22T08:00:00Z'), plugin_mastodon_local_item_date_key($entry, $entryId), plugin_mastodon_remote_status_date_key($remoteRootStatus));
	plugin_mastodon_state_set_comment_mapping($state, $entryId, $localCommentId, '9811', 'local', plugin_mastodon_comment_hash($localComment), $instanceUrl . '/@flatpress/9811', plugin_mastodon_parse_iso_datetime('2027-04-22T08:05:00Z'), '', '9810', plugin_mastodon_local_item_date_key($localComment, $localCommentId), plugin_mastodon_remote_status_date_key($remoteParentStatus));
	plugin_mastodon_state_write($state);

	$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
	$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
		'GET ' . $instanceUrl . '/api/v1/accounts/verify_credentials' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('id' => 'acct1', 'username' => 'flatpress'))
		),
		'GET ' . $instanceUrl . '/api/v1/accounts/acct1/statuses?limit=40&exclude_reblogs=true&exclude_replies=true' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array())
		),
		'GET ' . $instanceUrl . '/api/v1/statuses/9810/context' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('descendants' => array($remoteChildStatus)))
		),
		'GET ' . $instanceUrl . '/api/v2/instance' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array(
				'version' => '4.5.0-sim',
				'api_versions' => array('mastodon' => 7),
				'configuration' => array(
					'statuses' => array(
						'max_characters' => 500,
						'characters_reserved_per_url' => 23
					),
					'media_attachments' => array(
						'image_size_limit' => 1048576,
						'image_matrix_limit' => 16777216,
						'video_size_limit' => 41943040,
						'video_frame_rate_limit' => 60,
						'video_matrix_limit' => 2304000,
						'max_description_length' => 1500,
						'max_remote_url_size' => 2048,
						'supported_mime_types' => array('image/jpeg'),
						'max_media_attachments' => 4
					)
				)
			))
		)
	);
	$initialSyncResult = plugin_mastodon_run_sync($force);
	$stateAfterInitialSync = plugin_mastodon_state_read();
	$childRefAfterInitialSync = isset($stateAfterInitialSync ['comments_remote'] ['9812']) && is_array($stateAfterInitialSync ['comments_remote'] ['9812']) ? $stateAfterInitialSync ['comments_remote'] ['9812'] : array();
	$childCommentId = !empty($childRefAfterInitialSync ['comment_id']) ? (string) $childRefAfterInitialSync ['comment_id'] : '';

	comment_delete($entryId, $localCommentId);

	$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
	$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
		'GET ' . $instanceUrl . '/api/v1/accounts/verify_credentials' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('id' => 'acct1', 'username' => 'flatpress'))
		),
		'GET ' . $instanceUrl . '/api/v1/accounts/acct1/statuses?limit=40&exclude_reblogs=true&exclude_replies=true&since_id=9810' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array())
		),
		'GET ' . $instanceUrl . '/api/v1/statuses/9810/context' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('descendants' => array($remoteParentStatus, $remoteChildStatus)))
		),
		'GET ' . $instanceUrl . '/api/v2/instance' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array(
				'version' => '4.5.0-sim',
				'api_versions' => array('mastodon' => 7),
				'configuration' => array(
					'statuses' => array(
						'max_characters' => 500,
						'characters_reserved_per_url' => 23
					),
					'media_attachments' => array(
						'image_size_limit' => 1048576,
						'image_matrix_limit' => 16777216,
						'video_size_limit' => 41943040,
						'video_frame_rate_limit' => 60,
						'video_matrix_limit' => 2304000,
						'max_description_length' => 1500,
						'max_remote_url_size' => 2048,
						'supported_mime_types' => array('image/jpeg'),
						'max_media_attachments' => 4
					)
				)
			))
		)
	);
	$contentSyncBeforeDeletionResult = plugin_mastodon_run_sync($force);
	$stateAfterContentSyncBeforeDeletion = plugin_mastodon_state_read();
	$parentRefAfterContentSyncBeforeDeletion = isset($stateAfterContentSyncBeforeDeletion ['comments_remote'] ['9811']) && is_array($stateAfterContentSyncBeforeDeletion ['comments_remote'] ['9811']) ? $stateAfterContentSyncBeforeDeletion ['comments_remote'] ['9811'] : array();
	$commentIdsAfterContentSyncBeforeDeletion = plugin_mastodon_list_local_comment_ids($entryId);
	$parentExistsAfterContentSyncBeforeDeletion = (bool) comment_exists($entryId, $localCommentId);

	$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
	$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
		'GET ' . $instanceUrl . '/api/v1/statuses/9810' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('id' => '9810'))
		),
		'DELETE ' . $instanceUrl . '/api/v1/statuses/9811?delete_media=1' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('id' => '9811'))
		),
		'GET ' . $instanceUrl . '/api/v1/statuses/9812' => $staleChildLookup ? array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('id' => '9812'))
		) : array(
			'ok' => false,
			'code' => 404,
			'body' => json_encode(array('error' => 'Record not found'))
		)
	);
	simulate_allow_pending_deletion_sync();
	$deletionPassAfterContentSync = plugin_mastodon_run_deletion_sync(true);
	$stateAfterDeletionPass = plugin_mastodon_state_read();
	$childExistsAfterDeletionPass = ($childCommentId !== '') ? (bool) comment_exists($entryId, $childCommentId) : false;
	$childCommentAfterDeletionPass = ($childExistsAfterDeletionPass && $childCommentId !== '') ? comment_parse($entryId, $childCommentId) : array();
	$childMetaAfterDeletionPass = ($childCommentId !== '') ? plugin_mastodon_state_get_comment_meta($stateAfterDeletionPass, $entryId, $childCommentId) : array();
	$childParentAfterDeletionPass = ($childExistsAfterDeletionPass && is_array($childCommentAfterDeletionPass)) ? plugin_mastodon_detect_local_comment_parent_id($entryId, $childCommentAfterDeletionPass) : '';

	return array(
		'initial_sync_result' => $initialSyncResult,
		'initial_child_ref' => $childRefAfterInitialSync,
		'entry_id' => $entryId,
		'local_comment_id' => $localCommentId,
		'child_comment_id' => $childCommentId,
		'content_sync_before_deletion_result' => $contentSyncBeforeDeletionResult,
		'state_after_content_sync_before_deletion' => $stateAfterContentSyncBeforeDeletion,
		'parent_ref_after_content_sync_before_deletion' => $parentRefAfterContentSyncBeforeDeletion,
		'comment_ids_after_content_sync_before_deletion' => $commentIdsAfterContentSyncBeforeDeletion,
		'parent_exists_after_content_sync_before_deletion' => $parentExistsAfterContentSyncBeforeDeletion,
		'deletion_pass_after_content_sync' => $deletionPassAfterContentSync,
		'state_after_deletion_pass' => $stateAfterDeletionPass,
		'child_exists_after_deletion_pass' => $childExistsAfterDeletionPass,
		'child_comment_after_deletion_pass' => $childCommentAfterDeletionPass,
		'child_meta_after_deletion_pass' => $childMetaAfterDeletionPass,
		'child_parent_after_deletion_pass' => $childParentAfterDeletionPass,
		'requests_after_deletion_pass' => simulate_recorded_http_requests()
	);
}

/**
 * Reproduce the exact content-sync-before-deletion-sync sequence for one deleted exported FlatPress comment with a remote descendant reply that already has its own child.
 * @param bool $force
 * @return array<string, mixed>
 */
function simulate_run_exported_comment_descendant_with_child_content_before_deletion_case($force) {
	$force = (bool) $force;
	$configuredOptions = plugin_mastodon_get_options();
	$options = simulate_seed_options_from_config($configuredOptions);
	$options ['sync_start_date'] = '';
	$options ['update_local_from_remote'] = '0';
	$options ['import_synced_comments_as_entries'] = '0';
	$options ['quote_imported_reply_parent'] = '1';
	$options ['old_thread_reply_check'] = '1';
	$options ['delete_sync_enabled'] = '1';
	$options ['access_token'] = 'token123';
	plugin_mastodon_save_options($options);
	plugin_mastodon_runtime_cache_clear();

	simulate_delete_recursive(ABS_PATH . FP_CONTENT . 'content');
	@mkdir(ABS_PATH . FP_CONTENT . 'content', 0777, true);
	simulate_delete_recursive(ABS_PATH . FP_CONTENT . 'images/mastodon');
	plugin_mastodon_state_write(plugin_mastodon_default_state());

	$instanceUrl = plugin_mastodon_normalize_instance_url($options ['instance_url']);
	$entry = array(
		'version' => system_ver(),
		'subject' => 'Content-before-deletion follow-up test root with descendant child',
		'content' => 'Root entry for exported-comment descendant content-before-deletion testing with one additional child reply.',
		'author' => 'Simulation',
		'date' => strtotime('2027-04-23 08:00:00 UTC')
	);
	$entryId = entry_save($entry, null);
	$entry = entry_parse($entryId);
	$localComment = array(
		'version' => system_ver(),
		'name' => 'FlatPress Local',
		'content' => 'Exported FlatPress parent comment that will be deleted locally before deletion sync while its remote reply already has a child.',
		'date' => strtotime('2027-04-23 08:05:00 UTC')
	);
	$localCommentId = comment_save($entryId, $localComment);
	$localComment = comment_parse($entryId, $localCommentId);
	$localComment ['id'] = (string) $localCommentId;

	$state = plugin_mastodon_default_state();
	$remoteRootStatus = array(
		'id' => '9820',
		'created_at' => '2027-04-23T08:00:00Z'
	);
	$remoteParentStatus = array(
		'id' => '9821',
		'visibility' => 'public',
		'in_reply_to_id' => '9820',
		'in_reply_to_account_id' => 'acct1',
		'created_at' => '2027-04-23T08:05:00Z',
		'content' => '<p>Exported FlatPress parent comment that will be deleted locally before deletion sync while its remote reply already has a child.</p>',
		'url' => $instanceUrl . '/@flatpress/9821',
		'account' => array(
			'id' => 'acct1',
			'acct' => 'flatpress',
			'display_name' => 'FlatPress Bot',
			'url' => $instanceUrl . '/@flatpress'
		)
	);
	$remoteChildStatus = array(
		'id' => '9822',
		'visibility' => 'public',
		'in_reply_to_id' => '9821',
		'in_reply_to_account_id' => 'acct1',
		'created_at' => '2027-04-23T08:06:00Z',
		'content' => '<p>Imported descendant reply that should be reattached to the synchronized entry status.</p>',
		'url' => 'https://example.net/@alice/9822',
		'account' => array(
			'id' => 'acct9',
			'acct' => 'alice@example.net',
			'display_name' => 'Alice Example',
			'url' => 'https://example.net/@alice'
		)
	);
	$remoteGrandchildStatus = array(
		'id' => '9823',
		'visibility' => 'public',
		'in_reply_to_id' => '9822',
		'in_reply_to_account_id' => 'acct9',
		'created_at' => '2027-04-23T08:07:00Z',
		'content' => '<p>Imported grandchild reply that should continue to follow the reattached child reply.</p>',
		'url' => 'https://example.org/@bob/9823',
		'account' => array(
			'id' => 'acct10',
			'acct' => 'bob@example.org',
			'display_name' => 'Bob Example',
			'url' => 'https://example.org/@bob'
		)
	);
	plugin_mastodon_state_set_entry_mapping($state, $entryId, '9820', 'remote', plugin_mastodon_entry_hash($entry), $instanceUrl . '/@flatpress/9820', plugin_mastodon_parse_iso_datetime('2027-04-23T08:00:00Z'), plugin_mastodon_local_item_date_key($entry, $entryId), plugin_mastodon_remote_status_date_key($remoteRootStatus));
	plugin_mastodon_state_set_comment_mapping($state, $entryId, $localCommentId, '9821', 'local', plugin_mastodon_comment_hash($localComment), $instanceUrl . '/@flatpress/9821', plugin_mastodon_parse_iso_datetime('2027-04-23T08:05:00Z'), '', '9820', plugin_mastodon_local_item_date_key($localComment, $localCommentId), plugin_mastodon_remote_status_date_key($remoteParentStatus));
	plugin_mastodon_state_write($state);

	$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
	$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
		'GET ' . $instanceUrl . '/api/v1/accounts/verify_credentials' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('id' => 'acct1', 'username' => 'flatpress'))
		),
		'GET ' . $instanceUrl . '/api/v1/accounts/acct1/statuses?limit=40&exclude_reblogs=true&exclude_replies=true' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array())
		),
		'GET ' . $instanceUrl . '/api/v1/statuses/9820/context' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('descendants' => array($remoteChildStatus, $remoteGrandchildStatus)))
		),
		'GET ' . $instanceUrl . '/api/v2/instance' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array(
				'version' => '4.5.0-sim',
				'api_versions' => array('mastodon' => 7),
				'configuration' => array(
					'statuses' => array(
						'max_characters' => 500,
						'characters_reserved_per_url' => 23
					),
					'media_attachments' => array(
						'image_size_limit' => 1048576,
						'image_matrix_limit' => 16777216,
						'video_size_limit' => 41943040,
						'video_frame_rate_limit' => 60,
						'video_matrix_limit' => 2304000,
						'max_description_length' => 1500,
						'max_remote_url_size' => 2048,
						'supported_mime_types' => array('image/jpeg'),
						'max_media_attachments' => 4
					)
				)
			))
		)
	);
	$initialSyncResult = plugin_mastodon_run_sync($force);
	$stateAfterInitialSync = plugin_mastodon_state_read();
	$childRefAfterInitialSync = isset($stateAfterInitialSync ['comments_remote'] ['9822']) && is_array($stateAfterInitialSync ['comments_remote'] ['9822']) ? $stateAfterInitialSync ['comments_remote'] ['9822'] : array();
	$grandchildRefAfterInitialSync = isset($stateAfterInitialSync ['comments_remote'] ['9823']) && is_array($stateAfterInitialSync ['comments_remote'] ['9823']) ? $stateAfterInitialSync ['comments_remote'] ['9823'] : array();
	$childCommentId = !empty($childRefAfterInitialSync ['comment_id']) ? (string) $childRefAfterInitialSync ['comment_id'] : '';
	$grandchildCommentId = !empty($grandchildRefAfterInitialSync ['comment_id']) ? (string) $grandchildRefAfterInitialSync ['comment_id'] : '';

	comment_delete($entryId, $localCommentId);

	$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
	$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
		'GET ' . $instanceUrl . '/api/v1/accounts/verify_credentials' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('id' => 'acct1', 'username' => 'flatpress'))
		),
		'GET ' . $instanceUrl . '/api/v1/accounts/acct1/statuses?limit=40&exclude_reblogs=true&exclude_replies=true&since_id=9820' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array())
		),
		'GET ' . $instanceUrl . '/api/v1/statuses/9820/context' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('descendants' => array($remoteParentStatus, $remoteChildStatus, $remoteGrandchildStatus)))
		),
		'GET ' . $instanceUrl . '/api/v2/instance' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array(
				'version' => '4.5.0-sim',
				'api_versions' => array('mastodon' => 7),
				'configuration' => array(
					'statuses' => array(
						'max_characters' => 500,
						'characters_reserved_per_url' => 23
					),
					'media_attachments' => array(
						'image_size_limit' => 1048576,
						'image_matrix_limit' => 16777216,
						'video_size_limit' => 41943040,
						'video_frame_rate_limit' => 60,
						'video_matrix_limit' => 2304000,
						'max_description_length' => 1500,
						'max_remote_url_size' => 2048,
						'supported_mime_types' => array('image/jpeg'),
						'max_media_attachments' => 4
					)
				)
			))
		)
	);
	$contentSyncBeforeDeletionResult = plugin_mastodon_run_sync($force);
	$stateAfterContentSyncBeforeDeletion = plugin_mastodon_state_read();
	$commentIdsAfterContentSyncBeforeDeletion = plugin_mastodon_list_local_comment_ids($entryId);
	sort($commentIdsAfterContentSyncBeforeDeletion);
	$parentRefAfterContentSyncBeforeDeletion = isset($stateAfterContentSyncBeforeDeletion ['comments_remote'] ['9821']) && is_array($stateAfterContentSyncBeforeDeletion ['comments_remote'] ['9821']) ? $stateAfterContentSyncBeforeDeletion ['comments_remote'] ['9821'] : array();
	$parentExistsAfterContentSyncBeforeDeletion = (bool) comment_exists($entryId, $localCommentId);

	$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
	$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
		'GET ' . $instanceUrl . '/api/v1/statuses/9820' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('id' => '9820'))
		),
		'DELETE ' . $instanceUrl . '/api/v1/statuses/9821?delete_media=1' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('id' => '9821'))
		),
		'GET ' . $instanceUrl . '/api/v1/statuses/9822' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('id' => '9822'))
		),
		'GET ' . $instanceUrl . '/api/v1/statuses/9823' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('id' => '9823'))
		)
	);
	simulate_allow_pending_deletion_sync();
	$deletionPassAfterContentSync = plugin_mastodon_run_deletion_sync(true);
	$stateAfterDeletionPass = plugin_mastodon_state_read();
	$requestsAfterDeletionPass = simulate_recorded_http_requests();
	$childExistsAfterDeletionPass = ($childCommentId !== '') ? (bool) comment_exists($entryId, $childCommentId) : false;
	$grandchildExistsAfterDeletionPass = ($grandchildCommentId !== '') ? (bool) comment_exists($entryId, $grandchildCommentId) : false;
	$childCommentAfterDeletionPass = ($childExistsAfterDeletionPass && $childCommentId !== '') ? comment_parse($entryId, $childCommentId) : array();
	$grandchildCommentAfterDeletionPass = ($grandchildExistsAfterDeletionPass && $grandchildCommentId !== '') ? comment_parse($entryId, $grandchildCommentId) : array();
	$childMetaAfterDeletionPass = ($childCommentId !== '') ? plugin_mastodon_state_get_comment_meta($stateAfterDeletionPass, $entryId, $childCommentId) : array();
	$grandchildMetaAfterDeletionPass = ($grandchildCommentId !== '') ? plugin_mastodon_state_get_comment_meta($stateAfterDeletionPass, $entryId, $grandchildCommentId) : array();
	$childParentAfterDeletionPass = ($childExistsAfterDeletionPass && is_array($childCommentAfterDeletionPass)) ? plugin_mastodon_detect_local_comment_parent_id($entryId, $childCommentAfterDeletionPass) : '';
	$grandchildParentAfterDeletionPass = ($grandchildExistsAfterDeletionPass && is_array($grandchildCommentAfterDeletionPass)) ? plugin_mastodon_detect_local_comment_parent_id($entryId, $grandchildCommentAfterDeletionPass) : '';

	return array(
		'initial_sync_result' => $initialSyncResult,
		'initial_child_ref' => $childRefAfterInitialSync,
		'initial_grandchild_ref' => $grandchildRefAfterInitialSync,
		'entry_id' => $entryId,
		'local_comment_id' => $localCommentId,
		'child_comment_id' => $childCommentId,
		'grandchild_comment_id' => $grandchildCommentId,
		'content_sync_before_deletion_result' => $contentSyncBeforeDeletionResult,
		'state_after_content_sync_before_deletion' => $stateAfterContentSyncBeforeDeletion,
		'comment_ids_after_content_sync_before_deletion' => $commentIdsAfterContentSyncBeforeDeletion,
		'parent_ref_after_content_sync_before_deletion' => $parentRefAfterContentSyncBeforeDeletion,
		'parent_exists_after_content_sync_before_deletion' => $parentExistsAfterContentSyncBeforeDeletion,
		'deletion_pass_after_content_sync' => $deletionPassAfterContentSync,
		'state_after_deletion_pass' => $stateAfterDeletionPass,
		'requests_after_deletion_pass' => $requestsAfterDeletionPass,
		'child_exists_after_deletion_pass' => $childExistsAfterDeletionPass,
		'grandchild_exists_after_deletion_pass' => $grandchildExistsAfterDeletionPass,
		'child_comment_after_deletion_pass' => $childCommentAfterDeletionPass,
		'grandchild_comment_after_deletion_pass' => $grandchildCommentAfterDeletionPass,
		'child_meta_after_deletion_pass' => $childMetaAfterDeletionPass,
		'grandchild_meta_after_deletion_pass' => $grandchildMetaAfterDeletionPass,
		'child_parent_after_deletion_pass' => $childParentAfterDeletionPass,
		'grandchild_parent_after_deletion_pass' => $grandchildParentAfterDeletionPass
	);
}

/**
 * Reproduce hybrid descendant follow-up handling for a deeper imported reply chain below one deleted exported FlatPress comment.
 * @param bool $force
 * @return array<string, mixed>
 */
function simulate_run_exported_comment_descendant_chain_case($force) {
	$force = (bool) $force;
	$configuredOptions = plugin_mastodon_get_options();
	$options = simulate_seed_options_from_config($configuredOptions);
	$options ['sync_start_date'] = '';
	$options ['update_local_from_remote'] = '0';
	$options ['import_synced_comments_as_entries'] = '0';
	$options ['quote_imported_reply_parent'] = '1';
	$options ['old_thread_reply_check'] = '1';
	$options ['delete_sync_enabled'] = '1';
	$options ['access_token'] = 'token123';
	plugin_mastodon_save_options($options);
	plugin_mastodon_runtime_cache_clear();

	simulate_delete_recursive(ABS_PATH . FP_CONTENT . 'content');
	@mkdir(ABS_PATH . FP_CONTENT . 'content', 0777, true);
	simulate_delete_recursive(ABS_PATH . FP_CONTENT . 'images/mastodon');
	plugin_mastodon_state_write(plugin_mastodon_default_state());

	$instanceUrl = plugin_mastodon_normalize_instance_url($options ['instance_url']);
	$entry = array(
		'version' => system_ver(),
		'subject' => 'Hybrid deletion follow-up test root',
		'content' => 'Root entry for hybrid descendant deletion follow-up testing.',
		'author' => 'Simulation',
		'date' => strtotime('2027-04-21 08:00:00 UTC')
	);
	$entryId = entry_save($entry, null);
	$entry = entry_parse($entryId);
	$localComment = array(
		'version' => system_ver(),
		'name' => 'FlatPress Local',
		'content' => 'Exported FlatPress parent comment that will be deleted locally in the hybrid follow-up test.',
		'date' => strtotime('2027-04-21 08:05:00 UTC')
	);
	$localCommentId = comment_save($entryId, $localComment);
	$localComment = comment_parse($entryId, $localCommentId);
	$localComment ['id'] = (string) $localCommentId;

	$state = plugin_mastodon_default_state();
	$remoteRootStatus = array(
		'id' => '970',
		'created_at' => '2027-04-21T08:00:00Z'
	);
	$remoteParentStatus = array(
		'id' => '971',
		'visibility' => 'public',
		'in_reply_to_id' => '970',
		'in_reply_to_account_id' => 'acct1',
		'created_at' => '2027-04-21T08:05:00Z',
		'content' => '<p>Exported FlatPress parent comment that will be deleted locally in the hybrid follow-up test.</p>',
		'url' => $instanceUrl . '/@flatpress/971',
		'account' => array(
			'id' => 'acct1',
			'acct' => 'flatpress',
			'display_name' => 'FlatPress Bot',
			'url' => $instanceUrl . '/@flatpress'
		)
	);
	$remoteChildStatus = array(
		'id' => '972',
		'visibility' => 'public',
		'in_reply_to_id' => '971',
		'in_reply_to_account_id' => 'acct1',
		'created_at' => '2027-04-21T08:06:00Z',
		'content' => '<p>Imported child reply from another Mastodon account.</p>',
		'url' => 'https://example.net/@alice/972',
		'account' => array(
			'id' => 'acct9',
			'acct' => 'alice@example.net',
			'display_name' => 'Alice Example',
			'url' => 'https://example.net/@alice'
		)
	);
	$remoteGrandchildStatus = array(
		'id' => '973',
		'visibility' => 'public',
		'in_reply_to_id' => '972',
		'in_reply_to_account_id' => 'acct9',
		'created_at' => '2027-04-21T08:07:00Z',
		'content' => '<p>Imported grandchild reply from a third Mastodon account.</p>',
		'url' => 'https://example.org/@bob/973',
		'account' => array(
			'id' => 'acct10',
			'acct' => 'bob@example.org',
			'display_name' => 'Bob Example',
			'url' => 'https://example.org/@bob'
		)
	);
	plugin_mastodon_state_set_entry_mapping($state, $entryId, '970', 'remote', plugin_mastodon_entry_hash($entry), $instanceUrl . '/@flatpress/970', plugin_mastodon_parse_iso_datetime('2027-04-21T08:00:00Z'), plugin_mastodon_local_item_date_key($entry, $entryId), plugin_mastodon_remote_status_date_key($remoteRootStatus));
	plugin_mastodon_state_set_comment_mapping($state, $entryId, $localCommentId, '971', 'local', plugin_mastodon_comment_hash($localComment), $instanceUrl . '/@flatpress/971', plugin_mastodon_parse_iso_datetime('2027-04-21T08:05:00Z'), '', '970', plugin_mastodon_local_item_date_key($localComment, $localCommentId), plugin_mastodon_remote_status_date_key($remoteParentStatus));
	plugin_mastodon_state_write($state);

	$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
	$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
		'GET ' . $instanceUrl . '/api/v1/accounts/verify_credentials' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('id' => 'acct1', 'username' => 'flatpress'))
		),
		'GET ' . $instanceUrl . '/api/v1/accounts/acct1/statuses?limit=40&exclude_reblogs=true&exclude_replies=true' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array())
		),
		'GET ' . $instanceUrl . '/api/v1/statuses/970/context' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('descendants' => array($remoteChildStatus, $remoteGrandchildStatus)))
		),
		'GET ' . $instanceUrl . '/api/v2/instance' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array(
				'version' => '4.5.0-sim',
				'api_versions' => array('mastodon' => 7),
				'configuration' => array(
					'statuses' => array(
						'max_characters' => 500,
						'characters_reserved_per_url' => 23
					),
					'media_attachments' => array(
						'image_size_limit' => 1048576,
						'image_matrix_limit' => 16777216,
						'video_size_limit' => 41943040,
						'video_frame_rate_limit' => 60,
						'video_matrix_limit' => 2304000,
						'max_description_length' => 1500,
						'max_remote_url_size' => 2048,
						'supported_mime_types' => array('image/jpeg'),
						'max_media_attachments' => 4
					)
				)
			))
		)
	);
	$initialSyncResult = plugin_mastodon_run_sync($force);
	$stateAfterInitialSync = plugin_mastodon_state_read();
	$childRefAfterInitialSync = isset($stateAfterInitialSync ['comments_remote'] ['972']) && is_array($stateAfterInitialSync ['comments_remote'] ['972']) ? $stateAfterInitialSync ['comments_remote'] ['972'] : array();
	$grandchildRefAfterInitialSync = isset($stateAfterInitialSync ['comments_remote'] ['973']) && is_array($stateAfterInitialSync ['comments_remote'] ['973']) ? $stateAfterInitialSync ['comments_remote'] ['973'] : array();
	$childCommentId = !empty($childRefAfterInitialSync ['comment_id']) ? (string) $childRefAfterInitialSync ['comment_id'] : '';
	$grandchildCommentId = !empty($grandchildRefAfterInitialSync ['comment_id']) ? (string) $grandchildRefAfterInitialSync ['comment_id'] : '';

	comment_delete($entryId, $localCommentId);

	$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
	$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
		'GET ' . $instanceUrl . '/api/v1/statuses/970' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('id' => '970'))
		),
		'DELETE ' . $instanceUrl . '/api/v1/statuses/971?delete_media=1' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('id' => '971'))
		),
		'GET ' . $instanceUrl . '/api/v1/statuses/972' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('id' => '972'))
		),
		'GET ' . $instanceUrl . '/api/v1/statuses/973' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('id' => '973'))
		)
	);
	simulate_allow_pending_deletion_sync();
	$deletionPassOne = plugin_mastodon_run_deletion_sync(true);
	$stateAfterDeletionPassOne = plugin_mastodon_state_read();
	$requestsAfterDeletionPassOne = simulate_recorded_http_requests();

	$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
	$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
		'GET ' . $instanceUrl . '/api/v1/statuses/972' => array(
			'ok' => false,
			'code' => 404,
			'body' => json_encode(array('error' => 'Record not found'))
		),
		'GET ' . $instanceUrl . '/api/v1/statuses/973' => array(
			'ok' => false,
			'code' => 404,
			'body' => json_encode(array('error' => 'Record not found'))
		)
	);
	simulate_allow_pending_deletion_sync();
	$deletionPassTwo = plugin_mastodon_run_deletion_sync(true);
	$stateAfterDeletionPassTwo = plugin_mastodon_state_read();
	$requestsAfterDeletionPassTwo = simulate_recorded_http_requests();

	return array(
		'initial_sync_result' => $initialSyncResult,
		'initial_child_ref' => $childRefAfterInitialSync,
		'initial_grandchild_ref' => $grandchildRefAfterInitialSync,
		'entry_id' => $entryId,
		'local_comment_id' => $localCommentId,
		'child_comment_id' => $childCommentId,
		'grandchild_comment_id' => $grandchildCommentId,
		'deletion_pass_one' => $deletionPassOne,
		'state_after_deletion_pass_one' => $stateAfterDeletionPassOne,
		'requests_after_deletion_pass_one' => $requestsAfterDeletionPassOne,
		'deletion_pass_two' => $deletionPassTwo,
		'state_after_deletion_pass_two' => $stateAfterDeletionPassTwo,
		'requests_after_deletion_pass_two' => $requestsAfterDeletionPassTwo,
		'child_exists_after_deletion_pass_two' => ($childCommentId !== '') ? (bool) comment_exists($entryId, $childCommentId) : false,
		'grandchild_exists_after_deletion_pass_two' => ($grandchildCommentId !== '') ? (bool) comment_exists($entryId, $grandchildCommentId) : false
	);
}

function simulate_run_remote_entry_local_comment_export_case($force) {
	$force = (bool) $force;
	$configuredOptions = plugin_mastodon_get_options();
	$options = simulate_seed_options_from_config($configuredOptions);
	$options ['sync_start_date'] = '';
	$options ['update_local_from_remote'] = '0';
	$options ['import_synced_comments_as_entries'] = '0';
	$options ['delete_sync_enabled'] = '0';
	$options ['access_token'] = 'token123';
	plugin_mastodon_save_options($options);
	plugin_mastodon_runtime_cache_clear();
	plugin_mastodon_sync_guard_clear('content');

	simulate_delete_recursive(ABS_PATH . FP_CONTENT . 'content');
	@mkdir(ABS_PATH . FP_CONTENT . 'content', 0777, true);
	simulate_delete_recursive(ABS_PATH . FP_CONTENT . 'images/mastodon');
	plugin_mastodon_state_write(plugin_mastodon_default_state());

	$instanceUrl = plugin_mastodon_normalize_instance_url($options ['instance_url']);
	$entry = array(
		'version' => system_ver(),
		'subject' => 'Imported Mastodon root for local comment export',
		'content' => 'Remote-sourced entry used to verify FlatPress comment export on imported Mastodon threads.',
		'author' => 'Simulation',
		'date' => strtotime('2027-02-12 10:00:00 UTC')
	);
	$entryId = entry_save($entry, null);
	$entry = entry_parse($entryId);

	$parentComment = array(
		'version' => system_ver(),
		'name' => 'Parent Author',
		'content' => 'Remote-thread parent comment body',
		'date' => strtotime('2027-02-12 10:05:00 UTC')
	);
	$parentCommentId = comment_save($entryId, $parentComment);
	$parentComment = comment_parse($entryId, $parentCommentId);
	$parentComment ['id'] = (string) $parentCommentId;

	$childComment = array(
		'version' => system_ver(),
		'name' => 'Child Author',
		'content' => 'Remote-thread child comment body',
		'replyto' => $parentCommentId,
		'date' => strtotime('2027-02-12 10:06:00 UTC')
	);
	$childCommentId = comment_save($entryId, $childComment);
	$childComment = comment_parse($entryId, $childCommentId);
	$childComment ['id'] = (string) $childCommentId;

	$state = plugin_mastodon_default_state();
	$remoteRootStatus = array(
		'id' => '930',
		'created_at' => '2027-02-12T10:00:00Z'
	);
	plugin_mastodon_state_set_entry_mapping($state, $entryId, '930', 'remote', plugin_mastodon_entry_hash($entry), $instanceUrl . '/@flatpress/930', plugin_mastodon_parse_iso_datetime('2027-02-12T10:00:00Z'), plugin_mastodon_local_item_date_key($entry, $entryId), plugin_mastodon_remote_status_date_key($remoteRootStatus));
	plugin_mastodon_state_write($state);

	$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
	$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
		'GET ' . $instanceUrl . '/api/v1/accounts/verify_credentials' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('id' => 'acct1', 'username' => 'flatpress'))
		),
		'GET ' . $instanceUrl . '/api/v1/accounts/acct1/statuses?limit=40&exclude_reblogs=true&exclude_replies=true' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array())
		),
		'GET ' . $instanceUrl . '/api/v1/statuses/930/context' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('descendants' => array()))
		),
		'POST ' . $instanceUrl . '/api/v1/statuses' => array(
			array(
				'ok' => true,
				'code' => 200,
				'body' => json_encode(array(
					'id' => '931',
					'url' => $instanceUrl . '/@flatpress/931',
					'created_at' => '2027-02-12T10:05:00Z'
				))
			),
			array(
				'ok' => true,
				'code' => 200,
				'body' => json_encode(array(
					'id' => '932',
					'url' => $instanceUrl . '/@flatpress/932',
					'created_at' => '2027-02-12T10:06:00Z'
				))
			)
		),
		'GET ' . $instanceUrl . '/api/v2/instance' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array(
				'version' => '4.3.0-sim',
				'api_versions' => array('mastodon' => 6),
				'configuration' => array(
					'statuses' => array(
						'max_characters' => 500,
						'characters_reserved_per_url' => 23,
						'max_media_attachments' => 4
					),
					'media_attachments' => array(
						'description_limit' => 1500,
						'max_media_attachments' => 4
					)
				)
			))
		)
	);

	$result = plugin_mastodon_run_sync($force);
	$state = plugin_mastodon_state_read();
	$entryMeta = plugin_mastodon_state_get_entry_meta($state, $entryId);
	$parentMeta = plugin_mastodon_state_get_comment_meta($state, $entryId, $parentCommentId);
	$childMeta = plugin_mastodon_state_get_comment_meta($state, $entryId, $childCommentId);

	$requests = simulate_recorded_http_requests();
	$entryRequest = array();
	$parentRequest = array();
	$childRequest = array();
	foreach ($requests as $request) {
		if (!is_array($request) || empty($request ['method']) || empty($request ['url']) || strtoupper((string) $request ['method']) !== 'POST' || strpos((string) $request ['url'], '/api/v1/statuses') === false) {
			continue;
		}
		$parsed = simulate_parse_http_request_body($request);
		$statusText = isset($parsed ['status']) ? (string) $parsed ['status'] : '';
		if ($entryRequest === array() && strpos($statusText, 'Imported Mastodon root for local comment export') !== false) {
			$entryRequest = $parsed;
		}
		if ($parentRequest === array() && strpos($statusText, 'Remote-thread parent comment body') !== false) {
			$parentRequest = $parsed;
		}
		if ($childRequest === array() && strpos($statusText, 'Remote-thread child comment body') !== false) {
			$childRequest = $parsed;
		}
	}

	return array(
		'result' => $result,
		'state' => $state,
		'entry_id' => $entryId,
		'parent_comment_id' => $parentCommentId,
		'child_comment_id' => $childCommentId,
		'entry_meta' => $entryMeta,
		'parent_meta' => $parentMeta,
		'child_meta' => $childMeta,
		'entry_request' => $entryRequest,
		'parent_request' => $parentRequest,
		'child_request' => $childRequest,
		'http_requests' => $requests
	);
}

function simulate_run_emoticon_export_sync_case($force) {
	$force = (bool) $force;
	$configuredOptions = plugin_mastodon_get_options();
	$options = simulate_seed_options_from_config($configuredOptions);
	$options ['sync_start_date'] = '';
	$options ['update_local_from_remote'] = '0';
	$options ['import_synced_comments_as_entries'] = '0';
	$options ['delete_sync_enabled'] = '0';
	$options ['access_token'] = 'token123';
	plugin_mastodon_save_options($options);
	plugin_mastodon_runtime_cache_clear();

	simulate_delete_recursive(ABS_PATH . FP_CONTENT . 'content');
	@mkdir(ABS_PATH . FP_CONTENT . 'content', 0777, true);
	simulate_delete_recursive(ABS_PATH . FP_CONTENT . 'images/mastodon');
	plugin_mastodon_state_write(plugin_mastodon_default_state());

	$instanceUrl = plugin_mastodon_normalize_instance_url($options ['instance_url']);
	$entry = array(
		'version' => system_ver(),
		'subject' => 'Emoji title :smile:',
		'content' => 'Entry body :wink:',
		'author' => 'Simulation',
		'date' => strtotime('2027-02-13 10:00:00 UTC')
	);
	$entryId = entry_save($entry, null);
	$entry = entry_parse($entryId);

	$comment = array(
		'version' => system_ver(),
		'name' => 'Emoji author :grin:',
		'content' => 'Comment body :joy:',
		'date' => strtotime('2027-02-13 10:05:00 UTC')
	);
	$commentId = comment_save($entryId, $comment);
	$comment = comment_parse($entryId, $commentId);
	$comment ['id'] = (string) $commentId;

	$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
	$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
		'GET ' . $instanceUrl . '/api/v1/accounts/verify_credentials' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('id' => 'acct1', 'username' => 'flatpress'))
		),
		'GET ' . $instanceUrl . '/api/v1/accounts/acct1/statuses?limit=40&exclude_reblogs=true&exclude_replies=true' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array())
		),
		'POST ' . $instanceUrl . '/api/v1/statuses' => array(
			array(
				'ok' => true,
				'code' => 200,
				'body' => json_encode(array(
					'id' => '991',
					'url' => $instanceUrl . '/@flatpress/991',
					'created_at' => '2027-02-13T10:00:00Z'
				))
			),
			array(
				'ok' => true,
				'code' => 200,
				'body' => json_encode(array(
					'id' => '992',
					'url' => $instanceUrl . '/@flatpress/992',
					'created_at' => '2027-02-13T10:05:00Z'
				))
			)
		),
		'GET ' . $instanceUrl . '/api/v2/instance' => array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array(
				'version' => '4.3.0-sim',
				'api_versions' => array('mastodon' => 6),
				'configuration' => array(
					'statuses' => array(
						'max_characters' => 500,
						'characters_reserved_per_url' => 23,
						'max_media_attachments' => 4
					),
					'media_attachments' => array(
						'description_limit' => 1500,
						'max_media_attachments' => 4
					)
				)
			))
		)
	);

	$result = plugin_mastodon_run_sync($force);
	$state = plugin_mastodon_state_read();
	$entryMeta = plugin_mastodon_state_get_entry_meta($state, $entryId);
	$commentMeta = plugin_mastodon_state_get_comment_meta($state, $entryId, $commentId);
	$requests = simulate_recorded_http_requests();
	$entryRequest = array();
	$commentRequest = array();
	foreach ($requests as $request) {
		if (!is_array($request) || empty($request ['method']) || empty($request ['url']) || strtoupper((string) $request ['method']) !== 'POST' || strpos((string) $request ['url'], '/api/v1/statuses') === false) {
			continue;
		}
		$parsed = simulate_parse_http_request_body($request);
		$statusText = isset($parsed ['status']) ? (string) $parsed ['status'] : '';
		if ($entryRequest === array() && strpos($statusText, 'Entry body') !== false) {
			$entryRequest = $parsed;
		}
		if ($commentRequest === array() && strpos($statusText, 'Comment body') !== false) {
			$commentRequest = $parsed;
		}
	}

	return array(
		'result' => $result,
		'state' => $state,
		'entry_id' => $entryId,
		'comment_id' => $commentId,
		'entry_meta' => $entryMeta,
		'comment_meta' => $commentMeta,
		'entry_request' => $entryRequest,
		'comment_request' => $commentRequest,
		'http_requests' => $requests
	);
}

simulate_seed_regression_fixtures();

$GLOBALS ['plugin_mastodon_test_http_no_network'] = !simulate_request_flag('live-auth');

$allOk = true;

$longSummaryFixture = json_encode(array(
	'alpha' => str_repeat('x', 240),
	'beta' => array('one', 'two', 'three'),
	'gamma' => 'summary detail fixture'
));
$shortSummaryFixture = simulate_short_test_details($longSummaryFixture);
$allOk = test_result(
	'Simulation summary mode shortens verbose JSON details',
	strpos($shortSummaryFixture, 'json keys=alpha,beta,gamma') === 0
		&& strpos($shortSummaryFixture, 'bytes=') !== false
		&& strlen($shortSummaryFixture) < 120,
	$shortSummaryFixture
);

$summaryCountsBackup = $GLOBALS ['simulate_mastodon_result_counts'];
$GLOBALS ['simulate_mastodon_result_counts'] = array('OK' => 12, 'FAIL' => 1, 'WARN' => 2, 'SKIP' => 3);
$summaryFixture = simulate_format_final_summary(2);
$GLOBALS ['simulate_mastodon_result_counts'] = $summaryCountsBackup;
$allOk = test_result(
	'Simulation final summary reports exit code and status counters',
	strpos($summaryFixture, 'Exit-code: 2') !== false
		&& strpos($summaryFixture, '[OK]: 12') !== false
		&& strpos($summaryFixture, '[FAIL]: 1') !== false
		&& strpos($summaryFixture, '[WARN]: 2') !== false
		&& strpos($summaryFixture, '[SKIP]: 3') !== false,
	trim(str_replace(PHP_EOL, ' | ', $summaryFixture))
) && $allOk;

$sandboxCopyFixtureSource = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'flatpress-mastodon-sandbox-copy-source-' . str_replace('.', '-', uniqid('', true));
$sandboxCopyFixtureEntry = $sandboxCopyFixtureSource . DIRECTORY_SEPARATOR . 'fp-content' . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . '26' . DIRECTORY_SEPARATOR . '05' . DIRECTORY_SEPARATOR . 'entry260521-120000' . EXT;
$sandboxCopyFixtureConfig = $sandboxCopyFixtureSource . DIRECTORY_SEPARATOR . 'fp-content' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'settings.conf.php';
simulate_write_fixture_file($sandboxCopyFixtureEntry, 'live content fixture that must not be copied by default');
simulate_write_fixture_file($sandboxCopyFixtureConfig, '<?php return array();');
$sandboxCopyDefault = simulate_prepare_sandbox($sandboxCopyFixtureSource, false);
$sandboxCopyLive = simulate_prepare_sandbox($sandboxCopyFixtureSource, true);
$defaultSandboxEntry = $sandboxCopyDefault . DIRECTORY_SEPARATOR . 'fp-content' . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . '26' . DIRECTORY_SEPARATOR . '05' . DIRECTORY_SEPARATOR . 'entry260521-120000' . EXT;
$defaultSandboxContentDir = $sandboxCopyDefault . DIRECTORY_SEPARATOR . 'fp-content' . DIRECTORY_SEPARATOR . 'content';
$defaultSandboxConfig = $sandboxCopyDefault . DIRECTORY_SEPARATOR . 'fp-content' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'settings.conf.php';
$liveSandboxEntry = $sandboxCopyLive . DIRECTORY_SEPARATOR . 'fp-content' . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . '26' . DIRECTORY_SEPARATOR . '05' . DIRECTORY_SEPARATOR . 'entry260521-120000' . EXT;
$allOk = test_result(
	'Simulation sandbox excludes live fp-content/content by default',
	!is_file($defaultSandboxEntry)
		&& is_dir($defaultSandboxContentDir)
		&& is_file($defaultSandboxConfig),
	json_encode(array(
		'default_entry_copied' => is_file($defaultSandboxEntry),
		'content_dir_exists' => is_dir($defaultSandboxContentDir),
		'config_copied' => is_file($defaultSandboxConfig)
	))
) && $allOk;
$allOk = test_result(
	'Simulation sandbox can include live fp-content/content only when explicitly requested',
	is_file($liveSandboxEntry),
	json_encode(array('live_entry_copied' => is_file($liveSandboxEntry)))
) && $allOk;
simulate_delete_recursive($sandboxCopyDefault);
simulate_delete_recursive($sandboxCopyLive);
simulate_delete_recursive($sandboxCopyFixtureSource);

$scannerFixtureRoot = PLUGIN_MASTODON_STATE_DIR . 'entry-scanner-comments-skip';
simulate_delete_recursive($scannerFixtureRoot);
$scannerMonthDir = $scannerFixtureRoot . DIRECTORY_SEPARATOR . '26' . DIRECTORY_SEPARATOR . '05';
$scannerCommentsDir = $scannerMonthDir . DIRECTORY_SEPARATOR . 'entry260521-120000' . DIRECTORY_SEPARATOR . 'comments';
mkdir($scannerCommentsDir, 0777, true);
file_put_contents($scannerMonthDir . DIRECTORY_SEPARATOR . 'entry260521-120000' . EXT, 'real entry fixture');
file_put_contents($scannerCommentsDir . DIRECTORY_SEPARATOR . 'entry260521-120000' . EXT, 'duplicate entry-like file below comments');
file_put_contents($scannerCommentsDir . DIRECTORY_SEPARATOR . 'entry260521-120001' . EXT, 'unrelated entry-like file below comments');
$scannerFiles = array();
plugin_mastodon_collect_entry_files($scannerFixtureRoot, $scannerFiles);
sort($scannerFiles, SORT_STRING);
$scannerBasenames = array();
foreach ($scannerFiles as $scannerFile) {
	$scannerBasenames [] = basename((string) $scannerFile);
}
$scannerTestOk = test_result(
	'Entry file scanner skips comment directories while keeping real FlatPress entries',
	count($scannerFiles) === 1 && $scannerBasenames === array('entry260521-120000' . EXT),
	json_encode(array('files' => $scannerFiles, 'basenames' => $scannerBasenames))
);
if (!$scannerTestOk) {
	$allOk = false;
}

// Scheduled direct YY/MM scan keeps active-window entries and parent entries from dirty comments, but skips old clean entries.
$scheduledScannerOptions = simulate_seed_options_from_config(plugin_mastodon_get_options());
$scheduledScannerOptions ['sync_start_date'] = '2034-01-01';
$scheduledScannerOptions ['sync_scheduled_window_days'] = '7';
$GLOBALS ['plugin_mastodon_test_now'] = strtotime('2035-01-15 12:00:00 UTC');
$scheduledOldCleanEntryId = 'entry341220-120000';
$scheduledDirtyCommentEntryId = 'entry341221-120000';
$scheduledRecentEntryId = 'entry350114-120000';
$scheduledDirtyCommentId = 'comment341221-120500';
simulate_write_entry_fixture($scheduledOldCleanEntryId, array(
	'version' => system_ver(),
	'subject' => 'Scheduled scanner old clean entry',
	'content' => 'Old clean entry outside the scheduled window.',
	'author' => 'Simulation',
	'date' => strtotime('2034-12-20 12:00:00 UTC')
));
simulate_write_entry_fixture($scheduledDirtyCommentEntryId, array(
	'version' => system_ver(),
	'subject' => 'Scheduled scanner dirty comment parent',
	'content' => 'Old parent entry outside the scheduled window but required by a dirty comment.',
	'author' => 'Simulation',
	'date' => strtotime('2034-12-21 12:00:00 UTC')
));
simulate_write_comment_fixture($scheduledDirtyCommentEntryId, $scheduledDirtyCommentId, array(
	'version' => system_ver(),
	'name' => 'Simulation',
	'content' => 'Dirty comment parent must force the old entry into scheduled candidates.',
	'date' => strtotime('2034-12-21 12:05:00 UTC')
));
simulate_write_entry_fixture($scheduledRecentEntryId, array(
	'version' => system_ver(),
	'subject' => 'Scheduled scanner recent entry',
	'content' => 'Recent entry inside the scheduled window.',
	'author' => 'Simulation',
	'date' => strtotime('2035-01-14 12:00:00 UTC')
));
$scheduledScannerState = plugin_mastodon_default_state();
$scheduledScannerState ['dirty_comments'] [$scheduledDirtyCommentEntryId . ':' . $scheduledDirtyCommentId] = array(
	'entry_id' => $scheduledDirtyCommentEntryId,
	'comment_id' => $scheduledDirtyCommentId,
	'hash' => 'dirty-comment-hash',
	'queued_at' => '2035-01-15 12:00:00'
);
$scheduledScannerDirtyLookup = plugin_mastodon_dirty_entry_id_lookup($scheduledScannerState);
$scheduledScannerFiles = plugin_mastodon_collect_entry_files_for_sync($scheduledScannerOptions, $scheduledScannerDirtyLookup, false);
$scheduledScannerBasenames = array();
foreach ($scheduledScannerFiles as $scheduledScannerFile) {
	$scheduledScannerBasenames [] = basename((string) $scheduledScannerFile, EXT);
}
sort($scheduledScannerBasenames, SORT_STRING);
$scheduledScannerTestOk = test_result(
	'Scheduled direct YY/MM scanner keeps active-window entries and dirty-comment parents',
	in_array($scheduledRecentEntryId, $scheduledScannerBasenames, true)
		&& in_array($scheduledDirtyCommentEntryId, $scheduledScannerBasenames, true)
		&& !in_array($scheduledOldCleanEntryId, $scheduledScannerBasenames, true),
	json_encode(array(
		'basenames' => $scheduledScannerBasenames,
		'old_clean_entry' => $scheduledOldCleanEntryId,
		'dirty_comment_parent' => $scheduledDirtyCommentEntryId,
		'recent_entry' => $scheduledRecentEntryId
	))
);
if (!$scheduledScannerTestOk) {
	$allOk = false;
}
simulate_delete_entry_fixture($scheduledOldCleanEntryId);
simulate_delete_entry_fixture($scheduledDirtyCommentEntryId);
simulate_delete_entry_fixture($scheduledRecentEntryId);
unset($GLOBALS ['plugin_mastodon_test_now']);

// Scheduled direct scanner guardrails for the configurable 7/14/30-day admin window.
$scheduledWindowOptions = simulate_seed_options_from_config(plugin_mastodon_get_options());
$scheduledWindowOptions ['sync_start_date'] = '2035-01-01';

$scheduled7Ids = array('entry360107-120000', 'entry360108-120000', 'entry360115-120000');
$GLOBALS ['plugin_mastodon_test_now'] = strtotime('2036-01-15 12:00:00 UTC');
$scheduledWindowOptions ['sync_scheduled_window_days'] = '7';
simulate_write_entry_fixture($scheduled7Ids [0], array(
	'version' => system_ver(),
	'subject' => 'Seven-day scanner old entry',
	'content' => 'This clean entry is just outside the 7-day scheduled window.',
	'author' => 'Simulation',
	'date' => strtotime('2036-01-07 12:00:00 UTC')
));
simulate_write_entry_fixture($scheduled7Ids [1], array(
	'version' => system_ver(),
	'subject' => 'Seven-day scanner lower-bound entry',
	'content' => 'This clean entry is exactly at the 7-day scheduled window lower bound.',
	'author' => 'Simulation',
	'date' => strtotime('2036-01-08 12:00:00 UTC')
));
simulate_write_entry_fixture($scheduled7Ids [2], array(
	'version' => system_ver(),
	'subject' => 'Seven-day scanner current entry',
	'content' => 'This clean entry is inside the 7-day scheduled window.',
	'author' => 'Simulation',
	'date' => strtotime('2036-01-15 12:00:00 UTC')
));
$scheduled7ParsedIds = simulate_entry_ids_for_sync($scheduledWindowOptions, plugin_mastodon_default_state(), false);
$allOk = test_result(
	'Scheduled direct scanner honors the 7-day admin window at parse stage',
	!in_array($scheduled7Ids [0], $scheduled7ParsedIds, true)
		&& in_array($scheduled7Ids [1], $scheduled7ParsedIds, true)
		&& in_array($scheduled7Ids [2], $scheduled7ParsedIds, true),
	json_encode(array('parsed_ids' => $scheduled7ParsedIds, 'fixture_ids' => $scheduled7Ids))
) && $allOk;
simulate_delete_entry_fixtures($scheduled7Ids);
unset($GLOBALS ['plugin_mastodon_test_now']);

$scheduled14Ids = array('entry351221-120000', 'entry351222-120000', 'entry360105-120000');
$GLOBALS ['plugin_mastodon_test_now'] = strtotime('2036-01-05 12:00:00 UTC');
$scheduledWindowOptions ['sync_scheduled_window_days'] = '14';
simulate_write_entry_fixture($scheduled14Ids [0], array(
	'version' => system_ver(),
	'subject' => 'Fourteen-day scanner old December entry',
	'content' => 'This clean entry is outside the 14-day scheduled window.',
	'author' => 'Simulation',
	'date' => strtotime('2035-12-21 12:00:00 UTC')
));
simulate_write_entry_fixture($scheduled14Ids [1], array(
	'version' => system_ver(),
	'subject' => 'Fourteen-day scanner December boundary entry',
	'content' => 'This clean entry is exactly at the 14-day scheduled window lower bound.',
	'author' => 'Simulation',
	'date' => strtotime('2035-12-22 12:00:00 UTC')
));
simulate_write_entry_fixture($scheduled14Ids [2], array(
	'version' => system_ver(),
	'subject' => 'Fourteen-day scanner January entry',
	'content' => 'This clean entry is inside the 14-day scheduled window.',
	'author' => 'Simulation',
	'date' => strtotime('2036-01-05 12:00:00 UTC')
));
$scheduled14ParsedIds = simulate_entry_ids_for_sync($scheduledWindowOptions, plugin_mastodon_default_state(), false);
$allOk = test_result(
	'Scheduled direct scanner honors the 14-day admin window across a month boundary',
	!in_array($scheduled14Ids [0], $scheduled14ParsedIds, true)
		&& in_array($scheduled14Ids [1], $scheduled14ParsedIds, true)
		&& in_array($scheduled14Ids [2], $scheduled14ParsedIds, true),
	json_encode(array('parsed_ids' => $scheduled14ParsedIds, 'fixture_ids' => $scheduled14Ids))
) && $allOk;
simulate_delete_entry_fixtures($scheduled14Ids);
unset($GLOBALS ['plugin_mastodon_test_now']);

$scheduled30Ids = array('entry351130-120000', 'entry351216-120000', 'entry360115-120000');
$GLOBALS ['plugin_mastodon_test_now'] = strtotime('2036-01-15 12:00:00 UTC');
$scheduledWindowOptions ['sync_scheduled_window_days'] = '30';
simulate_write_entry_fixture($scheduled30Ids [0], array(
	'version' => system_ver(),
	'subject' => 'Thirty-day scanner old November entry',
	'content' => 'This clean entry is outside the 30-day scheduled window.',
	'author' => 'Simulation',
	'date' => strtotime('2035-11-30 12:00:00 UTC')
));
simulate_write_entry_fixture($scheduled30Ids [1], array(
	'version' => system_ver(),
	'subject' => 'Thirty-day scanner December boundary entry',
	'content' => 'This clean entry is exactly at the 30-day scheduled window lower bound.',
	'author' => 'Simulation',
	'date' => strtotime('2035-12-16 12:00:00 UTC')
));
simulate_write_entry_fixture($scheduled30Ids [2], array(
	'version' => system_ver(),
	'subject' => 'Thirty-day scanner January entry',
	'content' => 'This clean entry is inside the 30-day scheduled window.',
	'author' => 'Simulation',
	'date' => strtotime('2036-01-15 12:00:00 UTC')
));
$scheduled30ParsedIds = simulate_entry_ids_for_sync($scheduledWindowOptions, plugin_mastodon_default_state(), false);
$allOk = test_result(
	'Scheduled direct scanner honors the 30-day admin window across a year boundary',
	!in_array($scheduled30Ids [0], $scheduled30ParsedIds, true)
		&& in_array($scheduled30Ids [1], $scheduled30ParsedIds, true)
		&& in_array($scheduled30Ids [2], $scheduled30ParsedIds, true),
	json_encode(array('parsed_ids' => $scheduled30ParsedIds, 'fixture_ids' => $scheduled30Ids))
) && $allOk;
simulate_delete_entry_fixtures($scheduled30Ids);
unset($GLOBALS ['plugin_mastodon_test_now']);

$dirtyEntryGuardIds = array('entry360101-120000', 'entry360102-120000', 'entry360103-120000', 'entry360104-120000', 'entry360105-120000');
$GLOBALS ['plugin_mastodon_test_now'] = strtotime('2036-02-15 12:00:00 UTC');
$scheduledWindowOptions ['sync_scheduled_window_days'] = '7';
$dirtyEntryGuardState = plugin_mastodon_default_state();
foreach ($dirtyEntryGuardIds as $dirtyEntryGuardIndex => $dirtyEntryGuardId) {
	simulate_write_entry_fixture($dirtyEntryGuardId, array(
		'version' => system_ver(),
		'subject' => 'Dirty entry guard fixture ' . (string) $dirtyEntryGuardIndex,
		'content' => 'Older dirty entries are mandatory candidates and must not be capped at three.',
		'author' => 'Simulation',
		'date' => strtotime('2036-01-' . str_pad((string) ($dirtyEntryGuardIndex + 1), 2, '0', STR_PAD_LEFT) . ' 12:00:00 UTC')
	));
	$dirtyEntryGuardState ['dirty_entries'] [$dirtyEntryGuardId] = array(
		'hash' => 'dirty-entry-' . (string) $dirtyEntryGuardIndex,
		'queued_at' => '2036-02-15 12:00:00'
	);
}
$dirtyEntryGuardParsedIds = simulate_entry_ids_for_sync($scheduledWindowOptions, $dirtyEntryGuardState, false);
$dirtyEntryGuardFound = 0;
foreach ($dirtyEntryGuardIds as $dirtyEntryGuardId) {
	if (in_array($dirtyEntryGuardId, $dirtyEntryGuardParsedIds, true)) {
		$dirtyEntryGuardFound++;
	}
}
$allOk = test_result(
	'Scheduled direct scanner treats more than three dirty entries as mandatory candidates',
	$dirtyEntryGuardFound === count($dirtyEntryGuardIds),
	json_encode(array('parsed_ids' => $dirtyEntryGuardParsedIds, 'dirty_ids' => $dirtyEntryGuardIds))
) && $allOk;
simulate_delete_entry_fixtures($dirtyEntryGuardIds);
unset($GLOBALS ['plugin_mastodon_test_now']);

$dirtyCommentGuardEntryIds = array('entry360106-120000', 'entry360107-120000', 'entry360108-120000', 'entry360109-120000');
$GLOBALS ['plugin_mastodon_test_now'] = strtotime('2036-02-15 12:00:00 UTC');
$dirtyCommentGuardState = plugin_mastodon_default_state();
foreach ($dirtyCommentGuardEntryIds as $dirtyCommentGuardIndex => $dirtyCommentGuardEntryId) {
	$dirtyCommentGuardCommentId = 'comment3601' . str_pad((string) ($dirtyCommentGuardIndex + 6), 2, '0', STR_PAD_LEFT) . '-120500';
	simulate_write_entry_fixture($dirtyCommentGuardEntryId, array(
		'version' => system_ver(),
		'subject' => 'Dirty comment parent guard fixture ' . (string) $dirtyCommentGuardIndex,
		'content' => 'Older parents of dirty comments are mandatory candidates and must not be capped at three.',
		'author' => 'Simulation',
		'date' => strtotime('2036-01-' . str_pad((string) ($dirtyCommentGuardIndex + 6), 2, '0', STR_PAD_LEFT) . ' 12:00:00 UTC')
	));
	simulate_write_comment_fixture($dirtyCommentGuardEntryId, $dirtyCommentGuardCommentId, array(
		'version' => system_ver(),
		'name' => 'Simulation',
		'content' => 'Dirty comment parent guard fixture.',
		'date' => strtotime('2036-01-' . str_pad((string) ($dirtyCommentGuardIndex + 6), 2, '0', STR_PAD_LEFT) . ' 12:05:00 UTC')
	));
	$dirtyCommentGuardState ['dirty_comments'] [$dirtyCommentGuardEntryId . ':' . $dirtyCommentGuardCommentId] = array(
		'entry_id' => $dirtyCommentGuardEntryId,
		'comment_id' => $dirtyCommentGuardCommentId,
		'hash' => 'dirty-comment-' . (string) $dirtyCommentGuardIndex,
		'queued_at' => '2036-02-15 12:00:00'
	);
}
$dirtyCommentGuardParsedIds = simulate_entry_ids_for_sync($scheduledWindowOptions, $dirtyCommentGuardState, false);
$dirtyCommentGuardFound = 0;
foreach ($dirtyCommentGuardEntryIds as $dirtyCommentGuardEntryId) {
	if (in_array($dirtyCommentGuardEntryId, $dirtyCommentGuardParsedIds, true)) {
		$dirtyCommentGuardFound++;
	}
}
$allOk = test_result(
	'Scheduled direct scanner treats more than three dirty-comment parents as mandatory candidates',
	$dirtyCommentGuardFound === count($dirtyCommentGuardEntryIds),
	json_encode(array('parsed_ids' => $dirtyCommentGuardParsedIds, 'dirty_parent_ids' => $dirtyCommentGuardEntryIds))
) && $allOk;
simulate_delete_entry_fixtures($dirtyCommentGuardEntryIds);
unset($GLOBALS ['plugin_mastodon_test_now']);

$forceScannerIds = array('entry340101-120000', 'entry360215-120000');
$forceScannerOptions = simulate_seed_options_from_config(plugin_mastodon_get_options());
$forceScannerOptions ['sync_start_date'] = '2036-01-01';
$forceScannerOptions ['sync_scheduled_window_days'] = '7';
simulate_write_entry_fixture($forceScannerIds [0], array(
	'version' => system_ver(),
	'subject' => 'Force scanner older entry',
	'content' => 'Manual force sync must keep the all-YY/MM repair path.',
	'author' => 'Simulation',
	'date' => strtotime('2034-01-01 12:00:00 UTC')
));
simulate_write_entry_fixture($forceScannerIds [1], array(
	'version' => system_ver(),
	'subject' => 'Force scanner current entry',
	'content' => 'Manual force sync must also include current entries.',
	'author' => 'Simulation',
	'date' => strtotime('2036-02-15 12:00:00 UTC')
));
$forceScannerParsedIds = simulate_entry_ids_for_sync($forceScannerOptions, plugin_mastodon_default_state(), true);
$allOk = test_result(
	'Manual force sync keeps the direct all-YY/MM scanner repair path',
	in_array($forceScannerIds [0], $forceScannerParsedIds, true)
		&& in_array($forceScannerIds [1], $forceScannerParsedIds, true),
	json_encode(array('parsed_ids' => $forceScannerParsedIds, 'fixture_ids' => $forceScannerIds))
) && $allOk;
simulate_delete_entry_fixtures($forceScannerIds);

simulate_delete_recursive($scannerFixtureRoot);

$originalEnabledPlugins = isset($GLOBALS ['fp_plugins']) && is_array($GLOBALS ['fp_plugins']) ? $GLOBALS ['fp_plugins'] : null;
$companionFixturePlugins = array('bbcode', 'photoswipe', 'audiovideo', 'tag', 'emoticons', 'missing-mastodon-companion-plugin');
if (is_array($originalEnabledPlugins)) {
	$GLOBALS ['fp_plugins'] = array_values(array_unique(array_merge($originalEnabledPlugins, $companionFixturePlugins)));
}

$enabledPluginChecks = array(
	'bbcode' => plugin_mastodon_enabled_plugin_state('bbcode'),
	'photoswipe' => plugin_mastodon_enabled_plugin_state('photoswipe'),
	'audiovideo' => plugin_mastodon_enabled_plugin_state('audiovideo'),
	'tag' => plugin_mastodon_enabled_plugin_state('tag'),
	'emoticons' => plugin_mastodon_enabled_plugin_state('emoticons')
);
$enabledPluginChecksOk = !in_array(false, $enabledPluginChecks, true);
$enabledPluginTestOk = test_result(
	'Mastodon companion-plugin detection uses FlatPress central enabled-plugin state',
	$enabledPluginChecksOk,
	json_encode($enabledPluginChecks)
);
if (!$enabledPluginTestOk) {
	$allOk = false;
}

$allOk = test_result(
	'Mastodon companion-plugin detection respects FlatPress plugin_exists() for missing configured plugins',
	plugin_mastodon_enabled_plugin_state('missing-mastodon-companion-plugin') === false,
	json_encode(array('missing-mastodon-companion-plugin' => plugin_mastodon_enabled_plugin_state('missing-mastodon-companion-plugin')))
) && $allOk;

$companionPlugins = plugin_mastodon_companion_plugins_status(array('disable_remote_import' => '0'));
$companionBySlug = array();
foreach ($companionPlugins as $companionPlugin) {
	if (is_array($companionPlugin) && !empty($companionPlugin ['slug'])) {
		$companionBySlug [(string) $companionPlugin ['slug']] = $companionPlugin;
	}
}
$allOk = test_result(
	'Mastodon admin companion-plugin diagnostics cover BBCode, PhotoSwipe, AudioVideo, Tag and Emoticons',
	isset($companionBySlug ['bbcode'], $companionBySlug ['photoswipe'], $companionBySlug ['audiovideo'], $companionBySlug ['tag'], $companionBySlug ['emoticons'])
		&& !empty($companionBySlug ['bbcode'] ['active'])
		&& !empty($companionBySlug ['bbcode'] ['label'])
		&& !empty($companionBySlug ['bbcode'] ['description'])
		&& !empty($companionBySlug ['photoswipe'] ['active'])
		&& !empty($companionBySlug ['photoswipe'] ['label'])
		&& !empty($companionBySlug ['photoswipe'] ['description'])
		&& !empty($companionBySlug ['audiovideo'] ['active'])
		&& !empty($companionBySlug ['audiovideo'] ['label'])
		&& !empty($companionBySlug ['audiovideo'] ['description'])
		&& !empty($companionBySlug ['tag'] ['active'])
		&& !empty($companionBySlug ['tag'] ['label'])
		&& !empty($companionBySlug ['tag'] ['description'])
		&& !empty($companionBySlug ['emoticons'] ['active'])
		&& !empty($companionBySlug ['emoticons'] ['label'])
		&& !empty($companionBySlug ['emoticons'] ['description']),
	json_encode($companionBySlug)
) && $allOk;

$oneWayCompanionPlugins = plugin_mastodon_companion_plugins_status(array('disable_remote_import' => '1'));
$oneWayCompanionBySlug = array();
foreach ($oneWayCompanionPlugins as $oneWayCompanionPlugin) {
	if (is_array($oneWayCompanionPlugin) && !empty($oneWayCompanionPlugin ['slug'])) {
		$oneWayCompanionBySlug [(string) $oneWayCompanionPlugin ['slug']] = $oneWayCompanionPlugin;
	}
}
$oneWayTagDesc = plugin_mastodon_lang_string('companion_tag_desc_one_way', 'Exports FlatPress tags as Mastodon hashtags.');
$oneWayEmoticonsDesc = plugin_mastodon_lang_string('companion_emoticons_desc_one_way', 'Converts FlatPress Emoticons shortcodes to Unicode emoji before exporting to Mastodon.');
$allOk = test_result(
	'One-way companion-plugin diagnostics hide import-only helpers and describe export helpers',
	!isset($oneWayCompanionBySlug ['bbcode'], $oneWayCompanionBySlug ['photoswipe'], $oneWayCompanionBySlug ['audiovideo'])
		&& isset($oneWayCompanionBySlug ['tag'], $oneWayCompanionBySlug ['emoticons'])
		&& isset($oneWayCompanionBySlug ['tag'] ['description']) && $oneWayCompanionBySlug ['tag'] ['description'] === $oneWayTagDesc
		&& isset($oneWayCompanionBySlug ['emoticons'] ['description']) && $oneWayCompanionBySlug ['emoticons'] ['description'] === $oneWayEmoticonsDesc,
	json_encode($oneWayCompanionBySlug)
) && $allOk;
if (is_array($originalEnabledPlugins)) {
	$GLOBALS ['fp_plugins'] = $originalEnabledPlugins;
}

$configuredLocaleValue = plugin_mastodon_fp_config_value(array('locale', 'lang'), '');
if (!is_string($configuredLocaleValue) || $configuredLocaleValue === '') {
	$configuredLocaleValue = defined('LANG_DEFAULT') ? (string) LANG_DEFAULT : '';
}
$configuredStatusLanguage = plugin_mastodon_configured_status_language();
$expectedConfiguredLanguage = plugin_mastodon_normalize_status_language($configuredLocaleValue);

$allOk = test_result(
	'FlatPress locale is normalized to a Mastodon language code',
	plugin_mastodon_normalize_status_language('en-us') === 'en'
		&& plugin_mastodon_normalize_status_language('de_DE') === 'de'
		&& plugin_mastodon_normalize_status_language('invalid') === ''
		&& $configuredStatusLanguage === $expectedConfiguredLanguage,
	json_encode(array(
		'en-us' => plugin_mastodon_normalize_status_language('en-us'),
		'de_DE' => plugin_mastodon_normalize_status_language('de_DE'),
		'invalid' => plugin_mastodon_normalize_status_language('invalid'),
		'configured' => $configuredStatusLanguage,
		'expected' => $expectedConfiguredLanguage
	))
) && $allOk;

// Formatting tests
$mastodonHtml = '<p>Hello <strong>Fediverse</strong><br><a href="https://example.com/post">Link</a></p>';
$converted = plugin_mastodon_mastodon_html_to_flatpress($mastodonHtml);
$allOk = test_result(
	'Mastodon HTML -> FlatPress BBCode',
	strpos($converted, '[b]Fediverse[/b]') !== false && strpos($converted, '[url=https://example.com/post]Link[/url]') !== false,
	$converted
) && $allOk;

$mentionHtml = '<p><a href="https://mastodon.social/@spacesjut">@spacesjut</a></p><p>mastodon.social</p><p>If you can provide test data for an importer, please send it to my email address fraenkiman@flatpress.org.</p>';
$importOptions = plugin_mastodon_default_options();
$mentionState = plugin_mastodon_default_state();
$mentionEntryId = plugin_mastodon_import_remote_entry($importOptions, $mentionState, array(
	'id' => 'mention-test-1',
	'content' => $mentionHtml,
	'url' => 'https://mastodon.social/@spacesjut/123',
	'created_at' => '2026-03-13T12:34:56Z',
	'account' => array(
		'acct' => 'spacesjut@mastodon.social',
		'display_name' => 'Space Sjut',
		'url' => 'https://mastodon.social/@spacesjut'
	)
));
$mentionEntry = is_string($mentionEntryId) && $mentionEntryId !== '' ? entry_parse($mentionEntryId) : array();
$allOk = test_result(
	'Mastodon mention noise is cleaned for FlatPress',
	!empty($mentionEntry ['content']) && strpos($mentionEntry ['content'], '@spacesjut@mastodon.social') !== false && isset($mentionEntry ['subject']) && $mentionEntry ['subject'] === 'If you can provide test data for an importer, please send it to my email',
	trim(isset($mentionEntry ['content']) ? $mentionEntry ['content'] : '') . ' | subject=' . (isset($mentionEntry ['subject']) ? $mentionEntry ['subject'] : '')
) && $allOk;
$allOk = test_result(
	'Imported Mastodon entry keeps the Mastodon status link on a new line with target blank',
	!empty($mentionEntry ['content']) && strpos((string) $mentionEntry ['content'], "\n[url=https://mastodon.social/@spacesjut/123 target=_blank rel=\"nofollow noopener noreferrer\"]Mastodon[/url]") !== false,
	isset($mentionEntry ['content']) ? (string) $mentionEntry ['content'] : ''
) && $allOk;

$statusFooterBbcode = plugin_mastodon_imported_status_footer_bbcode('https://mastodon.social/@spacesjut/123');
$allOk = test_result(
	'Imported Mastodon status footer BBCode opens the single toot in a new tab',
	$statusFooterBbcode === '[url=https://mastodon.social/@spacesjut/123 target=_blank rel="nofollow noopener noreferrer"]Mastodon[/url]',
	$statusFooterBbcode
) && $allOk;
$statusFooterMastodonText = plugin_mastodon_flatpress_to_mastodon($statusFooterBbcode);
$allOk = test_result(
	'Imported Mastodon status footer attributes are stripped for outbound Mastodon text',
	strpos($statusFooterMastodonText, 'target=_blank') === false
		&& strpos($statusFooterMastodonText, 'rel="nofollow noopener noreferrer"') === false
		&& $statusFooterMastodonText === 'Mastodon https://mastodon.social/@spacesjut/123',
	$statusFooterMastodonText
) && $allOk;
$statusFooterHtml = function_exists('BBCode') ? BBCode($statusFooterBbcode) : '';
$allOk = test_result(
	'Imported Mastodon status footer renders target blank HTML for the single toot',
	strpos($statusFooterHtml, 'href="https://mastodon.social/@spacesjut/123"') !== false
		&& strpos($statusFooterHtml, 'target="_blank"') !== false
		&& strpos($statusFooterHtml, 'rel="nofollow noopener noreferrer"') !== false,
	$statusFooterHtml
) && $allOk;

$emojiImportHtml = '<p>Emoji 😄 <img src="https://mastodon.social/emoji/smile.png" alt=":wink:" class="emojione"></p>';
$emojiImport = plugin_mastodon_mastodon_html_to_flatpress($emojiImportHtml);
$allOk = test_result(
	'Mastodon emojis become FlatPress emoticons',
	strpos($emojiImport, ':smile:') !== false && strpos($emojiImport, ':wink:') !== false,
	$emojiImport
) && $allOk;

$flatpressText = "Title\n\n[quote]Hello[/quote]\n\nExample [url]https://example.com[/url]\n\nSmile :smile:";
$mastoText = plugin_mastodon_flatpress_to_mastodon($flatpressText);
$allOk = test_result(
	'FlatPress BBCode -> Mastodon text',
	strpos($mastoText, '> Hello') !== false && strpos($mastoText, 'Example https://example.com') !== false && strpos($mastoText, ':smile:') === false && strpos($mastoText, '😄') !== false,
	$mastoText
) && $allOk;

$bbcodeWithAttrs = "Link [url=https://www.flatpress.org target=_blank rel=external]FlatPress[/url]\n[url=static.php?page=about]About[/url]";
$mastoAttrs = plugin_mastodon_flatpress_to_mastodon($bbcodeWithAttrs);
$allOk = test_result(
	'FlatPress URLs lose HTML-like attributes and omit localhost links',
	strpos($mastoAttrs, 'target=_blank') === false && strpos($mastoAttrs, 'rel=external') === false && strpos($mastoAttrs, 'FlatPress https://www.flatpress.org') !== false && strpos($mastoAttrs, 'localhost') === false && strpos($mastoAttrs, 'About') !== false,
	$mastoAttrs
) && $allOk;

$externalUrlModifierFile = $simRoot . '/fp-includes/fp-smartyplugins/modifier.is_external_url.php';
if (is_file($externalUrlModifierFile)) {
	require_once $externalUrlModifierFile;
}
$previousFpConfigForExternalUrlTest = isset($GLOBALS ['fp_config']) && is_array($GLOBALS ['fp_config']) ? $GLOBALS ['fp_config'] : null;
if (!isset($GLOBALS ['fp_config']) || !is_array($GLOBALS ['fp_config'])) {
	$GLOBALS ['fp_config'] = array();
}
if (!isset($GLOBALS ['fp_config'] ['general']) || !is_array($GLOBALS ['fp_config'] ['general'])) {
	$GLOBALS ['fp_config'] ['general'] = array();
}
if (defined('STATIC_DIR') && defined('EXT')) {
	simulate_write_fixture_file(STATIC_DIR . 'about' . EXT, 'SUBJECT=About' . PHP_EOL . 'CONTENT=About page' . PHP_EOL);
}
$externalUrlCaseSets = array(
	'subdirectory blog base' => array(
		'base' => 'https://blog.example/flatpress/',
		'cases' => array(
			'remote Mastodon profile' => array('https://example.net/@alice', true),
			'absolute internal blog entry' => array('https://blog.example/flatpress/entry260314-080000', false),
			'absolute internal blog base without trailing slash' => array('https://blog.example/flatpress', false),
			'same host outside configured blog base' => array('https://blog.example/elsewhere', true),
			'root-relative blog URL' => array('/flatpress/entry260314-080000', false),
			'relative blog URL' => array('static.php?page=about', false),
			'non-browser mail URL' => array('mailto:alice@example.net', false),
			'protocol-relative external profile' => array('//example.net/@alice', true)
		)
	),
	'domain root blog base' => array(
		'base' => 'https://blog.example/',
		'cases' => array(
			'root blog home' => array('https://blog.example/', false),
			'root blog index entry point' => array('https://blog.example/index.php', false),
			'root pretty static page that exists' => array('https://blog.example/about/', false),
			'root pretty date entry' => array('https://blog.example/2026/03/14/tagged-entry/', false),
			'root path-info date entry' => array('https://blog.example/index.php/2026/03/14/tagged-entry/', false),
			'root get-mode date entry' => array('https://blog.example/?u=/2026/03/14/tagged-entry/', false),
			'root FlatPress-owned asset path' => array('https://blog.example/fp-plugins/mastodon/plugin.mastodon.php', false),
			'root unknown same-host app path' => array('https://blog.example/other-app/', true),
			'root unknown single segment without static page' => array('https://blog.example/shop/', true),
			'root external Mastodon profile' => array('https://example.net/@alice', true)
		)
	)
);
$externalUrlResults = array();
$externalUrlChecksOk = function_exists('smarty_modifier_is_external_url');
if ($externalUrlChecksOk) {
	foreach ($externalUrlCaseSets as $caseSetLabel => $caseSet) {
		$GLOBALS ['fp_config'] ['general'] ['www'] = (string) $caseSet ['base'];
		foreach ((array) $caseSet ['cases'] as $label => $case) {
			$urlToCheck = (string) $case [0];
			$expectedExternal = (bool) $case [1];
			$actualExternal = smarty_modifier_is_external_url($urlToCheck);
			$externalUrlResults [$caseSetLabel . ': ' . $label] = array(
				'base' => (string) $caseSet ['base'],
				'url' => $urlToCheck,
				'expected' => $expectedExternal,
				'actual' => $actualExternal
			);
			if ($actualExternal !== $expectedExternal) {
				$externalUrlChecksOk = false;
			}
		}
	}
}
if ($previousFpConfigForExternalUrlTest !== null) {
	$GLOBALS ['fp_config'] = $previousFpConfigForExternalUrlTest;
}
$commentsTemplate = @file_get_contents($simRoot . '/fp-interface/themes/leggero/comments.tpl');
if (!is_string($commentsTemplate)) {
	$commentsTemplate = '';
}
$allOk = test_result(
	'Comment author target blank is limited to external URLs',
	$externalUrlChecksOk
		&& strpos($commentsTemplate, '$url|is_external_url') !== false
		&& strpos($commentsTemplate, 'target="_blank"') !== false
		&& strpos($commentsTemplate, 'rel="nofollow noopener noreferrer"') !== false,
	json_encode($externalUrlResults)
) && $allOk;

$taggedEntry = array(
	'subject' => 'Tagged entry',
	'content' => "Body paragraph\n\n[tag]News, Neu[/tag]",
	'date' => strtotime('2026-03-14 08:00:00 UTC')
);
$taggedStatus = plugin_mastodon_build_entry_status_text('entry260314-080000', $taggedEntry, 500);
$allOk = test_result(
	'FlatPress entry tags become a Mastodon hashtag footer',
	strpos($taggedStatus, '[tag]') === false && substr($taggedStatus, -10) !== '[tag][/tag]' && strpos($taggedStatus, "\n#News #Neu") !== false,
	$taggedStatus
) && $allOk;

$remoteTagOptions = plugin_mastodon_default_options();
$remoteTagOptions ['update_local_from_remote'] = '1';
$remoteTagState = plugin_mastodon_default_state();
$remoteTaggedEntryId = plugin_mastodon_import_remote_entry($remoteTagOptions, $remoteTagState, array(
	'id' => 'tagged-remote-1',
	'content' => '<p>Remote tagged body</p><p>#News #Neu</p>',
	'url' => 'https://mastodon.social/@flatpress/777',
	'created_at' => '2026-03-14T09:00:00Z',
	'tags' => array(
		array('name' => 'News'),
		array('name' => 'Neu')
	),
	'account' => array(
		'acct' => 'flatpress@mastodon.social',
		'display_name' => 'FlatPress Bot',
		'url' => 'https://mastodon.social/@flatpress'
	)
));
$remoteTaggedEntry = is_string($remoteTaggedEntryId) && $remoteTaggedEntryId !== '' ? entry_parse($remoteTaggedEntryId) : array();
$allOk = test_result(
	'Mastodon tags become FlatPress tag BBCode without a visible hashtag footer',
	!empty($remoteTaggedEntry ['content'])
		&& strpos((string) $remoteTaggedEntry ['content'], '[tag]News, Neu[/tag]') !== false
		&& strpos((string) $remoteTaggedEntry ['content'], '#News #Neu') === false
		&& strpos((string) $remoteTaggedEntry ['content'], "\n[url=https://mastodon.social/@flatpress/777 target=_blank rel=\"nofollow noopener noreferrer\"]Mastodon[/url]") !== false,
	isset($remoteTaggedEntry ['content']) ? (string) $remoteTaggedEntry ['content'] : ''
) && $allOk;

$remoteTaggedEntryIdUpdated = plugin_mastodon_import_remote_entry($remoteTagOptions, $remoteTagState, array(
	'id' => 'tagged-remote-1',
	'content' => '<p>Remote tagged body updated</p>',
	'url' => 'https://mastodon.social/@flatpress/777',
	'created_at' => '2026-03-14T09:00:00Z',
	'edited_at' => '2026-03-14T09:05:00Z',
	'tags' => array(),
	'account' => array(
		'acct' => 'flatpress@mastodon.social',
		'display_name' => 'FlatPress Bot',
		'url' => 'https://mastodon.social/@flatpress'
	)
));
$remoteTaggedEntryUpdated = is_string($remoteTaggedEntryIdUpdated) && $remoteTaggedEntryIdUpdated !== '' ? entry_parse($remoteTaggedEntryIdUpdated) : array();
$allOk = test_result(
	'Removing Mastodon tags removes FlatPress tag BBCode on update',
	!empty($remoteTaggedEntryUpdated ['content'])
		&& strpos((string) $remoteTaggedEntryUpdated ['content'], '[tag]') === false
		&& strpos((string) $remoteTaggedEntryUpdated ['content'], 'Remote tagged body updated') !== false,
	isset($remoteTaggedEntryUpdated ['content']) ? (string) $remoteTaggedEntryUpdated ['content'] : ''
) && $allOk;

$taglessStatus = plugin_mastodon_build_entry_status_text('entry260314-080001', array(
	'subject' => 'Tagless entry',
	'content' => "Body paragraph",
	'date' => strtotime('2026-03-14 08:01:00 UTC')
), 500);
$allOk = test_result(
	'FlatPress entry without tag BBCode has no Mastodon hashtag footer',
	strpos($taglessStatus, '#') === false,
	$taglessStatus
) && $allOk;

$headOptions = plugin_mastodon_default_options();
$headOptions ['instance_url'] = 'https://mastodon.social';
$headOptions ['username'] = 'FrankHochmuth';
plugin_mastodon_save_options($headOptions);
ob_start();
plugin_mastodon_head();
$headMarkup = trim((string) ob_get_clean());
$allOk = test_result(
	'Mastodon head metadata uses the configured instance URL and username',
	strpos($headMarkup, '<link rel="me" href="https://mastodon.social/@FrankHochmuth">') !== false
		&& strpos($headMarkup, '<meta name="fediverse:creator" content="@FrankHochmuth@mastodon.social">') !== false,
	$headMarkup
) && $allOk;

$headAtOptions = $headOptions;
$headAtOptions ['username'] = '@FrankHochmuth';
plugin_mastodon_save_options($headAtOptions);
ob_start();
plugin_mastodon_head();
$headMarkupWithAt = trim((string) ob_get_clean());
$allOk = test_result(
	'Mastodon head metadata tolerates usernames stored with a leading at-sign',
	strpos($headMarkupWithAt, '<link rel="me" href="https://mastodon.social/@FrankHochmuth">') !== false
		&& strpos($headMarkupWithAt, '<meta name="fediverse:creator" content="@FrankHochmuth@mastodon.social">') !== false,
	$headMarkupWithAt
) && $allOk;

$headEmptyUserOptions = $headOptions;
$headEmptyUserOptions ['username'] = '';
plugin_mastodon_save_options($headEmptyUserOptions);
ob_start();
plugin_mastodon_head();
$headMarkupWithoutUser = trim((string) ob_get_clean());
$allOk = test_result(
	'Mastodon head metadata stays silent when no username is configured',
	$headMarkupWithoutUser === '',
	$headMarkupWithoutUser
) && $allOk;

plugin_mastodon_save_options(plugin_mastodon_default_options());

$welcomeEntryId = 'entry260410-161431';
$welcomeEntry = entry_parse($welcomeEntryId);
$commentId = 'comment260412-001722';
$welcomeComment = comment_parse($welcomeEntryId, $commentId);
$expectedCommentPrefix = sprintf(plugin_mastodon_lang_string('comment_by_format', 'Comment by %s:'), 'Frank');
$commentStatusPreview = plugin_mastodon_build_comment_status_text($welcomeEntryId, $welcomeEntry, $welcomeComment, 500);
$allOk = test_result(
	'Actual FlatPress comment exports as readable Mastodon reply',
	strpos($commentStatusPreview, $expectedCommentPrefix) !== false && strpos($commentStatusPreview, 'localhost') === false && strpos($commentStatusPreview, 'https://wiki.flatpress.org/doc:faq') !== false && strpos($commentStatusPreview, ':smile:') === false && strpos($commentStatusPreview, '😄') !== false,
	$commentStatusPreview
) && $allOk;

$welcomeCommentWithId = $welcomeComment;
$welcomeCommentWithId ['id'] = $commentId;
$welcomeCommentWithId ['public_url'] = 'https://www.flatpress.org/comments.php?entry=' . $welcomeEntryId . '#' . $commentId;
$commentStatusWithPublicLink = plugin_mastodon_build_comment_status_text($welcomeEntryId, $welcomeEntry, $welcomeCommentWithId, 500);
$allOk = test_result(
	'FlatPress comment export appends the public comment link on a new line',
	strpos($commentStatusWithPublicLink, "\nhttps://www.flatpress.org/comments.php?entry=" . $welcomeEntryId . '#' . $commentId) !== false,
	$commentStatusWithPublicLink
) && $allOk;

$emoticonEntryStatus = plugin_mastodon_build_entry_status_text('entry260314-emoji', array(
	'subject' => 'Titel :smile:',
	'content' => 'Inhalt :wink:',
	'date' => strtotime('2026-03-14 10:30:00 UTC')
), 500);
$allOk = test_result(
	'FlatPress entry export converts Emoticons plugin shortcodes in titles and bodies to Unicode emoji for Mastodon',
	strpos($emoticonEntryStatus, 'Titel 😄') !== false
		&& strpos($emoticonEntryStatus, 'Inhalt 😉') !== false
		&& strpos($emoticonEntryStatus, ':smile:') === false
		&& strpos($emoticonEntryStatus, ':wink:') === false,
	$emoticonEntryStatus
) && $allOk;

$emoticonCommentStatus = plugin_mastodon_build_comment_status_text('entry260314-emoji', array(
	'subject' => 'Titel',
	'content' => 'Body',
	'date' => strtotime('2026-03-14 10:30:00 UTC')
), array(
	'name' => 'Autor :grin:',
	'content' => 'Kommentar :joy:',
	'id' => 'comment-emoji',
	'public_url' => 'https://www.flatpress.org/comments.php?entry=entry260314-emoji#comment-emoji'
), 500);
$allOk = test_result(
	'FlatPress comment export converts Emoticons plugin shortcodes in author lines and bodies to Unicode emoji for Mastodon',
	strpos($emoticonCommentStatus, 'Autor 😁') !== false
		&& strpos($emoticonCommentStatus, 'Kommentar 😂') !== false
		&& strpos($emoticonCommentStatus, ':grin:') === false
		&& strpos($emoticonCommentStatus, ':joy:') === false,
	$emoticonCommentStatus
) && $allOk;

$localLongEntryId = 'entry260314-114108';
$localLongEntry = array(
	'subject' => 'Simulated long local entry',
	'content' => trim(str_repeat('This simulated FlatPress entry is intentionally long so that the Mastodon exporter must trim it without depending on a public permalink. ', 12)),
	'author' => 'FlatPress Bot'
);
$localLongStatus = plugin_mastodon_build_entry_status_text($localLongEntryId, $localLongEntry, 500);
$localLongStatusRawLength = function_exists('mb_strlen') ? mb_strlen($localLongStatus, 'UTF-8') : strlen($localLongStatus);
$localLongStatusMastodonLength = plugin_mastodon_status_text_length($localLongStatus, plugin_mastodon_instance_url_reserved_length(plugin_mastodon_get_options()));
$allOk = test_result(
	'Long local entry export is limited even without a public permalink',
	$localLongStatus !== '' && $localLongStatusMastodonLength <= 500 && strpos($localLongStatus, 'localhost') === false,
	json_encode(array('raw_len' => $localLongStatusRawLength, 'mastodon_len' => $localLongStatusMastodonLength))
) && $allOk;

$imageEntryId = 'entry260314-133022';
$imageEntry = entry_parse($imageEntryId);
$imageMedia = is_array($imageEntry) ? plugin_mastodon_collect_local_entry_media($imageEntry) : array();
$allOk = test_result(
	'FlatPress single-image entry exposes one uploadable media item',
	count($imageMedia) === 1 && !empty($imageMedia [0] ['absolute_path']) && is_file($imageMedia [0] ['absolute_path']),
	(string) count($imageMedia)
) && $allOk;

$galleryEntryId = 'entry260314-150101';
$galleryEntry = entry_parse($galleryEntryId);
$galleryMedia = is_array($galleryEntry) ? plugin_mastodon_collect_local_entry_media($galleryEntry) : array();
$galleryStatusPreview = is_array($galleryEntry) ? plugin_mastodon_build_entry_status_text($galleryEntryId, $galleryEntry, 500) : '';
$allOk = test_result(
	'FlatPress gallery entry exposes uploadable media items and keeps raw gallery BBCode out of Mastodon text',
	count($galleryMedia) >= 4 && strpos($galleryStatusPreview, '[gallery') === false && strpos($galleryStatusPreview, '[img=') === false,
	(string) count($galleryMedia) . ' media'
) && $allOk;

$audioVideoContent = 'Audio and video entry' . "\n\n"
	. '[audioplayer="attachs/mastodon-sim/demo-audio.mp3" controls="1"]Audio fixture from endtag[/audioplayer]' . "\n\n"
	. '[videoplayer="attachs/mastodon-sim/demo-video.mp4" controls="1" width="320" height="180" poster="images/mastodon-sim/video-poster.png"]Video fixture from endtag[/videoplayer]';
$audioVideoMedia = plugin_mastodon_collect_local_entry_media(array('content' => $audioVideoContent));
$audioVideoStatusPreview = plugin_mastodon_build_entry_status_text('entry260314-audiovideo', array(
	'subject' => 'AudioVideo media entry',
	'content' => $audioVideoContent,
	'date' => strtotime('2026-03-14 16:00:00 UTC')
), 500);
$audioVideoHtmlOk = false;
$audioVideoRenderedHtmlOk = false;
$audioVideoLegacyHtmlOk = false;
if (class_exists('AudioVideoPlugin')) {
	$audioHtml = AudioVideoPlugin::getAudioHtml('', array('default' => 'attachs/mastodon-sim/demo-audio.mp3', 'controls' => '1'), 'Audio fixture from endtag', array(), null);
	$videoHtml = AudioVideoPlugin::getVideoHtml('', array('default' => 'attachs/mastodon-sim/demo-video.mp4', 'controls' => '1', 'poster' => 'images/mastodon-sim/video-poster.png'), 'Video fixture from endtag', array(), null);
	$audioVideoHtmlOk = is_string($audioHtml) && is_string($videoHtml)
		&& strpos($audioHtml, 'aria-label="Audio fixture from endtag"') !== false
		&& strpos($audioHtml, 'title="Audio fixture from endtag"') !== false
		&& strpos($videoHtml, 'aria-label="Video fixture from endtag"') !== false
		&& strpos($videoHtml, 'title="Video fixture from endtag"') !== false
		&& strpos($videoHtml, 'poster="') !== false;
	if (function_exists('BBCode')) {
		AudioVideoPlugin::initializePluginTags();
		$renderedAudioHtml = BBCode('[audioplayer="attachs/mastodon-sim/demo-audio.mp3" controls="1"]Audio fixture from endtag[/audioplayer]');
		$renderedVideoHtml = BBCode('[videoplayer="attachs/mastodon-sim/demo-video.mp4" controls="1" width="320" height="180" poster="images/mastodon-sim/video-poster.png"]Video fixture from endtag[/videoplayer]');
		$legacyAudioHtml = BBCode('[audioplayer="attachs/mastodon-sim/demo-audio.mp3" controls="1"]');
		$legacyVideoHtml = BBCode('[videoplayer="attachs/mastodon-sim/demo-video.mp4" controls="1" width="320" height="180"]');
		$multiPlayerHtml = BBCode('[videoplayer="attachs/mastodon-sim/demo-video.mp4"]' . "\n\n"
			. '[videoplayer="attachs/mastodon-sim/demo-video.mp4" controls="1" width="320" height="180" poster="images/mastodon-sim/video-poster.png"]Video fixture from endtag[/videoplayer]' . "\n\n"
			. '[audioplayer="attachs/mastodon-sim/demo-audio.mp3"]' . "\n\n"
			. '[audioplayer="attachs/mastodon-sim/demo-audio.mp3" controls="1"]Audio fixture from endtag[/audioplayer]');
		$audioVideoRenderedHtmlOk = strpos($renderedAudioHtml, 'aria-label="Audio fixture from endtag"') !== false
			&& strpos($renderedAudioHtml, 'title="Audio fixture from endtag"') !== false
			&& strpos($renderedAudioHtml, '[/audioplayer]') === false
			&& strpos($renderedVideoHtml, 'aria-label="Video fixture from endtag"') !== false
			&& strpos($renderedVideoHtml, 'title="Video fixture from endtag"') !== false
			&& strpos($renderedVideoHtml, '[/videoplayer]') === false
			&& substr_count($multiPlayerHtml, '<video') === 2
			&& substr_count($multiPlayerHtml, '<audio') === 2
			&& strpos($multiPlayerHtml, 'aria-label="Video fixture from endtag"') !== false
			&& strpos($multiPlayerHtml, 'aria-label="Audio fixture from endtag"') !== false
			&& strpos($multiPlayerHtml, '[/videoplayer]') === false
			&& strpos($multiPlayerHtml, '[/audioplayer]') === false;
		$audioVideoLegacyHtmlOk = strpos($legacyAudioHtml, '<audio class="audiovideo"') !== false
			&& strpos($legacyVideoHtml, '<video class="audiovideo"') !== false;
	}
}
$allOk = test_result(
	'FlatPress AudioVideo entry exposes described audio/video media items and strips raw player BBCode from Mastodon text',
	count($audioVideoMedia) === 2
		&& isset($audioVideoMedia [0] ['media_type'], $audioVideoMedia [1] ['media_type'])
		&& (string) $audioVideoMedia [0] ['media_type'] === 'audio'
		&& (string) $audioVideoMedia [1] ['media_type'] === 'video'
		&& (string) $audioVideoMedia [0] ['description'] === 'Audio fixture from endtag'
		&& (string) $audioVideoMedia [1] ['description'] === 'Video fixture from endtag'
		&& !empty($audioVideoMedia [1] ['thumbnail_absolute_path'])
		&& is_file((string) $audioVideoMedia [1] ['thumbnail_absolute_path'])
		&& strpos($audioVideoStatusPreview, '[audioplayer') === false
		&& strpos($audioVideoStatusPreview, '[videoplayer') === false
		&& strpos($audioVideoStatusPreview, '[/audioplayer]') === false
		&& strpos($audioVideoStatusPreview, '[/videoplayer]') === false
		&& strpos($audioVideoStatusPreview, 'Audio fixture from endtag') === false
		&& strpos($audioVideoStatusPreview, 'Video fixture from endtag') === false
		&& $audioVideoHtmlOk,
	json_encode(array('media' => $audioVideoMedia, 'status' => $audioVideoStatusPreview, 'html_ok' => $audioVideoHtmlOk))
) && $allOk;

$allOk = test_result(
	'AudioVideo BBCode parser renders optional description endtags as media attributes and keeps legacy single tags working',
	$audioVideoRenderedHtmlOk && $audioVideoLegacyHtmlOk,
	json_encode(array('rendered_html_ok' => $audioVideoRenderedHtmlOk, 'legacy_html_ok' => $audioVideoLegacyHtmlOk))
) && $allOk;

$audioVideoUploadOptions = plugin_mastodon_default_options();
$audioVideoUploadOptions ['instance_url'] = 'https://mastodon-audiovideo-upload.example';
$audioVideoUploadOptions ['access_token'] = 'token-audiovideo-upload';
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'POST ' . $audioVideoUploadOptions ['instance_url'] . '/api/v2/media' => array(
		array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array(
				'id' => 'audio-upload-1',
				'type' => 'audio',
				'url' => $audioVideoUploadOptions ['instance_url'] . '/media/audio-upload-1.mp3'
			))
		),
		array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array(
				'id' => 'video-upload-1',
				'type' => 'video',
				'url' => $audioVideoUploadOptions ['instance_url'] . '/media/video-upload-1.mp4'
			))
		)
	)
);
$audioVideoUploadResult = plugin_mastodon_upload_media_items($audioVideoUploadOptions, $audioVideoMedia, 4);
$audioVideoUploadRequests = simulate_multipart_http_requests();
$audioUploadMultipart = isset($audioVideoUploadRequests [0] ['multipart']) && is_array($audioVideoUploadRequests [0] ['multipart']) ? $audioVideoUploadRequests [0] ['multipart'] : array();
$videoUploadMultipart = isset($audioVideoUploadRequests [1] ['multipart']) && is_array($audioVideoUploadRequests [1] ['multipart']) ? $audioVideoUploadRequests [1] ['multipart'] : array();
$allOk = test_result(
	'Mastodon media upload sends AudioVideo files with descriptions and video thumbnail',
	!empty($audioVideoUploadResult ['ok'])
		&& isset($audioVideoUploadResult ['media_ids'] [0], $audioVideoUploadResult ['media_ids'] [1])
		&& (string) $audioVideoUploadResult ['media_ids'] [0] === 'audio-upload-1'
		&& (string) $audioVideoUploadResult ['media_ids'] [1] === 'video-upload-1'
		&& isset($audioUploadMultipart ['description'])
		&& (string) $audioUploadMultipart ['description'] === 'Audio fixture from endtag'
		&& isset($videoUploadMultipart ['description'])
		&& (string) $videoUploadMultipart ['description'] === 'Video fixture from endtag'
		&& !empty($videoUploadMultipart ['thumbnail'] ['__file_path']),
	json_encode(array(
		'upload' => $audioVideoUploadResult,
		'audio_multipart' => $audioUploadMultipart,
		'video_multipart' => $videoUploadMultipart
	))
) && $allOk;

$mediaSelectionOptions = plugin_mastodon_default_options();
$mixedImageAudioVideoContent = 'Mixed media policy entry' . "\n\n"
	. '[videoplayer="attachs/mastodon-sim/demo-video.mp4" controls="1" width="320" height="180" poster="images/mastodon-sim/video-poster.png"]Video fixture from endtag[/videoplayer]' . "\n\n"
	. '[audioplayer="attachs/mastodon-sim/demo-audio.mp3" controls="1"]Audio fixture from endtag[/audioplayer]' . "\n\n"
	. '[img=images/mastodon-sim/single-image.jpg width="320" title="Image wins over audio and video"]';
$mixedImageAudioVideoMedia = plugin_mastodon_collect_local_entry_media(array('content' => $mixedImageAudioVideoContent));
$mixedImageAudioVideoPlan = plugin_mastodon_prepare_entry_media_sync_plan($mediaSelectionOptions, array(), $mixedImageAudioVideoMedia, 4);
$allOk = test_result(
	'Mastodon media planner exports only images when an entry mixes image, audio and video media',
	isset($mixedImageAudioVideoPlan ['media_items'] [0] ['media_type'])
		&& count($mixedImageAudioVideoPlan ['media_items']) === 1
		&& (string) $mixedImageAudioVideoPlan ['media_items'] [0] ['media_type'] === 'image'
		&& isset($mixedImageAudioVideoPlan ['skipped'])
		&& (int) $mixedImageAudioVideoPlan ['skipped'] === 2,
	json_encode(array('plan' => $mixedImageAudioVideoPlan, 'collected' => $mixedImageAudioVideoMedia))
) && $allOk;

$audioVideoPlan = plugin_mastodon_prepare_entry_media_sync_plan($mediaSelectionOptions, array(), $audioVideoMedia, 4);
$allOk = test_result(
	'Mastodon media planner exports exactly one audio attachment when an entry mixes audio and video media',
	isset($audioVideoPlan ['media_items'] [0] ['media_type'])
		&& count($audioVideoPlan ['media_items']) === 1
		&& (string) $audioVideoPlan ['media_items'] [0] ['media_type'] === 'audio'
		&& (string) $audioVideoPlan ['media_items'] [0] ['description'] === 'Audio fixture from endtag'
		&& isset($audioVideoPlan ['skipped'])
		&& (int) $audioVideoPlan ['skipped'] === 1,
	json_encode($audioVideoPlan)
) && $allOk;

$videoOnlyContent = '[videoplayer="attachs/mastodon-sim/demo-video.mp4" controls="1" width="320" height="180" poster="images/mastodon-sim/video-poster.png"]Video fixture from endtag[/videoplayer]';
$videoOnlyMedia = plugin_mastodon_collect_local_entry_media(array('content' => $videoOnlyContent));
$videoOnlyPlan = plugin_mastodon_prepare_entry_media_sync_plan($mediaSelectionOptions, array(), $videoOnlyMedia, 4);
$allOk = test_result(
	'Mastodon media planner exports one video and keeps its poster as an upload thumbnail only',
	isset($videoOnlyPlan ['media_items'] [0] ['media_type'])
		&& count($videoOnlyPlan ['media_items']) === 1
		&& (string) $videoOnlyPlan ['media_items'] [0] ['media_type'] === 'video'
		&& !empty($videoOnlyPlan ['media_items'] [0] ['thumbnail_absolute_path'])
		&& empty($videoOnlyPlan ['media_items'] [1])
		&& isset($videoOnlyPlan ['skipped'])
		&& (int) $videoOnlyPlan ['skipped'] === 0,
	json_encode($videoOnlyPlan)
) && $allOk;

$galleryLimitTwoPlan = plugin_mastodon_prepare_entry_media_sync_plan($mediaSelectionOptions, array(), $galleryMedia, 2);
$allOk = test_result(
	'Mastodon media planner keeps multiple images up to the Mastodon media limit',
	isset($galleryLimitTwoPlan ['media_items'] [0] ['media_type'], $galleryLimitTwoPlan ['media_items'] [1] ['media_type'])
		&& count($galleryLimitTwoPlan ['media_items']) === 2
		&& (string) $galleryLimitTwoPlan ['media_items'] [0] ['media_type'] === 'image'
		&& (string) $galleryLimitTwoPlan ['media_items'] [1] ['media_type'] === 'image'
		&& isset($galleryLimitTwoPlan ['skipped'])
		&& (int) $galleryLimitTwoPlan ['skipped'] === max(0, count($galleryMedia) - 2),
	json_encode(array('plan' => $galleryLimitTwoPlan, 'gallery_count' => count($galleryMedia)))
) && $allOk;

$audioSelectedUploadOptions = plugin_mastodon_default_options();
$audioSelectedUploadOptions ['instance_url'] = 'https://mastodon-audio-selection.example';
$audioSelectedUploadOptions ['access_token'] = 'token-audio-selection';
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'POST ' . $audioSelectedUploadOptions ['instance_url'] . '/api/v2/media' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			'id' => 'selected-audio-upload-1',
			'type' => 'audio',
			'url' => $audioSelectedUploadOptions ['instance_url'] . '/media/selected-audio-upload-1.mp3'
		))
	)
);
$audioSelectedUpload = plugin_mastodon_upload_media_items($audioSelectedUploadOptions, $audioVideoPlan ['media_items'], count($audioVideoPlan ['media_items']));
$audioSelectedMultipartRequests = simulate_multipart_http_requests();
$audioSelectedMultipart = isset($audioSelectedMultipartRequests [0] ['multipart']) && is_array($audioSelectedMultipartRequests [0] ['multipart']) ? $audioSelectedMultipartRequests [0] ['multipart'] : array();
$allOk = test_result(
	'Mastodon export uploads only the selected audio item from an audio/video FlatPress entry',
	!empty($audioSelectedUpload ['ok'])
		&& isset($audioSelectedUpload ['media_ids'] [0])
		&& (string) $audioSelectedUpload ['media_ids'] [0] === 'selected-audio-upload-1'
		&& count($audioSelectedMultipartRequests) === 1
		&& isset($audioSelectedMultipart ['description'])
		&& (string) $audioSelectedMultipart ['description'] === 'Audio fixture from endtag'
		&& empty($audioSelectedMultipart ['thumbnail']),
	json_encode(array('upload' => $audioSelectedUpload, 'requests' => $audioSelectedMultipartRequests))
) && $allOk;

$videoSelectedUploadOptions = plugin_mastodon_default_options();
$videoSelectedUploadOptions ['instance_url'] = 'https://mastodon-video-selection.example';
$videoSelectedUploadOptions ['access_token'] = 'token-video-selection';
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'POST ' . $videoSelectedUploadOptions ['instance_url'] . '/api/v2/media' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			'id' => 'selected-video-upload-1',
			'type' => 'video',
			'url' => $videoSelectedUploadOptions ['instance_url'] . '/media/selected-video-upload-1.mp4'
		))
	)
);
$videoSelectedUpload = plugin_mastodon_upload_media_items($videoSelectedUploadOptions, $videoOnlyPlan ['media_items'], count($videoOnlyPlan ['media_items']));
$videoSelectedMultipartRequests = simulate_multipart_http_requests();
$videoSelectedMultipart = isset($videoSelectedMultipartRequests [0] ['multipart']) && is_array($videoSelectedMultipartRequests [0] ['multipart']) ? $videoSelectedMultipartRequests [0] ['multipart'] : array();
$allOk = test_result(
	'Mastodon export sends a video poster as upload thumbnail instead of a second media ID',
	!empty($videoSelectedUpload ['ok'])
		&& isset($videoSelectedUpload ['media_ids'] [0])
		&& (string) $videoSelectedUpload ['media_ids'] [0] === 'selected-video-upload-1'
		&& count($videoSelectedMultipartRequests) === 1
		&& isset($videoSelectedMultipart ['description'])
		&& (string) $videoSelectedMultipart ['description'] === 'Video fixture from endtag'
		&& !empty($videoSelectedMultipart ['thumbnail'] ['__file_path']),
	json_encode(array('upload' => $videoSelectedUpload, 'requests' => $videoSelectedMultipartRequests))
) && $allOk;

simulate_delete_recursive($simRoot . '/fp-content/images/mastodon');
$GLOBALS ['plugin_mastodon_test_http_responses'] ['GET https://files.example/single.jpg'] = array(
	'ok' => true,
	'code' => 200,
	'headers' => array('content-type' => 'image/jpeg'),
	'body' => 'jpeg-data-1'
);
$remoteSingleMediaOptions = plugin_mastodon_default_options();
$remoteSingleMediaOptions ['instance_url'] = 'https://mastodon.example';
$singleMediaBbcode = plugin_mastodon_build_imported_media_bbcode($remoteSingleMediaOptions, array(
	'id' => 'single-media',
	'media_attachments' => array(
		array(
			'id' => 'att1',
			'type' => 'image',
			'url' => 'https://files.example/single.jpg',
			'description' => 'Remote image alt text'
		)
	)
));
$allOk = test_result(
	'Mastodon single image imports into a FlatPress image tag and stored file',
	strpos($singleMediaBbcode, '[img=images/mastodon/status-single-media/') !== false && is_file($simRoot . '/fp-content/images/mastodon/status-single-media/01-single.jpg'),
	$singleMediaBbcode
) && $allOk;

$longRemoteMediaDescription = substr(str_repeat('Lange Mastodon-Medienbeschreibung ', 400), 0, 10000);
$GLOBALS ['plugin_mastodon_test_http_responses'] ['GET https://files.example/long-media-description.jpg'] = array(
	'ok' => true,
	'code' => 200,
	'headers' => array('content-type' => 'image/jpeg'),
	'body' => 'jpeg-data-long-description'
);
$longMediaBbcode = plugin_mastodon_build_imported_media_bbcode($remoteSingleMediaOptions, array(
	'id' => 'long-media-description',
	'media_attachments' => array(
		array(
			'id' => 'att-long-description',
			'type' => 'image',
			'url' => 'https://files.example/long-media-description.jpg',
			'description' => $longRemoteMediaDescription
		)
	)
));
$allOk = test_result(
	'Mastodon import preserves long remote media descriptions without corrupting FlatPress image markup',
	strpos($longMediaBbcode, '[img=images/mastodon/status-long-media-description/01-long-media-description.jpg') !== false
		&& strpos($longMediaBbcode, 'title="') !== false
		&& strlen($longMediaBbcode) > 10000
		&& is_file($simRoot . '/fp-content/images/mastodon/status-long-media-description/01-long-media-description.jpg'),
	json_encode(array(
		'bbcode_length' => strlen($longMediaBbcode),
		'description_length' => strlen($longRemoteMediaDescription),
		'stored' => is_file($simRoot . '/fp-content/images/mastodon/status-long-media-description/01-long-media-description.jpg')
	))
) && $allOk;

$GLOBALS ['plugin_mastodon_test_http_responses'] ['GET https://files.example/gallery-a.png'] = array(
	'ok' => true,
	'code' => 200,
	'headers' => array('content-type' => 'image/png'),
	'body' => 'png-data-a'
);
$GLOBALS ['plugin_mastodon_test_http_responses'] ['GET https://files.example/gallery-b.png'] = array(
	'ok' => true,
	'code' => 200,
	'headers' => array('content-type' => 'image/png'),
	'body' => 'png-data-b'
);
$remoteGalleryMediaOptions = plugin_mastodon_default_options();
$remoteGalleryMediaOptions ['instance_url'] = 'https://mastodon.example';
$galleryMediaBbcode = plugin_mastodon_build_imported_media_bbcode($remoteGalleryMediaOptions, array(
	'id' => 'gallery-media',
	'media_attachments' => array(
		array(
			'id' => 'att2',
			'type' => 'image',
			'url' => 'https://files.example/gallery-a.png',
			'description' => 'Caption A'
		),
		array(
			'id' => 'att3',
			'type' => 'image',
			'url' => 'https://files.example/gallery-b.png',
			'description' => 'Caption B'
		)
	)
));
$allOk = test_result(
	'Mastodon multiple images import into a FlatPress gallery with captions',
	strpos($galleryMediaBbcode, '[gallery=images/mastodon/status-gallery-media width=' . PLUGIN_MASTODON_IMPORTED_MEDIA_WIDTH . ']') !== false
		&& is_file($simRoot . '/fp-content/images/mastodon/status-gallery-media/01-gallery-a.png')
		&& is_file($simRoot . '/fp-content/images/mastodon/status-gallery-media/02-gallery-b.png')
		&& is_file($simRoot . '/fp-content/images/mastodon/status-gallery-media/.captions.conf'),
	$galleryMediaBbcode
) && $allOk;

simulate_delete_recursive($simRoot . '/fp-content/attachs/mastodon');
simulate_delete_recursive($simRoot . '/fp-content/images/mastodon/status-audiovideo-media');
$GLOBALS ['plugin_mastodon_test_http_responses'] ['GET https://files.example/remote-audio.mp3'] = array(
	'ok' => true,
	'code' => 200,
	'headers' => array('content-type' => 'audio/mpeg'),
	'body' => 'remote-audio-data'
);
$GLOBALS ['plugin_mastodon_test_http_responses'] ['GET https://files.example/remote-video.mp4'] = array(
	'ok' => true,
	'code' => 200,
	'headers' => array('content-type' => 'video/mp4'),
	'body' => 'remote-video-data'
);
$GLOBALS ['plugin_mastodon_test_http_responses'] ['GET https://files.example/remote-video-poster.jpg'] = array(
	'ok' => true,
	'code' => 200,
	'headers' => array('content-type' => 'image/jpeg'),
	'body' => 'remote-poster-data'
);
$remoteAudioVideoOptions = plugin_mastodon_default_options();
$remoteAudioVideoOptions ['instance_url'] = 'https://mastodon.example';
$audioVideoMediaBbcode = plugin_mastodon_build_imported_media_bbcode($remoteAudioVideoOptions, array(
	'id' => 'audiovideo-media',
	'media_attachments' => array(
		array(
			'id' => 'att-audio',
			'type' => 'audio',
			'url' => 'https://files.example/remote-audio.mp3',
			'description' => 'Remote audio description'
		),
		array(
			'id' => 'att-video',
			'type' => 'video',
			'url' => 'https://files.example/remote-video.mp4',
			'preview_url' => 'https://files.example/remote-video-poster.jpg',
			'description' => 'Remote video description'
		)
	)
));
$allOk = test_result(
	'Mastodon audio and video attachments import into FlatPress AudioVideo BBCode and stored files',
	strpos($audioVideoMediaBbcode, '[audioplayer="attachs/mastodon/status-audiovideo-media/01-remote-audio.mp3" controls="1"]Remote audio description[/audioplayer]') !== false
		&& strpos($audioVideoMediaBbcode, '[videoplayer="attachs/mastodon/status-audiovideo-media/02-remote-video.mp4" controls="1" poster="images/mastodon/status-audiovideo-media/02-remote-video-poster.jpg"]Remote video description[/videoplayer]') !== false
		&& is_file($simRoot . '/fp-content/attachs/mastodon/status-audiovideo-media/01-remote-audio.mp3')
		&& is_file($simRoot . '/fp-content/attachs/mastodon/status-audiovideo-media/02-remote-video.mp4')
		&& is_file($simRoot . '/fp-content/images/mastodon/status-audiovideo-media/02-remote-video-poster.jpg'),
	$audioVideoMediaBbcode
) && $allOk;

simulate_delete_recursive($simRoot . '/fp-content/attachs/mastodon/status-audiovideo-fallback');
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] ['GET https://files.example/unavailable-video.mp4'] = array(
	'ok' => false,
	'code' => 503,
	'headers' => array(),
	'body' => '',
	'error' => 'storage_unavailable'
);
$GLOBALS ['plugin_mastodon_test_http_responses'] ['GET https://cdn.example/fallback-video.mp4'] = array(
	'ok' => true,
	'code' => 200,
	'headers' => array('content-type' => 'video/mp4'),
	'body' => 'fallback-video-data'
);
$audioVideoFallbackBbcode = plugin_mastodon_build_imported_media_bbcode($remoteAudioVideoOptions, array(
	'id' => 'audiovideo-fallback',
	'media_attachments' => array(
		array(
			'id' => 'att-video-fallback',
			'type' => 'video',
			'url' => 'https://files.example/unavailable-video.mp4',
			'remote_url' => 'https://cdn.example/fallback-video.mp4',
			'description' => 'Remote fallback video description'
		)
	)
));
$fallbackVideoPath = $simRoot . '/fp-content/attachs/mastodon/status-audiovideo-fallback/01-fallback-video.mp4';
$fallbackVideoBody = is_file($fallbackVideoPath) ? file_get_contents($fallbackVideoPath) : '';
$allOk = test_result(
	'Mastodon audio/video import retries alternate direct media URLs when the first download candidate fails',
	strpos($audioVideoFallbackBbcode, '[videoplayer="attachs/mastodon/status-audiovideo-fallback/01-fallback-video.mp4" controls="1"]') !== false
		&& strpos($audioVideoFallbackBbcode, 'Remote fallback video description[/videoplayer]') !== false
		&& $fallbackVideoBody === 'fallback-video-data'
		&& count(simulate_recorded_http_requests()) === 2,
	json_encode(array('bbcode' => $audioVideoFallbackBbcode, 'requests' => simulate_recorded_http_requests()))
) && $allOk;

// Scheduling tests

$options = plugin_mastodon_default_options();
$options ['sync_time'] = '23:00';
$state = plugin_mastodon_default_state();
$allOk = test_result('First sync is immediately due', plugin_mastodon_sync_due($options, $state, strtotime('2026-03-13 12:00:00'))) && $allOk;

$state ['last_run'] = '2026-03-12 23:10:00';
$allOk = test_result('Next day after 23:00 is due', plugin_mastodon_sync_due($options, $state, strtotime('2026-03-13 23:05:00'))) && $allOk;
$allOk = test_result('Before configured time is not due', !plugin_mastodon_sync_due($options, $state, strtotime('2026-03-13 22:59:00'))) && $allOk;

$originalTimezoneForScheduler = date_default_timezone_get();
$timezoneIndependentOptions = plugin_mastodon_default_options();
$timezoneIndependentOptions ['sync_time'] = '01:00';
$timezoneIndependentState = plugin_mastodon_default_state();
$timezoneIndependentState ['last_run'] = '2026-05-31 01:10:00';
$timezoneIndependenceChecks = array();
foreach (array('UTC', 'Europe/Berlin', 'America/New_York', 'Asia/Kolkata') as $timezoneName) {
	date_default_timezone_set($timezoneName);
	$timezoneIndependenceChecks [$timezoneName] = array(
		'before' => plugin_mastodon_sync_due($timezoneIndependentOptions, $timezoneIndependentState, gmmktime(0, 59, 0, 6, 1, 2026)),
		'at' => plugin_mastodon_sync_due($timezoneIndependentOptions, $timezoneIndependentState, gmmktime(1, 0, 0, 6, 1, 2026)),
		'same_day' => plugin_mastodon_sync_due($timezoneIndependentOptions, array('last_run' => '2026-06-01 01:05:00'), gmmktime(23, 0, 0, 6, 1, 2026))
	);
}
date_default_timezone_set($originalTimezoneForScheduler);
$timezoneIndependentSyncOk = true;
foreach ($timezoneIndependenceChecks as $timezoneCheck) {
	$timezoneIndependentSyncOk = $timezoneIndependentSyncOk && !$timezoneCheck ['before'] && $timezoneCheck ['at'] && !$timezoneCheck ['same_day'];
}
$allOk = test_result(
	'Scheduled synchronization due checks use stored UTC time regardless of PHP default timezone',
	$timezoneIndependentSyncOk,
	json_encode($timezoneIndependenceChecks)
) && $allOk;

$timezoneIndependentDeletionState = plugin_mastodon_default_state();
$timezoneIndependentDeletionState ['deletions_pending'] = 1;
$timezoneIndependentDeletionState ['deletions_not_before'] = '2026-06-01 01:05:00';
$timezoneIndependentDeletionChecks = array();
foreach (array('UTC', 'Europe/Berlin', 'America/New_York', 'Asia/Kolkata') as $timezoneName) {
	date_default_timezone_set($timezoneName);
	$timezoneIndependentDeletionChecks [$timezoneName] = array(
		'before' => plugin_mastodon_deletion_sync_due($timezoneIndependentDeletionState, gmmktime(1, 4, 59, 6, 1, 2026)),
		'at' => plugin_mastodon_deletion_sync_due($timezoneIndependentDeletionState, gmmktime(1, 5, 0, 6, 1, 2026))
	);
}
date_default_timezone_set($originalTimezoneForScheduler);
$timezoneIndependentDeletionOk = true;
foreach ($timezoneIndependentDeletionChecks as $timezoneCheck) {
	$timezoneIndependentDeletionOk = $timezoneIndependentDeletionOk && !$timezoneCheck ['before'] && $timezoneCheck ['at'];
}
$allOk = test_result(
	'Pending deletion synchronization due checks use UTC cooldowns regardless of PHP default timezone',
	$timezoneIndependentDeletionOk,
	json_encode($timezoneIndependentDeletionChecks)
) && $allOk;

$originalFpConfigForAdminDate = $GLOBALS ['fp_config'];
$originalEarlyFpConfigForAdminDate = $GLOBALS ['EARLY_FP_CONFIG'];
$adminDateConfig = $originalFpConfigForAdminDate;
if (!isset($adminDateConfig ['locale']) || !is_array($adminDateConfig ['locale'])) {
	$adminDateConfig ['locale'] = array();
}
$adminDateConfig ['locale'] ['timeoffset'] = 2;
$adminDateConfig ['locale'] ['dateformat'] = '%Y-%m-%d';
$adminDateConfig ['locale'] ['timeformat'] = '%H:%M';
$GLOBALS ['fp_config'] = $adminDateConfig;
$GLOBALS ['EARLY_FP_CONFIG'] = $adminDateConfig;
plugin_mastodon_runtime_cache_clear('core');
$adminDateTimezoneChecks = array();
foreach (array('UTC', 'Europe/Berlin', 'America/New_York', 'Asia/Kolkata') as $timezoneName) {
	date_default_timezone_set($timezoneName);
	$adminDateTimezoneChecks [$timezoneName] = plugin_mastodon_format_admin_datetime('2026-06-01 01:00:00');
}
date_default_timezone_set($originalTimezoneForScheduler);
$GLOBALS ['fp_config'] = $originalFpConfigForAdminDate;
$GLOBALS ['EARLY_FP_CONFIG'] = $originalEarlyFpConfigForAdminDate;
plugin_mastodon_runtime_cache_clear('core');
$adminDateTimezoneOk = true;
foreach ($adminDateTimezoneChecks as $formattedValue) {
	$adminDateTimezoneOk = $adminDateTimezoneOk && strpos((string) $formattedValue, '2026-06-01 03:00') !== false && strpos((string) $formattedValue, 'UTC+02:00') !== false;
}
$allOk = test_result(
	'Admin state timestamps are formatted with FlatPress timeoffset regardless of PHP default timezone',
	$adminDateTimezoneOk,
	json_encode($adminDateTimezoneChecks)
) && $allOk;

$guardNow = strtotime('2026-03-13 23:05:00');
plugin_mastodon_sync_guard_clear('content', $guardNow);
$guardMarked = plugin_mastodon_sync_guard_mark('content', 'simulation_guard_test', $guardNow);
$guardActive = plugin_mastodon_sync_guard_active('content', $guardNow + 60);
$guardCleared = plugin_mastodon_sync_guard_clear('content', $guardNow + 120);
$guardActiveAfterClear = plugin_mastodon_sync_guard_active('content', $guardNow + 121);
$guardMarkedAgain = plugin_mastodon_sync_guard_mark('content', 'simulation_guard_test_expiry', $guardNow);
$guardExpired = plugin_mastodon_sync_guard_active('content', $guardNow + PLUGIN_MASTODON_COOLDOWN_TTL + 1);
plugin_mastodon_sync_guard_clear('content', $guardNow + PLUGIN_MASTODON_COOLDOWN_TTL + 1);
$allOk = test_result(
	'Scheduled content synchronization uses a short file/APCu cooldown guard',
	$guardMarked && $guardActive && $guardCleared && !$guardActiveAfterClear && $guardMarkedAgain && !$guardExpired,
	json_encode(array(
		'marked' => $guardMarked,
		'active' => $guardActive,
		'cleared' => $guardCleared,
		'active_after_clear' => $guardActiveAfterClear,
		'marked_again' => $guardMarkedAgain,
		'expired' => $guardExpired
	))
) && $allOk;

$fallbackState = plugin_mastodon_default_state();
$fallbackState ['last_run'] = '2026-03-13 23:05:00';
$fallbackStored = plugin_mastodon_state_fallback_store($fallbackState);
$fallbackLoaded = plugin_mastodon_state_fallback_read();
$allOk = test_result(
	'Full Mastodon state fallback is not stored in APCu',
	!$fallbackStored && $fallbackLoaded === array(),
	json_encode(array('stored' => $fallbackStored, 'loaded' => $fallbackLoaded))
) && $allOk;

$rateLimitOptions = plugin_mastodon_default_options();
$rateLimitOptions ['instance_url'] = 'https://mastodon.example';
$rateLimitOptions ['access_token'] = 'token123';
$rateLimitOptions ['instance_info_url'] = 'https://mastodon.example';
$rateLimitInstanceInfoJson = json_encode(array(
	'version' => '4.4.0',
	'configuration' => array(
		'statuses' => array('max_media_attachments' => 4),
		'media_attachments' => array('description_limit' => 1500)
	)
));
$rateLimitOptions ['instance_info_json'] = is_string($rateLimitInstanceInfoJson) ? $rateLimitInstanceInfoJson : '';
@unlink(PLUGIN_MASTODON_LOG_FILE);
$GLOBALS ['plugin_mastodon_test_rate_limit_budgets'] = array('requests' => 1, 'media_uploads' => 24, 'deletes' => 24, 'remote_remaining_floor' => 0);
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET https://mastodon.example/api/v2/instance' => array('ok' => true, 'code' => 200, 'headers' => array('X-RateLimit-Remaining' => '299'), 'body' => '{"version":"4.4.0"}')
);
plugin_mastodon_rate_limit_guard_start('simulation_request_budget');
$rateFirst = plugin_mastodon_mastodon_json($rateLimitOptions, 'GET', '/api/v2/instance', array(), false);
$rateSecond = plugin_mastodon_mastodon_json($rateLimitOptions, 'GET', '/api/v2/instance', array(), false);
$rateSummary = plugin_mastodon_rate_limit_guard_summary();
plugin_mastodon_rate_limit_guard_stop();
$rateRequests = simulate_recorded_http_requests();
$rateLimitLog = @file_get_contents(PLUGIN_MASTODON_LOG_FILE);
$rateLimitLogText = is_string($rateLimitLog) ? $rateLimitLog : '';
$allOk = test_result(
	'Central Mastodon rate-limit guard stops requests after the per-run request budget',
	!empty($rateFirst ['ok'])
		&& empty($rateSecond ['ok'])
		&& isset($rateSecond ['code']) && (int) $rateSecond ['code'] === 429
		&& isset($rateSummary ['blocked_reason']) && $rateSummary ['blocked_reason'] === 'rate_limit_request_budget_exhausted'
		&& count($rateRequests) === 1
		&& strpos($rateLimitLogText, 'rate_limit_request_budget_exhausted') !== false,
	json_encode(array('second' => $rateSecond, 'summary' => $rateSummary, 'requests' => $rateRequests, 'log' => $rateLimitLogText))
) && $allOk;

$GLOBALS ['plugin_mastodon_test_rate_limit_budgets'] = array('requests' => 10, 'media_uploads' => 1, 'deletes' => 24, 'remote_remaining_floor' => 0);
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'POST https://mastodon.example/api/v2/media' => array('ok' => true, 'code' => 200, 'headers' => array('X-RateLimit-Remaining' => '298'), 'body' => '{"id":"media-budget-1","type":"image","url":"https://files.example/media-budget-1.jpg"}'),
	'DELETE https://mastodon.example/api/v1/media/media-budget-1' => array('ok' => true, 'code' => 200, 'headers' => array('X-RateLimit-Remaining' => '297'), 'body' => '{}')
);
plugin_mastodon_rate_limit_guard_start('simulation_media_upload_budget');
$mediaBudgetItems = array(
	array('absolute_path' => ABS_PATH . FP_CONTENT . 'images/mastodon-sim/single-image.jpg', 'mime_type' => 'image/jpeg', 'media_type' => 'image'),
	array('absolute_path' => ABS_PATH . FP_CONTENT . 'images/mastodon-sim/gallery/01-gallery.png', 'mime_type' => 'image/png', 'media_type' => 'image')
);
$mediaBudgetResult = plugin_mastodon_upload_media_items($rateLimitOptions, $mediaBudgetItems, 2);
$mediaBudgetSummary = plugin_mastodon_rate_limit_guard_summary();
plugin_mastodon_rate_limit_guard_stop();
$mediaBudgetRequests = simulate_recorded_http_requests();
$mediaBudgetLog = @file_get_contents(PLUGIN_MASTODON_LOG_FILE);
$mediaBudgetLogText = is_string($mediaBudgetLog) ? $mediaBudgetLog : '';
$mediaBudgetUploadRequests = 0;
foreach ($mediaBudgetRequests as $request) {
	if (isset($request ['method'], $request ['url']) && strtoupper((string) $request ['method']) === 'POST' && strpos((string) $request ['url'], '/api/v2/media') !== false) {
		$mediaBudgetUploadRequests++;
	}
}
$allOk = test_result(
	'Central Mastodon rate-limit guard stops media uploads after the per-run media budget',
	empty($mediaBudgetResult ['ok'])
		&& isset($mediaBudgetResult ['error']) && $mediaBudgetResult ['error'] === 'rate_limit_media_upload_budget_exhausted'
		&& isset($mediaBudgetSummary ['blocked_reason']) && $mediaBudgetSummary ['blocked_reason'] === 'rate_limit_media_upload_budget_exhausted'
		&& isset($mediaBudgetSummary ['media_uploads_used']) && (int) $mediaBudgetSummary ['media_uploads_used'] === 1
		&& $mediaBudgetUploadRequests === 1
		&& strpos($mediaBudgetLogText, 'rate_limit_media_upload_budget_exhausted') !== false,
	json_encode(array('result' => $mediaBudgetResult, 'summary' => $mediaBudgetSummary, 'requests' => $mediaBudgetRequests, 'log' => $mediaBudgetLogText))
) && $allOk;

$GLOBALS ['plugin_mastodon_test_rate_limit_budgets'] = array('requests' => 10, 'media_uploads' => 24, 'deletes' => 1, 'remote_remaining_floor' => 0);
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'DELETE https://mastodon.example/api/v1/statuses/status-budget-1?delete_media=1' => array('ok' => true, 'code' => 200, 'headers' => array('X-RateLimit-Remaining' => '298'), 'body' => '{}')
);
plugin_mastodon_rate_limit_guard_start('simulation_delete_budget');
$deleteBudgetFirst = plugin_mastodon_delete_status($rateLimitOptions, 'status-budget-1', true);
$deleteBudgetSecond = plugin_mastodon_delete_status($rateLimitOptions, 'status-budget-2', true);
$deleteBudgetSummary = plugin_mastodon_rate_limit_guard_summary();
plugin_mastodon_rate_limit_guard_stop();
$deleteBudgetRequests = simulate_recorded_http_requests();
$deleteBudgetLog = @file_get_contents(PLUGIN_MASTODON_LOG_FILE);
$deleteBudgetLogText = is_string($deleteBudgetLog) ? $deleteBudgetLog : '';
$allOk = test_result(
	'Central Mastodon rate-limit guard stops status deletions after the per-run delete budget',
	!empty($deleteBudgetFirst ['ok'])
		&& empty($deleteBudgetSecond ['ok'])
		&& isset($deleteBudgetSecond ['code']) && (int) $deleteBudgetSecond ['code'] === 429
		&& isset($deleteBudgetSummary ['blocked_reason']) && $deleteBudgetSummary ['blocked_reason'] === 'rate_limit_delete_budget_exhausted'
		&& count($deleteBudgetRequests) === 1
		&& strpos($deleteBudgetLogText, 'rate_limit_delete_budget_exhausted') !== false,
	json_encode(array('second' => $deleteBudgetSecond, 'summary' => $deleteBudgetSummary, 'requests' => $deleteBudgetRequests, 'log' => $deleteBudgetLogText))
) && $allOk;

$legacyDeleteOptions = $rateLimitOptions;
$legacyDeleteOptions ['instance_url'] = 'https://legacy.example';
$legacyDeleteOptions ['instance_info_url'] = 'https://legacy.example';
$legacyInstanceInfoJson = json_encode(array('version' => '4.3.9'));
$legacyDeleteOptions ['instance_info_json'] = is_string($legacyInstanceInfoJson) ? $legacyInstanceInfoJson : '';
$GLOBALS ['plugin_mastodon_test_rate_limit_budgets'] = array('requests' => 10, 'media_uploads' => 24, 'deletes' => 1, 'remote_remaining_floor' => 0);
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'DELETE https://legacy.example/api/v1/statuses/legacy-delete-1' => array('ok' => true, 'code' => 200, 'headers' => array('X-RateLimit-Remaining' => '298'), 'body' => '{}')
);
plugin_mastodon_rate_limit_guard_start('simulation_legacy_delete_without_media_parameter');
$legacyDeleteResult = plugin_mastodon_delete_status($legacyDeleteOptions, 'legacy-delete-1', true);
$legacyDeleteSummary = plugin_mastodon_rate_limit_guard_summary();
plugin_mastodon_rate_limit_guard_stop();
$legacyDeleteRequests = simulate_recorded_http_requests();
$allOk = test_result(
	'Cached Mastodon versions before 4.4 delete statuses without the delete_media query parameter',
	!empty($legacyDeleteResult ['ok'])
		&& count($legacyDeleteRequests) === 1
		&& isset($legacyDeleteRequests [0] ['url']) && (string) $legacyDeleteRequests [0] ['url'] === 'https://legacy.example/api/v1/statuses/legacy-delete-1'
		&& isset($legacyDeleteSummary ['deletes_used']) && (int) $legacyDeleteSummary ['deletes_used'] === 1,
	json_encode(array('result' => $legacyDeleteResult, 'summary' => $legacyDeleteSummary, 'requests' => $legacyDeleteRequests))
) && $allOk;

$fallbackDeleteOptions = $rateLimitOptions;
$fallbackDeleteOptions ['instance_url'] = 'https://fallback.example';
$fallbackDeleteOptions ['instance_info_url'] = '';
$fallbackDeleteOptions ['instance_info_json'] = '';
$GLOBALS ['plugin_mastodon_test_rate_limit_budgets'] = array('requests' => 10, 'media_uploads' => 24, 'deletes' => 4, 'remote_remaining_floor' => 0);
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'DELETE https://fallback.example/api/v1/statuses/fallback-delete-1?delete_media=1' => array('ok' => false, 'code' => 422, 'headers' => array('X-RateLimit-Remaining' => '298'), 'body' => json_encode(array('error' => 'unknown parameter: delete_media'))),
	'DELETE https://fallback.example/api/v1/statuses/fallback-delete-1' => array('ok' => true, 'code' => 200, 'headers' => array('X-RateLimit-Remaining' => '297'), 'body' => '{}')
);
plugin_mastodon_rate_limit_guard_start('simulation_delete_media_legacy_fallback');
$fallbackDeleteResult = plugin_mastodon_delete_status($fallbackDeleteOptions, 'fallback-delete-1', true);
$fallbackDeleteSummary = plugin_mastodon_rate_limit_guard_summary();
plugin_mastodon_rate_limit_guard_stop();
$fallbackDeleteRequests = simulate_recorded_http_requests();
$allOk = test_result(
	'Status deletion retries without delete_media when an older Mastodon server rejects the query parameter',
	!empty($fallbackDeleteResult ['ok'])
		&& !empty($fallbackDeleteResult ['delete_media_fallback_attempted'])
		&& isset($fallbackDeleteResult ['delete_media_first_code']) && (int) $fallbackDeleteResult ['delete_media_first_code'] === 422
		&& count($fallbackDeleteRequests) === 2
		&& isset($fallbackDeleteRequests [0] ['url']) && (string) $fallbackDeleteRequests [0] ['url'] === 'https://fallback.example/api/v1/statuses/fallback-delete-1?delete_media=1'
		&& isset($fallbackDeleteRequests [1] ['url']) && (string) $fallbackDeleteRequests [1] ['url'] === 'https://fallback.example/api/v1/statuses/fallback-delete-1'
		&& isset($fallbackDeleteSummary ['deletes_used']) && (int) $fallbackDeleteSummary ['deletes_used'] === 2,
	json_encode(array('result' => $fallbackDeleteResult, 'summary' => $fallbackDeleteSummary, 'requests' => $fallbackDeleteRequests))
) && $allOk;

plugin_mastodon_rate_limit_window_clear();
@unlink(PLUGIN_MASTODON_LOG_FILE);
$GLOBALS ['plugin_mastodon_test_rate_limit_budgets'] = array('requests' => 10, 'media_uploads' => 24, 'deletes' => 24, 'remote_remaining_floor' => 0);
$GLOBALS ['plugin_mastodon_test_rate_limit_window_budgets'] = array('media_uploads' => 1, 'media_uploads_ttl' => 1800, 'deletes' => 1000, 'deletes_ttl' => 1800, 'status_pages' => 1000, 'status_pages_ttl' => 900);
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'POST https://mastodon.example/api/v2/media' => array('ok' => true, 'code' => 200, 'headers' => array('X-RateLimit-Remaining' => '298'), 'body' => '{"id":"window-media-1","type":"image"}')
);
plugin_mastodon_rate_limit_guard_start('simulation_media_upload_window_first');
$mediaWindowFirst = plugin_mastodon_mastodon_json($rateLimitOptions, 'POST', '/api/v2/media', array(), true);
plugin_mastodon_rate_limit_guard_stop();
plugin_mastodon_rate_limit_guard_start('simulation_media_upload_window_second');
$mediaWindowSecond = plugin_mastodon_mastodon_json($rateLimitOptions, 'POST', '/api/v2/media', array(), true);
$mediaWindowSummary = plugin_mastodon_rate_limit_guard_summary();
plugin_mastodon_rate_limit_guard_stop();
$mediaWindowRequests = simulate_recorded_http_requests();
$mediaWindowLog = @file_get_contents(PLUGIN_MASTODON_LOG_FILE);
$mediaWindowLogText = is_string($mediaWindowLog) ? $mediaWindowLog : '';
$allOk = test_result(
	'Persistent Mastodon media-upload window budget is shared across forced runs',
	!empty($mediaWindowFirst ['ok'])
		&& empty($mediaWindowSecond ['ok'])
		&& isset($mediaWindowSecond ['code']) && (int) $mediaWindowSecond ['code'] === 429
		&& isset($mediaWindowSecond ['json'] ['error']) && $mediaWindowSecond ['json'] ['error'] === 'rate_limit_window_media_upload_budget_exhausted'
		&& isset($mediaWindowSummary ['blocked_reason']) && $mediaWindowSummary ['blocked_reason'] === 'rate_limit_window_media_upload_budget_exhausted'
		&& count($mediaWindowRequests) === 1
		&& strpos($mediaWindowLogText, 'rate_limit_window_media_upload_budget_exhausted') !== false
		&& strpos($mediaWindowLogText, 'window=media_uploads 1/1') !== false,
	json_encode(array('second' => $mediaWindowSecond, 'summary' => $mediaWindowSummary, 'requests' => $mediaWindowRequests, 'log' => $mediaWindowLogText))
) && $allOk;

$mediaWindowEntryId = 'entry300101-120000';
simulate_write_serialized_kv_file(entry_dir($mediaWindowEntryId) . EXT, array(
	'VERSION' => 'fp-1.6.dev',
	'SUBJECT' => 'Persistent media window entry',
	'CONTENT' => '[img=images/mastodon-sim/single-image.jpg title="Persistent media window"]',
	'AUTHOR' => 'FlatPress Test',
	'DATE' => mktime(12, 0, 0, 1, 1, 2030)
));
$mediaWindowSyncOptions = $rateLimitOptions;
$mediaWindowSyncOptions ['sync_start_date'] = '2030-01-01';
$mediaWindowSyncState = plugin_mastodon_default_state();
plugin_mastodon_rate_limit_guard_start('simulation_media_upload_window_state');
$mediaWindowSyncOk = plugin_mastodon_sync_local_to_remote($mediaWindowSyncOptions, $mediaWindowSyncState);
plugin_mastodon_rate_limit_guard_stop();
@unlink(entry_dir($mediaWindowEntryId) . EXT);
$mediaWindowStateLog = @file_get_contents(PLUGIN_MASTODON_LOG_FILE);
$mediaWindowStateLogText = is_string($mediaWindowStateLog) ? $mediaWindowStateLog : '';
$allOk = test_result(
	'Persistent media-upload window budget is written to the synchronization last error',
	!$mediaWindowSyncOk
		&& isset($mediaWindowSyncState ['last_error'])
		&& strpos((string) $mediaWindowSyncState ['last_error'], 'rate_limit_window_media_upload_budget_exhausted') !== false
		&& strpos($mediaWindowStateLogText, 'rate_limit_window_media_upload_budget_exhausted') !== false,
	json_encode(array('state_error' => isset($mediaWindowSyncState ['last_error']) ? $mediaWindowSyncState ['last_error'] : '', 'log' => $mediaWindowStateLogText))
) && $allOk;

plugin_mastodon_rate_limit_window_clear();
@unlink(PLUGIN_MASTODON_LOG_FILE);
$GLOBALS ['plugin_mastodon_test_rate_limit_budgets'] = array('requests' => 10, 'media_uploads' => 24, 'deletes' => 24, 'remote_remaining_floor' => 0);
$GLOBALS ['plugin_mastodon_test_rate_limit_window_budgets'] = array('media_uploads' => 1000, 'media_uploads_ttl' => 1800, 'deletes' => 1, 'deletes_ttl' => 1800, 'status_pages' => 1000, 'status_pages_ttl' => 900);
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'DELETE https://mastodon.example/api/v1/statuses/window-delete-1?delete_media=1' => array('ok' => true, 'code' => 200, 'headers' => array('X-RateLimit-Remaining' => '298'), 'body' => '{}')
);
plugin_mastodon_rate_limit_guard_start('simulation_delete_window_first');
$deleteWindowFirst = plugin_mastodon_delete_status($rateLimitOptions, 'window-delete-1', true);
plugin_mastodon_rate_limit_guard_stop();
plugin_mastodon_rate_limit_guard_start('simulation_delete_window_second');
$deleteWindowSecond = plugin_mastodon_delete_status($rateLimitOptions, 'window-delete-2', true);
$deleteWindowSummary = plugin_mastodon_rate_limit_guard_summary();
plugin_mastodon_rate_limit_guard_stop();
$deleteWindowRequests = simulate_recorded_http_requests();
$deleteWindowLog = @file_get_contents(PLUGIN_MASTODON_LOG_FILE);
$deleteWindowLogText = is_string($deleteWindowLog) ? $deleteWindowLog : '';
$allOk = test_result(
	'Persistent Mastodon delete window budget is shared across forced runs',
	!empty($deleteWindowFirst ['ok'])
		&& empty($deleteWindowSecond ['ok'])
		&& isset($deleteWindowSecond ['code']) && (int) $deleteWindowSecond ['code'] === 429
		&& isset($deleteWindowSecond ['json'] ['error']) && $deleteWindowSecond ['json'] ['error'] === 'rate_limit_window_delete_budget_exhausted'
		&& isset($deleteWindowSummary ['blocked_reason']) && $deleteWindowSummary ['blocked_reason'] === 'rate_limit_window_delete_budget_exhausted'
		&& count($deleteWindowRequests) === 1
		&& strpos($deleteWindowLogText, 'rate_limit_window_delete_budget_exhausted') !== false
		&& strpos($deleteWindowLogText, 'window=deletes 1/1') !== false,
	json_encode(array('second' => $deleteWindowSecond, 'summary' => $deleteWindowSummary, 'requests' => $deleteWindowRequests, 'log' => $deleteWindowLogText))
) && $allOk;

$deleteWindowState = plugin_mastodon_default_state();
plugin_mastodon_state_set_entry_mapping($deleteWindowState, 'entry300101-130000', 'window-delete-state', 'local', 'delete-window-hash', $rateLimitOptions ['instance_url'] . '/@flatpress/window-delete-state', '2030-01-01 13:00:00');
plugin_mastodon_state_set_deletions_pending($deleteWindowState, true, 'full', date('Y-m-d H:i:s', time() - 1));
plugin_mastodon_state_write($deleteWindowState);
$deleteWindowSavedOptions = plugin_mastodon_get_options();
plugin_mastodon_save_options($rateLimitOptions);
$deleteWindowRunResult = plugin_mastodon_run_deletion_sync(true);
plugin_mastodon_save_options($deleteWindowSavedOptions);
$deleteWindowRunState = isset($deleteWindowRunResult ['state']) && is_array($deleteWindowRunResult ['state']) ? $deleteWindowRunResult ['state'] : plugin_mastodon_state_read();
$deleteWindowStateLog = @file_get_contents(PLUGIN_MASTODON_LOG_FILE);
$deleteWindowStateLogText = is_string($deleteWindowStateLog) ? $deleteWindowStateLog : '';
$allOk = test_result(
	'Persistent delete window budget is written to the synchronization last error',
	empty($deleteWindowRunResult ['ok'])
		&& isset($deleteWindowRunState ['last_error'])
		&& strpos((string) $deleteWindowRunState ['last_error'], 'rate_limit_window_delete_budget_exhausted') !== false
		&& strpos($deleteWindowStateLogText, 'rate_limit_window_delete_budget_exhausted') !== false,
	json_encode(array('result' => $deleteWindowRunResult, 'state_error' => isset($deleteWindowRunState ['last_error']) ? $deleteWindowRunState ['last_error'] : '', 'log' => $deleteWindowStateLogText))
) && $allOk;

plugin_mastodon_rate_limit_window_clear();
@unlink(PLUGIN_MASTODON_LOG_FILE);
$GLOBALS ['plugin_mastodon_test_rate_limit_budgets'] = array('requests' => 10, 'media_uploads' => 24, 'deletes' => 24, 'remote_remaining_floor' => 0);
$GLOBALS ['plugin_mastodon_test_rate_limit_window_budgets'] = array('media_uploads' => 1000, 'media_uploads_ttl' => 1800, 'deletes' => 1000, 'deletes_ttl' => 1800, 'status_pages' => 1, 'status_pages_ttl' => 900);
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET https://mastodon.example/api/v1/accounts/window-account/statuses?limit=40&exclude_reblogs=true&exclude_replies=true' => array('ok' => true, 'code' => 200, 'headers' => array('X-RateLimit-Remaining' => '298'), 'body' => '[]')
);
plugin_mastodon_rate_limit_guard_start('simulation_status_page_window_first');
$statusPageWindowFirst = plugin_mastodon_mastodon_json($rateLimitOptions, 'GET', '/api/v1/accounts/window-account/statuses', array('limit' => 40, 'exclude_reblogs' => 'true', 'exclude_replies' => 'true'), true);
plugin_mastodon_rate_limit_guard_stop();
plugin_mastodon_rate_limit_guard_start('simulation_status_page_window_second');
$statusPageWindowSecond = plugin_mastodon_mastodon_json($rateLimitOptions, 'GET', '/api/v1/accounts/window-account/statuses', array('limit' => 40, 'exclude_reblogs' => 'true', 'exclude_replies' => 'true'), true);
$statusPageWindowSummary = plugin_mastodon_rate_limit_guard_summary();
plugin_mastodon_rate_limit_guard_stop();
$statusPageWindowRequests = simulate_recorded_http_requests();
$statusPageWindowLog = @file_get_contents(PLUGIN_MASTODON_LOG_FILE);
$statusPageWindowLogText = is_string($statusPageWindowLog) ? $statusPageWindowLog : '';
$allOk = test_result(
	'Persistent Mastodon account-status paging window budget is shared across forced runs',
	!empty($statusPageWindowFirst ['ok'])
		&& empty($statusPageWindowSecond ['ok'])
		&& isset($statusPageWindowSecond ['code']) && (int) $statusPageWindowSecond ['code'] === 429
		&& isset($statusPageWindowSecond ['json'] ['error']) && $statusPageWindowSecond ['json'] ['error'] === 'rate_limit_window_status_page_budget_exhausted'
		&& isset($statusPageWindowSummary ['blocked_reason']) && $statusPageWindowSummary ['blocked_reason'] === 'rate_limit_window_status_page_budget_exhausted'
		&& count($statusPageWindowRequests) === 1
		&& strpos($statusPageWindowLogText, 'rate_limit_window_status_page_budget_exhausted') !== false
		&& strpos($statusPageWindowLogText, 'window=status_pages 1/1') !== false,
	json_encode(array('second' => $statusPageWindowSecond, 'summary' => $statusPageWindowSummary, 'requests' => $statusPageWindowRequests, 'log' => $statusPageWindowLogText))
) && $allOk;

$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET https://mastodon.example/api/v1/accounts/verify_credentials' => array('ok' => true, 'code' => 200, 'headers' => array('X-RateLimit-Remaining' => '297'), 'body' => '{"id":"window-account","username":"flatpress"}')
);
$statusPageWindowSyncState = plugin_mastodon_default_state();
plugin_mastodon_rate_limit_guard_start('simulation_status_page_window_state');
$statusPageWindowSyncOk = plugin_mastodon_sync_remote_to_local($rateLimitOptions, $statusPageWindowSyncState);
plugin_mastodon_rate_limit_guard_stop();
$statusPageWindowStateLog = @file_get_contents(PLUGIN_MASTODON_LOG_FILE);
$statusPageWindowStateLogText = is_string($statusPageWindowStateLog) ? $statusPageWindowStateLog : '';
$allOk = test_result(
	'Persistent account-status paging window budget is written to the synchronization last error',
	!$statusPageWindowSyncOk
		&& isset($statusPageWindowSyncState ['last_error'])
		&& (string) $statusPageWindowSyncState ['last_error'] === 'rate_limit_window_status_page_budget_exhausted'
		&& strpos($statusPageWindowStateLogText, 'rate_limit_window_status_page_budget_exhausted') !== false,
	json_encode(array('state_error' => isset($statusPageWindowSyncState ['last_error']) ? $statusPageWindowSyncState ['last_error'] : '', 'log' => $statusPageWindowStateLogText))
) && $allOk;

plugin_mastodon_rate_limit_window_clear();
$GLOBALS ['plugin_mastodon_test_rate_limit_window_budgets'] = $simulateDefaultRateLimitWindowBudgets;
unset($GLOBALS ['plugin_mastodon_test_rate_limit_budgets']);
unset($GLOBALS ['plugin_mastodon_test_http_responses']);
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();

$allOk = test_result(
	'Admin synchronization time converts stored UTC to FlatPress local time and back',
	plugin_mastodon_sync_time_utc_to_local('23:00') === '01:00'
		&& plugin_mastodon_sync_time_local_to_utc('01:00') === '23:00'
		&& plugin_mastodon_fp_timeoffset_label() === 'UTC+02:00',
	json_encode(array(
		'local' => plugin_mastodon_sync_time_utc_to_local('23:00'),
		'utc' => plugin_mastodon_sync_time_local_to_utc('01:00'),
		'label' => plugin_mastodon_fp_timeoffset_label()
	))
) && $allOk;

$originalFpConfigForTimeoffset = $GLOBALS ['fp_config'];
$originalEarlyFpConfigForTimeoffset = $GLOBALS ['EARLY_FP_CONFIG'];
$fractionalTimeoffsetConfig = $originalFpConfigForTimeoffset;
if (!isset($fractionalTimeoffsetConfig ['locale']) || !is_array($fractionalTimeoffsetConfig ['locale'])) {
	$fractionalTimeoffsetConfig ['locale'] = array();
}
$fractionalTimeoffsetConfig ['locale'] ['timeoffset'] = 5.5;
$GLOBALS ['fp_config'] = $fractionalTimeoffsetConfig;
$GLOBALS ['EARLY_FP_CONFIG'] = $fractionalTimeoffsetConfig;
plugin_mastodon_runtime_cache_clear('core');
$allOk = test_result(
	'Admin synchronization time conversion supports fractional FlatPress offsets',
	plugin_mastodon_fp_timeoffset_label() === 'UTC+05:30'
		&& plugin_mastodon_sync_time_utc_to_local('23:00') === '04:30'
		&& plugin_mastodon_sync_time_local_to_utc('04:30') === '23:00',
	json_encode(array(
		'label' => plugin_mastodon_fp_timeoffset_label(),
		'local' => plugin_mastodon_sync_time_utc_to_local('23:00'),
		'utc' => plugin_mastodon_sync_time_local_to_utc('04:30')
	))
) && $allOk;
$GLOBALS ['fp_config'] = $originalFpConfigForTimeoffset;
$GLOBALS ['EARLY_FP_CONFIG'] = $originalEarlyFpConfigForTimeoffset;
plugin_mastodon_runtime_cache_clear('core');


$allOk = test_result(
	'Sync start date normalization accepts valid ISO dates',
	plugin_mastodon_normalize_sync_start_date('2026-03-14') === '2026-03-14' && plugin_mastodon_normalize_sync_start_date('2026-02-31') === '',
	plugin_mastodon_normalize_sync_start_date('2026-03-14')
) && $allOk;

$allOk = test_result(
	'Local and remote date helpers derive stable date keys',
	plugin_mastodon_local_item_date_key(array('date' => strtotime('2026-03-14 12:34:56 UTC')), '') === '2026-03-14'
		&& plugin_mastodon_remote_status_date_key(array('created_at' => '2026-03-14T10:20:30+02:00')) === '2026-03-14'
		&& plugin_mastodon_date_matches_sync_start(array('sync_start_date' => '2026-03-14'), '2026-03-14')
		&& !plugin_mastodon_date_matches_sync_start(array('sync_start_date' => '2026-03-14'), '2026-03-13'),
	plugin_mastodon_remote_status_date_key(array('created_at' => '2026-03-14T10:20:30+02:00'))
) && $allOk;

$allOk = test_result(
	'Remote-to-local update toggle normalizes safely',
	plugin_mastodon_normalize_update_local_from_remote('1') === '1'
		&& plugin_mastodon_normalize_update_local_from_remote('on') === '1'
		&& plugin_mastodon_normalize_update_local_from_remote('') === '0'
		&& plugin_mastodon_normalize_update_local_from_remote('false') === '0'
		&& plugin_mastodon_should_update_local_from_remote(array('update_local_from_remote' => '1'))
		&& !plugin_mastodon_should_update_local_from_remote(array('update_local_from_remote' => '0')),
	'update_local_from_remote'
) && $allOk;

$allOk = test_result(
	'Comment-as-entry import toggle normalizes safely',
	plugin_mastodon_normalize_import_synced_comments_as_entries('1') === '1'
		&& plugin_mastodon_normalize_import_synced_comments_as_entries('on') === '1'
		&& plugin_mastodon_normalize_import_synced_comments_as_entries('') === '0'
		&& plugin_mastodon_normalize_import_synced_comments_as_entries('false') === '0'
		&& plugin_mastodon_should_import_synced_comments_as_entries(array('import_synced_comments_as_entries' => '1'))
		&& !plugin_mastodon_should_import_synced_comments_as_entries(array('import_synced_comments_as_entries' => '0')),
	'import_synced_comments_as_entries'
) && $allOk;

// State persistence
$state = plugin_mastodon_default_state();
plugin_mastodon_state_set_entry_mapping($state, 'entry260313-120000', '100', 'local', 'abc', 'https://mastodon.example/@user/100', '2026-03-13 12:00:00');
plugin_mastodon_state_set_comment_mapping($state, 'entry260313-120000', 'comment260313-121000', '101', 'local', 'def', 'https://mastodon.example/@user/101', '2026-03-13 12:10:00', 'comment260313-120500', '100');
$allOk = test_result('State entry mapping created', isset($state ['entries'] ['entry260313-120000'] ['remote_id']) && $state ['entries'] ['entry260313-120000'] ['remote_id'] === '100') && $allOk;
$allOk = test_result('State comment mapping created', isset($state ['comments_remote'] ['101'] ['comment_id']) && $state ['comments_remote'] ['101'] ['comment_id'] === 'comment260313-121000' && isset($state ['comments'] ['entry260313-120000:comment260313-121000'] ['parent_comment_id']) && $state ['comments'] ['entry260313-120000:comment260313-121000'] ['parent_comment_id'] === 'comment260313-120500') && $allOk;

// Fresh sandbox state
simulate_delete_recursive($simRoot . '/fp-content/plugin_mastodon');
mkdir($simRoot . '/fp-content/plugin_mastodon', 0777, true);
plugin_mastodon_state_write(plugin_mastodon_default_state());

$permissionGuardEntry = array(
	'content' => array(
		'started_at' => time(),
		'expires_at' => time() + PLUGIN_MASTODON_COOLDOWN_TTL,
		'reason' => 'permission_simulation'
	)
);
plugin_mastodon_sync_guard_file_write($permissionGuardEntry);
plugin_mastodon_rate_limit_window_write(array(
	'media_uploads' => array(
		'started_at' => time(),
		'expires_at' => time() + PLUGIN_MASTODON_WINDOW_MEDIA_UPLOAD_TTL,
		'used' => 1
	)
));
plugin_mastodon_log('Permission simulation log entry');
$permissionLockHandle = @fopen(PLUGIN_MASTODON_LOCK_FILE, 'c+');
if ($permissionLockHandle) {
	plugin_mastodon_apply_file_permissions(PLUGIN_MASTODON_LOCK_FILE);
	@fclose($permissionLockHandle);
}
$permissionExpectedMode = plugin_mastodon_file_permissions_mode();
$permissionFiles = array(
	'state' => ABS_PATH . PLUGIN_MASTODON_STATE_FILE,
	'guard' => ABS_PATH . PLUGIN_MASTODON_GUARD_FILE,
	'rate_window' => ABS_PATH . PLUGIN_MASTODON_RATE_LIMIT_WINDOW_FILE,
	'log' => ABS_PATH . PLUGIN_MASTODON_LOG_FILE,
	'lock' => ABS_PATH . PLUGIN_MASTODON_LOCK_FILE
);
$permissionResults = array();
$permissionOk = true;
foreach ($permissionFiles as $permissionName => $permissionPath) {
	clearstatcache(true, $permissionPath);
	$permissionRaw = @fileperms($permissionPath);
	$permissionMode = $permissionRaw === false ? null : ($permissionRaw & 0777);
	$permissionMatch = simulate_file_mode_matches($permissionPath, $permissionExpectedMode);
	$permissionResults [$permissionName] = array(
		'exists' => is_file($permissionPath),
		'mode' => $permissionMode === null ? '' : decoct($permissionMode),
		'expected' => decoct($permissionExpectedMode),
		'matches' => $permissionMatch
	);
	$permissionOk = $permissionOk && $permissionMatch;
}
$allOk = test_result(
	'Mastodon runtime files in fp-content/plugin_mastodon use FlatPress FILE_PERMISSIONS',
	$permissionOk,
	json_encode($permissionResults)
) && $allOk;

// Mocked OAuth + sync simulation
$localThreadEntry = array(
	'version' => system_ver(),
	'subject' => 'Mastodon sync export root',
	'content' => 'Entry body for reply-chain export verification.',
	'author' => 'Simulation',
	'date' => strtotime('2026-03-13 10:10:10 UTC')
);
$localThreadEntryId = entry_save($localThreadEntry, null);
$localThreadEntry = entry_parse($localThreadEntryId);
$localThreadParentComment = array(
	'version' => system_ver(),
	'name' => 'Parent Author',
	'content' => 'Parent comment body',
	'date' => strtotime('2026-03-13 10:11:10 UTC')
);
$localThreadParentCommentId = comment_save($localThreadEntryId, $localThreadParentComment);
$localThreadChildComment = array(
	'version' => system_ver(),
	'name' => 'Child Author',
	'content' => 'Child comment body',
	'replyto' => $localThreadParentCommentId,
	'date' => strtotime('2026-03-13 10:12:10 UTC')
);
$localThreadChildCommentId = comment_save($localThreadEntryId, $localThreadChildComment);

$configuredOptions = plugin_mastodon_get_options();
$seededOptions = simulate_seed_options_from_config($configuredOptions);
$options = $seededOptions;
$options ['sync_start_date'] = '';
$options ['update_local_from_remote'] = '0';
plugin_mastodon_save_options($options);

$instanceUrl = plugin_mastodon_normalize_instance_url($options ['instance_url']);
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$statusPostResponses = array();
for ($i = 0; $i < 120; $i++) {
	$statusPostResponses [] = array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			'id' => (string) (600 + $i),
			'url' => $instanceUrl . '/@flatpress/' . (string) (600 + $i),
			'created_at' => gmdate('Y-m-d\\TH:i:s\\Z', strtotime('2026-03-13 00:00:00 UTC') + $i)
		))
	);
}
$mediaUploadResponses = array();
for ($i = 0; $i < 120; $i++) {
	$mediaUploadResponses [] = array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			'id' => 'media-' . (string) (800 + $i),
			'type' => 'image'
		))
	);
}
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'POST ' . $instanceUrl . '/oauth/token' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('access_token' => 'token123'))
	),
	'GET ' . $instanceUrl . '/api/v1/accounts/verify_credentials' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('id' => 'acct1', 'username' => 'flatpress'))
	),
	'GET ' . $instanceUrl . '/api/v1/accounts/acct1/statuses?limit=40&exclude_reblogs=true&exclude_replies=true' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			array(
				'id' => '550',
				'visibility' => 'direct',
				'created_at' => '2026-03-13T00:10:00Z',
				'content' => '<p><a href="https://mastodon.social/@spacesjut">@spacesjut</a></p><p>Private mention body</p>',
				'url' => $instanceUrl . '/@flatpress/550',
				'account' => array(
					'id' => 'acct1',
					'acct' => 'flatpress',
					'display_name' => 'FlatPress Bot',
					'url' => $instanceUrl . '/@flatpress'
				)
			),
			array(
				'id' => '500',
				'visibility' => 'public',
				'created_at' => '2026-03-12T23:30:00Z',
				'content' => '<p><a href="https://mastodon.social/@spacesjut">@spacesjut</a></p><p>mastodon.social</p><p>Remote status body</p>',
				'url' => $instanceUrl . '/@flatpress/500',
				'media_attachments' => array(
					array(
						'id' => 'remote-image-500',
						'type' => 'image',
						'url' => 'https://files.example/remote-status-500.jpg',
						'description' => 'Remote imported image'
					)
				),
				'account' => array(
					'id' => 'acct1',
					'acct' => 'flatpress',
					'display_name' => 'FlatPress Bot',
					'url' => $instanceUrl . '/@flatpress'
				)
			)
		))
	),
	'GET https://files.example/remote-status-500.jpg' => array(
		'ok' => true,
		'code' => 200,
		'headers' => array('content-type' => 'image/jpeg'),
		'body' => 'remote-status-image-500'
	),
	'GET ' . $instanceUrl . '/api/v1/statuses/500/context' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			'descendants' => array(
				array(
					'id' => '503',
					'visibility' => 'direct',
					'in_reply_to_id' => '500',
					'created_at' => '2026-03-12T23:34:00Z',
					'content' => '<p>Private mention reply</p>',
					'url' => $instanceUrl . '/@secret/503',
					'account' => array(
						'id' => 'acct4',
						'acct' => 'secret@example.net',
						'display_name' => 'Secret',
						'url' => 'https://example.net/@secret'
					)
				),
				array(
					'id' => '502',
					'visibility' => 'public',
					'in_reply_to_id' => '501',
					'created_at' => '2026-03-12T23:36:00Z',
					'content' => '<p>Nested remote reply</p>',
					'url' => $instanceUrl . '/@bob/502',
					'account' => array(
						'id' => 'acct3',
						'acct' => 'bob@example.net',
						'display_name' => 'Bob',
						'url' => 'https://example.net/@bob'
					)
				),
				array(
					'id' => '501',
					'visibility' => 'public',
					'in_reply_to_id' => '500',
					'created_at' => '2026-03-12T23:35:00Z',
					'content' => '<p>Remote reply</p>',
					'url' => $instanceUrl . '/@alice/501',
					'account' => array(
						'id' => 'acct2',
						'acct' => 'alice@example.net',
						'display_name' => 'Alice',
						'url' => 'https://example.net/@alice'
					)
				)
			)
		))
	),
	'POST ' . $instanceUrl . '/api/v2/media' => $mediaUploadResponses,
	'POST ' . $instanceUrl . '/api/v1/statuses' => $statusPostResponses,
	'GET ' . $instanceUrl . '/api/v2/instance' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			'configuration' => array(
				'statuses' => array(
					'max_characters' => 500,
					'max_media_attachments' => 4,
					'characters_reserved_per_url' => 23
				),
				'media_attachments' => array(
					'description_limit' => 1500
				)
			)
		))
	)
);

$response = plugin_mastodon_exchange_code_for_token($options, 'code-123');
$allOk = test_result('OAuth code exchange', $response ['ok'] && !empty(plugin_mastodon_get_options() ['access_token'])) && $allOk;

plugin_mastodon_runtime_cache_clear('state');
$cacheState = plugin_mastodon_default_state();
$cacheState ['last_error'] = 'cache-check';
$cacheState ['last_run'] = '2026-03-14 12:34:56';
plugin_mastodon_state_write($cacheState);
$cacheStateReloaded = plugin_mastodon_state_read();
$allOk = test_result(
	'State cache stays fresh after writing state.json',
	isset($cacheStateReloaded ['last_error']) && $cacheStateReloaded ['last_error'] === 'cache-check' && isset($cacheStateReloaded ['last_run']) && $cacheStateReloaded ['last_run'] === '2026-03-14 12:34:56',
	(isset($cacheStateReloaded ['last_error']) ? (string) $cacheStateReloaded ['last_error'] : 'missing') . ' | ' . (isset($cacheStateReloaded ['last_run']) ? (string) $cacheStateReloaded ['last_run'] : 'missing')
) && $allOk;
$cacheStateReloaded ['last_error'] = '';
$cacheStateReloaded ['last_run'] = '';
plugin_mastodon_state_write($cacheStateReloaded);

$prettyLegacyState = plugin_mastodon_default_state();
$prettyLegacyState ['last_run'] = '2026-03-14 14:00:00';
$prettyLegacyState ['entries'] ['entry260314-140000'] = array(
	'remote_id' => 'pretty-entry-1',
	'hash' => 'pretty-hash',
	'date_key' => '2026-03-14'
);
$prettyLegacyState ['entries_remote'] ['pretty-entry-1'] = 'entry260314-140000';
plugin_mastodon_runtime_cache_clear('state');
$prettyLegacyPayload = json_encode($prettyLegacyState, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
plugin_mastodon_io_write_file(PLUGIN_MASTODON_STATE_FILE, (is_string($prettyLegacyPayload) ? $prettyLegacyPayload : '{}') . PHP_EOL);
$prettyLegacyReloaded = plugin_mastodon_state_read();
$allOk = test_result(
	'Pretty-printed legacy state.json remains readable after compact-state optimization',
	isset($prettyLegacyReloaded ['entries'] ['entry260314-140000'] ['remote_id'])
		&& (string) $prettyLegacyReloaded ['entries'] ['entry260314-140000'] ['remote_id'] === 'pretty-entry-1'
		&& isset($prettyLegacyReloaded ['entries_remote'] ['pretty-entry-1'])
		&& (string) $prettyLegacyReloaded ['entries_remote'] ['pretty-entry-1'] === 'entry260314-140000',
	json_encode(array(
		'entry' => isset($prettyLegacyReloaded ['entries'] ['entry260314-140000']) ? $prettyLegacyReloaded ['entries'] ['entry260314-140000'] : array(),
		'reverse' => isset($prettyLegacyReloaded ['entries_remote'] ['pretty-entry-1']) ? $prettyLegacyReloaded ['entries_remote'] ['pretty-entry-1'] : ''
	))
) && $allOk;

$compactRoundtripState = plugin_mastodon_default_state();
$compactRoundtripState ['last_run'] = '2026-03-14 15:00:00';
$compactRoundtripState ['last_deletion_run'] = '2026-03-14 15:05:00';
$compactRoundtripState ['entries'] ['entry260314-150000'] = array(
	'remote_id' => 'compact-entry-1',
	'hash' => 'compact-entry-hash',
	'date_key' => '2026-03-14',
	'remote_url' => 'https://example.social/@flatpress/compact-entry-1'
);
$compactRoundtripState ['entries_remote'] ['compact-entry-1'] = 'entry260314-150000';
$compactRoundtripState ['comments'] ['entry260314-150000:comment1'] = array(
	'remote_id' => 'compact-comment-1',
	'hash' => 'compact-comment-hash',
	'parent_comment_id' => '',
	'in_reply_to_remote_id' => 'compact-entry-1'
);
$compactRoundtripState ['comments_remote'] ['compact-comment-1'] = array(
	'entry_id' => 'entry260314-150000',
	'comment_id' => 'comment1'
);
$compactRoundtripState ['dirty_entries'] ['entry260314-150000'] = array('reason' => 'simulation');
$compactRoundtripState ['dirty_comments'] ['entry260314-150000:comment1'] = array(
	'entry_id' => 'entry260314-150000',
	'comment_id' => 'comment1'
);
$compactRoundtripState ['comment_tombstones'] ['compact-deleted-comment'] = array('deleted_at' => '2026-03-14 15:10:00');
$compactRoundtripState ['pending_comment_remote_rechecks'] ['compact-entry-1'] = array('queued_at' => '2026-03-14 15:11:00');
plugin_mastodon_runtime_cache_clear('state');
$compactWriteOk = plugin_mastodon_state_write($compactRoundtripState);
$compactPayload = @file_get_contents(PLUGIN_MASTODON_STATE_FILE);
$compactPayloadData = is_string($compactPayload) ? json_decode($compactPayload, true) : array();
$compactShardFile = plugin_mastodon_state_comment_shard_file('entry260314-150000');
$compactShardPayload = $compactShardFile !== '' ? @file_get_contents($compactShardFile) : false;
$compactShardData = is_string($compactShardPayload) ? json_decode($compactShardPayload, true) : array();
plugin_mastodon_runtime_cache_clear('state');
$compactReloaded = plugin_mastodon_state_read();
$allOk = test_result(
	'State write persists compact split state.json while preserving round-trip mappings',
	$compactWriteOk
		&& is_string($compactPayload)
		&& is_array($compactPayloadData)
		&& substr_count($compactPayload, "\n") === 1
		&& strpos($compactPayload, "\n    \"entries\"") === false
		&& isset($compactPayloadData ['comments']) && $compactPayloadData ['comments'] === array()
		&& isset($compactPayloadData ['comment_shards'] ['storage'])
		&& (string) $compactPayloadData ['comment_shards'] ['storage'] === 'per_entry'
		&& is_string($compactShardPayload)
		&& is_array($compactShardData)
		&& isset($compactShardData ['comments'] ['entry260314-150000:comment1'] ['remote_id'])
		&& (string) $compactShardData ['comments'] ['entry260314-150000:comment1'] ['remote_id'] === 'compact-comment-1'
		&& isset($compactReloaded ['entries'] ['entry260314-150000'] ['remote_id'])
		&& (string) $compactReloaded ['entries'] ['entry260314-150000'] ['remote_id'] === 'compact-entry-1'
		&& isset($compactReloaded ['entries_remote'] ['compact-entry-1'])
		&& (string) $compactReloaded ['entries_remote'] ['compact-entry-1'] === 'entry260314-150000'
		&& isset($compactReloaded ['comments'] ['entry260314-150000:comment1'] ['remote_id'])
		&& (string) $compactReloaded ['comments'] ['entry260314-150000:comment1'] ['remote_id'] === 'compact-comment-1'
		&& isset($compactReloaded ['comments_remote'] ['compact-comment-1'] ['entry_id'])
		&& (string) $compactReloaded ['comments_remote'] ['compact-comment-1'] ['entry_id'] === 'entry260314-150000'
		&& isset($compactReloaded ['dirty_entries'] ['entry260314-150000'])
		&& isset($compactReloaded ['dirty_comments'] ['entry260314-150000:comment1'])
		&& isset($compactReloaded ['comment_tombstones'] ['compact-deleted-comment'])
		&& isset($compactReloaded ['pending_comment_remote_rechecks'] ['compact-entry-1']),
	json_encode(array(
		'payload_bytes' => is_string($compactPayload) ? strlen($compactPayload) : -1,
		'newline_count' => is_string($compactPayload) ? substr_count($compactPayload, "\n") : -1,
		'has_pretty_entries_indent' => is_string($compactPayload) ? (strpos($compactPayload, "\n    \"entries\"") !== false) : null
	))
) && $allOk;


$boundedState = plugin_mastodon_default_state();
foreach (array('entry260314-160000' => 'compact-comment-a', 'entry260314-170000' => 'compact-comment-b') as $boundedEntryId => $boundedRemoteId) {
	$boundedState ['entries'] [$boundedEntryId] = array(
		'remote_id' => $boundedEntryId . '-remote',
		'hash' => $boundedEntryId . '-hash',
		'remote_url' => 'https://example.social/@flatpress/' . $boundedEntryId,
		'remote_updated_at' => '2026-03-14 16:00:00',
		'local_date_key' => '2026-03-14',
		'remote_date_key' => '2026-03-14'
	);
	$boundedState ['entries_remote'] [$boundedEntryId . '-remote'] = $boundedEntryId;
	$boundedCommentId = $boundedEntryId === 'entry260314-160000' ? 'comment260314-160100' : 'comment260314-170100';
	plugin_mastodon_state_set_comment_mapping(
		$boundedState,
		$boundedEntryId,
		$boundedCommentId,
		$boundedRemoteId,
		'local',
		$boundedRemoteId . '-hash',
		'https://example.social/@flatpress/' . $boundedRemoteId,
		'2026-03-14 16:01:00',
		'',
		$boundedEntryId . '-remote',
		'2026-03-14',
		'2026-03-14'
	);
}
plugin_mastodon_runtime_cache_clear('state');
$boundedWriteOk = plugin_mastodon_state_write($boundedState);
plugin_mastodon_runtime_cache_clear('state');
$boundedSubset = plugin_mastodon_state_read(array('entry260314-160000'));
$allOk = test_result(
	'Split comment shards support bounded per-entry state reads',
	$boundedWriteOk
		&& isset($boundedSubset ['comments'] ['entry260314-160000:comment260314-160100'])
		&& !isset($boundedSubset ['comments'] ['entry260314-170000:comment260314-170100'])
		&& isset($boundedSubset ['comments_remote'] ['compact-comment-a'])
		&& isset($boundedSubset ['comments_remote'] ['compact-comment-b']),
	json_encode(array(
		'loaded_comment_keys' => isset($boundedSubset ['comments']) && is_array($boundedSubset ['comments']) ? array_keys($boundedSubset ['comments']) : array(),
		'remote_index_keys' => isset($boundedSubset ['comments_remote']) && is_array($boundedSubset ['comments_remote']) ? array_keys($boundedSubset ['comments_remote']) : array()
	))
) && $allOk;

$boundedSubset ['comments'] ['entry260314-160000:comment260314-160100'] ['hash'] = 'compact-comment-a-updated-hash';
$boundedSubset ['dirty_comments'] ['entry260314-160000:comment260314-160100'] = array(
	'entry_id' => 'entry260314-160000',
	'comment_id' => 'comment260314-160100',
	'hash' => 'compact-comment-a-updated-hash'
);
$boundedPartialWriteOk = plugin_mastodon_state_write($boundedSubset);
plugin_mastodon_runtime_cache_clear('state');
$boundedAfterPartialWrite = plugin_mastodon_state_read();
$boundedShardA = plugin_mastodon_state_read_comment_shard('entry260314-160000');
$boundedShardB = plugin_mastodon_state_read_comment_shard('entry260314-170000');
$allOk = test_result(
	'Partial split-state writes preserve unloaded comment shards',
	$boundedPartialWriteOk
		&& isset($boundedAfterPartialWrite ['comments'] ['entry260314-160000:comment260314-160100'] ['hash'])
		&& (string) $boundedAfterPartialWrite ['comments'] ['entry260314-160000:comment260314-160100'] ['hash'] === 'compact-comment-a-updated-hash'
		&& isset($boundedAfterPartialWrite ['comments'] ['entry260314-170000:comment260314-170100'] ['remote_id'])
		&& (string) $boundedAfterPartialWrite ['comments'] ['entry260314-170000:comment260314-170100'] ['remote_id'] === 'compact-comment-b'
		&& isset($boundedAfterPartialWrite ['dirty_comments'] ['entry260314-160000:comment260314-160100'])
		&& isset($boundedShardA ['entry260314-160000:comment260314-160100'] ['hash'])
		&& (string) $boundedShardA ['entry260314-160000:comment260314-160100'] ['hash'] === 'compact-comment-a-updated-hash'
		&& isset($boundedShardB ['entry260314-170000:comment260314-170100'] ['remote_id'])
		&& (string) $boundedShardB ['entry260314-170000:comment260314-170100'] ['remote_id'] === 'compact-comment-b',
	json_encode(array(
		'loaded_before_write' => plugin_mastodon_state_loaded_comment_entry_ids($boundedSubset),
		'full_comment_keys_after_write' => isset($boundedAfterPartialWrite ['comments']) && is_array($boundedAfterPartialWrite ['comments']) ? array_keys($boundedAfterPartialWrite ['comments']) : array(),
		'shard_b_preserved' => isset($boundedShardB ['entry260314-170000:comment260314-170100'])
	))
) && $allOk;

$legacyInlineCommentState = plugin_mastodon_default_state();
plugin_mastodon_state_set_comment_mapping(
	$legacyInlineCommentState,
	'entry260314-180000',
	'comment260314-180100',
	'legacy-inline-comment-1',
	'remote',
	'legacy-inline-hash',
	'https://example.social/@remote/legacy-inline-comment-1',
	'2026-03-14 18:01:00',
	'',
	'legacy-inline-entry-1',
	'2026-03-14',
	'2026-03-14'
);
$legacyInlinePayload = json_encode(plugin_mastodon_state_normalize($legacyInlineCommentState), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
plugin_mastodon_runtime_cache_clear('state');
plugin_mastodon_io_write_file(PLUGIN_MASTODON_STATE_FILE, (is_string($legacyInlinePayload) ? $legacyInlinePayload : '{}') . PHP_EOL);
$legacyInlineRead = plugin_mastodon_state_read();
$legacyInlineMigrated = plugin_mastodon_state_write($legacyInlineRead);
$legacyInlineMainPayload = @file_get_contents(PLUGIN_MASTODON_STATE_FILE);
$legacyInlineMain = is_string($legacyInlineMainPayload) ? json_decode($legacyInlineMainPayload, true) : array();
$legacyInlineShardFile = plugin_mastodon_state_comment_shard_file('entry260314-180000');
$legacyInlineShardPayload = $legacyInlineShardFile !== '' ? @file_get_contents($legacyInlineShardFile) : false;
$legacyInlineShard = is_string($legacyInlineShardPayload) ? json_decode($legacyInlineShardPayload, true) : array();
$allOk = test_result(
	'Legacy inline comment state migrates to per-entry comment shards on write',
	isset($legacyInlineRead ['comments'] ['entry260314-180000:comment260314-180100'])
		&& $legacyInlineMigrated
		&& is_array($legacyInlineMain)
		&& isset($legacyInlineMain ['comments']) && $legacyInlineMain ['comments'] === array()
		&& isset($legacyInlineMain ['comment_shards'] ['storage'])
		&& (string) $legacyInlineMain ['comment_shards'] ['storage'] === 'per_entry'
		&& is_array($legacyInlineShard)
		&& isset($legacyInlineShard ['comments'] ['entry260314-180000:comment260314-180100'] ['remote_id']),
	json_encode(array(
		'main_comments_count' => is_array($legacyInlineMain) && isset($legacyInlineMain ['comments']) && is_array($legacyInlineMain ['comments']) ? count($legacyInlineMain ['comments']) : -1,
		'shard_exists' => is_string($legacyInlineShardPayload),
		'shard_comment_keys' => is_array($legacyInlineShard) && isset($legacyInlineShard ['comments']) && is_array($legacyInlineShard ['comments']) ? array_keys($legacyInlineShard ['comments']) : array()
	))
) && $allOk;

simulate_delete_recursive(PLUGIN_MASTODON_COMMENT_SHARD_DIR);
$legacyBackupFiles = glob(PLUGIN_MASTODON_STATE_DIR . 'state.json.migration-backup-*.json');
if (is_array($legacyBackupFiles)) {
	foreach ($legacyBackupFiles as $legacyBackupFile) {
		@unlink($legacyBackupFile);
	}
}
$nonContiguousLegacyState = plugin_mastodon_default_state();
plugin_mastodon_state_set_comment_mapping(
	$nonContiguousLegacyState,
	'entry260314-190000',
	'comment260314-190100',
	'legacy-noncontig-comment-a1',
	'remote',
	'legacy-noncontig-hash-a1',
	'https://example.social/@remote/legacy-noncontig-comment-a1',
	'2026-03-14 19:01:00',
	'',
	'legacy-noncontig-entry-a',
	'2026-03-14',
	'2026-03-14'
);
plugin_mastodon_state_set_comment_mapping(
	$nonContiguousLegacyState,
	'entry260314-200000',
	'comment260314-200100',
	'legacy-noncontig-comment-b1',
	'remote',
	'legacy-noncontig-hash-b1',
	'https://example.social/@remote/legacy-noncontig-comment-b1',
	'2026-03-14 20:01:00',
	'',
	'legacy-noncontig-entry-b',
	'2026-03-14',
	'2026-03-14'
);
plugin_mastodon_state_set_comment_mapping(
	$nonContiguousLegacyState,
	'entry260314-190000',
	'comment260314-190200',
	'legacy-noncontig-comment-a2',
	'remote',
	'legacy-noncontig-hash-a2',
	'https://example.social/@remote/legacy-noncontig-comment-a2',
	'2026-03-14 19:02:00',
	'',
	'legacy-noncontig-entry-a',
	'2026-03-14',
	'2026-03-14'
);
$nonContiguousPayload = json_encode(plugin_mastodon_state_normalize($nonContiguousLegacyState), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
plugin_mastodon_runtime_cache_clear('state');
plugin_mastodon_io_write_file(PLUGIN_MASTODON_STATE_FILE, (is_string($nonContiguousPayload) ? $nonContiguousPayload : '{}') . PHP_EOL);
$nonContiguousRead = plugin_mastodon_state_read();
$nonContiguousShardA = plugin_mastodon_state_read_comment_shard('entry260314-190000');
$nonContiguousShardB = plugin_mastodon_state_read_comment_shard('entry260314-200000');
$nonContiguousBackups = glob(PLUGIN_MASTODON_STATE_DIR . 'state.json.migration-backup-*.json');
$nonContiguousBackupPayload = is_array($nonContiguousBackups) && isset($nonContiguousBackups [0]) ? @file_get_contents($nonContiguousBackups [0]) : false;
$allOk = test_result(
	'Legacy inline migration preserves non-contiguous comments and creates a migration backup',
	isset($nonContiguousRead ['comments'] ['entry260314-190000:comment260314-190100'])
		&& isset($nonContiguousRead ['comments'] ['entry260314-190000:comment260314-190200'])
		&& isset($nonContiguousRead ['comments'] ['entry260314-200000:comment260314-200100'])
		&& isset($nonContiguousShardA ['entry260314-190000:comment260314-190100'])
		&& isset($nonContiguousShardA ['entry260314-190000:comment260314-190200'])
		&& isset($nonContiguousShardB ['entry260314-200000:comment260314-200100'])
		&& is_array($nonContiguousBackups)
		&& count($nonContiguousBackups) >= 1
		&& is_string($nonContiguousBackupPayload)
		&& strpos($nonContiguousBackupPayload, 'legacy-noncontig-comment-a1') !== false,
	json_encode(array(
		'shard_a_keys' => array_keys($nonContiguousShardA),
		'shard_b_keys' => array_keys($nonContiguousShardB),
		'backup_count' => is_array($nonContiguousBackups) ? count($nonContiguousBackups) : 0
	))
) && $allOk;

simulate_delete_recursive(PLUGIN_MASTODON_COMMENT_SHARD_DIR);
plugin_mastodon_state_write(plugin_mastodon_default_state());
$repairState = plugin_mastodon_default_state();
foreach (array('entry260314-210000' => 'repair-comment-a', 'entry260314-220000' => 'repair-comment-b') as $repairEntryId => $repairRemoteId) {
	plugin_mastodon_state_set_comment_mapping(
		$repairState,
		$repairEntryId,
		'comment' . substr($repairEntryId, 5, 6) . '-210100',
		$repairRemoteId,
		'remote',
		$repairRemoteId . '-hash',
		'https://example.social/@remote/' . $repairRemoteId,
		'2026-03-14 21:01:00',
		'',
		$repairEntryId . '-remote',
		'2026-03-14',
		'2026-03-14'
	);
}
$repairWriteOk = plugin_mastodon_state_write($repairState);
$repairMainPayload = @file_get_contents(PLUGIN_MASTODON_STATE_FILE);
$repairMain = is_string($repairMainPayload) ? json_decode($repairMainPayload, true) : array();
if (is_array($repairMain)) {
	if (isset($repairMain ['comment_shards'] ['entries'] ['entry260314-220000'])) {
		unset($repairMain ['comment_shards'] ['entries'] ['entry260314-220000']);
	}
	$repairMain ['comments_remote'] = array(
		'orphan-remote-comment' => array(
			'entry_id' => 'entry260314-230000',
			'comment_id' => 'comment260314-230100'
		)
	);
	plugin_mastodon_state_write_json_file(PLUGIN_MASTODON_STATE_FILE, json_encode($repairMain, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL);
}
plugin_mastodon_runtime_cache_clear('state');
$corruptRepairState = plugin_mastodon_state_read(array());
$diagnosticsBeforeRepair = plugin_mastodon_state_diagnose_comment_shards($corruptRepairState);
$repairResult = plugin_mastodon_state_repair_comment_shards($corruptRepairState, true);
plugin_mastodon_runtime_cache_clear('state');
$repairReloaded = plugin_mastodon_state_read(array());
$allOk = test_result(
	'Comment-shard diagnostics detect stale metadata and repair the reverse index from shards',
	$repairWriteOk
		&& empty($diagnosticsBeforeRepair ['ok'])
		&& !empty($diagnosticsBeforeRepair ['warnings'])
		&& !empty($repairResult ['repaired'])
		&& isset($repairReloaded ['comment_shards'] ['entries'] ['entry260314-210000'])
		&& isset($repairReloaded ['comment_shards'] ['entries'] ['entry260314-220000'])
		&& isset($repairReloaded ['comments_remote'] ['repair-comment-a'])
		&& isset($repairReloaded ['comments_remote'] ['repair-comment-b'])
		&& !isset($repairReloaded ['comments_remote'] ['orphan-remote-comment']),
	json_encode(array(
		'warnings_before' => isset($diagnosticsBeforeRepair ['warnings']) ? $diagnosticsBeforeRepair ['warnings'] : array(),
		'repaired' => isset($repairResult ['repaired']) ? $repairResult ['repaired'] : null,
		'remote_keys_after' => isset($repairReloaded ['comments_remote']) && is_array($repairReloaded ['comments_remote']) ? array_keys($repairReloaded ['comments_remote']) : array()
	))
) && $allOk;

ob_start();
$cliDiagnoseExit = plugin_mastodon_cli_comment_shard_maintenance(array('diagnose'));
$cliDiagnoseOutput = (string) ob_get_clean();
$allOk = test_result(
	'CLI comment-shard diagnostics entry point returns a machine-checkable status',
	$cliDiagnoseExit === 0
		&& strpos($cliDiagnoseOutput, 'Mastodon comment-shard diagnostics') !== false
		&& strpos($cliDiagnoseOutput, 'shard_files:') !== false,
	json_encode(array(
		'exit' => $cliDiagnoseExit,
		'output' => $cliDiagnoseOutput
	))
) && $allOk;

simulate_delete_recursive(PLUGIN_MASTODON_COMMENT_SHARD_DIR);
plugin_mastodon_state_write(plugin_mastodon_default_state());
$shardFailureState = plugin_mastodon_default_state();
plugin_mastodon_state_set_comment_mapping(
	$shardFailureState,
	'entry260314-230000',
	'comment260314-230100',
	'shard-failure-comment',
	'remote',
	'shard-failure-hash',
	'https://example.social/@remote/shard-failure-comment',
	'2026-03-14 23:01:00',
	'',
	'shard-failure-entry',
	'2026-03-14',
	'2026-03-14'
);
$GLOBALS ['plugin_mastodon_test_write_json_fail_for'] = array('entry260314-230000.json');
$shardFailureWriteOk = plugin_mastodon_state_write($shardFailureState);
$shardFailureLastWriteError = plugin_mastodon_state_write_last_error();
unset($GLOBALS ['plugin_mastodon_test_write_json_fail_for']);
plugin_mastodon_runtime_cache_clear('state');
$shardFailureReloaded = plugin_mastodon_state_read();
$shardFailureFile = plugin_mastodon_state_comment_shard_file('entry260314-230000');
$allOk = test_result(
	'Shard-write failures leave the main state unchanged and return failure',
	!$shardFailureWriteOk
		&& strpos($shardFailureLastWriteError, 'comment_shard_write_failed') === 0
		&& !@is_file($shardFailureFile)
		&& !isset($shardFailureReloaded ['comments_remote'] ['shard-failure-comment']),
	json_encode(array(
		'last_write_error' => $shardFailureLastWriteError,
		'shard_file_exists' => @is_file($shardFailureFile) ? 1 : 0,
		'remote_keys_after' => isset($shardFailureReloaded ['comments_remote']) && is_array($shardFailureReloaded ['comments_remote']) ? array_keys($shardFailureReloaded ['comments_remote']) : array()
	))
) && $allOk;

simulate_delete_recursive(PLUGIN_MASTODON_COMMENT_SHARD_DIR);
plugin_mastodon_state_write(plugin_mastodon_default_state());
$mainFailureState = plugin_mastodon_default_state();
plugin_mastodon_state_set_comment_mapping(
	$mainFailureState,
	'entry260315-000000',
	'comment260315-000100',
	'main-failure-comment',
	'remote',
	'main-failure-hash',
	'https://example.social/@remote/main-failure-comment',
	'2026-03-15 00:01:00',
	'',
	'main-failure-entry',
	'2026-03-15',
	'2026-03-15'
);
$GLOBALS ['plugin_mastodon_test_write_json_fail_for'] = array('state.json');
$mainFailureWriteOk = plugin_mastodon_state_write($mainFailureState);
$mainFailureLastWriteError = plugin_mastodon_state_write_last_error();
unset($GLOBALS ['plugin_mastodon_test_write_json_fail_for']);
$mainFailureShard = plugin_mastodon_state_read_comment_shard('entry260315-000000');
plugin_mastodon_runtime_cache_clear('state');
$mainFailureRepair = plugin_mastodon_state_repair_comment_shards(null, true);
plugin_mastodon_runtime_cache_clear('state');
$mainFailureReloaded = plugin_mastodon_state_read(array());
$allOk = test_result(
	'Main-state write failures after shard writes remain repairable from shard files',
	!$mainFailureWriteOk
		&& $mainFailureLastWriteError === 'state_main_write_failed'
		&& isset($mainFailureShard ['entry260315-000000:comment260315-000100'])
		&& !empty($mainFailureRepair ['repaired'])
		&& isset($mainFailureReloaded ['comments_remote'] ['main-failure-comment'])
		&& isset($mainFailureReloaded ['comment_shards'] ['entries'] ['entry260315-000000']),
	json_encode(array(
		'last_write_error' => $mainFailureLastWriteError,
		'shard_keys' => array_keys($mainFailureShard),
		'repair_errors' => isset($mainFailureRepair ['errors']) ? $mainFailureRepair ['errors'] : array(),
		'remote_keys_after' => isset($mainFailureReloaded ['comments_remote']) && is_array($mainFailureReloaded ['comments_remote']) ? array_keys($mainFailureReloaded ['comments_remote']) : array()
	))
) && $allOk;

$legacyStatsState = array(
	'version' => 2,
	'last_run' => '2026-03-14 13:00:00',
	'last_deletion_run' => '2026-03-14 13:05:00',
	'deletions_pending' => 0,
	'last_error' => '',
	'last_remote_status_id' => '',
	'entries' => array(),
	'entries_remote' => array(),
	'comments' => array(),
	'comments_remote' => array(),
	'stats' => array(
		'imported_entries' => 4,
		'exported_comments' => 3,
		'deleted_remote_entries' => 2,
		'deleted_local_comments' => 1
	)
);
plugin_mastodon_runtime_cache_clear('state');
plugin_mastodon_io_write_file(PLUGIN_MASTODON_STATE_FILE, json_encode($legacyStatsState, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL);
$legacyStatsReloaded = plugin_mastodon_state_read();
$allOk = test_result(
	'Legacy combined stats are migrated into separate content and deletion counters',
	isset($legacyStatsReloaded ['content_stats'] ['imported_entries']) && (int) $legacyStatsReloaded ['content_stats'] ['imported_entries'] === 4
		&& isset($legacyStatsReloaded ['content_stats'] ['updated_local_comments']) && (int) $legacyStatsReloaded ['content_stats'] ['updated_local_comments'] === 0
		&& isset($legacyStatsReloaded ['content_stats'] ['exported_comments']) && (int) $legacyStatsReloaded ['content_stats'] ['exported_comments'] === 3
		&& isset($legacyStatsReloaded ['deletion_stats'] ['deleted_remote_entries']) && (int) $legacyStatsReloaded ['deletion_stats'] ['deleted_remote_entries'] === 2
		&& isset($legacyStatsReloaded ['deletion_stats'] ['deleted_local_comments']) && (int) $legacyStatsReloaded ['deletion_stats'] ['deleted_local_comments'] === 1
		&& !isset($legacyStatsReloaded ['stats']),
	json_encode($legacyStatsReloaded)
) && $allOk;
plugin_mastodon_state_write(plugin_mastodon_default_state());

$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
plugin_mastodon_runtime_cache_clear();
$instanceLimitOne = plugin_mastodon_instance_character_limit($options);
$instanceLimitTwo = plugin_mastodon_instance_media_limit($options);
$instanceConfigRequests = 0;
if (simulate_recorded_http_requests() !== array()) {
	foreach (simulate_recorded_http_requests() as $request) {
		if (is_array($request) && !empty($request ['url']) && (string) $request ['url'] === $instanceUrl . '/api/v2/instance') {
			$instanceConfigRequests++;
		}
	}
}
$allOk = test_result(
	'Instance configuration cache avoids repeated /api/v2/instance requests',
	$instanceLimitOne === 500 && $instanceLimitTwo === 4 && $instanceConfigRequests === 1,
	'instance_requests=' . $instanceConfigRequests
) && $allOk;
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$instanceCapabilityHttpResponsesBackup = $GLOBALS ['plugin_mastodon_test_http_responses'];

$nightlyCapabilityOptions = plugin_mastodon_clear_saved_instance_info(simulate_seed_options_from_config($options));
$nightlyCapabilityOptions ['instance_url'] = 'https://nightly.example';
$nightlyCapabilityOptions ['instance_info_url'] = 'https://nightly.example';
$nightlyCapabilityJson = json_encode(array(
	'version' => '4.6.0-nightly.2026-06-02',
	'api_versions' => array('mastodon' => 7)
));
$nightlyCapabilityOptions ['instance_info_json'] = is_string($nightlyCapabilityJson) ? $nightlyCapabilityJson : '';
plugin_mastodon_runtime_cache_clear();
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array();
$nightlyMediaAttributesSupported = plugin_mastodon_instance_supports_status_media_attributes($nightlyCapabilityOptions);
$nightlyDeleteMediaSupported = plugin_mastodon_instance_supports_status_delete_media($nightlyCapabilityOptions);
$nightlyCapabilityRequests = 0;
foreach (simulate_recorded_http_requests() as $request) {
	if (is_array($request) && !empty($request ['url']) && (string) $request ['url'] === 'https://nightly.example/api/v2/instance') {
		$nightlyCapabilityRequests++;
	}
}
$allOk = test_result(
	'Nightly Mastodon versions use cached api_versions for status edit and delete capabilities',
	$nightlyMediaAttributesSupported === true
		&& $nightlyDeleteMediaSupported === true
		&& $nightlyCapabilityRequests === 0,
	json_encode(array(
		'media_attributes_supported' => $nightlyMediaAttributesSupported,
		'delete_media_supported' => $nightlyDeleteMediaSupported,
		'instance_requests' => $nightlyCapabilityRequests
	))
) && $allOk;

$apiVersionOverrideOptions = plugin_mastodon_clear_saved_instance_info(simulate_seed_options_from_config($options));
$apiVersionOverrideOptions ['instance_url'] = 'https://api-version-override.example';
$apiVersionOverrideOptions ['instance_info_url'] = 'https://api-version-override.example';
$apiVersionOverrideJson = json_encode(array(
	'version' => '3.5.0+hometown-compat',
	'api_versions' => array('mastodon' => 4)
));
$apiVersionOverrideOptions ['instance_info_json'] = is_string($apiVersionOverrideJson) ? $apiVersionOverrideJson : '';
plugin_mastodon_runtime_cache_clear();
$apiOverrideMediaAttributesSupported = plugin_mastodon_instance_supports_status_media_attributes($apiVersionOverrideOptions);
$apiOverrideDeleteMediaSupported = plugin_mastodon_instance_supports_status_delete_media($apiVersionOverrideOptions);
$allOk = test_result(
	'Machine-readable api_versions are preferred over complex human-readable Mastodon version strings',
	$apiOverrideMediaAttributesSupported === true
		&& $apiOverrideDeleteMediaSupported === true,
	json_encode(array(
		'media_attributes_supported' => $apiOverrideMediaAttributesSupported,
		'delete_media_supported' => $apiOverrideDeleteMediaSupported
	))
) && $allOk;

$apiVersionLegacyDeleteOptions = plugin_mastodon_clear_saved_instance_info(simulate_seed_options_from_config($options));
$apiVersionLegacyDeleteOptions ['instance_url'] = 'https://api-version-legacy-delete.example';
$apiVersionLegacyDeleteOptions ['instance_info_url'] = 'https://api-version-legacy-delete.example';
$apiVersionLegacyDeleteJson = json_encode(array(
	'version' => '4.6.0-nightly.2026-06-02',
	'api_versions' => array('mastodon' => 3)
));
$apiVersionLegacyDeleteOptions ['instance_info_json'] = is_string($apiVersionLegacyDeleteJson) ? $apiVersionLegacyDeleteJson : '';
$GLOBALS ['plugin_mastodon_test_rate_limit_budgets'] = array('requests' => 10, 'media_uploads' => 24, 'deletes' => 1, 'remote_remaining_floor' => 0);
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'DELETE https://api-version-legacy-delete.example/api/v1/statuses/api-version-delete-1' => array('ok' => true, 'code' => 200, 'headers' => array('X-RateLimit-Remaining' => '298'), 'body' => '{}')
);
plugin_mastodon_runtime_cache_clear();
plugin_mastodon_rate_limit_guard_start('simulation_api_version_legacy_delete_without_media_parameter');
$apiVersionLegacyDeleteResult = plugin_mastodon_delete_status($apiVersionLegacyDeleteOptions, 'api-version-delete-1', true);
$apiVersionLegacyDeleteSummary = plugin_mastodon_rate_limit_guard_summary();
plugin_mastodon_rate_limit_guard_stop();
$apiVersionLegacyDeleteRequests = simulate_recorded_http_requests();
$allOk = test_result(
	'api_versions below delete_media support suppress the delete_media query even with a nightly version string',
	!empty($apiVersionLegacyDeleteResult ['ok'])
		&& count($apiVersionLegacyDeleteRequests) === 1
		&& isset($apiVersionLegacyDeleteRequests [0] ['url']) && (string) $apiVersionLegacyDeleteRequests [0] ['url'] === 'https://api-version-legacy-delete.example/api/v1/statuses/api-version-delete-1'
		&& isset($apiVersionLegacyDeleteSummary ['deletes_used']) && (int) $apiVersionLegacyDeleteSummary ['deletes_used'] === 1,
	json_encode(array(
		'result' => $apiVersionLegacyDeleteResult,
		'summary' => $apiVersionLegacyDeleteSummary,
		'requests' => $apiVersionLegacyDeleteRequests
	))
) && $allOk;

$apiVersionLegacyMediaDeleteOptions = plugin_mastodon_clear_saved_instance_info(simulate_seed_options_from_config($options));
$apiVersionLegacyMediaDeleteOptions ['instance_url'] = 'https://api-version-legacy-media-delete.example';
$apiVersionLegacyMediaDeleteOptions ['instance_info_url'] = 'https://api-version-legacy-media-delete.example';
$apiVersionLegacyMediaDeleteJson = json_encode(array(
	'version' => '4.6.0-nightly.2026-06-02',
	'api_versions' => array('mastodon' => 3)
));
$apiVersionLegacyMediaDeleteOptions ['instance_info_json'] = is_string($apiVersionLegacyMediaDeleteJson) ? $apiVersionLegacyMediaDeleteJson : '';
plugin_mastodon_runtime_cache_clear();
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array();
$apiVersionLegacyMediaCleanup = plugin_mastodon_cleanup_uploaded_media($apiVersionLegacyMediaDeleteOptions, array('legacy-media-delete-1', 'legacy-media-delete-2'));
$apiVersionLegacyMediaDeleteRequests = simulate_recorded_http_requests();
$allOk = test_result(
	'api_versions below unattached media delete support skip uploaded media cleanup DELETE requests',
	!empty($apiVersionLegacyMediaCleanup ['ok'])
		&& isset($apiVersionLegacyMediaCleanup ['deleted']) && $apiVersionLegacyMediaCleanup ['deleted'] === array()
		&& isset($apiVersionLegacyMediaCleanup ['skipped']) && $apiVersionLegacyMediaCleanup ['skipped'] === array('legacy-media-delete-1', 'legacy-media-delete-2')
		&& isset($apiVersionLegacyMediaCleanup ['failed']) && $apiVersionLegacyMediaCleanup ['failed'] === array()
		&& count($apiVersionLegacyMediaDeleteRequests) === 0,
	json_encode(array(
		'cleanup' => $apiVersionLegacyMediaCleanup,
		'requests' => $apiVersionLegacyMediaDeleteRequests
	))
) && $allOk;

$versionLegacyMediaDeleteOptions = plugin_mastodon_clear_saved_instance_info(simulate_seed_options_from_config($options));
$versionLegacyMediaDeleteOptions ['instance_url'] = 'https://version-legacy-media-delete.example';
$versionLegacyMediaDeleteOptions ['instance_info_url'] = 'https://version-legacy-media-delete.example';
$versionLegacyMediaDeleteJson = json_encode(array('version' => '4.3.9'));
$versionLegacyMediaDeleteOptions ['instance_info_json'] = is_string($versionLegacyMediaDeleteJson) ? $versionLegacyMediaDeleteJson : '';
plugin_mastodon_runtime_cache_clear();
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array();
$versionLegacyMediaDelete = plugin_mastodon_delete_media_attachment($versionLegacyMediaDeleteOptions, 'version-legacy-media-delete-1');
$versionLegacyMediaDeleteRequests = simulate_recorded_http_requests();
$allOk = test_result(
	'Cached Mastodon versions before 4.4 skip unattached media DELETE cleanup requests',
	!empty($versionLegacyMediaDelete ['ok'])
		&& !empty($versionLegacyMediaDelete ['skipped'])
		&& count($versionLegacyMediaDeleteRequests) === 0,
	json_encode(array(
		'response' => $versionLegacyMediaDelete,
		'requests' => $versionLegacyMediaDeleteRequests
	))
) && $allOk;

$unknownMediaDeleteOptions = plugin_mastodon_clear_saved_instance_info(simulate_seed_options_from_config($options));
$unknownMediaDeleteOptions ['instance_url'] = 'https://unknown-media-delete.example';
plugin_mastodon_runtime_cache_clear();
$GLOBALS ['plugin_mastodon_test_rate_limit_budgets'] = array('requests' => 10, 'media_uploads' => 24, 'deletes' => 2, 'remote_remaining_floor' => 0);
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'DELETE https://unknown-media-delete.example/api/v1/media/unknown-media-delete-1' => array('ok' => true, 'code' => 200, 'headers' => array('X-RateLimit-Remaining' => '298'), 'body' => '')
);
plugin_mastodon_rate_limit_guard_start('simulation_unknown_media_delete_best_effort');
$unknownMediaDeleteResult = plugin_mastodon_delete_media_attachment($unknownMediaDeleteOptions, 'unknown-media-delete-1');
$unknownMediaDeleteSummary = plugin_mastodon_rate_limit_guard_summary();
plugin_mastodon_rate_limit_guard_stop();
$unknownMediaDeleteRequests = simulate_recorded_http_requests();
$unknownMediaDeleteInstanceRequests = 0;
$unknownMediaDeleteActualDeletes = 0;
foreach ($unknownMediaDeleteRequests as $request) {
	if (is_array($request) && !empty($request ['url']) && (string) $request ['url'] === 'https://unknown-media-delete.example/api/v2/instance') {
		$unknownMediaDeleteInstanceRequests++;
	}
	if (is_array($request) && !empty($request ['method']) && strtoupper((string) $request ['method']) === 'DELETE' && !empty($request ['url']) && (string) $request ['url'] === 'https://unknown-media-delete.example/api/v1/media/unknown-media-delete-1') {
		$unknownMediaDeleteActualDeletes++;
	}
}
$allOk = test_result(
	'Unknown unattached media delete capability stays best-effort without an instance lookup',
	!empty($unknownMediaDeleteResult ['ok'])
		&& $unknownMediaDeleteInstanceRequests === 0
		&& $unknownMediaDeleteActualDeletes === 1,
	json_encode(array(
		'response' => $unknownMediaDeleteResult,
		'summary' => $unknownMediaDeleteSummary,
		'instance_requests' => $unknownMediaDeleteInstanceRequests,
		'delete_requests' => $unknownMediaDeleteActualDeletes,
		'requests' => $unknownMediaDeleteRequests
	))
) && $allOk;

$apiVersionSupportedMediaDeleteOptions = plugin_mastodon_clear_saved_instance_info(simulate_seed_options_from_config($options));
$apiVersionSupportedMediaDeleteOptions ['instance_url'] = 'https://api-version-media-delete.example';
$apiVersionSupportedMediaDeleteOptions ['instance_info_url'] = 'https://api-version-media-delete.example';
$apiVersionSupportedMediaDeleteJson = json_encode(array(
	'version' => '4.4+hometown-123',
	'api_versions' => array('mastodon' => 4)
));
$apiVersionSupportedMediaDeleteOptions ['instance_info_json'] = is_string($apiVersionSupportedMediaDeleteJson) ? $apiVersionSupportedMediaDeleteJson : '';
plugin_mastodon_runtime_cache_clear();
$GLOBALS ['plugin_mastodon_test_rate_limit_budgets'] = array('requests' => 10, 'media_uploads' => 24, 'deletes' => 2, 'remote_remaining_floor' => 0);
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'DELETE https://api-version-media-delete.example/api/v1/media/api-version-media-delete-1' => array('ok' => true, 'code' => 200, 'headers' => array('X-RateLimit-Remaining' => '298'), 'body' => '')
);
plugin_mastodon_rate_limit_guard_start('simulation_api_version_media_delete_supported');
$apiVersionSupportedMediaDeleteResult = plugin_mastodon_delete_media_attachment($apiVersionSupportedMediaDeleteOptions, 'api-version-media-delete-1');
$apiVersionSupportedMediaDeleteSummary = plugin_mastodon_rate_limit_guard_summary();
plugin_mastodon_rate_limit_guard_stop();
$apiVersionSupportedMediaDeleteRequests = simulate_recorded_http_requests();
$apiVersionSupportedMediaDeleteActualDeletes = 0;
foreach ($apiVersionSupportedMediaDeleteRequests as $request) {
	if (is_array($request) && !empty($request ['method']) && strtoupper((string) $request ['method']) === 'DELETE' && !empty($request ['url']) && (string) $request ['url'] === 'https://api-version-media-delete.example/api/v1/media/api-version-media-delete-1') {
		$apiVersionSupportedMediaDeleteActualDeletes++;
	}
}
$allOk = test_result(
	'Mastodon API version 4 deletes unattached uploaded media during cleanup',
	!empty($apiVersionSupportedMediaDeleteResult ['ok'])
		&& $apiVersionSupportedMediaDeleteActualDeletes === 1,
	json_encode(array(
		'response' => $apiVersionSupportedMediaDeleteResult,
		'summary' => $apiVersionSupportedMediaDeleteSummary,
		'delete_requests' => $apiVersionSupportedMediaDeleteActualDeletes,
		'requests' => $apiVersionSupportedMediaDeleteRequests
	))
) && $allOk;

$compactAccountsInstance = plugin_mastodon_compact_instance_document(array(
	'domain' => 'accounts.example',
	'version' => '4.6.0',
	'api_versions' => array('mastodon' => 10),
	'configuration' => array(
		'statuses' => array('max_characters' => 500, 'max_media_attachments' => 4),
		'accounts' => array(
			'max_display_name_length' => 30,
			'max_note_length' => 500,
			'max_featured_tags' => 10,
			'max_pinned_statuses' => 5,
			'max_profile_fields' => 4,
			'profile_field_name_limit' => 255,
			'profile_field_value_limit' => 2047,
			'ignored_future_field' => 'kept out intentionally'
		)
	)
));
$compactAccountsConfig = (isset($compactAccountsInstance ['configuration'] ['accounts']) && is_array($compactAccountsInstance ['configuration'] ['accounts'])) ? $compactAccountsInstance ['configuration'] ['accounts'] : array();
$allOk = test_result(
	'Mastodon 4.6 configuration.accounts limits survive compact instance snapshots',
	isset($compactAccountsConfig ['max_display_name_length']) && (int) $compactAccountsConfig ['max_display_name_length'] === 30
		&& isset($compactAccountsConfig ['max_note_length']) && (int) $compactAccountsConfig ['max_note_length'] === 500
		&& isset($compactAccountsConfig ['max_featured_tags']) && (int) $compactAccountsConfig ['max_featured_tags'] === 10
		&& isset($compactAccountsConfig ['max_pinned_statuses']) && (int) $compactAccountsConfig ['max_pinned_statuses'] === 5
		&& isset($compactAccountsConfig ['max_profile_fields']) && (int) $compactAccountsConfig ['max_profile_fields'] === 4
		&& isset($compactAccountsConfig ['profile_field_name_limit']) && (int) $compactAccountsConfig ['profile_field_name_limit'] === 255
		&& isset($compactAccountsConfig ['profile_field_value_limit']) && (int) $compactAccountsConfig ['profile_field_value_limit'] === 2047
		&& !isset($compactAccountsConfig ['ignored_future_field']),
	json_encode($compactAccountsConfig)
) && $allOk;

$excludeDirectNightlyOptions = plugin_mastodon_clear_saved_instance_info(simulate_seed_options_from_config($options));
$excludeDirectNightlyOptions ['instance_url'] = 'https://exclude-direct-nightly.example';
$excludeDirectNightlyOptions ['instance_info_url'] = 'https://exclude-direct-nightly.example';
$excludeDirectNightlyJson = json_encode(array('version' => '4.6.0-nightly.2026-06-02'));
$excludeDirectNightlyOptions ['instance_info_json'] = is_string($excludeDirectNightlyJson) ? $excludeDirectNightlyJson : '';
plugin_mastodon_runtime_cache_clear();
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET https://exclude-direct-nightly.example/api/v1/accounts/acct-nightly/statuses?limit=40&exclude_reblogs=true&exclude_replies=true&exclude_direct=true&since_id=since-nightly' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			array('id' => 'nightly-public', 'visibility' => 'public')
		))
	)
);
$excludeDirectNightlyStatuses = plugin_mastodon_fetch_account_statuses($excludeDirectNightlyOptions, 'acct-nightly', 'since-nightly');
$excludeDirectNightlyRequests = simulate_recorded_http_requests();
$excludeDirectNightlyUrl = isset($excludeDirectNightlyRequests [0] ['url']) ? (string) $excludeDirectNightlyRequests [0] ['url'] : '';
$allOk = test_result(
	'Mastodon 4.6.0-nightly account-status import sends exclude_direct for privacy',
	count($excludeDirectNightlyStatuses) === 1
		&& strpos($excludeDirectNightlyUrl, 'exclude_direct=true') !== false,
	json_encode(array(
		'statuses' => $excludeDirectNightlyStatuses,
		'requests' => $excludeDirectNightlyRequests
	))
) && $allOk;

$excludeDirectApiOptions = plugin_mastodon_clear_saved_instance_info(simulate_seed_options_from_config($options));
$excludeDirectApiOptions ['instance_url'] = 'https://exclude-direct-api.example';
$excludeDirectApiOptions ['instance_info_url'] = 'https://exclude-direct-api.example';
$excludeDirectApiJson = json_encode(array(
	'version' => '4.5.9+hometown-compat',
	'api_versions' => array('mastodon' => 10)
));
$excludeDirectApiOptions ['instance_info_json'] = is_string($excludeDirectApiJson) ? $excludeDirectApiJson : '';
plugin_mastodon_runtime_cache_clear();
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET https://exclude-direct-api.example/api/v1/accounts/acct-api/statuses?limit=40&exclude_reblogs=true&exclude_replies=true&exclude_direct=true&since_id=since-api' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			array('id' => 'api-public', 'visibility' => 'public')
		))
	)
);
$excludeDirectApiStatuses = plugin_mastodon_fetch_account_statuses($excludeDirectApiOptions, 'acct-api', 'since-api');
$excludeDirectApiRequests = simulate_recorded_http_requests();
$excludeDirectApiUrl = isset($excludeDirectApiRequests [0] ['url']) ? (string) $excludeDirectApiRequests [0] ['url'] : '';
$allOk = test_result(
	'Mastodon API version 10 account-status import sends exclude_direct even on forked version strings',
	count($excludeDirectApiStatuses) === 1
		&& strpos($excludeDirectApiUrl, 'exclude_direct=true') !== false
		&& strpos($excludeDirectApiUrl, 'since_id=since-api') !== false,
	json_encode(array(
		'statuses' => $excludeDirectApiStatuses,
		'requests' => $excludeDirectApiRequests
	))
) && $allOk;

$excludeDirectFallbackOptions = plugin_mastodon_clear_saved_instance_info(simulate_seed_options_from_config($options));
$excludeDirectFallbackOptions ['instance_url'] = 'https://exclude-direct-fallback.example';
$excludeDirectFallbackOptions ['instance_info_url'] = 'https://exclude-direct-fallback.example';
$excludeDirectFallbackJson = json_encode(array('version' => '4.6.0'));
$excludeDirectFallbackOptions ['instance_info_json'] = is_string($excludeDirectFallbackJson) ? $excludeDirectFallbackJson : '';
plugin_mastodon_runtime_cache_clear();
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET https://exclude-direct-fallback.example/api/v1/accounts/acct-fallback/statuses?limit=40&exclude_reblogs=true&exclude_replies=true&exclude_direct=true&since_id=since-fallback' => array(
		'ok' => false,
		'code' => 422,
		'body' => json_encode(array('error' => 'Unknown parameter: exclude_direct'))
	),
	'GET https://exclude-direct-fallback.example/api/v1/accounts/acct-fallback/statuses?limit=40&exclude_reblogs=true&exclude_replies=true&since_id=since-fallback' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			array('id' => 'fallback-public', 'visibility' => 'public')
		))
	)
);
$excludeDirectFallbackStatuses = plugin_mastodon_fetch_account_statuses($excludeDirectFallbackOptions, 'acct-fallback', 'since-fallback');
$excludeDirectFallbackRequests = simulate_recorded_http_requests();
$excludeDirectFallbackFirstUrl = isset($excludeDirectFallbackRequests [0] ['url']) ? (string) $excludeDirectFallbackRequests [0] ['url'] : '';
$excludeDirectFallbackSecondUrl = isset($excludeDirectFallbackRequests [1] ['url']) ? (string) $excludeDirectFallbackRequests [1] ['url'] : '';
$allOk = test_result(
	'Account-status import retries without exclude_direct when a compatible server rejects the parameter',
	count($excludeDirectFallbackStatuses) === 1
		&& isset($excludeDirectFallbackStatuses [0] ['id']) && (string) $excludeDirectFallbackStatuses [0] ['id'] === 'fallback-public'
		&& count($excludeDirectFallbackRequests) === 2
		&& strpos($excludeDirectFallbackFirstUrl, 'exclude_direct=true') !== false
		&& strpos($excludeDirectFallbackSecondUrl, 'exclude_direct=true') === false,
	json_encode(array(
		'statuses' => $excludeDirectFallbackStatuses,
		'requests' => $excludeDirectFallbackRequests
	))
) && $allOk;

$excludeDirectLegacyOptions = plugin_mastodon_clear_saved_instance_info(simulate_seed_options_from_config($options));
$excludeDirectLegacyOptions ['instance_url'] = 'https://exclude-direct-legacy.example';
$excludeDirectLegacyOptions ['instance_info_url'] = 'https://exclude-direct-legacy.example';
$excludeDirectLegacyJson = json_encode(array(
	'version' => '4.5.9',
	'api_versions' => array('mastodon' => 9)
));
$excludeDirectLegacyOptions ['instance_info_json'] = is_string($excludeDirectLegacyJson) ? $excludeDirectLegacyJson : '';
plugin_mastodon_runtime_cache_clear();
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET https://exclude-direct-legacy.example/api/v1/accounts/acct-legacy/statuses?limit=40&exclude_reblogs=true&exclude_replies=true&since_id=since-legacy' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			array('id' => 'legacy-public', 'visibility' => 'public')
		))
	)
);
$excludeDirectLegacyStatuses = plugin_mastodon_fetch_account_statuses($excludeDirectLegacyOptions, 'acct-legacy', 'since-legacy');
$excludeDirectLegacyRequests = simulate_recorded_http_requests();
$excludeDirectLegacyUrl = isset($excludeDirectLegacyRequests [0] ['url']) ? (string) $excludeDirectLegacyRequests [0] ['url'] : '';
$allOk = test_result(
	'Cached Mastodon versions before 4.6 keep account-status import compatible without exclude_direct',
	count($excludeDirectLegacyStatuses) === 1
		&& strpos($excludeDirectLegacyUrl, 'exclude_direct=true') === false,
	json_encode(array(
		'statuses' => $excludeDirectLegacyStatuses,
		'requests' => $excludeDirectLegacyRequests
	))
) && $allOk;

$failedInstanceOptions = plugin_mastodon_clear_saved_instance_info(simulate_seed_options_from_config($options));
$failedInstanceOptions ['instance_url'] = 'https://offline-instance.example';
$failedInstanceOptions ['access_token'] = 'token-offline-instance';
plugin_mastodon_runtime_cache_clear();
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET https://offline-instance.example/api/v2/instance' => array('ok' => false, 'code' => 503, 'body' => json_encode(array('error' => 'maintenance')), 'error' => 'maintenance')
);
$failedCharacterLimit = plugin_mastodon_instance_character_limit($failedInstanceOptions);
$failedMediaLimit = plugin_mastodon_instance_media_limit($failedInstanceOptions);
$failedUrlReservedLength = plugin_mastodon_instance_url_reserved_length($failedInstanceOptions);
$failedDescriptionLimit = plugin_mastodon_instance_media_description_limit($failedInstanceOptions);
$failedMediaAttributesSupported = plugin_mastodon_instance_supports_status_media_attributes($failedInstanceOptions);
$failedInstanceRequests = 0;
foreach (simulate_recorded_http_requests() as $request) {
	if (is_array($request) && !empty($request ['url']) && (string) $request ['url'] === 'https://offline-instance.example/api/v2/instance') {
		$failedInstanceRequests++;
	}
}
$allOk = test_result(
	'Failed instance-information lookups are negatively cached per request and fall back to defaults',
	$failedCharacterLimit === 500
		&& $failedMediaLimit === 4
		&& $failedUrlReservedLength === 23
		&& $failedDescriptionLimit === 1500
		&& $failedMediaAttributesSupported === false
		&& $failedInstanceRequests === 1,
	json_encode(array(
		'character_limit' => $failedCharacterLimit,
		'media_limit' => $failedMediaLimit,
		'url_reserved_length' => $failedUrlReservedLength,
		'description_limit' => $failedDescriptionLimit,
		'media_attributes_supported' => $failedMediaAttributesSupported,
		'instance_requests' => $failedInstanceRequests
	))
) && $allOk;
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = $instanceCapabilityHttpResponsesBackup;
unset($GLOBALS ['plugin_mastodon_test_rate_limit_budgets']);
plugin_mastodon_rate_limit_window_clear();
plugin_mastodon_runtime_cache_clear();

$urlReservedLength = plugin_mastodon_instance_url_reserved_length($options);
$urlAwareText = 'Alpha ' . $instanceUrl . '/this/is/a/very/very/long/path/that-remains-a-single-link';
$urlAwareLimited = plugin_mastodon_limit_status_text($urlAwareText, 29, $urlReservedLength);
$urlAwareComment = plugin_mastodon_build_comment_status_text(
	$welcomeEntryId,
	$welcomeEntry,
	array(
		'id' => 'url-aware-comment',
		'name' => 'URL Budget Tester',
		'content' => 'Kurznotiz',
		'public_url' => 'https://www.flatpress.org/comments.php?entry=entry260410-161431&replyto=comment260412-001722&very=long-path-fragment'
	),
	70
);
$allOk = test_result(
	'Mastodon URL budgeting respects characters_reserved_per_url when limiting plain-text exports',
	$urlReservedLength === 23
		&& plugin_mastodon_status_text_length($urlAwareText, $urlReservedLength) === 29
		&& $urlAwareLimited === $urlAwareText
		&& strpos($urlAwareComment, 'replyto=comment260412-001722') !== false
		&& plugin_mastodon_status_text_length($urlAwareComment, $urlReservedLength) <= 70,
	json_encode(array(
		'reserved' => $urlReservedLength,
		'limited' => $urlAwareLimited,
		'comment' => $urlAwareComment,
		'comment_length' => plugin_mastodon_status_text_length($urlAwareComment, $urlReservedLength)
	))
) && $allOk;
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();

if (simulate_request_flag('live-auth')) {
	$liveOptions = plugin_mastodon_get_options();
	if (!empty($configuredOptions ['instance_url'])) {
		$liveOptions ['instance_url'] = $configuredOptions ['instance_url'];
	}
	if (!empty($configuredOptions ['access_token'])) {
		$liveOptions ['access_token'] = $configuredOptions ['access_token'];
	}
	$verifyResponse = plugin_mastodon_verify_credentials($liveOptions);
	$allOk = test_result(
		'Configured credentials verify (read-only smoke test)',
		!empty($configuredOptions ['instance_url']) && !empty($configuredOptions ['access_token']) && $verifyResponse ['ok'] && !empty($verifyResponse ['json'] ['id']),
		isset($verifyResponse ['code']) ? (string) $verifyResponse ['code'] : ''
	) && $allOk;
}

$misleadingPost = array('subject' => '[url=https://mastodon.social/tags/flatpress]#FlatPress[/url]');
$GLOBALS ['post'] = $misleadingPost;
$welcomeStatus = plugin_mastodon_build_entry_status_text($welcomeEntryId, $welcomeEntry, 500);
unset($GLOBALS ['post']);

$allOk = test_result(
	'Entry export uses clean Mastodon text and suppresses localhost permalinks',
	strpos($welcomeStatus, 'target=_blank') === false && strpos($welcomeStatus, 'rel=external') === false && strpos($welcomeStatus, 'urlhttps') === false && strpos($welcomeStatus, 'localhost') === false && strpos($welcomeStatus, 'https://www.flatpress.org') !== false,
	$welcomeStatus
) && $allOk;

$commentStatus = plugin_mastodon_build_comment_status_text($welcomeEntryId, $welcomeEntry, array('name' => 'Frank Hochmuth', 'content' => 'Kommentar :smile:'), 500);
$expectedReplyPrefix = sprintf(plugin_mastodon_lang_string('comment_by_format', 'Comment by %s:'), 'Frank Hochmuth');
$allOk = test_result(
	'Comment export is reply-like, localized and emoji-aware',
	strpos($commentStatus, 'commented on') === false && strpos($commentStatus, $expectedReplyPrefix) !== false && strpos($commentStatus, ':smile:') === false && strpos($commentStatus, '😄') !== false && strpos($commentStatus, 'localhost') === false,
	$commentStatus
) && $allOk;

$result = plugin_mastodon_run_sync(true);
$state = plugin_mastodon_state_read();
$syncDeleteRequests = 0;
if (simulate_recorded_http_requests() !== array()) {
	foreach (simulate_recorded_http_requests() as $request) {
		if (is_array($request) && !empty($request ['method']) && strtoupper((string) $request ['method']) === 'DELETE') {
			$syncDeleteRequests++;
		}
	}
}

$importedEntryId = isset($state ['entries_remote'] ['500']) ? $state ['entries_remote'] ['500'] : '';
$importedCommentRef = isset($state ['comments_remote'] ['501']) ? $state ['comments_remote'] ['501'] : array();
$importedNestedCommentRef = isset($state ['comments_remote'] ['502']) ? $state ['comments_remote'] ['502'] : array();
$importedEntry = $importedEntryId !== '' ? entry_parse($importedEntryId) : array();
$importedComment = !empty($importedCommentRef ['comment_id']) ? comment_parse($importedCommentRef ['entry_id'], $importedCommentRef ['comment_id']) : array();
$importedNestedComment = !empty($importedNestedCommentRef ['comment_id']) ? comment_parse($importedNestedCommentRef ['entry_id'], $importedNestedCommentRef ['comment_id']) : array();
$expectedImportedTs = plugin_mastodon_parse_iso_timestamp('2026-03-12T23:30:00Z') + ((int) plugin_mastodon_fp_config_value(array('locale', 'timeoffset'), 0) * 3600);

$allOk = test_result(
	'Remote entry imported through sync with Mastodon media visible in FlatPress',
	$result ['ok'] && $importedEntryId !== '' && isset($importedEntry ['subject']) && $importedEntry ['subject'] === 'Remote status body' && strpos($importedEntry ['content'], '@spacesjut@mastodon.social') !== false && strpos($importedEntry ['content'], '[img=images/mastodon/status-500/') !== false && is_file($simRoot . '/fp-content/images/mastodon/status-500/01-remote-status-500.jpg'),
	$importedEntryId
) && $allOk;

$allOk = test_result(
	'Deletion sync is queued for a follow-up request instead of running inside the content sync request',
	$result ['ok'] && !empty($state ['deletions_pending']) && $syncDeleteRequests === 0,
	json_encode(array('deletions_pending' => isset($state ['deletions_pending']) ? $state ['deletions_pending'] : null, 'deletions_not_before' => isset($state ['deletions_not_before']) ? $state ['deletions_not_before'] : '', 'delete_requests' => $syncDeleteRequests))
) && $allOk;

$allOk = test_result(
	'Content sync updates the synchronization timestamp and counters while leaving the deletion timestamp for the follow-up request',
	$result ['ok']
		&& !empty($state ['last_run'])
		&& empty($state ['last_deletion_run'])
		&& !empty($state ['deletions_pending'])
		&& isset($state ['deletion_stats'] ['deleted_local_entries']) && (int) $state ['deletion_stats'] ['deleted_local_entries'] === 0
		&& isset($state ['deletion_stats'] ['deleted_local_comments']) && (int) $state ['deletion_stats'] ['deleted_local_comments'] === 0
		&& isset($state ['deletion_stats'] ['deleted_remote_entries']) && (int) $state ['deletion_stats'] ['deleted_remote_entries'] === 0
		&& isset($state ['deletion_stats'] ['deleted_remote_comments']) && (int) $state ['deletion_stats'] ['deleted_remote_comments'] === 0,
	json_encode(array(
		'last_run' => isset($state ['last_run']) ? $state ['last_run'] : '',
		'last_deletion_run' => isset($state ['last_deletion_run']) ? $state ['last_deletion_run'] : '',
		'deletions_pending' => isset($state ['deletions_pending']) ? $state ['deletions_pending'] : null,
		'content_stats' => isset($state ['content_stats']) ? $state ['content_stats'] : array(),
		'deletion_stats' => isset($state ['deletion_stats']) ? $state ['deletion_stats'] : array()
	))
) && $allOk;

$allOk = test_result(
	'Private mention status is skipped during Mastodon import',
	!isset($state ['entries_remote'] ['550']) && $state ['last_remote_status_id'] === '550',
	(isset($state ['entries_remote'] ['550']) ? (string) $state ['entries_remote'] ['550'] : 'skipped') . ' | last=' . (isset($state ['last_remote_status_id']) ? (string) $state ['last_remote_status_id'] : '')
) && $allOk;

$allOk = test_result(
	'Remote imported entry keeps a stable FlatPress date',
	$importedEntryId === 'entry260313-013000' && isset($importedEntry ['date']) && (int) $importedEntry ['date'] === $expectedImportedTs,
	($importedEntryId !== '' ? $importedEntryId : 'missing') . ' | date=' . (isset($importedEntry ['date']) ? (string) $importedEntry ['date'] : 'missing')
) && $allOk;

$allOk = test_result(
	'Remote comment imported through sync',
	!empty($importedCommentRef ['comment_id']) && isset($importedComment ['content']) && trim((string) $importedComment ['content']) === 'Remote reply',
	!empty($importedCommentRef ['comment_id']) ? $importedCommentRef ['comment_id'] : ''
) && $allOk;

$allOk = test_result(
	'Nested remote comment keeps parent mapping metadata',
	!empty($importedNestedCommentRef ['comment_id']) && isset($importedNestedComment ['replyto']) && (string) $importedNestedComment ['replyto'] === (string) $importedCommentRef ['comment_id'] && isset($state ['comments'] [$importedNestedCommentRef ['entry_id'] . ':' . $importedNestedCommentRef ['comment_id']] ['in_reply_to_remote_id']) && $state ['comments'] [$importedNestedCommentRef ['entry_id'] . ':' . $importedNestedCommentRef ['comment_id']] ['in_reply_to_remote_id'] === '501',
	!empty($importedNestedCommentRef ['comment_id']) ? $importedNestedCommentRef ['comment_id'] : ''
) && $allOk;

$allOk = test_result(
	'Private mention reply is skipped during Mastodon import',
	!isset($state ['comments_remote'] ['503']),
	isset($state ['comments_remote'] ['503']) ? json_encode($state ['comments_remote'] ['503']) : 'skipped'
) && $allOk;

$localThreadEntryMeta = plugin_mastodon_state_get_entry_meta($state, $localThreadEntryId);
$localThreadParentMeta = plugin_mastodon_state_get_comment_meta($state, $localThreadEntryId, $localThreadParentCommentId);
$localThreadChildMeta = plugin_mastodon_state_get_comment_meta($state, $localThreadEntryId, $localThreadChildCommentId);
$requestBodies = array();
if (simulate_recorded_http_requests() !== array()) {
	foreach (simulate_recorded_http_requests() as $request) {
		if (!is_array($request) || empty($request ['url']) || strpos((string) $request ['url'], '/api/v1/statuses') === false || strtoupper((string) $request ['method']) !== 'POST') {
			continue;
		}
		$parsed = array();
		$rawBody = isset($request ['body']) ? (string) $request ['body'] : '';
		parse_str($rawBody, $parsed);
		$parsed ['__raw_body'] = $rawBody;
		$requestBodies [] = $parsed;
	}
}
$entryRequest = array();
$parentRequest = array();
$childRequest = array();
foreach ($requestBodies as $parsed) {
	$statusText = isset($parsed ['status']) ? (string) $parsed ['status'] : '';
	if ($entryRequest === array() && strpos($statusText, 'Mastodon sync export root') !== false) {
		$entryRequest = $parsed;
	}
	if ($parentRequest === array() && strpos($statusText, 'Parent comment body') !== false) {
		$parentRequest = $parsed;
	}
	if ($childRequest === array() && strpos($statusText, 'Child comment body') !== false) {
		$childRequest = $parsed;
	}
}

$allOk = test_result(
	'Entry export creates a top-level Mastodon status',
	!empty($localThreadEntryMeta ['remote_id']) && $entryRequest !== array() && empty($entryRequest ['in_reply_to_id']),
	isset($entryRequest ['status']) ? $entryRequest ['status'] : ''
) && $allOk;

$allOk = test_result(
	'Comment to entry exports with in_reply_to_id on the entry status',
	!empty($localThreadParentMeta ['remote_id']) && $parentRequest !== array() && isset($parentRequest ['in_reply_to_id']) && (string) $parentRequest ['in_reply_to_id'] === (string) $localThreadEntryMeta ['remote_id'],
	isset($parentRequest ['in_reply_to_id']) ? (string) $parentRequest ['in_reply_to_id'] : 'missing'
) && $allOk;

$allOk = test_result(
	'Comment to comment exports with in_reply_to_id on the parent reply',
	!empty($localThreadChildMeta ['remote_id']) && $childRequest !== array() && isset($childRequest ['in_reply_to_id']) && (string) $childRequest ['in_reply_to_id'] === (string) $localThreadParentMeta ['remote_id'],
	isset($childRequest ['in_reply_to_id']) ? (string) $childRequest ['in_reply_to_id'] : 'missing'
) && $allOk;

$allOk = test_result(
	'Entry and comment exports include the configured Mastodon language code',
	$entryRequest !== array()
		&& $parentRequest !== array()
		&& $childRequest !== array()
		&& simulate_request_uses_language($entryRequest, $configuredStatusLanguage)
		&& simulate_request_uses_language($parentRequest, $configuredStatusLanguage)
		&& simulate_request_uses_language($childRequest, $configuredStatusLanguage),
	json_encode(array(
		'expected' => $configuredStatusLanguage,
		'entry' => isset($entryRequest ['language']) ? (string) $entryRequest ['language'] : '',
		'parent' => isset($parentRequest ['language']) ? (string) $parentRequest ['language'] : '',
		'child' => isset($childRequest ['language']) ? (string) $childRequest ['language'] : ''
	))
) && $allOk;

$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$immediateDeletionResult = plugin_mastodon_run_deletion_sync(false);
$immediateDeletionRequests = simulate_recorded_http_requests();
$allOk = test_result(
	'Deletion sync waits at least five minutes after a completed content sync',
	!empty($immediateDeletionResult ['ok'])
		&& isset($immediateDeletionResult ['message']) && (string) $immediateDeletionResult ['message'] === 'deletion_sync_wait'
		&& $immediateDeletionRequests === array(),
	json_encode(array('result' => $immediateDeletionResult, 'requests' => $immediateDeletionRequests, 'state' => plugin_mastodon_state_read()))
) && $allOk;

$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_timeout_calls'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'POST ' . $instanceUrl . '/api/v1/statuses' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			'id' => 'language-create-1',
			'url' => $instanceUrl . '/@flatpress/language-create-1',
			'created_at' => '2026-03-22T09:00:00Z'
		))
	),
	'PUT ' . $instanceUrl . '/api/v1/statuses/language-update-1' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			'id' => 'language-update-1',
			'url' => $instanceUrl . '/@flatpress/language-update-1',
			'created_at' => '2026-03-22T09:05:00Z'
		))
	)
);
$languageOptions = simulate_seed_options_from_config(plugin_mastodon_get_options());
$languageOptions ['instance_url'] = $instanceUrl;
$languageOptions ['access_token'] = 'token123';
$languageCreateResponse = plugin_mastodon_create_status($languageOptions, 'Language create test', '', array());
$languageUpdateResponse = plugin_mastodon_update_status($languageOptions, 'language-update-1', 'Language update test', array());
$languageCreateRequest = array();
$languageUpdateRequest = array();
if (simulate_recorded_http_requests() !== array()) {
	foreach (simulate_recorded_http_requests() as $request) {
		if (!is_array($request) || empty($request ['url'])) {
			continue;
		}
		$parsed = array();
		parse_str(isset($request ['body']) ? (string) $request ['body'] : '', $parsed);
		if ($languageCreateRequest === array() && strtoupper((string) $request ['method']) === 'POST' && strpos((string) $request ['url'], '/api/v1/statuses') !== false) {
			$languageCreateRequest = $parsed;
		}
		if ($languageUpdateRequest === array() && strtoupper((string) $request ['method']) === 'PUT' && strpos((string) $request ['url'], '/api/v1/statuses/language-update-1') !== false) {
			$languageUpdateRequest = $parsed;
		}
	}
}
$allOk = test_result(
	'Create and update status requests include the configured Mastodon language code',
	!empty($languageCreateResponse ['ok'])
		&& !empty($languageUpdateResponse ['ok'])
		&& simulate_request_uses_language($languageCreateRequest, $configuredStatusLanguage)
		&& simulate_request_uses_language($languageUpdateRequest, $configuredStatusLanguage),
	json_encode(array(
		'expected' => $configuredStatusLanguage,
		'create' => isset($languageCreateRequest ['language']) ? (string) $languageCreateRequest ['language'] : '',
		'update' => isset($languageUpdateRequest ['language']) ? (string) $languageUpdateRequest ['language'] : ''
	))
) && $allOk;

$uploadDescriptionOptions = simulate_seed_options_from_config(plugin_mastodon_get_options());
$uploadDescriptionOptions ['instance_url'] = 'https://mastodon-upload-description.example';
$uploadDescriptionOptions ['access_token'] = 'token-upload-description';
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'POST ' . $uploadDescriptionOptions ['instance_url'] . '/api/v2/media' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			'id' => 'upload-description-media-1',
			'url' => $uploadDescriptionOptions ['instance_url'] . '/media/upload-description-media-1.jpg'
		))
	)
);
$uploadDescriptionResult = plugin_mastodon_upload_media_items(
	$uploadDescriptionOptions,
	array(
		array(
			'absolute_path' => ABS_PATH . FP_CONTENT . 'images/mastodon-sim/single-image.jpg',
			'description' => 'Initial upload description fixture'
		)
	),
	1
);
$uploadDescriptionRequest = simulate_first_http_request(
	simulate_recorded_http_requests(),
	'POST',
	'/api/v2/media'
);
$uploadDescriptionMultipart = (!empty($uploadDescriptionRequest ['multipart']) && is_array($uploadDescriptionRequest ['multipart'])) ? $uploadDescriptionRequest ['multipart'] : array();
$allOk = test_result(
	'Initial Mastodon media uploads include the attachment description in POST /api/v2/media',
	!empty($uploadDescriptionResult ['ok'])
		&& !empty($uploadDescriptionMultipart ['description'])
		&& (string) $uploadDescriptionMultipart ['description'] === 'Initial upload description fixture',
	json_encode(array(
		'upload' => $uploadDescriptionResult,
		'multipart_description' => isset($uploadDescriptionMultipart ['description']) ? (string) $uploadDescriptionMultipart ['description'] : ''
	))
) && $allOk;

$reuseMediaItems = plugin_mastodon_collect_local_entry_media(array(
	'content' => '[img=images/mastodon-sim/single-image.jpg width=180 title="Persistent alt text"]'
));
$reuseAttachmentSignature = plugin_mastodon_entry_media_attachment_signature_from_items($reuseMediaItems);
$reuseDescriptionSignature = plugin_mastodon_entry_media_description_signature_from_items($reuseMediaItems);
$reuseMeta = array(
	'remote_media' => array(
		array(
			'id' => 'stored-media-1',
			'description' => 'Persistent alt text'
		)
	),
	'local_media_attachment_signature' => $reuseAttachmentSignature,
	'local_media_description_signature' => $reuseDescriptionSignature
);
$reusePlan = plugin_mastodon_prepare_entry_media_sync_plan(
	simulate_seed_options_from_config(plugin_mastodon_get_options()),
	$reuseMeta,
	$reuseMediaItems,
	4
);
$allOk = test_result(
	'Unchanged entry media is reused without a new upload when only the post text changes',
	isset($reusePlan ['mode']) && (string) $reusePlan ['mode'] === 'reuse'
		&& !empty($reusePlan ['media_ids'])
		&& (string) $reusePlan ['media_ids'] [0] === 'stored-media-1'
		&& empty($reusePlan ['media_attributes']),
	json_encode($reusePlan)
) && $allOk;

$modernMediaOptions = simulate_seed_options_from_config(plugin_mastodon_get_options());
$modernMediaOptions ['instance_url'] = 'https://mastodon-modern-media.example';
$modernMediaOptions ['access_token'] = 'token-modern-media';
$modernMediaItems = plugin_mastodon_collect_local_entry_media(array(
	'content' => '[img=images/mastodon-sim/single-image.jpg width=180 title="Updated modern alt text"]'
));
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $modernMediaOptions ['instance_url'] . '/api/v2/instance' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			'version' => '4.1.0',
			'configuration' => array(
				'statuses' => array(
					'max_media_attachments' => 4
				),
				'media_attachments' => array(
					'description_limit' => 1500
				)
			)
		))
	)
);
$modernMediaMeta = array(
	'remote_media' => array(
		array(
			'id' => 'modern-media-1',
			'description' => 'Persistent alt text'
		)
	),
	'local_media_attachment_signature' => $reuseAttachmentSignature,
	'local_media_description_signature' => $reuseDescriptionSignature
);
$modernMediaPlan = plugin_mastodon_prepare_entry_media_sync_plan($modernMediaOptions, $modernMediaMeta, $modernMediaItems, 4);
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] ['PUT ' . $modernMediaOptions ['instance_url'] . '/api/v1/statuses/modern-status-1'] = array(
	'ok' => true,
	'code' => 200,
	'body' => json_encode(array(
		'id' => 'modern-status-1',
		'url' => $modernMediaOptions ['instance_url'] . '/@flatpress/modern-status-1',
		'edited_at' => '2026-03-22T09:10:00Z',
		'media_attachments' => array(
			array(
				'id' => 'modern-media-1',
				'type' => 'image',
				'description' => 'Updated modern alt text'
			)
		)
	))
);
$modernMediaUpdate = plugin_mastodon_update_status(
	$modernMediaOptions,
	'modern-status-1',
	'Modern media description update',
	isset($modernMediaPlan ['media_ids']) ? $modernMediaPlan ['media_ids'] : array(),
	isset($modernMediaPlan ['media_attributes']) ? $modernMediaPlan ['media_attributes'] : array()
);
$modernMediaUpdateRequest = simulate_first_http_request(
	simulate_recorded_http_requests(),
	'PUT',
	'/api/v1/statuses/modern-status-1'
);
$modernMediaUpdateBody = simulate_parse_http_request_body($modernMediaUpdateRequest);
$allOk = test_result(
	'Mastodon 4.1+ updates changed image descriptions through status media_attributes without re-uploading media',
	!empty($modernMediaUpdate ['ok'])
		&& isset($modernMediaPlan ['mode']) && (string) $modernMediaPlan ['mode'] === 'reuse'
		&& !empty($modernMediaPlan ['media_attributes'])
		&& isset($modernMediaUpdateBody ['media_attributes'] [0] ['description'])
		&& (string) $modernMediaUpdateBody ['media_attributes'] [0] ['description'] === 'Updated modern alt text'
		&& strpos(isset($modernMediaUpdateBody ['__raw_body']) ? (string) $modernMediaUpdateBody ['__raw_body'] : '', 'media_attributes%5B%5D%5Bid%5D=modern-media-1') !== false
		&& strpos(isset($modernMediaUpdateBody ['__raw_body']) ? (string) $modernMediaUpdateBody ['__raw_body'] : '', 'media_attributes%5B0%5D%5Bid%5D=') === false
		&& simulate_first_http_request(simulate_recorded_http_requests(), 'POST', '/api/v2/media') === array(),
	json_encode(array(
		'plan' => $modernMediaPlan,
		'parsed_request' => $modernMediaUpdateBody
	))
) && $allOk;

$legacyMediaOptions = simulate_seed_options_from_config(plugin_mastodon_get_options());
$legacyMediaOptions ['instance_url'] = 'https://mastodon-legacy-media.example';
$legacyMediaOptions ['access_token'] = 'token-legacy-media';
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $legacyMediaOptions ['instance_url'] . '/api/v2/instance' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			'version' => '4.0.0',
			'configuration' => array(
				'statuses' => array(
					'max_media_attachments' => 4
				),
				'media_attachments' => array(
					'description_limit' => 1500
				)
			)
		))
	)
);
$legacyMediaPlan = plugin_mastodon_prepare_entry_media_sync_plan($legacyMediaOptions, $modernMediaMeta, $modernMediaItems, 4);
$allOk = test_result(
	'Older Mastodon versions fall back to a fresh upload when only the media description changes',
	isset($legacyMediaPlan ['mode']) && (string) $legacyMediaPlan ['mode'] === 'upload',
	json_encode($legacyMediaPlan)
) && $allOk;

$reuseSyncEntry = array(
	'version' => system_ver(),
	'subject' => 'Stored media reuse entry',
	'content' => "Updated body with unchanged media\n\n[img=images/mastodon-sim/single-image.jpg width=180 title=\"Persistent alt text\"]",
	'author' => 'Simulation',
	'date' => strtotime('2031-04-24 08:00:00 UTC')
);
$reuseSyncEntryId = entry_save($reuseSyncEntry, null);
$reuseSyncState = plugin_mastodon_default_state();
plugin_mastodon_state_set_entry_mapping(
	$reuseSyncState,
	$reuseSyncEntryId,
	'reuse-sync-status-1',
	'local',
	'outdated-hash',
	'https://mastodon-reuse-sync.example/@flatpress/reuse-sync-status-1',
	'2031-04-24 08:05:00',
	plugin_mastodon_local_item_date_key($reuseSyncEntry, $reuseSyncEntryId),
	'2031-04-24'
);
$reuseSyncMediaItems = plugin_mastodon_collect_local_entry_media($reuseSyncEntry);
plugin_mastodon_state_set_entry_media_meta(
	$reuseSyncState,
	$reuseSyncEntryId,
	array(
		array(
			'id' => 'reuse-sync-media-1',
			'description' => 'Persistent alt text'
		)
	),
	plugin_mastodon_entry_media_attachment_signature_from_items($reuseSyncMediaItems),
	plugin_mastodon_entry_media_description_signature_from_items($reuseSyncMediaItems)
);
$reuseSyncOptions = simulate_seed_options_from_config(plugin_mastodon_get_options());
$reuseSyncOptions ['instance_url'] = 'https://mastodon-reuse-sync.example';
$reuseSyncOptions ['access_token'] = 'token-reuse-sync';
$reuseSyncOptions ['sync_start_date'] = '2031-04-24';
plugin_mastodon_runtime_cache_clear();
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $reuseSyncOptions ['instance_url'] . '/api/v2/instance' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			'version' => '4.0.0',
			'configuration' => array(
				'statuses' => array(
					'max_characters' => 500,
					'max_media_attachments' => 4,
					'characters_reserved_per_url' => 23
				),
				'media_attachments' => array(
					'description_limit' => 1500
				)
			)
		))
	),
	'PUT ' . $reuseSyncOptions ['instance_url'] . '/api/v1/statuses/reuse-sync-status-1' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			'id' => 'reuse-sync-status-1',
			'url' => $reuseSyncOptions ['instance_url'] . '/@flatpress/reuse-sync-status-1',
			'edited_at' => '2031-04-24T08:10:00Z',
			'media_attachments' => array(
				array(
					'id' => 'reuse-sync-media-1',
					'type' => 'image',
					'description' => 'Persistent alt text'
				)
			)
		))
	)
);
$reuseSyncResult = plugin_mastodon_sync_local_to_remote($reuseSyncOptions, $reuseSyncState);
$reuseSyncPutRequest = simulate_first_http_request(
	simulate_recorded_http_requests(),
	'PUT',
	'/api/v1/statuses/reuse-sync-status-1'
);
$reuseSyncPutBody = simulate_parse_http_request_body($reuseSyncPutRequest);
$allOk = test_result(
	'Full local-to-remote sync reuses stored media IDs when the attachments did not change',
	$reuseSyncResult
		&& simulate_first_http_request(simulate_recorded_http_requests(), 'POST', '/api/v2/media') === array()
		&& isset($reuseSyncPutBody ['media_ids'] [0])
		&& (string) $reuseSyncPutBody ['media_ids'] [0] === 'reuse-sync-media-1',
	json_encode(array(
		'result' => $reuseSyncResult,
		'put_request' => $reuseSyncPutBody
	))
) && $allOk;

$reuseSyncEntryFile = entry_exists($reuseSyncEntryId);
if (is_string($reuseSyncEntryFile) && $reuseSyncEntryFile !== '') {
	simulate_delete_recursive(substr($reuseSyncEntryFile, 0, -strlen(EXT)));
}

$modernSyncEntry = array(
	'version' => system_ver(),
	'subject' => 'Modern media description edit entry',
	'content' => "Same image, updated description\n\n[img=images/mastodon-sim/single-image.jpg width=180 title=\"Updated modern alt text\"]",
	'author' => 'Simulation',
	'date' => strtotime('2031-04-25 08:30:00 UTC')
);
$modernSyncEntryId = entry_save($modernSyncEntry, null);
$modernSyncState = plugin_mastodon_default_state();
plugin_mastodon_state_set_entry_mapping(
	$modernSyncState,
	$modernSyncEntryId,
	'modern-sync-status-1',
	'local',
	'outdated-hash',
	'https://mastodon-modern-sync.example/@flatpress/modern-sync-status-1',
	'2031-04-25 08:35:00',
	plugin_mastodon_local_item_date_key($modernSyncEntry, $modernSyncEntryId),
	'2031-04-25'
);
$modernSyncPreviousMediaItems = plugin_mastodon_collect_local_entry_media(array(
	'content' => '[img=images/mastodon-sim/single-image.jpg width=180 title="Persistent alt text"]'
));
plugin_mastodon_state_set_entry_media_meta(
	$modernSyncState,
	$modernSyncEntryId,
	array(
		array(
			'id' => 'modern-sync-media-1',
			'description' => 'Persistent alt text'
		)
	),
	plugin_mastodon_entry_media_attachment_signature_from_items($modernSyncPreviousMediaItems),
	plugin_mastodon_entry_media_description_signature_from_items($modernSyncPreviousMediaItems)
);
$modernSyncOptions = simulate_seed_options_from_config(plugin_mastodon_get_options());
$modernSyncOptions ['instance_url'] = 'https://mastodon-modern-sync.example';
$modernSyncOptions ['access_token'] = 'token-modern-sync';
$modernSyncOptions ['sync_start_date'] = '2031-04-25';
plugin_mastodon_runtime_cache_clear();
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $modernSyncOptions ['instance_url'] . '/api/v2/instance' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			'version' => '4.1.0',
			'configuration' => array(
				'statuses' => array(
					'max_characters' => 500,
					'max_media_attachments' => 4,
					'characters_reserved_per_url' => 23
				),
				'media_attachments' => array(
					'description_limit' => 1500
				)
			)
		))
	),
	'PUT ' . $modernSyncOptions ['instance_url'] . '/api/v1/statuses/modern-sync-status-1' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			'id' => 'modern-sync-status-1',
			'url' => $modernSyncOptions ['instance_url'] . '/@flatpress/modern-sync-status-1',
			'edited_at' => '2031-04-25T08:40:00Z',
			'media_attachments' => array(
				array(
					'id' => 'modern-sync-media-1',
					'type' => 'image',
					'description' => 'Updated modern alt text'
				)
			)
		))
	)
);
$modernSyncResult = plugin_mastodon_sync_local_to_remote($modernSyncOptions, $modernSyncState);
$modernSyncPutRequest = simulate_first_http_request(
	simulate_recorded_http_requests(),
	'PUT',
	'/api/v1/statuses/modern-sync-status-1'
);
$modernSyncPutBody = simulate_parse_http_request_body($modernSyncPutRequest);
$allOk = test_result(
	'Full sync updates changed media descriptions through status media_attributes on Mastodon 4.1+',
	$modernSyncResult
		&& simulate_first_http_request(simulate_recorded_http_requests(), 'POST', '/api/v2/media') === array()
		&& isset($modernSyncPutBody ['media_ids'] [0])
		&& (string) $modernSyncPutBody ['media_ids'] [0] === 'modern-sync-media-1'
		&& isset($modernSyncPutBody ['media_attributes'] [0] ['description'])
		&& (string) $modernSyncPutBody ['media_attributes'] [0] ['description'] === 'Updated modern alt text'
		&& strpos(isset($modernSyncPutBody ['__raw_body']) ? (string) $modernSyncPutBody ['__raw_body'] : '', 'media_ids%5B%5D=modern-sync-media-1') !== false
		&& strpos(isset($modernSyncPutBody ['__raw_body']) ? (string) $modernSyncPutBody ['__raw_body'] : '', 'media_ids%5B0%5D=') === false
		&& strpos(isset($modernSyncPutBody ['__raw_body']) ? (string) $modernSyncPutBody ['__raw_body'] : '', 'media_attributes%5B%5D%5Bid%5D=modern-sync-media-1') !== false
		&& strpos(isset($modernSyncPutBody ['__raw_body']) ? (string) $modernSyncPutBody ['__raw_body'] : '', 'media_attributes%5B0%5D%5Bid%5D=') === false,
	json_encode(array(
		'result' => $modernSyncResult,
		'put_request' => $modernSyncPutBody
	))
) && $allOk;

$reuseSyncEntryFile = entry_exists($reuseSyncEntryId);
if (is_string($reuseSyncEntryFile) && $reuseSyncEntryFile !== '') {
	simulate_delete_recursive(substr($reuseSyncEntryFile, 0, -strlen(EXT)));
}
$modernSyncEntryFile = entry_exists($modernSyncEntryId);
if (is_string($modernSyncEntryFile) && $modernSyncEntryFile !== '') {
	simulate_delete_recursive(substr($modernSyncEntryFile, 0, -strlen(EXT)));
}

$options = $seededOptions;
$options ['sync_start_date'] = '';
$options ['update_local_from_remote'] = '0';
$options ['import_synced_comments_as_entries'] = '0';
$options ['access_token'] = 'token123';
plugin_mastodon_save_options($options);
plugin_mastodon_runtime_cache_clear();

// Regression test: when multiple local entries are exported in one batch, older entries must be posted first.
simulate_delete_recursive($simRoot . '/fp-content/plugin_mastodon');
mkdir($simRoot . '/fp-content/plugin_mastodon', 0777, true);
plugin_mastodon_state_write(plugin_mastodon_default_state());

$chronologyOlderEntry = array(
	'version' => system_ver(),
	'subject' => 'Chronology older FlatPress entry',
	'content' => 'This older entry must be posted before the newer one during the same synchronization run.',
	'author' => 'Simulation',
	'date' => strtotime('2026-03-20 08:00:00 UTC')
);
$chronologyOlderEntryId = entry_save($chronologyOlderEntry, null);
$chronologyNewerEntry = array(
	'version' => system_ver(),
	'subject' => 'Chronology newer FlatPress entry',
	'content' => 'This newer entry must remain above the older one on Mastodon after the batch export.',
	'author' => 'Simulation',
	'date' => strtotime('2026-03-21 08:00:00 UTC')
);
$chronologyNewerEntryId = entry_save($chronologyNewerEntry, null);

$options = $seededOptions;
$options ['sync_start_date'] = '2026-03-20';
$options ['access_token'] = 'token123';
plugin_mastodon_save_options($options);

$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $instanceUrl . '/api/v2/instance' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			'configuration' => array(
				'statuses' => array(
					'max_characters' => 500,
					'max_media_attachments' => 4
				)
			)
		))
	),
	'POST ' . $instanceUrl . '/api/v1/statuses' => array(
		array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array(
				'id' => 'chronology-800',
				'url' => $instanceUrl . '/@flatpress/chronology-800',
				'created_at' => '2026-03-20T08:00:00Z'
			))
		),
		array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array(
				'id' => 'chronology-801',
				'url' => $instanceUrl . '/@flatpress/chronology-801',
				'created_at' => '2026-03-21T08:00:00Z'
			))
		)
	)
);

$chronologyState = plugin_mastodon_state_read();
$chronologyExportOk = plugin_mastodon_sync_local_to_remote($options, $chronologyState);
$chronologyOrder = array();
$chronologyListIds = array_keys(plugin_mastodon_list_local_entries());
$chronologyOlderIndex = array_search($chronologyOlderEntryId, $chronologyListIds, true);
$chronologyNewerIndex = array_search($chronologyNewerEntryId, $chronologyListIds, true);
if (simulate_recorded_http_requests() !== array()) {
	foreach (simulate_recorded_http_requests() as $request) {
		if (!is_array($request) || empty($request ['url']) || strpos((string) $request ['url'], '/api/v1/statuses') === false || strtoupper((string) $request ['method']) !== 'POST') {
			continue;
		}
		$parsed = array();
		parse_str(isset($request ['body']) ? (string) $request ['body'] : '', $parsed);
		if (!empty($parsed ['in_reply_to_id'])) {
			continue;
		}
		$statusText = isset($parsed ['status']) ? (string) $parsed ['status'] : '';
		if (strpos($statusText, 'Chronology older FlatPress entry') !== false) {
			$chronologyOrder [] = 'older';
		}
		if (strpos($statusText, 'Chronology newer FlatPress entry') !== false) {
			$chronologyOrder [] = 'newer';
		}
	}
}
$allOk = test_result(
	'Batch entry export keeps older FlatPress entries below newer ones on Mastodon',
	$chronologyOlderIndex !== false
		&& $chronologyNewerIndex !== false
		&& $chronologyOlderIndex < $chronologyNewerIndex
		&& $chronologyOrder === array('older', 'newer'),
	json_encode(array(
		'export_ok' => $chronologyExportOk,
		'list_positions' => array(
			'older' => $chronologyOlderIndex,
			'newer' => $chronologyNewerIndex
		),
		'request_order' => $chronologyOrder
	))
) && $allOk;

entry_delete($chronologyOlderEntryId);
entry_delete($chronologyNewerEntryId);

// Regression test: a new local comment on an already synchronized older FlatPress entry must still be exported.
simulate_delete_recursive($simRoot . '/fp-content/plugin_mastodon');
mkdir($simRoot . '/fp-content/plugin_mastodon', 0777, true);
plugin_mastodon_state_write(plugin_mastodon_default_state());

$oldSyncedEntry = array(
	'version' => system_ver(),
	'subject' => 'Older synchronized entry',
	'content' => 'This entry was already synchronized on the previous day.',
	'author' => 'Simulation',
	'date' => strtotime('2026-03-14 08:00:00 UTC')
);
$oldSyncedEntryId = entry_save($oldSyncedEntry, null);
$oldSyncedEntry = entry_parse($oldSyncedEntryId);
$newCommentOnOldEntry = array(
	'version' => system_ver(),
	'name' => 'Follow-up Author',
	'content' => 'Fresh follow-up comment on an older synchronized entry',
	'date' => strtotime('2026-03-15 09:30:00 UTC')
);
$newCommentOnOldEntryId = comment_save($oldSyncedEntryId, $newCommentOnOldEntry);

$options = $seededOptions;
$options ['sync_start_date'] = '2026-03-15';
$options ['access_token'] = 'token123';
plugin_mastodon_save_options($options);

$oldCommentState = plugin_mastodon_default_state();
plugin_mastodon_state_set_entry_mapping(
	$oldCommentState,
	$oldSyncedEntryId,
	'old-entry-remote-700',
	'local',
	plugin_mastodon_entry_hash($oldSyncedEntry),
	$instanceUrl . '/@flatpress/old-entry-remote-700',
	'2026-03-14 08:00:00'
);
plugin_mastodon_state_write($oldCommentState);

$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $instanceUrl . '/api/v2/instance' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			'configuration' => array(
				'statuses' => array(
					'max_characters' => 500,
					'max_media_attachments' => 4
				)
			)
		))
	),
	'POST ' . $instanceUrl . '/api/v1/statuses' => array(
		array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array(
				'id' => 'old-entry-comment-701',
				'url' => $instanceUrl . '/@flatpress/old-entry-comment-701',
				'created_at' => '2026-03-15T09:30:00Z'
			))
		)
	)
);

$oldCommentState = plugin_mastodon_state_read();
$oldCommentExportOk = plugin_mastodon_sync_local_to_remote($options, $oldCommentState);
$oldCommentRequest = array();
if (simulate_recorded_http_requests() !== array()) {
	foreach (simulate_recorded_http_requests() as $request) {
		if (!is_array($request) || empty($request ['url']) || strpos((string) $request ['url'], '/api/v1/statuses') === false || strtoupper((string) $request ['method']) !== 'POST') {
			continue;
		}
		$parsed = array();
		parse_str(isset($request ['body']) ? (string) $request ['body'] : '', $parsed);
		if (isset($parsed ['status']) && strpos((string) $parsed ['status'], 'Fresh follow-up comment on an older synchronized entry') !== false) {
			$oldCommentRequest = $parsed;
			break;
		}
	}
}
$oldCommentMeta = plugin_mastodon_state_get_comment_meta($oldCommentState, $oldSyncedEntryId, $newCommentOnOldEntryId);
$allOk = test_result(
	'New local comment on an already synchronized older entry is exported to Mastodon',
	!empty($oldCommentMeta ['remote_id'])
		&& $oldCommentRequest !== array()
		&& isset($oldCommentRequest ['in_reply_to_id'])
		&& (string) $oldCommentRequest ['in_reply_to_id'] === 'old-entry-remote-700',
	json_encode(array(
		'export_ok' => $oldCommentExportOk,
		'comment_meta' => $oldCommentMeta,
		'request' => $oldCommentRequest
	))
) && $allOk;

// Regression test: an already synchronized older Mastodon thread must not keep causing daily context requests after a newer sync start date is configured.
simulate_delete_recursive($simRoot . '/fp-content/plugin_mastodon');
mkdir($simRoot . '/fp-content/plugin_mastodon', 0777, true);
plugin_mastodon_state_write(plugin_mastodon_default_state());

$remoteThreadEntry = array(
	'version' => system_ver(),
	'subject' => 'Older synchronized remote thread',
	'content' => 'This local entry represents an older synchronized Mastodon thread.',
	'author' => 'Simulation',
	'date' => strtotime('2026-03-14 07:45:00 UTC')
);
$remoteThreadEntryId = entry_save($remoteThreadEntry, null);
$remoteThreadEntry = entry_parse($remoteThreadEntryId);

$options = $seededOptions;
$options ['sync_start_date'] = '2026-03-15';
$options ['access_token'] = 'token123';
plugin_mastodon_save_options($options);

$remoteThreadState = plugin_mastodon_default_state();
plugin_mastodon_state_set_entry_mapping(
	$remoteThreadState,
	$remoteThreadEntryId,
	'old-remote-thread-800',
	'local',
	plugin_mastodon_entry_hash($remoteThreadEntry),
	$instanceUrl . '/@flatpress/old-remote-thread-800',
	'2026-03-14 07:45:00'
);
$remoteThreadState ['last_remote_status_id'] = '999';
plugin_mastodon_state_write($remoteThreadState);

$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $instanceUrl . '/api/v1/accounts/verify_credentials' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('id' => 'acct1', 'username' => 'flatpress'))
	),
	'GET ' . $instanceUrl . '/api/v1/accounts/acct1/statuses?limit=40&exclude_reblogs=true&exclude_replies=true&since_id=999' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array())
	),
	'GET ' . $instanceUrl . '/api/v1/statuses/old-remote-thread-800/context' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			'descendants' => array(
				array(
					'id' => '1001',
					'visibility' => 'public',
					'in_reply_to_id' => 'old-remote-thread-800',
					'created_at' => '2026-03-15T10:15:00Z',
					'content' => '<p>New remote reply on an older synchronized thread</p>',
					'url' => $instanceUrl . '/@alice/1001',
					'account' => array(
						'id' => 'acct2',
						'acct' => 'alice@example.net',
						'display_name' => 'Alice',
						'url' => 'https://example.net/@alice'
					)
				)
			)
		))
	)
);

$remoteThreadState = plugin_mastodon_state_read();
$remoteThreadImportOk = plugin_mastodon_sync_remote_to_local($options, $remoteThreadState);
$remoteThreadRequests = simulate_recorded_http_requests();
$remoteThreadContextRequests = 0;
foreach ($remoteThreadRequests as $request) {
	if (is_array($request) && !empty($request ['url']) && strpos((string) $request ['url'], '/api/v1/statuses/old-remote-thread-800/context') !== false) {
		$remoteThreadContextRequests++;
	}
}
$remoteFollowUpRef = isset($remoteThreadState ['comments_remote'] ['1001']) ? $remoteThreadState ['comments_remote'] ['1001'] : array();
$remoteFollowUpComment = (!empty($remoteFollowUpRef ['entry_id']) && !empty($remoteFollowUpRef ['comment_id']))
	? comment_parse($remoteFollowUpRef ['entry_id'], $remoteFollowUpRef ['comment_id'])
	: array();
$allOk = test_result(
	'Known synchronized entry mappings older than the sync start date do not trigger context refreshes',
	$remoteThreadImportOk
		&& $remoteThreadContextRequests === 0
		&& empty($remoteFollowUpRef),
	json_encode(array(
		'ref' => $remoteFollowUpRef,
		'comment' => $remoteFollowUpComment,
		'context_requests' => $remoteThreadContextRequests,
		'requests' => $remoteThreadRequests
	))
) && $allOk;

// Scheduled and normal manual runs use the automatic recent-content window while explicit full/direct runs can still use the durable start date.
simulate_delete_recursive($simRoot . '/fp-content/plugin_mastodon');
mkdir($simRoot . '/fp-content/plugin_mastodon', 0777, true);
plugin_mastodon_state_write(plugin_mastodon_default_state());

$GLOBALS ['plugin_mastodon_test_now'] = strtotime('2035-01-15 12:00:00 UTC');
$scheduledOldEntry = array(
	'version' => system_ver(),
	'subject' => 'Scheduled window old FlatPress entry',
	'content' => 'This entry is above the durable start date but outside the automatic scheduled window.',
	'author' => 'Simulation',
	'date' => strtotime('2034-12-20 08:00:00 UTC')
);
$scheduledOldEntryId = entry_save($scheduledOldEntry, null);
$scheduledRecentEntry = array(
	'version' => system_ver(),
	'subject' => 'Scheduled window recent FlatPress entry',
	'content' => 'This entry is inside the automatic scheduled window.',
	'author' => 'Simulation',
	'date' => strtotime('2035-01-14 08:00:00 UTC')
);
$scheduledRecentEntryId = entry_save($scheduledRecentEntry, null);

$scheduledWindowOptions = $seededOptions;
$scheduledWindowOptions ['sync_start_date'] = '2034-01-01';
$scheduledWindowOptions ['sync_scheduled_window_days'] = '7';
$scheduledWindowOptions ['access_token'] = 'token123';
plugin_mastodon_save_options($scheduledWindowOptions);

$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $instanceUrl . '/api/v2/instance' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('configuration' => array('statuses' => array('max_characters' => 500, 'max_media_attachments' => 4))))
	),
	'POST ' . $instanceUrl . '/api/v1/statuses' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			'id' => 'scheduled-window-recent-1',
			'url' => $instanceUrl . '/@flatpress/scheduled-window-recent-1',
			'created_at' => '2035-01-14T08:00:00Z'
		))
	)
);

$scheduledWindowState = plugin_mastodon_state_read();
$scheduledWindowOk = plugin_mastodon_sync_local_to_remote($scheduledWindowOptions, $scheduledWindowState, false);
$scheduledWindowPostedOld = false;
$scheduledWindowPostedRecent = false;
foreach (simulate_recorded_http_requests() as $request) {
	if (!is_array($request) || strtoupper((string) (isset($request ['method']) ? $request ['method'] : '')) !== 'POST') {
		continue;
	}
	$parsed = array();
	parse_str(isset($request ['body']) ? (string) $request ['body'] : '', $parsed);
	$status = isset($parsed ['status']) ? (string) $parsed ['status'] : '';
	$scheduledWindowPostedOld = $scheduledWindowPostedOld || strpos($status, 'Scheduled window old FlatPress entry') !== false;
	$scheduledWindowPostedRecent = $scheduledWindowPostedRecent || strpos($status, 'Scheduled window recent FlatPress entry') !== false;
}
$allOk = test_result(
	'Scheduled content synchronization respects the automatic recent-content window',
	$scheduledWindowOk && !$scheduledWindowPostedOld && $scheduledWindowPostedRecent,
	json_encode(array(
		'posted_old' => $scheduledWindowPostedOld,
		'posted_recent' => $scheduledWindowPostedRecent,
		'state_old' => plugin_mastodon_state_get_entry_meta($scheduledWindowState, $scheduledOldEntryId),
		'state_recent' => plugin_mastodon_state_get_entry_meta($scheduledWindowState, $scheduledRecentEntryId)
	))
) && $allOk;

simulate_delete_recursive($simRoot . '/fp-content/plugin_mastodon');
mkdir($simRoot . '/fp-content/plugin_mastodon', 0777, true);
$contentWorksetState = plugin_mastodon_default_state();
plugin_mastodon_state_set_entry_mapping($contentWorksetState, $scheduledOldEntryId, 'content-workset-old', 'local', plugin_mastodon_entry_hash(entry_parse($scheduledOldEntryId)), $instanceUrl . '/@flatpress/content-workset-old', '2034-12-20 08:00:00', plugin_mastodon_local_item_date_key(entry_parse($scheduledOldEntryId), $scheduledOldEntryId), '2034-12-20');
plugin_mastodon_state_set_entry_mapping($contentWorksetState, $scheduledRecentEntryId, 'content-workset-recent', 'local', plugin_mastodon_entry_hash(entry_parse($scheduledRecentEntryId)), $instanceUrl . '/@flatpress/content-workset-recent', '2035-01-14 08:00:00', plugin_mastodon_local_item_date_key(entry_parse($scheduledRecentEntryId), $scheduledRecentEntryId), '2035-01-14');
plugin_mastodon_state_set_comment_mapping($contentWorksetState, $scheduledOldEntryId, 'comment341220-080100', 'content-workset-old-comment', 'local', 'old-comment-hash', $instanceUrl . '/@flatpress/content-workset-old-comment', '2034-12-20 08:01:00', '', 'content-workset-old', '2034-12-20', '2034-12-20');
plugin_mastodon_state_set_comment_mapping($contentWorksetState, $scheduledRecentEntryId, 'comment350114-080100', 'content-workset-recent-comment', 'local', 'recent-comment-hash', $instanceUrl . '/@flatpress/content-workset-recent-comment', '2035-01-14 08:01:00', '', 'content-workset-recent', '2035-01-14', '2035-01-14');
plugin_mastodon_state_write($contentWorksetState);
plugin_mastodon_runtime_cache_clear('state');
$contentWorksetLoaded = plugin_mastodon_state_read(array());
$GLOBALS ['plugin_mastodon_test_comment_shard_read_count'] = 0;
$GLOBALS ['plugin_mastodon_test_comment_shard_read_ids'] = array();
$contentWorksetIds = plugin_mastodon_state_load_content_sync_comment_workset($scheduledWindowOptions, $contentWorksetLoaded, false);
$contentWorksetReadIds = simulate_mastodon_comment_shard_read_ids();
unset($GLOBALS ['plugin_mastodon_test_comment_shard_read_count'], $GLOBALS ['plugin_mastodon_test_comment_shard_read_ids']);
$allOk = test_result(
	'Productive content-sync workset loads only active scheduled comment shards',
	$contentWorksetIds === array($scheduledRecentEntryId)
		&& $contentWorksetReadIds === array($scheduledRecentEntryId)
		&& isset($contentWorksetLoaded ['comments'] [$scheduledRecentEntryId . ':comment350114-080100'])
		&& !isset($contentWorksetLoaded ['comments'] [$scheduledOldEntryId . ':comment341220-080100']),
	json_encode(array(
		'workset_ids' => $contentWorksetIds,
		'shard_read_ids' => $contentWorksetReadIds,
		'loaded_comment_keys' => isset($contentWorksetLoaded ['comments']) && is_array($contentWorksetLoaded ['comments']) ? array_keys($contentWorksetLoaded ['comments']) : array()
	))
) && $allOk;

simulate_delete_recursive($simRoot . '/fp-content/plugin_mastodon');
mkdir($simRoot . '/fp-content/plugin_mastodon', 0777, true);
plugin_mastodon_state_write(plugin_mastodon_default_state());
$normalManualWindowState = plugin_mastodon_default_state();
$normalManualWindowState ['last_run'] = '2035-01-15 03:00:00';
$normalManualWindowState ['last_remote_status_id'] = '999';
plugin_mastodon_state_write($normalManualWindowState);
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $instanceUrl . '/api/v1/accounts/verify_credentials' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('id' => 'acct1', 'username' => 'flatpress'))
	),
	'GET ' . $instanceUrl . '/api/v1/accounts/acct1/statuses?limit=40&exclude_reblogs=true&exclude_replies=true&since_id=999' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array())
	),
	'GET ' . $instanceUrl . '/api/v2/instance' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('configuration' => array('statuses' => array('max_characters' => 500, 'max_media_attachments' => 4))))
	),
	'POST ' . $instanceUrl . '/api/v1/statuses' => array(
		array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('id' => 'normal-manual-recent-1', 'url' => $instanceUrl . '/@flatpress/normal-manual-recent-1', 'created_at' => '2035-01-14T08:00:00Z'))
		)
	)
);
$normalManualResult = plugin_mastodon_run_sync(true, false);
$normalManualRequests = simulate_recorded_http_requests();
$normalManualPostedOld = false;
$normalManualPostedRecent = false;
foreach ($normalManualRequests as $request) {
	if (!is_array($request) || strtoupper((string) (isset($request ['method']) ? $request ['method'] : '')) !== 'POST') {
		continue;
	}
	$parsed = array();
	parse_str(isset($request ['body']) ? (string) $request ['body'] : '', $parsed);
	$status = isset($parsed ['status']) ? (string) $parsed ['status'] : '';
	$normalManualPostedOld = $normalManualPostedOld || strpos($status, 'Scheduled window old FlatPress entry') !== false;
	$normalManualPostedRecent = $normalManualPostedRecent || strpos($status, 'Scheduled window recent FlatPress entry') !== false;
}
$allOk = test_result(
	'Normal manual synchronization bypasses the daily due check but still respects the automatic window',
	!empty($normalManualResult ['ok']) && !$normalManualPostedOld && $normalManualPostedRecent,
	json_encode(array(
		'result' => $normalManualResult,
		'posted_old' => $normalManualPostedOld,
		'posted_recent' => $normalManualPostedRecent,
		'requests' => $normalManualRequests
	))
) && $allOk;

simulate_delete_recursive($simRoot . '/fp-content/plugin_mastodon');
mkdir($simRoot . '/fp-content/plugin_mastodon', 0777, true);
plugin_mastodon_state_write(plugin_mastodon_default_state());
$fullManualWindowState = plugin_mastodon_default_state();
$fullManualWindowState ['last_run'] = '2035-01-15 03:00:00';
$fullManualWindowState ['last_remote_status_id'] = '999';
plugin_mastodon_state_write($fullManualWindowState);
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $instanceUrl . '/api/v1/accounts/verify_credentials' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('id' => 'acct1', 'username' => 'flatpress'))
	),
	'GET ' . $instanceUrl . '/api/v1/accounts/acct1/statuses?limit=40&exclude_reblogs=true&exclude_replies=true&since_id=999' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array())
	),
	'GET ' . $instanceUrl . '/api/v2/instance' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('configuration' => array('statuses' => array('max_characters' => 500, 'max_media_attachments' => 4))))
	),
	'POST ' . $instanceUrl . '/api/v1/statuses' => array(
		array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('id' => 'full-manual-old-1', 'url' => $instanceUrl . '/@flatpress/full-manual-old-1', 'created_at' => '2034-12-20T08:00:00Z'))
		),
		array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('id' => 'full-manual-recent-1', 'url' => $instanceUrl . '/@flatpress/full-manual-recent-1', 'created_at' => '2035-01-14T08:00:00Z'))
		)
	)
);
$fullManualResult = plugin_mastodon_run_sync(true, true);
$fullManualRequests = simulate_recorded_http_requests();
$fullManualPostedOld = false;
$fullManualPostedRecent = false;
foreach ($fullManualRequests as $request) {
	if (!is_array($request) || strtoupper((string) (isset($request ['method']) ? $request ['method'] : '')) !== 'POST') {
		continue;
	}
	$parsed = array();
	parse_str(isset($request ['body']) ? (string) $request ['body'] : '', $parsed);
	$status = isset($parsed ['status']) ? (string) $parsed ['status'] : '';
	$fullManualPostedOld = $fullManualPostedOld || strpos($status, 'Scheduled window old FlatPress entry') !== false;
	$fullManualPostedRecent = $fullManualPostedRecent || strpos($status, 'Scheduled window recent FlatPress entry') !== false;
}
$allOk = test_result(
	'Explicit full manual synchronization bypasses the automatic window while keeping normal limits',
	!empty($fullManualResult ['ok']) && $fullManualPostedOld && $fullManualPostedRecent,
	json_encode(array(
		'result' => $fullManualResult,
		'posted_old' => $fullManualPostedOld,
		'posted_recent' => $fullManualPostedRecent,
		'requests' => $fullManualRequests
	))
) && $allOk;

entry_delete($scheduledOldEntryId);
entry_delete($scheduledRecentEntryId);
unset($GLOBALS ['plugin_mastodon_test_now']);

// Older mapped content that changed outside the scheduled window is synchronized through the dirty queue.
simulate_delete_recursive($simRoot . '/fp-content/plugin_mastodon');
mkdir($simRoot . '/fp-content/plugin_mastodon', 0777, true);
plugin_mastodon_state_write(plugin_mastodon_default_state());

$GLOBALS ['plugin_mastodon_test_now'] = strtotime('2035-01-15 12:00:00 UTC');
$dirtyOldEntry = array(
	'version' => system_ver(),
	'subject' => 'Dirty queue old mapped entry',
	'content' => 'Original older mapped content.',
	'author' => 'Simulation',
	'date' => strtotime('2034-12-20 08:00:00 UTC')
);
$dirtyOldEntryId = entry_save($dirtyOldEntry, null);
$dirtyOldEntryParsed = entry_parse($dirtyOldEntryId);
$dirtyQueueState = plugin_mastodon_default_state();
plugin_mastodon_state_set_entry_mapping(
	$dirtyQueueState,
	$dirtyOldEntryId,
	'dirty-queue-remote-1',
	'local',
	plugin_mastodon_entry_hash($dirtyOldEntryParsed),
	$instanceUrl . '/@flatpress/dirty-queue-remote-1',
	'2034-12-20 08:00:00'
);
plugin_mastodon_state_write($dirtyQueueState);
$dirtyOldEntryChanged = $dirtyOldEntryParsed;
$dirtyOldEntryChanged ['content'] = 'Changed older mapped content that must still be synchronized.';
entry_save($dirtyOldEntryChanged, $dirtyOldEntryId);
$dirtyQueueHookState = plugin_mastodon_state_read();
$dirtyQueueHookQueued = plugin_mastodon_state_has_dirty_entry($dirtyQueueHookState, $dirtyOldEntryId);

$dirtyQueueOptions = $seededOptions;
$dirtyQueueOptions ['sync_start_date'] = '2034-01-01';
$dirtyQueueOptions ['sync_scheduled_window_days'] = '7';
$dirtyQueueOptions ['access_token'] = 'token123';
plugin_mastodon_save_options($dirtyQueueOptions);

$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $instanceUrl . '/api/v2/instance' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('configuration' => array('statuses' => array('max_characters' => 500, 'max_media_attachments' => 4))))
	),
	'PUT ' . $instanceUrl . '/api/v1/statuses/dirty-queue-remote-1' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			'id' => 'dirty-queue-remote-1',
			'url' => $instanceUrl . '/@flatpress/dirty-queue-remote-1',
			'edited_at' => '2035-01-15T12:00:00Z'
		))
	)
);

$dirtyQueueState = plugin_mastodon_state_read();
$dirtyQueueOk = plugin_mastodon_sync_local_to_remote($dirtyQueueOptions, $dirtyQueueState, false);
$dirtyQueuePutSeen = false;
foreach (simulate_recorded_http_requests() as $request) {
	if (is_array($request) && strtoupper((string) (isset($request ['method']) ? $request ['method'] : '')) === 'PUT' && strpos((string) $request ['url'], '/api/v1/statuses/dirty-queue-remote-1') !== false) {
		$dirtyQueuePutSeen = true;
	}
}
$allOk = test_result(
	'Older changed mapped FlatPress entries are synchronized through the dirty queue',
	$dirtyQueueHookQueued
		&& $dirtyQueueOk
		&& $dirtyQueuePutSeen
		&& empty($dirtyQueueState ['dirty_entries'])
		&& !empty($dirtyQueueState ['entries'] [$dirtyOldEntryId] ['hash'])
		&& $dirtyQueueState ['entries'] [$dirtyOldEntryId] ['hash'] === plugin_mastodon_entry_hash(entry_parse($dirtyOldEntryId)),
	json_encode(array(
		'hook_queued' => $dirtyQueueHookQueued,
		'put_seen' => $dirtyQueuePutSeen,
		'dirty_entries' => isset($dirtyQueueState ['dirty_entries']) ? $dirtyQueueState ['dirty_entries'] : array(),
		'entry_meta' => plugin_mastodon_state_get_entry_meta($dirtyQueueState, $dirtyOldEntryId)
	))
) && $allOk;

entry_delete($dirtyOldEntryId);
unset($GLOBALS ['plugin_mastodon_test_now']);

// Dirty comment hooks queue older changed mapped comments without scanning all old entries.
simulate_delete_recursive($simRoot . '/fp-content/plugin_mastodon');
mkdir($simRoot . '/fp-content/plugin_mastodon', 0777, true);
plugin_mastodon_state_write(plugin_mastodon_default_state());
$GLOBALS ['plugin_mastodon_test_now'] = strtotime('2035-01-15 12:00:00 UTC');

$dirtyCommentEntry = array(
	'version' => system_ver(),
	'subject' => 'Dirty comment entry',
	'content' => 'Entry with an older mapped comment.',
	'author' => 'Simulation',
	'date' => strtotime('2034-12-20 09:00:00 UTC')
);
$dirtyCommentEntryId = entry_save($dirtyCommentEntry, null);
$dirtyCommentEntryParsed = entry_parse($dirtyCommentEntryId);
$dirtyComment = array(
	'version' => system_ver(),
	'name' => 'Simulation',
	'content' => 'Original older mapped comment.',
	'date' => strtotime('2034-12-20 09:05:00 UTC')
);
$dirtyCommentId = comment_save($dirtyCommentEntryId, $dirtyComment);
$dirtyCommentParsed = comment_parse($dirtyCommentEntryId, $dirtyCommentId);

$dirtyCommentState = plugin_mastodon_default_state();
plugin_mastodon_state_set_entry_mapping($dirtyCommentState, $dirtyCommentEntryId, 'dirty-comment-entry-remote', 'local', plugin_mastodon_entry_hash($dirtyCommentEntryParsed), $instanceUrl . '/@flatpress/dirty-comment-entry-remote', '2034-12-20 09:00:00');
plugin_mastodon_state_set_comment_mapping($dirtyCommentState, $dirtyCommentEntryId, $dirtyCommentId, 'dirty-comment-remote-1', 'local', plugin_mastodon_comment_hash($dirtyCommentParsed), $instanceUrl . '/@flatpress/dirty-comment-remote-1', '2034-12-20 09:05:00');
plugin_mastodon_state_write($dirtyCommentState);

$dirtyCommentChanged = $dirtyCommentParsed;
$dirtyCommentChanged ['content'] = 'Changed older mapped comment that must be synchronized.';
comment_save($dirtyCommentEntryId, $dirtyCommentChanged);
$dirtyCommentHookState = plugin_mastodon_state_read();
$allOk = test_result(
	'Post-success comment hook queues older changed mapped comments',
	plugin_mastodon_state_has_dirty_comment($dirtyCommentHookState, $dirtyCommentEntryId, $dirtyCommentId),
	json_encode(array(
		'dirty_comments' => isset($dirtyCommentHookState ['dirty_comments']) ? $dirtyCommentHookState ['dirty_comments'] : array()
	))
) && $allOk;

$dirtyCommentSyncOptions = $seededOptions;
$dirtyCommentSyncOptions ['sync_start_date'] = '2034-01-01';
$dirtyCommentSyncOptions ['sync_scheduled_window_days'] = '7';
$dirtyCommentSyncOptions ['access_token'] = 'token123';
plugin_mastodon_save_options($dirtyCommentSyncOptions);
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $instanceUrl . '/api/v2/instance' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('configuration' => array('statuses' => array('max_characters' => 500, 'max_media_attachments' => 4))))
	),
	'PUT ' . $instanceUrl . '/api/v1/statuses/dirty-comment-remote-1' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('id' => 'dirty-comment-remote-1', 'url' => $instanceUrl . '/@flatpress/dirty-comment-remote-1', 'edited_at' => '2035-01-15T12:00:00Z'))
	)
);
$dirtyCommentRunState = plugin_mastodon_state_read();
$dirtyCommentSyncOk = plugin_mastodon_sync_local_to_remote($dirtyCommentSyncOptions, $dirtyCommentRunState, false);
$dirtyCommentRequests = simulate_recorded_http_requests();
$dirtyCommentPutSeen = simulate_first_http_request($dirtyCommentRequests, 'PUT', '/api/v1/statuses/dirty-comment-remote-1') !== array();
$allOk = test_result(
	'Scheduled sync updates older dirty comments through direct YY/MM dirty-parent candidates',
	$dirtyCommentSyncOk
		&& $dirtyCommentPutSeen
		&& !plugin_mastodon_state_has_dirty_comment($dirtyCommentRunState, $dirtyCommentEntryId, $dirtyCommentId),
	json_encode(array(
		'put_seen' => $dirtyCommentPutSeen,
		'dirty_comments' => isset($dirtyCommentRunState ['dirty_comments']) ? $dirtyCommentRunState ['dirty_comments'] : array()
	))
) && $allOk;

entry_delete($dirtyCommentEntryId);
unset($GLOBALS ['plugin_mastodon_test_now']);

// Remote-write guard prevents plugin-owned remote imports from being recorded as local dirty edits.
simulate_delete_recursive($simRoot . '/fp-content/plugin_mastodon');
mkdir($simRoot . '/fp-content/plugin_mastodon', 0777, true);
plugin_mastodon_state_write(plugin_mastodon_default_state());
$GLOBALS ['plugin_mastodon_test_now'] = strtotime('2035-01-15 12:00:00 UTC');

$guardEntry = array(
	'version' => system_ver(),
	'subject' => 'Remote guard local entry',
	'content' => 'Before remote mirror update.',
	'author' => 'Simulation',
	'date' => strtotime('2035-01-14 10:00:00 UTC')
);
$guardEntryId = entry_save($guardEntry, null);
$guardEntryParsed = entry_parse($guardEntryId);
$guardState = plugin_mastodon_default_state();
plugin_mastodon_state_set_entry_mapping($guardState, $guardEntryId, 'remote-guard-status-1', 'local', plugin_mastodon_entry_hash($guardEntryParsed), $instanceUrl . '/@flatpress/remote-guard-status-1', '2035-01-14 10:00:00');
plugin_mastodon_state_write($guardState);

$guardStateForImport = plugin_mastodon_state_read();
$guardImportId = plugin_mastodon_import_remote_entry($seededOptions, $guardStateForImport, array(
	'id' => 'remote-guard-status-1',
	'content' => '<p>Remote mirror update must not become a local dirty edit.</p>',
	'url' => $instanceUrl . '/@flatpress/remote-guard-status-1',
	'created_at' => '2035-01-14T10:00:00Z',
	'edited_at' => '2035-01-15T12:00:00Z',
	'account' => array(
		'acct' => 'flatpress@example.social',
		'display_name' => 'FlatPress Bot',
		'url' => $instanceUrl . '/@flatpress'
	)
));
$guardStateAfterImport = plugin_mastodon_state_read();
$allOk = test_result(
	'Remote-write guard suppresses dirty tracking for Mastodon-owned entry_save calls',
	$guardImportId === $guardEntryId && empty($guardStateAfterImport ['dirty_entries']),
	json_encode(array(
		'import_id' => $guardImportId,
		'dirty_entries' => isset($guardStateAfterImport ['dirty_entries']) ? $guardStateAfterImport ['dirty_entries'] : array()
	))
) && $allOk;
entry_delete($guardEntryId);
unset($GLOBALS ['plugin_mastodon_test_now']);

// Large scheduled local-to-remote pass parses only active-window and dirty entries, not thousands of old fixtures.
simulate_delete_recursive($simRoot . '/fp-content/plugin_mastodon');
mkdir($simRoot . '/fp-content/plugin_mastodon', 0777, true);
plugin_mastodon_state_write(plugin_mastodon_default_state());
$GLOBALS ['plugin_mastodon_test_now'] = strtotime('2035-01-15 12:00:00 UTC');

$largeDirtyEntryId = 'entry341220-080000';
$largeRecentEntryId = 'entry350114-080000';
$largeFixtureEntryIds = array($largeDirtyEntryId, $largeRecentEntryId);
for ($i = 0; $i < 3000; $i++) {
	$timestamp = strtotime('2034-10-01 00:00:00 UTC') + ($i * 60);
	$entryId = 'entry' . gmdate('ymd-His', $timestamp);
	$largeFixtureEntryIds [] = $entryId;
	simulate_write_entry_fixture($entryId, array(
		'version' => system_ver(),
		'subject' => 'Large old fixture ' . $i,
		'content' => 'Old fixture outside the active synchronization window.',
		'author' => 'Simulation',
		'date' => $timestamp
	));
}

$largeDirtyOldHashEntry = array(
	'version' => system_ver(),
	'subject' => 'Large dirty old mapped entry',
	'content' => 'Old mapped content before local edit.',
	'author' => 'Simulation',
	'date' => strtotime('2034-12-20 08:00:00 UTC')
);
$largeDirtyChangedEntry = $largeDirtyOldHashEntry;
$largeDirtyChangedEntry ['content'] = 'Old mapped content after local edit.';
simulate_write_entry_fixture($largeDirtyEntryId, $largeDirtyChangedEntry);
simulate_write_entry_fixture($largeRecentEntryId, array(
	'version' => system_ver(),
	'subject' => 'Large recent entry',
	'content' => 'Recent content inside the active synchronization window.',
	'author' => 'Simulation',
	'date' => strtotime('2035-01-14 08:00:00 UTC')
));

$largeTargetedState = plugin_mastodon_default_state();
plugin_mastodon_state_set_entry_mapping($largeTargetedState, $largeDirtyEntryId, 'large-dirty-remote-1', 'local', plugin_mastodon_entry_hash($largeDirtyOldHashEntry), $instanceUrl . '/@flatpress/large-dirty-remote-1', '2034-12-20 08:00:00');
plugin_mastodon_state_set_dirty_entry($largeTargetedState, $largeDirtyEntryId, plugin_mastodon_entry_hash($largeDirtyChangedEntry));
plugin_mastodon_state_write($largeTargetedState);

$largeTargetedOptions = $seededOptions;
$largeTargetedOptions ['sync_start_date'] = '2034-01-01';
$largeTargetedOptions ['sync_scheduled_window_days'] = '7';
$largeTargetedOptions ['access_token'] = 'token123';
plugin_mastodon_save_options($largeTargetedOptions);

$GLOBALS ['plugin_mastodon_test_local_entry_parse_count'] = 0;
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $instanceUrl . '/api/v2/instance' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('configuration' => array('statuses' => array('max_characters' => 500, 'max_media_attachments' => 4))))
	),
	'PUT ' . $instanceUrl . '/api/v1/statuses/large-dirty-remote-1' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('id' => 'large-dirty-remote-1', 'url' => $instanceUrl . '/@flatpress/large-dirty-remote-1', 'edited_at' => '2035-01-15T12:00:00Z'))
	),
	'POST ' . $instanceUrl . '/api/v1/statuses' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('id' => 'large-recent-remote-1', 'url' => $instanceUrl . '/@flatpress/large-recent-remote-1', 'created_at' => '2035-01-14T08:00:00Z'))
	)
);
$largeTargetedRunState = plugin_mastodon_state_read();
$largeTargetedOk = plugin_mastodon_sync_local_to_remote($largeTargetedOptions, $largeTargetedRunState, false);
$largeTargetedParseCount = simulate_local_entry_parse_count();
unset($GLOBALS ['plugin_mastodon_test_local_entry_parse_count']);
$largeTargetedRequests = simulate_recorded_http_requests();
$largeTargetedPutSeen = simulate_first_http_request($largeTargetedRequests, 'PUT', '/api/v1/statuses/large-dirty-remote-1') !== array();
$largeTargetedPostSeen = simulate_first_http_request($largeTargetedRequests, 'POST', '/api/v1/statuses') !== array();

$allOk = test_result(
	'Large scheduled dirty-tracking sync parses only active-window and dirty entries',
	$largeTargetedOk && $largeTargetedPutSeen && $largeTargetedPostSeen && $largeTargetedParseCount <= 3,
	json_encode(array(
		'parse_count' => $largeTargetedParseCount,
		'fixture_entries' => count($largeFixtureEntryIds),
		'put_seen' => $largeTargetedPutSeen,
		'post_seen' => $largeTargetedPostSeen
	))
) && $allOk;

foreach ($largeFixtureEntryIds as $entryId) {
	simulate_delete_entry_fixture($entryId);
}
unset($GLOBALS ['plugin_mastodon_test_now']);

// Optional old-thread reply checks rotate through known synchronized threads in bounded batches.
simulate_delete_recursive($simRoot . '/fp-content/plugin_mastodon');
mkdir($simRoot . '/fp-content/plugin_mastodon', 0777, true);
plugin_mastodon_state_write(plugin_mastodon_default_state());

$oldThreadRotateEntryA = array(
	'version' => system_ver(),
	'subject' => 'Rotating old thread A',
	'content' => 'First old thread for rotating context checks.',
	'author' => 'Simulation',
	'date' => strtotime('2034-12-20 08:00:00 UTC')
);
$oldThreadRotateEntryAId = entry_save($oldThreadRotateEntryA, null);
$oldThreadRotateEntryB = array(
	'version' => system_ver(),
	'subject' => 'Rotating old thread B',
	'content' => 'Second old thread for rotating context checks.',
	'author' => 'Simulation',
	'date' => strtotime('2034-12-21 08:00:00 UTC')
);
$oldThreadRotateEntryBId = entry_save($oldThreadRotateEntryB, null);
$oldThreadRotateState = plugin_mastodon_default_state();
$oldThreadRotateState ['last_remote_status_id'] = 'rotate-last';
plugin_mastodon_state_set_entry_mapping($oldThreadRotateState, $oldThreadRotateEntryAId, 'rotate-thread-a', 'local', plugin_mastodon_entry_hash(entry_parse($oldThreadRotateEntryAId)), $instanceUrl . '/@flatpress/rotate-thread-a', '2034-12-20 08:00:00');
plugin_mastodon_state_set_entry_mapping($oldThreadRotateState, $oldThreadRotateEntryBId, 'rotate-thread-b', 'local', plugin_mastodon_entry_hash(entry_parse($oldThreadRotateEntryBId)), $instanceUrl . '/@flatpress/rotate-thread-b', '2034-12-21 08:00:00');
plugin_mastodon_state_write($oldThreadRotateState);

$oldThreadRotateOptions = $seededOptions;
$oldThreadRotateOptions ['sync_start_date'] = '2034-01-01';
$oldThreadRotateOptions ['sync_scheduled_window_days'] = '7';
$oldThreadRotateOptions ['old_thread_reply_check'] = '1';
$oldThreadRotateOptions ['access_token'] = 'token123';
plugin_mastodon_save_options($oldThreadRotateOptions);
$GLOBALS ['plugin_mastodon_test_old_thread_context_rotation_limit'] = 1;
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $instanceUrl . '/api/v1/accounts/verify_credentials' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('id' => 'acct1', 'username' => 'flatpress'))
	),
	'GET ' . $instanceUrl . '/api/v1/accounts/acct1/statuses?limit=40&exclude_reblogs=true&exclude_replies=true&since_id=rotate-last' => array(
		array('ok' => true, 'code' => 200, 'body' => json_encode(array())),
		array('ok' => true, 'code' => 200, 'body' => json_encode(array()))
	),
	'GET ' . $instanceUrl . '/api/v1/statuses/rotate-thread-a/context' => array('ok' => true, 'code' => 200, 'body' => json_encode(array('descendants' => array()))),
	'GET ' . $instanceUrl . '/api/v1/statuses/rotate-thread-b/context' => array('ok' => true, 'code' => 200, 'body' => json_encode(array('descendants' => array())))
);

$oldThreadRotateState = plugin_mastodon_state_read();
$oldThreadRotateFirstOk = plugin_mastodon_sync_remote_to_local($oldThreadRotateOptions, $oldThreadRotateState, false);
$oldThreadRotateFirstRequests = simulate_recorded_http_requests();
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$oldThreadRotateSecondOk = plugin_mastodon_sync_remote_to_local($oldThreadRotateOptions, $oldThreadRotateState, false);
$oldThreadRotateSecondRequests = simulate_recorded_http_requests();
$oldThreadRotateFirstContext = '';
foreach ($oldThreadRotateFirstRequests as $request) {
	if (is_array($request) && strpos((string) (isset($request ['url']) ? $request ['url'] : ''), '/context') !== false) {
		$oldThreadRotateFirstContext = (string) $request ['url'];
	}
}
$oldThreadRotateSecondContext = '';
foreach ($oldThreadRotateSecondRequests as $request) {
	if (is_array($request) && strpos((string) (isset($request ['url']) ? $request ['url'] : ''), '/context') !== false) {
		$oldThreadRotateSecondContext = (string) $request ['url'];
	}
}
$allOk = test_result(
	'Optional old-thread reply checks rotate through known synchronized threads',
	$oldThreadRotateFirstOk
		&& $oldThreadRotateSecondOk
		&& strpos($oldThreadRotateFirstContext, 'rotate-thread-a/context') !== false
		&& strpos($oldThreadRotateSecondContext, 'rotate-thread-b/context') !== false,
	json_encode(array(
		'first_context' => $oldThreadRotateFirstContext,
		'second_context' => $oldThreadRotateSecondContext,
		'cursor' => isset($oldThreadRotateState ['old_thread_context_cursor']) ? $oldThreadRotateState ['old_thread_context_cursor'] : ''
	))
) && $allOk;

$oldThreadDisabledState = plugin_mastodon_default_state();
$oldThreadDisabledState ['last_remote_status_id'] = 'rotate-last';
plugin_mastodon_state_set_entry_mapping($oldThreadDisabledState, $oldThreadRotateEntryAId, 'rotate-thread-a', 'local', plugin_mastodon_entry_hash(entry_parse($oldThreadRotateEntryAId)), $instanceUrl . '/@flatpress/rotate-thread-a', '2034-12-20 08:00:00');
plugin_mastodon_state_write($oldThreadDisabledState);

$oldThreadDisabledOptions = $oldThreadRotateOptions;
$oldThreadDisabledOptions ['old_thread_reply_check'] = '0';
plugin_mastodon_save_options($oldThreadDisabledOptions);
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $instanceUrl . '/api/v1/accounts/verify_credentials' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('id' => 'acct1', 'username' => 'flatpress'))
	),
	'GET ' . $instanceUrl . '/api/v1/accounts/acct1/statuses?limit=40&exclude_reblogs=true&exclude_replies=true&since_id=rotate-last' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array())
	)
);
$oldThreadDisabledOk = plugin_mastodon_sync_remote_to_local($oldThreadDisabledOptions, $oldThreadDisabledState, false);
$oldThreadDisabledContextRequests = 0;
foreach (simulate_recorded_http_requests() as $request) {
	if (is_array($request) && strpos((string) (isset($request ['url']) ? $request ['url'] : ''), '/context') !== false) {
		$oldThreadDisabledContextRequests++;
	}
}
$allOk = test_result(
	'Disabled old-thread reply checks do not refresh known synchronized entry contexts',
	$oldThreadDisabledOk && $oldThreadDisabledContextRequests === 0,
	json_encode(array(
		'context_requests' => $oldThreadDisabledContextRequests,
		'requests' => simulate_recorded_http_requests()
	))
) && $allOk;

// Automatic scheduled content runs must keep rotating known synchronized threads through the full run_sync(false) path.
$automaticThreadState = plugin_mastodon_default_state();
$automaticThreadState ['last_run'] = '2000-01-01 00:00:00';
$automaticThreadState ['last_remote_status_id'] = 'rotate-last-auto';
plugin_mastodon_state_set_entry_mapping($automaticThreadState, $oldThreadRotateEntryAId, 'rotate-thread-auto-a', 'local', plugin_mastodon_entry_hash(entry_parse($oldThreadRotateEntryAId)), $instanceUrl . '/@flatpress/rotate-thread-auto-a', '2034-12-20 08:00:00');
plugin_mastodon_state_set_entry_mapping($automaticThreadState, $oldThreadRotateEntryBId, 'rotate-thread-auto-b', 'local', plugin_mastodon_entry_hash(entry_parse($oldThreadRotateEntryBId)), $instanceUrl . '/@flatpress/rotate-thread-auto-b', '2034-12-21 08:00:00');
plugin_mastodon_state_write($automaticThreadState);

$automaticThreadOptions = $oldThreadRotateOptions;
$automaticThreadOptions ['sync_time'] = '00:00';
$automaticThreadOptions ['sync_start_date'] = '2034-01-01';
$automaticThreadOptions ['sync_scheduled_window_days'] = '7';
$automaticThreadOptions ['old_thread_reply_check'] = '1';
plugin_mastodon_save_options($automaticThreadOptions);
plugin_mastodon_sync_guard_clear('content');
$GLOBALS ['plugin_mastodon_test_old_thread_context_rotation_limit'] = 1;
$GLOBALS ['plugin_mastodon_test_now'] = strtotime('2035-01-15 12:00:00 UTC');
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $instanceUrl . '/api/v1/accounts/verify_credentials' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('id' => 'acct1', 'username' => 'flatpress'))
	),
	'GET ' . $instanceUrl . '/api/v1/accounts/acct1/statuses?limit=40&exclude_reblogs=true&exclude_replies=true&since_id=rotate-last-auto' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array())
	),
	'GET ' . $instanceUrl . '/api/v1/statuses/rotate-thread-auto-a/context' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('descendants' => array()))
	),
	'GET ' . $instanceUrl . '/api/v2/instance' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('configuration' => array('statuses' => array('max_characters' => 500, 'max_media_attachments' => 4))))
	)
);
$automaticThreadResult = plugin_mastodon_run_sync(false);
$automaticThreadRequests = simulate_recorded_http_requests();
$automaticThreadStateAfter = plugin_mastodon_state_read(array());
$automaticThreadContextUrl = '';
$automaticThreadUnexpectedPost = false;
foreach ($automaticThreadRequests as $request) {
	if (!is_array($request) || empty($request ['url'])) {
		continue;
	}
	$url = (string) $request ['url'];
	if (strpos($url, '/context') !== false) {
		$automaticThreadContextUrl = $url;
	}
	if (strtoupper((string) (isset($request ['method']) ? $request ['method'] : '')) === 'POST' && strpos($url, '/api/v1/statuses') !== false) {
		$automaticThreadUnexpectedPost = true;
	}
}
$allOk = test_result(
	'Automatic scheduled synchronization rotates known synchronized threads through the full sync path',
	!empty($automaticThreadResult ['ok'])
		&& strpos($automaticThreadContextUrl, 'rotate-thread-auto-a/context') !== false
		&& !$automaticThreadUnexpectedPost
		&& isset($automaticThreadStateAfter ['old_thread_context_cursor'])
		&& (string) $automaticThreadStateAfter ['old_thread_context_cursor'] === 'rotate-thread-auto-a'
		&& isset($automaticThreadStateAfter ['last_run'])
		&& (string) $automaticThreadStateAfter ['last_run'] !== '2000-01-01 00:00:00',
	json_encode(array(
		'result' => $automaticThreadResult,
		'context_url' => $automaticThreadContextUrl,
		'unexpected_post' => $automaticThreadUnexpectedPost,
		'cursor' => isset($automaticThreadStateAfter ['old_thread_context_cursor']) ? $automaticThreadStateAfter ['old_thread_context_cursor'] : '',
		'last_run' => isset($automaticThreadStateAfter ['last_run']) ? $automaticThreadStateAfter ['last_run'] : ''
	))
) && $allOk;
plugin_mastodon_sync_guard_clear('content');
unset($GLOBALS ['plugin_mastodon_test_now']);

// Notification hints import new replies on old synchronized Mastodon threads before the slow rotation fallback.
$notificationThreadOptions = $oldThreadRotateOptions;
$notificationThreadOptions ['old_thread_reply_check'] = '1';
$notificationThreadOptions ['old_thread_context_limit'] = '1';
$notificationThreadOptions ['oauth_registered_scopes'] = 'read:accounts read:statuses read:notifications write:statuses write:media';
plugin_mastodon_save_options($notificationThreadOptions);

$notificationEntryState = plugin_mastodon_default_state();
$notificationEntryState ['last_remote_status_id'] = 'notification-last-status';
$notificationEntryState ['last_remote_notification_id'] = 'notification-100';
plugin_mastodon_state_set_entry_mapping($notificationEntryState, $oldThreadRotateEntryAId, 'notification-entry-root', 'local', plugin_mastodon_entry_hash(entry_parse($oldThreadRotateEntryAId)), $instanceUrl . '/@flatpress/notification-entry-root', '2034-12-20 08:00:00');
plugin_mastodon_state_write($notificationEntryState);
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $instanceUrl . '/api/v1/accounts/verify_credentials' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('id' => 'acct1', 'username' => 'flatpress'))
	),
	'GET ' . $instanceUrl . '/api/v1/accounts/acct1/statuses?limit=40&exclude_reblogs=true&exclude_replies=true&since_id=notification-last-status' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array())
	),
	'GET ' . $instanceUrl . '/api/v1/notifications?limit=30&types%5B%5D=mention&since_id=notification-100' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			array(
				'id' => 'notification-101',
				'type' => 'mention',
				'status' => array(
					'id' => 'notification-entry-reply',
					'visibility' => 'public',
					'in_reply_to_id' => 'notification-entry-root',
					'created_at' => '2035-01-15T03:10:00Z',
					'content' => '<p>Notification reply on an old mapped entry</p>',
					'url' => $instanceUrl . '/@alice/notification-entry-reply',
					'account' => array(
						'id' => 'acct2',
						'acct' => 'alice@example.net',
						'display_name' => 'Alice',
						'url' => 'https://example.net/@alice'
					)
				),
				'fallback' => array(
					'type' => 'mention',
					'text' => 'Fallback payload must not replace the normal mention status'
				)
			)
		))
	)
);
$notificationEntryState = plugin_mastodon_state_read();
$notificationEntryOk = plugin_mastodon_sync_remote_to_local($notificationThreadOptions, $notificationEntryState, false);
$notificationEntryRequests = simulate_recorded_http_requests();
$notificationEntryContextRequests = 0;
foreach ($notificationEntryRequests as $request) {
	if (is_array($request) && strpos((string) (isset($request ['url']) ? $request ['url'] : ''), '/context') !== false) {
		$notificationEntryContextRequests++;
	}
}
$notificationEntryRef = isset($notificationEntryState ['comments_remote'] ['notification-entry-reply']) ? $notificationEntryState ['comments_remote'] ['notification-entry-reply'] : array();
$notificationEntryComment = (!empty($notificationEntryRef ['entry_id']) && !empty($notificationEntryRef ['comment_id']))
	? comment_parse($notificationEntryRef ['entry_id'], $notificationEntryRef ['comment_id'])
	: array();
$allOk = test_result(
	'Notification hints import a new reply on an old mapped Mastodon entry without context rotation',
	$notificationEntryOk
		&& $notificationEntryContextRequests === 0
		&& !empty($notificationEntryRef)
		&& isset($notificationEntryComment ['content']) && strpos((string) $notificationEntryComment ['content'], 'Notification reply on an old mapped entry') !== false
		&& isset($notificationEntryState ['last_remote_notification_id']) && (string) $notificationEntryState ['last_remote_notification_id'] === 'notification-101',
	json_encode(array(
		'ref' => $notificationEntryRef,
		'comment' => $notificationEntryComment,
		'context_requests' => $notificationEntryContextRequests,
		'last_notification' => isset($notificationEntryState ['last_remote_notification_id']) ? $notificationEntryState ['last_remote_notification_id'] : '',
		'requests' => $notificationEntryRequests
	))
) && $allOk;

$allOk = test_result(
	'Notification fallback payloads are ignored when a normal mention status is available',
	$notificationEntryOk
		&& isset($notificationEntryComment ['content'])
		&& strpos((string) $notificationEntryComment ['content'], 'Notification reply on an old mapped entry') !== false
		&& strpos((string) $notificationEntryComment ['content'], 'Fallback payload must not replace') === false,
	json_encode(array(
		'comment' => $notificationEntryComment,
		'requests' => $notificationEntryRequests
	))
) && $allOk;

$notificationParentComment = array(
	'version' => system_ver(),
	'name' => 'Known parent',
	'content' => 'Already imported parent reply.',
	'date' => strtotime('2035-01-10 08:30:00 UTC')
);
$notificationParentCommentId = comment_save($oldThreadRotateEntryAId, $notificationParentComment);
$notificationReplyState = plugin_mastodon_default_state();
$notificationReplyState ['last_remote_status_id'] = 'notification-reply-last-status';
$notificationReplyState ['last_remote_notification_id'] = 'notification-200';
plugin_mastodon_state_set_entry_mapping($notificationReplyState, $oldThreadRotateEntryAId, 'notification-reply-root', 'local', plugin_mastodon_entry_hash(entry_parse($oldThreadRotateEntryAId)), $instanceUrl . '/@flatpress/notification-reply-root', '2034-12-20 08:00:00');
$notificationParentParsed = comment_parse($oldThreadRotateEntryAId, $notificationParentCommentId);
plugin_mastodon_state_set_comment_mapping($notificationReplyState, $oldThreadRotateEntryAId, $notificationParentCommentId, 'notification-parent-reply', 'remote', plugin_mastodon_comment_hash($notificationParentParsed), $instanceUrl . '/@bob/notification-parent-reply', '2035-01-10 08:30:00', '', 'notification-reply-root', plugin_mastodon_local_item_date_key($notificationParentParsed, $notificationParentCommentId), '2035-01-10');
plugin_mastodon_state_write($notificationReplyState);
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $instanceUrl . '/api/v1/accounts/verify_credentials' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('id' => 'acct1', 'username' => 'flatpress'))
	),
	'GET ' . $instanceUrl . '/api/v1/accounts/acct1/statuses?limit=40&exclude_reblogs=true&exclude_replies=true&since_id=notification-reply-last-status' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array())
	),
	'GET ' . $instanceUrl . '/api/v1/notifications?limit=30&types%5B%5D=mention&since_id=notification-200' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			array(
				'id' => 'notification-201',
				'type' => 'mention',
				'status' => array(
					'id' => 'notification-child-reply',
					'visibility' => 'public',
					'in_reply_to_id' => 'notification-parent-reply',
					'created_at' => '2035-01-15T03:20:00Z',
					'content' => '<p>Notification reply to an old known reply</p>',
					'url' => $instanceUrl . '/@carol/notification-child-reply',
					'account' => array(
						'id' => 'acct3',
						'acct' => 'carol@example.net',
						'display_name' => 'Carol',
						'url' => 'https://example.net/@carol'
					)
				)
			)
		))
	)
);
$notificationReplyState = plugin_mastodon_state_read();
$notificationReplyOk = plugin_mastodon_sync_remote_to_local($notificationThreadOptions, $notificationReplyState, false);
$notificationReplyRef = isset($notificationReplyState ['comments_remote'] ['notification-child-reply']) ? $notificationReplyState ['comments_remote'] ['notification-child-reply'] : array();
$notificationReplyComment = (!empty($notificationReplyRef ['entry_id']) && !empty($notificationReplyRef ['comment_id']))
	? comment_parse($notificationReplyRef ['entry_id'], $notificationReplyRef ['comment_id'])
	: array();
$allOk = test_result(
	'Notification hints import a new reply on an old mapped Mastodon reply with reply metadata',
	$notificationReplyOk
		&& !empty($notificationReplyRef)
		&& isset($notificationReplyComment ['replyto']) && (string) $notificationReplyComment ['replyto'] === (string) $notificationParentCommentId
		&& isset($notificationReplyState ['last_remote_notification_id']) && (string) $notificationReplyState ['last_remote_notification_id'] === 'notification-201',
	json_encode(array(
		'ref' => $notificationReplyRef,
		'comment' => $notificationReplyComment,
		'last_notification' => isset($notificationReplyState ['last_remote_notification_id']) ? $notificationReplyState ['last_remote_notification_id'] : ''
	))
) && $allOk;

$notificationContextState = plugin_mastodon_default_state();
$notificationContextState ['last_remote_status_id'] = 'notification-context-last-status';
$notificationContextState ['last_remote_notification_id'] = 'notification-300';
plugin_mastodon_state_set_entry_mapping($notificationContextState, $oldThreadRotateEntryBId, 'notification-context-root', 'local', plugin_mastodon_entry_hash(entry_parse($oldThreadRotateEntryBId)), $instanceUrl . '/@flatpress/notification-context-root', '2034-12-21 08:00:00');
plugin_mastodon_state_write($notificationContextState);
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $instanceUrl . '/api/v1/accounts/verify_credentials' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('id' => 'acct1', 'username' => 'flatpress'))
	),
	'GET ' . $instanceUrl . '/api/v1/accounts/acct1/statuses?limit=40&exclude_reblogs=true&exclude_replies=true&since_id=notification-context-last-status' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array())
	),
	'GET ' . $instanceUrl . '/api/v1/notifications?limit=30&types%5B%5D=mention&since_id=notification-300' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			array(
				'id' => 'notification-301',
				'type' => 'mention',
				'status' => array(
					'id' => 'notification-context-child',
					'visibility' => 'public',
					'in_reply_to_id' => 'notification-context-parent',
					'created_at' => '2035-01-15T03:30:00Z',
					'content' => '<p>Notification child reply after an unmapped parent</p>',
					'url' => $instanceUrl . '/@erin/notification-context-child',
					'account' => array(
						'id' => 'acct5',
						'acct' => 'erin@example.net',
						'display_name' => 'Erin',
						'url' => 'https://example.net/@erin'
					)
				)
			)
		))
	),
	'GET ' . $instanceUrl . '/api/v1/statuses/notification-context-child/context' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			'ancestors' => array(
				array(
					'id' => 'notification-context-root',
					'visibility' => 'public',
					'in_reply_to_id' => null,
					'created_at' => '2034-12-21T08:00:00Z',
					'content' => '<p>Root entry</p>',
					'account' => array('id' => 'acct1', 'acct' => 'flatpress', 'display_name' => 'FlatPress')
				),
				array(
					'id' => 'notification-context-parent',
					'visibility' => 'public',
					'in_reply_to_id' => 'notification-context-root',
					'created_at' => '2035-01-14T09:00:00Z',
					'content' => '<p>Unmapped parent reply from context</p>',
					'url' => $instanceUrl . '/@dave/notification-context-parent',
					'account' => array(
						'id' => 'acct4',
						'acct' => 'dave@example.net',
						'display_name' => 'Dave',
						'url' => 'https://example.net/@dave'
					)
				)
			),
			'descendants' => array()
		))
	)
);
$notificationContextState = plugin_mastodon_state_read();
$notificationContextOk = plugin_mastodon_sync_remote_to_local($notificationThreadOptions, $notificationContextState, false);
$notificationContextRequests = simulate_recorded_http_requests();
$notificationContextChildRef = isset($notificationContextState ['comments_remote'] ['notification-context-child']) ? $notificationContextState ['comments_remote'] ['notification-context-child'] : array();
$notificationContextParentRef = isset($notificationContextState ['comments_remote'] ['notification-context-parent']) ? $notificationContextState ['comments_remote'] ['notification-context-parent'] : array();
$notificationContextRootRotationRequests = 0;
$notificationContextChildContextRequests = 0;
foreach ($notificationContextRequests as $request) {
	if (!is_array($request) || empty($request ['url'])) {
		continue;
	}
	$requestUrl = (string) $request ['url'];
	if (strpos($requestUrl, '/api/v1/statuses/notification-context-root/context') !== false) {
		$notificationContextRootRotationRequests++;
	}
	if (strpos($requestUrl, '/api/v1/statuses/notification-context-child/context') !== false) {
		$notificationContextChildContextRequests++;
	}
}
$allOk = test_result(
	'Notification context hints use the old-thread budget before normal rotation',
	$notificationContextOk
		&& !empty($notificationContextParentRef)
		&& !empty($notificationContextChildRef)
		&& $notificationContextChildContextRequests === 1
		&& $notificationContextRootRotationRequests === 0,
	json_encode(array(
		'parent_ref' => $notificationContextParentRef,
		'child_ref' => $notificationContextChildRef,
		'child_context_requests' => $notificationContextChildContextRequests,
		'root_rotation_requests' => $notificationContextRootRotationRequests,
		'requests' => $notificationContextRequests
	))
) && $allOk;

$notificationMissingScopeState = plugin_mastodon_default_state();
$notificationMissingScopeState ['last_remote_status_id'] = 'notification-missing-scope-status';
$notificationMissingScopeState ['last_remote_notification_id'] = 'notification-400';
plugin_mastodon_state_set_entry_mapping($notificationMissingScopeState, $oldThreadRotateEntryAId, 'notification-missing-scope-root', 'local', plugin_mastodon_entry_hash(entry_parse($oldThreadRotateEntryAId)), $instanceUrl . '/@flatpress/notification-missing-scope-root', '2034-12-20 08:00:00');
plugin_mastodon_state_write($notificationMissingScopeState);
$notificationMissingScopeOptions = $notificationThreadOptions;
$notificationMissingScopeOptions ['oauth_registered_scopes'] = plugin_mastodon_oauth_legacy_scopes();
plugin_mastodon_save_options($notificationMissingScopeOptions);
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $instanceUrl . '/api/v1/accounts/verify_credentials' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('id' => 'acct1', 'username' => 'flatpress'))
	),
	'GET ' . $instanceUrl . '/api/v1/accounts/acct1/statuses?limit=40&exclude_reblogs=true&exclude_replies=true&since_id=notification-missing-scope-status' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array())
	),
	'GET ' . $instanceUrl . '/api/v1/statuses/notification-missing-scope-root/context' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('descendants' => array()))
	)
);
$notificationMissingScopeState = plugin_mastodon_state_read();
$notificationMissingScopeOk = plugin_mastodon_sync_remote_to_local($notificationMissingScopeOptions, $notificationMissingScopeState, false);
$notificationMissingScopeRequests = simulate_recorded_http_requests();
$notificationMissingScopeNotificationRequests = 0;
foreach ($notificationMissingScopeRequests as $request) {
	if (is_array($request) && strpos((string) (isset($request ['url']) ? $request ['url'] : ''), '/api/v1/notifications') !== false) {
		$notificationMissingScopeNotificationRequests++;
	}
}
$allOk = test_result(
	'Reply-notification polling is skipped until read:notifications is authorized',
	$notificationMissingScopeOk && $notificationMissingScopeNotificationRequests === 0,
	json_encode(array(
		'notification_requests' => $notificationMissingScopeNotificationRequests,
		'requests' => $notificationMissingScopeRequests
	))
) && $allOk;

entry_delete($oldThreadRotateEntryAId);
entry_delete($oldThreadRotateEntryBId);
unset($GLOBALS ['plugin_mastodon_test_old_thread_context_rotation_limit']);

// Regression test: existing local content is not overwritten by Mastodon when the toggle is disabled.
simulate_delete_recursive($simRoot . '/fp-content/plugin_mastodon');
mkdir($simRoot . '/fp-content/plugin_mastodon', 0777, true);
plugin_mastodon_state_write(plugin_mastodon_default_state());

$existingRemoteEntry = array(
	'version' => system_ver(),
	'subject' => 'Local entry stays local',
	'content' => 'Local entry body that must not be replaced while updates are disabled.',
	'author' => 'Local Author',
	'date' => strtotime('2026-03-14 08:00:00 UTC')
);
$existingRemoteEntryId = entry_save($existingRemoteEntry, null);
$existingRemoteEntryParsed = entry_parse($existingRemoteEntryId);

$existingRemoteComment = array(
	'version' => system_ver(),
	'name' => 'Local Commenter',
	'content' => 'Local comment body that must not be replaced while updates are disabled.',
	'date' => strtotime('2026-03-14 08:05:00 UTC')
);
$existingRemoteCommentId = comment_save($existingRemoteEntryId, $existingRemoteComment);

$remoteUpdateState = plugin_mastodon_default_state();
plugin_mastodon_state_set_entry_mapping(
	$remoteUpdateState,
	$existingRemoteEntryId,
	'remote-update-entry-900',
	'remote',
	plugin_mastodon_entry_hash($existingRemoteEntryParsed),
	$instanceUrl . '/@flatpress/remote-update-entry-900',
	'2026-03-14 08:00:00'
);
$existingRemoteCommentParsed = comment_parse($existingRemoteEntryId, $existingRemoteCommentId);
plugin_mastodon_state_set_comment_mapping(
	$remoteUpdateState,
	$existingRemoteEntryId,
	$existingRemoteCommentId,
	'remote-update-comment-901',
	'remote',
	plugin_mastodon_comment_hash($existingRemoteCommentParsed),
	$instanceUrl . '/@flatpress/remote-update-comment-901',
	'2026-03-14 08:05:00',
	'',
	'remote-update-entry-900'
);
plugin_mastodon_state_write($remoteUpdateState);

$remoteUpdateOptions = $seededOptions;
$remoteUpdateOptions ['sync_start_date'] = '';
$remoteUpdateOptions ['update_local_from_remote'] = '0';
$remoteUpdateOptions ['access_token'] = 'token123';
plugin_mastodon_save_options($remoteUpdateOptions);

$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $instanceUrl . '/api/v1/accounts/verify_credentials' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('id' => 'acct1', 'username' => 'flatpress'))
	),
	'GET ' . $instanceUrl . '/api/v1/accounts/acct1/statuses?limit=40&exclude_reblogs=true&exclude_replies=true' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			array(
				'id' => 'remote-update-entry-900',
				'visibility' => 'public',
				'created_at' => '2026-03-14T08:00:00Z',
				'edited_at' => '2026-03-15T08:15:00Z',
				'content' => '<p>Remote changed entry body</p>',
				'url' => $instanceUrl . '/@flatpress/remote-update-entry-900',
				'account' => array(
					'id' => 'acct1',
					'acct' => 'flatpress',
					'display_name' => 'FlatPress Bot',
					'url' => $instanceUrl . '/@flatpress'
				)
			)
		))
	),
	'GET ' . $instanceUrl . '/api/v1/statuses/remote-update-entry-900/context' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			'descendants' => array(
				array(
					'id' => 'remote-update-comment-901',
					'visibility' => 'public',
					'in_reply_to_id' => 'remote-update-entry-900',
					'created_at' => '2026-03-14T08:05:00Z',
					'edited_at' => '2026-03-15T08:20:00Z',
					'content' => '<p>Remote changed comment body</p>',
					'url' => $instanceUrl . '/@flatpress/remote-update-comment-901',
					'account' => array(
						'id' => 'acct2',
						'acct' => 'alice@example.net',
						'display_name' => 'Alice',
						'url' => 'https://example.net/@alice'
					)
				)
			)
		))
	)
);

$remoteUpdateState = plugin_mastodon_state_read();
$remoteUpdateImportOk = plugin_mastodon_sync_remote_to_local($remoteUpdateOptions, $remoteUpdateState);
$existingEntryAfterDisabled = entry_parse($existingRemoteEntryId);
$existingCommentAfterDisabled = comment_parse($existingRemoteEntryId, $existingRemoteCommentId);
$allOk = test_result(
	'Remote updates do not overwrite existing local content when the toggle is disabled',
	$remoteUpdateImportOk
		&& isset($existingEntryAfterDisabled ['content'])
		&& strpos((string) $existingEntryAfterDisabled ['content'], 'must not be replaced') !== false
		&& isset($existingCommentAfterDisabled ['content'])
		&& strpos((string) $existingCommentAfterDisabled ['content'], 'must not be replaced') !== false,
	json_encode(array(
		'entry' => isset($existingEntryAfterDisabled ['content']) ? (string) $existingEntryAfterDisabled ['content'] : '',
		'comment' => isset($existingCommentAfterDisabled ['content']) ? (string) $existingCommentAfterDisabled ['content'] : ''
	))
) && $allOk;

// Regression test: existing local content is overwritten when the toggle is enabled.
$remoteUpdateOptions ['update_local_from_remote'] = '1';
plugin_mastodon_save_options($remoteUpdateOptions);
$remoteUpdateState = plugin_mastodon_state_read();
$remoteUpdateImportOkEnabled = plugin_mastodon_sync_remote_to_local($remoteUpdateOptions, $remoteUpdateState);
$existingEntryAfterEnabled = entry_parse($existingRemoteEntryId);
$existingCommentAfterEnabled = comment_parse($existingRemoteEntryId, $existingRemoteCommentId);
$allOk = test_result(
	'Remote updates overwrite existing local content when the toggle is enabled',
	$remoteUpdateImportOkEnabled
		&& isset($existingEntryAfterEnabled ['content'])
		&& strpos((string) $existingEntryAfterEnabled ['content'], 'Remote changed entry body') !== false
		&& isset($existingCommentAfterEnabled ['content'])
		&& strpos((string) $existingCommentAfterEnabled ['content'], 'Remote changed comment body') !== false,
	json_encode(array(
		'entry' => isset($existingEntryAfterEnabled ['content']) ? (string) $existingEntryAfterEnabled ['content'] : '',
		'comment' => isset($existingCommentAfterEnabled ['content']) ? (string) $existingCommentAfterEnabled ['content'] : ''
	))
) && $allOk;

$singleImageRequest = array();
$galleryRequest = array();
foreach ($requestBodies as $parsed) {
	$statusText = isset($parsed ['status']) ? (string) $parsed ['status'] : '';
	if ($singleImageRequest === array() && strpos($statusText, 'Beitrag mit einem einzelnen Bild') !== false) {
		$singleImageRequest = $parsed;
	}
	if ($galleryRequest === array() && strpos($statusText, 'Beitrag mit einer Galerie') !== false) {
		$galleryRequest = $parsed;
	}
}

$allOk = test_result(
	'FlatPress single-image entry exports Mastodon media_ids and strips raw image BBCode',
	$singleImageRequest !== array()
		&& !empty($singleImageRequest ['media_ids'])
		&& count($singleImageRequest['media_ids']) === 1
		&& strpos(isset($singleImageRequest ['status']) ? (string) $singleImageRequest ['status'] : '', '[img=') === false
		&& strpos(isset($singleImageRequest ['__raw_body']) ? (string) $singleImageRequest ['__raw_body'] : '', 'media_ids%5B%5D=') !== false
		&& strpos(isset($singleImageRequest ['__raw_body']) ? (string) $singleImageRequest ['__raw_body'] : '', 'media_ids%5B0%5D=') === false,
	json_encode($singleImageRequest)
) && $allOk;

$allOk = test_result(
	'FlatPress gallery entry exports Mastodon media_ids up to the instance limit and strips raw gallery BBCode',
	$galleryRequest !== array()
		&& !empty($galleryRequest ['media_ids'])
		&& count($galleryRequest ['media_ids']) === 4
		&& strpos(isset($galleryRequest ['status']) ? (string) $galleryRequest ['status'] : '', '[gallery=') === false
		&& substr_count(isset($galleryRequest ['__raw_body']) ? (string) $galleryRequest ['__raw_body'] : '', 'media_ids%5B%5D=') === 4
		&& strpos(isset($galleryRequest ['__raw_body']) ? (string) $galleryRequest ['__raw_body'] : '', 'media_ids%5B0%5D=') === false,
	json_encode($galleryRequest)
) && $allOk;


if (!empty($oldSyncedEntryId) && function_exists('entry_delete') && entry_exists($oldSyncedEntryId)) {
	entry_delete($oldSyncedEntryId);
}
if (!empty($remoteThreadEntryId) && function_exists('entry_delete') && entry_exists($remoteThreadEntryId)) {
	entry_delete($remoteThreadEntryId);
}

// Sync start date integration tests
simulate_delete_recursive($simRoot . '/fp-content/plugin_mastodon');
mkdir($simRoot . '/fp-content/plugin_mastodon', 0777, true);
plugin_mastodon_state_write(plugin_mastodon_default_state());
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();

$startDateOptions = $options;
$startDateOptions ['sync_start_date'] = '2026-03-14';
$startDateOptions ['access_token'] = 'token-sync-start';

$oldExportEntry = array(
	'version' => system_ver(),
	'subject' => 'Older local export entry',
	'content' => 'This entry must stay unsynchronized because it is older than the configured start date.',
	'author' => 'Simulation',
	'date' => strtotime('2026-03-13 09:00:00 UTC')
);
$oldExportEntryId = entry_save($oldExportEntry, null);

$newExportEntry = array(
	'version' => system_ver(),
	'subject' => 'Newer local export entry',
	'content' => 'This entry should be exported because it is new enough.',
	'author' => 'Simulation',
	'date' => strtotime('2026-03-14 09:00:00 UTC')
);
$newExportEntryId = entry_save($newExportEntry, null);

$oldEntryComment = array(
	'version' => system_ver(),
	'name' => 'Old Local Comment',
	'content' => 'This comment is older than the configured start date.',
	'date' => strtotime('2026-03-13 08:30:00 UTC')
);
$oldEntryCommentId = comment_save($newExportEntryId, $oldEntryComment);

$newEntryComment = array(
	'version' => system_ver(),
	'name' => 'New Local Comment',
	'content' => 'This comment should be exported because it is new enough.',
	'date' => strtotime('2026-03-14 10:00:00 UTC')
);
$newEntryCommentId = comment_save($newExportEntryId, $newEntryComment);

$startDateState = plugin_mastodon_default_state();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $instanceUrl . '/api/v2/instance' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			'configuration' => array(
				'statuses' => array(
					'max_characters' => 500,
					'max_media_attachments' => 4
				),
				'media_attachments' => array(
					'description_limit' => 1500
				)
			)
		))
	),
	'POST ' . $instanceUrl . '/api/v1/statuses' => array(
		array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array(
				'id' => 'start-date-entry-1',
				'url' => $instanceUrl . '/@flatpress/start-date-entry-1',
				'created_at' => '2026-03-14T09:00:00Z'
			))
		),
		array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array(
				'id' => 'start-date-comment-1',
				'url' => $instanceUrl . '/@flatpress/start-date-comment-1',
				'created_at' => '2026-03-14T10:00:00Z'
			))
		)
	)
);
$startDateLocalOk = plugin_mastodon_sync_local_to_remote($startDateOptions, $startDateState);
$startDateRequests = array();
if (simulate_recorded_http_requests() !== array()) {
	foreach (simulate_recorded_http_requests() as $request) {
		if (!is_array($request) || empty($request ['url']) || strpos((string) $request ['url'], '/api/v1/statuses') === false || strtoupper((string) $request ['method']) !== 'POST') {
			continue;
		}
		$parsed = array();
		parse_str(isset($request ['body']) ? (string) $request ['body'] : '', $parsed);
		$startDateRequests [] = $parsed;
	}
}
$startDateEntryMeta = plugin_mastodon_state_get_entry_meta($startDateState, $newExportEntryId);
$startDateOldEntryMeta = plugin_mastodon_state_get_entry_meta($startDateState, $oldExportEntryId);
$startDateNewCommentMeta = plugin_mastodon_state_get_comment_meta($startDateState, $newExportEntryId, $newEntryCommentId);
$startDateOldCommentMeta = plugin_mastodon_state_get_comment_meta($startDateState, $newExportEntryId, $oldEntryCommentId);
$startDateExportedEntry = false;
$startDateExportedComment = false;
$startDateExportedOldEntry = false;
$startDateExportedOldComment = false;
foreach ($startDateRequests as $request) {
	$statusText = isset($request ['status']) ? (string) $request ['status'] : '';
	if (strpos($statusText, 'Newer local export entry') !== false) {
		$startDateExportedEntry = true;
	}
	if (strpos($statusText, 'This comment should be exported because it is new enough.') !== false) {
		$startDateExportedComment = true;
	}
	if (strpos($statusText, 'Older local export entry') !== false) {
		$startDateExportedOldEntry = true;
	}
	if (strpos($statusText, 'This comment is older than the configured start date.') !== false) {
		$startDateExportedOldComment = true;
	}
}
$allOk = test_result(
	'Sync start date filters local exports by entry and comment date',
	$startDateExportedEntry
		&& $startDateExportedComment
		&& !$startDateExportedOldEntry
		&& !$startDateExportedOldComment
		&& empty($startDateOldEntryMeta ['remote_id'])
		&& !empty($startDateEntryMeta ['remote_id'])
		&& empty($startDateOldCommentMeta ['remote_id'])
		&& !empty($startDateNewCommentMeta ['remote_id']),
	json_encode(array(
		'requests' => $startDateRequests,
		'entry_meta' => $startDateEntryMeta,
		'old_entry_meta' => $startDateOldEntryMeta,
		'new_comment_meta' => $startDateNewCommentMeta,
		'old_comment_meta' => $startDateOldCommentMeta
	))
) && $allOk;

simulate_delete_recursive($simRoot . '/fp-content/plugin_mastodon');
mkdir($simRoot . '/fp-content/plugin_mastodon', 0777, true);
plugin_mastodon_state_write(plugin_mastodon_default_state());
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $instanceUrl . '/api/v1/accounts/verify_credentials' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('id' => 'acct-sync-start', 'username' => 'flatpress'))
	),
	'GET ' . $instanceUrl . '/api/v1/accounts/acct-sync-start/statuses?limit=40&exclude_reblogs=true&exclude_replies=true' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			array(
				'id' => '700',
				'content' => '<p>Older imported root</p>',
				'account' => array('display_name' => 'Older Import', 'acct' => 'older@example.social'),
				'created_at' => '2026-03-13T09:00:00Z',
				'visibility' => 'public',
				'url' => $instanceUrl . '/@flatpress/700'
			),
			array(
				'id' => '701',
				'content' => '<p>Newer imported root</p>',
				'account' => array('display_name' => 'Newer Import', 'acct' => 'newer@example.social'),
				'created_at' => '2026-03-14T09:00:00Z',
				'visibility' => 'public',
				'url' => $instanceUrl . '/@flatpress/701'
			)
		))
	),
	'GET ' . $instanceUrl . '/api/v1/statuses/701/context' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			'ancestors' => array(),
			'descendants' => array(
				array(
					'id' => '702',
					'in_reply_to_id' => '701',
					'content' => '<p>Older imported reply</p>',
					'account' => array('display_name' => 'Older Reply', 'acct' => 'older-reply@example.social', 'url' => $instanceUrl . '/@older-reply'),
					'created_at' => '2026-03-13T10:00:00Z',
					'visibility' => 'public',
					'url' => $instanceUrl . '/@flatpress/702'
				),
				array(
					'id' => '703',
					'in_reply_to_id' => '701',
					'content' => '<p>Newer imported reply</p>',
					'account' => array('display_name' => 'Newer Reply', 'acct' => 'newer-reply@example.social', 'url' => $instanceUrl . '/@newer-reply'),
					'created_at' => '2026-03-14T10:00:00Z',
					'visibility' => 'public',
					'url' => $instanceUrl . '/@flatpress/703'
				)
			)
		))
	),
	'GET ' . $instanceUrl . '/api/v1/statuses/700/context' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('ancestors' => array(), 'descendants' => array()))
	)
);
$startDateImportState = plugin_mastodon_default_state();
$startDateRemoteOk = plugin_mastodon_sync_remote_to_local($startDateOptions, $startDateImportState);
$allOk = test_result(
	'Sync start date filters remote imports by status and reply date',
	$startDateRemoteOk
		&& !isset($startDateImportState ['entries_remote'] ['700'])
		&& isset($startDateImportState ['entries_remote'] ['701'])
		&& !isset($startDateImportState ['comments_remote'] ['702'])
		&& isset($startDateImportState ['comments_remote'] ['703']),
	json_encode(array(
		'entries_remote' => $startDateImportState ['entries_remote'],
		'comments_remote' => $startDateImportState ['comments_remote']
	))
) && $allOk;

// Regression test: remote imports near midnight must honor the configured FlatPress timeoffset.
simulate_delete_recursive($simRoot . '/fp-content/plugin_mastodon');
mkdir($simRoot . '/fp-content/plugin_mastodon', 0777, true);
plugin_mastodon_runtime_cache_clear();

$timeOffsetOptions = $seededOptions;
$timeOffsetOptions ['sync_start_date'] = '';
$timeOffsetOptions ['access_token'] = 'token123';
plugin_mastodon_save_options($timeOffsetOptions);

$configuredTimeOffsetHours = (int) plugin_mastodon_fp_config_value(array('locale', 'timeoffset'), 0);
$expectedBoundaryEntryTimestamp = plugin_mastodon_parse_iso_timestamp('2026-04-26T23:30:00Z') + ($configuredTimeOffsetHours * 3600);
$expectedBoundaryParentTimestamp = plugin_mastodon_parse_iso_timestamp('2026-04-26T23:45:00Z') + ($configuredTimeOffsetHours * 3600);
$expectedBoundaryChildTimestamp = plugin_mastodon_parse_iso_timestamp('2026-04-26T23:50:00Z') + ($configuredTimeOffsetHours * 3600);

$timeOffsetState = plugin_mastodon_default_state();
$boundaryRemoteEntry = array(
	'id' => 'timeoffset-root-900',
	'visibility' => 'public',
	'created_at' => '2026-04-26T23:30:00Z',
	'content' => '<p>Boundary remote entry imported close to midnight UTC.</p>',
	'url' => $instanceUrl . '/@flatpress/timeoffset-root-900',
	'account' => array(
		'id' => 'acct1',
		'acct' => 'flatpress@example.social',
		'display_name' => 'FlatPress',
		'url' => $instanceUrl . '/@flatpress'
	)
);
$timeOffsetEntryId = plugin_mastodon_import_remote_entry($timeOffsetOptions, $timeOffsetState, $boundaryRemoteEntry);
$timeOffsetEntry = is_string($timeOffsetEntryId) && $timeOffsetEntryId !== '' ? entry_parse($timeOffsetEntryId) : array();
plugin_mastodon_import_remote_context_descendants(
	$timeOffsetOptions,
	$timeOffsetState,
	is_string($timeOffsetEntryId) ? $timeOffsetEntryId : '',
	'timeoffset-root-900',
	array(
		'descendants' => array(
			array(
				'id' => 'timeoffset-reply-901',
				'in_reply_to_id' => 'timeoffset-root-900',
				'visibility' => 'public',
				'created_at' => '2026-04-26T23:45:00Z',
				'content' => '<p>Boundary remote reply imported close to midnight UTC.</p>',
				'url' => $instanceUrl . '/@flatpress/timeoffset-reply-901',
				'account' => array(
					'id' => 'acct-other',
					'acct' => 'alice@example.social',
					'display_name' => 'Alice Example',
					'url' => $instanceUrl . '/@alice'
				)
			),
			array(
				'id' => 'timeoffset-reply-902',
				'in_reply_to_id' => 'timeoffset-reply-901',
				'visibility' => 'public',
				'created_at' => '2026-04-26T23:50:00Z',
				'content' => '<p>Boundary nested remote reply imported close to midnight UTC.</p>',
				'url' => $instanceUrl . '/@flatpress/timeoffset-reply-902',
				'account' => array(
					'id' => 'acct-other-2',
					'acct' => 'bob@example.social',
					'display_name' => 'Bob Example',
					'url' => $instanceUrl . '/@bob'
				)
			)
		)
	)
);
$timeOffsetParentRef = isset($timeOffsetState ['comments_remote'] ['timeoffset-reply-901']) ? $timeOffsetState ['comments_remote'] ['timeoffset-reply-901'] : array();
$timeOffsetChildRef = isset($timeOffsetState ['comments_remote'] ['timeoffset-reply-902']) ? $timeOffsetState ['comments_remote'] ['timeoffset-reply-902'] : array();
$timeOffsetParentComment = (!empty($timeOffsetParentRef ['entry_id']) && !empty($timeOffsetParentRef ['comment_id'])) ? comment_parse($timeOffsetParentRef ['entry_id'], $timeOffsetParentRef ['comment_id']) : array();
$timeOffsetChildComment = (!empty($timeOffsetChildRef ['entry_id']) && !empty($timeOffsetChildRef ['comment_id'])) ? comment_parse($timeOffsetChildRef ['entry_id'], $timeOffsetChildRef ['comment_id']) : array();
$timeOffsetParentMeta = (!empty($timeOffsetParentRef ['entry_id']) && !empty($timeOffsetParentRef ['comment_id'])) ? plugin_mastodon_state_get_comment_meta($timeOffsetState, $timeOffsetParentRef ['entry_id'], $timeOffsetParentRef ['comment_id']) : array();
$timeOffsetChildMeta = (!empty($timeOffsetChildRef ['entry_id']) && !empty($timeOffsetChildRef ['comment_id'])) ? plugin_mastodon_state_get_comment_meta($timeOffsetState, $timeOffsetChildRef ['entry_id'], $timeOffsetChildRef ['comment_id']) : array();
$timeOffsetEntryMeta = is_string($timeOffsetEntryId) && $timeOffsetEntryId !== '' ? plugin_mastodon_state_get_entry_meta($timeOffsetState, $timeOffsetEntryId) : array();
$expectedBoundaryEntryId = bdb_idfromtime(BDB_ENTRY, $expectedBoundaryEntryTimestamp);
$expectedBoundaryParentCommentId = bdb_idfromtime(BDB_COMMENT, $expectedBoundaryParentTimestamp);
$expectedBoundaryChildCommentId = bdb_idfromtime(BDB_COMMENT, $expectedBoundaryChildTimestamp);
$allOk = test_result(
	'Remote Mastodon imports honor the FlatPress timeoffset for stored entry/comment dates and ordering keys',
	is_string($timeOffsetEntryId) && $timeOffsetEntryId === $expectedBoundaryEntryId
		&& isset($timeOffsetEntry ['date']) && (int) $timeOffsetEntry ['date'] === $expectedBoundaryEntryTimestamp
		&& !empty($timeOffsetEntryMeta ['remote_date_key']) && (string) $timeOffsetEntryMeta ['remote_date_key'] === '2026-04-27'
		&& !empty($timeOffsetParentRef ['comment_id']) && (string) $timeOffsetParentRef ['comment_id'] === $expectedBoundaryParentCommentId
		&& isset($timeOffsetParentComment ['date']) && (int) $timeOffsetParentComment ['date'] === $expectedBoundaryParentTimestamp
		&& !empty($timeOffsetParentMeta ['remote_date_key']) && (string) $timeOffsetParentMeta ['remote_date_key'] === '2026-04-27'
		&& !empty($timeOffsetChildRef ['comment_id']) && (string) $timeOffsetChildRef ['comment_id'] === $expectedBoundaryChildCommentId
		&& isset($timeOffsetChildComment ['date']) && (int) $timeOffsetChildComment ['date'] === $expectedBoundaryChildTimestamp
		&& isset($timeOffsetChildComment ['replyto']) && (string) $timeOffsetChildComment ['replyto'] === (string) $timeOffsetParentRef ['comment_id']
		&& !empty($timeOffsetChildMeta ['remote_date_key']) && (string) $timeOffsetChildMeta ['remote_date_key'] === '2026-04-27',
	json_encode(array(
		'configured_timeoffset_hours' => $configuredTimeOffsetHours,
		'entry_id' => $timeOffsetEntryId,
		'expected_entry_id' => $expectedBoundaryEntryId,
		'entry' => $timeOffsetEntry,
		'entry_meta' => $timeOffsetEntryMeta,
		'parent_ref' => $timeOffsetParentRef,
		'parent' => $timeOffsetParentComment,
		'parent_meta' => $timeOffsetParentMeta,
		'child_ref' => $timeOffsetChildRef,
		'child' => $timeOffsetChildComment,
		'child_meta' => $timeOffsetChildMeta
	))
) && $allOk;

simulate_delete_recursive($simRoot . '/fp-content/plugin_mastodon');
mkdir($simRoot . '/fp-content/plugin_mastodon', 0777, true);
plugin_mastodon_runtime_cache_clear();

$timeOffsetStartOptions = $seededOptions;
$timeOffsetStartOptions ['sync_start_date'] = '2026-04-27';
$timeOffsetStartOptions ['access_token'] = 'token123';
plugin_mastodon_save_options($timeOffsetStartOptions);
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $instanceUrl . '/api/v1/accounts/verify_credentials' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('id' => 'acct-timeoffset', 'username' => 'flatpress'))
	),
	'GET ' . $instanceUrl . '/api/v1/accounts/acct-timeoffset/statuses?limit=40&exclude_reblogs=true&exclude_replies=true' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			array(
				'id' => 'timeoffset-feed-910',
				'visibility' => 'public',
				'created_at' => '2026-04-26T23:30:00Z',
				'content' => '<p>Boundary remote entry picked up by sync start date in FlatPress local time.</p>',
				'url' => $instanceUrl . '/@flatpress/timeoffset-feed-910',
				'account' => array(
					'id' => 'acct-timeoffset',
					'acct' => 'flatpress@example.social',
					'display_name' => 'FlatPress',
					'url' => $instanceUrl . '/@flatpress'
				)
			)
		))
	),
	'GET ' . $instanceUrl . '/api/v1/statuses/timeoffset-feed-910/context' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('ancestors' => array(), 'descendants' => array()))
	)
);
$timeOffsetStartState = plugin_mastodon_default_state();
$timeOffsetStartOk = plugin_mastodon_sync_remote_to_local($timeOffsetStartOptions, $timeOffsetStartState);
$timeOffsetStartEntryId = isset($timeOffsetStartState ['entries_remote'] ['timeoffset-feed-910']) ? (string) $timeOffsetStartState ['entries_remote'] ['timeoffset-feed-910'] : '';
$timeOffsetStartEntryMeta = $timeOffsetStartEntryId !== '' ? plugin_mastodon_state_get_entry_meta($timeOffsetStartState, $timeOffsetStartEntryId) : array();
$allOk = test_result(
	'Remote sync start filtering respects the FlatPress timeoffset near midnight',
	$timeOffsetStartOk
		&& $timeOffsetStartEntryId !== ''
		&& !empty($timeOffsetStartEntryMeta ['remote_date_key']) && (string) $timeOffsetStartEntryMeta ['remote_date_key'] === '2026-04-27',
	json_encode(array(
		'entry_id' => $timeOffsetStartEntryId,
		'entry_meta' => $timeOffsetStartEntryMeta,
		'entries_remote' => isset($timeOffsetStartState ['entries_remote']) ? $timeOffsetStartState ['entries_remote'] : array()
	))
) && $allOk;

$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array();
plugin_mastodon_runtime_cache_clear();

$options = $seededOptions;
$options ['sync_start_date'] = '';
$options ['access_token'] = 'token123';
plugin_mastodon_save_options($options);

// Failure propagation simulation
simulate_delete_recursive($simRoot . '/fp-content/plugin_mastodon');
mkdir($simRoot . '/fp-content/plugin_mastodon', 0777, true);
plugin_mastodon_state_write(plugin_mastodon_default_state());
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $instanceUrl . '/api/v1/accounts/verify_credentials' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('id' => 'acct1', 'username' => 'flatpress'))
	),
	'GET ' . $instanceUrl . '/api/v1/accounts/acct1/statuses?limit=40&exclude_reblogs=true&exclude_replies=true' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array())
	),
	'GET ' . $instanceUrl . '/api/v2/instance' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			'configuration' => array(
				'statuses' => array(
					'max_characters' => 500,
					'max_media_attachments' => 4
				),
				'media_attachments' => array(
					'description_limit' => 1500
				)
			)
		))
	),
	'POST ' . $instanceUrl . '/api/v2/media' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('id' => 'media-failure-test', 'type' => 'image'))
	),
	'POST ' . $instanceUrl . '/api/v1/statuses' => array(
		'ok' => false,
		'code' => 422,
		'body' => json_encode(array('error' => 'Text Begrenzung von 500 Zeichen überschritten'))
	)
);
$failureResult = plugin_mastodon_run_sync(true);
$failureState = plugin_mastodon_state_read();
$allOk = test_result(
	'Sync reports local export failures instead of silently succeeding',
	!$failureResult ['ok'] && !empty($failureState ['last_error']) && strpos((string) $failureState ['last_error'], 'local_entry_export_failed:') === 0,
	isset($failureState ['last_error']) ? (string) $failureState ['last_error'] : ''
) && $allOk;

$syncTimeoutCalls = simulate_timeout_calls();
$syncHasRunBudget = false;
$syncHasHttpBudget = false;
foreach ($syncTimeoutCalls as $timeoutCall) {
	$minimumTimeout = isset($timeoutCall ['minimum']) ? (int) $timeoutCall ['minimum'] : 0;
	if ($minimumTimeout >= 180) {
		$syncHasRunBudget = true;
	}
	if ($minimumTimeout >= 60) {
		$syncHasHttpBudget = true;
	}
}
$allOk = test_result(
	'Shared-request synchronization refreshes the PHP execution budget for long Mastodon work',
	$syncHasRunBudget && $syncHasHttpBudget,
	json_encode($syncTimeoutCalls)
) && $allOk;

if (function_exists('ob_get_level') && ob_get_level() > 0) {
	ob_end_flush();
}

// Regression test: a remote status already mapped to a local FlatPress comment must not be imported as an entry by default.
simulate_delete_recursive($simRoot . '/fp-content/plugin_mastodon');
mkdir($simRoot . '/fp-content/plugin_mastodon', 0777, true);

$commentEntry = array(
	'version' => system_ver(),
	'subject' => 'Local entry for synchronized comment',
	'content' => 'This entry keeps the synchronized local comment.',
	'author' => 'Simulation',
	'date' => strtotime('2026-03-16 08:00:00 UTC')
);
$commentEntryId = entry_save($commentEntry, null);
$mappedComment = array(
	'version' => system_ver(),
	'name' => 'Local commenter',
	'content' => 'This local comment is already synchronized with Mastodon.',
	'date' => strtotime('2026-03-16 08:05:00 UTC')
);
$mappedCommentId = comment_save($commentEntryId, $mappedComment);

$options = $seededOptions;
$options ['sync_start_date'] = '';
$options ['update_local_from_remote'] = '0';
$options ['import_synced_comments_as_entries'] = '0';
$options ['access_token'] = 'token123';
plugin_mastodon_save_options($options);

$commentMappedState = plugin_mastodon_default_state();
plugin_mastodon_state_set_comment_mapping(
	$commentMappedState,
	$commentEntryId,
	$mappedCommentId,
	'comment-status-2001',
	'local',
	plugin_mastodon_comment_hash(comment_parse($commentEntryId, $mappedCommentId)),
	$instanceUrl . '/@flatpress/comment-status-2001',
	'2026-03-16 08:05:00',
	'',
	'remote-entry-for-comment-root'
);
plugin_mastodon_state_write($commentMappedState);

$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $instanceUrl . '/api/v1/accounts/verify_credentials' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('id' => 'acct1', 'username' => 'flatpress'))
	),
	'GET ' . $instanceUrl . '/api/v1/accounts/acct1/statuses?limit=40&exclude_reblogs=true&exclude_replies=true' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			array(
				'id' => 'comment-status-2001',
				'visibility' => 'public',
				'created_at' => '2026-03-16T09:00:00Z',
				'content' => '<p>This top-level Mastodon status must not become a duplicate FlatPress entry while the toggle is disabled.</p>',
				'url' => $instanceUrl . '/@flatpress/comment-status-2001',
				'account' => array(
					'id' => 'acct1',
					'acct' => 'flatpress@example.social',
					'display_name' => 'FlatPress',
					'url' => $instanceUrl . '/@flatpress'
				)
			)
		))
	)
);

$commentMappedState = plugin_mastodon_state_read();
$skipDuplicateImportOk = plugin_mastodon_sync_remote_to_local($options, $commentMappedState);
$duplicateImportedEntryId = isset($commentMappedState ['entries_remote'] ['comment-status-2001']) ? (string) $commentMappedState ['entries_remote'] ['comment-status-2001'] : '';
$allOk = test_result(
	'Synchronized local comments are not imported as duplicate entries from Mastodon by default',
	$skipDuplicateImportOk
		&& $duplicateImportedEntryId === ''
		&& isset($commentMappedState ['comments_remote'] ['comment-status-2001'] ['comment_id'])
		&& (string) $commentMappedState ['comments_remote'] ['comment-status-2001'] ['comment_id'] === $mappedCommentId,
	json_encode(array(
		'duplicate_entry_id' => $duplicateImportedEntryId,
		'comment_ref' => isset($commentMappedState ['comments_remote'] ['comment-status-2001']) ? $commentMappedState ['comments_remote'] ['comment-status-2001'] : array()
	))
) && $allOk;

// Regression test: the same remote status may be imported as an entry when the administrator enables the toggle.
simulate_delete_recursive($simRoot . '/fp-content/plugin_mastodon');
mkdir($simRoot . '/fp-content/plugin_mastodon', 0777, true);
plugin_mastodon_state_write(plugin_mastodon_default_state());

$toggleEntry = array(
	'version' => system_ver(),
	'subject' => 'Local entry for optional duplicate import',
	'content' => 'Local entry used to keep the already synchronized comment mapping.',
	'author' => 'Simulation',
	'date' => strtotime('2026-03-16 10:00:00 UTC')
);
$toggleEntryId = entry_save($toggleEntry, null);
$toggleComment = array(
	'version' => system_ver(),
	'name' => 'Toggle commenter',
	'content' => 'This local comment intentionally shares a remote status with an optional entry import.',
	'date' => strtotime('2026-03-16 10:05:00 UTC')
);
$toggleCommentId = comment_save($toggleEntryId, $toggleComment);

$options = $seededOptions;
$options ['sync_start_date'] = '';
$options ['update_local_from_remote'] = '0';
$options ['import_synced_comments_as_entries'] = '1';
$options ['access_token'] = 'token123';
plugin_mastodon_save_options($options);

$toggleState = plugin_mastodon_default_state();
plugin_mastodon_state_set_comment_mapping(
	$toggleState,
	$toggleEntryId,
	$toggleCommentId,
	'comment-status-2002',
	'local',
	plugin_mastodon_comment_hash(comment_parse($toggleEntryId, $toggleCommentId)),
	$instanceUrl . '/@flatpress/comment-status-2002',
	'2026-03-16 10:05:00',
	'',
	'remote-entry-for-comment-root'
);
plugin_mastodon_state_write($toggleState);

$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $instanceUrl . '/api/v1/accounts/verify_credentials' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('id' => 'acct1', 'username' => 'flatpress'))
	),
	'GET ' . $instanceUrl . '/api/v1/accounts/acct1/statuses?limit=40&exclude_reblogs=true&exclude_replies=true' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			array(
				'id' => 'comment-status-2002',
				'visibility' => 'public',
				'created_at' => '2026-03-16T11:00:00Z',
				'content' => '<p>This status may also be imported as an entry when the administrator enables the toggle.</p>',
				'url' => $instanceUrl . '/@flatpress/comment-status-2002',
				'account' => array(
					'id' => 'acct1',
					'acct' => 'flatpress@example.social',
					'display_name' => 'FlatPress',
					'url' => $instanceUrl . '/@flatpress'
				)
			)
		))
	),
	'GET ' . $instanceUrl . '/api/v1/statuses/comment-status-2002/context' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('descendants' => array()))
	)
);

$toggleState = plugin_mastodon_state_read();
$allowDuplicateImportOk = plugin_mastodon_sync_remote_to_local($options, $toggleState);
$optionalDuplicateEntryId = isset($toggleState ['entries_remote'] ['comment-status-2002']) ? (string) $toggleState ['entries_remote'] ['comment-status-2002'] : '';
$optionalDuplicateEntry = $optionalDuplicateEntryId !== '' ? entry_parse($optionalDuplicateEntryId) : array();
$allOk = test_result(
	'Synchronized local comments may be imported as entries when the toggle is enabled',
	$allowDuplicateImportOk
		&& $optionalDuplicateEntryId !== ''
		&& isset($optionalDuplicateEntry ['content'])
		&& strpos((string) $optionalDuplicateEntry ['content'], 'This status may also be imported as an entry') !== false,
	json_encode(array(
		'duplicate_entry_id' => $optionalDuplicateEntryId,
		'entry' => $optionalDuplicateEntry
	))
) && $allOk;

// Regression test: local FlatPress comments and nested replies on an imported Mastodon entry must sync back to Mastodon.
simulate_delete_recursive($simRoot . '/fp-content/plugin_mastodon');
mkdir($simRoot . '/fp-content/plugin_mastodon', 0777, true);
plugin_mastodon_runtime_cache_clear();

$importedEntryCommentOptions = $seededOptions;
$importedEntryCommentOptions ['sync_start_date'] = '2032-02-01';
$importedEntryCommentOptions ['update_local_from_remote'] = '0';
$importedEntryCommentOptions ['import_synced_comments_as_entries'] = '0';
$importedEntryCommentOptions ['access_token'] = 'token123';
plugin_mastodon_save_options($importedEntryCommentOptions);

$importedEntryCommentState = plugin_mastodon_default_state();
$importedEntryRemoteStatus = array(
	'id' => 'remote-entry-3000',
	'visibility' => 'public',
	'created_at' => '2032-02-10T09:00:00Z',
	'content' => '<p>Imported Mastodon entry that receives local FlatPress comments.</p>',
	'url' => $instanceUrl . '/@flatpress/remote-entry-3000',
	'account' => array(
		'id' => 'acct1',
		'acct' => 'flatpress@example.social',
		'display_name' => 'FlatPress',
		'url' => $instanceUrl . '/@flatpress'
	)
);
$importedEntryCommentEntryId = plugin_mastodon_import_remote_entry($importedEntryCommentOptions, $importedEntryCommentState, $importedEntryRemoteStatus);
$importedEntryCommentEntry = is_string($importedEntryCommentEntryId) && $importedEntryCommentEntryId !== '' ? entry_parse($importedEntryCommentEntryId) : array();

$importedEntryCommentOne = array(
	'version' => system_ver(),
	'name' => 'Comment Tester One',
	'content' => 'First local FlatPress comment on imported Mastodon entry.',
	'date' => strtotime('2032-02-10 09:05:00 UTC')
);
$importedEntryCommentTwo = array(
	'version' => system_ver(),
	'name' => 'Comment Tester Two',
	'content' => 'Second local FlatPress comment on imported Mastodon entry.',
	'date' => strtotime('2032-02-10 09:06:00 UTC')
);
$importedEntryCommentThree = array(
	'version' => system_ver(),
	'name' => 'Nested Reply Tester',
	'content' => 'Nested reply to the first local FlatPress comment.',
	'date' => strtotime('2032-02-10 09:07:00 UTC')
);

$importedEntryCommentOneId = is_string($importedEntryCommentEntryId) ? comment_save($importedEntryCommentEntryId, $importedEntryCommentOne) : false;
$importedEntryCommentTwoId = is_string($importedEntryCommentEntryId) ? comment_save($importedEntryCommentEntryId, $importedEntryCommentTwo) : false;
$importedEntryCommentThree ['replyto'] = is_string($importedEntryCommentOneId) ? $importedEntryCommentOneId : '';
$importedEntryCommentThreeId = is_string($importedEntryCommentEntryId) ? comment_save($importedEntryCommentEntryId, $importedEntryCommentThree) : false;

$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $instanceUrl . '/api/v2/instance' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			'configuration' => array(
				'statuses' => array(
					'max_characters' => 500,
					'max_media_attachments' => 4,
					'characters_reserved_per_url' => 23
				),
				'media_attachments' => array(
					'description_limit' => 1500
				)
			)
		))
	),
	'POST ' . $instanceUrl . '/api/v1/statuses' => array(
		array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array(
				'id' => 'remote-comment-3001',
				'created_at' => '2032-02-10T09:05:00Z',
				'url' => $instanceUrl . '/@flatpress/remote-comment-3001'
			))
		),
		array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array(
				'id' => 'remote-comment-3002',
				'created_at' => '2032-02-10T09:06:00Z',
				'url' => $instanceUrl . '/@flatpress/remote-comment-3002'
			))
		),
		array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array(
				'id' => 'remote-comment-3003',
				'created_at' => '2032-02-10T09:07:00Z',
				'url' => $instanceUrl . '/@flatpress/remote-comment-3003'
			))
		)
	)
);

$importedEntryCommentSyncOk = is_string($importedEntryCommentEntryId) ? plugin_mastodon_sync_local_to_remote($importedEntryCommentOptions, $importedEntryCommentState) : false;
$importedEntryCommentRequests = simulate_recorded_http_requests();
$importedEntryCommentPostBodies = array();
foreach ($importedEntryCommentRequests as $request) {
	if (empty($request ['url']) || (string) $request ['url'] !== $instanceUrl . '/api/v1/statuses') {
		continue;
	}
	$decoded = json_decode(isset($request ['body']) ? (string) $request ['body'] : '', true);
	if (!is_array($decoded)) {
		$decoded = array();
		parse_str(isset($request ['body']) ? (string) $request ['body'] : '', $decoded);
	}
	$importedEntryCommentPostBodies [] = $decoded;
}
$importedEntryCommentOneMeta = is_string($importedEntryCommentOneId) ? plugin_mastodon_state_get_comment_meta($importedEntryCommentState, $importedEntryCommentEntryId, $importedEntryCommentOneId) : array();
$importedEntryCommentTwoMeta = is_string($importedEntryCommentTwoId) ? plugin_mastodon_state_get_comment_meta($importedEntryCommentState, $importedEntryCommentEntryId, $importedEntryCommentTwoId) : array();
$importedEntryCommentThreeMeta = is_string($importedEntryCommentThreeId) ? plugin_mastodon_state_get_comment_meta($importedEntryCommentState, $importedEntryCommentEntryId, $importedEntryCommentThreeId) : array();

$allOk = test_result(
	'Local FlatPress comments on imported Mastodon entries sync back to Mastodon, including nested replies',
	$importedEntryCommentSyncOk
		&& is_string($importedEntryCommentEntryId) && $importedEntryCommentEntryId !== ''
		&& is_array($importedEntryCommentEntry) && isset($importedEntryCommentEntry ['subject'])
		&& isset($importedEntryCommentState ['content_stats'] ['exported_comments']) && (int) $importedEntryCommentState ['content_stats'] ['exported_comments'] === 3
		&& count($importedEntryCommentPostBodies) === 3
		&& !empty($importedEntryCommentOneMeta ['remote_id']) && (string) $importedEntryCommentOneMeta ['remote_id'] === 'remote-comment-3001'
		&& !empty($importedEntryCommentTwoMeta ['remote_id']) && (string) $importedEntryCommentTwoMeta ['remote_id'] === 'remote-comment-3002'
		&& !empty($importedEntryCommentThreeMeta ['remote_id']) && (string) $importedEntryCommentThreeMeta ['remote_id'] === 'remote-comment-3003'
		&& isset($importedEntryCommentPostBodies [0] ['in_reply_to_id']) && (string) $importedEntryCommentPostBodies [0] ['in_reply_to_id'] === 'remote-entry-3000'
		&& isset($importedEntryCommentPostBodies [1] ['in_reply_to_id']) && (string) $importedEntryCommentPostBodies [1] ['in_reply_to_id'] === 'remote-entry-3000'
		&& isset($importedEntryCommentPostBodies [2] ['in_reply_to_id']) && (string) $importedEntryCommentPostBodies [2] ['in_reply_to_id'] === 'remote-comment-3001',
	json_encode(array(
		'entry_id' => $importedEntryCommentEntryId,
		'content_stats' => isset($importedEntryCommentState ['content_stats']) ? $importedEntryCommentState ['content_stats'] : array(),
		'post_bodies' => $importedEntryCommentPostBodies,
		'comment_meta' => array(
			'comment1' => $importedEntryCommentOneMeta,
			'comment2' => $importedEntryCommentTwoMeta,
			'comment3' => $importedEntryCommentThreeMeta
		)
	))
) && $allOk;

$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_timeout_calls'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'POST ' . $instanceUrl . '/api/v2/media' => array(
		'ok' => true,
		'code' => 202,
		'body' => json_encode(array(
			'id' => 'media-async-900',
			'preview_url' => $instanceUrl . '/media/preview/media-async-900.jpg',
			'url' => null
		))
	),
	'GET ' . $instanceUrl . '/api/v1/media/media-async-900' => array(
		array(
			'ok' => true,
			'code' => 206,
			'body' => json_encode(array(
				'id' => 'media-async-900',
				'preview_url' => $instanceUrl . '/media/preview/media-async-900.jpg',
				'url' => null
			))
		),
		array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array(
				'id' => 'media-async-900',
				'preview_url' => $instanceUrl . '/media/preview/media-async-900.jpg',
				'url' => $instanceUrl . '/media/original/media-async-900.jpg'
			))
		)
	)
);
$asyncUpload = plugin_mastodon_upload_media_items(
	$options,
	array(
		array(
			'absolute_path' => ABS_PATH . FP_CONTENT . 'images/mastodon-sim/single-image.jpg',
			'description' => 'Asynchronously processed media upload fixture'
		)
	),
	1
);
$asyncStatusChecks = 0;
if (simulate_recorded_http_requests() !== array()) {
	foreach (simulate_recorded_http_requests() as $request) {
		if (!empty($request ['url']) && (string) $request ['url'] === $instanceUrl . '/api/v1/media/media-async-900') {
			$asyncStatusChecks++;
		}
	}
}
$allOk = test_result(
	'Asynchronous Mastodon media uploads are polled until the attachment is ready',
	$asyncUpload ['ok']
		&& !empty($asyncUpload ['media_ids'])
		&& (string) $asyncUpload ['media_ids'] [0] === 'media-async-900'
		&& $asyncStatusChecks === 2,
	json_encode(array(
		'upload' => $asyncUpload,
		'status_checks' => $asyncStatusChecks
	))
) && $allOk;

$asyncTimeoutCalls = simulate_timeout_calls();
$asyncMultipartBudget = false;
$asyncPollingBudgetCount = 0;
foreach ($asyncTimeoutCalls as $timeoutCall) {
	$minimumTimeout = isset($timeoutCall ['minimum']) ? (int) $timeoutCall ['minimum'] : 0;
	if ($minimumTimeout >= 120) {
		$asyncMultipartBudget = true;
	}
	if ($minimumTimeout === 60) {
		$asyncPollingBudgetCount++;
	}
}
$allOk = test_result(
	'Long-running Mastodon media uploads refresh the PHP execution budget for upload and polling',
	$asyncMultipartBudget && $asyncPollingBudgetCount >= 2,
	json_encode(array(
		'timeout_calls' => $asyncTimeoutCalls,
		'polling_budget_calls' => $asyncPollingBudgetCount
	))
) && $allOk;

$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_timeout_calls'] = array();
$videoPollResponses = array();
for ($i = 0; $i < 10; $i++) {
	$videoPollResponses [] = array(
		'ok' => true,
		'code' => 206,
		'headers' => array('retry-after' => '1'),
		'body' => json_encode(array(
			'id' => 'media-slow-video-901',
			'type' => 'video',
			'preview_url' => $instanceUrl . '/media/preview/media-slow-video-901.jpg',
			'url' => null
		))
	);
}
$videoPollResponses [] = array(
	'ok' => true,
	'code' => 200,
	'body' => json_encode(array(
		'id' => 'media-slow-video-901',
		'type' => 'video',
		'preview_url' => $instanceUrl . '/media/preview/media-slow-video-901.jpg',
		'url' => $instanceUrl . '/media/original/media-slow-video-901.mp4'
	))
);
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'POST ' . $instanceUrl . '/api/v2/media' => array(
		'ok' => true,
		'code' => 202,
		'body' => json_encode(array(
			'id' => 'media-slow-video-901',
			'type' => 'video',
			'preview_url' => $instanceUrl . '/media/preview/media-slow-video-901.jpg',
			'url' => null
		))
	),
	'GET ' . $instanceUrl . '/api/v1/media/media-slow-video-901' => $videoPollResponses
);
$slowVideoUpload = plugin_mastodon_upload_media_items(
	$options,
	array(
		array(
			'absolute_path' => ABS_PATH . FP_CONTENT . 'attachs/mastodon-sim/demo-video.mp4',
			'mime_type' => 'video/mp4',
			'media_type' => 'video',
			'description' => 'Slow video processing fixture'
		)
	),
	1
);
$slowVideoPolls = 0;
$slowVideoUploadTimeoutBudget = false;
foreach (simulate_recorded_http_requests() as $request) {
	if (!empty($request ['url']) && (string) $request ['url'] === $instanceUrl . '/api/v1/media/media-slow-video-901') {
		$slowVideoPolls++;
	}
}
foreach (simulate_timeout_calls() as $timeoutCall) {
	if (!empty($timeoutCall ['minimum']) && (int) $timeoutCall ['minimum'] >= 120) {
		$slowVideoUploadTimeoutBudget = true;
	}
}
$allOk = test_result(
	'Slow asynchronous Mastodon video uploads keep polling beyond the old short retry window',
	!empty($slowVideoUpload ['ok'])
		&& !empty($slowVideoUpload ['media_ids'] [0])
		&& (string) $slowVideoUpload ['media_ids'] [0] === 'media-slow-video-901'
		&& $slowVideoPolls === 11
		&& $slowVideoUploadTimeoutBudget,
	json_encode(array(
		'upload' => $slowVideoUpload,
		'polls' => $slowVideoPolls,
		'timeout_calls' => simulate_timeout_calls()
	))
) && $allOk;

$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$audioPollResponses = array();
for ($i = 0; $i < 9; $i++) {
	$audioPollResponses [] = array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			'id' => 'media-slow-audio-902',
			'type' => 'audio',
			'url' => null
		))
	);
}
$audioPollResponses [] = array(
	'ok' => true,
	'code' => 200,
	'body' => json_encode(array(
		'id' => 'media-slow-audio-902',
		'type' => 'audio',
		'url' => $instanceUrl . '/media/original/media-slow-audio-902.mp3'
	))
);
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'POST ' . $instanceUrl . '/api/v2/media' => array(
		'ok' => true,
		'code' => 202,
		'body' => json_encode(array(
			'id' => 'media-slow-audio-902',
			'type' => 'audio',
			'url' => null
		))
	),
	'GET ' . $instanceUrl . '/api/v1/media/media-slow-audio-902' => $audioPollResponses
);
$slowAudioUpload = plugin_mastodon_upload_media_items(
	$options,
	array(
		array(
			'absolute_path' => ABS_PATH . FP_CONTENT . 'attachs/mastodon-sim/demo-audio.mp3',
			'mime_type' => 'audio/mpeg',
			'media_type' => 'audio',
			'description' => 'Slow audio processing fixture'
		)
	),
	1
);
$slowAudioPolls = 0;
foreach (simulate_recorded_http_requests() as $request) {
	if (!empty($request ['url']) && (string) $request ['url'] === $instanceUrl . '/api/v1/media/media-slow-audio-902') {
		$slowAudioPolls++;
	}
}
$allOk = test_result(
	'Mastodon audio uploads keep polling even when pending responses have no preview_url',
	!empty($slowAudioUpload ['ok'])
		&& !empty($slowAudioUpload ['media_ids'] [0])
		&& (string) $slowAudioUpload ['media_ids'] [0] === 'media-slow-audio-902'
		&& $slowAudioPolls === 10,
	json_encode(array('upload' => $slowAudioUpload, 'polls' => $slowAudioPolls))
) && $allOk;

// Deletion synchronization: local FlatPress deletions must be propagated to Mastodon in a follow-up request.
simulate_delete_recursive($simRoot . '/fp-content/plugin_mastodon');
mkdir($simRoot . '/fp-content/plugin_mastodon', 0777, true);
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array();
$deleteLocalOptions = $options;
plugin_mastodon_save_options($deleteLocalOptions);
$deleteLocalEntry = array(
	'version' => system_ver(),
	'subject' => 'Delete local entry remotely',
	'content' => 'This FlatPress entry will be deleted before the deletion sync request.',
	'author' => 'Simulation',
	'date' => strtotime('2026-03-24 09:00:00 UTC')
);
$deleteLocalEntryId = entry_save($deleteLocalEntry, null);
$deleteLocalComment = array(
	'version' => system_ver(),
	'name' => 'Simulation',
	'content' => 'This FlatPress comment will be deleted before the deletion sync request.',
	'date' => strtotime('2026-03-24 09:05:00 UTC')
);
$deleteLocalCommentId = comment_save($deleteLocalEntryId, $deleteLocalComment);
$deleteLocalState = plugin_mastodon_default_state();
$deleteLocalState ['last_run'] = '2026-03-24 08:50:00';
$deleteLocalState ['deletions_pending'] = 1;
$deleteLocalState ['content_stats'] ['imported_entries'] = 9;
$deleteLocalState ['content_stats'] ['exported_entries'] = 8;
$deleteLocalState ['content_stats'] ['imported_comments'] = 7;
plugin_mastodon_state_set_entry_mapping($deleteLocalState, $deleteLocalEntryId, '970', 'local', plugin_mastodon_entry_hash(entry_parse($deleteLocalEntryId)), $instanceUrl . '/@flatpress/970', '2026-03-24 09:00:00');
$deleteLocalParsedComment = comment_parse($deleteLocalEntryId, $deleteLocalCommentId);
plugin_mastodon_state_set_comment_mapping($deleteLocalState, $deleteLocalEntryId, $deleteLocalCommentId, '971', 'local', plugin_mastodon_comment_hash($deleteLocalParsedComment), $instanceUrl . '/@flatpress/971', '2026-03-24 09:05:00');
plugin_mastodon_state_write($deleteLocalState);
entry_delete($deleteLocalEntryId);
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'DELETE ' . $instanceUrl . '/api/v1/statuses/970?delete_media=1' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('id' => '970'))
	),
	'DELETE ' . $instanceUrl . '/api/v1/statuses/971?delete_media=1' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('id' => '971'))
	)
);
simulate_allow_pending_deletion_sync();
$deleteLocalResult = plugin_mastodon_run_deletion_sync(true);
$deleteLocalStateAfter = plugin_mastodon_state_read();
$deleteLocalDeleteRequests = 0;
if (simulate_recorded_http_requests() !== array()) {
	foreach (simulate_recorded_http_requests() as $request) {
		if (is_array($request) && !empty($request ['method']) && strtoupper((string) $request ['method']) === 'DELETE') {
			$deleteLocalDeleteRequests++;
		}
	}
}
$allOk = test_result(
	'Follow-up deletion sync removes remote Mastodon content after local FlatPress deletion',
	$deleteLocalResult ['ok']
		&& $deleteLocalDeleteRequests === 2
		&& empty($deleteLocalStateAfter ['entries'])
		&& empty($deleteLocalStateAfter ['comments'])
		&& !empty($deleteLocalStateAfter ['last_deletion_run'])
		&& empty($deleteLocalStateAfter ['deletions_pending'])
		&& isset($deleteLocalStateAfter ['content_stats'] ['imported_entries'])
		&& (int) $deleteLocalStateAfter ['content_stats'] ['imported_entries'] === 9
		&& isset($deleteLocalStateAfter ['content_stats'] ['exported_entries'])
		&& (int) $deleteLocalStateAfter ['content_stats'] ['exported_entries'] === 8
		&& isset($deleteLocalStateAfter ['content_stats'] ['imported_comments'])
		&& (int) $deleteLocalStateAfter ['content_stats'] ['imported_comments'] === 7
		&& isset($deleteLocalStateAfter ['deletion_stats'] ['deleted_remote_entries'])
		&& (int) $deleteLocalStateAfter ['deletion_stats'] ['deleted_remote_entries'] === 1
		&& isset($deleteLocalStateAfter ['deletion_stats'] ['deleted_remote_comments'])
		&& (int) $deleteLocalStateAfter ['deletion_stats'] ['deleted_remote_comments'] === 1,
	json_encode(array(
		'result' => $deleteLocalResult,
		'state' => $deleteLocalStateAfter,
		'delete_requests' => $deleteLocalDeleteRequests
	))
) && $allOk;

$allOk = test_result(
	'Follow-up deletion sync updates last_deletion_run without overwriting the last synchronization timestamp or content counters',
	$deleteLocalResult ['ok']
		&& isset($deleteLocalStateAfter ['last_run']) && $deleteLocalStateAfter ['last_run'] === '2026-03-24 08:50:00'
		&& !empty($deleteLocalStateAfter ['last_deletion_run'])
		&& $deleteLocalStateAfter ['last_deletion_run'] !== $deleteLocalStateAfter ['last_run']
		&& isset($deleteLocalStateAfter ['content_stats'] ['imported_entries']) && (int) $deleteLocalStateAfter ['content_stats'] ['imported_entries'] === 9
		&& isset($deleteLocalStateAfter ['content_stats'] ['exported_entries']) && (int) $deleteLocalStateAfter ['content_stats'] ['exported_entries'] === 8
		&& isset($deleteLocalStateAfter ['content_stats'] ['imported_comments']) && (int) $deleteLocalStateAfter ['content_stats'] ['imported_comments'] === 7,
	json_encode(array(
		'last_run' => isset($deleteLocalStateAfter ['last_run']) ? $deleteLocalStateAfter ['last_run'] : '',
		'last_deletion_run' => isset($deleteLocalStateAfter ['last_deletion_run']) ? $deleteLocalStateAfter ['last_deletion_run'] : '',
		'content_stats' => isset($deleteLocalStateAfter ['content_stats']) ? $deleteLocalStateAfter ['content_stats'] : array(),
		'deletion_stats' => isset($deleteLocalStateAfter ['deletion_stats']) ? $deleteLocalStateAfter ['deletion_stats'] : array()
	))
) && $allOk;

simulate_delete_recursive($simRoot . '/fp-content/plugin_mastodon');
mkdir($simRoot . '/fp-content/plugin_mastodon', 0777, true);
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array();
$deleteLegacyFallbackOptions = $options;
$deleteLegacyFallbackOptions ['instance_url'] = 'https://legacy-fallback.example';
$deleteLegacyFallbackOptions ['instance_info_url'] = '';
$deleteLegacyFallbackOptions ['instance_info_json'] = '';
plugin_mastodon_save_options($deleteLegacyFallbackOptions);
$deleteLegacyFallbackEntry = array(
	'version' => system_ver(),
	'subject' => 'Delete local entry with legacy Mastodon fallback',
	'content' => 'This FlatPress entry verifies the delete_media fallback in deletion synchronization.',
	'author' => 'Simulation',
	'date' => strtotime('2026-03-24 09:30:00 UTC')
);
$deleteLegacyFallbackEntryId = entry_save($deleteLegacyFallbackEntry, null);
$deleteLegacyFallbackState = plugin_mastodon_default_state();
$deleteLegacyFallbackState ['last_run'] = '2026-03-24 09:20:00';
$deleteLegacyFallbackState ['deletions_pending'] = 1;
plugin_mastodon_state_set_entry_mapping($deleteLegacyFallbackState, $deleteLegacyFallbackEntryId, '9725', 'local', plugin_mastodon_entry_hash(entry_parse($deleteLegacyFallbackEntryId)), 'https://legacy-fallback.example/@flatpress/9725', '2026-03-24 09:30:00');
plugin_mastodon_state_write($deleteLegacyFallbackState);
entry_delete($deleteLegacyFallbackEntryId);
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'DELETE https://legacy-fallback.example/api/v1/statuses/9725?delete_media=1' => array(
		'ok' => false,
		'code' => 422,
		'body' => json_encode(array('error' => 'unknown parameter: delete_media'))
	),
	'DELETE https://legacy-fallback.example/api/v1/statuses/9725' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('id' => '9725'))
	)
);
simulate_allow_pending_deletion_sync();
$deleteLegacyFallbackResult = plugin_mastodon_run_deletion_sync(true);
$deleteLegacyFallbackStateAfter = plugin_mastodon_state_read();
$deleteLegacyFallbackRequests = simulate_recorded_http_requests();
$allOk = test_result(
	'Deletion sync falls back to DELETE without delete_media for older Mastodon servers',
	$deleteLegacyFallbackResult ['ok']
		&& count($deleteLegacyFallbackRequests) === 2
		&& isset($deleteLegacyFallbackRequests [0] ['url']) && (string) $deleteLegacyFallbackRequests [0] ['url'] === 'https://legacy-fallback.example/api/v1/statuses/9725?delete_media=1'
		&& isset($deleteLegacyFallbackRequests [1] ['url']) && (string) $deleteLegacyFallbackRequests [1] ['url'] === 'https://legacy-fallback.example/api/v1/statuses/9725'
		&& empty($deleteLegacyFallbackStateAfter ['entries'])
		&& !empty($deleteLegacyFallbackStateAfter ['last_deletion_run'])
		&& empty($deleteLegacyFallbackStateAfter ['deletions_pending'])
		&& isset($deleteLegacyFallbackStateAfter ['deletion_stats'] ['deleted_remote_entries'])
		&& (int) $deleteLegacyFallbackStateAfter ['deletion_stats'] ['deleted_remote_entries'] === 1,
	json_encode(array(
		'result' => $deleteLegacyFallbackResult,
		'state' => $deleteLegacyFallbackStateAfter,
		'requests' => $deleteLegacyFallbackRequests
	))
) && $allOk;

// Deletion synchronization: remote Mastodon deletions must remove the mapped FlatPress content in the follow-up request.
simulate_delete_recursive($simRoot . '/fp-content/plugin_mastodon');
mkdir($simRoot . '/fp-content/plugin_mastodon', 0777, true);
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array();
$deleteRemoteOptions = $options;
plugin_mastodon_save_options($deleteRemoteOptions);
$deleteRemoteEntry = array(
	'version' => system_ver(),
	'subject' => 'Delete remote entry locally',
	'content' => 'This mirrored local entry should disappear after the follow-up deletion request.',
	'author' => 'Mastodon',
	'date' => strtotime('2026-03-24 10:00:00 UTC')
);
$deleteRemoteEntryId = entry_save($deleteRemoteEntry, null);
$deleteRemoteComment = array(
	'version' => system_ver(),
	'name' => '@flatpress@example.social',
	'content' => 'This mirrored local comment should disappear after the follow-up deletion request.',
	'date' => strtotime('2026-03-24 10:05:00 UTC')
);
$deleteRemoteCommentId = comment_save($deleteRemoteEntryId, $deleteRemoteComment);
$deleteRemoteState = plugin_mastodon_default_state();
$deleteRemoteState ['deletions_pending'] = 1;
plugin_mastodon_state_set_entry_mapping($deleteRemoteState, $deleteRemoteEntryId, '980', 'remote', plugin_mastodon_entry_hash(entry_parse($deleteRemoteEntryId)), $instanceUrl . '/@flatpress/980', '2026-03-24 10:00:00');
$deleteRemoteParsedComment = comment_parse($deleteRemoteEntryId, $deleteRemoteCommentId);
plugin_mastodon_state_set_comment_mapping($deleteRemoteState, $deleteRemoteEntryId, $deleteRemoteCommentId, '981', 'remote', plugin_mastodon_comment_hash($deleteRemoteParsedComment), $instanceUrl . '/@flatpress/981', '2026-03-24 10:05:00');
plugin_mastodon_state_write($deleteRemoteState);
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $instanceUrl . '/api/v1/statuses/980' => array(
		'ok' => false,
		'code' => 404,
		'body' => json_encode(array('error' => 'Record not found'))
	),
	'DELETE ' . $instanceUrl . '/api/v1/statuses/981?delete_media=1' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('id' => '981'))
	)
);
simulate_allow_pending_deletion_sync();
$deleteRemoteResult = plugin_mastodon_run_deletion_sync(true);
$deleteRemoteStateAfter = plugin_mastodon_state_read();
$deleteRemoteEntryExists = entry_exists($deleteRemoteEntryId);
$deleteRemoteCommentExists = comment_exists($deleteRemoteEntryId, $deleteRemoteCommentId);
$allOk = test_result(
	'Follow-up deletion sync removes mirrored FlatPress content after remote Mastodon deletion',
	$deleteRemoteResult ['ok']
		&& !$deleteRemoteEntryExists
		&& !$deleteRemoteCommentExists
		&& empty($deleteRemoteStateAfter ['entries'])
		&& empty($deleteRemoteStateAfter ['comments'])
		&& !empty($deleteRemoteStateAfter ['last_deletion_run'])
		&& empty($deleteRemoteStateAfter ['deletions_pending'])
		&& isset($deleteRemoteStateAfter ['deletion_stats'] ['deleted_local_entries'])
		&& (int) $deleteRemoteStateAfter ['deletion_stats'] ['deleted_local_entries'] === 1
		&& isset($deleteRemoteStateAfter ['deletion_stats'] ['deleted_remote_comments'])
		&& (int) $deleteRemoteStateAfter ['deletion_stats'] ['deleted_remote_comments'] === 1,
	json_encode(array(
		'result' => $deleteRemoteResult,
		'state' => $deleteRemoteStateAfter,
		'entry_exists' => $deleteRemoteEntryExists,
		'comment_exists' => $deleteRemoteCommentExists
	))
) && $allOk;

// Deletion synchronization must respect sync_start_date and leave older mapped content untouched.
simulate_delete_recursive($simRoot . '/fp-content/plugin_mastodon');
mkdir($simRoot . '/fp-content/plugin_mastodon', 0777, true);
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array();
$deleteOldLocalOptions = $options;
$deleteOldLocalOptions ['sync_start_date'] = '2026-03-20';
plugin_mastodon_save_options($deleteOldLocalOptions);
$deleteOldLocalEntry = array(
	'version' => system_ver(),
	'subject' => 'Old local entry outside sync window',
	'content' => 'This old local entry must not trigger a remote delete.',
	'author' => 'Simulation',
	'date' => strtotime('2026-03-10 12:00:00 UTC')
);
$deleteOldLocalEntryId = entry_save($deleteOldLocalEntry, null);
$deleteOldLocalComment = array(
	'version' => system_ver(),
	'name' => 'Simulation',
	'content' => 'This old local comment must not trigger a remote delete.',
	'date' => strtotime('2026-03-10 12:05:00 UTC')
);
$deleteOldLocalCommentId = comment_save($deleteOldLocalEntryId, $deleteOldLocalComment);
$deleteOldLocalState = plugin_mastodon_default_state();
$deleteOldLocalState ['deletions_pending'] = 1;
plugin_mastodon_state_set_entry_mapping($deleteOldLocalState, $deleteOldLocalEntryId, '990', 'local', plugin_mastodon_entry_hash(entry_parse($deleteOldLocalEntryId)), $instanceUrl . '/@flatpress/990', '2026-03-10 12:00:00');
$deleteOldLocalParsedComment = comment_parse($deleteOldLocalEntryId, $deleteOldLocalCommentId);
plugin_mastodon_state_set_comment_mapping($deleteOldLocalState, $deleteOldLocalEntryId, $deleteOldLocalCommentId, '991', 'local', plugin_mastodon_comment_hash($deleteOldLocalParsedComment), $instanceUrl . '/@flatpress/991', '2026-03-10 12:05:00');
plugin_mastodon_state_write($deleteOldLocalState);
entry_delete($deleteOldLocalEntryId);
simulate_allow_pending_deletion_sync();
$deleteOldLocalResult = plugin_mastodon_run_deletion_sync(true);
$deleteOldLocalStateAfter = plugin_mastodon_state_read();
$deleteOldLocalRequestCount = count(simulate_recorded_http_requests());
$allOk = test_result(
	'Deletion sync skips old locally deleted mappings outside the sync start date window',
	$deleteOldLocalResult ['ok']
		&& $deleteOldLocalRequestCount === 0
		&& isset($deleteOldLocalStateAfter ['entries'] [$deleteOldLocalEntryId])
		&& isset($deleteOldLocalStateAfter ['comments'] [$deleteOldLocalEntryId . ':' . $deleteOldLocalCommentId])
		&& !empty($deleteOldLocalStateAfter ['last_deletion_run'])
		&& empty($deleteOldLocalStateAfter ['deletions_pending'])
		&& (!isset($deleteOldLocalStateAfter ['deletion_stats'] ['deleted_remote_entries']) || (int) $deleteOldLocalStateAfter ['deletion_stats'] ['deleted_remote_entries'] === 0)
		&& (!isset($deleteOldLocalStateAfter ['deletion_stats'] ['deleted_remote_comments']) || (int) $deleteOldLocalStateAfter ['deletion_stats'] ['deleted_remote_comments'] === 0),
	json_encode(array(
		'result' => $deleteOldLocalResult,
		'state' => $deleteOldLocalStateAfter,
		'requests' => $deleteOldLocalRequestCount
	))
) && $allOk;

// Remote-side deletions outside the sync start date must not remove old mirrored FlatPress content.
simulate_delete_recursive($simRoot . '/fp-content/plugin_mastodon');
mkdir($simRoot . '/fp-content/plugin_mastodon', 0777, true);
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array();
$deleteOldRemoteOptions = $options;
$deleteOldRemoteOptions ['sync_start_date'] = '2026-03-20';
plugin_mastodon_save_options($deleteOldRemoteOptions);
$deleteOldRemoteEntry = array(
	'version' => system_ver(),
	'subject' => 'Old mirrored entry outside sync window',
	'content' => 'This mirrored entry must stay even if the remote side disappeared.',
	'author' => 'Mastodon',
	'date' => strtotime('2026-03-10 14:00:00 UTC')
);
$deleteOldRemoteEntryId = entry_save($deleteOldRemoteEntry, null);
$deleteOldRemoteComment = array(
	'version' => system_ver(),
	'name' => 'Mastodon',
	'content' => 'This mirrored comment must stay even if the remote side disappeared.',
	'date' => strtotime('2026-03-10 14:05:00 UTC')
);
$deleteOldRemoteCommentId = comment_save($deleteOldRemoteEntryId, $deleteOldRemoteComment);
$deleteOldRemoteState = plugin_mastodon_default_state();
$deleteOldRemoteState ['deletions_pending'] = 1;
plugin_mastodon_state_set_entry_mapping($deleteOldRemoteState, $deleteOldRemoteEntryId, '992', 'remote', plugin_mastodon_entry_hash(entry_parse($deleteOldRemoteEntryId)), $instanceUrl . '/@flatpress/992', '2026-03-10 14:00:00');
$deleteOldRemoteParsedComment = comment_parse($deleteOldRemoteEntryId, $deleteOldRemoteCommentId);
plugin_mastodon_state_set_comment_mapping($deleteOldRemoteState, $deleteOldRemoteEntryId, $deleteOldRemoteCommentId, '993', 'remote', plugin_mastodon_comment_hash($deleteOldRemoteParsedComment), $instanceUrl . '/@flatpress/993', '2026-03-10 14:05:00');
plugin_mastodon_state_write($deleteOldRemoteState);
simulate_allow_pending_deletion_sync();
$deleteOldRemoteResult = plugin_mastodon_run_deletion_sync(true);
$deleteOldRemoteStateAfter = plugin_mastodon_state_read();
$deleteOldRemoteRequestCount = count(simulate_recorded_http_requests());
$allOk = test_result(
	'Deletion sync skips old remotely mirrored mappings outside the sync start date window',
	$deleteOldRemoteResult ['ok']
		&& $deleteOldRemoteRequestCount === 0
		&& entry_exists($deleteOldRemoteEntryId)
		&& comment_exists($deleteOldRemoteEntryId, $deleteOldRemoteCommentId)
		&& isset($deleteOldRemoteStateAfter ['entries'] [$deleteOldRemoteEntryId])
		&& isset($deleteOldRemoteStateAfter ['comments'] [$deleteOldRemoteEntryId . ':' . $deleteOldRemoteCommentId])
		&& !empty($deleteOldRemoteStateAfter ['last_deletion_run'])
		&& empty($deleteOldRemoteStateAfter ['deletions_pending'])
		&& (!isset($deleteOldRemoteStateAfter ['deletion_stats'] ['deleted_local_entries']) || (int) $deleteOldRemoteStateAfter ['deletion_stats'] ['deleted_local_entries'] === 0)
		&& (!isset($deleteOldRemoteStateAfter ['deletion_stats'] ['deleted_local_comments']) || (int) $deleteOldRemoteStateAfter ['deletion_stats'] ['deleted_local_comments'] === 0),
	json_encode(array(
		'result' => $deleteOldRemoteResult,
		'state' => $deleteOldRemoteStateAfter,
		'requests' => $deleteOldRemoteRequestCount,
		'entry_exists' => entry_exists($deleteOldRemoteEntryId),
		'comment_exists' => comment_exists($deleteOldRemoteEntryId, $deleteOldRemoteCommentId)
	))
) && $allOk;

// Scheduled deletion synchronization should use the automatic content window only for remote existence lookups.
simulate_delete_recursive($simRoot . '/fp-content/plugin_mastodon');
mkdir($simRoot . '/fp-content/plugin_mastodon', 0777, true);
$GLOBALS ['plugin_mastodon_test_now'] = strtotime('2026-04-30 16:00:00 UTC');
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array();
plugin_mastodon_sync_guard_clear('deletion');
$scheduledWindowDeleteOptions = $options;
$scheduledWindowDeleteOptions ['sync_start_date'] = '2026-01-01';
$scheduledWindowDeleteOptions ['sync_scheduled_window_days'] = '14';
plugin_mastodon_save_options($scheduledWindowDeleteOptions);
$scheduledWindowEntry = array(
	'version' => system_ver(),
	'subject' => 'Old existing entry outside scheduled deletion lookup window',
	'content' => 'This existing entry should not trigger a remote lookup during a scheduled deletion sync.',
	'author' => 'Simulation',
	'date' => strtotime('2026-03-01 12:00:00 UTC')
);
$scheduledWindowEntryId = entry_save($scheduledWindowEntry, null);
$scheduledWindowComment = array(
	'version' => system_ver(),
	'name' => 'Simulation',
	'content' => 'This existing comment should not trigger a remote lookup during a scheduled deletion sync.',
	'date' => strtotime('2026-03-01 12:05:00 UTC')
);
$scheduledWindowCommentId = comment_save($scheduledWindowEntryId, $scheduledWindowComment);
$scheduledWindowState = plugin_mastodon_default_state();
$scheduledWindowState ['deletions_pending'] = 1;
$scheduledWindowState ['deletions_not_before'] = '2026-04-30 15:59:00';
plugin_mastodon_state_set_entry_mapping($scheduledWindowState, $scheduledWindowEntryId, '994', 'local', plugin_mastodon_entry_hash(entry_parse($scheduledWindowEntryId)), $instanceUrl . '/@flatpress/994', '2026-03-01 12:00:00');
$scheduledWindowParsedComment = comment_parse($scheduledWindowEntryId, $scheduledWindowCommentId);
plugin_mastodon_state_set_comment_mapping($scheduledWindowState, $scheduledWindowEntryId, $scheduledWindowCommentId, '995', 'local', plugin_mastodon_comment_hash($scheduledWindowParsedComment), $instanceUrl . '/@flatpress/995', '2026-03-01 12:05:00');
plugin_mastodon_state_write($scheduledWindowState);
$scheduledWindowResult = plugin_mastodon_run_deletion_sync(false);
$scheduledWindowStateAfter = plugin_mastodon_state_read();
$scheduledWindowRequestCount = count(simulate_recorded_http_requests());
$allOk = test_result(
	'Scheduled deletion sync skips remote lookups outside the automatic scheduled window',
	$scheduledWindowResult ['ok']
		&& $scheduledWindowRequestCount === 0
		&& entry_exists($scheduledWindowEntryId)
		&& comment_exists($scheduledWindowEntryId, $scheduledWindowCommentId)
		&& isset($scheduledWindowStateAfter ['entries'] [$scheduledWindowEntryId])
		&& isset($scheduledWindowStateAfter ['comments'] [$scheduledWindowEntryId . ':' . $scheduledWindowCommentId])
		&& empty($scheduledWindowStateAfter ['deletions_pending'])
		&& empty($scheduledWindowStateAfter ['deletion_cursor_entries'])
		&& empty($scheduledWindowStateAfter ['deletion_cursor_comments']),
	json_encode(array(
		'result' => $scheduledWindowResult,
		'state' => $scheduledWindowStateAfter,
		'requests' => $scheduledWindowRequestCount
	))
) && $allOk;
unset($GLOBALS ['plugin_mastodon_test_now']);

// Local deletions outside the automatic scheduled window must still be propagated to Mastodon.
simulate_delete_recursive($simRoot . '/fp-content/plugin_mastodon');
mkdir($simRoot . '/fp-content/plugin_mastodon', 0777, true);
$GLOBALS ['plugin_mastodon_test_now'] = strtotime('2026-04-30 16:00:00 UTC');
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array();
plugin_mastodon_sync_guard_clear('deletion');
$scheduledOldLocalDeleteOptions = $options;
$scheduledOldLocalDeleteOptions ['sync_start_date'] = '2026-01-01';
$scheduledOldLocalDeleteOptions ['sync_scheduled_window_days'] = '14';
plugin_mastodon_save_options($scheduledOldLocalDeleteOptions);
$scheduledOldDeleteEntry = array(
	'version' => system_ver(),
	'subject' => 'Old local deletion still removes remote status',
	'content' => 'This old local entry is deleted locally and must still delete the mapped Mastodon status.',
	'author' => 'Simulation',
	'date' => strtotime('2026-03-01 13:00:00 UTC')
);
$scheduledOldDeleteEntryId = entry_save($scheduledOldDeleteEntry, null);
$scheduledOldDeleteComment = array(
	'version' => system_ver(),
	'name' => 'Simulation',
	'content' => 'This old local comment is deleted locally and must still delete the mapped Mastodon reply.',
	'date' => strtotime('2026-03-01 13:05:00 UTC')
);
$scheduledOldDeleteCommentId = comment_save($scheduledOldDeleteEntryId, $scheduledOldDeleteComment);
$scheduledOldDeleteState = plugin_mastodon_default_state();
$scheduledOldDeleteState ['deletions_pending'] = 1;
$scheduledOldDeleteState ['deletions_not_before'] = '2026-04-30 15:59:00';
plugin_mastodon_state_set_entry_mapping($scheduledOldDeleteState, $scheduledOldDeleteEntryId, '996', 'local', plugin_mastodon_entry_hash(entry_parse($scheduledOldDeleteEntryId)), $instanceUrl . '/@flatpress/996', '2026-03-01 13:00:00');
$scheduledOldDeleteParsedComment = comment_parse($scheduledOldDeleteEntryId, $scheduledOldDeleteCommentId);
plugin_mastodon_state_set_comment_mapping($scheduledOldDeleteState, $scheduledOldDeleteEntryId, $scheduledOldDeleteCommentId, '997', 'local', plugin_mastodon_comment_hash($scheduledOldDeleteParsedComment), $instanceUrl . '/@flatpress/997', '2026-03-01 13:05:00');
plugin_mastodon_state_write($scheduledOldDeleteState);
entry_delete($scheduledOldDeleteEntryId);
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'DELETE ' . $instanceUrl . '/api/v1/statuses/996?delete_media=1' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('id' => '996'))
	),
	'DELETE ' . $instanceUrl . '/api/v1/statuses/997?delete_media=1' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('id' => '997'))
	)
);
$scheduledOldDeleteResult = plugin_mastodon_run_deletion_sync(false);
$scheduledOldDeleteStateAfter = plugin_mastodon_state_read();
$scheduledOldDeleteRequests = 0;
foreach (simulate_recorded_http_requests() as $request) {
	if (is_array($request) && !empty($request ['method']) && strtoupper((string) $request ['method']) === 'DELETE') {
		$scheduledOldDeleteRequests++;
	}
}
$allOk = test_result(
	'Scheduled deletion sync still propagates old local deletions outside the automatic window',
	$scheduledOldDeleteResult ['ok']
		&& $scheduledOldDeleteRequests === 2
		&& empty($scheduledOldDeleteStateAfter ['entries'])
		&& empty($scheduledOldDeleteStateAfter ['comments'])
		&& empty($scheduledOldDeleteStateAfter ['deletions_pending'])
		&& empty($scheduledOldDeleteStateAfter ['deletion_cursor_entries'])
		&& empty($scheduledOldDeleteStateAfter ['deletion_cursor_comments']),
	json_encode(array(
		'result' => $scheduledOldDeleteResult,
		'state' => $scheduledOldDeleteStateAfter,
		'delete_requests' => $scheduledOldDeleteRequests
	))
) && $allOk;
unset($GLOBALS ['plugin_mastodon_test_now']);

// Large deletion syncs should continue after the saved entry cursor instead of repeating the same first mappings.
simulate_delete_recursive($simRoot . '/fp-content/plugin_mastodon');
mkdir($simRoot . '/fp-content/plugin_mastodon', 0777, true);
$GLOBALS ['plugin_mastodon_test_now'] = strtotime('2026-04-30 16:00:00 UTC');
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array();
plugin_mastodon_sync_guard_clear('deletion');
plugin_mastodon_rate_limit_window_clear();
$cursorOptions = $options;
$cursorOptions ['sync_start_date'] = '2026-01-01';
$cursorOptions ['sync_scheduled_window_days'] = '30';
plugin_mastodon_save_options($cursorOptions);
$cursorState = plugin_mastodon_default_state();
$cursorState ['deletions_pending'] = 1;
$cursorState ['deletions_not_before'] = '2026-04-30 15:59:00';
for ($i = 0; $i < 245; $i++) {
	$entryTimestamp = strtotime('2026-04-20 08:00:00 UTC') + ($i * 60);
	$entry = array(
		'version' => system_ver(),
		'subject' => 'Cursor deletion lookup entry ' . $i,
		'content' => 'Cursor deletion lookup fixture ' . $i,
		'author' => 'Simulation',
		'date' => $entryTimestamp
	);
	$entryId = entry_save($entry, null);
	$parsedEntry = entry_parse($entryId);
	$remoteId = (string) (12000 + $i);
	plugin_mastodon_state_set_entry_mapping($cursorState, $entryId, $remoteId, 'local', plugin_mastodon_entry_hash($parsedEntry), $instanceUrl . '/@flatpress/' . $remoteId, date('Y-m-d H:i:s', $entryTimestamp), plugin_mastodon_local_item_date_key($parsedEntry, $entryId), date('Y-m-d', $entryTimestamp));
	$GLOBALS ['plugin_mastodon_test_http_responses'] ['GET ' . $instanceUrl . '/api/v1/statuses/' . $remoteId] = array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('id' => $remoteId))
	);
}
plugin_mastodon_state_write($cursorState);
$cursorRunOne = plugin_mastodon_run_deletion_sync(false);
$cursorStateAfterOne = plugin_mastodon_state_read();
$cursorRequestsOne = count(simulate_recorded_http_requests());
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
simulate_allow_pending_deletion_sync();
plugin_mastodon_sync_guard_clear('deletion');
$cursorRunTwo = plugin_mastodon_run_deletion_sync(false);
$cursorStateAfterTwo = plugin_mastodon_state_read();
$cursorRequestsTwo = count(simulate_recorded_http_requests());
$allOk = test_result(
	'Large scheduled deletion syncs resume from the saved entry cursor',
	empty($cursorRunOne ['ok'])
		&& isset($cursorRunOne ['message']) && $cursorRunOne ['message'] === 'rate_limit_request_budget_exhausted'
		&& $cursorRequestsOne === 240
		&& !empty($cursorStateAfterOne ['deletions_pending'])
		&& !empty($cursorStateAfterOne ['deletion_cursor_entries'])
		&& $cursorRunTwo ['ok']
		&& $cursorRequestsTwo === 5
		&& empty($cursorStateAfterTwo ['deletions_pending'])
		&& empty($cursorStateAfterTwo ['deletion_cursor_entries'])
		&& empty($cursorStateAfterTwo ['deletion_cursor_comments']),
	json_encode(array(
		'run_one' => $cursorRunOne,
		'state_after_one' => array(
			'deletions_pending' => isset($cursorStateAfterOne ['deletions_pending']) ? $cursorStateAfterOne ['deletions_pending'] : null,
			'deletion_cursor_entries' => isset($cursorStateAfterOne ['deletion_cursor_entries']) ? $cursorStateAfterOne ['deletion_cursor_entries'] : '',
			'last_error' => isset($cursorStateAfterOne ['last_error']) ? $cursorStateAfterOne ['last_error'] : ''
		),
		'requests_one' => $cursorRequestsOne,
		'run_two' => $cursorRunTwo,
		'state_after_two' => array(
			'deletions_pending' => isset($cursorStateAfterTwo ['deletions_pending']) ? $cursorStateAfterTwo ['deletions_pending'] : null,
			'deletion_cursor_entries' => isset($cursorStateAfterTwo ['deletion_cursor_entries']) ? $cursorStateAfterTwo ['deletion_cursor_entries'] : '',
			'deletion_cursor_comments' => isset($cursorStateAfterTwo ['deletion_cursor_comments']) ? $cursorStateAfterTwo ['deletion_cursor_comments'] : '',
			'last_error' => isset($cursorStateAfterTwo ['last_error']) ? $cursorStateAfterTwo ['last_error'] : ''
		),
		'requests_two' => $cursorRequestsTwo
	))
) && $allOk;
unset($GLOBALS ['plugin_mastodon_test_now']);

// Large deletion syncs must also resume from a saved comment cursor inside a single entry shard.
simulate_delete_recursive($simRoot . '/fp-content/plugin_mastodon');
mkdir($simRoot . '/fp-content/plugin_mastodon', 0777, true);
$GLOBALS ['plugin_mastodon_test_now'] = strtotime('2026-04-30 16:00:00 UTC');
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array();
plugin_mastodon_sync_guard_clear('deletion');
plugin_mastodon_rate_limit_window_clear();
$commentCursorOptions = $options;
$commentCursorOptions ['sync_start_date'] = '2026-01-01';
$commentCursorOptions ['sync_scheduled_window_days'] = '30';
plugin_mastodon_save_options($commentCursorOptions);
$commentCursorEntry = array(
	'version' => system_ver(),
	'subject' => 'Cursor deletion comment shard entry',
	'content' => 'This entry owns many comments for a mid-shard deletion cursor resume test.',
	'author' => 'Simulation',
	'date' => strtotime('2026-04-20 09:00:00 UTC')
);
$commentCursorEntryId = entry_save($commentCursorEntry, null);
$commentCursorState = plugin_mastodon_default_state();
$commentCursorState ['deletions_pending'] = 1;
$commentCursorState ['deletions_not_before'] = '2026-04-30 15:59:00';
for ($i = 0; $i < 245; $i++) {
	$commentTimestamp = strtotime('2026-04-20 09:05:00 UTC') + ($i * 60);
	$comment = array(
		'version' => system_ver(),
		'name' => 'Comment Cursor Tester',
		'content' => 'Cursor deletion comment fixture ' . $i,
		'date' => $commentTimestamp
	);
	$commentId = comment_save($commentCursorEntryId, $comment);
	$parsedComment = comment_parse($commentCursorEntryId, $commentId);
	$remoteId = (string) (22000 + $i);
	plugin_mastodon_state_set_comment_mapping($commentCursorState, $commentCursorEntryId, $commentId, $remoteId, 'local', plugin_mastodon_comment_hash($parsedComment), $instanceUrl . '/@flatpress/' . $remoteId, date('Y-m-d H:i:s', $commentTimestamp), '', '', plugin_mastodon_local_item_date_key($parsedComment, $commentId), date('Y-m-d', $commentTimestamp));
	$GLOBALS ['plugin_mastodon_test_http_responses'] ['GET ' . $instanceUrl . '/api/v1/statuses/' . $remoteId] = array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('id' => $remoteId))
	);
}
plugin_mastodon_state_write($commentCursorState);
$commentCursorRunOne = plugin_mastodon_run_deletion_sync(false);
$commentCursorStateAfterOne = plugin_mastodon_state_read();
$commentCursorRequestsOne = count(simulate_recorded_http_requests());
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
simulate_allow_pending_deletion_sync();
plugin_mastodon_sync_guard_clear('deletion');
$commentCursorRunTwo = plugin_mastodon_run_deletion_sync(false);
$commentCursorStateAfterTwo = plugin_mastodon_state_read();
$commentCursorRequestsTwo = count(simulate_recorded_http_requests());
$commentCursorSavedKey = isset($commentCursorStateAfterOne ['deletion_cursor_comments']) ? (string) $commentCursorStateAfterOne ['deletion_cursor_comments'] : '';
$allOk = test_result(
	'Large scheduled deletion syncs resume from the saved comment cursor inside one entry shard',
	empty($commentCursorRunOne ['ok'])
		&& isset($commentCursorRunOne ['message']) && $commentCursorRunOne ['message'] === 'rate_limit_request_budget_exhausted'
		&& $commentCursorRequestsOne === 240
		&& $commentCursorSavedKey !== ''
		&& plugin_mastodon_state_entry_id_from_comment_key($commentCursorSavedKey) === $commentCursorEntryId
		&& $commentCursorRunTwo ['ok']
		&& $commentCursorRequestsTwo === 5
		&& empty($commentCursorStateAfterTwo ['deletions_pending'])
		&& empty($commentCursorStateAfterTwo ['deletion_cursor_entries'])
		&& empty($commentCursorStateAfterTwo ['deletion_cursor_comments']),
	json_encode(array(
		'run_one' => $commentCursorRunOne,
		'saved_comment_cursor' => $commentCursorSavedKey,
		'requests_one' => $commentCursorRequestsOne,
		'run_two' => $commentCursorRunTwo,
		'requests_two' => $commentCursorRequestsTwo
	))
) && $allOk;
unset($GLOBALS ['plugin_mastodon_test_now']);

// Disabling deletion synchronization must clear pending delete work and skip deletion requests.
simulate_delete_recursive($simRoot . '/fp-content/plugin_mastodon');
mkdir($simRoot . '/fp-content/plugin_mastodon', 0777, true);
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array();
$deleteDisabledRunOptions = $options;
$deleteDisabledRunOptions ['delete_sync_enabled'] = '0';
plugin_mastodon_save_options($deleteDisabledRunOptions);
$deleteDisabledRunState = plugin_mastodon_default_state();
$deleteDisabledRunState ['deletions_pending'] = 1;
plugin_mastodon_state_set_entry_mapping($deleteDisabledRunState, 'entry260324-120000', '990', 'local', 'disabled-delete-hash', $instanceUrl . '/@flatpress/990', '2026-03-24 12:00:00');
plugin_mastodon_state_write($deleteDisabledRunState);
simulate_allow_pending_deletion_sync();
$deleteDisabledRunResult = plugin_mastodon_run_deletion_sync(true);
$deleteDisabledRunStateAfter = isset($deleteDisabledRunResult ['state']) && is_array($deleteDisabledRunResult ['state']) ? $deleteDisabledRunResult ['state'] : plugin_mastodon_state_read();
$deleteDisabledRunDeleteRequests = 0;
if (simulate_recorded_http_requests() !== array()) {
	foreach (simulate_recorded_http_requests() as $request) {
		if (is_array($request) && !empty($request ['method']) && strtoupper((string) $request ['method']) === 'DELETE') {
			$deleteDisabledRunDeleteRequests++;
		}
	}
}
$allOk = test_result(
	'Disabling deletion synchronization clears pending delete work without issuing deletion requests',
	$deleteDisabledRunResult ['ok']
		&& isset($deleteDisabledRunResult ['message']) && $deleteDisabledRunResult ['message'] === 'deletion_sync_disabled'
		&& empty($deleteDisabledRunStateAfter ['deletions_pending'])
		&& isset($deleteDisabledRunStateAfter ['entries'] ['entry260324-120000'])
		&& $deleteDisabledRunDeleteRequests === 0,
	json_encode(array(
		'result' => $deleteDisabledRunResult,
		'state' => $deleteDisabledRunStateAfter,
		'delete_requests' => $deleteDisabledRunDeleteRequests
	))
) && $allOk;

// Exact reproduction: after a local FlatPress comment delete, one more content sync must not resurrect the exported Mastodon reply before the later deletion sync runs.
$descendantDeletionContentBeforeCase = simulate_run_exported_comment_descendant_content_before_deletion_case(true, true);
$descendantDeletionContentBeforeState = isset($descendantDeletionContentBeforeCase ['state_after_content_sync_before_deletion']) && is_array($descendantDeletionContentBeforeCase ['state_after_content_sync_before_deletion']) ? $descendantDeletionContentBeforeCase ['state_after_content_sync_before_deletion'] : array();
$descendantDeletionContentBeforeDeletionState = isset($descendantDeletionContentBeforeCase ['state_after_deletion_pass']) && is_array($descendantDeletionContentBeforeCase ['state_after_deletion_pass']) ? $descendantDeletionContentBeforeCase ['state_after_deletion_pass'] : array();
$descendantDeletionContentBeforeCommentIds = isset($descendantDeletionContentBeforeCase ['comment_ids_after_content_sync_before_deletion']) && is_array($descendantDeletionContentBeforeCase ['comment_ids_after_content_sync_before_deletion']) ? $descendantDeletionContentBeforeCase ['comment_ids_after_content_sync_before_deletion'] : array();
sort($descendantDeletionContentBeforeCommentIds);
$allOk = test_result(
	'Content sync before deletion sync protects a locally deleted exported FlatPress comment from stale re-import',
		!empty($descendantDeletionContentBeforeCase ['initial_sync_result'] ['ok'])
		&& !empty($descendantDeletionContentBeforeCase ['initial_child_ref'] ['comment_id'])
		&& !empty($descendantDeletionContentBeforeCase ['content_sync_before_deletion_result'] ['ok'])
		&& !empty($descendantDeletionContentBeforeState ['comment_tombstones'] ['9811'])
		&& !$descendantDeletionContentBeforeCase ['parent_exists_after_content_sync_before_deletion']
		&& isset($descendantDeletionContentBeforeCase ['parent_ref_after_content_sync_before_deletion'] ['comment_id'])
		&& (string) $descendantDeletionContentBeforeCase ['parent_ref_after_content_sync_before_deletion'] ['comment_id'] === (string) $descendantDeletionContentBeforeCase ['local_comment_id']
		&& $descendantDeletionContentBeforeCommentIds === array($descendantDeletionContentBeforeCase ['child_comment_id']),
	json_encode(array(
		'content_sync_before_deletion_result' => $descendantDeletionContentBeforeCase ['content_sync_before_deletion_result'],
		'state_after_content_sync_before_deletion' => $descendantDeletionContentBeforeState,
		'comment_ids_after_content_sync_before_deletion' => $descendantDeletionContentBeforeCommentIds,
		'parent_ref_after_content_sync_before_deletion' => $descendantDeletionContentBeforeCase ['parent_ref_after_content_sync_before_deletion'],
		'parent_exists_after_content_sync_before_deletion' => $descendantDeletionContentBeforeCase ['parent_exists_after_content_sync_before_deletion']
	))
) && $allOk;

$allOk = test_result(
	'Deletion sync after the protected content sync reattaches the imported descendant reply to the synchronized entry status and keeps a later verification pending',
		!empty($descendantDeletionContentBeforeCase ['deletion_pass_after_content_sync'] ['ok'])
		&& !empty($descendantDeletionContentBeforeDeletionState ['comment_tombstones'] ['9811'])
		&& empty($descendantDeletionContentBeforeDeletionState ['comments_remote'] ['9811'])
		&& !empty($descendantDeletionContentBeforeCase ['child_exists_after_deletion_pass'])
		&& isset($descendantDeletionContentBeforeCase ['child_parent_after_deletion_pass'])
		&& (string) $descendantDeletionContentBeforeCase ['child_parent_after_deletion_pass'] === ''
		&& isset($descendantDeletionContentBeforeCase ['child_meta_after_deletion_pass'] ['parent_comment_id'])
		&& (string) $descendantDeletionContentBeforeCase ['child_meta_after_deletion_pass'] ['parent_comment_id'] === ''
		&& isset($descendantDeletionContentBeforeCase ['child_meta_after_deletion_pass'] ['in_reply_to_remote_id'])
		&& (string) $descendantDeletionContentBeforeCase ['child_meta_after_deletion_pass'] ['in_reply_to_remote_id'] === '9810'
		&& !empty($descendantDeletionContentBeforeDeletionState ['pending_comment_remote_rechecks'])
		&& !empty($descendantDeletionContentBeforeDeletionState ['deletions_pending']),
	json_encode(array(
		'deletion_pass_after_content_sync' => $descendantDeletionContentBeforeCase ['deletion_pass_after_content_sync'],
		'state_after_deletion_pass' => $descendantDeletionContentBeforeDeletionState,
		'child_comment_after_deletion_pass' => $descendantDeletionContentBeforeCase ['child_comment_after_deletion_pass'],
		'child_meta_after_deletion_pass' => $descendantDeletionContentBeforeCase ['child_meta_after_deletion_pass'],
		'child_parent_after_deletion_pass' => $descendantDeletionContentBeforeCase ['child_parent_after_deletion_pass'],
		'requests_after_deletion_pass' => $descendantDeletionContentBeforeCase ['requests_after_deletion_pass']
	))
) && $allOk;

// Deletion synchronization must tombstone locally deleted exported comments and recheck imported descendants in later follow-up passes.
$descendantDeletionStaleCase = simulate_run_exported_comment_descendant_deletion_case(true, true);
$descendantDeletionStaleStateOne = isset($descendantDeletionStaleCase ['state_after_deletion_pass_one']) && is_array($descendantDeletionStaleCase ['state_after_deletion_pass_one']) ? $descendantDeletionStaleCase ['state_after_deletion_pass_one'] : array();
$descendantDeletionStaleStateTwo = isset($descendantDeletionStaleCase ['state_after_deletion_pass_two']) && is_array($descendantDeletionStaleCase ['state_after_deletion_pass_two']) ? $descendantDeletionStaleCase ['state_after_deletion_pass_two'] : array();
$descendantDeletionStalePending = isset($descendantDeletionStaleStateOne ['pending_comment_remote_rechecks']) && is_array($descendantDeletionStaleStateOne ['pending_comment_remote_rechecks']) ? $descendantDeletionStaleStateOne ['pending_comment_remote_rechecks'] : array();
$allOk = test_result(
	'Deletion sync keeps imported descendants for a later follow-up verification when Mastodon still returns the child reply once',
		!empty($descendantDeletionStaleCase ['initial_sync_result'] ['ok'])
		&& !empty($descendantDeletionStaleCase ['initial_child_ref'] ['comment_id'])
		&& !empty($descendantDeletionStaleCase ['deletion_pass_one'] ['ok'])
		&& !empty($descendantDeletionStaleStateOne ['comment_tombstones'] ['961'])
		&& empty($descendantDeletionStaleStateOne ['comments_remote'] ['961'])
		&& !empty($descendantDeletionStaleCase ['child_exists_after_deletion_pass_one'])
		&& !empty($descendantDeletionStalePending)
		&& !empty($descendantDeletionStaleStateOne ['deletions_pending']),
	json_encode(array(
		'deletion_pass_one' => $descendantDeletionStaleCase ['deletion_pass_one'],
		'state_after_deletion_pass_one' => $descendantDeletionStaleStateOne,
		'child_exists_after_deletion_pass_one' => $descendantDeletionStaleCase ['child_exists_after_deletion_pass_one']
	))
) && $allOk;

$allOk = test_result(
	'Tombstones prevent re-importing the deleted exported parent comment while descendant rechecks are still pending',
		!empty($descendantDeletionStaleCase ['stale_content_sync_result'] ['ok'])
		&& empty($descendantDeletionStaleCase ['reimported_parent_ref'])
		&& empty($descendantDeletionStaleStateOne ['comments_remote'] ['961'])
		&& !empty($descendantDeletionStaleCase ['child_exists_after_deletion_pass_one']),
	json_encode(array(
		'stale_content_sync_result' => $descendantDeletionStaleCase ['stale_content_sync_result'],
		'state_after_stale_content_sync' => isset($descendantDeletionStaleCase ['state_after_stale_content_sync']) ? $descendantDeletionStaleCase ['state_after_stale_content_sync'] : array(),
		'reimported_parent_ref' => $descendantDeletionStaleCase ['reimported_parent_ref']
	))
) && $allOk;

$allOk = test_result(
	'A later follow-up deletion sync removes the imported descendant once Mastodon reports the child reply missing',
		!empty($descendantDeletionStaleCase ['deletion_pass_two'] ['ok'])
		&& empty($descendantDeletionStaleCase ['child_exists_after_deletion_pass_two'])
		&& !empty($descendantDeletionStaleStateTwo ['comment_tombstones'] ['962'])
		&& empty($descendantDeletionStaleStateTwo ['pending_comment_remote_rechecks'])
		&& empty($descendantDeletionStaleStateTwo ['deletions_pending'])
		&& isset($descendantDeletionStaleStateTwo ['deletion_stats'] ['deleted_local_comments'])
		&& (int) $descendantDeletionStaleStateTwo ['deletion_stats'] ['deleted_local_comments'] >= 1,
	json_encode(array(
		'deletion_pass_two' => $descendantDeletionStaleCase ['deletion_pass_two'],
		'state_after_deletion_pass_two' => $descendantDeletionStaleStateTwo,
		'child_exists_after_deletion_pass_two' => $descendantDeletionStaleCase ['child_exists_after_deletion_pass_two']
	))
) && $allOk;

$descendantDeletionContentBeforeChainCase = simulate_run_exported_comment_descendant_with_child_content_before_deletion_case(true);
$descendantDeletionContentBeforeChainState = isset($descendantDeletionContentBeforeChainCase ['state_after_content_sync_before_deletion']) && is_array($descendantDeletionContentBeforeChainCase ['state_after_content_sync_before_deletion']) ? $descendantDeletionContentBeforeChainCase ['state_after_content_sync_before_deletion'] : array();
$descendantDeletionContentBeforeChainDeletionState = isset($descendantDeletionContentBeforeChainCase ['state_after_deletion_pass']) && is_array($descendantDeletionContentBeforeChainCase ['state_after_deletion_pass']) ? $descendantDeletionContentBeforeChainCase ['state_after_deletion_pass'] : array();
$descendantDeletionContentBeforeChainCommentIds = isset($descendantDeletionContentBeforeChainCase ['comment_ids_after_content_sync_before_deletion']) && is_array($descendantDeletionContentBeforeChainCase ['comment_ids_after_content_sync_before_deletion']) ? $descendantDeletionContentBeforeChainCase ['comment_ids_after_content_sync_before_deletion'] : array();
sort($descendantDeletionContentBeforeChainCommentIds);
$descendantDeletionContentBeforeChainPassUrls = array();
if (!empty($descendantDeletionContentBeforeChainCase ['requests_after_deletion_pass']) && is_array($descendantDeletionContentBeforeChainCase ['requests_after_deletion_pass'])) {
	foreach ($descendantDeletionContentBeforeChainCase ['requests_after_deletion_pass'] as $request) {
		if (!is_array($request) || empty($request ['method']) || empty($request ['url'])) {
			continue;
		}
		$descendantDeletionContentBeforeChainPassUrls [] = strtoupper((string) $request ['method']) . ' ' . (string) $request ['url'];
	}
}
sort($descendantDeletionContentBeforeChainPassUrls);
$allOk = test_result(
	'Content sync before deletion sync also protects a locally deleted exported FlatPress comment when the imported descendant reply already has its own child',
		!empty($descendantDeletionContentBeforeChainCase ['initial_sync_result'] ['ok'])
		&& !empty($descendantDeletionContentBeforeChainCase ['initial_child_ref'] ['comment_id'])
		&& !empty($descendantDeletionContentBeforeChainCase ['initial_grandchild_ref'] ['comment_id'])
		&& !empty($descendantDeletionContentBeforeChainCase ['content_sync_before_deletion_result'] ['ok'])
		&& !empty($descendantDeletionContentBeforeChainState ['comment_tombstones'] ['9821'])
		&& !$descendantDeletionContentBeforeChainCase ['parent_exists_after_content_sync_before_deletion']
		&& isset($descendantDeletionContentBeforeChainCase ['parent_ref_after_content_sync_before_deletion'] ['comment_id'])
		&& (string) $descendantDeletionContentBeforeChainCase ['parent_ref_after_content_sync_before_deletion'] ['comment_id'] === (string) $descendantDeletionContentBeforeChainCase ['local_comment_id']
		&& $descendantDeletionContentBeforeChainCommentIds === array($descendantDeletionContentBeforeChainCase ['child_comment_id'], $descendantDeletionContentBeforeChainCase ['grandchild_comment_id']),
	json_encode(array(
		'content_sync_before_deletion_result' => $descendantDeletionContentBeforeChainCase ['content_sync_before_deletion_result'],
		'state_after_content_sync_before_deletion' => $descendantDeletionContentBeforeChainState,
		'comment_ids_after_content_sync_before_deletion' => $descendantDeletionContentBeforeChainCommentIds,
		'parent_ref_after_content_sync_before_deletion' => $descendantDeletionContentBeforeChainCase ['parent_ref_after_content_sync_before_deletion'],
		'parent_exists_after_content_sync_before_deletion' => $descendantDeletionContentBeforeChainCase ['parent_exists_after_content_sync_before_deletion']
	))
) && $allOk;

$allOk = test_result(
	'Deletion sync after the protected content sync keeps the descendant child below the reattached reply and verifies both remote replies in the same pass',
		!empty($descendantDeletionContentBeforeChainCase ['deletion_pass_after_content_sync'] ['ok'])
		&& !empty($descendantDeletionContentBeforeChainDeletionState ['comment_tombstones'] ['9821'])
		&& empty($descendantDeletionContentBeforeChainDeletionState ['comments_remote'] ['9821'])
		&& !empty($descendantDeletionContentBeforeChainCase ['child_exists_after_deletion_pass'])
		&& !empty($descendantDeletionContentBeforeChainCase ['grandchild_exists_after_deletion_pass'])
		&& isset($descendantDeletionContentBeforeChainCase ['child_parent_after_deletion_pass'])
		&& (string) $descendantDeletionContentBeforeChainCase ['child_parent_after_deletion_pass'] === ''
		&& isset($descendantDeletionContentBeforeChainCase ['grandchild_parent_after_deletion_pass'])
		&& (string) $descendantDeletionContentBeforeChainCase ['grandchild_parent_after_deletion_pass'] === (string) $descendantDeletionContentBeforeChainCase ['child_comment_id']
		&& isset($descendantDeletionContentBeforeChainCase ['child_meta_after_deletion_pass'] ['parent_comment_id'])
		&& (string) $descendantDeletionContentBeforeChainCase ['child_meta_after_deletion_pass'] ['parent_comment_id'] === ''
		&& isset($descendantDeletionContentBeforeChainCase ['child_meta_after_deletion_pass'] ['in_reply_to_remote_id'])
		&& (string) $descendantDeletionContentBeforeChainCase ['child_meta_after_deletion_pass'] ['in_reply_to_remote_id'] === '9820'
		&& isset($descendantDeletionContentBeforeChainCase ['grandchild_meta_after_deletion_pass'] ['parent_comment_id'])
		&& (string) $descendantDeletionContentBeforeChainCase ['grandchild_meta_after_deletion_pass'] ['parent_comment_id'] === (string) $descendantDeletionContentBeforeChainCase ['child_comment_id']
		&& isset($descendantDeletionContentBeforeChainCase ['grandchild_meta_after_deletion_pass'] ['in_reply_to_remote_id'])
		&& (string) $descendantDeletionContentBeforeChainCase ['grandchild_meta_after_deletion_pass'] ['in_reply_to_remote_id'] === '9822'
		&& !empty($descendantDeletionContentBeforeChainDeletionState ['pending_comment_remote_rechecks'])
		&& $descendantDeletionContentBeforeChainPassUrls === array(
			'DELETE ' . $instanceUrl . '/api/v1/statuses/9821?delete_media=1',
			'GET ' . $instanceUrl . '/api/v1/statuses/9820',
			'GET ' . $instanceUrl . '/api/v1/statuses/9822',
			'GET ' . $instanceUrl . '/api/v1/statuses/9823'
		),
	json_encode(array(
		'deletion_pass_after_content_sync' => $descendantDeletionContentBeforeChainCase ['deletion_pass_after_content_sync'],
		'state_after_deletion_pass' => $descendantDeletionContentBeforeChainDeletionState,
		'child_comment_after_deletion_pass' => $descendantDeletionContentBeforeChainCase ['child_comment_after_deletion_pass'],
		'grandchild_comment_after_deletion_pass' => $descendantDeletionContentBeforeChainCase ['grandchild_comment_after_deletion_pass'],
		'child_meta_after_deletion_pass' => $descendantDeletionContentBeforeChainCase ['child_meta_after_deletion_pass'],
		'grandchild_meta_after_deletion_pass' => $descendantDeletionContentBeforeChainCase ['grandchild_meta_after_deletion_pass'],
		'requests_after_deletion_pass' => $descendantDeletionContentBeforeChainCase ['requests_after_deletion_pass'],
		'pass_urls' => $descendantDeletionContentBeforeChainPassUrls
	))
) && $allOk;

$descendantDeletionImmediateCase = simulate_run_exported_comment_descendant_deletion_case(true, false);
$descendantDeletionImmediateStateOne = isset($descendantDeletionImmediateCase ['state_after_deletion_pass_one']) && is_array($descendantDeletionImmediateCase ['state_after_deletion_pass_one']) ? $descendantDeletionImmediateCase ['state_after_deletion_pass_one'] : array();
$allOk = test_result(
	'Deletion sync removes imported descendants immediately when Mastodon already reports the child reply missing in the first follow-up pass',
		!empty($descendantDeletionImmediateCase ['deletion_pass_one'] ['ok'])
		&& empty($descendantDeletionImmediateCase ['child_exists_after_deletion_pass_one'])
		&& !empty($descendantDeletionImmediateStateOne ['comment_tombstones'] ['961'])
		&& !empty($descendantDeletionImmediateStateOne ['comment_tombstones'] ['962'])
		&& empty($descendantDeletionImmediateStateOne ['pending_comment_remote_rechecks'])
		&& empty($descendantDeletionImmediateStateOne ['deletions_pending'])
		&& empty($descendantDeletionImmediateCase ['reimported_parent_ref']),
	json_encode(array(
		'deletion_pass_one' => $descendantDeletionImmediateCase ['deletion_pass_one'],
		'state_after_deletion_pass_one' => $descendantDeletionImmediateStateOne,
		'state_after_stale_content_sync' => isset($descendantDeletionImmediateCase ['state_after_stale_content_sync']) ? $descendantDeletionImmediateCase ['state_after_stale_content_sync'] : array(),
		'reimported_parent_ref' => $descendantDeletionImmediateCase ['reimported_parent_ref']
	))
) && $allOk;

$descendantChainCase = simulate_run_exported_comment_descendant_chain_case(true);
$descendantChainStateOne = isset($descendantChainCase ['state_after_deletion_pass_one']) && is_array($descendantChainCase ['state_after_deletion_pass_one']) ? $descendantChainCase ['state_after_deletion_pass_one'] : array();
$descendantChainStateTwo = isset($descendantChainCase ['state_after_deletion_pass_two']) && is_array($descendantChainCase ['state_after_deletion_pass_two']) ? $descendantChainCase ['state_after_deletion_pass_two'] : array();
$descendantChainPendingOne = isset($descendantChainStateOne ['pending_comment_remote_rechecks']) && is_array($descendantChainStateOne ['pending_comment_remote_rechecks']) ? $descendantChainStateOne ['pending_comment_remote_rechecks'] : array();
$descendantChainPassTwoUrls = array();
if (!empty($descendantChainCase ['requests_after_deletion_pass_two']) && is_array($descendantChainCase ['requests_after_deletion_pass_two'])) {
	foreach ($descendantChainCase ['requests_after_deletion_pass_two'] as $request) {
		if (!is_array($request) || empty($request ['method']) || empty($request ['url'])) {
			continue;
		}
		$descendantChainPassTwoUrls [] = strtoupper((string) $request ['method']) . ' ' . (string) $request ['url'];
	}
}
sort($descendantChainPassTwoUrls);
$allOk = test_result(
	'Hybrid descendant rechecks keep only the direct child queued after the first stale follow-up pass of a deeper reply chain',
		!empty($descendantChainCase ['initial_sync_result'] ['ok'])
		&& !empty($descendantChainCase ['initial_child_ref'] ['comment_id'])
		&& !empty($descendantChainCase ['initial_grandchild_ref'] ['comment_id'])
		&& !empty($descendantChainCase ['deletion_pass_one'] ['ok'])
		&& !empty($descendantChainStateOne ['comment_tombstones'] ['971'])
		&& !empty($descendantChainStateOne ['deletions_pending'])
		&& isset($descendantChainStateOne ['deletions_pending_scope']) && $descendantChainStateOne ['deletions_pending_scope'] === 'comment_rechecks'
		&& count($descendantChainPendingOne) === 1
		&& !empty($descendantChainPendingOne [$descendantChainCase ['entry_id'] . ':' . $descendantChainCase ['child_comment_id']])
		&& empty($descendantChainPendingOne [$descendantChainCase ['entry_id'] . ':' . $descendantChainCase ['grandchild_comment_id']]),
	json_encode(array(
		'deletion_pass_one' => $descendantChainCase ['deletion_pass_one'],
		'state_after_deletion_pass_one' => $descendantChainStateOne,
		'requests_after_deletion_pass_one' => $descendantChainCase ['requests_after_deletion_pass_one']
	))
) && $allOk;

$allOk = test_result(
	'Hybrid descendant rechecks remove the direct child and its direct child within the same targeted follow-up request',
		!empty($descendantChainCase ['deletion_pass_two'] ['ok'])
		&& empty($descendantChainCase ['child_exists_after_deletion_pass_two'])
		&& empty($descendantChainCase ['grandchild_exists_after_deletion_pass_two'])
		&& !empty($descendantChainStateTwo ['comment_tombstones'] ['972'])
		&& !empty($descendantChainStateTwo ['comment_tombstones'] ['973'])
		&& empty($descendantChainStateTwo ['pending_comment_remote_rechecks'])
		&& empty($descendantChainStateTwo ['deletions_pending'])
		&& $descendantChainPassTwoUrls === array(
			'GET ' . $instanceUrl . '/api/v1/statuses/972',
			'GET ' . $instanceUrl . '/api/v1/statuses/973'
		),
	json_encode(array(
		'deletion_pass_two' => $descendantChainCase ['deletion_pass_two'],
		'state_after_deletion_pass_two' => $descendantChainStateTwo,
		'requests_after_deletion_pass_two' => $descendantChainCase ['requests_after_deletion_pass_two'],
		'pass_two_urls' => $descendantChainPassTwoUrls
	))
) && $allOk;

$deletedImportedRemoteUneditedCase = simulate_run_deleted_imported_remote_comment_tombstone_case(false);
$deletedImportedRemoteUneditedStateAfterInitial = isset($deletedImportedRemoteUneditedCase ['state_after_initial_sync']) && is_array($deletedImportedRemoteUneditedCase ['state_after_initial_sync']) ? $deletedImportedRemoteUneditedCase ['state_after_initial_sync'] : array();
$deletedImportedRemoteUneditedStateAfterDelete = isset($deletedImportedRemoteUneditedCase ['state_after_local_delete']) && is_array($deletedImportedRemoteUneditedCase ['state_after_local_delete']) ? $deletedImportedRemoteUneditedCase ['state_after_local_delete'] : array();
$deletedImportedRemoteUneditedStateAfterNextSync = isset($deletedImportedRemoteUneditedCase ['state_after_next_content_sync']) && is_array($deletedImportedRemoteUneditedCase ['state_after_next_content_sync']) ? $deletedImportedRemoteUneditedCase ['state_after_next_content_sync'] : array();
$allOk = test_result(
	'Locally deleted imported external Mastodon reply is tombstoned immediately and not re-imported unchanged',
		!empty($deletedImportedRemoteUneditedCase ['initial_sync_result'] ['ok'])
		&& !empty($deletedImportedRemoteUneditedCase ['imported_reply_exists_before_delete'])
		&& isset($deletedImportedRemoteUneditedStateAfterInitial ['comments'] [$deletedImportedRemoteUneditedCase ['entry_id'] . ':' . $deletedImportedRemoteUneditedCase ['local_comment_one_id']] ['source'])
		&& (string) $deletedImportedRemoteUneditedStateAfterInitial ['comments'] [$deletedImportedRemoteUneditedCase ['entry_id'] . ':' . $deletedImportedRemoteUneditedCase ['local_comment_one_id']] ['source'] === 'local'
		&& !empty($deletedImportedRemoteUneditedStateAfterDelete ['comment_tombstones'] ['9903'])
		&& empty($deletedImportedRemoteUneditedStateAfterDelete ['comments_remote'] ['9903'])
		&& empty($deletedImportedRemoteUneditedCase ['imported_reply_exists_after_next_sync'])
		&& empty($deletedImportedRemoteUneditedCase ['reimported_reply_ref'])
		&& !empty($deletedImportedRemoteUneditedStateAfterNextSync ['comment_tombstones'] ['9903']),
	json_encode(array(
		'initial_sync_result' => $deletedImportedRemoteUneditedCase ['initial_sync_result'],
		'state_after_initial_sync' => $deletedImportedRemoteUneditedStateAfterInitial,
		'state_after_local_delete' => $deletedImportedRemoteUneditedStateAfterDelete,
		'next_content_sync_result' => $deletedImportedRemoteUneditedCase ['next_content_sync_result'],
		'state_after_next_content_sync' => $deletedImportedRemoteUneditedStateAfterNextSync,
		'reimported_reply_ref' => $deletedImportedRemoteUneditedCase ['reimported_reply_ref']
	))
) && $allOk;

$deletedImportedRemoteEditedCase = simulate_run_deleted_imported_remote_comment_tombstone_case(true);
$deletedImportedRemoteEditedStateAfterInitial = isset($deletedImportedRemoteEditedCase ['state_after_initial_sync']) && is_array($deletedImportedRemoteEditedCase ['state_after_initial_sync']) ? $deletedImportedRemoteEditedCase ['state_after_initial_sync'] : array();
$deletedImportedRemoteEditedStateAfterDelete = isset($deletedImportedRemoteEditedCase ['state_after_local_delete']) && is_array($deletedImportedRemoteEditedCase ['state_after_local_delete']) ? $deletedImportedRemoteEditedCase ['state_after_local_delete'] : array();
$deletedImportedRemoteEditedStateAfterNextSync = isset($deletedImportedRemoteEditedCase ['state_after_next_content_sync']) && is_array($deletedImportedRemoteEditedCase ['state_after_next_content_sync']) ? $deletedImportedRemoteEditedCase ['state_after_next_content_sync'] : array();
$allOk = test_result(
	'Locally deleted imported external Mastodon reply is not re-imported after the remote author edits it',
		!empty($deletedImportedRemoteEditedCase ['initial_sync_result'] ['ok'])
		&& !empty($deletedImportedRemoteEditedCase ['imported_reply_exists_before_delete'])
		&& isset($deletedImportedRemoteEditedStateAfterInitial ['comments'] [$deletedImportedRemoteEditedCase ['entry_id'] . ':' . $deletedImportedRemoteEditedCase ['local_comment_one_id']] ['source'])
		&& (string) $deletedImportedRemoteEditedStateAfterInitial ['comments'] [$deletedImportedRemoteEditedCase ['entry_id'] . ':' . $deletedImportedRemoteEditedCase ['local_comment_one_id']] ['source'] === 'local'
		&& !empty($deletedImportedRemoteEditedStateAfterDelete ['comment_tombstones'] ['9903'])
		&& empty($deletedImportedRemoteEditedStateAfterDelete ['comments_remote'] ['9903'])
		&& empty($deletedImportedRemoteEditedCase ['imported_reply_exists_after_next_sync'])
		&& empty($deletedImportedRemoteEditedCase ['reimported_reply_ref'])
		&& !empty($deletedImportedRemoteEditedStateAfterNextSync ['comment_tombstones'] ['9903']),
	json_encode(array(
		'initial_sync_result' => $deletedImportedRemoteEditedCase ['initial_sync_result'],
		'state_after_initial_sync' => $deletedImportedRemoteEditedStateAfterInitial,
		'state_after_local_delete' => $deletedImportedRemoteEditedStateAfterDelete,
		'next_content_sync_result' => $deletedImportedRemoteEditedCase ['next_content_sync_result'],
		'state_after_next_content_sync' => $deletedImportedRemoteEditedStateAfterNextSync,
		'reimported_reply_ref' => $deletedImportedRemoteEditedCase ['reimported_reply_ref']
	))
) && $allOk;

$deletedImportedRemoteCleanupCase = simulate_run_deleted_imported_remote_comment_deletion_sync_cleanup_case();
$deletedImportedRemoteCleanupState = isset($deletedImportedRemoteCleanupCase ['state_after_deletion_sync']) && is_array($deletedImportedRemoteCleanupCase ['state_after_deletion_sync']) ? $deletedImportedRemoteCleanupCase ['state_after_deletion_sync'] : array();
$allOk = test_result(
	'Deletion sync treats locally deleted imported remote replies as local ignore decisions without remote DELETE',
		!empty($deletedImportedRemoteCleanupCase ['deletion_sync_result'] ['ok'])
		&& !empty($deletedImportedRemoteCleanupState ['comment_tombstones'] ['9911'])
		&& empty($deletedImportedRemoteCleanupState ['comments_remote'] ['9911'])
		&& empty($deletedImportedRemoteCleanupState ['deletions_pending'])
		&& empty($deletedImportedRemoteCleanupCase ['delete_requests_after_deletion_sync'])
		&& isset($deletedImportedRemoteCleanupState ['deletion_stats'] ['deleted_remote_comments'])
		&& (int) $deletedImportedRemoteCleanupState ['deletion_stats'] ['deleted_remote_comments'] === 0,
	json_encode(array(
		'deletion_sync_result' => $deletedImportedRemoteCleanupCase ['deletion_sync_result'],
		'state_after_deletion_sync' => $deletedImportedRemoteCleanupState,
		'requests_after_deletion_sync' => $deletedImportedRemoteCleanupCase ['requests_after_deletion_sync'],
		'delete_requests_after_deletion_sync' => $deletedImportedRemoteCleanupCase ['delete_requests_after_deletion_sync']
	))
) && $allOk;

plugin_mastodon_save_options($options);

simulate_delete_recursive($simRoot . '/fp-content/plugin_mastodon');
mkdir($simRoot . '/fp-content/plugin_mastodon', 0777, true);
$commentUpdateOptions = $options;
$commentUpdateOptions ['update_local_from_remote'] = '1';
plugin_mastodon_save_options($commentUpdateOptions);
$commentUpdateEntry = array(
	'version' => system_ver(),
	'subject' => 'Remote comment counter entry',
	'content' => 'Entry body for remote comment counter test.',
	'author' => 'Simulation',
	'date' => strtotime('2026-04-05 12:00:00 UTC')
);
$commentUpdateEntryId = entry_save($commentUpdateEntry, null);
$commentUpdateLocalComment = array(
	'version' => system_ver(),
	'name' => 'Remote Counter Tester',
	'content' => 'Original local comment',
	'date' => strtotime('2026-04-05 12:01:00 UTC')
);
$commentUpdateCommentId = comment_save($commentUpdateEntryId, $commentUpdateLocalComment);
$commentUpdateEntryParsed = entry_parse($commentUpdateEntryId);
$commentUpdateState = plugin_mastodon_default_state();
plugin_mastodon_state_set_entry_mapping(
	$commentUpdateState,
	$commentUpdateEntryId,
	'970',
	'local',
	plugin_mastodon_entry_hash($commentUpdateEntryParsed),
	$instanceUrl . '/@flatpress/970',
	'2026-04-05 12:00:00',
	plugin_mastodon_local_item_date_key($commentUpdateEntryParsed, $commentUpdateEntryId),
	'2026-04-05'
);
plugin_mastodon_state_set_comment_mapping(
	$commentUpdateState,
	$commentUpdateEntryId,
	$commentUpdateCommentId,
	'971',
	'local',
	plugin_mastodon_comment_hash($commentUpdateLocalComment),
	$instanceUrl . '/@flatpress/971',
	'2026-04-05 12:01:00',
	'',
	'970',
	plugin_mastodon_local_item_date_key($commentUpdateLocalComment, $commentUpdateCommentId),
	'2026-04-05'
);
$commentUpdateRemote = array(
	'id' => '971',
	'url' => $instanceUrl . '/@flatpress/971',
	'created_at' => '2026-04-05T12:01:00Z',
	'edited_at' => '2026-04-05T12:05:00Z',
	'content' => '<p>Updated remote comment text</p>',
	'visibility' => 'public',
	'account' => array(
		'display_name' => 'Remote Counter Tester',
		'acct' => 'remotecounter@example.test',
		'url' => $instanceUrl . '/@remotecounter'
	)
);
$commentUpdateStateAfter = $commentUpdateState;
$commentUpdateImportedId = plugin_mastodon_import_remote_comment($commentUpdateOptions, $commentUpdateStateAfter, $commentUpdateEntryId, $commentUpdateRemote);
$commentUpdateStored = comment_parse($commentUpdateEntryId, $commentUpdateCommentId);
$allOk = test_result(
	'Remote Mastodon comment updates increment the local-comment update counter instead of the entry counter',
	$commentUpdateImportedId === $commentUpdateCommentId
		&& isset($commentUpdateStored ['content']) && trim((string) $commentUpdateStored ['content']) === 'Updated remote comment text'
		&& isset($commentUpdateStateAfter ['content_stats'] ['updated_local_comments']) && (int) $commentUpdateStateAfter ['content_stats'] ['updated_local_comments'] === 1
		&& isset($commentUpdateStateAfter ['content_stats'] ['updated_entries']) && (int) $commentUpdateStateAfter ['content_stats'] ['updated_entries'] === 0,
	json_encode(array(
		'comment_id' => $commentUpdateImportedId,
		'content_stats' => isset($commentUpdateStateAfter ['content_stats']) ? $commentUpdateStateAfter ['content_stats'] : array(),
		'stored_comment' => $commentUpdateStored
	))
) && $allOk;
plugin_mastodon_save_options($options);

// Admin assignment must expose deletion sync status counters for the admin template.
$adminState = plugin_mastodon_default_state();
$adminState ['last_run'] = '2026-04-01 10:00:00';
$adminState ['last_deletion_run'] = '2026-04-01 10:05:00';
$adminState ['content_stats'] ['imported_entries'] = 7;
$adminState ['content_stats'] ['updated_local_comments'] = 8;
$adminState ['content_stats'] ['exported_comments'] = 6;
$adminState ['deletion_stats'] ['deleted_local_entries'] = 2;
$adminState ['deletion_stats'] ['deleted_local_comments'] = 3;
$adminState ['deletion_stats'] ['deleted_remote_entries'] = 4;
$adminState ['deletion_stats'] ['deleted_remote_comments'] = 5;
plugin_mastodon_state_write($adminState);
$adminSmarty = new SimulateSmartyCollector();
plugin_mastodon_admin_assign($adminSmarty);
$assignedState = isset($adminSmarty->assigned ['mastodon_state']) && is_array($adminSmarty->assigned ['mastodon_state']) ? $adminSmarty->assigned ['mastodon_state'] : array();
$assignedCfg = isset($adminSmarty->assigned ['mastodon_cfg']) && is_array($adminSmarty->assigned ['mastodon_cfg']) ? $adminSmarty->assigned ['mastodon_cfg'] : array();
$expectedLastRunLocal = plugin_mastodon_format_admin_datetime($adminState ['last_run']);
$expectedLastDeletionRunLocal = plugin_mastodon_format_admin_datetime($adminState ['last_deletion_run']);
$allOk = test_result(
	'Admin assignment exposes split sync counters, local admin timestamps, and the deletion-sync option',
	!empty($assignedState)
		&& isset($assignedState ['last_deletion_run'])
		&& $assignedState ['last_deletion_run'] === '2026-04-01 10:05:00'
		&& isset($assignedState ['last_run_local']) && (string) $assignedState ['last_run_local'] === $expectedLastRunLocal
		&& isset($assignedState ['last_deletion_run_local']) && (string) $assignedState ['last_deletion_run_local'] === $expectedLastDeletionRunLocal
		&& isset($assignedCfg ['sync_time']) && (string) $assignedCfg ['sync_time'] === plugin_mastodon_sync_time_utc_to_local(isset($assignedCfg ['sync_time_utc']) ? (string) $assignedCfg ['sync_time_utc'] : '')
		&& isset($assignedCfg ['sync_time_offset_label']) && (string) $assignedCfg ['sync_time_offset_label'] === 'UTC+02:00'
		&& isset($assignedState ['content_stats'] ['imported_entries']) && (int) $assignedState ['content_stats'] ['imported_entries'] === 7
		&& isset($assignedState ['content_stats'] ['updated_local_comments']) && (int) $assignedState ['content_stats'] ['updated_local_comments'] === 8
		&& isset($assignedState ['content_stats'] ['exported_comments']) && (int) $assignedState ['content_stats'] ['exported_comments'] === 6
		&& isset($assignedState ['deletion_stats'] ['deleted_local_entries']) && (int) $assignedState ['deletion_stats'] ['deleted_local_entries'] === 2
		&& isset($assignedState ['deletion_stats'] ['deleted_local_comments']) && (int) $assignedState ['deletion_stats'] ['deleted_local_comments'] === 3
		&& isset($assignedState ['deletion_stats'] ['deleted_remote_entries']) && (int) $assignedState ['deletion_stats'] ['deleted_remote_entries'] === 4
		&& isset($assignedState ['deletion_stats'] ['deleted_remote_comments']) && (int) $assignedState ['deletion_stats'] ['deleted_remote_comments'] === 5
		&& isset($assignedCfg ['sync_scheduled_window_days']) && in_array((string) $assignedCfg ['sync_scheduled_window_days'], array('7', '14', '30'), true)
		&& isset($adminSmarty->assigned ['mastodon_scheduled_window_choices']) && is_array($adminSmarty->assigned ['mastodon_scheduled_window_choices']) && count($adminSmarty->assigned ['mastodon_scheduled_window_choices']) === 3
		&& isset($adminSmarty->assigned ['mastodon_state_maintenance_result']) && is_array($adminSmarty->assigned ['mastodon_state_maintenance_result'])
		&& empty($adminSmarty->assigned ['mastodon_state_maintenance_result'] ['available'])
		&& isset($assignedCfg ['old_thread_reply_check']) && (string) $assignedCfg ['old_thread_reply_check'] === '0'
		&& isset($assignedCfg ['delete_sync_enabled']) && (string) $assignedCfg ['delete_sync_enabled'] === '1',
	json_encode(array(
		'assigned_keys' => array_keys($adminSmarty->assigned),
		'cfg' => $assignedCfg,
		'state' => $assignedState
	))
) && $allOk;

$mainAdminTemplate = @file_get_contents(PLUGIN_MASTODON_DIR . 'tpls/admin.plugin.mastodon.tpl');
$maintenanceAdminTemplate = @file_get_contents(PLUGIN_MASTODON_DIR . 'tpls/admin.plugin.mastodon.maintenance.tpl');
$maintenanceLanguageAliasFiles = glob(PLUGIN_MASTODON_DIR . 'lang/lang.*.php');
$maintenanceLanguageAliasChecked = 0;
$maintenanceLanguageAliasMissing = array();
if (is_array($maintenanceLanguageAliasFiles)) {
	foreach ($maintenanceLanguageAliasFiles as $maintenanceLanguageAliasFile) {
		$maintenanceLanguageAliasChecked++;
		$maintenanceLanguagePluginEntries = simulate_mastodon_read_admin_plugin_language_entries((string) $maintenanceLanguageAliasFile);
		$maintenanceLanguagePanelStrings = (isset($maintenanceLanguagePluginEntries ['mastodon_maintenance']) && is_array($maintenanceLanguagePluginEntries ['mastodon_maintenance']))
			? $maintenanceLanguagePluginEntries ['mastodon_maintenance']
			: array();
		$maintenanceLanguageMessages = (isset($maintenanceLanguagePanelStrings ['msgs']) && is_array($maintenanceLanguagePanelStrings ['msgs']))
			? $maintenanceLanguagePanelStrings ['msgs']
			: array();
		$maintenanceLanguageRequiredKeys = array(
			'state_maintenance_head',
			'state_maintenance_desc',
			'state_maintenance_desc_short',
			'state_maintenance_open',
			'state_maintenance_back',
			'state_maintenance_diagnose',
			'state_maintenance_repair',
			'msgs'
		);
		foreach ($maintenanceLanguageRequiredKeys as $maintenanceLanguageRequiredKey) {
			if (!isset($maintenanceLanguagePanelStrings [$maintenanceLanguageRequiredKey])) {
				$maintenanceLanguageAliasMissing [] = basename((string) $maintenanceLanguageAliasFile) . ':' . $maintenanceLanguageRequiredKey;
			}
		}
		foreach (array(7, 8, -7, -8) as $maintenanceLanguageMessageKey) {
			if (!isset($maintenanceLanguageMessages [$maintenanceLanguageMessageKey])) {
				$maintenanceLanguageAliasMissing [] = basename((string) $maintenanceLanguageAliasFile) . ':msgs[' . $maintenanceLanguageMessageKey . ']';
			}
		}
	}
}
$assignedMastodonLang = isset($adminSmarty->assigned ['mastodon_lang']) && is_array($adminSmarty->assigned ['mastodon_lang']) ? $adminSmarty->assigned ['mastodon_lang'] : array();
$maintenancePanelStrings = (isset($GLOBALS ['lang'] ['admin'] ['plugin'] ['mastodon_maintenance']) && is_array($GLOBALS ['lang'] ['admin'] ['plugin'] ['mastodon_maintenance']))
	? $GLOBALS ['lang'] ['admin'] ['plugin'] ['mastodon_maintenance']
	: array();
$maintenancePanelMessages = (isset($maintenancePanelStrings ['msgs']) && is_array($maintenancePanelStrings ['msgs'])) ? $maintenancePanelStrings ['msgs'] : array();
$requiredMaintenanceLangKeys = array(
	'state_maintenance_head',
	'state_maintenance_desc',
	'state_maintenance_desc_short',
	'state_maintenance_open',
	'state_maintenance_back',
	'state_maintenance_actions_head',
	'state_maintenance_actions_desc',
	'state_maintenance_diagnose',
	'state_maintenance_repair',
	'description',
	'output'
);
$missingMaintenanceLangKeys = array();
foreach ($requiredMaintenanceLangKeys as $maintenanceLangKey) {
	if (!isset($assignedMastodonLang [$maintenanceLangKey]) || !is_string($assignedMastodonLang [$maintenanceLangKey]) || $assignedMastodonLang [$maintenanceLangKey] === '') {
		$missingMaintenanceLangKeys [] = $maintenanceLangKey;
	}
}
$allOk = test_result(
	'Admin maintenance actions are separated into a dedicated template reached from the main plugin page',
	is_string($mainAdminTemplate)
		&& is_string($maintenanceAdminTemplate)
		&& strpos($mainAdminTemplate, 'mastodon_diagnose_state') === false
		&& strpos($mainAdminTemplate, 'mastodon_repair_state') === false
		&& strpos($mainAdminTemplate, 'mastodon_admin_maintenance_url') !== false
		&& strpos($mainAdminTemplate, '$plang.state_maintenance_') === false
		&& strpos($maintenanceAdminTemplate, 'mastodon_diagnose_state') !== false
		&& strpos($maintenanceAdminTemplate, 'mastodon_repair_state') !== false
		&& strpos($maintenanceAdminTemplate, 'mastodon_admin_main_url') !== false
		&& strpos($maintenanceAdminTemplate, '$plang.state_maintenance_') === false
		&& $maintenanceLanguageAliasChecked === 15
		&& $maintenanceLanguageAliasMissing === array()
		&& isset($maintenancePanelStrings ['state_maintenance_head'])
		&& isset($maintenancePanelMessages [7])
		&& isset($maintenancePanelMessages [8])
		&& isset($maintenancePanelMessages [-7])
		&& isset($maintenancePanelMessages [-8])
		&& isset($adminSmarty->assigned ['mastodon_admin_maintenance_url'])
		&& strpos((string) $adminSmarty->assigned ['mastodon_admin_maintenance_url'], 'action=mastodon_maintenance') !== false
		&& strpos((string) $adminSmarty->assigned ['mastodon_admin_maintenance_url'], '&#038;') === false
		&& strpos((string) $adminSmarty->assigned ['mastodon_admin_maintenance_url'], '&amp;') === false
		&& parse_url((string) $adminSmarty->assigned ['mastodon_admin_maintenance_url'], PHP_URL_QUERY) !== null
		&& isset($adminSmarty->assigned ['mastodon_admin_main_url'])
		&& strpos((string) $adminSmarty->assigned ['mastodon_admin_main_url'], 'action=mastodon') !== false
		&& strpos((string) $adminSmarty->assigned ['mastodon_admin_main_url'], '&#038;') === false
		&& strpos((string) $adminSmarty->assigned ['mastodon_admin_main_url'], '&amp;') === false
		&& $missingMaintenanceLangKeys === array(),
	json_encode(array(
		'assigned_main_url' => isset($adminSmarty->assigned ['mastodon_admin_main_url']) ? $adminSmarty->assigned ['mastodon_admin_main_url'] : '',
		'assigned_maintenance_url' => isset($adminSmarty->assigned ['mastodon_admin_maintenance_url']) ? $adminSmarty->assigned ['mastodon_admin_maintenance_url'] : '',
		'main_has_diagnose' => is_string($mainAdminTemplate) ? strpos($mainAdminTemplate, 'mastodon_diagnose_state') !== false : null,
		'maintenance_has_diagnose' => is_string($maintenanceAdminTemplate) ? strpos($maintenanceAdminTemplate, 'mastodon_diagnose_state') !== false : null,
		'missing_lang_keys' => $missingMaintenanceLangKeys,
		'language_alias_checked' => $maintenanceLanguageAliasChecked,
		'language_alias_missing' => $maintenanceLanguageAliasMissing,
		'maintenance_panelstrings_keys' => array_keys($maintenancePanelStrings),
		'maintenance_msg_keys' => array_keys($maintenancePanelMessages)
	))
) && $allOk;

$cleanupImageRelative = 'images/mastodon-cleanup-test.jpg';
$cleanupImageAbsolute = $simRoot . '/fp-content/' . $cleanupImageRelative;
if (!is_dir(dirname($cleanupImageAbsolute))) {
	mkdir(dirname($cleanupImageAbsolute), 0777, true);
}
file_put_contents($cleanupImageAbsolute, 'cleanup-image-binary');
$cleanupMediaItems = array(
	array(
		'relative_path' => $cleanupImageRelative,
		'absolute_path' => $cleanupImageAbsolute,
		'description' => 'Cleanup media test'
	),
	array(
		'relative_path' => $cleanupImageRelative,
		'absolute_path' => $cleanupImageAbsolute,
		'description' => 'Cleanup media test duplicate for forced failure'
	)
);

$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'POST ' . $instanceUrl . '/api/v2/media' => array(
		array(
			'ok' => true,
			'code' => 200,
			'body' => json_encode(array('id' => 'cleanup-upload-1', 'type' => 'image', 'url' => 'https://files.example/cleanup-upload-1.jpg'))
		),
		array(
			'ok' => false,
			'code' => 500,
			'body' => json_encode(array('error' => 'upload_failed')),
			'error' => 'upload_failed'
		)
	),
	'DELETE ' . $instanceUrl . '/api/v1/media/cleanup-upload-1' => array(
		'ok' => true,
		'code' => 200,
		'body' => ''
	)
);
$cleanupUpload = plugin_mastodon_upload_media_items($options, $cleanupMediaItems, 4);
$cleanupUploadDeletes = 0;
if (simulate_recorded_http_requests() !== array()) {
	foreach (simulate_recorded_http_requests() as $request) {
		if (is_array($request) && !empty($request ['method']) && strtoupper((string) $request ['method']) === 'DELETE' && !empty($request ['url']) && (string) $request ['url'] === $instanceUrl . '/api/v1/media/cleanup-upload-1') {
			$cleanupUploadDeletes++;
		}
	}
}
$allOk = test_result(
	'Media upload cleanup deletes already uploaded attachments when a later media upload fails',
	empty($cleanupUpload ['ok']) && $cleanupUploadDeletes === 1,
	json_encode(array('response' => $cleanupUpload, 'delete_requests' => $cleanupUploadDeletes))
) && $allOk;

$cleanupEntry = array(
	'version' => system_ver(),
	'subject' => 'Media cleanup entry',
	'content' => 'Entry with cleanup media [img=' . $cleanupImageRelative . ' title="Cleanup media"]',
	'author' => 'Simulation',
	'date' => strtotime('2026-03-20 10:10:10 UTC')
);
$cleanupEntryId = entry_save($cleanupEntry, null);
$cleanupOptions = $seededOptions;
$cleanupOptions ['sync_start_date'] = '2026-03-20';
$cleanupOptions ['update_local_from_remote'] = '0';
$cleanupState = plugin_mastodon_default_state();
plugin_mastodon_runtime_cache_clear();
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $instanceUrl . '/api/v2/instance' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			'configuration' => array(
				'statuses' => array(
					'max_characters' => 500,
					'max_media_attachments' => 4,
					'characters_reserved_per_url' => 23
				),
				'media_attachments' => array(
					'description_limit' => 1500
				)
			)
		))
	),
	'POST ' . $instanceUrl . '/api/v2/media' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('id' => 'cleanup-final-post-media', 'type' => 'image', 'url' => 'https://files.example/cleanup-final-post-media.jpg'))
	),
	'POST ' . $instanceUrl . '/api/v1/statuses' => array(
		'ok' => false,
		'code' => 500,
		'body' => json_encode(array('error' => 'status_create_failed')),
		'error' => 'status_create_failed'
	),
	'DELETE ' . $instanceUrl . '/api/v1/media/cleanup-final-post-media' => array(
		'ok' => true,
		'code' => 200,
		'body' => ''
	)
);
$cleanupSyncResult = plugin_mastodon_sync_local_to_remote($cleanupOptions, $cleanupState);
$cleanupFinalDeletes = 0;
$cleanupFinalDeleteSeen = false;
if (simulate_recorded_http_requests() !== array()) {
	foreach (simulate_recorded_http_requests() as $request) {
		if (is_array($request) && !empty($request ['method']) && strtoupper((string) $request ['method']) === 'DELETE') {
			$cleanupFinalDeletes++;
			if (!empty($request ['url']) && (string) $request ['url'] === $instanceUrl . '/api/v1/media/cleanup-final-post-media') {
				$cleanupFinalDeleteSeen = true;
			}
		}
	}
}
$allOk = test_result(
	'Media upload cleanup deletes uploaded attachments when final status creation fails',
	empty($cleanupSyncResult) && $cleanupFinalDeleteSeen && $cleanupFinalDeletes >= 1 && strpos((string) $cleanupState ['last_error'], 'local_entry_export_failed: ') === 0,
	json_encode(array('sync_result' => $cleanupSyncResult, 'delete_requests' => $cleanupFinalDeletes, 'target_delete_seen' => $cleanupFinalDeleteSeen, 'last_error' => isset($cleanupState ['last_error']) ? $cleanupState ['last_error'] : ''))
) && $allOk;

// OAuth scope discovery regression tests
$oauthProfileScopes = plugin_mastodon_oauth_profile_scopes();
$oauthLegacyScopes = plugin_mastodon_oauth_legacy_scopes();
$oauthCompatibleScopes = plugin_mastodon_oauth_compatible_scopes();

$oauthOptions = simulate_seed_options_from_config(plugin_mastodon_get_options());
$oauthOptions ['instance_url'] = $instanceUrl;
$oauthOptions ['client_id'] = '';
$oauthOptions ['client_secret'] = '';
$oauthOptions ['oauth_registered_scopes'] = '';
$oauthOptions ['access_token'] = '';
plugin_mastodon_runtime_cache_clear('oauth_server_metadata');
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $instanceUrl . '/.well-known/oauth-authorization-server' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			'scopes_supported' => array('profile', 'read:statuses', 'write:statuses', 'write:media')
		))
	),
	'POST ' . $instanceUrl . '/api/v1/apps' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('client_id' => 'client-profile', 'client_secret' => 'secret-profile'))
	),
	'POST ' . $instanceUrl . '/oauth/token' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('access_token' => 'token-profile'))
	)
);
$oauthRegisterResponse = plugin_mastodon_register_app($oauthOptions);
$oauthAuthorizeUrl = plugin_mastodon_build_authorize_url($oauthOptions);
$oauthExchangeResponse = plugin_mastodon_exchange_code_for_token($oauthOptions, 'code-profile');
$oauthRegisterScopes = '';
$oauthTokenScopes = '';
foreach (simulate_recorded_http_requests() as $request) {
	if (!is_array($request) || empty($request ['url'])) {
		continue;
	}
	if ((string) $request ['url'] === $instanceUrl . '/api/v1/apps') {
		parse_str((string) $request ['body'], $oauthRegisterParams);
		$oauthRegisterScopes = isset($oauthRegisterParams ['scopes']) ? (string) $oauthRegisterParams ['scopes'] : '';
	}
	if ((string) $request ['url'] === $instanceUrl . '/oauth/token') {
		parse_str((string) $request ['body'], $oauthTokenParams);
		$oauthTokenScopes = isset($oauthTokenParams ['scope']) ? (string) $oauthTokenParams ['scope'] : '';
	}
}
$oauthAuthorizeQuery = array();
parse_str((string) parse_url($oauthAuthorizeUrl, PHP_URL_QUERY), $oauthAuthorizeQuery);
$allOk = test_result(
	'OAuth scope discovery prefers the profile scope on current Mastodon instances',
	$oauthRegisterResponse ['ok']
		&& $oauthExchangeResponse ['ok']
		&& $oauthRegisterScopes === $oauthProfileScopes
		&& isset($oauthAuthorizeQuery ['scope']) && (string) $oauthAuthorizeQuery ['scope'] === $oauthProfileScopes
		&& $oauthTokenScopes === $oauthProfileScopes
		&& isset($oauthOptions ['oauth_registered_scopes']) && (string) $oauthOptions ['oauth_registered_scopes'] === $oauthProfileScopes,
	json_encode(array(
		'register_scopes' => $oauthRegisterScopes,
		'authorize_scope' => isset($oauthAuthorizeQuery ['scope']) ? $oauthAuthorizeQuery ['scope'] : '',
		'token_scope' => $oauthTokenScopes,
		'stored_scope' => isset($oauthOptions ['oauth_registered_scopes']) ? $oauthOptions ['oauth_registered_scopes'] : ''
	))
) && $allOk;

$legacyOAuthOptions = simulate_seed_options_from_config(plugin_mastodon_get_options());
$legacyOAuthOptions ['instance_url'] = $instanceUrl;
$legacyOAuthOptions ['client_id'] = '';
$legacyOAuthOptions ['client_secret'] = '';
$legacyOAuthOptions ['oauth_registered_scopes'] = '';
plugin_mastodon_runtime_cache_clear('oauth_server_metadata');
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $instanceUrl . '/.well-known/oauth-authorization-server' => array(
		'ok' => false,
		'code' => 404,
		'body' => json_encode(array('error' => 'Not Found'))
	),
	'POST ' . $instanceUrl . '/api/v1/apps' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('client_id' => 'client-legacy', 'client_secret' => 'secret-legacy'))
	)
);
$legacyRegisterResponse = plugin_mastodon_register_app($legacyOAuthOptions);
$legacyAuthorizeUrl = plugin_mastodon_build_authorize_url($legacyOAuthOptions);
$legacyRegisterScopes = '';
foreach (simulate_recorded_http_requests() as $request) {
	if (is_array($request) && !empty($request ['url']) && (string) $request ['url'] === $instanceUrl . '/api/v1/apps') {
		parse_str((string) $request ['body'], $legacyRegisterParams);
		$legacyRegisterScopes = isset($legacyRegisterParams ['scopes']) ? (string) $legacyRegisterParams ['scopes'] : '';
	}
}
$legacyAuthorizeQuery = array();
parse_str((string) parse_url($legacyAuthorizeUrl, PHP_URL_QUERY), $legacyAuthorizeQuery);
$allOk = test_result(
	'OAuth scope discovery falls back to read:accounts plus notifications on older Mastodon instances',
	$legacyRegisterResponse ['ok']
		&& $legacyRegisterScopes === $oauthCompatibleScopes
		&& isset($legacyAuthorizeQuery ['scope']) && (string) $legacyAuthorizeQuery ['scope'] === $oauthCompatibleScopes
		&& isset($legacyOAuthOptions ['oauth_registered_scopes']) && (string) $legacyOAuthOptions ['oauth_registered_scopes'] === $oauthCompatibleScopes,
	json_encode(array(
		'register_scopes' => $legacyRegisterScopes,
		'authorize_scope' => isset($legacyAuthorizeQuery ['scope']) ? $legacyAuthorizeQuery ['scope'] : '',
		'stored_scope' => isset($legacyOAuthOptions ['oauth_registered_scopes']) ? $legacyOAuthOptions ['oauth_registered_scopes'] : ''
	))
) && $allOk;

$existingLegacyOptions = simulate_seed_options_from_config(plugin_mastodon_get_options());
$existingLegacyOptions ['instance_url'] = $instanceUrl;
$existingLegacyOptions ['client_id'] = 'existing-legacy-client';
$existingLegacyOptions ['client_secret'] = 'existing-legacy-secret';
$existingLegacyOptions ['oauth_registered_scopes'] = '';
plugin_mastodon_runtime_cache_clear('oauth_server_metadata');
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $instanceUrl . '/.well-known/oauth-authorization-server' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array(
			'scopes_supported' => array('profile', 'read:statuses', 'write:statuses', 'write:media')
		))
	),
	'POST ' . $instanceUrl . '/oauth/token' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('access_token' => 'token-legacy-existing'))
	)
);
$existingLegacyAuthorizeUrl = plugin_mastodon_build_authorize_url($existingLegacyOptions);
$existingLegacyAuthorizeQuery = array();
parse_str((string) parse_url($existingLegacyAuthorizeUrl, PHP_URL_QUERY), $existingLegacyAuthorizeQuery);
$existingLegacyExchangeResponse = plugin_mastodon_exchange_code_for_token($existingLegacyOptions, 'legacy-code');
$existingLegacyTokenScopes = '';
$existingLegacyDiscoveryRequests = 0;
foreach (simulate_recorded_http_requests() as $request) {
	if (!is_array($request) || empty($request ['url'])) {
		continue;
	}
	if ((string) $request ['url'] === $instanceUrl . '/oauth/token') {
		parse_str((string) $request ['body'], $existingLegacyTokenParams);
		$existingLegacyTokenScopes = isset($existingLegacyTokenParams ['scope']) ? (string) $existingLegacyTokenParams ['scope'] : '';
	}
	if ((string) $request ['url'] === $instanceUrl . '/.well-known/oauth-authorization-server') {
		$existingLegacyDiscoveryRequests++;
	}
}
$allOk = test_result(
	'Existing registered apps keep the legacy read:accounts scope until they are re-registered',
	$existingLegacyExchangeResponse ['ok']
		&& isset($existingLegacyAuthorizeQuery ['scope']) && (string) $existingLegacyAuthorizeQuery ['scope'] === $oauthLegacyScopes
		&& $existingLegacyTokenScopes === $oauthLegacyScopes
		&& $existingLegacyDiscoveryRequests === 0,
	json_encode(array(
		'authorize_scope' => isset($existingLegacyAuthorizeQuery ['scope']) ? $existingLegacyAuthorizeQuery ['scope'] : '',
		'token_scope' => $existingLegacyTokenScopes,
		'discovery_requests' => $existingLegacyDiscoveryRequests
	))
) && $allOk;

$instanceInfoOptions = simulate_seed_options_from_config(plugin_mastodon_get_options());
$instanceInfoOptions ['instance_url'] = $instanceUrl;
$instanceInfoOptions ['access_token'] = 'token-instance-info';
$instanceInfoOptions = plugin_mastodon_clear_saved_instance_info($instanceInfoOptions);
plugin_mastodon_save_options($instanceInfoOptions);
plugin_mastodon_runtime_cache_clear();

$instanceInfoDocument = array(
	'domain' => 'mastodon.example',
	'title' => 'Example Mastodon',
	'version' => '4.5.0+flatpress-test',
	'source_url' => 'https://github.com/mastodon/mastodon',
	'description' => 'Test instance used by the FlatPress simulation.',
	'languages' => array('de', 'en'),
	'usage' => array(
		'users' => array(
			'active_month' => 321
		)
	),
	'api_versions' => array(
		'mastodon' => 7
	),
	'contact' => array(
		'email' => 'admin@example.test',
		'account' => array(
			'acct' => 'admin@example.test',
			'url' => $instanceUrl . '/@admin'
		)
	),
	'registrations' => array(
		'enabled' => true,
		'approval_required' => false,
		'url' => $instanceUrl . '/auth/sign_up'
	),
	'rules' => array(
		array('id' => '1', 'text' => 'Be kind'),
		array('id' => '2', 'text' => 'Use alt text')
	),
	'configuration' => array(
		'urls' => array(
			'streaming' => 'wss://mastodon.example',
			'status' => $instanceUrl . '/about/status',
			'about' => $instanceUrl . '/about',
			'privacy_policy' => $instanceUrl . '/privacy-policy',
			'terms_of_service' => $instanceUrl . '/terms'
		),
		'statuses' => array(
			'max_characters' => 1234,
			'max_media_attachments' => 8,
			'characters_reserved_per_url' => 30
		),
		'media_attachments' => array(
			'description_limit' => 2048,
			'supported_mime_types' => array('image/jpeg', 'image/png')
		),
		'translation' => array(
			'enabled' => true
		)
	)
);

$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $instanceUrl . '/api/v2/instance' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode($instanceInfoDocument)
	)
);
$instanceInfoRefresh = plugin_mastodon_refresh_instance_information($instanceInfoOptions);
$storedInstanceOptions = plugin_mastodon_get_options();
$storedInstanceDocument = plugin_mastodon_instance_document($storedInstanceOptions, false);
$instanceInfoRefreshCalls = 0;
foreach (simulate_recorded_http_requests() as $request) {
	if (is_array($request) && !empty($request ['url']) && (string) $request ['url'] === $instanceUrl . '/api/v2/instance') {
		$instanceInfoRefreshCalls++;
	}
}
$allOk = test_result(
	'Instance information refresh persists a compact snapshot including the exact Mastodon version',
	!empty($instanceInfoRefresh ['ok'])
		&& $instanceInfoRefreshCalls === 1
		&& isset($storedInstanceOptions ['instance_info_json']) && trim((string) $storedInstanceOptions ['instance_info_json']) !== ''
		&& isset($storedInstanceDocument ['version']) && (string) $storedInstanceDocument ['version'] === '4.5.0+flatpress-test'
		&& isset($storedInstanceDocument ['api_versions'] ['mastodon']) && (int) $storedInstanceDocument ['api_versions'] ['mastodon'] === 7
		&& isset($storedInstanceDocument ['configuration'] ['statuses'] ['max_characters']) && (int) $storedInstanceDocument ['configuration'] ['statuses'] ['max_characters'] === 1234
		&& isset($storedInstanceDocument ['configuration'] ['media_attachments'] ['description_limit']) && (int) $storedInstanceDocument ['configuration'] ['media_attachments'] ['description_limit'] === 2048,
	json_encode(array(
		'refresh_response' => $instanceInfoRefresh,
		'refresh_calls' => $instanceInfoRefreshCalls,
		'stored_options' => array(
			'instance_info_url' => isset($storedInstanceOptions ['instance_info_url']) ? $storedInstanceOptions ['instance_info_url'] : '',
			'instance_info_fetched_at' => isset($storedInstanceOptions ['instance_info_fetched_at']) ? $storedInstanceOptions ['instance_info_fetched_at'] : '',
			'instance_info_json_length' => isset($storedInstanceOptions ['instance_info_json']) ? strlen((string) $storedInstanceOptions ['instance_info_json']) : 0
		),
		'stored_document' => $storedInstanceDocument
	))
) && $allOk;

plugin_mastodon_runtime_cache_clear();
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array();
$adminInstanceSmarty = new SimulateSmartyCollector();
plugin_mastodon_admin_assign($adminInstanceSmarty);
$adminInstanceRows = isset($adminInstanceSmarty->assigned ['mastodon_instance_info_rows']) && is_array($adminInstanceSmarty->assigned ['mastodon_instance_info_rows']) ? $adminInstanceSmarty->assigned ['mastodon_instance_info_rows'] : array();
$adminInstanceVersion = '';
$adminInstanceCacheState = '';
foreach ($adminInstanceRows as $row) {
	if (!is_array($row) || empty($row ['label'])) {
		continue;
	}
	if ((string) $row ['label'] === plugin_mastodon_lang_string('instance_info_version', 'Exact version')) {
		$adminInstanceVersion = isset($row ['value']) ? (string) $row ['value'] : '';
	}
	if ((string) $row ['label'] === plugin_mastodon_lang_string('instance_info_cache_state', 'Cache state')) {
		$adminInstanceCacheState = isset($row ['value']) ? (string) $row ['value'] : '';
	}
}
$adminInstanceCalls = 0;
foreach (simulate_recorded_http_requests() as $request) {
	if (is_array($request) && !empty($request ['url']) && (string) $request ['url'] === $instanceUrl . '/api/v2/instance') {
		$adminInstanceCalls++;
	}
}
$allOk = test_result(
	'Admin assignment exposes cached instance-information rows without triggering another live instance request',
	!empty($adminInstanceSmarty->assigned ['mastodon_instance_info_available'])
		&& $adminInstanceVersion === '4.5.0+flatpress-test'
		&& $adminInstanceCacheState === plugin_mastodon_lang_string('instance_info_cache_state_cached', 'Saved snapshot available')
		&& $adminInstanceCalls === 0,
	json_encode(array(
		'rows' => $adminInstanceRows,
		'instance_calls' => $adminInstanceCalls,
		'assigned_keys' => array_keys($adminInstanceSmarty->assigned)
	))
) && $allOk;

$instanceUrlChangeOptions = $storedInstanceOptions;
$instanceUrlChangeOptions ['instance_url'] = 'https://other-instance.example';
plugin_mastodon_save_options($instanceUrlChangeOptions);
$changedInstanceOptions = plugin_mastodon_get_options();
$allOk = test_result(
	'Changing the configured instance URL invalidates the saved instance-information snapshot',
	isset($changedInstanceOptions ['instance_info_json']) && (string) $changedInstanceOptions ['instance_info_json'] === ''
		&& isset($changedInstanceOptions ['instance_info_url']) && (string) $changedInstanceOptions ['instance_info_url'] === '',
	json_encode(array(
		'instance_url' => isset($changedInstanceOptions ['instance_url']) ? $changedInstanceOptions ['instance_url'] : '',
		'instance_info_url' => isset($changedInstanceOptions ['instance_info_url']) ? $changedInstanceOptions ['instance_info_url'] : '',
		'instance_info_json' => isset($changedInstanceOptions ['instance_info_json']) ? $changedInstanceOptions ['instance_info_json'] : ''
	))
) && $allOk;

$syncCachedOptions = plugin_mastodon_clear_saved_instance_info(simulate_seed_options_from_config(plugin_mastodon_get_options()));
$syncCachedOptions ['instance_url'] = $instanceUrl;
$syncCachedOptions ['access_token'] = 'token-sync-cached-instance';
$syncCachedOptions ['sync_start_date'] = '2026-12-31';
plugin_mastodon_store_instance_document($syncCachedOptions, $instanceInfoDocument);
$syncCachedOptions = plugin_mastodon_get_options();
$syncCachedState = plugin_mastodon_default_state();
$syncCachedEntry = array(
	'version' => system_ver(),
	'subject' => 'Cached instance snapshot export',
	'content' => 'This entry verifies that sync-related instance helper calls can reuse stored instance information without another /api/v2/instance request.',
	'author' => 'Simulation',
	'date' => strtotime('2026-12-31 12:34:56 UTC')
);
plugin_mastodon_runtime_cache_clear();
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array();
$syncCachedCharLimit = plugin_mastodon_instance_character_limit($syncCachedOptions);
$syncCachedMediaLimit = plugin_mastodon_instance_media_limit($syncCachedOptions);
$syncCachedUrlReservedLength = plugin_mastodon_instance_url_reserved_length($syncCachedOptions);
$syncCachedStatusText = plugin_mastodon_build_entry_status_text('entry261231-123456', $syncCachedEntry, 2000);
$syncCachedInstanceCalls = 0;
foreach (simulate_recorded_http_requests() as $request) {
	if (is_array($request) && !empty($request ['url']) && (string) $request ['url'] === $instanceUrl . '/api/v2/instance') {
		$syncCachedInstanceCalls++;
	}
}
$allOk = test_result(
	'Sync-related instance limit helpers reuse the stored instance snapshot without another /api/v2/instance request',
	$syncCachedCharLimit === 1234
		&& $syncCachedMediaLimit === 8
		&& $syncCachedUrlReservedLength === 30
		&& $syncCachedInstanceCalls === 0
		&& strpos($syncCachedStatusText, 'Cached instance snapshot export') !== false,
	json_encode(array(
		'char_limit' => $syncCachedCharLimit,
		'media_limit' => $syncCachedMediaLimit,
		'url_reserved_length' => $syncCachedUrlReservedLength,
		'instance_calls' => $syncCachedInstanceCalls,
		'status_text' => $syncCachedStatusText
	))
) && $allOk;

$remoteEntryManualCommentExport = simulate_run_remote_entry_local_comment_export_case(true);
$remoteEntryManualParentMeta = isset($remoteEntryManualCommentExport ['parent_meta']) && is_array($remoteEntryManualCommentExport ['parent_meta']) ? $remoteEntryManualCommentExport ['parent_meta'] : array();
$remoteEntryManualChildMeta = isset($remoteEntryManualCommentExport ['child_meta']) && is_array($remoteEntryManualCommentExport ['child_meta']) ? $remoteEntryManualCommentExport ['child_meta'] : array();
$remoteEntryManualParentRequest = isset($remoteEntryManualCommentExport ['parent_request']) && is_array($remoteEntryManualCommentExport ['parent_request']) ? $remoteEntryManualCommentExport ['parent_request'] : array();
$remoteEntryManualChildRequest = isset($remoteEntryManualCommentExport ['child_request']) && is_array($remoteEntryManualCommentExport ['child_request']) ? $remoteEntryManualCommentExport ['child_request'] : array();
$remoteEntryManualEntryRequest = isset($remoteEntryManualCommentExport ['entry_request']) && is_array($remoteEntryManualCommentExport ['entry_request']) ? $remoteEntryManualCommentExport ['entry_request'] : array();
$allOk = test_result(
	'Manual sync exports FlatPress comments on remote-sourced entries to Mastodon replies',
	!empty($remoteEntryManualCommentExport ['result'] ['ok'])
		&& $remoteEntryManualEntryRequest === array()
		&& !empty($remoteEntryManualParentMeta ['remote_id'])
		&& $remoteEntryManualParentMeta ['remote_id'] === '931'
		&& isset($remoteEntryManualParentRequest ['in_reply_to_id'])
		&& (string) $remoteEntryManualParentRequest ['in_reply_to_id'] === '930'
		&& isset($remoteEntryManualCommentExport ['state'] ['content_stats'] ['exported_comments'])
		&& (int) $remoteEntryManualCommentExport ['state'] ['content_stats'] ['exported_comments'] === 2,
	json_encode(array(
		'parent_meta' => $remoteEntryManualParentMeta,
		'parent_request' => $remoteEntryManualParentRequest,
		'entry_request' => $remoteEntryManualEntryRequest,
		'content_stats' => isset($remoteEntryManualCommentExport ['state'] ['content_stats']) ? $remoteEntryManualCommentExport ['state'] ['content_stats'] : array()
	))
) && $allOk;

$allOk = test_result(
	'Replies to FlatPress comments on remote-sourced entries are exported as replies to the parent Mastodon reply',
	!empty($remoteEntryManualChildMeta ['remote_id'])
		&& $remoteEntryManualChildMeta ['remote_id'] === '932'
		&& isset($remoteEntryManualChildRequest ['in_reply_to_id'])
		&& (string) $remoteEntryManualChildRequest ['in_reply_to_id'] === '931',
	json_encode(array(
		'child_meta' => $remoteEntryManualChildMeta,
		'child_request' => $remoteEntryManualChildRequest
	))
) && $allOk;

$remoteEntryManualParentRequestIndex = -1;
$remoteEntryManualChildRequestIndex = -1;
if (!empty($remoteEntryManualCommentExport ['http_requests']) && is_array($remoteEntryManualCommentExport ['http_requests'])) {
	foreach ($remoteEntryManualCommentExport ['http_requests'] as $requestIndex => $request) {
		if (!is_array($request) || empty($request ['method']) || empty($request ['url']) || strtoupper((string) $request ['method']) !== 'POST' || strpos((string) $request ['url'], '/api/v1/statuses') === false) {
			continue;
		}
		$parsed = simulate_parse_http_request_body($request);
		$statusText = isset($parsed ['status']) ? (string) $parsed ['status'] : '';
		if ($remoteEntryManualParentRequestIndex < 0 && strpos($statusText, 'Remote-thread parent comment body') !== false) {
			$remoteEntryManualParentRequestIndex = (int) $requestIndex;
		}
		if ($remoteEntryManualChildRequestIndex < 0 && strpos($statusText, 'Remote-thread child comment body') !== false) {
			$remoteEntryManualChildRequestIndex = (int) $requestIndex;
		}
	}
}

$allOk = test_result(
	'Unsynchronized local parent comment is exported before local child reply',
	$remoteEntryManualParentRequestIndex >= 0
		&& $remoteEntryManualChildRequestIndex > $remoteEntryManualParentRequestIndex
		&& !empty($remoteEntryManualParentMeta ['remote_id'])
		&& !empty($remoteEntryManualChildMeta ['remote_id'])
		&& isset($remoteEntryManualChildRequest ['in_reply_to_id'])
		&& (string) $remoteEntryManualChildRequest ['in_reply_to_id'] === (string) $remoteEntryManualParentMeta ['remote_id'],
	json_encode(array(
		'parent_request_index' => $remoteEntryManualParentRequestIndex,
		'child_request_index' => $remoteEntryManualChildRequestIndex,
		'parent_meta' => $remoteEntryManualParentMeta,
		'child_request' => $remoteEntryManualChildRequest
	))
) && $allOk;

$remoteEntryScheduledCommentExport = simulate_run_remote_entry_local_comment_export_case(false);
$remoteEntryScheduledParentMeta = isset($remoteEntryScheduledCommentExport ['parent_meta']) && is_array($remoteEntryScheduledCommentExport ['parent_meta']) ? $remoteEntryScheduledCommentExport ['parent_meta'] : array();
$remoteEntryScheduledParentRequest = isset($remoteEntryScheduledCommentExport ['parent_request']) && is_array($remoteEntryScheduledCommentExport ['parent_request']) ? $remoteEntryScheduledCommentExport ['parent_request'] : array();
$remoteEntryScheduledEntryRequest = isset($remoteEntryScheduledCommentExport ['entry_request']) && is_array($remoteEntryScheduledCommentExport ['entry_request']) ? $remoteEntryScheduledCommentExport ['entry_request'] : array();
$allOk = test_result(
	'Non-forced sync exports FlatPress comments on remote-sourced entries to Mastodon replies',
	!empty($remoteEntryScheduledCommentExport ['result'] ['ok'])
		&& $remoteEntryScheduledEntryRequest === array()
		&& !empty($remoteEntryScheduledParentMeta ['remote_id'])
		&& $remoteEntryScheduledParentMeta ['remote_id'] === '931'
		&& isset($remoteEntryScheduledParentRequest ['in_reply_to_id'])
		&& (string) $remoteEntryScheduledParentRequest ['in_reply_to_id'] === '930'
		&& isset($remoteEntryScheduledCommentExport ['state'] ['content_stats'] ['exported_comments'])
		&& (int) $remoteEntryScheduledCommentExport ['state'] ['content_stats'] ['exported_comments'] === 2,
	json_encode(array(
		'parent_meta' => $remoteEntryScheduledParentMeta,
		'parent_request' => $remoteEntryScheduledParentRequest,
		'entry_request' => $remoteEntryScheduledEntryRequest,
		'content_stats' => isset($remoteEntryScheduledCommentExport ['state'] ['content_stats']) ? $remoteEntryScheduledCommentExport ['state'] ['content_stats'] : array()
	))
) && $allOk;

$emoticonExportSyncCase = simulate_run_emoticon_export_sync_case(true);
$emoticonExportEntryMeta = isset($emoticonExportSyncCase ['entry_meta']) && is_array($emoticonExportSyncCase ['entry_meta']) ? $emoticonExportSyncCase ['entry_meta'] : array();
$emoticonExportCommentMeta = isset($emoticonExportSyncCase ['comment_meta']) && is_array($emoticonExportSyncCase ['comment_meta']) ? $emoticonExportSyncCase ['comment_meta'] : array();
$emoticonExportEntryRequest = isset($emoticonExportSyncCase ['entry_request']) && is_array($emoticonExportSyncCase ['entry_request']) ? $emoticonExportSyncCase ['entry_request'] : array();
$emoticonExportCommentRequest = isset($emoticonExportSyncCase ['comment_request']) && is_array($emoticonExportSyncCase ['comment_request']) ? $emoticonExportSyncCase ['comment_request'] : array();
$allOk = test_result(
	'Manual sync exports Emoticons plugin shortcodes from FlatPress entries to Unicode emoji in Mastodon status requests',
	!empty($emoticonExportSyncCase ['result'] ['ok'])
		&& !empty($emoticonExportEntryMeta ['remote_id'])
		&& (string) $emoticonExportEntryMeta ['remote_id'] === '991'
		&& isset($emoticonExportEntryRequest ['status'])
		&& strpos((string) $emoticonExportEntryRequest ['status'], 'Emoji title 😄') !== false
		&& strpos((string) $emoticonExportEntryRequest ['status'], 'Entry body 😉') !== false
		&& strpos((string) $emoticonExportEntryRequest ['status'], ':smile:') === false
		&& strpos((string) $emoticonExportEntryRequest ['status'], ':wink:') === false,
	json_encode(array(
		'entry_meta' => $emoticonExportEntryMeta,
		'entry_request' => $emoticonExportEntryRequest
	))
) && $allOk;

$allOk = test_result(
	'Manual sync exports Emoticons plugin shortcodes from FlatPress comments to Unicode emoji in Mastodon reply requests',
	!empty($emoticonExportCommentMeta ['remote_id'])
		&& (string) $emoticonExportCommentMeta ['remote_id'] === '992'
		&& isset($emoticonExportCommentRequest ['in_reply_to_id'])
		&& (string) $emoticonExportCommentRequest ['in_reply_to_id'] === '991'
		&& isset($emoticonExportCommentRequest ['status'])
		&& strpos((string) $emoticonExportCommentRequest ['status'], 'Emoji author 😁') !== false
		&& strpos((string) $emoticonExportCommentRequest ['status'], 'Comment body 😂') !== false
		&& strpos((string) $emoticonExportCommentRequest ['status'], ':grin:') === false
		&& strpos((string) $emoticonExportCommentRequest ['status'], ':joy:') === false,
	json_encode(array(
		'comment_meta' => $emoticonExportCommentMeta,
		'comment_request' => $emoticonExportCommentRequest
	))
) && $allOk;

$externalReplyDisabledCase = simulate_run_exported_comment_external_reply_import_case(true, false);
$externalReplyDisabledComment = isset($externalReplyDisabledCase ['imported_comment']) && is_array($externalReplyDisabledCase ['imported_comment']) ? $externalReplyDisabledCase ['imported_comment'] : array();
$externalReplyDisabledCommentRef = isset($externalReplyDisabledCase ['imported_comment_ref']) && is_array($externalReplyDisabledCase ['imported_comment_ref']) ? $externalReplyDisabledCase ['imported_comment_ref'] : array();
$externalReplyDisabledState = isset($externalReplyDisabledCase ['state']) && is_array($externalReplyDisabledCase ['state']) ? $externalReplyDisabledCase ['state'] : array();
$allOk = test_result(
	'Manual sync imports another Mastodon member reply to an exported FlatPress comment only as a FlatPress comment when comment-as-entry import is disabled',
	!empty($externalReplyDisabledCase ['result'] ['ok'])
		&& empty($externalReplyDisabledCase ['imported_entry_id'])
		&& !empty($externalReplyDisabledCommentRef ['comment_id'])
		&& !empty($externalReplyDisabledComment ['replyto'])
		&& (string) $externalReplyDisabledComment ['replyto'] === (string) $externalReplyDisabledCase ['local_comment_id']
		&& isset($externalReplyDisabledComment ['name']) && trim((string) $externalReplyDisabledComment ['name']) === 'Alice Example'
		&& isset($externalReplyDisabledComment ['url']) && trim((string) $externalReplyDisabledComment ['url']) === 'https://example.net/@alice'
		&& isset($externalReplyDisabledComment ['content']) && strpos((string) $externalReplyDisabledComment ['content'], simulate_import_quote_block('FlatPress Local', 'Previously exported FlatPress comment for external reply import')) === 0
		&& strpos((string) $externalReplyDisabledComment ['content'], 'Remote reply from another Mastodon account to the exported FlatPress comment') !== false
		&& isset($externalReplyDisabledState ['content_stats'] ['imported_entries']) && (int) $externalReplyDisabledState ['content_stats'] ['imported_entries'] === 0
		&& isset($externalReplyDisabledState ['content_stats'] ['imported_comments']) && (int) $externalReplyDisabledState ['content_stats'] ['imported_comments'] === 1,
	json_encode(array(
		'imported_entry_id' => isset($externalReplyDisabledCase ['imported_entry_id']) ? $externalReplyDisabledCase ['imported_entry_id'] : '',
		'imported_comment_ref' => $externalReplyDisabledCommentRef,
		'imported_comment' => $externalReplyDisabledComment,
		'content_stats' => isset($externalReplyDisabledState ['content_stats']) ? $externalReplyDisabledState ['content_stats'] : array()
	))
) && $allOk;

plugin_mastodon_sync_guard_clear('content');
$externalReplyScheduledCase = simulate_run_exported_comment_external_reply_import_case(false, false);
$externalReplyScheduledComment = isset($externalReplyScheduledCase ['imported_comment']) && is_array($externalReplyScheduledCase ['imported_comment']) ? $externalReplyScheduledCase ['imported_comment'] : array();
$externalReplyScheduledCommentRef = isset($externalReplyScheduledCase ['imported_comment_ref']) && is_array($externalReplyScheduledCase ['imported_comment_ref']) ? $externalReplyScheduledCase ['imported_comment_ref'] : array();
$externalReplyScheduledState = isset($externalReplyScheduledCase ['state']) && is_array($externalReplyScheduledCase ['state']) ? $externalReplyScheduledCase ['state'] : array();
$allOk = test_result(
	'Non-forced sync imports another Mastodon member reply to an exported FlatPress comment only as a FlatPress comment when comment-as-entry import is disabled',
	!empty($externalReplyScheduledCase ['result'] ['ok'])
		&& empty($externalReplyScheduledCase ['imported_entry_id'])
		&& !empty($externalReplyScheduledCommentRef ['comment_id'])
		&& !empty($externalReplyScheduledComment ['replyto'])
		&& (string) $externalReplyScheduledComment ['replyto'] === (string) $externalReplyScheduledCase ['local_comment_id']
		&& isset($externalReplyScheduledComment ['name']) && trim((string) $externalReplyScheduledComment ['name']) === 'Alice Example'
		&& isset($externalReplyScheduledComment ['content']) && strpos((string) $externalReplyScheduledComment ['content'], simulate_import_quote_block('FlatPress Local', 'Previously exported FlatPress comment for external reply import')) === 0
		&& strpos((string) $externalReplyScheduledComment ['content'], 'Remote reply from another Mastodon account to the exported FlatPress comment') !== false
		&& isset($externalReplyScheduledState ['content_stats'] ['imported_entries']) && (int) $externalReplyScheduledState ['content_stats'] ['imported_entries'] === 0
		&& isset($externalReplyScheduledState ['content_stats'] ['imported_comments']) && (int) $externalReplyScheduledState ['content_stats'] ['imported_comments'] === 1,
	json_encode(array(
		'imported_entry_id' => isset($externalReplyScheduledCase ['imported_entry_id']) ? $externalReplyScheduledCase ['imported_entry_id'] : '',
		'imported_comment_ref' => $externalReplyScheduledCommentRef,
		'imported_comment' => $externalReplyScheduledComment,
		'content_stats' => isset($externalReplyScheduledState ['content_stats']) ? $externalReplyScheduledState ['content_stats'] : array()
	))
) && $allOk;

$externalReplyEnabledCase = simulate_run_exported_comment_external_reply_import_case(true, true);
$externalReplyEnabledComment = isset($externalReplyEnabledCase ['imported_comment']) && is_array($externalReplyEnabledCase ['imported_comment']) ? $externalReplyEnabledCase ['imported_comment'] : array();
$externalReplyEnabledState = isset($externalReplyEnabledCase ['state']) && is_array($externalReplyEnabledCase ['state']) ? $externalReplyEnabledCase ['state'] : array();
$allOk = test_result(
	'Another Mastodon member reply to an exported FlatPress comment is still imported as a FlatPress comment when comment-as-entry import is enabled',
	!empty($externalReplyEnabledCase ['result'] ['ok'])
		&& !empty($externalReplyEnabledCase ['imported_comment_ref'] ['comment_id'])
		&& !empty($externalReplyEnabledComment ['replyto'])
		&& (string) $externalReplyEnabledComment ['replyto'] === (string) $externalReplyEnabledCase ['local_comment_id']
		&& isset($externalReplyEnabledState ['content_stats'] ['imported_comments']) && (int) $externalReplyEnabledState ['content_stats'] ['imported_comments'] === 1,
	json_encode(array(
		'imported_entry_id' => isset($externalReplyEnabledCase ['imported_entry_id']) ? $externalReplyEnabledCase ['imported_entry_id'] : '',
		'imported_comment_ref' => isset($externalReplyEnabledCase ['imported_comment_ref']) ? $externalReplyEnabledCase ['imported_comment_ref'] : array(),
		'imported_comment' => $externalReplyEnabledComment,
		'content_stats' => isset($externalReplyEnabledState ['content_stats']) ? $externalReplyEnabledState ['content_stats'] : array()
	))
) && $allOk;

$externalReplyQuoteDisabledCase = simulate_run_exported_comment_external_reply_import_case(true, false, false);
$externalReplyQuoteDisabledComment = isset($externalReplyQuoteDisabledCase ['imported_comment']) && is_array($externalReplyQuoteDisabledCase ['imported_comment']) ? $externalReplyQuoteDisabledCase ['imported_comment'] : array();
$allOk = test_result(
	'Disabling the quote option imports a Mastodon reply to an exported FlatPress comment without a leading quote block',
	!empty($externalReplyQuoteDisabledCase ['result'] ['ok'])
		&& isset($externalReplyQuoteDisabledComment ['content'])
		&& trim((string) $externalReplyQuoteDisabledComment ['content']) === 'Remote reply from another Mastodon account to the exported FlatPress comment',
	json_encode(array(
		'imported_comment' => $externalReplyQuoteDisabledComment
	))
) && $allOk;

$remoteReplyQuotedCase = simulate_run_remote_reply_to_reply_quote_case(true, true);
$remoteReplyQuotedChild = isset($remoteReplyQuotedCase ['child_comment']) && is_array($remoteReplyQuotedCase ['child_comment']) ? $remoteReplyQuotedCase ['child_comment'] : array();
$remoteReplyQuotedChildRef = isset($remoteReplyQuotedCase ['child_comment_ref']) && is_array($remoteReplyQuotedCase ['child_comment_ref']) ? $remoteReplyQuotedCase ['child_comment_ref'] : array();
$remoteReplyQuotedParentRef = isset($remoteReplyQuotedCase ['parent_comment_ref']) && is_array($remoteReplyQuotedCase ['parent_comment_ref']) ? $remoteReplyQuotedCase ['parent_comment_ref'] : array();
$allOk = test_result(
	'Imported Mastodon replies to another Mastodon reply quote the replied-to Mastodon user and text by default',
	!empty($remoteReplyQuotedCase ['result'] ['ok'])
		&& !empty($remoteReplyQuotedChildRef ['comment_id'])
		&& !empty($remoteReplyQuotedParentRef ['comment_id'])
		&& !empty($remoteReplyQuotedChild ['replyto'])
		&& (string) $remoteReplyQuotedChild ['replyto'] === (string) $remoteReplyQuotedParentRef ['comment_id']
		&& isset($remoteReplyQuotedChild ['content'])
		&& strpos((string) $remoteReplyQuotedChild ['content'], simulate_import_quote_block('Alice Example (@alice@example.net)', 'Parent Mastodon reply from Alice')) === 0
		&& strpos((string) $remoteReplyQuotedChild ['content'], 'Child Mastodon reply from Bob') !== false,
	json_encode(array(
		'parent_comment_ref' => $remoteReplyQuotedParentRef,
		'child_comment_ref' => $remoteReplyQuotedChildRef,
		'child_comment' => $remoteReplyQuotedChild
	))
) && $allOk;

$remoteReplyUnquotedCase = simulate_run_remote_reply_to_reply_quote_case(true, false);
$remoteReplyUnquotedChild = isset($remoteReplyUnquotedCase ['child_comment']) && is_array($remoteReplyUnquotedCase ['child_comment']) ? $remoteReplyUnquotedCase ['child_comment'] : array();
$allOk = test_result(
	'Disabling the quote option imports Mastodon reply-to-reply comments without a leading quote block',
	!empty($remoteReplyUnquotedCase ['result'] ['ok'])
		&& isset($remoteReplyUnquotedChild ['content'])
		&& trim((string) $remoteReplyUnquotedChild ['content']) === 'Child Mastodon reply from Bob',
	json_encode(array(
		'child_comment' => $remoteReplyUnquotedChild
	))
) && $allOk;

$manualCommentImportCase = simulate_run_exported_comment_self_reply_import_case(true);
$manualImportedComment = isset($manualCommentImportCase ['imported_comment']) && is_array($manualCommentImportCase ['imported_comment']) ? $manualCommentImportCase ['imported_comment'] : array();
$manualImportedCommentRef = isset($manualCommentImportCase ['imported_comment_ref']) && is_array($manualCommentImportCase ['imported_comment_ref']) ? $manualCommentImportCase ['imported_comment_ref'] : array();
$manualState = isset($manualCommentImportCase ['state']) && is_array($manualCommentImportCase ['state']) ? $manualCommentImportCase ['state'] : array();
$allOk = test_result(
	'Manual sync imports a Mastodon self-reply to an exported FlatPress comment only as a FlatPress comment',
	!empty($manualCommentImportCase ['result'] ['ok'])
		&& empty($manualCommentImportCase ['imported_entry_id'])
		&& !empty($manualImportedCommentRef ['comment_id'])
		&& !empty($manualImportedComment ['replyto'])
		&& (string) $manualImportedComment ['replyto'] === (string) $manualCommentImportCase ['local_comment_id']
		&& isset($manualImportedComment ['content']) && strpos((string) $manualImportedComment ['content'], simulate_import_quote_block('FlatPress Local', 'Previously exported FlatPress comment')) === 0
		&& strpos((string) $manualImportedComment ['content'], 'Remote self reply to exported FlatPress comment') !== false
		&& isset($manualState ['content_stats'] ['imported_entries']) && (int) $manualState ['content_stats'] ['imported_entries'] === 0
		&& isset($manualState ['content_stats'] ['imported_comments']) && (int) $manualState ['content_stats'] ['imported_comments'] === 1,
	json_encode(array(
		'imported_entry_id' => isset($manualCommentImportCase ['imported_entry_id']) ? $manualCommentImportCase ['imported_entry_id'] : '',
		'imported_comment_ref' => $manualImportedCommentRef,
		'imported_comment' => $manualImportedComment,
		'content_stats' => isset($manualState ['content_stats']) ? $manualState ['content_stats'] : array()
	))
) && $allOk;

plugin_mastodon_sync_guard_clear('content');
$scheduledCommentImportCase = simulate_run_exported_comment_self_reply_import_case(false);
$scheduledImportedComment = isset($scheduledCommentImportCase ['imported_comment']) && is_array($scheduledCommentImportCase ['imported_comment']) ? $scheduledCommentImportCase ['imported_comment'] : array();
$scheduledImportedCommentRef = isset($scheduledCommentImportCase ['imported_comment_ref']) && is_array($scheduledCommentImportCase ['imported_comment_ref']) ? $scheduledCommentImportCase ['imported_comment_ref'] : array();
$scheduledState = isset($scheduledCommentImportCase ['state']) && is_array($scheduledCommentImportCase ['state']) ? $scheduledCommentImportCase ['state'] : array();
$allOk = test_result(
	'Non-forced sync imports a Mastodon self-reply to an exported FlatPress comment only as a FlatPress comment',
	!empty($scheduledCommentImportCase ['result'] ['ok'])
		&& empty($scheduledCommentImportCase ['imported_entry_id'])
		&& !empty($scheduledImportedCommentRef ['comment_id'])
		&& !empty($scheduledImportedComment ['replyto'])
		&& (string) $scheduledImportedComment ['replyto'] === (string) $scheduledCommentImportCase ['local_comment_id']
		&& isset($scheduledImportedComment ['content']) && strpos((string) $scheduledImportedComment ['content'], simulate_import_quote_block('FlatPress Local', 'Previously exported FlatPress comment')) === 0
		&& strpos((string) $scheduledImportedComment ['content'], 'Remote self reply to exported FlatPress comment') !== false
		&& isset($scheduledState ['content_stats'] ['imported_entries']) && (int) $scheduledState ['content_stats'] ['imported_entries'] === 0
		&& isset($scheduledState ['content_stats'] ['imported_comments']) && (int) $scheduledState ['content_stats'] ['imported_comments'] === 1,
	json_encode(array(
		'imported_entry_id' => isset($scheduledCommentImportCase ['imported_entry_id']) ? $scheduledCommentImportCase ['imported_entry_id'] : '',
		'imported_comment_ref' => $scheduledImportedCommentRef,
		'imported_comment' => $scheduledImportedComment,
		'content_stats' => isset($scheduledState ['content_stats']) ? $scheduledState ['content_stats'] : array()
	))
) && $allOk;

plugin_mastodon_save_options($options);

/**
 * Scheduler-state regression tests:
 * - state.json remains the full synchronization state.
 * - scheduler-state.json carries only the small frontend/admin summary.
 * - fresh scheduler reads do not touch the full state file.
 * - stale scheduler summaries fall back to the full state.
 * - manual admin sync entry points still load the full state.
 */
$schedulerOriginalOptions = plugin_mastodon_get_options();
$schedulerOptions = simulate_seed_options_from_config($schedulerOriginalOptions);
$schedulerOptions ['instance_url'] = $instanceUrl;
$schedulerOptions ['access_token'] = 'token123';
$schedulerOptions ['sync_time'] = plugin_mastodon_normalize_sync_time(gmdate('H:i', time() - 3600));
$schedulerOptions ['delete_sync_enabled'] = '1';
plugin_mastodon_save_options($schedulerOptions);

$schedulerFullState = plugin_mastodon_default_state();
$schedulerFullState ['last_run'] = date('Y-m-d H:i:s');
$schedulerFullState ['last_deletion_run'] = date('Y-m-d H:i:s');
$schedulerFullState ['deletions_pending'] = 0;
$schedulerFullState ['deletions_not_before'] = '';
$schedulerFullState ['last_error'] = '';
$schedulerFullState ['content_stats'] ['exported_entries'] = 7;
$schedulerFullState ['deletion_stats'] ['deleted_remote_comments'] = 3;
$schedulerFullState ['entries'] ['entry-1'] = array('remote_id' => '1001');
$schedulerFullState ['comments'] ['entry-1:comment-1'] = array('remote_id' => '1002');
plugin_mastodon_state_write($schedulerFullState);
$schedulerPayload = @file_get_contents(PLUGIN_MASTODON_SCHEDULER_STATE_FILE);
$schedulerDecoded = is_string($schedulerPayload) ? json_decode($schedulerPayload, true) : array();
if (!is_array($schedulerDecoded)) {
	$schedulerDecoded = array();
}
$allOk = test_result(
	'Scheduler state is written as a compact summary without full mapping arrays',
	is_file(PLUGIN_MASTODON_SCHEDULER_STATE_FILE)
		&& isset($schedulerDecoded ['source_state_signature'])
		&& !isset($schedulerDecoded ['entries'])
		&& !isset($schedulerDecoded ['comments'])
		&& isset($schedulerDecoded ['content_stats'] ['exported_entries'])
		&& (int) $schedulerDecoded ['content_stats'] ['exported_entries'] === 7
		&& isset($schedulerDecoded ['deletion_stats'] ['deleted_remote_comments'])
		&& (int) $schedulerDecoded ['deletion_stats'] ['deleted_remote_comments'] === 3,
	json_encode(array('scheduler_keys' => array_keys($schedulerDecoded)))
) && $allOk;

plugin_mastodon_runtime_cache_clear();
$GLOBALS ['plugin_mastodon_test_uncached_file_reads'] = array();
$GLOBALS ['plugin_mastodon_test_file_reads'] = array();
$schedulerRead = plugin_mastodon_scheduler_state_read();
$freshSchedulerStateReads = simulate_count_uncached_reads_for_path(PLUGIN_MASTODON_STATE_FILE);
$freshSchedulerSummaryReads = simulate_count_file_reads_for_path(PLUGIN_MASTODON_SCHEDULER_STATE_FILE);
$allOk = test_result(
	'Fresh scheduler-state read uses the APCu-capable FlatPress I/O path and does not load full state.json',
	$freshSchedulerStateReads === 0
		&& $freshSchedulerSummaryReads >= 1
		&& isset($schedulerRead ['content_stats'] ['exported_entries'])
		&& (int) $schedulerRead ['content_stats'] ['exported_entries'] === 7,
	json_encode(array(
		'state_uncached_reads' => $freshSchedulerStateReads,
		'scheduler_cached_path_reads' => $freshSchedulerSummaryReads,
		'scheduler' => $schedulerRead
	))
) && $allOk;
unset($GLOBALS ['plugin_mastodon_test_uncached_file_reads'], $GLOBALS ['plugin_mastodon_test_file_reads']);

$staleFullState = $schedulerFullState;
$staleFullState ['last_run'] = '2000-01-02 03:04:05';
$staleFullState ['entries'] ['entry-2'] = array('remote_id' => '2002', 'padding' => str_repeat('x', 64));
$staleJson = json_encode(plugin_mastodon_state_normalize($staleFullState), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
if (is_string($staleJson)) {
	file_put_contents(PLUGIN_MASTODON_STATE_FILE, $staleJson . PHP_EOL);
	@touch(PLUGIN_MASTODON_STATE_FILE, time() + 2);
	clearstatcache(true, PLUGIN_MASTODON_STATE_FILE);
}
plugin_mastodon_runtime_cache_clear();
$GLOBALS ['plugin_mastodon_test_uncached_file_reads'] = array();
$GLOBALS ['plugin_mastodon_test_file_reads'] = array();
$rebuiltSchedulerRead = plugin_mastodon_scheduler_state_read();
$staleSchedulerStateReads = simulate_count_uncached_reads_for_path(PLUGIN_MASTODON_STATE_FILE);
$staleSchedulerSummaryReads = simulate_count_file_reads_for_path(PLUGIN_MASTODON_SCHEDULER_STATE_FILE);
$allOk = test_result(
	'Stale scheduler-state falls back to the full state and rebuilds the summary',
	$staleSchedulerSummaryReads >= 1
		&& $staleSchedulerStateReads >= 1
		&& isset($rebuiltSchedulerRead ['last_run'])
		&& (string) $rebuiltSchedulerRead ['last_run'] === '2000-01-02 03:04:05',
	json_encode(array(
		'state_uncached_reads' => $staleSchedulerStateReads,
		'scheduler_cached_path_reads' => $staleSchedulerSummaryReads,
		'last_run' => isset($rebuiltSchedulerRead ['last_run']) ? $rebuiltSchedulerRead ['last_run'] : ''
	))
) && $allOk;
unset($GLOBALS ['plugin_mastodon_test_uncached_file_reads'], $GLOBALS ['plugin_mastodon_test_file_reads']);

plugin_mastodon_runtime_cache_clear();
$GLOBALS ['plugin_mastodon_test_uncached_file_reads'] = array();
$schedulerManualOptions = $schedulerOptions;
$schedulerManualOptions ['access_token'] = '';
plugin_mastodon_save_options($schedulerManualOptions);
$manualSchedulerResult = plugin_mastodon_run_sync(true, false);
$manualSyncStateReads = simulate_count_uncached_reads_for_path(PLUGIN_MASTODON_STATE_FILE);
$allOk = test_result(
	'Manual admin synchronization still loads the full state before reporting configuration errors',
	$manualSyncStateReads >= 1
		&& empty($manualSchedulerResult ['ok'])
		&& isset($manualSchedulerResult ['message'])
		&& (string) $manualSchedulerResult ['message'] === 'missing_access_token',
	json_encode(array('state_reads' => $manualSyncStateReads, 'result' => $manualSchedulerResult))
) && $allOk;
unset($GLOBALS ['plugin_mastodon_test_uncached_file_reads']);

@unlink(PLUGIN_MASTODON_LOG_FILE);
@unlink(PLUGIN_MASTODON_LOG_FILE . '.1');
@unlink(PLUGIN_MASTODON_LOG_FILE . '.2');
$GLOBALS ['plugin_mastodon_test_log_max_bytes'] = 256;
$GLOBALS ['plugin_mastodon_test_log_rotate_files'] = 2;
$firstAppend = plugin_mastodon_io_append_file(PLUGIN_MASTODON_LOG_FILE, str_repeat('a', 220) . PHP_EOL);
$secondAppend = plugin_mastodon_io_append_file(PLUGIN_MASTODON_LOG_FILE, str_repeat('b', 80) . PHP_EOL);
$currentLog = @file_get_contents(PLUGIN_MASTODON_LOG_FILE);
$rotatedLog = @file_get_contents(PLUGIN_MASTODON_LOG_FILE . '.1');
$allOk = test_result(
	'sync.log uses append-only writes with size-based rotation',
	$firstAppend
		&& $secondAppend
		&& is_string($currentLog)
		&& is_string($rotatedLog)
		&& strpos($currentLog, str_repeat('b', 80)) !== false
		&& strpos($rotatedLog, str_repeat('a', 220)) !== false,
	json_encode(array(
		'current_size' => is_string($currentLog) ? strlen($currentLog) : -1,
		'rotated_size' => is_string($rotatedLog) ? strlen($rotatedLog) : -1
	))
) && $allOk;
unset($GLOBALS ['plugin_mastodon_test_log_max_bytes'], $GLOBALS ['plugin_mastodon_test_log_rotate_files']);

@unlink(PLUGIN_MASTODON_LOG_FILE);
@unlink(PLUGIN_MASTODON_LOG_FILE . '.1');
@unlink(PLUGIN_MASTODON_LOG_FILE . '.2');
for ($largeEntryIndex = 1; $largeEntryIndex <= 3000; $largeEntryIndex++) {
	plugin_mastodon_log_skip('sim_large_local_entries', 'local entry', 'local entries', 'entry-' . $largeEntryIndex, 'because they are outside the active synchronization date window');
}
for ($largeCommentIndex = 1; $largeCommentIndex <= 30000; $largeCommentIndex++) {
	plugin_mastodon_log_skip('sim_large_local_comments', 'local comment', 'local comments', 'entry-' . (int) ceil($largeCommentIndex / 10) . '/comment-' . $largeCommentIndex, 'because they are outside the active synchronization date window');
}
plugin_mastodon_log_flush_skip_summaries();
$aggregatedLog = @file_get_contents(PLUGIN_MASTODON_LOG_FILE);
$allOk = test_result(
	'Large skip volumes are logged as aggregate summaries',
	is_string($aggregatedLog)
		&& strpos($aggregatedLog, 'Skipped 3000 local entries because they are outside the active synchronization date window') !== false
		&& strpos($aggregatedLog, 'Skipped 30000 local comments because they are outside the active synchronization date window') !== false
		&& strpos($aggregatedLog, 'entry-1 because') === false,
	json_encode(array('log_length' => is_string($aggregatedLog) ? strlen($aggregatedLog) : -1))
) && $allOk;

$smallState = simulate_build_large_mastodon_state(300, 10);
$smallWriteOk = plugin_mastodon_state_write($smallState);
$smallStateSize = is_file(PLUGIN_MASTODON_STATE_FILE) ? (int) filesize(PLUGIN_MASTODON_STATE_FILE) : 0;
$smallSchedulerSize = is_file(PLUGIN_MASTODON_SCHEDULER_STATE_FILE) ? (int) filesize(PLUGIN_MASTODON_SCHEDULER_STATE_FILE) : 0;
$allOk = test_result(
	'Small 300x10 state keeps scheduler-state compact and disables full APCu fallback',
	$smallWriteOk
		&& $smallStateSize > 4096
		&& $smallStateSize < 2500000
		&& $smallSchedulerSize > 0
		&& $smallSchedulerSize <= 32768
		&& plugin_mastodon_state_fallback_read() === array(),
	json_encode(array(
		'state_bytes' => $smallStateSize,
		'compact_upper_bound_bytes' => 2500000,
		'scheduler_bytes' => $smallSchedulerSize,
		'fallback' => plugin_mastodon_state_fallback_read()
	))
) && $allOk;

plugin_mastodon_runtime_cache_clear();
$GLOBALS ['plugin_mastodon_test_uncached_file_reads'] = array();
$GLOBALS ['plugin_mastodon_test_file_reads'] = array();
$smallSchedulerRead = plugin_mastodon_scheduler_state_read();
$smallSchedulerStateReads = simulate_count_uncached_reads_for_path(PLUGIN_MASTODON_STATE_FILE);
$smallSchedulerSummaryReads = simulate_count_file_reads_for_path(PLUGIN_MASTODON_SCHEDULER_STATE_FILE);
$allOk = test_result(
	'Fresh small scheduler-state read avoids full state.json and uses APCu-capable file I/O',
	$smallSchedulerStateReads === 0
		&& $smallSchedulerSummaryReads >= 1
		&& isset($smallSchedulerRead ['last_run'])
		&& (string) $smallSchedulerRead ['last_run'] !== '',
	json_encode(array(
		'state_uncached_reads' => $smallSchedulerStateReads,
		'scheduler_cached_path_reads' => $smallSchedulerSummaryReads,
		'scheduler_bytes' => $smallSchedulerSize
	))
) && $allOk;
unset($GLOBALS ['plugin_mastodon_test_uncached_file_reads'], $GLOBALS ['plugin_mastodon_test_file_reads']);
unset($smallState, $smallSchedulerRead);
plugin_mastodon_runtime_cache_clear();

$largeStateMemoryDetails = array();
$largeStateDecision = simulate_large_state_memory_decision(256 * 1024 * 1024, 160 * 1024 * 1024, 384 * 1024 * 1024, $largeStateMemoryDetails);
if ($largeStateDecision === 'run') {
	$largeState = simulate_build_large_mastodon_state(3000, 10);
	$largeWriteOk = plugin_mastodon_state_write($largeState);
	$largeStateSize = is_file(PLUGIN_MASTODON_STATE_FILE) ? (int) filesize(PLUGIN_MASTODON_STATE_FILE) : 0;
	$largeSchedulerSize = is_file(PLUGIN_MASTODON_SCHEDULER_STATE_FILE) ? (int) filesize(PLUGIN_MASTODON_SCHEDULER_STATE_FILE) : 0;
	$allOk = test_result(
		'Large 3000x10 state keeps scheduler-state compact and disables full APCu fallback',
		$largeWriteOk
			&& $largeStateSize > 32768
			&& $largeStateSize < 20000000
			&& $largeSchedulerSize > 0
			&& $largeSchedulerSize <= 32768
			&& plugin_mastodon_state_fallback_read() === array(),
		json_encode(array(
			'state_bytes' => $largeStateSize,
			'compact_upper_bound_bytes' => 20000000,
			'scheduler_bytes' => $largeSchedulerSize,
			'fallback' => plugin_mastodon_state_fallback_read(),
			'memory' => $largeStateMemoryDetails
		))
	) && $allOk;

	plugin_mastodon_runtime_cache_clear();
	$GLOBALS ['plugin_mastodon_test_uncached_file_reads'] = array();
	$GLOBALS ['plugin_mastodon_test_file_reads'] = array();
	$largeSchedulerRead = plugin_mastodon_scheduler_state_read();
	$largeSchedulerStateReads = simulate_count_uncached_reads_for_path(PLUGIN_MASTODON_STATE_FILE);
	$largeSchedulerSummaryReads = simulate_count_file_reads_for_path(PLUGIN_MASTODON_SCHEDULER_STATE_FILE);
	$allOk = test_result(
		'Fresh large scheduler-state read avoids full state.json and uses APCu-capable file I/O',
		$largeSchedulerStateReads === 0
			&& $largeSchedulerSummaryReads >= 1
			&& isset($largeSchedulerRead ['last_run'])
			&& (string) $largeSchedulerRead ['last_run'] !== '',
		json_encode(array(
			'state_uncached_reads' => $largeSchedulerStateReads,
			'scheduler_cached_path_reads' => $largeSchedulerSummaryReads,
			'scheduler_bytes' => $largeSchedulerSize,
			'memory' => $largeStateMemoryDetails
		))
	) && $allOk;
	unset($GLOBALS ['plugin_mastodon_test_uncached_file_reads'], $GLOBALS ['plugin_mastodon_test_file_reads']);
	unset($largeState, $largeSchedulerRead);
} elseif ($largeStateDecision === 'skip') {
	test_skip('Large 3000x10 state keeps scheduler-state compact and disables full APCu fallback', json_encode($largeStateMemoryDetails));
	test_skip('Fresh large scheduler-state read avoids full state.json and uses APCu-capable file I/O', json_encode($largeStateMemoryDetails));
} else {
	test_warn('Large 3000x10 state keeps scheduler-state compact and disables full APCu fallback', json_encode($largeStateMemoryDetails));
	test_warn('Fresh large scheduler-state read avoids full state.json and uses APCu-capable file I/O', json_encode($largeStateMemoryDetails));
}
plugin_mastodon_runtime_cache_clear();

// Regression test: automatic scheduled sync must export a new current comment on an old mapped entry via dirty_comments.
simulate_delete_recursive($simRoot . '/fp-content/plugin_mastodon');
mkdir($simRoot . '/fp-content/plugin_mastodon', 0777, true);
plugin_mastodon_state_write(plugin_mastodon_default_state());
$GLOBALS ['plugin_mastodon_test_now'] = strtotime('2026-05-31 17:00:00 UTC');

$automaticOldCommentOptions = $seededOptions;
$automaticOldCommentOptions ['sync_start_date'] = '2026-01-01';
$automaticOldCommentOptions ['sync_scheduled_window_days'] = '7';
$automaticOldCommentOptions ['update_local_from_remote'] = '0';
$automaticOldCommentOptions ['old_thread_reply_check'] = '0';
$automaticOldCommentOptions ['access_token'] = 'token123';
plugin_mastodon_save_options($automaticOldCommentOptions);

$automaticOldCommentEntry = array(
	'version' => system_ver(),
	'subject' => 'Old mapped entry with a fresh comment',
	'content' => 'This entry is outside the scheduled window but already has a Mastodon status.',
	'author' => 'Simulation',
	'date' => strtotime('2026-04-19 12:00:00 UTC')
);
$automaticOldCommentEntryId = entry_save($automaticOldCommentEntry, null);
$automaticOldCommentEntryParsed = entry_parse($automaticOldCommentEntryId);
for ($automaticOldCommentIndex = 1; $automaticOldCommentIndex <= 10; $automaticOldCommentIndex++) {
	comment_save($automaticOldCommentEntryId, array(
		'version' => system_ver(),
		'name' => 'Simulation',
		'content' => 'Existing older comment ' . (string) $automaticOldCommentIndex,
		'date' => strtotime('2026-04-19 12:' . str_pad((string) $automaticOldCommentIndex, 2, '0', STR_PAD_LEFT) . ':00 UTC')
	));
}

$automaticOldCommentState = plugin_mastodon_default_state();
plugin_mastodon_state_set_entry_mapping($automaticOldCommentState, $automaticOldCommentEntryId, 'automatic-old-entry-remote-1', 'local', plugin_mastodon_entry_hash($automaticOldCommentEntryParsed), $instanceUrl . '/@flatpress/automatic-old-entry-remote-1', '2026-04-19 12:00:00');
plugin_mastodon_state_write($automaticOldCommentState);

$automaticFreshComment = array(
	'version' => system_ver(),
	'name' => 'Regression Author',
	'content' => 'Fresh 2026-05-31 16:48 comment on an old mapped entry',
	'date' => strtotime('2026-05-31 16:48:00 UTC')
);
$automaticFreshCommentId = comment_save($automaticOldCommentEntryId, $automaticFreshComment);
$automaticOldCommentHookState = plugin_mastodon_state_read(array($automaticOldCommentEntryId));
$automaticOldCommentHookQueued = plugin_mastodon_state_has_dirty_comment($automaticOldCommentHookState, $automaticOldCommentEntryId, $automaticFreshCommentId);

$GLOBALS ['plugin_mastodon_test_local_entry_parse_count'] = 0;
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $instanceUrl . '/api/v1/accounts/verify_credentials' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('id' => 'acct1', 'username' => 'flatpress'))
	),
	'GET ' . $instanceUrl . '/api/v1/accounts/acct1/statuses?limit=40&exclude_reblogs=true&exclude_replies=true' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array())
	),
	'GET ' . $instanceUrl . '/api/v2/instance' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('configuration' => array('statuses' => array('max_characters' => 500, 'max_media_attachments' => 4))))
	),
	'POST ' . $instanceUrl . '/api/v1/statuses' => array(
		'ok' => true,
		'code' => 200,
		'body' => json_encode(array('id' => 'automatic-old-comment-remote-1', 'url' => $instanceUrl . '/@flatpress/automatic-old-comment-remote-1', 'created_at' => '2026-05-31T16:48:00Z'))
	)
);
// Keep this scheduled-run fixture isolated from earlier cooldown-guard tests on both file-backed and APCu-backed hosts.
plugin_mastodon_sync_guard_clear('content');
$automaticOldCommentResult = plugin_mastodon_run_sync(false);
$automaticOldCommentStateAfter = isset($automaticOldCommentResult ['state']) && is_array($automaticOldCommentResult ['state']) ? $automaticOldCommentResult ['state'] : plugin_mastodon_state_read(array($automaticOldCommentEntryId));
$automaticOldCommentParseCount = simulate_local_entry_parse_count();
unset($GLOBALS ['plugin_mastodon_test_local_entry_parse_count']);
$automaticOldCommentRequests = simulate_recorded_http_requests();
$automaticOldCommentRequest = array();
foreach ($automaticOldCommentRequests as $request) {
	if (!is_array($request) || strtoupper((string) (isset($request ['method']) ? $request ['method'] : '')) !== 'POST' || strpos((string) (isset($request ['url']) ? $request ['url'] : ''), '/api/v1/statuses') === false) {
		continue;
	}
	$parsed = array();
	parse_str(isset($request ['body']) ? (string) $request ['body'] : '', $parsed);
	if (isset($parsed ['status']) && strpos((string) $parsed ['status'], 'Fresh 2026-05-31 16:48 comment') !== false) {
		$automaticOldCommentRequest = $parsed;
		break;
	}
}
$automaticOldCommentMeta = plugin_mastodon_state_get_comment_meta($automaticOldCommentStateAfter, $automaticOldCommentEntryId, $automaticFreshCommentId);
$allOk = test_result(
	'Automatic scheduled sync exports a new current comment on an old mapped entry through dirty_comments',
	!empty($automaticOldCommentResult ['ok'])
		&& $automaticOldCommentHookQueued
		&& !empty($automaticOldCommentMeta ['remote_id'])
		&& !plugin_mastodon_state_has_dirty_comment($automaticOldCommentStateAfter, $automaticOldCommentEntryId, $automaticFreshCommentId)
		&& $automaticOldCommentRequest !== array()
		&& isset($automaticOldCommentRequest ['in_reply_to_id'])
		&& (string) $automaticOldCommentRequest ['in_reply_to_id'] === 'automatic-old-entry-remote-1'
		&& $automaticOldCommentParseCount <= 2,
	json_encode(array(
		'run_message' => isset($automaticOldCommentResult ['message']) ? $automaticOldCommentResult ['message'] : '',
		'hook_queued' => $automaticOldCommentHookQueued,
		'parse_count' => $automaticOldCommentParseCount,
		'comment_meta' => $automaticOldCommentMeta,
		'request' => $automaticOldCommentRequest
	))
) && $allOk;
entry_delete($automaticOldCommentEntryId);
unset($GLOBALS ['plugin_mastodon_test_now']);

// Regression test: the explicit one-way mode must be disabled by default, saved from the admin form, and translated everywhere.
$oneWayDefaultOptions = plugin_mastodon_default_options();
$oneWayAdminLanguageOk = true;
$oneWayLanguageFiles = glob($simRoot . '/fp-plugins/mastodon/lang/lang.*.php');
if (!is_array($oneWayLanguageFiles)) {
	$oneWayLanguageFiles = array();
}
sort($oneWayLanguageFiles, SORT_STRING);
foreach ($oneWayLanguageFiles as $oneWayLanguageFile) {
	$oneWayLangEntries = simulate_mastodon_read_admin_plugin_language_entries((string) $oneWayLanguageFile);
	$oneWayPluginLang = isset($oneWayLangEntries ['mastodon']) && is_array($oneWayLangEntries ['mastodon']) ? $oneWayLangEntries ['mastodon'] : array();
	$oneWayRequiredAdminKeys = array(
		'disable_remote_import',
		'disable_remote_import_desc',
		'delete_sync_enabled_desc_one_way',
		'manual_runs_desc_one_way',
		'companion_plugins_intro_one_way',
		'companion_tag_desc_one_way',
		'companion_emoticons_desc_one_way'
	);
	foreach ($oneWayRequiredAdminKeys as $oneWayRequiredAdminKey) {
		if (empty($oneWayPluginLang [$oneWayRequiredAdminKey])) {
			$oneWayAdminLanguageOk = false;
			break 2;
		}
	}
}
$oneWayTemplate = @file_get_contents($simRoot . '/fp-plugins/mastodon/tpls/admin.plugin.mastodon.tpl');
$oneWayAdminOptions = $seededOptions;
$oneWayAdminOptions ['disable_remote_import'] = '1';
$oneWayAdminOptions ['access_token'] = 'token123';
plugin_mastodon_save_options($oneWayAdminOptions);
$oneWayAdminSmarty = new SimulateSmartyCollector();
plugin_mastodon_admin_assign($oneWayAdminSmarty);
$oneWayAdminCfg = isset($oneWayAdminSmarty->assigned ['mastodon_cfg']) && is_array($oneWayAdminSmarty->assigned ['mastodon_cfg']) ? $oneWayAdminSmarty->assigned ['mastodon_cfg'] : array();
$allOk = test_result(
	'One-way mode option is disabled by default, normalized, assigned to admin UI, templated and translated',
		isset($oneWayDefaultOptions ['disable_remote_import'])
		&& (string) $oneWayDefaultOptions ['disable_remote_import'] === '0'
		&& plugin_mastodon_normalize_disable_remote_import('on') === '1'
		&& plugin_mastodon_normalize_disable_remote_import('false') === '0'
		&& !plugin_mastodon_should_import_remote_to_local(array('disable_remote_import' => '1'))
		&& plugin_mastodon_should_import_remote_to_local(array('disable_remote_import' => '0'))
		&& isset($oneWayAdminCfg ['disable_remote_import'])
		&& (string) $oneWayAdminCfg ['disable_remote_import'] === '1'
		&& is_string($oneWayTemplate)
		&& strpos($oneWayTemplate, 'name="disable_remote_import"') !== false
		&& count($oneWayLanguageFiles) === 15
		&& $oneWayAdminLanguageOk,
	json_encode(array(
		'default' => isset($oneWayDefaultOptions ['disable_remote_import']) ? $oneWayDefaultOptions ['disable_remote_import'] : null,
		'admin_cfg' => $oneWayAdminCfg,
		'language_file_count' => count($oneWayLanguageFiles),
		'languages_ok' => $oneWayAdminLanguageOk
	))
) && $allOk;

$oneWayImportHiddenOptions = $seededOptions;
$oneWayImportHiddenOptions ['disable_remote_import'] = '1';
$oneWayImportHiddenOptions ['update_local_from_remote'] = '1';
$oneWayImportHiddenOptions ['import_synced_comments_as_entries'] = '1';
$oneWayImportHiddenOptions ['quote_imported_reply_parent'] = '1';
$oneWayImportHiddenOptions ['old_thread_reply_check'] = '1';
$oneWayImportHiddenOptions ['old_thread_context_limit'] = '9';
$oneWayImportHiddenPost = array(
	'instance_url' => $instanceUrl,
	'username' => 'flatpress',
	'sync_time' => '09:30',
	'sync_start_date' => '2026-01-01',
	'sync_scheduled_window_days' => '14',
	'disable_remote_import' => '1',
	'mastodon_remote_import_options_hidden' => '1',
	'delete_sync_enabled' => '1'
);
$oneWayImportHiddenSaved = plugin_mastodon_admin_apply_save_post($oneWayImportHiddenOptions, $oneWayImportHiddenPost);
$allOk = test_result(
	'One-way admin save preserves hidden import options while keeping one-way mode enabled',
	isset($oneWayImportHiddenSaved ['disable_remote_import'])
		&& (string) $oneWayImportHiddenSaved ['disable_remote_import'] === '1'
		&& isset($oneWayImportHiddenSaved ['update_local_from_remote']) && (string) $oneWayImportHiddenSaved ['update_local_from_remote'] === '1'
		&& isset($oneWayImportHiddenSaved ['import_synced_comments_as_entries']) && (string) $oneWayImportHiddenSaved ['import_synced_comments_as_entries'] === '1'
		&& isset($oneWayImportHiddenSaved ['quote_imported_reply_parent']) && (string) $oneWayImportHiddenSaved ['quote_imported_reply_parent'] === '1'
		&& isset($oneWayImportHiddenSaved ['old_thread_reply_check']) && (string) $oneWayImportHiddenSaved ['old_thread_reply_check'] === '1'
		&& isset($oneWayImportHiddenSaved ['old_thread_context_limit']) && (string) $oneWayImportHiddenSaved ['old_thread_context_limit'] === '9',
	json_encode(array(
		'saved' => $oneWayImportHiddenSaved
	))
) && $allOk;

$oneWayDisablePost = $oneWayImportHiddenPost;
unset($oneWayDisablePost ['disable_remote_import']);
$oneWayDisabledSaved = plugin_mastodon_admin_apply_save_post($oneWayImportHiddenOptions, $oneWayDisablePost);
$allOk = test_result(
	'One-way admin save preserves hidden import options when one-way mode is disabled in the same submit',
	isset($oneWayDisabledSaved ['disable_remote_import'])
		&& (string) $oneWayDisabledSaved ['disable_remote_import'] === '0'
		&& isset($oneWayDisabledSaved ['update_local_from_remote']) && (string) $oneWayDisabledSaved ['update_local_from_remote'] === '1'
		&& isset($oneWayDisabledSaved ['import_synced_comments_as_entries']) && (string) $oneWayDisabledSaved ['import_synced_comments_as_entries'] === '1'
		&& isset($oneWayDisabledSaved ['quote_imported_reply_parent']) && (string) $oneWayDisabledSaved ['quote_imported_reply_parent'] === '1'
		&& isset($oneWayDisabledSaved ['old_thread_reply_check']) && (string) $oneWayDisabledSaved ['old_thread_reply_check'] === '1'
		&& isset($oneWayDisabledSaved ['old_thread_context_limit']) && (string) $oneWayDisabledSaved ['old_thread_context_limit'] === '9',
	json_encode(array(
		'saved' => $oneWayDisabledSaved
	))
) && $allOk;

$bidirectionalUncheckedOptions = $oneWayImportHiddenOptions;
$bidirectionalUncheckedOptions ['disable_remote_import'] = '0';
$bidirectionalUncheckedPost = $oneWayImportHiddenPost;
unset($bidirectionalUncheckedPost ['disable_remote_import']);
unset($bidirectionalUncheckedPost ['mastodon_remote_import_options_hidden']);
$bidirectionalUncheckedSaved = plugin_mastodon_admin_apply_save_post($bidirectionalUncheckedOptions, $bidirectionalUncheckedPost);
$allOk = test_result(
	'Bidirectional admin save still stores visible unchecked import options as disabled',
	isset($bidirectionalUncheckedSaved ['disable_remote_import'])
		&& (string) $bidirectionalUncheckedSaved ['disable_remote_import'] === '0'
		&& isset($bidirectionalUncheckedSaved ['update_local_from_remote']) && (string) $bidirectionalUncheckedSaved ['update_local_from_remote'] === '0'
		&& isset($bidirectionalUncheckedSaved ['import_synced_comments_as_entries']) && (string) $bidirectionalUncheckedSaved ['import_synced_comments_as_entries'] === '0'
		&& isset($bidirectionalUncheckedSaved ['quote_imported_reply_parent']) && (string) $bidirectionalUncheckedSaved ['quote_imported_reply_parent'] === '0'
		&& isset($bidirectionalUncheckedSaved ['old_thread_reply_check']) && (string) $bidirectionalUncheckedSaved ['old_thread_reply_check'] === '0'
		&& isset($bidirectionalUncheckedSaved ['old_thread_context_limit']) && (string) $bidirectionalUncheckedSaved ['old_thread_context_limit'] === '1',
	json_encode(array(
		'saved' => $bidirectionalUncheckedSaved
	))
) && $allOk;

$oneWayTemplateImportBlockOk = is_string($oneWayTemplate)
	&& strpos($oneWayTemplate, 'name="mastodon_remote_import_options_hidden"') !== false
	&& strpos($oneWayTemplate, '$mastodon_cfg.disable_remote_import neq \'1\'') !== false
	&& strpos($oneWayTemplate, 'name="update_local_from_remote"') !== false
	&& strpos($oneWayTemplate, 'name="import_synced_comments_as_entries"') !== false
	&& strpos($oneWayTemplate, 'name="quote_imported_reply_parent"') !== false
	&& strpos($oneWayTemplate, 'name="old_thread_reply_check"') !== false
	&& strpos($oneWayTemplate, 'name="old_thread_context_limit"') !== false
	&& strpos($oneWayTemplate, 'reply_notifications_scope_active') !== false
	&& strpos($oneWayTemplate, 'reply_notifications_scope_missing') !== false;
$oneWayTemplateStatsOk = is_string($oneWayTemplate)
	&& strpos($oneWayTemplate, 'stats_imported_entries') !== false
	&& strpos($oneWayTemplate, 'stats_updated_entries') !== false
	&& strpos($oneWayTemplate, 'stats_imported_comments') !== false
	&& strpos($oneWayTemplate, 'stats_updated_local_comments') !== false
	&& strpos($oneWayTemplate, 'stats_deleted_local_entries') !== false
	&& strpos($oneWayTemplate, 'stats_deleted_local_comments') !== false
	&& substr_count($oneWayTemplate, '$mastodon_cfg.disable_remote_import neq \'1\'') >= 4;
$allOk = test_result(
	'One-way admin template hides import controls, notification hints and import-only counters',
	$oneWayTemplateImportBlockOk && $oneWayTemplateStatsOk,
	json_encode(array(
		'import_block_ok' => $oneWayTemplateImportBlockOk,
		'stats_ok' => $oneWayTemplateStatsOk
	))
) && $allOk;

$oneWayTemplateRelevantOutputsOk = is_string($oneWayTemplate)
	&& strpos($oneWayTemplate, 'name="mastodon_run_now"') !== false
	&& strpos($oneWayTemplate, 'name="mastodon_run_full_now"') !== false
	&& strpos($oneWayTemplate, 'name="mastodon_run_full_deletion"') !== false
	&& strpos($oneWayTemplate, 'name="mastodon_register_app"') !== false
	&& strpos($oneWayTemplate, 'name="mastodon_exchange_code"') !== false
	&& strpos($oneWayTemplate, 'name="mastodon_refresh_instance_info"') !== false
	&& strpos($oneWayTemplate, 'name="mastodon_clear_token"') !== false
	&& strpos($oneWayTemplate, 'mastodon_admin_maintenance_url') !== false
	&& strpos($oneWayTemplate, 'delete_sync_enabled_desc_one_way') !== false
	&& strpos($oneWayTemplate, 'manual_runs_desc_one_way') !== false
	&& strpos($oneWayTemplate, 'stats_exported_entries') !== false
	&& strpos($oneWayTemplate, 'stats_updated_remote_entries') !== false
	&& strpos($oneWayTemplate, 'stats_exported_comments') !== false
	&& strpos($oneWayTemplate, 'stats_updated_remote_comments') !== false
	&& strpos($oneWayTemplate, 'stats_deleted_remote_entries') !== false
	&& strpos($oneWayTemplate, 'stats_deleted_remote_comments') !== false;
$allOk = test_result(
	'One-way admin template keeps export, OAuth, instance, token, state and deletion outputs visible',
	$oneWayTemplateRelevantOutputsOk,
	json_encode(array(
		'relevant_outputs_ok' => $oneWayTemplateRelevantOutputsOk
	))
) && $allOk;

$oneWayAssignedCompanions = isset($oneWayAdminSmarty->assigned ['mastodon_companion_plugins']) && is_array($oneWayAdminSmarty->assigned ['mastodon_companion_plugins']) ? $oneWayAdminSmarty->assigned ['mastodon_companion_plugins'] : array();
$oneWayAssignedCompanionBySlug = array();
foreach ($oneWayAssignedCompanions as $oneWayAssignedCompanion) {
	if (is_array($oneWayAssignedCompanion) && !empty($oneWayAssignedCompanion ['slug'])) {
		$oneWayAssignedCompanionBySlug [(string) $oneWayAssignedCompanion ['slug']] = $oneWayAssignedCompanion;
	}
}
$oneWayAssignedIntro = isset($oneWayAdminSmarty->assigned ['mastodon_companion_plugins_intro']) ? (string) $oneWayAdminSmarty->assigned ['mastodon_companion_plugins_intro'] : '';
$allOk = test_result(
	'One-way admin assignment hides import-only companion plugins and uses one-way companion intro',
	!isset($oneWayAssignedCompanionBySlug ['bbcode'], $oneWayAssignedCompanionBySlug ['photoswipe'], $oneWayAssignedCompanionBySlug ['audiovideo'])
		&& isset($oneWayAssignedCompanionBySlug ['tag'], $oneWayAssignedCompanionBySlug ['emoticons'])
		&& $oneWayAssignedIntro === plugin_mastodon_lang_string('companion_plugins_intro_one_way', 'In one-way mode, only FlatPress-to-Mastodon export helpers are shown.'),
	json_encode(array(
		'intro' => $oneWayAssignedIntro,
		'companions' => $oneWayAssignedCompanionBySlug
	))
) && $allOk;

// Regression test: when one-way mode is enabled, remote statuses and replies do not create FlatPress content, while local content is still exported.
simulate_delete_recursive($simRoot . '/fp-content/content');
mkdir($simRoot . '/fp-content/content', 0777, true);
simulate_delete_recursive($simRoot . '/fp-content/plugin_mastodon');
mkdir($simRoot . '/fp-content/plugin_mastodon', 0777, true);
plugin_mastodon_runtime_cache_clear();
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$oneWaySyncOptions = $seededOptions;
$oneWaySyncOptions ['disable_remote_import'] = '1';
$oneWaySyncOptions ['update_local_from_remote'] = '1';
$oneWaySyncOptions ['old_thread_reply_check'] = '1';
$oneWaySyncOptions ['access_token'] = 'token123';
$oneWaySyncOptions ['sync_start_date'] = '2026-01-01';
plugin_mastodon_save_options($oneWaySyncOptions);
$oneWaySyncState = plugin_mastodon_default_state();
plugin_mastodon_state_write($oneWaySyncState);
$oneWayLocalEntry = array(
	'version' => system_ver(),
	'subject' => 'One-way local entry',
	'content' => 'This local FlatPress entry must still be exported to Mastodon.',
	'author' => 'Simulation',
	'date' => strtotime('2026-05-31 18:00:00 UTC')
);
$oneWayLocalEntryId = entry_save($oneWayLocalEntry, null);
$oneWayRemoteStatus = array(
	'id' => 'oneway-remote-entry-1',
	'visibility' => 'public',
	'in_reply_to_id' => null,
	'created_at' => '2026-05-31T18:10:00Z',
	'content' => '<p>This remote status must not become a FlatPress entry.</p>',
	'url' => $instanceUrl . '/@flatpress/oneway-remote-entry-1',
	'account' => array('id' => 'acct1', 'acct' => 'flatpress', 'display_name' => 'FlatPress Bot', 'url' => $instanceUrl . '/@flatpress')
);
$oneWayRemoteReply = array(
	'id' => 'oneway-remote-reply-1',
	'visibility' => 'public',
	'in_reply_to_id' => 'oneway-local-export-1',
	'created_at' => '2026-05-31T18:12:00Z',
	'content' => '<p>This remote reply must not become a FlatPress comment.</p>',
	'url' => $instanceUrl . '/@flatpress/oneway-remote-reply-1',
	'account' => array('id' => 'acct2', 'acct' => 'reply@example.net', 'display_name' => 'Reply User', 'url' => 'https://example.net/@reply')
);
$oneWayProfilePng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jz6kAAAAASUVORK5CYII=', true);
$oneWayProfilePng = is_string($oneWayProfilePng) ? $oneWayProfilePng : 'png';
$oneWayProfileAvatarUrl = $instanceUrl . '/system/accounts/avatars/original/oneway-profile.png';
$oneWayProfileAccount = array(
	'id' => 'acct1',
	'username' => 'flatpress',
	'acct' => 'flatpress',
	'display_name' => 'FlatPress Profile',
	'url' => $instanceUrl . '/@flatpress',
	'avatar_static' => $oneWayProfileAvatarUrl,
	'avatar' => $oneWayProfileAvatarUrl,
	'avatar_description' => 'One-way profile avatar'
);
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $instanceUrl . '/api/v1/accounts/verify_credentials' => array('ok' => true, 'code' => 200, 'body' => json_encode($oneWayProfileAccount)),
	'GET ' . $oneWayProfileAvatarUrl => array('ok' => true, 'code' => 200, 'headers' => array('content-type' => 'image/png'), 'body' => $oneWayProfilePng),
	'GET ' . $instanceUrl . '/api/v1/accounts/acct1/statuses?limit=40&exclude_reblogs=true&exclude_replies=true' => array('ok' => true, 'code' => 200, 'body' => json_encode(array($oneWayRemoteStatus))),
	'GET ' . $instanceUrl . '/api/v1/statuses/oneway-local-export-1/context' => array('ok' => true, 'code' => 200, 'body' => json_encode(array('descendants' => array($oneWayRemoteReply)))),
	'GET ' . $instanceUrl . '/api/v2/instance' => array('ok' => true, 'code' => 200, 'body' => json_encode(array('configuration' => array('statuses' => array('max_characters' => 500, 'max_media_attachments' => 4))))),
	'POST ' . $instanceUrl . '/api/v1/statuses' => array('ok' => true, 'code' => 200, 'body' => json_encode(array('id' => 'oneway-local-export-1', 'url' => $instanceUrl . '/@flatpress/oneway-local-export-1', 'created_at' => '2026-05-31T18:00:00Z')))
);
$oneWaySyncResult = plugin_mastodon_run_sync(true, true);
$oneWaySyncStateAfter = isset($oneWaySyncResult ['state']) && is_array($oneWaySyncResult ['state']) ? $oneWaySyncResult ['state'] : plugin_mastodon_state_read(array($oneWayLocalEntryId));
$oneWaySyncRequests = simulate_recorded_http_requests();
$oneWayRemoteReadRequests = 0;
$oneWayStatusPostRequests = 0;
$oneWayVerifyRequests = 0;
$oneWayAvatarRequests = 0;
foreach ($oneWaySyncRequests as $oneWayRequest) {
	if (!is_array($oneWayRequest) || empty($oneWayRequest ['method']) || empty($oneWayRequest ['url'])) {
		continue;
	}
	$oneWayRequestLine = strtoupper(strtoupper((string) $oneWayRequest ['method']) . ' ' . (string) $oneWayRequest ['url']);
	$oneWayRequestUrl = (string) $oneWayRequest ['url'];
	if ($oneWayRequestLine === 'GET ' . strtoupper($instanceUrl) . '/API/V1/ACCOUNTS/VERIFY_CREDENTIALS') {
		$oneWayVerifyRequests++;
		continue;
	}
	if (strtoupper((string) $oneWayRequest ['method']) === 'GET' && $oneWayRequestUrl === $oneWayProfileAvatarUrl) {
		$oneWayAvatarRequests++;
		continue;
	}
	if (strpos($oneWayRequestLine, 'GET ' . strtoupper($instanceUrl) . '/API/V1/ACCOUNTS/') !== false || strpos($oneWayRequestLine, '/CONTEXT') !== false || strpos($oneWayRequestLine, '/API/V1/NOTIFICATIONS') !== false) {
		$oneWayRemoteReadRequests++;
	}
	if (strpos($oneWayRequestLine, 'POST ' . strtoupper($instanceUrl) . '/API/V1/STATUSES') === 0) {
		$oneWayStatusPostRequests++;
	}
}
$oneWayProfileCache = plugin_mastodon_profile_cache_read(true);
$oneWayDirectState = plugin_mastodon_default_state();
$oneWayDirectEntryImport = plugin_mastodon_import_remote_entry($oneWaySyncOptions, $oneWayDirectState, $oneWayRemoteStatus);
$oneWayDirectCommentImport = plugin_mastodon_import_remote_comment($oneWaySyncOptions, $oneWayDirectState, $oneWayLocalEntryId, $oneWayRemoteReply);
$allOk = test_result(
	'One-way mode blocks Mastodon-to-FlatPress imports while FlatPress-to-Mastodon export still runs',
		!empty($oneWaySyncResult ['ok'])
		&& $oneWayStatusPostRequests === 1
		&& $oneWayRemoteReadRequests === 0
		&& $oneWayVerifyRequests === 1
		&& $oneWayAvatarRequests === 1
		&& isset($oneWayProfileCache ['display_name']) && (string) $oneWayProfileCache ['display_name'] === 'FlatPress Profile'
		&& isset($oneWayProfileCache ['avatar_file']) && plugin_mastodon_profile_relative_to_absolute((string) $oneWayProfileCache ['avatar_file']) !== ''
		&& isset($oneWaySyncStateAfter ['entries'] [$oneWayLocalEntryId] ['remote_id'])
		&& (string) $oneWaySyncStateAfter ['entries'] [$oneWayLocalEntryId] ['remote_id'] === 'oneway-local-export-1'
		&& empty($oneWaySyncStateAfter ['entries_remote'] ['oneway-remote-entry-1'])
		&& empty($oneWaySyncStateAfter ['comments_remote'] ['oneway-remote-reply-1'])
		&& $oneWayDirectEntryImport === false
		&& $oneWayDirectCommentImport === false,
	json_encode(array(
		'result' => $oneWaySyncResult,
		'remote_read_requests' => $oneWayRemoteReadRequests,
		'status_post_requests' => $oneWayStatusPostRequests,
		'verify_requests' => $oneWayVerifyRequests,
		'avatar_requests' => $oneWayAvatarRequests,
		'profile_cache' => $oneWayProfileCache,
		'requests' => $oneWaySyncRequests,
		'direct_entry_import' => $oneWayDirectEntryImport,
		'direct_comment_import' => $oneWayDirectCommentImport
	))
) && $allOk;
entry_delete($oneWayLocalEntryId);

// Regression test: automatic scheduled one-way synchronization refreshes the profile widget cache even when remote import is disabled.
simulate_delete_recursive($simRoot . '/fp-content/content');
mkdir($simRoot . '/fp-content/content', 0777, true);
simulate_delete_recursive($simRoot . '/fp-content/plugin_mastodon');
mkdir($simRoot . '/fp-content/plugin_mastodon', 0777, true);
plugin_mastodon_runtime_cache_clear();
plugin_mastodon_sync_guard_clear('content');
$oneWayAutomaticOptions = $seededOptions;
$oneWayAutomaticOptions ['disable_remote_import'] = '1';
$oneWayAutomaticOptions ['access_token'] = 'token123';
$oneWayAutomaticOptions ['sync_start_date'] = '2026-01-01';
$oneWayAutomaticOptions ['sync_time'] = '00:00';
plugin_mastodon_save_options($oneWayAutomaticOptions);
$oneWayAutomaticState = plugin_mastodon_default_state();
plugin_mastodon_state_write($oneWayAutomaticState);
$oneWayAutomaticPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jz6kAAAAASUVORK5CYII=', true);
$oneWayAutomaticPng = is_string($oneWayAutomaticPng) ? $oneWayAutomaticPng : 'png';
$oneWayAutomaticAvatarUrl = $instanceUrl . '/system/accounts/avatars/original/oneway-automatic-profile.png';
$oneWayAutomaticAccount = array(
	'id' => 'acct1',
	'username' => 'flatpress',
	'acct' => 'flatpress',
	'display_name' => 'FlatPress Scheduled Profile',
	'url' => $instanceUrl . '/@flatpress',
	'avatar_static' => $oneWayAutomaticAvatarUrl,
	'avatar' => $oneWayAutomaticAvatarUrl,
	'avatar_description' => 'Scheduled profile avatar'
);
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $instanceUrl . '/api/v1/accounts/verify_credentials' => array('ok' => true, 'code' => 200, 'body' => json_encode($oneWayAutomaticAccount)),
	'GET ' . $oneWayAutomaticAvatarUrl => array('ok' => true, 'code' => 200, 'headers' => array('content-type' => 'image/png'), 'body' => $oneWayAutomaticPng)
);
$oneWayAutomaticResult = plugin_mastodon_run_sync(false);
$oneWayAutomaticRequests = simulate_recorded_http_requests();
$oneWayAutomaticVerifyRequests = 0;
$oneWayAutomaticAvatarRequests = 0;
$oneWayAutomaticImportRequests = 0;
foreach ($oneWayAutomaticRequests as $oneWayAutomaticRequest) {
	if (!is_array($oneWayAutomaticRequest) || empty($oneWayAutomaticRequest ['method']) || empty($oneWayAutomaticRequest ['url'])) {
		continue;
	}
	$oneWayAutomaticLine = strtoupper(strtoupper((string) $oneWayAutomaticRequest ['method']) . ' ' . (string) $oneWayAutomaticRequest ['url']);
	$oneWayAutomaticUrl = (string) $oneWayAutomaticRequest ['url'];
	if ($oneWayAutomaticLine === 'GET ' . strtoupper($instanceUrl) . '/API/V1/ACCOUNTS/VERIFY_CREDENTIALS') {
		$oneWayAutomaticVerifyRequests++;
		continue;
	}
	if (strtoupper((string) $oneWayAutomaticRequest ['method']) === 'GET' && $oneWayAutomaticUrl === $oneWayAutomaticAvatarUrl) {
		$oneWayAutomaticAvatarRequests++;
		continue;
	}
	if (strpos($oneWayAutomaticLine, 'GET ' . strtoupper($instanceUrl) . '/API/V1/ACCOUNTS/') !== false || strpos($oneWayAutomaticLine, '/CONTEXT') !== false || strpos($oneWayAutomaticLine, '/API/V1/NOTIFICATIONS') !== false) {
		$oneWayAutomaticImportRequests++;
	}
}
$oneWayAutomaticProfileCache = plugin_mastodon_profile_cache_read(true);
plugin_mastodon_sync_guard_clear('content');
$allOk = test_result(
	'Automatic one-way content sync refreshes the Mastodon widget profile cache without remote import reads',
		!empty($oneWayAutomaticResult ['ok'])
		&& $oneWayAutomaticVerifyRequests === 1
		&& $oneWayAutomaticAvatarRequests === 1
		&& $oneWayAutomaticImportRequests === 0
		&& isset($oneWayAutomaticProfileCache ['display_name']) && (string) $oneWayAutomaticProfileCache ['display_name'] === 'FlatPress Scheduled Profile'
		&& isset($oneWayAutomaticProfileCache ['avatar_description']) && (string) $oneWayAutomaticProfileCache ['avatar_description'] === 'Scheduled profile avatar',
	json_encode(array(
		'result' => $oneWayAutomaticResult,
		'verify_requests' => $oneWayAutomaticVerifyRequests,
		'avatar_requests' => $oneWayAutomaticAvatarRequests,
		'import_requests' => $oneWayAutomaticImportRequests,
		'profile_cache' => $oneWayAutomaticProfileCache,
		'requests' => $oneWayAutomaticRequests
	))
) && $allOk;

// Regression test: one-way deletion sync must keep local content when remote statuses vanished, unlink stale mappings and queue re-export.
simulate_delete_recursive($simRoot . '/fp-content/content');
mkdir($simRoot . '/fp-content/content', 0777, true);
simulate_delete_recursive($simRoot . '/fp-content/plugin_mastodon');
mkdir($simRoot . '/fp-content/plugin_mastodon', 0777, true);
plugin_mastodon_runtime_cache_clear();
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$oneWayDeleteOptions = $seededOptions;
$oneWayDeleteOptions ['disable_remote_import'] = '1';
$oneWayDeleteOptions ['delete_sync_enabled'] = '1';
$oneWayDeleteOptions ['access_token'] = 'token123';
$oneWayDeleteOptions ['sync_start_date'] = '2026-01-01';
plugin_mastodon_save_options($oneWayDeleteOptions);
$oneWayDeleteEntry = array(
	'version' => system_ver(),
	'subject' => 'One-way deletion entry',
	'content' => 'This entry must survive a missing remote status.',
	'author' => 'Simulation',
	'date' => strtotime('2026-05-31 18:30:00 UTC')
);
$oneWayDeleteEntryId = entry_save($oneWayDeleteEntry, null);
$oneWayDeleteCommentEntry = array(
	'version' => system_ver(),
	'subject' => 'One-way deletion comment entry',
	'content' => 'This mapped entry remains available for comment re-export.',
	'author' => 'Simulation',
	'date' => strtotime('2026-05-31 18:35:00 UTC')
);
$oneWayDeleteCommentEntryId = entry_save($oneWayDeleteCommentEntry, null);
$oneWayDeleteComment = array(
	'version' => system_ver(),
	'name' => 'Simulation',
	'content' => 'This comment must survive a missing remote reply.',
	'date' => strtotime('2026-05-31 18:40:00 UTC')
);
$oneWayDeleteCommentId = comment_save($oneWayDeleteCommentEntryId, $oneWayDeleteComment);
$oneWayDeleteState = plugin_mastodon_default_state();
$oneWayDeleteState ['deletions_pending'] = 1;
plugin_mastodon_state_set_entry_mapping($oneWayDeleteState, $oneWayDeleteEntryId, 'oneway-delete-entry-missing', 'local', plugin_mastodon_entry_hash(entry_parse($oneWayDeleteEntryId)), $instanceUrl . '/@flatpress/oneway-delete-entry-missing', '2026-05-31 18:30:00', plugin_mastodon_local_item_date_key(entry_parse($oneWayDeleteEntryId), $oneWayDeleteEntryId), '2026-05-31');
plugin_mastodon_state_set_entry_mapping($oneWayDeleteState, $oneWayDeleteCommentEntryId, 'oneway-delete-entry-existing', 'local', plugin_mastodon_entry_hash(entry_parse($oneWayDeleteCommentEntryId)), $instanceUrl . '/@flatpress/oneway-delete-entry-existing', '2026-05-31 18:35:00', plugin_mastodon_local_item_date_key(entry_parse($oneWayDeleteCommentEntryId), $oneWayDeleteCommentEntryId), '2026-05-31');
plugin_mastodon_state_set_comment_mapping($oneWayDeleteState, $oneWayDeleteCommentEntryId, $oneWayDeleteCommentId, 'oneway-delete-comment-missing', 'local', plugin_mastodon_comment_hash(comment_parse($oneWayDeleteCommentEntryId, $oneWayDeleteCommentId)), $instanceUrl . '/@flatpress/oneway-delete-comment-missing', '2026-05-31 18:40:00', '', 'oneway-delete-entry-existing', plugin_mastodon_local_item_date_key(comment_parse($oneWayDeleteCommentEntryId, $oneWayDeleteCommentId), $oneWayDeleteCommentId), '2026-05-31');
plugin_mastodon_state_write($oneWayDeleteState);
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $instanceUrl . '/api/v1/statuses/oneway-delete-entry-missing' => array('ok' => false, 'code' => 404, 'body' => json_encode(array('error' => 'Record not found'))),
	'GET ' . $instanceUrl . '/api/v1/statuses/oneway-delete-entry-existing' => array('ok' => true, 'code' => 200, 'body' => json_encode(array('id' => 'oneway-delete-entry-existing', 'url' => $instanceUrl . '/@flatpress/oneway-delete-entry-existing', 'created_at' => '2026-05-31T18:35:00Z'))),
	'GET ' . $instanceUrl . '/api/v1/statuses/oneway-delete-comment-missing' => array('ok' => false, 'code' => 404, 'body' => json_encode(array('error' => 'Record not found')))
);
$oneWayDeleteResult = plugin_mastodon_run_deletion_sync(true);
$oneWayDeleteStateAfter = isset($oneWayDeleteResult ['state']) && is_array($oneWayDeleteResult ['state']) ? $oneWayDeleteResult ['state'] : plugin_mastodon_state_read(array($oneWayDeleteEntryId, $oneWayDeleteCommentEntryId));
$oneWayDeleteRequests = simulate_recorded_http_requests();
$oneWayDeleteDeleteRequests = 0;
foreach ($oneWayDeleteRequests as $oneWayDeleteRequest) {
	if (is_array($oneWayDeleteRequest) && !empty($oneWayDeleteRequest ['method']) && strtoupper((string) $oneWayDeleteRequest ['method']) === 'DELETE') {
		$oneWayDeleteDeleteRequests++;
	}
}
$allOk = test_result(
	'One-way deletion sync keeps local content, removes stale remote mappings and queues re-export',
		!empty($oneWayDeleteResult ['ok'])
		&& entry_exists($oneWayDeleteEntryId)
		&& comment_exists($oneWayDeleteCommentEntryId, $oneWayDeleteCommentId)
		&& empty($oneWayDeleteStateAfter ['entries'] [$oneWayDeleteEntryId])
		&& empty($oneWayDeleteStateAfter ['entries_remote'] ['oneway-delete-entry-missing'])
		&& empty($oneWayDeleteStateAfter ['comments_remote'] ['oneway-delete-comment-missing'])
		&& plugin_mastodon_state_has_dirty_entry($oneWayDeleteStateAfter, $oneWayDeleteEntryId)
		&& plugin_mastodon_state_has_dirty_comment($oneWayDeleteStateAfter, $oneWayDeleteCommentEntryId, $oneWayDeleteCommentId)
		&& isset($oneWayDeleteStateAfter ['deletion_stats'] ['deleted_local_entries'])
		&& (int) $oneWayDeleteStateAfter ['deletion_stats'] ['deleted_local_entries'] === 0
		&& isset($oneWayDeleteStateAfter ['deletion_stats'] ['deleted_local_comments'])
		&& (int) $oneWayDeleteStateAfter ['deletion_stats'] ['deleted_local_comments'] === 0
		&& $oneWayDeleteDeleteRequests === 0,
	json_encode(array(
		'result' => $oneWayDeleteResult,
		'state' => $oneWayDeleteStateAfter,
		'delete_requests' => $oneWayDeleteDeleteRequests
	))
) && $allOk;

plugin_mastodon_runtime_cache_clear();
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$oneWayReexportPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jz6kAAAAASUVORK5CYII=', true);
$oneWayReexportPng = is_string($oneWayReexportPng) ? $oneWayReexportPng : 'png';
$oneWayReexportAvatarUrl = $instanceUrl . '/system/accounts/avatars/original/oneway-reexport-profile.png';
$oneWayReexportAccount = array(
	'id' => 'acct1',
	'username' => 'flatpress',
	'acct' => 'flatpress',
	'display_name' => 'FlatPress Reexport Profile',
	'url' => $instanceUrl . '/@flatpress',
	'avatar_static' => $oneWayReexportAvatarUrl,
	'avatar' => $oneWayReexportAvatarUrl
);
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $instanceUrl . '/api/v1/accounts/verify_credentials' => array('ok' => true, 'code' => 200, 'body' => json_encode($oneWayReexportAccount)),
	'GET ' . $oneWayReexportAvatarUrl => array('ok' => true, 'code' => 200, 'headers' => array('content-type' => 'image/png'), 'body' => $oneWayReexportPng),
	'GET ' . $instanceUrl . '/api/v2/instance' => array('ok' => true, 'code' => 200, 'body' => json_encode(array('configuration' => array('statuses' => array('max_characters' => 500, 'max_media_attachments' => 4))))),
	'POST ' . $instanceUrl . '/api/v1/statuses' => array(
		array('ok' => true, 'code' => 200, 'body' => json_encode(array('id' => 'oneway-delete-entry-reexported', 'url' => $instanceUrl . '/@flatpress/oneway-delete-entry-reexported', 'created_at' => '2026-05-31T18:50:00Z'))),
		array('ok' => true, 'code' => 200, 'body' => json_encode(array('id' => 'oneway-delete-comment-reexported', 'url' => $instanceUrl . '/@flatpress/oneway-delete-comment-reexported', 'created_at' => '2026-05-31T18:51:00Z')))
	)
);
$oneWayReexportResult = plugin_mastodon_run_sync(true, true);
$oneWayReexportStateAfter = isset($oneWayReexportResult ['state']) && is_array($oneWayReexportResult ['state']) ? $oneWayReexportResult ['state'] : plugin_mastodon_state_read(array($oneWayDeleteEntryId, $oneWayDeleteCommentEntryId));
$oneWayReexportRequests = simulate_recorded_http_requests();
$oneWayReexportRemoteReadRequests = 0;
$oneWayReexportPostRequests = 0;
$oneWayReexportVerifyRequests = 0;
$oneWayReexportAvatarRequests = 0;
foreach ($oneWayReexportRequests as $oneWayReexportRequest) {
	if (!is_array($oneWayReexportRequest) || empty($oneWayReexportRequest ['method']) || empty($oneWayReexportRequest ['url'])) {
		continue;
	}
	$oneWayReexportLine = strtoupper(strtoupper((string) $oneWayReexportRequest ['method']) . ' ' . (string) $oneWayReexportRequest ['url']);
	$oneWayReexportUrl = (string) $oneWayReexportRequest ['url'];
	if ($oneWayReexportLine === 'GET ' . strtoupper($instanceUrl) . '/API/V1/ACCOUNTS/VERIFY_CREDENTIALS') {
		$oneWayReexportVerifyRequests++;
		continue;
	}
	if (strtoupper((string) $oneWayReexportRequest ['method']) === 'GET' && $oneWayReexportUrl === $oneWayReexportAvatarUrl) {
		$oneWayReexportAvatarRequests++;
		continue;
	}
	if (strpos($oneWayReexportLine, 'GET ' . strtoupper($instanceUrl) . '/API/V1/ACCOUNTS/') !== false || strpos($oneWayReexportLine, '/CONTEXT') !== false || strpos($oneWayReexportLine, '/API/V1/NOTIFICATIONS') !== false) {
		$oneWayReexportRemoteReadRequests++;
	}
	if (strpos($oneWayReexportLine, 'POST ' . strtoupper($instanceUrl) . '/API/V1/STATUSES') === 0) {
		$oneWayReexportPostRequests++;
	}
}
$oneWayReexportProfileCache = plugin_mastodon_profile_cache_read(true);
$oneWayReexportCommentMeta = plugin_mastodon_state_get_comment_meta($oneWayReexportStateAfter, $oneWayDeleteCommentEntryId, $oneWayDeleteCommentId);
$allOk = test_result(
	'One-way content sync re-exports local objects whose remote mappings were unlinked after remote deletion',
		!empty($oneWayReexportResult ['ok'])
		&& $oneWayReexportRemoteReadRequests === 0
		&& $oneWayReexportPostRequests === 2
		&& $oneWayReexportVerifyRequests === 1
		&& $oneWayReexportAvatarRequests === 1
		&& isset($oneWayReexportProfileCache ['display_name']) && (string) $oneWayReexportProfileCache ['display_name'] === 'FlatPress Reexport Profile'
		&& isset($oneWayReexportStateAfter ['entries'] [$oneWayDeleteEntryId] ['remote_id'])
		&& (string) $oneWayReexportStateAfter ['entries'] [$oneWayDeleteEntryId] ['remote_id'] === 'oneway-delete-entry-reexported'
		&& isset($oneWayReexportCommentMeta ['remote_id'])
		&& (string) $oneWayReexportCommentMeta ['remote_id'] === 'oneway-delete-comment-reexported'
		&& !plugin_mastodon_state_has_dirty_entry($oneWayReexportStateAfter, $oneWayDeleteEntryId)
		&& !plugin_mastodon_state_has_dirty_comment($oneWayReexportStateAfter, $oneWayDeleteCommentEntryId, $oneWayDeleteCommentId),
	json_encode(array(
		'result' => $oneWayReexportResult,
		'remote_read_requests' => $oneWayReexportRemoteReadRequests,
		'post_requests' => $oneWayReexportPostRequests,
		'verify_requests' => $oneWayReexportVerifyRequests,
		'avatar_requests' => $oneWayReexportAvatarRequests,
		'profile_cache' => $oneWayReexportProfileCache,
		'entry_meta' => isset($oneWayReexportStateAfter ['entries'] [$oneWayDeleteEntryId]) ? $oneWayReexportStateAfter ['entries'] [$oneWayDeleteEntryId] : array(),
		'comment_meta' => $oneWayReexportCommentMeta,
		'requests' => $oneWayReexportRequests
	))
) && $allOk;
entry_delete($oneWayDeleteEntryId);
entry_delete($oneWayDeleteCommentEntryId);

// Regression test: pending descendant rechecks must not delete local comments in one-way mode.
simulate_delete_recursive($simRoot . '/fp-content/content');
mkdir($simRoot . '/fp-content/content', 0777, true);
simulate_delete_recursive($simRoot . '/fp-content/plugin_mastodon');
mkdir($simRoot . '/fp-content/plugin_mastodon', 0777, true);
plugin_mastodon_runtime_cache_clear();
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$oneWayPendingOptions = $seededOptions;
$oneWayPendingOptions ['disable_remote_import'] = '1';
$oneWayPendingOptions ['delete_sync_enabled'] = '1';
$oneWayPendingOptions ['access_token'] = 'token123';
$oneWayPendingOptions ['sync_start_date'] = '2026-01-01';
plugin_mastodon_save_options($oneWayPendingOptions);
$oneWayPendingEntry = array(
	'version' => system_ver(),
	'subject' => 'One-way pending recheck entry',
	'content' => 'Entry for pending descendant recheck.',
	'author' => 'Simulation',
	'date' => strtotime('2026-05-31 19:00:00 UTC')
);
$oneWayPendingEntryId = entry_save($oneWayPendingEntry, null);
$oneWayPendingComment = array(
	'version' => system_ver(),
	'name' => 'Simulation',
	'content' => 'This pending comment must not be deleted locally.',
	'date' => strtotime('2026-05-31 19:05:00 UTC')
);
$oneWayPendingCommentId = comment_save($oneWayPendingEntryId, $oneWayPendingComment);
$oneWayPendingState = plugin_mastodon_default_state();
$oneWayPendingState ['deletions_pending'] = 1;
$oneWayPendingState ['deletions_pending_scope'] = 'comment_rechecks';
plugin_mastodon_state_set_entry_mapping($oneWayPendingState, $oneWayPendingEntryId, 'oneway-pending-entry-existing', 'local', plugin_mastodon_entry_hash(entry_parse($oneWayPendingEntryId)), $instanceUrl . '/@flatpress/oneway-pending-entry-existing', '2026-05-31 19:00:00', plugin_mastodon_local_item_date_key(entry_parse($oneWayPendingEntryId), $oneWayPendingEntryId), '2026-05-31');
plugin_mastodon_state_set_comment_mapping($oneWayPendingState, $oneWayPendingEntryId, $oneWayPendingCommentId, 'oneway-pending-comment-missing', 'local', plugin_mastodon_comment_hash(comment_parse($oneWayPendingEntryId, $oneWayPendingCommentId)), $instanceUrl . '/@flatpress/oneway-pending-comment-missing', '2026-05-31 19:05:00', '', 'oneway-pending-entry-existing', plugin_mastodon_local_item_date_key(comment_parse($oneWayPendingEntryId, $oneWayPendingCommentId), $oneWayPendingCommentId), '2026-05-31');
plugin_mastodon_state_set_pending_comment_remote_recheck($oneWayPendingState, $oneWayPendingEntryId, $oneWayPendingCommentId, 'oneway-pending-comment-missing', 'oneway-ancestor-missing');
plugin_mastodon_state_write($oneWayPendingState);
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $instanceUrl . '/api/v1/statuses/oneway-pending-comment-missing' => array('ok' => false, 'code' => 404, 'body' => json_encode(array('error' => 'Record not found')))
);
$oneWayPendingResult = plugin_mastodon_run_deletion_sync(true);
$oneWayPendingStateAfter = isset($oneWayPendingResult ['state']) && is_array($oneWayPendingResult ['state']) ? $oneWayPendingResult ['state'] : plugin_mastodon_state_read(array($oneWayPendingEntryId));
$allOk = test_result(
	'One-way pending descendant rechecks keep local comments and queue them for re-export instead of deleting them',
		!empty($oneWayPendingResult ['ok'])
		&& comment_exists($oneWayPendingEntryId, $oneWayPendingCommentId)
		&& empty($oneWayPendingStateAfter ['pending_comment_remote_rechecks'])
		&& empty($oneWayPendingStateAfter ['comments_remote'] ['oneway-pending-comment-missing'])
		&& plugin_mastodon_state_has_dirty_comment($oneWayPendingStateAfter, $oneWayPendingEntryId, $oneWayPendingCommentId)
		&& isset($oneWayPendingStateAfter ['deletion_stats'] ['deleted_local_comments'])
		&& (int) $oneWayPendingStateAfter ['deletion_stats'] ['deleted_local_comments'] === 0,
	json_encode(array('result' => $oneWayPendingResult, 'state' => $oneWayPendingStateAfter))
) && $allOk;
entry_delete($oneWayPendingEntryId);

// Regression test: the Mastodon profile widget must use only locally cached public profile data and a locally stored avatar.
simulate_delete_recursive(ABS_PATH . FP_CONTENT . 'plugin_mastodon');
@mkdir(ABS_PATH . FP_CONTENT . 'plugin_mastodon', 0777, true);
plugin_mastodon_runtime_cache_clear();
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array();
$widgetEmpty = plugin_mastodon_widget();
$widgetEmptyRequests = simulate_recorded_http_requests();
$allOk = test_result(
	'Mastodon widget remains hidden and performs no HTTP requests while its local profile cache is missing',
	$widgetEmpty === array() && $widgetEmptyRequests === array(),
	json_encode(array('widget' => $widgetEmpty, 'requests' => $widgetEmptyRequests))
) && $allOk;

$widgetPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jz6kAAAAASUVORK5CYII=', true);
$widgetPng = is_string($widgetPng) ? $widgetPng : 'png';
$widgetAvatarUrl = $instanceUrl . '/system/accounts/avatars/original/widget-avatar.png';
$widgetAccount = array(
	'id' => 'widget-account-1',
	'username' => 'Fraenkiman',
	'acct' => 'Fraenkiman',
	'display_name' => 'Frank Hochmuth',
	'url' => $instanceUrl . '/@Fraenkiman',
	'avatar_static' => $widgetAvatarUrl,
	'avatar' => $instanceUrl . '/system/accounts/avatars/original/widget-avatar-animated.gif',
	'avatar_description' => 'Porträtfoto von Frank Hochmuth'
);
$widgetOptions = $seededOptions;
$widgetOptions ['instance_url'] = $instanceUrl;
$widgetOptions ['access_token'] = 'token123';
plugin_mastodon_save_options($widgetOptions);
plugin_mastodon_runtime_cache_clear();
$GLOBALS ['plugin_mastodon_test_profile_avatar_responses'] = array();
$GLOBALS ['plugin_mastodon_test_profile_cache_events'] = array();
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $instanceUrl . '/api/v1/accounts/verify_credentials' => array('ok' => true, 'code' => 200, 'body' => json_encode($widgetAccount)),
	'GET ' . $widgetAvatarUrl => array('ok' => true, 'code' => 200, 'headers' => array('content-type' => 'image/png'), 'body' => $widgetPng)
);
$widgetVerifyResponse = plugin_mastodon_verify_credentials($widgetOptions);
$widgetProfileCacheRaw = @file_get_contents(PLUGIN_MASTODON_PROFILE_FILE);
$widgetProfileCache = is_string($widgetProfileCacheRaw) ? json_decode($widgetProfileCacheRaw, true) : array();
$widgetProfileCache = is_array($widgetProfileCache) ? $widgetProfileCache : array();
$widgetAvatarAbsolute = isset($widgetProfileCache ['avatar_file']) ? plugin_mastodon_profile_relative_to_absolute((string) $widgetProfileCache ['avatar_file']) : '';
$widgetVerifyRequests = simulate_recorded_http_requests();
$widgetVerifyAvatarGetCount = 0;
foreach ($widgetVerifyRequests as $widgetVerifyRequest) {
	if (is_array($widgetVerifyRequest) && isset($widgetVerifyRequest ['method'], $widgetVerifyRequest ['url']) && strtoupper((string) $widgetVerifyRequest ['method']) === 'GET' && (string) $widgetVerifyRequest ['url'] === $widgetAvatarUrl) {
		$widgetVerifyAvatarGetCount++;
	}
}
$allOk = test_result(
	'Mastodon widget profile cache is refreshed from verify_credentials and stores only public profile data plus a local avatar',
	!empty($widgetVerifyResponse ['ok'])
		&& isset($widgetProfileCache ['display_name']) && (string) $widgetProfileCache ['display_name'] === 'Frank Hochmuth'
		&& isset($widgetProfileCache ['acct']) && (string) $widgetProfileCache ['acct'] === 'Fraenkiman'
		&& isset($widgetProfileCache ['profile_url']) && (string) $widgetProfileCache ['profile_url'] === $instanceUrl . '/@Fraenkiman'
		&& isset($widgetProfileCache ['avatar_file']) && (string) $widgetProfileCache ['avatar_file'] === 'plugin_mastodon/profile/avatar.png'
		&& $widgetAvatarAbsolute !== '' && is_file($widgetAvatarAbsolute)
		&& isset($widgetProfileCache ['avatar_description']) && (string) $widgetProfileCache ['avatar_description'] === 'Porträtfoto von Frank Hochmuth'
		&& is_string($widgetProfileCacheRaw) && strpos($widgetProfileCacheRaw, 'token123') === false
		&& $widgetVerifyAvatarGetCount === 1,
	json_encode(array(
		'verify_ok' => !empty($widgetVerifyResponse ['ok']),
		'cache' => $widgetProfileCache,
		'avatar_absolute' => $widgetAvatarAbsolute,
		'requests' => $widgetVerifyRequests
	))
) && $allOk;

$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array();
$widgetRendered = plugin_mastodon_widget();
$widgetRenderRequests = simulate_recorded_http_requests();
$widgetSubject = isset($widgetRendered ['subject']) ? (string) $widgetRendered ['subject'] : '';
$widgetContent = isset($widgetRendered ['content']) ? (string) $widgetRendered ['content'] : '';
$allOk = test_result(
	'Mastodon widget renders compact local-cache markup without remote API calls or inline CSS',
	$widgetSubject === 'Mastodon'
		&& strpos($widgetContent, 'mastodon-profile-widget') !== false
		&& strpos($widgetContent, 'Frank Hochmuth') !== false
		&& strpos($widgetContent, '@Fraenkiman') !== false
		&& strpos($widgetContent, 'href="' . htmlspecialchars($instanceUrl . '/@Fraenkiman', ENT_QUOTES, 'UTF-8') . '"') !== false
		&& strpos($widgetContent, 'fp-content/plugin_mastodon/profile/avatar.png') !== false
		&& strpos($widgetContent, 'alt="Porträtfoto von Frank Hochmuth"') !== false
		&& strpos($widgetContent, 'loading="lazy"') !== false
		&& strpos($widgetContent, 'decoding="async"') !== false
		&& strpos($widgetContent, '<style') === false
		&& strpos($widgetContent, 'mastodon.css') === false
		&& strpos($widgetContent, 'https://files.example') === false
		&& $widgetRenderRequests === array(),
	json_encode(array('subject' => $widgetSubject, 'content' => $widgetContent, 'requests' => $widgetRenderRequests))
) && $allOk;

$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
ob_start();
plugin_mastodon_head();
$widgetHeadMarkup = trim((string) ob_get_clean());
$widgetHeadRequests = simulate_recorded_http_requests();
$widgetCssUrl = utils_asset_ver(plugin_geturl('mastodon') . 'res/mastodon.css', SYSTEM_VER);
$allOk = test_result(
	'Mastodon widget stylesheet is loaded by plugin_mastodon_head as a versioned CSS asset',
	strpos($widgetHeadMarkup, '<link rel="stylesheet" href="' . htmlspecialchars($widgetCssUrl, ENT_QUOTES, 'UTF-8') . '">') !== false
		&& strpos($widgetHeadMarkup, 'res/mastodon.css') !== false
		&& strpos($widgetHeadMarkup, '?v=') !== false
		&& strpos($widgetHeadMarkup, '<style') === false
		&& $widgetHeadRequests === array(),
	json_encode(array('head' => $widgetHeadMarkup, 'css' => $widgetCssUrl, 'requests' => $widgetHeadRequests))
) && $allOk;

plugin_mastodon_runtime_cache_clear();
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array();
$widgetReuseOk = plugin_mastodon_refresh_profile_cache_from_account($widgetOptions, $widgetAccount);
$widgetReuseRequests = simulate_recorded_http_requests();
$allOk = test_result(
	'Mastodon widget profile refresh reuses the cached avatar when the Mastodon avatar URL is unchanged',
	$widgetReuseOk && $widgetReuseRequests === array(),
	json_encode(array('reuse_ok' => $widgetReuseOk, 'requests' => $widgetReuseRequests))
) && $allOk;

simulate_delete_recursive(ABS_PATH . FP_CONTENT . 'plugin_mastodon');
@mkdir(ABS_PATH . FP_CONTENT . 'plugin_mastodon', 0777, true);
$widgetLegacyAvatarUrl = $instanceUrl . '/system/accounts/avatars/original/widget-legacy-avatar.png';
$widgetLegacyAccount = array(
	'id' => 'widget-account-legacy',
	'username' => 'legacyuser',
	'acct' => 'legacyuser',
	'display_name' => 'Legacy User',
	'url' => $instanceUrl . '/@legacyuser',
	'avatar_static' => $widgetLegacyAvatarUrl,
	'avatar' => $widgetLegacyAvatarUrl
);
plugin_mastodon_runtime_cache_clear();
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$GLOBALS ['plugin_mastodon_test_http_responses'] = array(
	'GET ' . $widgetLegacyAvatarUrl => array('ok' => true, 'code' => 200, 'headers' => array('content-type' => 'image/png'), 'body' => $widgetPng)
);
$widgetLegacyRefreshOk = plugin_mastodon_refresh_profile_cache_from_account($widgetOptions, $widgetLegacyAccount);
$widgetLegacyRendered = plugin_mastodon_widget();
$widgetLegacyContent = isset($widgetLegacyRendered ['content']) ? (string) $widgetLegacyRendered ['content'] : '';
$widgetFallbackAlt = sprintf(plugin_mastodon_widget_lang_string('avatar_alt_format', 'Profile picture of %s'), 'Legacy User');
$allOk = test_result(
	'Mastodon widget uses a localized fallback avatar alt text for Mastodon 4.0-4.5 account payloads without avatar_description',
	$widgetLegacyRefreshOk
		&& strpos($widgetLegacyContent, 'Legacy User') !== false
		&& strpos($widgetLegacyContent, 'alt="' . htmlspecialchars($widgetFallbackAlt, ENT_QUOTES, 'UTF-8') . '"') !== false,
	json_encode(array('refresh_ok' => $widgetLegacyRefreshOk, 'fallback_alt' => $widgetFallbackAlt, 'content' => $widgetLegacyContent))
) && $allOk;

$widgetBrokenProfile = array(
	'display_name' => 'Broken User',
	'acct' => 'brokenuser',
	'profile_url' => $instanceUrl . '/@brokenuser',
	'avatar_file' => 'plugin_mastodon/profile/missing.png',
	'avatar_description' => 'missing local avatar'
);
plugin_mastodon_profile_cache_write($widgetBrokenProfile);
$GLOBALS ['plugin_mastodon_test_http_requests'] = array();
$widgetBrokenRendered = plugin_mastodon_widget();
$widgetBrokenRequests = simulate_recorded_http_requests();
$allOk = test_result(
	'Mastodon widget hides incomplete local profile caches instead of falling back to remote avatar URLs',
	$widgetBrokenRendered === array() && $widgetBrokenRequests === array(),
	json_encode(array('widget' => $widgetBrokenRendered, 'requests' => $widgetBrokenRequests))
) && $allOk;

$widgetLanguageFiles = glob($simRoot . '/fp-plugins/mastodon/lang/lang.*.php');
if (!is_array($widgetLanguageFiles)) {
	$widgetLanguageFiles = array();
}
sort($widgetLanguageFiles, SORT_STRING);
$widgetLanguageMissing = array();
foreach ($widgetLanguageFiles as $widgetLanguageFile) {
	$widgetLanguageEntries = simulate_mastodon_read_widget_language_entries((string) $widgetLanguageFile);
	foreach (array('subject', 'avatar_alt_format', 'profile_link_title') as $widgetLanguageRequiredKey) {
		if (empty($widgetLanguageEntries [$widgetLanguageRequiredKey]) || !is_string($widgetLanguageEntries [$widgetLanguageRequiredKey])) {
			$widgetLanguageMissing [] = basename((string) $widgetLanguageFile) . ':' . $widgetLanguageRequiredKey;
		}
	}
}
$widgetRegistrationOk = false;
if (isset($GLOBALS ['fp_registered_widgets']) && is_array($GLOBALS ['fp_registered_widgets']) && isset($GLOBALS ['fp_registered_widgets'] ['mastodon']) && is_array($GLOBALS ['fp_registered_widgets'] ['mastodon'])) {
	$registeredWidget = $GLOBALS ['fp_registered_widgets'] ['mastodon'];
	$widgetRegistrationOk = isset($registeredWidget ['func']) && (string) $registeredWidget ['func'] === 'plugin_mastodon_widget';
}
$allOk = test_result(
	'Mastodon widget is registered and translated in all FlatPress plugin language files',
	count($widgetLanguageFiles) === 15 && $widgetLanguageMissing === array() && $widgetRegistrationOk,
	json_encode(array(
		'language_file_count' => count($widgetLanguageFiles),
		'missing' => $widgetLanguageMissing,
		'registered' => $widgetRegistrationOk
	))
) && $allOk;

plugin_mastodon_save_options($schedulerOriginalOptions);

$GLOBALS ['plugin_mastodon_test_http_requests'] = array();

$exitCode = $allOk ? 0 : 2;
simulate_print_final_summary($exitCode);
exit($exitCode);
?>
