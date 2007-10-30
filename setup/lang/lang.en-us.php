<?php
/*
LangId: English
*/
	$lang['locked'] = array(
		'head'	=> 'Setup has been locked',
		'descr' => 
		'It looks like you already run setup, because 
		we found the lockfile <code>%s</code>.
		
		If you need to restart setup, please delete this file first.
		
		<strong >Remember!</strong> It\'s not safe keeping <code>setup.php</code> and the <code>setup/</code> directory 	on your server, we suggest you to delete it!
		
		<ul>
		<li><a href="%s">Ok, take me back to my blog</a></li>
		<li><a href="%s">I\'ve deleted the file, restart the setup</a></li>
		</ul>'
	);

	$lang['step1'] = array (
		'head'	=> 'Welcome to FlatPress',
		'descr' => 
		'Thank you for choosing <strong>FlatPress</strong>!
		
		Before you can start having fun with your brand new blog, we have ask you a very few questions. 
		
		Don\'t worry, it won\'t take you long!',
		
		'descrl1' => 'Select your language.',
		'descrl2' => '<a class="hint" onclick="toggleinfo();">Not in the list?</a>',
		
		'descrlang' =>
		
		'If you don\'t see your language in this list, you might want to see if there is <a href="http://wiki.flatpress.org/res:language">a language pack</a> for this version:
		
		<pre>%s</pre>
		
		To install the language pack, upload the content of the package in your <code>flatpress/</code>, and overwrite all, then <a href="./setup.php">restart this setup</a>.',
		
		'descrw' =>
		'The <strong>only thing</strong> you need for FlatPress to work is a <em>writable</em> directory.
		
		Please make this directory <a class="hint" href="http://wiki.flatpress.org/doc:setup#making_the_contents_directory_writable">writable</a> before you continue. 
		
		<pre>%s</pre>'
		
	);
	
	$lang['step2'] = array (
		'head'	=> 'Create user',
		'descr' => 
		'You\'re already almost done, fill in the following details:',
		'fpuser'	=> 'Username',
		'fppwd' 	=> 'Password',
		'fppwd2'	=> 'Re-type password',
		'www'		=> 'Home Page',
		'email'		=> 'E-Mail'
		
	);
	
	$lang['step3'] = array (
		'head'	=> 'Done',
		'descr' => 
		'<strong>End of the story</strong>. 
		
		Unbelievable? 
		
		And you\'re right: <strong>the story has just begun</strong>, but <strong>writing it\'s up to you</strong>!
		
		<ul>
		<li>See <a href="%s">how the home page looks like</a></li>
		<li>Have fun! <a href="%s">Login now!</a></li>
		<li>Do you feel like dropping us a line? <a href="http://www.flatpress.org/">Go to FlatPress.org!</a></li>
		</ul>
		
		And thank you for choosing FlatPress!'
		
	);
	
	$lang['buttonbar'] = array(
		'next'	=> 'Next >'
	);

?>