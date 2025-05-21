<?php
	
	$lang['admin']['panel']['maintain'] = 'Maintainance';

	$lang['admin']['maintain']['default'] = array(
		'head'		=> 'メンテナンス',
		'descr'		=> '動作が何かおかしいとき、このページに来てください。'.
					'解決策を見つけられるかもしれません。
					でもうまく働かないかもしれませんが。',
		'opt0'		=> '&laquo; メインメニューに戻ります',
		'opt1'		=> 'インデックスを再構成します',
		'opt2'		=> 'テーマとテンプレートのキャッシュをクリアします',
		'opt3'		=> 'ファイルパーミッションの回復',
		'opt4'		=> 'PHP情報を表示します',
		'opt5'		=> 'アップデートをチェックします',

		'chmod_info'	=> "次のファイルのパーミッションを 0777 にリセット<strong>できません</strong>
					; おそらく、ファイルの所有権者とウェブサーバの権限が異なるのでしょう。
					でも通常、この通知を無視することができます。",
		
	);
	
	$lang['admin']['maintain']['default']['msgs'] = array(
		1		=> '作業を完了しました。'
	);
	
	$lang['admin']['maintain']['updates'] = array(
		'head'	=> 'アップデート',
		'list'	=> '<ul>
		<li>現在のFlatPressのヴァージョン <big>%s</big></li>
		<li>Last stable version は、<big><a href="%s">%s</a></big>です。</li>
		<li>Last unstable version は、<big><a href="%s">%s</a></big>です。</li>
		</ul>',
		'notice'=>'Notice:'
		
	);
	
	
	
	$lang['admin']['maintain']['updates']['msgs'] = array(
		1		=> 'アップデートがあります!',
		2		=> '最新版をご利用中です。',
		-1		=> '最新版の検索ができませんでした。'
	);

?>
