<?php
/**
 * Project:     SmartyValidate: Form Validator for the Smarty Template Engine
 * File:        validate_criteria.isFileSize.php
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
 * Test if a file's size does not exceed the maximum allowed.
 *
 * @param string $value The value being tested (not used here).
 * @param bool $empty Whether the field can be empty.
 * @param array $params Validation parameters (expects 'max' like "2M" or 'field2').
 * @param array $formvars Form variables.
 * @return bool True if file size is within limit, false otherwise.
 */
function smarty_validate_criteria_isFileSize($value, $empty, &$params, &$formvars) {

	$_field = $params['field'];

	// Check max value from 'field2' or 'max'
	if (isset($params ['field2'])) {
		$_max = trim($params ['field2']);
	} elseif (isset($params ['max'])) {
		$_max = trim($params ['max']);
	} else {
		trigger_error("SmartyValidate: [isFileSize] 'max' attribute is missing.");
		return false;
	}

	// Check if file field is set
	if (!isset($_FILES [$_field])) {
		// nothing in the form
		return false;
	}

	if ($_FILES [$_field] ['error'] == 4) {
		// no file uploaded
		return $empty;
	}

	// Parse the max size value
	if (!preg_match('!^(\d+)([bkmg](b)?)?$!i', $_max, $_match)) {
		trigger_error("SmartyValidate: [isFileSize] 'max' attribute is invalid.");
		return false;
	}

	$_size = $_match[1];
	$_type = strtolower($_match[2]);

	// Calculate max size in bytes
	switch ($_type) {
		case 'k':
			$_maxsize = $_size * 1024;
			break;
		case 'm':
			$_maxsize = $_size * 1024 * 1024;
			break;
		case 'g':
			$_maxsize = $_size * 1024 * 1024 * 1024;
			break;
		case 'b':
		default:
			$_maxsize = $_size;
			break;
	}

	// Compare file size with max allowed
	return $_FILES [$_field] ['size'] <= $_maxsize;
}
?>
