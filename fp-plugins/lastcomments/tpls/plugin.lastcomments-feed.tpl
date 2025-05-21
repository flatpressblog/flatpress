<?xml version="1.0" encoding="{$fp_config.locale.charset}" ?>
<rss version="2.0">
	<channel>
		
		<title>{$flatpress.title}</title>
		<link>{$flatpress.subtitle}</link>
		<description><![CDATA[{$flatpress.subtitle}]]></description>
		<copyright>Copyright {$smarty.now|date_format:"$Y"}, {$flatpress.author}</copyright>
		<managingEditor>{$flatpress.email}</managingEditor>
		<language>{$flatpress.lang}</language>

		<generator>FlatPress</generator>
		
			{foreach from=$lastcomments_list item=comment}
			<item>
			{assign var=comm_link value=$comment.entry|tag:comments_link}
			<title>{$comment.name}</title>
			<link>{$comm_link}#{$comment.id}</link>
			<description><![CDATA[{$comment.content}]]></description>
			
			<guid isPermaLink="true">{$comm_link}#{$comment.id}</guid>

			<author>{$comment.name} </author>
			<pubDate>{$date|date_format:"%a, %d %b %Y %H:%M:%S %z"}</pubDate>
			
			
			</item>
			{/foreach}
		
		
	</channel>
</rss>
