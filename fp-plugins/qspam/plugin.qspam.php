<?php
/*
Plugin Name: QuickSpamFilter
Plugin URI: http://flatpress.nowherland.it/
Description: Antispam asking to answer a simple math question.
Author: NoWhereMan  (E.Vacchi)
Version: 3.0
Author URI: http://www.nowhereland.it
*/

add_action('comment_validate', 'plugin_qspam_validate');

function plugin_qspam_validate($bool) {

	if (!$bool) return false;

	$BAN_WORDS = array(
		'href', '[url'
	);


	$txt = isset($_POST['content'])? $_POST['content'] : null;
	
	if ($txt) {
		$txt =  strtolower(trim($txt));
		while (($w = array_pop($BAN_WORDS))
							&& 
					(($r = strpos ($txt, $w)) === false));
		
		if( strrchr($txt, ':')==':' ) $r=true;
					
		if ($r!==false) {
			global $_FP_SMARTY;
			$_FP_SMARTY->assign('error', array('ERROR: The comment contained banned words'));
			return false;
		} 
	}
	
	return true;
	
	
}
?>