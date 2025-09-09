<?php
$baseurl = BLOG_BASEURL;

$lang ['contact'] = array(
	'head' => 'Jarri gurekin harremanetan',
	'descr' => 'Bete beheko formularioa iritzia bidaltzeko. Mesedez, gehitu zure helbide elektronikoa erantzun bat jaso nahi baduzu.',
	'fieldset1' => 'Erabiltzailearen datuak',
	'name' => 'Izena (*)',
	'email' => 'E-maila:',
	'www' => 'Web orria:',
	'cookie' => 'Gogoratu nazazu',
	'fieldset2' => 'Zure mezua',
	'comment' => 'Mezua (*):',
	'fieldset3' => 'Bidali',
	'submit' => 'Bidali',
	'reset' => 'Berrezarri',
	'loggedin' => 'Saioa hasita duzu 😉. <a href="' . $baseurl . 'login.php?do=logout">Itxi saioa</a> edo joan <a href="' . $baseurl . 'admin.php">kontrol-panelera</a>.'
);

$lang ['contact'] ['notification'] = array(
	'name' => 'Izena:',
	'email' => 'E-maila:',
	'www' => 'Web orria:',
	'content' => 'Mezua:',
	'subject' => 'Mezua honen bidez bidali da: '
);

$lang ['contact'] ['error'] = array(
	'name' => 'Izena sartu behar duzu.',
	'email' => 'Baliozko helbide elektronikoa sartu behar duzu.',
	'www' => 'Baliozko URLa sartu behar duzu.',
	'content' => 'Mezua sartu behar duzu.'
);

$lang ['contact'] ['msgs'] = array(
	1 => 'Mezua ondo bidali da.',
	-1 => 'Errore bat gertatu da mezua bidaltzen saiatzean.'
);
?>