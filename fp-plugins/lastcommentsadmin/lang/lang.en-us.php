<?php
	$lang['plugin']['lastcommentsadmin ']['errors'] = array (
		-1	=> 'API key not set. Open the plugin to set your API key. Register on <a href="http://wordpress.com">Wordpress.com</a> to get one'
	);

	$lang['admin']['plugin']['submenu']['lastcommentsadmin'] = 'Last Comments Admin';

	$lang['admin']['plugin']['lastcommentsadmin'] = array(
		'head'		=> 'Last Comments Admin',
		'description'=>'Clear and rebuil last comment cache ',
		'clear'	=> 'Clear cache',
		'cleardescription' => 'Delete last comment cache file. New file cache will created when a new comment will be posted.',
		'rebuild' => 'Rebuild cache',
		'rebuilddescription' => 'Rebuild last comment cache file. Could take very long time. Could not work at all. Could burn your mouse up!',
	);
	$lang['admin']['plugin']['lastcommentsadmin']['msgs'] = array(
		1		=> 'Cache deleted',
		2		=> 'Cache rebuilded!',
		-1		=> 'Error!',
		-2	   =>  'This plugin require LastComments plugin!'
	);
	

?>