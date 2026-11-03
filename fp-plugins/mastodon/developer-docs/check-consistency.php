<?php
/**
 * Verify that the Mastodon developer documentation matches the current plugin
 * and simulation harness.
 *
 * This script intentionally has no Composer dependency so it can run on
 * shared-hosting-style PHP installations as well as development machines.
 *
 * Usage:
 *   php fp-plugins/mastodon/developer-docs/check-consistency.php
 */

$rootDir = dirname(__DIR__, 3);
$pluginFile = $rootDir . '/fp-plugins/mastodon/plugin.mastodon.php';
$simulationFile = $rootDir . '/simulate_mastodon_plugin.php';
$regressionSimulationFile = $rootDir . '/fp-plugins/mastodon/regression-test/simulate_mastodon_plugin.php';
$regressionSimulationFallbackFile = $rootDir . '/fp-plugins/mastodon/regression-test/simulate_mastodon_plugin.ph_';
if (!is_file($regressionSimulationFile) && is_file($regressionSimulationFallbackFile)) {
	$regressionSimulationFile = $regressionSimulationFallbackFile;
}
// The distributed .ph_ harness is sufficient; a root copy is optional.
if (!is_file($simulationFile)) {
	$simulationFile = $regressionSimulationFile;
}
$commentTemplateFile = $rootDir . '/fp-interface/themes/leggero/comments.tpl';
$externalUrlModifierFile = $rootDir . '/fp-includes/fp-smartyplugins/modifier.is_external_url.php';
$mentalModelDocFile = __DIR__ . '/00-Mental-Model.md';
$processMapDocFile = __DIR__ . '/01-Process-Map.md';
$stateModelDocFile = __DIR__ . '/02-State-Model.md';
$apiDocFile = __DIR__ . '/04-API-Compatibility.md';
$regressionDocFile = __DIR__ . '/05-Regression-Test-Matrix.md';
$flowDocFile = __DIR__ . '/06-Process-Flow.md';
$organigramDocFile = __DIR__ . '/07-Function-Organigram.md';

$errors = array();

function mastodon_docs_read_file($file, &$errors) {
	if (!is_file($file)) {
		$errors [] = 'Missing file: ' . $file;
		return '';
	}
	$content = file_get_contents($file);
	if (!is_string($content)) {
		$errors [] = 'Unable to read file: ' . $file;
		return '';
	}
	return mastodon_docs_normalize_line_endings($content);
}

/**
 * Read the literal module list from the loader without executing plugin code.
 *
 * @param string $pluginFile
 * @param array<int, string> $errors
 * @return array<string, string>
 */
function mastodon_docs_read_plugin_sources($pluginFile, &$errors) {
	$main = mastodon_docs_read_file($pluginFile, $errors);
	$sources = array('plugin.mastodon.php' => $main);
	$matches = array();
	preg_match_all("~^[ \t]*require_once __DIR__ \\. '/(inc/[a-z-]+\\.php)';$~m", $main, $matches);
	foreach ($matches [1] as $relative) {
		if (isset($sources [$relative])) {
			$errors [] = 'Duplicate module include: ' . $relative;
			continue;
		}
		$sources [$relative] = mastodon_docs_read_file(dirname($pluginFile) . '/' . $relative, $errors);
	}
	$moduleFiles = glob(dirname($pluginFile) . '/inc/*.php');
	foreach (is_array($moduleFiles) ? $moduleFiles : array() as $moduleFile) {
		$relative = 'inc/' . basename($moduleFile);
		if (!isset($sources [$relative])) {
			$errors [] = 'Module is not explicitly loaded: ' . $relative;
		}
	}
	return $sources;
}

function mastodon_docs_normalize_line_endings($content) {
	return str_replace(array("\r\n", "\r"), "\n", $content);
}

function mastodon_docs_is_cli() {
	return PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg';
}

function mastodon_docs_prepare_text_output() {
	if (!mastodon_docs_is_cli() && !headers_sent()) {
		header('Content-Type: text/plain; charset=UTF-8');
	}
}

function mastodon_docs_write_line($message, $preferStderr) {
	$line = $message . PHP_EOL;
	mastodon_docs_prepare_text_output();

	if ($preferStderr && mastodon_docs_is_cli()) {
		if (defined('STDERR')) {
			$stderr = constant('STDERR');
			if (is_resource($stderr)) {
				fwrite($stderr, $line);
				return;
			}
		}
		$stderrHandle = @fopen('php://stderr', 'ab');
		if (is_resource($stderrHandle)) {
			fwrite($stderrHandle, $line);
			fclose($stderrHandle);
			return;
		}
	}

	echo $line;
	if (!mastodon_docs_is_cli()) {
		flush();
	}
}

function mastodon_docs_write_failure($message) {
	mastodon_docs_write_line('[FAIL] ' . $message, true);
}

function mastodon_docs_write_success() {
	mastodon_docs_write_line('[OK] Mastodon developer documentation is consistent with plugin.mastodon.php and the distributed simulation harness.', false);
}

function mastodon_docs_line_for_offset($content, $offset) {
	return substr_count(substr($content, 0, (int) $offset), "\n") + 1;
}

function mastodon_docs_extract_test_results($content) {
	$tests = array();
	if (preg_match_all('/test_result\s*\(\s*([\'"])(.*?)\1/s', $content, $matches, PREG_OFFSET_CAPTURE)) {
		foreach ($matches [2] as $index => $match) {
			$name = (string) $match [0];
			$offset = isset($matches [0] [$index] [1]) ? (int) $matches [0] [$index] [1] : (int) $match [1];
			$tests [$name] = mastodon_docs_line_for_offset($content, $offset);
		}
	}
	ksort($tests, SORT_STRING);
	return $tests;
}

function mastodon_docs_extract_regression_matrix($content) {
	$tests = array();
	if (preg_match_all('/^\|\s*(\d+)\s*\|\s*(.*?)\s*\|\s*$/m', $content, $matches, PREG_SET_ORDER)) {
		foreach ($matches as $match) {
			$name = trim((string) $match [2]);
			if ($name === '' || $name === 'Static test name') {
				continue;
			}
			$tests [$name] = (int) $match [1];
		}
	}
	ksort($tests, SORT_STRING);
	return $tests;
}

function mastodon_docs_extract_functions($content) {
	$functions = array();
	if (preg_match_all('/^[ \t]*function\s+([A-Za-z_][A-Za-z0-9_]*)\s*\(/m', $content, $matches, PREG_OFFSET_CAPTURE)) {
		foreach ($matches [1] as $match) {
			$name = (string) $match [0];
			$offset = (int) $match [1];
			$functions [$name] = mastodon_docs_line_for_offset($content, $offset);
		}
	}
	ksort($functions, SORT_STRING);
	return $functions;
}

function mastodon_docs_extract_function_declarations($content) {
	$declarations = array();
	if (preg_match_all('/^[ \t]*function\s+([A-Za-z_][A-Za-z0-9_]*)\s*\(/m', $content, $matches, PREG_OFFSET_CAPTURE)) {
		foreach ($matches [1] as $match) {
			$declarations [] = array(
				'name' => (string) $match [0],
				'line' => mastodon_docs_line_for_offset($content, (int) $match [1])
			);
		}
	}
	return $declarations;
}

/**
 * Extract the function-line anchors used by 07-Function-Organigram.md.
 *
 * Use physical source lines, including compatibility guards and analyzer
 * directives, so developers can navigate directly to each declaration.
 *
 * @param string $content
 * @return array<string, int>
 */
function mastodon_docs_extract_organigram_function_lines($content) {
	return mastodon_docs_extract_functions($content);
}

function mastodon_docs_extract_organigram_functions($content) {
	$functions = array();
	if (preg_match_all('/`([A-Za-z_][A-Za-z0-9_]*)\(\)`\s+—\s+line\s+(\d+)/u', $content, $matches, PREG_SET_ORDER)) {
		foreach ($matches as $match) {
			$name = (string) $match [1];
			$line = (int) $match [2];
			if (!isset($functions [$name])) {
				$functions [$name] = array();
			}
			$functions [$name] [] = $line;
		}
	}
	ksort($functions, SORT_STRING);
	return $functions;
}

function mastodon_docs_extract_function_entries_without_description($content) {
	$missing = array();
	if (preg_match_all('/^-\s+`([A-Za-z_][A-Za-z0-9_]*)\(\)`\s+—\s+line\s+(\d+)\s+in\s+`[^`]+`(?:\s+—\s*(.*))?$/mu', $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
		foreach ($matches as $match) {
			$name = (string) $match [1] [0];
			$description = isset($match [3] [0]) ? trim((string) $match [3] [0]) : '';
			if ($description === '') {
				if (!isset($missing [$name])) {
					$missing [$name] = array();
				}
				$missing [$name] [] = mastodon_docs_line_for_offset($content, (int) $match [0] [1]);
			}
		}
	}
	ksort($missing, SORT_STRING);
	return $missing;
}

function mastodon_docs_section_contains_described_function_entries($content, $heading) {
	$position = strpos($content, $heading);
	if ($position === false) {
		return false;
	}
	$next = strpos($content, "\n## ", $position + strlen($heading));
	$section = $next === false ? substr($content, $position) : substr($content, $position, $next - $position);
	return preg_match('/^-\s+`[A-Za-z_][A-Za-z0-9_]*\(\)`\s+—\s+line\s+\d+\s+in\s+`[^`]+`\s+—\s+\S/mu', $section) === 1;
}

function mastodon_docs_extract_backticked_function_references($content) {
	$functions = array();
	if (preg_match_all('/`([A-Za-z_][A-Za-z0-9_]*)\(\)`/u', $content, $matches, PREG_OFFSET_CAPTURE)) {
		foreach ($matches [1] as $match) {
			$name = (string) $match [0];
			$offset = (int) $match [1];
			if (!isset($functions [$name])) {
				$functions [$name] = array();
			}
			$functions [$name] [] = mastodon_docs_line_for_offset($content, $offset);
		}
	}
	ksort($functions, SORT_STRING);
	return $functions;
}

/** Return one Markdown section including its subsections, without executing code. */
function mastodon_docs_section_text($content, $heading) {
	$match = array();
	if (!preg_match('/^' . preg_quote($heading, '/') . '[ 	]*\n/m', $content, $match, PREG_OFFSET_CAPTURE)) {
		return '';
	}
	$start = $match [0] [1] + strlen($match [0] [0]);
	$next = array();
	if (preg_match('/^#{1,' . strspn($heading, '#') . '} /m', $content, $next, PREG_OFFSET_CAPTURE, $start)) {
		return substr($content, $start, $next [0] [1] - $start);
	}
	return substr($content, $start);
}

/** Build GitHub-style heading anchors used by this English Markdown set. */
function mastodon_docs_markdown_anchors($content) {
	$anchors = array();
	$counts = array();
	$headings = array();
	// Code blocks do not create Markdown heading anchors.
	$content = preg_replace('/^```[^\n]*\n.*?^```[^\n]*$/ms', '', $content);
	preg_match_all('/^#{1,6}[ 	]+(.+?)[ 	]*#*[ 	]*$/m', $content, $headings);
	foreach ($headings [1] as $heading) {
		$text = strip_tags($heading);
		$text = function_exists('mb_strtolower') ? mb_strtolower($text, 'UTF-8') : strtolower($text);
		$slug = str_replace(' ', '-', (string) preg_replace('/[^\p{L}\p{N}_ -]/u', '', $text));
		$count = isset($counts [$slug]) ? $counts [$slug] : 0;
		$counts [$slug] = $count + 1;
		$anchors [$slug . ($count > 0 ? '-' . $count : '')] = true;
	}
	$explicit = array();
	preg_match_all('/\b(?:id|name)=["\x27]([^"\x27]+)["\x27]/', $content, $explicit);
	foreach ($explicit [1] as $anchor) {
		$anchors [$anchor] = true;
	}
	return $anchors;
}

$pluginSources = mastodon_docs_read_plugin_sources($pluginFile, $errors);
// Media safety keeps a separately checked appendix in the function organigram.
$catalogSources = $pluginSources;
unset($catalogSources ['inc/media-safety.php']);
$pluginContent = implode("\n", $catalogSources);
$simulationContent = mastodon_docs_read_file($simulationFile, $errors);
$regressionSimulationContent = mastodon_docs_read_file($regressionSimulationFile, $errors);
$mentalModelDocContent = mastodon_docs_read_file($mentalModelDocFile, $errors);
$processMapDocContent = mastodon_docs_read_file($processMapDocFile, $errors);
$stateModelDocContent = mastodon_docs_read_file($stateModelDocFile, $errors);
$apiDocContent = mastodon_docs_read_file($apiDocFile, $errors);
$regressionDocContent = mastodon_docs_read_file($regressionDocFile, $errors);
$flowDocContent = mastodon_docs_read_file($flowDocFile, $errors);
$organigramDocContent = mastodon_docs_read_file($organigramDocFile, $errors);
$commentTemplateContent = mastodon_docs_read_file($commentTemplateFile, $errors);
$externalUrlModifierContent = mastodon_docs_read_file($externalUrlModifierFile, $errors);

if ($simulationContent !== '' && $regressionSimulationContent !== ''
	&& mastodon_docs_normalize_line_endings($simulationContent) !== mastodon_docs_normalize_line_endings($regressionSimulationContent)) {
	$errors [] = 'Regression-test simulator copy differs from root simulate_mastodon_plugin.php.';
}

if ($simulationContent !== '' && $regressionDocContent !== '') {
	$actualTests = mastodon_docs_extract_test_results($simulationContent);
	$documentedTests = mastodon_docs_extract_regression_matrix($regressionDocContent);

	foreach ($actualTests as $name => $line) {
		if (!isset($documentedTests [$name])) {
			$errors [] = 'Regression matrix missing test: ' . $name;
			continue;
		}
		if ((int) $documentedTests [$name] !== (int) $line) {
			$errors [] = 'Regression matrix line mismatch for "' . $name . '": documented ' . $documentedTests [$name] . ', actual ' . $line;
		}
	}
	foreach ($documentedTests as $name => $line) {
		if (!isset($actualTests [$name])) {
			$errors [] = 'Regression matrix documents unknown test: ' . $name;
		}
	}
}

$mainOrganigramDocContent = explode('## Media safety helper catalog', $organigramDocContent, 2) [0];

if ($pluginContent !== '' && $mainOrganigramDocContent !== '') {
	$actualFunctions = array();
	$actualFunctionDeclarations = array();
	$actualFunctionFiles = array();
	foreach ($catalogSources as $sourceFile => $sourceContent) {
		foreach (mastodon_docs_extract_functions($sourceContent) as $name => $line) {
			if (isset($actualFunctions [$name]) && strpos($name, 'plugin_mastodon_') === 0) {
				$errors [] = 'Duplicate plugin function: ' . $name;
			}
			$actualFunctions [$name] = $line;
			$actualFunctionFiles [$name] = $sourceFile;
		}
		$actualFunctionDeclarations = array_merge($actualFunctionDeclarations, mastodon_docs_extract_function_declarations($sourceContent));
	}
	$actualOrganigramFunctionLines = $actualFunctions;
	$documentedFunctions = mastodon_docs_extract_organigram_functions($mainOrganigramDocContent);
	$locations = array();
	preg_match_all('/`([A-Za-z_][A-Za-z0-9_]*)\(\)`\s+—\s+line\s+(\d+)\s+in\s+`([^`]+)`/u', $mainOrganigramDocContent, $locations, PREG_SET_ORDER);
	$documentedLocationNames = array();
	foreach ($locations as $location) {
		$name = $location [1];
		$documentedLocationNames [$name] = true;
		if (!isset($actualFunctionFiles [$name]) || $actualFunctionFiles [$name] !== $location [3]) {
			$errors [] = 'Function organigram source-file mismatch for ' . $name . ': ' . $location [3];
		}
	}

	$adminMethods = array('setup' => true, 'main' => true, 'onsubmit' => true);
	$totalCallableNames = count($actualFunctions);
	$topLevelFunctions = 0;
	$adminMethodDeclarations = 0;
	foreach ($actualFunctionDeclarations as $declaration) {
		$name = isset($declaration ['name']) ? (string) $declaration ['name'] : '';
		if (isset($adminMethods [$name])) {
			$adminMethodDeclarations++;
		} else {
			$topLevelFunctions++;
		}
	}

	if (!preg_match('/\*\*' . preg_quote((string) $totalCallableNames, '/') . '\*\* distinct callable names/', $mainOrganigramDocContent)) {
		$errors [] = 'Function organigram unique callable-name count is not ' . $totalCallableNames . '.';
	}
	if (!preg_match('/currently contains \*\*' . preg_quote((string) count($actualFunctionDeclarations), '/') . '\*\* named function\/method declarations/', $mainOrganigramDocContent)) {
		$errors [] = 'Function organigram declaration count is not ' . count($actualFunctionDeclarations) . '.';
	}
	if (!preg_match('/- \*\*' . preg_quote((string) $topLevelFunctions, '/') . '\*\* top-level plugin functions/', $mainOrganigramDocContent)) {
		$errors [] = 'Function organigram top-level count is not ' . $topLevelFunctions . '.';
	}
	if (!preg_match('/- \*\*' . preg_quote((string) $adminMethodDeclarations, '/') . '\*\* admin action methods implemented with the shared method names/', $mainOrganigramDocContent)) {
		$errors [] = 'Function organigram admin action method count is not ' . $adminMethodDeclarations . '.';
	}

	foreach ($actualFunctions as $name => $line) {
		if (!isset($documentedLocationNames [$name])) {
			$errors [] = 'Function organigram missing source-file location: ' . $name;
		}
		if (!isset($documentedFunctions [$name])) {
			$errors [] = 'Function organigram missing function: ' . $name;
			continue;
		}
		$organigramLine = isset($actualOrganigramFunctionLines [$name]) ? (int) $actualOrganigramFunctionLines [$name] : (int) $line;
		foreach ($documentedFunctions [$name] as $documentedLine) {
			if ((int) $documentedLine !== $organigramLine) {
				$errors [] = 'Function organigram line mismatch for "' . $name . '": documented ' . $documentedLine . ', catalog anchor ' . $organigramLine . ', physical source ' . $line;
			}
		}
	}
	foreach ($documentedFunctions as $name => $lines) {
		if (!isset($actualFunctions [$name])) {
			$errors [] = 'Function organigram documents unknown function: ' . $name;
		}
	}

	$functionEntriesWithoutDescription = mastodon_docs_extract_function_entries_without_description($mainOrganigramDocContent);
	foreach ($functionEntriesWithoutDescription as $name => $lines) {
		$errors [] = 'Function organigram entry for "' . $name . '" has no description on line(s): ' . implode(', ', $lines);
	}

	$requiredOrganigramSections = array(
		'## A. Entry points and admin integration',
		'## B. Defaults, configuration, secrets, and centralized FlatPress feature toggles',
		'## C. Caching, filesystem helpers, logging, and persisted state',
		'## D. Date, timestamp, visibility, and threading helpers',
		'## E. Text, URLs, language strings, tags, emojis, and BBCode/HTML conversion',
		'## F. Local content access, media processing, hashing, and export ordering',
		'## G. HTTP transport, PHP timeout budgeting, instance capability lookup, status-length budgeting, OAuth, Mastodon API calls, and media upload',
		'## H. Import/export builders and synchronization orchestration',
		'## Alphabetical appendix / Generated function catalog'
	);
	foreach ($requiredOrganigramSections as $heading) {
		if (!mastodon_docs_section_contains_described_function_entries($mainOrganigramDocContent, $heading)) {
			$errors [] = 'Function organigram section has no described function entries: ' . $heading;
		}
	}

	$referencedFunctions = mastodon_docs_extract_backticked_function_references($mainOrganigramDocContent);
	foreach ($referencedFunctions as $name => $lines) {
		if (!isset($actualFunctions [$name])) {
			$errors [] = 'Function organigram references unknown function "' . $name . '" on line(s): ' . implode(', ', $lines);
		}
	}
}

$oneWayRequiredDocs = array(
	'00-Mental-Model.md' => array(
		$mentalModelDocContent,
		array(
			'disable_remote_import',
			'FlatPress-to-Mastodon export active',
			'one-way admin UI hides import-only controls',
			'remotely deleted status must not delete the still-existing FlatPress object'
		)
	),
	'01-Process-Map.md' => array(
		$processMapDocContent,
		array(
			'Content sync when `disable_remote_import` is off',
			'admin save preserves hidden import options',
			'one-way mode unlinks stale mappings and queues re-export'
		)
	),
	'02-State-Model.md' => array(
		$stateModelDocContent,
		array(
			'Explicit one-way mode state lifecycle',
			'Admin save with hidden import UI',
			'one-way remote-missing repair',
			'import-only companion diagnostics',
			'do not create a tombstone'
		)
	),
	'06-Process-Flow.md' => array(
		$flowDocContent,
		array(
			'optional `disable_remote_import` setting is an explicit direction gate',
			'Admin UI direction gate',
			'mastodon_remote_import_options_hidden marker',
			'plugin_mastodon_companion_plugins_status() hides import-only helpers',
			'Unlink stale remote mapping and queue dirty_entries',
			'Unlink stale remote mapping and queue dirty_comments'
		)
	),
	'07-Function-Organigram.md' => array(
		$organigramDocContent,
		array(
			'disable_remote_import enabled?',
			'hides import-only companion diagnostics',
			'plugin_mastodon_admin_apply_save_post()',
			'plugin_mastodon_should_import_remote_to_local()',
			'plugin_mastodon_state_unlink_entry_remote_for_reexport()',
			'plugin_mastodon_state_unlink_comment_remote_for_reexport()'
		)
	)
);
foreach ($oneWayRequiredDocs as $docName => $docData) {
	$docContent = (string) $docData [0];
	$requiredSnippets = $docData [1];
	foreach ($requiredSnippets as $requiredSnippet) {
		if ($docContent !== '' && strpos($docContent, (string) $requiredSnippet) === false) {
			$errors [] = $docName . ' missing one-way-mode documentation snippet: ' . (string) $requiredSnippet;
		}
	}
}

$commentReplySyncRequiredDocs = array(
	'00-Mental-Model.md' => array(
		$mentalModelDocContent,
		array(
			'disable_comment_reply_sync',
			'Comment/reply sync gate',
			'keeps entry sync active'
		)
	),
	'01-Process-Map.md' => array(
		$processMapDocContent,
		array(
			'Comment/reply synchronization gate',
			'disable_comment_reply_sync',
			'Comment/reply disable'
		)
	),
	'02-State-Model.md' => array(
		$stateModelDocContent,
		array(
			'Comment/reply synchronization gate lifecycle',
			'clear `dirty_comments`',
			'skip context descendants and notification replies'
		)
	),
	'03-Function-Process-Matrix.md' => array(
		mastodon_docs_read_file(__DIR__ . '/03-Function-Process-Matrix.md', $errors),
		array(
			'plugin_mastodon_should_sync_comments_and_replies',
			'Central option gate for all comment/reply synchronization boundaries'
		)
	),
	'04-API-Compatibility.md' => array(
		$apiDocContent,
		array(
			'Comment/reply synchronization gate compatibility',
			'does not require any new Mastodon API capability',
			'Mastodon `>= 4.0.0` compatibility is preserved'
		)
	),
	'05-Regression-Test-Matrix.md' => array(
		$regressionDocContent,
		array(
			'Disabled comment/reply sync keeps local comments off Mastodon',
			'Disabled comment/reply sync imports Mastodon entries without fetching reply contexts or notifications',
			'Disabled comment/reply sync skips remote reply deletion follow-up'
		)
	),
	'06-Process-Flow.md' => array(
		$flowDocContent,
		array(
			'Comment/reply synchronization gate',
			'disable_comment_reply_sync enabled?',
			'Skip comment/reply deletion follow-up'
		)
	),
	'07-Function-Organigram.md' => array(
		$organigramDocContent,
		array(
			'plugin_mastodon_normalize_disable_comment_reply_sync()',
			'plugin_mastodon_should_sync_comments_and_replies()',
			'plugin_mastodon_state_remove_dirty_comments_for_entry()'
		)
	),
	'README.md' => array(
		mastodon_docs_read_file(__DIR__ . '/README.md', $errors),
		array(
			'Disable comment/reply synchronization',
			'comment/reply gate lifecycle'
		)
	)
);
foreach ($commentReplySyncRequiredDocs as $docName => $docData) {
	$docContent = (string) $docData [0];
	$requiredSnippets = $docData [1];
	foreach ($requiredSnippets as $requiredSnippet) {
		if ($docContent !== '' && strpos($docContent, (string) $requiredSnippet) === false) {
			$errors [] = $docName . ' missing comment/reply-sync gate documentation snippet: ' . (string) $requiredSnippet;
		}
	}
}

$commentReplyOptinRequiredDocs = array(
	'00-Mental-Model.md' => array(
		$mentalModelDocContent,
		array(
			'{comment_mastodon}',
			'comment_reply_optins',
			'Visitor comments without a grant stay local'
		)
	),
	'01-Process-Map.md' => array(
		$processMapDocContent,
		array(
			'Visitor comment Mastodon opt-in',
			'Direct saves work without CommentCenter'
		)
	),
	'02-State-Model.md' => array(
		$stateModelDocContent,
		array(
			'Comment-to-reply opt-in markers',
			'comment_reply_optins',
			'no error is shown'
		)
	),
	'03-Function-Process-Matrix.md' => array(
		mastodon_docs_read_file(__DIR__ . '/03-Function-Process-Matrix.md', $errors),
		array(
			'plugin_mastodon_comment_to_reply_optin_required',
			'plugin_mastodon_local_comment_export_allowed'
		)
	),
	'04-API-Compatibility.md' => array(
		$apiDocContent,
		array(
			'Visitor comment opt-in compatibility',
			'do not add a Mastodon endpoint'
		)
	),
	'05-Regression-Test-Matrix.md' => array(
		$regressionDocContent,
		array(
			'Mastodon comment opt-in is required only when local comments may be exported',
			'Local visitor comment without Mastodon opt-in is not eligible for Mastodon export'
		)
	),
	'06-Process-Flow.md' => array(
		$flowDocContent,
		array(
			'Visitor opt-in for FlatPress comment-to-Mastodon reply export',
			'comment_reply_optins[entry:comment]'
		)
	),
	'07-Function-Organigram.md' => array(
		$organigramDocContent,
		array(
			'plugin_mastodon_optin_comment_to_reply()',
			'plugin_mastodon_state_set_comment_reply_optin()',
			'plugin_mastodon_local_comment_export_allowed()'
		)
	),
	'README.md' => array(
		mastodon_docs_read_file(__DIR__ . '/README.md', $errors),
		array(
			'Require visitor approval before exporting comments to Mastodon',
			'comment_reply_optins'
		)
	)
);
foreach ($commentReplyOptinRequiredDocs as $docName => $docData) {
	$docContent = (string) $docData [0];
	$requiredSnippets = $docData [1];
	foreach ($requiredSnippets as $requiredSnippet) {
		if ($docContent !== '' && strpos($docContent, (string) $requiredSnippet) === false) {
			$errors [] = $docName . ' missing comment-to-reply opt-in documentation snippet: ' . (string) $requiredSnippet;
		}
	}
}

$commentReplyOptinLifecycleRequiredDocs = array(
	'01-Process-Map.md' => array(
		$processMapDocContent,
		array(
			'preserves the checked state after unrelated validation errors',
			'Direct saves work without CommentCenter',
			'removed on rejection/orphan pruning'
		)
	),
	'02-State-Model.md' => array(
		$stateModelDocContent,
		array(
			'remains checked when another validator redisplays the submitted form',
			'rejection or a later bounded sync prune removes orphaned pending grants',
			'pending_file',
			'commentcenter_comment_discarded',
			'bounded orphan pruner'
		)
	),
	'03-Function-Process-Matrix.md' => array(
		mastodon_docs_read_file(__DIR__ . '/03-Function-Process-Matrix.md', $errors),
		array(
			'plugin_mastodon_on_commentcenter_comment_discarded',
			'plugin_mastodon_state_prune_orphaned_pending_comment_reply_optins'
		)
	),
	'05-Regression-Test-Matrix.md' => array(
		$regressionDocContent,
		array(
			'Visitor Mastodon opt-in checkbox preserves POST state and hook priorities/signatures are explicit',
			'Visitor opt-in persists through the direct FlatPress save path when CommentCenter is disabled',
			'CommentCenter rejection and bounded pruning remove orphaned pending opt-in grants'
		)
	),
	'06-Process-Flow.md' => array(
		$flowDocContent,
		array(
			'commentcenter_comment_discarded',
			'retain checked POST state',
			'bounded sync-time prune'
		)
	),
	'07-Function-Organigram.md' => array(
		$organigramDocContent,
		array(
			'plugin_mastodon_on_commentcenter_comment_discarded()',
			'plugin_mastodon_state_finalize_comment_reply_optin()',
			'plugin_mastodon_state_prune_orphaned_pending_comment_reply_optins()'
		)
	),
	'README.md' => array(
		mastodon_docs_read_file(__DIR__ . '/README.md', $errors),
		array(
			'direct path without CommentCenter',
			'rejection/orphan cleanup'
		)
	)
);
foreach ($commentReplyOptinLifecycleRequiredDocs as $docName => $docData) {
	$docContent = (string) $docData [0];
	$requiredSnippets = $docData [1];
	foreach ($requiredSnippets as $requiredSnippet) {
		if ($docContent !== '' && strpos($docContent, (string) $requiredSnippet) === false) {
			$errors [] = $docName . ' missing comment opt-in lifecycle documentation snippet: ' . (string) $requiredSnippet;
		}
	}
}

$guardIsolationRequiredDocs = array(
	'05-Regression-Test-Matrix.md' => array(
		$regressionDocContent,
		array(
			'dirty-comment scheduled-run regression clears the `content` sync guard',
			'sandbox directory cleanup and produce `sync_cooldown`'
		)
	),

);
foreach ($guardIsolationRequiredDocs as $docName => $docData) {
	$docContent = (string) $docData [0];
	$requiredSnippets = $docData [1];
	foreach ($requiredSnippets as $requiredSnippet) {
		if ($docContent !== '' && strpos($docContent, (string) $requiredSnippet) === false) {
			$errors [] = $docName . ' missing scheduler-guard isolation documentation snippet: ' . (string) $requiredSnippet;
		}
	}
}

$fixtureIsolationRequiredImplementation = array(
	"\$GLOBALS ['plugin_mastodon_test_now'] = strtotime('2026-06-16 12:00:00 UTC');",
	"unset(\$GLOBALS ['plugin_mastodon_test_now']);",
	'plugin_mastodon_save_options($adminPreviousOptions);',
	'$regressionCopySourceRoot = dirname(__DIR__, 3);'
);
foreach ($fixtureIsolationRequiredImplementation as $requiredSnippet) {
	if ($simulationContent !== '' && strpos($simulationContent, $requiredSnippet) === false) {
		$errors [] = 'Simulation harness missing deterministic fixture-isolation snippet: ' . $requiredSnippet;
	}
}

$fixtureIsolationRequiredDocs = array(
	'README.md' => array(
		mastodon_docs_read_file(__DIR__ . '/README.md', $errors),
		array(
			'05-Regression-Test-Matrix.md#simulation-parameters',
			'regression-test/simulate_mastodon_plugin.ph_'
		)
	),
	'05-Regression-Test-Matrix.md' => array(
		$regressionDocContent,
		array(
			'freezes the plugin clock around date-window-sensitive fixture groups',
			'snapshots and restores mutable option fixtures',
			'Date-window fixture isolation policy',
			'Mutable option fixture isolation policy',
		)
	),
	'06-Process-Flow.md' => array(
		$flowDocContent,
		array('05-Regression-Test-Matrix.md#harness-model')
	)
);
foreach ($fixtureIsolationRequiredDocs as $docName => $docData) {
	$docContent = (string) $docData [0];
	$requiredSnippets = $docData [1];
	foreach ($requiredSnippets as $requiredSnippet) {
		if ($docContent !== '' && strpos($docContent, (string) $requiredSnippet) === false) {
			$errors [] = $docName . ' missing deterministic fixture-isolation documentation snippet: ' . (string) $requiredSnippet;
		}
	}
}

$importedRemoteReplyDeleteRequiredDocs = array(
	'02-State-Model.md' => array(
		$stateModelDocContent,
		array(
			'imported-reply local delete',
			'local_deleted_imported_remote_ignored',
			'without an outbound status `DELETE`',
			'Context refreshes must preserve existing `source=local` comment ownership'
		)
	),
	'05-Regression-Test-Matrix.md' => array(
		$regressionDocContent,
		array(
			'Locally deleted imported remote replies',
			'edited-remote-reply case',
			'without remote DELETE',
			'preserves `source=local` ownership'
		)
	),
	'06-Process-Flow.md' => array(
		$flowDocContent,
		array(
			'locally deleted imported remote replies',
			'comment source remote and entry still exists?',
			'preserve existing source ownership'
		)
	)
);
foreach ($importedRemoteReplyDeleteRequiredDocs as $docName => $docData) {
	$docContent = (string) $docData [0];
	$requiredSnippets = $docData [1];
	foreach ($requiredSnippets as $requiredSnippet) {
		if ($docContent !== '' && strpos($docContent, (string) $requiredSnippet) === false) {
			$errors [] = $docName . ' missing imported-remote-reply local-delete documentation snippet: ' . (string) $requiredSnippet;
		}
	}
}

$exportedCommentDeleteInvariantRequiredDocs = array(
	'00-Mental-Model.md' => array(
		$mentalModelDocContent,
		array(
			'local_deleted_pending_remote_delete',
			'one Mastodon reply ID may belong to exactly one local comment mapping'
		)
	),
	'01-Process-Map.md' => array(
		$processMapDocContent,
		array(
			'`comments_remote` is the authoritative one-to-one owner index',
			'loads only that entry\'s comment shard'
		)
	),
	'02-State-Model.md' => array(
		$stateModelDocContent,
		array(
			'Locally deleted mapped replies',
			'local_deleted_pending_remote_delete',
			'One remote reply ID may be owned by exactly one local comment key'
		)
	),
	'03-Function-Process-Matrix.md' => array(
		mastodon_docs_read_file(__DIR__ . '/03-Function-Process-Matrix.md', $errors),
		array(
			'plugin_mastodon_state_comment_mapping_conflict',
			'plugin_mastodon_protect_missing_local_exported_comment_by_remote_id'
		)
	),
	'04-API-Compatibility.md' => array(
		$apiDocContent,
		array(
			'Exported-comment deletion invariant compatibility',
			'Mastodon `>= 4.0.0` compatibility is preserved'
		)
	),
	'05-Regression-Test-Matrix.md' => array(
		$regressionDocContent,
		array(
			'Normal manual partial content sync keeps an old locally deleted exported comment tombstoned without stale re-import',
			'Automatic scheduled partial content sync keeps an old locally deleted exported comment tombstoned without stale re-import',
			'Remote import defensively tombstones a missing source=local comment when the deletion hook was bypassed',
			'Comment mapping rejects duplicate remote ids and removes the previous reverse id on a legitimate remap'
		)
	),
	'06-Process-Flow.md' => array(
		$flowDocContent,
		array(
			'local_deleted_pending_remote_delete',
			'the import guard loads at most the shard named'
		)
	),
	'07-Function-Organigram.md' => array(
		$organigramDocContent,
		array(
			'plugin_mastodon_state_comment_mapping_conflict()',
			'plugin_mastodon_protect_missing_local_exported_comment_by_remote_id()'
		)
	),
	'README.md' => array(
		mastodon_docs_read_file(__DIR__ . '/README.md', $errors),
		array(
			'exported-comment deletion invariant'
		)
	)
);
foreach ($exportedCommentDeleteInvariantRequiredDocs as $docName => $docData) {
	$docContent = (string) $docData [0];
	$requiredSnippets = $docData [1];
	foreach ($requiredSnippets as $requiredSnippet) {
		if ($docContent !== '' && strpos($docContent, (string) $requiredSnippet) === false) {
			$errors [] = $docName . ' missing exported-comment deletion-invariant documentation snippet: ' . (string) $requiredSnippet;
		}
	}
}

$exportedCommentDeleteInvariantPluginSnippets = array(
	'function plugin_mastodon_state_comment_mapping_conflict',
	'function plugin_mastodon_protect_missing_local_exported_comment_by_remote_id',
	"'local_deleted_pending_remote_delete'",
	'Refusing duplicate remote comment mapping'
);
foreach ($exportedCommentDeleteInvariantPluginSnippets as $requiredPluginSnippet) {
	if ($pluginContent !== '' && strpos($pluginContent, $requiredPluginSnippet) === false) {
		$errors [] = 'Plugin missing exported-comment deletion-invariant implementation snippet: ' . $requiredPluginSnippet;
	}
}

$exportedCommentDeleteInvariantSimulationSnippets = array(
	'Normal manual partial content sync keeps an old locally deleted exported comment tombstoned without stale re-import',
	'Automatic scheduled partial content sync keeps an old locally deleted exported comment tombstoned without stale re-import',
	'Remote import defensively tombstones a missing source=local comment when the deletion hook was bypassed',
	'Comment mapping rejects duplicate remote ids and removes the previous reverse id on a legitimate remap'
);
foreach ($exportedCommentDeleteInvariantSimulationSnippets as $requiredSimulationSnippet) {
	if ($simulationContent !== '' && strpos($simulationContent, $requiredSimulationSnippet) === false) {
		$errors [] = 'Simulation missing exported-comment deletion-invariant regression: ' . $requiredSimulationSnippet;
	}
}

$urlPunctuationBudgetRequiredDocs = array(
	'00-Mental-Model.md' => array(
		$mentalModelDocContent,
		array(
			'plugin_mastodon_status_text_url_spans()',
			'sentence punctuation immediately after a link is normal text'
		)
	),
	'01-Process-Map.md' => array(
		$processMapDocContent,
		array(
			'URL-aware text budgeting',
			'punctuation after `https://...`'
		)
	),
	'03-Function-Process-Matrix.md' => array(
		mastodon_docs_read_file(__DIR__ . '/03-Function-Process-Matrix.md', $errors),
		array(
			'plugin_mastodon_status_text_url_spans',
			'URL punctuation-budget regression tests'
		)
	),
	'04-API-Compatibility.md' => array(
		$apiDocContent,
		array(
			'configuration.statuses.characters_reserved_per_url',
			'trailing sentence punctuation such as `,` or `.` outside the URL span'
		)
	),
	'05-Regression-Test-Matrix.md' => array(
		$regressionDocContent,
		array(
			'Mastodon URL budgeting counts trailing punctuation outside the URL placeholder',
			'URL-aware status budgeting at punctuation boundaries'
		)
	),
	'06-Process-Flow.md' => array(
		$flowDocContent,
		array(
			'plugin_mastodon_status_text_url_spans',
			'trim punctuation boundaries'
		)
	),
	'07-Function-Organigram.md' => array(
		$organigramDocContent,
		array(
			'plugin_mastodon_status_text_url_spans()',
			'leave trailing sentence punctuation outside the URL placeholder budget'
		)
	),
	'README.md' => array(
		mastodon_docs_read_file(__DIR__ . '/README.md', $errors),
		array(
			'Mastodon rejects a post as over 500 characters when URLs are present',
			'URL-budget documentation'
		)
	)
);
foreach ($urlPunctuationBudgetRequiredDocs as $docName => $docData) {
	$docContent = (string) $docData [0];
	$requiredSnippets = $docData [1];
	foreach ($requiredSnippets as $requiredSnippet) {
		if ($docContent !== '' && strpos($docContent, (string) $requiredSnippet) === false) {
			$errors [] = $docName . ' missing URL-punctuation budget documentation snippet: ' . (string) $requiredSnippet;
		}
	}
}

$urlPunctuationBudgetPluginSnippets = array(
	'function plugin_mastodon_status_text_url_spans(',
	'plugin_mastodon_status_text_url_spans($text)',
	'Sentence punctuation appended after an URL'
);
foreach ($urlPunctuationBudgetPluginSnippets as $requiredPluginSnippet) {
	if ($pluginContent !== '' && strpos($pluginContent, $requiredPluginSnippet) === false) {
		$errors [] = 'Plugin missing URL-punctuation budget implementation snippet: ' . $requiredPluginSnippet;
	}
}

$urlPunctuationBudgetSimulationSnippets = array(
	'Mastodon URL budgeting counts trailing punctuation outside the URL placeholder',
	'$punctuatedUrlSpans = plugin_mastodon_status_text_url_spans($punctuatedUrlStatus);'
);
foreach ($urlPunctuationBudgetSimulationSnippets as $requiredSimulationSnippet) {
	if ($simulationContent !== '' && strpos($simulationContent, $requiredSimulationSnippet) === false) {
		$errors [] = 'Simulation missing URL-punctuation budget regression: ' . $requiredSimulationSnippet;
	}
}

$instanceCapabilityRequiredDocs = array(
	'01-Process-Map.md' => array(
		$processMapDocContent,
		array(
			'prefers `api_versions[mastodon]` for capabilities',
			'negatively caches failed live instance lookups per request'
		)
	),
	'03-Function-Process-Matrix.md' => array(
		mastodon_docs_read_file(__DIR__ . '/03-Function-Process-Matrix.md', $errors),
		array(
			'plugin_mastodon_instance_document_api_version',
			'per-request failed cache'
		)
	),
	'04-API-Compatibility.md' => array(
		$apiDocContent,
		array(
			'api_versions[mastodon] >= 4',
			'Unattached media cleanup delete',
			'plugin_mastodon_instance_supports_mastodon_api_v4()',
			'plugin_mastodon_instance_supports_media_delete()',
			'negatively cached per PHP request',
			'failed live lookup is negatively cached'
		)
	),
	'05-Regression-Test-Matrix.md' => array(
		$regressionDocContent,
		array(
			'Nightly Mastodon versions use cached api_versions',
			'Machine-readable api_versions are preferred',
			'Failed instance-information lookups are negatively cached',
			'api_versions below unattached media delete support skip uploaded media cleanup DELETE requests',
			'Unknown unattached media delete capability stays best-effort without an instance lookup'
		)
	),
	'06-Process-Flow.md' => array(
		$flowDocContent,
		array(
			'api_versions[mastodon]',
			'Failed lookup already cached',
			'short instance timeout',
			'plugin_mastodon_instance_supports_mastodon_api_v4()',
			'plugin_mastodon_instance_supports_media_delete()',
			'Capability-gated DELETE /api/v1/media/:id'
		)
	),
	'07-Function-Organigram.md' => array(
		$organigramDocContent,
		array(
			'plugin_mastodon_instance_document_api_version()',
			'plugin_mastodon_normalized_instance_version()',
			'plugin_mastodon_instance_supports_mastodon_api_v4()',
			'plugin_mastodon_instance_supports_media_delete()'
		)
	)
);
foreach ($instanceCapabilityRequiredDocs as $docName => $docData) {
	$docContent = (string) $docData [0];
	$requiredSnippets = $docData [1];
	foreach ($requiredSnippets as $requiredSnippet) {
		if ($docContent !== '' && strpos($docContent, (string) $requiredSnippet) === false) {
			$errors [] = $docName . ' missing instance-capability documentation snippet: ' . (string) $requiredSnippet;
		}
	}
}

if ($apiDocContent !== '') {
	if (strpos($apiDocContent, 'plugin_mastodon_authorize_url') !== false) {
		$errors [] = 'API compatibility doc still references plugin_mastodon_authorize_url.';
	}
	if (strpos($apiDocContent, 'plugin_mastodon_exchange_code ') !== false || strpos($apiDocContent, 'plugin_mastodon_exchange_code|') !== false) {
		$errors [] = 'API compatibility doc still references plugin_mastodon_exchange_code.';
	}
	if (strpos($apiDocContent, 'plugin_mastodon_build_authorize_url()') === false) {
		$errors [] = 'API compatibility doc does not reference plugin_mastodon_build_authorize_url().';
	}
	if (strpos($apiDocContent, 'plugin_mastodon_exchange_code_for_token()') === false) {
		$errors [] = 'API compatibility doc does not reference plugin_mastodon_exchange_code_for_token().';
	}
}

$importedStatusFooterRequiredDocs = array(
	'00-Mental-Model.md' => array(
		$mentalModelDocContent,
		array(
			'Imported status source link',
			'Status.url` source link'
		)
	),
	'01-Process-Map.md' => array(
		$processMapDocContent,
		array(
			'Imported status source footer',
			'plugin_mastodon_imported_status_footer_bbcode()'
		)
	),
	'03-Function-Process-Matrix.md' => array(
		mastodon_docs_read_file(__DIR__ . '/03-Function-Process-Matrix.md', $errors),
		array(
			'plugin_mastodon_imported_status_footer_bbcode',
			'target=_blank rel="nofollow noopener noreferrer"'
		)
	),
	'04-API-Compatibility.md' => array(
		$apiDocContent,
		array(
			'Imported status source links',
			'Status.url` for the automatic source footer'
		)
	),
	'05-Regression-Test-Matrix.md' => array(
		$regressionDocContent,
		array(
			'Imported Mastodon status footer BBCode opens the single toot in a new tab',
			'Imported Mastodon status footer renders target blank HTML for the single toot'
		)
	),
	'06-Process-Flow.md' => array(
		$flowDocContent,
		array(
			'Status.url single-toot source link',
			'Append [url=... target=_blank rel=&quot;nofollow noopener noreferrer&quot;]Mastodon[/url]'
		)
	),
	'07-Function-Organigram.md' => array(
		$organigramDocContent,
		array(
			'plugin_mastodon_imported_status_footer_bbcode()',
			'Build the FlatPress BBCode footer that links an imported entry back to its source Mastodon status with a new-tab target.'
		)
	),
	'README.md' => array(
		mastodon_docs_read_file(__DIR__ . '/README.md', $errors),
		array(
			'Imported status source links open the single toot in a new tab',
			'plugin_mastodon_imported_status_footer_bbcode()'
		)
	)
);
foreach ($importedStatusFooterRequiredDocs as $docName => $docData) {
	$docContent = (string) $docData [0];
	$requiredSnippets = $docData [1];
	foreach ($requiredSnippets as $requiredSnippet) {
		if ($docContent !== '' && strpos($docContent, (string) $requiredSnippet) === false) {
			$errors [] = $docName . ' missing imported-status-footer documentation snippet: ' . (string) $requiredSnippet;
		}
	}
}

if ($pluginContent !== '') {
	$importedStatusFooterRequiredPluginSnippets = array(
		'function plugin_mastodon_imported_status_footer_bbcode(',
		'target=_blank rel="nofollow noopener noreferrer"',
		'$footer = plugin_mastodon_imported_status_footer_bbcode($url);'
	);
	foreach ($importedStatusFooterRequiredPluginSnippets as $requiredPluginSnippet) {
		if (strpos($pluginContent, $requiredPluginSnippet) === false) {
			$errors [] = 'Plugin missing imported-status-footer implementation snippet: ' . $requiredPluginSnippet;
		}
	}
}

$externalCommentAuthorLinkRequiredDocs = array(
	'00-Mental-Model.md' => array(
		$mentalModelDocContent,
		array(
			'Imported Mastodon reply author links',
			'only external profile URLs receive `target="_blank"`'
		)
	),
	'01-Process-Map.md' => array(
		$processMapDocContent,
		array(
			'Frontend comment author-link rendering',
			'unknown same-host root paths'
		)
	),
	'05-Regression-Test-Matrix.md' => array(
		$regressionDocContent,
		array(
			'Comment author target blank is limited to external URLs',
			'unknown same-host root paths'
		)
	),
	'06-Process-Flow.md' => array(
		$flowDocContent,
		array(
			'Comment author link target decision',
			'Known FlatPress route, entry point, asset path, or existing static page?'
		)
	),
	'README.md' => array(
		mastodon_docs_read_file(__DIR__ . '/README.md', $errors),
		array(
			'Comment author links open new tabs only for external URLs',
			'modifier.is_external_url.php'
		)
	)
);
foreach ($externalCommentAuthorLinkRequiredDocs as $docName => $docData) {
	$docContent = (string) $docData [0];
	$requiredSnippets = $docData [1];
	foreach ($requiredSnippets as $requiredSnippet) {
		if ($docContent !== '' && strpos($docContent, (string) $requiredSnippet) === false) {
			$errors [] = $docName . ' missing external comment-author-link documentation snippet: ' . (string) $requiredSnippet;
		}
	}
}

if ($commentTemplateContent !== '') {
	if (strpos($commentTemplateContent, '$url|is_external_url') === false) {
		$errors [] = 'Leggero comments template does not use the is_external_url modifier for comment author links.';
	}
	if (strpos($commentTemplateContent, 'target="_blank"') === false) {
		$errors [] = 'Leggero comments template does not add target="_blank" for external comment author links.';
	}
	if (strpos($commentTemplateContent, 'rel="nofollow noopener noreferrer"') === false) {
		$errors [] = 'Leggero comments template does not pair external target="_blank" links with noopener/noreferrer.';
	}
}

if ($externalUrlModifierContent !== '') {
	$requiredModifierSnippets = array(
		'function smarty_modifier_is_external_url(',
		'Relative and root-relative links stay inside the current blog.',
		'smarty_modifier_is_external_url_blog_bases()',
		'smarty_modifier_is_external_url_root_blog_url_is_internal(',
		'smarty_modifier_is_external_url_path_matches_flatpress_route(',
		'smarty_modifier_is_external_url_path_is_inside('
	);
	foreach ($requiredModifierSnippets as $requiredModifierSnippet) {
		if (strpos($externalUrlModifierContent, $requiredModifierSnippet) === false) {
			$errors [] = 'External URL Smarty modifier missing required implementation snippet: ' . $requiredModifierSnippet;
		}
	}
}

// Check summaries as well as the detailed catalog: stale headline totals must fail.
if ($simulationContent !== '' && $regressionDocContent !== '') {
	$staticTests = mastodon_docs_extract_test_results($simulationContent);
	$staticCount = count($staticTests);
	$optionalName = 'Configured credentials verify (read-only smoke test)';
	$optionalCount = isset($staticTests [$optionalName]) ? 1 : 0;
	$regularCount = $staticCount - $optionalCount;
	$summaryPatterns = array(
		'/Static `test_result\(\)` calls found in the script:\s*`' . $staticCount . '`/' => 'static total',
		'/^\[OK\]:\s*' . $regularCount . '\s*$/m' => 'normal-run OK total',
		'/^\|\s*Regular simulation tests\s*\|\s*' . $regularCount . '\s*\|/m' => 'regular category total',
		'/^\|\s*Optional live\/auth smoke\s*\|\s*' . $optionalCount . '\s*\|/m' => 'optional category total',
		'/^\|\s*Total static `test_result\(\)` calls\s*\|\s*' . $staticCount . '\s*\|/m' => 'static category total'
	);
	foreach ($summaryPatterns as $pattern => $description) {
		if (!preg_match($pattern, $regressionDocContent)) {
			$errors [] = 'Regression harness summary has a stale or missing ' . $description . '.';
		}
	}
}

// Compare every optional executable copy against the distributed source.
if (is_file($regressionSimulationFallbackFile)) {
	$canonicalHarness = mastodon_docs_read_file($regressionSimulationFallbackFile, $errors);
	foreach (array($rootDir . '/simulate_mastodon_plugin.php', $rootDir . '/fp-plugins/mastodon/regression-test/simulate_mastodon_plugin.php') as $optionalCopy) {
		if (is_file($optionalCopy)) {
			$copyContent = mastodon_docs_read_file($optionalCopy, $errors);
			if (mastodon_docs_normalize_line_endings($copyContent) !== mastodon_docs_normalize_line_endings($canonicalHarness)) {
				$errors [] = 'Optional simulator copy differs from distributed .ph_ harness: ' . $optionalCopy;
			}
		}
	}
}

// Feature details are checked at their canonical owners after documentation consolidation.
$documentationOwnershipRequiredDocs = array(
	'README.md' => array(
		mastodon_docs_read_file(__DIR__ . '/README.md', $errors),
		array('### PHPStan on an installed instance', 'scanFiles:',
			'../.setup/lib/permissions.lib.php', '05-Regression-Test-Matrix.md#recommended-commands')
	),
	'02-State-Model.md' => array(
		$stateModelDocContent,
		array('## Public profile cache lifecycle', 'profile/profile.json', 'profile/avatar.*',
			'## UTC timestamp policy', '## Compact scheduler fields', 'source_state_signature',
			'local_entry_deletions', 'deletion_retry_not_before', 'import_fingerprint',
			'avatar_source_url', 'avatar_mime_type', 'avatar_bytes', 'fetched_at')
	),
	'03-Function-Process-Matrix.md' => array(
		mastodon_docs_read_file(__DIR__ . '/03-Function-Process-Matrix.md', $errors),
		array('Comment shards and migration', 'Admin/CLI maintenance', 'P8', 'P7/API',
			'### Entry point and loading behavior', '### Module responsibilities',
			"class_exists('AdminPanelAction')", 'OPcache remains optional')
	),
	'04-API-Compatibility.md' => array(
		$apiDocContent,
		array('PLUGIN_MASTODON_WINDOW_MEDIA_UPLOAD_BUDGET', 'PLUGIN_MASTODON_WINDOW_DELETE_BUDGET',
			'PLUGIN_MASTODON_INSTANCE_REQUEST_TIMEOUT', 'Account statuses `exclude_direct`',
			'02-State-Model.md#public-profile-cache-lifecycle')
	),
	'05-Regression-Test-Matrix.md' => array(
		$regressionDocContent,
		array('### Simulation parameters', '### Recommended commands',
			'content-identical after CRLF/LF normalization', '## Historical verification events',
			'278 regular plugin simulations in that October 26 event',
			'69 Mermaid blocks in that historical documentation set',
			'check-module-loading.ph_ frontend', 'check-module-loading.ph_ repeat',
			'### Modularization verification', 'SHA-256',
			'Subsequent', 'formatting edits are outside that historical byte-for-byte comparison',
			'The baseline and each of the three successive stages',
			'complete regression and PHPStan results were obtained before')
	),
	'07-Function-Organigram.md' => array(
		$organigramDocContent,
		array('04-API-Compatibility.md#endpoint-matrix', '03-Function-Process-Matrix.md#change-impact-examples')
	)
);
foreach ($documentationOwnershipRequiredDocs as $docName => $requirement) {
	foreach ($requirement [1] as $snippet) {
		if (strpos($requirement [0], $snippet) === false) {
			$errors [] = $docName . ' missing canonical documentation snippet: ' . $snippet;
		}
	}
}

// The canonical module map must follow the actual explicit loader inventory.
$moduleMapDoc = mastodon_docs_read_file(__DIR__ . '/03-Function-Process-Matrix.md', $errors);
$moduleMapSection = mastodon_docs_section_text($moduleMapDoc, '### Module responsibilities');
$mappedModules = array();
$moduleRows = array();
preg_match_all('/^\|\s*\[`(inc\/[^`]+)`\]/m', $moduleMapSection, $moduleRows);
foreach ($moduleRows [1] as $moduleName) {
	if (isset($mappedModules [$moduleName])) {
		$errors [] = 'Module responsibility table duplicates module: ' . $moduleName;
	}
	$mappedModules [$moduleName] = true;
	if (!isset($pluginSources [$moduleName])) {
		$errors [] = 'Module responsibility table lists an unloaded module: ' . $moduleName;
	}
}
foreach ($pluginSources as $moduleName => $source) {
	if ($moduleName !== 'plugin.mastodon.php' && !isset($mappedModules [$moduleName])) {
		$errors [] = 'Module responsibility table missing module: ' . $moduleName;
	}
}

$stateFieldTable = mastodon_docs_section_text($stateModelDocContent, '## `state.json` fields');
$entryMetadataTable = mastodon_docs_section_text($stateModelDocContent, '### Entry metadata in `entries`');
foreach (array('local_entry_deletions', 'deletion_retry_not_before') as $field) {
	if (!preg_match('/^\|\s*' . preg_quote($field, '/') . '\s*\|/m', $stateFieldTable)) {
		$errors [] = 'Main state field table missing field: ' . $field;
	}
}
if (!preg_match('/^\|\s*`import_fingerprint`\s*\|/m', $entryMetadataTable)) {
	$errors [] = 'Entry metadata table missing import_fingerprint.';
}

// Review the entire Markdown set, not only the function organigram.
$documentationFiles = glob(__DIR__ . '/*.md');
$mediaSafetyContent = mastodon_docs_read_file($rootDir . '/fp-plugins/mastodon/inc/media-safety.php', $errors);
$allPluginFunctions = array_merge(mastodon_docs_extract_functions($pluginContent), mastodon_docs_extract_functions($mediaSafetyContent));
$mediaSafetySections = explode('## Media safety helper catalog', $organigramDocContent, 2);
$mediaSafetyDoc = isset($mediaSafetySections [1]) ? $mediaSafetySections [1] : '';
if ($mediaSafetyDoc === '') {
	$errors [] = 'Function organigram missing media safety helper catalog.';
}
$functionProcessDoc = mastodon_docs_read_file(__DIR__ . '/03-Function-Process-Matrix.md', $errors);
foreach (mastodon_docs_extract_functions($mediaSafetyContent) as $helper => $helperLine) {
	if (strpos($functionProcessDoc, '`' . $helper . '()`') === false) {
		$errors [] = 'Function/process matrix missing media safety helper: ' . $helper;
	}
	if (strpos($mediaSafetyDoc, '`' . $helper . '()`') === false) {
		$errors [] = 'Media safety catalog missing function: ' . $helper;
	}
}
$mediaSafetyRequiredDocs = array(
	'04-API-Compatibility.md' => array(
		$apiDocContent,
		array('## Import: streamed downloads', '## Export: poster validation',
			'## Instance information and limits', '## Export: video metadata',
			'## Export without cURL: memory-aware multipart fallback',
			'PLUGIN_MASTODON_DOWNLOAD_IMAGE_BYTES', 'PLUGIN_MASTODON_DOWNLOAD_AV_BYTES',
			'PLUGIN_MASTODON_DOWNLOAD_TOTAL_BYTES', 'PLUGIN_MASTODON_DOWNLOAD_REQUESTS',
			'PLUGIN_MASTODON_MULTIPART_MAX_BYTES', 'PLUGIN_MASTODON_MULTIPART_MEMORY_RESERVE')
	),
	'02-State-Model.md' => array(
		$stateModelDocContent,
		array('## Media retries and instance freshness', 'import_fingerprint', 'last_remote_status_id')
	),
	'05-Regression-Test-Matrix.md' => array(
		$regressionDocContent,
		array('## Media verification and evidence limitations', 'media-transport-test.py',
			'media-transport-test.ph_', 'ffprobe')
	)
);
foreach ($mediaSafetyRequiredDocs as $docName => $requirement) {
	foreach ($requirement [1] as $snippet) {
		if (strpos($requirement [0], $snippet) === false) {
			$errors [] = $docName . ' missing media safety documentation snippet: ' . $snippet;
		}
	}
}
$localAnchorCache = array();
if (is_array($documentationFiles)) {
	foreach ($documentationFiles as $documentationFile) {
		$docText = mastodon_docs_read_file($documentationFile, $errors);
		$docName = basename($documentationFile);
		$references = mastodon_docs_extract_backticked_function_references($docText);
		foreach ($references as $functionName => $referenceLines) {
			if (strpos($functionName, 'plugin_mastodon_') === 0 && !isset($allPluginFunctions [$functionName])) {
				$errors [] = $docName . ' references unknown plugin helper ' . $functionName . '.';
			}
		}
		$links = array();
		if (preg_match_all('/\]\(([^)]+)\)/', $docText, $links)) {
			foreach ($links [1] as $link) {
				$link = trim((string) $link, "<> \t\r\n");
				if ($link === '' || preg_match('/^[a-z][a-z0-9+.-]*:/i', $link)) {
					continue;
				}
				$parts = explode('#', $link, 2);
				$relativePath = rawurldecode($parts [0]);
				$target = $relativePath === '' ? $documentationFile : __DIR__ . '/' . $relativePath;
				if (!is_file($target)) {
					$errors [] = $docName . ' links to a missing local file: ' . $relativePath;
					continue;
				}
				if (isset($parts [1]) && $parts [1] !== '' && strtolower(pathinfo($target, PATHINFO_EXTENSION)) === 'md') {
					if (!isset($localAnchorCache [$target])) {
						$targetContent = mastodon_docs_read_file($target, $errors);
						$localAnchorCache [$target] = mastodon_docs_markdown_anchors($targetContent);
					}
					$anchor = rawurldecode($parts [1]);
					if (!isset($localAnchorCache [$target] [$anchor])) {
						$errors [] = $docName . ' links to a missing local section: ' . $link;
					}
				}
			}
		}
		if (strpos($docText, '```mermaid') !== false && substr_count($docText, '```') % 2 !== 0) {
			$errors [] = $docName . ' has an unclosed fenced code block.';
		}
		if ($docName !== '02-State-Model.md' && strpos($docText, '[more]') === false && strpos($docText, 'More') === false) {
			$errors [] = $docName . ' omits the entry More boundary.';
		}
	}
}

$readmeContent = mastodon_docs_read_file(__DIR__ . '/README.md', $errors);
$phpstanConfig = mastodon_docs_read_file($rootDir . '/.dist/phpstan.neon.dist', $errors);
if (preg_match('~smarty-([0-9]+\.[0-9]+\.[0-9]+)/src~', $phpstanConfig, $smartyMatch)
	&& strpos($readmeContent, 'scans Smarty ' . $smartyMatch [1] . ' types') === false) {
	$errors [] = 'README Smarty scan version differs from phpstan.neon.dist.';
}
if (strpos($readmeContent, 'fp-plugins/mastodon/regression-test/simulate_mastodon_plugin.ph_') === false) {
	$errors [] = 'README must document the distributed simulator entry point.';
}

// Semantic guardrails for the installed-instance documentation audit.
// These targeted claims/edges complement structural checks; they are not a proof of all prose.
$semanticDocRules = array(
	array('F01', '02-State-Model.md', array('diagnostics read the main state and can trigger legacy migration', 'refreshes `scheduler-state.json`'), array('Diagnostics are read-only', 'Writes only the compact main state')),
	array('F02', '02-State-Model.md', array('full / comment_rechecks'), array('full\\entries\\comments')),
	array('F03', '07-Function-Organigram.md', array('FixedDue -- yes --> DeleteRun', 'FixedDue -- no --> Due'), array('Fast --> Due{')),
	array('F04', '06-Process-Flow.md', array('Cooldown -- "Clear" --> GuardMark --> Lock', 'Mark content cooldown: scheduled_content_sync'), array('GuardClear')),
	array('F05', '06-Process-Flow.md', array('EntryDeadline -- "No" --> KeepEntry', 'EntryDeadline -- "Yes" --> DeleteRemoteEntry', 'Remaining -- "Yes" --> KeepPending', 'Remaining -- "No" --> ClearPending'), array('EntryLocal -- "No" --> DeleteRemoteEntry', 'Complete -- "No failures" --> ClearPending')),
	array('F06', '07-Function-Organigram.md', array('Shard and main-state writes are not one transaction', 'individual atomic file replacement'), array('atomic shard/main-state write')),
	array('F07', '04-API-Compatibility.md', array('A present machine-readable API version takes precedence', 'only when it is absent'), array('confirms Mastodon `>= 4.6.0` or')),
	array('F08', '04-API-Compatibility.md', array('Content-Length is present and contains only decimal digits', 'helper does not compare non-numeric values'), array('When Content-Length is present, it must match')),
	array('F09', '04-API-Compatibility.md', array('without an API-version gate'), array('Mastodon 4.6/API-v9')),
);
// Follow-up review: related summaries and missing control-flow branches.
$followupDocRules = array(
	array('R01', '02-State-Model.md', array('Repair persistence requires `$persist=true`', 'no diagnostic errors', 'legacy reads may migrate state'), array('read-only diagnostics')),
	array('R02', '02-State-Model.md', array('ObjectKind -->|entry| EntryDeadline', 'ObjectKind -->|comment| ImportedRemoteDelete', 'no comment tombstone'), array('Map --> LocalDelete --> ImportedRemoteDelete')),
	array('R03', '02-State-Model.md', array('Orchestrators initialize/persist; import, export and deletion/recheck helpers increment.'), array('Deep helper functions that cannot know the whole run result.')),
	array('R04', '02-State-Model.md', array('pending-recheck parent shards when comment sync is enabled'), array('Loads only active-window and dirty parent-entry shards.')),
	array('R04b', '07-Function-Organigram.md', array('Later remote thread processing can load additional shards on demand.', 'pending-recheck parent shards'), array('load scheduled/dirty/known-thread shards')),
	array('R05', '06-Process-Flow.md', array('GeneralDeleteDue -- "Yes" --> FixedDelete', 'GeneralDeleteDue -- "No" --> End'), array('	Due -- "No" --> End')),
	array('R06', '06-Process-Flow.md', array('Force, deletions_pending or an entry deletion deadline?', 'DeleteCooldown -- "Yes" --> Return', 'Lock -- "No" --> Return', 'LockOpen -- "No" --> LockError', 'due requires pending work or an entry deadline'), array('Force or deletions_pending?', 'Due -- "Yes" --> Lock --> RateGuard', 'but deletions_pending and not-before due check still apply', 'Force bypasses the early pending gate and cooldown guard; due requires')),
	array('R07', '06-Process-Flow.md', array('CommentDeadline -- "Yes" --> SkipFutureShard', 'retain and skip each recheck with a future parent entry deadline', 'ShardDeadline -- "yes: skip shard" --> ShardList', 'load only eligible recheck shards'), array('CommentLoop --> CommentScope', 'ShardList --> Skip --> Load')),
	array('R08', '04-API-Compatibility.md', array('only without it does version >= 4.4.0 apply', 'only when it is absent', 'only without it does the cached/stored version'), array('`api_versions[mastodon] >= 4` or version >= 4.4.0 enables cleanup', 'data confirms `api_versions.mastodon >= 10`')),
);
$semanticDocRules = array_merge($semanticDocRules, $followupDocRules);
foreach ($semanticDocRules as $rule) {
	$semanticText = mastodon_docs_read_file(__DIR__ . '/' . $rule [1], $errors);
	foreach ($rule [2] as $requiredClaim) {
		if (strpos($semanticText, $requiredClaim) === false) {
			$errors [] = $rule [0] . ' ' . $rule [1] . ' missing audited semantic claim/edge: ' . $requiredClaim;
		}
	}
	foreach ($rule [3] as $obsoleteClaim) {
		if (strpos($semanticText, $obsoleteClaim) !== false) {
			$errors [] = $rule [0] . ' ' . $rule [1] . ' retains contradicted claim/edge: ' . $obsoleteClaim;
		}
	}
}

if ($errors !== array()) {
	foreach ($errors as $error) {
		mastodon_docs_write_failure($error);
	}
	exit(1);
}

mastodon_docs_write_success();
exit(0);
?>
