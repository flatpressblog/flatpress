<?php
$lang ['admin'] ['config'] ['default'] = array(
	'head' => 'Opciones',
	'descr' => 'Personalice y configure su instalación de FlatPress.',
	'submit' => 'Guardar Cambios',

	'sysfset' => 'Información general del sistema',
	'syswarning' => '<big>¡Advertencia!</big> Estas informaciones son críticas y deben ser correctas, o FlatPress (probablemente) se negará a funcionar correctamente.',
	'blog_root' => '<strong>Path absoluto a FlatPress</strong>. Nota: ' . //
		'generalmente no tendrá que editar esto, de todos modos tenga cuidado, porque no podemos verificar si es correcto o no.',
	'www' => '<strong>Blog root</strong>. URL hacia su blog, completo con subdirectorios. <br>' . //
		'e.g.: http://www.mydomain.com/flatpress/ (trailing slash needed)',

	// ------
	'gensetts' => 'Configuración general',
	'adminname' => 'Nombre del administrador',
	'adminpassword' => 'Nueva contraseña',
	'adminpasswordconfirm' => 'Repetir contraseña',
	'blogtitle' => 'Título del Blog',
	'blogsubtitle' => 'Subtítulo del Blog',
	'blogfooter' => 'Pie de página del Blog',
	'blogauthor' => 'Autor del Blog',
	'startpage' => 'La página de inicio de este sitio web es',
	'stdstartpage' => 'Mi blog (predeterminado)',
	'blogurl' => 'URL del Blog',
	'blogemail' => 'Email del Blog',
	'notifications' => 'Notificaciones',
	'mailnotify' => 'Habilitar notificación por correo electrónico de los comentarios',
	'blogmaxentries' => 'Número de publicaciones por página',
	'langchoice' => 'Idioma',

	'intsetts' => 'Configuración internacional',
	'utctime' => '<abbr title="Hora Universal Coordinado">UTC</abbr> la hora es',
	'timeoffset' => 'La diferencia de hora es',
	'hours' => 'hours',
	'timeformat' => 'Formato inicial para la hora',
	'dateformat' => 'Formato inicial para la fecha',
	'dateformatshort' => 'Formato inicial para la fecha (corto)',
	'output' => 'Resulta',
	'charset' => 'Conjunto de caracteres',
	'charsettip' => 'Conjunto de caracteres en el que usted escribe su blog (UTF-8 is ' . //
		'<a class="hint" href="https://wiki.flatpress.org/doc:techfaq#character_encoding" target="_blank" title="¿Qué normas de codificación de caracteres admite FlatPress?">recomendado</a>).'
);

$lang ['admin'] ['config'] ['default'] ['msgs'] = array(
	1 => 'La configuración se ha guardado correctamente.',
	2 => 'El administrador ha sido cambiado. Ahora se le cerrará la sesión.',
	-1 => 'Se produjo un error al intentar guardar la configuración.'
);

$lang ['admin'] ['config'] ['default'] ['error'] = array(
	'www' => 'El root del blog debe ser un URL válido',
	'title' => 'Debe especificar un título',
	'email' => 'El correo electrónico debe tener un formato válido',
	'maxentries' => 'No ingreso un número válido de entradas',
	'timeoffset' => '¡No ingresó una corrección de tiempo válida! Puedes usar punto flotante (ej. 2h30" => 2.5)',
	'timeformat' => 'Debe insertar el formato para la hora',
	'dateformat' => 'Debe insertar el formato para la fecha',
	'dateformatshort' => 'Debe insertar el formato para la fecha (corto)',
	'charset' => 'Debe insertar un ID de set de caracteres',
	'lang' => 'El idioma que eligió no está disponible',
	'admin' => 'El nombre del administrador sólo puede contener letras, números y 1 guión bajo.',
	'password' => 'La contraseña debe contener al menos 6 caracteres y no debe contener espacios.',
	'confirm_password' => 'Las contraseñas no coinciden.'
);
?>
