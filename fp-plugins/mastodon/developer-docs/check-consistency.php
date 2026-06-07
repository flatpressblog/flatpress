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

if ($errors !== array()) {
	foreach ($errors as $error) {
		fwrite(STDERR, '[FAIL] ' . $error . PHP_EOL);
	}
	exit(1);
}

echo '[OK] Mastodon developer documentation is consistent with plugin.mastodon.php and simulate_mastodon_plugin.php.' . PHP_EOL;
exit(0);
?>
