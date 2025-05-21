<?php
	$lang['plugin']['akismet']['errors'] = array (
		-1	=> 'No se estableció la clave de API. Abra el Plugin para configurar su clave de API. Registrarse en <a href="http://wordpress.com">Wordpress.com</a> para conseguir uno'
	);
	
	$lang['admin']['plugin']['submenu']['akismet'] = 'Configuración de Akismet';
	
	$lang['admin']['plugin']['akismet'] = array(
		'head'		=> 'Configuración de Akismet',
		'description'=>'Para muchas personas, <a href="http://akismet.com/">Akismet</a> reducirá en gran medida '
					 .'o incluso eliminar por completo el spam de comentarios y trackback que recibe en su sitio. '
					 .'Si aún no tiene una cuenta de WordPress.com, puede obtener una en '.
					 '<a href="http://wordpress.com/api-keys/">WordPress.com</a>.',
		'apikey'	=> 'WordPress.com API Key',
		'whatis'	=> '(<a href="http://faq.wordpress.com/2005/10/19/api-key/">¿Que es esto?</a>)',
		'submit'	=> 'Guardar clave de API'
	);
	$lang['admin']['plugin']['akismet']['msgs'] = array(
		1		=> 'Clave de API guardada',
		-1		=> 'La clave de API no es válida'
	);
	
?>