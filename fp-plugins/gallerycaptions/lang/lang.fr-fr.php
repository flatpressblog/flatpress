<?php
/**
 * Admin area phrases
 */
// "Plugins" menu entry
$lang ['admin'] ['uploader'] ['submenu'] ['gallerycaptions'] = 'Légendes de la galerie';

// Plugin configuration panel
$lang ['admin'] ['uploader'] ['gallerycaptions'] = array(
	'head' => 'Légendes de la galerie',
	'label_selectgallery' => 'Sélectionnez la galerie à modifier:',
	'button_selectgallery' => 'Sélectionner une galerie',
	'label_editcaptionsforgallery' => 'Modifier les légendes pour la galerie:',
	'label_noimagesingallery' => 'Cette galerie ne contient pas encore d’images ¯\_(ツ)_/¯<br>' . //
		'<br>Téléchargez des images via le <a href="' . BLOG_BASEURL . 'admin.php?p=uploader&action=default' . '">Chargeur</a>, puis ajoutez-les à la galerie en utilisant le <a href="' . BLOG_BASEURL . 'admin.php?p=uploader&action=mediamanager' . '">Gestionnaire de médias</a>!',
	'button_savecaptions' => 'Enregistrer les légendes'
);
?>
