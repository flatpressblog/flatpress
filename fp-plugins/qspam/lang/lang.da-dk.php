<?php
$lang ['plugin'] ['qspam'] = array(
	'error' => 'FEJL: Denne kommentar indeholder forbudte ord'
);

$lang ['admin'] ['entry'] ['submenu'] ['qspam'] = 'QuickSpamFilter';
$lang ['admin'] ['entry'] ['qspam'] = array(
	'head' => 'QuickSpam Konfiguration',
	'desc1' => 'Bloker kommentarer, der indeholder følgende forbudte ord. For hvert nyt ord skal du starte en ny linje:',
	'desc2' => '<strong>Advarsel:</strong> En kommentar vil også blive blokeret, hvis et andet ord indeholder denne streng! (e.g. "old" matches "b<em>old</em>" too)',
	'options' => 'Andre muligheder',
	'desc3' => 'Tæller for forbudte ord',
	'desc3pre' => 'Kommentaren vil blive blokeret, så snart den indeholder mere end ',
	'desc3post' => ' forbudte ord.',
	'submit' => 'Gem konfiguration',
	'msgs' => array(
		1 => 'Forbudte ord gemt med succes.',
		-1 => 'Bemærk: Forbudte ord blev ikke gemt.'
	)
);
?>
