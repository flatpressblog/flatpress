<?php

	$lang['admin']['static']['submenu'] = array(
		'list'		=> 'G&eacute;rer pages statiques',
		'write'		=> '&Eacute;crire'
	);

	
	/* main panel */
		
	$lang['admin']['static']['list'] = array(
	
		'head'		=> 'Pages Statiques',
		'descr'		=> 'S&eacute;lectionnez une page &agrave; &eacute;diter ou <a href="admin.php?p=static&amp;action=write">ajoutez-en une nouvelle</a>.',
	
		'sel'		=> 'Sel', // checkbox
		'date'		=> 'Date',
		'name'		=> 'Page',
		'title'		=> 'Titre',
		'author'	=> 'Auteur',
		
		'action'	=> 'Action',
		'act_view'	=> 'Voir',
		'act_del'	=> 'Effacer',
		'act_edit'	=> '&Eacute;diter'		
	);
	
	$lang['admin']['static']['list']['msgs'] = array(
		1	=> 'Page enregistr&eacute;e avec succ&egrave;s',
		-1	=> 'Echec de la sauvegarde de la page',
		2	=> 'La page a &eacute;t&eacute; effac&eacute;e',
		-2	=>	 'Echec de la suppression de la page',
	);

	/* write panel */

	$lang['admin']['static']['write'] = 
	array(
		'head'		=> 'Publier une page statique',
		'descr'		=> 'Editez le formulaire pour publier la page',
		'fieldset1'	=> '&Eacute;diter',
		'subject'	=> 'Sujet (*):',
		'content'	=> 'Contenu (*):',
		'fieldset2'	=> 'Soumettre',
		'pagename'	=> 'Nom de la page (*):',
		'submit'	=> 'Publier',
		'preview'	=> 'Aper&ccedil;u',

		'delfset'	=> 'Effacer',
		'deletemsg'	=> 'Effacer cette page',
		'del'		=> 'Effacer',
		'success'	=> 'Votre page a &eacute;t&eacute; publi&eacute;e',
		'otheropts'	=> 'Autres options',
	);
	
	$lang['admin']['static']['write']['error'] = array(
		'subject'	=> 'Compl&eacute;tez le sujet',
		'content'	=> 'Compl&eacute;tez les champs requis',
		'id'		=> 'Vous devez entrer un id valide'
	);
	
	
	/* delete action */	
	$lang['admin']['static']['delete'] = array(
		'head'		=> "Supprimer Page", 
		'descr'		=> 'Vous allez effacer la page suivante:',
		'preview'	=> 'Aper&ccedil;u',
		'confirm'	=> 'Confirmer la suppression?',
		'fset'		=> 'Effacer',
		'ok'		=> 'Oui, effacer cette page',
		'cancel'	=> 'Non, retour au panel',
		'err'		=> 'La page sp&eacute;cifi&eacute;e n\'existe pas',
	
	);
	
	
		
?>
