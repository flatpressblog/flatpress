{include file="header.tpl"}

		<div id="main">
		
		
		{static_block}
		{static}
			<div id="{$id}" class="entry page-{$id}">
				<h2 class="entry-title">{$subject}</h2>
				<p class="date">{$lang.staticauthor.published_by} {$author} {$lang.staticauthor.on} {$date|date_format_daily}</p>
				
				{$content|tag:the_content}
			</div>
		{/static}

		{/static_block}
		
				
		</div>
		
		{include file="widgets.tpl"}
	
{include file="footer.tpl"}
