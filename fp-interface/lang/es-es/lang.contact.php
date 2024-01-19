<?php
$baseurl = BLOG_BASEURL;

$lang ['contact'] = array(
	'head' => 'Contáctanos',
	'descr' => 'Complete el siguiente formulario para enviarnos sus comentarios. Agregue su correo electrónico si desea ser respondido.',
	'fieldset1' => 'Datos del usuario',
	'name' => 'Nombre (*)',
	'email' => 'Email:',
	'www' => 'Web:',
	'cookie' => 'Recuérdame',
	'fieldset2' => 'Tu mensaje',
	'comment' => 'Mensaje (*):',
	'fieldset3' => 'Enviar',
	'submit' => 'Enviar',
	'reset' => 'Reiniciar',
	'loggedin' => 'Ha iniciado sesión 😉. <a href="' . $baseurl . 'login.php?do=logout">Cerrar sesión</a> o al <a href="' . $baseurl . 'admin.php">área de administración</a>.'
);

$lang ['contact'] ['notification'] = array(
	'name' => 'Nombre:',
	'email' => 'Correo electrónico:',
	'www' => 'Web:',
	'content' => 'Mensaje:',
	'subject' => 'Contacto enviado a través de '
);

$lang['contact'] ['error'] = array(
	'name' => 'Debes ingresar un nombre',
	'email' => 'Debes ingresar un correo electrónico válido',
	'www' => 'Debes ingresar una URL válida',
	'content' => 'Debes ingresar un mensaje'
);

$lang ['contact'] ['msgs'] = array(
	1 => 'El mensaje se envió con éxito',
	-1 => 'No se pudo enviar el mensaje'
);
?>