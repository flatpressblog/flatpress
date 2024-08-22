<?php
# START OF hoop jumping to load Smarty without Composer
# https://github.com/smarty-php/smarty/issues/999#issuecomment-2109190898

//define('SMARTY_DIR', dirname(__FILE__) . "/smarty-5.4.0/");

require_once SMARTY_DIR . 'functions.php';

spl_autoload_register(function (string $class) {
	// Class prefix
	$prefix = 'Smarty\\';

	// Does the class use the namespace prefix?
	$len = strlen($prefix);
	if (strncmp($prefix, $class, $len) !== 0) {
		// If not, move to the next registered autoloader
		return;
	}

	// Hack off the prefix part
	$relative_class = substr($class, $len);

	// Build a path to the include file
	// 1 with namespaces
	// 2 without
	$fileName = [
		SMARTY_DIR . str_replace('\\', '/', $relative_class . '.php'),
		SMARTY_DIR . $relative_class . '.php'
	];
	foreach ($fileName as $file) {
		if (file_exists($file)) {
			require_once $file;
			break;
		}
	}
});

# END OF hoop jumping to load Smarty without Composer

$GLOBALS ['smarty'] = new Smarty\Smarty();

$smarty->setCacheDir = CACHE_DIR . 'smatry-cache/';
$smarty->setCompileDir = CACHE_DIR;
$smarty->caching = false;
$smarty->testInstall();

?>
