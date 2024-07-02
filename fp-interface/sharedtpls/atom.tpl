<?xml version="1.0" encoding="{$fp_config.locale.charset}"?>
<!--

             _                                  ______                     _ 
     /\     | |                                |  ____|                   | |
    /  \    | |_    ___    _ __ ___    ______  | |__      ___    ___    __| |
   / /\ \   | __|  / _ \  | '_ ` _ \  |______| |  __|    / _ \  / _ \  / _` |
  / ____ \  | |_  | (_) | | | | | | |          | |      |  __/ |  __/ | (_| |
 /_/    \_\  \__|  \___/  |_| |_| |_|          |_|       \___|  \___|  \__,_|


The specified file is not intended for direct display in the browser, but solely for the configuration of your newsreader.
To receive my Atom-feed, enter the address {if function_exists('plugin_prettyurls_setup')}{$smarty.const.BLOG_BASEURL}feed/atom/{else}{$smarty.const.BLOG_BASEURL}/rss.php{/if} in your newsreader.

Visit https://aboutfeeds.com to get started with newsreaders and subscribing. It's free.


-->
<feed xmlns="http://www.w3.org/2005/Atom">

	<title>{$flatpress.title}</title>

	{if $flatpress.subtitle!=""}
	<subtitle>{$flatpress.subtitle}</subtitle>
	{/if}

	<link href="{$smarty.const.BLOG_BASEURL}" />
	<link rel="self" href="{'atom'|theme_feed_link}" />
	<generator uri="https://www.flatpress.org/" version="{$smarty.const.SYSTEM_VER}">
		FlatPress
	</generator>
	<rights> {$flatpress.author} {'Y'|date} </rights>
	<updated>{$smarty.now|date_rfc3339}</updated>
	<author>
		<name>{$flatpress.author}</name>
	</author>
	<id>{$smarty.const.BLOG_BASEURL}</id>

	{entry_block}
	{entry}
	<entry>
		<title>{$subject}</title>
		<link href="{$id|link:post_link}" />

		<id>{$id|link:post_link}</id>
		{assign var=the_date value=$date|date_rfc3339}
		<published>{$the_date}</published>
		<updated>{$the_date}</updated>
		<content type="xhtml">
			<div xmlns="http://www.w3.org/1999/xhtml"> 
				<![CDATA[{$content|tag:the_content}]]>
			</div>
		</content>

		{if isset($enclosure)}
			{foreach from=$enclosure item=encl}
			<link rel="enclosure" 
				href="{$encl.url}" 
				title="{$encl.title}"
				length="{$encl.length}" 
				type="{$encl.type}" />
			{/foreach}
		{/if}

	</entry>
	{/entry}
	{/entry_block}

</feed>
