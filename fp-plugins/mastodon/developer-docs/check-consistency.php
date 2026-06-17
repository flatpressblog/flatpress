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
	return $content;
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
	mastodon_docs_write_line('[OK] Mastodon developer documentation is consistent with plugin.mastodon.php and simulate_mastodon_plugin.php.', false);
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
	if (preg_match_all('/^-\s+`([A-Za-z_][A-Za-z0-9_]*)\(\)`\s+—\s+line\s+(\d+)(?:\s+—\s*(.*))?$/mu', $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
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
	return preg_match('/^-\s+`[A-Za-z_][A-Za-z0-9_]*\(\)`\s+—\s+line\s+\d+\s+—\s+\S/mu', $section) === 1;
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

$pluginContent = mastodon_docs_read_file($pluginFile, $errors);
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

if ($pluginContent !== '' && $organigramDocContent !== '') {
	$actualFunctions = mastodon_docs_extract_functions($pluginContent);
	$documentedFunctions = mastodon_docs_extract_organigram_functions($organigramDocContent);

	$adminMethods = array('setup' => true, 'main' => true, 'onsubmit' => true);
	$totalFunctions = count($actualFunctions);
	$topLevelFunctions = 0;
	foreach ($actualFunctions as $name => $line) {
		if (!isset($adminMethods [$name])) {
			$topLevelFunctions++;
		}
	}

	if (!preg_match('/currently contains \*\*' . preg_quote((string) $totalFunctions, '/') . '\*\* callable functions\/methods/', $organigramDocContent)) {
		$errors [] = 'Function organigram total count is not ' . $totalFunctions . '.';
	}
	if (!preg_match('/- \*\*' . preg_quote((string) $topLevelFunctions, '/') . '\*\* top-level plugin functions/', $organigramDocContent)) {
		$errors [] = 'Function organigram top-level count is not ' . $topLevelFunctions . '.';
	}

	foreach ($actualFunctions as $name => $line) {
		if (!isset($documentedFunctions [$name])) {
			$errors [] = 'Function organigram missing function: ' . $name;
			continue;
		}
		foreach ($documentedFunctions [$name] as $documentedLine) {
			if ((int) $documentedLine !== (int) $line) {
				$errors [] = 'Function organigram line mismatch for "' . $name . '": documented ' . $documentedLine . ', actual ' . $line;
			}
		}
	}
	foreach ($documentedFunctions as $name => $lines) {
		if (!isset($actualFunctions [$name])) {
			$errors [] = 'Function organigram documents unknown function: ' . $name;
		}
	}

	$functionEntriesWithoutDescription = mastodon_docs_extract_function_entries_without_description($organigramDocContent);
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
		if (!mastodon_docs_section_contains_described_function_entries($organigramDocContent, $heading)) {
			$errors [] = 'Function organigram section has no described function entries: ' . $heading;
		}
	}

	$referencedFunctions = mastodon_docs_extract_backticked_function_references($organigramDocContent);
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
			'regression-test simulator copy must stay content-identical to the root harness after CRLF/LF line-ending normalization',
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
			'comments without that marker stay local'
		)
	),
	'01-Process-Map.md' => array(
		$processMapDocContent,
		array(
			'Visitor comment Mastodon opt-in',
			'comments without that marker are accepted locally'
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

$guardIsolationRequiredDocs = array(
	'05-Regression-Test-Matrix.md' => array(
		$regressionDocContent,
		array(
			'dirty-comment scheduled-run regression clears the `content` sync guard',
			'APCu guard can survive sandbox directory cleanup'
		)
	),
	'06-Process-Flow.md' => array(
		$flowDocContent,
		array(
			'Dirty-comment scheduled fixture clears content guard',
			'earlier cooldown assertions cannot survive through APCu'
		)
	)
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
			'without outbound Mastodon `DELETE` requests',
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
			'version-gated DELETE /api/v1/media/:id'
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

if ($errors !== array()) {
	foreach ($errors as $error) {
		mastodon_docs_write_failure($error);
	}
	exit(1);
}

mastodon_docs_write_success();
exit(0);
?>
