<?php
$lang ['admin'] ['entry'] ['submenu'] = array(
	'list' => 'Upravljanje Vnosov',
	'write' => 'Napiši Vnos',
	'cats' => 'Upravljanje Kategorij',
	'stats' => 'Statistika'
);

/* privzeta dejanja */
$lang ['admin'] ['entry'] ['list'] = array(
	'head' => 'Upravljanje Vnosov',
	'descr' => 'Izberite vnos za urejanje ali <a href="admin.php?p=entry&amp;action=write">dodajte novega</a><br>' . //
		'<a href="admin.php?p=entry&amp;action=cats">Uredi kategorije</a>',
	'drafts' => 'Osnutki: ',
	'filter' => 'Filter: ',
	'nofilter' => 'Prikaži vse',
	'filterbtn' => 'Uporabi filter',
	'sel' => 'Izberi', // polje za označevanje
	'date' => 'Datum',
	'title' => 'Naslov',
	'author' => 'Avtor',
	'comms' => '#Komentarji', // komentarji
	'action' => 'Dejanje',
	'act_del' => 'Izbriši',
	'act_view' => 'Poglej',
	'act_edit' => 'Uredi'
);

/* dejanje pisanja */
$lang ['admin'] ['entry'] ['write'] = array(
	'head' => 'Napiši Vnos',
	'descr' => 'Uredi obrazec za pisanje vnosa',
	'uploader' => 'Nalagalnik',
	'fieldset1' => 'Uredi',
	'subject' => 'Zadeva (*):',
	'content' => 'Vsebina (*):',
	'fieldset2' => 'Oddaj',
	'submit' => 'Objavi',
	'preview' => 'Predogled',
	'savecontinue' => 'Shrani in Nadaljuj',
	'categories' => 'Kategorije',
	'nocategories' => 'Niso nastavljene kategorije. <a href="admin.php?p=entry&amp;action=cats">Ustvarite svoje kategorije</a> iz glavnega upravljalskega okna vnosa. ' . //
		'<a href="#save">Najprej shranite</a> svoj vnos.',
	'saveopts' => 'Možnosti shranjevanja',
	'success' => 'Vaš vnos je bil uspešno objavljen',
	'otheropts' => 'Druge možnosti',
	'commmsg' => 'Upravljajte komentarje za ta vnos',
	'delmsg' => 'Izbriši ta vnos'
	// 'back' => 'Nazaj, zavrzi spremembe',
);

$lang ['admin'] ['entry'] ['list'] ['msgs'] = array(
	1 => 'Vnos je bil uspešno shranjen',
	-1 => 'Prišlo je do napake med poskusom shranjevanja vnosa',
	2 => 'Vnos je bil uspešno izbrisan',
	-2 => 'Prišlo je do napake med poskusom brisanja vnosa'
);

$lang ['admin'] ['entry'] ['write'] ['error'] = array(
	'subject' => 'Ne morete poslati prazne zadeve',
	'content' => 'Ne morete objaviti praznega vnosa'
);

$lang ['admin'] ['entry'] ['write'] ['msgs'] = array(
	1 => 'Vnos je bil uspešno shranjen',
	-1 => 'Prišlo je do napake: vaš vnos ni bil uspešno shranjen',
	-2 => 'Prišlo je do napake: vaš vnos ni bil shranjen; indeks bi se lahko pokvaril',
	-3 => 'Prišlo je do napake: vaš vnos je bil shranjen kot osnutek',
	-4 => 'Prišlo je do napake: vaš vnos je bil shranjen kot osnutek; indeks bi se lahko pokvaril',
	'draft' => 'Urejate <strong>osnutek</strong> vnosa'
);

/* komentarji */
$lang ['admin'] ['entry'] ['commentlist'] = array(
	'head' => 'Komentarji za vnos: ',
	'descr' => 'Tu lahko uredite ali izbrišete komentarje.',
	'sel' => 'Izberi',
	'content' => 'Vsebina',
	'date' => 'Datum',
	'author' => 'Avtor',
	'email' => 'E-pošta',
	'ip' => 'IP',
	'actions' => 'Dejanja',
	'act_edit' => 'Uredi',
	'act_del' => 'Izbriši',
	'act_del_confirm' => 'Ali res želite izbrisati ta komentar?',
	'nocomments' => 'Za ta vnos še ni komentarjev.'
);

$lang ['admin'] ['entry'] ['commentlist'] ['msgs'] = array(
	1 => 'Komentar je bil uspešno izbrisan',
	-1 => 'Prišlo je do napake med poskusom brisanja komentarja'
);

$lang ['admin'] ['entry'] ['commedit'] = array(
	'head' => 'Uredi komentar za vnos: ',
	'descr' => 'Tu lahko po želji uredite avtorjev komentar, ime, e-poštni naslov in spletno stran.<br><br>',
	'content' => 'Vsebina',
	'date' => 'Datum',
	'author' => 'Avtor',
	'www' => 'Spletna stran',
	'email' => 'E-pošta',
	'ip' => 'IP',
	'loggedin' => 'Prijavljen skrbnik',
	'submit' => 'Shrani',
	'commentlist' => 'Nazaj na pregled komentarjev'
);

$lang ['admin'] ['entry'] ['commedit'] ['error'] = array(
	'name' => 'Ime ne sme manjkati.',
	'email' => 'E-poštni naslov je napačen.',
	'url' => 'Spletna stran je napačna in se mora začeti s <strong>http://</strong> ali <strong>https://</strong>.',
	'content' => 'Komentar ne sme manjkati.'
);

$lang ['admin'] ['entry'] ['commedit'] ['msgs'] = array(
	1 => 'Komentar je bil urejen',
	-1 => 'Prišlo je do napake med poskusom urejanja komentarja'
);

/* brisanje dejanja */
$lang ['admin'] ['entry'] ['delete'] = array(
	'head' => 'Izbriši Vnos',
	'descr' => 'Izbrisali boste naslednji vnos: ',
	'preview' => 'Predogled',
	'confirm' => 'Ali ste prepričani, da želite nadaljevati?',
	'fset' => 'Izbriši',
	'ok' => 'Da, izbriši ta vnos',
	'cancel' => 'Ne, vrni me na ploščo',
	'err' => 'Določenega vnosa ni mogoče najti'
);

/* upravljanje kategorij */
$lang ['admin'] ['entry'] ['cats'] = array(
	'head' => 'Uredi kategorije',
	'descr' => '<p>Uporabite obrazec spodaj za dodajanje in urejanje vaših kategorij.</p>' . //
		'<p>Vsaka postavka kategorije mora biti v tem formatu "ime kategorije: <em>številka_id</em>". Indentirajte elemente z vezaji, da ustvarite hierarhije.</p>

	<p>Primer:</p>
	<pre>
Splošno :1
Novice :2
--Obvestila :3
--Dogodki :4
----Razno :5
Tehnologija :6
	</pre>',
	'clear' => 'Izbriši vse podatke o kategorijah',

	'fset1' => 'Urejevalnik',
	'fset2' => 'Uporabi Spremembe',
	'submit' => 'Shrani'
);

$lang ['admin'] ['entry'] ['cats'] ['msgs'] = array(
	1 => 'Kategorije so bile shranjene',
	-1 => 'Prišlo je do napake med poskusom shranjevanja kategorij',
	2 => 'Kategorije so bile izbrisane',
	-2 => 'Prišlo je do napake med poskusom brisanja kategorij',
	-3 => 'ID-ji kategorij morajo biti strogo pozitivni (0 ni dovoljeno)'
);

/* statistika */
$lang ['admin'] ['entry'] ['stats'] = array(
	'head' => 'Statistika',
	'entries' => 'Vnosi',
	'you_have' => 'Imate',
	'entries_using' => 'vnosov, ki uporabljajo',
	'characters_in' => 'znakov v',
	'words' => 'besedah',
	'total_disk_space_is' => 'Skupna velikost diska je',
	'comments' => 'Komentarji',
	'comments_using' => 'komentarjev, ki uporabljajo',
	'the' => 'Naj',
	'most_commented_entries' => 'najbolj komentirani vnosi'
);
?>
