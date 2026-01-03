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

	// Part for external iFrame embedding
	'allow_external_iframe' => 'Dovoli vdelavo zunanje vsebine prek iFrame (ni priporočljivo).',
	'allowExternalIframeDsc' => 'Dovoli vdelavo zunanje vsebine prek oznake <code>&lt;iframe&gt;</code> (npr. videoposnetki, zemljevidi, gradniki). ' . //
		'Vdelana vsebina tretjih oseb lahko sledi obiskovalcem in je lahko nevarna. Omogočite to le, če jo resnično potrebujete.',

	// Part for SVG uploads via admin uploader
	'allow_svg_upload' => 'Dovoli nalaganje datotek SVG prek programa za nalaganje (samo za zaupanja vredne uporabnike).',
	'allowSvgUploadDsc' => 'Dovoli nalaganje datotek SVG prek skrbniškega programa za nalaganje. Datoteka SVG lahko vsebuje aktivno vsebino (npr. skripte); omogočite to le, če zaupate programom za nalaganje in ne vdelate nezaupanja vrednih datotek SVG.',

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

	// Part for Idle timeout for admin session
	'session_timeout_label' => 'Časovna omejitev nedejavnosti za sejo upravitelja (v minutah)',
	'session_timeout_desc' => 'Minute nedejavnosti do izteka seje administratorja. Prazno ali 0 pomeni privzeto 60 minut.',

	'submit' => 'Shranjevanje nastavitev',
		'msgs' => array(
		1 => 'Nastavitve so bile uspešno shranjene.',
		-1 => 'Napaka pri shranjevanju nastavitev.'
	),

	// Warning message
	'warning_allowUnsafeInline' => 'Opozorilo: Content-Security-Policy -> Ta politika vsebuje "unsafe-inline", kar je nevarno v politiki script-src.',
	'warning_allowExternalIframe' => 'Opozorilo: Pravilnik o varnosti vsebine -> Vdelava zunanjega iFrame je omogočena. Vdelana vsebina tretjih oseb lahko sledi obiskovalcem in je lahko nevarna.',
	'warning_allowSvgUpload' => 'Opozorilo: Datoteke SVG lahko vsebujejo aktivno vsebino. Naložite samo zaupanja vredne datoteke SVG in jih ne vdelajte brez pregleda!',
	'warning_allowVisitorIp' => 'Opozorilo: Uporaba neanonimiziranih IP naslovov obiskovalcev -> Ne pozabite o tem obvestiti <a href="static.php?page=privacy-policy" title="uredi statično stran">obiskovalce vašega FlatPress bloga</a>!'
);
?>
