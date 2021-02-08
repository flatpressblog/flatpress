<?php
	
	$lang['admin']['panel']['maintain'] = 'Mantención';

	$lang['admin']['maintain']['default'] = array(
		'head'		=> 'Mantención',
		'descr'		=> 'Consulta esta sección cuando creas que algo se ha estropeado '.
					'y quizás encuentres aquí una solución.
					Sin embargo, esto podría no funcionar.',
		'opt0'		=> '&laquo; Regresar al menú principal',
		'opt1'		=> 'Reconstruir índice',
		'opt2'		=> 'Vaciar el caché de plantillas y temas',
		'opt3'		=> 'Restaurar permisos de archivos',
		'opt4'		=> 'Mostrar información sobre PHP',
		'opt5'		=> 'Buscar actualizaciones',

		'chmod_info'	=> "Los siguientes permisos de archivo <strong>no pudieron</strong>
					ser reiniciados a 0777; probablemente el propietario del archivo no sea el mismo que el
					del servidor web. Por lo general, puede ignorar este aviso.",
		
	);
	
	$lang['admin']['maintain']['default']['msgs'] = array(
		1		=> 'Operación completada'
	);
	
	$lang['admin']['maintain']['updates'] = array(
		'head'	=> 'Actualizaciones',
		'list'	=> '<ul>
		<li>Usted tiene la versión FlatPress <big>%s</big></li>
		<li>La última versión estable de FlatPress es <big><a href="%s">%s</a></big></li>
		<li>La última versión inestable de FlatPress es <big><a href="%s">%s</a></big></li>
		</ul>',
		'notice'=>'Aviso:'
		
	);
	
	
	
	$lang['admin']['maintain']['updates']['msgs'] = array(
		1		=> '¡Hay actualizaciones disponibles!',
		2		=> 'Su sistema ya está actualizado',
		-1		=> 'No se encontraron actualizaciones'
	);

?>
