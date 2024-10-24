<?php
$lang ['plugin'] ['prettyurls'] ['errors'] = array (
	-2 => 'Ik vind geen of kan geen <code>.htaccess</code> bestand maken in jouw root ' . //
		'directorie. PrettyURLs might not work properly, ie het configuratie menu.'
);

$lang ['admin'] ['plugin'] ['submenu'] ['prettyurls'] = 'PrettyURLs Configuratie';
$lang ['admin'] ['plugin'] ['prettyurls'] = array(
	'head' => 'PrettyURLs Configuratie',
	'description1' => 'Hier kun je de standaard FlatPress URL\'s omzetten in mooie, SEO-vriendelijke URL\'s.',
	'fpprotect_is_on' => 'De PrettyURLs plugin heeft een .htaccess bestand nodig. ' . //
		'Als u dit bestand wilt maken of wijzigen, activeert u de optie in de <a href="admin.php?p=config&action=fpprotect" title="ga naar FlatPress Beschermen Plugin">FlatPress Protect plugin</a>. ',
	'fpprotect_is_off' => 'De FlatPress Protect plugin beschermt het .htaccess bestand tegen onbedoelde wijzigingen. ' . //
		'U kunt de plugin <a href="admin.php?p=plugin&action=default" title="Ga naar de plugin administratie">hier</a> activeren!',
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
