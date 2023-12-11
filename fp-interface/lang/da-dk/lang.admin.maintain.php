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

	'chmod_info' => 'Filadgangsrettighederne <strong>kunne ikke være nulstilles til standardværdierne på 0775.' . //
		'Normalt kan du ignorere dette tip.'
);

$lang ['admin'] ['maintain'] ['default'] ['msgs'] = array(
	1 => 'Handling udført.'
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