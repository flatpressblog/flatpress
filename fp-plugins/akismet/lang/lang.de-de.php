<?php
$lang ['plugin'] ['akismet'] ['errors'] = array (
	-1 => 'Kein API Key vorhanden, bitte diesen für das Plugin eintragen oder auf <a href="https://akismet.com/signup/" target="_blank">akismet.com</a> einen gültigen API Key durch Registrierung beantragen.'
);

$lang ['admin'] ['plugin'] ['submenu'] ['akismet'] = 'Akismet Konfiguration';

$lang ['admin'] ['plugin'] ['akismet'] = array(
	'head' => 'Akismet Konfiguration',
	'description'=>'Mit <a href="https://akismet.com/" target="_blank">Akismet</a> kann man Spam reduzieren ' . //
		'oder komplett eliminieren der durch Kommentare oder Trackbacks dieses Blog erreicht. ' . //
		'Wenn bis jetzt noch kein Akismet Account existiert, so kann man auf ' . //
		'<a href="https://akismet.com/signup/" target="_blank">akismet.com/signup<a> einen anlegen um einen API key zu beantragen.',
	'apikey' => 'Akismet API Key',
	'whatis' => '(<a href="https://akismet.com/support/getting-started/api-key/" target="_blank">Was ist ein API Key?</a>)',
	'submit' => 'API key speichern'
);

$lang ['admin'] ['plugin'] ['akismet'] ['msgs'] = array(
	1 => 'Der API key wurde gespeichert',
	-1 => 'Der API key ist nicht gültig'
);
?>
