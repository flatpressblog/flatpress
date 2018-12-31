<?php

$lang['plugin']['qspam'] = array(
	'error' => 'ERROR: Dieses Kommentar beinhaltet verbotene Wörter'
);

$lang['admin']['plugin']['submenu']['qspam'] = 'QuickSpamFilter';
$lang['admin']['plugin']['qspam'] = array(
	'head' => 'QuickSpam Konfiguration',
	'desc1' => 'Blocke Kommentare die folgende verbotene Wörter enthalten. Für jedes neue Wort bitte eine neue Zeile beginnen:',
	'desc2' => '<strong>Warnung:</strong> Ein Kommentar wird auch geblockt, wenn ein anderes Wort diese Zeichenfolge enthält! 
	
	(Beispiel: "alt" ist auch Teil von "h<strong>alt</strong>")',
	'options' => 'Andere Optionen',
	'desc3' => 'Zähler für verbotene Wörter',
	'desc3pre' => 'Das Kommentar wird geblockt sobald mehr als ',
	'desc3post' => ' verbotene Wörter enthalten sind.',
	'submit' => 'Konfiguration speichern',
	'msgs' => array(
		1 => 'Verbotene Wörter erfolgreich gespeichert.',
		-1 => 'Hinweis: Verbotene Wörter wurden nicht gespeichert.'
	)
);

?>
