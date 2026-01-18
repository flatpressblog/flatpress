<?php
/**
 * FlatPress mbstring polyfill (best-effort)
 * https://github.com/symfony/polyfill-mbstring
 *
 * Goal: Prevent fatal errors on hosts without ext-mbstring by providing a minimal
 * subset of mb_* functions/constants used by FlatPress and bundled Smarty 5.x.
 *
 * Notes:
 * - This is NOT a full mbstring replacement.
 * - If iconv/iconv_* functions are available, they are preferred.
 * - UTF-8 validation uses preg_match('//u', ...).
 *
 * Compatible with PHP 7.2+.
 */

if (extension_loaded('mbstring')) {
	return;
}

// mbstring case constants used by Smarty (avoid fatal "Undefined constant" on PHP 8+)
if (!defined('MB_CASE_UPPER')) {
	define('MB_CASE_UPPER', 0);
}
if (!defined('MB_CASE_LOWER')) {
	define('MB_CASE_LOWER', 1);
}
if (!defined('MB_CASE_TITLE')) {
	define('MB_CASE_TITLE', 2);
}

if (!function_exists('fp_mbstring_polyfill_normalize_encoding')) {
	/**
	 * @param mixed $enc
	 * @return string
	 */
	function fp_mbstring_polyfill_normalize_encoding($enc) {
		$e = is_string($enc) ? trim($enc) : '';
		if ($e === '') {
			return 'UTF-8';
		}
		$e = str_replace('_', '-', $e);
		$e_uc = strtoupper($e);

		// Common aliases
		if ($e_uc === 'UTF8') {
			return 'UTF-8';
		}
		if ($e_uc === 'ISO8859-1') {
			return 'ISO-8859-1';
		}
		if ($e_uc === 'ISO8859-15') {
			return 'ISO-8859-15';
		}
		return $e;
	}
}

if (!function_exists('mb_check_encoding')) {
	/**
	 * @param mixed $var
	 * @param string|null $encoding
	 * @return bool
	 */
	function mb_check_encoding($var, $encoding = null) {
		if (!is_string($var)) {
			return false;
		}

		$enc = fp_mbstring_polyfill_normalize_encoding($encoding);
		if (strcasecmp($enc, 'UTF-8') === 0) {
			return preg_match('//u', $var) === 1;
		}

		// Best effort for non-UTF-8: try iconv roundtrip
		if (function_exists('iconv')) {
			$out = @iconv($enc, $enc . '//IGNORE', $var);
			return $out !== false;
		}

		// Without iconv, no reliable validation -> "true" as a degrading fallback
		return true;
	}
}

if (!function_exists('mb_convert_encoding')) {
	/**
	 * @param mixed $string
	 * @param string $to_encoding
	 * @param mixed $from_encoding
	 * @return string|false
	 */
	function mb_convert_encoding($string, $to_encoding, $from_encoding = null) {
		if (!is_string($string)) {
			$string = (string)$string;
		}

		$to = fp_mbstring_polyfill_normalize_encoding($to_encoding);

		// Handle array or comma-separated list
		$from = $from_encoding;
		if (is_array($from)) {
			$from = reset($from);
		}
		if (is_string($from) && strpos($from, ',') !== false) {
			$parts = array_filter(array_map('trim', explode(',', $from)));
			$from = isset($parts[0]) ? $parts[0] : $from;
		}
		$from = fp_mbstring_polyfill_normalize_encoding($from);

		// AUTO detection (very rough)
		if (strcasecmp($from, 'AUTO') === 0) {
			if (mb_check_encoding($string, 'UTF-8')) {
				$from = 'UTF-8';
			} else {
				$guesses = array('Windows-1252', 'ISO-8859-1', 'ISO-8859-15');
				$from = $guesses[0];
				if (function_exists('iconv')) {
					foreach ($guesses as $g) {
						$try = @iconv($g, 'UTF-8//IGNORE', $string);
						if ($try !== false) {
							$from = $g;
							break;
						}
					}
				}
			}
		}

		if (strcasecmp($from, $to) === 0) {
			return $string;
		}

		if (function_exists('iconv')) {
			$out = @iconv($from, $to . '//IGNORE', $string);
			if ($out === false) {
				$out = @iconv($from, $to, $string);
			}
			return $out;
		}

		// Without iconv, no secure conversion -> degrading fallback.
		return $string;
	}
}

if (!function_exists('mb_strlen')) {
	/**
	 * @param mixed $string
	 * @param string|null $encoding
	 * @return int
	 */
	function mb_strlen($string, $encoding = null) {
		if (!is_string($string)) {
			$string = (string)$string;
		}

		$enc = fp_mbstring_polyfill_normalize_encoding($encoding);

		if (function_exists('iconv_strlen')) {
			$len = @iconv_strlen($string, $enc);
			if ($len !== false) {
				return (int)$len;
			}
		}

		if (strcasecmp($enc, 'UTF-8') === 0 && preg_match('//u', $string) === 1) {
			$cnt = preg_match_all('/./us', $string, $m);
			return $cnt === false ? strlen($string) : (int)$cnt;
		}

		return strlen($string);
	}
}

if (!function_exists('mb_substr')) {
	/**
	 * @param mixed $string
	 * @param int $start
	 * @param int|null $length
	 * @param string|null $encoding
	 * @return string
	 */
	function mb_substr($string, $start, $length = null, $encoding = null) {
		if (!is_string($string)) {
			$string = (string)$string;
		}

		$enc = fp_mbstring_polyfill_normalize_encoding($encoding);

		if (function_exists('iconv_substr')) {
			if ($length === null) {
				$out = @iconv_substr($string, $start, mb_strlen($string, $enc), $enc);
			} else {
				$out = @iconv_substr($string, $start, $length, $enc);
			}
			if ($out !== false) {
				return (string)$out;
			}
		}

		// UTF-8 best-effort
		if (strcasecmp($enc, 'UTF-8') === 0 && preg_match('//u', $string) === 1) {
			$chars = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);
			if (!is_array($chars)) {
				return $length === null ? substr($string, (int)$start) : substr($string, (int)$start, (int)$length);
			}

			$start_i = (int)$start;
			if ($start_i < 0) {
				$start_i = count($chars) + $start_i;
			}

			if ($length === null) {
				$slice = array_slice($chars, $start_i);
			} else {
				$slice = array_slice($chars, $start_i, (int)$length);
			}
			return implode('', $slice);
		}

		return $length === null ? substr($string, (int)$start) : substr($string, (int)$start, (int)$length);
	}
}

if (!function_exists('mb_strtolower')) {
	/**
	 * @param mixed $string
	 * @param string|null $encoding
	 * @return string
	 */
	function mb_strtolower($string, $encoding = null) {
		return strtolower((string)$string);
	}
}

if (!function_exists('mb_strtoupper')) {
	/**
	 * @param mixed $string
	 * @param string|null $encoding
	 * @return string
	 */
	function mb_strtoupper($string, $encoding = null) {
		return strtoupper((string)$string);
	}
}

if (!function_exists('mb_convert_case')) {
	/**
	 * @param mixed $string
	 * @param int $mode
	 * @param string|null $encoding
	 * @return string
	 */
	function mb_convert_case($string, $mode, $encoding = null) {
		$s = (string)$string;
		$m = (int)$mode;

		if ($m === (int)MB_CASE_UPPER) {
			return mb_strtoupper($s, $encoding);
		}
		if ($m === (int)MB_CASE_LOWER) {
			return mb_strtolower($s, $encoding);
		}
		if ($m === (int)MB_CASE_TITLE) {
			// Best-effort Title Case (ASCII-oriented)
			return ucwords(mb_strtolower($s, $encoding));
		}

		// Unknown mode -> degrading fallback
		return mb_strtolower($s, $encoding);
	}
}

if (!function_exists('mb_substitute_character')) {
	/**
	 * Best-effort stub: only stores the last value set.
	 * mbstring uses this for conversions; without mbstring, its only task here is
	 * to prevent fatal errors.
	 *
	 * @param mixed $substitute_character
	 * @return mixed
	 */
	function mb_substitute_character($substitute_character = null) {
		static $current = 'none';
		if ($substitute_character === null) {
			return $current;
		}
		$prev = $current;
		$current = $substitute_character;
		return $prev;
	}
}

if (!function_exists('mb_regex_encoding')) {
	/**
	 * @param string|null $encoding
	 * @return string|bool
	 */
	function mb_regex_encoding($encoding = null) {
		static $current = 'UTF-8';
		if ($encoding === null) {
			return $current;
		}
		$current = fp_mbstring_polyfill_normalize_encoding($encoding);
		return true;
	}
}

if (!function_exists('mb_split')) {
	/**
	 * @param string $pattern
	 * @param string $string
	 * @param int $limit
	 * @return array|false
	 */
	function mb_split($pattern, $string, $limit = -1) {
		// mb_split expects a pattern without delimiters; we use PCRE as best effort.
		// If $pattern is already delimited, preg_split may work directly.
		$result = @preg_split($pattern, $string, $limit);
		if (is_array($result)) {
			return $result;
		}

		// Fallback: Delimit safely and set UTF-8 modifier
		$delim = '#';
		$safePattern = str_replace($delim, '\\' . $delim, (string)$pattern);
		$wrapped = $delim . $safePattern . $delim . 'u';

		$result = @preg_split($wrapped, $string, $limit);
		return is_array($result) ? $result : false;
	}
}
?>
