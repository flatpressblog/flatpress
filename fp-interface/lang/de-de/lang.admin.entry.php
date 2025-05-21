<?php


	$lang['admin']['entry']['submenu'] = 
	array (
		'list'		=> 'Beiträge verwalten',
		'write'		=> 'Beitrag schreiben',
		'cats'		=> 'Kategorien verwalten'
	);


	/* default action */
	
	$lang['admin']['entry']['list'] = 
	array(
		'head'		=> 'Beiträge und Kategorien verwalten',
		'descr'		=> 'An dieser Stelle kann man Beiträge zum Bearbeiten auswählen, einen <a href="admin.php?p=entry&amp;action=write">neuen Beitrag</a> schreiben oder '.
					       '<a href="admin.php?p=entry&amp;action=cats">Kategorien bearbeiten</a>. Ebenfalls besteht die Möglichkeit Kommentare von Beiträgen zu löschen.',
		'filter'	=> 'Filter: ',
		'nofilter'	=> 'Zeige alle',
		'filterbtn'	=> 'Filter anwenden',
		'sel'		=> 'Sel', // checkbox
		'date'		=> 'Datum',
		'title'		=> 'Titel',
		'author'	=> 'Autor',
		'comms'		=> '#Kommentare', // comments
		'action'	=> 'Aktion',
		'act_del'	=> 'Löschen',
		'act_view'	=> 'Anzeigen',
		'act_edit'	=> 'Bearbeiten'
	);
	
	/* write action */
	$lang['admin']['entry']['write'] = 
	array(
		'head'		=> 'Beitrag schreiben',
		'descr'		=> 'Bitte ausfüllen um einen neuen Beitrag zu schreiben',
		'uploader'	=> 'Uploader',
		'fieldset1'	=> 'Bearbeiten',
		'subject'	=> 'Titel (*):',
		'content'	=> 'Inhalt (*):',
		'fieldset2'	=> 'Senden',
		'submit'	=> 'Veröffentlichen',
		'preview'	=> 'Vorschau',
		'savecontinue'	=> 'Speichern &amp; weiter',
		'categories'	=> 'Kategorie für den Beitrag auswählen',
		'nocategories'	=> 'Keine Kategorie ausgewählt. <a href="admin.php?p=entry&amp;action=cats">Erstelle eine '. 
					'Kategorie</a> im Verwaltungsbereich. '.
					'Bitte Beitrag vorher <a href="#save">speichern</a>.',
		'saveopts'	=> 'Speicheroptionen',
		'success'	=> 'Dein Beitrag wurde erfolgreich veröffentlicht',
		'otheropts'	=> 'Andere Optionen',
		'commmsg'	=> 'Verwalte die Kommentare für diesen Beitrag',
		'delmsg'	=> 'Lösche diesen Beitrag',
		//'back'		=> 'Back discarding changes',
	);
	

	$lang['admin']['entry']['list']['msgs'] = array(
		1	=> 'Beitrag wurde erfolgreich gespeichert',
		-1	=> 'Ein Fehler ist beim Speichern des Beitrags aufgetreten',
		2	=> 'Beitrag wurde erfolgreich gelöscht',
		-2	=> 'Ein Fehler ist beim löschen des Beitrags aufgetreten',
	);

	
	$lang['admin']['entry']['write']['error'] = array(
		'subject'	=> 'Es wurde kein Titel angegeben',
		'content'	=> 'Es ist kein Inhalt vorhanden',
	);
	
	$lang['admin']['entry']['write']['msgs'] = array(
		1	=> 'Beitrag wurde erfolgreich gespeichert',
		-1	=> 'Ein Fehler ist aufgetreten: Der Beitrag konnte nicht erfolgreich gespeichert werden',
		-2	=> 'Ein Fehler ist aufgetreten: Der Beitrag wurde nicht gespeichert; eventuell ist der Index beschädigt',
		-3	=> 'Ein Fehler ist aufgetreten: Der Beitrag wurde zur Sicherheit als Entwurf abgelegt',
		-4	=> 'Ein Fehler ist aufgetreten: Der Beitrag wurde zur Sicherheit als Entwurf abgelegt; möglicherweise ist der Index beschädigt worden',
		'draft'=> 'Du bearbeitest gerade einen Beitrag im <strong>Entwurfs</strong>-Modus'		
	);
	
	/* comments */
	
	$lang['admin']['entry']['commentlist'] = 
	array(
		'head'		=> "Kommentare für den Beitrag: ", 
		'descr'		=> 'Bitte Kommentar auswählen, der gelöscht werden soll',
		'sel'		=> 'Sel',
		'content'	=> 'Inhalt',
		'date'		=> 'Datum',
		'author'	=> 'Autor',
		'email'		=> 'E-Mail',
		'ip'		=> 'IP',
		'actions'	=> 'Aktion',
		'act_edit'	=> 'Bearbeiten',		
		'act_del'	=> 'Löschen',
		'act_del_confirm' => 'Willst du diesen Kommentar wirklich löschen?',
		'nocomments'	=> 'Dieser Beitrag enthält zur Zeit keine Kommentare.',
		
	
	);
	
	$lang['admin']['entry']['commentlist']['msgs'] =
	array(
		1	=> 'Der Kommentar wurde erfolgreich gelöscht',
		-1	=> 'Ein Fehler ist beim Löschen des Kommentars aufgetreten',
		
	);	
	
	$lang['admin']['entry']['commedit'] = 
	array(
		'head'		=> "Bearbeite den Kommentar für Beitrag", 
		'content'	=> 'Kommentarinhalt',
		'date'		=> 'Datum',
		'author'	=> 'Autor',
		'www'		=> 'Website',
		'email'		=> 'E-Mail',
		'ip'		=> 'IP-Adresse',
		'loggedin'	=> 'Eingetragener Benutzer',		
		'submit'	=> 'Änderungen speichern'
		
	
	);

	$lang['admin']['entry']['commedit']['msgs'] =
	array(
		1	=> 'Der Kommentar wurde geändert',
		-1	=> 'Ein Fehler ist beim Ändern des Kommentars aufgetreten',
	);	
	
	/* delete action */
	
	$lang['admin']['entry']['delete'] = 
	array(
		'head'		=> 'Beitrag löschen', 
		'descr'		=> 'Du hast diesen Beitrag zum löschen ausgewählt:',
		'preview'	=> 'Vorschau',
		'confirm'	=> 'Willst du diesen Beitrag wirklich löschen?',
		'fset'		=> 'Löschen',
		'ok'		=> 'Ja, diesen Beitrag löschen',
		'cancel'	=> 'Nein, zurück zur Verwaltung',
		'err'		=> 'Der ausgewählte Beitrag existiert nicht',
	
	);
	
	/* category mgmt */
	
	$lang['admin']['entry']['cats'] =
	array(
		'head'		=> 'Kategorien verwalten',
		'descr'		=> '<p>Jede Kategorie sollte im Schema "Kategorie Name <em>:id_nummer</em>" angelegt werden. Die "<em>id_nummer</em>" ist <strong>eindeutig</strong> den Beiträgen zugeordnet, darf <strong>nicht</strong> mehr verändert werden und muss größer <strong>0</strong> sein. Der Kategoriename hingegen kann auch später noch geändert werden.</p><p>Ein späteres Umstellen der Kategorienreihenfolge ist zu jeder Zeit möglich. Mit Bindestrichen kann man Unterkategorien anlegen.</p>
		
	<p>Beispiel:</p>
	<pre>
Allgemein :1
News :2
--Bekanntmachungen :5
--Events :3
----Verschiedenes :6
Technik :4
	</pre>',
		'clear'		=> 'Alle Kategorien löschen',
	
		'fset1'		=> 'Editor',
		'fset2'		=> 'Änderungen durchführen',
		'submit'	=> 'Speichern'
	);
	
	$lang['admin']['entry']['cats']['msgs'] = array(
		
		1	=> 'Kategorien gespeichert',
		-1	=> 'Ein Fehler ist beim Speichern der Kategorien aufgetreten',
		2	=> 'Alle Kategorien gelöscht',
		-2	=> 'Ein Fehler ist beim Löschen der Kategorien aufgetreten',
		-3 	=> 'Die Kategorie ID <strong>muss größer als 0 sein</strong>. Der Wert <strong>0</strong> ist nicht erlaubt.'

	);
	
	
		
?>
