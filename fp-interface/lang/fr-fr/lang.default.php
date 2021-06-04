<?php

	$lang = array();

	$lang['main'] = array(
		
		'nextpage'		=> 'Page suivante;',
		'prevpage'		=> 'Page pr&eacute;c&eacute;dente',
		'entry'      	=> 'Billet',
		'static'     	=> 'Page statique',
		'comment'    	=> 'Commentaire',
		'preview'    	=> '&Eacute;diter/aper&ccedil;u',
		
		'filed_under'	=> 'Class&eacute; sous ',	
		
		'add_entry'  	=> 'Ajouter billet',
		'add_comment'  	=> 'Ajouter commentaire',
		'add_static'  	=> 'Ajouter page statique',
		
		'btn_edit'     	=> '&Eacute;diter',
		'btn_delete'   	=> 'Supprimer',
		
		'nocomments'	=> 'Ajouter un commentaire',
		'comment'	=> '1 commentaire',
		'comments'	=> 'commentaires',
		
	);
	
	$lang['search'] = array(
		
		'head'	=> 'Rechercher',
		'fset1'	=> 'Ins&eacute;rer un crit&egrave;re de recherche',
		'keywords'	=> 'Phrase',
		'onlytitles'	=> 'Seulement les titres',
		'fulltext'	=> 'Texte en entier',
		
		'fset2'	=> 'Date',
		'datedescr'	=> 'Vous pouvez affiner votre recherche &agrave; une date sp&eacute;cifique. Vous pouvez s&eacute;lectionner une ann&eacute;e, une ann&eacute;e et un mois, ou une date compl&egrave;te. '.
					'Laissez vide pour chercher dans l\'ensemble de la base de donn&eacute;es.',
		
		'fset3' 	=> 'Rechercher dans les cat&eacute;gories',
		'catdescr'	=> 'Laissez vide pour rechercher dans la totalit&eacute;',
		
		'fset4'	=> 'Commencer la recherche',
		'submit'	=> 'Chercher',
		
		'headres'	=> 'R&eacute;sultats de la recherche',
		'descrres'	=> 'La recherche de <strong>%s</strong> a donn&eacute; les r&eacute;sultats suivants:',
		'descrnores'=> 'La recherche de <strong>%s</strong> a donn&eacute; les r&eacute;sultats suivants:',
		
		'moreopts'	=> 'Plus d\'options',
		
		
		'searchag'	=> 'Nouvelle recherche',
		
	);
	
	$lang['search']['error'] = array(
	
		'keywords'	=> 'Vous devez sp&eacute;cifier au moins un mot-cl&eacute;'
	
	);
	
	
	
	
	
	$lang['entry'] = array();
	$lang['entry']['flags'] = array();
	
	$lang['entry']['flags']['long'] = array(
		'draft' => '<strong>Billet brouillon</strong>: cach&eacute;, en attente de publication',
		//'static' => '<strong>Static entry</strong>: normally hidden, to reach the entry put ?page=title-of-the-entry in url (experimental)',
		'commslock' => '<strong>Commentaires d&eacute;sactiv&eacute;s</strong>: commentaires d&eacute;sactiv&eacute;s pour ce billet'
	);
	
	$lang['entry']['flags']['short'] = array(
		'draft' => 'Brouillon',
		//'static' => 'Static',
		'commslock' => 'Commentaires d&eacute;sactiv&eacute;s'
	);

	$lang['404error'] = array(
		'subject'	=> 'Pas trouv&eacute;',
		'content'	=> '<p>D&eacute;sol&eacute;, la page demand&eacute;e n\'a pas &eacute;t&eacute; trouv&eacute;e!</p>'
	);
		
	// Login
	$lang['login'] = array(
		
		'head'		=> 'Identifiant',
		'fieldset1'	=> 'Ins&eacute;rez vos identifiants',
		'user'		=> 'Nom d\'utilisateur:',
		'pass'		=> 'Mot de passe:',
		'fieldset2'	=> 'Se connecter',
		'submit'	=> 'Connexion',
		'forgot'	=> 'Mot de passe oubli&eacute; ?'
	);
		
	$lang['login']['success'] = array(
		'success'	=> 'Vous &ecirc;tes connect&eacute;.',
		'logout'	=> 'Vous &ecirc;tes d&eacute;connect&eacute;.',
		'redirect'	=> 'Vous serez redirig&eacute; dans 5 secondes.',
		'opt1'		=> 'Retour &agrave; l\'index',
		'opt2'		=> 'Aller au panneau de contr&ocirc;le',
		'opt3'		=> 'Ajouter un nouveau billet'
	);
	
	$lang['login']['error'] = array(
		'user'		=> 'Vous devez entrer un nom d\'utilisateur.',
		'pass'		=> 'Vous devez entrer un mot de passe.',
		'match'		=> 'Mot de passe incorrect.'
	);
	
	
	$lang['comments'] = array(
		'head'		=> 'Ajouter commentaire',
		'descr'		=> 'Remplissez le formulaire ci-dessous pour ajouter vos propres commentaires',
		'fieldset1'	=> 'Donn&eacute;es utilisateur',
		'name'		=> 'Nom (*)',
		'email'		=> 'Email:',
		'www'		=> 'Web:',
		'cookie'	=> 'Se souvenir de moi',
		'fieldset2'	=> 'Ajouter votre commentaire',
		'comment'	=> 'Commentaire (*):',
		'fieldset3'	=> 'Envoyer',
		'submit'	=> 'Ajouter',
		'reset'		=> 'R&eacute;initialiser',
		'success'	=> 'Votre commentaire a &eacute;t&eacute; ajout&eacute; avec succ&egrave;s',
		'nocomments'	=> 'Pas de commentaires pour ce billet',
		'commslock'	=> 'Les commentaires ont &eacute;t&eacute; d&eacute;sactiv&eacute;s pour ce billet',
	);
	
	$lang['comments']['error'] = array(
		'name'		=> 'Vous devez entrer un nom',
		'email'		=> 'Vous devez entrer une adresse email valide',
		'www'		=> 'Vous devez entrer une URL valide',
		'comment'	=> 'Vous devez &eacute;crire un commentaire',
	);
	
	$lang['date']['month'] = array(
		
		'Janvier',
		'F&eacute;vrier',
		'Mars',
		'Avril',
		'Mai',
		'Juin',
		'Juillet',
		'Ao&ucirc;t',
		'Septembre',
		'Octobre',
		'Novembre',
		'D&eacute;cembre'
		
	);

	$lang['date']['month_abbr'] = array(
		
		'Jan',
		'Fev',
		'Mar',
		'Avr',
		'Mai',
		'Jun',
		'Jul',
		'Aou',
		'Sep',
		'Oct',
		'Nov',
		'Dec'
		
	);

	$lang['date']['weekday'] = array(
		
		'Dimanche',
		'Lundi',
		'Mardi',
		'Mercredi',
		'Jeudi',
		'Vendredi',
		'Samedi',
		
	);

	$lang['date']['weekday_abbr'] = array(
		
		'Dim',
		'Lun',
		'Mar',
		'Mer',
		'Jeu',
		'Ven',
		'Sam',
		
	);



?>
