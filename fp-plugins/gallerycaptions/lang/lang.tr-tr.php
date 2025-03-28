<?php
/**
 * Yönetici alanı ifadeleri
 */
// "Eklentiler" menü girişi
$lang ['admin'] ['uploader'] ['submenu'] ['gallerycaptions'] = 'Galeri başlıkları';

// Eklenti yapılandırma paneli
$lang ['admin'] ['uploader'] ['gallerycaptions'] = array(
	'head' => 'Galeri başlıkları',
	'label_selectgallery' => 'Düzenlemek için galeri seçin:',
	'button_selectgallery' => 'Galeri seç',
	'label_editcaptionsforgallery' => 'Galeri başlıklarını düzenle:',
	'label_noimagesingallery' => 'Bu galeri henüz hiçbir resim içermiyor ¯\_(ツ)_/¯<br>' . //
		'<br>Resimleri <a href="' . BLOG_BASEURL . 'admin.php?p=uploader&action=default' . '">Yükleyici</a> aracılığıyla yükleyin, ardından <a href="' . BLOG_BASEURL . 'admin.php?p=uploader&action=mediamanager' . '">Medya Yöneticisi</a> ile galerinizden ekleyin!',
	'button_savecaptions' => 'Başlıkları kaydet'
);
?>
