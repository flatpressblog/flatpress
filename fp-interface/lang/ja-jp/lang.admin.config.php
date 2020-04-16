<?php

	$lang['admin']['config']['default'] = 
	array(
		'head'		=> '設定',
		'descr'		=> 'FlatPressインストールのカスタマイズと設定',
		'submit'		=> '変更を保存する',
		
		'sysfset'		=> '一般設定',
		'syswarning'	=> '<big>警告!</big> この設定には慎重さと正確さが必要です。さもないとFlatPressの動作に(おそらく)支障が出るでしょう。',
		'blog_root'		=> '<strong>flatpressへの絶対パス</strong> Note: 
	普通は、これを編集する必要がないでしょう。flatpressは正しいかどうかチェックできませんから注意深く編集してください。',
		'www'		=>'<strong>ブログのURL</strong>. サブディレクトリで終わるブログのURL <br />
	例: http://www.mydomain.com/flatpress/ (末尾のスラッシュが必要)',
		
		// ------
		
		'gensetts'		=> '全般の設定',
		'blogtitle'		=> 'サイトのタイトル',
		'blogsubtitle'		=> 'サイトのサブタイトル',
		'blogfooter'		=> 'フッター欄',
		'blogauthor'		=> '管理者名',
		'startpage'			=> 'サイトのトップ(優先表示する)ページ',
		'stdstartpage'		=> '記事リスト(ブログ)の1ページめ(初期設定)',
		'blogurl'			=> 'サイトのURL',
		'blogemail'			=> '管理者のメールアドレス',
		'notifications'		=> '通知設定',
		'mailnotify'		=> 'コメントがつくとメールで通知する。',
		'blogmaxentries'	=> 'ブログの1ページに表示する記事数',
		'langchoice'		=> '言語の選択',

		'intsetts'		=> 'ローカルの設定',
		'utctime'		=> '<acronym title="Universal Coordinated Time">UTC</acronym>の時刻：',
		'timeoffset'		=> '投稿時に加算する時間',
		'hours'			=> '時間',
		'timeformat'		=> '時刻表示のデフォルト形式',
		'dateformat'		=> '日付表示のデフォルト形式',
		'dateformatshort'	=> '日付短縮表示のデフォルト形式',
		'output'		=> '現在の設定での表示例',
		'charset'		=> '使用する文字コード',
		'charsettip'	=> '(使用する文字コードは、utf-8を '.
						'<a href="http://wiki.flatpress.org/doc:charsets">推奨します</a>。)'
		);
		
	$lang['admin']['config']['default']['msgs'] = 
	array(
		1		=> '変更された設定を保存しました。',
		-1		=> '設定を保存できませんでした。',
		
		);
			
	$lang['admin']['config']['default']['error'] = 
	array(
		'www' 		=>	'サイトのURLが有効ではないようです。',
		'title'		=>	'タイトルを記入してください。',
		'email'		=>	'正しくメールアドレスを記入してください。',
		'maxentries'=>	'記事数は半角数字で正しく入力してください。',
		'timeoffset'=>	'有効な時差を半角数字で入力してください! '.
					'なお小数を使用できます。 (例 2時間30分 => 2.5)',
		'timeformat'=>	'時刻表示用の表記で指定してください。',
		'dateformat'=>	'日付表示用の形式で指定してください。',
		'dateformatshort'=>	'日付短縮表示用の形式を使用してください。',
		'charset'	=>	'You must insert a charset id.(文字コード名を正確に記入してください)',
		'lang'		=>	'The language you chose is not available.(選択言語は使用不可)'
		);		
			
		
?>
