<?php

/**
 * Project:     FlatPress
 * File:        validate_criteria.isValidPassword.php
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
 * @link http://flatpress.sf.net
 * @copyright 2006 FlatPress team
 * @author NoWhereMan <real_nowhereman at users dot sf dot net>
 * @package SmartyValidate
 * @version 2.6
 */

/**
 * test if a value is a valid range
 *
 * @param string $value the value being tested
 * @param boolean $empty if field can be empty
 * @param array params validate parameter values
 * @param array formvars form var values
 */
function smarty_validate_criteria_isValidPassword($value, $empty, &$params, &$formvars) {
        if(!isset($params['field2'])) {
                trigger_error("SmartyValidate: [isValidPassword] parameter 'field2' is missing.");        
                return false;
        }
        if(strlen($value) == 0)
            return $empty;
	
	return user_login($formvars[$params['field']], $formvars[$params['field2']]);
	
}

?>
