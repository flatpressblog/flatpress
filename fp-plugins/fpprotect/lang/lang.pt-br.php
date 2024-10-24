<?php
$lang ['admin'] ['config'] ['submenu'] ['fpprotect'] = 'FlatPress Protect';

$lang ['admin'] ['config'] ['fpprotect'] = array(
	'head' => 'Configurações do FlatPress Protect',
	'desc1' => 'Aqui você pode alterar as opções relacionadas à segurança do seu blog FlatPress.',

	// Part for unsafe inline scripts
	'allow_unsafe_inline' => 'Permitir scripts Java inseguros (Não recomendado)',

	'allowUnsafeInlineDsc' => '<p>Permite o carregamento de código JavaScript inseguro em linha.</p>' . //
		'<p><br>Observação para desenvolvedores de plug-ins: adicione um nonce ao seu script Java.</p>' . //
		'Um exemplo para PHP:
		<pre>$random_hex = RANDOM_HEX;
' . //
		'... script nonce="\' . $random_hex . \'" src=" ...' . //
		'</pre>' . //
		'Um exemplo para o modelo Smarty:
		<pre>' . //
		'... script nonce="{$smarty.const.RANDOM_HEX}" ...' . //
		'</pre>' . //
		'<p>Isso garante que o navegador do visitante só execute scripts Java originários do seu blog do FlatPress.</p>',

	// Part for the PrettyURLs .htaccess edit-field
	'allow_htaccess_edit' => 'Permitir a criação e a edição do arquivo .htaccess.',
	'allowPrettyURLEditDsc' => 'Permite o acesso ao campo de edição .htaccess do plug-in PrettyURLs para criar ou modificar o arquivo .htaccess.',

	'submit' => 'Salvar configurações',
		'msgs' => array(
		1 => 'Configurações salvas com êxito.',
		-1 => 'Erro ao salvar as configurações.'
	),

	// Warning message for unsafe inline scripts
	'warning_allowUnsafeInline' => 'Aviso: Content-Security-Policy -> Essa política contém "unsafe-inline", que é perigoso na política de script-src.'
);
?>
