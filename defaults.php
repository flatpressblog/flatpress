<?php

// defaults.php

// This file defines standard positions of some important
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

/**
 * Default file permissions
 * https://binary-butterfly.de/artikel/dateirechte-wie-stelle-ich-das-bei-meinem-hoster-ein/
 * Note: Lowering the directory and file permissions may result in FlatPress or some additional plugins not working correctly.
 */
// For the directory fp-content and its subdirectories including the files
define('FILE_PERMISSIONS', 0644); // 0644 is recommended
define('DIR_PERMISSIONS', 0755); // 0755 is recommended

// FlatPress core: This includes this file, recursively the directories ADMIN_DIR, FP_INCLUDES, CONFIG_DIR, USERS_DIR, LANG_DIR and SHARED_TPLS
define('CORE_FILE_PERMISSIONS', 0640); // 0640 for productive operation
define('CORE_DIR_PERMISSIONS', 0750); // 0750 for productive operation

// For all other files and directories, e.g. FP_INTERFACE, THEMES_DIR and PLUGINS_DIR
define('RESTRICTED_FILE_PERMISSIONS', 0644); // 0644 is recommended
define('RESTRICTED_DIR_PERMISSIONS', 0755); // 0755 is recommended

// For small APCu pools (< 32 MiB), set to 600–1800 s; for standard pools, set it to 1800–3600 s; for large pools, set it to 3600–7200 s.
$_ENV ['FP_APCU_IO_TTL'] = 3600;

/**
 * First some webserver setup...
 */

// Here's where your server save session-related stuff.
// If you don't experience any session-related problem, you
// you can leave it blank and it will default to standard webserver config
define('SESSION_PATH', '');
// Absolute path to your webserver dir; if you don't experience any problem
// you can leave this as it is
define('ABS_PATH', str_replace('\\', '/', dirname(__FILE__)) . '/');
// Here was blog root in earlier versions. This has been moved to config_load()

// Is required so that the file and directory permissions can be set when executing the setup
define('BASE_DIR', str_replace('\\', '/', dirname(__FILE__)));

// Here are default config files
define('FP_DEFAULTS', 'fp-defaults/');

// All writable directories go here.
define('FP_CONTENT', 'fp-content/');

// Blog configurations files
define('CONFIG_DIR', FP_CONTENT . 'config/');
define('CONFIG_FILE', CONFIG_DIR . 'settings.conf.php');

/**
 *
 * @deprecated as of FlatPress 1.2 - still here only to be able to update pre-1.2 credentials
 */
define('HASHSALT_FILE', CONFIG_DIR . 'hashsalt.conf.php');
define('CONFIG_DEFAULT', FP_DEFAULTS . 'settings-defaults.php');
define('USERS_DIR', FP_CONTENT . 'users/');

// Here we'll store all your entries, comments, static pages and blocks
// Comments are stored automatically in subdirs of the dir CONTENT_DIR
define('CONTENT_DIR', FP_CONTENT . 'content/');
// define('BLOCKS_DIR', CONTENT_DIR . 'blocks/');

// !!! CRITICAL !!!
// all includes file
define('FP_INCLUDES', 'fp-includes/');

// core include scripts
define('INCLUDES_DIR', FP_INCLUDES . 'core/');

// smarty engine
define('COMPILE_DIR', FP_CONTENT . 'compile/');
define('CACHE_DIR', FP_CONTENT . 'cache/');
// FlatPress specific Smarty plugins
define('FP_SMARTYPLUGINS_DIR', ABS_PATH . FP_INCLUDES . 'fp-smartyplugins/');

define('FP_INTERFACE', 'fp-interface/');
// theme dir
define('THEMES_DIR', FP_INTERFACE . 'themes/');
// language files
define('LANG_DIR', FP_INTERFACE . 'lang/');
// misc forms
define('SHARED_TPLS', ABS_PATH . FP_INTERFACE . 'sharedtpls/');

// Here is where all plugins are saved
define('PLUGINS_DIR', 'fp-plugins/');

define('ADMIN_DIR', 'admin/');

// Cache file name and path.
define('CACHE_FILE', '%%cached_list.php');

define('INDEX_DIR', FP_CONTENT . 'index/');

define('LOCKFILE', FP_CONTENT . '%%setup.lock');

// Here is where all the uploaded images will be saved
define('IMAGES_DIR', FP_CONTENT . 'images/');
// Here is where all the attachments will be saved
define('ATTACHS_DIR', FP_CONTENT . 'attachs/');

set_include_path(ABS_PATH);

include (LANG_DIR . 'browserlang.php');
define('LANG_DEFAULT', $browserLang);
define('BPT_SORT', SORT_DESC);

// Generates for your scripts in plugins and templates
// a random hexadecimal value for the nonce attribute
// https://wiki.selfhtml.org/wiki/HTML/Attribute/nonce
define('RANDOM_HEX', bin2hex(random_bytes(18)));

// Adding security and HTTPS support
include (INCLUDES_DIR . 'core.connection.php');

// define('BLOG_ROOT', dirname($_SERVER['PHP_SELF']) . '/');
define('BLOG_ROOT', ('/' == ($v = dirname($_SERVER ['SCRIPT_NAME'])) ? $v : $v . '/'));

define('BLOG_BASEURL', $scheme . $_SERVER ['HTTP_HOST'] . BLOG_ROOT);
?>
