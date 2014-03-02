	<div id="{$id}" class="entry {$date|date_format:"y-%Y m-%m d-%d"}">
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
			
				<li class="entry-info">Posted by {$author} at
				{$date|date_format}
				{if ($categories)} in {$categories|@filed}{/if}
				</li> 
				
				{if !(in_array('commslock', $categories) && !$comments)}
				<li class="link-comments">
				<a href="{$id|link:comments_link}#comments">{$comments|tag:comments_number} 
					{if isset($views)}(<strong>{$views}</strong> views){/if}
				</a>
				</li>
				{/if}
				
				</ul>
				
				<ul class="share">
					<li>
					<span class="share"><a id="share" href="https://www.facebook.com/sharer/sharer.php?u={$id|link:post_link}&t={$subject}" style="text-decoration:none;vertical-align:top;" target="_blank" title="Facebook"><img class="share" src="/fp-content/images/Social/facebook-16x16.png" alt="Facebook" title="Facebook"> Facebook</a></span>
					<span class="share"><a id="share" href="https://twitter.com/intent/tweet?source=webclient&text={$subject}&via=MarcThibeault&url={$id|link:post_link}" target="_blank" title="Twitter"><img class="share" src="/fp-content/images/Social/twitter-16x16.png" alt="Twitter" title="Twitter"> Twitter</a> </span>
					<span class="share"><a id="share" href="https://plusone.google.com/_/+1/confirm?url={$id|link:post_link}&title={$subject}" target="_blank" title="Google+"><img class="share" src="/fp-content/images/Social/google+-16x16.png" alt="Google+" title="Google+"> Google+</a> </span>
					</li>
				</ul>
			
				
	</div>
	
