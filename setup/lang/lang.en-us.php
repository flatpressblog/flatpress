<?php
/*
LangId: English
*/
	$lang['locked'] = array(
		'head'	=> 'Setup has been locked',
		'descr' => 
		'It looks like you already run setup, because 
		we found the lockfile <code>%s</code>.
		
		If you need to restart setup, please delete this file first.
		
		<strong >Remember!</strong> It\'s not safe keeping <code>setup.php</code> and the <code>setup/</code> directory 	on your server, we suggest you to delete it!
		
		<ul>
		<li><a href="%s">Ok, take me back to my blog</a></li>
		<li><a href="%s">I\'ve deleted the file, restart the setup</a></li>
		</ul>'
	);

	$lang['step1'] = array (
		'head'	=> 'Welcome to FlatPress',
		'descr' => 
		'Thank you for choosing <strong>FlatPress</strong>!
		
		Before you can start having fun with your brand new blog, we have to ask you a very few questions. 
		
		Don\'t worry, it won\'t take you long!',
		
		'descrl1' => 'Select your language.',
		'descrl2' => '<a class="hint" onclick="toggleinfo();">Not in the list?</a>',
		
		'descrlang' =>
		
		'If you don\'t see your language in this list, you might want to see if there is <a href="http://wiki.flatpress.org/res:language">a language pack</a> for this version:
		
		<pre>%s</pre>
		
		To install the language pack, upload the content of the package in your <code>flatpress/</code>, and overwrite all, then <a href="./setup.php">restart this setup</a>.',
		
		'descrw' =>
		'The <strong>only thing</strong> you need for FlatPress to work is a <em>writable</em> directory.
		
		Please make this directory <a class="hint" href="http://wiki.flatpress.org/doc:setup#making_the_contents_directory_writable">writable</a> before you continue. 
		
		<pre>%s</pre>'
		
	);
	
	$lang['step2'] = array (
		'head'	=> 'Create user',
		'descr' => 
		'You\'re already almost done, fill in the following details:',
		'fpuser'	=> 'Username',
		'fppwd' 	=> 'Password',
		'fppwd2'	=> 'Re-type password',
		'www'		=> 'Home Page',
		'email'		=> 'E-Mail'
		
	);
	
	$lang['step3'] = array (
		'head'	=> 'Done',
		'descr' => 
		'<strong>End of the story</strong>. 
		
		Unbelievable? 
		
		And you\'re right: <strong>the story has just begun</strong>, but <strong>writing is up to you</strong>!
		
		<ul>
		<li>See <a href="%s">how the home page looks like</a></li>
		<li>Have fun! <a href="%s">Login now!</a></li>
		<li>Do you feel like dropping us a line? <a href="http://www.flatpress.org/">Go to FlatPress.org!</a></li>
		</ul>
		
		And thank you for choosing FlatPress!'
		
	);
	
	$lang['buttonbar'] = array(
		'next'	=> 'Next >'
	);
	
	$lang['samplecontent'] = array();
	
	$lang['samplecontent']['menu']['subject'] = 'Menu'; 
	$lang['samplecontent']['menu']['content'] = <<<MENU
[list]
[*][url=?]Home[/url]
[*][url=?paged=1]Blog[/url]
[*][url=static.php?page=about]About[/url]
[*][url=contact.php]Contact[/url]
[/list]
MENU;

	$lang['samplecontent']['entry']['subject'] = 'Welcome to FlatPress!' ;
	$lang['samplecontent']['entry']['content'] = <<<CONT
This is a sample entry, posted to show you some of the features of [url=http://www.flatpress.org]FlatPress[/url].

The more tag allows you to create a "jump" between an excerpt and the complete article.

[more] 


[h4]Styling[/h4]

The default way to style and format your content is [url=http://wiki.flatpress.org/doc:plugins:bbcode]BBcode[/url] (bulletin board code). BBCode is an easy way to style your posts. Most common codes are allowed. Like [b] for [b]bold[/b] (html: strong), [i] for [i]italics[/i] (html: em), etc.

[quote]
There are also [b]quote[/b] blocks to display your favourite quotations. 
[/quote]

[code]
and 'code' displays your snippets in a monospaced fashion
[/code]

img and url tag have also special options. You can find out more on the [url=http://www.flatpress.org]FP official website[/url].


[h4]Entries (posts) and Static pages[/h4]

This is an entry, while [url=static.php?page=about]About[/url] is a [b]static page[/b]. A static page is an entry (a post) which cannot be commented, and which does not appear together with the normal posts of the blog.

Static pages are useful to create general information pages; with this new version of FlatPress you are allowed to make one of these pages the [b]opening page[/b] for your visitors. This means that now with FlatPress you can now run a complete non-blog site. The option to make a static page your start page is in the [b]option panel[/b] of the [url=admin.php]admin area[/url]   


[h4]Plugins[/h4]

FlatPress is very customizable, and supports [url=http://wiki.flatpress.org/doc:plugins]plugins[/url] to extend its power. BBCode is itself a plugin!

We have created some more sample content, to show you some of the FP well hidden functions and gems :) ; you can find two [b]static pages[/b] ready to accept your contents ([url=static.php?page=about]About me[/url]) and links ([url=static.php?page=menu]Menu[/url]): notice that your links will appear on your sidebar as well, this is the magic of the [b]blockparser widget[/b]: see the [url=http://wiki.flatpress.org/doc:faq]FAQ[/url] for this and more. 


[h4]Widgets[/h4]

There isn't a single fixed element in the sidebar(s). All the elements you can find in the bars sourrounding this text are completely positionable, and most of them are customizable as well. Some themes even provide a panel interface in the admin area.  

These elements are called [url=http://wiki.flatpress.org/doc:widgets]widgets[/url].

For more on widgets and [url=http://wiki.flatpress.org/doc:tips:widgets]some tips[/url] to get nice effects, read the [url=http://wiki.flatpress.org/]wiki[/url]


[h4]See more[/h4]

Want to see more?

[list]
[*]Follow the [url=http://www.flatpress.org/home/blog.php]official blog[/url] to know what's going on in the FlatPress world
[*]Go to the [url=http://www.flatpress.org/forums/categories.php]forums[/url] for support and chit-chat
[*]Get [b]great themes[/b] on the [url=http://themes.flatpress.org]editor's pick[/url] site or choose from [url=http://wiki.flatpress.org/res:themes]other users' submissions[/url]!
[*]Check out the [url=http://wiki.flatpress.org/res:plugins]unofficial plugins[/url]
[*]Get [url=http://wiki.flatpress.org/res:language]translation pack[/url] for your language 
[/list]


[h4]How can I help?[/h4]

[list]
[*]Support the project with a [url=http://www.flatpress.org/home/static.php?page=donate]small donation[/url]
[*]Translate FlatPress or the documentation into [url=http://wiki.flatpress.org/res:language]your language[/url]
[*]Share your knowledge with other people on the [url=http://www.flatpress.org/forums/categories.php]forums[/url]
[*]Spread the word!
[/list]


[h4]And what now?[/h4]

Now you can [url=login.php]Login[/url] to get to the [url=admin.php]Control Panel[/url] and start posting!

Have fun! :) 

[i][url=http://www.nowhereland.it]NoWhereMan[/url] and the FlatPress Team[/i]
	
CONT;


	$lang['samplecontent']['about']['subject']	= 'About';
	$lang['samplecontent']['about']['content']	= 
		"Write something about yourself here ([url=admin.php?p=static&action=write&page=about]Edit me![/url])";

?>
