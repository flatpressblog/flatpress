<?php

$lang['plugin']['qspam'] = array(
	'error' => 'ERROR: The comment contained banned words'
);

$lang['admin']['plugin']['submenu']['qspam'] = 'QuickSpamFilter';
$lang['admin']['plugin']['qspam'] = array(
	'head' => 'QuickSpam Configuration',
	'desc1' => 'Do not allow comments containing these words (write one per line) :',
	'desc2' => '<strong>Warning:</strong> A comment will be disallowed even when one word is part of another. 
	
	(e.g. "old" matches "b<em>old</em>" too)',
	'options' => 'Other options',
	'desc3' => 'Bad Word Count',
	'desc3pre' => 'Block comments containing more than ',
	'desc3post' => ' bad word(s).',
	'submit' => 'Save configuration',
	'msgs' => array(
		1 => 'Bad words successful saved.',
		-1 => 'Bad words not saved.'
	)
);

?>
