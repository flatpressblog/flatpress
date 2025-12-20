<?php
$lang ['admin'] ['config'] ['default'] = array(
	'head' => 'Konfiguration',
	'descr' => 'FlatPress konfigurieren und anpassen.',
	'submit' => 'Einstellungen übernehmen',

	'sysfset' => 'Allgemeine Einstellungen',
	'syswarning' => '<big>Warnung!</big> Diese Einstellungen sollten sorgfältig eingegeben werden, sonst könnte FlatPress nicht richtig funktionieren.',
	'blog_root' => '<strong>Absoluter Pfad zu FlatPress</strong>. Hinweis: ' . //
		'Normalerweise muss hier nichts geändert werden. FlatPress bietet keine interne Funktion um eventuelle Änderungen von sich aus zu prüfen.',
	'www' => '<strong>Blog Root</strong>. URL deines Blogs mit Angabe des Verzeichnisses.<br>' . //
		'Beispiel: http://www.mydomain.com/flatpress/ (abschließender Slash wird benötigt)',

	// ------
	'gensetts' => 'Grundlegende Einstellungen',
	'adminname' => 'Administrator Name',
	'adminpassword' => 'Neues Passwort',
	'adminpasswordconfirm' => 'Passwort wiederholen',
	'blogtitle' => 'Blog-Titel',
	'blogsubtitle' => 'Blog-Untertitel',
	'blogfooter' => 'Fußzeile des Blogs',
	'blogauthor' => 'Autor des Blogs',
	'startpage' => 'Die Startseite dieses Blogs ist',
	'stdstartpage' => 'Mein Blog (default)',
	'blogurl' => 'URL des Blogs',
	'blogemail' => 'E-Mail-Adresse für Benachrichtigungen',
	'notifications' => 'Benachrichtigungen',
	'mailnotify' => 'Aktiviere E-Mail-Benachrichtigung bei neuen Kommentaren',
	'blogmaxentries' => 'Anzahl der Beiträge pro Seite',
	'langchoice' => 'Sprache',

	'intsetts' => 'Internationale Einstellungen',
	'utctime' => 'Aktuelle <abbr title="Universal Coordinated Time">UTC</abbr>-Zeit',
	'timeoffset' => 'Uhrzeit soll korrigiert werden um',
	'hours' => 'Stunden',
	'timeformat' => 'Standard Zeitformat',
	'dateformat' => 'Standard Datumsformat',
	'dateformatshort' => 'Standard Datumsformat (kurz)',
	'output' => 'Ausgabe',
	'charset' => 'Zeichensatz',
	'charsettip' => 'Der empfohlene Zeichensatz für FlatPress ist ' . //
		'<a class="hint" href="https://wiki.flatpress.org/doc:techfaq#character_encoding" target="_blank" title="Welche Zeichenkodierungsstandards werden von FlatPress unterstützt?">UTF-8</a>.'
);

$lang ['admin'] ['config'] ['default'] ['msgs'] = array(
	1 => 'Die Konfiguration wurde erfolgreich gespeichert.',
	2 => 'Der Administrator wurde geändert. Du wirst jetzt abgemeldet.',
	-1 => 'Es ist ein Fehler beim Speichern der Konfiguration aufgetreten.'
);

$lang ['admin'] ['config'] ['default'] ['error'] = array(
	'www' => 'Das Blog Root muss eine gültige URL haben',
	'title' => 'Du musst einen Titel angeben',
	'email' => 'Die E-Mail Adresse benötigt ein gültiges Format',
	'maxentries' => 'Du hast eine ungültige Anzahl für die Beiträge eingegeben',
	'timeoffset' => 'Du hast eine ungültige Zeitkorrektur eingegeben! ' . //
		'Es werden auch Kommas akzeptiert (Beispiel: 2h30" => 2.5)',
	'timeformat' => 'Das Format für die Uhrzeit ist ungültig',
	'dateformat' => 'Das Format für das Datum ist ungültig',
	'dateformatshort' => 'Bitte ein gültiges kurzes Datum eingeben',
	'charset' => 'Der angegebene Zeichensatz ist ungültig',
	'lang' => 'Die ausgewählte Sprache ist nicht verfügbar',
	'admin' => 'Der Name des Administrators darf nur Buchstaben, Zahlen und 1 Unterstrich enthalten.',
	'password' => 'Das Passwort muss mindestens 6 Zeichen und darf keine Leerzeichen enthalten.',
	'confirm_password' => 'Die Passwörter stimmen nicht überein.'
);
?>
