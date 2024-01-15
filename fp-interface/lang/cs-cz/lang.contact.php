<?php
$lang ['contact'] = array(
	'head' => 'Kontaktujte nás',
	'descr' => 'Vyplňte prosím formulář (níže) pro zaslání dotazu. Napište email, pokud chcete, aby Vám přišla odpověď.',
	'fieldset1' => 'Údaje o uživateli',
	'name' => 'Jméno (*)',
	'email' => 'Email:',
	'www' => 'Web:',
	'cookie' => 'Pamatovat',
	'fieldset2' => 'Vaše zpráva',
	'comment' => 'Zpráva (*):',
	'fieldset3' => 'Poslat',
	'submit' => 'Poslat',
	'reset' => 'Resetovat',
	'loggedin' => 'Jste přihlášen 😉. <a href="' . $baseurl . 'login.php?do=logout">Odhlásit se</a> nebo na <a href="' . $baseurl . 'admin.php">administrativní oblasti</a>.'
);

$lang ['contact'] ['notification'] = array(
	'name' => 'Název:',
	'email' => 'E-mail:',
	'www' => 'Web:',
	'content' => 'Zpráva:',
	'subject' => 'Kontakt zaslaný prostřednictvím '
);

$lang ['contact'] ['error'] = array(
	'name' => 'Musíte vložit jméno',
	'email' => 'Musíte vložit správný email',
	'www' => 'Musíte vložit správné URL',
	'content' => 'Musíte vložit zprávu'
);

$lang ['contact'] ['msgs'] = array(
	1 => 'Zpráva byla úspěšně odeslána',
	-1 => 'Zpráva nemohla být odeslána'
);
?>