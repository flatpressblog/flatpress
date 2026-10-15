<?php
$lang ['admin'] ['plugin'] ['submenu'] ['bbcode'] = 'BBCode';
$lang ['admin'] ['plugin'] ['bbcode'] = array(
	'head' => 'Configurazione BBCode',
	'desc1' => 'Questo plugin ti consente di usare <a href="https://wiki.flatpress.org/' . //
		'doc:techfaq#bbcode" class="hint" target="_blank">BBCode</a> come markup.',

	'options' => 'Opzioni',

	'editing' => 'Modifica',
	'allow_html' => 'Inline HTML',
	'allow_html_long' => 'Consente di usare l\'HTML insieme a BBCode',
	'toolbar' => 'Barra strumenti',
	'toolbar_long' => 'Abilita la barra strumenti dell\'editor.',

	'other' => 'Altre opzioni',
	'comments' => 'Commenti',
	'comments_long' => 'Consenti di usare BBCode nei commenti',
	'url_new_window' => 'Apri i link in una nuova scheda o finestra',
	'url_new_window_long' => 'Per impostazione predefinita è disattivata e i link si aprono nella stessa finestra. Una destinazione impostata esplicitamente nel BBCode ha la precedenza.',
	'urlmaxlen' => 'Lunghezza massima degli URL',
	'urlmaxlen_long_pre' => 'Accorcia gli URL più lunghi di ',
	'urlmaxlen_long_post'=>' caratteri.',

	'attachsdir' => 'Download di file',
	'attachsdir_long' => 'Non visualizzare la directory di caricamento (fp-content/attachs/) nell\'URL.',

	'submit' => 'Salva la configurazione',
	'msgs' => array(
		1 => 'La configurazione di BBCode è stata salvata con successo.',
		-1 => 'La configurazione di BBCode non è stata salvata.'
	),

	'editor' => array(
		'formatting' => 'Formattazione',
		'textarea' => 'Casella di testo: ',
		'expand' => 'Espandi',
		'expandtitle' => 'Espandi l\'altezza della casella di testo',
		'reduce' => 'Riduci',
		'reducetitle' => 'Riduci l\'altezza della casella di testo',
		'urltitle' => 'URL/ Link (Ctrl+K / ⌘K)',
		'mailtitle' => 'Indirizzo e-mail',
		'boldtitle' => 'Grassetto (Ctrl+B / ⌘B)',
		'italictitle' => 'Corsivo (Ctrl+I / ⌘I)',
		'headlinetitle' => 'Intestazione',
		'fonttitle' => 'Carattere tipografico',
		'underlinetitle' => 'Sottolineato (Ctrl+U / ⌘U)',
		'crossouttitle' => 'Cancellato',
		'unorderedlisttitle' => 'Elenco non ordinato',
		'orderedlisttitle' => 'Elenco ordinato',
		'quotetitle' => 'Citazione',
		'codetitle' => 'Codice',
		'htmltitle' => 'Inserire come codice HTML',
		'help' => 'Guida  di BBCode',
		'file' => 'File: ',
		'image' => 'Immagine: ',
		'gallery' => 'Galleria: ',
		'selection' => '-- Selezione --'
	)
);

$lang ['plugin'] ['bbcode'] = array (
	'go_to' => 'Vai a',

	// Filewrapper get.php
	'error_403' => 'Errore 403',
	'not_send' => 'Il file richiesto non può essere inviato.',
	'error_404' => 'Errore 404',
	'not_found' => 'Il file richiesto non è stato trovato.',
	'file' => 'File',
	'report_error_1' => '',
	'report_error_2' => 'Segnala errore',
	'blog_search_1' => '',
	'blog_search_2' => 'cerca nel blog',
	'start_page_1' => 'o torna alla',
	'start_page_2' => 'pagina iniziale'
);
?>
