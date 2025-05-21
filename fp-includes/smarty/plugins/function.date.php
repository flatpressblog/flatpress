<?php


/**
* date tag
*/

function smarty_function_date($params, &$smarty) {

require_once $smarty->_get_plugin_filepath('modifier','date_format');


	$format = isset($params['format'])? $params['format'] : "%B %e, %Y";
	$date = $smarty->get_template_vars('date');
	$day = smarty_modifier_date_format($date, $format);
		
	if ($smarty->get_template_vars('prev_entry_day') != $day) {
		$smarty->assign('prev_entry_day', $day);
		
		$ret = isset($params['html'])? sprintf($params['html'], $day) : $day;
		
		return $ret;
	}
	
	return '';
	
}


/* vim: set expandtab: */

?>