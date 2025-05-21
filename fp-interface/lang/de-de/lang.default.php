<?php

	$lang = array();

	$lang['main'] = array(
		
		'nextpage'		=> 'Nächste Seite &raquo;',
		'prevpage'		=> '&laquo; Vorherige Seite',
		'entry'      	=> 'Beitrag',
		'static'     	=> 'Statische Seite',
		'comment'    	=> 'Kommentar',
		'preview'    	=> 'Bearbeiten/Vorschau',
		
		'filed_under'	=> 'Abgelegt unter ',	
		
		'add_entry'  	=> 'Beitrag hinzufügen',
		'add_comment'  	=> 'Kommentar hinzufügen',
		'add_static'  	=> 'Statische Seite hinzufügen',
		
		'btn_edit'     	=> 'Bearbeiten',
		'btn_delete'   	=> 'Löschen',
		
		'nocomments'	=> 'Kommentar hinzufügen',
		'comment'	=> '1 Kommentar',
		'comments'	=> 'Kommentare',
		
	);
	
	$lang['search'] = array(
		
		'head'	=> 'Suchen',
		'fset1'	=> 'Suchkriterien einfügen',
		'keywords'	=> 'Suchwörter',
		'onlytitles'	=> 'Nur Titel suchen',
		'fulltext'	=> 'Volltextsuche',
		
		'fset2'	=> 'Suche nach Datum',
		'datedescr'	=> 'Du kannst nach einem beliebigen Datum suchen. Kriterien können sein: Jahr, Jahr und Monat oder als komplettes Datum. '.
					'Ohne Angaben wird alles durchsucht.',
		
		'fset3' 	=> 'In Kategorien suchen',
		'catdescr'	=> 'Es muss mindestens eine Kategorie angegeben werden.',
		
		'fset4'     => 'Suche starten',
		'submit'	=> 'Suche starten',
		
		'headres'	=> 'Suchergebnisse',
		'descrres'	=> 'Die Suche nach <strong>%s</strong> brachte folgende Ergebnisse:',
		'descrnores'=> 'Die Suche nach <strong>%s</strong> blieb erfolglos.',
		
		'moreopts'	=> 'Mehr Optionen',
		
		'searchag'	=> 'Suche wiederholen',
		
	);
	
	$lang['search']['error'] = array(
	
		'keywords'	=> 'Es muss mindestens ein Suchkriterium angegeben werden.'
	
	);
	
	$lang['entry'] = array();
	$lang['entry']['flags'] = array();
	
	$lang['entry']['flags']['long'] = array(
		'draft' => '<strong>Beitrag als Entwurf speichern</strong>: wird erst sichtbar, wenn er veröffentlicht wird.',
		//'static' => '<strong>Static entry</strong>: normally hidden, to reach the entry put ?page=title-of-the-entry in url (experimental)',
		'commslock' => '<strong>Kommentare sperren</strong>: Keine Kommentare für diesen Beitrag zulassen.'
	);
	
	$lang['entry']['flags']['short'] = array(
		'draft' => 'Entwürfe',
		//'static' => 'Static',
		'commslock' => 'Kommentare gesperrt.'
	);

	$lang['404error'] = array(
		'subject'	=> 'Nicht gefunden',
		'content'	=> '<p>Sorry, es wurde nichts passendes für diese Anfrage gefunden.</p>'
	);
		
	// Login
	$lang['login'] = array(
		
		'head'		=> 'Anmelden',
		'fieldset1'	=> 'Bitte Benutzer und Passwort eingeben',
		'user'		=> 'Benutzer:',
		'pass'		=> 'Passwort:',
		'fieldset2'	=> 'Einloggen',
		'submit'	=> 'Anmelden',
		'forgot'	=> 'Passwort vergessen'
	);
		
	$lang['login']['success'] = array(
		'success'	=> 'Du bist angemeldet.',
		'logout'	=> 'Du bist abgemeldet.',
		'redirect'	=> 'Automatische Weiterleitung auf das Blog in 5 Sekunden.',
		'opt1'		=> 'Zurück zum Blog',
		'opt2'		=> 'Zum Administrationsmenü',
		'opt3'		=> 'Neuen Beitrag erstellen'
	);
	
	$lang['login']['error'] = array(
		'user'		=> 'Bitte Benutzer angeben.',
		'pass'		=> 'Bitte Passwort eingeben.',
		'match'		=> 'Benutzer oder Passwort sind falsch.'
	);
	
	
	$lang['comments'] = array(
		'head'		=> 'Kommentar hinzufügen',
		'descr'		=> 'Die Felder Name und Kommentar sind Pflichtfelder.',
		'fieldset1'	=> 'Deine Angaben',
		'name'		=> 'Name (notwendig)',
		'email'		=> 'E-Mail Adresse (wird nicht veröffentlicht):',
		'www'		=> 'Website (optional):',
		'cookie'	=> 'Daten für das nächste Mal merken',
		'fieldset2'	=> 'Einen Kommentar schreiben',
		'comment'	=> 'Kommentar:',
		'fieldset3'	=> 'Senden',
		'submit'	=> 'Abschicken',
		'reset'		=> 'Zurücksetzen',
		'success'	=> 'Dein Kommentar wurde erfolgreich hinzugefügt',
		'nocomments'	=> 'Bis jetzt noch keine Kommentare vorhanden',
		'commslock'	=> 'Für diesen Eintrag sind keine Kommentare möglich',
	);
	
	$lang['comments']['error'] = array(
		'name'		=> 'Bitte einen Namen eingeben',
		'email'		=> 'Bitte eine gültige E-Mail Adresse eingeben',
		'www'		=> 'Bitte eine gültige URL eingeben',
		'comment'	=> 'Bitte einen Kommentar schreiben',
	);
	
	$lang['date']['month'] = array(
		
		'Januar',
		'Februar',
		'März',
		'April',
		'Mai',
		'Juni',
		'Juli',
		'August',
		'September',
		'Oktober',
		'November',
		'Dezember'
		
	);

	$lang['date']['month_abbr'] = array(
		
		'Jan',
		'Feb',
		'Mär',
		'Apr',
		'Mai',
		'Jun',
		'Jul',
		'Aug',
		'Sep',
		'Okt',
		'Nov',
		'Dez'
		
	);

	$lang['date']['weekday'] = array(

		'Sonntag',		
		'Montag',
		'Dienstag',
		'Mittwoch',
		'Donnerstag',
		'Freitag',
		'Samstag'
		
	);

	$lang['date']['weekday_abbr'] = array(

		'So',		
		'Mo',
		'Di',
		'Mi',
		'Do',
		'Fr',
		'Sa'
		
	);

?>
