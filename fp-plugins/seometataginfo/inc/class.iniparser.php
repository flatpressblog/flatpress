<?php

/**
 * Author: Enrico Reinsdorf (enrico@re-design.de)
 * Author URI: www.re-design.de
 * Changelog: RAM hits instead of I/O
 * Change-Date: 06.09.2025
 */
class iniParser {

	var $_iniFilename = '';

	var $_iniParsedArray = array();

	/**
	 * Creates a multidimensional array from the INI file
	 */
	function __construct($filename) {
		$this->_iniFilename = $filename;

		static $cache = array();

		$rp = @realpath($this->_iniFilename);
		if ($rp === false) { $rp = $this->_iniFilename; }
		$exists = @file_exists($rp);
		$mt = $exists ? @filemtime($rp) : 0;
		$sz = $exists ? @filesize($rp) : 0;
		$token = $mt . ':' . $sz;
		$local_key = $rp . '|' . $token;
		$apcu_key = 'fp:ini:' . sha1($local_key);

		$apcu_on = false;
		if (function_exists('apcu_fetch')) {
			if (function_exists('apcu_enabled')) {
				$apcu_on = @apcu_enabled();
			} else {
				$apcu_on = (bool) @ini_get('apcu.enabled') || (bool) @ini_get('apc.enabled');
			}
			if ($apcu_on && PHP_SAPI === 'cli' && !((bool) @ini_get('apc.enable_cli'))) {
				$apcu_on = false;
			}
		}

		// per-Request-Cache
		if (isset($cache [$local_key])) {
			$this->_iniParsedArray = $cache [$local_key];
			return;
		}

		// Optional APCu hot cache
		if ($apcu_on) {
			$v = apcu_fetch($apcu_key, $hit);
			if ($hit && is_array($v)) {
				$this->_iniParsedArray = $cache [$local_key] = $v;
				return;
			}
		}

		// Fallback: parse file now
		$file_content = $exists ? @file($rp) : array();

		$this->_iniParsedArray = array();
		$cur_sec = false;
		foreach ($file_content as $line) {
			$line = trim($line);
			if ($line !== '' && $line [0] === '[' && substr($line, -1) === ']') {
				$sec_name = substr($line, 1, -1);
				// If this section already exists, ignore the line.
				if (!isset($this->_iniParsedArray [$sec_name])) {
					$this->_iniParsedArray [$sec_name] = array();
					$cur_sec = $sec_name;
				}
			} else {
				$line_arr = explode('=', $line, 2);
				$lhs = isset($line_arr [0]) ? trim($line_arr [0]) : '';
				// If the line doesn't match the var=value pattern, or if it's a comment then add it without a key.
				if (isset($line_arr [1]) && !(substr($lhs, 0, 2) === '//' || substr($lhs, 0, 1) === ';')) {
					$this->_iniParsedArray [$cur_sec] [$line_arr [0]] = $line_arr [1];
				} else {
					$this->_iniParsedArray [] = $line_arr [0];
				}
			}
		}
		$cache [$local_key] = $this->_iniParsedArray;
		if ($apcu_on) {
			@apcu_store($apcu_key, $this->_iniParsedArray, 60);
		}
	}

	/**
	 * Returns the entire section
	 */
	function getSection($key) {
		return $this->_iniParsedArray [$key];
	}

	/**
	 * Returns a value from a section
	 */
	function getValue($section, $key) {
		if (!isset($this->_iniParsedArray [$section])) {
			return false;
		}
		return $this->_iniParsedArray [$section] [$key];
	}

	/**
	 * Returns the value of a section or the entire section
	 */
	function get($section, $key = NULL) {
		if (is_null($key)) {
			return $this->getSection($section);
		}
		return $this->getValue($section, $key);
	}

	/**
	 * Seta um valor de acordo com a chave especificada
	 */
	function setSection($section, $array) {
		if (!is_array($array)) {
			return false;
		}
		return $this->_iniParsedArray [$section] = $array;
	}

	/**
	 * Sets a new value in a section
	 */
	function setValue($section, $key, $value) {
		if ($this->_iniParsedArray [$section] [$key] = $value) {
			return true;
		}
	}

	/**
	 * Sets a new value in a section or an entire new section
	 */
	function set($section, $key, $value = NULL) {
		if (is_array($key) && is_null($value)) {
			return $this->setSection($section, $key);
		}
		return $this->setValue($section, $key, $value);
	}

	/**
	 * Saves the entire array to the INI file.
	 */
	function save($filename = null) {
		if ($filename == null) {
			$filename = $this->_iniFilename;
		}
		if (is_writeable($filename)) {
			$SFfdescriptor = fopen($filename, "w");
			foreach ($this->_iniParsedArray as $section => $array) {
				fwrite($SFfdescriptor, "[" . $section . "]\n");
				foreach ($array as $key => $value) {
					fwrite($SFfdescriptor, $key . ' = ' . $value . "\n");
				}
				fwrite($SFfdescriptor, "\n");
			}
			fclose($SFfdescriptor);
			return true;
		} else {
			return false;
		}
	}

}
?>
