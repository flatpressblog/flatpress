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
?>
