<?php
$lang = array();

$lang ['main'] = array(
	'nextpage' => 'Hurrengo orria &raquo;',
	'prevpage' => '&laquo; Aurreko orria',
	'entry' => 'Sarrera',
	'entries' => 'Sarrerak',
	'static' => 'Orri estatikoa',
	'preview' => 'Editatu/Aurrebista',

	'filed_under' => 'Filed under ',

	'add_entry' => 'Add Entry',
	'add_comment' => 'Add Comment',
	'add_static' => 'Add Static Page',

	'btn_edit' => 'Editatu',
	'btn_delete' => 'Ezabatu',

	'nocomments' => 'Iruzkindu',
	'comment' => 'Iruzkin 1',
	'comments' => 'iruzkin',

	'rss' => 'Subscribe RSS feed',
	'atom' => 'Subscribe Atom feed'
);

$lang ['search'] = array(
	'head' => 'Bilatu',
	'fset1' => 'Insert search criteria',
	'keywords' => 'Phrase',
	'onlytitles' => 'Soilik izenburuak',
	'fulltext' => 'Full-text',

	'fset2' => 'Data',
	'datedescr' => 'You can bind your search to a specific date. You may select an year, an year and a month, or a full date. ' . //
		'Leave blank to search the entire database.',

	'fset3' => 'Search in categories',
	'catdescr' => 'Don\'t select any to search all',

	'fset4' => 'Hasi bilatzen',
	'submit' => 'Bilatu',

	'headres' => 'Search Results',
	'descrres' => 'Searching for <strong>%s</strong> returned the following results:',
	'descrnores' => 'Searching for <strong>%s</strong> returned no results.',

	'moreopts' => 'Aukera gehiago',

	'searchag' => 'Bilatu berriro',
);

$lang ['search'] ['error'] = array(
	'keywords' => 'You must specify at least one keyword'
);

$lang ['staticauthor'] = array(
	// "Published by" in static pages
	'published_by' => 'Honek argitaratua',
	'on' => 'on'
);

$lang ['entryauthor'] = array(
	// "Posted by" in entry pages
	'posted_by' => 'Honek bidalia',
	'at' => 'at'
);

$lang ['entry'] = array();
$lang ['entry'] ['flags'] = array();

$lang ['entry'] ['flags'] ['long'] = array(
	'draft' => '<strong>Draft entry</strong>: hidden, awaiting publication',
	// 'static' => '<strong>Static entry</strong>: normally hidden, to reach the entry put ?page=title-of-the-entry in url (experimental)',
	'commslock' => '<strong>Comments locked</strong>: comments disallowed for this entry'
);

$lang ['entry'] ['flags'] ['short'] = array(
	'draft' => 'Zirriborroa',
	// 'static' => 'Estatikoa',
	'commslock' => 'Iruzkinak blokeatuta'
);

$lang ['entry'] ['categories'] = array(
	'unfiled' => 'Unfiled'
);

$lang ['404error'] = array(
	'subject' => 'Not Found',
	'content' => '<p>Sorry, we could not find the page you requested</p>'
);

// Login
$lang ['login'] = array(
	'head' => 'Login',
	'fieldset1' => 'Insert your user name and password',
	'user' => 'Erabiltzailea:',
	'pass' => 'Pasahitza:',
	'fieldset2' => 'Hasi saioa',
	'submit' => 'Hasi saioa',
	'forgot' => 'Password lost'
);

$lang ['login'] ['success'] = array(
	'success' => 'You are now logged in.',
	'logout' => 'You are now logged out.',
	'redirect' => 'You will be redirected in 5 seconds.',
	'opt1' => 'Back to index',
	'opt2' => 'Go to Admin Area',
	'opt3' => 'Add new entry'
);

$lang ['login'] ['error'] = array(
	'user' => 'Erabiltzaile-izen bat sartu behar duzu.',
	'pass' => 'Pasahitz bat sartu behar duzu.',
	'match' => 'Pasahitza ez da zuzena.',
	'timeout' => 'Mesedez, itxaron 30 segundo berriro saiatu aurretik.'
);

$lang ['comments'] = array(
	'head' => 'Add comment',
	'descr' => 'Fill out the form below to add your own comments',
	'fieldset1' => 'User data',
	'name' => 'Name (*)',
	'email' => 'Email:',
	'www' => 'Web:',
	'cookie' => 'Remember me',
	'fieldset2' => 'Add your comment',
	'comment' => 'Comment (*):',
	'fieldset3' => 'Send',
	'submit' => 'Add',
	'reset' => 'Reset',
	'success' => 'Your comment was added successfully',
	'nocomments' => 'This entry have not been commented yet',
	'commslock' => 'Comments have been disabled for this entry'
);

$lang ['comments'] ['error'] = array(
	'name' => 'Izen bat sartu behar duzu.',
	'email' => 'Baliozko helbide elektroniko bat sartu behar duzu.',
	'www' => 'Baliozko URL bat sartu behar duzu.',
	'comment' => 'Iruzkin bat sartu behar duzu.'
);

$lang ['postviews'] = array(
	// PostView-Plugin
	'views' => 'views',
);

$lang ['date'] ['month'] = array(
	'urtarrila',
	'otsaila',
	'martxoa',
	'apirila',
	'maiatza',
	'ekaina',
	'uztaila',
	'abuztua',
	'iraila',
	'urria',
	'azaroa',
	'abendua'
);

$lang ['date'] ['month_abbr'] = array(
	'Urt',
	'Ots',
	'Mar',
	'Api',
	'Mai',
	'Eka',
	'Uzt',
	'Abu',
	'Ira',
	'Urr',
	'Aza',
	'Abe'
);

$lang ['date'] ['weekday'] = array(
	'Igandea',
	'Astelehena',
	'Asteartea',
	'Asteazkena',
	'Osteguna',
	'Ostirala',
	'Larunbata'
);

$lang ['date'] ['weekday_abbr'] = array(
	'Ig',
	'Al',
	'Ar',
	'Az',
	'Og',
	'Ol',
	'Lr'
);
?>
