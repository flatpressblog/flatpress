<?xml version="1.0" encoding="{$fp_config.locale.charset}"?>
{entry_block}
	{entry}
	{assign var=the_comment_link value=$id|link:comments_link}

<!--

             _                                  ______                     _ 
     /\     | |                                |  ____|                   | |
    /  \    | |_    ___    _ __ ___    ______  | |__      ___    ___    __| |
   / /\ \   | __|  / _ \  | '_ ` _ \  |______| |  __|    / _ \  / _ \  / _` |
  / ____ \  | |_  | (_) | | | | | | |          | |      |  __/ |  __/ | (_| |
 /_/    \_\  \__|  \___/  |_| |_| |_|          |_|       \___|  \___|  \__,_|


The specified file is not intended for direct display in the browser, but solely for the configuration of your newsreader.
To receive my Atom-feed, enter the address {'atom'|theme_comments_feed_link:$id} in your newsreader.

Visit https://aboutfeeds.com to get started with newsreaders and subscribing. It's free.


-->
<feed xmlns="http://www.w3.org/2005/Atom">

	<title>{$flatpress.title|tag:wp_title:':'}</title>

	{if $flatpress.subtitle!=""}
	<subtitle>{$flatpress.subtitle}</subtitle>
	{/if}

	<link href="{$smarty.const.BLOG_BASEURL}" />
	<link rel="self" href="{'atom'|theme_comments_feed_link:$id}" type="application/atom+xml" />
	<generator uri="https://www.flatpress.org/" version="{$smarty.const.SYSTEM_VER}">
		FlatPress
	</generator>
	<rights> {$flatpress.author} {$smarty.now|date_format:'%Y'} </rights>

	<updated>{$date|date_rfc3339}</updated>
	<author>
		<name>{$author}</name>
	</author>
	<id>{$id|link:post_link}</id>

		{comment_block}
			{comment}
			<entry>

				<title>{$name}</title>
				<author>
					<name>{$name}</name>
					{if $www}
					<uri>{$www}</uri>
					{/if}
				</author>
				<link href="{$the_comment_link}#{$id}" />
				<id>{$the_comment_link}#{$id}</id>
				{assign var=the_date value=$date|date_rfc3339}
				<published>{$the_date}</published>
				<updated>{$the_date}</updated>
				<content type="xhtml">
					<div xmlns="http://www.w3.org/1999/xhtml">
						<![CDATA[
						{$content|tag:the_content|strip_tags|strip|truncate:180:"...":true|escape}
						]]>
					</div>
				</content>

			</entry>
			{/comment}
		{/comment_block}

	{/entry}
{/entry_block}

</feed>
