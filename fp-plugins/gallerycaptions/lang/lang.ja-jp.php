<?php
/**
 * Admin area phrases
 */
// "Plugins" menu entry
$lang ['admin'] ['uploader'] ['submenu'] ['gallerycaptions'] = 'ギャラリーのキャプション';

// Plugin configuration panel
$lang ['admin'] ['uploader'] ['gallerycaptions'] = array(
	'head' => 'ギャラリーのキャプション',
	'label_selectgallery' => '編集するギャラリーを選択します：',
	'button_selectgallery' => 'ギャラリーを選択',
	'label_editcaptionsforgallery' => 'ギャラリーのキャプションを編集する：',
	'label_noimagesingallery' => 'このギャラリーにはまだ画像がありません ¯\_(ツ)_/¯<br>' .
		'<br><a href="' . BLOG_BASEURL . 'admin.php?p=uploader&action=default' . '">アップローダー</a>アップローダー</a>を使って画像をアップロードし、<a href="' . BLOG_BASEURL . 'admin.php?p=uploader&action=mediamanager' . '">メディアマネージャー</a>を使ってギャラリーに追加します!',
	'button_savecaptions' => 'キャプションの保存'
);
?>
