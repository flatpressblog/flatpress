<?php

	$lang = array();

	$lang['main'] = array(
		
		'nextpage'		=> 'Pagina successiva &raquo;',
		'prevpage'		=> '&laquo; Pagina precedente',
		'entry'      	=> 'Articolo',
		'static'     	=> 'Pagina statica',
		'comment'    	=> 'Commento',
		'preview'    	=> 'Modifica/Anteprima',
		
		'filed_under'	=> 'Inserito sotto ',	
		
		'add_entry'  	=> 'Aggiungi articolo',
		'add_comment'  	=> 'Aggiungi commento',
		'add_static'  	=> 'Aggiungi pagina statica',
		
		'btn_edit'     	=> 'Modifica',
		'btn_delete'   	=> 'Elimina',
		
		'nocomments'	=> 'Aggiungi un commento',
		'comment'	=> '1 commento',
		'comments'	=> 'commenti',
		
	);
	
	$lang['search'] = array(
		
		'head'	=> 'Cerca',
		'fset1'	=> 'Inserisci un criterio di ricerca',
		'keywords'	=> 'Frase',
		'onlytitles'	=> 'Solo i titoli',
		'fulltext'	=> 'Testo completo',
		
		'fset2'	=> 'Data',
		'datedescr'	=> 'Puoi collegare la tua ricerca ad una data specifica. Puoi selezionare un anno, un anno e un mese o una data completa. '.
					'Lascia in bianco se vuoi cercare nell\'intero database.',
		
		'fset3' 	=> 'Cerca nelle categorie',
		'catdescr'	=> 'Non selezionare nulla per la ricerca completa',
		
		'fset4'	=> 'Inizia la ricerca',
		'submit'	=> 'Cerca',
		
		'headres'	=> 'Risultati della ricerca',
		'descrres'	=> 'La ricerca per <strong>%s</strong> ha dato i seguenti risultati:',
		'descrnores'=> 'La ricerca per <strong>%s</strong> non ha prodotto risultati.',
		
		'moreopts'	=> 'Altre opzioni',
		
		
		'searchag'	=> 'Cerca ancora',
		
	);
	
	$lang['search']['error'] = array(
	
		'keywords'	=> 'Devi specificare almeno una parola chiave'
	
	);
	
	
	
	
	
	$lang['entry'] = array();
	$lang['entry']['flags'] = array();
	
	$lang['entry']['flags']['long'] = array(
		'draft' => '<strong>Bozza di articolo</strong>: nascosta, in attesa di pubblicazione',
		//'static' => '<strong>Static entry</strong>: normally hidden, to reach the entry put ?page=title-of-the-entry in url (experimental)',
		'commslock' => '<strong>Commenti bloccati</strong>: i commenti non sono permessi per questo articolo'
	);
	
	$lang['entry']['flags']['short'] = array(
		'draft' => 'Bozza',
		//'static' => 'Static',
		'commslock' => 'Commenti bloccati'
	);

	$lang['404error'] = array(
		'subject'	=> 'Non Trovato',
		'content'	=> '<p>Spiacenti, non è possibile trovare la pagina che stavi cercando</p>'
	);
		
	// Login
	$lang['login'] = array(
		
		'head'		=> 'Login',
		'fieldset1'	=> 'Inserisci nome utente e password',
		'user'		=> 'Nome utente:',
		'pass'		=> 'Password:',
		'fieldset2'	=> 'Connettiti',
		'submit'	=> 'Login',
		'forgot'	=> 'Password dimenticata'
	);
		
	$lang['login']['success'] = array(
		'success'	=> 'Non sei connesso.',
		'logout'	=> 'Ora sei disconnesso.',
		'redirect'	=> 'Sarai reindirizzato in 5 secondi.',
		'opt1'		=> 'Torna all\'indice',
		'opt2'		=> 'Vai al pannello di controllo',
		'opt3'		=> 'Aggiungi un nuovo articolo'
	);
	
	$lang['login']['error'] = array(
		'user'		=> 'Devi inserire un nome utente.',
		'pass'		=> 'Devi inserire una password.',
		'match'		=> 'La password non è corretta.'
	);
	
	
	$lang['comments'] = array(
		'head'		=> 'Aggiungi un commento',
		'descr'		=> 'Compila il modulo qui sotto per aggiungere i tuoi commenti',
		'fieldset1'	=> 'Dati utente',
		'name'		=> 'Nome (*)',
		'email'		=> 'Email:',
		'www'		=> 'Web:',
		'cookie'	=> 'Ricordami',
		'fieldset2'	=> 'Aggiungi il tuo commento',
		'comment'	=> 'Commento (*):',
		'fieldset3'	=> 'Invia',
		'submit'	=> 'Aggiungi',
		'reset'		=> 'Azzera',
		'success'	=> 'Il tuo commento è stato aggiunto con successo',
		'nocomments'	=> 'Questo articolo non è stato ancora commentato',
		'commslock'	=> 'I commenti sono stati disabilitati per questo articolo',
	);
	
	$lang['comments']['error'] = array(
		'name'		=> 'Devi inserire un nome',
		'email'		=> 'Devi inserire un indirizzo email valido',
		'www'		=> 'Devi inserire un URL valido',
		'comment'	=> 'Devi inserire un commento',
	);
	
	$lang['date']['month'] = array(
		
		'Gennaio',
		'Febbraio',
		'Marzo',
		'Aprile',
		'Maggio',
		'Giugno',
		'Luglio',
		'Agosto',
		'Settembre',
		'Ottobre',
		'Novembre',
		'Dicembre'
		
	);

	$lang['date']['month_abbr'] = array(
		
		'Gen',
		'Feb',
		'Mar',
		'Apr',
		'Mag',
		'Giu',
		'Lug',
		'Ago',
		'Set',
		'Ott',
		'Nov',
		'Dic'
		
	);

	$lang['date']['weekday'] = array(
		
		'Domenica',
		'Lunedì',
		'Martedì',
		'Mercoledì',
		'Giovedì',
		'Venerdì',
		'Sabato',
		
	);

	$lang['date']['weekday_abbr'] = array(
		
		'Dom',
		'Lun',
		'Mar',
		'Mer',
		'Gio',
		'Ven',
		'Sab',
		
	);



?>
