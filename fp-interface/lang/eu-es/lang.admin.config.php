<?php
$lang ['admin'] ['config'] ['default'] = array(
	'head' => 'Ezarpenak',
	'descr' => 'Pertsonalizatu eta konfiguratu zure FlatPress instalazioa.',
	'submit' => 'Gorde aldaketak',
	'sysfset' => 'Sistemaren informazio orokorra',
	'syswarning' => '<big>Kontuz!</big> Informazio hau funtsezkoa da eta zuzena izan behar da, bestela FlatPressek (ziurrenik) behar bezala funtzionatzeari uko egingo dio.',
	'blog_root' => '<strong>FlatPresserako bide-izen absolutua</strong>. Oharra: ' . //
		'oro har, ez duzu hau editatu beharko, hala ere kontuz ibili, ezin baitugu egiaztatu zuzena den ala ez.',
	'www' => '<strong>Blogaren erroa</strong>. Zure blogaren URL nagusia.<br>' . //
		'adib.: http://www.mydomain.com/flatpress/ (atzeko barra beharrezkoa da)',

	// ------
	'gensetts' => 'Ezarpen orokorrak',
	'adminname' => 'Administratzailearen izena',
	'adminpassword' => 'Pasahitz berria',
	'adminpasswordconfirm' => 'Errepikatu pasahitza',
	'blogtitle' => 'Blogaren izenburua',
	'blogsubtitle' => 'Blogaren azpititulua',
	'blogfooter' => 'Blogaren oina',
	'blogauthor' => 'Blogaren egilea',
	'startpage' => 'Blog honen hasierako orria',
	'stdstartpage' => 'Bloga (lehenetsia)',
	'blogurl' => 'Blogaren URLa',
	'blogemail' => 'Blogaren e-maila',
	'notifications' => 'Jakinarazpenak',
	'mailnotify' => 'Gaitu iruzkinen posta elektroniko bidezko jakinarazpenak.',
	'blogmaxentries' => 'Orrialdeko argitalpen kopurua',
	'langchoice' => 'Hizkuntza',

	'intsetts' => 'Nazioarteko ezarpenak',
	'utctime' => '<abbr title="Denbora Unibertsal Koordinatua">UTC</abbr> ordua honako hau da:',
	'timeoffset' => 'Ordu-diferentzia ',
	'hours' => 'ordukoa izan behar da.',
	'timeformat' => 'Orduaren formatu lehenetsia',
	'dateformat' => 'Dataren formatu lehenetsia',
	'dateformatshort' => 'Dataren formatu lehenetsia (laburra)',
	'output' => 'Irteera',
	'charset' => 'Karaktere multzoa',
	'charsettip' => 'Zure bloga idazteko erabiltzen duzun karaktere multzoa (UTF-8 ' . //
		'<a class="hint" href="https://wiki.flatpress.org/doc:techfaq#character_encoding" target="_blank" title="Zein karaktere-kodeketa estandar onartzen ditu FlatPress-ek?">gomendatzen da</a>).'
);

$lang ['admin'] ['config'] ['default'] ['msgs'] = array(
	1 => 'Ezarpenak ondo gorde dira.',
	2 => 'Administratzailea aldatu da. Saioa itxiko da orain.',
	-1 => 'Errore bat gertatu da ezarpenak gordetzen saiatzean.'
);

$lang ['admin'] ['config'] ['default'] ['error'] = array(
	'www' => 'Blogaren erroa baliozko URLa izan behar da.',
	'title' => 'Izenburu bat zehaztu behar duzu.',
	'email' => 'E-mailak baliozko formatua izan behar du.',
	'maxentries' => 'Ez duzu sarrera kopuru baliodun bat sartu.',
	'timeoffset' => 'Ez duzu ordu-diferentzia baliodun bat sartu. Koma mugikorra erabil dezakezu (adibidez, 2h30" => 2.5)',
	'timeformat' => 'Orduaren formaturako karaktere-kate zuzena zehaztu behar duzu.',
	'dateformat' => 'Dataren formaturako karaktere-kate zuzena zehaztu behar duzu.',
	'dateformatshort' => 'Data laburraren formaturako karaktere-kate zuzena zehaztu behar duzu.',
	'charset' => 'Karaktere-multzo bat zehaztu behar duzu.',
	'lang' => 'Aukeratu duzun hizkuntza ez dago eskuragarri.',
	'admin' => 'Administratzailearen izenak letrak, zenbakiak eta azpimarra bat bakarrik izan ditzake.',
	'password' => 'Pasahitzak gutxienez 6 karaktere izan behar ditu eta ez du hutsunerik izan behar.',
	'confirm_password' => 'Pasahitzak ez datoz bat.'
);
?>
