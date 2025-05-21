<?php
/**
 * Smarty {admincontrols}{/admincontrols} block plugin
 *
 * Type:     block function<br />
 * Name:     admincontrols<br />
 * Purpose:  automatically show/hides admin controls<br />
 * @author NoWhereMan <monte at ohrt dot com>
 * @return string string $content if loggedin
 */
function smarty_block_admincontrols($params, $content, &$smarty)
{
	if (user_loggedin())
		return $content;
}

?>
