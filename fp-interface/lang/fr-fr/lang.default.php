<?php
$lang = array();

$lang ['main'] = array(
	'nextpage' => 'Page suivante',
	'prevpage' => 'Page précédente',
	'entry' => 'Billet',
	'entries' => 'Billets',
	'static' => 'Page statique',
	'preview' => 'Éditer/Aperçu',

	'filed_under' => 'Classé sous',

	'add_entry' => 'Ajouter un billet',
	'add_comment' => 'Ajouter un commentaire',
	'add_static' => 'Ajouter une page statique',

	'btn_edit' => 'Éditer',
	'btn_delete' => 'Supprimer',

	'nocomments' => 'Pas de commentaire',
	'comment' => '1 commentaire',
	'comments' => 'commentaires',

	'rss' => 'S’abonner au flux RSS',
	'atom' => 'S’abonner au flux Atom'
);

$lang['search'] = array(
	'head' => 'Rechercher',
	'fset1' => 'Entrer un critère de recherche',
	'keywords' => 'Mots-clés',
	'onlytitles' => 'Seulement les titres',
	'fulltext' => 'Texte complet',

	'fset2' => 'Date',
	'datedescr' => 'Vous pouvez restreindre votre recherche à une date spécifique. Vous pouvez sélectionner une année, une année et un mois, ou une date complète.' . //
		'Laissez vide pour rechercher dans toutes les données.',

	'fset3' => 'Rechercher dans les catégories',
	'catdescr' => 'Laissez vide pour rechercher dans toutes les catégories',

	'fset4' => 'Démarrer la recherche',
	'submit' => 'Chercher',

	'headres' => 'Résultats de la recherche',
	'descrres' => 'La recherche pour <strong>%s</strong> a donné les résultats suivants :',
	'descrnores' => 'La recherche pour <strong>%s</strong> n’a donné aucun résultat.',

	'moreopts' => 'Options supplémentaires',

	'searchag' => 'Nouvelle recherche'
);

$lang['search']['error'] = array(
	'keywords' => 'Vous devez entrer au moins un mot-clé.'
);

$lang['staticauthor'] = array(
	'published_by' => 'Publié par',
	'on' => 'le'
);

$lang['entryauthor'] = array(
	'posted_by' => 'Posté par',
	'at' => 'à'
);

$lang['entry'] = array();
$lang['entry']['flags'] = array();

$lang['entry']['flags']['long'] = array(
	'draft' => '<strong>Brouillon</strong> : non publié pour l’instant',
	'commslock' => '<strong>Commentaires désactivés</strong> : les commentaires sont fermés pour ce billet'
);

$lang['entry']['flags']['short'] = array(
	'draft' => 'Brouillon',
	'commslock' => 'Commentaires désactivés'
);

$lang['entry']['categories'] = array(
	'unfiled' => 'Non classé'
);

$lang['404error'] = array(
	'subject' => 'Page non trouvée',
	'content' => '<p>Désolé, la page demandée est introuvable !</p>'
);

// Connexion
$lang['login'] = array(
	'head' => 'Connexion',
	'fieldset1' => 'Entrez vos identifiants',
	'user' => 'Nom d’utilisateur :',
	'pass' => 'Mot de passe :',
	'fieldset2' => 'Se connecter',
	'submit' => 'Connexion',
	'forgot' => 'Mot de passe oublié ?'
);

$lang['login']['success'] = array(
	'success' => 'Vous êtes maintenant connecté.',
	'logout' => 'Vous êtes maintenant déconnecté.',
	'redirect' => 'Vous allez être redirigé dans 5 secondes.',
	'opt1' => 'Retour à l’accueil',
	'opt2' => 'Aller au panneau de contrôle',
	'opt3' => 'Créer un nouveau billet'
);

$lang['login']['error'] = array(
	'user' => 'Vous devez entrer un nom d’utilisateur.',
	'pass' => 'Vous devez entrer un mot de passe.',
	'match' => 'Nom d’utilisateur ou mot de passe incorrect.',
	'timeout' => 'Veuillez attendre 30 secondes avant de réessayer.'
);

$lang['comments'] = array(
	'head' => 'Ajouter un commentaire',
	'descr' => 'Remplissez le formulaire ci-dessous pour ajouter un commentaire.',
	'fieldset1' => 'Informations utilisateur',
	'name' => 'Nom (*)',
	'email' => 'Email :',
	'www' => 'Site web :',
	'cookie' => 'Se souvenir de moi',
	'fieldset2' => 'Votre commentaire',
	'comment' => 'Commentaire (*) :',
	'fieldset3' => 'Envoyer',
	'submit' => 'Envoyer',
	'reset' => 'Réinitialiser',
	'success' => 'Votre commentaire a été ajouté avec succès.',
	'nocomments' => 'Pas de commentaire sur ce billet.',
	'commslock' => 'Les commentaires sont fermés pour ce billet.'
);

$lang['comments']['error'] = array(
	'name' => 'Vous devez entrer un nom.',
	'email' => 'Vous devez entrer une adresse électronique valide.',
	'www' => 'Vous devez entrer une URL valide.',
	'comment' => 'Vous devez entrer un commentaire.'
);

$lang['postviews'] = array(
	'views' => 'Vues'
);

$lang['date']['month'] = array(
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

$lang['date']['month_abbr'] = array(
	'Jan',
	'Fév',
	'Mar',
	'Avr',
	'Mai',
	'Juin',
	'Juil',
	'Aoû',
	'Sep',
	'Oct',
	'Nov',
	'Déc'
);

$lang['date']['weekday'] = array(
	'Dimanche',
	'Lundi',
	'Mardi',
	'Mercredi',
	'Jeudi',
	'Vendredi',
	'Samedi'
);

$lang['date']['weekday_abbr'] = array(
	'Dim',
	'Lun',
	'Mar',
	'Mer',
	'Jeu',
	'Ven',
	'Sam'
);
?>
