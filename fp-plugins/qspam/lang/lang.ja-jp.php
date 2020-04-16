<?php

$lang['plugin']['qspam'] = array(
	'error' => 'エラー: 禁止単語が、含まれていました。'
);

$lang['admin']['plugin']['submenu']['qspam'] = 'QuickSpamFilter';
$lang['admin']['plugin']['qspam'] = array(
	'head' => 'QuickSpamFilterの設定',
	'desc1' => 'これらの禁止単語を含むコメントを許可しません (一行に一語を記述してください) :',
	'desc2' => '<strong>注意:</strong> もし禁止単語が記述の一部であっても、コメントを許可しれません。
	
	(例. "全部" は "完<em>全部</em>分" にもマッチします)',
	'options' => 'その他のオプション',
	'desc3' => '禁止単語の数による制限',
	'desc3pre' => '文中に禁止単語が ',
	'desc3post' => ' 語以上含まれるとき、コメントを許可しない。',
	'submit' => '設定の変更を保存する',
	'msgs' => array(
		1 => '禁止単語の変更を保存しました。',
		-1 => '禁止単語の変更を保存できませんでした。'
	)
);

?>
