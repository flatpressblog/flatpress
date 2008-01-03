{*assign var=panelstrings value=`$panelstrings.raw`*}
{validate id="content" message=$panelstrings.error.content append="error"}

<h2>{$panelstrings.head}</h2>
<p>{$panelstrings.descr}</p>

{include file='shared:errorlist.tpl'}

{html_form}	
		
		<p>
		<textarea name="content" id="content"
		class="code" rows="20" cols="74">{$pluginconf|escape}</textarea><br />
		</p>
	
	
	<div class="buttonbar">
	{html_submit name="save" id="save" value=$panelstrings.submit}
	</div>

{/html_form}
