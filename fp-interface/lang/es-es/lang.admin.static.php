<?php

	$lang['admin']['static']['submenu'] = array(
		'list'		=> 'Administrar Staticas',
		'write'		=> 'Escribir Statica'
	);

	
	/* main panel */
		
	$lang['admin']['static']['list'] = array(
	
		'head'		=> 'Páginas estáticas',
		'descr'		=> 'Seleccione una página para editar o <a href="admin.php?p=static&amp;action=write">Añadir nueva</a>.',
	
		'sel'		=> 'Sel', // checkbox
		'date'		=> 'Fecha',
		'name'		=> 'Pagina',
		'title'		=> 'Titulo',
		'author'	=> 'Autor',
		
		'action'	=> 'Accion',
		'act_view'	=> 'Ver',
		'act_del'	=> 'Eliminar',
		'act_edit'	=> 'Editar'		
	);
	
	$lang['admin']['static']['list']['msgs'] = array(
		1	=> 'Página archivada correctamente',
		-1	=> 'Se produjo un error al intentar archivar  
					la página',
		2	=> 'Página eliminada correctamente',
		-2	=>	 'Error al intentar eliminar
 					la página',
	);

	/* write panel */

	$lang['admin']['static']['write'] = 
	array(
		'head'		=> 'Publicar la página estatica',
		'descr'		=> 'Editar el formulario para publicar la página',
		'fieldset1'	=> 'Editar',
		'subject'	=> 'Asunto (*):',
		'content'	=> 'Contenido (*):',
		'fieldset2'	=> 'Enviar',
		'pagename'	=> 'Nombre de la página (*):',
		'submit'	=> 'Publicar',
		'preview'	=> 'Vista Previa',

		'delfset'	=> 'Eliminar',
		'deletemsg'	=> 'Eliminar esta pagina',
		'del'		=> 'Eliminar',
		'success'	=> 'Tu página se publicó con éxito',
		'otheropts'	=> 'Otras opciones',
	);
	
	$lang['admin']['static']['write']['error'] = array(
		'subject'	=> 'No puedes enviar un asunto en blanco',
		'content'	=> 'No puedes publicar una entrada en blanco',
		'id'		=> 'Debes enviar una identificación válida'
	);
	
	
	/* delete action */	
	$lang['admin']['static']['delete'] = array(
		'head'		=> "Eliminar Página", 
		'descr'		=> 'Estás a punto de eliminar la siguiente página:',
		'preview'	=> 'Vista Previa',
		'confirm'	=> '¿Estas seguro que deseas continuar?',
		'fset'		=> 'Eliminar',
		'ok'		=> 'Si, elimine esta página',
		'cancel'	=> 'No, llévame de vuelta al panel',
		'err'		=> 'La página especificada no existe',
	
	);
	
	
		
?>
