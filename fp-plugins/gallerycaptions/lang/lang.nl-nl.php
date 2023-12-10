<?php
/**
 * Admin area phrases
 */
// "Plugins" menu entry
$lang ['admin'] ['uploader'] ['submenu'] ['gallerycaptions'] = 'Galerijen: Bijschriften';

// Plugin configuration panel
$lang ['admin'] ['uploader'] ['gallerycaptions'] = array(
	'head' => 'Galerijen: Bijschriften',
	'label_selectgallery' => 'Selecteer galerij om te bewerken:',
	'button_selectgallery' => 'Selecteer galerij',
	'label_editcaptionsforgallery' => 'Bijschrift voor deze galerij:',
	'label_noimagesingallery' => 'Deze galerij bevat nog geen foto\'s ¯\_(ツ)_/¯<br>' . //
		'<br>Upload ze met de <a href="' . BLOG_BASEURL . 'admin.php?p=uploader&action=default' . '">Uploader</a> en wijs ze dan toe aan de galerij met de <a href="' . BLOG_BASEURL . 'admin.php?p=uploader&action=mediamanager' . '">Mediabeheerder</a>!',
	'button_savecaptions' => 'Bijschriften opslaan'
);
?>
