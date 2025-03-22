<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsFunction
 */
/**
 * Smarty {html_checkboxes} function plugin
 *
 * Generates a list of HTML checkbox inputs, optionally wrapped in <label> tags.
 *
 * Example usage:
 *  {html_checkboxes values=$ids output=$names}
 *  {html_checkboxes values=$ids name='box' separator='<br>' output=$names}
 *  {html_checkboxes values=$ids checked=$checked separator='<br>' output=$names}
 *
 * Accepted parameters:
 * - name        (string)   Name attribute of each checkbox (default: "checkbox")
 * - values      (array)    Indexed array of checkbox values (alternative to "options")
 * - output      (array)    Indexed array of labels for each value (used with "values")
 * - options     (array)    Associative array of value => label (alternative to "values"/"output")
 * - checked     (mixed)    Pre-selected value(s) (alias: "selected")
 * - separator   (string)   String to separate each checkbox (e.g., '<br>' or '&nbsp;')
 * - assign      (string)   Template variable to assign the result to instead of outputting it
 * - escape      (bool)     Whether to escape labels and values (default: true)
 * - labels      (bool)     Whether to wrap each input in a <label> (default: true)
 * - label_ids   (bool)     Whether to assign unique IDs to inputs and labels (default: false)
 * - disabled    (bool)     Whether to add a "disabled" attribute to each input
 * - readonly    (bool)     Whether to add a "readonly" attribute to each input
 * - strict      (bool)     Enforce strict validation on certain attributes (optional)
 * - ...         (mixed)    Any other attributes will be added to the input elements
 *
 * @link     https://www.smarty.net/manual/en/language.function.html.checkboxes.php
 * @author   Christopher Kvarme <christopher.kvarme@flashjab.com>
 * @author   Monte Ohrt <monte at ohrt dot com>
 * @version  1.1
 *
 * @param array<string, mixed>        $params   Parameters passed from the template
 * @param Smarty_Internal_Template    $template The Smarty template object
 *
 * @return string                     Rendered HTML output or an empty string if options/values are missing
 * @throws \SmartyException
 * @uses smarty_function_escape_special_chars()
 */
function smarty_function_html_checkboxes($params, Smarty_Internal_Template $template)
{
    $template->_checkPlugins(
        array(
            array(
                'function' => 'smarty_function_escape_special_chars',
                'file'     => SMARTY_PLUGINS_DIR . 'shared.escape_special_chars.php'
            )
        )
    );
    $name = 'checkbox';
    $values = null;
    $options = null;
    $selected = array();
    $separator = '';
    $escape = true;
    $labels = true;
    $label_ids = false;
    $output = null;
    $extra = '';
    foreach ($params as $_key => $_val) {
        switch ($_key) {
            case 'name':
            case 'separator':
                $$_key = (string)$_val;
                break;
            case 'escape':
            case 'labels':
            case 'label_ids':
                $$_key = (bool)$_val;
                break;
            case 'options':
                $$_key = (array)$_val;
                break;
            case 'values':
            case 'output':
                $$_key = array_values((array)$_val);
                break;
            case 'checked':
            case 'selected':
                if (is_array($_val)) {
                    $selected = array();
                    foreach ($_val as $_sel) {
                        if (is_object($_sel)) {
                            if (method_exists($_sel, '__toString')) {
                                $_sel = smarty_function_escape_special_chars((string)$_sel->__toString());
                            } else {
                                trigger_error(
                                    'html_checkboxes: selected attribute contains an object of class \'' .
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
                            'html_checkboxes: selected attribute is an object of class \'' . get_class($_val) .
                            '\' without __toString() method',
                            E_USER_NOTICE
                        );
                    }
                } else {
                    $selected = smarty_function_escape_special_chars((string)$_val);
                }
                break;
            case 'checkboxes':
                trigger_error(
                    'html_checkboxes: the use of the "checkboxes" attribute is deprecated, use "options" instead',
                    E_USER_WARNING
                );
                $options = (array)$_val;
                break;
            case 'assign':
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
                    trigger_error("html_checkboxes: extra attribute '{$_key}' cannot be an array", E_USER_NOTICE);
                }
                break;
        }
    }
    if ((!isset($params['options']) || empty($params['options'])) && (!isset($params['values']) || empty($params['values']))) {
        return '';
    } /* raise error here? */
    $_html_result = array();
    if (!empty($options)) {
        foreach ((array) $values as $_i => $_key) {
            $_html_result[] =
                smarty_function_html_checkboxes_output(
                    $name,
                    $_key,
                    $_val,
                    $selected,
                    $extra,
                    $separator,
                    $labels,
                    $label_ids,
                    $escape
                );
        }
    } else {
        $values = (array) $values;
        foreach ($values as $_i => $_key) {
            $_val = isset($output[ $_i ]) ? $output[ $_i ] : '';
            $_html_result[] =
                smarty_function_html_checkboxes_output(
                    $name,
                    $_key,
                    $_val,
                    $selected,
                    $extra,
                    $separator,
                    $labels,
                    $label_ids,
                    $escape
                );
        }
    }
    if (!empty($params[ 'assign' ])) {
        $template->assign($params[ 'assign' ], $_html_result);
        return '';
    } else {
        return implode("\n", $_html_result);
    }
}

/**
 * Generates the HTML markup for a single checkbox input.
 *
 * This helper is used internally by {@see smarty_function_html_checkboxes()}.
 * It supports optional wrapping labels, automatic ID generation, and value escaping.
 *
 * @param string       $name        Name attribute for the checkbox input
 * @param string|int   $value       Value attribute for the checkbox input
 * @param string|int   $output      Label to be shown next to the checkbox
 * @param array|string $selected    Selected values (array for multiple selections or string for single)
 * @param string       $extra       Extra HTML attributes (e.g. disabled, class, style)
 * @param string       $separator   HTML used to separate checkboxes (e.g. '<br>')
 * @param bool         $labels      Whether to wrap the input in a <label> element
 * @param bool         $label_ids   Whether to generate unique ID attributes and link <label for="">
 * @param bool         $escape      Whether to escape label/output and value content
 *
 * @return string Rendered HTML string for a single checkbox with label and optional attributes
 */
function smarty_function_html_checkboxes_output(
    $name,
    $value,
    $output,
    $selected,
    $extra,
    $separator,
    $labels,
    $label_ids,
    $escape = true
) {
    $_output = '';
    if (is_object($value)) {
        if (method_exists($value, '__toString')) {
            $value = (string)$value->__toString();
        } else {
            trigger_error(
                'html_options: value is an object of class \'' . get_class($value) .
                '\' without __toString() method',
                E_USER_NOTICE
            );
            return '';
        }
    } else {
        $value = (string)$value;
    }
    if (is_object($output)) {
        if (method_exists($output, '__toString')) {
            $output = (string)$output->__toString();
        } else {
            trigger_error(
                'html_options: output is an object of class \'' . get_class($output) .
                '\' without __toString() method',
                E_USER_NOTICE
            );
            return '';
        }
    } else {
        $output = (string)$output;
    }
    if ($labels) {
        if ($label_ids) {
            $_id = smarty_function_escape_special_chars(
                preg_replace(
                    '![^\w\-\.]!' . Smarty::$_UTF8_MODIFIER,
                    '_',
                    $name . '_' . $value
                )
            );
            $_output .= '<label for="' . $_id . '">';
        } else {
            $_output .= '<label>';
        }
    }
    $name = smarty_function_escape_special_chars($name);
    $value = smarty_function_escape_special_chars($value);
    if ($escape) {
        $output = smarty_function_escape_special_chars($output);
    }
    $_output .= '<input type="checkbox" name="' . $name . '[]" value="' . $value . '"';
    if ($labels && $label_ids) {
        $_output .= ' id="' . $_id . '"';
    }
    if (is_array($selected)) {
        if (isset($selected[ $value ])) {
            $_output .= ' checked="checked"';
        }
    } elseif ($value === $selected) {
        $_output .= ' checked="checked"';
    }
    $_output .= $extra . '>' . $output;
    if ($labels) {
        $_output .= '</label>';
    }
    $_output .= $separator;
    return $_output;
}
