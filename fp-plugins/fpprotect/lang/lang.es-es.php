<?php
$lang ['admin'] ['config'] ['submenu'] ['fpprotect'] = 'FlatPress Protect';

$lang ['admin'] ['config'] ['fpprotect'] = array(
	'head' => 'Configuración de FlatPress Protect',
	'desc1' => 'Aquí puede cambiar las opciones relacionadas con la seguridad de su blog FlatPress. ' . //
		'La mejor protección para sus visitantes y su blog FlatPress es cuando todas las opciones están desactivadas.',

	// Part for unsafe inline scripts
	'allow_unsafe_inline' => 'Permitir scripts Java inseguros (No recomendado)',

	'allowUnsafeInlineDsc' => '<p>Permite la carga de código JavaScript en línea inseguro.</p>' . //
		'<p><br>Nota para los desarrolladores de plugins: añada un nonce a su script Java.</p>' . //
		'Un ejemplo para PHP:
		<pre>$random_hex = RANDOM_HEX;
' . //
		'... script nonce="\' . $random_hex . \'" src=" ...' . //
		'</pre>' . //
		'Un ejemplo para la plantilla Smarty:
		<pre>' . //
		'... script nonce="{$smarty.const.RANDOM_HEX}" ...' . //
		'</pre>' . //
		'<p>Esto garantiza que el navegador del visitante sólo ejecute scripts Java que se originen en su blog FlatPress.</p>',

	// Part for the PrettyURLs .htaccess edit-field
	'allow_htaccess_edit' => 'Permitir la creación y edición del archivo .htaccess.',
	'allowPrettyURLEditDsc' => 'Permite acceder al campo de edición .htaccess del plugin PrettyURLs para crear o modificar el archivo .htaccess.',

	// Part for metadate in images after upload
	'allow_image_metadate' => 'Conservar los metadatos y la calidad de imagen original en las imágenes cargadas.',
	'allowImageMetadataDsc' => 'Una vez cargadas las imágenes con el cargador, se conservan los metadatos. Esto incluye la información de la cámara y las coordenadas geográficas, por ejemplo.',

	// Part for the visitor-ip in FlatPress
	'allow_visitor_ip' => 'Permitir que FlatPress utilice la dirección IP no anonimizada del visitante.',
	'allowVisitorIpDsc' => 'FlatPress guardará la dirección IP no anonimizada en los comentarios, entre otras cosas. ' . //
		'Si utiliza el servicio Akismet Antispam, Akismet también recibirá la dirección IP no anonimizada.',

	// Part for Idle timeout for admin session
	'session_timeout_label' => 'Tiempo de espera de inactividad para la sesión de administrador (minutos)',
	'session_timeout_desc' => 'Minutos de inactividad hasta que expira la sesión de administrador. Si se deja en blanco o se establece en 0, el valor predeterminado es 60 minutos.',

	'submit' => 'Guardar configuración',
		'msgs' => array(
		1 => 'Configuración guardada correctamente.',
		-1 => 'Error al guardar la configuración.'
	),

	// Warning message
	'warning_allowUnsafeInline' => 'Advertencia: Content-Security-Policy -> Esta política contiene "unsafe-inline", que es peligroso en la política script-src.',
	'warning_allowVisitorIp' => 'Advertencia: Utilización de direcciones IP de visitantes no anonimizadas -> ¡No olvide informar de ello a <a href="static.php?page=privacy-policy" title="edit static page">los visitantes de su blog FlatPress</a>!'
);
?>
