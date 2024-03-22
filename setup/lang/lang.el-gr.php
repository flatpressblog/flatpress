<?php
/*
 * LangId: Greek
 */
$lang ['setup'] = array(
	'setup' => 'Ρύθμιση'
);

$lang ['locked'] = array(
	'head' => 'Η ρύθμιση είναι κλειδωμένη',
	'descr' => 'Φαίνεται ότι η ρύθμιση τρέχει ήδη: Το αρχείο κλειδώματος <code>%s</code> υπάρχει ήδη.
		
		Αν θέλετε να επανεκκινήσετε την εγκατάσταση, διαγράψτε πρώτα αυτό το αρχείο.
		
		<strong >Προσοχή!</strong> Το αρχείο <code>setup.php</code> και ο κατάλογος <code>setup/</code> δεν πρέπει να παραμείνουν στον διακομιστή. Παρακαλούμε διαγράψτε τα μετά την ολοκλήρωση της εγκατάστασης!
		
		<ul>
		<li><a href="%s">Εντάξει, πήγαινέ με στο blog μου</a></li>
		<li><a href="%s">Έχω διαγράψει το αρχείο. Επανεκκίνηση της εγκατάστασης.</a></li>
		</ul>'
);

$lang ['err'] = array(
	'setuprun1' => 'Η εγκατάσταση εκτελείται.',

	'setuprun2' => 'Η εγκατάσταση εκτελείται ήδη: Εάν είστε διαχειριστής, μπορείτε να διαγράψετε το ',
	'setuprun3' => ' για επανεκκίνηση.',
	'writeerror' => 'Σφάλμα γραφής',

	'fpuser1' => ' δεν είναι έγκυρος χρήστης. ' . //
		'Το όνομα χρήστη πρέπει να είναι αλφαριθμητικό και δεν πρέπει να περιέχει κενά.',
	'fpuser2' => ' δεν είναι έγκυρος χρήστης. ' . //
		'Το όνομα χρήστη μπορεί να περιέχει μόνο γράμματα, αριθμούς και 1 υπογράμμιση.',
	'fppwd' => 'Ο κωδικός πρόσβασης πρέπει να περιέχει τουλάχιστον 6 χαρακτήρες και όχι κενά.',
	'fppwd2' => 'Οι κωδικοί πρόσβασης δεν ταιριάζουν.',
	'email' => ' δεν είναι έγκυρη διεύθυνση ηλεκτρονικού ταχυδρομείου.',
	'www' => ' δεν είναι έγκυρη διεύθυνση URL.',
	'error' => '<p><big>Σφάλμα!</big> ' . //
		'Κατά την επεξεργασία της φόρμας προέκυψαν τα ακόλουθα σφάλματα:</p><ul>'
);

$lang ['step1'] = array(
	'head' => 'Καλώς ήρθατε στην FlatPress!',
	'descr' => 'Σας ευχαριστούμε που επιλέξατε την <strong>FlatPress</strong>.

		Πριν ξεκινήσετε με το ολοκαίνουργιο ιστολόγιό σας, πρέπει να καθορίσετε μερικά μικρά πράγματα.

		Αλλά μην ανησυχείτε, δεν θα πάρει πολύ χρόνο!',
	'descrl1' => 'Επιλέξτε τη γλώσσα σας.',
	'descrl2' => '<a class="hint" onclick="toggleinfo();">Δεν υπάρχει στη λίστα;</a>',
	'descrlang' => 'Αν δεν βρείτε τη γλώσσα σας στη λίστα, δείτε αν υπάρχει <a href="https://wiki.flatpress.org/res:language" target="_blank" rel="external">κατάλληλο πακέτο γλώσσας</a>:

		<pre>%s</pre>

		Για να εγκαταστήσετε ένα γλωσσικό πακέτο, απλά φορτώστε τα περιεχόμενά του στον κατάλογο <code>flatpress/</code>. Στη συνέχεια, κάντε <a href="./setup.php">επανεκκίνηση της εγκατάστασης</a>.',
	'descrw' => '<strong>Το μόνο πράγμα</strong> που χρειάζεστε για να τρέξετε το FlatPress είναι ένας <em>εγγράψιμος</em> κατάλογος.

		<pre>%s</pre>'
);

$lang ['step2'] = array(
	'head' => 'Δημιουργία χρήστη',
	'descr' => 'Σχεδόν έτοιμο! Απομένουν μόνο οι ακόλουθες λεπτομέρειες:',
	'fpuser' => 'Όνομα χρήστη',
	'fppwd' => 'Κωδικός πρόσβασης',
	'fppwd2' => 'Κωδικός πρόσβασης (επανάληψη)',
	'www' => 'Αρχική σελίδα',
	'email' => 'Ηλεκτρονικό ταχυδρομείο'
);

$lang ['step3'] = array(
	'head' => 'Έτοιμο',
	'descr' => '<strong>Αυτό ήταν όλο.</strong>

		Δεν πρέπει να γίνει πιστευτή?

		Όχι, στην πραγματικότητα <strong>μόλις αρχίζει</strong>! Αλλά το blogging είναι πλέον <em>η δουλειά σας</em> ;)

		<ul>
		<li>Στην <a href="%s">κυρίως σελίδα του ιστολογίου σας</a></li>
		<li>Καλή διασκέδαση στο blogging! <a href="%s">Συνδεθείτε τώρα</a></li>
		<li>Θέλετε να μας επαινέσετε ή να μας επικρίνετε? Επισκεφθείτε μας στη διεύθυνση <a href="https://www.flatpress.org/" target="_blank" rel="external">FlatPress.org</a>!</li>
		</ul>

		Σας ευχαριστούμε που επιλέξατε την FlatPress!'
);

$lang ['buttonbar'] = array(
	'next' => 'Περαιτέρω >'
);

$lang ['samplecontent'] = array();

$lang ['samplecontent'] ['menu'] ['subject'] = 'Μενού';
$lang ['samplecontent'] ['menu'] ['content'] = '[list]
[*][url=?]Αρχική σελίδα[/url]
[*][url=?paged=1]Blog[/url]
[*][url=static.php?page=about]Σχετικά με το[/url]
[*][url=contact.php]Επικοινωνία[/url]
[/list]';

$lang ['samplecontent'] ['entry'] ['subject'] = 'Καλώς ήρθατε στην FlatPress!';
$lang ['samplecontent'] ['entry'] ['content'] = 'Αυτό είναι ένα δείγμα ανάρτησης. Σας δείχνει μερικές από τις λειτουργίες του [url=https://www.flatpress.org target=_blank rel=external]FlatPress[/url].

Το στοιχείο "more" σας επιτρέπει να μεταβείτε από το περίγραμμα του άρθρου στο πλήρες άρθρο.

[more]


[h4]Μορφοποίηση κειμένου[/h4]

Στο FlatPress μορφοποιείτε το περιεχόμενό σας με [url=https://wiki.flatpress.org/doc:plugins:bbcode target=_blank rel=external]BBcode[/url] (Bulletin-Board-Code). Αυτό είναι πολύ εύκολο με το BBCode. Θέλετε μερικά παραδείγματα; [b] κάνει [b]έντονο κείμενο[/b], [i] [i]πλάγιο[/i].

[quote]Το στοιχείο [b]quote[/b] μπορεί να χρησιμοποιηθεί για τη σήμανση εισαγωγικών.[/quote]

[code]Το στοιχείο \'code\' δημιουργεί ένα τμήμα με σταθερό πλάτος χαρακτήρων.
Μπορεί επίσης να
   να αναπαριστά εσοχές.[/code]

Τα στοιχεία \'img\' (εικόνες) και \'url\' (σύνδεσμοι) έχουν ειδικές επιλογές. Μπορείτε να μάθετε περισσότερα σχετικά με αυτό στο [url=https://wiki.flatpress.org/doc:plugins:bbcode target=_blank rel=external]FlatPress-Wiki[/url].


[h4]Καταχωρήσεις (άρθρα ιστολογίου) και στατικές σελίδες[/h4]

Αυτή είναι μια καταχώρηση, ενώ η [url=static.php?page=about]Σχετικά με το[/url] είναι μια [b]στατική σελίδα[/b]. Μια στατική σελίδα, σε αντίθεση με μια καταχώρηση, δεν μπορεί να σχολιαστεί και δεν εμφανίζεται στις λίστες των καταχωρήσεων του ιστολογίου.

Οι στατικές σελίδες είναι χρήσιμες για γενικές πληροφορίες, για παράδειγμα μια σταθερή αρχική σελίδα ή το αποτύπωμα. Θα μπορούσατε ακόμη και να απαρνηθείτε εντελώς τις λειτουργίες του ιστολογίου και να χρησιμοποιήσετε το FlatPress για να δημιουργήσετε έναν ιστότοπο με μόνο στατικές σελίδες.

Στην [url=admin.php]περιοχή διαχείρισης[/url] μπορείτε να δημιουργήσετε καταχωρήσεις και στατικές σελίδες - και να ορίσετε αν η αρχική σελίδα του ιστολογίου σας FlatPress θα είναι μια στατική σελίδα ή η επισκόπηση του ιστολογίου.


[h4]Plugins[/h4]

Μπορείτε να προσαρμόσετε εκτενώς το FlatPress στις ανάγκες σας επεκτείνοντάς το με [url=https://wiki.flatpress.org/doc:plugins:standard target=_blank rel=external]Plugins[/url]. Το BBCode, για παράδειγμα, είναι ένα plugin.

Εδώ είναι μερικά ακόμα δείγματα περιεχομένου που σας δείχνουν ακόμα περισσότερα χαρακτηριστικά του FlatPress :)

Δύο στατικές σελίδες είναι ήδη έτοιμες για εσάς:
[list]
[*][url=static.php?page=about]Σχετικά με το[/url]
[*][url=static.php?page=menu]Μενού[/url] (Το περιεχόμενο αυτής της στατικής σελίδας εμφανίζεται επίσης στην πλαϊνή μπάρα του ιστολογίου σας - αυτή είναι η μαγεία του [b]blockparser widget[/b]. Το [url=https://wiki.flatpress.org/ target=_blank rel=external]FlatPress-Wiki[/url] έχει πληροφορίες σχετικά με αυτό, και πολλά άλλα!)
[/list]

Με το πρόσθετο [b]PhotoSwipe-Plugin[/b] μπορείτε τώρα να τοποθετήσετε τις εικόνες σας ακόμα πιο εύκολα, είτε ως float="left"- είτε ως float="right" ευθυγραμμισμένη μεμονωμένη εικόνα, που περιβάλλεται από το κείμενο.
Μπορείτε ακόμη και να παρουσιάσετε ολόκληρες γκαλερί στους επισκέπτες σας με το στοιχείο \'gallery\'. Μπορείτε να μάθετε πόσο εύκολο είναι [url=https://wiki.flatpress.org/res:plugins:photoswipe target=_blank rel=external]εδώ[/url].


[h4]Widgets[/h4]

Κανένα από τα στοιχεία στην πλαϊνή μπάρα του ιστολογίου σας δεν είναι σταθερό, μπορείτε να τα μετακινήσετε, να τα αφαιρέσετε και να προσθέσετε νέα στην περιοχή διαχείρισης.

Αυτά τα στοιχεία ονομάζονται [b]widgets[/b]. Φυσικά, το FlatPress Wiki έχει πολλές χρήσιμες πληροφορίες σχετικά με αυτό το θέμα [url=https://wiki.flatpress.org/doc:tips:widgets target=_blank rel=external]επίσης[/url].


[h4]Θέματα[/h4]

[gallery="images/Leggero-Themepreview/" width="140"]
Με το θέμα FlatPress Leggero έχετε στη διάθεσή σας 3 πρότυπα στυλ - από κλασικό έως μοντέρνο. Αυτά τα πρότυπα είναι μια θαυμάσια αρχή για να δημιουργήσετε κάτι δικό σας.


[h4]Ακόμα περισσότερο[/h4]

Θα θέλατε να μάθετε περισσότερα για το FlatPress?

[list]
[*]Στο [url=https://www.flatpress.org/?x target=_blank rel=external]blog του έργου[/url] μπορείτε να μάθετε τι συμβαίνει αυτή τη στιγμή στο έργο FlatPress.
[*]Επισκεφθείτε το [url=https://forum.flatpress.org/ target=_blank rel=external]Φόρουμ υποστήριξης[/url] για υποστήριξη και επικοινωνία με άλλους χρήστες του FlatPress.
[*]Κατεβάστε σπουδαία θέματα [b]δημιουργημένα από την κοινότητα[/b] από το [url=https://wiki.flatpress.org/res:themes target=_blank rel=external]Wiki[/url].
[*]Υπάρχουν επίσης σπουδαία [url=https://wiki.flatpress.org/res:plugins target=_blank rel=external]plugins[/url] εκεί.
[*]Αποκτήστε [url=https://wiki.flatpress.org/res:language target=_blank rel=external]πακέτο μεταφράσεων[/url] για τη γλώσσα σας.
[*]Μπορείτε επίσης να ακολουθήσετε την FlatPress στα [url=https://twitter.com/FlatPress target=_blank rel=external]X (Twitter)[/url] και [url=https://fosstodon.org/@flatpress target=_blank rel=external]Mastodon[/url].
[/list]


[h4]Πώς μπορώ να υποστηρίξω το FlatPress?[/h4]

[list]
[*]Υποστηρίξτε το έργο με μια [url=https://www.flatpress.org/home/static.php?page=donate target=_blank rel=external]μικρή δωρεά[/url].
[*][url=https://www.flatpress.org/contact/ target=_blank rel=external]Αναφέρετε[/url] σφάλματα που έχουν εμφανιστεί ή στείλτε μας προτάσεις για βελτίωση.
[*]Οι προγραμματιστές είναι ευπρόσδεκτοι να μας υποστηρίξουν στο [url=https://github.com/flatpressblog/flatpress target=_blank rel=external]GitHub[/url].
[*]Μεταφράστε το FlatPress και την τεκμηρίωσή του στη [url=https://wiki.flatpress.org/res:language target=_blank rel=external]γλώσσα σας[/url].
[*]Γίνετε μέλος της κοινότητας του FlatPress στο [url=https://forum.flatpress.org/ target=_blank rel=external]Φόρουμ υποστήριξης[/url].
[*]Πείτε στον κόσμο πόσο σπουδαίο είναι το FlatPress! :)
[/list]


[h4]Λοιπόν, τι κάνουμε τώρα?[/h4]

[url=login.php]Συνδεθείτε[/url] για να ξεκινήσετε να γράφετε ιστολόγια στην [url=admin.php]Έκθεση διαχείρισης[/url].

Καλή διασκέδαση! :)

[i]Η ομάδα [url=https://www.flatpress.org target=_blank rel=external]FlatPress[/url][/i]

';

$lang ['samplecontent'] ['about'] ['subject'] = 'Σχετικά με το';
$lang ['samplecontent'] ['about'] ['content'] = 'Γράψτε κάτι για τον εαυτό σας και αυτό το ιστολόγιο εδώ. ([url=admin.php?p=static&action=write&page=about]Δούλεψε με![/url])';

$lang ['samplecontent'] ['privacy-policy'] ['subject'] = 'Πολιτική απορρήτου';
$lang ['samplecontent'] ['privacy-policy'] ['content'] = 'Σε ορισμένες χώρες, εάν χρησιμοποιείτε την υπηρεσία Akismet Antispam, για παράδειγμα, είναι απαραίτητο να παρέχετε στους επισκέπτες σας μια πολιτική απορρήτου. Μια πολιτική απορρήτου μπορεί επίσης να είναι απαραίτητη εάν ο επισκέπτης μπορεί να χρησιμοποιήσει τη φόρμα επικοινωνίας ή τη λειτουργία σχολίων.

[b]Συμβουλή:[/b] Υπάρχουν πολλά πρότυπα και γεννήτριες στο διαδίκτυο.

Μπορείτε να τα εισάγετε εδώ. ([url=admin.php?p=static&action=write&page=privacy-policy]Επεξεργαστείτε με![/url])

Εάν ενεργοποιήσετε το πρόσθετο CookieBanner, οι επισκέπτες σας θα μπορούν να μεταβούν απευθείας σε αυτή τη σελίδα στη φόρμα επικοινωνίας και στη λειτουργία σχολιασμού.
';
?>
