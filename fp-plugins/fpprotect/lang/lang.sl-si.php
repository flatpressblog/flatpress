<?php
$lang ['admin'] ['config'] ['submenu'] ['fpprotect'] = 'FlatPress Protect';

$lang ['admin'] ['config'] ['fpprotect'] = array(
	'head' => 'Nastavitve FlatPress Protect',
	'desc1' => 'Tukaj lahko spremenite možnosti, povezane z varnostjo, za svoj blog FlatPress.',

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

	'submit' => 'Shranjevanje nastavitev',
		'msgs' => array(
		1 => 'Nastavitve so bile uspešno shranjene.',
		-1 => 'Napaka pri shranjevanju nastavitev.'
	)
);
?>
