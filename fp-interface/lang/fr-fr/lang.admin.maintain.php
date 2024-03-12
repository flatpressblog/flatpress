<?php
$lang ['admin'] ['panel'] ['maintain'] = 'Maintenance';

$lang ['admin'] ['maintain'] ['default'] = array(
	'head' => 'Maintenance',
	'descr' => 'Venez ici lorsque vous croyez que quelque chose cloche. peut-&ecirc;tre trouverez vous une solution!',
	'opt0' => '&laquo; Retour au menu principal',
	'opt1' => 'Reconstruire les index',
	'opt2' => 'Purger le cache des th&egrave;mes et des templates',
	'opt3' => 'Restaurer les permissions de fichiers',
	'opt4' => 'Afficher info.php',
	'opt5' => 'V&eacute;rifier les mises &agrave; jour',
	'opt6' => 'Afficher les données de support',

	'chmod_info' => 'Si les permissions du fichier <strong>n\'ont pas pu être remises à ' . decoct(FILE_PERMISSIONS) . '</strong>, il est probable que le propriétaire du fichier ne soit pas le même que celui du serveur web.<br>' . //
		'tre diff&eacute;rent du serveur web.'
);

$lang ['admin'] ['maintain'] ['default'] ['msgs'] = array(
	1 => 'Op&eacute;ration effectu&eacute;e',
	-1 => '&Eacute;chec de l\'op&eacute;ration'
);
	
$lang ['admin'] ['maintain'] ['updates'] = array(
	'head' => 'Mises &agrave; jour',
	'list' => '<ul>
		<li>Votre version de FlatPress <big>%s</big></li>
		<li>La derni&egrave;re version stable de FlatPress est <big><a href="%s">%s</a></big></li>
		<li>La derni&egrave;re version dev(beta, rc) de FlatPress est <big><a href="%s">%s</a></big></li>
		</ul>',
	'notice' => 'Note:'
);

$lang ['admin'] ['maintain'] ['updates'] ['msgs'] = array(
	1 => 'Mises &agrave; jour disponibles!',
	2 => 'Vous avez d&eacute;j&agrave; la version la plus r&eacute;cente!',
	-1 => 'Impossible de V&eacute;rifier les mises &agrave; jour'
);
?>
