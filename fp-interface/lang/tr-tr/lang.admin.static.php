<?php
$lang ['admin'] ['static'] ['submenu'] = array(
	'list' => 'Statik Sayfaları Yönet',
	'write' => 'Statik Sayfa Yaz'
);

/* ana panel */
$lang ['admin'] ['static'] ['list'] = array(
	'head' => 'Statik Sayfalar',
	'descr' => 'Lütfen düzenlemek için bir sayfa seçin veya <a href="admin.php?p=static&amp;action=write">yeni bir sayfa ekleyin</a>.',

	'sel' => 'Seç', // onay kutusu
	'date' => 'Tarih',
	'name' => 'Sayfa',
	'title' => 'Başlık',
	'author' => 'Yazar',

	'action' => 'Eylem',
	'act_view' => 'Görüntüle',
	'act_del' => 'Sil',
	'act_edit' => 'Düzenle',

	'natural' => 'Başlıkları oluşturulma tarihine göre değil, tersine sırala.',
	'submit' => 'Sayfa adlarını yeniden sırala'
);

$lang ['admin'] ['static'] ['list'] ['msgs'] = array(
	1 => 'Sayfa başarıyla kaydedildi',
	-1 => 'Sayfa kaydedilirken bir hata oluştu',
	2 => 'Sayfa başarıyla silindi',
	-2 => 'Sayfa silinirken bir hata oluştu'
);

/* yazma paneli */
$lang ['admin'] ['static'] ['write'] = array(
	'head' => 'Statik Sayfa Yayınla',
	'descr' => 'Sayfayı yayınlamak için formu düzenleyin',
	'fieldset1' => 'Düzenle',
	'subject' => 'Konu (*):',
	'content' => 'İçerik (*):',
	'fieldset2' => 'Gönder',
	'pagename' => 'Sayfa Adı (*):',
	'submit' => 'Yayınla',
	'preview' => 'Önizleme',

	'delfset' => 'Sil',
	'deletemsg' => 'Bu sayfayı sil',
	'del' => 'Sil',
	'success' => 'Sayfanız başarıyla yayınlandı',
	'otheropts' => 'Diğer seçenekler'
);

$lang ['admin'] ['static'] ['write'] ['error'] = array(
	'subject' => 'Konu boşken gönderemezsiniz',
	'content' => 'İçerik boşken gönderemezsiniz',
	'id' => 'Geçerli bir id girmelisiniz gerekmektedir'
);

/* silme eylemi */	
$lang ['admin'] ['static'] ['delete'] = array(
	'head' => 'Sayfayı Sil',
	'descr' => 'Aşağıdaki sayfayı silmek üzeresiniz:',
	'preview' => 'Önizleme',
	'confirm' => 'Devam etmek istediğinizden emin misiniz?',
	'fset' => 'Sil',
	'ok' => 'Evet, bu sayfayı sil',
	'cancel' => 'Hayır, panelime geri dön',
	'err' => 'Belirtilen sayfa mevcut değil'
);
?>
