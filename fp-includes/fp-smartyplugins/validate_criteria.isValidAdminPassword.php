<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage PluginsValidator
 */

/**
 * Smarty validation function for admin password
 *
 * @param string|null $value The value to validate
 * @param array $params Additional parameters
 * @return bool True if the value is valid, false otherwise
 */
function smarty_validate_criteria_isValidAdminPassword($value, $params) {
	// Ensure value is a string
	if (!is_string($value)) {
		return false;
	}

	// Passwords are opaque input: never HTML-encode or trim before hashing.
	return strlen($value) >= 6 && strpos($value, "\0") === false && preg_match('/\s/', $value) === 0;
}
?>
