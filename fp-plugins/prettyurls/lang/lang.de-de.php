<?php
	$lang['plugin']['prettyurls']['errors'] = array (
		-2	=> 'Es ist keine <code>.htaccess</code> vorhanden oder es kann keine <code>.htaccess</code> im Blog Root angelegt werden. '.
				'Das PrettyURLs Plugin wird dann unter Umständen nicht korrekt arbeiten.'
	);
	
	$lang['admin']['plugin']['submenu']['prettyurls'] = 'PrettyURLs';
	$lang['admin']['plugin']['prettyurls'] = array(
		'head'		=> 'PrettyURLs Konfiguration',
		'htaccess'	=> '.htaccess',
		'description'=>'Dieser Editor ermöglicht die für das PrettyUrls Plugin benötigte '.
						'<code>.htaccess</code> direkt zu bearbeiten.',
		'cantsave'	=> 'Diese Datei kann nicht bearbeitet werden, weil sie schreibgeschützt ist. Ändere die Zugriffsrechte oder kopiere diese Zeilen, füge sie in eine lokale Datei ein und lade diese dann hoch.',
		'mode'		=> 'Modus',
		'auto'		=> 'Automatisch',
			'autodescr'	=> 'Ermittle die beste Möglichkeit automatisch',
		'pathinfo'	=> 'Path Info',
			'pathinfodescr' => 'e.g. /index.php/2011/01/01/hello-world/',
		'httpget'	=> 'HTTP Get',
			'httpgetdescr'=> 'e.g. /?u=/2011/01/01/hello-world/',
		'pretty'	=> 'Pretty',
			'prettydescr'=> 'e.g. /2011/01/01/hello-world/',

		'saveopt' 	=> 'Einstellungen speichern',

		'submit'	=> '.htaccess speichern'
	);
	$lang['admin']['plugin']['prettyurls']['msgs'] = array(
		1		=> 'Die Datei wurde .htaccess erfolgreich gespeichert',
		-1		=> 'Die Datei .htaccess konnte nicht gespeichert werden (Keine Schreibrechte im <code>'. BLOG_ROOT .'</code>)?',

		2		=> 'Einstellungen wurden erfolgreich gespeichert',
		-2		=> 'Ein Fehler ist beim Speichern aufgetreten',
	);
	
?>
