<?php
$lang ['admin'] ['entry'] ['submenu'] = array(
	'list' => '文章管理',
	'write' => '创建文章',
	'cats' => '管理文章类别'
);

/* default action */
$lang ['admin'] ['entry'] ['list'] = array(
	'head' => '文章管理',
	'descr' => '请选择要编辑的文章，或<a href="admin.php?p=entry&amp;action=write">新建文章</a>。' . //
		'您也可以<a href="admin.php?p=entry&amp;action=cats">编辑类别</a>。',
	'drafts' => '草稿: ',
	'filter' => '类别筛选器（可按类别缩小文章范围）：',
	'nofilter' => '显示全部',
	'filterbtn' => '应用筛选器',
	'sel' => '选择', // checkbox
	'date' => '创建时间',
	'title' => '标题',
	'author' => '作者',
	'comms' => '评论数', // comments
	'action' => '请选择操作',
	'act_del' => '删除',
	'act_view' => '查看',
	'act_edit' => '编辑',
	'perpage_show' => '显示',
	'perpage_entries' => '文章/页面'
);

/* write action */
$lang ['admin'] ['entry'] ['write'] = array(
	'head' => '创建/编辑文章',
	'descr' => '编辑表单以撰写文章',
	'uploader' => '上传器',
	'fieldset1' => '编辑',
	'subject' => '标题 (*):',
	'content' => '内容 (*):',
	'fieldset2' => '提交',
	'submit' => '保存',
	'preview' => '预览',
	'savecontinue' => '保存并继续',
	'categories' => '类别',
	'nocategories' => '尚未创建类别。请前往<a href="admin.php?p=entry&amp;action=cats">管理类别</a>页面创建。' . //
		'如有需要，请先<a href="#save">保存</a>当前文章。',
	'saveopts' => '保存选项',
	'success' => '文章已发布。',
	'otheropts' => '其他选项',
	'commmsg' => '管理这篇文章的评论。',
	'delmsg' => '删除此文章。'
	// 'back' => '返回并放弃更改',
);

$lang ['admin'] ['entry'] ['list'] ['msgs'] = array(
	1 => '文章已保存。',
	-1 => '无法保存文章。',
	2 => '已删除文章。',
	-2 => '无法删除文章。'
);

$lang ['admin'] ['entry'] ['write'] ['error'] = array(
	'subject' => '请填写标题。',
	'content' => '请填写内容。'
);

$lang ['admin'] ['entry'] ['write'] ['msgs'] = array(
	1 => '文章已保存。',
	-1 => '无法保存文章。',
	-2 => '出现错误：您的输入尚未保存；索引可能已损坏',
	-3 => '已保存为草稿。',
	-4 => '出现错误：您的条目已另存为草稿；索引可能已损坏',
	'draft' => '编辑草稿。'
);

/* comments */
$lang ['admin'] ['entry'] ['commentlist'] = array(
	'head' => '文章评论列表: ',
	'descr' => '请选择想要编辑/删除的评论。',
	'sel' => '选择',
	'content' => '内容',
	'date' => '日期',
	'author' => '填写者',
	'email' => '电子邮件地址',
	'ip' => 'IP地址',
	'actions' => '操作',
	'act_edit' => '编辑',
	'act_del' => '删除',
	'act_del_confirm' => '您确定要删除此评论吗?',
	'nocomments' => '这篇文章还没有评论。'
);

$lang ['admin'] ['entry'] ['commentlist'] ['msgs'] = array(
	1 => '评论已删除。',
	-1 => '无法删除评论。'
);

$lang ['admin'] ['entry'] ['commedit'] = array(
	'head' => '编辑评论: ',
	'descr' => '在这里可以编辑评论者姓名、电子邮件地址和网站 URL。<br><br>',
	'content' => '内容',
	'date' => '日期',
	'author' => '评论者',
	'www' => '网站',
	'email' => '电子邮件地址',
	'ip' => 'IP地址',
	'loggedin' => '管理员填写',
	'submit' => '保存',
	'commentlist' => '返回到评论列表'
);

$lang ['admin'] ['entry'] ['commedit'] ['error'] = array(
	'name' => '请填写名字。',
	'email' => '请正确填写邮件地址。',
	'url' => '请确认网站 URL 是否正确，应以 <strong>http://</strong> 或 <strong>https://</strong> 开头。',
	'content' => '无评论。'
);

$lang ['admin'] ['entry'] ['commedit'] ['msgs'] = array(
	1 => '已完成评论编辑。',
	-1 => '无法编辑评论。'
);

/* delete action */
$lang ['admin'] ['entry'] ['delete'] = array(
	'head' => '删除文章',
	'descr' => '您将要删除下一篇文章: ',
	'preview' => '预览',
	'confirm' => '确定要继续此操作吗？',
	'fset' => '删除',
	'ok' => '是的，删除这篇文章。',
	'cancel' => '否，返回管理员页面。',
	'err' => '指定的文章不存在。'
);

/* category mgmt */
$lang ['admin'] ['entry'] ['cats'] = array(
	'head' => '编辑文章类别',
	'descr' => '<p>通过以下表单添加、编辑类别：。各个类别项目以“类别名称：ID编号”的形式指定。用连字符缩进可以创建层次。</p>
	<p>指定例:</p>
	<pre>
---一般 :1
---新闻 :2
---通知 :3
---活动 :4
---其他 :5
技术信息 :6
	</pre>',
	'clear' => '全部清除',
	'fset1' => '编辑',
	'fset2' => '反映更改',
	'submit' => '保存'
);

$lang ['admin'] ['entry'] ['cats'] ['msgs'] = array(
	1 => '已保存类别数据。',
	-1 => '无法保存类别数据。',
	2 => '类别数据已清除。',
	-2 => '无法清除类别数据。',
	-3 => '类别ID必须严格为正（不允许为0）'
);
?>
