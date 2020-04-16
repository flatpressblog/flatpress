<?php

$lang['admin']['plugin']['submenu']['bbcode'] = 'BBCode';
$lang['admin']['plugin']['bbcode'] = array(
	'head' => 'BBCodeの設定',
	'desc1' => 'このプラグインは <a href="http://www.phpbb.com/'.
		'phpBB/faq.php?mode=bbcode">BBCode</a> を使用できるようにし、'.
		'lightbox(プラグインが有効であれば)も自動的に組み込みます。',
	
	'options' => 'オプション',

	'editing'	=> '編集',
	'allow_html'=> 'インラインHTML',
	'allow_html_long' => 'BBCodeと通常HTMLを併用する',
	'toolbar' => 'ツールバー',
	'toolbar_long' => '編集ツールバーを有効にする',

	'other'	=>	'その他のオプション',
	'comments' => 'コメント',
	'comments_long' => 'コメント欄でBBCodeの使用できるようにする',
	'urlmaxlen' => 'URLの最大文字数',
	'urlmaxlen_long_pre' => '何文字以上のとき短縮URL表示に変換するか：',
	'urlmaxlen_long_post'=>' 文字',
	'submit' => '設定の変更を保存する',
	'msgs' => array(
		1 => 'BBCodeの設定変更を保存しました。',
		-1 => 'BBCodeの設定変更が保存されませんでした。'
	),

	'editor' => array(
		'formatting'     => 'Formatting',
		'textarea'       => 'テキストエリア: ',
		'expand'         => '拡大',
		'expandtitle'    => 'テキストエリアの高さを増やします。',
		'reduce'         => '縮小',
		'reducetitle'    => 'テキストエリアの高さを減らします。',
		// note: accesskeys are not internationalized...
		// btw. why not :-D
		'bold'           => 'B',
		'boldtitle'      => 'ボールド体',
		'italic'         => 'I',
		'italictitle'    => 'イタリック体',
		'underline'      => 'U',
		'underlinetitle' => '下線',
		'quote'          => 'Quote',
		'quotetitle'     => '引用文として領域指定',
		'code'           => 'Code',
		'codetitle'      => 'プログラムコードとして領域指定',
		'help'           => 'BBCode Help',
		// currently not used
		'status'         => 'ステータスバー',
		'statusbar'      => 'ノーマルモードです。&lt;Esc&gt;キーで編集モードに切り替えられます。'
	)
);

?>
