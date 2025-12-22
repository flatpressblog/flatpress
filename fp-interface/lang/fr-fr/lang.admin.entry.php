<?php
$lang ['admin'] ['entry'] ['submenu'] = array(
	'list' => 'Gérer les billets',
	'write' => 'Écrire un billet',
	'cats' => 'Gérer les catégories'
);

/* action par défaut */
$lang ['admin'] ['entry'] ['list'] = array(
	'head' => 'Gérer les billets',
	'descr' => 'Sélectionnez un billet à éditer ou <a href="admin.php?p=entry&amp;action=write">ajouter un nouveau billet</a><br>' . //
		'<a href="admin.php?p=entry&amp;action=cats">Éditer les catégories</a>',
	'drafts' => 'Brouillons : ',
	'filter' => 'Filtrer : ',
	'nofilter' => 'Tout afficher',
	'filterbtn' => 'Appliquer le filtre',
	'sel' => 'Sélection', // checkbox
	'date' => 'Date',
	'title' => 'Titre',
	'author' => 'Auteur',
	'comms' => '#Commentaires',
	'action' => 'Action',
	'act_del' => 'Effacer',
	'act_view' => 'Voir',
	'act_edit' => 'Éditer'
);

/* écrire action */
$lang ['admin'] ['entry'] ['write'] = array(
	'head' => 'Écrire un billet',
	'descr' => 'Remplir le formulaire pour écrire un billet',
	'uploader' => 'Uploader',
	'fieldset1' => 'Rédaction',
	'subject' => 'Sujet (*):',
	'content' => 'Contenu (*):',
	'fieldset2' => 'Soumission',
	'submit' => 'Publier',
	'preview' => 'Aperçu',
	'savecontinue' => 'Enregistrer et continuer',
	'categories' => 'Catégories',
	'nocategories' => 'Pas de catégories définies. <a href="admin.php?p=entry&amp;action=cats">Créer des catégories</a> dans le menu des catégories. ' . //
		'<a href="#save">Enregistrer</a> votre première entrée.',
	'saveopts' => 'Enregistrer les options',
	'success' => 'Votre billet a été publié',
	'otheropts' => 'Autres options',
	'commmsg' => 'Gérer les commentaires de ce billet',
	'delmsg' => 'Effacer ce billet'
);

$lang ['admin'] ['entry'] ['list'] ['msgs'] = array(
	1 => 'Le billet a été enregistré avec succès',
	-1 => 'Une erreur est survenue pendant l’enregistrement du billet',
	2 => 'Billet effacé',
	-2 => 'Une erreur est survenue pendant la suppression du billet'
);

$lang ['admin'] ['entry'] ['write'] ['error'] = array(
	'subject' => 'Veuillez compléter le champ du sujet',
	'content' => 'Veuillez remplir correctement le formulaire'
);

$lang ['admin'] ['entry'] ['write'] ['msgs'] = array(
	1 => 'Le billet a été enregistré avec succès',
	-1 => 'Une erreur est survenue : votre billet n’a pas pu être enregistré',
	-2 => 'Une erreur est survenue : votre billet n’a pas été enregistré ; l’index semble corrompu',
	-3 => 'Une erreur est survenue : votre billet n’a pas été enregistré',
	-4 => 'Une erreur est survenue : votre billet n’a pas été enregistré ; l’index semble corrompu',
	'draft' => 'Vous éditez actuellement un <strong>brouillon</strong>'
);

/* commentaires */
$lang ['admin'] ['entry'] ['commentlist'] = array(
	'head' => 'Commentaires du billet : ',
	'descr' => 'Sélectionnez un commentaire à effacer',
	'sel' => 'Sélection',
	'content' => 'Contenu',
	'date' => 'Date',
	'author' => 'Auteur',
	'email' => 'Email',
	'ip' => 'IP',
	'actions' => 'Actions',
	'act_edit' => 'Éditer',
	'act_del' => 'Effacer',
	'act_del_confirm' => 'Confirmez-vous la suppression du commentaire ?',
	'nocomments' => 'Pas de commentaires pour ce billet.'
);

$lang ['admin'] ['entry'] ['commentlist'] ['msgs'] = array(
	1 => 'Commentaire effacé avec succès',
	-1 => 'Une erreur est survenue pendant la suppression du commentaire'
);

$lang ['admin'] ['entry'] ['commedit'] = array(
	'head' => 'Éditer un commentaire du billet : ',
	'descr' => 'Vous pouvez modifier ici le commentaire, le nom, l’adresse email et le site web de l’auteur.',
	'content' => 'Contenu',
	'date' => 'Date',
	'author' => 'Auteur',
	'www' => 'Site Web',
	'email' => 'Email',
	'ip' => 'IP',
	'loggedin' => 'Administrateur connecté',
	'submit' => 'Enregistrer',
	'commentlist' => 'Retour à la liste des commentaires'
);

$lang ['admin'] ['entry'] ['commedit'] ['error'] = array(
	'name' => 'Le nom est requis.',
	'email' => 'L’adresse email est incorrecte.',
	'url' => 'L’URL est incorrecte et doit commencer par <strong>http://</strong> ou <strong>https://</strong>.',
	'content' => 'Un commentaire est requis.'
);

$lang ['admin'] ['entry'] ['commedit'] ['msgs'] = array(
	1 => 'Le commentaire a été modifié',
	-1 => 'Une erreur est survenue pendant la modification du commentaire'
);

/* suppression */
$lang ['admin'] ['entry'] ['delete'] = array(
	'head' => 'Effacer ce billet',
	'descr' => 'Vous allez supprimer le billet suivant : ',
	'preview' => 'Aperçu',
	'confirm' => 'Confirmer la suppression ?',
	'fset' => 'Supprimer',
	'ok' => 'Oui, supprimer ce billet',
	'cancel' => 'Non, retour au panneau',
	'err' => 'Le billet spécifié est introuvable'
);

/* gestion des catégories */
$lang ['admin'] ['entry'] ['cats'] = array(
	'head' => 'Éditer les catégories',
	'descr' => '<p>Utilisez le formulaire pour ajouter et modifier des catégories.</p>' . //
		'<p>Chaque élément doit être au format "nom de la catégorie : <em>numéro de la catégorie</em>". Placez des tirets devant les éléments pour créer des hiérarchies.</p>
		
	<p>Exemple:</p>
	<pre>
Accueil :1
Infos :2
--Annonces :3
--Evènements :4
----Misc :5
Technologie :6
	</pre>',
	'clear' => 'Effacer toutes les catégories',

	'fset1' => 'Éditeur',
	'fset2' => 'Appliquer les modifications',
	'submit' => 'Enregistrer les catégories'
);

$lang ['admin'] ['entry'] ['cats'] ['msgs'] = array(
	1 => 'Les catégories ont été enregistrées.',
	-1 => 'Une erreur est survenue pendant l’enregistrement des catégories.',
	2 => 'Les catégories ont été effacées.',
	-2 => 'Une erreur est survenue pendant l’effacement des catégories.',
	-3 => 'Le numéro de catégorie doit être supérieur à zéro.'
);
?>
