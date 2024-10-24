<?php
$lang ['admin'] ['config'] ['submenu'] ['fpprotect'] = 'FlatPress Protect';

$lang ['admin'] ['config'] ['fpprotect'] = array(
	'head' => 'Configuración de FlatPress Protect',
	'desc1' => 'Aquí puede cambiar las opciones relacionadas con la seguridad de su blog FlatPress.',

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

	'submit' => 'Guardar configuración',
		'msgs' => array(
		1 => 'Configuración guardada correctamente.',
		-1 => 'Error al guardar la configuración.'
	),

	// Warning message for unsafe inline scripts
	'warning_allowUnsafeInline' => 'Advertencia: Content-Security-Policy -> Esta política contiene "unsafe-inline", que es peligroso en la política script-src.'
);
?>
