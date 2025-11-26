<?php
$lang ['admin'] ['plugin'] ['submenu'] ['bbcode'] = 'BBCode';
$lang ['admin'] ['plugin'] ['bbcode'] = array(
	'head' => 'BBCodeの設定',
	'desc1' => 'このプラグインは <a href="https://wiki.flatpress.org/' . //
		'doc:techfaq#bbcode" class="hint" target="_blank">BBCode</a> を使用できるようにします。',

	'options' => 'オプション',

	'editing' => '編集',
	'allow_html' => 'インラインHTML',
	'allow_html_long' => 'BBCodeと通常HTMLを併用する',
	'toolbar' => 'ツールバー',
	'toolbar_long' => '編集ツールバーを有効にする',

	'other' => 'その他のオプション',
	'comments' => 'コメント',
	'comments_long' => 'コメント欄でBBCodeの使用できるようにする',
	'urlmaxlen' => 'URLの最大文字数',
	'urlmaxlen_long_pre' => '何文字以上のとき短縮URL表示に変換するか：',
	'urlmaxlen_long_post' => ' 文字',

	'attachsdir' => 'ファイルのダウンロード',
	'attachsdir_long' => 'URLにアップロードディレクトリ(fp-content/attachs/)を表示しない。',

	'submit' => '設定の変更を保存する',
	'msgs' => array(
		1 => 'BBCodeの設定変更を保存しました。',
		-1 => 'BBCodeの設定変更が保存されませんでした。'
	),

	'editor' => array(
		'formatting' => 'Formatting',
		'textarea' => 'テキストエリア: ',
		'expand' => '拡大',
		'expandtitle' => 'テキストエリアの高さを増やします。',
		'reduce' => '縮小',
		'reducetitle' => 'テキストエリアの高さを減らします。',
		'urltitle' => 'URL/リンク',
		'mailtitle' => 'Eメールアドレス',
		'boldtitle' => 'ボールド体',
		'italictitle' => 'イタリック体',
		'headlinetitle' => '見出し',
		'fonttitle' => 'フォント',
		'underlinetitle' => '下線',
		'crossouttitle' => '取り消し線',
		'unorderedlisttitle' => '順序なしリスト',
		'orderedlisttitle' => '順序付きリスト',
		'quotetitle' => '引用文として領域指定',
		'codetitle' => 'プログラムコードとして領域指定',
		'htmltitle' => 'HTMLコードとして挿入',
		'help' => 'BBCode ヘルプ',
		'file' => 'ファイル: ',
		'image' => '画像: ',
		'gallery' => 'ギャラリー: ',
		'selection' => '-- 選択して挿入 --'
	)
);

$lang ['plugin'] ['bbcode'] = array (
	'go_to' => '移動先: ',

	// Filewrapper get.php
	'error_403' => 'エラー 403',
	'not_send' => '要求されたファイルは送信できません。',
	'error_404' => 'エラー 404',
	'not_found' => '要求されたファイルが見つかりません。',
	'file' => 'ファイル',
	'report_error_1' => '',
	'report_error_2' => 'エラーを報告してください',
	'blog_search_1' => '',
	'blog_search_2' => 'ブログ検索',
	'start_page_1' => '',
	'start_page_2' => 'トップページに戻る'
);
?>
