<?php
$lang ['plugin'] ['newsletter'] = array(
	// Widget
	'subject' => 'ニュースレター',
	'input_email_placeholder' => 'Eメールアドレス',
	'accept_privacy_policy' => 'プライバシーポリシーに同意します',
	'privacy_link_text' => 'プライバシーポリシーへ',
	'button' => '購読する',
	'csrf_error' => '無効なCSRFトークンです。',

	// Double Opt-In
	'confirm_subject' => 'ニュースレターの購読をご確認ください',
	'confirm_greeting' => '月刊ニュースレターのご購読ありがとうございます。',
	'confirm_link_text' => '購読の確認はこちらをクリックしてください。',
	'confirm_ignore' => 'このメールをご希望でない場合は、無視してください。',

	// E-Mail-Content
	'last_entries' => '最後のエントリ',
	'no_entries' => 'エントリーはありません',
	'last_comments' => '最後のコメント',
	'no_comments' => 'コメントなし',
	'unsubscribe' => 'ニュースレターの購読解除',
	'privacy_policy' => 'プライバシーポリシー',
	'legal_notice' => '法的通知'
);

// Admin panel: Newsletter subscribers
$lang ['admin'] ['plugin'] ['submenu'] ['newsletter'] = 'ニュースレター';
$lang ['admin'] ['plugin'] ['newsletter'] = array(
	'head' => 'ニュースレター管理',
	'desc_subscribers' => 'ここでは、ニュースレター購読者のすべてのメールアドレスと、購読者がプライバシーポリシーに同意したことを確認できます。 ' . //
		'また、購読者を削除することもできます。',
	'admin_subscribers_list' => '購読者リスト',
	'email_address' => 'メールアドレス',
	'subscribe_date' => '日時',
	'subscribe_time' => '時刻',
	'newsletter_no_subscribers' => '購読者なし',
	'delete_subscriber' => 'このアドレスを削除する',
	'delete_confirm' => 'このアドレスを本当に削除しますか？',
	'desc_batch' => 'ここでは、1日に何人の購読者にニュースレターを送信するかを指定できます。 '. //
		'一日に何通送信できるかは、メールプロバイダにお問い合わせください。 ' . //
		'ニュースレターは、月初めにすべての購読者に自動的に送信されます。 ' . //
		'現在、自動配信が実行されていない場合、ニュースレターの即時配信を開始することもできます。 ' . //
		'即時配信が月の28日までに完了しなかった場合、すべての購読者は、再来月まで通常のニュースレターを自動的に受け取ることはできません。',
	'send_all_button' => 'すべての購読者にニュースレターを送信する',
	'send_all_confirm' => 'すべての購読者にニュースレターを送信しますか？',
	'send_type_monthly' => '毎月配信',
	'send_type_manual'  => '手動配信',
	'sub_remaining' => 'まだ送信されていません：',
	'batch_size_label' => 'バッチあたりのメール数',
	'save_button' => '保存'
);

$lang ['plugin'] ['newsletter'] ['errors'] = array (
	-2 => 'このプラグインを使用するには、LastEntriesプラグインが有効である必要があります。'
);

$lang ['admin'] ['plugin'] ['newsletter'] ['msgs'] = array(
	1 => 'ニュースレターはすべての購読者に送信されます。',
	-2 => 'このプラグインを使用するには、FlatPressに統合されているLastEntriesプラグインが必要です。あらかじめプラグインエリアで有効化しておいてください！',
	2 => '設定が保存されました。'
);
?>
