<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsFunction
 */
/**
 * Smarty {html_options} function plugin
 *
 * Type:     function
 * Name:     html_options
 * Purpose:  Prints the list of <option> tags generated from the passed parameters.
 *
 * Parameters:
 * - name       (optional)  - name of the select element (default: "select")
 * - values     (required if "options" not set) - array of values
 * - options    (required if "values" not set) - associative array of options
 * - selected   (optional)  - value or array of selected option(s)
 * - output     (required if "options" not set) - array of displayed text values
 * - id         (optional)  - HTML ID attribute for <select>
 * - class      (optional)  - HTML class attribute for <select>
 *
 * @link   https://www.smarty.net/manual/en/language.function.html.options.php {html_options}
 * @author Monte Ohrt <monte at ohrt dot com>
 * @author Ralf Strehle (minor optimization) <ralf dot strehle at yahoo dot de>
 * @param array $params Parameters passed to the function
 * @param \Smarty_Internal_Template $template Smarty template object
 * @return string Rendered HTML <select> element
 * @uses smarty_function_escape_special_chars()
 * @throws \SmartyException
 */
function smarty_function_html_options($params, Smarty_Internal_Template $template)
{
    $template->_checkPlugins(
        array(
            array(
                'function' => 'smarty_function_escape_special_chars',
                'file'     => SMARTY_PLUGINS_DIR . 'shared.escape_special_chars.php'
            )
        )
    );
    $name = null;
    $values = null;
    $options = null;
    $selected = null;
    $output = null;
    $id = null;
    $class = null;
    $extra = '';

    foreach ($params as $_key => $_val) {
        switch ($_key) {
            case 'name':
            case 'class':
            case 'id':
                $$_key = (string)$_val;
                break;
            case 'options':
                $options = (array)$_val;
                break;
            case 'values':
            case 'output':
                $$_key = array_values((array)$_val);
                break;
            case 'selected':
                if (is_array($_val)) {
                    $selected = array();
                    foreach ($_val as $_sel) {
                        if (is_object($_sel)) {
                            if (method_exists($_sel, '__toString')) {
                                $_sel = smarty_function_escape_special_chars((string)$_sel->__toString());
                            } else {
                                trigger_error(
                                    'html_options: selected attribute contains an object of class \'' .
                                    get_class($_sel) . '\' without __toString() method',
                                    E_USER_NOTICE
                                );
                                continue;
                            }
                        } else {
                            $_sel = smarty_function_escape_special_chars((string)$_sel);
                        }
                        $selected[ $_sel ] = true;
                    }
                } elseif (is_object($_val)) {
                    if (method_exists($_val, '__toString')) {
                        $selected = smarty_function_escape_special_chars((string)$_val->__toString());
                    } else {
                        trigger_error(
                            'html_options: selected attribute is an object of class \'' . get_class($_val) .
                            '\' without __toString() method',
                            E_USER_NOTICE
                        );
                    }
                } else {
                    $selected = smarty_function_escape_special_chars((string)$_val);
                }
                break;
            case 'strict':
                break;
            case 'disabled':
            case 'readonly':
                if (!empty($params[ 'strict' ])) {
                    if (!is_scalar($_val)) {
                        trigger_error(
                            "html_options: {$_key} attribute must be a scalar, only boolean true or string '{$_key}' will actually add the attribute",
                            E_USER_NOTICE
                        );
                    }
                    if ($_val === true || $_val === $_key) {
                        $extra .= ' ' . $_key . '="' . smarty_function_escape_special_chars($_key) . '"';
                    }
                    break;
                }
            // omit break; to fall through!
            // no break
            default:
                if (!is_array($_val)) {
                    $extra .= ' ' . $_key . '="' . smarty_function_escape_special_chars($_val) . '"';
                } else {
                    trigger_error("html_options: extra attribute '{$_key}' cannot be an array", E_USER_NOTICE);
                }
                break;
        }
    }

    if (empty($options) && $values === null) {
        /* raise error here? */
        return '';
    }

    $_html_result = '';
    $_idx = 0;
    if ($options !== null) {
        foreach ($options as $_key => $_val) {
            $_html_result .= smarty_function_html_options_optoutput($_key, $_val, $selected, $id, $class, $_idx);
        }
    } else {
        foreach ($values as $_i => $_key) {
            $_val = isset($output[ $_i ]) ? $output[ $_i ] : '';
            $_html_result .= smarty_function_html_options_optoutput($_key, $_val, $selected, $id, $class, $_idx);
        }
    }

    if ($name !== null && $name !== '') {
        $_html_class = ($class !== null && $class !== '') ? ' class="' . $class . '"' : '';
        $_html_id = ($id !== null && $id !== '') ? ' id="' . $id . '"' : '';
        $_html_result =
            '<select name="' . $name . '"' . $_html_class . $_html_id . $extra . '>' . "\n" . $_html_result .
            '</select>' . "\n";
    }

    return $_html_result;
}

/**
 * @param $key
 * @param $value
 * @param $selected
 * @param $id
 * @param $class
 * @param $idx
 *
 * @return string
 */
function smarty_function_html_options_optoutput($key, $value, $selected, $id, $class, &$idx)
{
    if (!is_array($value)) {
        $_key = smarty_function_escape_special_chars($key);
        $_html_result = '<option value="' . $_key . '"';
        if (is_array($selected)) {
            if (isset($selected[ $_key ])) {
                $_html_result .= ' selected="selected"';
            }
        } elseif ($_key === $selected) {
            $_html_result .= ' selected="selected"';
        }
        $_html_class = !empty($class) ? ' class="' . $class . ' option"' : '';
        $_html_id = !empty($id) ? ' id="' . $id . '-' . $idx . '"' : '';
        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                $value = smarty_function_escape_special_chars((string)$value->__toString());
            } else {
                trigger_error(
                    'html_options: value is an object of class \'' . get_class($value) .
                    '\' without __toString() method',
                    E_USER_NOTICE
                );
                return '';
            }
        } else {
            $value = smarty_function_escape_special_chars((string)$value);
        }
        $_html_result .= $_html_class . $_html_id . '>' . $value . '</option>' . "\n";
        $idx++;
    } else {
        $_idx = 0;
        $_html_result =
            smarty_function_html_options_optgroup(
                $key,
                $value,
                $selected,
                !empty($id) ? ($id . '-' . $idx) : null,
                $class,
                $_idx
            );
        $idx++;
    }
    return $_html_result;
}

/**
 * @param $key
 * @param $values
 * @param $selected
 * @param $id
 * @param $class
 * @param $idx
 *
 * @return string
 */
function smarty_function_html_options_optgroup($key, $values, $selected, $id, $class, &$idx)
{
    $optgroup_html = '<optgroup label="' . smarty_function_escape_special_chars($key) . '">' . "\n";
    foreach ($values as $key => $value) {
        $optgroup_html .= smarty_function_html_options_optoutput($key, $value, $selected, $id, $class, $idx);
    }
    $optgroup_html .= "</optgroup>\n";
    return $optgroup_html;
}
