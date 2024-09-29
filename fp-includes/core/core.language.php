<?php

function lang_load($postfix = null) {
	global $fp_config;

	$pluginpath = '';

	// checks if we already loaded this lang file
	$old_lang = &$GLOBALS ['lang'];

	if (!$old_lang) {
		$old_lang = array();
	}

	if ($postfix) {

		if (strpos($postfix, 'plugin:') === 0) {
			$pluginpath = substr($postfix, 7);
		}

		$file = "lang.$postfix.php";
	} else {

		$postfix = 'default';
		$file = "lang.default.php";
	}

	$fpath = LANG_DIR . "{$fp_config['locale']['lang']}/$file";
	$fallback = LANG_DIR . LANG_DEFAULT . "/$file";

	$path = '';
	$plugin = $pluginpath;

	if ($pluginpath) {
		if (($n = strpos($pluginpath, '/')) !== false) {
			$plugin = substr($plugin, 0, $n - 1);
			$path = substr($plugin, $n + 1);
			$path = str_replace('/', '.', $path);
		}

		$dir = plugin_getdir($plugin);

		$fpath = $dir . "lang/lang.{$fp_config['locale']['lang']}{$path}.php";
		$fallback = $dir . "lang/lang." . LANG_DEFAULT . "{$path}.php";
	}

	if (!file_exists($fpath)) {
		/* if file does not exist, we fall back on English */
		if (!file_exists($fallback)) {
			trigger_error("No suitable language file was found <b>$postfix</b>", E_USER_WARNING);
			return;
		}

		$fpath = $fallback;
	}

	/* load $lang from file */

	/*
	 * utf encoded files may output whitespaces known as BOM, we must
	 * capture this chars
	 */

	ob_start();

	include_once ($fpath);

	if (!isset($lang)) {
		return $GLOBALS ['lang'];
	}

	ob_end_clean();

	$GLOBALS ['lang'] = array_merge_recursive($lang, $old_lang);

	return $GLOBALS ['lang'];
}

function lang_getconf($id) {
	global $lang;

	$fpath = LANG_DIR . "$id/lang.conf.php";
	if (file_exists($fpath)) {
		include ($fpath);
		return $langconf;
	} else {
		trigger_error("Error loading config for language \"$file\"", E_USER_WARNING);
	}
}

class lang_indexer extends fs_filelister {

	var $_directory = LANG_DIR;

	function _checkFile($directory, $file) {
		if (is_dir("$directory/$file")) {
			if (!preg_match('![a-z]{2}-[a-z]{2}!', $file)) {
				return 0;
			}
			$this->_list [$file] = lang_getconf($file);
		}

		return 0;
	}

}

function lang_list() {
	$obj = new lang_indexer();
	return $obj->getList();
}

/**
 * Localize Smarty function {html_select_date} with LC_TIME
 *
 * Hint: The character set and coding must be installed on the web server (locale -a),
 * otherwise there will be display problems with non-ASCII characters.
 */
function set_locale() {
	global $fp_config;

	$langId = $fp_config ['locale'] ['lang'];

	$langConfFile = LANG_DIR . $langId . '/lang.conf.php';
	if (file_exists($langConfFile)) {
		include_once $langConfFile;
	}

	// As entered in the admin area in the configuration panel -> International settings -> Character set.
	$charset = $fp_config ['locale'] ['charset'];

	// Read different possible locale names from lang.conf file
	$localeCountry_a = $langconf ['localecountry_a']; // de_DE
	$localeCountry_b = $langconf ['localecountry_b']; // de-DE

	// Entered character set coding available in the lang.conf file?
	if (preg_match('/\b' . $langconf ['charsets'] [0] . '\b/i', $charset)) {
		$localeCharset_a = $langconf ['localecharset_a']; // .UTF-8
		$localeCharset_b = $langconf ['localecharset_b']; // .utf8
	} else {
		$localeCharset_a = '';
		$localeCharset_b = '';
	}

	if (preg_match('/\b' . $langconf ['charsets'] [1] . '\b/i', $charset)) {
		$localeCharset_c = $langconf ['localecharset_c']; // .ISO-8859-15
		$localeCharset_d = $langconf ['localecharset_d']; // .iso885915
	} else {
		$localeCharset_c = '';
		$localeCharset_d = '';
	}

	$localeShort = $langconf ['localeshort']; // de

	// Check if LC_TIME is set and returns the current locale
	$currentLocale = @setlocale(LC_TIME, 0);

	// If LC_TIME is not set or contains something else, but not the correct locale,
	if ($currentLocale === false || (!preg_match('/\b' . $localeShort . '\b/i', $currentLocale))) {
		// then try different possible locale names.
		$currentLocale = @setlocale(LC_TIME, $localeCountry_a . $localeCharset_a, $localeCountry_a . $localeCharset_b, $localeCountry_a . $localeCharset_c, $localeCountry_a . $localeCharset_d, $localeCountry_a, $localeCountry_b, $localeShort);
	}

	//echo '<pre>' . strftime_replacement("%B") . '</pre>';
}
?>
