<?php

	$lang = array();

	$lang['main'] = array(
		
		'nextpage'	=> 'Página Siguiente &raquo;',
		'prevpage'	=> '&laquo; Página anterior',
		'entry'      	=> 'Entrada',
		'static'     	=> 'Pagina Estatica',
		'comment'    	=> 'Comentario',
		'preview'    	=> 'Editar/Vista previa',
		
		'filed_under'	=> 'Archivado bajo ',	
		
		'add_entry'  	=> 'Agregar Entrada',
		'add_comment'  	=> 'Agregar Comentario',
		'add_static'  	=> 'Agregar Pagina Estatica',
		
		'btn_edit'     	=> 'Editar',
		'btn_delete'   	=> 'Borrar',
		
		'nocomments'	=> 'Agregar un Comentario',
		'comment'	=> '1 comentario',
		'comments'	=> 'comentarios',
		
	);
	
	$lang['search'] = array(
		
		'head'		=> 'Buscar',
		'fset1'		=> 'Insertar criterios de búsqueda',
		'keywords'	=> 'Frase',
		'onlytitles'	=> 'Solo Títulos',
		'fulltext'	=> 'Text Completo',
		
		'fset2'	=> 'Date',
		'datedescr'	=> 'Puede vincular su búsqueda a una fecha específica. Puede seleccionar un año, un año y un mes o una fecha completa. '.
					'Déjelo en blanco para buscar en toda la base de datos.',
		
		'fset3' 	=> 'Buscar en categorías',
		'catdescr'	=> 'No seleccione ninguno para buscar todos',
		
		'fset4'		=> 'Empezar a buscar',
		'submit'	=> 'Buscar',
		
		'headres'	=> 'Resultados de la búsqueda',
		'descrres'	=> 'Searching for <strong>%s</strong> returned the following results:',
		'descrnores'	=> 'Buscando <strong>%s</strong> no devolvió ningún resultado.',
		
		'moreopts'	=> 'Más Opciones',
		
		
		'searchag'	=> 'Busca de nuevo',
		
	);
	
	$lang['search']['error'] = array(
	
		'keywords'	=> 'Debe especificar al menos una palabra clave'
	
	);
	
	
	
	
	
	$lang['entry'] = array();
	$lang['entry']['flags'] = array();
	
	$lang['entry']['flags']['long'] = array(
		'draft' => '<strong>Entrada de borrador</strong>: oculto, esperando publicación',
		//'static' => '<strong>Entrada estática</strong>: normalmente oculto, para llegar a la entrada poner ?page=title-of-the-entry en url (experimental)',
		'commslock' => '<strong>Comentarios bloqueados</strong>: comentarios no permitidos para esta entrada'
	);
	
	$lang['entry']['flags']['short'] = array(
		'draft' 	=> 'Borrador',
		//'static' => 'Estático',
		'commslock'	=> 'Comentarios bloqueados'
	);

	$lang['404error'] = array(
		'subject'	=> 'No encontrado',
		'content'	=> '<p>Lo sentimos, no pudimos encontrar la página solicitada</p>'
	);
		
	// Login
	$lang['login'] = array(
		
		'head'		=> 'Iniciar sesión',
		'fieldset1'	=> 'Inserte su nombre de usuario y contraseña',
		'user'		=> 'Usuario:',
		'pass'		=> 'Contraseña:',
		'fieldset2'	=> 'Inicie sesión',
		'submit'	=> 'Iniciar sesión',
		'forgot'	=> 'Contraseña perdida'
	);
		
	$lang['login']['success'] = array(
		'success'	=> 'Ahora está conectado.',
		'logout'	=> 'Ahora está desconectado.',
		'redirect'	=> 'Serás redirigido en 5 segundos.',
		'opt1'		=> 'Volver al índice',
		'opt2'		=> 'Ir al panel de control',
		'opt3'		=> 'Agregar nueva entrada'
	);
	
	$lang['login']['error'] = array(
		'user'		=> 'Debes ingresar un nombre de usuario.',
		'pass'		=> 'Debes ingresar una contraseña.',
		'match'		=> 'Contraseña incorrecta.'
	);
	
	
	$lang['comments'] = array(
		'head'		=> 'Agregar comentario',
		'descr'		=> 'Complete el siguiente formulario para agregar sus propios comentarios',
		'fieldset1'	=> 'Datos del usuario',
		'name'		=> 'Nombre (*)',
		'email'		=> 'Email:',
		'www'		=> 'Web:',
		'cookie'	=> 'Recuérdame',
		'fieldset2'	=> 'Añade tu comentario',
		'comment'	=> 'Comentario (*):',
		'fieldset3'	=> 'Enviar',
		'submit'	=> 'Agregar',
		'reset'		=> 'Reiniciar',
		'success'	=> 'Tu comentario fue agregado exitosamente',
		'nocomments'	=> 'Esta entrada aún no ha sido comentada',
		'commslock'	=> 'Los comentarios han sido desactivados para esta entrada.',
	);
	
	$lang['comments']['error'] = array(
		'name'		=> 'Debes ingresar un nombre',
		'email'		=> 'Debes ingresar un correo electrónico válido',
		'www'		=> 'Debes ingresar una URL válida',
		'comment'	=> 'Debes ingresar un comentario',
	);
	
	$lang['date']['month'] = array(
		
		'Enero',
		'Febrero',
		'Marzo',
		'Abril',
		'Mayo',
		'Junio',
		'Julio',
		'Agosto',
		'Septiembre',
		'Octubre',
		'Noviembre',
		'Deciembre'
		
	);

	$lang['date']['month_abbr'] = array(
		
		'Ene',
		'Feb',
		'Mar',
		'Abr',
		'May',
		'Jun',
		'Jul',
		'Ago',
		'Sep',
		'Oct',
		'Nov',
		'Dec'
		
	);

	$lang['date']['weekday'] = array(
		
		'Domingo',
		'Lunes',
		'Martes',
		'Miércoles',
		'Jueves',
		'Viernes',
		'Sabado',
		
	);

	$lang['date']['weekday_abbr'] = array(
		
		'Dom',
		'Lun',
		'Mar',
		'Mie',
		'Jue',
		'Vie',
		'Sab',
		
	);



?>
