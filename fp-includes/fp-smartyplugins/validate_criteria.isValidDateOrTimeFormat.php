<?php
/**
 * Smarty Validate Criteria: isValidDateOrTimeFormat
 *
 * Checks whether a value is not empty and only contains the permitted characters.
 * Is required in the admin area -> Configuration -> International settings (timeformat, dateformat, dateformatshort)
 *
 * @param string $value The value to be checked
 * @return bool true, if the value is valid, otherwise false
 */
function smarty_validate_criteria_isValidDateOrTimeFormat($value) {
	// Check that the value is not empty
	if (!isset($value) || trim($value) === '') {
		return false;
	}
	// Check whether the value only contains permitted characters
	return preg_match('/^[a-zA-Z0-9:%.,\- ]+$/', $value);
}
?>
