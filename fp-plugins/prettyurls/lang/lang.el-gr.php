<?php
$lang ['plugin'] ['prettyurls'] ['errors'] = array (
	-2 => 'Δεν υπάρχει διαθέσιμο <code>.htaccess</code> ή δεν μπορεί να δημιουργηθεί <code>.htaccess</code> στη ρίζα του ιστολογίου. ' . //
		'Το πρόσθετο PrettyURLs μπορεί τότε να μην λειτουργεί σωστά.'
);

$lang ['admin'] ['plugin'] ['submenu'] ['prettyurls'] = 'PrettyURLs';
$lang ['admin'] ['plugin'] ['prettyurls'] = array(
	'head' => 'Διαμόρφωση της PrettyURL',
	'htaccess' => '.htaccess',
	'description' => 'Αυτός ο επεξεργαστής σας επιτρέπει να επεξεργαστείτε απευθείας το <code>.htaccess</code> που απαιτείται για το πρόσθετο PrettyUrls.',
	'cantsave' => 'Αυτό το αρχείο δεν μπορεί να επεξεργαστεί επειδή προστατεύεται από εγγραφή. Αλλάξτε τα δικαιώματα πρόσβασης ή αντιγράψτε αυτές τις γραμμές, επικολλήστε τις σε ένα τοπικό αρχείο και στη συνέχεια ανεβάστε το.',
	'mode' => 'Λειτουργία',
	'auto' => 'Αυτόματο',
	'autodescr' => 'Καθορίστε αυτόματα την καλύτερη επιλογή',
	'pathinfo' => 'Πληροφορίες διαδρομής',
	'pathinfodescr' => 'Παράδειγμα: /index.php/2011/01/01/hello-world/',
	'httpget' => 'HTTP Get',
	'httpgetdescr' => 'Παράδειγμα: /?u=/2011/01/01/hello-world/',
	'pretty' => 'Pretty',
	'prettydescr' => 'Παράδειγμα: /2011/01/01/hello-world/',

	'saveopt' => 'Αποθήκευση ρυθμίσεων',

	'submit' => 'Αποθήκευση .htaccess'
);

$lang ['admin'] ['plugin'] ['prettyurls'] ['msgs'] = array(
	1 => 'Το αρχείο .htaccess αποθηκεύτηκε με επιτυχία',
	-1 => 'Το αρχείο .htaccess δεν μπόρεσε να αποθηκευτεί (δεν υπάρχει εξουσιοδότηση εγγραφής στο <code>' . BLOG_ROOT . '</code>)?',

	2 => 'Οι ρυθμίσεις αποθηκεύτηκαν με επιτυχία',
	-2 => 'Εμφανίστηκε σφάλμα κατά την αποθήκευση'
);
?>
