<?php
	$lang['plugin']['lastcommentsadmin ']['errors'] = array (
		-1	=> 'Chave da API não definida. Abra o plug-in para definir sua chave de API. Registre-se no <a href="http://wordpress.com">Wordpress.com</a> para obter uma.'
	);

	$lang['admin']['plugin']['submenu']['lastcommentsadmin'] = 'Admin de Últimos Comentários';

	$lang['admin']['plugin']['lastcommentsadmin'] = array(
		'head'		=> 'Admin de Últimos Comentários',
		'description'=>'Limpar e recriar o cache do último comentário',
		'clear'	=> 'Limpar o cache',
		'cleardescription' => 'Exclua o arquivo de cache do último comentário. O novo cache de arquivo será criado quando um novo comentário será publicado.',
		'rebuild' => 'Recriar o cache',
		'rebuilddescription' => 'Recriar o arquivo de cache do último comentário. Pode demorar muito tempo. Não foi possível funcionar. Pode queimar o mouse!',
	);
	$lang['admin']['plugin']['lastcommentsadmin']['msgs'] = array(
		1		=> 'Cache excluído',
		2		=> 'Cache recriado',
		-1		=> 'Erro!',
		-2	   =>  'Este plugin requer o plugin LastComments!'
	);
	

?>