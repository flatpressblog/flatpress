<?php
$lang ['plugin'] ['lastcommentsadmin '] ['errors'] = array (
	-1 => 'Akismet-API-Schlüssel nicht gesetzt. Öffne das Plugin, um deinen API-Schlüssel festzulegen. Registriere dich auf <a href="https://akismet.com/" target="_blank">akismet.com</a> um einen Schlüssel zu erhalten.'
);

$lang ['admin'] ['plugin'] ['submenu'] ['lastcommentsadmin'] = 'Last Comments Admin';

$lang ['admin'] ['plugin'] ['lastcommentsadmin'] = array(
	'head' => '"Letzte Kommentare" Cache verwalten',
	'description' => 'Leert und erneuert den Inhalt für die "Letzte Kommentare" Cache Datei ',
	'clear' => 'Lösche Cache',
	'cleardescription' => 'Lösche die vorhandene "Letzte Kommentare" Cache Datei. Eine neue Datei wird nach dem Posten eines Kommentares wieder angelegt.',
	'rebuild' => 'Cache neu anlegen',
	'rebuilddescription' => 'Eine neue Cache Datei anlegen. Diese Funktion sucht in allen Blog Beiträgen nach Kommentaren und übernimmt diese in die neue Cache Datei. Das kann einige Zeit dauern, je nachdem wieviele Kommentare gefunden werden!'
);

$lang ['admin'] ['plugin'] ['lastcommentsadmin'] ['msgs'] = array(
	1 => 'Cache wurde erfolgreich gelöscht',
	2 => 'Cache wurde erfolgreich erneuert',
	-1 => 'Fehler!',
	-2 => 'Dieses Plugin benötigt das in FlatPress integrierte LastComment Plugin. Bitte dieses vorher im Plugin Bereich aktivieren!'
);
?>