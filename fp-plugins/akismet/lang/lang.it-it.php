<?php
$lang ['plugin'] ['akismet'] ['errors'] = array (
	-1	=> 'La chiave API non è stata impostata. Esegui il plugin per impostare la chiave API. Registrati su <a href="https://akismet.com/signup/" target="_blank">akismet.com</a> per riceverne una.'
);

$lang ['admin'] ['plugin'] ['submenu'] ['akismet'] = 'Configurazione di Akismet';

$lang ['admin'] ['plugin'] ['akismet'] = array(
	'head' => 'Configurazione di Akismet',
	'description' => 'Per molte persone, <a href="https://akismet.com/" target="_blank">Akismet</a> riduce enormemente ' . //
		'o perfino elimina completamente i commenti e i collegamenti traccianti di spam che compaiono sul proprio sito. ' . //
		'Se non hai ancora un profilo su akismet.com, puoi crearne uno su ' . //
		'<a href="https://akismet.com/signup/" target="_blank">akismet.com/signup</a>.',
	'apikey' => 'Chiave API di akismet.com',
	'whatis' => '(<a href="https://akismet.com/support/getting-started/api-key/" target="_blank">What is this?</a>)',
	'submit' => 'Salva chiave API'
);

$lang ['admin'] ['plugin'] ['akismet'] ['msgs'] = array(
	1 => 'Chiave API salvata',
	-1 => 'La chiave API non è valida'
);
?>
