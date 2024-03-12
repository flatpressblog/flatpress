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
	'faq_spamcomments' => 'Hilfe zum Umgang mit Spam-Kommentaren erhalten',

	// Policies
	'policies' => 'Richtlinien',
	'desc_pol' => 'Hier kannst du die Richtlinien für Kommentare bearbeiten.',
	'select' => 'Auswählen',
	'criteria' => 'Kriterien',
	'behavoir' => 'Verhalten',
	'options' => 'Einstellungen',
	'entry' => 'Beitrag',
	'entries' => 'Beiträge',
	'categories' => 'Kategorien',
	'nopolicies' => 'Es gibt keine Richtlinien',
	'all_entries' => 'Alle Beiträge',
	'fol_entries' => 'Die Richtlinie gilt für die folgenden Beiträge:',
	'fol_cats' => 'Die Richtlinie gilt für Beiträge in den folgenden Kategorien:',
	'older' => 'Die Richtlinie gilt für Beiträge, die älter als %d Tag (e) sind.',
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
	'email_alert_long' => 'Wenn du einen Kommentar zum Genehmigen prüfen sollst, kannst du über E-Mail informiert werden.',
	'akismet' => 'Akismet',
	'akismet_use' => 'Kommentar-Prüfung mit Akismet',
	'akismet_use_long' => 'Mit <a href="https://akismet.com/" target="_blank">Akismet</a> kann man Spam in Kommentaren reduzieren.',
	'akismet_key' => 'Akismet-Schlüssel',
	'akismet_key_long' => 'Der <a href="https://akismet.com/signup/" target="_blank">Akismet-Dienst</a> stellt dir einen <a class="hint externlink" href="https://akismet.com/support/getting-started/api-key/" target="_blank">Schlüssel</a> zur Verfügung. Füge diesen hier ein.',
	'akismet_url' => 'Blog-URL für Akismet',
	'akismet_url_long' => 'Für den kostenlosen Service von Akismet solltest du nur eine Domain verwenden. Du kannst dieses Feld leer lassen. Es wird dann <code>%s</code> verwendet.',
	'save_conf' => 'Einstellungen speichern',

	// Edit policy page
	'apply_to' => 'Anwenden auf',
	'editpol' => 'Bearbeiten einer Richtlinie',
	'createpol' => 'Erstellen einer Richtlinie',
	'some_entries' => 'Bestimmte Beiträge',
	'properties' => 'Beitrag mit bestimmten Eigenschaften',
	'se_desc' => 'Wenn du die Option %s ausgewählt hast, füge bitte Beiträge ein, die du auf diese Richtlinie anwenden möchtest.',
	'se_fill' => 'Bitte fülle die Felder mit der <a href="admin.php?p=entry">ID</a> der Beiträge aus (<code>entryYYMMDD-HHMMSS</code>).',
	'po_title' => 'Eigenschaften',
	'po_desc' => 'Wenn du die Option %s ausgewählt hast, fülle bitte die Eigenschaften aus.',
	'po_comp' => 'Die Felder sind nicht obligatorisch, aber du musst mindestens eines ausfüllen oder die Richtlinie wird auf alle Beiträge gelten.',
	'po_time' => 'Zeiteinstellungen',
	'po_older' => 'Auf Beiträge anwenden, die älter sind als ',
	'days' => 'Tage.',
	'save_policy' => 'Richtlinie speichern',

	// Delete policies page
	'del_policies' => 'Richtlinien löschen',
	'del_descs' => 'Du wirst diese Richtlinie löschen: ',
	'del_descm' => 'Du wirst diese Richtlinien löschen: ',
	'sure' => 'Bist du sicher?',
	'del_subs' => 'Ja, bitte löschen.',
	'del_subm' => 'Ja, bitte alle löschen.',
	'del_cancel' => 'Nein, zurück zu den Einstellungen.',

	// Approve comments page
	'app_title' => 'Genehmige den Kommentar',
	'app_desc' => 'Hier kannst du Kommentare genehmigen.',
	'app_date' => 'Datum',
	'app_content' => 'Kommentar',
	'app_author' => 'Autor',
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
	'man_searcht' => 'Einen Beitrag suchen',
	'man_searchd' => 'Füge die <a href="admin.php?p=entry">ID</a> (<code>entryYYMMDD-HHMMSS</code>) des Beitrags ein, dessen Kommentare du verwalten möchtest.',
	'man_search' => 'Suche',
	'man_commfor' => 'Bemerkungen für %s',
	'man_spam' => 'Als Spam an Akismet melden',

	// The simple edit
	'simple_pre' => 'Die Kommentare zu diesem Beitrag ',
	'simple_1' => 'werden erlaubt.',
	'simple_0' => 'benötigen deine Zustimmung.',
	'simple_-1' => 'werden geblockt.',
	'simple_manage' => 'Verwalte die Kommentare zu diesem Beitrag.',
	'simple_edit' => 'Richtlinien bearbeiten',

	// Akismet warnings
	'akismet_errors' => array(
		-1 => 'Der Akismet-Schlüssel ist leer. Bitte gebe diesen ein.',
		-2 => 'Wir konnten die Akismet-Server nicht erreichen.',
		-3 => 'Die Akismet-Antwort ist gescheitert.',
		-4 => 'Der Akismet-Schlüssel ist ungültig.'
	),

	// Messages
	'msgs' => array(
		1 => 'Konfiguration gespeichert.',
		-1 => 'Beim Speichern der Konfiguration ist ein Fehler aufgetreten.',

		2 => 'Richtlinie gespeichert.',
		-2 => 'Beim Speichern der Richtlinie ist ein Fehler aufgetreten (vielleicht sind deine Einstellungen falsch).',

		3 => 'Richtlinie verschoben.',
		-3 => 'Beim Versuch, die Richtlinie zu verschieben, ist ein Fehler aufgetreten (oder sie kann nicht verschoben werden).',

		4 => 'Richtlinie (n) entfernt.',
		-4 => 'Beim Versuch, die Richtlinie (n) zu entfernen, ist ein Fehler aufgetreten (oder du hast keine Richtlinie ausgewählt).',

		5 => 'Kommentar (e) veröffentlicht.',
		-5 => 'Beim Versuch, die Kommentare zu veröffentlichen, ist ein Fehler aufgetreten.',

		6 => 'Kommentar (e) entfernt.',
		-6 => 'Beim Versuch, die Kommentare zu entfernen, ist ein Fehler aufgetreten (oder du hast keinen Kommentar ausgewählt).',

		7 => 'Kommentar abgesendet.',
		-7 => 'Beim Absenden des Kommentars ist ein Fehler aufgetreten.'
	),

	// Errors
	'errors' => array(
		'pol_nonex' => 'Die Richtlinie, die du bearbeiten möchtest, existiert nicht.',
		'entry_nf' => 'Der gewählte Beitrag existiert nicht.'
	)
);

$lang ['plugin'] ['commentcenter'] = array(
	'akismet_error' => 'Sorry, wir stossen auf technische Schwierigkeiten.',
	'lock' => 'Dieser Beitrag kann leider nicht kommentiert werden.',
	'approvation' => 'Der Kommentar wurde gespeichert, aber der Administrator muss ihn freischalten, bevor er angezeigt wird.',

	// Mail for comments
	'mail_subj' => 'Neuer Kommentar zu genehmigen %s'
);

$lang ['plugin'] ['commentcenter'] ['mail_text'] = 'Hallo %toname%,

"%fromname%" %frommail% hat einen Kommentar zu dem Beitrag geschrieben mit dem Titel "%entrytitle%"
Aber dieser braucht deine Zustimmung, bevor dieser veröffentlicht wird.

Folgendes wurde als Kommentar geschrieben:
__________________________________________
%content%
__________________________________________

Logge dich im administrativen Bereich deines FlatPress-Blogs ein und prüfe im Comment-Center den blockierten Kommentar.

Automatisch generiert von
%blogtitle%

';
?>
