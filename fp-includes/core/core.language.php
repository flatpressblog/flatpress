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

		$file = "lang." . $postfix . ".php";
	} else {

		$postfix = 'default';
		$file = "lang.default.php";
	}

	$fpath = LANG_DIR . $fp_config ['locale'] ['lang'] . "/" . $file;
	$fallback = LANG_DIR . LANG_DEFAULT . "/" . $file;

	$path = '';
	$plugin = $pluginpath;

	if ($pluginpath) {
		if (($n = strpos($pluginpath, '/')) !== false) {
			$plugin = substr($plugin, 0, $n - 1);
			$path = substr($plugin, $n + 1);
			$path = str_replace('/', '.', $path);
		}

		$dir = plugin_getdir($plugin);

		$fpath = $dir . "lang/lang." . $fp_config ['locale'] ['lang'] . $path . ".php";
		$fallback = $dir . "lang/lang." . LANG_DEFAULT . $path . ".php";
	}

	if (!file_exists($fpath)) {
		// if file does not exist, we fall back on English
		if (!file_exists($fallback)) {
			trigger_error("No suitable language file was found <b>" . $postfix . "</b>", E_USER_WARNING);
			return;
		}

		$fpath = $fallback;
	}

	// per-request and optional APCu caching
	static $fp_lang_loaded = []; // realpath => true (already merged this file in this request)
	static $fp_lang_req = []; // cacheKey => array ($lang from file) for this request

	$real = realpath($fpath) ?: $fpath;
	if (isset($fp_lang_loaded [$real])) {
		return $GLOBALS ['lang'];
	}

	$mtime = @filemtime($fpath) ?: 0;
	$locale = isset($fp_config ['locale'] ['lang']) ? (string)$fp_config ['locale'] ['lang'] : '';
	$ckey = 'fp:lang:' . md5($real . '|' . $mtime . '|' . $locale);

	$apcu_on = function_exists('is_apcu_on') ? is_apcu_on() : false;

	$lang_loaded = false;
	if (isset($fp_lang_req [$ckey]) && is_array($fp_lang_req [$ckey])) {
		$lang = $fp_lang_req [$ckey];
		$lang_loaded = true;
	} elseif ($apcu_on) {
		$ok = false;
		$cached = apcu_get($ckey, $ok);
		if ($ok && is_array($cached)) {
			$lang = $cached;
			$fp_lang_req [$ckey] = $lang;
			$lang_loaded = true;
		}
	}

	if (!$lang_loaded) {
		// load $lang from file; capture BOM/whitespace
		ob_start();
		include_once ($fpath);
		if (!isset($lang)) {
			ob_end_clean();
			return $GLOBALS ['lang'];
		}
		ob_end_clean();
		$fp_lang_req [$ckey] = $lang;
		if ($apcu_on) {
			@apcu_set($ckey, $lang, 0);
		}
	}

	$GLOBALS ['lang'] = array_merge_recursive($lang, $old_lang);
	$fp_lang_loaded [$real] = true;
	return $GLOBALS ['lang'];
}

/**
 * Loads lang.conf.php with per-request cache.
 * @param string $langId e.g. en-US
 * @return array{charsets?: array<int,string>}
 */
function lang_getconf($langId) {
	static $cache = [];

	$id = is_string($langId) ? strtolower(trim($langId)) : '';
	if ($id === '') {
		return [];
	}
	if (array_key_exists($id, $cache)) {
		return $cache [$id];
	}

	$file = LANG_DIR . $id . '/lang.conf.php';

	/** @var array{charsets?: array<int,string>} $conf */
	$conf = [];
	if (file_exists($file) && is_readable($file)) {
		/** @var mixed $langconf */
		$langconf = null; // Is set in the file
		include $file; // Expected $langconf
		if (is_array($langconf)) {
			$conf = $langconf;
		}
	} else {
		trigger_error("Error loading config for language \"" . $file . "\"", E_USER_WARNING);
	}

	return $cache [$id] = $conf;
}

class lang_indexer extends fs_filelister {

	var $_directory = LANG_DIR;

	function _checkFile($directory, $file) {
		if (is_dir($directory . "/" . $file)) {
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
 * Set Locale for LC_* Categories
 *
 * This function attempts to set the appropriate locale settings for various LC_* categories 
 * (LC_TIME, LC_MESSAGES, LC_MONETARY, LC_NUMERIC, LC_COLLATE) based on configuration 
 * provided in the `lang.conf.php` file and the current charset settings in `fp_config`.
 *
 * The function is designed to work across both development and production environments, 
 * accommodating systems with limited or extensive locale support.
 *
 * Features:
 * - Validates and applies locale settings using combinations of `locale names` and `charsets`.
 * - Logs debug messages if the `DEBUG` constant is defined and enabled.
 * - Uses `locale -a` to verify available locales when supported.
 * - Provides a fallback mechanism to `en_US.UTF-8` if no suitable locale is found.
 * - Ensures robust error handling and compatibility across different server setups.
 *
 * Requirements:
 * - `lang.conf.php` must define locale and charset mappings.
 * - If `locale -a` is unavailable, the function attempts direct locale setting.
 *
 * Debugging:
 * - Enable detailed logging by defining the `DEBUG` constant as `true` in the configuration.
 *   Example: `define('DEBUG', true);`
 *
 * Notes:
 * - Ensure the required locales are installed on the server (e.g., `locale-gen` on Linux).
 * - Unsupported or invalid combinations will be logged but do not terminate execution.
 */
//define('DEBUG', true);
function set_locale() {
	global $fp_config;

	$debug = defined('DEBUG') && DEBUG;

	// Preconditions
	$langId  = (string)($fp_config ['locale'] ['lang'] ?? '');
	$charset = (string)($fp_config ['locale'] ['charset'] ?? '');
	if ($langId === '' || $charset === '') {
		if ($debug) {
			trigger_error('set_locale -> Locale configuration missing in fp_config.', E_USER_WARNING);
		}
		return;
	}

	/** @var array{
	 *   id?: string,
	 *   locale?: string|array<mixed,mixed>,
	 *   locales?: array<mixed,mixed>,
	 *   charsets?: array<int,string>,
	 *   localecountry_a?: string,
	 *   localecountry_b?: string,
	 *   localeshort?: string,
	 *   localecharset_a?: string,
	 *   localecharset_b?: string,
	 *   localecharset_c?: string,
	 *   localecharset_d?: string
	 * } $langConf */
	$langConf = (array)(lang_getconf($langId) ?? []);
	if ($debug) {
		error_log('set_locale -> Langconf loaded: ' . print_r($langConf, true));
	}

	// Fallbacks for missing keys
	$langConf += [
		'localecountry_a' => 'en_US',
		'localecountry_b' => 'en-US',
		'localeshort' => 'en',
		'charsets' => ['UTF-8'],
		'localecharset_a' => '.UTF-8',
		'localecharset_b' => '.utf8',
		'localecharset_c' => '.ISO-8859-1',
		'localecharset_d' => '.iso88591',
	];

	// Candidates
	$a = is_string($langConf ['localecountry_a']) ? $langConf ['localecountry_a'] : ''; // de_DE
	$b = is_string($langConf ['localecountry_b']) ? $langConf ['localecountry_b'] : ''; // de-DE
	$short = is_string($langConf ['localeshort']) ? $langConf ['localeshort'] : ''; // de

	if ($a === '' && $b !== '') {
		$a = str_replace('-', '_', $b);
	}
	if ($b === '' && $a !== '') {
		$b = str_replace('_', '-', $a);
	}

	$localeVariants = [];
	if ($a !== '') {
		$localeVariants [] = $a; // .UTF-8
	}
	if ($b !== '') {
		$localeVariants [] = $b; // .utf8
	}
	if ($short !== '') {
		$localeVariants [] = $short; // de_DE
	}

	// Add charset variations based on the current charset
	$localeVariantsWithCharsets = [];
	$charsets = $langConf ['charsets'] ?? [];
	$second = (is_array($charsets) && isset($charsets [1]) && is_string($charsets [1])) ? $charsets [1] : null;
	$useLegacy = ($second !== null && strcasecmp($charset, $second) === 0);

	/** @var array<int,string> $suffixes */
	$suffixes = $useLegacy ? [(string)($langConf ['localecharset_c'] ?? ''), (string)($langConf ['localecharset_d'] ?? '')] : [(string)($langConf ['localecharset_a'] ?? ''), (string)($langConf ['localecharset_b'] ?? '')];

	foreach ($localeVariants as $variant) {
		foreach ($suffixes as $suf) {
			if ($suf !== '') {
				$localeVariantsWithCharsets [] = $variant . $suf;
			}
		}
		$localeVariantsWithCharsets [] = $variant;
	}

	if ($debug) {
		error_log('set_locale -> Adding charset variations. Current charset: ' . $charset);
	}

	$supportedLocales = [];
	/** @phpstan-ignore-next-line */
	if (function_exists('shell_exec') && is_callable('shell_exec') && stripos(ini_get('disable_functions'), 'shell_exec') === false) {
		// Checks the supported locales with locale -a and only uses valid combinations
		$output = shell_exec('timeout 5s locale -a');
		if ($output !== null) {
			$supportedLocales = explode("\n", trim($output));
		}
	}

	// Check whether a locale is already set
	$currentLocale = setlocale(LC_TIME, 0);
	if ($debug) {
		error_log('set_locale -> Current locale before change: ' . $currentLocale);
	}

	$selectedLocale = false;

	// Validate LC_* constants before use
	$localeCategories = [];
	if (defined('LC_TIME')) $localeCategories [] = LC_TIME;
	if (defined('LC_MESSAGES')) $localeCategories [] = LC_MESSAGES;
	if (defined('LC_MONETARY')) $localeCategories [] = LC_MONETARY;
	if (defined('LC_NUMERIC')) $localeCategories [] = LC_NUMERIC;
	if (defined('LC_COLLATE')) $localeCategories [] = LC_COLLATE;

	// If supportedLocales is not empty, validate against it
	if (!empty($supportedLocales)) {
		foreach ($localeVariantsWithCharsets as $variant) {
			foreach ($localeCategories as $category) {
				$currentLocale = @setlocale($category, $variant);
				if ($currentLocale === false) {
					if ($debug) {
						error_log('set_locale -> Failed to set locale for category ' . $category . ': ' . $variant);
					}
				} else {
					if ($debug) {
						error_log('set_locale -> Locale set for category ' . $category . ': ' . $currentLocale);
					}
					$selectedLocale = $currentLocale;
				}
			}
			if ($selectedLocale) {
				break;
			}
		}
	} else {
		// If locale -a is not available, try setting directly
		foreach ($localeVariantsWithCharsets as $variant) {
			foreach ($localeCategories as $category) {
				$currentLocale = @setlocale($category, $variant);
				if ($currentLocale === false) {
					if ($debug) {
						error_log('set_locale -> No set locale for category ' . $category . ': ' . $variant);
					}
				} else {
					if ($debug) {
						error_log('set_locale -> Locale set for category ' . $category . ': ' . $variant);
					}
					$selectedLocale = $variant;
				}
			}
			if ($selectedLocale) {
				break;
			}
		}
	}

	if (!$selectedLocale) {
		// Fallback to a default locale if all else fails
		$defaultLocale = 'en_US.UTF-8';
		foreach ($localeCategories as $category) {
			$currentLocale = setlocale($category, $defaultLocale);
			if ($currentLocale === false) {
				if ($debug) {
					error_log('set_locale -> No set locale for category ' . $category . ', including fallback: ' . $defaultLocale);
				}
			} else {
				if ($debug) {
					error_log('set_locale -> Fallback locale set for category ' . $category . ': ' . $defaultLocale);
				}
			}
		}
		$selectedLocale = $defaultLocale;
	}

	if ($debug) {
		error_log('set_locale -> Locale successfully set to: ' . $selectedLocale);
	}
}

/**
 * Decode HTML named entities only. Numeric entities are preserved.
 */
function fp_decode_named_entities_only($text) {
	return preg_replace_callback('/&([a-zA-Z][a-zA-Z0-9]+);/', function($m) {
		return html_entity_decode($m [0], ENT_QUOTES | ENT_HTML5, 'UTF-8');
	}, $text);
}

/**
 * Function: fix_encoding_issues
 *
 * Description:
 * This function resolves encoding issues by ensuring that input text is correctly interpreted as UTF-8,
 * decoding HTML entities, handling typical mixed encodings (e.g., "Ã¤" to "ä"), and supporting conversion
 * back to specific target encodings (e.g., `ISO-8859-1`, `ISO-8859-15`, `ISO-8859-7`, `ISO-8859-5`).
 * It is designed for multilingual environments, ensuring compatibility with legacy systems and diverse input sources.
 *
 * Usage:
 * - **In PHP**: Use this function to sanitize and standardize text inputs before further processing or output.
 * - **In Smarty Templates**: Apply this function to variables in templates by registering it as a Smarty modifier
 *   or using it in preprocessed data sent to the templates. When necessary, specify the target encoding.
 *
 * Inputs:
 * - `$text` (string): The input string that might contain HTML entities, mixed encodings, or non-UTF-8 characters.
 * - `$target_encoding` (string): The desired output encoding. Defaults to `UTF-8`. Supported values include:
 *   `utf-8`, `iso-8859-1`, `iso-8859-15`, `iso-8859-7`, `iso-8859-5`.
 *
 * Outputs:
 * - (string): A string encoded in the specified target encoding, with HTML entities decoded and encoding issues resolved.
 *
 * Process:
 * 1. **HTML Entity Decoding**:
 *    - Decodes HTML entities like `&#8220;` into their respective characters (e.g., `“`).
 *    - Uses `html_entity_decode` with the `UTF-8` character set.
 * 2. **Encoding Verification and Conversion**:
 *    - Checks if the input text is valid UTF-8 using `mb_check_encoding`.
 *    - Attempts to convert the text from known source encodings (`ISO-8859-1`, `ISO-8859-15`, etc.)
 *      to UTF-8 using `mb_convert_encoding`.
 *    - If none of the predefined encodings work, forces UTF-8 using `mb_convert_encoding`.
 * 3. **Mixed Encoding Correction**:
 *    - Handles common mixed encoding issues, such as "Ã¤" (UTF-8 interpreted as ISO-8859-1) to "ä".
 *    - Uses a mapping table for multilingual support, covering German, Spanish, French, Italian, Czech,
 *      Danish, Greek, Russian, Portuguese, Dutch, and English special characters.
 * 4. **Final UTF-8 Enforcement**:
 *    - Ensures the text is consistently UTF-8 by re-encoding it with `mb_convert_encoding`.
 * 5. **Optional Target Encoding Conversion**:
 *    - If `$target_encoding` is not `UTF-8`, converts the UTF-8 text to the specified encoding
 *      using `mb_convert_encoding`. This supports FlatPress configurations or other system requirements.
 *
 * Limitations:
 * - Assumes text can be interpreted as UTF-8 or one of the predefined source encodings.
 * - FlatPress `$fp_config['locale']['charset']` should be lowercase to avoid misinterpretation of encodings.
 * - Complex encoding scenarios beyond predefined mappings may require additional adjustments.
 *
 * Example:
 * ```php
 * $text = "Hallo, das ist ein Ã¤lteres Dokument.";
 * $fixed_text = fix_encoding_issues($text, 'ISO-8859-1');
 * echo $fixed_text; // Output: "Hallo, das ist ein älteres Dokument."
 * ```
 *
 * Note:
 * - Ensure that text passed to Smarty templates matches the expected encoding to prevent double encoding issues.
 * - Supports FlatPress configurations and international applications with diverse character sets.
 */
function fix_encoding_issues($text, $target_encoding = 'UTF-8', $locale = null, $decode_all_entities = false, $respect_target = false) {
	global $fp_config;

	// Only apply target character set from config if not explicitly specified
	if (!$respect_target && isset($fp_config ['general'] ['charset']) && is_string($fp_config ['general'] ['charset'])) {
		$target_encoding = strtolower($fp_config ['general'] ['charset']);
	}

	// Resolve locale (allows explicit override)
	if ($locale === null && isset($fp_config ['locale'] ['lang'])) {
		$locale = (string) $fp_config ['locale'] ['lang'];
	}
	$locale_lc = strtolower((string) $locale);

	// Preserve numeric entities in legacy character sets, otherwise special characters will be lost.
	if (strtolower($target_encoding) === 'utf-8' || $decode_all_entities) {
		$text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	} else {
		$text = fp_decode_named_entities_only($text);
	}

	// Normalize to UTF-8
	if (!mb_check_encoding($text, 'UTF-8')) {
		$possible = ['ISO-8859-1', 'ISO-8859-2', 'ISO-8859-5', 'ISO-8859-7', 'ISO-8859-9', 'ISO-8859-15', 'Shift_JIS'];
		foreach ($possible as $enc) {
			$converted = @mb_convert_encoding($text, 'UTF-8', $enc);
			if ($converted !== false && mb_check_encoding($converted, 'UTF-8')) {
				$text = $converted;
				break;
			}
		}
		if (!mb_check_encoding($text, 'UTF-8')) {
			$text = @mb_convert_encoding($text, 'UTF-8', 'auto');
		}
	}

	// Fixing typical mojibake (UTF-8 - cp1252 incorrectly decoded)
	$grp_common = [
		'â€œ' => '“', 'â€�' => '”', 'â€˜' => '‘', 'â€™' => '’', 'â€“' => '–', 'â€”' => '—', 'â€¦' => '…', 'â‚¬' => '€', 'â€¢' => '•',
		'â„¢' => '™', 'Â©' => '©', 'Â®' => '®', 'Â°' => '°', 'Â«' => '«', 'Â»' => '»', 'Â¡' => '¡', 'Â¿' => '¿', 'Â·' => '·',
		'Â ' => "\u{00A0}", 'Â­' => '', 'â\x80\x94' => '—', 'â\x80\x98' => '‘', 'â\x80\x99' => '’', 'â\x80\x9C' => '“', 'â\x80\x9D' => '”',
		'ã\x80\x81' => '、', 'ã\x80\x82' => '。', 'ã\x81\x8C' => 'が', 'ã\x81¨' => 'と', 'ã\x81ª' => 'な', 'ã\x81®' => 'の', 'ã\x81²' => 'ひ',
		'ã\x82\x89' => 'ら', 'ã\x82«' => 'カ', 'ã\x82¿' => 'タ', 'ã\x83\x8A' => 'ナ', 'å\xAD\x97' => '字', 'æ\x96\x87' => '文',
		'æ\x97\xA5' => '日', 'æ\x9C\xAC' => '本', 'æ¼¢' => '漢', 'ç«\xA0' => '章', 'èª\x9E' => '語', 'ã\x81Œ' => 'が', 'ãƒŠ' => 'ナ',
		'ã‚«' => 'カ', 'ã‚¿' => 'タ', 'ã‚‰' => 'ら', 'ã€\x81' => '、', 'ã€\x82' => '。', 'å­—' => '字', 'æœ¬' => '本', 'æ–‡' => '文',
		'æ—¥' => '日', 'èªž' => '語', 'â€\x9d' => '”', 'â€\x9c' => '“', 'ã€‚' => '。'
	];
	// German
	$grp_de = ['Ã¤' => 'ä', 'Ã¶' => 'ö', 'Ã¼' => 'ü', 'Ã„' => 'Ä', 'Ã–' => 'Ö', 'Ãœ' => 'Ü', 'ÃŸ' => 'ß', 'Ã\x9C' => 'Ü', 'Ã\x9F' => 'ß'];
	// Danish
	$grp_da = ['Ã†' => 'Æ', 'Ã¦' => 'æ', 'Ã˜' => 'Ø', 'Ã¸' => 'ø', 'Ã…' => 'Å', 'Ã¥' => 'å'];
	// Spanish/ Euskara
	$grp_es = [
		'Ã¡' => 'á', 'Ã©' => 'é', 'Ã­' => 'í', 'Ã³' => 'ó', 'Ãº' => 'ú', 'Ã±' => 'ñ', 'Ã�' => 'Á', 'Ã‰' => 'É', 'Ã“' => 'Ó', 'Ãš' => 'Ú',
		'Ã‘' => 'Ñ', 'Ã¼' => 'ü', 'Ã\x91' => 'Ñ'
	];
	// Portuguese (Brazil)
	$grp_pt = [
		'Ã£' => 'ã', 'Ãµ' => 'õ', 'Ã¢' => 'â', 'Ãª' => 'ê', 'Ã´' => 'ô', 'Ã¡' => 'á', 'Ã©' => 'é', 'Ã­' => 'í', 'Ã³' => 'ó', 'Ãº' => 'ú',
		'Ã‡' => 'Ç', 'Ã§' => 'ç'
	];
	// French
	$grp_fr = [
		'Ã ' => 'à', 'Ã¡' => 'á', 'Ã¢' => 'â', 'Ãª' => 'ê', 'Ã«' => 'ë', 'Ã¨' => 'è', 'Ã®' => 'î', 'Ã´' => 'ô', 'Ã»' => 'û', 'Ã¹' => 'ù',
		'Ã§' => 'ç', 'Å“' => 'œ', 'Å’' => 'Œ', 'Ã€' => 'À', 'Ã‚' => 'Â', 'ÃŠ' => 'Ê', 'Ã‹' => 'Ë', 'Ãˆ' => 'È', 'ÃŽ' => 'Î', 'Ã”' => 'Ô',
		'Ã›' => 'Û', 'Ã™' => 'Ù', 'Ã‡' => 'Ç', 'Ã‰' => 'É', 'Ã\x80' => 'À', 'Ã\x87' => 'Ç', 'Å\x93' => 'œ'
	];
	// Italian
	$grp_it = [
		'Ã ' => 'à', 'Ã¨' => 'è', 'Ã¬' => 'ì', 'Ã²' => 'ò', 'Ã¹' => 'ù', 'Ã€' => 'À', 'Ãˆ' => 'È', 'ÃŒ' => 'Ì', 'Ã’' => 'Ò', 'Ã™' => 'Ù',
		'Ã‰' => 'É', 'Ã\xA0' => 'à'
	];
	// Nederlands
	$grp_nl = ['Ã´' => 'ô'];
	// Czech/ Slovenian
	$grp_cs_sl = [
		'Å¡' => 'š', 'Å ' => 'Š', 'Å¾' => 'ž', 'Å½' => 'Ž', 'Å™' => 'ř', 'Å˜' => 'Ř', 'Ä›' => 'ě', 'ÄŒ' => 'Č', 'Ä\x8d' => 'č', 'Ä�' => 'ď',
		'Å¥' => 'ť', 'Å¯' => 'ů', 'Åˆ' => 'ň', 'Äš' => 'Ě', 'Ã¡' => 'á', 'Ã©' => 'é', 'Ã­' => 'í', 'Ã³' => 'ó', 'Ãº' => 'ú', 'Ã½' => 'ý',
		'Ã�' => 'Á', 'Ã‰' => 'É', 'Ã\x8d' => 'Í', 'Ã“' => 'Ó', 'Ãš' => 'Ú', 'Ã\x9d' => 'Ý', 'Ä\x8F' => 'ď', 'Ä\x9B' => 'ě', 'Å\x88' => 'ň',
		'Å\x99' => 'ř', 'Å\xA0' => 'Š', 'â\x80\x93' => '–'
	];
	// Greek
	$grp_el = [
		'Î±' => 'α', 'Î²' => 'β', 'Î³' => 'γ', 'Î´' => 'δ', 'Îµ' => 'ε', 'Î¶' => 'ζ', 'Î·' => 'η', 'Î¸' => 'θ', 'Î¹' => 'ι', 'Îº' => 'κ',
		'Î»' => 'λ', 'Î¼' => 'μ', 'Î½' => 'ν', 'Î¾' => 'ξ', 'Î¿' => 'ο', 'Ï€' => 'π', 'Ï�' => 'ρ', 'Ïƒ' => 'σ', 'Ï\x82' => 'ς', 'Ï„' => 'τ',
		'Ï…' => 'υ', 'Ï†' => 'φ', 'Ï‡' => 'χ', 'Ïˆ' => 'ψ', 'Ï‰' => 'ω', 'Î¬' => 'ά', 'Î­' => 'έ', 'Î®' => 'ή', 'Î¯' => 'ί', 'ÏŒ' => 'ό',
		'ÏŽ' => 'ώ', 'ÏŠ' => 'ϊ', 'Ï‹' => 'ϋ', 'Î†' => 'Ά', 'Îˆ' => 'Έ', 'Î‰' => 'Ή', 'ÎŠ' => 'Ί', 'ÎŒ' => 'Ό', 'ÎŽ' => 'Ύ', 'Î\x8F' => 'Ώ',
		'Îª' => 'Ϊ', 'Î«' => 'Ϋ', 'Î\x95' => 'Ε', 'Î¤' => 'Τ', 'Ï\x80' => 'π', 'Ï\x81' => 'ρ', 'Ï\x83' => 'σ', 'Ï\x84' => 'τ', 'Ï\x86' => 'φ',
		'Ï\x8C' => 'ό', 'Î•' => 'Ε'
	];
	// Russian
	$grp_ru = [
		'Ð°' => 'а', 'Ð±' => 'б', 'Ð²' => 'в', 'Ð³' => 'г', 'Ð´' => 'д', 'Ðµ' => 'е', 'Ñ\x91' => 'ё', 'Ð¶' => 'ж', 'Ð·' => 'з', 'Ð¸' => 'и',
		'Ð¹' => 'й', 'Ðº' => 'к', 'Ð»' => 'л', 'Ð¼' => 'м', 'Ð½' => 'н', 'Ð¾' => 'о', 'Ð¿' => 'п', 'Ð ' => 'Р', 'Ð¡' => 'С', 'Ð¢' => 'Т',
		'Ð£' => 'У', 'Ð¤' => 'Ф', 'Ð¥' => 'Х', 'Ð¦' => 'Ц', 'Ð§' => 'Ч', 'Ð¨' => 'Ш', 'Ð©' => 'Щ', 'Ðª' => 'Ъ', 'Ð«' => 'Ы', 'Ð¬' => 'Ь',
		'Ð­' => 'Э', 'Ð®' => 'Ю', 'Ð¯' => 'Я', 'Ñ\x80' => 'р', 'Ñ\x81' => 'с', 'Ñ\x82' => 'т', 'Ñ\x83' => 'у', 'Ñ\x84' => 'ф', 'Ñ\x85' => 'х',
		'Ñ\x86' => 'ц', 'Ñ\x87' => 'ч', 'Ñ\x88' => 'ш', 'Ñ\x89' => 'щ', 'Ñ\x8a' => 'ъ', 'Ñ\x8b' => 'ы', 'Ñ\x8c' => 'ь', 'Ñ\x8d' => 'э',
		'Ñ\x8e' => 'ю', 'Ñ\x8f' => 'я', 'Ð�' => 'А', 'Ð‘' => 'Б', 'Ð’' => 'В', 'Ð“' => 'Г', 'Ð”' => 'Д', 'Ð•' => 'Е', 'Ð\x81' => 'Ё',
		'Ð–' => 'Ж', 'Ð—' => 'З', 'Ð˜' => 'И', 'Ð™' => 'Й', 'Ðš' => 'К', 'Ð›' => 'Л', 'Ðœ' => 'М', 'Ðž' => 'О', 'ÐŸ' => 'П',
		'ÑŒ' => 'ь', 'ÑŠ' => 'ъ', 'ÑŽ' => 'ю', 'Ñƒ' => 'у', 'Ñˆ' => 'ш', 'Ñ‘' => 'ё', 'Ñ‚' => 'т', 'Ñ„' => 'ф', 'Ñ†' => 'ц', 'Ñ‡' => 'ч',
		'Ñ…' => 'х', 'Ñ‰' => 'щ', 'Ñ‹' => 'ы', 'Ñ€' => 'р'
	];
	// Turkish
	$grp_tr = [
		'Ã‡' => 'Ç', 'Ã§' => 'ç', 'ÄŸ' => 'ğ', 'Äž' => 'Ğ', 'Ä±' => 'ı', 'IÌ‡' => 'İ', 'Ä°' => 'İ', 'Ã–' => 'Ö', 'Ã¶' => 'ö', 'ÅŸ' => 'ş',
		'Åž' => 'Ş', 'Ãœ' => 'Ü', 'Ã¼' => 'ü', 'Ä\x9F' => 'ğ', 'Å\x9F' => 'ş'
	];

	// Euskara nutzt spanische Diakritik
	if ($locale_lc === 'eu-es') {
		$groups = [$grp_es, $grp_common];
	} else {
		$groups = [$grp_de, $grp_da, $grp_es, $grp_pt, $grp_fr, $grp_it, $grp_cs_sl, $grp_el, $grp_ru, $grp_tr, $grp_nl, $grp_common];
	}
	$mappings = [];
	foreach ($groups as $g) {
		foreach ($g as $k => $v) {
			if (!isset($mappings [$k])) {
				// First wins
				$mappings [$k] = $v;
			}
		}
	}
	$text = strtr($text, $mappings);

	// Ensure the text is in UTF-8 for consistent processing
	$text = @mb_convert_encoding($text, 'UTF-8', 'UTF-8');

	// Decode HTML entities again after final conversion
	if (strtolower($target_encoding) === 'utf-8' || $decode_all_entities) {
		$text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	} else {
		$text = fp_decode_named_entities_only($text);
	}

	// Select target character set without data loss
	$enc = strtolower($target_encoding);
	if ($enc !== 'utf-8') {
		// Preferred legacy encoding
		/** @var array<string, array<int,string>> $pref */
		$pref = [];
		if ($locale_lc !== '') {
			/** @var array{charsets?: array<int,string>} $langconf */
			$langconf = (array) lang_getconf($fp_config ['locale'] ['lang']);
			$cs = $langconf ['charsets'] [1] ?? null;
			if (is_string($cs)) {
				$pref [$locale_lc] = [strtoupper($cs)];
			}
		}
		$candidates = [$enc];
		if (isset($pref [$locale_lc])) {
			foreach ($pref [$locale_lc] as $c) {
				if (!in_array(strtolower($c), array_map('strtolower', $candidates), true)) {
					$candidates [] = $c;
				}
			}
		}
		$chosen = null;
		foreach ($candidates as $cand) {
			$out = @iconv('UTF-8', strtoupper($cand) . '//IGNORE', $text);
			if ($out === false) {
				continue;
			}
			$back = @iconv(strtoupper($cand), 'UTF-8//IGNORE', $out);
			if ($back !== false && $back === $text) {
				$chosen = $cand;
				break;
			}
		}
		if ($chosen === null) {
			// If there is no lossless 8-bit alternative, stick with UTF-8.
			return $text;
		}
		$text = @iconv('UTF-8', strtoupper($chosen) . '//IGNORE', $text);
	}

	return $text;
}

/**
 * Normalizes buffered output to UTF-8, then converts to the configured locale charset.
 * Repairs mojibake and decodes entities before final conversion.
 * @param string $buffer Full output buffer (HTML/XML text)
 * @return string Converted buffer ready to send to the client
 */
function fp_output_encoding_handler($buffer) {
	$cfg = isset($GLOBALS ['fp_config']) ? $GLOBALS ['fp_config'] : [];

	// Only normalize if site charset is not UTF-8
	$siteCharset = strtolower((string)($cfg ['locale'] ['charset'] ?? 'utf-8'));
	if ($siteCharset === 'utf-8') {
		return $buffer;
	}

	// Enable only for text/XML responses
	$ctype = null;
	foreach (@headers_list() as $h) {
		if (stripos($h, 'Content-Type:') === 0) {
			$ctype = trim(substr($h, 13));
			break;
		}
	}
	// Leave images, downloads, etc. unchanged
	if ($ctype && !preg_match('~^(text/|application/(xhtml\+xml|rss\+xml|atom\+xml|xml))~i', (string)$ctype)) {
		return $buffer;
	}

	$target = 'utf-8';
	if (!empty($GLOBALS ['fp_config'] ['locale'] ['charset']) && is_string($GLOBALS ['fp_config'] ['locale'] ['charset'])) {
		$target = strtolower($GLOBALS ['fp_config'] ['locale'] ['charset']);
	}
	$locale = !empty($GLOBALS ['fp_config'] ['locale'] ['lang']) ? (string)$GLOBALS ['fp_config'] ['locale'] ['lang'] : null;
	// Pre-normalization: reliably convert mixed inputs to UTF-8
	if (function_exists('mb_convert_encoding')) {
		$lc = isset($GLOBALS ['langconf']) && is_array($GLOBALS ['langconf']) ? $GLOBALS ['langconf'] : [];
		$lang = strtolower((string)($GLOBALS ['fp_config'] ['locale'] ['lang'] ?? 'en-us'));
		$primary = strtoupper((string)($lc ['charsets'] [0] ?? 'UTF-8')); // mostly UTF-8
		$legacy = strtoupper((string)($lc ['charsets'] [1] ?? '')); // historical encoding

		$detect = ['UTF-8'];
		if ($legacy) {
			$detect [] = $legacy;
		}
		if ($primary && $primary !== 'UTF-8') {
			$detect [] = $primary;
		}
		// Language-specific candidates
		switch (substr($lang, 0, 2)) {
			case 'en':
			case 'pt':
				$detect = array_merge($detect, ['ISO-8859-1']);
				break;
			case 'cs':
			case 'sl':
				$detect = array_merge($detect, ['ISO-8859-2']);
				break;
			case 'ru':
				$detect = array_merge($detect, ['ISO-8859-5']);
				break;
			case 'el':
				$detect = array_merge($detect, ['ISO-8859-7']);
				break;
			case 'tr':
				$detect = array_merge($detect, ['ISO-8859-9']);
				break;
			case 'da':
			case 'de':
			case 'es':
			case 'eu':
			case 'fr':
			case 'it':
			case 'nl':
				$detect = array_merge($detect, ['ISO-8859-15']);
				break;
			case 'ja':
				$detect = array_merge($detect, ['Shift_JIS']);
				break;
		}
		$detect = implode(',', array_values(array_unique($detect)));
		// IMPORTANT: assign, do not concatenate
		$buffer = @mb_convert_encoding($buffer, 'UTF-8', $detect);
	}
	// Normalize UTF-8 (Mojibake, NBSP, quotes, dashes, etc.)
	$fixed = fix_encoding_issues($buffer, 'UTF-8', $locale);

	// Always convert to target character set with entity fallback
	if ($target !== 'utf-8') {
		if (function_exists('mb_convert_encoding')) {
			$prev = null;
			if (function_exists('mb_substitute_character')) {
				$prev = mb_substitute_character();
				@mb_substitute_character('entity');
			}
			$conv = @mb_convert_encoding($fixed, strtoupper($target), 'UTF-8');
			if ($conv !== false) {
				$fixed = $conv;
			}
			if (function_exists('mb_substitute_character') && $prev !== null) {
				@mb_substitute_character($prev);
			}
		} elseif (function_exists('iconv')) {
			$conv = @iconv('UTF-8', strtoupper($target) . '//TRANSLIT//IGNORE', $fixed);
			if ($conv !== false) {
				$fixed = $conv;
			}
		}
	}
	return $fixed;
}

/**
 * Converts $_GET/$_POST/$_COOKIE from the user's charset (locale) to UTF-8.
 * Leaves binary paths untouched.
 * This ensures that templates and PHP APIs never see non-UTF-8 bytes.
 * Required by system_init()
 */
function normalize_to_utf8() {
	$cfg = isset($GLOBALS ['fp_config']) ? $GLOBALS ['fp_config'] : [];
	$langId = strtolower((string)($cfg ['locale'] ['lang'] ?? 'en-us'));
	$siteCharset = strtolower((string)($cfg ['locale'] ['charset'] ?? 'utf-8'));

	// Only normalize if site charset is not UTF-8
	if ($siteCharset === 'utf-8') {
		return;
	}

	// Load Langconf - historical character set [1]
	$langconf = [];
	if (function_exists('lang_getconf')) {
		try {
			$langconf = lang_getconf($langId);
		} catch (\Throwable $e) {
			$langconf = [];
		}
	}
	$legacy = strtoupper((string)($langconf ['charsets'] [1] ?? ''));

	// Build recognition list: always UTF-8 first
	$detect = ['UTF-8'];
	if ($legacy && $legacy !== 'UTF-8') {
		$detect [] = $legacy;
	}

	// Language-safe fallbacks when [1] is missing
	switch (substr($langId, 0, 2)) {
		case 'el':
			if (!in_array('ISO-8859-7', $detect, true)) {
				// el-GR
				$detect [] = 'ISO-8859-7';
			}
			break;
		case 'ru':
			if (!in_array('ISO-8859-5', $detect, true)) {
				// ru-RU
				$detect [] = 'ISO-8859-5';
			}
			break;
		case 'tr':
			if (!in_array('ISO-8859-9', $detect, true)) {
				// tr-TR
				$detect [] = 'ISO-8859-9';
			}
			break;
		case 'cs':
		case 'sl':
			if (!in_array('ISO-8859-2', $detect, true)) {
				// cs-CZ, sl-SI
				$detect [] = 'ISO-8859-2';
			}
			break;
		case 'ja':
			foreach (['Shift_JIS'] as $sj) {
				if (!in_array($sj, $detect, true)) {
					// ja-JP
					$detect [] = $sj;
				}
			}
			break;
		default:
			// Latin languages: Prefer ISO-8859-15 if nothing is set
			if ($legacy === 'ISO-8859-1' || $legacy === '') {
				$detect [] = 'ISO-8859-15';
			}
			break;
	}

	// Add site charset as candidate
	$sc = strtoupper($siteCharset);
	if ($sc && !in_array($sc, $detect, true)) {
		$detect [] = $sc;
	}

	// Allow CP1252 in addition to Latin-1/15
	if (in_array('ISO-8859-1', $detect, true) || in_array('ISO-8859-15', $detect, true)) {
		$detect [] = 'Windows-1252';
	}

	$conv = function (&$v) use (&$conv, $detect) {
		if (is_array($v)) {
			foreach ($v as &$x) {
				$conv($x);
			}
			return;
		}
		if (!is_string($v) || $v === '') {
			return;
		}

		// UTF-8 fast path
		if (function_exists('mb_check_encoding') && @mb_check_encoding($v, 'UTF-8')) {
			return;
		}

		// mbstring: with recognition list
		if (function_exists('mb_convert_encoding')) {
			$fromList = implode(',', $detect);
			$cv = @mb_convert_encoding($v, 'UTF-8', $fromList);
			if ($cv !== false) {
				$v = $cv;
				return;
			}
		}

		// Fallback: try iconv sequentially
		if (function_exists('iconv')) {
			foreach ($detect as $enc) {
				$cv = @iconv($enc, 'UTF-8//IGNORE', $v);
				if ($cv !== false && $cv !== '') {
					$v = $cv;
					return;
				}
			}
		}
	};

	$conv($_GET);
	$conv($_POST);
	$conv($_COOKIE);
	$_REQUEST = array_merge($_COOKIE, $_GET, $_POST);

	if (function_exists('ini_set')) {
		@ini_set('default_charset', 'UTF-8');
	}
}

function set_default_html_ct() {
	if (PHP_SAPI === 'cli' || headers_sent()) {
		return;
	}
	foreach (headers_list() as $h) {
		if (stripos($h, 'Content-Type:') === 0) {
			return;
		}
	}
	$cs = strtoupper($GLOBALS ['fp_config'] ['locale'] ['charset'] ?? 'UTF-8');
	header('Content-Type: text/html; charset=' . $cs);
}
?>
