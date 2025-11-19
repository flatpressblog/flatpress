<?php
$lang ['admin'] ['panel'] ['maintain'] = 'Mantentze-lanak';

$lang ['admin'] ['maintain'] ['default'] = array(
	'head' => 'Mantentze-lanak',
	'descr' => 'Etorri hona zerbait hondatu dela uste duzunean eta agian hemen aurkituko duzu konponbideren bat. Hala ere, baliteke honek ez funtzionatzea.',
	'opt0' => '&laquo; Itzuli',
	'opt1' => 'Berreraiki aurkibidea',
	'opt2' => 'Garbitu gai eta txantiloien cachea',
	'opt3' => 'Berrezarri ekoizpen-funtzionamendurako baimenak',
	'opt4' => 'Erakutsi PHPri buruzko informazioa',
	'opt5' => 'Bilatu eguneraketak',
	'opt6' => 'APCu cachearen egoera',

	'chmod_info' => 'Baimenak berrezarri <strong>ezin badira</strong>, ziurrenik fitxategiaren/direktorioaren jabea ez da web zerbitzariaren jabea bera.<br>' . //
		'
		<table>
			<thead>
				<tr>
					<th>Baimenak</th>
					<th>' . FP_CONTENT . '</th>
					<th>Nukleoa</th>
					<th>Beste guztiak</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>Fitxategiak</td>
					<td>' . decoct(FILE_PERMISSIONS) . '</td>
					<td>' . decoct(CORE_FILE_PERMISSIONS) . '</td>
					<td>' . decoct(RESTRICTED_FILE_PERMISSIONS) . '</td>
				</tr>
				<tr>
					<td>Direktorioak</td>
					<td>' . decoct(DIR_PERMISSIONS) . '</td>
					<td>' . decoct(CORE_DIR_PERMISSIONS) . '</td>
					<td>' . decoct(RESTRICTED_DIR_PERMISSIONS) . '</td>
				</tr>
			</tbody>
		</table>
		',

	'opt3_success' => 'Baimen guztiak behar bezala eguneratu dira.',
	'opt3_error' => 'Errore bat gertatu da baimenak ezartzerakoan:'
);

$lang ['admin'] ['maintain'] ['default'] ['msgs'] = array(
	1 => 'Eragiketa ondo burutu da.',
	-1 => 'Eragiketak huts egin du.'
);

$lang ['admin'] ['maintain'] ['updates'] = array(
	'head' => 'Eguneraketak',
	'list' => '<ul>
		<li>Zure FlatPressen bertsioa: <big>%s</big></li>
		<li>FlatPressen azken bertsio egonkorra honako hau da: <big><a href="%s">%s</a></big></li>
		<li>FlatPressen azken bertsio ezegonkorra honako hau da: <big><a href="%s">%s</a></big></li>
		</ul>',
	'notice' => 'Oharra:'
);

$lang ['admin'] ['maintain'] ['updates'] ['msgs'] = array(
	1 => 'Eguneraketa eskuragarri dago!',
	2 => 'FlatPressen azken bertsioa erabiltzen ari zara.',
	-1 => 'Ezin izan da eguneraketarik aurkitu.'
);

$lang ['admin'] ['maintain'] ['apcu'] = array(
	'head' => 'APCu cachea',
	'descr' => 'APCu memoria partekatuaren erabileraren eta cachearen eraginkortasunaren ikuspegi orokorra.',
	'status_heading' => 'Egoera heuristikoa',
	'status_good' => 'Cachea tamaina egokia duela dirudi uneko lan-kargarako.',
	'status_bad' => 'Huts-tasa handia edo memoria libre oso baxua: APCu cachea txikiegia edo oso zatikatua izan daiteke.',
	'hit_rate' => 'Arrakasta-tasa',
	'free_mem' => 'Memoria librea',
	'total_mem' => 'Partekatutako memoria osoa',
	'used_mem' => 'Erabilitako memoria',
	'avail_mem' => 'Eskuragarri dagoen memoria',
	'memory_type' => 'Memoria mota',
	'memory_type_unknown' => 'e/a',
	'num_slots' => 'Zirrikitu kopurua',
	'num_hits' => 'Asmatze kopurua',
	'num_misses' => 'Huts-kopurua',
	'cache_type' => 'Cache mota',
	'cache_user_only' => 'Erabiltzaile datuen cachea',
	'legend_good' => 'Berdea: konfigurazioa osasuntsu dagoela dirudi (arrakasta-tasa handia, memoria libre arrazoizkoa).',
	'legend_bad' => 'Gorria: cachea presiopean (huts asko edo ia memoria librerik ez).',
	'no_apcu' => 'APCu ez dirudi gaituta dagoela zerbitzari honetan.',
	'back' => '&laquo; Mantentze-lanetara itzuli',
	'clear_fp_button'=> 'Garbitu FlatPress APCu sarrerak',
	'clear_fp_confirm' => 'Benetan ezabatu nahi dituzu APCu sarrera guztiak? Honek FlatPress-en APCu cacheak garbituko ditu.',
	'clear_fp_result'=> '%d APCu sarrera ezabatu dira.',
	'msgs' => array(
		1  => 'FlatPress APCu sarrerak garbitu dira.',
		2  => 'Ez da APCu sarrerarik aurkitu.',
		-1 => 'APCu ez dago erabilgarri edo ezin izan da atzitu; ez da ezer ezabatu.'
	)
);
?>
