<?php
$lang ['admin'] ['entry'] ['submenu'] ['commentcenter'] = 'コメントセンター';
$lang ['admin'] ['entry'] ['commentcenter'] = array(
	// Header of the panel
	'title' => 'コメントセンター',
	'desc1' => 'このパネルでは,ブログ記事へのコメントを管理します.',
	'desc2' => '次のようなことができます:',

	// Links
	'lpolicies' => 'ポリシーの管理',
	'lapprove' => '承認待ち(ブロック中)のコメントの操作',
	'lmanage' => '公開中のコメントの管理',
	'lconfig' => 'コメントセンターの設定',
	'faq_spamcomments' => 'スパムコメントへの対処に関するよくある質問(FAQ)ページを開く',

	// Policies
	'policies' => 'ポリシーの管理',
	'desc_pol' => 'ここではコメントポリシーの編集等ができます.',
	'select' => '選択',
	'criteria' => '対象となる記事',
	'behavoir' => '動作の指定',
	'options' => 'オプション',
	'entry' => '記事',
	'entries' => '記事',
	'categories' => '記事カテゴリー',
	'nopolicies' => 'ポリシーがひとつもありません',
	'all_entries' => 'すべての記事',
	'fol_entries' => '次の記事に対してポリシーを適用します:',
	'fol_cats' => '次の記事カテゴリに対してポリシーを適用します:',
	'older' => '%d 日以上経過した記事に対してポリシーを適用します.',
	'allow' => 'コメントを許可します',
	'block' => 'コメントをブロックします',
	'approvation' => 'コメントに承認を必要とします',
	'up' => '上へ移動する',
	'down' => '下へ移動する',
	'edit' => '編集する',
	'delete' => '消去する',
	'newpol' => '新しいポリシーを追加します',
	'del_selected' => '選択したポリシーを消去します',
	'select_all' => 'すべてを選択します',
	'deselect_all' => 'すべての選択を解除します',

	// Configuration page
	'configure' => 'コメントセンターの設定',
	'desc_conf' => 'ここではコメントセンターのオプションを設定します.',
	'log_all' => 'ブロックしたコメントを記録',
	'log_all_long' => 'ブロックしたコメントを記録するにはチェックを入れます.',
	'email_alert' => 'emailでコメントを通知',
	'email_alert_long' => '承認が必要なコメントが投稿されたことをemailで通知させるにはチェックを入れます.',
	'akismet' => 'Akismet',
	'akismet_use' => 'Akismetチェックを有効化',
	'akismet_use_long' => '<a href="https://akismet.com/" target="_blank">Akismet</a>を使えば、コメントのスパムを減らすことができます。',
	'akismet_key' => 'Akismet Key',
	'akismet_key_long' => '<a href="https://akismet.com/signup/" target="_blank">Akismetサービス</a>から提供された<a class="hint externlink" href="https://akismet.com/support/getting-started/api-key/" target="_blank">Akismet Key</a>をここに記入してください.',
	'akismet_url' => 'Akismetチェックの対象ブログのベースURL',
	'akismet_url_long' => 'Akismetの無料サービスでは,1つのドメイン名だけで利用できるようです. 空欄のままにすれば, <code>%s</code> が使用されるでしょう.',
	'save_conf' => '設定を保存します',

	// Edit policy page
	'apply_to' => '適用の対象',
	'editpol' => 'ポリシーの編集',
	'createpol' => 'ポリシーの作成',
	'some_entries' => '指定する記事',
	'properties' => '指定するプロパティを持つ記事',
	'se_desc' => '適用の対象に「%s」を選びました, ポリシーを適用する記事をIDで指定してください.',
	'se_fill' => '指定する記事の<a href="admin.php?p=entry">ID</a>を記入してください, 記入形式: (<code>entryYYMMDD-HHMMSS</code>).',
	'po_title' => 'プロパティの指定',
	'po_desc' => '適用の対象に「%s」を選びました, ポリシーを適用する記事のプロパティを指定してください.',
	'po_comp' => '以下で1つ以上を指定してください. いずれも指定しない場合すべての記事を適用の対象とします.',
	'po_time' => '日数で指定',
	'po_older' => '',
	'days' => '日以上経過した記事に対してポリシーを適用します.',
	'save_policy' => 'ポリシーを保存します',

	// Delete policies page
	'del_policies' => 'ポリシーの削除',
	'del_descs' => 'このポリシーを削除しようとしてます： ',
	'del_descm' => 'これらのポリシーを削除しようとしてます： ',
	'sure' => 'いいですか?',
	'del_subs' => 'はい, 削除してください',
	'del_subm' => 'はい, それらすべてを削除してください',
	'del_cancel' => 'いいえ, 削除せずにもどってください',

	// Approve comments page
	'app_title' => '承認待ち(ブロック中)のコメントの操作',
	'app_desc' => 'コメントの承認をします.',
	'app_date' => '日付',
	'app_content' => 'コメント',
	'app_author' => '投稿者',
	'app_email' => 'Email',
	'app_ip' => 'IPアドレス',
	'app_actions' => '操作',
	'app_publish' => '承認して公開します',
	'app_delete' => '削除します',
	'app_nocomms' => 'コメントがひとつもありません.',
	'app_pselected' => '選択したコメントを承認して公開します',
	'app_dselected' => '選択したコメントを消去します',
	'app_other' => 'その他のコメント',
	'app_akismet' => 'スパムの印',
	'app_spamdesc' => 'Akismetでブロックされたコメント',
	'app_hamsubmit' => 'Akismetに非スパムと通知し, 承認して公開します.',
	'app_pubnotham' => 'Akismetに非スパムと通知せずに, 承認だけして公開します.',

	// Delete comments page
	'delc_title' => 'コメント削除',
	'delc_descs' => 'このコメントを削除しようとしています： ',
	'delc_descm' => 'これらのコメントを削除しようとしています： ',

	// Manage comments page
	'man_searcht' => '記事を検索(公開中のコメントの管理)',
	'man_searchd' => 'コメントを管理したい記事の<a href="admin.php?p=entry">ID</a>を記入してください, 記入形式: (<code>entryYYMMDD-HHMMSS</code>).',
	'man_search' => '検索します',
	'man_commfor' => '%s のコメント',
	'man_spam' => 'Akismetにスパムとして提出します',

	// The simple edit
	'simple_pre' => 'この記事へのコメントは',
	'simple_1' => '許可されてます',
	'simple_0' => '承認が必要です。',
	'simple_-1' => 'ブロックされてます。',
	'simple_manage' => 'この記事のコメントを管理します',
	'simple_edit' => 'ポリシーを編集します',

	// Akismet warnings
	'akismet_errors' => array(
		-1 => 'Akismet Key欄が空欄です. 記入してください.',
		-2 => 'Akismetサービスに接続できません.',
		-3 => 'Akismetからの返答がありません.',
		-4 => 'Akismet Keyが無効です.'
	),

	// Messages
	'msgs' => array(
		1 => '設定を保存しました.',
		-1 => '設定を保存しようとしましたがエラーがありました.',

		2 => 'ポリシーを保存しました.',
		-2 => 'ポリシーを保存しようとしましたがエラーがありました(誤りがないか確認して下さい).',

		3 => 'ポリシーは移動しました.',
		-3 => 'ポリシーを移動しようとしましたがエラーがありました(あるいは移動不可です).',

		4 => 'ポリシーは削除されました.',
		-4 => 'ポリシーを削除しようとしましたがエラーがありました(あるいはいずれも選択されてませんでした).',

		5 => 'コメントは公開されました.',
		-5 => 'コメントを公開しようとしましたがエラーがありました.',

		6 => 'コメントは削除されました.',
		-6 => 'コメントを削除しようとしましたがエラーがありました(あるいはいずれも選択されてませんでした).',

		7 => 'コメントは送信されました.',
		-7 => 'コメントを送信しようとしましたがエラーがありました.'
	),

	// Errors
	'errors' => array(
		'pol_nonex' => '編集しようとしたポリシーは存在しません.',
		'entry_nf' => '選択した記事は存在しません.'
	)
);

$lang ['plugin'] ['commentcenter'] = array(
	'akismet_error' => 'ごめんなさい,技術的な問題が発生してます.',
	'lock' => 'この記事へのコメントはブロックされてます,ごめんなさい.',
	'approvation' => 'コメントは保存されました, 管理者が承認すると表示されます.',

	// Mail for comments
	'mail_subj' => '%s ：承認する新しいコメント'
);

$lang ['plugin'] ['commentcenter'] ['mail_text'] = '%toname% さん,

"%fromname%" %frommail% さんが 記事"%entrytitle%"にコメントを投稿しましたが
表示されるためにはあなたの承認が必要です.

以下が投稿されたコメントです:
―――――――――――――――――
%content%
―――――――――――――――――

FlatPressの管理エリアにログインし, コメントセンターでブロックされているコメントを確認してください.
以上, よろしくお願いします.

%blogtitle%

';
?>
