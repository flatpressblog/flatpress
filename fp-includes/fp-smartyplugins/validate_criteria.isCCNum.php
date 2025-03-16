<?php
/**
 * Project:     SmartyValidate: Form Validator for the Smarty Template Engine
 * File:        validate_criteria.isCCNum.php
 * Author:      Monte Ohrt <monte at newdigitalgroup dot com>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @link http://www.phpinsider.com/php/code/SmartyValidate/
 * @copyright 2001-2005 New Digital Group, Inc.
 * @author Monte Ohrt <monte at newdigitalgroup dot com>
 * @package SmartyValidate
 */

/**
 * Test if a value is a valid credit card checksum using the Luhn algorithm.
 *
 * @param string $value The value being tested.
 * @param bool $empty Whether the field can be empty and still pass validation.
 * @param array $params Validation parameter values (passed by reference).
 * @param array $formvars Form variable values (passed by reference).
 * @return bool True if valid, false otherwise.
 */
function smarty_validate_criteria_isCCNum($value, $empty, &$params, &$formvars) {
	if(strlen($value) == 0) {
		return $empty;
	}

	// strip everything but digits
	$value = preg_replace('!\D+!', '', $value);

	if (empty($value)) {
		return false;
	}

	$_c_digits = preg_split('//', $value, -1, PREG_SPLIT_NO_EMPTY);

	$_max_digit = count($_c_digits)-1;
	$_even_odd = $_max_digit % 2;

	$_sum = 0;
	for ($_count=0; $_count <= $_max_digit; $_count++) {
		$_digit = (int) $_c_digits [$_count];
		if ($_even_odd) {
			$_digit = $_digit * 2;
			if ($_digit > 9) {
				$_digit = (int) substr($_digit, 1, 1) + 1;
			}
		}
		$_even_odd = 1 - $_even_odd;
		$_sum += $_digit;
	}
	$_sum = $_sum % 10;
	if($_sum) {
		return false;
	}
	return true;

}

?>
