<?php
/**
 * Admin area phrases
 */
// "Plugins" menu entry
$lang ['admin'] ['uploader'] ['submenu'] ['gallerycaptions'] = 'Λεζάντες γκαλερί';

// Plugin configuration panel
$lang ['admin'] ['uploader'] ['gallerycaptions'] = array(
	'head' => 'Λεζάντες γκαλερί',
	'label_selectgallery' => 'Επιλέξτε γκαλερί για επεξεργασία:',
	'button_selectgallery' => 'Επιλέξτε γκαλερί',
	'label_editcaptionsforgallery' => 'Επεξεργαστείτε λεζάντες για τη γκαλερί:',
	'label_noimagesingallery' => 'Αυτή η γκαλερί δεν περιέχει ακόμα εικόνες ¯\_(ツ)_/¯<br>' . //
		'<br>Ανεβάστε εικόνες μέσω του <a href="' . BLOG_BASEURL . 'admin.php?p=uploader&action=default' . '">Uploader</a> και, στη συνέχεια, προσθέστε τις στη γκαλερί χρησιμοποιώντας τον <a href="' . BLOG_BASEURL . 'admin.php?p=uploader&action=mediamanager' . '">Διαχειριστή πολυμέσων</a>!',
	'button_savecaptions' => 'Αποθήκευση λεζάντων'
);
?>
