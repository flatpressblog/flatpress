<?php
$lang ['admin'] ['config'] ['submenu'] ['fpprotect'] = 'FlatPress Protect';

$lang ['admin'] ['config'] ['fpprotect'] = array(
	'head' => 'FlatPress Protect konfigurazioa',
	'desc1' => 'Hemen zure FlatPress blogaren segurtasun-aukera garrantzitsuak alda ditzakezu. ' . //
		'Zure bisitarientzako eta zure FlatPress blogarentzako babesik onena aukera guztiak desaktibatzea da.',

	// Part for unsafe inline scripts
	'allow_unsafe_inline' => 'JavaScript script ez-seguruak baimendu (ez da gomendagarria).',

	'allowUnsafeInlineDsc' => '<p>JavaScript kode ez-segurua kargatzea baimentzen du.</p>' . //
		'<p><br>Oharra plugin garatzaileentzat: mesedez, gehitu "nonce" atributua zure scriptari.</p>' . //
		'Adibidea PHPn:
		<pre>$random_hex = RANDOM_HEX;
' . //
		'... script nonce="\' . $random_hex . \'" src=" ...' . //
		'</pre>' . //
		'Smarty txantiloian adibidea:
		<pre>' . //
		'... script nonce="{$smarty.const.RANDOM_HEX}" ...' . //
		'</pre>' . //
		'<p>Honek bermatuko du bisitariaren nabigatzaileak zure FlatPress blogetik datozen JavaScript script-ak bakarrik exekutatuko dituela.</p>',

	// Part for the PrettyURLs .htaccess edit-field
	'allow_htaccess_edit' => 'Baimendu .htaccess fitxategia sortzea eta editatzea.',
	'allowPrettyURLEditDsc' => 'PrettyURLs pluginaren .htaccess edizio eremurako sarbidea baimentzen du .htaccess fitxategia sortu edo aldatzeko.',

	// Part for metadate in images after upload
	'allow_image_metadate' => 'Gorde metadatuak eta jatorrizko irudiaren kalitatea igotako irudietan.',
	'allowImageMetadataDsc' => 'Irudiak fitxategi-kargatzailearekin igo ondoren, metadatuak gordetzen dira. Horrek kameraren informazioa eta geokoordenatuak barne hartzen ditu, adibidez.',

	// Part for the visitor-ip in FlatPress
	'allow_visitor_ip' => 'Baimendu FlatPressi bisitariaren IP helbide ez-anonimizatua erabiltzea.',
	'allowVisitorIpDsc' => 'FlatPressek IP helbide ez-anonimizatua gordeko du iruzkinetan, besteak beste. ' . //
		'Akismet Antispam zerbitzua erabiltzen baduzu, Akismetek IP helbide ez-anonimizatua ere jasoko du.',

	'submit' => 'Gorde ezarpenak',
		'msgs' => array(
		1 => 'Ezarpenak ondo gorde dira.',
		-1 => 'Errore bat gertatu da ezarpenak gordetzen saiatzean.'
	),

	// Warning message
	'warning_allowUnsafeInline' => 'Abisua! Content-Security-Policy -> Politika honek "unsafe-inline" gako-hitza du, <code>script-src-policy</code> direktiba arriskuan jartzen duena.',
	'warning_allowVisitorIp' => 'Abisua! Bisitariaren IP helbide ez-anonimizatuen erabilera -> Ez ahaztu zure FlatPress blogaren <a href="static.php?page=privacy-policy" title="edit static page">bisitariei</a> honen berri ematea!'
);
?>
