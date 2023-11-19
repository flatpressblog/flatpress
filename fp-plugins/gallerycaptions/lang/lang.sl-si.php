<?php
/**
 * Phrases for the admin area
 */
// "Vstavki" menu entry
$lang['admin']['uploader']['submenu']['gallerycaptions'] = 'Napisni podnapisi galerije';

// Panel za konfiguracijo vstavka
$lang['admin']['uploader']['gallerycaptions'] = array(
    'head' => 'Napisni podnapisi galerije',
    'label_selectgallery' => 'Izberite galerijo za urejanje:',
    'button_selectgallery' => 'Izberi galerijo',
    'label_editcaptionsforgallery' => 'Uredi podnapise za galerijo:',
    'label_noimagesingallery' => 'Ta galerija še ne vsebuje nobenih slik ¯\_(ツ)_/¯<br>
<br>
Naložite slike preko <a href="' . BLOG_BASEURL . 'admin.php?p=uploader&action=default' . '">Uploader</a>, nato jih dodajte v galerijo z uporabo <a href="' . BLOG_BASEURL . 'admin.php?p=uploader&action=mediamanager' . '">Upravitelja medijev</a>!',
    'button_savecaptions' => 'Shrani podnapise'
);
?>