<?php
$lang = array();

$lang ['main'] = array(
	'nextpage' => 'Volgende pagina &raquo;',
	'prevpage' => '&laquo; Vorige pagina',
	'entry' => 'Data invoer',
	'entries' => 'Vermeldingen',
	'static' => 'Statische pagina',
	'preview' => 'Wijzig/Voorbeeld',

	'filed_under' => 'Geachiveerd onder ',

	'add_entry' => 'Voeg data invoer toe',
	'add_comment' => 'Voeg Commentaar toe',
	'add_static' => 'Voeg Statische Pagina toe',

	'btn_edit' => 'Wijzig',
	'btn_delete' => 'Verwijder',

	'nocomments' => 'Voeg een commentaar toe',
	'comment' => '1 commentaar',
	'comments' => 'commentaren',

	'rss' => 'Abonneren op RSS-feed',
	'atom' => 'Abonneren op Atom-feed'
);

$lang ['search'] = array(
	'head' => 'Zoek',
	'fset1' => 'Vul zoek criteria in',
	'keywords' => 'Zin',
	'onlytitles' => 'Alleen titels',
	'fulltext' => 'Volledige tekst',

	'fset2' => 'Datum',
	'datedescr' => 'Je kan zoeken op een specifieke datum. Je kan een jaar, een jaar en maand, of een volledige datum invoeren. ' . //
		'Laat het leeg om alle data invoer uit de database te tonen.',

	'fset3' => 'Zoek op categorie',
	'catdescr' => 'Selecteer niets om alles te zoeken',

	'fset4' => 'Start zoeken',
	'submit' => 'Zoek',

	'headres' => 'Zoek resultaten',
	'descrres' => 'Zoeken naar <strong>%s</strong> geeft de volgende resultaten:',
	'descrnores' => 'Zoeken naar <strong>%s</strong> geeft geen resultaten.',

	'moreopts' => 'Meer opties',

	'searchag' => 'Zoek opnieuw'
);

$lang ['search'] ['error'] = array(
	'keywords' => 'Je moet tenminste 1 zoekwoord invullen'
);

$lang ['staticauthor'] = array(
	// "Published by" in static pages
	'published_by' => 'Gepubliceerd door',
	'on' => 'op'
);

$lang ['entryauthor'] = array(
	// "Posted by" in entry pages
	'posted_by' => 'Geplaatst door',
	'at' => 'om'
);

$lang ['entry'] = array();
$lang ['entry'] ['flags'] = array();

$lang ['entry'] ['flags'] ['long'] = array(
	'draft' => '<strong>Concept vermelding</strong>: verborgen, wacht op publicatie',
	// 'static' => '<strong>Statische vermelding</strong>: normaal gesproken verborgen, om de data invoer te bereiken voer ?page=title-of-the-entry in url (experimenteel)',
	'commslock' => '<strong>Geblokkeerde commentaar</strong>: commentaar niet toegestaan voor deze data invoer'
);

$lang ['entry'] ['flags'] ['short'] = array(
	'draft' => 'Ontwerp',
	// 'static' => 'Statisch',
	'commslock' => 'Commentaar geblokt'
);

$lang ['entry'] ['categories'] = array(
	'unfiled' => 'Niet gestort'
);

$lang ['404error'] = array(
	'subject' => 'Niet gevonden',
	'content' => '<p>Sorry, we konden de door u gevraagde pagina niet vinden</p>'
);

// Login
$lang ['login'] = array(
	'head' => 'Login',
	'fieldset1' => 'Voeg uw gebruikersnaam en wachtwoord in',
	'user' => 'Gebruikersnaam:',
	'pass' => 'Wachtwoord:',
	'fieldset2' => 'Inloggen',
	'submit' => 'Inloggen',
	'forgot' => 'Wachtwoord vergeten'
);

$lang ['login'] ['success'] = array(
	'success' => 'Je bent nu ingelogd.',
	'logout' => 'Je bent nu uitgelogd.',
	'redirect' => 'U wordt binnen 5 seconden omgeleid.',
	'opt1' => 'Terug naar index',
	'opt2' => 'Ga naar Administratie omgeving',
	'opt3' => 'Voeg nieuwe data invoer in'
);

$lang ['login'] ['error'] = array(
	'user' => 'Je moet een gebruikersnaam in voeren.',
	'pass' => 'Je moet een wachtwoord invoeren.',
	'match' => 'Wachtwoord is fout.',
	'timeout' => 'Wacht 30 seconden voordat u het opnieuw probeert.'
);

$lang ['comments'] = array(
	'head' => 'Voeg commentaar in',
	'descr' => 'Vul het onderstaande formulier in om uw eigen opmerkingen toe te voegen',
	'fieldset1' => 'Gebruikersgegevens',
	'name' => 'Naam (*)',
	'email' => 'Email:',
	'www' => 'Web:',
	'cookie' => 'Herinner mij',
	'fieldset2' => 'Voeg je commentaar toe',
	'comment' => 'Commentaar (*):',
	'fieldset3' => 'Stuur',
	'submit' => 'Opslaan',
	'reset' => 'Reset',
	'success' => 'Uw commentaar is met succes toegevoegd',
	'nocomments' => 'Op dit bericht is nog niet gereageerd',
	'commslock' => 'Commentaren zijn uitgeschakeld voor dit bericht'
);

$lang ['comments'] ['error'] = array(
	'name' => 'Je moet een naam invoeren',
	'email' => 'Je moet een geldige e-mail invoeren',
	'www' => 'Je moet een geldige URL invoeren',
	'comment' => 'U moet een commentaar invoeren'
);

$lang ['postviews'] = array(
	// PostView-Plugin
	'views' => 'maal gezien'
);

$lang ['date'] ['month'] = array(
	'januari',
	'februari',
	'maart',
	'april',
	'mei',
	'juni',
	'juli',
	'augustus',
	'september',
	'oktober',
	'november',
	'december'
);

$lang ['date'] ['month_abbr'] = array(
	'jan',
	'feb',
	'mrt',
	'apr',
	'mei',
	'jun',
	'jul',
	'aug',
	'sep',
	'okt',
	'nov',
	'dec'
);

$lang ['date'] ['weekday'] = array(
	'zondag',
	'maandag',
	'dinsdag',
	'woensdag',
	'donderdag',
	'vrijdag',
	'zaterdag'
);

$lang ['date'] ['weekday_abbr'] = array(
	'zo',
	'ma',
	'di',
	'wo',
	'do',
	'vr',
	'za'
);
?>
