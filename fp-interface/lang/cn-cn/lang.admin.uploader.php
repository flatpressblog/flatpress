<?php
$lang ['admin'] ['uploader'] ['default'] = array(
	'head' => '资源管理',
	'descr' => '请指定一个要上传的文件。',
	'fset1' => '文件指定',
	'fset2' => '上传',
	'submit' => '开始上传',
	'uploader_some_failed' => '由于安全或系统原因，此文件未上载：',
	'uploader_metadata_failed' => '文件已上载，但无法删除元数据：',
	'uploader_drop' => '将文件拖到此处',
	'uploader_browse_hint' => '…或单击以选择文件',
	'uploader_drop_active' => '取消拖动并添加',
	'uploader_selected_count' => '%d 文件已选定',
	'uploader_clear' => '清除选择',
	'uploader_remove' => '删除',
	'uploader_limit_files' => '每个上载的最大文件数: %d 个。',
	'uploader_limit_size' => '最大上传总容量: %s。'
);

$lang ['admin'] ['uploader'] ['default'] ['msgs'] = array(
	1 => '文件上载已完成。',
	-1 => '无法上传。',
	-2 => '服务器拒绝上传：上传总容量超过post_max_size（%s）。',
	-3 => '服务器拒绝上传。可能是上传大小或文件数限制的原因。未接收到文件。',
	-4 => '未接收到文件。上传前请选择一个或多个文件。'
);

$lang ['admin'] ['uploader'] ['browse'] = array(
	'head' => '一览',
	'descr' => '请指定一个以上要上传的文件。',
	'fset1' => '文件指定',
	'submit' => '上传'
);
?>
