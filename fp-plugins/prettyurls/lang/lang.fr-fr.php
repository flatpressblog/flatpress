<?php
$lang ['plugin'] ['prettyurls'] ['errors'] = array (
	-2 => 'Impossible de trouver ou de cr&eacute;er le fichier <code>.htaccess</code> dans le r&eacute;pertoire ' . //
		'principal. PrettyURLs peut ne pas fonctionner correctement, voyez le panneau de configuration.'
);

$lang ['admin'] ['plugin'] ['submenu'] ['prettyurls'] = 'PrettyURLs Config';
$lang ['admin'] ['plugin'] ['prettyurls'] = array(
	'head' => 'Configuration de PrettyURLs',
	'description1' => 'Ici, tu peux transformer les URL standard de FlatPress en de jolies URL adaptées au SEO.',
	'nginx' => 'PrettyURLs avec NGINX',
	'wiki_nginx' => 'https://wiki.flatpress.org/res:plugins:prettyurls#nginx',
	'htaccess' => '.htaccess',
	'description2' => 'Cet éditeur permet d\'éditer directement le <code>.htaccess</code> nécessaire au plugin PrettyUrls.<br>' . //
		'<strong>Remarque:</strong> Seuls les serveurs web compatibles avec NCSA, comme Apache, connaissent le concept des fichiers .htaccess. ' . //
		'Ton logiciel serveur est: <strong>' . $_SERVER ['SERVER_SOFTWARE'] . '</strong>',
	'cantsave' => 'Vous ne pouvez pas &eacute;diter ce fichier, parce qu\'il n\'est pas autoris&eacute; en <strong>&eacute;criture</strong>. Vous devez autoriser l\'&eacute;criture du fichier ou copier-coller vers un fichier &agrave; transf&eacute;rer.',
	'mode' => 'Mode',
	'auto' => 'Automatique',
	'autodescr' => 'PrettyURLs va tenter de trouver la meilleur configuration',
	'pathinfo' => 'Path Info',
	'pathinfodescr' => 'Exemple: /index.php/2024/01/01/hello-world/',
	'httpget' => 'HTTP Get',
	'httpgetdescr' => 'Exemple: /?u=/2024/01/01/hello-world/',
	'pretty' => 'Pretty',
	'prettydescr' => 'Exemple: /2024/01/01/hello-world/',

	'saveopt' => 'Sauvegarder',

	'submit' => 'Sauvegarder .htaccess'
);

$lang ['admin'] ['plugin'] ['prettyurls'] ['msgs'] = array(
	1 => '.htaccess enregistr&eacute;',
	-1 => '.htaccess n\'a pas &eacute;t&eacute; enregistr&eacute; (v&eacute;rifiez les permissions de <code>' . BLOG_ROOT . '</code>)?',

	2 => 'Options sauvegard&eacute;es',
	-2 => 'Une erreur est survenue pendant de la sauvegarde'
);
?>
