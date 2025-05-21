<?php

	$lang['admin']['static']['submenu'] = array(
		'list'		=> '固定ページの管理',
		'write'		=> '固定ページの作成'
	);

	
	/* main panel */
		
	$lang['admin']['static']['list'] = array(
	
		'head'		=> '固定ページ',
		'descr'		=> '編集する固定ページを選んでください。<a href="admin.php?p=static&amp;action=write">固定ページを新規作成するにはここをクリック</a>します。',
	
		'sel'		=> 'Sel', // checkbox
		'date'		=> '日付',
		'name'		=> '固定ページ名',
		'title'		=> '題名',
		'author'	=> '作成者',
		
		'action'	=> '作業を選んでください',
		'act_view'	=> '表示',
		'act_del'	=> '削除',
		'act_edit'	=> '編集'		
	);
	
	$lang['admin']['static']['list']['msgs'] = array(
		1	=> '固定ページは保存されました。',
		-1	=> '固定ページを保存できませんでした。',
		2	=> '固定ページを削除しました。',
		-2	=> '固定ページを削除できませんでした。',
	);

	/* write panel */

	$lang['admin']['static']['write'] = 
	array(
		'head'		=> '固定ページの作成／編集',
		'descr'		=> 'Edit the form to publish the page',
		'fieldset1'	=> '編集',
		'subject'	=> '題名 (*):',
		'content'	=> '内容 (*):',
		'fieldset2'	=> '保存',
		'pagename'	=> 'url用の固定ページ名 (*):',
		'submit'	=> '保存する',
		'preview'	=> 'プレビュー',

		'delfset'	=> '削除',
		'deletemsg'	=> 'この固定ページを削除します',
		'del'		=> 'Delete',
		'success'	=> '固定ページを公開しました。',
		'otheropts'	=> 'その他のオプション',
	);
	
	$lang['admin']['static']['write']['error'] = array(
		'subject'	=> '題名を記入してください。',
		'content'	=> '内容を記入してください。',
		'id'		=> 'You must send a valid id'
	);
	
	
	/* delete action */	
	$lang['admin']['static']['delete'] = array(
		'head'		=> "固定ページの削除", 
		'descr'		=> '次のページを削除しようとしています:',
		'preview'	=> 'プレビュー',
		'confirm'	=> 'この作業を続行しますか?',
		'fset'		=> '削除',
		'ok'		=> 'はい、この固定ページを削除します。',
		'cancel'	=> 'いいえ、コントロールパネルへ戻ります。',
		'err'		=> '指定された固定ページは存在しません。',
	
	);
	
	
		
?>
