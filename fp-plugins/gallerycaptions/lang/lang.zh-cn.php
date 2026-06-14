<?php
/**
 * Admin area phrases
 */
// "Plugins" menu entry
$lang ['admin'] ['uploader'] ['submenu'] ['gallerycaptions'] = '图片库';

// Plugin configuration panel
$lang ['admin'] ['uploader'] ['gallerycaptions'] = array(
	'head' => '图片库',
	'label_selectgallery' => '选择要编辑的图片库：',
	'button_selectgallery' => '选择图片库',
	'label_editcaptionsforgallery' => '编辑图片库标题：',
	'label_noimagesingallery' => '这个图片库还没有图像¯<br>' . //
		'<br><a href="' . BLOG_BASEURL . 'admin.php?p=uploader&action=default' . '">上传器</a>上传图片、<a href="' . BLOG_BASEURL . 'admin.php?p=uploader&action=mediamanager' . '">使用介质管理器添加到图片库!',
	'button_savecaptions' => '保存'
);
?>
