<?php
declare(strict_types=1);

/**
 * FlatPress path constants
 */
namespace {
	$flatpressRoot = str_replace('\\', '/', dirname(__DIR__)) . '/';

	if (!defined('ABS_PATH')) {
		define('ABS_PATH', $flatpressRoot);
	}
	if (!defined('FP_DEFAULTS')) {
		define('FP_DEFAULTS', ABS_PATH . 'fp-defaults/');
	}
	if (!defined('FP_CONTENT')) {
		define('FP_CONTENT', ABS_PATH . 'fp-content/');
	}
	if (!defined('CONFIG_DIR')) {
		define('CONFIG_DIR', FP_CONTENT . 'config/');
	}
	if (!defined('FP_INCLUDES')) {
		define('FP_INCLUDES', ABS_PATH . 'fp-includes/');
	}
	if (!defined('INCLUDES_DIR')) {
		define('INCLUDES_DIR', FP_INCLUDES . 'core/');
	}
	if (!defined('FP_SMARTYPLUGINS_DIR')) {
		define('FP_SMARTYPLUGINS_DIR', FP_INCLUDES . 'fp-smartyplugins/');
	}
	if (!defined('FP_INTERFACE')) {
		define('FP_INTERFACE', ABS_PATH . 'fp-interface/');
	}
	if (!defined('LANG_DIR')) {
		define('LANG_DIR', FP_INTERFACE . 'lang/');
	}
	if (!defined('SHARED_TPLS')) {
		define('SHARED_TPLS', FP_INTERFACE . 'sharedtpls/');
	}
	if (!defined('PLUGINS_DIR')) {
		define('PLUGINS_DIR', ABS_PATH . 'fp-plugins/');
	}
	if (!defined('ADMIN_DIR')) {
		define('ADMIN_DIR', ABS_PATH . 'admin/');
	}

	$phpstanConstants = [
		'ADMIN_PANEL' => 'entry',
		'ADMIN_PANEL_ACTION' => 'default',
		'BASE_DIR' => rtrim(ABS_PATH, '/'),
		'BLOG_BASEURL' => 'https://example.test/',
		'BLOG_ROOT' => '/',
		'INDEX' => 'index.php',
		'MOD_BLOG' => 'index.php',
		'LANG_DEFAULT' => 'en-us',
		'RANDOM_HEX' => '000000000000000000000000000000000000',
		'THE_THEME' => 'leggero',
		'THEME_LEGACY_MODE' => false,
		'THEMES_DIR' => FP_INTERFACE . 'themes/',
		'CONTENT_DIR' => FP_CONTENT . 'content/',
		'CACHE_DIR' => FP_CONTENT . 'cache/',
		'INDEX_DIR' => FP_CONTENT . 'index/',
		'IMAGES_DIR' => FP_CONTENT . 'images/',
		'ATTACHS_DIR' => FP_CONTENT . 'attachs/',
		'COOKIEHASH' => 'phpstan',
		'COOKIE_PREFIX' => '',
		'COOKIEPATH' => '/',
		'SITECOOKIEPATH' => '/',
		'COOKIE_DOMAIN' => '',
		'COOKIE_SECURE' => false,
		'COOKIE_HTTPONLY' => true,
		'SAMESITE_VALUE' => 'Lax',
		'SESS_COOKIE' => 'fpsess_phpstan',
		'USER' => 'user',
		'GMT' => 'gmt',
		'BBCODE_ALLOW_HTML' => true,
		'BBCODE_ENABLE_COMMENTS' => false,
		'BBCODE_USE_EDITOR' => true,
		'BBCODE_MASK_ATTACHS' => true,
		'BBCODE_URL_MAXLEN' => 40,
		'BBCODE_DEFAULT_FONT_FAMILY' => '"Arial"',
		'BBCODE_USE_WRAPPER' => true,
		'BBCODE_USE_FILEWRAPPER' => true,
	];
	foreach ($phpstanConstants as $name => $value) {
		if (!defined($name)) {
			define($name, $value);
		}
	}

	set_include_path(ABS_PATH . PATH_SEPARATOR . get_include_path());
}

/**
 * PHPStan stubs for Smarty 5 – no runtime effect!
 * Covers both worlds:
 *  - \Smarty\Smarty (new, namespaced class)
 *  - Smarty (old, global short form in docblocks)
 * Plus a minimal template API.
 */
namespace Smarty {
	class Smarty {
		public const COMPILECHECK_OFF = 0;
		public const COMPILECHECK_ON  = 1;
		public const COMPILECHECK_CACHEMISS = 2;

		public const PLUGIN_FUNCTION = 'function';
		public const PLUGIN_BLOCK = 'block';
		public const PLUGIN_MODIFIER = 'modifier';
		public const PLUGIN_MODIFIERCOMPILER = 'modifiercompiler';
		public const PLUGIN_COMPILER = 'compiler';

		public function setCompileDir($dir): void {}
		public function setCacheDir($dir): void {}
		public function setCaching($flag): void {}
		public function setDebugging($flag): void {}

		public function setCompileCheck(int $mode): void {}
		public function setForceCompile(bool $flag): void {}

		/** @param object $resource */
		public function registerResource(string $name, $resource): void {}
		/** @param callable $callback */
		public function registerPlugin(string $type, string $name, $callback): void {}
		/** @param callable $callback */
		public function registerFilter(string $type, $callback): void {}

		public function assign($var, $value = null): void {}
		public function display($tpl): void {}
		public function fetch($tpl): string { return ''; }
		public function clearAllCache(): void {}
		public function clearCompiledTemplate(): void {}
	}

	class Template {
		public function getSmarty(): Smarty { return new Smarty(); }
		/** @return mixed */
		public function getTemplateVars(?string $name = null) { return null; }
		public function renderSubTemplate($tpl, ...$args): string { return ''; }
	}
}

namespace {
	/**
	 * Old short forms that can be found in Docblocks/Properties.
	 * (Global classes, separate from \Smarty\Smarty.)
	 */
	class Smarty extends \Smarty\Smarty {}
	class Smarty_Internal_Template extends \Smarty\Template {}
}
?>
