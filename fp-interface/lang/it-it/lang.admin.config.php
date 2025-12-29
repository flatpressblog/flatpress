<?php
$lang ['admin'] ['config'] ['default'] = array(
	'head' => 'Opzioni',
	'descr' => 'Personalizza e configura la tua installazione di FlatPress.',
	'submit' => 'Salva le modifiche',

	'sysfset' => 'Informazioni generali di sistema',
	'syswarning' => '<big>Attenzione!</big> Queste informazioni sono critiche e devono essere corrette, oppure FlatPress (probabilmente) si rifiuterà di funzionare.',
	'blog_root' => '<strong>Percorso assoluto di FlatPress</strong>. N.B.: ' . //
		'generalmente non dovrai modificarlo, ma comunque fai attenzione, perché non è possibile controllare se è corretto oppure no.',
	'www' =>'<strong>Root del Blog</strong>. L\'URL al tuo blog, completo di sottocartelle.<br>' . //
		'ad es.: http://www.ilmiosito.it/flatpress/ (sono necesssarie anche le slash /)',

	// ------
	'gensetts' => 'Impostazioni generali',
	'adminname' => 'Nome dell\'amministratore',
	'adminpassword' => 'Nuova password',
	'adminpasswordconfirm' => 'Ripetizione della password',
	'blogtitle' => 'Titolo del Blog',
	'blogsubtitle' => 'Sottotitolo del Blog',
	'blogfooter' => 'Pie\' di pagina del Blog',
	'blogauthor' => 'Autore del Blog',
	'startpage' => 'La home page di questo sito è',
	'stdstartpage' => 'il mio blog (predefinita)',
	'blogurl' => 'URL del Blog',
	'blogemail' => 'Indirizzo e-mail del Blog',
	'notifications' => 'Notifiche',
	'mailnotify' => 'Abilita le notifiche via e-mail per i commenti',
	'blogmaxentries' => 'Numero di articoli per pagina',
	'langchoice' => 'Lingua',

	'intsetts' => 'Impostazioni internazionali',
	'utctime' => 'L\'orario <abbr title="Universal Coordinated Time">UTC</abbr> è',
	'timeoffset' => 'Le ore dovrebbero differire di',
	'hours' => 'ore',
	'timeformat' => 'Formato predefinito per l\'orario',
	'dateformat' => 'Formato predefinito per la data',
	'dateformatshort' => 'Formato predefinito per la data (breve)',
	'output' => 'Output',
	'charset' => 'Set di caratterit',
	'charsettip' => 'Il set di caratteri che userai per scrivere nel blog (UTF-8 è quello ' . //
		'<a class="hint" href="https://wiki.flatpress.org/doc:techfaq#character_encoding" target="_blank" title="Quali standard di codifica dei caratteri sono supportati da FlatPress?">raccomandato</a>).'
);

$lang ['admin'] ['config'] ['default'] ['msgs'] = array(
	1 => 'La configurazione è stata salvata con successo.',
	2 => 'L\'amministratore è stato cambiato. Ora sarete disconnessi.',
	-1 => 'Si è verificato un errore durante il salvataggio della configurazione.'
);

$lang ['admin'] ['config'] ['default'] ['error'] = array(
	'www' => 'La root del blog deve essere un URL valido',
	'title' => 'Devi inserire un titolo',
	'email' => 'L\'e-mail deve avere un formato valido',
	'maxentries' => 'Non hai inserito un numero valido di articoli',
	'timeoffset' => 'Non hai inserito un offset di orario valido! ' . //
		'Puoi usare la virgola mobile (ad es. 2h30" => 2.5)',
	'timeformat' => 'Devi inserire un formato di stringa per l\'ora',
	'dateformat' => 'Devi inserire un formato di stringa per la data',
	'dateformatshort' => 'Devi inserire un formato di stringa per la data (breve)',
	'charset' => 'Devi inserire un id per il set di caratteri',
	'lang' => 'La lingua che hai scelto non è disponibile',
	'admin' => 'Il nome dell\'amministratore può contenere solo lettere, numeri e 1 trattino basso.',
	'password' => 'La password deve contenere almeno 6 caratteri e non deve contenere spazi.',
	'confirm_password' => 'Le password non corrispondono.'
);
?>
