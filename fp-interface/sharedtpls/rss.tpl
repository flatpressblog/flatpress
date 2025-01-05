<?xml version="1.0" encoding="{$fp_config.locale.charset}"?>
<!--

  _____     _____    _____   ___             ______                     _ 
 |  __ \   / ____|  / ____| |__ \           |  ____|                   | |
 | |__) | | (___   | (___      ) |  ______  | |__      ___    ___    __| |
 |  _  /   \___ \   \___ \    / /  |______| |  __|    / _ \  / _ \  / _` |
 | | \ \   ____) |  ____) |  / /_           | |      |  __/ |  __/ | (_| |
 |_|  \_\ |_____/  |_____/  |____|          |_|       \___|  \___|  \__,_|
                                                                          

The specified file is not intended for direct display in the browser, but solely for the configuration of your newsreader.
To receive my RSS2-feed, enter the address {'rss2'|theme_feed_link} in your newsreader.

Visit https://aboutfeeds.com to get started with newsreaders and subscribing. It's free.


-->
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>

		<title>{$flatpress.title} » {$lang.main.entries}</title>
		<link>{$flatpress.www}</link>

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
		<atom:link rel="self" href="{'rss2'|theme_feed_link}" type="application/rss+xml" />
		<generator>FlatPress</generator>

		{entry_block}
			{entry}
			<item>

				<title>{$subject}</title>
				<link>{$id|link:post_link}</link>
				<description>
					<![CDATA[
					{$content|tag:the_content|fix_encoding_issues}
					]]>
				</description>
				{if ($categories)}
				<category>
					<![CDATA[
					{$categories|@filed:false}
					]]>
				</category>
				{/if}
				<guid isPermaLink="true">{$id|link:post_link}</guid>

				{*<author>{$flatpress.email} ({$flatpress.author})</author>*}
				<pubDate>{'r'|date:$date}</pubDate>
				<comments>{$id|link:comments_link}</comments>

				{if isset($enclosure)}
					{foreach from=$enclosure item=encl} 
					<enclosure url="{$encl.url}" length="{$encl.length}" type="{$encl.type}" />
					{/foreach}
				{/if}

			</item>
			{/entry}
		{/entry_block}

	</channel>
</rss>
