<?php

$lang['admin']['plugin']['submenu']['bbcode'] = 'BBCode';
$lang['admin']['plugin']['bbcode'] = array(
	'head' => 'Configurazione BBCode',
	'desc1' => 'Questo plugin ti consente di usare <a href="http://www.phpbb.com/'.
		'phpBB/faq.php?mode=bbcode">BBCode</a> come markup e fornisce '.
		'un\'integrazione automatica con lightbox (quando è abilitato).',
	
	'options' => 'Opzioni',

	'editing'	=> 'Modifica',
	'allow_html'=> 'Inline HTML',
	'allow_html_long' => 'Consente di usare l\'HTML insieme a BBCode',
	'toolbar' => 'Barra strumenti',
	'toolbar_long' => 'Abilita la barra strumenti dell\'editor.',

	'other'	=>	'Altre opzioni',
	'comments' => 'Commenti',
	'comments_long' => 'Consenti di usare BBCode nei commenti',
	'urlmaxlen' => 'Lungehzza massima degli URL',
	'urlmaxlen_long_pre' => 'Accorcia gli URL più lunghi di ',
	'urlmaxlen_long_post'=>' caratteri.',
	'submit' => 'Salva la configurazione',
	'msgs' => array(
		1 => 'La configurazione di BBCode è stata salvata con successo.',
		-1 => 'La configurazione di BBCode non è stata salvata.'
	),

	'editor' => array(
		'formatting'     => 'Formattazione',
		'textarea'       => 'Casella di testo: ',
		'expand'         => 'Espandi',
		'expandtitle'    => 'Espandi l\'altezza della casella di testo',
		'reduce'         => 'Riduci',
		'reducetitle'    => 'Riduci l\'altezza della casella di testo',
		// note: accesskeys are not internationalized...
		// btw. why not :-D
		'bold'           => 'G',
		'boldtitle'      => 'Grassetto',
		'italic'         => 'C',
		'italictitle'    => 'Corsivo',
		'underline'      => 'S',
		'underlinetitle' => 'Sottolineato',
		'quote'          => 'Cita',
		'quotetitle'     => 'Citazione',
		'code'           => 'Codice',
		'codetitle'      => 'Codice',
		'help'           => 'Guida  di BBCode',
		// currently not used
		'status'         => 'Barra di stato',
		'statusbar'      => 'Modalità normale. Premi &lt;Esc&gt; per passare da una modalità all\'altra.'
	)
);

?>
