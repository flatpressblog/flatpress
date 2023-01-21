<?php

/*
 * Author: Enrico Reinsdorf (enrico@.re-design.de)
 * Author URI: www.re-design.de
 * Changelog: *Fixed* PHP 7.4 Methods with the same name as their class will not be constructors in a future version of PHP
 * Change-Date: 12.10.2022
 */

// error_reporting(E_ALL);
// error_reporting(-1);
// ini_set('error_reporting', E_ALL);
class iniParser {

	var $_iniFilename = '';

	var $_iniParsedArray = array();

	/**
	 * erstellt einen mehrdimensionalen Array aus der INI-Datei
	 */
	function __construct($filename) {
		$this->_iniFilename = $filename;

		$file_content = file($this->_iniFilename);
		$this->_iniParsedArray = array();
		$cur_sec = false;
		foreach ($file_content as $line) {
			$line = trim($line);
			if (preg_match("/^\[.+\]$/", $line)) {
				$sec_name = str_replace(array(
					"[",
					"]"
				), "", $line);
				// If this section already exists, ignore the line.
				if (!isset($this->_iniParsedArray [$sec_name])) {
					$this->_iniParsedArray [$sec_name] = array();
					$cur_sec = $sec_name;
				}
			} else {
				$line_arr = explode('=', $line, 2);
				// If the line doesn't match the var=value pattern, or if it's a
				// comment then add it without a key.
				if (isset($line_arr [1]) && !(substr(trim($line_arr [0]), 0, 1) == '//' || substr(trim($line_arr [0]), 0, 1) == ';')) {
					$this->_iniParsedArray [$cur_sec] [$line_arr [0]] = $line_arr [1];
				} else {
					$this->_iniParsedArray [] = $line_arr [0];
				}
			}
		}
	}

	/**
	 * gibt die komplette Sektion zur�ck
	 */
	function getSection($key) {
		return $this->_iniParsedArray [$key];
	}

	/**
	 * gibt einen Wert aus einer Sektion zur�ck
	 */
	function getValue($section, $key) {
		if (!isset($this->_iniParsedArray [$section]))
			return false;
		return $this->_iniParsedArray [$section] [$key];
	}

	/**
	 * gibt den Wert einer Sektion oder die ganze Section zur�ck
	 */
	function get($section, $key = NULL) {
		if (is_null($key))
			return $this->getSection($section);
		return $this->getValue($section, $key);
	}

	/**
	 * Seta um valor de acordo com a chave especificada
	 */
	function setSection($section, $array) {
		if (!is_array($array))
			return false;
		return $this->_iniParsedArray [$section] = $array;
	}

	/**
	 * setzt einen neuen Wert in einer Section
	 */
	function setValue($section, $key, $value) {
		if ($this->_iniParsedArray [$section] [$key] = $value)
			return true;
	}

	/**
	 * setzt einen neuen Wert in einer Section oder eine gesamte, neue Section
	 */
	function set($section, $key, $value = NULL) {
		if (is_array($key) && is_null($value))
			return $this->setSection($section, $key);
		return $this->setValue($section, $key, $value);
	}

	/**
	 * sichert den gesamten Array in die INI-Datei
	 */
	function save($filename = null) {
		if ($filename == null)
			$filename = $this->_iniFilename;
		if (is_writeable($filename)) {
			$SFfdescriptor = fopen($filename, "w");
			foreach ($this->_iniParsedArray as $section => $array) {
				fwrite($SFfdescriptor, "[" . $section . "]\n");
				foreach ($array as $key => $value) {
					fwrite($SFfdescriptor, "$key = $value\n");
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
