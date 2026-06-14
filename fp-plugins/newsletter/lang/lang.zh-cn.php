<?php
$lang ['plugin'] ['newsletter'] = array(
	// Widget
	'subject' => '新闻通讯',
	'input_email_placeholder' => '电子邮件地址',
	'accept_privacy_policy' => '同意隐私政策',
	'privacy_link_text' => '隐私政策',
	'button' => '订阅',
	'csrf_error' => '无效的 CSRF 令牌。',

	// Double Opt-In
	'confirm_subject' => '请确认订阅新闻通讯',
	'confirm_greeting' => '感谢您订阅每月新闻通讯。',
	'confirm_link_text' => '点击此处确认订阅。',
	'confirm_ignore' => '如果您不想要这封邮件，请忽略它。',

	// E-Mail-Content
	'last_entries' => '最新文章',
	'no_entries' => '没有文章',
	'last_comments' => '最新评论',
	'no_comments' => '无评论',
	'unsubscribe' => '取消订阅新闻通讯',
	'privacy_policy' => '隐私政策',
	'legal_notice' => '法律声明'
);

// Admin panel: Newsletter subscribers
$lang ['admin'] ['plugin'] ['submenu'] ['newsletter'] = '新闻通讯';
$lang ['admin'] ['plugin'] ['newsletter'] = array(
	'head' => '新闻通讯管理',
	'desc_subscribers' => '在这里，您可以查看新闻通讯订阅者的所有电子邮件地址，以及订阅者是否同意隐私政策。' . //
		'您还可以删除订阅。',
	'admin_subscribers_list' => '订阅者列表',
	'email_address' => '电子邮件地址',
	'subscribe_date' => '日期',
	'subscribe_time' => '时间',
	'newsletter_no_subscribers' => '无订阅者',
	'delete_subscriber' => '删除此地址',
	'delete_confirm' => '您确定要删除此地址吗？',
	'desc_batch' => '在此处设置插件在每个发送日发送的邮件数量。' . //
		'请选择一个低于您的邮件服务商每日发送限额的数值。' . //
		'每月月初，常规通讯会自动开始发送，如有必要，将分批次每日发送，直至所有订阅者均收到。' . //
		'如果当前没有正在运行的发送任务，您也可以手动启动一个；手动发送任务将使用相同的每日发送限额。' . //
		'如果在新月开始时仍有手动发送任务正在运行，则自动月度发送任务将推迟至下个月。',
	'icon_sent_title' => '本次发送已完成',
	'icon_sent_alt' => '已发送',
	'icon_queued_title' => '计划下一批',
	'icon_queued_alt' => '已计划',
	'send_now_button' => '立即向所有订阅者发送新闻通讯',
	'send_now_confirm' => '是否立即向所有订阅者发送新闻通讯？',
	'send_type_monthly' => '每月发送',
	'send_type_manual'  => '手动发送',
	'sub_remaining' => '尚未发送：',
	'batch_size_label' => '每批邮件数',
	'save_button' => '保存'
);

$lang ['plugin'] ['newsletter'] ['errors'] = array (
	-2 => '必须启用 LastEntries 插件才能使用此插件。'
);

$lang ['admin'] ['plugin'] ['newsletter'] ['msgs'] = array(
	1 => '新闻通讯将发送给所有订阅者。',
	-2 => '要使用此插件，需要启用与 FlatPress 集成的 LastEntries 插件。请先在插件区域启用！',
	2 => '设置已保存。'
);
?>
