<?php
$lang ['admin'] ['static'] ['submenu'] = array(
	'list' => 'Kudeatu orri estatikoak',
	'write' => 'Sortu orri estatikoa'
);

/* main panel */
$lang ['admin'] ['static'] ['list'] = array(
	'head' => 'Orri estatikoak',
	'descr' => 'Aukeratu editatzeko orria edo <a href="admin.php?p=static&amp;action=write">gehitu orri berri bat</a>.',

	'sel' => 'Auk', // checkbox
	'date' => 'Data',
	'name' => 'Orria',
	'title' => 'Izenburua',
	'author' => 'Egilea',

	'action' => 'Ekintza',
	'act_view' => 'Ikusi',
	'act_del' => 'Ezabatu',
	'act_edit' => 'Editatu',

	'natural' => 'Izenak ordena alfabetikoan erakutsi, sortze-dataren arabera ordenatu beharrean.',
	'submit' => 'Orrien izenak berrantolatu'
);

$lang ['admin'] ['static'] ['list'] ['msgs'] = array(
	1 => 'Orria ondo gorde da.',
	-1 => 'Errore bat gertatu da orria gordetzen saiatzean.',
	2 => 'Orria ondo ezabatu da.',
	-2 => 'Errore bat gertatu da orria ezabatzen saiatzean.'
);

/* write panel */
$lang ['admin'] ['static'] ['write'] = array(
	'head' => 'Argitaratu orri estatikoa',
	'descr' => 'Editatu inprimakia orria argitaratzeko',
	'fieldset1' => 'Editatu',
	'subject' => 'Gaia (*):',
	'content' => 'Edukia (*):',
	'fieldset2' => 'Gordetze aukerak',
	'pagename' => 'Orriaren izena (*):',
	'submit' => 'Argitaratu',
	'preview' => 'Aurrebista',

	'delfset' => 'Ezabatu',
	'deletemsg' => 'Ezabatu orri hau',
	'del' => 'Ezabatu',
	'success' => 'Orria ondo argitaratu da',
	'otheropts' => 'Beste aukera batzuk'
);

$lang ['admin'] ['static'] ['write'] ['error'] = array(
	'subject' => 'Ezin duzu gaia hutsik gorde',
	'content' => 'Ezin duzu edukia hutsik gorde',
	'id' => 'Baliozko ID bat idatzi behar duzu',
);

/* delete action */	
$lang ['admin'] ['static'] ['delete'] = array(
	'head' => 'Ezabatu orria', 
	'descr' => 'Hurrengo orria ezabatuko duzu:',
	'preview' => 'Aurrebista',
	'confirm' => 'Ziur zaude jarraitu nahi duzula?',
	'fset' => 'Ezabatu',
	'ok' => 'Bai, ezabatu orria',
	'cancel' => 'Ez, eraman nazazu panelera',
	'err' => 'Zehaztutako orria ez da existitzen.'
);
?>
