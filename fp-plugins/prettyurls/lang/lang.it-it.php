<?php
$lang ['plugin'] ['prettyurls'] ['errors'] = array (
	-2 => 'Non è possibile trovare o creare un file <code>.htaccess</code> nella tua cartella ' . //
		'root. PrettyURLs potrebbe non funzionare correttamente, vai al pannello di controllo.'
);

$lang ['admin'] ['plugin'] ['submenu'] ['prettyurls'] = 'Configurazione di PrettyURLs';
$lang ['admin'] ['plugin'] ['prettyurls'] = array(
	'head' => 'Configurazione di PrettyURLs',
	'description1' => 'Qui è possibile trasformare gli URL standard di FlatPress in URL belli e SEO-friendly.',
	'fpprotect_is_on' => 'Il plugin PrettyURLs richiede un file .htaccess. ' . //
		'Per creare o modificare questo file, <a href="admin.php?p=plugin&action=default" title="Andate all\'amministrazione del plugin">disattivare</a> il plugin FlatPress Protect. ',
	'fpprotect_is_off' => 'Il plugin FlatPress Protect protegge il file .htaccess da modifiche involontarie. ' . //
		'Potete attivare il plugin <a href="admin.php?p=plugin&action=default" title="Andate all\'amministrazione del plugin">qui</a>!',
	'nginx' => 'PrettyURL con NGINX',
	'wiki_nginx' => 'https://wiki.flatpress.org/res:plugins:prettyurls#nginx',
	'htaccess' => '.htaccess',
	'description2' => 'Questo editor consente di modificare direttamente il file <code>.htaccess</code> necessario per il plugin PrettyUrls.<br>' . //
		'<strong>Nota:</strong> Solo i server web compatibili con NCSA, come Apache, riconoscono il concetto di file .htaccess. ' . //
		'Il software del server è: <strong>' . $_SERVER ['SERVER_SOFTWARE'] . '</strong>',
	'cantsave' => 'Non puoi modificare questo file, perché non è <strong>scrivibile</strong>. Puoi dargli i permessi di scrittura o copiarlo e incollarlo in un file e poi caricarlo.',
	'mode' => 'Modalità',
	'auto' => 'Automatica',
	'autodescr' => 'prova a cercare la migliore scelta per me',
	'pathinfo' => 'Informazioni sul percorso',
	'pathinfodescr' => 'ad es. /index.php/2024/01/01/hello-world/',
	'httpget' => 'HTTP Get',
	'httpgetdescr' => 'ad es. /?u=/2024/01/01/hello-world/',
	'pretty' => 'Pretty',
	'prettydescr' => 'ad es. /2024/01/01/hello-world/',

	'saveopt' => 'Salva le impostazioni',

	'location' => '<strong>Luogo di stoccaggio:</strong> ' . ABS_PATH . '',
	'submit' => 'Salva .htaccess'
);

$lang ['admin'] ['plugin'] ['prettyurls'] ['msgs'] = array(
	1 => '.htaccess è stato salvato con successo',
	-1 => '.htaccess non può essere salvato (hai i permessi di scrittura su <code>' . BLOG_ROOT . '</code>)?',

	2 => 'Impostazioni salvate con successo',
	-2 => 'Si è verificato un errore durante il salvataggio delle impostazioni'
);
?>
