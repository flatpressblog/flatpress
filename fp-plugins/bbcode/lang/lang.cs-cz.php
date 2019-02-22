<?php

$lang['admin']['plugin']['submenu']['bbcode'] = 'BBCode';
$lang['admin']['plugin']['bbcode'] = array(
	'head' => 'Nastavení BBCode',
	'desc1' => 'Tento plugin umožňuje používat <a href="http://www.phpbb.com/'.
		'phpBB/faq.php?mode=bbcode">BBCode</a> na vašem blogu. ',
	
	'options' => 'Nastavení',

	'editing'	=> 'Úprava',
	'allow_html'=> 'Inline HTML',
	'allow_html_long' => 'Umožnit používání HTML tagu současně s BBCode',
	'toolbar' => 'Lišta s nástroji',
	'toolbar_long' => 'Zapnout upravování pomocí lišty nástrojů.',

	'other'	=>	'Ostatní možnosti',
	'comments' => 'Komentáře',
	'comments_long' => 'Povolit BBCode v komentářích',
	'urlmaxlen' => 'Maximální délka URL',
	'urlmaxlen_long_pre' => 'Zkrátit URL delší jako ',
	'urlmaxlen_long_post'=>' znaků.',
	'submit' => 'Uložit nastavení',
	'msgs' => array(
		1 => 'BBCode configuration successful saved.',
		-1 => 'BBCode configuration not saved.'
	),

	'editor' => array(
		'formatting'     => 'Formátování',
		'textarea'       => 'Textové pole: ',
		'expand'         => 'Zvětšit',
		'expandtitle'    => 'Zvětšit výšku textového pole',
		'reduce'         => 'Zmenšit',
		'reducetitle'    => 'Zmenšit výšku textového pole',
		// note: accesskeys are not internationalized...
		// btw. why not :-D
		'bold'           => 'B',
		'boldtitle'      => 'Tučné',
		'italic'         => 'I',
		'italictitle'    => 'Kurzíva',
		'underline'      => 'U',
		'underlinetitle' => 'Podtržené',
		'quote'          => 'Citovat',
		'quotetitle'     => 'Citace',
		'code'           => 'Kód',
		'codetitle'      => 'Kód',
		'help'           => 'BBCode Pomoc',
		// currently not used
		'status'         => 'Status bar',
		'statusbar'      => 'Normalní mód. Stiskni &lt;Esc&gt; pro přepnutí módu.'
	)
);

?>
