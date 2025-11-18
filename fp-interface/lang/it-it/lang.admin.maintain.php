<?php
$lang ['admin'] ['panel'] ['maintain'] = 'Manutenzione';

$lang ['admin'] ['maintain'] ['default'] = array(
	'head' => 'Manutenzione',
	'descr' => 'Vieni qui se pensi che qualcosa sia fuori posto, e forse troverai una soluzione. Tuttavia non sempre potrebbe funzionare.',
	'opt0' => '&laquo; Torna al menu principale',
	'opt1' => 'Ricostruisci l\'indice',
	'opt2' => 'Spurga la cache dei temi e dei modelli',
	'opt3' => 'Ripristino delle autorizzazioni per il funzionamento produttivo',
	'opt4' => 'Visualizza informazioni su PHP',
	'opt5' => 'Controllo aggiornamenti',
	'opt6' => 'Stato della cache APCu',

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

$lang ['admin'] ['maintain'] ['apcu'] = array(
	'head' => 'Cache APCu',
	'descr' => 'Panoramica sull\'utilizzo della memoria condivisa APCu e sull\'efficienza della cache.',
	'status_heading' => 'Stato euristico',
	'status_good' => 'La cache sembra avere dimensioni adeguate al carico di lavoro attuale.',
	'status_bad' => 'Alto tasso di errori o memoria libera molto bassa: la cache APCu potrebbe essere troppo piccola o fortemente frammentata.',
	'hit_rate' => 'Tasso di successo',
	'free_mem' => 'Memoria libera',
	'total_mem' => 'Memoria condivisa totale',
	'used_mem' => 'Memoria utilizzata',
	'avail_mem' => 'Memoria disponibile',
	'memory_type' => 'Tipo di memoria',
	'memory_type_unknown' => 'n/a',
	'num_slots' => 'Numero di slot',
	'num_hits' => 'Numero di successi',
	'num_misses' => 'Numero di errori',
	'cache_type' => 'Tipo di cache',
	'cache_user_only' => 'Cache dei dati utente',
	'legend_good' => 'Verde: la configurazione sembra corretta (alto tasso di successo, memoria libera ragionevole).',
	'legend_bad' => 'Rosso: cache sotto pressione (molti errori o memoria libera quasi esaurita).',
	'no_apcu' => 'APCu non sembra essere abilitato su questo server.',
	'back' => '&laquo; Torna alla manutenzione',
	'clear_fp_button'=> 'Cancella voci APCu FlatPress',
	'clear_fp_confirm' => 'Vuoi davvero eliminare tutte le voci APCu? Questa operazione cancellerà le cache APCu di FlatPress.',
	'clear_fp_result'=> 'Eliminate %d voci APCu.',
	'msgs' => array(
		1  => 'Le voci APCu di FlatPress sono state cancellate.',
		2  => 'Non sono state trovate voci APCu.',
		-1 => 'APCu non è disponibile o non è stato possibile accedervi; non è stato eliminato nulla.'
	)
);
?>
