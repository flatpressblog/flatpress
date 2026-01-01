<?php
$lang ['admin'] ['entry'] ['submenu'] = array(
	'list' => 'Kudeatu sarrerak',
	'write' => 'Idatzi sarrera berria',
	'cats' => 'Kudeatu kategoriak'
);

/* default action */
$lang ['admin'] ['entry'] ['list'] = array(
	'head' => 'Kudeatu sarrerak',
	'descr' => 'Aukeratu editatzeko sarrera edo <a href="admin.php?p=entry&amp;action=write">gehitu sarrera berri bat</a><br>' . //
		'<a href="admin.php?p=entry&amp;action=cats">Kategoriak kudeatu</a>.',
	'drafts' => 'Zirriborroak: ',
	'filter' => 'Iragazkia: ',
	'nofilter' => 'Erakutsi denak',
	'filterbtn' => 'Aplikatu iragazkia',
	'sel' => 'Aukeratu', // checkbox
	'date' => 'Data',
	'title' => 'Izenburua',
	'author' => 'Egilea',
	'comms' => 'Iruzkinak', // comments
	'action' => 'Ekintza',
	'act_del' => 'Ezabatu',
	'act_view' => 'Ikusi',
	'act_edit' => 'Editatu',
	'perpage_show' => 'Erakutsi',
	'perpage_entries' => 'sarrera orriko'
);

/* write action */
$lang ['admin'] ['entry'] ['write'] = array(
	'head' => 'Idatzi sarrera',
	'descr' => 'Inprimakia editatu sarrera berria argitaratzeko',
	'uploader' => 'Fitxategi-kargatzailea',
	'fieldset1' => 'Editatu',
	'subject' => 'Gaia (*):',
	'content' => 'Edukia (*):',
	'fieldset2' => 'Gorde',
	'submit' => 'Argitaratu',
	'preview' => 'Aurrebista',
	'savecontinue' => 'Gorde eta jarraitu editatzen',
	'categories' => 'Kategoriak',
	'nocategories' => 'Ez da kategoriarik ezarri. <a href="admin.php?p=entry&amp;action=cats">Sortu zure kategoriak</a> kontrol-panel nagusian. ' . //
		'<a href="#save">Gorde</a> zure sarrera lehenik.',
	'saveopts' => 'Gordetze aukerak',
	'success' => 'Zure sarrera ondo argitaratu da.',
	'otheropts' => 'Beste aukera batzuk',
	'commmsg' => 'Kudeatu sarrera honen iruzkinak',
	'delmsg' => 'Ezabatu sarrera hau'
	// 'back' => 'Atzera aldaketak gorde barik',
);

$lang ['admin'] ['entry'] ['list'] ['msgs'] = array(
	1 => 'Sarrera ondo gorde da.',
	-1 => 'Errorea gertatu da sarrera gordetzen saiatzean.',
	2 => 'Sarrera ondo ezabatu da.',
	-2 => 'Errorea gertatu da sarrera ezabatzen saiatzean.'
);

$lang ['admin'] ['entry'] ['write'] ['error'] = array(
	'subject' => 'Ezin duzu gaia hutsik gorde.',
	'content' => 'Ezin duzu sarrera huts bat gorde.'
);

$lang ['admin'] ['entry'] ['write'] ['msgs'] = array(
	1 => 'Sarrera ondo gorde da.',
	-1 => 'Errore bat gertatu da: zure sarrera ezin izan da behar bezala gorde.',
	-2 => 'Errore bat gertatu da: zure sarrera ez da gorde; aurkibidea hondatuta egon daiteke.',
	-3 => 'Errore bat gertatu da: zure sarrera zirriborro gisa gorde da.',
	-4 => 'Errore bat gertatu da: zure sarrera zirriborro gisa gorde da; aurkibidea hondatuta egon daiteke.',
	'draft' => '<strong>Zirriborro</strong> bat editatzen ari zara.'
);

/* comments */
$lang ['admin'] ['entry'] ['commentlist'] = array(
	'head' => 'Iruzkinak: ',
	'descr' => 'Iruzkinak editatu edo ezaba ditzakezu hemen.',
	'sel' => 'Aukeratu',
	'content' => 'Edukia',
	'date' => 'Data',
	'author' => 'Egilea',
	'email' => 'E-maila',
	'ip' => 'IPa',
	'actions' => 'Ekintzak',
	'act_edit' => 'Editatu',
	'act_del' => 'Ezabatu',
	'act_del_confirm' => 'Benetan iruzkin hau ezabatu nahi duzu?',
	'nocomments' => 'Sarrera honek ez du iruzkinik oraindik.',
);

$lang ['admin'] ['entry'] ['commentlist'] ['msgs'] = array(
	1 => 'Iruzkin ondo ezabatu da.',
	-1 => 'Errore bat gertatu da iruzkina ezabatzen saiatzean.'
);

$lang ['admin'] ['entry'] ['commedit'] = array(
	'head' => 'Iruzkina editatu: ',
	'descr' => 'Hemen iruzkinen egilearen izena, helbide elektronikoa eta webgunea nahi duzun bezala edita dezakezu.<br><br>',
	'content' => 'Edukia',
	'date' => 'Data',
	'author' => 'Egilea',
	'www' => 'Web orria',
	'email' => 'E-maila',
	'ip' => 'IPa',
	'loggedin' => 'Administratzailea saioa hasita',
	'submit' => 'Gorde aldaketak',
	'commentlist' => 'Itzuli iruzkinen ikuspegi orokorrera'
);

$lang ['admin'] ['entry'] ['commedit'] ['error'] = array(
	'name' => 'Ezin duzu izena hutsik gorde.',
	'email' => 'E-maila ez da zuzena.',
	'url' => 'Web orria ez da zuzena, <strong>http://</strong> edo <strong>https://</strong> izan behar du hasieran.',
	'content' => 'Ezin duzu iruzkina hutsik gorde.'
);

$lang ['admin'] ['entry'] ['commedit'] ['msgs'] = array(
	1 => 'Iruzkina ondo gorde da.',
	-1 => 'Errore bat gertatu da editatutako iruzkina gordetzen saiatzean.'
);

/* delete action */
$lang ['admin'] ['entry'] ['delete'] = array(
	'head' => 'Ezabatu sarrera',
	'descr' => 'Sarrera hau ezabatzear zaude: ',
	'preview' => 'Aurrebista',
	'confirm' => 'Ziur zaude jarraitu nahi duzula?',
	'fset' => 'Ezabatu',
	'ok' => 'Bai, ezabatu sarrera hau.',
	'cancel' => 'Ez, eraman nazazu panelera.',
	'err' => 'Aukeratutako sarrera ez da existitzen.'
);

/* category mgmt */
$lang ['admin'] ['entry'] ['cats'] = array(
	'head' => 'Kudeatu kategoriak',
	'descr' => '<p>Erabili beheko formularioa kategoriak gehitzeko eta editatzeko. ' . //
		'Kategoria-elementu bakoitza formatu honetan egon behar da: "kategoria-izena: <em>id_zenbakia</em>". Elementuak marratxoekin koskatu ditzakezu hierarkiak sortzeko.</p>
	<p>Adibidea:</p>
	<pre>
Orokorra :1
Berriak :2
--Iragarkiak :3
--Gertaerak :4
----Askotarikoak :5
Teknologia :6
	</pre>',
	'clear' => 'Ezabatu kategoria guztiak',
	'fset1' => 'Editorea',
	'fset2' => 'Aplikatu aldaketak',
	'submit' => 'Gorde'
);

$lang ['admin'] ['entry'] ['cats'] ['msgs'] = array(
	1 => 'Kategoriak ondo gorde dira.',
	-1 => 'Errore bat gertatu da kategoria gordetzen saiatzean.',
	2 => 'Kategoriak ondo ezabatu dira.',
	-2 => 'Errore bat gertatu da kategoriak ezabatzen saiatzean.',
	-3 => 'Kategoria IDak positiboak izan behar dira (0 ez da onartzen).'
);
?>
