<?php
$baseurl = BLOG_BASEURL;

$lang ['contact'] = array(
	'head' => 'Nous Contacter',
	'descr' => 'Remplissez le formulaire ci-dessous pour nous envoyer vos commentaires. Merci d\'ajouter votre e-mail si vous souhaitez une r&eacute;ponse.',
	'fieldset1' => 'Donn&eacute;es utilisateur',
	'name' => 'Nom (*)',
	'email' => 'Email:',
	'www' => 'Web:',
	'cookie' => 'Se souvenir de moi',
	'fieldset2' => 'Votre message',
	'comment' => 'Message (*):',
	'fieldset3' => 'Envoyer',
	'submit' => 'Envoyer',
	'reset' => 'Réinitialiser',
	'loggedin' => 'Vous êtes connecté 😉. <a href="' . $baseurl . 'login.php?do=logout">Se déconnecter</a> ou accéder à <a href="' . $baseurl . 'admin.php">Espace d\'administration</a>.'
);

$lang ['contact'] ['notification'] = array(
	'name' => 'Nom:',
	'email' => 'Courriel:',
	'www' => 'Web:',
	'content' => 'Message:',
	'subject' => 'Contact envoyé par '
);

$lang ['contact'] ['error'] = array(
	'name' => 'Vous devez entrer un nom',
	'email' => 'Vous devez entrer une adresse email valide',
	'www' => 'Vous devez entrer une URL correcte',
	'content' => 'Vous devez écrire un message'
);

$lang ['contact'] ['msgs'] = array(
	1 => 'Message envoyé avec succ&egrave;s',
	-1 => 'Echec d\'envoi du message'
);
?>