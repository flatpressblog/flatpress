<?php
$lang ['plugin'] ['qspam'] = array(
	'error' => '错误：包含禁用词。'
);

$lang ['admin'] ['entry'] ['submenu'] ['qspam'] = 'QuickSpamFilter';
$lang ['admin'] ['entry'] ['qspam'] = array(
	'head' => 'QuickSpamFilter 设置',
	'desc1' => '不允许包含以下禁用词的评论（每行一个词）：',
	'desc2' => '<strong>注意：</strong>如果禁用词只是某个词的一部分，评论也会被阻止。（例如：“全部”也会匹配“完全部”）',
	'options' => '其他选项',
	'desc3' => '禁用词数量限制',
	'desc3pre' => '评论中包含禁用词达到 ',
	'desc3post' => ' 个以上时，不允许提交评论。',
	'submit' => '保存设置更改',
	'msgs' => array(
		1 => '禁用词更改已保存。',
		-1 => '无法保存禁用词更改。'
	)
);
?>
