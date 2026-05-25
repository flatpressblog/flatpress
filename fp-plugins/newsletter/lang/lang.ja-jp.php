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
	'desc_batch' => 'ここでは、プラグインが1回の送信日に何通のメールを送信するかを設定します。 ' . //
		'メールプロバイダの1日あたりの送信上限より低い値を選んでください。 ' . //
		'月初になると通常のニュースレター配信が自動的に開始され、必要に応じて購読者全員に届くまで日ごとのバッチで送信されます。 ' . //
		'現在送信処理が実行されていない場合は、手動で配信を開始することもできます。手動配信にも同じ1日あたりの上限が適用されます。 ' . //
		'新しい月の開始時点で手動配信がまだ実行中の場合、自動の月次配信は翌月に延期されます。',
	'icon_sent_title' => '今回の発送ですでにお届け済み',
	'icon_sent_alt' => '配送済み',
	'icon_queued_title' => '次のバッチを予定',
	'icon_queued_alt' => '予定',
	'send_now_button' => '今すぐ購読者にニュースレターを送信します',
	'send_now_confirm' => '今すぐ購読者にニュースレターを送信しますか？',
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
	1 => 'ニュースレターが購読者に配信されます。',
	-2 => 'このプラグインを使用するには、FlatPressに統合されているLastEntriesプラグインが必要です。あらかじめプラグインエリアで有効化しておいてください！',
	2 => '設定が保存されました。'
);
?>
