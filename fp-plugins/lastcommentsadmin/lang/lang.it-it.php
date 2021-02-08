<?php
	$lang['plugin']['lastcommentsadmin ']['errors'] = array (
		-1	=> 'La chiave API non è stata impostata. Apri il plugin per impostare la tua chiave API. Registrati su <a href="http://wordpress.com">Wordpress.com</a> per riceverne una'
	);

	$lang['admin']['plugin']['submenu']['lastcommentsadmin'] = 'Amministrazione Ultimi commenti';

	$lang['admin']['plugin']['lastcommentsadmin'] = array(
		'head'		=> 'Amministrazione ultimi commenti',
		'description'=>'Svuota e ricostruisci la cache degli ultimi commenti',
		'clear'	=> 'Svuota la cache',
		'cleardescription' => 'Elimina il file della cache degli ultimi commenti. Il nuovo file della cache verrà creato quando verrà inserito un nuovo commento.',
		'rebuild' => 'Ricostruisci la cache',
		'rebuilddescription' => 'Ricostruisci il file della cache degli ultimi commenti. Potrebbe volerci molto tempo. Potrebbe non funzionare proprio. Potrebbe bruciarti il mouse!',
	);
	$lang['admin']['plugin']['lastcommentsadmin']['msgs'] = array(
		1		=> 'La cache è stata eliminata',
		2		=> 'La cache è stata ricostruita!',
		-1		=> 'Errore!',
		-2	   =>  'Questo plugin richiede il plugin LastComments!'
	);
	

?>