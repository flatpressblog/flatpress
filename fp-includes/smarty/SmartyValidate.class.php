<?php

/**
 * Project:     SmartyValidate: Form Validator for the Smarty Template Engine
 * File:        SmartyValidate.class.php
 * Author:      Monte Ohrt <monte at newdigitalgroup dot com>
 * Website:     http://www.phpinsider.com/php/code/SmartyValidate/
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
 * @version 2.8
 */

if(!defined('SMARTY_VALIDATE_DEFAULT_FORM'))
    define('SMARTY_VALIDATE_DEFAULT_FORM', 'default');

class SmartyValidate {

    /**
     * Class Constructor
     */
    function SmartyValidate() { }

    
    /**
     * initialize the validator
     *
     * @param obj    $smarty the smarty object
     * @param string $reset reset the default form?
     */
    function connect(&$smarty, $reset = false) {
        if(SmartyValidate::is_valid_smarty_object($smarty)) {
            SmartyValidate::_object_instance('Smarty', $smarty);
            SmartyValidate::register_form(SMARTY_VALIDATE_DEFAULT_FORM, $reset);
        } else {
            trigger_error("SmartyValidate: [connect] I need a valid Smarty object.");
            return false;
        }
    }   

    /**
     * test if object is a valid smarty object
     *
     * @param obj    $smarty_obj the smarty object
     */
    function is_valid_smarty_object(&$smarty_obj) {
        return (is_object($smarty_obj) && (strtolower(get_class($smarty_obj)) == 'smarty' || is_subclass_of($smarty_obj, 'smarty')));
        
    }
    
    /**
     * clear the entire SmartyValidate session
     *
     */
    function disconnect() {
        unset($_SESSION['SmartyValidate']);
        SmartyValidate::_object_instance('-', $_dummy);
    }    
            
    /**
     * initialize the session data
     *
     * @param string $form the name of the form being validated
     * @param string $reset reset an already registered form?
     */
    function register_form($form, $reset = false) {
        if(SmartyValidate::is_registered_form($form) && !$reset) {
            return false;
        } else {
            $_SESSION['SmartyValidate'][$form] = array();
            $_SESSION['SmartyValidate'][$form]['registered_funcs']['criteria'] = array();
            $_SESSION['SmartyValidate'][$form]['registered_funcs']['transform'] = array();
            $_SESSION['SmartyValidate'][$form]['validators'] = array();
            $_SESSION['SmartyValidate'][$form]['is_error'] = false;
            $_SESSION['SmartyValidate'][$form]['is_init'] = true;
            SmartyValidate::_smarty_assign();
            return true;
        }
    }
    
    /**
     * unregister a form from the session
     *
     * @param string $form the name of the form being validated
     */
    function unregister_form($form) {
        unset($_SESSION['SmartyValidate'][$form]);
    }    
    
    /**
     * test if the session data is initialized
     *
     * @param string $form the name of the form being validated
     */
    function is_registered_form($form = SMARTY_VALIDATE_DEFAULT_FORM) {    
        return isset($_SESSION['SmartyValidate'][$form]);
    }
    
    function _failed_fields(&$formvars, $form = SMARTY_VALIDATE_DEFAULT_FORM, $revalidate = false)
    {
        // keep track of failed fields
        static $_failed_fields = array();
        
        if(isset($_failed_fields[$form]) && !$revalidate) {
            // already validated the form
            return $_failed_fields[$form];
        }
        
        // failed fields for current pass
        $_ret = array(); 
        
        $_sess =& $_SESSION['SmartyValidate'][$form]['validators'];
        
        foreach($_sess as $_key => $_val) {
            
            if(isset($_SESSION['SmartyValidate'][$form]['page'])
                && $_sess[$_key]['page'] != $_SESSION['SmartyValidate'][$form]['page']) {
                // not on page, do not validate
                continue;
            }

            $_full_field = $_field = $_sess[$_key]['field'];
            $_field_key = null;
            $_empty = isset($_sess[$_key]['empty']) ? $_sess[$_key]['empty'] : false;
            $_message = isset($_sess[$_key]['message']) ? $_sess[$_key]['message'] : null;

            if(is_array($_ret) && in_array($_full_field, $_ret)) {
                // already failed, skip this test
                continue;   
            }
            
            // field is name-keyed array, pull it apart
            if(($_lpos = strpos($_field, '[')) !== false && ($_rpos = strpos($_field, ']')) !== false) {
                if (($_keylen = ($_rpos - $_lpos - 1)) > 0) {
                    $_field_key = substr($_field, $_lpos+1, $_keylen);
                }
                $_field = substr($_field, 0, $_lpos);                    
            }
            
            if(isset($_sess[$_key]['transform'])) {
                $_trans_names = preg_split('![\s,]+!', $_sess[$_key]['transform'], -1, PREG_SPLIT_NO_EMPTY);
                if($_sess[$_key]['trim']) {
                    // put trim on front of transform array
                    array_unshift($_trans_names, 'trim');
                }
                foreach($_trans_names as $_trans_name) {
                    if(substr($_trans_name,0,1) == '@') {
                        // transformation will apply to entire array
                        $_trans_on_array = true;   
                        $_trans_name = substr($_trans_name,1);   
                    } else {
                        // transformation will apply to each array element
                        $_trans_on_array = false;
                    }
                                        
                    if(strpos($_trans_name,':') !== false) {
                        // transform has parameters, put them in $formvars
                        $_trans_parts = explode(':', $_trans_name);
                        $_trans_name = array_shift($_trans_parts);
                        $_trans_index = 2;
                        foreach($_trans_parts as $_trans_param) {
                            $_trans_field = $_trans_name . $_trans_index;
                            $_sess[$_key][$_trans_field] = $_trans_param; 
                            $_trans_index++;
                        }
                    }
                    
                    if(is_array($formvars[$_field]) && !$_trans_on_array) {
                        if(isset($_field_key)) {
                            // only apply to given key
                            if(($_new_val = SmartyValidate::_execute_transform($_trans_name, $formvars[$_field][$_field_key], $_sess[$_key], $formvars, $form)) !== false)
                                $formvars[$_field][$_field_key] = $_new_val;
                            
                        } else {
                            // apply to all keys
                            for($_x = 0, $_y = count($formvars[$_field]); $_x < $_y; $_x++) {
                                if(($_new_val = SmartyValidate::_execute_transform($_trans_name, $formvars[$_field][$_x], $_sess[$_key], $formvars, $form)) !== false)
                                    $formvars[$_field][$_x] = $_new_val;
                            }
                        }
                    } else {
                         if(($_new_val = SmartyValidate::_execute_transform($_trans_name, $formvars[$_field], $_sess[$_key], $formvars, $form)) !== false)
                             $formvars[$_field] = $_new_val;
                    }
                }
            }

            if((!isset($formvars[$_field]) && (!isset($_FILES[$_field])))
                || (
                    ((is_array($formvars[$_field]) && count($_field) == 0) || (is_string($formvars[$_field]) && strlen($formvars[$_field]) == 0)) && $_empty
                   )
                ) {
                // field must exist, or else fails automatically
                $_sess[$_key]['valid'] = $_empty;
            } else {
                if(substr($_val['criteria'],0,1) == '@') {
                    // criteria will apply to entire array or given key
                    $_criteria_on_array = true;   
                    $_val['criteria'] = substr($_val['criteria'],1);
                } else {
                    // criteria will apply to each array element
                    $_criteria_on_array = false;
                }
                
                if(is_array($formvars[$_field]) && !$_criteria_on_array) {
                    if(isset($_field_key)) {
                        // only apply to given key
                        $_sess[$_key]['valid'] = SmartyValidate::_is_valid_criteria($_val['criteria'], $formvars[$_field][$_field_key], $_empty, $_sess[$_key], $formvars, $form);
                    } else {
                        // apply to all keys
                        for($_x = 0, $_y = count($formvars[$_field]); $_x < $_y; $_x++) {
                            if(! $_sess[$_key]['valid'] = SmartyValidate::_is_valid_criteria($_val['criteria'], $formvars[$_field][$_x], $_empty, $_sess[$_key], $formvars, $form)) {
                                // found invalid array element, exit for loop
                                break;
                            }   
                        }
                    }
                } else {
                    $_sess[$_key]['valid'] = SmartyValidate::_is_valid_criteria($_val['criteria'], $formvars[$_field], $_empty, $_sess[$_key], $formvars, $form);
                }
            }
            
            if(!$_sess[$_key]['valid']) {
                $_ret[] = $_full_field;
                if(isset($_sess[$_key]['halt']) && $_sess[$_key]['halt'])
                    break;
            }
        }
        
        $_failed_fields[$form] = $_ret;
        
        return $_ret;
    }    
            
    /**
     * validate the form
     *
     * @param string $formvars the array of submitted for variables
     * @param string $form the name of the form being validated
     */
    function is_valid(&$formvars, $form = SMARTY_VALIDATE_DEFAULT_FORM) {
        
        static $_is_valid = array();
        
        if(isset($_is_valid[$form])) {
            // already validated the form
            return $_is_valid[$form];
        }
        
        $_smarty_obj =& SmartyValidate::_object_instance('Smarty', $_dummy);
        if(!SmartyValidate::is_valid_smarty_object($_smarty_obj)) {
            trigger_error("SmartyValidate: [is_valid] No valid smarty object, call connect() first.");
            return false;            
        }        

        if(!SmartyValidate::is_registered_form($form)) {
            trigger_error("SmartyValidate: [is_valid] form '$form' is not registered.");
            return false;
        } elseif ($_SESSION['SmartyValidate'][$form]['is_init']) {
            // first run, skip validation
            return false;
        } elseif (count($_SESSION['SmartyValidate'][$form]['validators']) == 0) {
            // nothing to validate
            return true;   
        }
        
        // check for failed fields
        $_failed_fields = SmartyValidate::_failed_fields($formvars, $form);
        $_ret = is_array($_failed_fields) && count($_failed_fields) == 0;
                               
        // set validation state of form
        $_SESSION['SmartyValidate'][$form]['is_error'] = !$_ret;
        
        $_is_valid[$form] = $_ret;
        
        return $_ret;
    }
    
    /**
     * register a callable function for form verification
     *
     * @param string $func_name the function being registered
     */
    function register_object($object_name, &$object) {
        if(!is_object($object)) {
            trigger_error("SmartyValidate: [register_object] not a valid object.");
            return false;
        }
        SmartyValidate::_object_instance($object_name, $object);
    }    
    
    /**
     * register a callable function for form verification
     *
     * @param string $func_name the function being registered
     */
    function is_registered_object($object_name) {
        $_object =& SmartyValidate::_object_instance($object_name, $_dummy);
        return is_object($_object);
    }    

    /**
     * register a callable function for form verification
     *
     * @param string $func_name the function being registered
     */
    function register_criteria($name, $func_name, $form = SMARTY_VALIDATE_DEFAULT_FORM) {
        return SmartyValidate::_register_function('criteria', $name, $func_name, $form);
    }    
            
    /**
     * register a callable function for form verification
     *
     * @param string $func_name the function being registered
     */
    function register_transform($name, $func_name, $form = SMARTY_VALIDATE_DEFAULT_FORM) {
        return SmartyValidate::_register_function('transform', $name, $func_name, $form);
    }    
        
    /**
     * test if a criteria function is registered
     *
     * @param string $var the value being booleanized
     */
    function is_registered_criteria($name, $form = SMARTY_VALIDATE_DEFAULT_FORM) {  
        if(!SmartyValidate::is_registered_form($form)) {
            trigger_error("SmartyValidate: [is_registered_criteria] form '$form' is not registered.");
            return false;
        }
        return isset($_SESSION['SmartyValidate'][$form]['registered_funcs']['criteria'][$name]);
    }
    
    /**
     * test if a tranform function is registered
     *
     * @param string $var the value being booleanized
     */
    function is_registered_transform($name, $form = SMARTY_VALIDATE_DEFAULT_FORM) {
        if(!SmartyValidate::is_registered_form($form)) {
            trigger_error("SmartyValidate: [is_registered_transform] form '$form' is not registered.");
            return false;
        }
        return isset($_SESSION['SmartyValidate'][$form]['registered_funcs']['transform'][$name]);
    }    

    /**
     * register a validator
     *
     * @param string $id the id of the validator
     * @param string $field the field to be validated
     * @param string $criteria the name of the criteria function
     * @param string $empty allow field to be empty (optional)
     * @param string $halt stop validation if this one fails (optional)
     * @param string $transform transform function(s) to apply (optional)
     * @param string $form name of the form (optional)
     */
    function register_validator($id, $field, $criteria, $empty = false, $halt = false, $transform = null, $form = SMARTY_VALIDATE_DEFAULT_FORM) {
        if(!SmartyValidate::is_registered_form($form)) {
            trigger_error("SmartyValidate: [register_validator] form '$form' is not registered.");
            return false;
        }
        SmartyValidate::unregister_validator($id,$form);
        
        $_field = explode(':', $field);
        $_validator = array();
                
        foreach($_field as $_key => $_val) {
            if($_key == 0)
                $_validator['field'] = $_val;
            else {
                $_field_name = 'field';
                $_field_name .= $_key + 1;
                $_validator[$_field_name] = $_val;   
            }
        }
        
        $_validator['id'] = $id;
        $_validator['criteria'] = $criteria;
        $_validator['message'] = '';
        $_validator['trim'] = false;
        $_validator['empty'] = $empty;
        $_validator['halt'] = $halt;
        $_validator['transform'] = $transform;
               
        $_SESSION['SmartyValidate'][$form]['validators'][]  = $_validator;
    }    

    /**
     * register a validator
     *
     * @param string $id the id of the validator
     * @param string $transform the name of the transform function(s)
     * @param string $form name of the form (optional)
     */
    function set_transform($id, $transform, $form = SMARTY_VALIDATE_DEFAULT_FORM) {
                
        if(($_validator_key = SmartyValidate::is_registered_validator($id,$form)) === false) {
            trigger_error("SmartyValidate: [set_transform] validator '$id' is not registered.");
            return false;               
        }
                
        $_SESSION['SmartyValidate'][$form]['validators'][$_validator_key]['transform'] = $transform;
    }    
    
    
    /**
     * test if a validator is registered
     *
     * @param string $id the validator to test
     */
    function is_registered_validator($id, $form = SMARTY_VALIDATE_DEFAULT_FORM) {  
        if(!SmartyValidate::is_registered_form($form)) {
            trigger_error("SmartyValidate: [is_registered_validator] form '$form' is not registered.");
            return false;
        }
        
        foreach($_SESSION['SmartyValidate'][$form]['validators'] as $_key => $_val) {
            if($_SESSION['SmartyValidate'][$form]['validators'][$_key]['id'] == $id) {
                // return array index of validator
                return $_key;
            }
        }
        return false;
    }

    /**
     * unregister a validator
     *
     * @param string $id the validator to unregister
     */
    function unregister_validator($id, $form = SMARTY_VALIDATE_DEFAULT_FORM) {  
        if(!SmartyValidate::is_registered_form($form)) {
            return false;
        }
        
        foreach($_SESSION['SmartyValidate'][$form]['validators'] as $_key => $_val) {
            if(isset($_SESSION['SmartyValidate'][$form]['validators'][$_key]['id'])
                    && $_SESSION['SmartyValidate'][$form]['validators'][$_key]['id'] == $id) {
               unset($_SESSION['SmartyValidate'][$form]['validators'][$_key]);
               break;
            }
        }

    }        

    /**
     * set the current page of the form
     *
     * @param string $page the name of the page being validated
     * @param string $form the name of the form being validated
     */
    function set_page($page, $form = SMARTY_VALIDATE_DEFAULT_FORM) {
        $_SESSION['SmartyValidate'][$form]['page'] = $page;
        $_SESSION['SmartyValidate'][$form]['is_error'] = false;
        $_SESSION['SmartyValidate'][$form]['is_init'] = true;
    }    
            
    /**
     * return actual function name of registered func
     *
     * @param string $type the type of func
     * @param string $name the registered name
     * @param string $form the form name
     */
    function _execute_transform($name, $value, $params, &$formvars, $form) {
        
        if(SmartyValidate::is_registered_transform($name, $form)) {
            $_func_name = SmartyValidate::_get_registered_func_name('transform', $name, $form);
        } else {
            $_func_name = 'smarty_validate_transform_' . $name;
            if(!function_exists($_func_name)) {            
                $_smarty_obj =& SmartyValidate::_object_instance('Smarty', $_dummy);
                if($_plugin_file = $_smarty_obj->_get_plugin_filepath('validate_transform', $name)) {
                    include_once($_plugin_file);
                } else {
                    trigger_error("SmartyValidate: [is_valid] transform function '$name' was not found.");
                    return false;                    
                }
            }
        }
        if(strpos($_func_name,'->') !== false) {
            // object method
            preg_match('!(\w+)->(\w+)!', $_func_name, $_match);
            $_object_name = $_match[1];
            $_method_name = $_match[2];
            $_object =& SmartyValidate::_object_instance($_object_name, $_dummy);
            if(!method_exists($_object, $_method_name)) {
                trigger_error("SmartyValidate: [is_valid] method '$_method_name' is not valid for object '$_object_name'.");
                return false;                
            }
            return $_object->$_method_name($value, $params, $formvars);
        } else {
            return $_func_name($value, $params, $formvars);   
        }        
    }    
    
    /**
     * register a callable function for form verification
     *
     * @param string $func_name the function being registered
     */
    function _register_function($type, $name, $func_name, $form = SMARTY_VALIDATE_DEFAULT_FORM) {
        if(!SmartyValidate::is_registered_form($form)) {
            trigger_error("SmartyValidate: [register_$type] form '$form' is not registered.");
            return false;
        }
        if(strpos($func_name,'->') !== false) {
            // object method
            preg_match('!(\w+)->(\w+)!', $func_name, $_match);
            $_object_name = $_match[1];
            $_method_name = $_match[2];
            $_object =& SmartyValidate::_object_instance($_object_name, $_dummy);
            if(!method_exists($_object, $_method_name)) {
                trigger_error("SmartyValidate: [register_$type] method '$_method_name' is not valid for object '$_object_name'.");
                return false;                
            }
        } elseif (strpos($func_name,'::') !== false) {
            // static method
            preg_match('!(\w+)::(\w+)!', $func_name, $_match);
            if(!is_callable(array($_match[1], $_match[2]))) {
                trigger_error("SmartyValidate: [register_$type] static method '$func_name' does not exist.");
                return false;                
            }            
        } elseif(!function_exists($func_name)) {
            trigger_error("SmartyValidate: [register_$type] function '$func_name' does not exist.");
            return false;
        }
        $_SESSION['SmartyValidate'][$form]['registered_funcs'][$type][$name] = $func_name;
        return true;
    }    

    /**
     * return actual function name of registered func
     *
     * @param string $type the type of func
     * @param string $name the registered name
     * @param string $form the form name
     */
    function _get_registered_func_name($type,$name,$form) {
        return isset($_SESSION['SmartyValidate'][$form]['registered_funcs'][$type][$name])
           ? $_SESSION['SmartyValidate'][$form]['registered_funcs'][$type][$name]
           : false;
    }
    
            
    /**
     * booleanize a value
     *
     * @param string $var the value being booleanized
     */
    function _booleanize($var) {
        if(in_array(strtolower($var), array(true, 1, 'true','on','yes','y'),true)) {
            return true;
        }
        return false;
    }
    
    /**
     * validate criteria for given value
     *
     * @param string $criteria the criteria to test against
     * @param string $value the value being tested
     * @param string $empty skip empty values or not
     */
    function _is_valid_criteria($criteria, $value, $empty, &$params, &$formvars, $form) {
        if(SmartyValidate::is_registered_criteria($criteria,$form)) {
            $_func_name = SmartyValidate::_get_registered_func_name('criteria',$criteria, $form);
        } else {
            $_func_name = 'smarty_validate_criteria_' . $criteria;
            if(!function_exists($_func_name)) {            
                $_smarty_obj =& SmartyValidate::_object_instance('Smarty', $_dummy);
                if($_plugin_file = $_smarty_obj->_get_plugin_filepath('validate_criteria', $criteria)) {
                    include_once($_plugin_file);
                } else {
                    trigger_error("SmartyValidate: [is_valid] criteria function '$criteria' was not found.");
                    return false;                    
                }
            }
        }
        if(strpos($_func_name,'->') !== false) {
            // object method
            preg_match('!(\w+)->(\w+)!', $_func_name, $_match);
            $_object_name = $_match[1];
            $_method_name = $_match[2];            
            $_object =& SmartyValidate::_object_instance($_object_name, $_dummy);
            if(!method_exists($_object, $_method_name)) {
                trigger_error("SmartyValidate: [is_valid] method '$_method_name' is not valid for object '$_object_name'.");
                return false;                
            }
            return $_object->$_method_name($value, $empty, $params, $formvars);
        } else {
            return $_func_name($value, $empty, $params, $formvars);   
        }
    }
    
    /**
     * get or set an object instance
     *
     * @param string $name the object name
     * @param object $object the object being set
     */
    function &_object_instance($name, &$object) {
        $return = false;
        static $_objects = array();
        if ($name=='-') {
            unset ($_objects);
            static $_objects = array();
        }
        if(!is_object($object)) {
            if (isset($_objects[$name]))
                return $_objects[$name];
            else
                return $return;
        } else {
            $_objects[$name] =& $object;
            return $object;
        }
    }    
    
    /**
     * get or set the smarty object instance
     *
     * @param string $value the value being tested
     */
    function _smarty_assign($vars = array()) {
        
        $_smarty_obj =& SmartyValidate::_object_instance('Smarty', $_dummy);
        
        if(!is_object($_smarty_obj)) {
            trigger_error("SmartyValidate: [assign] no valid smarty object found, call connect() first.");
            return false;
        }              
                        
        if(!empty($vars)) {
            $_smarty_obj->assign($vars);
        }
        foreach($_SESSION['SmartyValidate'] as $_key => $_val) {
            $_info[$_key]['is_error'] = isset($_SESSION['SmartyValidate'][$_key]['is_error']) ? $_SESSION['SmartyValidate'][$_key]['is_error'] : null;        
        }
        $_smarty_obj->assign('validate', $_info);
        
    }    
        
}

?>
