<?php

$lang ['admin'] ['plugin'] ['submenu'] ['bbcode'] = 'BBCode';
$lang ['admin'] ['plugin'] ['bbcode'] = array(
	'head' => 'BBCode Konfiguration',
	'desc1' => 'Dette plugin tillader <a href="https://wiki.flatpress.org/'.
		'doc:techfaq#bbcode" class="hint" target="_blank">BBCode</a> markup til at blive brugt.',
	
	'options' => 'Valgmuligheder',

	'editing' => 'Rediger indstillinger',
	'allow_html' => 'Inline HTML',
	'allow_html_long' => 'Tillad brug af HTML-kode og BBCode',
	'toolbar' => 'Værktøjslinje',
	'toolbar_long' => 'Aktivér Editor-værktøjslinjen.',

	'other'	=> 'Flere muligheder',
	'comments' => 'Kommentarer',
	'comments_long' => 'Tillad BBCode i kommentarerne',
	'urlmaxlen' => 'Maksimal længde af URL-visningen',
	'urlmaxlen_long_pre' => 'Korte URL\'er, der har mere end ',
	'urlmaxlen_long_post' =>' tegn.',
	'submit' => 'Gem konfiguration',
	'msgs' => array(
		1 => 'BBCode-konfiguration gemt med succes.',
		-1 => 'Bemærk: BBCode-konfigurationen blev ikke gemt.'
	),

	'editor' => array(
		'formatting' => 'Formatering',
		'textarea' => 'Indtastningsfelt: ',
		'expand' => 'Forstør',
		'expandtitle' => 'Forstør inputfeltet',
		'reduce' => 'Reducer',
		'reducetitle' => 'Reducer størrelsen på inputfeltet',
		// note: accesskeys are not internationalized...
		// btw. why not :-D
		'bold' => 'B',
		'boldtitle' => 'Fedt',
		'italic' => 'I',
		'italictitle' => 'Kursiv',
		'underline' => 'U',
		'underlinetitle' => 'Understregning',
		'quote' => 'Quote',
		'quotetitle' => 'Kommentar/citation',
		'code' => 'Code',
		'codetitle' => 'Eksempel på kode',
		'help' => 'Hjælp til BBCode',
		'file' => 'Fil: ',
		'image' => 'Billede: ',
		'selection' => '-- Udvælgelse --',
		// currently not used
		'status' => 'Statusbjælke',
		'statusbar' => 'Normal tilstand. Tryk på &lt;Esc&gt; for at skifte til redigeringstilstand.'
	)
);

$lang ['plugin'] ['bbcode'] = array (
		'go_to' => 'Gå til',
		// language tag for Facebook Video
		'langtag' => 'da_DK'
);

?>
