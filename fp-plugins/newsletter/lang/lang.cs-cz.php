<?php
$lang ['plugin'] ['newsletter'] = array(
	// Widget
	'subject' => 'Newsletter',
	'input_email_placeholder' => 'Vaše e-mailová adresa',
	'accept_privacy_policy' => 'Souhlasím se zásadami ochrany osobních údajů',
	'privacy_link_text' => 'přejít na zásady ochrany osobních údajů',
	'button' => 'Přihlásit se k odběru',
	'csrf_error' => 'Neplatný token CSRF.',

	// Double Opt-In
	'confirm_subject' => 'Potvrďte prosím svůj odběr newsletteru',
	'confirm_greeting' => 'Děkujeme, že jste se přihlásili k odběru našeho měsíčního zpravodaje.',
	'confirm_link_text' => 'Klikněte zde pro potvrzení odběru',
	'confirm_ignore' => 'Pokud jste si tento e-mail nevyžádali, ignorujte jej.',

	// E-Mail-Content
	'last_entries' => 'Poslední záznamy',
	'no_entries' => 'Žádné záznamy',
	'last_comments' => 'Poslední komentáře',
	'no_comments' => 'Žádné komentáře',
	'unsubscribe' => 'Odhlášení odběru newsletteru',
	'privacy_policy' => 'Zásady ochrany osobních údajů',
	'legal_notice' => 'Právní upozornění'
);

// Admin panel: Newsletter subscribers
$lang ['admin'] ['plugin'] ['submenu'] ['newsletter'] = 'Newsletter';
$lang ['admin'] ['plugin'] ['newsletter'] = array(
	'head' => 'Správa newsletteru',
	'desc_subscribers' => 'Zde můžete vidět všechny e-mailové adresy odběratelů newsletteru a kdy odběratelé přijali zásady ochrany osobních údajů. ' . //
		'Odběratele můžete také odstranit.',
	'admin_subscribers_list' => 'Seznam odběratelů',
	'email_address' => 'E-mailová adresa',
	'subscribe_date' => 'Datum a čas',
	'subscribe_time' => 'Čas',
	'newsletter_no_subscribers' => 'Nejsou k dispozici žádní odběratelé',
	'delete_subscriber' => 'Smazat tuto adresu',
	'delete_confirm' => 'Opravdu chcete tuto adresu smazat?',
	'desc_batch' => 'Zde můžete zadat, kolika odběratelům se newsletter denně odešle. '. //
		'Zeptejte se svého poskytovatele e-mailu, kolik e-mailů lze denně odeslat. ' . //
		'Newsletter je automaticky odeslán všem odběratelům na začátku měsíce. ' . //
		'Pokud v současné době není spuštěno žádné automatické rozesílání, můžete rozesílání newsletteru iniciovat také okamžitě. ' . //
		'Pokud nebude okamžité rozesílání ukončeno do 28. dne v měsíci, budou všichni odběratelé dostávat pravidelný newsletter automaticky až v následujícím měsíci.',
	'send_all_button' => 'Odeslat newsletter všem odběratelům nyní',
	'send_all_confirm' => 'Chcete zaslat newsletter všem odběratelům nyní?',
	'send_type_monthly' => 'Měsíční rozesílání.',
	'send_type_manual'  => 'Ruční rozesílání.',
	'sub_remaining' => 'Ještě je třeba odeslat:',
	'batch_size_label' => 'Počet e-mailů v jedné dávce',
	'save_button' => 'Uložit'
);

$lang ['plugin'] ['newsletter'] ['errors'] = array (
	-2 => 'Pro použití tohoto doplňku musí být aktivní doplněk LastEntries.'
);

$lang ['admin'] ['plugin'] ['newsletter'] ['msgs'] = array(
	1 => 'Zpravodaj je zasílán všem odběratelům.',
	-2 => 'Tento plugin vyžaduje plugin LastEntries integrovaný v aplikaci FlatPress. Aktivujte jej prosím předem v oblasti pro zásuvné moduly!',
	2 => 'Nastavení bylo uloženo.'
);
?>
