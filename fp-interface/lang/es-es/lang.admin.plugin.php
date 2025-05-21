<?php

	$lang['admin']['plugin']['submenu'] = array (
		'default'		=> 'Administrar Plugins'
	);
	
	/* main plugin panel */

	$lang['admin']['plugin']['default'] = array(
	
		'head'		=> 'Administrar Plugins',
		'enable'	=> 'Habilitar',
		'disable'	=> 'Inhabilitar',
		'descr'		=> 'A <a class="hint" '.
						'href="http://wiki.flatpress.org/doc:plugins" title="Que es un plugin?">'.
						'Plugin</a> es un componente que puede ampliar las capacidades de FlatPress.</p>'.
						'<p>Puede instalar plugins subiendolos a su <code>fp-plugins/</code> '.
						'directorio.</p>'.
						'<p>Este panel le permite habilitar y deshabilitar Plugins',
		'name'		=> 'Nombre',
		'description'	=> 'Descripción',
		'author'	=> 'Autor',
		'version'	=> 'Versión',
		'action'	=> 'Acción',
	);
	
	$lang['admin']['plugin']['default']['msgs'] = array(
		1	=> 'Config guardada',
		-1	=> 'Se produjo un error al intentar guardar. Esto puede suceder por varias razones: tal vez su archivo contenga errores de sintaxis.',
	);
	
	/* system errors */
	
	$lang['admin']['plugin']['errors'] = array(
		'head'		=> 'Se encontraron los siguientes errores al cargar Plugin:',
		'notfound'	=> 'No se encontró el Plugin. Omitido.',
		'generic'	=> 'Numero de error %d',
	);
	
?>
