<?php

	$lang['admin']['plugin']['submenu'] = array (
		'default'		=> 'プラグインの管理'
	);
	
	/* main plugin panel */

	$lang['admin']['plugin']['default'] = array(
	
		'head'		=> 'プラグインの管理',
		'enable'	=> '有効にする',
		'disable'	=> '無効にする',
		'descr'		=> '<a class="hint" '.
						'href="http://wiki.flatpress.org/doc:plugins" title="What is a plugin?">'.
						'プラグイン</a>は、FlatPressに機能を追加・変更するような部品です。</p>'.
						'<p>新たにプラグインをインストールするには、<code>fp-plugins/</code> '.
						'ディレクトリにアップロードしてください。</p>'.
						'<p>このパネルでは、プラグインの有効／無効を切り替えることができます。',
		'name'		=> 'プラグイン名',
		'description'=>'説明',
		'author'	=> '作者',
		'version'	=> 'ヴァージョン',
		'action'	=> '切替設定',
	);
	
	$lang['admin']['plugin']['default']['msgs'] = array(
		1	=> '設定は変更されました。',
		-1	=> '設定の変更ができませんでした。考えられる理由: プラグインに文法エラーがある。',
	);
	
	/* system errors */
	
	$lang['admin']['plugin']['errors'] = array(
		'head'		=> 'The following errors were encountered while loading plugins:',
		'notfound'	=> 'プラグインが見つかりません。スキップされました。',
		'generic'	=> 'エラーナンバー %d',
	);
	
?>
