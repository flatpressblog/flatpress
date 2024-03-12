<?php
$lang ['admin'] ['entry'] ['submenu'] = array(
	'list' => 'Gestione Articoli',
	'write' => 'Scrivi Articolo',
	'cats' => 'Gestione Categorie',
	'stats' => 'Statistiche'
);

/* default action */
$lang ['admin'] ['entry'] ['list'] = array(
	'head' => 'Gestione Articoli',
	'descr' => 'Seleziona un articolo da modificare o <a href="admin.php?p=entry&amp;action=write">aggiungine uno nuovo</a><br>' . //
		'<a href="admin.php?p=entry&amp;action=cats">Modifica le categorie</a>',
	'drafts' => 'Bozze: ',
	'filter' => 'Filtro: ',
	'nofilter' => 'Visualizza tutto',
	'filterbtn' => 'Applica filtro',
	'sel' => 'Seleziona', // checkbox
	'date' => 'Data',
	'title' => 'Titolo',
	'author' => 'Autore',
	'comms' => '#Commenti', // comments
	'action' => 'Azione',
	'act_del' => 'Elimina',
	'act_view' => 'Visualizza',
	'act_edit' => 'Modifica'
);

/* write action */
$lang ['admin'] ['entry'] ['write'] = array(
	'head' => 'Scrivi articolo',
	'descr' => 'Modifica il modulo per scrivere l\'articolo',
	'uploader' => 'Caricatore',
	'fieldset1' => 'Modifica',
	'subject' => 'Titolo (*):',
	'content' => 'Contenuto (*):',
	'fieldset2' => 'Invia',
	'submit' => 'Pubblica',
	'preview' => 'Anteprima',
	'savecontinue' => 'Salva e continua',
	'categories' => 'Categorie',
	'nocategories' => 'Nessuna categoria impostata. <a href="admin.php?p=entry&amp;action=cats">Crea una categoria</a> dal pannello principale degli articoli. ' . //
		'<a href="#save">Salva</a> prima l\'articolo.',
	'saveopts' => 'Opzioni di salvataggio',
	'success' => 'L\'articolo è stato pubblicato con successo',
	'otheropts' => 'Altre opzioni',
	'commmsg' => 'Gestisci i commenti per questo articolo',
	'delmsg' => 'Elimina questo articolo'
	// 'back' => 'Indietro ignorando le modifiche',
);

$lang ['admin'] ['entry'] ['list'] ['msgs'] = array(
	1 => 'L\'articolo è stato salvato con successo',
	-1 => 'Si è verificato un errore durante il salvataggio dell\'articolo',
	2 => 'L\'articolo è stato eliminato con successo',
	-2 => 'Si è verificato un errore durante l\'eliminazione dell\'articolo'
);

$lang ['admin'] ['entry'] ['write'] ['error'] = array(
	'subject' => 'Non è possibile lasciare un titolo in bianco',
	'content' => 'Non è possibile pubblicare un articolo in bianco'
);

$lang ['admin'] ['entry'] ['write'] ['msgs'] = array(
	1 => 'L\'articolo è stato salvato con successo',
	-1 => 'Si è verificato un errore: il tuo articolo potrebbe non essere stato salvato con successo',
	-2 => 'Si è verificato un errore: il tuo articolo non è stato salvato; l\'indice potrebbe essere stato corrotto',
	-3 => 'Si è verificato un errore: il tuo articolo è stato salvato come bozza',
	-4 => 'Si è verificato un errore: il tuo articolo è stato salvato come bozza; l\'indice potrebbe essere stato corrotto',
	'draft' => 'Stai modificando una <strong>bozza</strong> di articolo'
);

/* comments */
$lang ['admin'] ['entry'] ['commentlist'] = array(
	'head' => 'Commenti per l\'articolo: ',
	'descr' => 'Qui è possibile modificare o eliminare i commenti.',
	'sel' => 'Seleziona',
	'content' => 'Contenuto',
	'date' => 'Data',
	'author' => 'Autore',
	'email' => 'Email',
	'ip' => 'IP',
	'actions' => 'Azioni',
	'act_edit' => 'Modifica',
	'act_del' => 'Elimina',
	'act_del_confirm' => 'Vuoi davvero eliminare questo commento?',
	'nocomments' => 'Questo articolo non è ancora stato commentato.'
);

$lang ['admin'] ['entry'] ['commentlist'] ['msgs'] = array(
	1 => 'Il commento è stato eliminato con successo',
	-1 => 'Si è verificato un errore durante l\'eliminazione del commento'
);

$lang ['admin'] ['entry'] ['commedit'] = array(
	'head' => 'Modifica commento per l\'articolo: ',
	'descr' => 'Qui è possibile modificare a piacere il commento, il nome, l\'indirizzo e-mail e il sito web di un autore.<br><br>',
	'content' => 'Contenuto',
	'date' => 'Data',
	'author' => 'Autore',
	'www' => 'Sito Web',
	'email' => 'Email',
	'ip' => 'IP',
	'loggedin' => 'Amministratore collegato',
	'submit' => 'Salva le modifiche',
	'commentlist' => 'Torna alla panoramica dei commenti'
);

$lang ['admin'] ['entry'] ['commedit'] ['error'] = array(
	'name' => 'Il nome non deve mancare.',
	'email' => 'L\'indirizzo e-mail non è corretto.',
	'url' => 'Il sito web non è corretto e deve iniziare con <strong>http://</strong> o <strong>https://</strong>.',
	'content' => 'Il commento non deve mancare.'
);

$lang ['admin'] ['entry'] ['commedit'] ['msgs'] = array(
	1 => 'Il commento è stato modificato',
	-1 => 'Si è verifcato un errore durante la modifica del commento'
);

/* delete action */
$lang ['admin'] ['entry'] ['delete'] = array(
	'head' => 'Elimina articolo',
	'descr' => 'Stai per eliminare il seguente articolo: ',
	'preview' => 'Anteprima',
	'confirm' => 'Sei sicuro di voler continuare?',
	'fset' => 'Elimina',
	'ok' => 'Si, elimina questo articolo',
	'cancel' => 'No, fammi tornare al pannello',
	'err' => 'L\'articolo specificato non esiste'
);

/* category mgmt */
$lang ['admin'] ['entry'] ['cats'] = array(
	'head' => 'Modifica categorie',
	'descr' => '<p>Usa il modulo qui sotto per aggiungere e modificare le tue categorie.</p>' . //
		'<p>Ogni elemento della categoria deve avere questo formato "nome categoria: <em>numero_id</em>". Indicizza gli elementi con dei trattini per creare delle gerarchie.</p>

	<p>Ad esempio:</p>
	<pre>
Generali :1
Notizie :2
--Annunci :3
--Eventi :4
----Varie :5
Tecnologia :6
	</pre>',
	'clear' => 'Elimina tutti i dati delle categorie',

	'fset1' => 'Editor',
	'fset2' => 'Applica le modifiche',
	'submit' => 'Salva'
);

$lang ['admin'] ['entry'] ['cats'] ['msgs'] = array(

	1 => 'Categorie salvate',
	-1 => 'Si è verificato un errore durante il salvataggio delle categorie',
	2 => 'Categorie eliminate',
	-2 => 'Si è verificato un errore durante l\'eliminazione delle categorie',
	-3 => 'Gli ID delle categorie devono essere assolutamente positivi (lo 0 non è consentito)'
);

/* stats */
$lang ['admin'] ['entry'] ['stats'] = array(
	'head' => 'Statistiche',
	'entries' => 'Entrate',
	'you_have' => 'Hai',
	'entries_using' => 'voci con',
	'characters_in' => 'caratteri in',
	'words' => 'parole',
	'total_disk_space_is' => 'Lo spazio di archiviazione totale è di',
	'comments' => 'Commenti',
	'comments_using' => 'commenti con',
	'the' => 'Le',
	'most_commented_entries' => 'articoli più commentati'
);
?>
