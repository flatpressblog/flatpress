<?php
$lang ['admin'] ['static'] ['submenu'] = array(
	'list' => 'Gérer les pages statiques',
	'write' => 'Écrire une page'
);

/* panneau principal */
$lang ['admin'] ['static'] ['list'] = array(
	'head' => 'Pages statiques',
	'descr' => 'Sélectionnez une page à éditer ou <a href="admin.php?p=static&amp;action=write">ajoutez-en une nouvelle</a>.',

	'sel' => 'Sélection', // checkbox
	'date' => 'Date',
	'name' => 'Page',
	'title' => 'Titre',
	'author' => 'Auteur',

	'action' => 'Action',
	'act_view' => 'Voir',
	'act_del' => 'Effacer',
	'act_edit' => 'Éditer',

	'natural' => 'Trier les titres par ordre décroissant plutôt que par date de création.',
	'submit' => 'Réorganiser les noms des pages'
);

$lang ['admin'] ['static'] ['list'] ['msgs'] = array(
	1 => 'La page a été enregistrée avec succès',
	-1 => 'Échec de la sauvegarde de la page',
	2 => 'La page a été supprimée',
	-2 => 'Échec de la suppression de la page'
);

/* panneau de rédaction */
$lang ['admin'] ['static'] ['write'] = array(
	'head' => 'Publier une page statique',
	'descr' => 'Remplissez le formulaire pour publier une nouvelle page.',
	'fieldset1' => 'Informations de la page',
	'subject' => 'Titre (*) :',
	'content' => 'Contenu (*) :',
	'fieldset2' => 'Soumettre',
	'pagename' => 'Nom de la page (*) :',
	'submit' => 'Publier',
	'preview' => 'Prévisualiser',

	'delfset' => 'Supprimer',
	'deletemsg' => 'Supprimer cette page',
	'del' => 'Effacer',
	'success' => 'Votre page a été publiée avec succès',
	'otheropts' => 'Options avancées'
);

$lang ['admin'] ['static'] ['write'] ['error'] = array(
	'subject' => 'Veuillez compléter le champ pour le titre.',
	'content' => 'Veuillez remplir le contenu de la page.',
	'id' => 'L’identifiant de la page doit être valide'
);

/* action de suppression */
$lang ['admin'] ['static'] ['delete'] = array(
	'head' => 'Supprimer une page', 
	'descr' => 'Vous êtes sur le point de supprimer la page suivante :',
	'preview' => 'Aperçu',
	'confirm' => 'Confirmez-vous la suppression ?',
	'fset' => 'Supprimer',
	'ok' => 'Oui, supprimer cette page',
	'cancel' => 'Non, retourner au menu',
	'err' => 'La page spécifiée est introuvable'
);
?>