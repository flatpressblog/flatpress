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

	'chmod_info' => 'Se non è stato possibile ripristinare i permessi, probabilmente il proprietario del file/della directory non è lo stesso del server web.<br>' . //
		'
		<table>
			<thead>
				<tr>
					<th>Autorizzazioni</th>
					<th>' . FP_CONTENT . '</th>
					<th>Nucleo</th>
					<th>Tutti gli altri</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>File</td>
					<td>' . decoct(FILE_PERMISSIONS) . '</td>
					<td>' . decoct(CORE_FILE_PERMISSIONS) . '</td>
					<td>' . decoct(RESTRICTED_FILE_PERMISSIONS) . '</td>
				</tr>
				<tr>
					<td>Directory</td>
					<td>' . decoct(DIR_PERMISSIONS) . '</td>
					<td>' . decoct(CORE_DIR_PERMISSIONS) . '</td>
					<td>' . decoct(RESTRICTED_DIR_PERMISSIONS) . '</td>
				</tr>
			</tbody>
		</table>
		',

	'opt3_success' => 'Tutte le autorizzazioni sono state aggiornate con successo.',
	'opt3_error' => 'Errore nell\'impostazione delle autorizzazioni:'
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
