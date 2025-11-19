<?php
$lang ['admin'] ['panel'] ['maintain'] = 'Onderhoud';

$lang ['admin'] ['maintain'] ['default'] = array(
	'head' => 'Onderhoud',
	'descr' => 'Kom hier als je denkt dat er iets is verknoeid, en misschien vind je hier een oplossing. Dit werkt echter mogelijk niet.',
	'opt0' => '&laquo; Terug naar hoofdmenu',
	'opt1' => 'Herbouw index',
	'opt2' => 'Thema- en sjablonencache opschonen',
	'opt3' => 'Machtigingen voor productieve werking herstellen',
	'opt4' => 'Toon info over PHP',
	'opt5' => 'Controleren op updates',
	'opt6' => 'APCu-cachetstatus',

	'chmod_info' => 'Als de machtigingen <strong>niet</strong> konden worden gereset, is de eigenaar van het bestand/de map waarschijnlijk niet dezelfde als de eigenaar van de webserver.<br>' . //
		'
		<table>
			<thead>
				<tr>
					<th>Machtigingen</th>
					<th>' . FP_CONTENT . '</th>
					<th>Kern</th>
					<th>Alle andere</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>Bestanden</td>
					<td>' . decoct(FILE_PERMISSIONS) . '</td>
					<td>' . decoct(CORE_FILE_PERMISSIONS) . '</td>
					<td>' . decoct(RESTRICTED_FILE_PERMISSIONS) . '</td>
				</tr>
				<tr>
					<td>Mappen</td>
					<td>' . decoct(DIR_PERMISSIONS) . '</td>
					<td>' . decoct(CORE_DIR_PERMISSIONS) . '</td>
					<td>' . decoct(RESTRICTED_DIR_PERMISSIONS) . '</td>
				</tr>
			</tbody>
		</table>
		',

	'opt3_success' => 'Alle machtigingen zijn succesvol bijgewerkt.',
	'opt3_error' => 'Fout bij het instellen van de machtigingen:'
);

$lang ['admin'] ['maintain'] ['default'] ['msgs'] = array(
	1 => 'Operation completed',
	-1 => 'Operatie mislukt'
);

$lang ['admin'] ['maintain'] ['updates'] = array(
	'head' => 'Updates',
	'list' => '<ul>
		<li>Je hebt FlatPress versie <big>%s</big></li>
		<li>Laatste stabiele versie voor FlatPress is <big><a href="%s">%s</a></big></li>
		<li>Laatste onstabiele versie voor FlatPress is <big><a href="%s">%s</a></big></li>
		</ul>',
	'notice' => 'Kennisgeving:'
);

$lang ['admin'] ['maintain'] ['updates'] ['msgs'] = array(
	1 => 'Er zijn updates beschikbaar!',
	2 => 'U bent al up-to-date',
	-1 => 'Kan updates niet ophalen'
);

$lang ['admin'] ['maintain'] ['apcu'] = array(
	'head' => 'APCu-cache',
	'descr' => 'Overzicht van het gebruik van gedeeld geheugen en de cache-efficiëntie van APCu.',
	'status_heading' => 'Heuristische status',
	'status_good' => 'De cache lijkt voldoende groot voor de huidige werklast.',
	'status_bad' => 'Hoog percentage missers of zeer weinig vrij geheugen: APCu-cache is mogelijk te klein of sterk gefragmenteerd.',
	'hit_rate' => 'Hitpercentage',
	'free_mem' => 'Vrij geheugen',
	'total_mem' => 'Totaal gedeeld geheugen',
	'used_mem' => 'Gebruikt geheugen',
	'avail_mem' => 'Beschikbaar geheugen',
	'memory_type' => 'Geheugentype',
	'memory_type_unknown' => 'n.v.t.',
	'num_slots' => 'Aantal slots',
	'num_hits' => 'Aantal hits',
	'num_misses' => 'Aantal missers',
	'cache_type' => 'Cachetype',
	'cache_user_only' => 'Gebruikersgegevenscache',
	'legend_good' => 'Groen: configuratie lijkt in orde (hoog hitpercentage, redelijk vrij geheugen).',
	'legend_bad' => 'Rood: cache staat onder druk (veel missers of bijna geen vrij geheugen).',
	'no_apcu' => 'APCu lijkt niet ingeschakeld te zijn op deze server.',
	'back' => '&laquo; Terug naar onderhoud',
	'clear_fp_button'=> 'FlatPress APCu-vermeldingen wissen',
	'clear_fp_confirm' => 'Wilt u echt alle APCu-vermeldingen verwijderen? Hiermee worden de APCu-caches van FlatPress gewist.',
	'clear_fp_result'=> '%d APCu-vermeldingen verwijderd.',
	'msgs' => array(
		1  => 'FlatPress APCu-vermeldingen zijn gewist.',
		2  => 'Er zijn geen APCu-vermeldingen gevonden.',
		-1 => 'APCu is niet beschikbaar of kon niet worden geopend; er is niets verwijderd.'
	)
);
?>
