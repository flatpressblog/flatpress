<?php
	$lang['plugin']['akismet']['errors'] = array (
		-1	=> 'Chave da API não definida. Abra o plugin para definir sua chave de API. Registre-se no <a href="http://wordpress.com">Wordpress.com</a> para obter uma.'
	);
	
	$lang['admin']['plugin']['submenu']['akismet'] = 'Akismet Configuração';
	
	$lang['admin']['plugin']['akismet'] = array(
		'head'		=> 'Configure o Akismet',
		'description'=>'Para muitas pessoas, o <a href="http://akismet.com/">Akismet</a> reduzirá bastante '
					 .'ou até eliminará completamente o comentário e o spam de trackback que você recebe no seu site.'
					 .'Se você ainda não possui uma conta no WordPress.com, pode obtê-la em '.
					 '<a href="http://wordpress.com/api-keys/">WordPress.com</a>.',
		'apikey'	=> 'WordPress.com API Key',
		'whatis'	=> '(<a href="http://faq.wordpress.com/2005/10/19/api-key/">O que é isso?</a>)',
		'submit'	=> 'Salvar a chave da API'
	);
	$lang['admin']['plugin']['akismet']['msgs'] = array(
		1		=> 'A chave da API foi salva',
		-1		=> 'A chave da API não é válida'
	);
	
?>