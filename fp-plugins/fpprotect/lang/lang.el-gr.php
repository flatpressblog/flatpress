<?php
$lang ['admin'] ['config'] ['submenu'] ['fpprotect'] = 'FlatPress Protect';

$lang ['admin'] ['config'] ['fpprotect'] = array(
	'head' => 'Ρυθμίσεις FlatPress Protect',
	'desc1' => 'Εδώ μπορείτε να αλλάξετε τις επιλογές που σχετίζονται με την ασφάλεια για το ιστολόγιό σας FlatPress. ' . //
		'Η καλύτερη προστασία για τους επισκέπτες σας και το FlatPress blog σας είναι όταν όλες οι επιλογές είναι απενεργοποιημένες.',

	// Part for unsafe inline scripts
	'allow_unsafe_inline' => 'Επιτρέψτε τα ανασφαλή Java scripts (Δεν συνιστάται)',

	'allowUnsafeInlineDsc' => '<p>Επιτρέπει τη φόρτωση μη ασφαλούς inline κώδικα JavaScript.</p>' . //
		'<p><br>Σημείωση για τους προγραμματιστές πρόσθετων: Παρακαλούμε προσθέστε ένα nonce στο Java script σας.</p>' . //
		'Ένα παράδειγμα για PHP:
		<pre>$random_hex = RANDOM_HEX;
' . //
		'... script nonce="\' . $random_hex . \'" src=" ...' . //
		'</pre>' . //
		'Ένα παράδειγμα για το πρότυπο Smarty:
		<pre>' . //
		'... script nonce="{$smarty.const.RANDOM_HEX}" ...' . //
		'</pre>' . //
		'<p>Αυτό διασφαλίζει ότι το πρόγραμμα περιήγησης του επισκέπτη εκτελεί μόνο σενάρια Java που προέρχονται από το ιστολόγιό σας FlatPress.</p>',

	// Part for the PrettyURLs .htaccess edit-field
	'allow_htaccess_edit' => 'Επιτρέπει τη δημιουργία και την επεξεργασία του αρχείου .htaccess.',
	'allowPrettyURLEditDsc' => 'Επιτρέπει την πρόσβαση στο πεδίο επεξεργασίας .htaccess του πρόσθετου PrettyURLs για τη δημιουργία ή την τροποποίηση του αρχείου .htaccess.',

	// Part for metadate in images after upload
	'allow_image_metadate' => 'Διατήρηση των μεταδεδομένων και της ποιότητας της αρχικής εικόνας στις εικόνες που μεταφορτώνονται.',
	'allowImageMetadataDsc' => 'Μετά τη μεταφόρτωση των εικόνων με το πρόγραμμα μεταφόρτωσης, τα μεταδεδομένα διατηρούνται. Αυτά περιλαμβάνουν πληροφορίες για την κάμερα και τις γεωγραφικές συντεταγμένες, για παράδειγμα.',

	// Part for the visitor-ip in FlatPress
	'allow_visitor_ip' => 'Επιτρέψτε στην FlatPress να χρησιμοποιήσει τη μη ανώνυμη διεύθυνση IP του επισκέπτη.',
	'allowVisitorIpDsc' => 'Το FlatPress θα αποθηκεύσει στη συνέχεια τη μη ανώνυμη διεύθυνση IP στα σχόλια, μεταξύ άλλων. ' . //
		'Εάν χρησιμοποιείτε την υπηρεσία Antispam Akismet, το Akismet θα λάβει επίσης τη μη ανωνυμοποιημένη διεύθυνση IP.',

	'submit' => 'Αποθήκευση ρυθμίσεων',
		'msgs' => array(
		1 => 'Οι ρυθμίσεις αποθηκεύτηκαν με επιτυχία.',
		-1 => 'Σφάλμα κατά την αποθήκευση των ρυθμίσεων.'
	),

	// Warning message
	'warning_allowUnsafeInline' => 'Προειδοποίηση: Content-Security-Policy -> Αυτή η πολιτική περιέχει "unsafe-inline", η οποία είναι επικίνδυνη στην πολιτική script-src-policy.',
	'warning_allowVisitorIp' => 'Προειδοποίηση: Χρήση μη ανωνυμοποιημένων διευθύνσεων IP των επισκεπτών -> Μην ξεχάσετε να ενημερώσετε τους <a href="static.php?page=privacy-policy" title="edit static page">επισκέπτες του FlatPress blog σας</a> σχετικά με αυτό!'
);
?>
