<?php
	$lang['plugin']['lastcommentsadmin ']['errors'] = array (
		-1	=> 'API key not set. Open the plugin to set your API key. Register on <a href="http://wordpress.com">Wordpress.com</a> to get one'
	);

	$lang['admin']['plugin']['submenu']['lastcommentsadmin'] = '『最近のコメント』の管理';

	$lang['admin']['plugin']['lastcommentsadmin'] = array(
		'head'		=> '『最近のコメント』の管理',
		'description'=>'最近のコメントのキャッシュをクリアしたり、再構築します ',
		'clear'	=> 'キャッシュのクリア',
		'cleardescription' => '最近のコメントのキャッシュファイルを削除します. 新規にコメントがあると新しいキャッシュファイルが作成されます.',
		'rebuild' => 'キャッシュの再構築',
		'rebuilddescription' => '最近のコメントのキャッシュファイルと再構築します. 少し時間がかかります. その間,何も操作できなくなります. ひと息入れてください!',
	);
	$lang['admin']['plugin']['lastcommentsadmin']['msgs'] = array(
		1		=> 'キャッシュを削除しました',
		2		=> 'キャッシュを再構築しました!',
		-1		=> 'エラーです!',
		-2	   =>  'このプラグインの動作には『最近のコメント』プラグインが必要です!'
	);
	

?>