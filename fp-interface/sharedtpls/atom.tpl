<?xml version="1.0" encoding="{$flatpress.charset}"?>
<feed xmlns="http://www.w3.org/2005/Atom">

	<title>{$flatpress.title}</title>
	<subtitle>{$flatpress.subtitle}</subtitle>
	<link href="{$smarty.const.BLOG_BASEURL}" />
	<link rel="self" href="{$smarty.server.REQUEST_URI|escape}" />
	<generator uri="http://www.flatpress.org/" version="{$flatpress.version}">
  		FlatPress
	</generator>
	<rights> {$flatpress.author} {$smarty.now|date_format:'%Y'} </rights>
	<updated>{$smarty.now|date_rfc3339}</updated>
	<author>
		<name>{$flatpress.author}</name>
	</author>
	<id>{$smarty.const.BLOG_BASEURL}</id>
	
	{entry_block}
	{entry}
	<entry>
		<title>{$subject}</title>
		<link href="{$id|link:permalink_link}" />
		<id>{$id|link:post_link}</id>
		{assign var=the_date value=$date|date_rfc3339}
		<published>{$the_date}</published>
		<updated>{$the_date}</updated>
		<content type="xhtml">
			<div xmlns="http://www.w3.org/1999/xhtml"> 
				 {$content|tag:the_content} 
			</div>
		</content>
	</entry>
	{/entry}
	{/entry_block}
	
</feed>