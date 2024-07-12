<?xml version="1.0" encoding="{$fp_config.locale.charset}"?>
	{entry_block}
		{entry}
		{assign var=the_comment_link value=$id|link:comments_link}

<!--

  _____     _____    _____   ___             ______                     _ 
 |  __ \   / ____|  / ____| |__ \           |  ____|                   | |
 | |__) | | (___   | (___      ) |  ______  | |__      ___    ___    __| |
 |  _  /   \___ \   \___ \    / /  |______| |  __|    / _ \  / _ \  / _` |
 | | \ \   ____) |  ____) |  / /_           | |      |  __/ |  __/ | (_| |
 |_|  \_\ |_____/  |_____/  |____|          |_|       \___|  \___|  \__,_|
                                                                          

The specified file is not intended for direct display in the browser, but solely for the configuration of your newsreader.
To receive my RSS2-feed, enter the address {'rss2'|theme_comments_feed_link:$id} in your newsreader.

Visit https://aboutfeeds.com to get started with newsreaders and subscribing. It's free.


-->
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:dc="http://purl.org/dc/elements/1.1/">
	<channel>

		<title>
			<![CDATA[
			{$flatpress.title} » {$subject}
			]]>
		</title>
		<link>{$the_comment_link}</link>

		{if $flatpress.subtitle!=""}
		<description>
			<![CDATA[
			{$flatpress.subtitle}
			]]>
		</description>
		{/if}

		<copyright>Copyright {'Y'|date}, {$flatpress.author}</copyright>
		{*<managingEditor>{$flatpress.email} ({$flatpress.author})</managingEditor>*}
		<language>{$fp_config.locale.lang}</language>
		<atom:link rel="self" href="{'rss2'|theme_comments_feed_link:$id}" type="application/rss+xml" />

		<generator>FlatPress</generator>

		{comment_block}
			{comment}
			<item>

				<title>{$name}</title>
				<link>{$the_comment_link}#{$id}</link>
				<description>
					<![CDATA[
					{$content|tag:the_content}
					]]>
				</description>

				<guid isPermaLink="true">{$the_comment_link}#{$id}</guid>

				<dc:creator>{$name}</dc:creator>
				<pubDate>{'r'|date:$date}</pubDate>

			</item>
			{/comment}
		{/comment_block}

		{/entry}
	{/entry_block}

	</channel>
</rss>
