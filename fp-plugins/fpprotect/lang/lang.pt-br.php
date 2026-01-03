<?php
$lang ['admin'] ['config'] ['submenu'] ['fpprotect'] = 'FlatPress Protect';

$lang ['admin'] ['config'] ['fpprotect'] = array(
	'head' => 'Configurações do FlatPress Protect',
	'desc1' => 'Aqui você pode alterar as opções relacionadas à segurança do seu blog FlatPress. ' . //
		'A melhor proteção para seus visitantes e seu blog FlatPress é quando todas as opções estão desativadas.',

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

	// Part for external iFrame embedding
	'allow_external_iframe' => 'Permitir a incorporação de conteúdo externo via iFrame (Não recomendado).',
	'allowExternalIframeDsc' => 'Permite a incorporação de conteúdo externo através da tag <code>&lt;iframe&gt;</code> (por exemplo, vídeos, mapas, widgets). ' . //
		'Conteúdo incorporado de terceiros pode rastrear visitantes e pode ser inseguro. Habilite esta opção somente se realmente necessário.',

	// Part for SVG uploads via admin uploader
	'allow_svg_upload' => 'Permitir o upload de arquivos SVG através do carregador (somente usuários confiáveis).',
	'allowSvgUploadDsc' => 'Permite o upload de arquivos SVG através do carregador administrativo. Arquivos SVG podem conter conteúdo ativo (por exemplo, scripts); habilite esta opção somente se você confiar nos usuários que fazem o upload e não incorpore arquivos SVG não confiáveis.',

	// Part for the PrettyURLs .htaccess edit-field
	'allow_htaccess_edit' => 'Permitir a criação e a edição do arquivo .htaccess.',
	'allowPrettyURLEditDsc' => 'Permite o acesso ao campo de edição .htaccess do plug-in PrettyURLs para criar ou modificar o arquivo .htaccess.',

	// Part for metadate in images after upload
	'allow_image_metadate' => 'Mantenha os metadados e a qualidade da imagem original nas imagens carregadas.',
	'allowImageMetadataDsc' => 'Depois que as imagens são carregadas com o carregador, os metadados são mantidos. Isso inclui informações da câmera e coordenadas geográficas, por exemplo.',

	// Part for the visitor-ip in FlatPress
	'allow_visitor_ip' => 'Permitir que o FlatPress use o endereço IP não anônimo do visitante.',
	'allowVisitorIpDsc' => 'O FlatPress salvará o endereço IP não anônimo nos comentários, entre outras coisas. ' . //
		'Se você usar o serviço Akismet Antispam, o Akismet também receberá o endereço IP não anônimo.',

	// Part for Idle timeout for admin session
	'session_timeout_label' => 'Tempo limite de inatividade para sessão de administrador (minutos)',
	'session_timeout_desc' => 'Minutos de inatividade até que a sessão de administrador expire. Vazio ou 0 significa padrão 60 minutos.',

	'submit' => 'Salvar configurações',
		'msgs' => array(
		1 => 'Configurações salvas com êxito.',
		-1 => 'Erro ao salvar as configurações.'
	),

	// Warning message
	'warning_allowUnsafeInline' => 'Aviso: Content-Security-Policy -> Essa política contém "unsafe-inline", que é perigoso na política de script-src.',
	'warning_allowExternalIframe' => 'Aviso: A Política de Segurança de Conteúdo -> Incorporação externa via iFrame está habilitada. Conteúdo incorporado de terceiros pode rastrear visitantes e pode ser inseguro.',
	'warning_allowSvgUpload' => 'Aviso: Arquivos SVG podem conter conteúdo ativo. Faça upload apenas de arquivos SVG confiáveis ​​e não os incorpore sem revisão!',
	'warning_allowVisitorIp' => 'Aviso: Uso de endereços IP de visitantes não anônimos -> Não se esqueça de informar os <a href="static.php?page=privacy-policy" title="edit static page">visitantes do seu blog FlatPress</a> sobre isso!'
);
?>
