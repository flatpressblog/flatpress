<?php
$lang ['admin'] ['entry'] ['submenu'] ['commentcenter'] = 'Comment Center';
$lang ['admin'] ['entry'] ['commentcenter'] = array(
	// Header of the panel
	'title' => 'Comment Center',
	'desc1' => 'Dieses Panel ermöglicht es dir, die Kommentare in deinem Blog zu verwalten.',
	'desc2' => 'Hier kannst du mehrere Dinge tun:',

	// Links
	'lpolicies' => 'Verwaltung der Richtlinien',
	'lapprove' => 'Blockierte Kommentare anzeigen',
	'lmanage' => 'Kommentare verwalten',
	'lconfig' => 'Konfigurieren des Plugins',

	// Policies
	'policies' => 'Richtlinien',
	'desc_pol' => 'Hier kannst du die Richtlinien für Kommentare bearbeiten.',
	'select' => 'Auswählen',
	'criteria' => 'Kriterien',
	'behavoir' => 'Verhalten',
	'options' => 'Einstellungen',
	'entry' => 'Eintrag',
	'entries' => 'Einträge',
	'categories' => 'Kategorien',
	'nopolicies' => 'Es gibt keine Richtlinien',
	'all_entries' => 'Alle Einträge',
	'fol_entries' => 'Die Richtlinie gilt für die folgenden Einträge:',
	'fol_cats' => 'Die Richtlinie gilt für Einträge in den folgenden Kategorien:',
	'older' => 'Die Richtlinie gilt für Einträge, die älter als %d Tag (e) sind.',
	'allow' => 'Kommentare erlauben',
	'block' => 'Kommentare verbieten',
	'approvation' => 'Kommentare müssen genehmigt werden',
	'up' => 'Nach oben',
	'down' => 'Nach unten',
	'edit' => 'Bearbeiten',
	'delete' => 'Löschen',
	'newpol' => 'Eine neue Richtlinie hinzufügen',
	'del_selected' => 'Ausgewählte Richtlinie (n) löschen',
	'select_all' => 'Alle auswählen',
	'deselect_all' => 'Keine auswählen',

	// Configuration page
	'configure' => 'Konfigurieren des Plugins',
	'desc_conf' => 'Hier kannst du die Optionen des Plugins ändern.',
	'log_all' => 'Blockierte Kommentare protokollieren',
	'log_all_long' => 'Aktiviere diese Option, wenn du auch blockierte Kommentare protokollieren möchtest.',
	'email_alert' => 'Benachrichtigung per E-Mail',
	'email_alert_long' => 'Wenn du einen Kommentar zum Genehmigen prüfen sollst, kannst du ' . 'über E-Mail informiert werden.',
	'akismet' => 'Akismet',
	'akismet_use' => 'Kommentar-Prüfung mit Akismet',
	'akismet_key' => 'Akismet-Schlüssel',
	'akismet_key_long' => 'Der Akismet-Dienst stellt dir einen Schlüssel zur Verfügung. Füge diesen hier ein.',
	'akismet_url' => 'Blog-URL für Akismet',
	'akismet_url_long' => 'Für den kostenlosen Service von Akismet solltest du nur eine Domain verwenden. ' . 'Du kannst dieses Feld leer lassen. Es wird dann <code>%s</code> verwendet.',
	'save_conf' => 'Einstellungen speichern',

	// Edit policy page
	'apply_to' => 'Anwenden auf',
	'editpol' => 'Bearbeiten einer Richtlinie',
	'createpol' => 'Erstellen einer Richtlinie',
	'some_entries' => 'Bestimmte Einträge',
	'properties' => 'Eintrag mit bestimmten Eigenschaften',
	'se_desc' => 'Wenn du die Option %s ausgewählt habst, füge bitte Einträge ein, die du auf diese Richtlinie anwenden möchtest.',
	'se_fill' => 'Bitte fülle die Felder mit der ID der Einträge aus (<code>entryYYMMDD-HHMMSS</code>).',
	'po_title' => 'Eigenschaften',
	'po_desc' => 'Wenn du die Option %s ausgewählt hast, fülle bitte die Eigenschaften aus.',
	'po_comp' => 'Die Felder sind nicht obligatorisch, aber du musst mindestens eines ausfüllen oder die Richtlinie ' . 'wird auf alle Einträge gelten.',
	'po_time' => 'Zeiteinstellungen',
	'po_older' => 'Auf Einträge anwenden, die älter sind als ',
	'days' => 'Tage.',
	'save_policy' => 'Richtlinie speichern',

	// Delete policies page
	'del_policies' => 'Richtlinien löschen',
	'del_descs' => 'Du wirst diese Richtlinie löschen: ',
	'del_descm' => 'Du wirst diese Richtlinien löschen: ',
	'sure' => 'Bist du sicher?',
	'del_subs' => 'Ja, bitte löschen',
	'del_subm' => 'Ja, bitte alle löschen',
	'del_cancel' => 'Nein, zurück zu den Einstellungen.',

	// Approve comments page
	'app_title' => 'Genehmige den Kommentar',
	'app_desc' => 'Hier kannst du Kommentare genehmigen.',
	'app_date' => 'Datum',
	'app_content' => 'Kommentar',
	'app_author' => 'Verfasser',
	'app_email' => 'Email',
	'app_ip' => 'IP',
	'app_actions' => 'Maßnahmen',
	'app_publish' => 'Veröffentlichung',
	'app_delete' => 'Löschen',
	'app_nocomms' => 'Es gibt keinen Kommentar.',
	'app_pselected' => 'Ausgewählte Kommentare veröffentlichen',
	'app_dselected' => 'Ausgewählte Kommentare entfernen',
	'app_other' => 'Sonstige Bemerkungen',
	'app_akismet' => 'Als Spam erkannt',
	'app_spamdesc' => 'Diese Kommentare wurden von Akismet blockiert.',
	'app_hamsubmit' => 'Beim Veröffentlichen auch gleich als Ham an Akismet melden.',
	'app_pubnotham' => 'Veröffentlichen, aber nicht an Akismet übertragen',

	// Delete comments page
	'delc_title' => 'Kommentare löschen',
	'delc_descs' => 'Du wirst diesen Kommentar löschen: ',
	'delc_descm' => 'Du wirst diese Kommentare löschen: ',

	// Manage comments page
	'man_searcht' => 'Einen Eintrag suchen',
	'man_searchd' => 'Füge die ID des Eintrags ein, dessen Kommentare du verwalten möchten.',
	'man_search' => 'Suche',
	'man_commfor' => 'Bemerkungen für %s',
	'man_spam' => 'Als Spam an Akismet melden',

	// The simple edit
	'simple_pre' => 'Die Kommentare zu diesem Eintrag ',
	'simple_1' => 'werden erlaubt.',
	'simple_0' => 'benötigen deine Zustimmung.',
	'simple_-1' => 'werden geblockt.',
	'simple_manage' => 'Verwalte die Kommentare zu diesem Eintrag.',
	'simple_edit' => 'Richtlinien bearbeiten',

	// Akismet warnings
	'akismet_errors' => array(
		-1 => 'Der Akismet-Schlüssel ist leer. Bitte gebe diesen ein.',
		-2 => 'Wir konnten die Akismet-Server nicht erreichen.',
		-3 => 'Die Reaktion von Akismet schlug fehl.',
		-4 => 'Der Akismet-Schlüssel ist ungültig.'
	),

	// Messages
	'msgs' => array(
		1 => 'Konfiguration gespeichert.',
		-1 => 'Beim Speichern der Konfiguration ist ein Fehler aufgetreten.',

		2 => 'Richtlinie gespeichert.',
		-2 => 'Beim Speichern der Richtlinie ist ein Fehler aufgetreten (vielleicht sind Ihre Einstellungen falsch).',

		3 => 'Richtlinie verschoben.',
		-3 => 'Beim Versuch, die Richtlinie zu verschieben, ist ein Fehler aufgetreten (oder sie kann nicht verschoben werden).',

		4 => 'Richtlinie (n) entfernt.',
		-4 => 'Beim Versuch, die Richtlinie (n) zu entfernen, ist ein Fehler aufgetreten (oder Sie haben keine Richtlinie ausgewählt).',

		5 => 'Kommentar (e) veröffentlicht.',
		-5 => 'Beim Versuch, die Kommentare zu veröffentlichen, ist ein Fehler aufgetreten.',

		6 => 'Kommentar (e) entfernt.',
		-6 => 'Beim Versuch, die Kommentare zu entfernen, ist ein Fehler aufgetreten (oder Sie haben keinen Kommentar ausgewählt).',

		7 => 'Kommentar eingereicht.',
		-7 => 'Beim Absenden des Kommentars ist ein Fehler aufgetreten.'
	),

	// Errors
	'errors' => array(
		'pol_nonex' => 'Die Richtlinie, die Sie bearbeiten möchten, existiert nicht.',
		'entry_nf' => 'Der gewählte Eintrag existiert nicht.'
	)
);
$lang ['plugin'] ['commentcenter'] = array(
	'akismet_error' => 'Sorry, wir stossen auf technische Schwierigkeiten.',
	'lock' => 'Dieser Eintrag kann leider nicht kommentiert werden.',
	'approvation' => 'Der Kommentar wurde gespeichert, aber der Administrator muss ihn freischalten, bevor er angezeigt wird.',

	// Mail for comments
	'mail_subj' => 'Neuer Kommentar zu genehmigen %s'
);

$lang ['plugin'] ['commentcenter'] ['mail_text'] = 'Hallo %toname%,

"%fromname%" %frommail% hat einen Kommentar zu dem Eintrag geschrieben mit dem Titel "%entrytitle%"
Aber dieser braucht deine Zustimmung, bevor dieser veröffentlicht wird.

Folgendes wurde als Kommentar geschrieben:
__________________________________________
%content%
__________________________________________

Automatisch generiert von
%blogtitle%

';
