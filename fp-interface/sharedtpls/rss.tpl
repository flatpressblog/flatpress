<?xml version="1.0" encoding="{$fp_config.locale.charset}" ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<title>{$flatpress.title}</title>
		<link>{$flatpress.www}</link>
		<description><![CDATA[{$flatpress.subtitle}]]></description>
		<copyright>Copyright {$smarty.now|date_format:"$Y"}, {$flatpress.author}</copyright>
		{*<managingEditor>{$flatpress.email} ({$flatpress.author})</managingEditor>*}
		<language>{$fp_config.locale.lang}</language>
		<atom:link rel="self" href="{'rss2'|theme_feed_link}" type="application/rss+xml" />
		<generator>FlatPress</generator>
		{entry_block}
			{entry}
			<item>
		
			<title>{$subject}</title>
			<link>{$id|link:post_link}</link>
			<description><![CDATA[{$content|tag:the_content}]]></description>
			{if ($categories)} <category><![CDATA[ {$categories|@filed:false} ]]></category>{/if}
			<guid isPermaLink="true">{$id|link:post_link}</guid>

			{*<author>{$flatpress.email} ({$flatpress.author})</author>*}
			<pubDate>{'r'|date:$date}</pubDate>
			<comments>{$id|link:comments_link}</comments>

			{foreach from=$enclosure item=encl} 
				<enclosure url="{$encl.url}" length="{$encl.length}" type="{$encl.type}" />
			{/foreach}

			</item>
			{/entry}
		
		{/entry_block}
		
	</channel>
</rss>
