<?php
$lang ['plugin'] ['newsletter'] = array(
	// Widget
	'subject' => 'Buletina',
	'input_email_placeholder' => 'Zure helbide elektronikoa',
	'accept_privacy_policy' => 'Pribatutasun-politika onartzen dut',
	'privacy_link_text' => 'joan pribatutasun-politikara',
	'button' => 'Harpidetu',
	'csrf_error' => 'CSRF token baliogabea.',

	// Double Opt-In
	'confirm_subject' => 'Mesedez, baieztatu zure buletinaren harpidetza',
	'confirm_greeting' => 'Eskerrik asko gure hileroko buletinera harpidetzeagatik.',
	'confirm_link_text' => 'Egin klik hemen zure harpidetza berresteko',
	'confirm_ignore' => 'Mezu elektroniko hau eskatu ez baduzu, mesedez, ez egin kasurik.',

	// E-Mail-Content
	'last_entries' => 'Azken sarrerak',
	'no_entries' => 'Ez dago sarrerarik',
	'last_comments' => 'Azken iruzkinak',
	'no_comments' => 'Ez dago iruzkinik',
	'unsubscribe' => 'Buletinan baja eman',
	'privacy_policy' => 'Pribatutasun-politika',
	'legal_notice' => 'Lege oharra'
);

// Admin panel: Newsletter subscribers
$lang ['admin'] ['plugin'] ['submenu'] ['newsletter'] = 'Buletina';
$lang ['admin'] ['plugin'] ['newsletter'] = array(
	'head' => 'Buletinaren kudeaketa',
	'desc_subscribers' => 'Hemen ikus ditzakezu buletinaren harpidedunen helbide elektroniko guztiak eta harpidedunek pribatutasun-politika onartu baldin badute. ' . //
		'Harpidedunak ere ezaba ditzakezu.',
	'admin_subscribers_list' => 'Harpidedunen zerrenda',
	'email_address' => 'Helbide elektronikoa',
	'subscribe_date' => 'Data',
	'subscribe_time' => 'Ordua',
	'newsletter_no_subscribers' => 'Ez dago harpidedunik',
	'delete_subscriber' => 'Ezabatu',
	'delete_confirm' => 'Benetan helbide hau ezabatu nahi duzu?',
	'desc_batch' => 'Hemen zehazten duzu pluginak bidalketa-egun bakoitzean zenbat mezu elektroniko bidaliko dituen. ' . //
		'Aukeratu zure posta-hornitzailearen eguneko mugaren azpitik dagoen balio bat. ' . //
		'Hilaren hasieran ohiko buletina automatikoki hasten da eta, behar izanez gero, eguneroko multzotan bidaltzen da harpidedun guztiengana iritsi arte. ' . //
		'Une honetan bidalketarik martxan ez badago, eskuz ere abiaraz dezakezu; eskuzko bidalketak eguneko muga bera erabiltzen du. ' . //
		'Hilabete berri bat hastean eskuzko bidalketa bat oraindik martxan badago, hileko bidalketa automatikoa hurrengo hilabetera atzeratzen da.',
	'icon_sent_title' => 'Bidalketa honetan dagoeneko entregatu dira',
	'icon_sent_alt' => 'Entregatua',
	'icon_queued_title' => 'Hurrengo multzorako programatuta',
	'icon_queued_alt' => 'Programatuta',
	'send_now_button' => 'Bidali orain buletina harpidetuei',
	'send_now_confirm' => 'Orain bidali nahi al duzu buletina harpidetuei?',
	'send_type_monthly' => 'Hileroko bidalketa.',
	'send_type_manual'  => 'Eskuzko bidalketa.',
	'sub_remaining' => 'Oraindik bidali beharrekoak:',
	'batch_size_label' => 'Multzo bakoitzeko mezu elektroniko kopurua',
	'save_button' => 'Gorde'
);

$lang ['plugin'] ['newsletter'] ['errors'] = array (
	-2 => 'LastEntries plugina gaituta egon behar da plugin hau erabili ahal izateko.'
);

$lang ['admin'] ['plugin'] ['newsletter'] ['msgs'] = array(
	1 => 'Buletina harpidetuei bidaltzen zaie.',
	-2 => 'Plugin honek FlatPressen integratutako LastEntries plugina behar du. Mesedez, gaitu aldez aurretik pluginen kontrol-panelean!',
	2 => 'Ezarpenak ondo gorde dira.'
);
?>
