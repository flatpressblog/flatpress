<?php
$lang ['admin'] ['plugin'] ['submenu'] ['bbcode'] = 'BBCode';
$lang ['admin'] ['plugin'] ['bbcode'] = array(
	'head' => 'Configuration du BBCode',
	'desc1' => 'Ce plugin autorise l\'usage du <a href="https://wiki.flatpress.org/' . //
		'doc:techfaq#bbcode" class="hint" target="_blank">BBCode</a>.',

	'options' => 'Options',

	'editing' => 'Edition',
	'allow_html' => 'Prise en charge de l\'HTML',
	'allow_html_long' => 'Autoriser l\'usage de l\'HTML avec le BBCode',
	'toolbar' => 'Barre d\'outils',
	'toolbar_long' => 'Activer la Barre d\'outils d\'&eacute;dition.',

	'other' =>'Autres options',
	'comments' => 'Commentaires',
	'comments_long' => 'Autoriser le BBCode dans les commentaires',
	'urlmaxlen' => 'Longueur maximum URL',
	'urlmaxlen_long_pre' => 'Pas plus longue que ',
	'urlmaxlen_long_post'=>' caract&egrave;res.',

	'attachsdir' => 'Téléchargement de fichiers',
	'attachsdir_long' => 'Ne pas afficher le répertoire de téléchargement (fp-content/attachs/) dans l\'URL.',

	'submit' => 'Enregistrer la configuration',
	'msgs' => array(
		1 => 'Configuration BBCode enregistr&eacute;e avec succ&egrave;s.',
		-1 => 'Configuration BBCode non enregistr&eacute;e.'
	),

	'editor' => array(
		'formatting' => 'Format',
		'textarea' => 'Zone de texte: ',
		'expand' => 'Elargir',
		'expandtitle' => 'Elargir la hauteur de la zone de texte',
		'reduce' => 'R&eacute;duire',
		'reducetitle' => 'R&eacute;duire la hauteur de la zone de texte',
		'urltitle' => 'URL/ lien',
		'mailtitle' => 'Adresse e-mail', 
		'boldtitle' => 'Gras',
		'italictitle' => 'Italique',
		'headlinetitle' => 'Titre',
		'underlinetitle' => 'Soulign&eacute;',
		'crossouttitle' => 'Barré',
		'unorderedlisttitle' => 'Liste non triée',
		'orderedlisttitle' => 'Liste triée',
		'quotetitle' => 'Citation',
		'codetitle' => 'Code',
		'htmltitle' => 'Insérer en tant que code HTML',
		'help' => 'Aide BBCode',
		'file' => 'Fichier: ',
		'image' => 'Image: ',
		'selection' => '-- Sélection --'
	)
);

$lang ['plugin'] ['bbcode'] = array (
	'go_to' => 'Aller à',

	// Filewrapper get.php
	'error_403' => 'Erreur 403', 
	'not_send' => 'Le fichier demandé ne peut pas être envoyé.',
	'error_404' => 'Erreur 404',
	'not_found' => 'Le fichier demandé n\'a pas pu être trouvé.',
	'file' => 'Fichier',
	'report_error_1' => '',
	'report_error_2' => 'Signaler une erreur',
	'blog_search_1' => '',
	'blog_search_2' => 'chercher dans le blog',
	'start_page_1' => 'ou retour',
	'start_page_2' => 'à la page d\'accueil'
);
?>
