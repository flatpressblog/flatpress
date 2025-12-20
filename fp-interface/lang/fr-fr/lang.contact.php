<?php
$baseurl = BLOG_BASEURL;

$lang ['contact'] = array(
	'head' => 'Nous contacter',
	'descr' => 'Remplissez le formulaire ci-dessous pour nous envoyer vos commentaires. Merci d\'ajouter votre courriel si vous souhaitez une réponse.',
	'fieldset1' => 'Données utilisateur',
	'name' => 'Nom (*)',
	'email' => 'Courriel :',
	'www' => 'Web :',
	'cookie' => 'Se souvenir de moi',
	'fieldset2' => 'Votre message',
	'comment' => 'Message (*) :',
	'fieldset3' => 'Envoyer',
	'submit' => 'Envoyer',
	'reset' => 'Réinitialiser',
	'loggedin' => 'Vous êtes connecté 😉. <a href="' . $baseurl . 'login.php?do=logout">Se déconnecter</a> ou accéder à l\'<a href="' . $baseurl . 'admin.php">espace d\'administration</a>.'
);

$lang ['contact'] ['notification'] = array(
	'name' => 'Nom :',
	'email' => 'Courriel :',
	'www' => 'Web :',
	'content' => 'Message :',
	'subject' => 'Contact envoyé par '
);

$lang ['contact'] ['error'] = array(
	'name' => 'Vous devez entrer un nom',
	'email' => 'Vous devez entrer une adresse courriel valide',
	'www' => 'Vous devez entrer une URL correcte',
	'content' => 'Vous devez écrire un message'
);

$lang ['contact'] ['msgs'] = array(
	1 => 'Message envoyé avec succès',
	-1 => 'Échec d\'envoi du message'
);
?>