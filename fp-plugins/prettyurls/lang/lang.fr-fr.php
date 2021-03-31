<?php
	$lang['plugin']['prettyurls']['errors'] = array (
		-2	=> 'Impossible de trouver ou de cr&eacute;er le fichier <code>.htaccess</code> dans le r&eacute;pertoire '.
				'principal. PrettyURLs peut ne pas fonctionner correctement, voyez le panneau de configuration.'
	);
	
	$lang['admin']['plugin']['submenu']['prettyurls'] = 'PrettyURLs Config';
	$lang['admin']['plugin']['prettyurls'] = array(
		'head'		=> 'Configuration de PrettyURLs',
		'htaccess'	=> '.htaccess',
		'description'=>'Cet &eacute;diteur vous permet de modifier le fichier .htaccess '.
						'<code>.htaccess</code>.',
		'cantsave'	=> 'Vous ne pouvez pas &eacute;diter ce fichier, parce qu\'il n\'est pas autoris&eacute; en <strong>&eacute;criture</strong>. Vous devez autoriser l\'&eacute;criture du fichier ou copier-coller vers un fichier &agrave; transf&eacute;rer.',
		'mode'		=> 'Mode',
		'auto'		=> 'Automatique',
			'autodescr'	=> 'PrettyURLs va tenter de trouver la meilleur configuration',
		'pathinfo'	=> 'Path Info',
			'pathinfodescr' => 'Exemple: /index.php/2011/01/01/hello-world/',
		'httpget'	=> 'HTTP Get',
			'httpgetdescr'=> 'Exemple: /?u=/2011/01/01/hello-world/',
		'pretty'	=> 'Pretty',
			'prettydescr'=> 'Exemple: /2011/01/01/hello-world/',

		'saveopt' 	=> 'Sauvegarder',

		'submit'	=> 'Sauvegarder .htaccess'
	);
	$lang['admin']['plugin']['prettyurls']['msgs'] = array(
		1		=> '.htaccess enregistr&eacute;',
		-1		=> '.htaccess n\'a pas &eacute;t&eacute; enregistr&eacute; (v&eacute;rifiez les permissions de <code>'. BLOG_ROOT .'</code>)?',

		2		=> 'Options sauvegard&eacute;es',
		-2		=> 'Une erreur est survenue pendant de la sauvegarde',
	);
	
?>
