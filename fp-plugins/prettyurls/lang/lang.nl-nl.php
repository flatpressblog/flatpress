<?php
$lang ['plugin'] ['prettyurls'] ['errors'] = array (
	-2 => 'Ik vind geen of kan geen <code>.htaccess</code> bestand maken in jouw root ' . //
		'directorie. PrettyURLs might not work properly, ie het configuratie menu.'
);

$lang ['admin'] ['plugin'] ['submenu'] ['prettyurls'] = 'PrettyURLs Configuratie';
$lang ['admin'] ['plugin'] ['prettyurls'] = array(
	'head' => 'PrettyURLs Configuratie',
	'description1' => 'Hier kun je de standaard FlatPress URL\'s omzetten in mooie, SEO-vriendelijke URL\'s.',
	'nginx' => 'PrettyURLs met NGINX',
	'wiki_nginx' => 'https://wiki.flatpress.org/res:plugins:prettyurls#nginx',
	'htaccess' => '.htaccess',
	'description2' => 'Met deze editor kun je direct de <code>.htaccess</code> bewerken die nodig is voor de PrettyURLs plugin.<br>' . //
		'<strong>Opmerking:</strong> Alleen webservers die NCSA-compatibel zijn, zoals Apache, herkennen het concept van .htaccess-bestanden. ' . //
		'Uw serversoftware is: <strong>' . $_SERVER ['SERVER_SOFTWARE'] . '</strong>',
	'cantsave' => 'U kunt dit bestand niet bewerken omdat het niet <strong>schrijfbaar</strong> is. U kunt schrijfmachtigingen geven of een kopie maken en plakken in een bestand en vervolgens handmatig uploaden.',
	'mode' => 'Modus',
	'auto' => 'Automatisch',
	'autodescr' => 'probeer de beste keuze voor mij te raden',
	'pathinfo' => 'Path Info',
	'pathinfodescr' => 'e.g. /index.php/2024/01/01/hello-world/',
	'httpget' => 'HTTP Get',
	'httpgetdescr' => 'e.g. /?u=/2024/01/01/hello-world/',
	'pretty' => 'Pretty',
	'prettydescr' => 'e.g. /2024/01/01/hello-world/',

	'saveopt' => 'Instellingen opslaan',

	'location' => '<strong>Opslaglocatie:</strong> ' . ABS_PATH . '',
	'submit' => 'Bewaar .htaccess'
);

$lang ['admin'] ['plugin'] ['prettyurls'] ['msgs'] = array(
	1 => '.htaccess opgeslagen',
	-1 => '.htaccess kon niet worden opgeslagen (heb je schrijfrechten op <code>' . BLOG_ROOT . '</code>)?',

	2 => 'Opties opgeslagen',
	-2 => 'Er is een fout opgetreden bij het opslaan van instellingen'
);
?>
