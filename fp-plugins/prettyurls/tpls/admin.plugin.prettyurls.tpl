<h2>{$plang.head}</h2>
<p>{$plang.description}</p>

{include file=shared:errorlist.tpl}

	
{html_form}
	
	<p>	
	<textarea id="htaccess" name="htaccess" 
	{if $cantsave}readonly="readonly" {/if}cols="70" rows="16">{$htaccess|escape:'html'}</textarea>
	</p>
	
	<div class="buttonbar">
	{if $cantsave}
	<p><em>{$plang.cantsave}</em></p>
	{else}
	<input type="submit" value="{$plang.submit}"/>
	{/if}
	</div>
		
{/html_form}