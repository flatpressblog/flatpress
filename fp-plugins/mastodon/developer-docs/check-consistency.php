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
$apiDocFile = __DIR__ . '/04-API-Compatibility.md';
$regressionDocFile = __DIR__ . '/05-Regression-Test-Matrix.md';
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

$pluginContent = mastodon_docs_read_file($pluginFile, $errors);
$simulationContent = mastodon_docs_read_file($simulationFile, $errors);
$apiDocContent = mastodon_docs_read_file($apiDocFile, $errors);
$regressionDocContent = mastodon_docs_read_file($regressionDocFile, $errors);
$organigramDocContent = mastodon_docs_read_file($organigramDocFile, $errors);

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
