{if $noresults}
<p>{$smarty.request.q|string_format:$lang.search.descrnores}</p>
{else}
<p>{$smarty.request.q|string_format:$lang.search.descrres}</p>

{search_result_block}
<ol>
	{search_result}
	<li><a href="{$id|link:post_link}">{$subject}</a></li>
	{/search_result}	
</ol>
{/search_result_block}
{/if}


<p><a href="{$smarty.const.BLOG_BASEURL}search.php">{$lang.search.searchag}</a></p>
