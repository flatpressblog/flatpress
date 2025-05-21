<?php


	$lang['admin']['entry']['submenu'] = 
	array (
		'list'		=> '記事の管理',
		'write'		=> '記事の作成',
		'cats'		=> '記事カテゴリの管理'
	);


	/* default action */
	
	$lang['admin']['entry']['list'] = 
	array(
		'head'		=> '記事の管理',
		'descr'		=> '編集する記事の作業を選んでください。<a href="admin.php?p=entry&amp;action=write">記事の新規作成は、ここをクリック</a>します。'.
					'あるいは<a href="admin.php?p=entry&amp;action=cats">カテゴリの編集</a>へ移動します。',
		'filter'	=> 'カテゴリフィルタ(カテゴリ名で記事をしぼり込むことができます): ',
		'nofilter'	=> 'すべてを表示',
		'filterbtn'	=> 'フィルタを適用する',
		'sel'		=> 'Sel', // checkbox
		'date'		=> '作成日時',
		'title'		=> '題名',
		'author'	=> '作成者',
		'comms'		=> 'コメント数', // comments
		'action'	=> '作業を選んでください',
		'act_del'	=> '削除',
		'act_view'	=> '表示',
		'act_edit'	=> '編集'
	);
	
	/* write action */
	$lang['admin']['entry']['write'] = 
	array(
		'head'		=> '記事の作成／編集',
		'descr'		=> 'Edit the form to write the entry',
		'uploader'	=> 'アップローダー',
		'fieldset1'	=> '編集',
		'subject'	=> '題名 (*):',
		'content'	=> '内容 (*):',
		'fieldset2'	=> 'Submit',
		'submit'	=> '保存する',
		'preview'	=> 'プレビュー',
		'savecontinue'	=> '保存して継続',
		'categories'	=> 'カテゴリ',
		'nocategories'	=> 'カテゴリは未作成です。 <a href="admin.php?p=entry&amp;action=cats">カテゴリの管理'. 
					'</a>ページに移動して作成してください。 '.
					'必要なら、まず<a href="#save">保存</a>してください。',
		'saveopts'	=> '保存オプション',
		'success'	=> '記事は公開されました。',
		'otheropts'	=> 'その他のオプション',
		'commmsg'	=> 'この記事のコメントを管理します。',
		'delmsg'	=> 'この記事を削除します。',
		//'back'		=> 'Back discarding changes',
	);
	

	$lang['admin']['entry']['list']['msgs'] = array(
		1	=> '記事は保存されました。',
		-1	=> '記事を保存できませんでした。',
		2	=> '記事を削除しました。',
		-2	=> '記事を削除できませんでした。',
	);

	
	$lang['admin']['entry']['write']['error'] = array(
		'subject'	=> '題名を記入してください。',
		'content'	=> '記事の内容を記入してください。',
	);
	
	$lang['admin']['entry']['write']['msgs'] = array(
		1	=> '記事は保存されました。',
		-1	=> '記事を保存できませんでした。',
		-2	=> 'An error occurred: your entry has not been saved; index might have become corrupt',
		-3	=> '下書きとして保存されました。',
		-4	=> 'An error occurred: your entry has been saved as draft; index might have become corrupt',
		'draft'=> '<strong>下書き</strong>の記事を編集しています。'
	);

	
	/* comments */
	
	$lang['admin']['entry']['commentlist'] = 
	array(
		'head'		=> "記事へのコメント一覧", 
		'descr'		=> '削除したいコメントを選んでください。',
		'sel'		=> 'Sel',
		'content'	=> '内容',
		'date'		=> '日付',
		'author'	=> '著者',
		'email'		=> 'メールアドレス',
		'ip'		=> 'IPアドレス',
		'actions'	=> 'Actions',
		'act_edit'	=> '編集',
		'act_del'	=> '削除',
		'act_del_confirm' => '本当にこのコメントを削除しますか?',
		'nocomments'	=> 'この記事には、まだコメントがありません。',
		

	);

	$lang['admin']['entry']['commentlist']['msgs'] =
	array(
		1	=> 'コメントが削除されました。',
		-1	=> 'コメントを削除できませんでした。',
		
	);

	$lang['admin']['entry']['commedit'] = 
	array(
		'head'		=> "コメントを編集します", 
		'content'	=> '内容',
		'date'		=> '日付',
		'author'	=> '著者',
		'www'		=> 'webサイト',
		'email'		=> 'メールアドレス',
		'ip'		=> 'IPアドレス',
		'loggedin'	=> '登録済みユーザ',
		'submit'	=> '保存します'
		
	
	);

	$lang['admin']['entry']['commedit']['msgs'] =
	array(
		1	=> 'コメント編集を完了しました。',
		-1	=> 'コメント編集ができませんでした。',
	);
	
	/* delete action */
	
	$lang['admin']['entry']['delete'] = 
	array(
		'head'		=> '記事の削除', 
		'descr'		=> '次の記事を削除しようとしています:',
		'preview'	=> 'プレビュー',
		'confirm'	=> 'この作業を続行しますか?',
		'fset'		=> '削除',
		'ok'		=> 'はい、この記事を削除します。',
		'cancel'	=> 'いいえ、管理者用ページへ戻ります。',
		'err'		=> '指定された記事は存在しません。',
	
	);
	
	/* category mgmt */
	
	$lang['admin']['entry']['cats'] =
	array(
		'head'		=> '記事カテゴリの編集',
		'descr'		=> '<p>カテゴリの追加・編集は、次のフォームで行ないます。 </p><p>それぞれのカテゴリ項目は、"カテゴリ名: <em>ID番号</em>"という形式で指定します。 ハイフンでインデントすることで、階層を作ることができます。</p>
		
	<p>指定例:</p>
	<pre>
一般 :1
ニュース :2
--お知らせ :3
--イベント :4
----その他 :5
技術情報 :6
	</pre>',
		'clear'		=> 'すべて消去する',
	
		'fset1'		=> 'Editor',
		'fset2'		=> '変更を反映します',
		'submit'	=> '保存します'
	);
	
	$lang['admin']['entry']['cats']['msgs'] = array(
		
		1	=> 'カテゴリデータを保存しました。',
		-1	=> 'カテゴリデータを保存できませんでした。',
		2	=> 'カテゴリデータは消去されました。',
		-2	=> 'カテゴリデータの消去ができませんでした。',
		-3 	=> 'Category IDs must be strictly positive (0 is not allowed)'

	);
	
	
		
?>
