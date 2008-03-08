<?php
/*
Plugin Name: QuickSpamFilter
Plugin URI: http://flatpress.nowherland.it/
Description: Antispam asking to answer a simple math question.
Author: NoWhereMan  (E.Vacchi)
Version: 3.0
Author URI: http://www.nowhereland.it
*/

add_action('comment_validate', 'plugin_qspam_validate', 5, 2);

function plugin_qspam_validate(&$bool, $contents) {

	if (!$bool) return false;

	$BAN_WORDS = array(
		'href', '[url' // bans links 
	);

	$txt =  strtolower(trim($contents['content']));
	while (($w = array_pop($BAN_WORDS))
				&& 
				(($r = strpos ($txt, $w)) === false));
		
	# if( strrchr($txt, ':')==':' ) $r=true;
					
	if ($r!==false) {
		global $smarty;
		$smarty->assign('error', array('ERROR: The comment contained banned words'));
		return false;
	} 
		
	return true;
	
	
}
?>
