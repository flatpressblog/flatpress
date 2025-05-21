<?php
	$lang['plugin']['prettyurls']['errors'] = array (
		-2	=> 'ルートディレクトリに<code>.htaccess</code>ファイルが見つけられないか、作成することができません。'.
				'PrettyURLsは適切に動作できないかもしれません。PrettyURLsの設定を見てください。'
	);
	
	$lang['admin']['plugin']['submenu']['prettyurls'] = 'PrettyURLsの設定';
	$lang['admin']['plugin']['prettyurls'] = array(
		'head'		=> 'PrettyURLsの設定',
		'htaccess'	=> '.htaccess',
		'description'=>'This raw editor let you edit your '.
						'<code>.htaccess</code>.',
		'cantsave'	=> 'このファイルを編集できません、なぜなら <strong>書き込み許可</strong>されてないからです。'.
						'You can give writing permission or copy and paste to a file and then upload.',
		'mode'		=> 'モード',
		'auto'		=> '自動',
			'autodescr'	=> '最良の選択を推定します。',
		'pathinfo'	=> 'Path Info',
			'pathinfodescr' => '例. /index.php/2011/01/01/hello-world/',
		'httpget'	=> 'HTTP Get',
			'httpgetdescr'=> '例. /?u=/2011/01/01/hello-world/',
		'pretty'	=> 'Pretty',
			'prettydescr'=> '例. /2011/01/01/hello-world/',

		'saveopt' 	=> '設定の変更を保存する',

		'submit'	=> '.htaccess を保存する'
	);
	$lang['admin']['plugin']['prettyurls']['msgs'] = array(
		1		=> '.htaccess を保存しました。',
		-1		=> '.htaccess を保存できませんでした。(<code>'. BLOG_ROOT .'</code>への書き込みパーミッションが設定されていますか)?',

		2		=> 'Options saved successfully',
		-2		=> 'An error occurred attempting to save settings',
	);
	
?>
