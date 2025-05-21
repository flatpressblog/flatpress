<?php
	$lang['plugin']['lastcommentsadmin ']['errors'] = array (
		-1	=> 'No se estableció la clave de API. Abra el Plugin para configurar su clave API. Registrarse en <a href="http://wordpress.com">Wordpress.com</a> para conseguir una'
	);

	$lang['admin']['plugin']['submenu']['lastcommentsadmin'] = 'Últimos comentarios Admin';

	$lang['admin']['plugin']['lastcommentsadmin'] = array(
		'head'			=> 'Últimos comentarios Admin',
		'description'		=> 'Limpiar y reconstruir la caché del último comentario ',
		'clear'			=> 'Limpiar cache',
		'cleardescription' 	=> 'Elimina el último archivo de caché de comentarios. Se creará una nueva caché de archivos cuando se publique un nuevo comentario.',
		'rebuild'		=> 'Reconstruir caché',
		'rebuilddescription'	=> 'Reconstruye el último archivo de caché de comentarios. Podría llevar mucho tiempo. Podría no funcionar en absoluto. ¡Podría quemar tu ratón!',
	);
	$lang['admin']['plugin']['lastcommentsadmin']['msgs'] = array(
		1		=> 'Caché eliminada',
		2		=> 'Caché reconstruida!',
		-1		=> '¡Error!',
		-2	  	=> '¡Este Plugin requiere el Plugin LastComments!'
	);
	

?>