<?php
$lang ['admin'] ['entry'] ['submenu'] ['commentcenter'] = 'Centre de commentaires';
$lang ['admin'] ['entry'] ['commentcenter'] = array(
	// Header of the panel
	'title' => 'Centre de commentaires',
	'desc1' => 'Ce panneau te permet de gérer les commentaires sur ton blog.',
	'desc2' => 'Ici, tu peux faire plusieurs choses :',

	// Links
	'lpolicies' => 'Gestion des politiques',
	'lapprove' => 'Afficher les commentaires bloqués',
	'lmanage' => 'Gérer les commentaires',
	'lconfig' => 'Configurer le plugin',
	'faq_spamcomments' => 'Obtenir de l\'aide pour gérer les commentaires de spam',

	// Policies
	'policies' => 'Directives',
	'desc_pol' => 'Ici, tu peux modifier les directives pour les commentaires.',
	'select' => 'Sélectionner',
	'criteria' => 'Critères',
	'behavoir' => 'Comportement',
	'options' => 'Réglages',
	'entry' => 'Entrée',
	'entries' => 'Entrées',
	'categories' => 'Catégories',
	'nopolicies' => 'Il n\'y a pas de directives',
	'all_entries' => 'Toutes les entrées',
	'fol_entries' => 'La directive s\'applique aux entrées suivantes :',
	'fol_cats' => 'La directive s\'applique aux contributions dans les catégories suivantes: ',
	'older' => 'La directive s\'applique aux contributions datant de plus de %d jour(s).',
	'allow' => 'Autoriser les commentaires',
	'block' => 'Interdire les commentaires',
	'approvation' => 'Les commentaires doivent être approuvés',
	'up' => 'Haut de page',
	'down' => 'Vers le bas',
	'edit' => 'Modifier',
	'delete' => 'Supprimer',
	'newpol' => 'Ajouter une nouvelle politique',
	'del_selected' => 'Supprimer la ou les politiques sélectionnées',
	'select_all' => 'Sélectionner tout',
	'deselect_all' => 'N\'en choisir aucun',

	// Configuration page
	'configure' => 'Configurer le plugin',
	'desc_conf' => 'Ici, tu peux modifier les options du plugin.',
	'log_all' => 'Consigner les commentaires bloqués',
	'log_all_long' => 'Active cette option si tu souhaites également consigner les commentaires bloqués.',
	'email_alert' => 'Notification par e-mail',
	'email_alert_long' => 'Si tu dois examiner un commentaire pour approbation, tu peux être informé par e-mail.',
	'akismet' => 'Akismet',
	'akismet_use' => 'Vérification des commentaires avec Akismet',
	'akismet_use_long' => 'Avec <a href="https://akismet.com/" target="_blank">Akismet</a>, il est possible de réduire le spam dans les commentaires.',
	'akismet_key' => 'Clé Akismet',
	'akismet_key_long' => 'Le service <a href="https://akismet.com/signup/" target="_blank">Akismet</a> met à ta disposition une <a class="hint externlink" href="https://akismet.com/support/getting-started/api-key/" target="_blank">clé</a>. Colle celui-ci.',
	'akismet_url' => 'URL du blog pour Akismet',
	'akismet_url_long' => 'Pour bénéficier du service gratuit d\'Akismet, tu ne dois utiliser qu\'un seul domaine. Tu peux laisser ce champ vide. On utilise alors <code>%s</code>.',
	'save_conf' => 'Enregistrer les paramètres',

	// Edit policy page
	'apply_to' => 'Appliquer à',
	'editpol' => 'Modifier une politique',
	'createpol' => 'Création d\'une politique',
	'some_entries' => 'Certaines entrées',
	'properties' => 'Entrée avec certaines caractéristiques',
	'se_desc' => 'Si vous avez choisi l\'option %s, veuillez insérer des messages que vous souhaitez appliquer à cette politique.',
	'se_fill' => 'Remplis les champs avec le <a href="admin.php?p=entry">ID</a> des inscriptions (<code>entryYYMMDD-HHMMSS</code>).',
	'po_title' => 'Caractéristiques',
	'po_desc' => 'Si tu as choisi l\'option %s, remplis les propriétés.',
	'po_comp' => 'Les champs ne sont pas obligatoires, mais tu dois en remplir au moins un ou la directive s\'appliquera à tous les messages.',
	'po_time' => 'Réglages de l\'heure',
	'po_older' => 'Appliquer aux entrées datant de plus de ',
	'days' => 'jours.',
	'save_policy' => 'Enregistrer la politique',

	// Delete policies page
	'del_policies' => 'Supprimer des directives',
	'del_descs' => 'Tu vas supprimer cette politique: ',
	'del_descm' => 'Tu vas supprimer ces directives: ',
	'sure' => 'Tu es sûr?',
	'del_subs' => 'Oui, veuillez supprimer.',
	'del_subm' => 'Oui, veuillez tous les supprimer.',
	'del_cancel' => 'Non, retour aux réglages.',

	// Approve comments page
	'app_title' => 'Approuve le commentaire',
	'app_desc' => 'Ici, tu peux approuver des commentaires.',
	'app_date' => 'Date',
	'app_content' => 'Commentaire',
	'app_author' => 'Auteur',
	'app_email' => 'Email',
	'app_ip' => 'IP',
	'app_actions' => 'Mesures',
	'app_publish' => 'Publication',
	'app_delete' => 'Supprimer',
	'app_nocomms' => 'Il n\'y a pas de commentaire.',
	'app_pselected' => 'Publier les commentaires sélectionnés',
	'app_dselected' => 'Supprimer les commentaires sélectionnés',
	'app_other' => 'Autres remarques',
	'app_akismet' => 'Détecté comme spam',
	'app_spamdesc' => 'Ces commentaires ont été bloqués par Akismet.',
	'app_hamsubmit' => 'Lors de la publication, signalez-le également à Akismet en tant que ham.',
	'app_pubnotham' => 'Publier, mais ne pas transmettre à Akismet',

	// Delete comments page
	'delc_title' => 'Supprimer les commentaires',
	'delc_descs' => 'Tu vas supprimer ce commentaire: ',
	'delc_descm' => 'Tu vas supprimer ces commentaires: ',

	// Manage comments page
	'man_searcht' => 'Rechercher une entrée',
	'man_searchd' => 'Insérez le <a href="admin.php?p=entry">ID</a> (<code>entryYYMMDD-HHMMSS</code>) du message dont vous souhaitez gérer les commentaires.',
	'man_search' => 'Recherche',
	'man_commfor' => 'Remarques pour %s',
	'man_spam' => 'Signaler comme spam à Akismet',

	// The simple edit
	'simple_pre' => 'Les commentaires sur cette entrée ',
	'simple_1' => 'sont autorisés.',
	'simple_0' => 'nécessitent ton approbation.',
	'simple_-1' => 'sont bloqués.',
	'simple_manage' => 'Gérer les commentaires sur cette entrée.',
	'simple_edit' => 'Modifier les directives',

	// Akismet warnings
	'akismet_errors' => array(
		-1 => 'La clé Akismet est vide. Veuillez saisir celle-ci.',
		-2 => 'Nous n\'avons pas pu accéder aux serveurs d\'Akismet.',
		-3 => 'La réponse Akismet a échoué.',
		-4 => 'La clé Akismet n\'est pas valide.'
	),

	// Messages
	'msgs' => array(
		1 => 'Configuration enregistrée.',
		-1 => 'Une erreur est survenue lors de l\'enregistrement de la configuration.',

		2 => 'Directive enregistrée.',
		-2 => 'Une erreur s\'est produite lors de l\'enregistrement de la politique (tes paramètres sont peut-être incorrects).',

		3 => 'Directive reportée.',
		-3 => 'Une erreur s\'est produite lors de la tentative de déplacement de la stratégie (ou celle-ci ne peut pas être déplacée).',

		4 => 'Directive(s) supprimée(s).',
		-4 => 'Une erreur s\'est produite lors de la tentative de suppression de la (des) politique(s) (ou tu n\'as pas sélectionné de politique).',

		5 => 'Commentaire(s) publié(s).',
		-5 => 'Une erreur s\'est produite lors de la tentative de publication des commentaires.',

		6 => 'Commentaire(s) supprimé(s).',
		-6 => 'Une erreur s\'est produite lors de la tentative de suppression des commentaires (ou tu n\'as pas sélectionné de commentaire).',

		7 => 'Commentaire déposé.',
		-7 => 'Une erreur est survenue lors de l\'envoi du commentaire.'
	),

	// Errors
	'errors' => array(
		'pol_nonex' => 'La directive que tu souhaites modifier n\'existe pas.',
		'entry_nf' => 'Le message sélectionné n\'existe pas.'
	)
);

$lang ['plugin'] ['commentcenter'] = array(
	'akismet_error' => 'Désolé, nous rencontrons des difficultés techniques.',
	'akismet_spam'  => 'Ton commentaire a malheureusement été identifié comme spam.',
	'lock' => 'Il n\'est malheureusement pas possible de commenter cet article.',
	'approvation' => 'Le commentaire a été enregistré, mais l\'administrateur doit le valider avant de l\'afficher.',

	// Mail for comments
	'mail_subj' => 'Nouveau commentaire à approuver %s'
);

$lang ['plugin'] ['commentcenter'] ['mail_text'] = 'Bonjour %toname%,

"%fromname%" %frommail% a posté un commentaire sur l\'article intitulé "%entrytitle%"
Mais celui-ci a besoin de ton accord avant d\'être publié.

Ce qui suit a été écrit comme commentaire:
__________________________________________
%content%
__________________________________________

Connectez-vous à la zone administrative de votre blog FlatPress et vérifiez le commentaire bloqué dans le centre de commentaires.

Généré automatiquement par
%blogtitle%

';
?>
