<?php
$lang ['admin'] ['panel'] ['maintain'] = 'Administration';

$lang ['admin'] ['maintain'] ['default'] = array(
	'head' => 'Vedligeholdelse af FlatPress',
	'descr' => 'Denne menu tilbyder flere muligheder for Flatpress-bloggen til at rette nogle ting eller bare tjekke for opdateringer.',
	'opt0' => '&laquo; Tilbage til vedligeholdelse',
	'opt1' => 'Genskab FlatPress-indekset',
	'opt2' => 'Tøm tema- og skabeloncachen',
	'opt3' => 'Genopret tilladelser til produktiv drift',
	'opt4' => 'Vis PHP-information om webserveren',
	'opt5' => 'Tjek for nye versioner',
	'opt6' => 'APCu-cache-status',

	'chmod_info' => 'Hvis tilladelserne <strong>ikke</strong> kunne nulstilles, er ejeren af filen/ mappen sandsynligvis ikke den samme som ejeren af webserveren.<br>' . //
		'
		<table>
			<thead>
				<tr>
					<th>Autorisationer</th>
					<th>' . FP_CONTENT . '</th>
					<th>Kerne</th>
					<th>Alle andre</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>Filer</td>
					<td>' . decoct(FILE_PERMISSIONS) . '</td>
					<td>' . decoct(CORE_FILE_PERMISSIONS) . '</td>
					<td>' . decoct(RESTRICTED_FILE_PERMISSIONS) . '</td>
				</tr>
				<tr>
					<td>Mapper</td>
					<td>' . decoct(DIR_PERMISSIONS) . '</td>
					<td>' . decoct(CORE_DIR_PERMISSIONS) . '</td>
					<td>' . decoct(RESTRICTED_DIR_PERMISSIONS) . '</td>
				</tr>
			</tbody>
		</table>
		',

	'opt3_success' => 'Alle autorisationer er blevet opdateret.',
	'opt3_error' => 'Fejl ved indstilling af autorisationer:'
);

$lang ['admin'] ['maintain'] ['default'] ['msgs'] = array(
	1 => 'Handling udført.',
	-1 => 'Handling mislukt.'
);

$lang ['admin'] ['maintain'] ['updates'] = array(
	'head' => 'Opdateringer',
	'list' => '<ul>
		<li>Du har FlatPress-versionen <big>%s</big></li>
		<li>Den sidste stabile FlatPress-version er <big><a href="%s">%s</a></big></li>
		<li>Den seneste udviklingsversion af FlatPress er <big><a href="%s">%s</a></big></li>
		</ul>',
	'notice' => 'Et tip:'
);

$lang ['admin'] ['maintain'] ['updates'] ['msgs'] = array(
	1 => 'Opdateringer er tilgængelige!',
	2 => 'Du bruger allerede den aktuelle version',
	-1 => 'Fejl: Der kunne ikke hentes opdateringsoplysninger'
);

$lang ['admin'] ['maintain'] ['apcu'] = array(
	'head' => 'APCu cache',
	'descr' => 'Oversigt over APCu-delt hukommelsesforbrug og cache-effektivitet.',
	'status_heading' => 'Heuristisk status',
	'status_good' => 'Cachen synes at have en passende størrelse til den aktuelle arbejdsbyrde.',
	'status_bad' => 'Høj fejlrate eller meget lav ledig hukommelse: APCu-cachen er muligvis for lille eller meget fragmenteret.',
	'hit_rate' => 'Hitrate',
	'free_mem' => 'Ledig hukommelse',
	'total_mem' => 'Samlet delt hukommelse',
	'used_mem' => 'Brugt hukommelse',
	'avail_mem' => 'Tilgængelig hukommelse',
	'memory_type' => 'Hukommelsestype',
	'memory_type_unknown' => 'n/a',
	'num_slots' => 'Antal slots',
	'num_hits' => 'Antal hits',
	'num_misses' => 'Antal fejl',
	'cache_type' => 'Cache-type',
	'cache_user_only' => 'Brugerdatacache',
	'legend_good' => 'Grøn: konfigurationen ser sund ud (høj hitrate, rimelig ledig hukommelse).',
	'legend_bad' => 'Rød: cache under pres (mange fejl eller næsten ingen ledig hukommelse).',
	'no_apcu' => 'APCu ser ikke ud til at være aktiveret på denne server.',
	'back' => '&laquo; Tilbage til vedligeholdelse',
	'clear_fp_button'=> 'Ryd FlatPress APCu-poster',
	'clear_fp_confirm' => 'Vil du virkelig slette alle APCu-poster? Dette vil rydde FlatPress\' APCu-cacher.',
	'clear_fp_result'=> 'Slet %d APCu-poster.',
	'msgs' => array(
		1  => 'FlatPress APCu-poster er blevet ryddet.',
		2  => 'Der blev ikke fundet nogen APCu-poster.',
		-1 => 'APCu er ikke tilgængelig eller kunne ikke tilgås; intet blev slettet.'
	)
);
?>