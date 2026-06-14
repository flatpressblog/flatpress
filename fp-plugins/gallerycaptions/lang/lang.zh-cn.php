<?php
/**
 * Admin area phrases
 */
// "Plugins" menu entry
$lang ['admin'] ['uploader'] ['submenu'] ['gallerycaptions'] = '图片库标题';

// Plugin configuration panel
$lang ['admin'] ['uploader'] ['gallerycaptions'] = array(
	'head' => '图片库标题',
	'label_selectgallery' => '选择要编辑的图片库：',
	'button_selectgallery' => '选择图片库',
	'label_editcaptionsforgallery' => '编辑图片库标题：',
	'label_noimagesingallery' => '这个图片库还没有任何图片 ¯\_(^_^)_/¯<br>' . //
		'<br>请先通过<a href="' . BLOG_BASEURL . 'admin.php?p=uploader&action=default' . '">上传器</a>上传图片，然后使用<a href="' . BLOG_BASEURL . 'admin.php?p=uploader&action=mediamanager' . '">媒体管理器</a>将图片添加到图片库！',
	'button_savecaptions' => '保存标题'
);
?>
