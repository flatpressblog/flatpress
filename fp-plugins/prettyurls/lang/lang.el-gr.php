<?php
$lang ['plugin'] ['prettyurls'] ['errors'] = array (
	-2 => 'Δεν υπάρχει διαθέσιμο <code>.htaccess</code> ή δεν μπορεί να δημιουργηθεί <code>.htaccess</code> στη ρίζα του ιστολογίου. ' . //
		'Το πρόσθετο PrettyURLs μπορεί τότε να μην λειτουργεί σωστά.'
);

$lang ['admin'] ['plugin'] ['submenu'] ['prettyurls'] = 'PrettyURLs';
$lang ['admin'] ['plugin'] ['prettyurls'] = array(
	'head' => 'Διαμόρφωση της PrettyURL',
	'description1' => 'Εδώ μπορείτε να μετατρέψετε τις τυπικές διευθύνσεις URL του FlatPress σε όμορφες, φιλικές προς το SEO διευθύνσεις URL.',
	'nginx' => 'PrettyURLs με NGINX',
	'fpprotect_is_on' => 'Το πρόσθετο PrettyURLs απαιτεί ένα αρχείο .htaccess. ' . //
		'Για να δημιουργήσετε ή να αλλάξετε αυτό το αρχείο, <a href="admin.php?p=plugin&action=default" title="Μεταβείτε στη διαχείριση του πρόσθετου">απενεργοποιήστε</a> το πρόσθετο FlatPress Protect. ',
	'fpprotect_is_off' => 'Το πρόσθετο FlatPress Protect προστατεύει το αρχείο .htaccess από ακούσιες αλλαγές. ' . //
		'Μπορείτε να ενεργοποιήσετε το πρόσθετο <a href="admin.php?p=plugin&action=default" title="Μεταβείτε στη διαχείριση του πρόσθετου">εδώ</a>!',
	'wiki_nginx' => 'https://wiki.flatpress.org/res:plugins:prettyurls#nginx',
	'htaccess' => '.htaccess',
	'description2' => 'Αυτός ο επεξεργαστής σας επιτρέπει να επεξεργαστείτε απευθείας το <code>.htaccess</code> που απαιτείται για το πρόσθετο PrettyUrls.<br>' . //
		'<strong>Σημείωση:</strong> Μόνο οι διακομιστές ιστού που είναι συμβατοί με την NCSA, όπως ο Apache, αναγνωρίζουν την έννοια των αρχείων .htaccess. ' . //
		'Το λογισμικό του διακομιστή σας είναι: <strong>' . $_SERVER ['SERVER_SOFTWARE'] . '</strong>',
	'cantsave' => 'Αυτό το αρχείο δεν μπορεί να επεξεργαστεί επειδή προστατεύεται από εγγραφή. Αλλάξτε τα δικαιώματα πρόσβασης ή αντιγράψτε αυτές τις γραμμές, επικολλήστε τις σε ένα τοπικό αρχείο και στη συνέχεια ανεβάστε το.',
	'mode' => 'Λειτουργία',
	'auto' => 'Αυτόματο',
	'autodescr' => 'Καθορίστε αυτόματα την καλύτερη επιλογή',
	'pathinfo' => 'Πληροφορίες διαδρομής',
	'pathinfodescr' => 'Παράδειγμα: /index.php/2024/01/01/hello-world/',
	'httpget' => 'HTTP Get',
	'httpgetdescr' => 'Παράδειγμα: /?u=/2024/01/01/hello-world/',
	'pretty' => 'Pretty',
	'prettydescr' => 'Παράδειγμα: /2024/01/01/hello-world/',

	'saveopt' => 'Αποθήκευση ρυθμίσεων',

	'location' => '<strong>Τοποθεσία αποθήκευσης:</strong> ' . ABS_PATH . '',
	'submit' => 'Αποθήκευση .htaccess'
);

$lang ['admin'] ['plugin'] ['prettyurls'] ['msgs'] = array(
	1 => 'Το αρχείο .htaccess αποθηκεύτηκε με επιτυχία',
	-1 => 'Το αρχείο .htaccess δεν μπόρεσε να αποθηκευτεί (δεν υπάρχει εξουσιοδότηση εγγραφής στο <code>' . BLOG_ROOT . '</code>)?',

	2 => 'Οι ρυθμίσεις αποθηκεύτηκαν με επιτυχία',
	-2 => 'Εμφανίστηκε σφάλμα κατά την αποθήκευση'
);
?>
