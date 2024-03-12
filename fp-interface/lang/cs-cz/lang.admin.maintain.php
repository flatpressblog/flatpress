<?php
$lang ['admin'] ['panel'] ['maintain'] = 'Údržba';

$lang ['admin'] ['maintain'] ['default'] = array(
	'head' => 'Údržba',
	'descr' => 'Vstupte pokud si myslíte, že se něco pokazilo, a možná najdete řešení. Nemusí to ale pomoci.',
	'opt0' => '&laquo; Návrat do hlavního menu',
	'opt1' => 'Opravit index',
	'opt2' => 'Vyčistit cache motivů a šablon',
	'opt3' => 'Obnovit oprávnění souborů',
	'opt4' => 'Ukázat informace o PHP',
	'opt5' => 'Zjistit aktualizace',
	'opt6' => 'Zobrazit údaje o podpoře',

	'chmod_info' => 'Pokud se oprávnění souboru <strong>nepodařilo</strong> obnovit na ' . decoct(FILE_PERMISSIONS) . ', vlastník souboru pravděpodobně není stejný jako vlastník webového serveru.<br>' . //
		'Případně můžete ignorovat toto oznámení.'
);

$lang ['admin'] ['maintain'] ['default'] ['msgs'] = array(
	1 => 'Operace kompletní',
	-1 => 'Operace se nezdařila'
);

$lang ['admin'] ['maintain'] ['updates'] = array(
	'head' => 'Aktualizace',
	'list' => '<ul>
		<li>Verze FlatPressu <big>%s</big></li>
		<li>Poslední stabilní verze FlatPressu je <big><a href="%s">%s</a></big></li>
		<li>Poslední nestabilní verze FlatPressu je <big><a href="%s">%s</a></big></li>
		</ul>',
	'notice' => 'Oznámení:'
);

$lang ['admin'] ['maintain'] ['updates'] ['msgs'] = array(
	1 => 'Aktualizace jsou dostupné!',
	2 => 'Verze Flatpressu je aktuální',
	-1 => 'Nepodařilo sa získat aktualizace'
);
?>
