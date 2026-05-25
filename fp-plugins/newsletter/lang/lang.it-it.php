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
	'desc_batch' => 'Qui imposti quante e-mail il plugin invia in ogni giorno di invio. ' . //
		'Scegli un valore inferiore al limite giornaliero del tuo provider di posta. ' . //
		'All\'inizio del mese, la newsletter regolare parte automaticamente e, se necessario, viene inviata in lotti giornalieri finché tutti gli iscritti sono stati raggiunti. ' . //
		'Se non è in corso alcun invio, puoi avviarne uno anche manualmente; l\'invio manuale usa lo stesso limite giornaliero. ' . //
		'Se all\'inizio di un nuovo mese è ancora in corso un invio manuale, l\'invio mensile automatico viene rimandato al mese successivo.',
	'icon_sent_title' => 'Già consegnato in questo invio',
	'icon_sent_alt' => 'Consegnato',
	'icon_queued_title' => 'Programmato per il prossimo lotto',
	'icon_queued_alt' => 'Programmato',
	'send_now_button' => 'Invia subito la newsletter agli iscritti',
	'send_now_confirm' => 'Vuoi inviare subito la newsletter agli iscritti?',
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
	1 => 'La newsletter viene inviata agli iscritti.',
	-2 => 'Questo plugin richiede il plugin LastEntries integrato in FlatPress. Si prega di attivarlo preventivamente nell\'area plugin!',
	2 => 'Le impostazioni sono state salvate.'
);
?>
