<?php
$lang ['plugin'] ['newsletter'] = array(
	// Widget
	'subject' => 'Nieuwsbrief',
	'input_email_placeholder' => 'Uw e-mailadres',
	'accept_privacy_policy' => 'Ik accepteer het privacybeleid',
	'privacy_link_text' => 'ga naar het privacybeleid',
	'button' => 'Inschrijven',
	'csrf_error' => 'Ongeldig CSRF-token.',

	// Double Opt-In
	'confirm_subject' => 'Bevestig uw inschrijving voor de nieuwsbrief',
	'confirm_greeting' => 'Bedankt voor je inschrijving op onze maandelijkse nieuwsbrief.',
	'confirm_link_text' => 'Klik hier om uw inschrijving te bevestigen',
	'confirm_ignore' => 'Als je deze e-mail niet hebt aangevraagd, negeer hem dan.',

	// E-Mail-Content
	'last_entries' => 'Laatste inzendingen',
	'no_entries' => 'Geen inzendingen',
	'last_comments' => 'Laatste commentaar',
	'no_comments' => 'Geen reacties',
	'unsubscribe' => 'Nieuwsbrief afmelden',
	'privacy_policy' => 'Privacybeleid',
	'legal_notice' => 'Wettelijke kennisgeving'
);

// Admin panel: Newsletter subscribers
$lang ['admin'] ['plugin'] ['submenu'] ['newsletter'] = 'Nieuwsbrief';
$lang ['admin'] ['plugin'] ['newsletter'] = array(
	'head' => 'Beheer nieuwsbrief',
	'desc_subscribers' => 'Hier kun je alle e-mailadressen van de nieuwsbriefabonnees zien en wanneer de abonnees het privacybeleid hebben geaccepteerd. ' . //
		'Je kunt ook abonnees verwijderen.',
	'admin_subscribers_list' => 'Lijst abonnees',
	'email_address' => 'E-mailadres',
	'subscribe_date' => 'Datum',
	'subscribe_time' => 'Tijd',
	'newsletter_no_subscribers' => 'Geen abonnees beschikbaar',
	'delete_subscriber' => 'Dit adres verwijderen',
	'delete_confirm' => 'Wilt u dit adres echt verwijderen?',
	'desc_batch' => 'Hier stel je in hoeveel e-mails de plugin per verzenddag verstuurt. ' . //
		'Kies een waarde onder de daglimiet van je e-mailprovider. ' . //
		'Aan het begin van de maand start de reguliere nieuwsbrief automatisch en wordt deze zo nodig in dagelijkse batches verzonden totdat alle abonnees zijn bereikt. ' . //
		'Als er geen verzending loopt, kun je ook handmatig een verzending starten; deze handmatige verzending gebruikt dezelfde daglimiet. ' . //
		'Als er bij het begin van een nieuwe maand nog een handmatige verzending loopt, wordt de automatische maandelijkse verzending uitgesteld tot de volgende maand.',
	'icon_sent_title' => 'Reeds geleverd in deze zending',
	'icon_sent_alt' => 'Geleverd',
	'icon_queued_title' => 'Gepland voor volgende batch',
	'icon_queued_alt' => 'Gepland',
	'send_now_button' => 'Stuur de nieuwsbrief nu naar de abonnees',
	'send_now_confirm' => 'Wilt u de nieuwsbrief nu naar de abonnees sturen?',
	'send_type_monthly' => 'Maandelijkse verzending.',
	'send_type_manual'  => 'Handmatige verzending.',
	'sub_remaining' => 'Nog te versturen:',
	'batch_size_label' => 'Aantal e-mails per batch',
	'save_button' => 'Opslaan'
);

$lang ['plugin'] ['newsletter'] ['errors'] = array (
	-2 => 'De LastEntries plugin moet actief zijn om deze plugin te kunnen gebruiken.'
);

$lang ['admin'] ['plugin'] ['newsletter'] ['msgs'] = array(
	1 => 'De nieuwsbrief wordt naar de abonnees verstuurd.',
	-2 => 'Deze plugin vereist de LastEntries plugin die is geïntegreerd in FlatPress. Activeer deze eerst in de plugin omgeving!',
	2 => 'Instellingen zijn opgeslagen.'
);
?>
