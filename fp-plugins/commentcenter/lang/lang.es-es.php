<?php
$lang ['admin'] ['entry'] ['submenu'] ['commentcenter'] = 'Centro de comentarios';
$lang ['admin'] ['entry'] ['commentcenter'] = array(
	// Header of the panel
	'title' => 'Centro de comentarios',
	'desc1' => 'Este panel te permite gestionar los comentarios de tu blog.',
	'desc2' => 'Aquí puedes hacer varias cosas:',

	// Links
	'lpolicies' => 'Gestión de las directrices',
	'lapprove' => 'Mostrar comentarios bloqueados',
	'lmanage' => 'Gestionar comentarios',
	'lconfig' => 'Configuración del plugin',
	'faq_spamcomments' => 'Obtén ayuda sobre cómo hacer frente a los comentarios spam',

	// Policies
	'policies' => 'Directrices',
	'desc_pol' => 'Aquí puede editar las directrices para los comentarios.',
	'select' => 'Seleccione',
	'criteria' => 'Criterios',
	'behavoir' => 'Comportamiento',
	'options' => 'Ajustes',
	'entry' => 'Entrada',
	'entries' => 'Entradas',
	'categories' => 'Categorías',
	'nopolicies' => 'No existen directrices',
	'all_entries' => 'Todas las entradas',
	'fol_entries' => 'La directriz se aplica a las siguientes entradas:',
	'fol_cats' => 'La directriz se aplica a las inscripciones en las siguientes categorías:',
	'older' => 'La directriz se aplica a las entradas anteriores al día %d (e).',
	'allow' => 'Permitir comentarios',
	'block' => 'Prohibir comentarios',
	'approvation' => 'Los comentarios deben estar autorizados',
	'up' => 'Hasta arriba',
	'down' => 'Hacia abajo',
	'edit' => 'Editar',
	'delete' => 'Borrar',
	'newpol' => 'Añadir una nueva política',
	'del_selected' => 'Borrar directiva(s) seleccionada(s)',
	'select_all' => 'Seleccionar todo',
	'deselect_all' => 'Seleccione ninguno',

	// Configuration page
	'configure' => 'Configuración del plugin',
	'desc_conf' => 'Puede cambiar las opciones del plugin aquí.',
	'log_all' => 'Registrar comentarios bloqueados',
	'log_all_long' => 'Active esta opción si también desea registrar los comentarios bloqueados.',
	'email_alert' => 'Notificación por correo electrónico',
	'email_alert_long' => 'Si necesita comprobar un comentario para su aprobación, puede por correo electrónico.',
	'akismet' => 'Akismet',
	'akismet_use' => 'Comprobación de comentarios con Akismet',
	'akismet_use_long' => 'Con <a href="https://akismet.com/" target="_blank">Akismet</a> puedes reducir el spam en los comentarios.',
	'akismet_key' => 'Clave Akismet',
	'akismet_key_long' => 'El servicio <a href="https://akismet.com/signup/" target="_blank">Akismet</a> le proporciona una <a class="hint externlink" href="https://akismet.com/support/getting-started/api-key/" target="_blank">clave</a>. Insértalo aquí.',
	'akismet_url' => 'URL del blog para Akismet',
	'akismet_url_long' => 'Sólo debe utilizar un dominio para el servicio gratuito Akismet. Puede dejar este campo vacío. A continuación se utiliza el <code>%s</code>.',
	'save_conf' => 'Guardar ajustes',

	// Edit policy page
	'apply_to' => 'Solicitar al',
	'editpol' => 'Editar una directriz',
	'createpol' => 'Crear una directriz',
	'some_entries' => 'Algunas entradas',
	'properties' => 'Entrada con determinadas propiedades',
	'se_desc' => 'Si ha seleccionado la opción %s, añada los mensajes que desea aplicar a esta política.',
	'se_fill' => 'Por favor, rellene los campos con el <a href="admin.php?p=entry">ID</a> de las entradas (<code>entryYYMMDD-HHMMSS</code>).',
	'po_title' => 'Propiedades',
	'po_desc' => 'Si ha seleccionado la opción %s, rellene las propiedades.',
	'po_comp' => 'Los campos no son obligatorios, pero debe rellenar al menos uno o la política se aplicará a todos los mensajes.',
	'po_time' => 'Ajustes de tiempo',
	'po_older' => 'Se aplica a los mensajes con más de ',
	'days' => 'días de antigüedad.',
	'save_policy' => 'Política de ahorro',

	// Delete policies page
	'del_policies' => 'Eliminar directrices',
	'del_descs' => 'Eliminará esta política: ',
	'del_descm' => 'Borrará estas directrices: ',
	'sure' => '¿Seguro?',
	'del_subs' => 'Sí, por favor, borre.',
	'del_subm' => 'Sí, por favor, bórrelos todos.',
	'del_cancel' => 'No, volvamos a la configuración.',

	// Approve comments page
	'app_title' => 'Aprobar el comentario',
	'app_desc' => 'Aquí puede aprobar los comentarios.',
	'app_date' => 'fecha',
	'app_content' => 'Comentario',
	'app_author' => 'Autor',
	'app_email' => 'correo electrónico',
	'app_ip' => 'IP',
	'app_actions' => 'Medidas',
	'app_publish' => 'Publicación',
	'app_delete' => 'Borrar',
	'app_nocomms' => 'No hay comentarios.',
	'app_pselected' => 'Publicar los comentarios seleccionados',
	'app_dselected' => 'Eliminar los comentarios seleccionados',
	'app_other' => 'Otras observaciones',
	'app_akismet' => 'Reconocido como spam',
	'app_spamdesc' => 'Estos comentarios han sido bloqueados por Akismet.',
	'app_hamsubmit' => 'Al publicar, informe también como Ham a Akismet.',
	'app_pubnotham' => 'Publicar, pero no transferir a Akismet.',

	// Delete comments page
	'delc_title' => 'Eliminar comentarios',
	'delc_descs' => 'Borrarás este comentario: ',
	'delc_descm' => 'Borrarás estos comentarios: ',

	// Manage comments page
	'man_searcht' => 'Buscar una entrada',
	'man_searchd' => 'Introduce el <a href="admin.php?p=entry">ID</a> (<code>entryYYMMDD-HHMMSS</code>) del post cuyos comentarios quieres gestionar.',
	'man_search' => 'Buscar en',
	'man_commfor' => 'Observaciones para %s',
	'man_spam' => 'Reportar como spam a Akismet',

	// The simple edit
	'simple_pre' => 'Los comentarios sobre este post ',
	'simple_1' => 'serán permitidos.',
	'simple_0' => 'necesitan tu aprobación.',
	'simple_-1' => 'están bloqueados.',
	'simple_manage' => 'Gestionar los comentarios de esta entrada.',
	'simple_edit' => 'Editar directrices',

	// Akismet warnings
	'akismet_errors' => array(
		-1 => 'La clave de Akismet está vacía. Por favor, introdúzcala.',
		-2 => 'No hemos podido contactar con los servidores de Akismet.',
		-3 => 'La respuesta de Akismet ha fallado.',
		-4 => 'La clave de Akismet no es válida.'
	),

	// Messages
	'msgs' => array(
		1 => 'Configuración guardada.',
		-1 => 'Se ha producido un error al guardar la configuración.',

		2 => 'Política salvada.',
		-2 => 'Se ha producido un error al guardar la política (tal vez la configuración es incorrecta).',

		3 => 'Directiva aplazada.',
		-3 => 'Se ha producido un error al intentar mover la política (o no se puede mover).',

		4 => 'Directiva(s) suprimida(s).',
		-4 => 'Se ha producido un error al intentar eliminar la(s) política(s) (o no ha seleccionado una política).',

		5 => 'Comentario(s) publicado(s).',
		-5 => 'Se ha producido un error al intentar publicar los comentarios.',

		6 => 'Comentario(s) eliminado(s).',
		-6 => 'Se ha producido un error al intentar eliminar los comentarios (o no ha seleccionado ningún comentario).',

		7 => 'Comentario presentado.',
		-7 => 'Se ha producido un error al enviar el comentario.'
	),

	// Errors
	'errors' => array(
		'pol_nonex' => 'La política que desea editar no existe.',
		'entry_nf' => 'La entrada seleccionada no existe.'
	)
);

$lang ['plugin'] ['commentcenter'] = array(
	'akismet_error' => 'Lo sentimos, estamos experimentando dificultades técnicas.',
	'akismet_spam'  => 'Tu comentario ha sido reconocido como spam.',
	'lock' => 'Lo sentimos, este post no se puede comentar.',
	'approvation' => 'El comentario se ha guardado, pero el administrador debe aprobarlo antes de que pueda mostrarse.',

	// Mail for comments
	'mail_subj' => 'Nuevo comentario para aprobar %s'
);

$lang ['plugin'] ['commentcenter'] ['mail_text'] = 'Hola %toname%,

"%fromname%" %frommail% ha escrito un comentario sobre la entrada titulada "%entrytitle%"
Pero esto necesita su aprobación antes de ser publicado.

Lo siguiente fue escrito como comentario:
_________________________________________
%content%
_________________________________________

Acceda al área administrativa de su blog FlatPress y compruebe el comentario bloqueado en el centro de comentarios.

Generado automáticamente por
%blogtitle%

';
?>
