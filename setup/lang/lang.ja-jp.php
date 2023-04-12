<?php
/*
 * LangId: Japanese
 */
$lang ['locked'] = array(
	'head' => 'セットアップは中断されました.',
	'descr' => 'ロックファイル「<code>%s</code>」がサーバ上に存在しますので,
		すでにセットアップ済みと判断しました.
		
		もしセットアップをやり直したいのであれば, まずこのロックファイルをサーバ上から削除してください.
		
		<strong >警告!</strong>
		 <code>setup.php</code> ファイルや <code>setup/</code> ディレクトリをサーバに残しておくのは危険です. セットアップ後に削除することをお勧めします!
		<ul>
		<li><a href="%s">ブログに戻ります</a></li>
		<li><a href="%s">ロックファイルを削除しましたので, セットアップを再開します</a></li>
		</ul>'
);

$lang ['step1'] = array(
	'head' => 'ようこそFlatPressへ',
	'descr' => '<strong>FlatPress</strong>を選んでくださり, 感謝申し上げます!
		
		新規のブログをお楽しみいただく前に, 少しばかりお尋ねします. 
		
		時間はかかりませんから, ご心配なく!',

	'descrl1' => 'Select your language.',
	'descrl2' => '<a class="hint" onclick="toggleinfo();">Not in the list?</a>',

	'descrlang' => 'If you don\'t see your language in this list, you might want to see if there is <a href="http://wiki.flatpress.org/res:language">a language pack</a> for this version:
		
		<pre>%s</pre>
		
		To install the language pack, upload the content of the package in your <code>flatpress/</code>, and overwrite all, then <a href="./setup.php">restart this setup</a>.',

	'descrw' => 'FlatPressが動作するために必須な<strong>たったひとつのこと</strong>は、<em>書き込み可能な</em>ディレクトリを用意することです.
		
		次のディレクトリを<a class="hint" href="http://wiki.flatpress.org/doc:setup#making_the_contents_directory_writable">書き込み可能な</a>パーミッションに変更してから続けて下さい. 
		
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
	'head' => 'はい, おしまいです',
	'descr' => '<strong>作業は終了しました</strong>. 
		
		信じられないって? 
		
		そう, おっしゃるのはごもっともです:
		 <strong>だって本当の作業は今からなのです</strong>,
		 <strong>記事作成するのはあなた自身なのですから</strong>!
		 
		<p style="color:#cc0000">注意：より快適に、より安全にご利用いただくために、PrettyURLプラグインを使用して、管理領域でサーバーの指示を設定することをお勧めします。</p>
		
		<ul>
		<li><a href="%s">トップページがどう見えるか</a> 見てみます</li>
		<li><a href="%s">さっそくログインします!</a> お楽しみください!</li>
		<li>私たちに何か言いたいことがありますか? <a href="http://www.flatpress.org/">FlatPress.org サイトに来てください!</a></li>
		</ul>
		
		最後に, FlatPress を選んでくださって感謝申し上げます!'
);

$lang ['buttonbar'] = array(
	'next' => 'Next >'
);

$lang ['samplecontent'] = array();

$lang ['samplecontent'] ['menu'] ['subject'] = 'Menu';
$lang ['samplecontent'] ['menu'] ['content'] = '[list]
[*][url=?]Home[/url]
[*][url=?paged=1]Blog[/url]
[*][url=static.php?page=about]About[/url]
[*][url=contact.php]Contact[/url]
[/list]';

$lang ['samplecontent'] ['entry'] ['subject'] = 'Welcome to FlatPress!';
$lang ['samplecontent'] ['entry'] ['content'] = 'This is a sample entry, posted to show you some of the features of [url=https://www.flatpress.org]FlatPress[/url].

The more tag allows you to create a "jump" between an excerpt and the complete article.

[more] 


[h4]Styling[/h4]

The default way to style and format your content is [url=http://wiki.flatpress.org/doc:plugins:bbcode]BBcode[/url] (bulletin board code). BBCode is an easy way to style your posts. Most common codes are allowed. Like [b] for [b]bold[/b] (html: strong), [i] for [i]italics[/i] (html: em), etc.

[quote]There are also [b]quote[/b] blocks to display your favourite quotations. [/quote]

[code]And \'code\' displays your snippets in a monospaced fashion.
It also supports
   indented content.[/code]

img and url tag have also special options. You can find out more on the [url=https://wiki.flatpress.org/doc:plugins:bbcode]FP official website[/url].


[h4]Entries (posts) and Static pages[/h4]

This is an entry, while [url=static.php?page=about]About[/url] is a [b]static page[/b]. A static page is an entry (a post) which cannot be commented, and which does not appear together with the normal posts of the blog.

Static pages are useful to create general information pages. You can also make one of these pages the [b]opening page[/b] for your visitors. This means that with FlatPress you could also run a complete non-blog site. The option to make a static page your start page is in the [b]option panel[/b] of the [url=admin.php]admin area[/url].


[h4]Plugins[/h4]

FlatPress is very customizable, and supports [url=https://wiki.flatpress.org/doc:plugins:standard]plugins[/url] to extend its power. BBCode is a plugin itself.

We have created some more sample content, to show you some of the FP well hidden functions and gems :) 
You can find two [b]static pages[/b] ready to accept your contents:
[list]
[*][url=static.php?page=about]About me[/url]
[*][url=static.php?page=menu]Menu[/url] (notice that the links in this page will appear on your sidebar as well - this is the magic of the [b]blockparser widget[/b]. See the [url=http://wiki.flatpress.org/doc:faq]FAQ[/url] for this and more!)
[/list]

With the [b]PhotoSwipe plugin[/b] you can now place your images even easier, either as float="left"- or float="right" aligned single image, enclosed by the text.
You can even use the \'gallery\' element to present entire galleries to your visitors. How easy it works, [url="https://wiki.flatpress.org/res:plugins:photoswipe"]you can learn here[/url].


[h4]Widgets[/h4]

There isn\'t a single fixed element in the sidebar(s). All the elements you can find in the bars surrounding this text are completely positionable, and most of them are customizable as well. Some themes even provide a panel interface in the admin area.  

These elements are called [b]widgets[/b]. For more on widgets and [url=https://wiki.flatpress.org/doc:tips:widgets]some tips[/url] to get nice effects, take a look at the [url=https://wiki.flatpress.org/]wiki[/url].


[h4]Themes[/h4]

[gallery="images/Leggero-Themepreview/" width="140"]
With the FlatPress-Leggero theme you have 3 style templates at your disposal - from classic to modern. These templates are a wonderful start to create something of your own.


[h4]See more[/h4]

Want to see more?

[list]
[*]Support the project with a [url=http://www.flatpress.org/home/static.php?page=donate]small donation[/url]
[*]Follow the [url=https://www.flatpress.org/?x]official blog[/url] to know what\'s going on in the FlatPress world
[*]Visit the [url=https://forum.flatpress.org/]forum[/url] for support and chit-chat
[*]Get [b]great themes[/b] from [url=https://wiki.flatpress.org/res:themes]other users\' submissions[/url]!
[*]Check out the [url=https://wiki.flatpress.org/res:plugins]unofficial plugins[/url]
[*]Get [url=https://wiki.flatpress.org/res:language]translation pack[/url] for your language 
[/list]


[h4]How can I help?[/h4]

[list]
[*][url=https://www.flatpress.org/contact/]Contact us[/url] to report bugs or suggest improvements.
[*]Contribute to the development of Flatpress on [url="https://github.com/flatpressblog/flatpress"]GitHub[/url].
[*]Translate FlatPress or the documentation into [url=https://wiki.flatpress.org/res:language]your language[/url].
[*]Share your knowledge and get connected with other FlatPress users on the [url=https://forum.flatpress.org/]forum[/url].
[*]Spread the word! :)
[/list]


[h4]And what now?[/h4]

Now you can [url=login.php]Login[/url] to get to the [url=admin.php]Administration Area[/url] and start posting!

Have fun! :) 

[i]The [url=https://www.flatpress.org]FlatPress[/url] Team[/i]
	
';

$lang ['samplecontent'] ['about'] ['subject'] = 'About';
$lang ['samplecontent'] ['about'] ['content'] = "Write something about yourself here ([url=admin.php?p=static&action=write&page=about]Edit me![/url])";

?>
