<?php
$lang ['admin'] ['panel'] ['maintain'] = 'Administration';

$lang ['admin'] ['maintain'] ['default'] = array(
	'head' => 'Vedligeholdelse af FlatPress',
	'descr' => 'Denne menu tilbyder flere muligheder for Flatpress-bloggen til at rette nogle ting eller bare tjekke for opdateringer.',
	'opt0' => '&laquo; Tilbage til vedligeholdelse',
	'opt1' => 'Genskab FlatPress-indekset',
	'opt2' => 'Tøm tema- og skabeloncachen',
	'opt3' => 'Gendan rettigheder til filadgang',
	'opt4' => 'Vis PHP-information om webserveren',
	'opt5' => 'Tjek for nye versioner',

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
?>