<?php
	$lang['plugin']['prettyurls']['errors'] = array (
		-2	=> 'I can\'t find or create an <code>.htaccess</code> file in your root '.
				'directory. PrettyURLs might not work properly, see the config panel.'
	);
	
	$lang['admin']['plugin']['submenu']['prettyurls'] = 'PrettyURLs Config';
	$lang['admin']['plugin']['prettyurls'] = array(
		'head'		=> 'PrettyURLs Configuration',
		'description'=>'This raw editor let you edit your '.
						'<code><a class="hint" href="http://wiki.flatpress.org/doc:plugins:prettyurls#htaccess">.htaccess</a></code>.',
		'cantsave'	=> 'You can\'t edit this file, because it\'s not <strong>writable</strong>. You can give writing permission or copy and paste to a file and then upload as <a class="hint" href="http://wiki.flatpress.org/doc:plugins:prettyurls#manual_upload">described here</a>',
		'submit'	=> 'Save'
	);
	$lang['admin']['plugin']['prettyurls']['msgs'] = array(
		1		=> '.htaccess saved successfully',
		-1		=> '.htaccess could not be saved (do you have writing permissions on <code>'. BLOG_ROOT .'</code>)?'
	);
	
?>