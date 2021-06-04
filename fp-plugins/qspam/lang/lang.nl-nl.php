<?php

$lang['plugin']['qspam'] = array(
	'error' => 'ERROR: Het commentaar bevatte verboden woorden'
);

$lang['admin']['plugin']['submenu']['qspam'] = 'QuickSpamFilter';
$lang['admin']['plugin']['qspam'] = array(
	'head' => 'QuickSpam Configuratie',
	'desc1' => 'Commentaren met deze woorden niet toestaan (schrijf er één per regel) :',
	'desc2' => '<strong>Waarschuwing:</strong> Een commentaar wordt niet toegestaan, zelfs als het ene woord deel uitmaakt van een ander woord. 
	
	(e.g. "old" matches "b<em>old</em>" too)',
	'options' => 'Andere opties',
	'desc3' => 'Slecht aantal woorden',
	'desc3pre' => 'Commentaar blokkeren die meer dan ',
	'desc3post' => ' slecht woorden.',
	'submit' => 'Bewaar configuratie',
	'msgs' => array(
		1 => 'Slechte woorden bewaard.',
		-1 => 'Slechte woorden niet bewaard.'
	)
);

?>
