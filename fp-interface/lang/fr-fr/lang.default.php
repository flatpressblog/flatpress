<?php
$lang = array();

$lang ['main'] = array(
	'nextpage' => 'Page suivante',
	'prevpage' => 'Page précédente',
	'entry' => 'Billet',
	'entries' => 'Billets',
	'static' => 'Page statique',
	'preview' => 'Éditer/aperçu',

	'filed_under' => 'Classé sous ',	

	'add_entry' => 'Ajouter un billet',
	'add_comment' => 'Ajouter un commentaire',
	'add_static' => 'Ajouter une page statique',

	'btn_edit' => 'Éditer',
	'btn_delete' => 'Supprimer',

	'nocomments' => 'Ajouter un commentaire',
	'comment' => '1 commentaire',
	'comments' => 'commentaires',

	'rss' => 'S’abonner au flux RSS',
	'atom' => 'S’abonner au flux Atom'
);

$lang ['search'] = array(
	'head' => 'Rechercher',
	'fset1' => 'Insérer un critère de recherche',
	'keywords' => 'Phrase',
	'onlytitles' => 'Seulement les titres',
	'fulltext' => 'Texte en entier',

	'fset2' => 'Date',
	'datedescr' => 'Vous pouvez affiner votre recherche à une date spécifique. Vous pouvez sélectionner une année, une année et un mois, ou une date complète. ' . //
		'Laissez vide pour chercher dans l’ensemble de la base de données.',

	'fset3' => 'Rechercher dans les catégories',
	'catdescr' => 'Laissez vide pour rechercher dans la totalité',

	'fset4' => 'Commencer la recherche',
	'submit' => 'Chercher',

	'headres' => 'Résultats de la recherche',
	'descrres' => 'La recherche de <strong>%s</strong> a donné les résultats suivants :',
	'descrnores' => 'La recherche de <strong>%s</strong> a donné les résultats suivants :',

	'moreopts' => 'Plus d’options',

	'searchag' => 'Nouvelle recherche'
);

$lang ['search'] ['error'] = array(
	'keywords' => 'Vous devez spécifier au moins un mot-clé'
);

$lang ['staticauthor'] = array(
	// "Published by" in static pages
	'published_by' => 'Publié par',
	'on' => 'le'
);

$lang ['entryauthor'] = array(
	// "Posted by" in entry pages
	'posted_by' => 'Posté par',
	'at' => 'à'
);

$lang ['entry'] = array();
$lang ['entry'] ['flags'] = array();

$lang ['entry'] ['flags'] ['long'] = array(
	'draft' => '<strong>Billet brouillon</strong> : caché, en attente de publication',
	//'static' => '<strong>Static entry</strong>: normally hidden, to reach the entry put ?page=title-of-the-entry in url (experimental)',
	'commslock' => '<strong>Commentaires désactivés</strong> : commentaires désactivés pour ce billet'
);

$lang ['entry'] ['flags'] ['short'] = array(
	'draft' => 'Brouillon',
	//'static' => 'Static',
	'commslock' => 'Commentaires désactivés'
);

$lang ['entry'] ['categories'] = array(
	'unfiled' => 'Non déposé'
);

$lang ['404error'] = array(
	'subject' => 'Page non trouvée',
	'content' => '<p>Désolé, la page demandée n’a pas été trouvée !</p>'
);

// Login
$lang ['login'] = array(
	'head' => 'Identifiant',
	'fieldset1' => 'Insérez vos identifiants',
	'user' => 'Nom d’utilisateur :',
	'pass' => 'Mot de passe :',
	'fieldset2' => 'Se connecter',
	'submit' => 'Connexion',
	'forgot' => 'Mot de passe oublié ?'
);

$lang ['login'] ['success'] = array(
	'success' => 'Vous êtes connecté.',
	'logout' => 'Vous êtes déconnecté.',
	'redirect' => 'Vous serez redirigé dans 5 secondes.',
	'opt1' => 'Retour à l’index',
	'opt2' => 'Aller au panneau de contrôle',
	'opt3' => 'Ajouter un nouveau billet'
);

$lang ['login'] ['error'] = array(
	'user' => 'Vous devez entrer un nom d’utilisateur.',
	'pass' => 'Vous devez entrer un mot de passe.',
	'match' => 'Mot de passe incorrect.',
	'timeout' => 'Veuillez attendre 30 secondes avant de réessayer.'
);

$lang ['comments'] = array(
	'head' => 'Ajouter un commentaire',
	'descr' => 'Remplissez le formulaire ci-dessous pour ajouter vos propres commentaires',
	'fieldset1' => 'Données utilisateur',
	'name' => 'Nom (*)',
	'email' => 'Courriel :',
	'www' => 'Web :',
	'cookie' => 'Se souvenir de moi',
	'fieldset2' => 'Ajouter votre commentaire',
	'comment' => 'Commentaire (*) :',
	'fieldset3' => 'Envoyer',
	'submit' => 'Ajouter',
	'reset' => 'Réinitialiser',
	'success' => 'Votre commentaire a été ajouté avec succès',
	'nocomments' => 'Pas de commentaires pour ce billet',
	'commslock' => 'Les commentaires ont été désactivés pour ce billet'
);

$lang ['comments'] ['error'] = array(
	'name' => 'Vous devez entrer un nom',
	'email' => 'Vous devez entrer une adresse courriel valide',
	'www' => 'Vous devez entrer une URL valide',
	'comment' => 'Vous devez écrire un commentaire'
);

$lang ['postviews'] = array(
	// PostView-Plugin
	'views' => 'Vues'
);

$lang ['date'] ['month'] = array(
	'Janvier',
	'Février',
	'Mars',
	'Avril',
	'Mai',
	'Juin',
	'Juillet',
	'Août',
	'Septembre',
	'Octobre',
	'Novembre',
	'Décembre'
);

$lang ['date'] ['month_abbr'] = array(
	'Jan',
	'Fév',
	'Mar',
	'Avr',
	'Mai',
	'Jun',
	'Jul',
	'Aoû',
	'Sep',
	'Oct',
	'Nov',
	'Déc'
);

$lang ['date'] ['weekday'] = array(
	'Dimanche',
	'Lundi',
	'Mardi',
	'Mercredi',
	'Jeudi',
	'Vendredi',
	'Samedi'
);

$lang ['date'] ['weekday_abbr'] = array(
	'Dim',
	'Lun',
	'Mar',
	'Mer',
	'Jeu',
	'Ven',
	'Sam'
);
?>
