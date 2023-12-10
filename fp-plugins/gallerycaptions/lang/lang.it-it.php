<?php
/**
 * Admin area phrases
 */
// "Plugins" menu entry
$lang ['admin'] ['uploader'] ['submenu'] ['gallerycaptions'] = 'Didascalie della Galleria';

// Plugin configuration panel
$lang ['admin'] ['uploader'] ['gallerycaptions'] = array(
	'head' => 'Didascalie della Galleria',
	'label_selectgallery' => 'Seleziona una galleria da modificare:',
	'button_selectgallery' => 'Seleziona la galleria',
	'label_editcaptionsforgallery' => 'Modifica le didascalie per la galleria:',
	'label_noimagesingallery' => 'Questa galleria non contiene ancora immagini ¯\_(ツ)_/¯<br>' .
		'<br>Carica le immagini attraverso il <a href="' . BLOG_BASEURL . 'admin.php?p=uploader&action=default' . '">Caricatore</a>, poi aggiungile alla galleria usando il <a href="' . BLOG_BASEURL . 'admin.php?p=uploader&action=mediamanager' . '">Media Manager</a>!',
	'button_savecaptions' => 'Salva le didascalie'
);
?>
