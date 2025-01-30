<?php
$lang ['admin'] ['panel'] ['maintain'] = 'Maintainance';

$lang ['admin'] ['maintain'] ['default'] = array(
	'head' => 'メンテナンス',
	'descr' => '動作が何かおかしいとき、このページに来てください。解決策を見つけられるかもしれません。でもうまく働かないかもしれませんが。',
	'opt0' => '&laquo; メインメニューに戻ります',
	'opt1' => 'インデックスを再構成します',
	'opt2' => 'テーマとテンプレートのキャッシュをクリアします',
	'opt3' => 'パーミッションの回復',
	'opt4' => 'PHP情報を表示します',
	'opt5' => 'アップデートをチェックします',

	'chmod_info' => 'パーミッションを<strong>回復(リセット)できなかった</strong>場合、おそらく、ファイル/ディレクトリの所有者とウェブサーバの実行者が異なるのでしょう。<br>' . //
		'
		<table>
			<thead>
				<tr>
					<th>パーミッション</th>
					<th>' . FP_CONTENT . '</th>
					<th>Core(コア部分)</th>
					<th>その他</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>ファイル</td>
					<td>' . decoct(FILE_PERMISSIONS) . '</td>
					<td>' . decoct(CORE_FILE_PERMISSIONS) . '</td>
					<td>' . decoct(RESTRICTED_FILE_PERMISSIONS) . '</td>
				</tr>
				<tr>
					<td>ディレクトリ</td>
					<td>' . decoct(DIR_PERMISSIONS) . '</td>
					<td>' . decoct(CORE_DIR_PERMISSIONS) . '</td>
					<td>' . decoct(RESTRICTED_DIR_PERMISSIONS) . '</td>
				</tr>
			</tbody>
		</table>
		',

	'opt3_success' => 'すべてのパーミッションが正常に更新されました。',
	'opt3_error' => 'パーミッション更新エラー：'
);

$lang ['admin'] ['maintain'] ['default'] ['msgs'] = array(
	1 => '作業を完了しました。',
	-1 => '作業を完了できませんでした。'
);

$lang ['admin'] ['maintain'] ['updates'] = array(
	'head' => 'アップデート',
	'list' => '<ul>
		<li>現在のFlatPressのヴァージョン <big>%s</big></li>
		<li>Last stable version は、<big><a href="%s">%s</a></big>です。</li>
		<li>Last unstable version は、<big><a href="%s">%s</a></big>です。</li>
		</ul>',
	'notice' => 'Notice:'
);

$lang ['admin'] ['maintain'] ['updates'] ['msgs'] = array(
	1 => 'アップデートがあります!',
	2 => '最新版をご利用中です。',
	-1 => '最新版の検索ができませんでした。'
);
?>
