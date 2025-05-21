<?php

$lang['admin']['plugin']['submenu']['bbcode'] = 'BBCode';
$lang['admin']['plugin']['bbcode'] = array(
	'head' => 'Configuración de BBCode',
	'desc1' => 'Este Plugin permite usar <a href="http://www.phpbb.com/'.
		'phpBB/faq.php?mode=bbcode">BBCode</a> markup y proporciona '.
		'integración automática con lightbox (cuando también está habilitado).',
	
	'options' => 'Opciones',

	'editing'		=> 'Edición',
	'allow_html'		=> 'HTML en línea',
	'allow_html_long' 	=> 'Habilite el uso de HTML junto con BBCode',
	'toolbar' 		=> 'Barra de herramientas',
	'toolbar_long' 		=> 'Habilite la barra de herramientas del editor.',

	'other'			=> 'Otras opciones',
	'comments' 		=> 'Comments',
	'comments_long' 	=> 'Permitir BBCode en los comentarios',
	'urlmaxlen' 		=> 'Largo máximo del URL',
	'urlmaxlen_long_pre' 	=> 'Acorte los URL más largos de ',
	'urlmaxlen_long_post'	=>' caracteres.',
	'submit' 		=> 'Save configuration',
	'msgs' => array(
			1 => 'BBCode configuration successful saved.',
			-1 => 'Configuración de BBCode no guardada.'
	),

	'editor' => array(
		'formatting'     => 'Formateo',
		'textarea'       => 'Área de texto: ',
		'expand'         => 'Ampliar',
		'expandtitle'    => 'Ampliar la altura del área de texto',
		'reduce'         => 'Reducir',
		'reducetitle'    => 'Reducir la altura del área de texto',
		// note: accesskeys are not internationalized...
		// btw. why not :-D
		'bold'           => 'B',
		'boldtitle'      => 'Negrita',
		'italic'         => 'I',
		'italictitle'    => 'Itálica',
		'underline'      => 'U',
		'underlinetitle' => 'Subrayada',
		'quote'          => 'Quote',
		'quotetitle'     => 'Citar',
		'code'           => 'Code',
		'codetitle'      => 'Código',
		'help'           => 'Ayuda de BBCode',
		// currently not used
		'status'         => 'Barra de estado',
		'statusbar'      => 'Modo normal. presiona &lt;Esc&gt; para cambiar el modo de edición.'
	)
);

?>
