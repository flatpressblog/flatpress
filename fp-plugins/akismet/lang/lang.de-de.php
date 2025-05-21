<?php
	$lang['plugin']['akismet']['errors'] = array (
		-1	=> 'Kein API Key vorhanden, bitte diesen für das Plugin eintragen oder auf <a href="http://wordpress.com">Wordpress.com</a> einen gültigen API Key durch Registrierung beantragen'
	);
	
	$lang['admin']['plugin']['submenu']['akismet'] = 'Akismet Konfiguration';
	
	$lang['admin']['plugin']['akismet'] = array(
		'head'		=> 'Akismet Konfiguration',
		'description'=>'Mit <a href="http://akismet.com/">Akismet</a> kann man Spam reduzieren '
					 .'oder komplett eliminieren der durch Kommentare oder Trackbacks dieses Blog erreicht. '
					 .'Wenn bis jetzt noch kein Wordpress.com Account existiert, so kann man auf '.
					 '<a href="http://wordpress.com/api-keys/">WordPress.com</a> einen anlegen um einen API key zu beantragen.',
		'apikey'	=> 'WordPress.com API Key',
		'whatis'	=> '(<a href="http://faq.wordpress.com/2005/10/19/api-key/">Was ist ein API Key?</a>)',
		'submit'	=> 'API key speichern'
	);
	$lang['admin']['plugin']['akismet']['msgs'] = array(
		1		=> 'Der API key wurde gespeichert',
		-1		=> 'Der API key ist nicht gültig'
	);
	
?>
