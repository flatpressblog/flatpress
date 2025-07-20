<?php
$lang ['plugin'] ['newsletter'] = array(
	// Widget
	'subject' => 'Newsletter',
	'input_email_placeholder' => 'Ton adresse e-mail',
	'accept_privacy_policy' => 'J\'accepte la déclaration de confidentialité',
	'privacy_link_text' => 'aller à la déclaration de confidentialité',
	'button' => 'S\'abonner',
	'csrf_error' => 'Jeton CSRF non valide.',

	// Double Opt-In
	'confirm_subject' => 'Confirme ton inscription à la newsletter',
	'confirm_greeting' => 'Nous te remercions pour ton inscription à la newsletter mensuelle.',
	'confirm_link_text' => 'Clique ici pour confirmer ton inscription.',
	'confirm_ignore' => 'Si tu n\'as pas demandé cet e-mail, merci de l\'ignorer.',

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
	'desc_subscribers' => 'Ici, tu peux voir toutes les adresses e-mail des abonnés à la newsletter et quand les abonnés ont accepté la déclaration de confidentialité. ' . //
		'Tu peux aussi supprimer des abonnés.',
	'admin_subscribers_list' => 'Liste des abonnés',
	'email_address' => 'Adresse e-mail',
	'subscribe_date' => 'Date',
	'subscribe_time' => 'Heure',
	'newsletter_no_subscribers' => 'Aucun abonné disponible',
	'delete_subscriber' => 'Supprimer',
	'delete_confirm' => 'Tu veux vraiment supprimer cette adresse ?',
	'desc_batch' => 'Ici, tu peux définir le nombre d\'abonnés auxquels une newsletter sera envoyée par jour. '. //
		'Renseigne-toi auprès de ton fournisseur d\'e-mail pour savoir combien d\'e-mails peuvent être envoyés par jour. ' . //
		'La newsletter est envoyée automatiquement à tous les abonnés au début du mois. ' . //
		'Wenn gerade kein automatischer Versand läuft, kannst du auch sofort den Newsletterversand anstoßen. ' . //
		'Si aucun envoi automatique n\'est en cours, tu peux aussi déclencher l\'envoi de la newsletter immédiatement.',
	'send_all_button' => 'Envoyer maintenant la newsletter à tous les abonnés',
	'send_all_confirm' => 'Souhaites-tu envoyer la newsletter maintenant à tous les abonnés ?',
	'send_type_monthly' => 'Envoi mensuel.',
	'send_type_manual'  => 'Envoi manuel.',
	'sub_remaining' => 'Encore à envoyer:',
	'batch_size_label' => 'Nombre d\'e-mails par lot',
	'save_button' => 'Enregistrer'
);

$lang ['plugin'] ['newsletter'] ['errors'] = array (
	-2 => 'Le plugin LastEntries doit être actif pour que tu puisses utiliser ce plugin.'
);

$lang ['admin'] ['plugin'] ['newsletter'] ['msgs'] = array(
	1 => 'La newsletter est envoyée à tous les abonnés.',
	-2 => 'Ce plugin nécessite le plugin LastEntries intégré dans FlatPress. Veuillez l\'activer au préalable dans le domaine des plugins!',
	2 => 'Les paramètres ont été enregistrés.'
);
?>
