<h2>{$plang.head}</h2>
<p>{$plang.description1}</p>

{include file="shared:errorlist.tpl"}

{html_form}

	<p>
	<textarea id="robots" name="robots" 
	{if $cantsave}readonly="readonly" {/if}cols="70" rows="18">{$robots|escape:'html'}</textarea>
	</p>

	<div class="buttonbar">
	{if $cantsave}
		<p><em>{$plang.cantsave}</em></p>
	{else}
		<p>{$lang.admin.plugin.seometataginfo.location}</p>
		<input type="submit" name="robots-submit" value="{$plang.submit}">
	{/if}
	</div>

{/html_form}
