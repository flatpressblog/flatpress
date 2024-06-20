<?php
$lang ['admin'] ['plugin'] ['submenu'] ['seometataginfo'] = 'SEO';
$lang ['admin'] ['plugin'] ['seometataginfo'] = array(

	// SEO robots.txt part 1
	'head' => 'SEO robots.txt',
	'description1' => 'Το αρχείο <code>robots.txt</code> ελέγχει τους ανιχνευτές μιας μηχανής αναζήτησης και τη συμπεριφορά των ανιχνευτών στο ιστολόγιό σας FlatPress. ' . //
		'Εδώ μπορείτε να δημιουργήσετε και να επεξεργαστείτε ένα αρχείο <code>rotots.txt</code> για τη βελτιστοποίηση των μηχανών αναζήτησης.',
	'location' => '<strong>Τοποθεσία αποθήκευσης:</strong> ' . $_SERVER ['DOCUMENT_ROOT'] . '/',
	'submit' => 'Αποθήκευση του robots.txt',

	// SEO Metatags part
	'legend_desc' => 'Περιγραφή και λέξεις-κλειδιά',
	'description' => 'Αυτά τα στοιχεία διευκολύνουν την εύρεσή τους από τις μηχανές αναζήτησης και την ανάρτησή τους στα μέσα κοινωνικής δικτύωσης. <a class="hint" href="https://en.wikipedia.org/wiki/Meta_element" title="Wikipedia" target="_blank">Wikipedia</a>',
	'input_desc' => 'Εισάγετε την περιγραφή:',
	'sample_desc' => 'FlatPress σχετικά άρθρα, οδηγοί και plugins',
	'input_keywords' => 'Εισάγετε τις λέξεις-κλειδιά:',
	'sample_keywords' => 'flatpress, flatpress άρθρα, flatpress οδηγοί, flatpress πρόσθετα',
	'input_noindex' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=en#noindex" target="_blank" title="Διαβάστε περισσότερα για το noindex">Απαγόρευση ευρετηρίασης</a>:',
	'input_nofollow' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=en#nofollow" target="_blank" title="Διαβάστε περισσότερα για το nofollow">Απαγόρευση της παρακολούθησης</a>:',
	'input_noarchive' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=en#noarchive" target="_blank" title="Διαβάστε περισσότερα για το noarchive">Απαγόρευση αρχειοθέτησης</a>:',
	'input_nosnippet' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=en#nosnippet" target="_blank" title="Διαβάστε περισσότερα για το nosnippet">Απαγόρευση αποσπασμάτων</a>:'
);

$lang ['plugin'] ['seometataginfo'] = array(
	'sep' => ' - ',
	'home' => 'Αρχική σελίδα',
	'blog_home' => 'Blog Αρχική σελίδα',
	'blog_page' => 'Blog',
	'archive' => 'Αρχείο',
	'category' => 'Κατηγορία',
	'tag' => 'Tag',
	'contact' => 'Επικοινωνήστε μαζί μας',
	'comments' => 'Σχόλια',
	'pagenum' => 'Σελίδα #'
);

// SEO robots.txt part 2
$lang ['admin'] ['plugin'] ['seometataginfo'] ['msgs'] = array(
	1 => 'Το αρχείο <code>robots.txt</code> αποθηκεύτηκε με επιτυχία',
	-1 => 'Το αρχείο <code>robots.txt</code> δεν μπόρεσε να αποθηκευτεί (Δεν υπάρχει εξουσιοδότηση εγγραφής στο <code>' . $_SERVER ['DOCUMENT_ROOT'] . '</code>)?',

	2 => 'Οι ρυθμίσεις αποθηκεύτηκαν με επιτυχία',
	-2 => 'Εμφανίστηκε σφάλμα κατά την αποθήκευση'
);

$lang ['plugin'] ['seometataginfo'] ['errors'] = array (
	-2 => 'Δεν υπάρχει <code>robots.txt</code> ή δεν μπορεί να δημιουργηθεί <code>robots.txt</code> στο ριζικό κατάλογο του εγγράφου HTTP.'
);
?>
