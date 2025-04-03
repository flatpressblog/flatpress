<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsFunction
 */
/**
 * Generates an HTML table from a data array.
 *
 * Smarty {html_table} function plugin
 * Type:     function
 * Name:     html_table
 * Date:     Feb 17, 2003
 * Purpose:  make an html table from an array of data
 *
 * @param array{
 *     loop: array|Traversable,                        // array to loop through
 *     cols?: int|string|array,                        // number of columns or list of column names
 *     rows?: int,                                     // number of rows
 *     table_attr?: string,                            // table attributes
 *     th_attr?: string|array,                         // table heading attributes (arrays are cycled)
 *     tr_attr?: string|array,                         // table row attributes (arrays are cycled)
 *     td_attr?: string|array,                         // table cell attributes (arrays are cycled)
 *     trailpad?: string,                              // value to pad trailing cells with
 *     caption?: string,                               // text for caption element
 *     vdir?: string,                                  // vertical direction ("down" or "up")
 *     hdir?: string,                                  // horizontal direction ("right" or "left")
 *     inner?: string                                  // inner loop direction ("cols" or "rows")
 * } $params
 *
 * Examples:
 * {table loop=$data}
 * {table loop=$data cols=4 tr_attr='"bgcolor=red"'}
 * {table loop=$data cols="first,second,third" tr_attr=$colors}
 *
 * @return string HTML table markup
 *
 * @author  Monte Ohrt <monte at ohrt dot com>
 * @author  credit to Messju Mohr <messju at lammfellpuschen dot de>
 * @author  credit to boots <boots dot smarty at yahoo dot com>
 * @version 1.1
 * @link    https://www.smarty.net/manual/en/language.function.html.table.php {html_table}
 */
function smarty_function_html_table($params)
{
    $table_attr = 'border="1"';
    $tr_attr = '';
    $th_attr = '';
    $td_attr = '';
    $cols = $cols_count = 3;
    $rows = 3;
    $trailpad = '&nbsp;';
    $vdir = 'down';
    $hdir = 'right';
    $inner = 'cols';
    $caption = '';
    $loop = [];
    if (!isset($params[ 'loop' ])) {
        trigger_error("html_table: missing 'loop' parameter", E_USER_WARNING);
        return '';
    }
    foreach ($params as $_key => $_value) {
        switch ($_key) {
            case 'loop':
                $loop = (array)$_value;
                break;
            case 'cols':
                if (is_array($_value) && !empty($_value)) {
                    $cols = $_value;
                    $cols_count = count($_value);
                } elseif (!is_numeric($_value) && is_string($_value) && !empty($_value)) {
                    $cols = explode(',', $_value);
                    $cols_count = count($cols);
                } elseif (!empty($_value)) {
                    $cols_count = (int)$_value;
                } else {
                    $cols_count = (int)$cols;
                }
                break;
            case 'rows':
                $rows = (int)$_value;
                break;
            case 'table_attr':
            case 'trailpad':
            case 'hdir':
            case 'vdir':
            case 'inner':
            case 'caption':
                $$_key = (string)$_value;
                break;
            case 'tr_attr':
            case 'td_attr':
            case 'th_attr':
                $$_key = $_value;
                break;
        }
    }
    $loop_count = count($loop);
    $cols_count = (!empty($cols_count)) ? (int)$cols_count : 3;
    $rows = (int)$rows;

    if (empty($params[ 'rows' ])) {
        /* no rows specified */
        $rows = ($cols_count > 0) ? (int)ceil($loop_count / $cols_count) : 0;
    } elseif (empty($params[ 'cols' ])) {
        if (!empty($params[ 'rows' ])) {
            /* no cols specified, but rows */
            $cols_count = ($rows > 0) ? (int)ceil($loop_count / $rows) : 0;
        }
    }
    $output = "<table $table_attr>\n";
    if (trim($caption) !== '') {
        $output .= '<caption>' . $caption . "</caption>\n";
    }

    if (is_array($cols)) {
        $cols = ($hdir === 'right') ? array_values($cols) : array_reverse(array_values($cols));
        $output .= "<thead><tr>\n";
        for ($r = 0; $r < $cols_count; $r++) {
            $output .= '<th' . smarty_function_html_table_cycle('th', $th_attr, $r) . '>';
            $output .= isset($cols[$r]) ? $cols[$r] : '';
            $output .= "</th>\n";
        }
        $output .= "</tr></thead>\n";
    }

    $output .= "<tbody>\n";
    for ($r = 0; $r < $rows; $r++) {
        $output .= "<tr" . smarty_function_html_table_cycle('tr', $tr_attr, $r) . ">\n";
        $rx = ($vdir === 'down') ? $r * $cols_count : ($rows - 1 - $r) * $cols_count;
        for ($c = 0; $c < $cols_count; $c++) {
            $x = ($hdir === 'right') ? $rx + $c : $rx + $cols_count - 1 - $c;
            if ($inner !== 'cols') {
                /* shuffle x to loop over rows*/
                $x = (int)(floor($x / $cols_count) + ($x % $cols_count) * $rows);
            }
            if ($x < $loop_count) {
                $output .= "<td" . smarty_function_html_table_cycle('td', $td_attr, $c) . ">" . $loop[ $x ] . "</td>\n";
            } else {
                $output .= "<td" . smarty_function_html_table_cycle('td', $td_attr, $c) . ">$trailpad</td>\n";
            }
        }
        $output .= "</tr>\n";
    }
    $output .= "</tbody>\n";
    $output .= "</table>\n";
    return $output;
}

/**
 * @param $name
 * @param $var
 * @param $no
 *
 * @return string
 */
function smarty_function_html_table_cycle($name, $var, $no)
{
    if (!is_array($var)) {
        $ret = $var;
    } else {
        $ret = $var[ $no % count($var) ];
    }
    return ($ret) ? ' ' . $ret : '';
}
