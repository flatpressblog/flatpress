<?php
$lang ['admin'] ['entry'] ['submenu'] ['commentcenter'] = '评论中心';
$lang ['admin'] ['entry'] ['commentcenter'] = array(
	// Header of the panel
	'title' => '评论中心',
	'desc1' => '此面板管理您对博客文章的评论.',
	'desc2' => '可以：:',

	// Links
	'lpolicies' => '管理策略',
	'lapprove' => '未审核的评论',
	'lmanage' => '管理发布评论',
	'lconfig' => '设置评论中心',
	'faq_spamcomments' => '打开“垃圾评论常见问题”页面',

	// Policies
	'policies' => '管理策略',
	'desc_pol' => '这里可以编辑评论策略等。',
	'select' => '选择',
	'criteria' => '目标文章',
	'behavoir' => '指定行为',
	'options' => '选项',
	'entry' => '记录',
	'entries' => '记录集',
	'categories' => '文章类别',
	'nopolicies' => '没有一个政策',
	'all_entries' => '所有文章',
	'fol_entries' => '对以下文章应用策略：',
	'fol_cats' => '将策略应用于以下文章类别：',
	'older' => '该策略应用于超过%d天的条目。',
	'allow' => '允许评论',
	'block' => '阻止评论',
	'approvation' => '评论需要审核',
	'up' => '上一页',
	'down' => '下一页',
	'edit' => '编辑',
	'delete' => '删除',
	'newpol' => '添加新评论',
	'del_selected' => '删除选定的评论',
	'select_all' => '选择全部',
	'deselect_all' => '取消选择所有项',

	// Configuration page
	'configure' => '设置评论中心',
	'desc_conf' => '在此设定评论中心的选项。',
	'log_all' => '记录阻止的注释',
	'log_all_long' => '要记录已阻止的注释，请勾选。',
	'email_alert' => 'email通知评论',
	'email_alert_long' => '要通过email通知投稿了需要批准的评论，请选中。',
	'akismet' => 'Akismet',
	'akismet_use' => '启用Akismet检查',
	'akismet_use_long' => '<a href="https://akismet.com/" target="_blank">Akismet</a>使用的话，可以减少评论的垃圾邮件。',
	'akismet_key' => 'Akismet Key',
	'akismet_key_long' => '<a href="https://akismet.com/signup/" target="_blank">Akismet服务</a>提供的<a class="hint externlink" href="https://akismet.com/support/getting-started/api-key/" target="_blank">Akismet Key</a>请在这里填写。',
	'akismet_url' => 'Akismet检查对象博客的基础URL',
	'akismet_url_long' => 'Akismet的免费服务，好像只有一个域名可以使用。如果保持空白，<code>%s</code> 会被使用吧。',
	'save_conf' => '保存设置',

	// Edit policy page
	'apply_to' => '申请',
	'editpol' => '编辑策略',
	'createpol' => '创建策略',
	'some_entries' => '指定的文章',
	'properties' => '具有指定属性的文章',
	'se_desc' => '选择了适用对象「%s」请用ID指定适用策略的报道。',
	'se_fill' => '指定文章的<a href="admin.php?p=entry">ID</a>请填写，填写形式：(<code>entryYYMMDD-HHMMSS</code>).',
	'po_title' => '指定属性',
	'po_desc' => '选择了适用对象「%s」请指定适用策略的报道的属性。',
	'po_comp' => '请在下面指定一个以上。均未指定时，将所有文章作为适用对象。',
	'po_time' => '按天数指定',
	'po_older' => '适用于早于以下日期的条目',
	'days' => '对经过一天以上的报道适用政策。',
	'save_policy' => '保存策略',

	// Delete policies page
	'del_policies' => '删除策略',
	'del_descs' => '尝试删除此策略： ',
	'del_descm' => '您将要删除这些策略： ',
	'sure' => '可以吗？',
	'del_subs' => '是的，请删除',
	'del_subm' => '是的，请把这些全部删除',
	'del_cancel' => '不，请不要删除',

	// Approve comments page
	'app_title' => '评论管理',
	'app_desc' => '批准评论。',
	'app_date' => '日期',
	'app_content' => '评论',
	'app_author' => '发帖人',
	'app_email' => '电子邮件',
	'app_ip' => 'IP地址',
	'app_actions' => '操作',
	'app_publish' => '批准并公开',
	'app_delete' => '删除',
	'app_nocomms' => '没有一个评论',
	'app_pselected' => '批准并发布选定的评论',
	'app_dselected' => '清除选定注释',
	'app_other' => '其他评论',
	'app_akismet' => '垃圾评论',
	'app_spamdesc' => 'Akismet已阻止的评论',
	'app_hamsubmit' => 'Akismet通知非垃圾评论，审核后公开。',
	'app_pubnotham' => 'Akismet不通知非垃圾评论，审核后公开。',

	// Delete comments page
	'delc_title' => '删除评论',
	'delc_descs' => '您将要删除此评论： ',
	'delc_descm' => '您将要删除这些评论： ',

	// Manage comments page
	'man_searcht' => '搜索（管理发布评论）',
	'man_searchd' => '想管理评论的文章的<a href="admin.php?p=entry">ID</a>请填写，填写形式: (<code>entryYYMMDD-HHMMSS</code>).',
	'man_search' => '查找',
	'man_commfor' => '%s 评论',
	'man_spam' => 'Akismet垃圾',

	// The simple edit
	'simple_pre' => '对这篇文章的评论是',
	'simple_1' => '允许',
	'simple_0' => '需要批准。',
	'simple_-1' => '被阻止了。',
	'simple_manage' => '管理这篇文章的评论',
	'simple_edit' => '编辑策略',

	// Akismet warnings
	'akismet_errors' => array(
		-1 => 'Akismet Key栏是空的。请填写。',
		-2 => 'Akismet无法连接到服务。',
		-3 => 'Akismet通讯错误',
		-4 => 'Akismet Key无效。'
	),

	// Messages
	'msgs' => array(
		1 => '已保存设置。',
		-1 => '尝试保存设置时出错。',

		2 => '已保存策略。',
		-2 => '尝试保存策略时出错（请确认是否有错误）。',

		3 => '策略已改变。',
		-3 => '尝试改动策略时出错（或无法改动）。',

		4 => '策略已被删除。',
		-4 => '尝试删除策略时出错（或者没有选择）。',

		5 => '评论被公开了。',
		-5 => '尝试公开评论时出错。',

		6 => '评论被删除了。',
		-6 => '尝试删除评论时出错（或者没有选择）。',

		7 => '评论已发送。',
		-7 => '尝试发送评论时出错。'
	),

	// Errors
	'errors' => array(
		'pol_nonex' => '想要编辑的评论不存在。',
		'entry_nf' => '所选评论不存在.'
	)
);

$lang ['plugin'] ['commentcenter'] = array(
	'akismet_error' => '对不起，发生了技术问题。',
	'akismet_spam'  => '你的评论被认为是垃圾邮件。',
	'lock' => '对这篇文章的评论被阻止了，对不起。',
	'approvation' => '评论被保存了，管理员批准后显示。',

	// Mail for comments
	'mail_subj' => '%s ：要批准的新注释'
);

$lang ['plugin'] ['commentcenter'] ['mail_text'] = '%toname% 先生,

"%fromname%" %frommail% 先生的评论"%entrytitle%"的评论
为了被表示需要你的承认。

以下是投稿的评论:
―――――――――――――――――
%content%
―――――――――――――――――

FlatPress登录的管理区域，确认在评论中心被阻止的评论。
以上，请多关照。

%blogtitle%

';
?>
