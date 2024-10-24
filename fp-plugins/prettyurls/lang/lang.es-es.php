<?php
$lang ['plugin'] ['prettyurls'] ['errors'] = array (
	-2 => 'No puedo encontrar o crear un archivo <code>.htaccess</code> en su root ' . //
		'directorio. Es posible que PrettyURLs no funcione correctamente, consulte el panel de configuración.'
);

$lang ['admin'] ['plugin'] ['submenu'] ['prettyurls'] = 'Configuración de PrettyURLs';
$lang ['admin'] ['plugin'] ['prettyurls'] = array(
	'head' => 'Configuración de PrettyURLs',
	'description1' => 'Aquí puedes transformar las URLs estándar de FlatPress en URLs bonitas y SEO-friendly.',
	'fpprotect_is_on' => 'El plugin PrettyURLs requiere un archivo .htaccess. ' . //
		'Para crear o modificar este archivo, active la opción en el <a href="admin.php?p=config&action=fpprotect" title="ir a FlatPress Protect Plugin">plugin FlatPress Protect</a>. ',
	'fpprotect_is_off' => 'El plugin FlatPress Protect protege el archivo .htaccess de cambios involuntarios. ' . //
		'Puede activar el plugin <a href="admin.php?p=plugin&action=default" title="Vaya a la administración del plugin">aquí</a>!',
	'nginx' => 'PrettyURLs con NGINX',
	'wiki_nginx' => 'https://wiki.flatpress.org/res:plugins:prettyurls#nginx',
	'htaccess' => '.htaccess',
	'description2' => 'Este editor le permite editar directamente el <code>.htaccess</code> necesario para el plugin PrettyUrls.<br>' . //
		'<strong>Nota:</strong> Sólo los servidores web compatibles con NCSA, como Apache, reconocen el concepto de archivos .htaccess. ' . //
		'El software de tu servidor lo es: <strong>' . $_SERVER ['SERVER_SOFTWARE'] . '</strong>',
	'cantsave' => 'No puede editar este archivo, porque no es <strong>writable</strong>. Puede otorgar permiso de escritura o copiar y pegar en un archivo y luego cargarlo.',
	'mode' => 'Modo',
	'auto' => 'Automático',
	'autodescr' => 'trata de adivinar la mejor opción para mí',
	'pathinfo' => 'Información de path',
	'pathinfodescr' => 'e.g. /index.php/2024/01/01/hello-world/',
	'httpget' => 'HTTP Get',
	'httpgetdescr' => 'e.g. /?u=/2024/01/01/hello-world/',
	'pretty' => 'Pretty',
	'prettydescr' => 'e.g. /2024/01/01/hello-world/',

	'saveopt' => 'Guardar la configuración',

	'location' => '<strong>Lugar de almacenamiento:</strong> ' . ABS_PATH . '',
	'submit' => 'Guardar .htaccess'
);

$lang ['admin'] ['plugin'] ['prettyurls'] ['msgs'] = array(
	1 => '.htaccess guardado con éxito',
	-1 => '.htaccess no se pudo guardar (tiene permisos de escritura en <code>' . BLOG_ROOT . '</code>)?',

	2 => 'Opciones guardadas con éxito',
	-2 => 'Se produjo un error al intentar guardar la configuración'
);
?>
