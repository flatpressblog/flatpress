<?php
$lang ['admin'] ['uploader'] ['default'] = array(
	'head' => 'Transférer',
	'descr' => 'Choisissez un ou plusieurs fichiers à transférer.',
	'fset1' => 'Explorateur de fichiers',
	'fset2' => 'Transférer',
	'submit' => 'Transférer',
	'uploader_some_failed' => 'Certains fichiers n’ont pas pu être transférés en raison de problèmes de sécurité ou du système :',
	'uploader_metadata_failed' => 'Le fichier a été transféré, mais les métadonnées n’ont pas été supprimées :',
	'uploader_drop' => 'Déposez vos fichiers ici',
	'uploader_browse_hint' => '...ou cliquez pour choisir vos fichiers',
	'uploader_drop_active' => 'Relâchez pour ajouter',
	'uploader_selected_count' => '%d fichier(s) sélectionné(s)',
	'uploader_clear' => 'Effacer la sélection',
	'uploader_remove' => 'Retirer',
	'uploader_limit_files' => 'Nombre maximum de fichiers par transfert : %d.',
	'uploader_limit_size' => 'Taille totale maximum du transfert : %s.'
);

$lang ['admin'] ['uploader'] ['default'] ['msgs'] = array(
	1 => 'Fichier(s) transféré(s)',
	-1 => 'Échec du transfert.',
	-2 => 'Le serveur a refusé le téléchargement : la taille totale dépasse la limite "post_max_size" (%s).',
	-3 => 'Le serveur a rejeté le transfert. Aucune donnée n’a été reçue, probablement en raison de la taille des fichiers ou des restrictions serveur.',
	-4 => 'Aucun fichier n’a été reçu. Veuillez sélectionner au moins un fichier.'
);

$lang ['admin'] ['uploader'] ['browse'] = array(
	'head' => 'Parcourir',
	'descr' => 'Utilisez cet explorateur pour parcourir et gérer vos fichiers.',
	'fset1' => 'Fichiers disponibles',
	'fset2' => 'Aperçu',
	'submit' => 'Afficher le fichier'
);
?>