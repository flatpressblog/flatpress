<?php
$lang ['plugin'] ['newsletter'] = array(
	// Widget
	'subject' => 'Boletim informativo',
	'input_email_placeholder' => 'Seu endereço de e-mail',
	'accept_privacy_policy' => 'Aceito a política de privacidade',
	'privacy_link_text' => 'ir para a política de privacidade',
	'button' => 'Registre-se',
	'csrf_error' => 'Token CSRF inválido.',

	// Double Opt-In
	'confirm_subject' => 'Por favor, confirme sua assinatura do boletim informativo',
	'confirm_greeting' => 'Obrigado por assinar nosso boletim informativo mensal.',
	'confirm_link_text' => 'Clique aqui para confirmar sua assinatura',
	'confirm_ignore' => 'Se você não solicitou este e-mail, ignore-o.',

	// E-Mail-Content
	'last_entries' => 'Últimas entradas',
	'no_entries' => 'Nenhuma entrada',
	'last_comments' => 'Últimos comentários',
	'no_comments' => 'Sem comentários',
	'unsubscribe' => 'Cancelar a assinatura do boletim informativo',
	'privacy_policy' => 'Política de privacidade',
	'legal_notice' => 'Aviso legal'
);

// Admin panel: Newsletter subscribers
$lang ['admin'] ['plugin'] ['submenu'] ['newsletter'] = 'Assinantes do boletim informativo';
$lang ['admin'] ['plugin'] ['newsletter'] = array(
	'head' => 'Gerenciamento de boletins informativos',
	'desc_subscribers' => 'Aqui você pode ver todos os endereços de e-mail dos assinantes do boletim informativo e quando os assinantes aceitaram a política de privacidade. ' . //
		'Você também pode excluir assinantes.',
	'admin_subscribers_list' => 'Lista de assinantes',
	'email_address' => 'Endereço de e-mail',
	'subscribe_date' => 'Data',
	'subscribe_time' => 'Hora',
	'newsletter_no_subscribers' => 'Não há assinantes disponíveis',
	'delete_subscriber' => 'Excluir este endereço',
	'delete_confirm' => 'Deseja realmente excluir este endereço?',
	'desc_batch' => 'Aqui você pode especificar para quantos assinantes um boletim informativo é enviado por dia. '. //
		'Pergunte ao seu provedor de e-mail quantos e-mails podem ser enviados por dia. ' . //
		'O boletim informativo é enviado automaticamente a todos os assinantes no início do mês. ' . //
		'Se nenhum envio automático estiver em execução no momento, você também poderá iniciar o envio do boletim informativo imediatamente. ' . //
		'Se o envio imediato não tiver sido concluído até o dia 28 do mês, todos os assinantes não receberão automaticamente o boletim informativo regular até o mês seguinte.',
	'icon_sent_title' => 'Já entregue nesta expedição',
	'icon_sent_alt' => 'Entregue',
	'icon_queued_title' => 'Programado para o próximo lote',
	'icon_queued_alt' => 'Programado',
	'send_all_button' => 'Enviar newsletter para todos os assinantes agora',
	'send_all_confirm' => 'Deseja enviar o boletim informativo a todos os assinantes agora?',
	'send_type_monthly' => 'Envio mensal.',
	'send_type_manual'  => 'Envio manual.',
	'sub_remaining' => 'Ainda a ser enviado:',
	'batch_size_label' => 'Número de e-mails por lote',
	'save_button' => 'Salvar'
);

$lang ['plugin'] ['newsletter'] ['errors'] = array (
	-2 => 'O plug-in LastEntries deve estar ativo para que você possa usar esse plug-in.'
);

$lang ['admin'] ['plugin'] ['newsletter'] ['msgs'] = array(
	1 => 'O boletim informativo é enviado a todos os assinantes.',
	-2 => 'Esse plug-in requer o plug-in LastEntries integrado no FlatPress. Por favor, ative-o previamente na área de plugins!',
	2 => 'As configurações foram salvas.'
);
?>
