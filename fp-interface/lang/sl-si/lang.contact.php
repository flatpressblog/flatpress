<?php
$baseurl = BLOG_BASEURL;

$lang ['contact'] = array(
	'head' => 'Kontaktirajte nas',
	'descr' => 'Izpolnite spodnji obrazec, da nam pošljete povratne informacije. Če želite prejeti odgovor, dodajte svoj e-poštni naslov.',
	'fieldset1' => 'Podatki uporabnika',
	'name' => 'Ime (*)',
	'email' => 'E-pošta:',
	'www' => 'Spletna stran:',
	'cookie' => 'Zapomni si me',
	'fieldset2' => 'Vaše sporočilo',
	'comment' => 'Sporočilo (*):',
	'fieldset3' => 'Pošlji',
	'submit' => 'Pošlji',
	'reset' => 'Ponastavi',
	'loggedin' => 'Prijavljeni ste 😉. <a href="' . $baseurl . 'login.php?do=logout">Odjavi se</a> ali na <a href="' . $baseurl . 'admin.php">upravno območje</a>.'
);

$lang ['contact'] ['notification'] = array(
	'name' => 'Ime:',
	'email' => 'E-pošta:',
	'www' => 'Spletna stran:',
	'content' => 'Sporočilo:',
	'subject' => 'Stik poslan prek '
);

$lang ['contact'] ['error'] = array(
	'name' => 'Vnesti morate ime',
	'email' => 'Vnesti morate veljaven e-poštni naslov',
	'www' => 'Vnesti morate veljavno spletno stran',
	'content' => 'Vnesti morate sporočilo'
);

$lang ['contact'] ['msgs'] = array(
	1 => 'Sporočilo je bilo uspešno poslano',
	-1 => 'Sporočilo ni bilo mogoče poslati'
);
?>
