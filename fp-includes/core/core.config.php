<?php

function config_read($fullpath) {
	if ($fullpath [0] != '/') {
		trigger_error('config_read: syntax error. Path must begin with a /');
	}
	$last_slash = strrpos($fullpath, '/');
	$option = substr($fullpath, $last_slash + 1);
	$path = substr($fullpath, 1, $last_slash);
	$file = str_replace('/', '.', $path) . 'conf.php';
	$f = CONFIG_DIR . $file;
	if (file_exists($f)) {
		include ($f);
	}

	$arr = explode('/', $fullpath);

	/* todo finire */
}

/**
 * a cosmetic wrapper around an include :D
 * plus, loads the defaults if CONFIG_FILE is not found
 */
function config_load($conffile = CONFIG_FILE) {
	// Reuse config parsed early in core.connection.php (optional perf + reliability)
	if ($conffile == CONFIG_FILE && isset($GLOBALS ['EARLY_FP_CONFIG']) && is_array($GLOBALS ['EARLY_FP_CONFIG'])) {
		return $GLOBALS ['EARLY_FP_CONFIG'];
	}

	$resolve = static function ($path): string {
		$path = (string)$path;
		if ($path === '') {
			return '';
		}
		$path = str_replace('\\', '/', $path);
		// Windows drive letter paths are like "C:/...". Use a non-slash regex delimiter to avoid issues with '/' inside a character class.
		$abs = ($path [0] === '/' || preg_match('~^[A-Za-z]:/~', $path) === 1);
		if ($abs) {
			return $path;
		}
		$base = defined('ABS_PATH') ? (string)ABS_PATH : '';
		$base = str_replace('\\', '/', $base);
		if ($base !== '' && substr($base, -1) !== '/') {
			$base .= '/';
		}
		return $base . ltrim($path, '/');
	};

	$resolved = $resolve($conffile);
	if ($resolved !== '' && !file_exists($resolved) && ($conffile == CONFIG_FILE)) {
		$conffile = CONFIG_DEFAULT;
		$resolved = $resolve($conffile);
	}

	include $resolved;

	return $fp_config;
}

/**
 * $conf_arr can have a variable number of args
 * they are the same of system_save(), as this is in fact
 * a wrapper to that ;)
 * so:
 * $conf_arr[ 'myvariable' ] = $myvariable;
 */
function config_save($conf_arr = null, $conffile = CONFIG_FILE) {
	if ($conf_arr == null) {
		global $fp_config;
		$conf_arr = $fp_config;
	}

	$arr = array(
		'fp_config' => $conf_arr
	);
	return system_save($conffile, $arr);
}

?>
