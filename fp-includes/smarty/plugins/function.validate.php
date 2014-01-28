<?php

/**
 * Project:     SmartyValidate: Form Validator for the Smarty Template Engine
 * File:        function.validate.php
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

function smarty_function_validate($params, &$smarty) {

    $_init_params = $smarty->get_template_vars('validate_init');

    if(isset($_init_params)) {
        $params = array_merge($_init_params, $params);
    }
    
    static $_halt = array();
    static $_is_init = null;

    $_form = isset($params['form']) ? $params['form'] : 'default';
    $_sess =& $_SESSION['SmartyValidate'][$_form];

    if(!isset($_is_init))
        $_is_init = $_sess['is_init'];

    if(!SmartyValidate::is_registered_form($_form)) {
        trigger_error("SmartyValidate: [validate plugin] form '$_form' is not registered.");
        return false;
    }    
    
    if(isset($_halt[$_form]) && $_halt[$_form])
        return;    
    
    if (!class_exists('SmartyValidate')) {
        $smarty->trigger_error("validate: missing SmartyValidate class");
        return;
    }
    if (!isset($_SESSION['SmartyValidate'])) {
        $smarty->trigger_error("validate: SmartyValidate is not initialized, use connect() first");
        return;        
    }
    
    if(isset($params['id'])) {
        if (($_validator_key = SmartyValidate::is_registered_validator($params['id'], $_form)) === false) {
            $smarty->trigger_error("validate: validator id '" . $params['id'] . "' is not registered.");
            return;         
        }
    } else {
        if (strlen($params['field']) == 0) {
            $smarty->trigger_error("validate: missing 'field' parameter");
            return;
        }
        if (strlen($params['criteria']) == 0) {
            $smarty->trigger_error("validate: missing 'criteria' parameter");
            return;
        }
    }
    if(isset($params['trim'])) {
        $params['trim'] = SmartyValidate::_booleanize($params['trim']);   
    }
    if(isset($params['empty'])) {
        $params['empty'] = SmartyValidate::_booleanize($params['empty']);
    }
    if(isset($params['halt'])) {
        $params['halt'] = SmartyValidate::_booleanize($params['halt']);
    }
                
    if(isset($_sess['validators']) && is_array($_sess['validators'])) {
        if(isset($params['id'])) {
            if($_is_init) {
                $_sess['validators'][$_validator_key]['message'] = $params['message'];
            }
        } else {
            foreach($_sess['validators'] as $_key => $_field) {
                if($_field['field'] == $params['field']
                    && $_field['criteria'] == $params['criteria']) { 
                    // field exists
                    $_validator_key = $_key;
                    break;
                }
            }
        }
        
        if(!$_is_init) {

            if(!$_sess['is_error']) // no validation error
                return;
        
            if(!isset($_sess['validators'][$_validator_key]['valid']) || !$_sess['validators'][$_validator_key]['valid']) {
                // not valid, show error and reset
                $_halt[$_form] = isset($_sess['validators'][$_validator_key]['halt'])
                        ? $_sess['validators'][$_validator_key]['halt']
                        : false;
                $_echo = true;
                if(isset($params['assign'])) {
                    $smarty->assign($params['assign'], $_sess['validators'][$_validator_key]['message']);                   
                } elseif (isset($params['append'])) {
                	//$smarty->append($params['append'], $_sess['validators'][$_validator_key]['message']);  
          		$smarty->append($params['append'], 
          			array(
          				$_sess['validators'][$_validator_key]['field']
          					=>	$_sess['validators'][$_validator_key]['message']
          			),
          			true);                                        
                } else {
                    // no assign or append, so echo message
                    echo $_sess['validators'][$_validator_key]['message'];
                }
            }
        } else {
            if(isset($params['id'])) {
                $_sess['validators'][$_validator_key] = 
                    array_merge($_sess['validators'][$_validator_key], $params);
            } else {
                $_params = $params;
                $_params['valid'] = false;
                $_sess['validators'][] = $_params;
            }
        }
    }
    
    $_sess['is_init'] = false;
}

?>
