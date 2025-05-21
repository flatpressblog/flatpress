<?php


	$lang['admin']['entry']['submenu'] = 
	array (
		'list'		=> 'Gestione Articoli',
		'write'		=> 'Scrivi Articolo',
		'cats'		=> 'Gestione Categorie'
	);


	/* default action */
	
	$lang['admin']['entry']['list'] = 
	array(
		'head'		=> 'Gestione Articoli',
		'descr'		=> 'Seleziona un articolo da modificare o <a href="admin.php?p=entry&amp;action=write">aggiungine uno nuovo</a>'.
					'<br /><a href="admin.php?p=entry&amp;action=cats">Modifica le categorie</a>',
		'filter'	=> 'Filtro: ',
		'nofilter'	=> 'Visualizza tutto',
		'filterbtn'	=> 'Applica filtro',
		'sel'		=> 'Seleziona', // checkbox
		'date'		=> 'Data',
		'title'		=> 'Titolo',
		'author'	=> 'Autore',
		'comms'		=> '#Commenti', // comments
		'action'	=> 'Azione',
		'act_del'	=> 'Elimina',
		'act_view'	=> 'Visualizza',
		'act_edit'	=> 'Modifica'
	);
	
	/* write action */
	$lang['admin']['entry']['write'] = 
	array(
		'head'		=> 'Scrivi articolo',
		'descr'		=> 'Modifica il modulo per scrivere l\'articolo',
		'uploader'	=> 'Caricatore',
		'fieldset1'	=> 'Modifica',
		'subject'	=> 'Titolo (*):',
		'content'	=> 'Contenuto (*):',
		'fieldset2'	=> 'Invia',
		'submit'	=> 'Pubblica',
		'preview'	=> 'Anteprima',
		'savecontinue'	=> 'Salva e continua',
		'categories'	=> 'Categorie',
		'nocategories'	=> 'Nessuna categoria impostata. <a href="admin.php?p=entry&amp;action=cats">Creane una '. 
					'categories</a> dal pannello principale degli articoli. '.
					'<a href="#save">Salva</a> prima l\'articolo.',
		'saveopts'	=> 'Opzioni di salvataggio',
		'success'	=> 'L\'articolo è stato pubblicato con successo',
		'otheropts'	=> 'Altre opzioni',
		'commmsg'	=> 'Gestisci i commenti per questo articolo',
		'delmsg'	=> 'Elimina questo articolo',
		//'back'		=> 'Back discarding changes',
	);
	

	$lang['admin']['entry']['list']['msgs'] = array(
		1	=> 'L\'articolo è stato salvato con successo',
		-1	=> 'Si è verificato un errore durante il salvataggio 
					dell\'articolo',
		2	=> 'L\'articolo è stato elminato con successo',
		-2	=>	 'Si è verificato un errore durante l\'eliminazione 
					dell\'articolo',
	);

	
	$lang['admin']['entry']['write']['error'] = array(
		'subject'	=> 'Non è possibile lasciare un titolo in bianco',
		'content'	=> 'Non è possibile pubblicare un articolo in bianco',
	);
	
	$lang['admin']['entry']['write']['msgs'] = array(
		1	=> 'L\'articolo è stato salvato con successo',
		-1	=> 'Si è verificato un errore: il tuo articolo potrebbe non essere stato salvato con successo',
		-2	=> 'Si è verificato un errore: il tuo articolo non è stato salvato; l\'indice potrebbe essere stato corrotto',
		-3	=> 'Si è verificato un errore: il tuo articolo è stato salvato come bozza',
		-4	=> 'Si è verificato un errore: il tuo articolo è stato salvato come bozza; l\'indice potrebbe essere stato corrotto',
		'draft'=> 'Stai modificando una <strong>bozza</strong> di articolo'
	);

	
	/* comments */
	
	$lang['admin']['entry']['commentlist'] = 
	array(
		'head'		=> "Commenti per l'articolo", 
		'descr'		=> 'Seleziona un commento da eliminare',
		'sel'		=> 'Seleziona',
		'content'	=> 'Contenuto',
		'date'		=> 'Data',
		'author'	=> 'Autore',
		'email'		=> 'Email',
		'ip'		=> 'IP',
		'actions'	=> 'Azioni',
		'act_edit'	=> 'Modifica',
		'act_del'	=> 'Elimina',
		'act_del_confirm' => 'Vuoi davvero eliminare questo commento?',
		'nocomments'	=> 'Questo articolo non è ancora stato commentato.',
		
	
	);

	$lang['admin']['entry']['commentlist']['msgs'] =
	array(
		1	=> 'Il commento è stato eliminato con successo',
		-1	=> 'Si è verificato un errore durante l\'eliminazione 
					del commento',
		
	);

	$lang['admin']['entry']['commedit'] = 
	array(
		'head'		=> "Modifica commento per l'articolo", 
		'content'	=> 'Contenuto',
		'date'		=> 'Data',
		'author'	=> 'Autore',
		'www'		=> 'Sito Web',
		'email'		=> 'Email',
		'ip'		=> 'IP',
		'loggedin'	=> 'Utente registrato',
		'submit'	=> 'Salva'
		
	
	);

	$lang['admin']['entry']['commedit']['msgs'] =
	array(
		1	=> 'Il commento è stato modificato',
		-1	=> 'Si è verifcato un errore durante la modifica del commento',
	);
	
	/* delete action */
	
	$lang['admin']['entry']['delete'] = 
	array(
		'head'		=> 'Elimina articolo', 
		'descr'		=> 'Stai per eliminare il seguente articolo:',
		'preview'	=> 'Anteprima',
		'confirm'	=> 'Sei sicuro di voler continuare?',
		'fset'		=> 'Elimina',
		'ok'		=> 'Si, elimina questo articolo',
		'cancel'	=> 'No, fammi tornare al pannello',
		'err'		=> 'L\'articolo specificato non esiste',
	
	);
	
	/* category mgmt */
	
	$lang['admin']['entry']['cats'] =
	array(
		'head'		=> 'Modifica categorie',
		'descr'		=> '<p>Usa il modulo qui sotto per aggiungere e modificare le tue categorie. </p><p>Ogni elemento della categoria deve avere questo formato "nome categoria: <em>numero_id</em>". Indicizza gli elementi con dei trattini per creare delle gerarchie.</p>
		
	<p>Ad esempio:</p>
	<pre>
Generali :1
Notizie :2
--Annunci :3
--Eventi :4
----Varie :5
Tecnologia :6
	</pre>',
		'clear'		=> 'Elimina tutti i dati delle categorie',
	
		'fset1'		=> 'Editor',
		'fset2'		=> 'Applica le modifiche',
		'submit'	=> 'Salva'
	);
	
	$lang['admin']['entry']['cats']['msgs'] = array(
		
		1	=> 'Categorie salvate',
		-1	=> 'Si è verificato un errore durante il salvataggio delle categorie',
		2	=> 'Categorie eliminate',
		-2	=> 'Si è verificato un errore durante l\'eliminazione delle categorie',
		-3 	=> 'Gli ID delle categorie devono essere assolutamente positivi (lo 0 non è consentito)'

	);
	
	
		
?>
