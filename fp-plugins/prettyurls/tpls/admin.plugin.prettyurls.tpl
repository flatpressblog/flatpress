<h2>{$plang.head}</h2>
<p>{$plang.description}</p>

{include file=shared:errorlist.tpl}

	
<form method="post"
	action="{$smarty.const.BLOG_BASEURL}admin.php?{$smarty.server.QUERY_STRING|escape:"html"}"
	enctype="multipart/form-data">
	
	<p>	
	<textarea id="htaccess" name="htaccess" cols="70" rows="16">{$htaccess|escape:'html'}</textarea>
	</p>
	
	<div class="buttonbar">
	{if $cantsave}
	<p><em>{$plang.cantsave}</em></p>
	{else}
	<input type="submit" value="{$plang.submit}"/>
	{/if}
	</div>
		
</form>
