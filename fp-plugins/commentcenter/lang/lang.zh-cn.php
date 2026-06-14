<?php
$lang ['admin'] ['entry'] ['submenu'] ['commentcenter'] = '评论中心';
$lang ['admin'] ['entry'] ['commentcenter'] = array(
	// Header of the panel
	'title' => '评论中心',
	 'desc1' => '此面板用于管理博客文章的评论。',
	 'desc2' => '您可以：',

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
	 'entry' => '文章',
	 'entries' => '文章',
	'categories' => '文章类别',
	 'nopolicies' => '没有策略',
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
	 'newpol' => '添加新策略',
	'del_selected' => '删除选定的评论',
	'select_all' => '选择全部',
	'deselect_all' => '取消选择所有项',

	// Configuration page
	'configure' => '设置评论中心',
	'desc_conf' => '在此设定评论中心的选项。',
	 'log_all' => '记录被阻止的评论',
	 'log_all_long' => '勾选后会记录被阻止的评论。',
	 'email_alert' => '通过电子邮件通知评论',
	 'email_alert_long' => '勾选后，当有评论需要审核时会通过电子邮件通知。',
	'akismet' => 'Akismet',
	'akismet_use' => '启用Akismet检查',
	 'akismet_use_long' => '使用 <a href="https://akismet.com/" target="_blank">Akismet</a> 可以减少垃圾评论。',
	 'akismet_key' => 'Akismet 密钥',
	 'akismet_key_long' => '请在这里填写 <a href="https://akismet.com/signup/" target="_blank">Akismet 服务</a>提供的 <a class="hint externlink" href="https://akismet.com/support/getting-started/api-key/" target="_blank">Akismet 密钥</a>。',
	 'akismet_url' => 'Akismet 检查的博客基础 URL',
	 'akismet_url_long' => 'Akismet 免费服务通常只能用于一个域名。如果留空，将使用 <code>%s</code>。',
	'save_conf' => '保存设置',

	// Edit policy page
	 'apply_to' => '应用到',
	'editpol' => '编辑策略',
	'createpol' => '创建策略',
	'some_entries' => '指定的文章',
	'properties' => '具有指定属性的文章',
	 'se_desc' => '选择适用对象“%s”时，请用 ID 指定要应用此策略的文章。',
	 'se_fill' => '请填写指定文章的 <a href="admin.php?p=entry">ID</a>，格式为：<code>entryYYMMDD-HHMMSS</code>。',
	'po_title' => '指定属性',
	 'po_desc' => '选择适用对象“%s”时，请指定要应用此策略的文章属性。',
	 'po_comp' => '请在下面至少指定一项。如果均未指定，则策略将应用于所有文章。',
	'po_time' => '按天数指定',
	 'po_older' => '适用于早于以下日期的文章',
	 'days' => '将策略应用于超过指定天数的文章。',
	'save_policy' => '保存策略',

	// Delete policies page
	'del_policies' => '删除策略',
	'del_descs' => '尝试删除此策略： ',
	'del_descm' => '您将要删除这些策略： ',
	 'sure' => '确定吗？',
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
	 'app_nocomms' => '没有评论',
	'app_pselected' => '批准并发布选定的评论',
	 'app_dselected' => '删除选定的评论',
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
	 'man_searchd' => '请填写要管理评论的文章 <a href="admin.php?p=entry">ID</a>，格式为：<code>entryYYMMDD-HHMMSS</code>。',
	'man_search' => '查找',
	 'man_commfor' => '%s 的评论',
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
		 -1 => 'Akismet 密钥栏为空，请填写。',
		-2 => 'Akismet无法连接到服务。',
		-3 => 'Akismet通讯错误',
		 -4 => 'Akismet 密钥无效。'
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
	 'mail_subj' => '%s：有新的评论需要审核'
);

$lang ['plugin'] ['commentcenter'] ['mail_text'] = '%toname%：

"%fromname%" <%frommail%> 在文章 "%entrytitle%" 中提交了一条评论，
该评论需要您的审核。

评论内容如下：
―――――――――――――――――
%content%
―――――――――――――――――

请登录 FlatPress 管理区域，并在评论中心中审核被拦截的评论。

%blogtitle%

';
?>
