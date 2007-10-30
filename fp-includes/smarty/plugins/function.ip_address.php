<?php


/**
* ip_address tag
*/

function smarty_function_ip_address($params, &$smarty) {

	$address = $smarty->get_template_vars('ip-address');
	
	return $address;
	
}


/* vim: set expandtab: */

?>