<?php
$lang ['admin'] ['static'] ['submenu'] = array(
	'list' => '管理静态页面',
	'write' => '创建静态页面'
);

/* main panel */
$lang ['admin'] ['static'] ['list'] = array(
	'head' => '静态页面',
	'descr' => '请选择要编辑的页面，或<a href="admin.php?p=static&amp;action=write">新建静态页面</a>。',

	'sel' => '选择', // checkbox
	'date' => '日期',
	'name' => '页面名称',
	'title' => '标题',
	'author' => '作者',

	'action' => '选择',
	'act_view' => '查看',
	'act_del' => '删除',
	'act_edit' => '编辑',

	'natural' => '按页面名称降序排列（取消勾选则按创建日期排列）',
	'submit' => '重新排列页面名称'
);

$lang ['admin'] ['static'] ['list'] ['msgs'] = array(
	1 => '页面已保存。',
	-1 => '无法保存页面。',
	2 => '页面已删除。',
	-2 => '无法删除页面。'
);

/* write panel */
$lang ['admin'] ['static'] ['write'] = array(
	'head' => '创建/编辑静态页面',
	'descr' => '编辑表单以发布页面',
	'fieldset1'	=> '编辑',
	'subject' => '标题 (*):',
	'content' => '内容 (*):',
	'fieldset2' => '保存',
	'pagename' => '页面 URL 名称 (*):',
	'submit' => '保存',
	'preview' => '预览',

	'delfset' => '删除',
	'deletemsg' => '删除此页面',
	'del' => '删除',
	'success' => '页面已发布。',
	'otheropts' => '其他选项'
);

$lang ['admin'] ['static'] ['write'] ['error'] = array(
	'subject' => '请填写标题。',
	'content' => '请填写内容。',
	'id' => '必须提供有效的 ID。'
);

/* delete action */	
$lang ['admin'] ['static'] ['delete'] = array(
	'head' => '删除页面', 
	'descr' => '您将要删除以下页面：',
	'preview' => '预览',
	'confirm' => '确定要继续此操作吗？',
	'fset' => '删除',
	'ok' => '是的，删除此页面。',
	'cancel' => '不，返回控制面板。',
	'err' => '指定的页面不存在。'
);
?>
