<?php
$lang ['admin'] ['plugin'] ['submenu'] ['bbcode'] = 'BBCode';
$lang ['admin'] ['plugin'] ['bbcode'] = array(
	'head' => 'BBCode Konfiguration',
	'desc1' => 'Dieses Plugin erlaubt <a href="https://wiki.flatpress.org/' . //
		'doc:techfaq#bbcode" class="hint" target="_blank">BBCode</a> Markup zu benutzen.',

	'options' => 'Optionen',

	'editing' => 'Einstellungen bearbeiten',
	'allow_html' => 'Inline HTML',
	'allow_html_long' => 'Benutzung von HTML Code und BBCode erlauben',
	'toolbar' => 'Toolbar',
	'toolbar_long' => 'Editor Toolbar aktivieren.',

	'other'	=> 'Weitere Optionen',
	'comments' => 'Kommentare',
	'comments_long' => 'erlaube BBCode in den Kommentaren',
	'urlmaxlen' => 'Maximale Länge der URL Anzeige',
	'urlmaxlen_long_pre' => 'Kürze URLs die mehr als ',
	'urlmaxlen_long_post' =>' Zeichen haben.',
	'submit' => 'Konfiguration speichern',
	'msgs' => array(
		1 => 'BBCode Konfiguration erfolgreich gespeichert.',
		-1 => 'Hinweis: BBCode Konfiguration wurde nicht gespeichert.'
	),

	'editor' => array(
		'formatting' => 'Formatierung',
		'textarea' => 'Eingabefeld: ',
		'expand' => 'Vergrößern',
		'expandtitle' => 'Das Eingabefeld vergrößern',
		'reduce' => 'Verkleinern',
		'reducetitle' => 'Das Eingabefeld verkleinern',
		// note: accesskeys are not internationalized...
		// btw. why not :-D
		'bold' => 'B',
		'boldtitle' => 'Fett',
		'italic' => 'I',
		'italictitle' => 'Kursiv',
		'underline' => 'U',
		'underlinetitle' => 'Unterstreichen',
		'quote' => 'Quote',
		'quotetitle' => 'Bemerkung/Zitat',
		'code' => 'Code',
		'codetitle' => 'Code Beispiel',
		'help' => 'BBCode Hilfe',
		'file' => 'Datei: ',
		'image' => 'Bild: ',
		'selection' => '-- Auswahl --',
		// currently not used
		'status' => 'Status bar',
		'statusbar' => 'Normal mode. Press &lt;Esc&gt; to switch editing mode.'
	)
);

$lang ['plugin'] ['bbcode'] = array (
	'go_to' => 'Gehe zu',
	'langtag' => 'de_DE' // language tag for Facebook Video
);
?>
