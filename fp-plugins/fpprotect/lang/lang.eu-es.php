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

	// Part for external iFrame embedding
	'allow_external_iframe' => 'Kanpoko edukia iFrame bidez txertatzea baimendu (Ez da gomendagarria).',
	'allowExternalIframeDsc' => 'Kanpoko edukia <code>&lt;iframe&gt;</code> etiketaren bidez txertatzea baimendu (adibidez, bideoak, mapak, widgetak). ' . //
		'Txertatutako hirugarrenen edukiak bisitariak jarrai ditzake eta ez da segurua izan daiteke. Gaitu hau benetan behar baduzu bakarrik.',

	// Part for SVG uploads via admin uploader
	'allow_svg_upload' => 'Baimendu SVG fitxategiak igotzea kargatzailearen bidez (erabiltzaile fidagarrientzat bakarrik).',
	'allowSvgUploadDsc' => 'Baimendu SVG fitxategiak administratzailearen kargatzailearen bidez igotzea. SVG-k eduki aktiboa izan dezake (adibidez, script-ak); gaitu hau kargatzaileengan konfiantza baduzu eta SVG fidagarriak ez txertatzen badituzu bakarrik.',

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

	// Part for Idle timeout for admin session
	'session_timeout_label' => 'Administrazio Saioaren Inaktibotasun Denbora-muga (Minutuak)',
	'session_timeout_desc' => 'Administrazio saioa amaitu arteko inaktibitate minutuak. Hutsik edo 0 bada, lehenetsitako denbora 60 minutu dela esan nahi du.',

	'submit' => 'Gorde ezarpenak',
		'msgs' => array(
		1 => 'Ezarpenak ondo gorde dira.',
		-1 => 'Errore bat gertatu da ezarpenak gordetzen saiatzean.'
	),

	// Warning message
	'warning_allowUnsafeInline' => 'Abisua! Content-Security-Policy -> Politika honek "unsafe-inline" gako-hitza du, <code>script-src-policy</code> direktiba arriskuan jartzen duena.',
	'warning_allowExternalIframe' => 'Abisua: Edukiaren Segurtasun Politika -> Kanpoko iFrame txertatzea gaituta dago. Txertatutako hirugarrenen edukiak bisitariak jarrai ditzake eta ez da segurua izan daiteke.',
	'warning_allowSvgUpload' => 'Abisua: SVG fitxategiek eduki aktiboa izan dezakete. Kargatu SVG fidagarriak bakarrik eta ez txertatu berrikusi gabe!',
	'warning_allowVisitorIp' => 'Abisua! Bisitariaren IP helbide ez-anonimizatuen erabilera -> Ez ahaztu zure FlatPress blogaren <a href="static.php?page=privacy-policy" title="edit static page">bisitariei</a> honen berri ematea!'
);
?>
