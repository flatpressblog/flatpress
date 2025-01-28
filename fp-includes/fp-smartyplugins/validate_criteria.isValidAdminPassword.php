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
	global $fp_config;
	$localeCharset = strtoupper($fp_config ['locale'] ['charset'] ?? 'UTF-8');

	// Ensure value is a string
	if (!is_string($value)) {
		return false;
	}

	// Sanitize the input to prevent XSS vulnerabilities
	$value = htmlspecialchars($value, ENT_QUOTES, $localeCharset);

	// Password must be at least 6 characters long and not contain spaces
	return strlen($value) >= 6 && strpos($value, ' ') === false;
}
?>
