<?php
$lang ['admin'] ['entry'] ['submenu'] ['commentcenter'] = 'Yorum Merkezi';
$lang ['admin'] ['entry'] ['commentcenter'] = array(
	// Panel başlığı
	'title' => 'Yorum Merkezi',
	'desc1' => 'Bu panel, blogunuzdaki yorumları yönetmenizi sağlar.',
	'desc2' => 'Burada yapabileceğiniz birkaç şey şunlardır:',

	// Bağlantılar
	'lpolicies' => 'Kuralları yönet',
	'lapprove' => 'Engellenmiş yorumları göster',
	'lmanage' => 'Yorumları yönet',
	'lconfig' => 'Eklentiyi yapılandır',
	'faq_spamcomments' => 'İstenmeyen yorumlarla başa çıkmak için yardım alın',

	// Kurallar
	'policies' => 'Kurallar',
	'desc_pol' => 'Burada yorum kurallarını düzenleyebilirsiniz.',
	'select' => 'Seç',
	'criteria' => 'Kriter',
	'behavoir' => 'Davranış',
	'options' => 'Seçenekler',
	'entry' => 'gönderi',
	'entries' => 'gönderiler',
	'categories' => 'Kategoriler',
	'nopolicies' => 'Herhangi bir kural yok.',
	'all_entries' => 'Tüm gönderiler',
	'fol_entries' => 'Kural aşağıdaki gönderilere uygulanmıştır:',
	'fol_cats' => 'Kural aşağıdaki kategorilerdeki gönderilere uygulanmıştır:',
	'older' => 'Kural, %d günden eski gönderilere uygulanmıştır.',
	'allow' => 'Yorum yapmaya izin ver',
	'block' => 'Yorumları engelle',
	'approvation' => 'Yorumların onaylanmalıdır',
	'up' => 'Yukarı taşı',
	'down' => 'Aşağı taşı',
	'edit' => 'Düzenle',
	'delete' => 'Sil',
	'newpol' => 'Yeni bir kural ekle',
	'del_selected' => 'Seçilen kural(ları) sil',
	'select_all' => 'Tümünü Seç',
	'deselect_all' => 'Tümünü Kaldır',

	// Yapılandırma sayfası
	'configure' => 'Eklentiyi yapılandır',
	'desc_conf' => 'Burada eklenti seçeneklerini değiştirebilirsiniz.',
	'log_all' => 'Engellenmiş yorumları kaydet',
	'log_all_long' => 'Engellenmiş yorumları kaydetmek için bunu işaretleyin.',
	'email_alert' => 'Yorumlar hakkında e-posta bildirimi',
	'email_alert_long' => 'Onay bekleyen yorum olduğunda e-posta bildirimi almak için bunu işaretleyin.',
	'akismet' => 'Akismet',
	'akismet_use' => 'Akismet kontrolünü etkinleştir',
	'akismet_use_long' => 'Akismet ile yorumlardaki istenmeyen kısımları azaltabilirsiniz.',
	'akismet_key' => 'Akismet Anahtarı',
	'akismet_key_long' => 'Akismet servisi size bir <a class="hint externlink" href="https://akismet.com/support/getting-started/api-key/" target="_blank">anahtar</a> sağlar. Buraya girin.',
	'akismet_url' => 'Akismet için blog ana URL\'si',
	'akismet_url_long' => 'Akismet’in ücretsiz servisi için sadece bir alan adı kullanmanın yeterli olduğunu düşünüyorum. Bu alanı boş bırakabilirsiniz, <code>%s</code> kullanılacaktır.',
	'save_conf' => 'Yapılandırmayı Kaydet',

	// Kural düzenleme sayfası
	'apply_to' => 'Uygula',
	'editpol' => 'Bir kuralı düzenle',
	'createpol' => 'Bir kural oluştur',
	'some_entries' => 'Bazı gönderiler',
	'properties' => 'Belirli özelliklere sahip gönderi',
	'se_desc' => '%s seçeneğini seçtiyseniz, bu kuralı uygulamak istediğiniz gönderileri girin.',
	'se_fill' => 'Lütfen gönderilerin <a href="admin.php?p=entry">ID</a>’lerini (<code>entryYYMMDD-HHMMSS</code>) alanlara doldurun.',
	'po_title' => 'Özellikler',
	'po_desc' => '%s seçeneğini seçtiyseniz, lütfen özellikleri doldurun.',
	'po_comp' => 'Alanlar zorunlu değildir, ancak en az birini doldurmalısınız, yoksa kural tüm gönderilere uygulanır.',
	'po_time' => 'Zaman seçenekleri',
	'po_older' => 'Şu tarihten önceki gönderilere uygulayın',
	'days' => 'gün.',
	'save_policy' => 'Kuralı Kaydet',

	// Kural silme sayfası
	'del_policies' => 'Kuralları Sil',
	'del_descs' => 'Bu kuralı sileceksiniz: ',
	'del_descm' => 'Bu kuralları sileceksiniz: ',
	'sure' => 'Emin misiniz?',
	'del_subs' => 'Evet, lütfen sil',
	'del_subm' => 'Evet, lütfen silin',
	'del_cancel' => 'Hayır, panelime geri dön',

	// Yorum onaylama sayfası
	'app_title' => 'Yorum Onayla',
	'app_desc' => 'Burada yorumları onaylayabilirsiniz.',
	'app_date' => 'Tarih',
	'app_content' => 'Yorum',
	'app_author' => 'Yazar',
	'app_email' => 'E-posta',
	'app_ip' => 'IP',
	'app_actions' => 'Eylemler',
	'app_publish' => 'Yayınla',
	'app_delete' => 'Sil',
	'app_nocomms' => 'Henüz yorum yok.',
	'app_pselected' => 'Seçilen yorumları yayınla',
	'app_dselected' => 'Seçilen yorumları kaldır',
	'app_other' => 'Diğer Yorumlar',
	'app_akismet' => 'İstenmeyen olarak işaretlendi',
	'app_spamdesc' => 'Bu yorumlar Akismet tarafından engellendi.',
	'app_hamsubmit' => 'Yayınladığınızda Akismet\'e düzmetin olarak gönderin.',
	'app_pubnotham' => 'Yayınlayın ama düzmetin olarak göndermeyin',

	// Yorum silme sayfası
	'delc_title' => 'Yorumları Sil',
	'delc_descs' => 'Bu yorumu sileceksiniz: ',
	'delc_descm' => 'Bu yorumları sileceksiniz: ',

	// Yorumları yönetme sayfası
	'man_searcht' => 'Bir gönderi ara',
	'man_searchd' => 'Yorumlarını yönetmek istediğiniz gönderinin <a href="admin.php?p=entry">ID</a>’sini (<code>entryYYMMDD-HHMMSS</code>) girin.',
	'man_search' => 'Ara',
	'man_commfor' => '%s için yorumlar',
	'man_spam' => 'Akismet\'e istenmeyen olarak gönder',

	// Basit düzenleme
	'simple_pre' => 'Bu gönderi için yorumlar ',
	'simple_1' => 'izin verilecek.',
	'simple_0' => 'onaya ihtiyaç duyacak.',
	'simple_-1' => 'engellenecek.',
	'simple_manage' => 'Bu gönderinin yorumlarını yönetin.',
	'simple_edit' => 'Kuralları Düzenle',

	// Akismet hataları
	'akismet_errors' => array(
		-1 => 'Akismet anahtarı boş. Lütfen girin.',
		-2 => 'Akismet sunucularına bağlanılamadı.',
		-3 => 'Akismet yanıtı başarısız oldu.',
		-4 => 'Akismet anahtarı geçersiz.'
	),

	// Mesajlar
	'msgs' => array(
		1 => 'Yapılandırma kaydedildi.',
		-1 => 'Yapılandırma kaydedilirken bir hata oluştu.',

		2 => 'Kural kaydedildi.',
		-2 => 'Kural kaydedilirken bir hata oluştu (belki ayarlarınız yanlış).',

		3 => 'Kural taşındı.',
		-3 => 'Kural taşınırken bir hata oluştu (ya da taşınamıyor).',

		4 => 'Kural(lar) silindi.',
		-4 => 'Kural(lar) silinirken bir hata oluştu (ya da herhangi bir Kural seçmediniz).',

		5 => 'Yorum(lar) yayınlandı.',
		-5 => 'Yorum(lar) yayınlanırken bir hata oluştu.',

		6 => 'Yorum(lar) kaldırıldı.',
		-6 => 'Yorum(lar) kaldırılırken bir hata oluştu (ya da herhangi bir yorum seçmediniz).',

		7 => 'Yorum gönderildi.',
		-7 => 'Yorum gönderilirken bir hata oluştu.'
	),

	// Hatalar
	'errors' => array(
		'pol_nonex' => 'Düzenlemek istediğiniz kural mevcut değil.',
		'entry_nf' => 'Seçtiğiniz gönderi mevcut değil.'
	)
);

$lang ['plugin'] ['commentcenter'] = array(
	'akismet_error' => 'Üzgünüz, teknik zorluklarla karşılaşıyoruz.',
	'lock' => 'Bu gönderi için yorumlar engellenmiş, üzgünüz.',
	'approvation' => 'Yorum kaydedildi ancak gösterilmeden önce Yönetici onaylamalıdır.',
	
	// Yorumlar için mail
	'mail_subj' => '%s\'de onaylanacak yeni yorum'
);

$lang ['plugin'] ['commentcenter'] ['mail_text'] = 'Sevgili %toname%,

"%fromname%" %frommail% şu başlıklı "%entrytitle%" gönderisine bir yorum gönderdi ancak gösterilmeden önce onayınız gerekiyor.

İşte gönderilen yorum:
__________________________________________
%content%
__________________________________________

Lütfen blogunuzun yönetici paneline giriş yapın ve yorum merkezinde engellenmiş yorumu kontrol edin.

Otomatik olarak oluşturulmuştur
%blogtitle%

';
?>
