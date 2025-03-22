<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsFunction
 */
/**
 * Smarty {html_radios} function plugin
 *
 * Renders a list of HTML radio input elements based on the given parameters.
 * Supports both `values`/`output` arrays and associative `options` arrays.
 *
 * Example usage in templates:
 *   {html_radios values=$ids output=$names}
 *   {html_radios values=$ids name='group' separator='<br>' output=$names}
 *   {html_radios values=$ids checked=$selected separator='<br>' output=$names}
 *
 * Supported parameters:
 * - name       (string)  – Name attribute of the radio input group (default: "radio")
 * - values     (array)   – List of values for the radio buttons (used with "output")
 * - output     (array)   – Corresponding labels for the "values"
 * - options    (array)   – Associative array of value => label (alternative to values/output)
 * - checked    (mixed)   – Pre-selected value (alias: "selected")
 * - separator  (string)  – String to separate each radio input (e.g. '<br>', '&nbsp;')
 * - assign     (string)  – Assign the rendered output to a template variable
 * - escape     (bool)    – Whether to escape the label content (default: true)
 * - labels     (bool)    – Whether to wrap radio inputs in <label> tags (default: true)
 * - label_ids  (bool)    – Whether to add `id` attributes to inputs and link them with labels
 * - strict     (bool)    – Enables stricter attribute validation for 'disabled' or 'readonly'
 * - disabled   (bool|string) – Adds disabled attribute when strict mode is on
 * - readonly   (bool|string) – Adds readonly attribute when strict mode is on
 * - ...        (mixed)   – Any additional key-value pairs are added as HTML attributes
 *
 * @link    https://www.smarty.net/manual/en/language.function.html.radios.php
 * @author  Christopher Kvarme <christopher.kvarme@flashjab.com>
 * @author  Monte Ohrt <monte at ohrt dot com>
 * @version 1.1
 *
 * @param array<string, mixed>         $params   Parameters passed from the template
 * @param Smarty_Internal_Template     $template The Smarty template object
 * @return string                      Rendered HTML or empty string if 'assign' is used
 * @throws \SmartyException
 * @uses smarty_function_escape_special_chars()
 */
function smarty_function_html_radios($params, Smarty_Internal_Template $template)
{
    $template->_checkPlugins(
        array(
            array(
                'function' => 'smarty_function_escape_special_chars',
                'file'     => SMARTY_PLUGINS_DIR . 'shared.escape_special_chars.php'
            )
        )
    );
    $name = 'radio';
    $values = null;
    $options = null;
    $selected = null;
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
            case 'checked':
            case 'selected':
                if (is_array($_val)) {
                    trigger_error('html_radios: the "' . $_key . '" attribute cannot be an array', E_USER_WARNING);
                } elseif (is_object($_val)) {
                    if (method_exists($_val, '__toString')) {
                        $selected = smarty_function_escape_special_chars((string)$_val->__toString());
                    } else {
                        trigger_error(
                            'html_radios: selected attribute is an object of class \'' . get_class($_val) .
                            '\' without __toString() method',
                            E_USER_NOTICE
                        );
                    }
                } else {
                    $selected = (string)$_val;
                }
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
            case 'radios':
                trigger_error(
                    'html_radios: the use of the "radios" attribute is deprecated, use "options" instead',
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
                            "html_options: {$_key} attribute must be a scalar, only boolean true or string '$_key' will actually add the attribute",
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
                    trigger_error("html_radios: extra attribute '{$_key}' cannot be an array", E_USER_NOTICE);
                }
                break;
        }
    }
    $values = isset($params['values']) ? (array)$params['values'] : [];
    if ((empty($options) || !is_array($options)) && (!isset($params['values']) || empty($params['values']) || !is_array($params['values']))) {
        /* raise error here? */
        return '';
    }
    $_html_result = array();
    if (isset($options)) {
        foreach ($options as $_key => $_val) {
            $_html_result[] =
                smarty_function_html_radios_output(
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
        foreach ($values as $_i => $_key) {
            $_val = isset($output[ $_i ]) ? $output[ $_i ] : '';
            $_html_result[] =
                smarty_function_html_radios_output(
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
 * Helper function to render a single HTML radio input element (optionally wrapped in a <label> tag).
 *
 * This function is called internally by {@see smarty_function_html_radios()} for each radio item.
 * It handles value escaping, label wrapping, and optional ID generation for better accessibility.
 *
 * @param string $name       The name attribute for the radio input
 * @param mixed  $value      The value attribute for the radio input
 * @param mixed  $output     The visible label/text next to the radio input
 * @param mixed  $selected   The selected value (to determine "checked" state)
 * @param string $extra      Additional attributes as a string (e.g. 'disabled="disabled"')
 * @param string $separator  HTML or plain-text separator after the radio input
 * @param bool   $labels     Whether to wrap the radio input in a <label> tag
 * @param bool   $label_ids  Whether to generate a unique ID and associate it with the label
 * @param bool   $escape     Whether to HTML-escape the visible label text
 *
 * @return string            The rendered HTML string for the radio input (and optional label)
 */
function smarty_function_html_radios_output(
    $name,
    $value,
    $output,
    $selected,
    $extra,
    $separator,
    $labels,
    $label_ids,
    $escape
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
    $_output .= '<input type="radio" name="' . $name . '" value="' . $value . '"';
    if ($labels && $label_ids) {
        $_output .= ' id="' . $_id . '"';
    }
    if ($value === $selected) {
        $_output .= ' checked="checked"';
    }
    $_output .= $extra . '>' . $output;
    if ($labels) {
        $_output .= '</label>';
    }
    $_output .= $separator;
    return $_output;
}
