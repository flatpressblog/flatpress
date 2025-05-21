<?php

$lang['plugin']['qspam'] = array(
	'error' => 'ERREUR: le commentaire contient des mots bannis'
);

$lang['admin']['plugin']['submenu']['qspam'] = 'QuickSpamFilter';
$lang['admin']['plugin']['qspam'] = array(
	'head' => 'Configuration de QuickSpam',
	'desc1' => 'Ne pas autoriser les commentaires qui contiennent les mots suivants (un par ligne) :',
	'desc2' => '<strong>Attention:</strong> Un commentaire sera d&eacute;sactiv&eacute; s\'il contient un ou des mots bannis. 
	
	(Exemple: "old" est pr&eacute;sent dans le mot "b<em>old</em>" &eacute;galement)',
	'options' => 'Autres options',
	'desc3' => 'Compteur de mots bannis',
	'desc3pre' => 'Commentaires contenant plus de ',
	'desc3post' => ' mot(s) banni(s).',
	'submit' => 'Enregistrer la configuration',
	'msgs' => array(
		1 => 'Mots bannis enregistr&eacute;s.',
		-1 => 'Echec de l\'enregistrement des mots bannis.'
	)
);

?>
