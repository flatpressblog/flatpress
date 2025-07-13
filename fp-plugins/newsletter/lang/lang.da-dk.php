<?php
$lang ['plugin'] ['newsletter'] = array(
	// Widget
	'subject' => 'Nyhedsbrev',
	'input_email_placeholder' => 'Din e-mailadresse',
	'accept_privacy_policy' => 'Jeg accepterer privatlivspolitikken',
	'privacy_link_text' => 'Gå til privatlivspolitikken',
	'button' => 'Tilmeld dig',
	'csrf_error' => 'Ugyldigt CSRF-token.',

	// Double Opt-In
	'confirm_subject' => 'Bekræft venligst dit abonnement på nyhedsbrevet',
	'confirm_greeting' => 'Tak, fordi du abonnerer på vores månedlige nyhedsbrev.',
	'confirm_link_text' => 'Klik her for at bekræfte dit abonnement',
	'confirm_ignore' => 'Hvis du ikke har bedt om denne e-mail, bedes du ignorere den.',

	// E-Mail-Content
	'last_entries' => 'Sidste poster',
	'no_entries' => 'Ingen indlæg',
	'last_comments' => 'Sidste kommentarer',
	'no_comments' => 'Ingen kommentarer',
	'unsubscribe' => 'Afmeld nyhedsbrev',
	'privacy_policy' => 'Fortrolighedspolitik',
	'legal_notice' => 'Juridisk meddelelse'
);

// Admin panel: Newsletter subscribers
$lang ['admin'] ['plugin'] ['submenu'] ['newsletter'] = 'Nyhedsbrev';
$lang ['admin'] ['plugin'] ['newsletter'] = array(
	'head' => 'Administration af nyhedsbreve',
	'desc_subscribers' => 'Her kan du se alle e-mailadresser på nyhedsbrevets abonnenter, og hvornår abonnenterne har accepteret privatlivspolitikken. ' . //
		'Du kan også slette abonnenter.',
	'admin_subscribers_list' => 'Liste over abonnenter',
	'email_address' => 'E-mail-adresse',
	'subscribe_date' => 'Dato',
	'subscribe_time' => 'Tid',
	'newsletter_no_subscribers' => 'Ingen tilgængelige abonnenter',
	'delete_subscriber' => 'Slet denne adresse',
	'delete_confirm' => 'Ønsker du virkelig at slette denne adresse?',
	'desc_batch' => 'Her kan du angive, hvor mange abonnenter et nyhedsbrev skal sendes til pr. dag. '. //
		'Spørg din e-mailudbyder, hvor mange e-mails der kan sendes pr. dag. ' . //
		'Nyhedsbrevet sendes automatisk til alle abonnenter i begyndelsen af måneden. ' . //
		'Hvis der ikke kører en automatisk udsendelse i øjeblikket, kan du også starte udsendelsen af nyhedsbrevet med det samme. ' . //
		'Hvis den øjeblikkelige udsendelse ikke er gennemført inden den 28. i måneden, vil alle abonnenter først automatisk modtage det almindelige nyhedsbrev i den efterfølgende måned.',
	'send_all_button' => 'Send nyhedsbrev til alle abonnenter nu',
	'send_all_confirm' => 'Vil du sende nyhedsbrevet til alle abonnenter nu?',
	'send_type_monthly' => 'Månedlig udsendelse.',
	'send_type_manual'  => 'Manuel udsendelse.',
	'sub_remaining' => 'Skal stadig sendes:',
	'batch_size_label' => 'Antal e-mails pr. batch',
	'save_button' => 'Gem'
);

$lang ['plugin'] ['newsletter'] ['errors'] = array (
	-2 => 'LastEntries-plugin\'et skal være aktivt for at kunne bruge dette plugin.'
);

$lang ['admin'] ['plugin'] ['newsletter'] ['msgs'] = array(
	1 => 'Nyhedsbrevet sendes til alle abonnenter.',
	-2 => 'Dette plugin kræver LastEntries-plugin\'et, der er integreret i FlatPress. Aktivér det venligst på forhånd i plugin-området!',
	2 => 'Indstillingerne er blevet gemt.'
);
?>
