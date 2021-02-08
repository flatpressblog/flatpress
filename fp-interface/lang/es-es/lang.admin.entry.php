<?php


	$lang['admin']['entry']['submenu'] = 
	array (
		'list'		=> 'Administrar entradas',
		'write'		=> 'Escribir entrada',
		'cats'		=> 'Administrar Categorías'
	);


	/* default action */
	
	$lang['admin']['entry']['list'] = 
	array(
		'head'		=> 'Administrar entradas',
		'descr'		=> 'Seleccione una entrada para editar o <a href="admin.php?p=entry&amp;action=write">Añadir nueva</a>'.
					'<br /><a href="admin.php?p=entry&amp;action=cats">Editar las categorías</a>',
		'filter'	=> 'Filtrar: ',
		'nofilter'	=> 'Mostrar todo',
		'filterbtn'	=> 'Aplicar filtro',
		'sel'		=> 'Sel', // checkbox
		'date'		=> 'Fecha',
		'title'		=> 'Título',
		'author'	=> 'Autor',
		'comms'		=> '#Comms', // comments
		'action'	=> 'Action',
		'act_del'	=> 'Eliminar',
		'act_view'	=> 'Ver',
		'act_edit'	=> 'Editar'
	);
	
	/* write action */
	$lang['admin']['entry']['write'] = 
	array(
		'head'		=> 'Escribir entrada',
		'descr'		=> 'Edite el formulario para escribir la entrada',
		'uploader'	=> 'Subir',
		'fieldset1'	=> 'Editar',
		'subject'	=> 'Asunto (*):',
		'content'	=> 'Contenido (*):',
		'fieldset2'	=> 'Enviar',
		'submit'	=> 'Publicar',
		'preview'	=> 'vista previa',
		'savecontinue'	=> 'Guardar&amp;Continuar',
		'categories'	=> 'Categorias',
		'nocategories'	=> 'categorías no establecidas. <a href="admin.php?p=entry&amp;action=cats">Cree sus propias'. 
					'categorias</a> desde el panel de entrada principal. '.
					'<a href="#save">Guarde</a> primero su entrada.',
		'saveopts'	=> 'Guardar opciones',
		'success'	=> 'Su entrada fue publicada correctamente',
		'otheropts'	=> 'Otras opciones',
		'commmsg'	=> 'Administrar comentarios para esta entrada',
		'delmsg'	=> 'Eliminar esta entrada',
		//'back'		=> 'Volver descartando cambios',
	);
	

	$lang['admin']['entry']['list']['msgs'] = array(
		1	=> 'La entrada se ha guardado correctamente',
		-1	=> 'Se produjo un error al intentar guardar 
					la entrada',
		2	=> 'La entrada se eliminó correctamente',
		-2	=>	 'Se produjo un error al intentar eliminar 
					la entrada',
	);

	
	$lang['admin']['entry']['write']['error'] = array(
		'subject'	=> 'No puedes enviar un asunto en blanco',
		'content'	=> 'No puedes publicar una entrada en blanco',
	);
	
	$lang['admin']['entry']['write']['msgs'] = array(
		1	=> 'La entrada se ha guardado correctamente',
		-1	=> 'Se produjo un error: su entrada no se pudo guardar correctamente',
		-2	=> 'Se produjo un error: su entrada no se ha guardado; el índice puede estar corrupto',
		-3	=> 'Se produjo un error: su entrada se ha guardado como borrador',
		-4	=> 'Se produjo un error: su entrada se ha guardado como borrador; el índice puede estar corrupto',
		'draft'=> 'Está editando un <strong>borrador</strong> de entrada'
	);

	
	/* comments */
	
	$lang['admin']['entry']['commentlist'] = 
	array(
		'head'		=> "Comentarios para la entrada ", 
		'descr'		=> 'Seleccione un comentario para eliminar',
		'sel'		=> 'Sel',
		'content'	=> 'Contenido',
		'date'		=> 'Fecha',
		'author'	=> 'Autor',
		'email'		=> 'Email',
		'ip'		=> 'IP',
		'actions'	=> 'Acciones',
		'act_edit'	=> 'Editar',
		'act_del'	=> 'Eliminar',
		'act_del_confirm' => '¿Realmente quiere eliminar este comentario?',
		'nocomments'	=> 'Esta entrada aún no ha sido comentada.',
		
	
	);

	$lang['admin']['entry']['commentlist']['msgs'] =
	array(
		1	=> 'El comentario se ha eliminado correctamente',
		-1	=> 'Ocurrió un error al intentar eliminar 
					el comentario',
		
	);

	$lang['admin']['entry']['commedit'] = 
	array(
		'head'		=> "Editar comentario para entrada", 
		'content'	=> 'Contenido',
		'date'		=> 'Fecha',
		'author'	=> 'Autor',
		'www'		=> 'Sitio web',
		'email'		=> 'Email',
		'ip'		=> 'IP',
		'loggedin'	=> 'Usuario registrado',
		'submit'	=> 'Guardar'
		
	
	);

	$lang['admin']['entry']['commedit']['msgs'] =
	array(
		1	=> 'Comment has been edited',
		-1	=> 'An error occurred while trying to edit the comment',
	);
	
	/* delete action */
	
	$lang['admin']['entry']['delete'] = 
	array(
		'head'		=> 'Eliminar entrada', 
		'descr'		=> 'Estás a punto de eliminar la siguiente entrada:',
		'preview'	=> 'Vista previa',
		'confirm'	=> '¿Esta seguro que desea continuar?',
		'fset'		=> 'Eliminar',
		'ok'		=> 'Si, eliminar esta entrada',
		'cancel'	=> 'No, volver al panel',
		'err'		=> 'La entrada especificada no existe',
	
	);
	
	/* category mgmt */
	
	$lang['admin']['entry']['cats'] =
	array(
		'head'		=> 'Editar categorias',
		'descr'		=> '<p>Utilice el formulario a continuación para agregar y editar sus categorías. </p><p>Cada elemento de categoría debe tener este formato "nombre de categoría: <em>id_number</em>". Indentar los elementos con guiones para crear jerarquías.</p>
		
	<p>Ejemplo:</p>
	<pre>
General :1
News :2
--Announcements :3
--Events :4
----Misc :5
Technology :6
	</pre>',
		'clear'		=> 'Eliminar todos los datos de las categorías',
	
		'fset1'		=> 'Editor',
		'fset2'		=> 'Aplicar cambios',
		'submit'	=> 'Guardar'
	);
	
	$lang['admin']['entry']['cats']['msgs'] = array(
		
		1	=> 'Categorías guardadas',
		-1	=> 'Se produjo un error al intentar guardar categorías',
		2	=> 'Categorías vacías',
		-2	=> 'Se produjo un error al intentar vaciar categorías',
		-3 	=> 'Los ID de categoría deben ser estrictamente positivos (0 is not allowed)'

	);
	
	
		
?>
