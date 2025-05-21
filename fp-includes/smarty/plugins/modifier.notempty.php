<?php

/**
 * Smarty notempty modifier plugin
 *
 * Type:     modifier<br>
 * Name:     lower<br>
 * Purpose:  print a message if the input variable is not empty or null
 * @author   Edoardo Vacchi (NoWhereMan)
 * @param string
 * @param string
 * @return string
 */

function smarty_modifier_notempty($string, $default = '')
{
    if (isset($string) && $string !== '')
        return $default;
}

?>
