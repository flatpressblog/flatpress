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
	'desc_batch' => 'Hemen zehaztu dezakezu buletina egunean zenbat harpideduni bidaliko zaien. '. //
		'Galdetu zure posta elektroniko hornitzaileari zenbat mezu elektroniko bidali daitezkeen egunean. ' . //
		'Buletina automatikoki bidaltzen zaie harpidedun guztiei hilaren hasieran. ' . //
		'Une honetan bidalketa automatikorik ez badago martxan, buletinaren bidalketa berehala ere aktibatu dezakezu. ' . //
		'Hilaren 28rako berehalako bidalketa osatu ez bada, harpidedun guztiek ez dute automatikoki jasoko ohiko buletina hurrengo hilabetera arte.',
	'icon_sent_title' => 'Bidalketa honetan dagoeneko entregatu dira',
	'icon_sent_alt' => 'Entregatua',
	'icon_queued_title' => 'Hurrengo multzorako programatuta',
	'icon_queued_alt' => 'Programatuta',
	'send_all_button' => 'Bidali buletina harpidedun guztiei orain',
	'send_all_confirm' => 'Buletina harpidedun guztiei bidali nahi diezu orain?',
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
	1 => 'Buletina harpidedun guztiei bidalti zaie.',
	-2 => 'Plugin honek FlatPressen integratutako LastEntries plugina behar du. Mesedez, gaitu aldez aurretik pluginen kontrol-panelean!',
	2 => 'Ezarpenak ondo gorde dira.'
);
?>
