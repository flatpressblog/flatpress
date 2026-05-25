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
	'desc_batch' => 'Zde určíte, kolik e-mailů plugin odešle za jeden den rozesílání. ' . //
		'Zvolte hodnotu, která je nižší než denní limit vašeho poskytovatele e-mailu. ' . //
		'Na začátku měsíce se běžný newsletter spustí automaticky a v případě potřeby se odesílá v denních dávkách, dokud nejsou osloveni všichni odběratelé. ' . //
		'Pokud právě žádné rozesílání neběží, můžete ho spustit také ručně; i ruční rozesílání používá stejný denní limit. ' . //
		'Pokud na začátku nového měsíce stále běží ruční rozesílání, automatické měsíční rozesílání se přesune na další měsíc.',
	'icon_sent_title' => 'Již doručeno v této zásilce',
	'icon_sent_alt' => 'Dodáno',
	'icon_queued_title' => 'Naplánováno pro další dávku',
	'icon_queued_alt' => 'Naplánováno',
	'send_now_button' => 'Odešlete nyní newsletter odběratelům',
	'send_now_confirm' => 'Chcete nyní odeslat newsletter odběratelům?',
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
	1 => 'Newsletter se zasílá předplatitelům.',
	-2 => 'Tento plugin vyžaduje plugin LastEntries integrovaný v aplikaci FlatPress. Aktivujte jej prosím předem v oblasti pro zásuvné moduly!',
	2 => 'Nastavení bylo uloženo.'
);
?>
