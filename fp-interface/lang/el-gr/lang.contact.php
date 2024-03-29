<?php
$baseurl = BLOG_BASEURL;

$lang ['contact'] = array(
	'head' => 'Επικοινωνία',
	'descr' => 'Συμπηρώστε την φόρμα παρακάτω αν επιθυμείτε να επικοινωνήσετε μαζί μου. Παρακαλώ προσθέστε την διεύθυνση ηλεκτρονικού ταχυδρομείου σας αν επιθυμείτε να λάβετε απάντηση.',
	'fieldset1' => 'Στοιχεία χρήστη',
	'name' => 'Όνομα (*)',
	'email' => 'Ηλ. ταχυδρομείο:',
	'www' => 'Προσωπική σελίδα:',
	'cookie' => 'Να με θυμάσαι',
	'fieldset2' => 'Το μήνυμα σας',
	'comment' => 'Μήνυμα (*):',
	'fieldset3' => 'Αποστολή',
	'submit' => 'Αποστολή',
	'reset' => 'Ακύρωση',
	'loggedin' => 'Έχετε συνδεθεί 😉. <a href="' . $baseurl . 'login.php?do=logout">Αποσυνδεθείτε</a> ή μεταβείτε στο <a href="' . $baseurl . 'admin.php">περιοχή διαχείρισης</a>.'
);

$lang ['contact'] ['notification'] = array(
	'name' => 'Όνομα:',
	'email' => 'Ηλεκτρονικό ταχυδρομείο:',
	'www' => 'Ιστοσελίδα:',
	'content' => 'Μήνυμα:',
	'subject' => 'Επικοινωνία που αποστέλλεται μέσω '
);

$lang ['contact'] ['error'] = array(
	'name' => 'Πρέπει να εισάγετε ένα όνομα',
	'email' => 'Πρέπει να εισάγετε μια ισχύουσα ηλεκτρονική διεύθυνση',
	'www' => 'Πρέπει να προσθέσετε μια ισχύουσα σελίδα',
	'content' => 'Πρέπει να εισάγετε ένα μήνυμα'
);

$lang ['contact'] ['msgs'] = array(
	1 => 'Το μήνυμα σας εστάλη επιτυχώς',
	-1 => 'Το μήνυμα σας δεν μπόρεσε να σταλεί'
);	
?>