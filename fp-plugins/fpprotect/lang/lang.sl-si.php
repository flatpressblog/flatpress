<?php
$lang ['admin'] ['config'] ['submenu'] ['fpprotect'] = 'FlatPress Protect';

$lang ['admin'] ['config'] ['fpprotect'] = array(
	'head' => 'Nastavitve FlatPress Protect',
	'desc1' => 'Tukaj lahko spremenite možnosti, povezane z varnostjo, za svoj blog FlatPress. ' . //
		'Najboljša zaščita za vaše obiskovalce in vaš FlatPress blog je, če so vse možnosti deaktivirane.',

	// Part for unsafe inline scripts
	'allow_unsafe_inline' => 'Dovoli negotove Java skripte (Ni priporočljivo)',

	'allowUnsafeInlineDsc' => '<p>Omogoča nalaganje nezanesljive vdelane kode JavaScript.</p>' . //
		'<p><br>Opomba za razvijalce vtičnikov: Prosimo, dodajte nonce skriptam Java.</p>' . //
		'Primer za PHP:
		<pre>$random_hex = RANDOM_HEX;
' . //
		'... script nonce="\' . $random_hex . \'" src=" ...' . //
		'</pre>' . //
		'Primer za predlogo Smarty:
		<pre>' . //
		'... script nonce="{$smarty.const.RANDOM_HEX}" ...' . //
		'</pre>' . //
		'<p>S tem zagotovite, da brskalnik obiskovalca izvede samo skripte Java, ki izvirajo iz vašega bloga FlatPress.</p>',

	// Part for the PrettyURLs .htaccess edit-field
	'allow_htaccess_edit' => 'Omogoča ustvarjanje in urejanje datoteke .htaccess.',
	'allowPrettyURLEditDsc' => 'Omogoča dostop do polja za urejanje datoteke .htaccess v vtičniku PrettyURLs za ustvarjanje ali spreminjanje datoteke .htaccess.',

	// Part for metadate in images after upload
	'allow_image_metadate' => 'Ohranite metapodatke in izvirno kakovost slik v naloženih slikah.',
	'allowImageMetadataDsc' => 'Ko so slike naložene s programom za nalaganje, se metapodatki ohranijo. To vključuje na primer podatke o fotoaparatu in geografske koordinate.',

	// Part for the visitor-ip in FlatPress
	'allow_visitor_ip' => 'FlatPressu dovolite uporabo neanonimiziranega naslova IP obiskovalca.',
	'allowVisitorIpDsc' => 'FlatPress bo neanonimizirani naslov IP med drugim shranil v komentarjih. ' . //
		'Če uporabljate storitev Akismet Antispam, bo Akismet prav tako prejel neanonimiziran naslov IP.',

	'submit' => 'Shranjevanje nastavitev',
		'msgs' => array(
		1 => 'Nastavitve so bile uspešno shranjene.',
		-1 => 'Napaka pri shranjevanju nastavitev.'
	),

	// Warning message
	'warning_allowUnsafeInline' => 'Opozorilo: Content-Security-Policy -> Ta politika vsebuje "unsafe-inline", kar je nevarno v politiki script-src.',
	'warning_allowVisitorIp' => 'Opozorilo: Uporaba neanonimiziranih IP naslovov obiskovalcev -> Ne pozabite o tem obvestiti <a href="static.php?page=privacy-policy" title="uredi statično stran">obiskovalce vašega FlatPress bloga</a>!'
);
?>
