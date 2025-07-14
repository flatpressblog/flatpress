<?php
$lang ['plugin'] ['newsletter'] = array(
	// Widget
	'subject' => 'Newsletter',
	'input_email_placeholder' => 'Deine E-Mail-Adresse',
	'accept_privacy_policy' => 'Ich akzeptiere die Datenschutzerklärung',
	'privacy_link_text' => 'gehe zur Datenschutzerklärung',
	'button' => 'Abonnieren',
	'csrf_error' => 'Ungültiger CSRF-Token.',

	// Double Opt-In
	'confirm_subject' => 'Bitte bestätige deine Newsletter-Anmeldung',
	'confirm_greeting' => 'Vielen Dank für deine Anmeldung zum monatlichen Newsletter.',
	'confirm_link_text' => 'Klicke hier, um deine Anmeldung zu bestätigen',
	'confirm_ignore' => 'Wenn du diese E-Mail nicht angefordert hast, ignoriere sie bitte.',

	// E-Mail-Content
	'last_entries' => 'Letzte Einträge',
	'no_entries' => 'Keine Einträge',
	'last_comments' => 'Letzte Kommentare',
	'no_comments' => 'Keine Kommentare',
	'unsubscribe' => 'Newsletter abbestellen',
	'privacy_policy' => 'Datenschutzerklärung',
	'legal_notice' => 'Impressum'
);

// Admin panel: Newsletter subscribers
$lang ['admin'] ['plugin'] ['submenu'] ['newsletter'] = 'Newsletter';
$lang ['admin'] ['plugin'] ['newsletter'] = array(
	'head' => 'Newsletter-Verwaltung',
	'desc_subscribers' => 'Hier siehst du alle E-Mail-Adressen der Newsletter-Abonnenten und wann die Abonnenten die Datenschutzerklärung akzeptiert haben. ' . //
		'Du kannst auch Abonnenten löschen.',
	'admin_subscribers_list' => 'Abonnentenliste',
	'email_address' => 'E-Mail-Adresse',
	'subscribe_date' => 'Datum',
	'subscribe_time' => 'Uhrzeit',
	'newsletter_no_subscribers' => 'Keine Abonnenten vorhanden',
	'delete_subscriber' => 'Löschen',
	'delete_confirm' => 'Möchtest du diese Adresse wirklich löschen?',
	'desc_batch' => 'Hier kannst du festlegen, an wie viele Abonnenten pro Tag ein Newsletter versendet wird. '. //
		'Erkundige dich bei deinem E-Mail-Anbieter, wie viel E-Mails pro Tag versendet werden können. ' . //
		'Der Newsletter wird zu Beginn des Monats automatisch an alle Abonnenten versendet. ' . //
		'Wenn gerade kein automatischer Versand läuft, kannst du auch sofort den Newsletterversand anstoßen. ' . //
		'Wenn bis zum 28 des Monats der sofortige Versand noch nicht abgeschlossen ist, erhalten alle Abonnenten erst im übernächsten Monat automatisch den regulären Newsletter.',
	'send_all_button' => 'Newsletter jetzt an alle Abonnenten versenden',
	'send_all_confirm' => 'Möchtest du den Newsletter jetzt an alle Abonnenten versenden?',
	'send_type_monthly' => 'Monatlicher Versand.',
	'send_type_manual'  => 'Manueller Versand.',
	'sub_remaining' => 'Noch zu versenden:',
	'batch_size_label' => 'Anzahl der E-Mails pro Charge',
	'save_button' => 'Speichern'
);

$lang ['plugin'] ['newsletter'] ['errors'] = array (
	-2 => 'Das LastEntries-Plugin muss aktiv sein, damit du dieses Plugin nutzen kannst.'
);

$lang ['admin'] ['plugin'] ['newsletter'] ['msgs'] = array(
	1 => 'Newsletter wird an alle Abonnenten versendet.',
	-2 => 'Dieses Plugin benötigt das in FlatPress integrierte LastEntries Plugin. Bitte dieses vorher im Plugin Bereich aktivieren!',
	2 => 'Einstellungen wurden gespeichert.'
);
?>
