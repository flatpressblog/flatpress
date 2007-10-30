	<div id="{$id}" class="entry">
				{* 	using the following way to print the date, if more 	*} 
				{*	than one entry have been written the same day,		*} 
				{*	 the date will be printed only once 				*}
				
		{$date|date_format_daily:"<h2 class=\"date\">`$fp_config.locale.dateformat`</h2>"}
		
				<h3>
				<a href="{$id|link:post_link}">
				{$subject|tag:the_title}
				</a>
				</h3>
				{include file=shared:entryadminctrls.tpl}
				
				
				{$content|tag:the_content}
			
				
				<ul class="entry-footer">
			
				<li>Posted by {$author} at
				{$date|date_format}
				{if ($categories)} in {$categories|@filed}{/if}
				</li> 
				
				<li>
				<a href="{$id|link:comments_link}#comments">{$comments|tag:comments_number} 
					{if isset($views)}(<strong>{$views}</strong> views){/if}
				</a>
				</li>
				
				
				</ul>
			
				
	</div>
	