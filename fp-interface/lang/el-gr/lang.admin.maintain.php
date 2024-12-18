<?php
$lang ['admin'] ['panel'] ['maintain'] = 'Maintainance';

$lang ['admin'] ['maintain'] ['default'] = array(
	'head' => 'Συντήρηση',
	'descr' => 'Ελάτε εδώ αν πιστεύετε πως κάτι χάλασε και μπορεί εδώ να βρείτε λύση. Παρόλα αυτά, ίσως να μην πετύχει.',
	'opt0' => '&laquo; Πίσω στο βασικό μενού',
	'opt1' => 'Ανακατασκευή ευρετηρίου',
	'opt2' => 'Εκκαθάριση προσωρινής μνήμης θεμάτων και προτύπων',
	'opt3' => 'Επαναφορά αδειών για παραγωγική λειτουργία',
	'opt4' => 'Εμφάνιση πληροφοριών σχετικά με την PHP',
	'opt5' => 'Έλεγχος για αναβαθμίσεις',

	'chmod_info' => 'Αν τα δικαιώματα <strong>δεν μπόρεσαν</strong> να επαναρυθμιστούν, ο ιδιοκτήτης του αρχείου/καταλόγου πιθανόν να μην είναι ο ίδιος με τον ιδιοκτήτη του διακομιστή ιστού.<br>' . //
		'
		<table>
			<thead>
				<tr>
					<th>Εξουσιοδοτήσεις</th>
					<th>' . FP_CONTENT . '</th>
					<th>πυρήνας</th>
					<th>Όλα τα άλλα</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>αρχεία</td>
					<td>' . decoct(FILE_PERMISSIONS) . '</td>
					<td>' . decoct(CORE_FILE_PERMISSIONS) . '</td>
					<td>' . decoct(RESTRICTED_FILE_PERMISSIONS) . '</td>
				</tr>
				<tr>
					<td>Κατάλογοι</td>
					<td>' . decoct(DIR_PERMISSIONS) . '</td>
					<td>' . decoct(CORE_DIR_PERMISSIONS) . '</td>
					<td>' . decoct(RESTRICTED_DIR_PERMISSIONS) . '</td>
				</tr>
			</tbody>
		</table>
		',

	'opt3_success' => 'Όλες οι άδειες ενημερώθηκαν επιτυχώς.',
	'opt3_error' => 'Σφάλμα κατά τη ρύθμιση των εξουσιοδοτήσεων:'
);

$lang ['admin'] ['maintain'] ['default'] ['msgs'] = array(
	1 => 'Η διαδικασία ολοκληρώθηκε',
	-1 => 'Η διαδικασία απέτυχε'
);

$lang ['admin'] ['maintain'] ['updates'] = array(
	'head' => 'Αναβαθμίσεις',
	'list' => '<ul>
		<li>Έχετε το FlatPress έκδοση <big>%s</big></li>
		<li>Η τελευταία σταθερή έκδοση του FlatPress είναι <big><a href="%s">%s</a></big></li>
		<li>Η τελευταία δοκιμαστική έκδοση του FlatPress είναι <big><a href="%s">%s</a></big></li>
		</ul>',
	'notice' => 'Σημείωση:'
);

$lang ['admin'] ['maintain'] ['updates'] ['msgs'] = array(
	1 => 'Υπάρχουν διαθέσιμες αναβαθμίσεις!',
	2 => 'Χρησιμοποιήτε ήδη την τελευταία έκδοση',
	-1 => 'Αδυναμία λήψης αναβαθμίσεων'
);
?>
