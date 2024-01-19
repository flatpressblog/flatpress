<?php
$baseurl = BLOG_BASEURL;

$lang ['contact'] = array(
	'head' => 'Neem contact op',
	'descr' => 'Vul het formulier hieronder in. 
	Om een antwoord te krijgen is een e-mail adres nodig.
	(*) verplicht in te vullen velden',
	'fieldset1' => 'Gegevens gebruiker',
	'name' => 'Naam (*)',
	'email' => 'Email:',
	'www' => 'Web:',
	'cookie' => 'Stuur herinnering',
	'fieldset2' => 'Bericht',
	'comment' => 'Bericht (*):',
	'fieldset3' => 'Stuur',
	'submit' => 'Stuur',
	'reset' => 'Reset',
	'loggedin' => 'U bent ingelogd 😉. <a href="' . $baseurl . 'login.php?do=logout">Uitloggen</a> of naar het <a href="' . $baseurl . 'admin.php">administratiegedeelte</a>.'
);

$lang ['contact'] ['notification'] = array(
	'name' => 'Naam:',
	'email' => 'Email:',
	'www' => 'Web:',
	'content' => 'Boodschap:',
	'subject' => 'Contact verzonden via '
);

$lang ['contact'] ['error'] = array(
	'name' => 'Er dient een naam ingevuld te worden',
	'email' => 'Geen geldig e-mail adres',
	'www' => 'Geen geldige URL ',
	'content' => 'Het bericht mag niet blanko zijn'
);

$lang ['contact'] ['msgs'] = array(
	1 => 'Bericht is succesvol verstuurd',
	-1 => 'Bericht kon niet verstuurd worden'
);
?>