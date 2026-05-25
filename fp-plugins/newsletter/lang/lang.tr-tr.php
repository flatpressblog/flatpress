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
	'desc_batch' => 'Burada eklentinin her gönderim gününde kaç e-posta göndereceğini belirlersiniz. ' . //
		'E-posta sağlayıcınızın günlük gönderim sınırının altında bir değer seçin. ' . //
		'Ayın başında normal bülten otomatik olarak başlar ve gerekirse tüm abonelere ulaşılana kadar günlük partiler halinde gönderilir. ' . //
		'O anda çalışan bir gönderim yoksa, gönderimi elle de başlatabilirsiniz; elle gönderim de aynı günlük sınırı kullanır. ' . //
		'Yeni bir ay başladığında elle gönderim hâlâ devam ediyorsa, otomatik aylık gönderim bir sonraki aya ertelenir.',
	'icon_sent_title' => 'Bu sevkiyatta zaten teslim edildi',
	'icon_sent_alt' => 'Teslim edildi',
	'icon_queued_title' => 'Bir sonraki parti için planlandı',
	'icon_queued_alt' => 'Planlanmış',
	'send_now_button' => 'Haber bültenini abonelere şimdi gönder',
	'send_now_confirm' => 'Haber bültenini abonelere şimdi göndermek istiyor musun?',
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
	1 => 'Haber bülteni abonelere gönderilir.',
	-2 => 'Bu eklenti, FlatPress\'e entegre edilmiş LastEntries eklentisini gerektirir. Lütfen eklenti alanında önceden etkinleştirin!',
	2 => 'Ayarlar kaydedildi.'
);
?>
