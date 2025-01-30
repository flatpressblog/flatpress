<?php
/*
 * LangId: Japanese
 */
$lang ['setup'] = array(
	'setup' => '初回セットアップ'
);

$lang ['locked'] = array(
	'head' => 'セットアップは中断されました。',
	'descr' => 'ロックファイル「<code>%s</code>」がサーバ上に存在しますので、すでにセットアップ済みと判断しました。

		もしセットアップをやり直したいのでしたら、まずこのロックファイルをサーバ上から削除してください。

		<strong >警告!</strong> <code>setup.php</code> ファイルや <code>setup/</code> ディレクトリをサーバに残しておくのは危険です。 セットアップ後に削除することをお勧めします!

		<ul>
		<li><a href="%s">ブログに戻ります</a></li>
		<li><a href="%s">ロックファイルを削除しましたので、セットアップを再開します</a></li>
		</ul>'
);

$lang ['err'] = array(
	'setuprun1' => 'インストールは実行中です。',

	'setuprun2' => 'インストールがすでに実行されています: 管理者であれば、 ',
	'setuprun3' => ' を削除して再起動できます。',
	'writeerror' => '書き込みエラー',

	'fpuser1' => ' は有効なユーザではありません。 ' . //
		'ユーザ名は英数字でなければならず、スペースを含んではいけません。',
	'fpuser2' => ' は有効なユーザではありません。 ' . //
		'ユーザ名には、アルファベット、数字、アンダースコア(1字のみ)を使用できます。',
	'fppwd' => 'パスワードは、スペース以外の英数6字以上で指定してください。',
	'fppwd2' => 'パスワードが一致しません。',
	'email' => ' は有効なメールアドレスではありません。',
	'www' => ' は有効なURLではありません。',
	'error' => '<p><big>エラー!</big> ' . //
		'フォームの処理中に以下のエラーが発生しました：</p><ul>'
);

$lang ['step1'] = array(
	'head' => 'ようこそFlatPressへ',
	'descr' => '<strong>FlatPress</strong>を選んでくださり、ありがとうございます!

		新規のブログをお楽しみいただく前に、少しばかりお尋ねします。

		時間はかかりませんから、ご心配なく!',

	'descrl1' => 'Select your language.',
	'descrl2' => '<a class="hint" onclick="toggleinfo();">Not in the list?</a>',

	'descrlang' => 'If you don\'t see your language in this list, you might want to see if there is <a href="https://wiki.flatpress.org/res:language" target="_blank" rel="external">a language pack</a> for this version:

		<pre>%s</pre>

		To install the language pack, upload the content of the package in your <code>flatpress/</code>, and overwrite all, then <a href="./setup.php">restart this setup</a>.',

	'descrw' => 'FlatPressが動作するために必須な<strong>たったひとつの要件</strong>は、<em>書き込み可能な</em>ディレクトリを用意することです。

		<pre>%s</pre>'
);

$lang ['step2'] = array(
	'head' => '管理ユーザの作成',
	'descr' => '次の各欄にご記入ください:',
	'fpuser' => 'ユーザ名',
	'fppwd' => 'ログインパスワード(英数6文字以上)',
	'fppwd2' => 'ログインパスワードの再入力',
	'www' => 'ホームページurl',
	'email' => 'E-Mailアドレス'
);

$lang ['step3'] = array(
	'head' => 'はい、おしまいです',
	'descr' => '<strong>作業は終了しました</strong>. 

		信じられないですって? 

		そうおっしゃるのはごもっともです:
		 <strong>なぜなら本当の作業は今からなのです</strong>,
		 <strong>記事作成するのはあなた自身なのですからね</strong>!

		<ul>
		<li><a href="%s">トップページ</a>がどう見えるでしょうか? 見てみましょう!</li>
		<li>さっそく<a href="%s">ログイン</a>しますか? お楽しみください!</li>
		<li>私たちに何か言いたいことがありますか? <a href="https://www.flatpress.org/" target="_blank" rel="external">FlatPress.org サイト</a>に来てください!</li>
		</ul>

		最後に、FlatPress を選んでくださってありがとうございます!'
);

$lang ['buttonbar'] = array(
	'next' => 'Next >'
);

$lang ['samplecontent'] = array();

$lang ['samplecontent'] ['menu'] ['subject'] = 'メニュー';
$lang ['samplecontent'] ['menu'] ['content'] = '[list]
[*][url=?]トップページ[/url]
[*][url=?paged=1]ブログ[/url]
[*][url=static.php?page=about]自己紹介[/url]
[*][url=contact.php]お問い合わせ[/url]
[/list]';

$lang ['samplecontent'] ['entry'] ['subject'] = 'FlatPressへ ようこそ!';
$lang ['samplecontent'] ['entry'] ['content'] = 'これは[url=https://www.flatpress.org target=_blank rel=external]FlatPress[/url]の特徴をいくつかご紹介するサンプル記事です。

［more］タグを使用すると、抜粋記事から記事全文への橋渡しをする［続きを読む...］リンクが作成されます。

[more]


[h4]装飾と書式[/h4]

記事への装飾や書式の指定には[url=https://wiki.flatpress.org/doc:plugins:bbcode target=_blank rel=external]BBcode[/url] (bulletin board code)がデフォルトで用意されています。BBCodeなら簡単に記事へ装飾できます。よくある装飾用タグが用意されています。例えば［b］タグで [b]ボールド[/b] (html: strong)、［i］タグで [i]イタリック[/i] (html: i)、などです。

[quote]お気に入りの引用を表示するための[b]quote[/b] タグもあります。[/quote]

[code]また、［code］タグではスニペットが等幅形式で表示されます。
   コンテンツのインデントも
      対応しています。[/code]

［img］タグと［url］タグには特別なオプションもあります。詳しくは、[url=https://wiki.flatpress.org/doc:plugins:bbcode target=_blank rel=external]FlatPress official website[/url]をご覧ください。


[h4]ブログ記事と固定ページ[/h4]

お読みいただいてるのはブログ記事ですが、[url=static.php?page=about]「自己紹介」[/url] は [b]固定ページ[/b]です。固定ページにはコメントができません。またブログ記事と同時には表示されません。

固定ページは広報ページに向いています。 また[b]サイトのトップ(優先表示する)ページ[/b]に指定することもできます。ひいてはブログサイトでないサイト運営も可能ということです。固定ページを「サイトのトップ(優先表示する)ページ」に指定するには [url=admin.php]管理者ページ[/url] の [b]設定[/b] を開きます。


[h4]プラグイン[/h4]

FlatPressは非常に高いカスタマイズ性があり、 [url=https://wiki.flatpress.org/doc:plugins:standard target=_blank rel=external]プラグイン[/url] で機能を拡張することができます。ちなみに BBCode自身もプラグイン機能です。

FlatPressの隠された珠玉の機能を紹介するためサンプルページを作りました :) 
あなたの編集を待っている [b]固定ページ[/b] が2つあります:
[list]
[*][url=static.php?page=about]自己紹介[/url]
[*][url=static.php?page=menu]メニュー[/url] (ページ内のリンクがサイドバーにも反映されることにご注目ください。これぞ[b]BlockParserウィジェット[/b]の魔法なのです。このウィジェットやその他の詳細については[url=https://wiki.flatpress.org/doc:faq target=_blank rel=external]FAQ[/url]を参照してください!)
[/list]

[b]PhotoSwipeプラグイン[/b]を使用すると、float="left"またはfloat="right"で揃えられた単一の画像として、いとも簡単に画像を配置できます。
［gallery］タグを使用すると、ギャラリー単位で配置することもできます。そのやり方がいかに簡単かは、[url=https://wiki.flatpress.org/doc:plugins:photoswipe target=_blank rel=external]こちらで学べます[/url]。


[h4]ウィジェット[/h4]

サイドバーには固定された部品が1つもありません。この記事周辺のサイドバー上にあるすべての部品は、すべて自由に配置でき、そのほとんどはカスタマイズ可能です。その機能によっては、管理者用ページに設定用パネルが用意されているものもあります。

これらの部品は[b]ウィジェット[/b]と呼ばれます。ウィジェットの詳細やナイスな機能を得るための[url=https://wiki.flatpress.org/doc:tips:widgets target=_blank rel=external]ヒント[/url]は、[url=https://wiki.flatpress.org/ target=_blank rel=external]wiki[/url]からご覧ください。


[h4]テーマ[/h4]

[gallery="images/Leggero-Themepreview/" width="140"]
FlatPress-Leggeroテーマでは、クラシックからモダンまで3つのスタイルを自由に使用できます。これらのテンプレートは独自のものを作成するための素晴らしい出発点となります。


[h4]もっと知る[/h4]

もっと知りたいですか?

[list]
[*]FlatPressの世界で何が起こっているかを知るには、[url=https://www.flatpress.org/?x target=_blank rel=external]公式ブログ[/url]をフォローしてください。
[*]サポートや雑談については[url=https://forum.flatpress.org/ target=_blank rel=external]フォーラム[/url]にアクセスしてください。
[*][url=https://wiki.flatpress.org/res:themes target=_blank rel=external]他のユーザから投稿[/url]された[b]素晴らしいテーマ[/b]を入手しましょう!
[*][url=https://wiki.flatpress.org/res:plugins target=_blank rel=external]プラグイン[/url]をチェックしてください。
[*]あなたの言語の[url=https://wiki.flatpress.org/res:language target=_blank rel=external]翻訳パック[/url]を入手しましょう。
[*][url=https://twitter.com/FlatPress target=_blank rel=external]X(Twitter)[/url]と[url=https://fosstodon.org/@flatpress target=_blank rel=external]Mastodon[/url]でもFlatPressをフォローできます。
[/list]


[h4]お手伝いできますか?[/h4]

[list]
[*][url=https://www.flatpress.org/home/static.php?page=donate target=_blank rel=external]少額寄付[/url]でプロジェクトを支援してください。
[*]バグ報告や改善提案は、[url=https://www.flatpress.org/contact/ target=_blank rel=external]お問い合わせ[/url]まで。
[*][url=https://github.com/flatpressblog/flatpress target=_blank rel=external]GitHub[/url]で FlatPressの開発に貢献してください。
[*]FlatPressまたはドキュメントを[url=https://wiki.flatpress.org/res:language target=_blank rel=external]あなたの言語[/url]に翻訳してください。
[*][url=https://forum.flatpress.org/ target=_blank rel=external]フォーラム[/url]で知識を共有し、他の FlatPressユーザとつながりましょう。
[*]広めてください! :)
[/list]


[h4]ではさっそく何をしましょうか?[/h4]

さっそく[url=admin.php]管理者用ページ[/url]に[url=login.php]ログイン[/url]して投稿してみましょう!

さあ、存分にお楽しみあれ! :)

[i]The [url=https://www.flatpress.org target=_blank rel=external]FlatPress[/url] Team[/i]

';

$lang ['samplecontent'] ['about'] ['subject'] = '自己紹介';
$lang ['samplecontent'] ['about'] ['content'] = 'ここに自己紹介を何か書いてみましょう! ([url=admin.php?p=static&action=write&page=about]さっそく編集![/url])';

$lang ['samplecontent'] ['privacy-policy'] ['subject'] = 'プライバシーポリシー';
$lang ['samplecontent'] ['privacy-policy'] ['content'] = '国によっては、例えばAkismet Antispamサービスを利用する場合、訪問者にプライバシーポリシーを提供する必要があります。また、訪問者がコンタクトフォームやコメント機能を利用する場合にも、プライバシーポリシーが必要になることがあります。

[b]ヒント:[/b] インターネット上にはたくさんのテンプレートやジェネレータがあります。

ここにそれらを挿入することができます。 ([url=admin.php?p=static&action=write&page=privacy-policy]編集する[/url])

CookieBannerプラグインを有効にすると、訪問者はコンタクトフォームやコメント機能から直接このページにアクセスできるようになります。
';
?>
