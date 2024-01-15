<?php
$lang ['contact'] = array(
	'head' => 'Kontakt',
	'descr' => 'Felterne Navn og Besked er obligatoriske. For at kunne svare dig, har jeg brug for din e-mailadresse.',
	'fieldset1' => 'Dine oplysninger',
	'name' => 'Navn (nødvendigt)',
	'email' => 'E-Mail Adresse:',
	'www' => 'Hjemmeside (valgfri):',
	'cookie' => 'Husk datoer til næste gang',
	'fieldset2' => 'Din besked',
	'comment' => 'Besked (påkrævet):',
	'fieldset3' => 'Send',
	'submit' => 'Send',
	'reset' => 'Nulstil',
	'loggedin' => 'Du er logget ind 😉. <a href="' . $baseurl . 'login.php?do=logout">Log ud</a> eller til <a href="' . $baseurl . 'admin.php">administrationsområdet</a>.'
);

$lang ['contact'] ['notification'] = array(
	'name' => 'Navn:',
	'email' => 'E-Mail Adresse:',
	'www' => 'Hjemmeside:',
	'content' => 'Besked:',
	'subject' => 'Kontakt via '
);

$lang ['contact'] ['error'] = array(
	'name' => 'Indtast venligst et navn',
	'email' => 'Indtast venligst en gyldig e-mailadresse',
	'www' => 'Indtast venligst en gyldig URL',
	'content' => 'Skriv venligst en besked'
);

$lang ['contact'] ['msgs'] = array(
	1 => 'Beskeden blev sendt med succes',
	-1 => 'Fejl: Beskeden kunne ikke sendes'
);
?>