<?php

/**
 * Project:     SmartyValidate: Form Validator for the Smarty Template Engine
 * File:        validate_criteria.isCCExpDate.php
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
 * test if a value is a valid credit card expiration date
 *
 * @param string $value the value being tested
 * @param boolean $empty if field can be empty
 * @param array params validate parameter values
 * @param array formvars form var values
 */
function smarty_validate_criteria_isCCExpDate($value, $empty, &$params, &$formvars) {
    if(strlen($value) == 0)
        return $empty;

    if(!preg_match('!^(\d+)\D+(\d+)$!', $value, $_match))
        return false;

    $_month = $_match[1];
    $_year = $_match[2];
    
    if(strlen($_year) == 2)
        $_year = substr(date('Y', time()),0,2) . $_year;

    $_month = (int) $_month;
    $_year = (int) $_year;
    
	if($_month < 1 || $_month > 12)
		return false;
	if(date('Y',time()) > $_year)
		return false;
    
	if(date('Y',time()) == $_year && date('m', time()) > $_month)
		return false;

	return true;

}

?>
