<?php

// defaults.php

// this file defines standard positions of some important
// dirs or files.

// For security reasons
// these can't be modified in the common settings panels
// but only through this file.
// If you want to change these constants, just change
// the second parameter.

// We suggest not to define here your owns, but instead
// inmplementing them as plugins

// legacy mode; needed with some ill-formed spb files
define('DUMB_MODE_ENABLED', false);

// default file permissions
// https://binary-butterfly.de/artikel/dateirechte-wie-stelle-ich-das-bei-meinem-hoster-ein/
// change file to 666 and dir to 777 if your webserver "complains"
// Note: Lowering the directory and file permissions may result in FlatPress or some additional plugins not working correctly.
define('FILE_PERMISSIONS', 0666);
define('DIR_PERMISSIONS', 0777);

// first some webserver setup...

// here's where your server save session-related stuff.
// If you don't experience any session-related problem, you
// you can leave it blank and it will default to standard webserver config
define('SESSION_PATH', '');
// absolute path to your webserver dir; if you don't experience any problem
// you can leave this as it is
define('ABS_PATH', dirname(__FILE__) . '/');
// here was blog root in earlier versions. This has been moved to config_load()

// Is required so that the file and directory permissions can be set when executing the setup
define('BASE_DIR', dirname(__FILE__));

// here are default config files
define('FP_DEFAULTS', 'fp-defaults/');

// all writable directories go here.
define('FP_CONTENT', 'fp-content/'); // must be chmodded to 0777

// blog configurations files
define('CONFIG_DIR', FP_CONTENT . 'config/'); // must be chmodded to 0777
define('CONFIG_FILE', CONFIG_DIR . 'settings.conf.php');

/**
 *
 * @deprecated as of FlatPress 1.2 - still here only to be able to update pre-1.2 credentials
 */
define('HASHSALT_FILE', CONFIG_DIR . 'hashsalt.conf.php');
define('CONFIG_DEFAULT', FP_DEFAULTS . 'settings-defaults.php');
define('USERS_DIR', FP_CONTENT . 'users/');

// here we'll store all your entries, comments, static pages and blocks
// comments are stored automatically in subdirs of the dir CONTENT_DIR
define('CONTENT_DIR', FP_CONTENT . 'content/');
// define('BLOCKS_DIR', CONTENT_DIR . 'blocks/');

// !!! CRITICAL !!!
// all includes file
define('FP_INCLUDES', 'fp-includes/');

// core include scripts
define('INCLUDES_DIR', FP_INCLUDES . 'core/');

// smarty engine
define('SMARTY_DIR', ABS_PATH . FP_INCLUDES . 'smarty-5.4.0/src/');

// FlatPress specific Smarty plugins
define('FP_SMARTYPLUGINS_DIR', ABS_PATH . FP_INCLUDES . 'fp-smartyplugins/');

define('FP_INTERFACE', 'fp-interface/');
// theme dir
define('THEMES_DIR', FP_INTERFACE . 'themes/');
// language files
define('LANG_DIR', FP_INTERFACE . 'lang/');
// misc forms
define('SHARED_TPLS', ABS_PATH . FP_INTERFACE . 'sharedtpls/');

// here is where all plugins are saved
define('PLUGINS_DIR', 'fp-plugins/');

define('ADMIN_DIR', 'admin/');

// cache file name and path.
define('CACHE_DIR', FP_CONTENT . 'cache/'); // must be chmodded to 0777
define('CACHE_FILE', '%%cached_list.php');

define('INDEX_DIR', FP_CONTENT . 'index/');

define('LOCKFILE', FP_CONTENT . '%%setup.lock');

// these will be probably moved soon to plugins
// here is where all the uploaded images will be saved
define('IMAGES_DIR', FP_CONTENT . 'images/');
// here is where all the attachments will be saved
define('ATTACHS_DIR', FP_CONTENT . 'attachs/');

include (LANG_DIR . 'browserlang.php');
define('LANG_DEFAULT', $browserLang);
define('BPT_SORT', SORT_DESC);

// generates for your scripts in plugins and templates
// a random hexadecimal value for the nonce attribute
// https://wiki.selfhtml.org/wiki/HTML/Attribute/nonce
define('RANDOM_HEX', bin2hex(random_bytes(18)));

set_include_path(ABS_PATH);

//
// original Flatpress 1.0.3 coding disabled
//
// compatibility with ISS
// if (!isset($_SERVER['REQUEST_URI']))
// $_SERVER['REQUEST_URI'] = 'http://localhost/flatpress/';

// #define('BLOG_ROOT', dirname($_SERVER['PHP_SELF']) . '/');
// define('BLOG_ROOT', ('/'==($v=dirname($_SERVER['SCRIPT_NAME']))? $v : $v.'/') );

// define('BLOG_BASEURL', 'http://'.$_SERVER['HTTP_HOST']. BLOG_ROOT);

//
// Adding security and HTTPS support
//

ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);

if (isset($_SERVER ['HTTPS'])) {
	$_SERVER ['HTTPS'] = htmlspecialchars($_SERVER ['HTTPS'], ENT_QUOTES, "UTF-8");
}

// supports Apache and IIS
$serverport = '';
if (is_https()) {
	// HTTPS enabled
	$serverport = "https://";
	ini_set('session.cookie_httponly', 1);
	define('COOKIE_PREFIX', '__secure-');
	ini_set('session.cookie_secure', 1);
	ini_set('session.cookie_samesite', 'Lax');
} else {
	// HTTP only
	$serverport = "http://";
	ini_set('session.cookie_httponly', 0);
	define('COOKIE_PREFIX', '');
	ini_set('session.cookie_secure', 0);
}

// compatibility with ISS
$_SERVER ["REQUEST_URI"] = htmlspecialchars($_SERVER ["REQUEST_URI"], ENT_QUOTES, "UTF-8");
if (!isset($_SERVER ['REQUEST_URI']))
	$_SERVER ['REQUEST_URI'] = $serverport . 'localhost/flatpress/';

// define('BLOG_ROOT', dirname($_SERVER['PHP_SELF']) . '/');
define('BLOG_ROOT', ('/' == ($v = dirname($_SERVER ['SCRIPT_NAME'])) ? $v : $v . '/'));

define('BLOG_BASEURL', $serverport . $_SERVER ['HTTP_HOST'] . BLOG_ROOT);

//
// OWASP - Browser Cache - How can the browser cache be used in attacks?
// https://www.owasp.org/index.php/OWASP_Application_Security_FAQ#How_can_the_browser_cache_be_used_in_attacks.3F
//
// http://stackoverflow.com/questions/13640109/how-to-prevent-browser-cache-for-php-site
//
header('Expires: Sun, 01 Jan 2015 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
//
// http://de.wikipedia.org/wiki/Liste_der_HTTP-Headerfelder
//
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');

//
// End of send header
//

/**
 * Checks if FlatPress is called via HTTPS.
 *
 * @return boolean <code>true</code> when FlatPress is called via HTTPS; <code>false</code> otherwise.
 */
function is_https() {
	// HTTPS called web server
	if (isset($_SERVER ['HTTPS']) && !empty($_SERVER ['HTTPS'])) {
		return true;
	}
	// HTTPS called reverse proxy / load balancer
	if (!empty($_SERVER ['HTTP_X_FORWARDED_PROTO']) && $_SERVER ['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER ['HTTP_X_FORWARDED_SSL']) && $_SERVER ['HTTP_X_FORWARDED_SSL'] == 'on') {
		return true;
	}
	// none of the above: must be HTTP
	return false;
}

/**
 * START OF hoop jumping to load Smarty without Composer
 */
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
