<?php
$lang ['admin'] ['entry'] ['submenu'] ['commentcenter'] = 'コメントセンター';
$lang ['admin'] ['entry'] ['commentcenter'] = array(
	// Header of the panel
	'title' => 'コメントセンター',
	'desc1' => 'このパネルでは、ブログのコメントを管理できます。',
	'desc2' => 'ここでいくつかのことができます。',

	// Links
	'lpolicies' => 'ポリシーの管理',
	'lapprove' => 'ブロックされたコメントを表示する',
	'lmanage' => 'コメントの管理',
	'lconfig' => 'プラグインの設定',
	'faq_spamcomments' => 'スパムコメントへの対処に関するサポートを受ける',

	// Policies
	'policies' => 'ガイドライン',
	'desc_pol' => 'ここでコメントガイドラインを編集できます。',
	'select' => '選ぶ',
	'criteria' => '基準',
	'behavoir' => '振る舞う',
	'options' => '設定',
	'entry' => 'エントリ',
	'entries' => 'エントリー',
	'categories' => 'カテゴリー',
	'nopolicies' => 'ガイドラインはありません',
	'all_entries' => 'すべてのエントリー',
	'fol_entries' => 'このポリシーは次の投稿に適用されます。',
	'fol_cats' => 'このポリシーは、次のカテゴリの投稿に適用されます。',
	'older' => 'このポリシーは、%d 日より古い投稿に適用されます。',
	'allow' => 'コメントを許可します',
	'block' => 'コメント禁止',
	'approvation' => 'コメントは承認が必要です',
	'up' => '上',
	'down' => '下向き',
	'edit' => '編集',
	'delete' => '消去',
	'newpol' => '新しいポリシーを追加する',
	'del_selected' => '選択したポリシーを削除します。',
	'select_all' => 'すべて選択',
	'deselect_all' => '何も選択しない',

	// Configuration page
	'configure' => 'プラグインの設定',
	'desc_conf' => 'ここでプラグインのオプションを変更できます。',
	'log_all' => 'ブロックされたコメントをログに記録する',
	'log_all_long' => 'ブロックされたコメントもログに記録したい場合は、このオプションを有効にします。',
	'email_alert' => 'メールによる通知',
	'email_alert_long' => '承認のためにコメントをチェックする必要がある場合は、' . 'を電子メールで送ることができる。',
	'akismet' => 'Akismet',
	'akismet_use' => 'Akismet によるコメントチェック',
	'akismet_use_long' => '<a href="https://akismet.com/" target="_blank">Akismet</a>を使えば、コメントのスパムを減らすことができます。',
	'akismet_key' => 'Akismet キー',
	'akismet_key_long' => '<a href="https://akismet.com/signup/" target="_blank">Akismetサービス</a>は、<a class="hint externlink" href="https://akismet.com/support/getting-started/api-key/" target="_blank">キー</a>を提供します。 ここに挿入してください。',
	'akismet_url' => 'Akismet 用ブログURL',
	'akismet_url_long' => '無料の Akismet サービスには、1つのドメインしか使用しないでください。 このフィールドは空欄のままでも構いません。 そして<code>%s</code>が使われる。',
	'save_conf' => '設定の保存',

	// Edit policy page
	'apply_to' => '応募する',
	'editpol' => 'ガイドラインの編集',
	'createpol' => 'ガイドラインの作成',
	'some_entries' => '一定の貢献',
	'properties' => '特定の特性を持つ貢献',
	'se_desc' => 'sオプションを選択した場合、このポリシーに適用したい投稿を追加してください。',
	'se_fill' => 'エントリーの<a href="admin.php?p=entry">ID</a>を記入してください。 (<code>entryYYMMDD-HHMMSS</code>).',
	'po_title' => 'プロパティ',
	'po_desc' => 'オプション %s を選択した場合は、プロパティを入力してください。',
	'po_comp' => 'フィールドは必須ではありませんが、少なくとも1つは記入しなければ、ポリシー がすべての献金に適用されます。',
	'po_time' => '時間設定',
	'po_older' => 'より古いエントリーに適用 ',
	'days' => '日数.',
	'save_policy' => 'ポリシーを保存',

	// Delete policies page
	'del_policies' => 'ガイドラインの削除',
	'del_descs' => 'このポリシーは削除されます： ',
	'del_descm' => 'このガイドラインは削除する： ',
	'sure' => '本当か？',
	'del_subs' => 'はい、削除してください。',
	'del_subm' => 'はい、すべて削除してください。',
	'del_cancel' => 'いや、セッティングに戻る。',

	// Approve comments page
	'app_title' => 'コメントを承認する',
	'app_desc' => 'ここでコメントを承認することができる。',
	'app_date' => '日付',
	'app_content' => 'コメント',
	'app_author' => '著者',
	'app_email' => '電子メール',
	'app_ip' => 'IP',
	'app_actions' => '対策',
	'app_publish' => '出版',
	'app_delete' => '削除',
	'app_nocomms' => 'コメントはない。',
	'app_pselected' => '選択したコメントを公開する',
	'app_dselected' => '選択したコメントを削除する',
	'app_other' => 'その他の発言',
	'app_akismet' => 'スパムとして認識される',
	'app_spamdesc' => 'これらのコメントは Akismet によってブロックされています。',
	'app_hamsubmit' => '公開する際、AkismetにもHamと報告する。',
	'app_pubnotham' => '公開するが、Akismet には転送しない',

	// Delete comments page
	'delc_title' => 'コメント削除',
	'delc_descs' => 'このコメントは削除してください： ',
	'delc_descm' => 'これらのコメントは削除してください： ',

	// Manage comments page
	'man_searcht' => '寄付の検索',
	'man_searchd' => 'コメントを管理したい投稿の <a href="admin.php?p=entry">ID</a> (<code>entryYYMMDD-HHMMSS</code>) を入力してください。',
	'man_search' => '検索',
	'man_commfor' => '備考 %s',
	'man_spam' => 'Akismet にスパムとして報告する',

	// The simple edit
	'simple_pre' => 'このエントリーへのコメント ',
	'simple_1' => '可',
	'simple_0' => 'あなたの承認が必要です。',
	'simple_-1' => 'ブロックされています。',
	'simple_manage' => 'この記事のコメントを管理する',
	'simple_edit' => '編集ガイドライン',

	// Akismet warnings
	'akismet_errors' => array(
		-1 => 'Akismet キーが空です。入力してください。',
		-2 => 'Akismet のサーバーにアクセスできませんでした。',
		-3 => 'Akismet の応答に失敗しました。',
		-4 => 'Akismet のキーが無効です。'
	),

	// Messages
	'msgs' => array(
		1 => '設定が保存された。',
		-1 => '設定の保存中にエラーが発生しました。',

		2 => '政策が救われた。',
		-2 => 'ポリシーの保存中にエラーが発生しました（設定が間違っている可能性があります）。',

		3 => '指令は延期された。',
		-3 => 'ポリシーを移動しようとしてエラーが発生しました (または移動できません)。',

		4 => '指令を削除した。',
		-4 => 'ポリシーを削除しようとしてエラーが発生しました。',

		5 => 'コメントを発表した。',
		-5 => 'コメントを公開しようとしてエラーが発生しました。',

		6 => 'コメントは削除された。',
		-6 => 'コメントを削除しようとしてエラーが発生しました。',

		7 => 'コメントを提出した。',
		-7 => 'コメント送信中にエラーが発生しました。'
	),

	// Errors
	'errors' => array(
		'pol_nonex' => '編集したいポリシーが存在しません。',
		'entry_nf' => '選択された貢献は存在しない。'
	)
);

$lang ['plugin'] ['commentcenter'] = array(
	'akismet_error' => '申し訳ございません。',
	'lock' => '申し訳ありませんが、この投稿にはコメントできません。',
	'approvation' => 'コメントは保存されましたが、表示するには管理者の承認が必要です。',

	// Mail for comments
	'mail_subj' => '新規承認コメント %s'
);

$lang ['plugin'] ['commentcenter'] ['mail_text'] = 'こんにちは %toname%,

"%fromname%" %frommail% はコメントを受け付けていません "%entrytitle%"
しかし、これは掲載前にあなたの承認が必要だ。

以下はコメントとして書いたものである：
___________________________________
%content%
___________________________________

FlatPressブログの管理エリアにログインし、コメントセンターでブロックされているコメントを確認してください。

によって自動的に生成される
%blogtitle%

';
?>
