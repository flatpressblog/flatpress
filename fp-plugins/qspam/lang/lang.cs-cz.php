<?php

$lang['plugin']['qspam'] = array(
	'error' => 'ERROR: Komentář obsahoval zakázané výrazy'
);

$lang['admin']['plugin']['submenu']['qspam'] = 'QuickSpamFilter';
$lang['admin']['plugin']['qspam'] = array(
	'head' => 'Nastavení QuickSpam',
	'desc1' => 'Nepovolit komentáře, které obsahují tato slova (napsat jedno na řádek) :',
	'desc2' => '<strong>Výstraha:</strong> Komentář bude zakázán i když jedno slovo je součástí druhého.', 
	'options' => 'Ostatní volby',
	'desc3' => 'Počet zakázaných slov',
	'desc3pre' => 'Blokovat kommentář obsahující více než ',
	'desc3post' => ' zakázaných slov.',
	'submit' => 'Uložit konfiguraci',
	'msgs' => array(
		1 => 'Zakázaná slova úspěšně uložena.',
		-1 => 'Zakázaná slova neuložena.'
	)
);

?>
