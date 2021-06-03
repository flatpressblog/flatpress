<?php


	$lang['admin']['entry']['submenu'] = 
	array (
		'list'		=> 'G&eacute;rer les billets',
		'write'		=> '&Eacute;crire un billet',
		'cats'		=> 'G&eacute;rer les cat&eacute;gories'
	);


	/* action par défaut */
	
	$lang['admin']['entry']['list'] = 
	array(
		'head'		=> 'G&eacute;rer les billets',
		'descr'		=> 'S&eacute;lectionner un billet &agrave; &eacute;diter ou<a href="admin.php?p=entry&amp;action=write"> ajouter un nouveau billet</a>'.
					'<br /><a href="admin.php?p=entry&amp;action=cats">&Eacute;diter les cat&eacute;gories</a>',
		'filter'	=> 'Filtrer: ',
		'nofilter'	=> 'Tout Afficher',
		'filterbtn'	=> 'Appliquer filtre',
		'sel'		=> 'Sel', // checkbox
		'date'		=> 'Date',
		'title'		=> 'Titre',
		'author'	=> 'Auteur',
		'comms'		=> '#Comms', // commentaires
		'action'	=> 'Action',
		'act_del'	=> 'Effacer',
		'act_view'	=> 'Voir',
		'act_edit'	=> '&Eacute;diter'
	);
	
	/* écrire action */
	$lang['admin']['entry']['write'] = 
	array(
		'head'		=> '&Eacute;crire un billet',
		'descr'		=> '&Eacute;diter les formulaire d\'&eacute;criture du billet',
		'uploader'	=> 'Uploader',
		'fieldset1'	=> '&Eacute;diter',
		'subject'	=> 'Sujet (*):',
		'content'	=> 'Contenu (*):',
		'fieldset2'	=> 'Soumettre',
		'submit'	=> 'Publier',
		'preview'	=> 'Aper&ccedil;u',
		'savecontinue'	=> 'enregistrer et continuer',
		'categories'	=> 'Cat&eacute;gories',
		'nocategories'	=> 'Pas de cat&eacute;gories d&eacute;finies. <a href="admin.php?p=entry&amp;action=cats"> Cr&eacute;er '. 
					'cat&eacute;gories</a>  &agrave; partir de du menu des cat&eacute;gories. '.
					'<a href="#save">Enregistrer</a> votre premi&egrave;re entr&eacute;e.',
		'saveopts'	=> 'Enregistrer options',
		'success'	=> 'Votre billet a &eacute;t&eacute; publi&eacute;',
		'otheropts'	=> 'Autres options',
		'commmsg'	=> 'G&eacute;rer les commentaires de ce billet',
		'delmsg'	=> 'Effacer ce billet',
		//'back'		=> 'Back discarding changes',
	);
	

	$lang['admin']['entry']['list']['msgs'] = array(
		1	=> 'Le billet a &eacute;t&eacute; enregistr&eacute; avec succ&egrave;s',
		-1	=> 'Une erreur est survenue pendant l\'enregistrement du billet',
		2	=> 'Billet effac&eacute;',
		-2	=>	 'Une erreur est survenue pendant la suppression du billet',
	);

	
	$lang['admin']['entry']['write']['error'] = array(
		'subject'	=> 'Compl&eacute;tez le sujet',
		'content'	=> 'Compl&eacute;tez correctement le formulaire',
	);
	
	$lang['admin']['entry']['write']['msgs'] = array(
		1	=> 'Le billet a &eacute;t&eacute; enregistr&eacute; avec succ&egrave;s',
		-1	=> 'Une erreur est survenue: votre billet n\'a pas &eacute;t&eacute; enregistr&eacute;',
		-2	=> 'Une erreur est survenue: votre billet n\'a pas &eacute;t&eacute; enregistr&eacute;; index peut &ecirc;tre endommag&eacute;',
		-3	=> 'Une erreur est survenue: votre billet n\'a pas &eacute;t&eacute; enregistr&eacute;',
		-4	=> 'Une erreur est survenue: votre billet n\'a pas &eacute;t&eacute; enregistr&eacute;; index peut &ecirc;tre endommag&eacute;',
		'draft'=> 'Vous &eacute;ditez actuellement un <strong>brouillon</strong>'
	);

	
	/* comments */
	
	$lang['admin']['entry']['commentlist'] = 
	array(
		'head'		=> "Commentaires du billet ", 
		'descr'		=> 'S&eacute;lectionner un commentaire &agrave; effacer',
		'sel'		=> 'Sel',
		'content'	=> 'Contenu',
		'date'		=> 'Date',
		'author'	=> 'Auteur',
		'email'		=> 'Email',
		'ip'		=> 'IP',
		'actions'	=> 'Actions',
		'act_edit'	=> '&Eacute;diter',
		'act_del'	=> 'Effacer',
		'act_del_confirm' => 'Confirmez-vous la suppression du commentaire?',
		'nocomments'	=> 'pas de commentaires pour ce billet.',
		
	
	);

	$lang['admin']['entry']['commentlist']['msgs'] =
	array(
		1	=> 'Commentaire effac&eacute; avec succ&egrave;s',
		-1	=> 'Une erreur est survenue pendant la suppression du commentaire',
		
	);

	$lang['admin']['entry']['commedit'] = 
	array(
		'head'		=> "&Eacute;diter commentaire du billet", 
		'content'	=> 'Contenu',
		'date'		=> 'Date',
		'author'	=> 'Auteur',
		'www'		=> 'Site Web',
		'email'		=> 'Email',
		'ip'		=> 'IP',
		'loggedin'	=> 'Utilisateur enregistr&eacute;',
		'submit'	=> 'Enregistrer'
		
	
	);

	$lang['admin']['entry']['commedit']['msgs'] =
	array(
		1	=> 'Commentaire a &eacute;t&eacute; modifi&eacute;',
		-1	=> 'Une erreur est survenue pendant l\'&eacute;dition du commentaire',
	);
	
	/* delete action */
	
	$lang['admin']['entry']['delete'] = 
	array(
		'head'		=> 'Supprimer ce billet', 
		'descr'		=> 'Vous allez supprimer le billet suivant:',
		'preview'	=> 'Aper&ccedil;u',
		'confirm'	=> 'Confirmez-vous l\'action?',
		'fset'		=> 'Effacer',
		'ok'		=> 'Oui, effacer ce billet',
		'cancel'	=> 'Non, retour au panel',
		'err'		=> 'Le billet sp&eacute;cifi&eacute;e n\'existe pas',
	
	);
	
	/* cat�gories gestionnaire */
	
	$lang['admin']['entry']['cats'] =
	array(
		'head'		=> '&Eacute;diter cat&eacute;gories',
		'descr'		=> '<p>Utilisez le formulaire pour ajouter et &eacute;diter vos cat&eacute;gories. </p><p>Chaque item doit &ecirc;tre au format "nom cat&eacute;gorie: <em>num&eacute;ro de cat&eacute;gorie</em>". Placez des tirets devant les articles pour cr&eacute;er des hi&eacute;rarchies.</p>
		
	<p>Exemple:</p>
	<pre>
Accueil :1
Infos :2
--Annonces :3
--Ev&egrave;nements :4
----Misc :5
Technologie :6
	</pre>',
		'clear'		=> 'Effacer toutes les donn&eacute;es des cat&eacute;gories',
	
		'fset1'		=> '&Eacute;diteur',
		'fset2'		=> 'Appliquer modifications',
		'submit'	=> 'Enregistrer'
	);
	
	$lang['admin']['entry']['cats']['msgs'] = array(
		
		1	=> 'Cat&eacute;gories enregistr&eacute;es',
		-1	=> 'Une erreur est survenue pendant l\'enregistrement des cat&eacute;gories',
		2	=> 'Cat&eacute;gories effac&eacute;es',
		-2	=> 'Une erreur est survenue pendant la suppression des cat&eacute;gories',
		-3 	=> 'Le num&eacute;ro de cat&eacute;gorie doit &ecirc;tre positif (0 pas permis)'

	);
	
	
		
?>
