<?php
$lang ['admin'] ['config'] ['submenu'] ['fpprotect'] = 'FlatPress Protect';

$lang ['admin'] ['config'] ['fpprotect'] = array(
	'head' => 'Nastavení aplikace FlatPress Protect',
	'desc1' => 'Zde můžete změnit možnosti zabezpečení blogu FlatPress.',

	// Part for unsafe inline scripts
	'allow_unsafe_inline' => 'Povolit nezabezpečené Java skripty (Nedoporučuje se)',

	'allowUnsafeInlineDsc' => '<p>Povolí načítání nezabezpečeného inline kódu JavaScriptu.</p>' . //
		'<p><br>Poznámka pro vývojáře zásuvných modulů: Přidejte prosím do svého Java skriptu nonce.</p>' . //
		'Příklad pro PHP:
		<pre>$random_hex = RANDOM_HEX;
' . //
		'... script nonce="\' . $random_hex . \'" src=" ...' . //
		'</pre>' . //
		'Příklad pro šablonu Smarty:
		<pre>' . //
		'... script nonce="{$smarty.const.RANDOM_HEX}" ...' . //
		'</pre>' . //
		'<p>Tím zajistíte, že prohlížeč návštěvníka spustí pouze skripty Java, které pocházejí z vašeho blogu FlatPress.</p>',

	// Part for the PrettyURLs .htaccess edit-field
	'allow_htaccess_edit' => 'Umožňuje vytvoření a úpravu souboru .htaccess.',
	'allowPrettyURLEditDsc' => 'Umožňuje přístup k editačnímu poli .htaccess zásuvného modulu PrettyURLs pro vytvoření nebo úpravu souboru .htaccess.',

	'submit' => 'Uložení nastavení',
		'msgs' => array(
		1 => 'Nastavení bylo úspěšně uloženo.',
		-1 => 'Chyba při ukládání nastavení.'
	)
);
?>
