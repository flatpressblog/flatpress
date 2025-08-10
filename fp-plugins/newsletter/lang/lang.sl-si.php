<?php
$lang ['plugin'] ['newsletter'] = array(
	// Widget
	'subject' => 'Novice',
	'input_email_placeholder' => 'Vaš e-poštni naslov',
	'accept_privacy_policy' => 'Sprejemam politiko zasebnosti',
	'privacy_link_text' => 'pojdite na pravilnik o zasebnosti',
	'button' => 'Naročite se na',
	'csrf_error' => 'Nepravilen žeton CSRF.',

	// Double Opt-In
	'confirm_subject' => 'Prosimo, potrdite svojo naročnino na novice',
	'confirm_greeting' => 'Zahvaljujemo se vam za prijavo na naše mesečne novice.',
	'confirm_link_text' => 'Kliknite tukaj za potrditev naročnine.',
	'confirm_ignore' => 'Če tega e-poštnega sporočila niste zahtevali, ga ne upoštevajte.',

	// E-Mail-Content
	'last_entries' => 'Zadnji vnosi',
	'no_entries' => 'Ni vnosov',
	'last_comments' => 'Zadnji komentarji',
	'no_comments' => 'Ni komentarjev',
	'unsubscribe' => 'Odjava od prejemanja novic',
	'privacy_policy' => 'Politika zasebnosti',
	'legal_notice' => 'Pravno obvestilo'
);

// Admin panel: Newsletter subscribers
$lang ['admin'] ['plugin'] ['submenu'] ['newsletter'] = 'Novice';
$lang ['admin'] ['plugin'] ['newsletter'] = array(
	'head' => 'Upravljanje novic',
	'desc_subscribers' => 'Tukaj si lahko ogledate vse e-poštne naslove naročnikov na novice in kdaj so naročniki sprejeli politiko zasebnosti. ' . //
		'Naročnike lahko tudi izbrišete.',
	'admin_subscribers_list' => 'Seznam naročnikov',
	'email_address' => 'E-poštni naslov',
	'subscribe_date' => 'Datum',
	'subscribe_time' => 'Ura',
	'newsletter_no_subscribers' => 'Naročniki niso na voljo',
	'delete_subscriber' => 'Izbriši ta naslov',
	'delete_confirm' => 'Ali res želite izbrisati ta naslov?',
	'desc_batch' => 'Tu lahko določite, koliko naročnikom se glasilo pošlje na dan. '. //
		'Vprašajte svojega ponudnika e-pošte, koliko e-poštnih sporočil lahko pošljete na dan. ' . //
		'Glasilo se samodejno pošlje vsem naročnikom na začetku meseca. ' . //
		'Če trenutno ne poteka samodejno pošiljanje, lahko pošiljanje glasila sprožite tudi takoj. ' . //
		'Če takojšnje pošiljanje ni zaključeno do 28. dne v mesecu, bodo vsi naročniki samodejno prejeli redne novice šele v naslednjem mesecu.',
	'icon_sent_title' => 'Že dostavljeno v tej pošiljki',
	'icon_sent_alt' => 'Dostavljeno',
	'icon_queued_title' => 'Načrtovano za naslednjo pošiljko',
	'icon_queued_alt' => 'Načrtovano',
	'send_all_button' => 'Zdaj pošljite novice vsem naročnikom',
	'send_all_confirm' => 'Ali želite zdaj poslati novice vsem naročnikom?',
	'send_type_monthly' => 'Mesečno pošiljanje.',
	'send_type_manual'  => 'Ročno pošiljanje.',
	'sub_remaining' => 'Še vedno je treba poslati:',
	'batch_size_label' => 'Število e-poštnih sporočil v seriji',
	'save_button' => 'Shrani'
);

$lang ['plugin'] ['newsletter'] ['errors'] = array (
	-2 => 'Za uporabo tega vtičnika mora biti vtičnik LastEntries aktiven.'
);

$lang ['admin'] ['plugin'] ['newsletter'] ['msgs'] = array(
	1 => 'Glasilo je poslano vsem naročnikom.',
	-2 => 'Ta vtičnik zahteva vtičnik LastEntries, ki je integriran v FlatPress. Prosimo, da ga predhodno aktivirate v območju za vtičnike!',
	2 => 'Nastavitve so shranjene.'
);
?>
