<?php
/**
 * Smarty notempty modifier plugin
 *
 * Type: modifier
 * Name: notempty
 * Purpose: Return default value if input string is not empty, otherwise empty string.
 * @author Edoardo Vacchi (NoWhereMan)
 * @param string $string The input string to check
 * @param string $default The default string to return if not empty
 * @return string
 *
 */
function smarty_modifier_notempty($string, $default = '') {
	if ($string !== '') {
		return $default;
	}
	return '';
}
?>
