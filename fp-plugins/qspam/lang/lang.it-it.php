<?php

$lang['plugin']['qspam'] = array(
	'error' => 'ERRORE: Il commento contiene parole vietate'
);

$lang['admin']['plugin']['submenu']['qspam'] = 'QuickSpamFilter';
$lang['admin']['plugin']['qspam'] = array(
	'head' => 'Configurazione di QuickSpam',
	'desc1' => 'Non consentire commenti che contengono queste parole (scrivine una per riga) :',
	'desc2' => '<strong>Attenzione:</strong> Un commento verrà vietato anche quando una parola fa parte di un\'altra parola. 
	
	(ad esempio. "gomma" corrisponde anche a "s<em>gomma</em>ta" )',
	'options' => 'Altre opzioni',
	'desc3' => 'Conteggio parolacce',
	'desc3pre' => 'Blocca i commenti che contengono più di ',
	'desc3post' => ' parolaccia(e).',
	'submit' => 'Salva la configurazione',
	'msgs' => array(
		1 => 'Parolacce salvate con successo.',
		-1 => 'Le parolacce non sono state salvate.'
	)
);

?>
