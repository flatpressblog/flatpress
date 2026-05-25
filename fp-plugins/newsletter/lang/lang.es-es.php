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
	'desc_batch' => 'Aquí defines cuántos correos envía el plugin en cada día de envío. ' . //
		'Elige un valor inferior al límite diario de tu proveedor de correo. ' . //
		'Al comienzo del mes, el boletín habitual se inicia automáticamente y, si es necesario, se envía en lotes diarios hasta llegar a todos los suscriptores. ' . //
		'Si no hay ningún envío en curso, también puedes iniciarlo manualmente; el envío manual usa el mismo límite diario. ' . //
		'Si al comenzar un nuevo mes todavía hay un envío manual en curso, el envío mensual automático se pospone hasta el mes siguiente.',
	'icon_sent_title' => 'Ya entregado en este envío',
	'icon_sent_alt' => 'Entregado',
	'icon_queued_title' => 'Programado para el próximo lote',
	'icon_queued_alt' => 'Programado',
	'send_now_button' => 'Envía ahora el boletín a los suscriptores',
	'send_now_confirm' => '¿Quieres enviar ahora el boletín a los suscriptores?',
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
	1 => 'El boletín se envía a los suscriptores.',
	-2 => 'Este plugin requiere el plugin LastEntries integrado en FlatPress. Por favor, actívelo previamente en el área de plugins.',
	2 => 'Se ha guardado la configuración.'
);
?>
