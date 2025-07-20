<?php
$lang ['plugin'] ['newsletter'] = array(
	// Widget
	'subject' => 'Bülten',
	'input_email_placeholder' => 'E-posta adresiniz',
	'accept_privacy_policy' => 'Gizlilik politikasını kabul ediyorum',
	'privacy_link_text' => 'gizlilik politikasına git',
	'button' => 'Abone Olun',
	'csrf_error' => 'Geçersiz CSRF belirteci.',

	// Double Opt-In
	'confirm_subject' => 'Lütfen bülten aboneliğinizi onaylayın',
	'confirm_greeting' => 'Aylık bültenimize abone olduğunuz için teşekkür ederiz.',
	'confirm_link_text' => 'Aboneliğinizi onaylamak için buraya tıklayın',
	'confirm_ignore' => 'Bu e-postayı talep etmediyseniz, lütfen dikkate almayın.',

	// E-Mail-Content
	'last_entries' => 'Son girişler',
	'no_entries' => 'Giriş yok',
	'last_comments' => 'Son yorumlar',
	'no_comments' => 'Yorum yok',
	'unsubscribe' => 'Haber bülteni aboneliğini iptal et',
	'privacy_policy' => 'Gizlilik Politikası',
	'legal_notice' => 'Yasal Uyarı'
);

// Admin panel: Newsletter subscribers
$lang ['admin'] ['plugin'] ['submenu'] ['newsletter'] = 'Bülten';
$lang ['admin'] ['plugin'] ['newsletter'] = array(
	'head' => 'Bülten yönetimi',
	'desc_subscribers' => 'Burada bülten abonelerinin tüm e-posta adreslerini ve abonelerin gizlilik politikasını ne zaman kabul ettiklerini görebilirsiniz. ' . //
		'Aboneleri de silebilirsiniz.',
	'admin_subscribers_list' => 'Abone listesi',
	'email_address' => 'E-posta adresi',
	'subscribe_date' => 'Tarih',
	'subscribe_time' => 'Saat',
	'newsletter_no_subscribers' => 'Mevcut abone yok',
	'delete_subscriber' => 'Bu adresi silin',
	'delete_confirm' => 'Bu adresi gerçekten silmek istiyor musunuz?',
	'desc_batch' => 'Burada bir bültenin günde kaç aboneye gönderileceğini belirleyebilirsiniz. '. //
		'E-posta sağlayıcınıza günde kaç e-posta gönderilebileceğini sorun. ' . //
		'Bülten, ayın başında tüm abonelere otomatik olarak gönderilir. ' . //
		'Otomatik gönderim o anda çalışmıyorsa, bülten gönderimini hemen de başlatabilirsiniz. ' . //
		'Anında gönderim ayın 28\'ine kadar tamamlanmamışsa, tüm aboneler bir sonraki aya kadar düzenli bülteni otomatik olarak almayacaktır.',
	'send_all_button' => 'Bülteni şimdi tüm abonelere gönder',
	'send_all_confirm' => 'Bülteni şimdi tüm abonelere göndermek istiyor musunuz?',
	'send_type_monthly' => 'Aylık gönderim.',
	'send_type_manual'  => 'Manuel gönderim.',
	'sub_remaining' => 'Hala gönderilecek:',
	'batch_size_label' => 'Parti başına e-posta sayısı',
	'save_button' => 'Kaydet'
);

$lang ['plugin'] ['newsletter'] ['errors'] = array (
	-2 => 'Bu eklentiyi kullanabilmek için LastEntries eklentisinin aktif olması gerekmektedir.'
);

$lang ['admin'] ['plugin'] ['newsletter'] ['msgs'] = array(
	1 => 'Bülten tüm abonelere gönderilir.',
	-2 => 'Bu eklenti, FlatPress\'e entegre edilmiş LastEntries eklentisini gerektirir. Lütfen eklenti alanında önceden etkinleştirin!',
	2 => 'Ayarlar kaydedildi.'
);
?>
