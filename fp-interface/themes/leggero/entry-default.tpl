	<div itemscope itemtype="http://schema.org/BlogPosting" id="{$id}" class="entry {$date|date_format:"y-%Y m-%m d-%d"}">
				{* 	using the following way to print the date, if more 	*} 
				{*	than one entry have been written the same day,		*} 
				{*	 the date will be printed only once 				*}
				
		{$date|date_format_daily:"<h2 class=\"date\">`$fp_config.locale.dateformat`</h2>"}
		
				<h3 itemprop="name">
				<a href="{$id|link:post_link}">
				{$subject|tag:the_title}
				</a>
				</h3>
				{include file=shared:entryadminctrls.tpl}
				
				<div itemprop="articleBody">
				{$content|tag:the_content}
				</div>
				
				<ul class="entry-footer">
			
				<li class="entry-info">Posted by <span itemprop="author">{$author}</span> at
				{$date|date_format}

				<span itemprop="articleSection">
				{if ($categories)} in {$categories|@filed}{/if}
				</span>
				</li> 
				
				{if !(in_array('commslock', $categories) && !$comments)}
				<li class="link-comments">
				<a href="{$id|link:comments_link}#comments">{$comments|tag:comments_number} 
					{if isset($views)}(<strong>{$views}</strong> views){/if}
				</a>
				</li>
				{/if}
				
				</ul>
			
				
	</div>
	
