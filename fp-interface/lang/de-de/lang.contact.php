<?php
$baseurl = BLOG_BASEURL;

$lang ['contact'] = array(
	'head' => 'Kontakt',
	'descr' => 'Die Felder Name und Nachricht sind Pflichtfelder. Um dir eventuell antworten zu können, benötigen wir deine E-Mail Adresse.',
	'fieldset1' => 'Deine Angaben',
	'name' => 'Name (notwendig)',
	'email' => 'E-Mail Adresse:',
	'www' => 'Website (optional):',
	'cookie' => 'Daten für das nächste Mal merken',
	'fieldset2' => 'Deine Nachricht',
	'comment' => 'Nachricht (notwendig):',
	'fieldset3' => 'Senden',
	'submit' => 'Abschicken',
	'reset' => 'Zurücksetzen',
	'loggedin' => 'Du bist angemeldet 😉. <a href="' . $baseurl . 'login.php?do=logout">Abmelden</a> oder zum <a href="' . $baseurl . 'admin.php">Administrationsbereich</a>.'
);

$lang ['contact'] ['notification'] = array(
	'name' => 'Name:',
	'email' => 'E-Mail Adresse:',
	'www' => 'Website:',
	'content' => 'Nachricht:',
	'subject' => 'Kontaktaufnahme über '
);

$lang ['contact'] ['error'] = array(
	'name' => 'Bitte einen Namen eingeben',
	'email' => 'Bitte eine gültige E-Mail Adresse eingeben',
	'www' => 'Bitte eine gültige URL eingeben',
	'content' => 'Bitte eine Nachricht schreiben'
);

$lang ['contact'] ['msgs'] = array(
	1 => 'Die Nachricht wurde erfolgreich versendet',
	-1 => 'Fehler: Die Nachricht konnte nicht versendet werden'
);
?>