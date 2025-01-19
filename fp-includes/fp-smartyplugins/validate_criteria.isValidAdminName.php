<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage PluginsValidator
 */

/**
 * Smarty validation function for admin username
 *
 * @param string|null $value The value to validate
 * @param array $params Additional parameters
 * @return bool True if the value is valid, false otherwise
 */
function smarty_validate_criteria_isValidAdminName($value, $params) {
	// Ensure value is a string
	if (!is_string($value)) {
		return false;
	}

	// Sanitize the input to prevent XSS vulnerabilities
	$value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

	// Admin name must only contain alphanumeric characters and underscores
	return preg_match('/^[a-zA-Z0-9_]+$/', $value) === 1;
}
?>
