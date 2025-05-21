<?php

	$lang['admin']['static']['submenu'] = array(
		'list'		=> 'Beheer Statische pagina',
		'write'		=> 'Schrijf Statische pagina'
	);

	
	/* main panel */
		
	$lang['admin']['static']['list'] = array(
	
		'head'		=> 'Statische paginas',
		'descr'		=> 'Selecteer een pagina om te bewerken of <a href="admin.php?p=static&amp;action=write">voeg nieuw toe</a>.',
	
		'sel'		=> 'Selecteer', // checkbox
		'date'		=> 'Datum',
		'name'		=> 'Pagina',
		'title'		=> 'Titel',
		'author'	=> 'Auteur',
		
		'action'	=> 'Actie',
		'act_view'	=> 'Toon',
		'act_del'	=> 'Verwijder',
		'act_edit'	=> 'Bewerk'		
	);
	
	$lang['admin']['static']['list']['msgs'] = array(
		1	=> 'Pagina is opgeslagen',
		-1	=> 'Er is een fout opgetreden tijdens het opslaan van de pagina',
		2	=> 'Pagina is verwijderd',
		-2	=> 'Er is een fout opgetreden tijdens het verwijderen van de pagina',
	);

	/* write panel */

	$lang['admin']['static']['write'] = 
	array(
		'head'		=> 'Statische pagina publiceren',
		'descr'		=> 'Het formulier bewerken om de pagina te publiceren',
		'fieldset1'	=> 'Bewerk',
		'subject'	=> 'Onderwerp (*):',
		'content'	=> 'Inhoud (*):',
		'fieldset2'	=> 'Opslaan',
		'pagename'	=> 'Pagina Naam (*):',
		'submit'	=> 'Publiceer',
		'preview'	=> 'Voorbeeld',

		'delfset'	=> 'Verwijder',
		'deletemsg'	=> 'Verwijder deze pagina',
		'del'		=> 'Verwijder',
		'success'	=> 'Uw pagina is succesvol gepubliceerd',
		'otheropts'	=> 'Andere opties',
	);
	
	$lang['admin']['static']['write']['error'] = array(
		'subject'	=> 'U kunt geen leeg onderwerp verzenden',
		'content'	=> 'U kunt geen lege inhoud verzenden',
		'id'		=> 'U moet een geldig ID verzenden'
	);
	
	
	/* delete action */	
	$lang['admin']['static']['delete'] = array(
		'head'		=> "Verwijder pagina", 
		'descr'		=> 'U staat op het punt de volgende pagina te verwijderen:',
		'preview'	=> 'Voorbeeld',
		'confirm'	=> 'Weet u zeker dat u door wilt gaan?',
		'fset'		=> 'Verwijder',
		'ok'		=> 'Ja, verwijder deze pagina',
		'cancel'	=> 'Nee, breng me terug naar het menu.',
		'err'		=> 'De opgegeven pagina bestaat niet',
	
	);
	
	
		
?>
