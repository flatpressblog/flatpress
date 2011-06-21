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
	// change file to 644 and dir to 755 if your webserver "complains"
	define('FILE_PERMISSIONS', 0777);
	define('DIR_PERMISSIONS', 0777);

	
	// first some webserver setup...
	
	// here's where your server save session-related stuff.
	// If you don't experience any session-related problem, you
	// you can leave it blank and it will default to standard webserver config
	define('SESSION_PATH', '');
	// absolute path to your webserver dir; if you don't experience any problem
	// you can leave this as it is
	define('ABS_PATH', dirname(__FILE__).'/');
	// here was blog root in earlier versions. This has been moved to config_load()
	
	
	// here are default config files
	define('FP_DEFAULTS', 'fp-defaults/');
	
	
	// all writable directories go here. 
	define('FP_CONTENT', 'fp-content/'); //must be chmodded to 0777
	
	// blog configurations files
	define('CONFIG_DIR', FP_CONTENT . 'config/');  //must be chmodded to 0777
	define('CONFIG_FILE', CONFIG_DIR . 'settings.conf.php'); 
	define('HASHSALT_FILE', CONFIG_DIR . 'hashsalt.conf.php'); 
	define('CONFIG_DEFAULT', FP_DEFAULTS. 'settings-defaults.php');
	define('USERS_DIR', FP_CONTENT . 'users/');
	
	// here we'll store all your entries, comments, static pages and blocks
	// comments are stored automatically in subdirs of the dir CONTENT_DIR
	define('CONTENT_DIR', FP_CONTENT . 'content/');
	//define('BLOCKS_DIR', CONTENT_DIR . 'blocks/');
	
	
	// !!! CRITICAL !!!
	// all includes file
	define('FP_INCLUDES', 'fp-includes/');
	
	// core include scripts
	define('INCLUDES_DIR', FP_INCLUDES . 'core/');
	// smarty engine
	define('SMARTY_DIR', ABS_PATH . FP_INCLUDES . 'smarty/');
	
	
	
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
	define('CACHE_DIR', FP_CONTENT . 'cache/');
	define('CACHE_FILE', '%%cached_list.php');

	define('INDEX_DIR', FP_CONTENT.'index/');
	
	define('LOCKFILE', FP_CONTENT . '%%setup.lock');

	
	// these will be probably moved soon to plugins
		// here is where all the uploaded images will be saved
		define('IMAGES_DIR', FP_CONTENT . 'images/');
		// here is where all the attachments will be saved
		define('ATTACHS_DIR', FP_CONTENT . 'attachs/');
		
	
	define('LANG_DEFAULT', 'en-us');
	define('BPT_SORT', SORT_DESC);
		
	
	set_include_path(ABS_PATH);
	
	// compatibility with ISS
	if (!isset($_SERVER['REQUEST_URI']))
		$_SERVER['REQUEST_URI'] = 'http://localhost/flatpress/';
	
	#define('BLOG_ROOT', dirname($_SERVER['PHP_SELF']) . '/');
	define('BLOG_ROOT', ('/'==($v=dirname($_SERVER['SCRIPT_NAME']))? $v : $v.'/') ); 

		
	define('BLOG_BASEURL', 'http://'.$_SERVER['HTTP_HOST']. BLOG_ROOT);

#function _dummy() {}
#set_error_handler('_dummy');

