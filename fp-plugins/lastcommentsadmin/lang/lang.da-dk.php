<?php
$lang ['plugin'] ['lastcommentsadmin '] ['errors'] = array (
	-1 => 'API-nøgle ikke indstillet. Åbn plugin\'et for at indstille din API-nøgle. Registrer dig på <a href="https://akismet.com/" target="_blank">akismet.com</a> for at få en.'
);

$lang ['admin'] ['plugin'] ['submenu'] ['lastcommentsadmin'] = 'Last Comments Admin';

$lang ['admin'] ['plugin'] ['lastcommentsadmin'] = array(
	'head' => '"Seneste kommentarer" Administrer cache',
	'description' => 'Tømmer og opdaterer indholdet i cache-filen "Seneste kommentarer". ',
	'clear' => 'Ryd cache',
	'cleardescription' => 'Slet den eksisterende cache-fil "Seneste kommentarer". En ny fil vil blive oprettet igen, når du har skrevet en kommentar.',
	'rebuild' => 'Opret ny cache',
	'rebuilddescription' => 'Opret en ny cache-fil. Denne funktion søger efter kommentarer i alle blogindlæg og overfører dem til den nye cache-fil. Det kan tage lidt tid, afhængigt af hvor mange kommentarer der findes!'
);

$lang ['admin'] ['plugin'] ['lastcommentsadmin'] ['msgs'] = array(
	1 => 'Cache blev slettet med succes',
	2 => 'Cache blev fornyet med succes',
	-1 => 'Fejl!',
	-2 => 'Dette plugin kræver LastComment-pluginet, der er integreret i FlatPress. Aktiver det venligst på forhånd i plugin-området!'
);
?>