<?php
/**
 * Admin area phrases
 */
// "Plugins" menu entry
$lang ['admin'] ['uploader'] ['submenu'] ['gallerycaptions'] = 'Galerien irudi-testuak';

// Plugin configuration panel
$lang ['admin'] ['uploader'] ['gallerycaptions'] = array(
	'head' => 'Galerien irudi-testuak',
	'label_selectgallery' => 'Hautatu editatzeko galeria:',
	'button_selectgallery' => 'Hautatu galeria',
	'label_editcaptionsforgallery' => 'Editatu galeri honen irudi-testuak:',
	'label_noimagesingallery' => 'Galeria honek ez dauka irudirik oraindik ¯\_(ツ)_/¯<br>' . //
		'<br>Igo irudiak <a href="' . BLOG_BASEURL . 'admin.php?p=uploader&action=default' . '">fitxategi-kargatzailea</a> erabiliz. Ondoren dagokion galeriara gehitu ahalko duzu <a href="' . BLOG_BASEURL . 'admin.php?p=uploader&action=mediamanager' . '">multimedia-kudeatzailearekin</a>!',
	'button_savecaptions' => 'Gorde irudi-testuak'
);
?>
