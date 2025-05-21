<?php

	$lang['admin']['plugin']['submenu'] = array (
		'default'		=> 'Manage Plugins'
	);
	
	/* main plugin panel */

	$lang['admin']['plugin']['default'] = array(
	
		'head'		=> 'Manage Plugins',
		'enable'	=> 'Enable',
		'disable'	=> 'Disable',
		'descr'		=> 'A <a class="hint" '.
						'href="http://wiki.flatpress.org/doc:plugins" title="What is a plugin?">'.
						'Plugin</a> is a component which can expand the capabilities of FlatPress.</p>'.
						'<p>You can install plugins by uploading them in your <code>fp-plugins/</code> '.
						'directory.</p>'.
						'<p>This panel allows you to enable and disable plugins',
		'name'		=> 'Name',
		'description'=>'Description',
		'author'	=> 'Author',
		'version'	=> 'Version',
		'action'	=> 'Action',
	);
	
	$lang['admin']['plugin']['default']['msgs'] = array(
		1	=> 'Config saved',
		-1	=> 'An error occurred while trying to save. This may happen for several reasons: maybe your file contains syntax errors.',
	);
	
	/* system errors */
	
	$lang['admin']['plugin']['errors'] = array(
		'head'		=> 'The following errors were encountered while loading plugins:',
		'notfound'	=> 'Plugin was not found. Skipped.',
		'generic'	=> 'Error number %d',
	);
	
?>
