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
To receive my Atom-feed, enter the address {'atom'|theme_comments_feed_link:$id|escape:'html'} in your newsreader.

Visit https://aboutfeeds.com to get started with newsreaders and subscribing. It's free.


-->
<feed xmlns="http://www.w3.org/2005/Atom">

	<title>{$flatpress.title|tag:wp_title:':'|escape:'html'} » {$subject|tag:the_title|escape:'html'} » {$lang.main.comments|escape:'html'}</title>

	{if $flatpress.subtitle != ""}
	<subtitle>{$flatpress.subtitle|escape:'html'}</subtitle>
	{/if}

	<link href="{$smarty.const.BLOG_BASEURL|escape:'html'}" />
	<link rel="self" href="{'atom'|theme_comments_feed_link:$id|escape:'html'}" type="application/atom+xml" />
	<generator uri="https://www.flatpress.org/" version="{$smarty.const.SYSTEM_VER}">
		FlatPress
	</generator>
	<rights>{$flatpress.author|escape:'html'} {$smarty.now|date_format:'%Y'}</rights>

	<updated>{$date|date_rfc3339}</updated>
	<author>
		<name>{$author|escape:'html'}</name>
	</author>
	<id>{$id|link:post_link|escape:'html'}</id>

		{comment_block}
			{comment}
			<entry>

				<title>{$name|escape:'html'}</title>
				<author>
					<name>{$name|escape:'html'}</name>
					{if isset($www) && $www != ""}
					<uri>{$www|escape:'html'}</uri>
					{/if}
				</author>
				<link href="{$the_comment_link|escape:'html'}#{$id}" />
				<id>{$the_comment_link|escape:'html'}#{$id}</id>
				{assign var=the_date value=$date|date_rfc3339}
				<published>{$the_date}</published>
				<updated>{$the_date}</updated>
				<content type="xhtml">
					<div xmlns="http://www.w3.org/1999/xhtml">
						<![CDATA[
						{$content|tag:the_content|strip_tags|strip|escape|fix_encoding_issues}
						]]>
					</div>
				</content>

			</entry>
			{/comment}
		{/comment_block}

	{/entry}
{/entry_block}

</feed>
