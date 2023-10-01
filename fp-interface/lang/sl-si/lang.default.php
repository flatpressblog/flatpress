<?php
$lang = array();

$lang ['main'] = array(
	'nextpage' => 'Naslednja stran &raquo;',
	'prevpage' => '&laquo; Prejšnja stran',
	'entry' => 'Vnos',
	'static' => 'Statična stran',
	'comment' => 'Komentar',
	'preview' => 'Uredi/Ogled',

	'filed_under' => 'Kategorija ',

	'add_entry' => 'Dodaj vnos',
	'add_comment' => 'Dodaj komentar',
	'add_static' => 'Dodaj statično stran',

	'btn_edit' => 'Uredi',
	'btn_delete' => 'Izbriši',

	'nocomments' => 'Dodajte komentar',
	'comment' => '1 komentar',
	'comments' => 'komentarji'
);

$lang ['search'] = array(
	'head' => 'Iskanje',
	'fset1' => 'Vnesite iskalne kriterije',
	'keywords' => 'Fraza',
	'onlytitles' => 'Samo naslovi',
	'fulltext' => 'Celoten besedilo',

	'fset2' => 'Datum',
	'datedescr' => 'Lahko omejite iskanje na določen datum. Lahko izberete leto, leto in mesec ali celotni datum. ' . 'Pustite prazno, da iščete po celotni zbirki.',

	'fset3' => 'Iskanje v kategorijah',
	'catdescr' => 'Če ne izberete nobene, bo iskanje zajeto v vseh kategorijah',

	'fset4' => 'Začni iskanje',
	'submit' => 'Išči',

	'headres' => 'Rezultati iskanja',
	'descrres' => 'Iskanje za <strong>%s</strong> je vrnilo naslednje rezultate:',
	'descrnores' => 'Iskanje za <strong>%s</strong> ni vrnilo rezultatov.',

	'moreopts' => 'Več možnosti',

	'searchag' => 'Ponovno iskanje'
);

$lang ['search'] ['error'] = array(
	'keywords' => 'Navedeti morate vsaj eno ključno besedo'
);

$lang ['staticauthor'] = array(
	// "Objavil(a)" na statičnih straneh
	'published_by'	=> 'Objavil(a)',
	'na' => 'na'
);

$lang ['entryauthor'] = array(
	// "Objavil(a)" na vnosih
	'posted_by'	=> 'Objavil(a)',
	'ob' => 'ob'
);

$lang ['entry'] = array();
$lang ['entry'] ['flags'] = array();

$lang ['entry'] ['flags'] ['long'] = array(
	'draft' => '<strong>Osnutek vnosa</strong>: skrito, čaka na objavo',
	// 'static' => '<strong>Statičen vnos</strong>: običajno skrit, za dostop do vnosa vnesite ?page=naslov-vnosa v URL (eksperimentalno)',
	'commslock' => '<strong>Zaklenjeni komentarji</strong>: komentarji niso dovoljeni za ta vnos'
);

$lang ['entry'] ['flags'] ['short'] = array(
	'draft' => 'Osnutek',
	// 'static' => 'Statičen',
	'commslock' => 'Zaklenjeni komentarji'
);

$lang ['404error'] = array(
	'subject' => 'Ni najdeno',
	'content' => '<p>Oprostite, zahtevane strani nismo našli</p>'
);

// Prijava
$lang ['login'] = array(
	'head' => 'Prijava',
	'fieldset1' => 'Vnesite vaše uporabniško ime in geslo',
	'user' => 'Uporabniško ime:',
	'pass' => 'Geslo:',
	'fieldset2' => 'Prijava',
	'submit' => 'Prijava',
	'forgot' => 'Pozabljeno geslo'
);

$lang ['login'] ['success'] = array(
	'success' => 'Sedaj ste prijavljeni.',
	'logout' => 'Sedaj ste odjavljeni.',
	'redirect' => 'Preusmerjeni boste v 5 sekundah.',
	'opt1' => 'Nazaj na začetno stran',
	'opt2' => 'Pojdi na administrativno območje',
	'opt3' => 'Dodaj nov vnos'
);

$lang ['login'] ['error'] = array(
	'user' => 'Vnesti morate uporabniško ime.',
	'pass' => 'Vnesti morate geslo.',
	'match' => 'Napačno geslo.'
);

$lang ['comments'] = array(
	'head' => 'Dodaj komentar',
	'descr' => 'Izpolnite spodnji obrazec, da dodate svoje komentarje',
	'fieldset1' => 'Podatki uporabnika',
	'name' => 'Ime (*)',
	'email' => 'E-pošta:',
	'www' => 'Spletna stran:',
	'cookie' => 'Zapomni si me',
	'fieldset2' => 'Dodajte svoj komentar',
	'comment' => 'Komentar (*):',
	'fieldset3' => 'Pošlji',
	'submit' => 'Dodaj',
	'reset' => 'Ponastavi',
	'success' => 'Vaš komentar je bil uspešno dodan',
	'nocomments' => 'Za ta vnos še ni komentarjev',
	'commslock' => 'Komentarji so onemogočeni za ta vnos'
);

$lang ['comments'] ['error'] = array(
	'name' => 'Vnesti morate ime',
	'email' => 'Vnesti morate veljaven e-poštni naslov',
	'www' => 'Vnesti morate veljavno spletno stran',
	'comment' => 'Vnesti morate komentar'
);

$lang ['postviews'] = array(
	// PostView-Plugin
	'views'	=> 'ogledi',
);

$lang ['date'] ['month'] = array(
	'januar',
	'februar',
	'marec',
	'april',
	'maj',
	'junij',
	'julij',
	'avgust',
	'september',
	'oktober',
	'november',
	'december'
);

$lang ['date'] ['month_abbr'] = array(
	'jan',
	'feb',
	'mar',
	'apr',
	'maj',
	'jun',
	'jul',
	'avg',
	'sep',
	'okt',
	'nov',
	'dec'
);

$lang ['date'] ['weekday'] = array(
	'nedelja',
	'ponedeljek',
	'torek',
	'sreda',
	'četrtek',
	'petek',
	'sobota'
);

$lang ['date'] ['weekday_abbr'] = array(
	'ned',
	'pon',
	'tor',
	'sre',
	'čet',
	'pet',
	'sob'
);
?>
