<?xml version="1.0" encoding="{$fp_config.locale.charset}"?>
<!--

             _                                  ______                     _ 
     /\     | |                                |  ____|                   | |
    /  \    | |_    ___    _ __ ___    ______  | |__      ___    ___    __| |
   / /\ \   | __|  / _ \  | '_ ` _ \  |______| |  __|    / _ \  / _ \  / _` |
  / ____ \  | |_  | (_) | | | | | | |          | |      |  __/ |  __/ | (_| |
 /_/    \_\  \__|  \___/  |_| |_| |_|          |_|       \___|  \___|  \__,_|


The specified file is not intended for direct display in the browser, but solely for the configuration of your newsreader.
To receive my Atom-feed, enter the address {$atom_link} in your newsreader.

Visit https://aboutfeeds.com to get started with newsreaders and subscribing. It's free.


-->
<feed xmlns="http://www.w3.org/2005/Atom">
	<title>{$flatpress.title} » {$dynamic_title}</title>
	<link href="{$smarty.const.BLOG_BASEURL}" rel="alternate" />
	<link href="{$atom_link}" rel="self" type="application/atom+xml" />
	<generator uri="https://www.flatpress.org" version="{$smarty.const.SYSTEM_VER}">
		FlatPress
	</generator>
	<rights>{$flatpress.author} {'Y'|date}</rights>
	<updated>{$smarty.now|date_rfc3339}</updated>
	<author>
		<name>{$flatpress.author}</name>
		<email>{$flatpress.email}</email>
	</author>
	<id>{$smarty.const.BLOG_BASEURL}</id>

	{if $flatpress.subtitle != ""}
	<subtitle>
		<![CDATA[
		{$flatpress.subtitle}
		]]>
	</subtitle>
	{/if}

	{foreach from=$lastcomments_list item=comment}
	<entry>
		<title>{$comment.subject|escape:"html"}</title>
		<link href="{$smarty.const.BLOG_BASEURL}{$comment.entry|cmnt:comments_link}#{$comment.id}" />
		<id>{$smarty.const.BLOG_BASEURL}{$comment.entry|cmnt:comments_link}#{$comment.id}</id>
		<published>{$comment.date|date_format:"%Y-%m-%dT%H:%M:%SZ"}</published>
		<updated>{$comment.date|date_format:"%Y-%m-%dT%H:%M:%SZ"}</updated>
		<summary type="xhtml">
			<div xmlns="http://www.w3.org/1999/xhtml">
				<![CDATA[
				{$comment.name|escape:"html"}: {$comment.content|remove_bb_code|strip_tags|strip|escape:"html"}
				]]>
			</div>
		</summary>
		<author>
			<name>{$comment.name|escape:"html"}</name>

			{if $comment.email != ""}
			<email>{$comment.email}</email>
			{/if}

			{if $comment.url != ""}
			<uri>{$comment.url}</uri>
			{/if}

		</author>
	</entry>
	{/foreach}
</feed>
