<?php
/**
 * Admin area phrases
 */
// "Plugins" menu entry
$lang ['admin'] ['uploader'] ['submenu'] ['gallerycaptions'] = 'Gallerier: Billedtekster';

// Plugin configuration panel
$lang ['admin'] ['uploader'] ['gallerycaptions'] = array(
	'head' => 'Gallerier: Billedtekster',
	'label_selectgallery' => 'Vælg galleri:',
	'button_selectgallery' => 'Vælg galleri',
	'label_editcaptionsforgallery' => 'Billedtekst til dette galleri:',
	'label_noimagesingallery' => 'Dette galleri indeholder endnu ikke nogen billeder ¯\_(ツ)_/¯<br>
<br>
Upload nogle med <a href="' . BLOG_BASEURL . 'admin.php?p=uploader&action=default' . '">Uploader</a>, og tildel dem derefter til galleriet med <a href="' . BLOG_BASEURL . 'admin.php?p=uploader&action=mediamanager' . '">Media Manager</a>!',
	'button_savecaptions' => 'Gem billedtekster'
);