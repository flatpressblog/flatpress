<?php
$lang ['plugin'] ['prettyurls'] ['errors'] = array (
	-2 => 'Impossible de trouver ou de cr&eacute;er le fichier <code>.htaccess</code> dans le r&eacute;pertoire ' . //
		'principal. PrettyURLs peut ne pas fonctionner correctement, voyez le panneau de configuration.'
);

$lang ['admin'] ['plugin'] ['submenu'] ['prettyurls'] = 'PrettyURLs Config';
$lang ['admin'] ['plugin'] ['prettyurls'] = array(
	'head' => 'Configuration de PrettyURLs',
	'description1' => 'Ici, vous pouvez transformer les URL standard de FlatPress en URL conviviales adaptées au SEO.',
	'fpprotect_is_on' => 'Le plugin PrettyURLs nécessite un fichier .htaccess. ' . //
		'Pour créer ou modifier ce fichier, activez l’option dans le <a href="admin.php?p=config&action=fpprotect" title="aller au plugin FlatPress Protect">plugin FlatPress Protect</a>. ',
	'fpprotect_is_off' => 'Le plugin FlatPress Protect protège le fichier .htaccess contre les modifications involontaires. ' . //
		'Vous pouvez activer le plugin <a href="admin.php?p=plugin&action=default" title="Va dans la gestion des plugins">ici</a>&nbsp;!',
	'nginx' => 'PrettyURLs avec NGINX',
	'wiki_nginx' => 'https://wiki.flatpress.org/res:plugins:prettyurls#nginx',
	'htaccess' => '.htaccess',
	'description2' => 'Cet éditeur permet d’éditer directement le <code>.htaccess</code> nécessaire à l’extension PrettyUrls.<br>' . //
	'<strong>Remarque :</strong> seuls les serveurs web compatibles avec NCSA, comme Apache, connaissent le concept des fichiers .htaccess. ' . //
		'Votre logiciel serveur est : <strong>' . $_SERVER ['SERVER_SOFTWARE'] . '</strong>',
	'cantsave' => 'Vous ne pouvez pas &eacute;diter ce fichier, car il n’est pas autoris&eacute; en <strong>&eacute;criture</strong>. Autorisez l’&eacute;criture ou copiez le contenu vers un fichier &agrave; transf&eacute;rer.',
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

	'location' => '<strong>Lieu de stockage:</strong> ' . ABS_PATH . '',
	'submit' => 'Sauvegarder .htaccess'
);

$lang ['admin'] ['plugin'] ['prettyurls'] ['msgs'] = array(
	1 => '.htaccess enregistr&eacute;',
	-1 => '.htaccess n’a pas &eacute;t&eacute; enregistr&eacute; (v&eacute;rifiez les permissions de <code>' . BLOG_ROOT . '</code>)?',

	2 => 'Options sauvegard&eacute;es',
	-2 => 'Une erreur est survenue pendant la sauvegarde'
);
?>
