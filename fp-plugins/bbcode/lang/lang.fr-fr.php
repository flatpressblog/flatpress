<?php

$lang['admin']['plugin']['submenu']['bbcode'] = 'BBCode';
$lang['admin']['plugin']['bbcode'] = array(
	'head' => 'Configuration du BBCode',
	'desc1' => 'Ce plugin autorise l\'usage du <a href="http://www.phpbb.com/'.
		'phpBB/faq.php?mode=bbcode">BBCode</a> et permet une int&eacute;gration '.
		'automatique avec une lightbox (si disponible).',
	
	'options' => 'Options',

	'editing'	=> 'Edition',
	'allow_html'=> 'Prise en charge de l\'HTML',
	'allow_html_long' => 'Autoriser l\'usage de l\'HTML avec le BBCode',
	'toolbar' => 'Barre d\'outils',
	'toolbar_long' => 'Activer la Barre d\'outils d\'&eacute;dition.',

	'other'	=>	'Autres options',
	'comments' => 'Commentaires',
	'comments_long' => 'Autoriser le BBCode dans les commentaires',
	'urlmaxlen' => 'Longueur maximum URL',
	'urlmaxlen_long_pre' => 'Pas plus longue que ',
	'urlmaxlen_long_post'=>' caract&egrave;res.',
	'submit' => 'Enregistrer la configuration',
	'msgs' => array(
		1 => 'Configuration BBCode enregistr&eacute;e avec succ&egrave;s.',
		-1 => 'Configuration BBCode non enregistr&eacute;e.'
	),

	'editor' => array(
		'formatting'     => 'Format',
		'textarea'       => 'Zone de texte: ',
		'expand'         => 'Elargir',
		'expandtitle'    => 'Elargir la hauteur de la zone de texte',
		'reduce'         => 'R&eacute;duire',
		'reducetitle'    => 'R&eacute;duire la hauteur de la zone de texte',
		// note: accesskeys are not internationalized...
		// btw. why not :-D
		'bold'           => 'G',
		'boldtitle'      => 'Gras',
		'italic'         => 'I',
		'italictitle'    => 'Italique',
		'underline'      => 'S',
		'underlinetitle' => 'Soulign&eacute;',
		'quote'          => 'Citation',
		'quotetitle'     => 'Citation',
		'code'           => 'Code',
		'codetitle'      => 'Code',
		'help'           => 'Aide BBCode',
		// currently not used
		'status'         => 'barre de statut',
		'statusbar'      => 'Mode Normal. Pressez &lt;Esc&gt; pour passer en mode &eacute;dition .'
	)
);

?>
