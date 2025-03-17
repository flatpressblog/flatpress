<?php
/**
 * Smarty {admincontrols}{/admincontrols} block plugin
 *
 * Type: block function<br />
 * Name: admincontrols<br />
 * Purpose:  automatically show/hides admin controls<br />
 * @author NoWhereMan <monte at ohrt dot com>
 * @param array $params Parameters passed to block
 * @param string $content Content inside the block
 * @param object $smarty Smarty template object
 * @return string Content if logged in, otherwise empty string
 */
function smarty_block_admincontrols($params, $content, &$smarty) {
	if (user_loggedin()) {
		return $content;
	}
	return '';
}
?>
