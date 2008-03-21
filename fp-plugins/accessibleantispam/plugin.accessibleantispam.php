<?php
/*
Plugin Name: Accessible Antispam
Plugin URI: http://flatpress.nowherland.it/
Description: Antispam asking to answer a simple math question.
Author: NoWhereMan  (E.Vacchi)
Version: 3.0
Author URI: http://www.nowhereland.it
*/

define('AASPAM_DEBUG', false);
define('AASPAM_LOG', CACHE_DIR . 'aaspamlog.txt');

add_action('comment_validate', 'plugin_aaspam_validate', 5, 2);
add_action('comment_form', 'plugin_aaspam_comment_form');


function plugin_aaspam_validate($bool, $arr) {
	
	// if boolean $bool==false
	// the test is forced to fail
	if (!$bool)
		return false;

	// if user is loggedin we ignore the plugin
	if (user_loggedin())
		return true;
	
	// get the value and reset last saved, so that
	// an attacker can't use the old one for multiple posting
	$v = sess_remove('aaspam');
	
	// we get the array stored in session:
	// if it evaluated to false value (e.g. is null) test fails
	if (!$v) {
		return false;
	}
	// we test the result wether match user input 
	if (!($ret = $_POST['aaspam']==$v)) {
		global $smarty;
		$lang = lang_load('plugin:accessibleantispam');
			
		$smarty->append('error', $lang['plugin']['accessibleantispam']['error']);
	}
	
		if ( AASPAM_DEBUG && $f=@fopen(AASPAM_LOG, 'a') ) {
			$arr['aaspam-q'] = $_POST['aaspam'];
			$arr['aaspam-a'] = $v;
			$arr['SUCCESS'] = $ret;
			
			$s = date('r'). "|" . session_id().'|'.utils_kimplode($arr)."\r\n";
			@fwrite($f, $s);
			@fclose($f);
		}
	
	
	return $ret;
}

function plugin_aaspam_comment_form() {
	
	// we get a random arithmetic operation
	// between sum, subtraction and multiplication;
	
	// we intentionally left out division because
	// it can lead to situations like division by zero
	// or floating point numbers

	$myop = array_rand($ops=array('+','-','*'));
	$op=$ops[$myop];
	
	// we get two random integers between 1 and 10
	$v1 = mt_rand(1, 10);
		// we rand $v2 until it differs from $v1 
		// (otherwise result for subtractions is zero) 
	while (($v2 = mt_rand(1, 10))==$v1); 
	
	// if operation is subtraction
	// the higher number must always come first
	// or you'll get a negative integer
	if ($v2>$v1 && $op=='-') {
		$tmp = $v1;
		$v1 = $v2;
		$v2 = $tmp;
		
	}

	// execute the operation
	switch($op) {
		case '+' :
			$v = $v1+$v2;
			break;
		case '-' :
			$v = $v1-$v2;
			break;
		case '*' :
			$v = $v1*$v2;
			break;
		}
	

	
	sess_add('aaspam', $v);
		
	// load plugin strings
	// they're located under plugin.PLUGINNAME/lang/LANGID/
	$lang = lang_load('plugin:accessibleantispam');
	
	$langstrings =& $lang['plugin']['accessibleantispam'];
	
	// get the correct question depending on the operation
	switch($op) {
		case '+' :
			$question = $langstrings['sum'];
			break;
		case '-' :
			$question = $langstrings['sub'];
			break;
		case '*' :
			$question = $langstrings['prod'];
			break;
		}
		
	// format the question with numbers at the proper positions
	$question = sprintf($question, $v1, $v2);
	
	if ( AASPAM_DEBUG && $f=@fopen(AASPAM_LOG, 'a') ) {
		$arr['aaspam-q'] = $v;
		@fwrite($f, date('r'). '|'.session_id() .'|'. utils_kimplode($arr)."\r\n");
		@fclose($f);
	}
	
	// echoes the question and the form part
	echo <<<STR
	<p><label class="textlabel" for="aaspam">{$lang['plugin']['accessibleantispam']['prefix']} <strong>$question (*)</strong></label><br />
		<input type="text" name="aaspam" id="aaspam" /></p>
STR;

}


?>
