<?xml version="1.0" encoding="{$fp_config.locale.charset}" ?>
<rss version="2.0">
	<channel>
		<title>{$flatpress.title}</title>
		<link>{$flatpress.www}</link>
		<description><![CDATA[{$flatpress.subtitle}]]></description>
		<copyright>Copyright {$smarty.now|date_format:"$Y"}, {$flatpress.author}</copyright>
		<managingEditor>{$flatpress.email}</managingEditor>
		<language>{$flatpress.lang}</language>

		<generator>FlatPress</generator>
		{entry_block}
			{entry}
			<item>
		
			<title>{$subject}</title>
			<link>{$id|link:post_link}</link>
			<description><![CDATA[{$content|tag:the_content}]]></description>
			{if ($categories)} <category>{$categories|@filed:false} </category>{/if}
			<guid isPermaLink="true">{$id|link:post_link}</guid>

			<author>{$flatpress.AUTHOR} {$flatpress.EMAIL}</author>
			<pubDate>{$date|date_format:"%a, %d %b %Y %H:%M:%S %z"}</pubDate>
			<comments>{$id|link:comments_link}</comments>
			
			</item>
			{/entry}
		
		{/entry_block}
		
	</channel>
</rss>