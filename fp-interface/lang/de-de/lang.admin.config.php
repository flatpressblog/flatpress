<?php

	$lang['admin']['config']['default'] = 
	array(
		'head'		=> 'Konfiguration',
		'descr'		=> 'Flatpress konfigurieren und anpassen.',
		'submit'		=> 'Einstellungen übernehmen',
		
		'sysfset'		=> 'Allgemeine Einstellungen',
		'syswarning'	=> '<big>Warnung!</big> Diese Einstellungen sollten sorgfältig eingegeben werden,
	                    sonst könnte Flatpress nicht richtig funktionieren.',
		'blog_root'		=> '<strong>Absoluter Pfad zu Flatpress</strong>. Hinweis: 
	                    Normalerweise muss hier nichts geändert werden. Flatpress bietet keine interne
	                    Funktion um eventuelle Änderungen von sich aus zu prüfen.',
		'www'		=>'<strong>Blog Root</strong>. URL deines Blogs mit Angabe des Verzeichnisses. <br />
	             Beispiel: http://www.mydomain.com/flatpress/ (abschließender Slash wird benötigt)',
		
		// ------
		
		'gensetts'		=> 'Grundlegende Einstellungen',
		'blogtitle'		=> 'Blog Titel',
		'blogsubtitle'		=> 'Blog Untertitel',
		'blogfooter'		=> 'Blog Fussbereich',
		'blogauthor'		=> 'Blog Autor',
		'startpage'			=> 'Die Startseite dieses Blogs ist',
		'stdstartpage'		=> 'Mein Blog (default)',
		'blogurl'			=> 'Blog URL',
		'blogemail'			=> 'Blog E-Mail',
		'notifications'		=> 'Benachrichtigungen',
		'mailnotify'		=> 'Aktiviere E-Mail Benachrichtigung bei neuen Kommentaren',
		'blogmaxentries'	=> 'Anzahl der Beiträge pro Seite',
		'langchoice'		=> 'Sprache',

		'intsetts'		=> 'Internationale Einstellungen',
		'utctime'		=> '<acronym title="Universal Coordinated Time">UTC</acronym> Zeitzone',
		'timeoffset'		=> 'Uhrzeit soll korrigiert werden um',
		'hours'			=> 'Stunden',
		'timeformat'		=> 'Standard Zeitformat',
		'dateformat'		=> 'Standard Datumformat',
    'dateformatshort'	=> 'Standard Datumformat (kurz)',
		'output'		=> 'Ausgabe',
		'charset'		=> 'Zeichensatz',
		'charsettip'	=> 'Der empfohlene Zeichensatz für Flatpress ist '.
						'<a href="http://wiki.flatpress.org/doc:charsets">UTF-8</a>.'
		);
		
	$lang['admin']['config']['default']['msgs'] = 
	array(
		1		=> 'Die Konfiguration wurde erfolgreich gespeichert.',
		-1		=> 'Es ist ein Fehler beim Speichern der Konfiguration aufgetreten.',
		
		);
			
	$lang['admin']['config']['default']['error'] = 
	array(
		'www' 		=>	'Das Blog Root muss eine gültige URL haben',
		'title'		=>	'Du must einen Titel angeben',
		'email'		=>	'Die E-Mail Adresse benötigt ein gültiges Format',
		'maxentries'=>	'Du hast eine ungültige Anzahl für die Beiträge eingegeben',
		'timeoffset'=>	'Du hast eine ungültige Zeitkorrektur eingegeben! '.
						'Es werden auch Kommas akzeptiert (Beispiel: 2h30" => 2.5)',
		'timeformat'=>	'Das Format für die Uhrzeit ist ungültig',
		'dateformat'=>	'Das Format für das Datum ist ungültig',
    'dateformatshort'=>	'Bitte ein gültiges kurzes Datum eingeben',
		'charset'	=>	'Der angegebene Zeichensatz ist ungültig',
		'lang'		=>	'Die ausgewählte Sprache ist nicht verfügbar'
		);		
			
		
?>
