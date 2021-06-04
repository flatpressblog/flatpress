<?php

$lang['admin']['plugin']['submenu']['bbcode'] = 'BBCode';
$lang['admin']['plugin']['bbcode'] = array(
	'head' => 'BBCode Configuratie',
	'desc1' => 'Deze plugin staat gebruik toe <a href="http://www.phpbb.com/'.
		'phpBB/faq.php?mode=bbcode">BBCode</a> voor opmaak en biedt '.
		'automatische integratie met lightbox (wanneer ook ingeschakeld).',
	
	'options' => 'Opties',

	'editing'	=> 'Bewerken',
	'allow_html'	=> 'Inline HTML',
	'allow_html_long' => 'Schakel het gebruik van HTML samen met BBCode in',
	'toolbar' 	=> 'Werkbalk',
	'toolbar_long' 	=> 'Schakel de editorwerkbalk in.',

	'other'		=> 'Andere opties',
	'comments' 	=> 'Opmerkingen',
	'comments_long' => 'Sta BBCode toe in opmerkingen',
	'urlmaxlen' 	=> 'URL max lengte',
	'urlmaxlen_long_pre' => 'Verkort URL langer dan ',
	'urlmaxlen_long_post'=>' karakters.',
	'submit' 	=> 'Bewaar de configuratie',
	'msgs' => array(
		1 => 'BBCode-configuratie succesvol opgeslagen.',
		-1 => 'BBCode-configuratie niet opgeslagen.'
	),

	'editor' => array(
		'formatting'     => 'Formatteren',
		'textarea'       => 'Textgebied: ',
		'expand'         => 'Uitbreiden',
		'expandtitle'    => 'Breid hoogte Textgebied uit',
		'reduce'         => 'Verminder',
		'reducetitle'    => 'Verminder hoogte Textgebied',
		// note: accesskeys are not internationalized...
		// btw. why not :-D
		'bold'           => 'B',
		'boldtitle'      => 'Bold',
		'italic'         => 'I',
		'italictitle'    => 'Italic',
		'underline'      => 'U',
		'underlinetitle' => 'Underlined',
		'quote'          => 'Quote',
		'quotetitle'     => 'Quote',
		'code'           => 'Code',
		'codetitle'      => 'Code',
		'help'           => 'BBCode Help',
		// currently not used
		'status'         => 'Statusbalk',
		'statusbar'      => 'Normale modus. Druk &lt;Esc&gt; om van bewerkingsmodus te wisselen.'
	)
);

?>
