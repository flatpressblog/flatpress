<?php

	$lang['admin']['static']['submenu'] = array(
		'list'		=> 'Verwaltung statischer Seiten',
		'write'		=> 'Erstellen statischer Seiten'
	);

	
	/* main panel */
		
	$lang['admin']['static']['list'] = array(
	
		'head'		=> 'Statische Seiten verwalten',
		'descr'		=> 'Dieses Menü dient zum Bearbeiten von statischen Seiten oder um eine <a href="admin.php?p=static&amp;action=write">neue statische Seite</a> zu erstellen.',
	
		'sel'		=> 'Sel', // checkbox
		'date'		=> 'Datum',
		'name'		=> 'Seitenname',
		'title'		=> 'Titel',
		'author'	=> 'Autor',
		
		'action'	=> 'Aktion',
		'act_view'	=> 'Anzeigen',
		'act_del'	=> 'Löschen',
		'act_edit'	=> 'Bearbeiten'		
	);
	
	$lang['admin']['static']['list']['msgs'] = array(
		1	=> 'Statische Seite erfolgreich gespeichert',
		-1	=> 'Ein Fehler ist beim Speichern der Seite aufgetreten',
		2	=> 'Seite wurde erfolgreich gelöscht',
		-2	=>	 'Ein Fehler ist beim Löschen der Seite aufgetreten',
	);

	/* write panel */

	$lang['admin']['static']['write'] = 
	array(
		'head'		=> 'Statische Seite erstellen',
		'descr'		=> 'Bearbeiten der Parameter, um diese Seite zu erstellen',
		'fieldset1'	=> 'Bearbeiten',
		'subject'	=> 'Titel (*):',
		'content'	=> 'Inhalt (*):',
		'fieldset2'	=> 'Seitenname eingeben',
		'pagename'	=> 'Statische Seite speichern als (*):',
		'submit'	=> 'Seite speichern',
		'preview'	=> 'Vorschau',

		'delfset'	=> 'Löschen',
		'deletemsg'	=> 'Löschen der Seite',
		'del'		=> 'Löschen',
		'success'	=> 'Die Seite wurde erfolgreich gespeichert',
		'otheropts'	=> 'Andere Optionen',
	);
	
	$lang['admin']['static']['write']['error'] = array(
		'subject'	=> 'Es wurde kein Titel angegeben',
		'content'	=> 'Es ist kein Inhalt vorhanden',
		'id'		=> 'Kein Seitenname für die statische Seite angegeben'
	);
	
	
	/* delete action */	
	$lang['admin']['static']['delete'] = array(
		'head'		=> "Seite löschen", 
		'descr'		=> 'Möchtest du diese Seite wirklich löschen?',
		'preview'	=> 'Vorschau',
		'confirm'	=> 'Bist du sicher?',
		'fset'		=> 'Löschen',
		'ok'		=> 'Ja, diese Seite löschen',
		'cancel'	=> 'Nein, zurück zur Verwaltung',
		'err'		=> 'Die ausgewählte Seite existiert nicht',
	
	);
	
	
		
?>
