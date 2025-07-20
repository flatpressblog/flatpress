<?php
$lang ['plugin'] ['newsletter'] = array(
	// Widget
	'subject' => 'Newsletter',
	'input_email_placeholder' => 'Il tuo indirizzo e-mail',
	'accept_privacy_policy' => 'Accetto l\'informativa sulla privacy',
	'privacy_link_text' => 'vai all\'informativa sulla privacy',
	'button' => 'Sottoscrizione',
	'csrf_error' => 'Token CSRF non valido.',

	// Double Opt-In
	'confirm_subject' => 'Confermare l\'iscrizione alla newsletter',
	'confirm_greeting' => 'Grazie per esservi iscritti alla nostra newsletter mensile.',
	'confirm_link_text' => 'Fare clic qui per confermare l\'iscrizione',
	'confirm_ignore' => 'Se non avete richiesto questa e-mail, ignoratela.',

	// E-Mail-Content
	'last_entries' => 'Ultimi inserimenti',
	'no_entries' => 'Nessun inserimento',
	'last_comments' => 'Ultimi commenti',
	'no_comments' => 'Nessun commento',
	'unsubscribe' => 'Disdire la newsletter',
	'privacy_policy' => 'Informativa sulla privacy',
	'legal_notice' => 'Nota legale'
);

// Admin panel: Newsletter subscribers
$lang ['admin'] ['plugin'] ['submenu'] ['newsletter'] = 'Newsletter';
$lang ['admin'] ['plugin'] ['newsletter'] = array(
	'head' => 'Gestione della newsletter',
	'desc_subscribers' => 'Qui è possibile vedere tutti gli indirizzi e-mail degli iscritti alla newsletter e quando gli iscritti hanno accettato l\'informativa sulla privacy. ' . //
		'È anche possibile eliminare gli abbonati.',
	'admin_subscribers_list' => 'Elenco abbonati',
	'email_address' => 'Indirizzo e-mail',
	'subscribe_date' => 'Data',
	'subscribe_time' => 'Ora',
	'newsletter_no_subscribers' => 'Nessun abbonato disponibile',
	'delete_subscriber' => 'Cancella questo indirizzo',
	'delete_confirm' => 'Vuoi davvero cancellare questo indirizzo?',
	'desc_batch' => 'Qui è possibile specificare il numero di iscritti a cui inviare una newsletter al giorno. '. //
		'Chiedete al vostro provider di posta elettronica quante e-mail possono essere inviate al giorno. ' . //
		'La newsletter viene inviata automaticamente a tutti gli abbonati all\'inizio del mese. ' . //
		'Se non è in corso un invio automatico, è possibile avviare l\'invio immediato della newsletter. ' . //
		'Se l\'invio immediato non è stato completato entro il 28 del mese, tutti gli abbonati riceveranno automaticamente la newsletter regolare solo il mese successivo.',
	'send_all_button' => 'Invia ora la newsletter a tutti gli iscritti',
	'send_all_confirm' => 'Si desidera inviare subito la newsletter a tutti gli iscritti?',
	'send_type_monthly' => 'Invio mensile.',
	'send_type_manual'  => 'Invio manuale.',
	'sub_remaining' => 'Ancora da inviare:',
	'batch_size_label' => 'Numero di e-mail per lotto',
	'save_button' => 'Salva'
);

$lang ['plugin'] ['newsletter'] ['errors'] = array (
	-2 => 'Il plugin LastEntries deve essere attivo per poter utilizzare questo plugin.'
);

$lang ['admin'] ['plugin'] ['newsletter'] ['msgs'] = array(
	1 => 'La newsletter viene inviata a tutti gli iscritti.',
	-2 => 'Questo plugin richiede il plugin LastEntries integrato in FlatPress. Si prega di attivarlo preventivamente nell\'area plugin!',
	2 => 'Le impostazioni sono state salvate.'
);
?>
