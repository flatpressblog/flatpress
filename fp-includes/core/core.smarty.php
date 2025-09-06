<?php
// Minimum required Smarty version for FlatPress core.
if (!defined('FP_SMARTY_MIN_VERSION')) {
	define('FP_SMARTY_MIN_VERSION', '5.5.1');
}

/**
 * Internal Bootstrap error output, usable before core.system.php is loaded.
 */
function utils_boot_failure($msg) {
	if (!headers_sent()) {
		header('HTTP/1.1 500 Internal Server Error');
		header('Content-Type: text/plain; charset=utf-8');
	}
	echo "FlatPress bootstrap error: " . (string)$msg;
	exit(1);
}

/**
 * Finds the non-Composer stub of Smarty under fp-includes/smarty-X.X.X/libs/Smarty.class.php
 * and returns [path, version string] or [null, null].
 */
function utils_smarty_find_stub() {
	$base = rtrim(ABS_PATH . FP_INCLUDES, '/\\');
	$dirs = @glob($base . '/smarty-*', GLOB_ONLYDIR | GLOB_NOSORT);
	$bestStub = null;
	$bestVer = null;
	if (is_array($dirs)) {
		foreach ($dirs as $dir) {
			$name = basename($dir); // e.g. smarty-5.5.1, smarty-6.0.0-rc1
			if (preg_match('/^smarty-(\d+\.\d+\.\d+)(?:[._-].*)?$/i', $name, $m)) {
				$ver = $m [1];
				$stub = rtrim($dir, '/\\') . '/libs/Smarty.class.php';
				if (is_file($stub) && is_readable($stub)) {
					if ($bestVer === null || version_compare($ver, $bestVer, '>')) {
						$bestVer = $ver;
						$bestStub = $stub;
					}
				}
			}
		}
	}
	return array($bestStub, $bestVer);
}

/**
 * Checks and loads Smarty 5 or higher (without Composer). Ensures that at least $minVersion is available.
 * Uses the PSR-4 stub from fp-includes/smarty-X.X.X/libs/Smarty.class.php.
 */
function utils_checksmarty($minVersion = FP_SMARTY_MIN_VERSION) {
	// Class already there? (e.g., because it was manually integrated beforehand)
	if (!class_exists(\Smarty\Smarty::class, false)) {
		list($stub, $verFromDir) = utils_smarty_find_stub();
		if (!$stub) {
			utils_boot_failure('Smarty stub not found. Expected under ' . ABS_PATH . FP_INCLUDES . 'smarty-*/libs/Smarty.class.php');
		}
		require_once $stub;
	}

	// After the require, the class must exist.
	if (!class_exists(\Smarty\Smarty::class)) {
		utils_boot_failure('Smarty could not be loaded ((\Smarty\Smarty::class) is missing).');
	}

	// Determine version: prefers class constant, otherwise fallback from directory name
	$detected = null;
	if (defined(\Smarty\Smarty::class . '::SMARTY_VERSION')) { // PHP 7.2+: class constant check
		$detected = \Smarty\Smarty::SMARTY_VERSION;
	} else {
		// If utils_smarty_find_stub() ran before, we use its version.
		if (!isset($verFromDir)) {
			list(, $verFromDir) = utils_smarty_find_stub();
		}
		if ($verFromDir) {
			$detected = $verFromDir;
		}
	}

	// Check minimum version (only if determinable)
	if ($detected !== null && version_compare($detected, $minVersion, '<')) {
		utils_boot_failure(sprintf('Smarty too old: %s found, >= %s required', $detected, $minVersion));
	}
}

/**
 * Register FlatPress Smarty plugins without using addPluginsDir() (deprecated since Smarty 5).
 * Scans $dir for classic plugin filenames and registers them via registerPlugin()/registerFilter().
 */
function fp_register_fp_plugins(\Smarty\Smarty $smarty, string $dir): void {
	// Prevent multiple calls - Terminate immediately when called again
	static $done = false;
	if ($done) {
		return;
	}
	$done = true;

	if (!defined('FP_SMARTY_FP_PLUGINS_DONE')) {
		// Set to true only at the end
		define('FP_SMARTY_FP_PLUGINS_DONE', false);
	}

	if (!is_dir($dir)) {
		return;
	}

	$dh = @opendir($dir);
	if (!$dh) {
		return;
	}

	// Lazy loading for classic plugins (function|modifier|block|compiler|modifiercompiler)
	$lazy = method_exists($smarty, 'addPluginsDir');
	if ($lazy) {
		$smarty->addPluginsDir($dir);
	}

	while (($file = readdir($dh)) !== false) {
		if ($file === '.' || $file === '..' || $file [0] === '.') {
			continue;
		}
		$path = $dir . DIRECTORY_SEPARATOR . $file;
		if (!is_file($path) || pathinfo($path, PATHINFO_EXTENSION) !== 'php') {
			continue;
		}

		// Classic plugin files: function.|modifier.|block.|compiler.|modifiercompiler.
		if (preg_match('/^(function|modifier|block|compiler|modifiercompiler)\.([A-Za-z0-9_]+)\.php$/', $file, $m)) {
			if ($lazy) {
				continue;
			}
			require_once $path;
			$type = $m [1];
			$name = $m [2];
			switch ($type) {
				case 'function':
					$smarty->registerPlugin(\Smarty\Smarty::PLUGIN_FUNCTION, $name, 'smarty_function_' . $name);
					break;
				case 'block':
					$smarty->registerPlugin(\Smarty\Smarty::PLUGIN_BLOCK, $name, 'smarty_block_' . $name);
					break;
				case 'modifier':
					$smarty->registerPlugin(\Smarty\Smarty::PLUGIN_MODIFIER, $name, 'smarty_modifier_' . $name);
					break;
				case 'modifiercompiler':
					$smarty->registerPlugin(\Smarty\Smarty::PLUGIN_MODIFIERCOMPILER, $name, 'smarty_modifiercompiler_' . $name);
					break;
				case 'compiler':
					$smarty->registerPlugin(\Smarty\Smarty::PLUGIN_COMPILER, $name, 'smarty_compiler_' . $name);
					break;
			}
			continue;
		}

		// Filters: prefilter.|postfilter.|outputfilter.|variablefilter.
		if (preg_match('/^(pre|post|output|variable)filter\.([A-Za-z0-9_]+)\.php$/', $file, $m)) {
			require_once $path;
			$kind = $m [1]; // pre|post|output|variable
			$name = $m [2];
			$smarty->registerFilter($kind, 'smarty_' . $kind . 'filter_' . $name);
			continue;
		}

		// Shared helpers used by other plugins (no registration, just load)
		if (preg_match('/^shared\.([A-Za-z0-9_]+)\.php$/', $file)) {
			if (!$lazy) {
				require_once $path;
			}
			continue;
		}

		// Validation helpers (no direct registration, just make functions/classes available)
		if (preg_match('/^validate_[A-Za-z0-9_.]+\.(php)$/', $file)) {
			if (!$lazy) {
				require_once $path;
			}
			continue;
		}

		// Inserts are removed in Smarty 5 – soft warning for plugin authors.
		if (preg_match('/^insert\.([A-Za-z0-9_]+)\.php$/', $file, $m)) {
			@trigger_error('Smarty insert plugin "' . $m [1] . '" not supported in Smarty 5 or later; convert to a function plugin.', E_USER_DEPRECATED);
			continue;
		}
	}
	closedir($dh);

	// End – Registration successfully completed
	if (!defined('FP_SMARTY_FP_PLUGINS_DONE')) {
		define('FP_SMARTY_FP_PLUGINS_DONE', true);
	}
}
?>
