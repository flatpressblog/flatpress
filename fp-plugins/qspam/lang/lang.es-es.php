<?php

$lang['plugin']['qspam'] = array(
	'error' => 'ERROR: El comentario contenía palabras prohibidas.'
);

$lang['admin']['plugin']['submenu']['qspam'] = 'QuickSpamFilter';
$lang['admin']['plugin']['qspam'] = array(
	'head' 	=> 'Configuración de QuickSpam',
	'desc1' => 'no permita comentarios que contengan estas palabras (escriba uno por línea):',
	'desc2' => '<strong>Warning:</strong> No se permitirá un comentario incluso cuando una palabra sea parte de otra. 
	
	(Por Ej. "old" también coincide con "b<em>old</em>")',
	'options' 	=> 'Otras opciones',
	'desc3'		=> 'Mala cantidad de palabras',
	'desc3pre' 	=> 'Bloquear comentarios que contengan más de ',
	'desc3post' 	=> ' palabra(s) mala(s).',
	'submit' 	=> 'Save configuration',
	'msgs' => array(
		1 => 'Las malas palabras se guardaron con éxito.',
		-1 => 'Las malas palabras no se guardaron.'
	)
);

?>
