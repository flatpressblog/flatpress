<?php
$lang ['admin'] ['entry'] ['submenu'] ['commentcenter'] = 'Comment Center';
$lang ['admin'] ['entry'] ['commentcenter'] = array(
	// Header of the panel
	'title' => 'Gerenciar comentários',
	'desc1' => 'Este painel permite que você gerencie os comentários de seu blog.',
	'desc2' => 'Aqui você pode fazer várias coisas:',

	// Links
	'lpolicies' => 'Gerenciar as políticas',
	'lapprove' => 'Mostrar comentários bloqueados',
	'lmanage' => 'Gerenciar comentários',
	'lconfig' => 'Configurar o plugin',
	'faq_spamcomments' => 'Obtenha ajuda sobre como lidar com comentários de spam',

	// Policies
	'policies' => 'Políticas',
	'desc_pol' => 'Aqui você pode editar as políticas de comentários.',
	'select' => 'Selecionar',
	'criteria' => 'Critério',
	'behavoir' => 'Comportamento',
	'options' => 'Opções',
	'entry' => 'Entrada',
	'entries' => 'Entradas',
	'categories' => 'Categorias',
	'nopolicies' => 'Não há nenhuma política.',
	'all_entries' => 'Todas as entradas',
	'fol_entries' => 'A política é aplicada às seguintes entradas:',
	'fol_cats' => 'A política é aplicada a entradas nas seguintes categorias:',
	'older' => 'A política é aplicada a entradas anteriores a %d dia(s).',
	'allow' => 'Permitir comentários',
	'block' => 'Bloquear comentários',
	'approvation' => 'Comentários precisam ser aprovados',
	'up' => 'Mover pra cima',
	'down' => 'Mover pra baixo',
	'edit' => 'Editar',
	'delete' => 'Deletar',
	'newpol' => 'Adicionar uma nova política',
	'del_selected' => 'Excluir política(s) selecionada(s)',
	'select_all' => 'Selecionar tudo',
	'deselect_all' => 'Desmarcar todos',

	// Configuration page
	'configure' => 'Configurar o plugin',
	'desc_conf' => 'Aqui você pode modificar as opções do plugin',
	'log_all' => 'Registrar comentários bloqueados',
	'log_all_long' => 'Verifique se você deseja registrar também os comentários que estão bloqueados',
	'email_alert' => 'Notificar comentários por e-mail',
	'email_alert_long' => 'Marque para ser informado via email quando houver um comentário para aprovar',
	'akismet' => 'Akismet',
	'akismet_use' => 'Ativar verificação do Akismet',
	'akismet_use_long' => 'Com o <a href="https://akismet.com/" target="_blank">Akismet</a>, você pode reduzir o spam nos comentários.',
	'akismet_key' => 'Akismet Key',
	'akismet_key_long' => 'O serviço <a href="https://akismet.com/signup/" target="_blank">Akismet</a> fornece a você uma <a class="hint externlink" href="https://akismet.com/support/getting-started/api-key/" target="_blank">chave</a>. Insira-a aqui.',
	'akismet_url' => 'URL base do blog para o Akismet',
	'akismet_url_long' => 'Acho que para o serviço gratuito Akismet você deve usar apenas um domínio. Você pode deixar este campo em branco, <code>%s</code> será usado',
	'save_conf' => 'Salvar configuração',

	// Edit policy page
	'apply_to' => 'Aplicar para',
	'editpol' => 'Editar uma política',
	'createpol' => 'Criar uma política',
	'some_entries' => 'Algumas entradas',
	'properties' => 'Entrada com certas propriedades',
	'se_desc' => 'Se você selecionou a opção %s, insira as entradas que deseja aplicar a esta política',
	'se_fill' => 'Por favor, preencha os campos com o <a href="admin.php?p=entry">ID</a> das entradas (<code>entryYYMMDD-HHMMSS</code>)',
	'po_title' => 'Propriedades',
	'po_desc' => 'Se você selecionou a opção %s, preencha as propriedades',
	'po_comp' => 'Os campos não são obrigatórios, mas você deve preencher pelo menos um ou a política será aplicada em todas as entradas',
	'po_time' => 'Opções de tempo',
	'po_older' => 'Aplicar a entradas anteriores a ',
	'days' => 'dias',
	'save_policy' => 'Salvar política',

	// Delete policies page
	'del_policies' => 'Excluir políticas',
	'del_descs' => 'Você vai excluir esta política: ',
	'del_descm' => 'Você vai excluir estas políticas: ',
	'sure' => 'Tem certeza?',
	'del_subs' => 'Sim, por favor exclua',
	'del_subm' => 'Sim, exclua-os',
	'del_cancel' => 'Não, me leve de volta ao painel',

	// Approve comments page
	'app_title' => 'Aprovar comentário',
	'app_desc' => 'Aqui você pode aprovar os comentários',
	'app_date' => 'Data',
	'app_content' => 'Comentário',
	'app_author' => 'Autor',
	'app_email' => 'Email',
	'app_ip' => 'IP',
	'app_actions' => 'Ações',
	'app_publish' => 'Publicar',
	'app_delete' => 'Deletar',
	'app_nocomms' => 'Não há nenhum comentário',
	'app_pselected' => 'Publicar comentário(s) selecionado(s)',
	'app_dselected' => 'Remover comentário(s) selecionado(s)',
	'app_other' => 'Outros comentários',
	'app_akismet' => 'Assinado como spam',
	'app_spamdesc' => 'Estes comentários foram bloqueados por Akismet',
	'app_hamsubmit' => 'Envie para o Akismet como ham quando você publicá-los',
	'app_pubnotham' => 'Publique, mas não envie como ham',

	// Delete comments page
	'delc_title' => 'Excluir comentários',
	'delc_descs' => 'Você vai deletar este comentário: ',
	'delc_descm' => 'Você vai deletar estes comentários: ',

	// Manage comments page
	'man_searcht' => 'Pesquisar uma entrada',
	'man_searchd' => 'Insira o <a href="admin.php?p=entry">ID</a> (<code>entryYYMMDD-HHMMSS</code>) da entrada cujos comentários você deseja gerenciar',
	'man_search' => 'Procurar',
	'man_commfor' => 'Comentários para %s',
	'man_spam' => 'Enviar como spam para Akismet',

	// The simple edit
	'simple_pre' => 'Os comentários para esta entrada serão ',
	'simple_1' => 'será permitido',
	'simple_0' => 'requer sua aprovação',
	'simple_-1' => 'será bloqueado',
	'simple_manage' => 'Gerenciar os comentários desta entrada',
	'simple_edit' => 'Editar políticas',

	// Akismet warnings
	'akismet_errors' => array(
		-1 => 'A chave Akismet está vazia. Por favor, insira-o.',
		-2 => 'Não conseguimos contatar os servidores da Akismet.',
		-3 => 'A resposta do Akismet falhou.',
		-4 => 'A chave Akismet não é válida.'
	),

	// Messages
	'msgs' => array(
		1 => 'Configuração salva.',
		-1 => 'Ocorreu um erro ao tentar salvar a configuração.',

		2 => 'Política salva.',
		-2 => 'Ocorreu um erro ao tentar salvar a política (talvez suas configurações estejam erradas).',

		3 => 'Política movida.',
		-3 => 'Ocorreu um erro ao tentar mover a política (ou não pode ser movida).',

		4 => 'Política(s) removida(s).',
		-4 => 'Ocorreu um erro ao tentar remover a(s) política(s) (ou você não selecionou nenhuma política).',

		5 => 'Comentário(s) publicado(s).',
		-5 => 'Ocorreu um erro ao tentar publicar o(s) comentário(s).',

		6 => 'Comentário(s) removido(s).',
		-6 => 'Ocorreu um erro ao tentar remover o(s) comentário(s) (ou você não selecionou nenhum comentário).',

		7 => 'Comentário enviado.',
		-7 => 'Ocorreu um erro ao tentar enviar o comentário.'
	),

	// Errors
	'errors' => array(
		'pol_nonex' => 'A política que você deseja editar não existe.',
		'entry_nf' => 'A entrada que você selecionou não existe.'
	)
);

$lang ['plugin'] ['commentcenter'] = array(
	'akismet_error' => 'Desculpe, estamos encontrando dificuldades técnicas.',
	'lock' => 'Desculpe, comentários para esta entrada estão bloqueados.',
	'approvation' => 'Os comentários foram salvos, mas o Administrador deve aprová-los antes de exibi-los.',

	// Mail for comments
	'mail_subj' => 'Novo comentário para aprovar em %s'
);

$lang ['plugin'] ['commentcenter'] ['mail_text'] = 'Querido(a) %toname%,

"%fromname%" %frommail% acaba de postar um comentário na entrada intitulada "%entrytitle%"
mas precisa de sua aprovação antes de mostrá-lo.

Aqui está o comentário que acabou de ser postado:
__________________________________________
%content%
__________________________________________

Faça login na área administrativa do seu blog FlatPress e verifique o comentário bloqueado no centro de comentários.

Gerado automaticamente por
%blogtitle%

';
?>
