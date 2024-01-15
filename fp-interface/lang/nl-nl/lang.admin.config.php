<?php
$lang ['admin'] ['config'] ['default'] = array(
	'head' => 'Opties',
	'descr' => 'Aanpassen en configureren van de FlatPress installatie.',
	'submit' => 'Bewaar aanpassingen',

	'sysfset' => 'Algemene systeeminformatie',
	'syswarning' => '<big>Warschuwing!</big> Deze informatie is van cruciaal belang en moet correct zijn, anders zal FlatPress  (waarschijnlijk) weigeren om goed te werken.',
	'blog_root' => '<strong>Absoluut pad naar FlatPress</strong>. Opmerking: ' . //
		'over het algemeen hoeft u dit niet te bewerken, wees hoe dan ook voorzichtig, want we kunnen niet controleren of het correct is of niet.',
	'www' =>'<strong>Blog root</strong>. URL naar de blog, compleet met subdirectories.<br>' . //
		'VB: https://www.mydomain.com/flatpress/ (voorwaard slash is nodig)',

	// ------
	'gensetts' => 'Algemene instellingen',
	'blogtitle' => 'Blog titel',
	'blogsubtitle' => 'Blog subtitel',
	'blogfooter' => 'Blog voettekst',
	'blogauthor' => 'Blog auteur',
	'startpage' => 'De home page van deze website is',
	'stdstartpage' => 'mijn blog (default)',
	'blogurl' => 'Blog URL',
	'blogemail' => 'Blog e-mail',
	'notifications' => 'Notificaties',
	'mailnotify' => 'E-mailmelding inschakelen voor opmerkingen',
	'blogmaxentries' => 'Aantal berichten per pagina',
	'langchoice' => 'Taal',

	'intsetts' => 'Internationale instellingen',
	'utctime' => '<abbr title="Universal Coordinated Time">UTC</abbr> time is',
	'timeoffset' => 'De tijd moet verschuiven per',
	'hours' => 'uren',
	'timeformat' => 'Default format voor tijd',
	'dateformat' => 'Default format voor datum',
	'dateformatshort' => 'Default format voor datum kort',
	'output' => 'Output',
	'charset' => 'Character set',
	'charsettip' => 'De tekenset waarin je je blog schrijft (UTF-8 is ' . //
		'<a class="hint" href="https://wiki.flatpress.org/doc:techfaq#character_encoding" target="_blank" title="Welke tekencoderingsstandaarden worden ondersteund door FlatPress?">aanbevolen</a>).'
);

$lang ['admin'] ['config'] ['default'] ['msgs'] = array(
	1 => 'Configuratie opgeslagen',
	-1 => 'Er is een fout opgetreden tijdens het opslaan van de configuratie'
);

$lang ['admin'] ['config'] ['default'] ['error'] = array(
	'www' => 'Blog root moet een geldige URL zijn',
	'title' => 'U moet een titel opgeven',
	'email' => 'E-mail moet een geldige indeling hebben',
	'maxentries' => 'U hebt geen geldig aantal vermeldingen ingevoerd',
	'timeoffset' => 'U hebt geen geldige tijdverschuiving ingevoerd! U kunt drijvende komma gebruiken (bijv. 2u30" => 2.5)',
	'timeformat' => 'U moet een opmaaktekenreeks voor tijd invoegen',
	'dateformat' => 'U moet een opmaaktekenreeks voor datum invoegen',
	'dateformatshort' => 'U moet een opmaaktekenreeks voor korte datum invoegen',
	'charset' => 'U moet een tekenset-id invoegen',
	'lang' => 'De taal die u hebt gekozen, is niet beschikbaar'
);
?>
