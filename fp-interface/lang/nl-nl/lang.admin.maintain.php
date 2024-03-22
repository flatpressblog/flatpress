<?php
$lang ['admin'] ['panel'] ['maintain'] = 'Onderhoud';

$lang ['admin'] ['maintain'] ['default'] = array(
	'head' => 'Onderhoud',
	'descr' => 'Kom hier als je denkt dat er iets is verknoeid, en misschien vind je hier een oplossing. Dit werkt echter mogelijk niet.',
	'opt0' => '&laquo; Terug naar hoofdmenu',
	'opt1' => 'Herbouw index',
	'opt2' => 'Thema- en sjablonencache opschonen',
	'opt3' => 'Bestandsmachtigingen herstellen',
	'opt4' => 'Toon info over PHP',
	'opt5' => 'Controleren op updates',

	'chmod_info' => 'Als de bestandsrechten niet konden worden teruggezet naar ' . decoct(FILE_PERMISSIONS) . ', is de eigenaar van het bestand waarschijnlijk niet dezelfde als de eigenaar van de webserver.<br>' . //
		'Meestal kunt u deze kennisgeving negeren.'
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
