<?php
$lang = array();

$lang ['main'] = array(
	'nextpage' => 'Næste side &raquo;',
	'prevpage' => '&laquo; Forrige side',
	'entry' => 'Bidrag',
	'static' => 'Statisk side',
	'comment' => 'Kommentar',
	'preview' => 'Rediger/gennemse',
		
	'filed_under' => 'Arkiveret under ',	
		
	'add_entry' => 'Tilføj bidrag',
	'add_comment' => 'Tilføj kommentar',
	'add_static' => 'Tilføj statisk side',
		
	'btn_edit' => 'Rediger',
	'btn_delete' => 'Slet',
		
	'nocomments' => 'Tilføj kommentar',
	'comment' => '1 Kommentar',
	'comments' => 'Kommentarer'
);
	
$lang ['search'] = array(
	'head' => 'Søgning',
	'fset1'	=> 'Indsæt søgekriterier',
	'keywords' => 'Søg på ord',
	'onlytitles' => 'Søg kun i titler',
	'fulltext' => 'Fuldtekstsøgning',
		
	'fset2'	=> 'Søg efter dato',
	'datedescr'	=> 'Du kan søge efter en hvilken som helst dato. Kriterierne kan være: År, år og måned eller som en komplet dato. ' . 'Uden information bliver alt gennemsøgt.',
		
	'fset3' => 'Søg i kategorier',
	'catdescr' => 'Der skal angives mindst én kategori.',
		
	'fset4' => 'Start søgning',
	'submit' => 'Start søgning',
		
	'headres' => 'Søgeresultater',
	'descrres' => 'Søgningen efter <strong>%s</strong> gav følgende resultater:',
	'descrnores' => 'Søgningen efter <strong>%s</strong> var resultatløs.',
		
	'moreopts' => 'Flere muligheder',
		
	'searchag' => 'Gentag søgning'
);
	
$lang ['search'] ['error'] = array(

	'keywords' => 'Der skal angives mindst ét søgekriterium.'
);
	
$lang ['staticauthor'] = array(
	// "Published by" in static pages
	'published_by' => 'Udgivet af',
	'on' => 'den'
);

$lang ['entryauthor'] = array(
	// "Posted by" in entry pages
	'posted_by'	=> 'Indsendt af',
	'at' => 'på'
);

$lang ['entry'] = array();
$lang ['entry'] ['flags'] = array();
	
$lang ['entry'] ['flags'] ['long'] = array(
	'draft' => '<strong>Gem indlæg som kladde</strong>: Vil kun være synlig, når den er offentliggjort.',
	//'static' => '<strong>Static entry</strong>: normally hidden, to reach the entry put ?page=title-of-the-entry in url (experimental)',
	'commslock' => '<strong>Lås kommentarer</strong>: Tillad ikke kommentarer til dette indlæg.'
);
	
$lang ['entry'] ['flags'] ['short'] = array(
	'draft' => 'Kladder',
	//'static' => 'Static',
	'commslock' => 'Kommentarer blokeret.'
);

$lang ['entry'] ['categories'] = array(
	'unfiled' => 'Ikke deponeret'
);

$lang ['404error'] = array(
	'subject' => 'Ikke fundet',
	'content' => '<p>Beklager, der blev ikke fundet noget passende til denne anmodning.</p>'
);
		
// Login
$lang ['login'] = array(
		
	'head' => 'Log ind',
	'fieldset1'	=> 'Indtast venligst bruger og adgangskode',
	'user' => 'Bruger:',
	'pass' => 'Adgangskode:',
	'fieldset2'	=> 'Log ind',
	'submit' => 'Log ind',
	'forgot' => 'Glemt adgangskode'
);
		
$lang ['login'] ['success'] = array(
	'success' => 'Du er logget ind.',
	'logout' => 'Du er logget ud.',
	'redirect' => 'Automatisk omdirigering til bloggen på 5 sekunder.',
	'opt1' => 'Tilbage til bloggen',
	'opt2' => 'Til administrationsmenuen',
	'opt3' => 'Opret nyt indlæg'
);
	
$lang ['login'] ['error'] = array(
	'user' => 'Angiv venligst bruger.',
	'pass' => 'Indtast venligst adgangskode.',
	'match' => 'Bruger eller adgangskode er forkert.'
);
	
$lang ['comments'] = array(
	'head' => 'Tilføj kommentar',
	'descr' => 'Felterne Navn og Kommentar er obligatoriske.',
	'fieldset1'	=> 'Dine oplysninger',
	'name' => 'Navn (nødvendigt)',
	'email' => 'E-mail-adresse (vil ikke blive offentliggjort):',
	'www' => 'Hjemmeside (valgfri):',
	'cookie' => 'Husk datoer til næste gang',
	'fieldset2'	=> 'Skriv en kommentar',
	'comment' => 'Kommentar:',
	'fieldset3'	=> 'Send',
	'submit' => 'Send',
	'reset' => 'Nulstil',
	'success' => 'Din kommentar blev tilføjet med succes',
	'nocomments' => 'Ingen kommentarer tilgængelige endnu',
	'commslock'	=> 'Ingen kommentarer er mulige til denne post'
);
	
$lang ['comments'] ['error'] = array(
	'name' => 'Indtast venligst et navn',
	'email' => 'Indtast venligst en gyldig e-mailadresse',
	'www' => 'Indtast venligst en gyldig URL',
	'comment' => 'Skriv venligst en kommentar'
);
	
$lang ['postviews'] = array(
	// PostView-Plugin
	'views' => 'Opkald(er)'
);

$lang ['date'] ['month'] = array(		
	'Januar',
	'Februar',
	'Marts',
	'April',
	'Maj',
	'Juni',
	'Juli',
	'August',
	'September',
	'Oktober',
	'November',
	'December'	
);

$lang ['date'] ['month_abbr'] = array(
	'Jan',
	'Feb',
	'Mar',
	'Apr',
	'Mai',
	'Jun',
	'Jul',
	'Aug',
	'Sep',
	'Okt',
	'Nov',
	'Dec'
);

$lang ['date'] ['weekday'] = array(
	'Søndag',		
	'Mandag',
	'Tirsdag',
	'Onsdag',
	'Torsdag',
	'Fredag',
	'Lørdag'		
);

$lang ['date'] ['weekday_abbr'] = array(
	'Sø',		
	'Ma',
	'Ti',
	'On',
	'To',
	'Fr',
	'Sø'
);
?>
