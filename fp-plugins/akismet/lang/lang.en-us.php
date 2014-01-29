<?php
	$lang['plugin']['akismet']['errors'] = array (
		-1	=> 'API key not set. Open the plugin to set your API key. Register on <a href="http://wordpress.com">Wordpress.com</a> to get one'
	);
	
	$lang['admin']['plugin']['submenu']['akismet'] = 'Akismet Config';
	
	$lang['admin']['plugin']['akismet'] = array(
		'head'		=> 'Akismet Configuration',
		'description'=>'For many people, <a href="http://akismet.com/">Akismet</a> will greatly reduce '
					 .'or even completely eliminate the comment and trackback spam you get on your site. '
					 .'If you don\'t have a WordPress.com account yet, you can get one at '.
					 '<a href="http://wordpress.com/api-keys/">WordPress.com</a>.',
		'apikey'	=> 'WordPress.com API Key',
		'whatis'	=> '(<a href="http://faq.wordpress.com/2005/10/19/api-key/">What is this?</a>)',
		'submit'	=> 'Save API key'
	);
	$lang['admin']['plugin']['akismet']['msgs'] = array(
		1		=> 'API key saved',
		-1		=> 'API key is not valid'
	);
	
?>