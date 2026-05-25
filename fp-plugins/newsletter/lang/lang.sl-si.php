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
	'desc_batch' => 'Tukaj določite, koliko e-poštnih sporočil vtičnik pošlje na posamezen dan pošiljanja. ' . //
		'Izberite vrednost, ki je nižja od dnevne omejitve vašega ponudnika e-pošte. ' . //
		'Na začetku meseca se redno glasilo zažene samodejno in se po potrebi pošilja v dnevnih paketih, dokler niso doseženi vsi naročniki. ' . //
		'Če pošiljanje trenutno ne poteka, ga lahko zaženete tudi ročno; ročno pošiljanje uporablja isto dnevno omejitev. ' . //
		'Če ob začetku novega meseca ročno pošiljanje še vedno poteka, se samodejno mesečno pošiljanje prestavi na naslednji mesec.',
	'icon_sent_title' => 'Že dostavljeno v tej pošiljki',
	'icon_sent_alt' => 'Dostavljeno',
	'icon_queued_title' => 'Načrtovano za naslednjo pošiljko',
	'icon_queued_alt' => 'Načrtovano',
	'send_now_button' => 'Pošljite glasilo naročnikom zdaj',
	'send_now_confirm' => 'Ali želite glasilo naročnikom poslati zdaj?',
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
	1 => 'Novice se pošiljajo naročnikom.',
	-2 => 'Ta vtičnik zahteva vtičnik LastEntries, ki je integriran v FlatPress. Prosimo, da ga predhodno aktivirate v območju za vtičnike!',
	2 => 'Nastavitve so shranjene.'
);
?>
