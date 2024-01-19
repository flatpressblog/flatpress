<?php
$baseurl = BLOG_BASEURL;

$lang ['contact'] = array(
	'head' => 'Contattaci',
	'descr' => 'Compila il modulo qui sotto per dirci cosa ne pensi. Aggiungi il tuo indirizzo email se vuoi avere una risposta.',
	'fieldset1' => 'Dati utente',
	'name' => 'Nome (*)',
	'email' => 'Email:',
	'www' => 'Web:',
	'cookie' => 'Ricordami',
	'fieldset2' => 'Il tuo messaggio',
	'comment' => 'Messaggio (*):',
	'fieldset3' => 'Invia',
	'submit' => 'Invia',
	'reset' => 'Azzera',
	'loggedin' => 'Sei connesso 😉. <a href="' . $baseurl . 'login.php?do=logout">Uscire</a> o accedere <a href="' . $baseurl . 'admin.php">all\'area amministrativa</a>.'
);

$lang ['contact'] ['notification'] = array(
	'name' => 'Nome:',
	'email' => 'Email:',
	'www' => 'Web:',
	'content' => 'Messaggio:',
	'subject' => 'Contatto inviato tramite '
);

$lang ['contact'] ['error'] = array(
	'name' => 'Devi inserire un nome',
	'email' => 'Devi inserire un indirizzo email valido',
	'www' => 'Devi inserire un URL valido',
	'content' => 'Devi inserire un messaggio'
);

$lang ['contact'] ['msgs'] = array(
	1 => 'Il messaggio è stato inviato con successo',
	-1 => 'Il messaggio non è stato inviato'
);
?>