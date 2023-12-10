<?php
$lang ['admin'] ['config'] ['default'] = array(
	'head' => 'Konfiguration',
	'descr' => 'Konfigurer og tilpas FlatPress.',
	'submit' => 'Anvend indstillinger',

	'sysfset' => 'Generelle indstillinger',
	'syswarning' => '<big>Advarsel!</big> Disse indstillinger skal indtastes omhyggeligt, ellers fungerer FlatPress muligvis ikke korrekt.',
	'blog_root' => '<strong>Absolut sti til FlatPress</strong>. Et tip: ' . //
		'Normalt er der ikke noget, der skal ændres her. FlatPress tilbyder ikke en intern funktion til selv at tjekke mulige ændringer.',
	'www' => '<strong>Blog Root</strong>. URL på din blog med angivelse af katalog.<br>' . //
		'Eksempel: http://www.mydomain.com/flatpress/ (sidste skråstreg er påkrævet)',

	// ------
	'gensetts' => 'Grundlæggende indstillinger',
	'blogtitle' => 'Blog Titel',
	'blogsubtitle' => 'Bloggens undertitel',
	'blogfooter' => 'Bloggens fodområde',
	'blogauthor' => 'Blogforfatter',
	'startpage' => 'Hjemmesiden for denne blog er',
	'stdstartpage' => 'Min blog (standard)',
	'blogurl' => 'Blog URL',
	'blogemail' => 'Blog E-Mail',
	'notifications' => 'Meddelelser',
	'mailnotify' => 'Aktivér e-mailnotifikation for nye kommentarer',
	'blogmaxentries' => 'Antal indlæg pr. side',
	'langchoice' => 'Sprog',

	'intsetts' => 'Internationale indstillinger',
	'utctime' => '<abbr title="Universal Coordinated Time">UTC</abbr> Tidszone',
	'timeoffset' => 'Tid, der skal korrigeres med',
	'hours' => 'timer',
	'timeformat' => 'Standard tidsformat',
	'dateformat' => 'Standard datoformat',
	'dateformatshort' => 'Standard datoformat (kort)',
	'output' => 'Output',
	'charset' => 'Tegnsæt',
	'charsettip' => 'Det anbefalede tegnsæt til FlatPress er ' . //
		'<a class="hint" href="https://wiki.flatpress.org/doc:techfaq#character_encoding" target="_blank" title="Hvilke tegnkodningsstandarder understøttes af FlatPress?">UTF-8</a>.'
);

$lang ['admin'] ['config'] ['default'] ['msgs'] = array(
	1 => 'Konfigurationen er blevet gemt med succes.',
	-1 => 'Der opstod en fejl, da konfigurationen blev gemt.'
);

$lang ['admin'] ['config'] ['default'] ['error'] = array(
	'www' => 'Blogroden skal have en gyldig URL',
	'title' => 'Du skal indtaste en titel',
	'email' => 'E-mailadressen skal have et gyldigt format',
	'maxentries' => 'Du har indtastet et ugyldigt nummer for indlæggene',
	'timeoffset' => 'Du har indtastet en ugyldig tidskorrektion! Kommaer accepteres også (eksempel: 2h30" => 2.5)',
	'timeformat' => 'Formatet for klokkeslættet er ugyldigt',
	'dateformat' => 'Formatet for datoen er ugyldigt',
	'dateformatshort' => 'Indtast venligst en gyldig kort dato',
	'charset' => 'Det angivne tegnsæt er ugyldigt',
	'lang' => 'Det valgte sprog er ikke tilgængeligt'
);
?>
