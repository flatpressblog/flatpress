<?php

	$lang['admin']['widgets']['submenu']['default'] = 'Administrar widgets';
	$lang['admin']['widgets']['submenu']['raw'] 	= 'Administrar widgets (raw)';

	/* default action */
	
	$lang['admin']['widgets']['default'] = array(
		'head'		=> 'Administrar widgets (<em>experimental</em>)',
		
		'descr'		=> 	'A <a class="hint" '.
						'href="http://wiki.flatpress.org/doc:widgets" title="Que es un Widget?">'.
						'Widget</a> es un componente dinámico que puede mostrar datos e interactuar con el usuario.
						Mientras <strong>Temas</strong> están destinados a cambiar el aspecto de tu blog, Widgets 
						<strong>Amplían</strong> apariencia y funcionalidades.</p>

						<p>Widgets pueden ser puestos en areas especiales de su tema llamado el 
						<strong>WidgetSets</strong>. El número y el nombre de los WidgetSets pueden variar según el
								tema que elija.</p>

						<p>FlatPress viene con varios widgets: hay widgets para ayudar con el inicio de sesión, para
							mostrar un cuadro de búsqueda, etc.</p>
						
						<p>Cada Widget está definido por un <a class="hint" '.
						'href="http://wiki.flatpress.org/doc:plugins" title="What is a Widget?">plugin</a>.',
						
		'availwdgs'	=> 'Widgets Disponibles',
		'trashcan'	=> 'Mover aquí para borrar',
		
		'themewdgs' 	=> 'Widgetsets para este tema',
		'themewdgsdescr' => 'El tema que ha seleccionado le permite tener los siguientes conjuntos de widgets',
		'oldwdgs'	=> 'Otros widgetsets',
		'oldwdgsdescr' 	=>'Los siguientes set de widgets parecen no pertenecer a ninguno de los '.
						'widgetsets listados arriba. Pueden ser restantes de otro tema.',
		
		'submit'	=> 'Guardar Cambios',

	);
	
	$lang['admin']['widgets']['default']['stdsets'] = array(
		'top'		=> 'Barra superior',
		'bottom'	=> 'Barra inferior',
		'left'		=> 'Barra izquierda',
		'right'		=> 'Barra derecha',
	);
	
	$lang['admin']['widgets']['default']['msgs'] = array(
		1	=> 'Configuración guardada',
		-1	=> 'Se produjo un error al intentar guardar. Vuelve a intentarlo.',
	);


	
	/* "raw" panel */	
	
	$lang['admin']['widgets']['raw'] = array(
		'head'		=> 'Administrar widgets (<em>editor raw</em>)',
		'descr'		=> 'A <a class="hint" '.
						'href="http://wiki.flatpress.org/doc:plugins" title="Que es un Widget?">'.
						'Widget</a> is a visual element of a <a class="hint" '.
						'href="http://wiki.flatpress.org/doc:plugins" title="Que es un plugin?">'.
						'Plugin</a> que puede poner en algunas áreas especiales (los <em>widgetsets</em>) en las páginas de su blog. </p>'.
						'<p>Este es el editor <strong>raw</strong> ; algunos usuarios avanzados o personas que '.
						'no deseen JavaScript tal vez lo prefieran',
						
		'fset1'		=> 'Editor',
		'fset2'		=> 'Aplicar cambios',
		'submit'	=> 'Aplicar',

	);
	
	
	$lang['admin']['widgets']['raw']['msgs'] = array(
		1	=> 'Configuración guardada',
		-1	=> 'Se produjo un error al intentar guardar. Esto puede suceder por varias razones: tal vez su archivo contenga errores de sintaxis.',
	);

		

	/* system errors */
		
	$lang['admin']['widgets']['errors'] = array(
		'generic'	=> 'El widget llamado <strong>%s</strong> no está registrado y sera omitido. '.
 				'Está el plugin habilitado en el <a href="admin.php?p=plugin"> panel de plugin</a>?'

	);
	
?>
