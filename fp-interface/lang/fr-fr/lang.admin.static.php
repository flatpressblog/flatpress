<?php
$lang ['admin'] ['static'] ['submenu'] = array(
	'list' => 'Gérer pages statiques',
	'write' => 'Écrire'
);

/* main panel */
$lang ['admin'] ['static'] ['list'] = array(
	'head' => 'Pages statiques',
	'descr' => 'Sélectionnez une page à éditer ou <a href="admin.php?p=static&amp;action=write">ajoutez-en une nouvelle</a>.',

	'sel' => 'Sél', // checkbox
	'date' => 'Date',
	'name' => 'Page',
	'title' => 'Titre',
	'author' => 'Auteur',

	'action' => 'Action',
	'act_view' => 'Voir',
	'act_del' => 'Supprimer',
	'act_edit' => 'Éditer',

	'natural' => 'Trier les titres par ordre décroissant plutôt que par date de création.',
	'submit' => 'Réorganiser les noms de page'
);

$lang ['admin'] ['static'] ['list'] ['msgs'] = array(
	1 => 'Page enregistrée avec succès',
	-1 => 'Échec de la sauvegarde de la page',
	2 => 'La page a été supprimée',
	-2 => 'Échec de la suppression de la page'
);

/* write panel */
$lang ['admin'] ['static'] ['write'] = array(
	'head' => 'Publier une page statique',
	'descr' => 'Éditez le formulaire pour publier la page',
	'fieldset1' => 'Éditer',
	'subject' => 'Sujet (*) :',
	'content' => 'Contenu (*) :',
	'fieldset2' => 'Soumettre',
	'pagename' => 'Nom de la page (*) :',
	'submit' => 'Publier',
	'preview' => 'Aperçu',

	'delfset' => 'Supprimer',
	'deletemsg' => 'Supprimer cette page',
	'del' => 'Supprimer',
	'success' => 'Votre page a été publiée',
	'otheropts' => 'Autres options',
);

$lang ['admin'] ['static'] ['write'] ['error'] = array(
	'subject' => 'Complétez le sujet',
	'content' => 'Complétez les champs requis',
	'id' => 'Vous devez entrer un identifiant valide'
);

/* delete action */	
$lang ['admin'] ['static'] ['delete'] = array(
	'head' => 'Supprimer la page', 
	'descr' => 'Vous allez supprimer la page suivante :',
	'preview' => 'Aperçu',
	'confirm' => 'Confirmer la suppression ?',
	'fset' => 'Supprimer',
	'ok' => 'Oui, supprimer cette page',
	'cancel' => 'Non, retour au panneau',
	'err' => 'La page spécifiée n’existe pas'
);
?>
