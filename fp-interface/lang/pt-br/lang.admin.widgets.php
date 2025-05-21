<?php
//Terminado - 15 de fevereiro de 2020.

	$lang['admin']['widgets']['submenu']['default'] = 'Administre Widgets';
	$lang['admin']['widgets']['submenu']['raw'] 	= 'Administre Widgets (cru)';

	/* default action */
	
	$lang['admin']['widgets']['default'] = array(
		'head'		=> 'Administre Widgets',
		
		'descr'		=> 	'Um <a class="hint" '.
						'href="http://wiki.flatpress.org/doc:widgets" title="O que é um Widget?">'.
						'Widget</a>  é um componente dinâmico que pode exibir dados e interagir com o usuário.
						Embora <strong>Temas</strong> tenham como objetivo alterar a aparência do seu blog, os Widgets 
						<strong>estenda</strong> aparência e funcionalidades.</p>

						<p>Os widgets podem ser arrastados para áreas especiais do seu tema, chamadas de <strong>WidgetSets</strong>. O número e o nome dos WidgetSets podem variar de acordo com o
tema que você escolher.</p>

						<p>O FlatPress vem com vários widgets: existem widgets para ajudar no login, para exibir uma caixa de pesquisa, etc.</p>
						
						<p>Cada widget é definido por um <a class="hint" '.
						'href="http://wiki.flatpress.org/doc:plugins" title="O que é um Plugin?">plugin</a>.',
						
		'availwdgs'	=> 'Widgets disponíveis',
		'trashcan'	=> 'Coloque aqui para excluir',
		
		'themewdgs' 	=> 'Conjunto de widgets para este tema.',
		'themewdgsdescr' => 'O tema que você selecionou atualmente permite que você tenha os seguintes WidgetSets.',
		'oldwdgs'	=> 'Outros WidgetSets',
		'oldwdgsdescr' 	=>'Os seguintes WidgetSets parecem não pertencer a nenhum dos WidgetSets listados acima. Eles podem ser restos de outro tema.',
		
		'submit'	=> 'Salve alterações',

	);
	
	$lang['admin']['widgets']['default']['stdsets'] = array(
		'top'		=> 'Barra superior',
		'bottom'	=> 'Barra inferior',
		'left'		=> 'Barra esquerda',
		'right'		=> 'Barra direita',
	);
	
	$lang['admin']['widgets']['default']['msgs'] = array(
		1		=> 'Configuração salva',
		-1		=> 'Ocorreu um erro ao tentar salvar, tente novamente.',
	);


	
	/* "raw" panel */	
	
	$lang['admin']['widgets']['raw'] = array(
		'head'		=> 'Administre Widgets (<em>editor cru</em>)',
		'descr'		=> 'Um <a class="hint" '.
						'href="http://wiki.flatpress.org/doc:plugins" title="O que é um Widget?">'.
						'Widget</a> é um elemento visual de um <a class="hint" '.
						'href="http://wiki.flatpress.org/doc:plugins" title="O que é um plugin?">'.
						'Plugin</a> que você pode colocar em algumas áreas especiais (os <em>WidgetSets</em>) nas páginas do seu blog.</p>'.
						'<p>Isso é o editor <strong>raw</strong>; alguns usuários avançados ou pessoas que não pode ter JavaScript pode preferir.',
						
		'fset1'		=> 'Editor',
		'fset2'		=> 'Aplique alterações',
		'submit'	=> 'Aplique',

	);
	
	
	$lang['admin']['widgets']['raw']['msgs'] = array(
		1		=> 'Configuração salva',
		-1		=> 'Ocorreu um erro ao tentar salvar. Isso pode acontecer por vários motivos: talvez seu arquivo contenha erros de sintaxe.',
	);

		

	/* system errors */
		
	$lang['admin']['widgets']['errors'] = array(
		'generic'	=> 'O widget chamado <strong>%s</strong> não está registrado e será ignorado.'.
 				'O plugin está ativado no <a href="admin.php?p=plugin">painel de plugins</a>?'

	);
	
?>
