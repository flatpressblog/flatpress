<?php
/**
 * Smarty notempty modifier plugin
 *
 * Type: modifier
 * Name: notempty
 * Purpose:  print a message if the input variable is not empty or null
 * @author Edoardo Vacchi (NoWhereMan)
 * @param string $string The input string to check
 * @param string $default The default string to return if not empty
 * @return string
 *
 */
function smarty_modifier_notempty($string, $default = '') {
	if (isset($string) && $string !== '') {
		return $default;
	}
	return '';
}
?>
