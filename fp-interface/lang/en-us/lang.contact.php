<?php
$baseurl = BLOG_BASEURL;

$lang ['contact'] = array(
	'head' => 'Contact Us',
	'descr' => 'Fill out the form below to send us feedback. Please add your email if you wish to be answered.',
	'fieldset1' => 'User data',
	'name' => 'Name (*)',
	'email' => 'Email:',
	'www' => 'Web:',
	'cookie' => 'Remember me',
	'fieldset2' => 'Your message',
	'comment' => 'Message (*):',
	'fieldset3' => 'Send',
	'submit' => 'Send',
	'reset' => 'Reset',
	'loggedin' => 'You are logged in 😉. <a href="' . $baseurl . 'login.php?do=logout">Log out</a> or to the <a href="' . $baseurl . 'admin.php">Administration area</a>.'
);

$lang ['contact'] ['notification'] = array(
	'name' => 'Name:',
	'email' => 'Email:',
	'www' => 'Web:',
	'content' => 'Message:',
	'subject' => 'Contact sent through '
);

$lang ['contact'] ['error'] = array(
	'name' => 'You must enter a name',
	'email' => 'You must enter a valid email',
	'www' => 'You must enter a valid URL',
	'content' => 'You must enter a message'
);

$lang ['contact'] ['msgs'] = array(
	1 => 'Message was sent successfully',
	-1 => 'Message could not be sent'
);
?>