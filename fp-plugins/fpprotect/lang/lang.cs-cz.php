<?php
$lang ['admin'] ['config'] ['submenu'] ['fpprotect'] = 'FlatPress Protect';

$lang ['admin'] ['config'] ['fpprotect'] = array(
	'head' => 'Nastavení aplikace FlatPress Protect',
	'desc1' => 'Zde můžete změnit možnosti zabezpečení blogu FlatPress. ' . //
		'Nejlepší ochranou pro vaše návštěvníky a váš blog FlatPress je, když jsou všechny možnosti deaktivovány.',

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

	// Part for metadate in images after upload
	'allow_image_metadate' => 'Zachování metadat a původní kvality obrázků v nahraných snímcích.',
	'allowImageMetadataDsc' => 'Po nahrání obrázků pomocí nástroje pro nahrávání se metadata zachovají. Patří mezi ně například informace o fotoaparátu a zeměpisné souřadnice.',

	// Part for the visitor-ip in FlatPress
	'allow_visitor_ip' => 'Povolte FlatPressu používat neanonymizovanou IP adresu návštěvníka.',
	'allowVisitorIpDsc' => 'FlatPress pak neanonymizovanou IP adresu uloží mimo jiné do komentářů. ' . //
		'Pokud používáte antispamovou službu Akismet, bude Akismet rovněž přijímat neanonymizovanou IP adresu.',

	// Part for Idle timeout for admin session
	'session_timeout_label' => 'Časový limit nečinnosti pro relaci správce (minuty)',
	'session_timeout_desc' => 'Minuty nečinnosti do vypršení relace správce. Prázdný nebo 0 znamená ve výchozím nastavení 60 minut.',

	'submit' => 'Uložení nastavení',
		'msgs' => array(
		1 => 'Nastavení bylo úspěšně uloženo.',
		-1 => 'Chyba při ukládání nastavení.'
	),

	// Warning message
	'warning_allowUnsafeInline' => 'Varování: Content-Security-Policy -> Tato zásada obsahuje "unsafe-inline", což je v zásadě script-src-policy nebezpečné.',
	'warning_allowVisitorIp' => 'Varování: Použití neanonymizovaných IP adres návštěvníků -> Nezapomeňte o tom informovat <a href="static.php?page=privacy-policy" title="editovat statickou stránku">návštěvníky vašeho blogu FlatPress</a>!'
);
?>
