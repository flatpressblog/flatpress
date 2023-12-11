<?php
/**
 * Admin area phrases
 */
// "Plugins" menu entry
$lang ['admin'] ['uploader'] ['submenu'] ['gallerycaptions'] = 'Leyendas de la galería';

// Plugin configuration panel
$lang ['admin'] ['uploader'] ['gallerycaptions'] = array(
	'head' => 'Leyendas de la galería',
	'label_selectgallery' => 'Seleccione la galería que desea editar:',
	'button_selectgallery' => 'Seleccionar galería',
	'label_editcaptionsforgallery' => 'Edita los pies de foto de la galería:',
	'label_noimagesingallery' => 'Esta galería aún no contiene imágenes ¯\_(ツ)_/¯<br>' . //
		'<br>Sube imágenes a través del <a href="' . BLOG_BASEURL . 'admin.php?p=uploader&action=default' . '">Descargador</a> y añádelas a la galería con el <a href="' . BLOG_BASEURL . 'admin.php?p=uploader&action=mediamanager' . '">Administrador de medios</a>!',
	'button_savecaptions' => 'Guardar textos de imágenes'
);
?>
