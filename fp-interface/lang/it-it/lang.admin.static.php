<?php

	$lang['admin']['static']['submenu'] = array(
		'list'		=> 'Gestione pagine statiche',
		'write'		=> 'Scrivi una pagina statica'
	);

	
	/* main panel */
		
	$lang['admin']['static']['list'] = array(
	
		'head'		=> 'Pagine statiche',
		'descr'		=> 'Seleziona una pagina da modificare o <a href="admin.php?p=static&amp;action=write">aggiungine una nuova</a>.',
	
		'sel'		=> 'Sel', // checkbox
		'date'		=> 'Data',
		'name'		=> 'Pagina',
		'title'		=> 'Titolo',
		'author'	=> 'Autore',
		
		'action'	=> 'Azione',
		'act_view'	=> 'Visualizza',
		'act_del'	=> 'Elimina',
		'act_edit'	=> 'Modifica'		
	);
	
	$lang['admin']['static']['list']['msgs'] = array(
		1	=> 'La pagina è stata salvata con successo',
		-1	=> 'Si è verificato un errore durante il salvataggio 
					della pagina',
		2	=> 'La pagina è stata eliminata con successo',
		-2	=>	 'Si è verificato un errore durante l\'eliminazione 
					della pagina',
	);

	/* write panel */

	$lang['admin']['static']['write'] = 
	array(
		'head'		=> 'Pubblica la pagina statica',
		'descr'		=> 'Modifica il modulo per pubblicare la pagina',
		'fieldset1'	=> 'Modifica',
		'subject'	=> 'Titolo (*):',
		'content'	=> 'Contenuto (*):',
		'fieldset2'	=> 'Invia',
		'pagename'	=> 'Nome della pagina (*):',
		'submit'	=> 'Pubblica',
		'preview'	=> 'Anteprima',

		'delfset'	=> 'Elimina',
		'deletemsg'	=> 'Elimina questa pagina',
		'del'		=> 'Elimina',
		'success'	=> 'La tua pagina è stata pubblicata con successo',
		'otheropts'	=> 'Altre opzioni',
	);
	
	$lang['admin']['static']['write']['error'] = array(
		'subject'	=> 'Non è possinbile lasciare un titolo in bianco',
		'content'	=> 'Non è possibile inserire un contenuto in bianco',
		'id'		=> 'Devi inviare un id valido'
	);
	
	
	/* delete action */	
	$lang['admin']['static']['delete'] = array(
		'head'		=> "Elimina la pagina", 
		'descr'		=> 'Stai per eliminare la seguente pagina:',
		'preview'	=> 'Anteprima',
		'confirm'	=> 'Sei sicuro di voler continuare?',
		'fset'		=> 'Elimina',
		'ok'		=> 'Si, elimina questa pagina',
		'cancel'	=> 'No, fammi tornare al pannello',
		'err'		=> 'La pagina specificata non esiste',
	
	);
	
	
		
?>
