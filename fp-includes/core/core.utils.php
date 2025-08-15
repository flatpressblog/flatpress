<?php

// utils.php
// library of misc utilities

// function subkey sort
// function prototype :
// array utils_sksort(array $arr, string $key, int $flag=SORT_ASC)
// sorts an array of associative arrays by given key $key
// $flag can be SORT_ASC or SORT_DESC for ascending
// or descending order (defaults to ascending);
// other flags are the same of array_multisort() php function ;)
function utils_sksort($arr, $key, $flag = SORT_ASC) {
	if ($arr) {
		foreach ($arr as $val) {
			$sorter [] = $val [$key];
		}
		array_multisort($sorter, $flag, $arr);
		return $arr;
	} else {
		return false;
	}
}

// function prototype:
// bool utils_pattern_match(string $string, string $pattern)

// returns true if $pattern matches $string, else returns false (what else?)
// $pattern is a string containing standard shell-style jokers: * and ?
// regex are powerful but somtimes, too complicated :)
// usage: * matches a variable number of chars
// e.g. : doc*.txt matches document.txt, docs.txt, dock.txt, etc.
// and also doc.txt (note: I didn't want it to do that, but I didn't change it)
// ? matches one char, whichever is
// e.g. : document?.txt matches document1.txt, document2.txt, document3.txt, etc.
// likewise "*", it should match document.txt, too (but I hadn't tried it :) )

// code from http://www.php.net/function.glob.php.htm#54519
// by x_terminat_or_3 at yahoo dot country:fr
// thank you, man ;)
// as usual, slightly modified to fit my tastes :)

if (!function_exists('fnmatch')) {

	function fnmatch($pattern, $string) {
		if ($pattern == null) {
			return false;
		}

		// basically prepare a regular expression
		$out = null;
		$chunks = explode(';', $pattern);
		foreach ($chunks as $pattern) {
			$escape = array(
				'$',
				'^',
				'.',
				'{',
				'}',
				'(',
				')',
				'[',
				']',
				'|'
			);
			while (strpos($pattern, '**') !== false) {
				$pattern = str_replace('**', '*', $pattern);
			}

			foreach ($escape as $probe) {
				$pattern = str_replace($probe, "\\" . $probe, $pattern);
			}
			$pattern = str_replace('?*', '*', str_replace('*?', '*', str_replace('*', ".*", str_replace('?', '.{1,1}', $pattern))));
			$out [] = $pattern;
		}
		/*
		 * // NoWhereMan note: why splitting this in two? :)
		 * if(count($out)==1) return(eregi("^$out[0]$",$string)); else
		 */
		foreach ($out as $tester) {
			if (preg_match("/^" . $tester . "$/i", $string)) {
				return true;
			}
		}

		return false;
	}
}

/**
 * Tokenizer is a lightweight string tokenizer that splits a string
 * into substrings based on a set of delimiter characters.
 *
 * This class allows independent tokenization processes without using
 * the global state like the native strtok() function does.
 *
 * @package Utils
 */
class Tokenizer {
	/**
	 * The input string to tokenize.
	 *
	 * @var string
	 */
	private $str;

	/**
	 * The list of delimiter characters.
	 *
	 * @var string
	 */
	private $delims;

	 /**
	 * The current position within the string.
	 *
	 * @var int
	 */
	 private $position;

	/**
	 * Create a new Tokenizer instance.
	 *
	 * @param string $string  The input string to tokenize.
	 * @param string $delims  A string containing delimiter characters.
	 */
	public function __construct(string $string, string $delims) {
		$this->str = $string;
		$this->delims = $delims;
		$this->position = 0;
	}

	/**
	 * Get the next token from the string.
	 *
	 * @return string|false Returns the next token as a string, or false if no more tokens are found.
	 */
	public function nextToken() {
		if ($this->position >= strlen($this->str)) {
			return false;
		}

		// Skip leading delimiters
		while ($this->position < strlen($this->str) && strpos($this->delims, $this->str[$this->position]) !== false) {
			$this->position++;
		}

		if ($this->position >= strlen($this->str)) {
			return false;
		}

		$start = $this->position;

		// Find next delimiter
		while ($this->position < strlen($this->str) && strpos($this->delims, $this->str[$this->position]) === false) {
			$this->position++;
		}

		return substr($this->str, $start, $this->position - $start);
	}
}

/**
 * Parses a delimited string into an associative array of key-value pairs.
 *
 * The input string must be formatted as:
 *     "KEY1|value1|KEY2|value2" (or using other delimiters)
 *
 * It will be converted into:
 *     ['key1' => 'value1', 'key2' => 'value2']
 *
 * Multiple delimiters can be used (e.g. ",:|"). Keys are lowercased in the result.
 * Empty tokens are ignored. If an odd number of tokens is found, the last key
 * without a value will be ignored.
 *
 * If $keyupper is true, only keys that contain at least one uppercase letter (A–Z),
 * a hyphen (-), or an underscore (_) are accepted. Other keys will be skipped.
 *
 * @param string $string    The delimited input string to parse.
 * @param string $delims    One or more delimiter characters (default: "|").
 * @param bool   $keyupper  Whether to only accept keys with [A-Z\-_] (default: true).
 *
 * @return array<string, string> Associative array of parsed and filtered key-value pairs.
 */
function utils_kexplode($string, $delim = '|', $keyupper = true) {
	$arr = array();
	$string = trim($string);

	$tokenizer = new Tokenizer($string, $delim);

	$k = strtolower($tokenizer->nextToken());
	if (empty($k)) {
		return $arr;
	}

	$arr[$k] = $tokenizer->nextToken();
	if (empty($arr[$k])) {
		return $arr;
	}

	while (($k = $tokenizer->nextToken()) !== false) {
		if ($keyupper && !preg_match('/[A-Z-_]/', $k)) {
			continue;
		}

		$arr [strtolower($k)] = $tokenizer->nextToken();
	}

	return $arr;
}

/**
 * function utils_newkexplode($string, $delim='|') {
 *
 * $arr = array();
 *
 * $lastoffset = $offset = 0;
 * $len = strlen($string);
 *
 * while ($lastoffset<$len) {
 * $offset = strpos($string, $delim, $lastoffset);
 * $key = substr($string, $lastoffset, $offset-$lastoffset);
 * //echo 'parsing key: ', $key, $offset, chr(10);
 *
 * $lastoffset = $offset + 1;
 *
 * if (!ctype_upper($key))
 * trigger_error("Failed parsing \"$string\"
 * keys were supposed to be UPPERCASE", E_USER_ERROR);
 *
 * $offset = strpos($string, $delim, $lastoffset);
 *
 * if ($offset===false)
 * $offset = $len;
 *
 * $val = substr($string, $lastoffset, $offset-$lastoffset);
 *
 * //echo 'parsing value ', $val, $offset, chr(10);
 *
 * $lastoffset = $offset + 1;
 *
 * $arr[$key] = $val;
 *
 * }
 * return $arr;
 *
 * }
 */

// function prototype:
// array utils_kimplode(string $string, string $delim='|')

// explodes a string into an array by the given delimiter;
// delimiter defaults to pipe ('|').
// the string must be formatted as in:
// key1|value1|key2|value2 , etc.
// the array will look like
// $arr['key1'] = 'value1'; $arr['key2'] = 'value2'; etc.
function utils_kimplode($arr, $delim = '|') {
	$string = "";
	foreach ($arr as $k => $val) {
		if ($val) {
			$string .= strtoupper($k) . $delim . ($val) . $delim;
		}
	}
	return $string;
}

/**
 *
 * @todo send mail to admin
 */
function &utils_explode_recursive($array, &$string, $rdelim, $ldelim = '', $outerldelim = '', $outerrdelim = '') {
	$string .= $outerldelim;

	while ($val = array_shift($array)) {

		$string .= $rdelim;
		if (is_array($val)) {
			$string .= utils_explode_recursive($val, $string, $rdelim, $ldelim, $outerldelim, $outerrdelim);
		} else {
			$string .= $val;
		}

		$string .= $ldelim;
	}

	$string .= $outerrdelim;
}

function utils_validateinput($str) {
	if (preg_match('/[^a-z0-9\-_]/i', $str)) {
		trigger_error("String \"" . $str . "\" is not a valid input", E_USER_ERROR);
		// return false;
	} else {
		return true;
	}
}

function utils_cut_string($str, $maxc) {
	$car = strlen($str);
	if ($car > $maxc) {
		return substr($str, 0, $maxc) . "...";
	} else {
		return $str;
	}
}

function utils_status_header($status) {
	switch ($status) {
		case 301:
			header("HTTP/1.1 301 Moved Permanently");
			break;
		case 403:
			header("HTTP/1.1 403 Forbidden");
			break;
		case 404:
			header("HTTP/1.1 404 Not Found");
			break;
	}
}

// code from php.net ;)
// defaults to index.php ;)
function utils_redirect($location = "", $absolute_path = false, $red_type = null) {
	if (!$absolute_path) {
		$location = BLOG_BASEURL . $location;
	}

	if (function_exists('wp_redirect')) {
		wp_redirect($location);
	} else {
		header("Location: " . $location);
	}

	exit();
}

/*
 * utils_geturlstring()
 *
 * @return string complete url string as displayed in the browser
 *
 */
function utils_geturlstring() {
	$str = BLOG_BASEURL . $_SERVER ['PHP_SELF'];
	if ($_SERVER ['QUERY_STRING']) {
		$str .= '?' . $_SERVER ['QUERY_STRING'];
	}
	return $str;
}

// custom array_merge:
// pads the second array to match the length of the first
// this can be improved, anyway for now I'd just
// do a quick & dirty solution :)
function utils_array_merge($arr1, $arr2) {
	$len = count($arr1 [0]);

	foreach ($arr2 as $k => $v) {
		$arr2 [$k] = array_pad((array) $v, $len, null);
	}

	return array_merge($arr1, $arr2);
}

/*
 * Simple function to replicate PHP 5 behaviour
 */
function utils_microtime() {
	list ($usec, $sec) = explode(" ", microtime());
	return ((float) $usec + (float) $sec);
}

function utils_countdashes($string, &$rest) {
	$string = trim($string);
	$i = 0;
	while (isset($string [$i]) && $string [$i] === '-') {
		$i++;
	}
	if ($i) {
		$rest = substr($string, $i);
	} else {
		$rest = $string;
	}

	return $i;
}

function utils_mail($from = '', $subject = '', $message = '', $headers = '') {
	global $fp_config;
	/**
	 * Many e-mail providers only allow e-mail addresses from domains that are known to the mail server via their mail server (SMTP host).
	 * As a rule, these are all e-mail addresses for domains that are registered with the provider.
	 * In some cases, however, there may be further restrictions, which you should ask your mail provider about.
	 * When using the PHP mail() function, clarify directly with your provider what restrictions and regulations there are for sending e-mails.
	 * For this reason, you should have set a sender e-mail address that is permitted for sending by the mail system used (e.g. SMTP).
	 */

	// Use default sender from configuration
	if (empty($from)) {
		$from = $fp_config ['general'] ['email'];
	}

	// Security filter: Prevent header injection
	$from = filter_var($from, FILTER_VALIDATE_EMAIL) ? $from : $fp_config ['general'] ['email'];
	$subject = preg_replace('/[\r\n]/', '', $subject);
	$headers = preg_replace('/[\r\n]/', '', $headers);

	// Define allowed character sets
	$allowed_charsets = ['UTF-8', 'ISO-8859-1','ISO-8859-5', 'ISO-8859-9', 'ISO-8859-15', 'Windows-1252', 'Shift_JIS', 'EUC-JP', 'GB2312'];

	// Validate and set charset
	$charset = strtoupper($fp_config ['locale'] ['charset'] ?? 'UTF-8');
	if (!in_array($charset, $allowed_charsets)) {
		// Fallback to UTF-8
		$charset = 'UTF-8';
	}

	// Convert subject to the correct charset
	if ($charset !== 'UTF-8') {
		$subject = mb_convert_encoding($subject, $charset, 'UTF-8');
	}

	// Set default header if not specified
	if (empty($headers)) {
		$headers = "MIME-Version: 1.0\r\n" .
			"From: " . $from . "\r\n" .
			"Content-Type: text/plain; charset=\"" . $charset . "\"\r\n";
	}

	// Add Date header for RFC 5322 compliance
	$headers = "Date: " . date('r') . "\r\n" . $headers;

	// Encode subject with Base64 for UTF-8
	$encoded_subject = '=?' . $charset . '?B?' . base64_encode($subject) . '?=';

	// Ensure session is started
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}

	// Rate limiting: Maximum 30 emails per hour
	if (!isset($_SESSION ['email_sent'])) {
		$_SESSION ['email_sent'] = [];
	}

	$_SESSION ['email_sent'] = array_filter(
		$_SESSION ['email_sent'],
		function($t) {
			return $t > time() - 3600;
		}
	);

	if (count($_SESSION ['email_sent']) >= 30) {
		// Limit reached
		return false;
	}

	$_SESSION ['email_sent'] [] = time();

	// Send email
	return mail($fp_config ['general'] ['email'], $encoded_subject, $message, $headers);
}

/**
 * props: http://crisp.tweakblogs.net/blog/2031
 */
function utils_validateIPv4($IP) {
	return $IP == long2ip(ip2long($IP));
}

function utils_validateIPv6($IP) {
	// fast exit for localhost
	if (strlen($IP) < 3) {
		return $IP == '::';
	}

	// Check if part is in IPv4 format
	if (strpos($IP, '.')) {
		$lastcolon = strrpos($IP, ':');
		if (!($lastcolon && validateIPv4(substr($IP, $lastcolon + 1)))) {
			return false;
		}
		// replace IPv4 part with dummy
		$IP = substr($IP, 0, $lastcolon) . ':0:0';
	}

	// check uncompressed
	if (strpos($IP, '::') === false) {
		return preg_match('/^(?:[a-f0-9]{1,4}:){7}[a-f0-9]{1,4}$/i', $IP);
	}

	// check colon-count for compressed format
	if (substr_count($IP, ':') < 8) {
		return preg_match('/^(?::|(?:[a-f0-9]{1,4}:)+):(?:(?:[a-f0-9]{1,4}:)*[a-f0-9]{1,4})?$/i', $IP);
	}

	return false;
}

/**
 * Retrieves and optionally anonymizes the client's IP address based on the plugin configuration.
 * 
 * - If `allowVisitorIp` is set to true in the configuration, the original IP is returned after validation.
 * - If `allowVisitorIp` is false or not set, the IP address is anonymized and validated.
 * - Supports both IPv4 and IPv6 addresses.
 *
 * @global array $fp_config Plugin configuration.
 * @return string The (optionally anonymized) validated IP address, or an empty string if invalid.
 */
function utils_ipget() {
	global $fp_config;

	// Retrieve the configuration value for allowing visitor IPs.
	$allowVisitorIp = $fp_config ['plugins'] ['fpprotect'] ['allowVisitorIp'] ?? false;
	$ip = '';

	// Retrieve the client's IP address
	if (!empty($_SERVER ['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER ['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER ['HTTP_X_FORWARDED_FOR'])) {
		$ip = $_SERVER ['HTTP_X_FORWARDED_FOR'];
	} elseif (!empty($_SERVER ['REMOTE_ADDR'])) {
		$ip = $_SERVER ['REMOTE_ADDR'];
	} elseif (getenv("HTTP_CLIENT_IP")) {
		$ip = getenv("HTTP_CLIENT_IP");
	} elseif (getenv("HTTP_X_FORWARDED_FOR")) {
		$ip = getenv("HTTP_X_FORWARDED_FOR");
	} elseif (getenv("REMOTE_ADDR")) {
		$ip = getenv("REMOTE_ADDR");
	}

	if ($allowVisitorIp) {
		// If visitor IP is allowed, validate and return the original IP
		if (utils_validateIPv4($ip) || utils_validateIPv6($ip)) {
			return $ip;
		}
		return '';
	} else {
		// If visitor IP is not allowed, anonymize and validate
		if (utils_validateIPv4($ip)) {
			// Backup the original IP address.
			$_SERVER ['ORIG_REMOTE_ADDR'] = $ip;

			// Replace the last two blocks with 0.123 (e.g. 217.83.0.123)
			$octets = explode(".", $ip);
			if (count($octets) === 4) {
				$octets [2] = "0";
				$octets [3] = "123";
				$ip = implode(".", $octets);

				// Update the server variables with the anonymized IP.
				if (!empty($_SERVER ['HTTP_CLIENT_IP'])) {
					$_SERVER ['HTTP_CLIENT_IP'] = $ip;
				} elseif (!empty($_SERVER ['HTTP_X_FORWARDED_FOR'])) {
					$_SERVER ['HTTP_X_FORWARDED_FOR'] = $ip;
				} elseif (!empty($_SERVER ['REMOTE_ADDR'])) {
					$_SERVER ['REMOTE_ADDR'] = $ip;
				}
				return $ip;
			}
			return '';
		}

		if (utils_validateIPv6($ip)) {
			// Backup the original IP address.
			$_SERVER ['ORIG_REMOTE_ADDR'] = $ip;

			// Anonymize the IPv6 address using a hash of browser language, user agent, and the original IP.
			$ip = implode(':', str_split(md5($_SERVER ['HTTP_ACCEPT_LANGUAGE'] . $_SERVER ['HTTP_USER_AGENT'] . $ip), 4));

			// Update the server variables with the anonymized IP.
			if (!empty($_SERVER ['HTTP_CLIENT_IP'])) {
				$_SERVER ['HTTP_CLIENT_IP'] = $ip;
			} elseif (!empty($_SERVER ['HTTP_X_FORWARDED_FOR'])) {
				$_SERVER ['HTTP_X_FORWARDED_FOR'] = $ip;
			} elseif (!empty($_SERVER ['REMOTE_ADDR'])) {
				$_SERVER ['REMOTE_ADDR'] = $ip;
			}
			return $ip;
		}
		return '';
	}
}

function utils_nocache_headers() {
	@ header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');
	@ header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	@ header('Cache-Control: no-cache, must-revalidate, max-age=0');
	@ header('Pragma: no-cache');
}

// from http://nadeausoftware.com/articles/2007/06/php_tip_how_get_web_page_using_curl
// code under OSI BSD
/**
 * Get a web file (HTML, XHTML, XML, image, etc.) from a URL.
 * Return an
 * array containing the HTTP server response header fields and content.
 */
function utils_geturl($url) {
	/*
	 * if (ini_get('allow_url_fopen')) {
	 * return array('content' => io_load_file($url));
	 * }
	 */
	if (!function_exists('curl_init')) {
		trigger_error('curl extension is not installed');
		return array();
	}

	$options = array(
		CURLOPT_RETURNTRANSFER => true, // return web page
		CURLOPT_HEADER => false, // don't return headers
		CURLOPT_ENCODING => "", // handle all encodings
		CURLOPT_USERAGENT => "spider", // who am i
		CURLOPT_AUTOREFERER => true, // set referer on redirect
		CURLOPT_CONNECTTIMEOUT => 120, // timeout on connect
		CURLOPT_TIMEOUT => 120, // timeout on response
		CURLOPT_FOLLOWLOCATION => true, // follow redirects
		CURLOPT_MAXREDIRS => 10 // stop after 10 redirects
	);

	$ch = curl_init($url);
	curl_setopt_array($ch, $options);
	$content = curl_exec($ch);
	$err = curl_errno($ch);
	$errmsg = curl_error($ch);
	$header = curl_getinfo($ch);
	curl_close($ch);

	$header ['errno'] = $err;
	$header ['errmsg'] = $errmsg;
	$header ['content'] = $content;
	return $header;
}

function fplog($str) {
	if (!defined('DEBUG_MODE')) {
		echo "\n[DEBUG] " . $str . " \n";
	}
}

/**
 * Shift an element with its key off the beginning of array.
 * Just like array_shift(), but for an associative array.
 *
 * @param mixed $arr Input, which can be an array.
 * @return array|null The shifted key-value pair as array, or null if array is empty or not an array
 */
function utils_array_kshift(&$arr) {
	if (!is_array($arr) || count($arr) === 0) {
		return null;
	}
	list ($k) = array_keys($arr);
	$r = array(
		$k => $arr [$k]
	);
	unset($arr [$k]);
	return $r;
}

/**
 * Versioned asset URLs (JS/CSS/images) via query parameter v=…
 * Priority: $version (explicit) > filemtime (local file) > SYSTEM_VER (fallback).
 *
 * @param string      $path     URL or path (relative or absolute, with/without schema)
 * @param string|null $version  Force specific version (e.g., build/release number)
 * @return string     Versioned URL
 */
function utils_asset_ver(string $path, $version = null): string {
	if ($path === '') {
		return '';
	}

	// Explicit version takes precedence
	$ver = (is_string($version) && $version !== '') ? (string)$version : null;

	// If no version is specified: try mtime of local file
	if ($ver === null) {
		$u = @parse_url($path);
		$p = $u ['path'] ?? $path;
		if (defined('ABS_PATH')) {
			$file = rtrim(ABS_PATH, "/\\") . '/' . ltrim($p, "/\\");
			if (is_file($file)) {
				$m = @filemtime($file);
				if ($m) {
					$ver = (string)$m;
				}
			}
		}
	}

	// Fallback: FlatPress system version
	if ($ver === null && defined('SYSTEM_VER')) {
		$ver = (string)SYSTEM_VER;
	}

	// If still nothing: return original path
	if ($ver === null || $ver === '') {
		return $path;
	}

	return utils_url_set_query_param($path, 'v', $ver);
}

/**
 * Sets/replaces a query parameter in a URL (RFC-3986).
 */
function utils_url_set_query_param(string $url, string $key, string $value): string {
	$u = @parse_url($url);
	if ($u === false) {
		// Fallback: simply append
		return $url . (strpos($url, '?') === false ? '?' : '&') . rawurlencode($key) . '=' . rawurlencode($value);
	}
	$scheme = isset($u ['scheme']) ? $u ['scheme'] . '://' : '';
	$auth = '';
	if (isset($u ['user'])) {
		$auth = $u ['user'];
		if (isset($u ['pass'])) $auth .= ':' . $u ['pass'];
		$auth .= '@';
	}
	$host = $u ['host'] ?? '';
	$port = isset($u ['port']) ? ':' . $u ['port'] : '';
	$path = $u ['path'] ?? '';
	$qArr = [];
	if (!empty($u ['query'])) {
		parse_str($u ['query'], $qArr);
	}
	$qArr [$key] = $value;
	$query = http_build_query($qArr, '', '&', PHP_QUERY_RFC3986);
	$frag = isset($u ['fragment']) ? '#' . $u ['fragment'] : '';
	return $scheme . $auth . $host . $port . $path . ($query !== '' ? '?' . $query : '') . $frag;
}
?>
