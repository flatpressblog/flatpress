<?php
$lang ['admin'] ['entry'] ['submenu'] = array(
	'list' => 'Yazıları Yönet',
	'write' => 'Yazı Yaz',
	'cats' => 'Kategorileri Yönet'
);

/* varsayılan işlem */
$lang ['admin'] ['entry'] ['list'] = array(
	'head' => 'Yazıları Yönet',
	'descr' => 'Lütfen düzenlemek için bir yazı seçin veya <a href="admin.php?p=entry&amp;action=write">yeni bir tane ekleyin</a><br>' . //
		'<a href="admin.php?p=entry&amp;action=cats">Kategorileri düzenle</a>',
	'drafts' => 'Taslaklar: ',
	'filter' => 'Filtre: ',
	'nofilter' => 'Hepsini göster',
	'filterbtn' => 'Filtreyi uygula',
	'sel' => 'Seç', // checkbox
	'date' => 'Tarih',
	'title' => 'Başlık',
	'author' => 'Yazar',
	'comms' => 'Yorumlar', // comments
	'action' => 'İşlem',
	'act_del' => 'Sil',
	'act_view' => 'Görüntüle',
	'act_edit' => 'Düzenle'
);

/* yazma işlemi */
$lang ['admin'] ['entry'] ['write'] = array(
	'head' => 'Yazı Yaz',
	'descr' => 'Yazı yazmak için formu düzenleyin',
	'uploader' => 'Yükleyici',
	'fieldset1' => 'Düzenle',
	'subject' => 'Konu (*):',
	'content' => 'İçerik (*):',
	'fieldset2' => 'Gönder',
	'submit' => 'Yayınla',
	'preview' => 'Önizle',
	'savecontinue' => 'Kaydet ve Devam Et',
	'categories' => 'Kategoriler',
	'nocategories' => 'Henüz kategori yok. <a href="admin.php?p=entry&amp;action=cats">Kendi kategorilerinizi oluşturun</a> ana yazı panelinden. ' . //
		'<a href="#save">Önce yazınızı kaydedin</a>',
	'saveopts' => 'Kaydetme seçenekleri',
	'success' => 'Yazınız başarıyla yayınlandı',
	'otheropts' => 'Diğer seçenekler',
	'commmsg' => 'Bu yazıya ait yorumları yönet',
	'delmsg' => 'Bu yazıyı sil'
	// 'back' => 'Değişiklikleri kaydetmeden geri dön',
);

$lang ['admin'] ['entry'] ['list'] ['msgs'] = array(
	1 => 'Yazı başarıyla kaydedildi',
	-1 => 'Yazıyı kaydederken bir hata oluştu',
	2 => 'Yazı başarıyla silindi',
	-2 => 'Yazıyı silerken bir hata oluştu'
);
$lang ['admin'] ['entry'] ['write'] ['error'] = array(
	'subject' => 'Konu alanı boşken gönderemezsiniz',
	'content' => 'Yazı alanı boşken gönderemezsiniz'
);

$lang ['admin'] ['entry'] ['write'] ['msgs'] = array(
	1 => 'Yazı başarıyla kaydedildi',
	-1 => 'Bir hata oluştu: yazınız başarıyla kaydedilemedi',
	-2 => 'Bir hata oluştu: yazınız kaydedilmedi; anasayfa bozulmuş olabilir',
	-3 => 'Bir hata oluştu: yazınız taslak olarak kaydedildi',
	-4 => 'Bir hata oluştu: yazınız taslak olarak kaydedildi; anasayfa bozulmuş olabilir',
	'draft' => 'Bir <strong>taslak</strong> düzenliyorsunuz'
);

/* yorumlar */
$lang ['admin'] ['entry'] ['commentlist'] = array(
	'head' => 'Yazı için yorumlar: ',
	'descr' => 'Buradan yorumları düzenleyebilir veya silebilirsiniz.',
	'sel' => 'Seç',
	'content' => 'İçerik',
	'date' => 'Tarih',
	'author' => 'Yazar',
	'email' => 'E-posta',
	'ip' => 'IP adresi',
	'actions' => 'İşlemler',
	'act_edit' => 'Düzenle',
	'act_del' => 'Sil',
	'act_del_confirm' => 'Bu yorumu gerçekten silmek istiyor musunuz?',
	'nocomments' => 'Bu yazıya henüz yorum yapılmamış.'
);

$lang ['admin'] ['entry'] ['commentlist'] ['msgs'] = array(
	1 => 'Yorum başarıyla silindi',
	-1 => 'Yorumu silerken bir hata oluştu'
);

$lang ['admin'] ['entry'] ['commedit'] = array(
	'head' => 'Bu yazı için yorumu düzenle: ',
	'descr' => 'Buradan yorumun yazarını, adını, e-posta adresini ve web sitesini düzenleyebilirsiniz.<br><br>',
	'content' => 'İçerik',
	'date' => 'Tarih',
	'author' => 'Yazar',
	'www' => 'Web Sitesi',
	'email' => 'E-posta',
	'ip' => 'IP adresi',
	'loggedin' => 'Giriş yapmış yönetici',
	'submit' => 'Değişiklikleri kaydet',
	'commentlist' => 'Yorumlar listesine geri dön'
);

$lang ['admin'] ['entry'] ['commedit'] ['error'] = array(
	'name' => 'Ad boş bırakılamaz.',
	'email' => 'E-posta adresi hatalı.',
	'url' => 'Web sitesi adresi hatalı ve adres <strong>http://</strong> veya <strong>https://</strong> ile başlamalıdır.',
	'content' => 'Yorum boş bırakılamaz.'
);

$lang ['admin'] ['entry'] ['commedit'] ['msgs'] = array(
	1 => 'Yorum başarıyla düzenlendi',
	-1 => 'Yorumu düzenlerken bir hata oluştu'
);

/* silme işlemi */
$lang ['admin'] ['entry'] ['delete'] = array(
	'head' => 'Yazıyı Sil',
	'descr' => 'Aşağıdaki yazıyı silmek üzeresiniz: ',
	'preview' => 'Önizle',
	'confirm' => 'Devam etmek istediğinizden emin misiniz?',
	'fset' => 'Sil',
	'ok' => 'Evet, bu yazıyı sil',
	'cancel' => 'Hayır, panelime geri dön',
	'err' => 'Belirtilen yazı mevcut değil'
);

/* kategori yönetimi */
$lang ['admin'] ['entry'] ['cats'] = array(
	'head' => 'Kategorileri Düzenle',
	'descr' => '<p>Aşağıdaki formu kullanarak kategorilerinizi ekleyebilir ve düzenleyebilirsiniz.</p>' . //
		'<p>Her kategori öğesi şu formatta olmalıdır: "kategori adı: <em>id_numarası</em>". Öğeleri hiyerarşi oluşturmak için tireler ile girintileyin.</p>
		
	<p>Örnek:</p>
	<pre>
Genel :1
Haberler :2
--Duyurular :3
--Etkinlikler :4
----Diğer :5
Teknoloji :6
	</pre>',
	'clear' => 'Tüm kategori verilerini sil',

	'fset1' => 'Düzenleyici',
	'fset2' => 'Değişiklikleri Uygula',
	'submit' => 'Kaydet'
);

$lang ['admin'] ['entry'] ['cats'] ['msgs'] = array(
	1 => 'Kategoriler kaydedildi',
	-1 => 'Kategoriler kaydedilirken bir hata oluştu',
	2 => 'Kategoriler temizlendi',
	-2 => 'Kategoriler temizlenirken bir hata oluştu',
	-3 => 'Kategori ID\'leri yalnızca pozitif olmalıdır (0 kabul edilmez)'
);
?>
