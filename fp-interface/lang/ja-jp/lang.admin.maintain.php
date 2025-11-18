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
	'opt6' => 'APCuキャッシュの状態',

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

$lang ['admin'] ['maintain'] ['apcu'] = array(
	'head' => 'APCuキャッシュ',
	'descr' => 'APCu共有メモリ使用量とキャッシュ効率の概要。',
	'status_heading' => 'ヒューリスティック状態',
	'status_good' => '現在のワークロードに対してキャッシュサイズは適切と思われる。',
	'status_bad' => 'ミス率が高い、または空きメモリが極端に少ない場合：APCuキャッシュが小さすぎるか、深刻な断片化が発生している可能性があります。',
	'hit_rate' => 'ヒット率',
	'free_mem' => '空きメモリ',
	'total_mem' => '共有メモリ合計',
	'used_mem' => '使用済みメモリ',
	'avail_mem' => '利用可能メモリ',
	'memory_type' => 'メモリタイプ',
	'memory_type_unknown' => '該当なし',
	'num_slots' => 'スロット数',
	'num_hits' => 'ヒット数',
	'num_misses' => 'ミス数',
	'cache_type' => 'キャッシュタイプ',
	'cache_user_only' => 'ユーザーデータキャッシュ',
	'legend_good' => '緑： 設定は健全な状態（高いヒット率、適切な空きメモリ）。',
	'legend_bad' => '赤： キャッシュに負荷がかかっている状態（ミスが多発、または空きメモリがほぼない）。',
	'no_apcu' => 'このサーバーではAPCuが有効化されていないようです。',
	'back' => '&laquo; メンテナンスに戻る',
	'clear_fp_button'=> 'FlatPress APCuエントリのクリア',
	'clear_fp_confirm' => 'すべてのAPCuエントリを本当に削除しますか？これによりFlatPressのAPCuキャッシュがクリアされます。',
	'clear_fp_result'=> '%d個のAPCuエントリを削除しました。',
	'msgs' => array(
		1  => 'FlatPress APCuエントリがクリアされました。',
		2  => 'APCuエントリが見つかりませんでした。',
		-1 => 'APCuが利用不可またはアクセス不可のため、削除対象はありませんでした。'
	)
);
?>
