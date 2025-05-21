<?php

	$lang['admin']['widgets']['submenu']['default'] = 'ウィジェットの管理';
	$lang['admin']['widgets']['submenu']['raw'] 	= 'ウィジェットの管理 (直接編集)';

	/* default action */
	
	$lang['admin']['widgets']['default'] = array(
		'head'		=> 'ウィジェットの管理 (<em>試験的運用中</em>)',
		
		'descr'		=> 	'<a class="hint" '.
						'href="http://wiki.flatpress.org/doc:widgets" title="What is a Widget?">'.
						'ウィジェット</a>は、情報の表示や閲覧者とのやり取りをダイナミックにできる部品です。
						<strong>テーマ</strong>はサイトの見かけを変えますが、ウィジェットは
						見かけと機能を<strong>拡張</strong>します。</p>

						<p>ウィジェットは、<strong>ウィジェットセット</strong>という、テーマの特別な領域にドラッグできます。
						使えるウィジェットセットの数や拡張機能は、選んだテーマに依存します。
						</p>

						<p>FlatPressには、あらかじめいくつかのウィジェットがついています: ログインのためのウィジェット、
						検索窓のウィジェットなどです。</p>
						
						<p>各々のウィジェットは、<a class="hint" '.
						'href="http://wiki.flatpress.org/doc:plugins" title="What is a Widget?">プラグイン</a>で定義されます。',
						
		'availwdgs'	=> '使用できるウィジェット',
		'trashcan'	=> 'バーから削除したい場合は、ここにドロップします',
		
		'themewdgs' => 'このテーマのためのウィジェット',
		'themewdgsdescr' => 'The theme you have currently selected let you have the following widgetsets',
		'oldwdgs'	=> 'その他のウィジェット',
		'oldwdgsdescr' =>'The following widgetsets seems not to belong to any of the '.
						'widgetsets listed above. They might be remainders from another theme.',
		
		'submit'	=> '変更を保存する',

	);
	
	$lang['admin']['widgets']['default']['stdsets'] = array(
		'top'		=> 'トップバー',
		'bottom'	=> 'ボトムバー',
		'left'		=> '左サイドバー',
		'right'		=> '右サイドバー',
	);
	
	$lang['admin']['widgets']['default']['msgs'] = array(
		1	=> '変更は保存されました',
		-1	=> '保存できませんでした。やり直してください。',
	);


	
	/* "raw" panel */	
	
	$lang['admin']['widgets']['raw'] = array(
		'head'		=> 'ウィジェットの管理 (<em>raw editor</em>)',
		'descr'		=> 'A <a class="hint" '.
						'href="http://wiki.flatpress.org/doc:plugins" title="What is a Widget?">'.
						'Widget</a> is a visual element of a <a class="hint" '.
						'href="http://wiki.flatpress.org/doc:plugins" title="What is a plugin?">'.
						'Plugin</a> that you can put in some special areas (the <em>widgetsets</em>) on your blog pages. </p>'.
						'<p>This is the <strong>raw</strong> editor; some advanced users or people who '.
						'can\'t have JavaScript might prefer it.',
						
		'fset1'		=> 'Editor',
		'fset2'		=> 'Apply Changes',
		'submit'	=> 'Apply',

	);
	
	
	$lang['admin']['widgets']['raw']['msgs'] = array(
		1	=> 'Config saved',
		-1	=> 'An error occurred while trying to save. This may happen for several reasons: maybe your file contains syntax errors.',
	);

		

	/* system errors */
		
	$lang['admin']['widgets']['errors'] = array(
		'generic'	=> 'The widget called <strong>%s</strong> is not registered, and will be skipped. '.
 				'Is the plugin enabled in the <a href="admin.php?p=plugin">plugin panel</a>?'

	);
	
?>
