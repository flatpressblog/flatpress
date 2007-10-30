<?php
/*
Plugin Name: Accessible Antispam
Plugin URI: http://flatpress.nowherland.it/
Description: Antispam asking to answer a simple math question.
Author: NoWhereMan  (E.Vacchi)
Version: 3.0
Author URI: http://www.nowhereland.it
*/

add_action('comment_validate', 'plugin_aaspam_validate');
add_action('comment_form', 'plugin_aaspam_comment_form');


function plugin_aaspam_validate($bool) {
	
	// if boolean $bool==false
	// the test is forced to fail
	if (!$bool)
		return false;

	// if user is loggedin we ignore the plugin
	if (user_loggedin())
		return true;
	
	// get the value and reset last saved, so that
	// an attacker can't use the old one for multiple posting
	$val = sess_remove('aaspam');
	
	// we get the array stored in session:
	// if it evaluated to false value (e.g. is null) test fails
	if (!$val)
		return false;
	
	// we import the array keys into current scope
	extract($val);
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
	
	// we test the result wether match user input 
	if (!($ret = $_POST['aaspam']==$v)) {
		global $_FP_SMARTY;
		$lang = lang_load('plugin:accessibleantispam');
			
		$_FP_SMARTY->append('error', $lang['plugin']['accessibleantispam']['error']);
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
	
	// save an array like this array(operand, operation, operand)
	sess_add('aaspam', compact('v1','op','v2'));
		
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
	
	
	// echoes the question and the form part
	echo <<<STR
	<p><label class="textlabel" for="aaspam">{$lang['plugin']['accessibleantispam']['prefix']} <strong>$question (*)</strong></label><br />
		<input type="text" name="aaspam" id="aaspam" /></p>
STR;

}


?>
