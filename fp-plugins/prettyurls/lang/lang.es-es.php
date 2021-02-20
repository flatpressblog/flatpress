<?php
	$lang['plugin']['prettyurls']['errors'] = array (
		-2	=> 'No puedo encontrar o crear un archivo <code>.htaccess</code> en su root '.
				'directorio. Es posible que PrettyURLs no funcione correctamente, consulte el panel de configuración.'
	);
	
	$lang['admin']['plugin']['submenu']['prettyurls'] = 'Configuración de PrettyURLs';
	$lang['admin']['plugin']['prettyurls'] = array(
		'head'		=> 'Configuración de PrettyURLs',
		'htaccess'	=> '.htaccess',
		'description'=>'Este editor en bruto te permite editar tu '.
						'<code>.htaccess</code>.',
		'cantsave'	=> 'No puede editar este archivo, porque no es <strong>writable</strong>. Puede otorgar permiso de escritura o copiar y pegar en un archivo y luego cargarlo.',
		'mode'		=> 'Modo',
		'auto'		=> 'Automático',
			'autodescr'	=> 'trata de adivinar la mejor opción para mí',
		'pathinfo'	=> 'Información de path',
			'pathinfodescr' => 'e.g. /index.php/2011/01/01/hello-world/',
		'httpget'	=> 'HTTP Get',
			'httpgetdescr'=> 'e.g. /?u=/2011/01/01/hello-world/',
		'pretty'	=> 'Pretty',
			'prettydescr'=> 'e.g. /2011/01/01/hello-world/',

		'saveopt' 	=> 'Guardar la configuración',

		'submit'	=> 'Guardar .htaccess'
	);
	$lang['admin']['plugin']['prettyurls']['msgs'] = array(
		1		=> '.htaccess guardado con éxito',
		-1		=> '.htaccess no se pudo guardar (tiene permisos de escritura en <code>'. BLOG_ROOT .'</code>)?',

		2		=> 'Opciones guardadas con éxito',
		-2		=> 'Se produjo un error al intentar guardar la configuración',
	);
	
?>
