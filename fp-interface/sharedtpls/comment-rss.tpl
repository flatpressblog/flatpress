<?xml version="1.0" encoding="{$fp_config.locale.charset}" ?>
<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
	{entry_block}
		{entry}
		
		{assign var=the_comment_link value=$id|link:comments_link}
		<title>{$flatpress.title} » {$subject}</title>
		<link>{$the_comment_link}</link>
		<description><![CDATA[{$flatpress.subtitle}]]></description>
		<copyright>Copyright {$smarty.now|date_format:"$Y"}, {$flatpress.author}</copyright>
 		{*<managingEditor>{$flatpress.email} ({$flatpress.author})</managingEditor>*}
		<language>{$fp_config.locale.lang}</language>
		<atom:link rel="self" href="{'rss2'|theme_comments_feed_link:$id}" type="application/rss+xml" />

		<generator>FlatPress</generator>
		
		{comment_block}
			{comment}
			<item>
		
			<title>{$name}</title>
			<link>{$the_comment_link}#{$id}</link>
			<description><![CDATA[{$content|tag:the_content}]]></description>
			
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
