<?php
$lang ['admin'] ['uploader'] ['default'] = array(
	'head' => 'アップローダー',
	'descr' => 'アップロードしたいファイルを1つ以上指定してください。',
	'fset1' => 'ファイル指定',
	'fset2' => 'アップロード',
	'submit' => 'アップロードを開始する',
	'uploader_some_failed' => 'このファイルはセキュリティまたはシステム上の理由によりアップロードされませんでした：',
	'uploader_metadata_failed' => 'ファイルはアップロードされましたが、メタデータは削除できませんでした：',
	'uploader_drop' => 'ファイルをここにドラッグ',
	'uploader_browse_hint' => '…またはクリックしてファイルを選択',
	'uploader_drop_active' => 'ドラッグを解除して追加',
	'uploader_selected_count' => '%d ファイルが選択されました',
	'uploader_clear' => '選択をクリア',
	'uploader_remove' => '削除',
	'uploader_limit_files' => 'アップロードごとの最大ファイル数: %d 個。',
	'uploader_limit_size' => '最大アップロード総容量: %s。'
);

$lang ['admin'] ['uploader'] ['default'] ['msgs'] = array(
	1 => 'ファイルのアップロードが完了しました。',
	-1 => 'アップロードができませんでした。',
	-2 => 'サーバーによりアップロードが拒否されました: アップロード総容量が post_max_size (%s) を超えています。',
	-3 => 'サーバーによりアップロードが拒否されました。おそらくアップロードサイズまたはファイル数制限が原因です。ファイルは受信されませんでした。',
	-4 => 'ファイルは受信されませんでした。アップロード前に1つ以上のファイルを選択してください。'
);

$lang ['admin'] ['uploader'] ['browse'] = array(
	'head' => '一覧',
	'descr' => 'アップロードしたいファイルを1つ以上指定してください。',
	'fset1' => 'ファイル指定',
	'submit' => 'アップロードする'
);
?>
