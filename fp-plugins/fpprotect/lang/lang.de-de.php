<?php
$lang ['admin'] ['config'] ['submenu'] ['fpprotect'] = 'FlatPress Protect';

$lang ['admin'] ['config'] ['fpprotect'] = array(
	'head' => 'FlatPress Protect Einstellungen',
	'desc1' => 'Hier kannst du sicherheitsrelevante Optionen deines FlatPress-Blogs ändern. ' . //
		'Der beste Schutz für deine Besucher und deinem FlatPress-Blog ist dann gegeben, wenn alle Optionen deaktiviert sind.',

	// Part for unsafe inline scripts
	'allow_unsafe_inline' => 'Unsichere Java-Scripte zulassen (Nicht empfohlen)',

	'allowUnsafeInlineDsc' => '<p>Erlaubt das Laden von unsicherem Inline-JavaScript-Code.</p>' . //
		'<p><br>Hinweis an Plugin-Entwickler: Bitte statte dein Java-Skript mit einer Nonce aus.</p>' . //
		'Ein Beispiel für PHP:
		<pre>$random_hex = RANDOM_HEX;
' . //
		'... script nonce="\' . $random_hex . \'" src=" ...' . //
		'</pre>' . //
		'Ein Beispiel für das Smarty-Template:
		<pre>' . //
		'... script nonce="{$smarty.const.RANDOM_HEX}" ...' . //
		'</pre>' . //
		'<p>So wird sichergestellt, dass der Browser des Besuchers nur Java-Skripte ausführt, welche von deinem FlatPress-Blog stammen.</p>',

	// Part for the PrettyURLs .htaccess edit-field
	'allow_htaccess_edit' => 'Erlaube das Erstellen und Editieren der .htaccess Datei.',
	'allowPrettyURLEditDsc' => 'Erlaubt den Zugriff auf das .htaccess Bearbeitungsfeld des PrettyURLs-Plugins, um die .htaccess-Datei zu erstellen oder zu ändern.',

	// Part for metadate in images after upload
	'allow_image_metadate' => 'Metadaten und ursprüngliche Bildqualität in hochgeladenen Bildern behalten.',
	'allowImageMetadataDsc' => 'Nachdem Bilder mit dem Uploader hochgeladen worden sind, bleiben die Metadaten erhalten. Dazu gehören z.B. Kamerainformationen und Geo-Koordinaten.',

	// Part for the visitor-ip in FlatPress
	'allow_visitor_ip' => 'Erlaube FlatPress, die nicht anonymisierte IP-Adresse des Besuchers zu verwenden.',
	'allowVisitorIpDsc' => 'FlatPress speichert dann unter anderem in Kommentare die nicht anonymisierte IP-Adresse. ' . //
		'Wenn du den Dienst von Akismet Antispam nutzt, erhält Akismet ebenfalls die nicht anonymisierte IP-Adresse.',

	// Part for Idle timeout for admin session
	'session_timeout_label' => 'Idle-Timeout für Admin-Session (Minuten)',
	'session_timeout_desc' => 'Minuten der Inaktivität bis die Admin-Session abläuft. Leer oder 0 bedeutet Standard 60 Minuten.',

	'submit' => 'Einstellungen speichern',
		'msgs' => array(
		1 => 'Einstellungen erfolgreich gespeichert.',
		-1 => 'Fehler beim Speichern der Einstellungen.'
	),

	// Warning message
	'warning_allowUnsafeInline' => 'Warnung: Content-Security-Policy -> Diese Richtlinie enthält "unsafe-inline", was in der script-src-Richtlinie gefährlich ist.',
	'warning_allowVisitorIp' => 'Warnung: Verwendung nicht anonymisierter IP-Adressen des Besuchers -> Vergesse nicht, die <a href="static.php?page=privacy-policy" title="statische Seite bearbeiten">Besucher deines FlatPress-Blogs</a> darauf hinzuweisen!'
);
?>
