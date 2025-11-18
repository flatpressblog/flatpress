<?php
$lang ['admin'] ['panel'] ['maintain'] = 'Údržba';

$lang ['admin'] ['maintain'] ['default'] = array(
	'head' => 'Údržba',
	'descr' => 'Vstupte pokud si myslíte, že se něco pokazilo, a možná najdete řešení. Nemusí to ale pomoci.',
	'opt0' => '&laquo; Návrat do hlavního menu',
	'opt1' => 'Opravit index',
	'opt2' => 'Vyčistit cache motivů a šablon',
	'opt3' => 'Obnovení oprávnění k produktivnímu provozu',
	'opt4' => 'Ukázat informace o PHP',
	'opt5' => 'Zjistit aktualizace',
	'opt6' => 'Stav mezipaměti APCu',

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

$lang ['admin'] ['maintain'] ['apcu'] = array(
	'head' => 'Mezipaměť APCu',
	'descr' => 'Přehled využití sdílené paměti APCu a účinnosti mezipaměti.',
	'status_heading' => 'Heuristický stav',
	'status_good' => 'Velikost mezipaměti se jeví jako vhodná pro aktuální pracovní zátěž.',
	'status_bad' => 'Vysoká míra chyb nebo velmi málo volné paměti: mezipaměť APCu může být příliš malá nebo silně fragmentovaná.',
	'hit_rate' => 'Míra zásahů',
	'free_mem' => 'Volná paměť',
	'total_mem' => 'Celková sdílená paměť',
	'used_mem' => 'Použitá paměť',
	'avail_mem' => 'Dostupná paměť',
	'memory_type' => 'Typ paměti',
	'memory_type_unknown' => 'n/a',
	'num_slots' => 'Počet slotů',
	'num_hits' => 'Počet zásahů',
	'num_misses' => 'Počet chyb',
	'cache_type' => 'Typ mezipaměti',
	'cache_user_only' => 'Mezipaměť uživatelských dat',
	'legend_good' => 'Zelená: konfigurace vypadá v pořádku (vysoká míra zásahů, přiměřené množství volné paměti).',
	'legend_bad' => 'Červená: cache je pod tlakem (mnoho chyb nebo téměř žádná volná paměť).',
	'no_apcu' => 'APCu se na tomto serveru nejeví jako povolené.',
	'back' => '&laquo; Zpět k údržbě',
	'clear_fp_button'=> 'Vymazat položky FlatPress APCu',
	'clear_fp_confirm' => 'Opravdu chcete smazat všechny položky APCu? Tím se vymažou cache APCu FlatPress.',
	'clear_fp_result'=> 'Smazáno %d položek APCu.',
	'msgs' => array(
		1  => 'Položky FlatPress APCu byly vymazány.',
		2  => 'Nebyly nalezeny žádné položky APCu.',
		-1 => 'APCu není k dispozici nebo nebylo možné k němu získat přístup; nic nebylo smazáno.'
	)
);
?>
