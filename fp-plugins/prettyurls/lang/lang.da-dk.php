<?php
$lang ['plugin'] ['prettyurls'] ['errors'] = array (
	-2 => 'Der kan ikke oprettes en <code>.htaccess</code> eller en <code>.htaccess</code> i blogroden. ' . //
		'PrettyURLs-plugin\'et fungerer så muligvis ikke korrekt.'
);

$lang ['admin'] ['plugin'] ['submenu'] ['prettyurls'] = 'PrettyURLs';
$lang ['admin'] ['plugin'] ['prettyurls'] = array(
	'head' => 'PrettyURLs Konfiguration',
	'description1' => 'Her kan du forvandle FlatPress\' standard-URL\'er til smukke, SEO-venlige URL\'er.',
	'nginx' => 'PrettyURLs med NGINX',
	'wiki_nginx' => 'https://wiki.flatpress.org/res:plugins:prettyurls#nginx',
	'htaccess' => '.htaccess',
	'description2' => 'Denne editor giver dig mulighed for direkte at redigere den <code>.htaccess</code>, der kræves til PrettyUrls-pluginet.<br>' . //
		'<strong>Bemærk:</strong> Kun webservere, der er NCSA-kompatible, såsom Apache, anerkender begrebet .htaccess-filer. ' . //
		'Din serversoftware er: <strong>' . $_SERVER ['SERVER_SOFTWARE'] . '</strong>',
	'cantsave' => 'Denne fil kan ikke redigeres, fordi den er skrivebeskyttet. Ændr adgangsrettighederne, eller kopier disse linjer, indsæt dem i en lokal fil, og upload den derefter.',
	'mode' => 'Tilstand',
	'auto' => 'Automatisk',
	'autodescr' => 'Find automatisk den bedste løsning',
	'pathinfo' => 'Sti-info',
	'pathinfodescr' => 'e.g. /index.php/2024/01/01/hello-world/',
	'httpget' => 'HTTP Get',
	'httpgetdescr' => 'e.g. /?u=/2024/01/01/hello-world/',
	'pretty' => 'Pretty',
	'prettydescr' => 'e.g. /2024/01/01/hello-world/',

	'saveopt' => 'Gem indstillinger',

	'submit' => '.htaccess gem'
);

$lang ['admin'] ['plugin'] ['prettyurls'] ['msgs'] = array(
	1 => '.htaccess-filen blev gemt med succes',
	-1 => '.htaccess-filen kunne ikke gemmes (ingen skriverettigheder i <code>' . BLOG_ROOT . '</code>)?',

	2 => 'Indstillingerne blev gemt med succes',
	-2 => 'Der opstod en fejl under lagring'
);
?>
