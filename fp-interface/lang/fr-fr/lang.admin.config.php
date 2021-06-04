<?php

	$lang['admin']['config']['default'] = 
	array(
		'head'		=> 'Options',
		'descr'		=> 'Personnaliser et configurer FlatPress
					installation.',
		'submit'		=> 'Enregistrer',
		
		'sysfset'		=> 'Informations g&eacute;n&eacute;rales du syst&egrave;me',
		'syswarning'	=> '<big>Attention!</big> Ces informations sont indispensables et doivent &ecirc;tre correctes ou
	FlatPress ne pourra pas fonctionner correctement.',
		'blog_root'		=> '<strong>Chemin absolu de flatpress</strong>. Note: 
	vous ne devez g&eacute;n&eacute;ralement pas modifier ceci, n&eacute;anmoins il est recommand&eacute; de v&eacute;rifier si le chemin est correct.',
		'www'		=>'<strong>Adresse du blog (root)</strong>. Adresse de votre blog, en incluant les sous-r&eacute;pertoires. <br />
	e.g.: http://www.mondomaine.be/flatpress/ (le slash de fin est requis)',
		
		// ------
		
		'gensetts'		=> 'Configuration g&eacute;n&eacute;rale',
		'blogtitle'		=> 'Titre du Blog',
		'blogsubtitle'		=> 'Description du blog',
		'blogfooter'		=> 'Texte en bas de page du blog',
		'blogauthor'		=> 'Auteur du Blog',
		'startpage'			=> 'La page d\'accueil de ce site web est',
		'stdstartpage'		=> 'mon blog (d&eacute;faut)',
		'blogurl'			=> 'Url du blog',
		'blogemail'			=> 'Adresse email (notifications)',
		'notifications'		=> 'Notifications',
		'mailnotify'		=> 'Activer les notifications par email pour les commentaires',
		'blogmaxentries'	=> 'Nombre de sujets par page',
		'langchoice'		=> 'Langage',

		'intsetts'		=> 'R&eacute;glages Internationaux',
		'utctime'		=> '<acronym title="Universal Coordinated Time">L\'heure (UTC) </acronym>actuelle est',
		'timeoffset'		=> 'Fuseau horaire (GMT+)',
		'hours'			=> 'heures',
		'timeformat'		=> 'Format par d&eacute;faut pour l\'heure',
		'dateformat'		=> 'Format par d&eacute;faut pour la date',
		'dateformatshort'	=> 'Format par d&eacute;faut pour la date (court)',
		'output'		=> 'Pr&eacute;visualisation',
		'charset'		=> 'Encodage',
		'charsettip'	=> 'L\'encodage de votre blog est en UTF-8 '.
						'<a href="http://wiki.flatpress.org/doc:charsets">(recommand&eacute;)</a>'
		);
		
	$lang['admin']['config']['default']['msgs'] = 
	array(
		1		=> 'La configuration a &eacute;t&eacute; enregistr&eacute;e avec succ&egrave;s.',
		-1		=> 'Une erreur est apparue au moment de l\'enregistrement de la configuration!',
		
		);
			
	$lang['admin']['config']['default']['error'] = 
	array(
		'www' 		=>	'Adresse du Blog (root) doit avoir une URL valide',
		'title'		=>	'Vous devez ins&eacute;rer un titre',
		'email'		=>	'l\'adresse email n\'est pas au format correct',
		'maxentries'=>	'Vous n\'avez pas entr&eacute; un nombre correct d\'entr&eacute;es',
		'timeoffset'=>	'Vous n\'avez pas entr&eacute; un format d\'heure correct!'.
						'Vous pouvez utiliser des points flottants (ex: 2h30" => 2.5)',
		'timeformat'=>	'Vous devez ins&eacute;rer l\'heure au format correct',
		'dateformat'=>	'Vous devez ins&eacute;rer la date au format correct',
		'dateformatshort'=>	'Vous devez ins&eacute;rer la date au format correct (court)',
		'charset'	=>	'Vous devez choisir le format d\'encodage de votre blog',
		'lang'		=>	'La langue choisie n\'est pas disponible'
		);		
			
		
?>
