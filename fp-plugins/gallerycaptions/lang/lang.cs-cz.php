<?php
/**
 * Admin area phrases
 */
// "Plugins" menu entry
$lang ['admin'] ['uploader'] ['submenu'] ['gallerycaptions'] = 'Galerie: Popisky';

// Plugin configuration panel
$lang ['admin'] ['uploader'] ['gallerycaptions'] = array(
	'head' => 'Galerie: Popisky',
	'label_selectgallery' => 'Vyberte galerii, kterou chcete upravit:',
	'button_selectgallery' => 'Vyberte galerii',
	'label_editcaptionsforgallery' => 'Titulek k této galerii:',
	'label_noimagesingallery' => 'Tato galerie zatím neobsahuje žádné obrázky ¯\_(ツ)_/¯<br>' . //
		'<br>Nahrajte je pomocí <a href="' . BLOG_BASEURL . 'admin.php?p=uploader&action=default' . '">Uploaderu</a> a poté je přidejte do galerie pomocí <a href="' . BLOG_BASEURL . 'admin.php?p=uploader&action=mediamanager' . '">Správce médií</a>!',
	'button_savecaptions' => 'Uložení textů obrázků'
);
?>
