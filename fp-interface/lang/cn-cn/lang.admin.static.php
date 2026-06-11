<?php
$lang ['admin'] ['static'] ['submenu'] = array(
	'list' => '管理菜单栏',
	'write' => '创建菜单栏'
);

/* main panel */
$lang ['admin'] ['static'] ['list'] = array(
	'head' => '菜单栏',
	'descr' => '请选择要编辑的菜单。<a href=“admin.php？p=static&amp；action=write”>单击此处新建菜单。',

	'sel' => '选择', // checkbox
	'date' => '日期',
	'name' => '菜单名称',
	'title' => '标题',
	'author' => '作者',

	'action' => '选择',
	'act_view' => '查看',
	'act_del' => '删除',
	'act_edit' => '编辑',

	'natural' => '按“菜单名称”的降序排列（取消勾选为创建日期顺序）',
	'submit' => '重新排列名称'
);

$lang ['admin'] ['static'] ['list'] ['msgs'] = array(
	1 => '已保存菜单。',
	-1 => '无法保存菜单。',
	2 => '已删除菜单。',
	-2 => '无法删除菜单。'
);

/* write panel */
$lang ['admin'] ['static'] ['write'] = array(
	'head' => '创建/编辑菜单',
	'descr' => '编辑菜单发布页面',
	'fieldset1'	=> '编辑',
	'subject' => '标题 (*):',
	'content' => '内容 (*):',
	'fieldset2' => '保存',
	'pagename' => 'url菜单名 (*):',
	'submit' => '保存',
	'preview' => '预览',

	'delfset' => '删除',
	'deletemsg' => '删除菜单',
	'del' => '删除',
	'success' => '已发布菜单页面。',
	'otheropts' => '其他选项'
);

$lang ['admin'] ['static'] ['write'] ['error'] = array(
	'subject' => '请填写题目。',
	'content' => '请填写内容。',
	'id' => '您必须发送有效的id'
);

/* delete action */	
$lang ['admin'] ['static'] ['delete'] = array(
	'head' => '删除菜单', 
	'descr' => '您将要删除下一页:',
	'preview' => '预览',
	'confirm' => '是否继续此操作?',
	'fset' => '删除',
	'ok' => '好的，删除此菜单。',
	'cancel' => '不，返回控制面板。',
	'err' => '指定的菜单不存在。'
);
?>
