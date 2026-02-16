<?php
/*
 * LangId: English
 */
$lang ['setup'] = array(
	'setup' => 'Setup'
);

$lang ['locked'] = array(
	'head' => 'Setup has been locked',
	'descr' => 'It looks like you already run setup, because we found the lockfile <code>%s</code>.

		If you need to restart setup, please delete this file first.

		<strong >Remember!</strong> It\'s not safe keeping <code>setup.php</code> and the <code>setup/</code> directory on your server, we suggest you to delete it!

		<ul>
		<li><a href="%s">Ok, take me back to my blog</a></li>
		<li><a href="%s">I\'ve deleted the file, restart the setup</a></li>
		</ul>'
);

$lang ['err'] = array(
	'setuprun1' => 'The installation is running.',

	'setuprun2' => 'The installation is already underway: If you are the administrator, you can delete ',
	'setuprun3' => ' to restart.',
	'writeerror' => 'Writing errors',

	'fpuser1' => ' is not a valid user. ' . //
		'The user name must be alphanumeric and must not contain any spaces.',
	'fpuser2' => ' is not a valid user. ' . //
		'The user name may only contain letters, numbers and 1 underscore.',
	'fppwd' => 'The password must contain at least 6 characters and must not contain any spaces.',
	'fppwd2' => 'The passwords do not match.',
	'email' => ' is not a valid e-mail address.',
	'www' => ' is not a valid URL.',
	'error' => '<p><big>Error!</big> ' . //
		'The following errors occurred while processing the form:</p><ul>'
);

$lang ['step1'] = array(
	'head' => 'Welcome to FlatPress!',
	'descr' => 'Thank you for choosing <strong>FlatPress</strong>.

		Before you can start having fun with your brand new blog, we have to ask you a very few questions.

		Don\'t worry, it won\'t take you long!',
	'descrl1' => 'Select your language.',
	'descrl2' => '<a class="hint" onclick="toggleinfo();">Not in the list?</a>',
	'descrlang' => 'If you don\'t see your language in this list, you might want to see if there is <a href="https://wiki.flatpress.org/res:language" target="_blank" rel="external">a language pack</a> for this version:

		<pre>%s</pre>

		To install the language pack, upload the content of the package in your <code>flatpress/</code>, and overwrite all, then <a href="./setup.php">restart this setup</a>.',
	'descrw' => 'The <strong>only thing</strong> you need for FlatPress to work is a <em>writable</em> directory.

		<pre>%s</pre>'
);

$lang ['step2'] = array(
	'head' => 'Create user',
	'descr' => 'You\'re already almost done, fill in the following details:',
	'fpuser' => 'Username',
	'fppwd' => 'Password',
	'fppwd2' => 'Re-type password',
	'www' => 'Home Page',
	'email' => 'E-Mail'
);

$lang ['step3'] = array(
	'head' => 'Done',
	'descr' => '<strong>End of the story</strong>.

		Unbelievable?

		And you\'re right: <strong>the story has just begun</strong>, but <strong>writing is up to you</strong>!

		<ul>
		<li>See <a href="%s">how the home page looks like</a></li>
		<li>Have fun! <a href="%s">Login now!</a></li>
		<li>Do you feel like dropping us a line? <a href="https://www.flatpress.org/" target="_blank" rel="external">Go to FlatPress.org!</a></li>
		</ul>

		And thank you for choosing FlatPress!'
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
$lang ['samplecontent'] ['entry'] ['content'] = 'This is a sample entry, posted to show you some of the features of [url=https://www.flatpress.org target=_blank rel=external]FlatPress[/url].

The more tag allows you to create a "jump" between an excerpt and the complete article.

[more]


[h4]Styling[/h4]

The default way to style and format your content is [url=https://wiki.flatpress.org/doc:plugins:bbcode target=_blank rel=external]BBcode[/url] (bulletin board code). BBCode is an easy way to style your posts. Most common codes are allowed. Like [b] for [b]bold[/b] (html: strong), [i] for [i]italics[/i] (html: em), etc.

[quote]There are also [b]quote[/b] blocks to display your favourite quotations.[/quote]

[code]And \'code\' displays your snippets in a monospaced fashion.
It also supports
   indented content.[/code]

img and url tag have also special options. You can find out more on the [url=https://wiki.flatpress.org/doc:plugins:bbcode target=_blank rel=external]FlatPress-Wiki[/url].


[h4]Entries (posts) and Static pages[/h4]

This is an entry, while [url=static.php?page=about]About[/url] is a [b]static page[/b]. A static page is an entry (a post) which cannot be commented, and which does not appear together with the normal posts of the blog.

Static pages are useful to create general information pages. You can also make one of these pages the [b]opening page[/b] for your visitors. This means that with FlatPress you could also run a complete non-blog site. The option to make a static page your start page is in the [b]option panel[/b] of the [url=admin.php]admin area[/url].


[h4]Plugins[/h4]

FlatPress is very customizable, and supports [url=https://wiki.flatpress.org/doc:plugins:standard target=_blank rel=external]plugins[/url] to extend its power. BBCode is a plugin itself.

We have created some more sample content, to show you some of the FP well hidden functions and gems :)
You can find two [b]static pages[/b] ready to accept your contents:
[list]
[*][url=static.php?page=about]About me[/url]
[*][url=static.php?page=menu]Menu[/url] (notice that the links in this page will appear on your sidebar as well - this is the magic of the [b]blockparser widget[/b]. See the [url=https://wiki.flatpress.org/doc:faq target=_blank rel=external]FAQ[/url] for this and more!)
[/list]

With the [b]PhotoSwipe plugin[/b] you can now place your images even easier, either as float="left"- or float="right" aligned single image, enclosed by the text.
You can even use the \'gallery\' element to present entire galleries to your visitors. How easy it works, [url=https://wiki.flatpress.org/doc:plugins:photoswipe target=_blank rel=external]you can learn here[/url].


[h4]Widgets[/h4]

There isn\'t a single fixed element in the sidebar(s). All the elements you can find in the bars surrounding this text are completely positionable, and most of them are customizable as well. Some themes even provide a panel interface in the admin area.  

These elements are called [b]widgets[/b]. For more on widgets and [url=https://wiki.flatpress.org/doc:tips:widgets target=_blank rel=external]some tips[/url] to get nice effects, take a look at the [url=https://wiki.flatpress.org/ target=_blank rel=external]wiki[/url].


[h4]Themes[/h4]

[gallery="images/Leggero-Themepreview/" width="140"]
With the FlatPress-Leggero theme you have 4 style templates at your disposal - from classic to modern. These templates are a wonderful start to create something of your own.


[h4]See more[/h4]

Want to see more?

[list]
[*]Follow the [url=https://www.flatpress.org/?x target=_blank rel=external]official blog[/url] to know what\'s going on in the FlatPress world.
[*]Visit the [url=https://forum.flatpress.org/ target=_blank rel=external]forum[/url] for support and chit-chat.
[*]Get [b]great themes[/b] from [url=https://wiki.flatpress.org/res:themes target=_blank rel=external]other users\' submissions[/url]!
[*]Check out the [url=https://wiki.flatpress.org/res:plugins target=_blank rel=external]plugins[/url].
[*]Get [url=https://wiki.flatpress.org/res:language target=_blank rel=external]translation pack[/url] for your language.
[*]You can also follow FlatPress on [url=https://fosstodon.org/@flatpress target=_blank rel=external]Mastodon[/url].
[/list]


[h4]How can I help?[/h4]

[list]
[*]Support the project with a [url=https://www.flatpress.org/home/static.php?page=donate target=_blank rel=external]small donation[/url].
[*][url=https://www.flatpress.org/contact/ target=_blank rel=external]Contact us[/url] to report bugs or suggest improvements.
[*]Contribute to the development of FlatPress on [url=https://github.com/flatpressblog/flatpress target=_blank rel=external]GitHub[/url].
[*]Translate FlatPress or the documentation into [url=https://wiki.flatpress.org/res:language target=_blank rel=external]your language[/url].
[*]Share your knowledge and get connected with other FlatPress users on the [url=https://forum.flatpress.org/ target=_blank rel=external]forum[/url].
[*]Spread the word! :)
[/list]


[h4]And what now?[/h4]

Now you can [url=login.php]Login[/url] to get to the [url=admin.php]Administration Area[/url] and start posting!

Have fun! :)

[i]The [url=https://www.flatpress.org target=_blank rel=external]FlatPress[/url] Team[/i]

';

$lang ['samplecontent'] ['about'] ['subject'] = 'About';
$lang ['samplecontent'] ['about'] ['content'] = 'Write something about yourself here. ([url=admin.php?p=static&action=write&page=about]Edit me![/url])';

$lang ['samplecontent'] ['privacy-policy'] ['subject'] = 'Privacy policy';
$lang ['samplecontent'] ['privacy-policy'] ['content'] = 'In some countries, if you use the Akismet Antispam service, for example, it is necessary to provide your visitors with a privacy policy. A privacy policy may also be necessary if the visitor can use the contact form or the comment function.

[b]Tip:[/b] There are lots of templates and generators on the Internet.

You can insert them here. ([url=admin.php?p=static&action=write&page=privacy-policy]Edit me![/url])

If you activate the CookieBanner plugin, your visitors will be able to go directly to this page in the contact form and in the comment function.
';
?>
