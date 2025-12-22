<?php
$lang ['plugin'] ['qspam'] = array(
	'error' => 'ERREUR : le commentaire contient des mots interdits'
);

$lang ['admin'] ['entry'] ['submenu'] ['qspam'] = 'QuickSpamFilter';
$lang ['admin'] ['entry'] ['qspam'] = array(
	'head' => 'Configuration de QuickSpam',
	'desc1' => 'Refuser les commentaires contenant les mots suivants (un par ligne) :',
	'desc2' => '<strong>Attention :</strong> un commentaire sera refusé s’il contient un mot interdit. (Exemple : « old » est aussi présent dans « b<em>old</em> »)',
	'options' => 'Autres options',
	'desc3' => 'Compteur de mots bannis',
	'desc3pre' => 'Commentaires contenant plus de ',
	'desc3post' => ' mot(s) interdit(s).',
	'submit' => 'Enregistrer la configuration',
	'msgs' => array(
		1 => 'Mots interdits enregistrés.',
		-1 => 'Échec de l’enregistrement des mots interdits.'
	)
);
?>
