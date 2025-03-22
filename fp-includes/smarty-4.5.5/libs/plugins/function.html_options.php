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
 * Renders a set of <option> tags for a <select> dropdown based on the given parameters.
 * Supports both indexed arrays (`values`/`output`) and associative arrays (`options`).
 *
 * Example usage in templates:
 *   {html_options values=$ids output=$labels}
 *   {html_options options=$assoc_array selected=$selected_id}
 *
 * Supported parameters:
 * - name      (string)   – The name attribute of the <select> element (optional)
 * - values    (array)    – List of option values (used with "output")
 * - output    (array)    – Labels corresponding to "values"
 * - options   (array)    – Associative array of value => label (alternative to values/output)
 * - selected  (mixed)    – Single value or array of preselected options
 * - id        (string)   – ID attribute for the <select> element
 * - class     (string)   – CSS class for the <select> element
 * - strict    (bool)     – Enables strict validation for certain attributes (optional)
 * - disabled  (bool)     – Add `disabled="disabled"` to the <select> element
 * - readonly  (bool)     – Add `readonly="readonly"` to the <select> element
 * - ...       (mixed)    – Any other key-value pair is added as an HTML attribute to the <select> tag
 *
 * @link     https://www.smarty.net/manual/en/language.function.html.options.php
 * @author   Monte Ohrt <monte at ohrt dot com>
 * @author   Ralf Strehle <ralf.strehle at yahoo dot de>
 * @version  1.1
 *
 * @param array<string, mixed>         $params   Parameters passed from the template
 * @param Smarty_Internal_Template     $template The Smarty template object
 * @return string                      Rendered <select> HTML with nested <option>/<optgroup> tags
 * @throws \SmartyException
 * @uses smarty_function_escape_special_chars()
 */
function smarty_function_html_options($params, Smarty_Internal_Template $template)
{
    $template->_checkPlugins([
        [
            'function' => 'smarty_function_escape_special_chars',
            'file'     => SMARTY_PLUGINS_DIR . 'shared.escape_special_chars.php'
        ]
    ]);

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
                $options = is_array($_val) ? $_val : [];
                break;
            case 'values':
            case 'output':
                $$_key = array_values((array)$_val);
                break;
            case 'selected':
                if (is_array($_val)) {
                    $selected = [];
                    foreach ($_val as $_sel) {
                        if (is_object($_sel) && method_exists($_sel, '__toString')) {
                            $_sel = smarty_function_escape_special_chars((string)$_sel->__toString());
                        } elseif (!is_object($_sel)) {
                            $_sel = smarty_function_escape_special_chars((string)$_sel);
                        } else {
                            trigger_error(
                                'html_options: selected attribute contains an object of class \'' .
                                get_class($_sel) . '\' without __toString() method',
                                E_USER_NOTICE
                            );
                            continue;
                        }
                        $selected[$_sel] = true;
                    }
                } elseif (is_object($_val) && method_exists($_val, '__toString')) {
                    $selected = smarty_function_escape_special_chars((string)$_val->__toString());
                } elseif (!is_object($_val)) {
                    $selected = smarty_function_escape_special_chars((string)$_val);
                } else {
                    trigger_error(
                        'html_options: selected attribute is an object of class \'' . get_class($_val) .
                        '\' without __toString() method',
                        E_USER_NOTICE
                    );
                }
                break;
            case 'strict':
                break;
            case 'disabled':
            case 'readonly':
                if (!empty($params['strict'])) {
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

    if ((!is_array($options) || empty($options)) && (!is_array($values) || empty($values))) {
        /* raise error here? */
        return '';
    }

    $_html_result = '';
    $_idx = 0;

    if (is_array($options) && !empty($options)) {
        foreach ($options as $_key => $_val) {
            $_html_result .= smarty_function_html_options_optoutput($_key, $_val, $selected, $id, $class, $_idx);
        }
    } elseif (is_array($values)) {
        foreach ($values as $_i => $_key) {
            $_val = isset($output[$_i]) ? $output[$_i] : '';
            $_html_result .= smarty_function_html_options_optoutput($_key, $_val, $selected, $id, $class, $_idx);
        }
    }

    if ($name !== null && $name !== '') {
        $_html_class = ($class !== null && $class !== '') ? ' class="' . $class . '"' : '';
        $_html_id = ($id !== null && $id !== '') ? ' id="' . $id . '"' : '';
        $_html_result = '<select name="' . $name . '"' . $_html_class . $_html_id . $extra . ">\n" .
            $_html_result . '</select>' . "\n";
    }

    return $_html_result;
}

/**
 * Helper function to render a single <option> element or delegate to optgroup.
 *
 * @internal Used by smarty_function_html_options()
 *
 * @param string|int                   $key       Option value
 * @param string|int|array|object      $value     Option label or optgroup array
 * @param string|array|null            $selected  Selected value(s)
 * @param string|null                  $id        Optional HTML id prefix
 * @param string|null                  $class     Optional CSS class
 * @param int                          &$idx      Current index (for id generation)
 * @return string                      Rendered <option> tag or <optgroup> block
 */
function smarty_function_html_options_optoutput($key, $value, $selected, $id, $class, &$idx)
{
    if (!is_array($value)) {
        $_key = smarty_function_escape_special_chars($key);
        $_html_result = '<option value="' . $_key . '"';

        if (is_array($selected) && isset($selected[$_key])) {
            $_html_result .= ' selected="selected"';
        } elseif ($_key === $selected) {
            $_html_result .= ' selected="selected"';
        }

        $_html_class = !empty($class) ? ' class="' . $class . ' option"' : '';
        $_html_id = !empty($id) ? ' id="' . $id . '-' . $idx . '"' : '';

        if (is_object($value) && method_exists($value, '__toString')) {
            $value = smarty_function_escape_special_chars((string)$value->__toString());
        } elseif (!is_object($value)) {
            $value = smarty_function_escape_special_chars((string)$value);
        } else {
            trigger_error(
                'html_options: value is an object of class \'' . get_class($value) . '\' without __toString() method',
                E_USER_NOTICE
            );
            return '';
        }

        $_html_result .= $_html_class . $_html_id . '>' . $value . '</option>' . "\n";
        $idx++;
    } else {
        $_idx = 0;
        $_html_result = smarty_function_html_options_optgroup(
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
 * Helper function to render an <optgroup> element with nested <option> tags.
 *
 * @internal Used by smarty_function_html_options_optoutput()
 *
 * @param string|int                   $key       Label for the optgroup
 * @param array                        $values    List of options inside the group
 * @param string|array|null            $selected  Selected value(s)
 * @param string|null                  $id        Optional ID prefix
 * @param string|null                  $class     Optional CSS class
 * @param int                          &$idx      Current index (passed by reference)
 * @return string                      Rendered <optgroup> HTML block
 */
function smarty_function_html_options_optgroup($key, $values, $selected, $id, $class, &$idx)
{
    $optgroup_html = '<optgroup label="' . smarty_function_escape_special_chars($key) . '">' . "\n";
    foreach ((array) $values as $key => $value) {
        $optgroup_html .= smarty_function_html_options_optoutput($key, $value, $selected, $id, $class, $idx);
    }
    $optgroup_html .= "</optgroup>\n";
    return $optgroup_html;
}
