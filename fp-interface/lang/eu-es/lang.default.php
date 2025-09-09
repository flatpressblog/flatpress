<?php
$lang = array();

$lang ['main'] = array(
	'nextpage' => 'Hurrengo orria &raquo;',
	'prevpage' => '&laquo; Aurreko orria',
	'entry' => 'Sarrera',
	'entries' => 'Sarrerak',
	'static' => 'Orri estatikoa',
	'preview' => 'Editatu/Aurrebista',

	'filed_under' => 'Kategoria honetan gordeta: ',

	'add_entry' => 'Idatzi sarrera',
	'add_comment' => 'Idatzi iruzkina',
	'add_static' => 'Sortu orri estatikoa',

	'btn_edit' => 'Editatu',
	'btn_delete' => 'Ezabatu',

	'nocomments' => 'Iruzkindu',
	'comment' => 'Iruzkin 1',
	'comments' => 'iruzkin',

	'rss' => 'Harpidetu RSS jariora',
	'atom' => 'Harpidetu Atom jariora'
);

$lang ['search'] = array(
	'head' => 'Bilatu',
	'fset1' => 'Sartu bilaketa-irizpideak',
	'keywords' => 'Gako-hitzak',
	'onlytitles' => 'Soilik izenburuak',
	'fulltext' => 'Testu osoa',

	'fset2' => 'Data',
	'datedescr' => 'Bilaketa data zehatz bati lotu diezaiokezu. Urte bat, urte bat eta hilabete bat edo data oso bat hauta dezakezu. ' . //
		'Utzi hutsik datu-base osoan bilatzeko.',

	'fset3' => 'Bilatu kategorietan',
	'catdescr' => 'Ez hautatu bat ere denetan bilatzeko.',

	'fset4' => 'Hasi bilatzen',
	'submit' => 'Bilatu',

	'headres' => 'Bilaketa-emaitzak',
	'descrres' => '<strong>%s</strong> bilaketak emaitza hauek eman ditu:',
	'descrnores' => '<strong>%s</strong> bilaketak ez du emaitzarik eman.',

	'moreopts' => 'Aukera gehiago',

	'searchag' => 'Bilatu berriro',
);

$lang ['search'] ['error'] = array(
	'keywords' => 'Gutxienez gako-hitz bat zehaztu behar duzu.'
);

$lang ['staticauthor'] = array(
	// "Published by" in static pages
	'published_by' => 'Honek argitaratua',
	'on' => 'data honetan'
);

$lang ['entryauthor'] = array(
	// "Posted by" in entry pages
	'posted_by' => '(e)k idatzia',
	'at' => '(r)etan'
);

$lang ['entry'] = array();
$lang ['entry'] ['flags'] = array();

$lang ['entry'] ['flags'] ['long'] = array(
	'draft' => '<strong>Zirriborroa</strong>: ezkutatuta, argitaratzeko zain.',
	// 'static' => '<strong>Orri estatikoa</strong>: normalean ezkutatuta, sarrerara iristeko jarri ?page=sarreraren-izenburua URLan (esperimentala)',
	'commslock' => '<strong>Blokeatu iruzkinak</strong>: sarrera honetarako iruzkinak debekatuta daude.'
);

$lang ['entry'] ['flags'] ['short'] = array(
	'draft' => 'Zirriborroa',
	// 'static' => 'Orri estatikoa',
	'commslock' => 'Blokeatu iruzkinak'
);

$lang ['entry'] ['categories'] = array(
	'unfiled' => 'Artxibatu gabe'
);

$lang ['404error'] = array(
	'subject' => 'Oh! Ez dugu ezer erakusteko',
	'content' => '<p>Barkatu, ezin izan dugu eskatutako orrialdea aurkitu.</p>'
);

// Login
$lang ['login'] = array(
	'head' => 'Hasi saioa',
	'fieldset1' => 'Sartu zure erabiltzaile-izena eta pasahitza',
	'user' => 'Erabiltzailea:',
	'pass' => 'Pasahitza:',
	'fieldset2' => 'Hasi saioa',
	'submit' => 'Hasi saioa',
	'forgot' => 'Pasahitza galdu dut'
);

$lang ['login'] ['success'] = array(
	'success' => 'Saioa hasita duzu orain.',
	'logout' => 'Saioa itxita duzu orain.',
	'redirect' => '5 segundotan birbideratuko zaitugu.',
	'opt1' => 'Itzuli hasiera orrira',
	'opt2' => 'Joan kontrol-panelera',
	'opt3' => 'Idatzi sarrera berria'
);

$lang ['login'] ['error'] = array(
	'user' => 'Erabiltzaile-izen bat sartu behar duzu.',
	'pass' => 'Pasahitz bat sartu behar duzu.',
	'match' => 'Pasahitza ez da zuzena.',
	'timeout' => 'Mesedez, itxaron 30 segundo berriro saiatu aurretik.'
);

$lang ['comments'] = array(
	'head' => 'Iruzkindu',
	'descr' => 'Bete beheko formularioa zure iruzkinak gehitzeko.',
	'fieldset1' => 'Erabiltzailearen datuak',
	'name' => 'Izena (*)',
	'email' => 'E-maila:',
	'www' => 'Web orria:',
	'cookie' => 'Gogoratu nazazu',
	'fieldset2' => 'Gehitu iruzkina',
	'comment' => 'Iruzkina (*):',
	'fieldset3' => 'Bidali',
	'submit' => 'Gehitu',
	'reset' => 'Berrezarri',
	'success' => 'Zure iruzkina ondo gorde da.',
	'nocomments' => 'Sarrera honek oraindik ez du iruzkinik.',
	'commslock' => 'Iruzkinak desgaitu dira sarrera honetan.'
);

$lang ['comments'] ['error'] = array(
	'name' => 'Izen bat sartu behar duzu.',
	'email' => 'Baliozko helbide elektroniko bat sartu behar duzu.',
	'www' => 'Baliozko URL bat sartu behar duzu.',
	'comment' => 'Iruzkin bat sartu behar duzu.'
);

$lang ['postviews'] = array(
	// PostView-Plugin
	'views' => 'ikustaldi',
);

$lang ['date'] ['month'] = array(
	'urtarrila',
	'otsaila',
	'martxoa',
	'apirila',
	'maiatza',
	'ekaina',
	'uztaila',
	'abuztua',
	'iraila',
	'urria',
	'azaroa',
	'abendua'
);

$lang ['date'] ['month_abbr'] = array(
	'Urt',
	'Ots',
	'Mar',
	'Api',
	'Mai',
	'Eka',
	'Uzt',
	'Abu',
	'Ira',
	'Urr',
	'Aza',
	'Abe'
);

$lang ['date'] ['weekday'] = array(
	'Igandea',
	'Astelehena',
	'Asteartea',
	'Asteazkena',
	'Osteguna',
	'Ostirala',
	'Larunbata'
);

$lang ['date'] ['weekday_abbr'] = array(
	'Ig',
	'Al',
	'Ar',
	'Az',
	'Og',
	'Ol',
	'Lr'
);
?>
