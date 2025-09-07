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
?>
