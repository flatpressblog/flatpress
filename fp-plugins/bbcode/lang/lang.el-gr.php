<?php
$lang ['admin'] ['plugin'] ['submenu'] ['bbcode'] = 'BBCode';
$lang ['admin'] ['plugin'] ['bbcode'] = array(
	'head' => 'Ρύθμιση του BBCode',
	'desc1' => 'Το πρόσθετο αυτό επιτρέπει τη χρήση σύνταξης <a href="https://wiki.flatpress.org/' . //
		'doc:techfaq#bbcode" class="hint" target="_blank">BBCode</a>.',

	'options' => 'Επιλογές',

	'editing' => 'Επεξεργασία',
	'allow_html' => 'Χρήση HTML',
	'allow_html_long' => 'Επιτρέπεται χρήση HTML παράλληλα με το BBCode',
	'toolbar' => 'Μπάρα συντομεύσεων',
	'toolbar_long' => 'Ενεργοποιείται η μπάρα συντομεύσεων κατά την καταχώρηση.',

	'other' => 'Άλλες επιλογές',
	'comments' => 'Σχόλια',
	'comments_long' => 'Επιτρέπεται η χρήση BBCode στα σχόλια',
	'urlmaxlen' => 'Μέγιστο μήκος συνδέσμων',
	'urlmaxlen_long_pre' => 'Σμίκρυνση συνδέσμων μεγαλύτερων από ',
	'urlmaxlen_long_post'=>' χαρακτήρες.',

	'attachsdir' => 'Λήψεις αρχείων',
	'attachsdir_long' => 'Απόκρυψη καταλόγου μεταφόρτωσης (fp-content/attachs/)',

	'submit' => 'Αποθήκευση ρύθμισης',
	'msgs' => array(
		1 => 'Η ρύθμιση του BBCode αποθηκεύτηκε επιτυχώς.',
		-1 => 'Η ρύθμιση του BBCode δεν αποθηκεύτηκε.'
	),

	'editor' => array(
		'formatting' => 'Formatting',
		'textarea' => 'Textarea: ',
		'expand' => 'Expand',
		'expandtitle' => 'Expand Textarea Height',
		'reduce' => 'Reduce',
		'reducetitle' => 'Reduce Textarea Height',
		'urltitle' => 'URL/ Σύνδεσμος',
		'mailtitle' => 'Διεύθυνση ηλεκτρονικού ταχυδρομείου',
		'boldtitle' => 'Έντονα',
		'italictitle' => 'Πλάγια',
		'headlinetitle' => 'Επικεφαλής',
		'underlinetitle' => 'Υπογραμμισμένα',
		'crossouttitle' => 'Διαγραμμένο',
		'unorderedlisttitle' => 'Μη ταξινομημένος κατάλογος',
		'orderedlisttitle' => 'Ταξινομημένος κατάλογος',
		'quotetitle' => 'Παράθεση',
		'codetitle' => 'Κώδικας',
		'htmltitle' => 'Εισαγωγή ως κώδικας HTML',
		'help' => 'Βοήθεια σχετικά με το BBCode',
		'file' => 'Φάκελος: ',
		'image' => 'Εικόνα: ',
		'selection' => '-- Επιλογή --'
	)
);

$lang ['plugin'] ['bbcode'] = array (
	'go_to' => 'Πηγαίνετε στον',

	// Filewrapper getfille.php
	'error_403' => 'Σφάλμα 403', // neu
	'not_send' => 'Το ζητούμενο αρχείο δεν μπορεί να αποσταλεί.', // neu
	'error_404' => 'Σφάλμα 404', // neu
	'not_found' => 'Το ζητούμενο αρχείο δεν βρέθηκε.', // neu
	'file' => 'Αρχείο', // neu
	'report_error_1' => '', // neu
	'report_error_2' => 'Αναφορά σφάλματος', // neu
	'blog_search_1' => '', // neu
	'blog_search_2' => 'Αναζήτηση στο ιστολόγιο', // neu
	'start_page_1' => '', // neu
	'start_page_2' => 'ή πίσω στην αρχική σελίδα' // neu
);
?>
