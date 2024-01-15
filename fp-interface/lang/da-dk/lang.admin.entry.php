<?php
$lang ['admin'] ['entry'] ['submenu'] = array(
	'list' => 'Administrer bidrag',
	'write' => 'Skriv et bidrag',
	'cats' => 'Administrer kategorier',
	'stats' => 'Statistik'
);

/* default action */
$lang ['admin'] ['entry'] ['list'] = array(
	'head' => 'Administrer indlæg og kategorier',
	'descr' => 'På dette tidspunkt kan du vælge indlæg at redigere, skrive et <a href="admin.php?p=entry&amp;action=write">nyt indlæg</a> eller ' . //
		'<a href="admin.php?p=entry&amp;action=cats">Rediger kategorier</a>. Det er også muligt at slette kommentarer fra indlæg.',
	'filter' => 'Filter: ',
	'drafts' => 'Design: ',
	'nofilter' => 'Vis alle',
	'filterbtn' => 'Anvend filter',
	'sel' => 'Sel', // checkbox
	'date' => 'Dato',
	'title' => 'Titel',
	'author' => 'Forfatter',
	'comms' => '#Kommentarer', // comments
	'action' => 'Handling',
	'act_del' => 'Slet',
	'act_view' => 'Se',
	'act_edit' => 'Rediger'
);

/* write action */
$lang ['admin'] ['entry'] ['write'] = array(
	'head' => 'Skriv et bidrag',
	'descr' => 'Udfyld venligst for at skrive et nyt indlæg',
	'uploader' => 'Uploader',
	'fieldset1' => 'Rediger',
	'subject' => 'Titel (*):',
	'content' => 'Indhold (*):',
	'fieldset2' => 'Send',
	'submit' => 'Publicer',
	'preview' => 'Forhåndsvisning',
	'savecontinue' => 'Spar og mere',
	'categories' => 'Vælg kategori for posten',
	'nocategories' => 'Ingen kategori valgt. <a href="admin.php?p=entry&amp;action=cats">Opret en kategori</a> i administrationsområdet. ' . //
		'<a href="#save">gem venligst indlægget på forhånd</a>.',
	'saveopts' => 'Opbevaringsmuligheder',
	'success' => 'Dit bidrag blev offentliggjort med succes',
	'otheropts' => 'Andre muligheder',
	'commmsg' => 'Administrer kommentarerne til dette indlæg',
	'delmsg' => 'Slet dette indlæg'
	// 'back' => 'Back discarding changes',
);

$lang ['admin'] ['entry'] ['list'] ['msgs'] = array(
	1 => 'Indlægget blev gemt med succes',
	-1 => 'Der opstod en fejl under lagring af indlægget',
	2 => 'Indlægget blev slettet med succes',
	-2 => 'Der opstod en fejl under sletning af indlægget'
);

$lang ['admin'] ['entry'] ['write'] ['error'] = array(
	'subject' => 'Der blev ikke givet nogen titel',
	'content' => 'Der er intet indhold'
);

$lang ['admin'] ['entry'] ['write'] ['msgs'] = array(
	1 => 'Indlægget blev gemt med succes',
	-1 => 'Der er opstået en fejl: Indlægget kunne ikke gemmes med succes',
	-2 => 'Der er opstået en fejl: Indlægget blev ikke gemt; muligvis er indekset beskadiget',
	-3 => 'Der er opstået en fejl: Indlægget blev arkiveret som en kladde af sikkerhedshensyn',
	-4 => 'Der er opstået en fejl: Bidraget blev arkiveret som et udkast for en sikkerheds skyld; indekset kan være blevet beskadiget',
	'draft' => 'Du er ved at redigere et indlæg i <strong>Udkast</strong>-tilstand.'
);

/* comments */
$lang ['admin'] ['entry'] ['commentlist'] = array(
	'head' => 'Kommentarer til indlægget: ',
	'descr' => 'Vælg venligst kommentar, der skal slettes',
	'sel' => 'Sel',
	'content' => 'Indhold',
	'date' => 'Dato',
	'author' => 'Forfatter',
	'email' => 'E-Mail',
	'ip' => 'IP',
	'actions' => 'Handling',
	'act_edit' => 'Rediger',
	'act_del' => 'Slet',
	'act_del_confirm' => 'Vil du virkelig slette denne kommentar?',
	'nocomments' => 'Dette indlæg indeholder i øjeblikket ingen kommentarer.'
);

$lang ['admin'] ['entry'] ['commentlist'] ['msgs'] = array(
	1 => 'Kommentaren blev slettet med succes',
	-1 => 'Der opstod en fejl under sletning af kommentaren'
);

$lang ['admin'] ['entry'] ['commedit'] = array(
	'head' => 'Rediger kommentar til indlæg',
	'descr' => 'Her kan du redigere en forfatters kommentar, hans navn, hans e-mailadresse, hans hjemmeside og hans IP-adresse, som du vil.<br><br>',
	'content' => 'Kommentar indhold',
	'date' => 'Dato',
	'author' => 'Forfatter',
	'www' => 'Hjemmeside',
	'email' => 'E-Mail',
	'ip' => 'IP-adresse',
	'loggedin' => 'Logget ind som administrator',
	'submit' => 'Gem ændringer'
);

$lang ['admin'] ['entry'] ['commedit'] ['msgs'] = array(
	1 => 'Kommentaren blev ændret',
	-1 => 'Der opstod en fejl ved ændring af kommentaren'
);

/* delete action */
$lang ['admin'] ['entry'] ['delete'] = array(
	'head' => 'Slet indlæg',
	'descr' => 'Du har valgt at slette dette indlæg:',
	'preview' => 'Forhåndsvisning',
	'confirm' => 'Vil du virkelig slette dette indlæg?',
	'fset' => 'Slet',
	'ok' => 'Ja, slet dette indlæg',
	'cancel' => 'Nej, tilbage til administrationen',
	'err' => 'Det valgte indlæg findes ikke'
);

/* category mgmt */
$lang ['admin'] ['entry'] ['cats'] = array(
	'head' => 'Administrer kategorier',
	'descr' => '<p>Hver kategori skal oprettes i skemaet "Category Name <em>:id_number</em>". "<em>id_nummer</em>" er <strong>unikt</strong> tildelt bidragene, må <strong>ikke</strong> ændres og skal være større end <strong>0</strong>. Kategorinavnet kan derimod stadig ændres senere.</p>' . //
		'<p>En senere ændring af kategorirækkefølgen er til enhver tid mulig. Bindestreger kan bruges til at oprette underkategorier.</p>
		
	<p>Eksempel:</p>
	<pre>
Generelt :1
Nyheder :2
--Meddelelser :5
--Begivenheder :3
----Diverse :6
Teknologi :4
	</pre>',
	'clear' => 'Slet alle kategorier',

	'fset1' => 'Redaktør',
	'fset2' => 'Udfør ændringer',
	'submit' => 'Gem'
);

$lang ['admin'] ['entry'] ['cats'] ['msgs'] = array(

	1 => 'Gemte kategorier',
	-1 => 'Der opstod en fejl, da kategorierne blev gemt',
	2 => 'Alle kategorier slettet',
	-2 => 'Der opstod en fejl ved sletning af kategorierne',
	-3 => 'Kategori ID <strong>skal være større end 0</strong>. Værdien <strong>0</strong> er ikke tilladt.'
);

/* stats */
$lang ['admin'] ['entry'] ['stats'] = array(
	'head' => 'Statistik',
	'entries' => 'Bidrag',
	'you_have' => 'Du har',
	'entries_using' => 'indlæg med',
	'characters_in' => 'tegn i',
	'words' => 'ord',
	'total_disk_space_is' => 'Den samlede lagerplads er',
	'comments' => 'Kommentarer',
	'comments_using' => 'Kommentarer med',
	'the' => 'De',
	'most_commented_entries' => 'mest kommenterede indlæg'
);
?>
