<?php
	$lang['plugin']['akismet']['errors'] = array (
		-1	=> 'La chiave API non è stata impostata. Esegui il plugin per impostare la chiave API. Registrati su <a href="http://wordpress.com">Wordpress.com</a> per riceverne una'
	);
	
	$lang['admin']['plugin']['submenu']['akismet'] = 'Configurazione di Akismet';
	
	$lang['admin']['plugin']['akismet'] = array(
		'head'		=> 'Configurazione di Akismet',
		'description'=>'Per molte persone, <a href="http://akismet.com/">Akismet</a> riduce enormemente '
					 .'o perfino elimina completamente i commenti e i collegamenti traccianti di spam che compaiono sul proprio sito. '
					 .'Se non hai ancora un profilo su WordPress.com, puoi crearne uno su '.
					 '<a href="http://wordpress.com/api-keys/">WordPress.com</a>.',
		'apikey'	=> 'Chiave API di WordPress.com',
		'whatis'	=> '(<a href="http://faq.wordpress.com/2005/10/19/api-key/">What is this?</a>)',
		'submit'	=> 'Salva chiave API'
	);
	$lang['admin']['plugin']['akismet']['msgs'] = array(
		1		=> 'Chiave API salvata',
		-1		=> 'La chiave API non è valida'
	);
	
?>