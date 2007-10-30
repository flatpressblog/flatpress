<h2>{$panelstrings.head}</h2>
	
<p>{$panelstrings.descr}</p>
	
	{entry_block}
	{html_form}
	<fieldset><legend>{$panelstrings.preview}</legend>
	{include file=preview.tpl}
	</fieldset>
	<p>{$panelstrings.confirm}</p>
	
		<input type="hidden" name="entry" value="{$id}" />
		<div class="buttonbar">
		{html_submit  name="delete" id="delete" value=$panelstrings.ok}
		{html_submit  name="cancel" id="cancel" value=$panelstrings.cancel}
		</div>
	{/html_form}
	{/entry_block}
	
