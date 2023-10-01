<?php
	$lang['admin']['panel']['maintain'] = 'Vzdrževanje';

	$lang['admin']['maintain']['default'] = array(
		'head'		=> 'Vzdrževanje',
		'descr'		=> 'Pridite sem, če mislite, da se je kaj zapletlo, '.
					'in morda tukaj boste našli rešitev.
					Vendar to morda ne bo delovalo.',
		'opt0'		=> '&laquo; Nazaj na glavni meni',
		'opt1'		=> 'Ponovno zgradi indeks',
		'opt2'		=> 'Počisti predpomnilnik teme in predlog',
		'opt3'		=> 'Obnovi dovoljenja datotek',
		'opt4'		=> 'Prikaži informacije o PHP',
		'opt5'		=> 'Preveri posodobitve',

		'chmod_info'	=> "Naslednja dovoljenja za datoteke <strong>ni mogoče</strong>
					povrniti na 0777; verjetno lastnik datoteke ni isti kot
					spletne strežnik. Običajno lahko to obvestilo prezrete.",
	);
	
	$lang['admin']['maintain']['default']['msgs'] = array(
		1		=> 'Operacija je končana'
	);
	
	$lang['admin']['maintain']['updates'] = array(
		'head'	=> 'Posodobitve',
		'list'	=> '<ul>
		<li>Imate različico FlatPress <big>%s</big></li>
		<li>Zadnja stabilna različica za FlatPress je <big><a href="%s">%s</a></big></li>
		<li>Zadnja nestabilna različica za FlatPress je <big><a href="%s">%s</a></big></li>
		</ul>',
		'notice'=>'Opomba:'
	);
	
	$lang['admin']['maintain']['updates']['msgs'] = array(
		1		=> 'Na voljo so posodobitve!',
		2		=> 'Ste že posodobljeni',
		-1		=> 'Ni mogoče pridobiti posodobitev'
	);
?>
