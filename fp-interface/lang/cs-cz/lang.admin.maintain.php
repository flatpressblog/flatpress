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

	'chmod_info' => 'Pokud se oprávnění <strong>nepodařilo</strong> obnovit, vlastník souboru/adresáře pravděpodobně není stejný jako vlastník webového serveru.<br>' . //
		'
		<table>
			<thead>
				<tr>
					<th>Oprávnění</th>
					<th>' . FP_CONTENT . '</th>
					<th>Jádro</th>
					<th>Všechny ostatní</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>Soubory</td>
					<td>' . decoct(FILE_PERMISSIONS) . '</td>
					<td>' . decoct(CORE_FILE_PERMISSIONS) . '</td>
					<td>' . decoct(RESTRICTED_FILE_PERMISSIONS) . '</td>
				</tr>
				<tr>
					<td>Adresáře</td>
					<td>' . decoct(DIR_PERMISSIONS) . '</td>
					<td>' . decoct(CORE_DIR_PERMISSIONS) . '</td>
					<td>' . decoct(RESTRICTED_DIR_PERMISSIONS) . '</td>
				</tr>
			</tbody>
		</table>
		',

	'opt3_success' => 'Všechna povolení byla úspěšně aktualizována.',
	'opt3_error' => 'Chyba při nastavování oprávnění:'
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
