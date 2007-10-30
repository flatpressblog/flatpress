<h2>{$panelstrings.head}</h2>
	
<p>{$panelstrings.descr}</p>
	{statics}
	<fieldset><legend>{$panelstrings.preview}</legend>
	{include file=previewstatic.tpl}
	</fieldset>
	{/statics}
	<p>{$panelstrings.confirm}</p>
	
	{html_form}
		<input type="hidden" name="page" value="{$pageid}" />
		<div class="buttonbar">
		{html_submit name="delete" id="delete" value=$panelstrings.ok}
		{html_submit name="cancel" id="cancel" value=$panelstrings.cancel}
		</div>
	{/html_form}

