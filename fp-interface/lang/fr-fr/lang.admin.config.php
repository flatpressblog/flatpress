<?php
$lang ['admin'] ['config'] ['default'] = array(
	'head' => 'Options',
	'descr' => 'Personnalisez et configurez l’installation de FlatPress.',
	'submit' => 'Sauvegarder',

	'sysfset' => 'Informations générales du système',
	'syswarning' => 'Attention ! Ces informations sont critiques et doivent être exactes, sinon FlatPress pourrait ne pas fonctionner correctement.',
	'blog_root' => '<strong>Chemin absolu vers FlatPress</strong>. Remarque : ' . //
		'vous ne devez généralement pas modifier ceci, mais il est recommandé de vérifier si ce chemin est correct.',
	'www' => '<strong>Adresse du blog (racine)</strong>. Spécifiez l’adresse complète de votre blog, y compris tous les sous-répertoires.<br>' . //
		'par exemple : http://www.monblog.fr/flatpress/ (le slash à la fin est requis)',

	// ------
	'gensetts' => 'Paramètres globaux',
	'adminname' => 'Nom de l’administrateur',
	'adminpassword' => 'Nouveau mot de passe',
	'adminpasswordconfirm' => 'Confirmez le mot de passe',
	'blogtitle' => 'Titre du blog',
	'blogsubtitle' => 'Sous-titre du blog',
	'blogfooter' => 'Pied de page du blog',
	'blogauthor' => 'Auteur du blog',
	'startpage' => 'La page d’accueil par défaut de ce site web est',
	'stdstartpage' => 'Mon blog (par défaut)',
	'blogurl' => 'URL du blog',
	'blogemail' => 'Adresse courriel (notifications)',
	'notifications' => 'Options de notification',
	'mailnotify' => 'Activer les notifications par courriel pour les commentaires',
	'blogmaxentries' => 'Nombre d’articles affichés par page',
	'langchoice' => 'Langue utilisée',

	'intsetts' => 'Internationalisation',
	'utctime' => '<abbr title="Universal Coordinated Time">L’heure (UTC) </abbr>actuelle est',
	'timeoffset' => 'Fuseau horaire (exemple : GMT+)',
	'hours' => 'heures',
	'timeformat' => 'Format par défaut pour l’heure',
	'dateformat' => 'Format par défaut pour la date',
	'dateformatshort' => 'Format abrégé pour la date',
	'output' => 'Aperçu',
	'charset' => 'Encodage',
	'charsettip' => 'L’encodage de votre blog est en UTF-8 ' . //
		'<a href="https://wiki.flatpress.org/doc:techfaq#character_encoding" title="Quelles sont les normes d’encodage de caractères prises en charge par FlatPress ?">(recommandé)</a>.'
);

$lang ['admin'] ['config'] ['default'] ['msgs'] = array(
	1 => 'La configuration a été enregistrée avec succès.',
	-1 => 'Une erreur est survenue lors de l’enregistrement de la configuration.'
);

$lang ['admin'] ['config'] ['default'] ['error'] = array(
	'www' => 'L’adresse racine du blog doit être une URL valide.',
	'title' => 'Vous devez saisir un titre.',
	'email' => 'L’adresse courriel n’est pas valide.',
	'maxentries' => 'Vous n’avez pas renseigné un nombre d’articles valide.',
	'timeoffset' => 'Vous n’avez pas spécifié un fuseau horaire correct ! Vous pouvez utiliser des nombres décimaux (exemple : 2h30 = 2.5).',
	'timeformat' => 'Vous devez indiquer un format horaire valide.',
	'dateformat' => 'Vous devez indiquer un format de date valide.',
	'dateformatshort' => 'Vous devez indiquer un format abrégé de date valide.',
	'charset' => 'Vous devez sélectionner un encodage pour votre blog.',
	'lang' => 'La langue choisie n’est pas disponible.',
	'admin' => 'Le nom de l’administrateur ne peut contenir que des lettres, des chiffres et un underscore.',
	'password' => 'Le mot de passe doit contenir au moins 6 caractères et ne pas inclure d’espaces.',
	'confirm_password' => 'Les mots de passe ne correspondent pas.'
);
?>
