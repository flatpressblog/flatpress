<?php
$lang ['plugin'] ['newsletter'] = array(
	// Widget
	'subject' => 'Newsletter',
	'input_email_placeholder' => 'Votre adresse e-mail',
	'accept_privacy_policy' => 'J’accepte la déclaration de confidentialité',
	'privacy_link_text' => 'Consulter la déclaration de confidentialité',
	'button' => 'S’abonner',
	'csrf_error' => 'Jeton CSRF non valide.',

	// Double Opt-In
	'confirm_subject' => 'Confirmez votre inscription à la newsletter',
	'confirm_greeting' => 'Merci de votre inscription à la newsletter mensuelle.',
	'confirm_link_text' => 'Cliquez ici pour confirmer votre inscription.',
	'confirm_ignore' => 'Si vous n’avez pas demandé cet e-mail, vous pouvez l’ignorer.',

	// E-Mail-Content
	'last_entries' => 'Dernières entrées',
	'no_entries' => 'Aucune entrée',
	'last_comments' => 'Derniers commentaires',
	'no_comments' => 'Aucun commentaire',
	'unsubscribe' => 'Se désabonner de la newsletter',
	'privacy_policy' => 'Déclaration de confidentialité',
	'legal_notice' => 'Avis légal'
);

// Admin panel: Newsletter subscribers
$lang ['admin'] ['plugin'] ['submenu'] ['newsletter'] = 'Newsletter';
$lang ['admin'] ['plugin'] ['newsletter'] = array(
	'head' => 'Gestion de la newsletter',
	'desc_subscribers' => 'Retrouvez ici toutes les adresses e-mail des abonnés et la date à laquelle ils ont accepté la déclaration de confidentialité. ' . //
		'Vous pouvez également supprimer des abonnés.',
	'admin_subscribers_list' => 'Liste des abonnés',
	'email_address' => 'Adresse e-mail',
	'subscribe_date' => 'Date',
	'subscribe_time' => 'Heure',
	'newsletter_no_subscribers' => 'Aucun abonné disponible',
	'delete_subscriber' => 'Supprimer',
	'delete_confirm' => 'Voulez-vous vraiment supprimer cette adresse ?',
	'desc_batch' => 'Définissez ici combien d’abonnés peuvent recevoir la newsletter par jour. '. //
		'Renseignez-vous auprès de votre fournisseur d’e-mails pour connaître les limites d’envoi. ' . //
		'La newsletter est envoyée automatiquement à tous les abonnés au début du mois. ' . //
		'Si aucun envoi automatique n’est en cours, vous pouvez également lancer un envoi immédiat.',
	'icon_sent_title' => 'Déjà distribué dans cet envoi',
	'icon_sent_alt' => 'Livré',
	'icon_queued_title' => 'Planifié pour le lot suivant',
	'icon_queued_alt' => 'Planifié',
	'send_all_button' => 'Envoyer maintenant la newsletter à tous les abonnés',
	'send_all_confirm' => 'Souhaitez-vous envoyer la newsletter maintenant à tous les abonnés ?',
	'send_type_monthly' => 'Envoi mensuel.',
	'send_type_manual'  => 'Envoi manuel.',
	'sub_remaining' => 'Encore à envoyer:',
	'batch_size_label' => 'Nombre d’e-mails par lot',
	'save_button' => 'Enregistrer'
);

$lang ['plugin'] ['newsletter'] ['errors'] = array (
	-2 => 'Le plugin LastEntries doit être actif pour utiliser cette extension.'
);

$lang ['admin'] ['plugin'] ['newsletter'] ['msgs'] = array(
	1 => 'La newsletter est envoyée à tous les abonnés.',
	-2 => 'Ce plugin nécessite le plugin LastEntries intégré dans FlatPress. Veuillez l’activer au préalable dans la gestion des plugins&nbsp;!',
	2 => 'Les paramètres ont été enregistrés.'
);
?>
