<?php
$lang ['plugin'] ['newsletter'] = array(
	// Widget
	'subject' => 'Boletín',
	'input_email_placeholder' => 'Su dirección de correo electrónico',
	'accept_privacy_policy' => 'Acepto la política de privacidad',
	'privacy_link_text' => 'ir a la política de privacidad',
	'button' => 'Suscribirse',
	'csrf_error' => 'CSRF token inválido.',

	// Double Opt-In
	'confirm_subject' => 'Confirme su suscripción al boletín',
	'confirm_greeting' => 'Gracias por suscribirse a nuestro boletín mensual.',
	'confirm_link_text' => 'Haga clic aquí para confirmar su suscripción',
	'confirm_ignore' => 'Si no ha solicitado este correo electrónico, ignórelo.',

	// E-Mail-Content
	'last_entries' => 'Últimas entradas',
	'no_entries' => 'No hay entradas',
	'last_comments' => 'Últimos comentarios',
	'no_comments' => 'Sin comentarios',
	'unsubscribe' => 'Cancelar suscripción al boletín',
	'privacy_policy' => 'Política de privacidad',
	'legal_notice' => 'Menciones legales'
);

// Admin panel: Newsletter subscribers
$lang ['admin'] ['plugin'] ['submenu'] ['newsletter'] = 'Boletín';
$lang ['admin'] ['plugin'] ['newsletter'] = array(
	'head' => 'Gestión de boletines',
	'desc_subscribers' => 'Aquí puede ver todas las direcciones de correo electrónico de los suscriptores del boletín y cuándo los suscriptores han aceptado la política de privacidad. ' . //
		'También puede eliminar abonados.',
	'admin_subscribers_list' => 'Lista de suscriptores',
	'email_address' => 'Dirección de correo electrónico',
	'subscribe_date' => 'Fecha del boletín',
	'subscribe_time' => 'Hora',
	'newsletter_no_subscribers' => 'No hay suscriptores disponibles',
	'delete_subscriber' => 'Eliminar esta dirección',
	'delete_confirm' => '¿Realmente desea eliminar esta dirección?',
	'desc_batch' => 'Aquí puede especificar a cuántos suscriptores se envía un boletín al día. '. //
		'Pregunte a su proveedor de correo electrónico cuántos correos pueden enviarse al día. ' . //
		'El boletín se envía automáticamente a todos los suscriptores a principios de mes. ' . //
		'Si actualmente no se está realizando ningún envío automático, también puede iniciar el envío inmediato del boletín. ' . //
		'Si el envío inmediato no se ha completado antes del día 28 del mes, todos los suscriptores no recibirán automáticamente el boletín periódico hasta el mes siguiente.',
	'icon_sent_title' => 'Ya entregado en este envío',
	'icon_sent_alt' => 'Entregado',
	'icon_queued_title' => 'Programado para el próximo lote',
	'icon_queued_alt' => 'Programado',
	'send_all_button' => 'Enviar el boletín a todos los suscriptores ahora',
	'send_all_confirm' => '¿Desea enviar el boletín a todos los suscriptores ahora?',
	'send_type_monthly' => 'Envío mensual.',
	'send_type_manual'  => 'Envío manual.',
	'sub_remaining' => 'Pendiente de envío:',
	'batch_size_label' => 'Número de correos electrónicos por lote',
	'save_button' => 'Guardar'
);

$lang ['plugin'] ['newsletter'] ['errors'] = array (
	-2 => 'El plugin LastEntries debe estar activo para poder utilizar este plugin.'
);

$lang ['admin'] ['plugin'] ['newsletter'] ['msgs'] = array(
	1 => 'El boletín se envía a todos los abonados.',
	-2 => 'Este plugin requiere el plugin LastEntries integrado en FlatPress. Por favor, actívelo previamente en el área de plugins.',
	2 => 'Se ha guardado la configuración.'
);
?>
