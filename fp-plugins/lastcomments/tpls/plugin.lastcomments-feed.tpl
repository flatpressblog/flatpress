<?xml version="1.0" encoding="{$fp_config.locale.charset}"?>
<!--

  _____     _____    _____   ___             ______                     _ 
 |  __ \   / ____|  / ____| |__ \           |  ____|                   | |
 | |__) | | (___   | (___      ) |  ______  | |__      ___    ___    __| |
 |  _  /   \___ \   \___ \    / /  |______| |  __|    / _ \  / _ \  / _` |
 | | \ \   ____) |  ____) |  / /_           | |      |  __/ |  __/ | (_| |
 |_|  \_\ |_____/  |_____/  |____|          |_|       \___|  \___|  \__,_|
                                                                          

The specified file is not intended for direct display in the browser, but solely for the configuration of your newsreader.
To receive my RSS2-feed, enter the address {$rss_link} in your newsreader.

Visit https://aboutfeeds.com to get started with newsreaders and subscribing. It's free.


-->
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:dc="http://purl.org/dc/elements/1.1/">
	<channel>
		<title>{$flatpress.title} » {$dynamic_title}</title>
		<link>{$smarty.const.BLOG_BASEURL}</link>

		{if $flatpress.subtitle != ""}
		<description>
			<![CDATA[
			{$flatpress.subtitle}
			]]>
		</description>
		{/if}

		<copyright>Copyright {'Y'|date}, {$flatpress.author}</copyright>
		<managingEditor>{$flatpress.email} ({$flatpress.author})</managingEditor>
		<language>{$flatpress.lang}</language>
		<atom:link rel="self" href="{$rss_link}" type="application/rss+xml" />

		<generator>FlatPress</generator>
			{foreach from=$lastcomments_list item=comment}
			<item>
			{assign var=comm_link value=$comment.entry|cmnt:comments_link}
				<title>{$comment.subject|escape:"html"}</title>
				<link>{$smarty.const.BLOG_BASEURL}{$comm_link}#{$comment.id}</link>
				<description>
					<![CDATA[
					{$comment.name|escape:"html"}: {$comment.content|bbcode}
					]]>
				</description>

				<guid isPermaLink="true">{$smarty.const.BLOG_BASEURL}{$comm_link}#{$comment.id}</guid>

				<dc:creator>{$comment.name|escape:"html"}</dc:creator>

				<pubDate>{$comment.date}</pubDate>
			</item>
			{/foreach}
	</channel>
</rss>
