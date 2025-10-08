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
 * Build/load persistent index for fp-smartyplugins.
 * Cached under CACHE_DIR, invalidated via mtime($dir).
 */
function fp_smarty_get_plugin_index(string $dir): array {
	$empty = [
		// Classic plugin files
		'function' => [],
		'block' => [],
		'modifier' => [],
		'compiler' => [],
		'modifiercompiler' => [],
		// Filters
		'filters' => ['pre' => [],
		'post' => [],
		'output' => [],
		'variable' => []],
		// Custom resources
		'resources' => [],
		// Shared helpers
		'helpers' => []
	];

	if (!is_dir($dir)) {
		return $empty;
	}

	$cacheDir = defined('CACHE_DIR') ? CACHE_DIR : (defined('FP_CONTENT') ? FP_CONTENT . 'cache/' : sys_get_temp_dir() . '/');
	$token = @filemtime($dir) ?: 0;
	$indexFile = rtrim($cacheDir, '/\\') . '/smarty_plugins.index.php';

	// Check APCu securely and host-agnostically
	$apcuOn = function_exists('is_apcu_on') ? is_apcu_on() : false;

	if ($apcuOn) {
		$val = apcu_fetch('fp:spi:' . sha1($dir . '|' . $token), $hit);
		if ($hit && is_array($val)) {
			return $val;
		}
	}

	$map = null;
	if (is_file($indexFile)) {
		$payload = @include $indexFile;
		if (is_array($payload) && ($payload ['_token'] ?? null) === $token && is_array($payload ['map'] ?? null)) {
			$map = $payload ['map'];
		}
	}
	if ($map === null) {
		$map = $empty;
		if ($dh = @opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				if ($file === '.' || $file === '..' || $file [0]==='.') {
					continue;
				}
				$path = $dir.DIRECTORY_SEPARATOR.$file;
				if (!is_file($path) || pathinfo($path, PATHINFO_EXTENSION) !== 'php') {
					continue;
				}
				// Classic plugin files: function.|modifier.|block.|compiler.|modifiercompiler.
				if (preg_match('/^(function|modifier|block|compiler|modifiercompiler)\.([A-Za-z0-9_]+)\.php$/', $file, $m)) {
					$map [$m [1]] [$m [2]] = $path;
					continue;
				}
				// Filters: prefilter.|postfilter.|outputfilter.|variablefilter.
				if (preg_match('/^(pre|post|output|variable)filter\.([A-Za-z0-9_]+)\.php$/', $file, $m)) {
					$map ['filters'] [$m [1]] [$m [2]] = $path;
					continue;
				}
				// Custom resources: Classes outside the Smarty namespace (admin, plugin, shared).
				if (preg_match('/^resource\.([A-Za-z0-9_]+)\.php$/', $file, $m)) {
					$map ['resources'] [$m [1]] = $path;
					continue;
				}
				// Shared helpers (no direct registration; can be preloaded once)
				if (preg_match('/^shared\.([A-Za-z0-9_]+)\.php$/', $file)) {
					$map ['helpers'] [] = $path;
					continue;
				}
				// Validation helpers (no direct registration, just make functions/classes available)
				if (preg_match('/^validate_[A-Za-z0-9_.]+\.php$/', $file)) {
					$map ['helpers'] [] = $path;
					continue;
				}
			}
			closedir($dh);
		}
		$php = "<?php\nreturn " . var_export(['_token' => $token, 'map' => $map], true) . ";\n";
		if (function_exists('io_write_file')) {
			@io_write_file($indexFile, $php);
		} else {
			@file_put_contents($indexFile, $php, LOCK_EX);
		}
	}
	if ($apcuOn) {
		@apcu_store('fp:spi:' . sha1($dir . '|' . $token), $map, 60);
	}
	return $map;
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

	$index = fp_smarty_get_plugin_index($dir);

	foreach ($index ['function'] as $name => $path) {
		$smarty->registerPlugin(\Smarty\Smarty::PLUGIN_FUNCTION, $name,
			static function(array $params, $template) use ($name, $path) {
				static $fn = [];
				if (!isset($fn [$name])) {
					@require_once $path;
					$fn [$name] = 'smarty_function_' . $name;
				}
				return $fn [$name]($params, $template);
			}
		);
	}
	foreach ($index ['block'] as $name => $path) {
		$smarty->registerPlugin(\Smarty\Smarty::PLUGIN_BLOCK, $name,
			static function(array $params, $content, $template, &$repeat) use ($name, $path) {
				static $fn = [];
				if (!isset($fn [$name])) {
					@require_once $path;
					$fn [$name] = 'smarty_block_' . $name;
				}
				return $fn [$name]($params, $content, $template, $repeat);
			}
		);
	}
	foreach ($index ['modifier'] as $name => $path) {
		$smarty->registerPlugin(\Smarty\Smarty::PLUGIN_MODIFIER, $name,
			static function($value, ...$args) use ($name, $path) {
				static $fn = [];
				if (!isset($fn [$name])) {
					@require_once $path;
					$fn [$name] = 'smarty_modifier_' . $name;
				}
				return $fn [$name]($value, ...$args);
			}
		);
	}
	foreach ($index ['compiler'] as $name => $path) {
		$smarty->registerPlugin(\Smarty\Smarty::PLUGIN_COMPILER, $name,
			static function($params, $compiler) use ($name, $path) {
				static $fn = [];
				if (!isset($fn [$name])) {
					@require_once $path;
					$fn [$name] = 'smarty_compiler_' . $name;
				}
				return $fn [$name]($params, $compiler);
			}
		);
	}
	foreach ($index ['modifiercompiler'] as $name => $path) {
		$smarty->registerPlugin(\Smarty\Smarty::PLUGIN_MODIFIERCOMPILER, $name,
			static function($params, $compiler) use ($name, $path) {
				static $fn = [];
				if (!isset($fn [$name])) {
					@require_once $path;
					$fn [$name] = 'smarty_modifiercompiler_' . $name;
				}
				return $fn [$name]($params, $compiler);
			}
		);
	}

	foreach (['pre', 'post', 'output', 'variable'] as $kind) {
		foreach ($index ['filters'] [$kind] as $name => $path) {
			$n = (string)$name;
			if ($n === '' || !is_string($path)) {
				continue;
			}
			@require_once $path;
			switch ($kind) {
				case 'pre':
					$fn = 'smarty_prefilter_' . $n;
					if (function_exists($fn)) {
						$smarty->registerFilter('pre', $fn);
					}
					break;
				case 'post':
					$fn = 'smarty_postfilter_' . $n;
					if (function_exists($fn)) {
						$smarty->registerFilter('post', $fn);
					}
					break;
				case 'output':
					$fn = 'smarty_outputfilter_' . $n;
					if (function_exists($fn)) {
						$smarty->registerFilter('output', $fn);
					}
					break;
				case 'variable':
					$fn = 'smarty_variablefilter_' . $n;
					if (function_exists($fn)) {
						$smarty->registerFilter('variable', $fn);
					}
					break;
			}
		}
	}

	/**
	 * Register FlatPress custom Smarty resources (admin, plugin, shared).
	 * Note: Resources are classes outside the Smarty namespace
	 * and must be registered as objects (Smarty 5).
	 */
	if (!empty($index ['resources']) && is_array($index ['resources'])) {
		foreach ($index ['resources'] as $name => $path) {
			$n = (string)$name;
			if ($n === '' || !is_string($path)) {
				continue;
			}
			@require_once $path;
			$cls = 'Smarty_Resource_' . ucfirst($n);
			if (class_exists($cls, false)) {
				$smarty->registerResource($n, new $cls());
			}
		}
	}

	foreach ($index ['helpers'] as $path) {
		require_once $path;
	}

	// End – Registration successfully completed
	if (!defined('FP_SMARTY_FP_PLUGINS_DONE')) {
		define('FP_SMARTY_FP_PLUGINS_DONE', true);
	}
}
?>
