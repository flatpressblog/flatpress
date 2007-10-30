<?xml version="1.0" encoding="{$flatpress.charset}"?>
<feed xmlns="http://www.w3.org/2005/Atom">

	<title>{$flatpress.title|tag:wp_title:':'}</title>
	<subtitle>{$flatpress.subtitle}</subtitle>
	<link href="{$smarty.const.BLOG_BASEURL}" />
	<link rel="self" href="{$smarty.server.REQUEST_URI|escape}" />
	<generator uri="http://www.flatpress.org/" version="{$flatpress.version}">
  		FlatPress
	</generator>
	<rights> {$flatpress.author} {$smarty.now|date_format:'%Y'} </rights>
	
	{entry_block}
	{entry}
	
	<updated>{$date|date_rfc3339}</updated>
	<author>
		<name>{$author}</name>
	</author>
	<id>{$id|link:post_link}</id>
	
	{assign var=the_comment_link value=$id|link:comments_link}

	{comment_block}
	{comment}
	<entry>
		<title>{$name}</title>
		<author>
			<name>{$name}</name>
			{if $www}<uri>{$www}</uri>{/if}
		</author>
		<link href="{$the_comment_link}#{$id}" />
		<id>{$the_comment_link}#{$id}</id>
		{assign var=the_date value=$date|date_rfc3339}
		<published>{$the_date}</published>
		<updated>{$the_date}</updated>
		<content type="xhtml">
			<div xmlns="http://www.w3.org/1999/xhtml"> 
				 {$content|tag:the_content} 
			</div>
		</content>
	</entry>
	{/comment}
	{/comment_block}
	
	{/entry}
	{/entry_block}
	
</feed>