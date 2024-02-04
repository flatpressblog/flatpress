<?php
$lang ['admin'] ['panel'] ['maintain'] = 'Manutenzione';

$lang ['admin'] ['maintain'] ['default'] = array(
	'head' => 'Manutenzione',
	'descr' => 'Vieni qui se pensi che qualcosa sia fuori posto, e forse troverai una soluzione. Tuttavia non sempre potrebbe funzionare.',
	'opt0' => '&laquo; Torna al menu principale',
	'opt1' => 'Ricostruisci l\'indice',
	'opt2' => 'Spurga la cache dei temi e dei modelli',
	'opt3' => 'Ripristina i permessi dei file',
	'opt4' => 'Visualizza informazioni su PHP',
	'opt5' => 'Controllo aggiornamenti',

	'chmod_info' => 'Se non è stato possibile reimpostare i permessi del file a ' . decoct(FILE_PERMISSIONS) . ', probabilmente il proprietario del file non è lo stesso del server web.<br>' . //
		'Di solito puoi ignorare questo avviso.'
);

$lang ['admin'] ['maintain'] ['default'] ['msgs'] = array(
	1 => 'Operazione completata con successo',
	-1 => 'Operazione non completata con successo'
);

$lang ['admin'] ['maintain'] ['updates'] = array(
	'head' => 'Aggiornamenti',
	'list' => '<ul>
		<li>Hai installato la versione di FlatPress <big>%s</big></li>
		<li>L\'ultima versione stabile di FlatPress è la <big><a href="%s">%s</a></big></li>
		<li>L\'ultima versione non stabile di FlatPress è la <big><a href="%s">%s</a></big></li>
		</ul>',
	'notice' => 'Avviso:'
);

$lang ['admin'] ['maintain'] ['updates'] ['msgs'] = array(
	1 => 'Sono disponibili degli aggionamenti!',
	2 => 'È tutto aggiornato all\'ultima versione',
	-1 => 'Impossibile controllare gli aggiornamenti'
);
?>
