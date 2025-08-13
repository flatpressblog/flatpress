
<?php
declare(strict_types=1);
/**
 * PHPStan Bootstrap for FlatPress without Composer + Smarty 5.5.1
 * No runtime side effects: no sessions, no system_init(), no plugin load.
 */

// Select analysis context
defined('PHPSTAN') || define('PHPSTAN', true);

// repo root
$root = dirname(__DIR__);

// Minimum required constants
defined('ABS_PATH') || define('ABS_PATH', $root . DIRECTORY_SEPARATOR);
defined('FP_INCLUDES') || define('FP_INCLUDES', 'fp-includes' . DIRECTORY_SEPARATOR);
defined('FP_SMARTYPLUGINS_DIR') || define('FP_SMARTYPLUGINS_DIR', ABS_PATH . FP_INCLUDES . 'fp-smartyplugins' . DIRECTORY_SEPARATOR);

defined('COMPILE_DIR') || define('COMPILE_DIR', ABS_PATH . 'fp-content' . DIRECTORY_SEPARATOR . 'compile' . DIRECTORY_SEPARATOR);
defined('CACHE_DIR') || define('CACHE_DIR',   ABS_PATH . 'fp-content' . DIRECTORY_SEPARATOR . 'cache'   . DIRECTORY_SEPARATOR);

// Legacy compatibility: some old code may still reference SMARTY_DIR
defined('SMARTY_DIR') || define('SMARTY_DIR', ABS_PATH . FP_INCLUDES . 'smarty-5.5.1' . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR);

// Load Smarty 5 without Composer so that \Smarty\* types can be resolved
$smartyBootstrap = SMARTY_DIR . 'Smarty.class.php';
if (is_file($smartyBootstrap)) {
	/** @noinspection PhpIncludeInspection */
	require_once $smartyBootstrap; // Internally loads Smarty 5 src/* (without Composer) :contentReference[oaicite:2]{index=2}
}

// --- PHPStan type aliases for legacy names used in FlatPress docblocks/code ---
// Map old global 'Smarty' name to namespaced '\Smarty\Smarty'
if (!class_exists('Smarty', false) && class_exists(\Smarty\Smarty::class, false)) {
	class_alias(\Smarty\Smarty::class, 'Smarty');
}
// Map old internal template class name used in some annotations
if (!class_exists('Smarty_Internal_Template', false) && class_exists(\Smarty\Template::class, false)) {
	class_alias(\Smarty\Template::class, 'Smarty_Internal_Template');
}

// FlatPress-specific Smarty resources (class declarations only, no registration)
foreach (['resource.admin.php', 'resource.plugin.php', 'resource.shared.php'] as $res) {
	$file = FP_SMARTYPLUGINS_DIR . $res;
	if (is_file($file)) {
		require_once $file;
	}
}
