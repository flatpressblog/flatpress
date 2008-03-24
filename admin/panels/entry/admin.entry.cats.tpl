<h2>{$panelstrings.head}</h2>
{include file=shared:errorlist.tpl}


{$panelstrings.descr}

<p><a href="?p=entry&amp;action=cats&amp;do=clear">{$panelstrings.clear}</a></p>



{html_form}
	
		<p>
		<textarea name="content" id="content" rows="20" cols="74">{$catdefs|escape}</textarea><br />
		</p>
	
	
	<div class="buttonbar">

	{html_submit name="save" id="save" value=$panelstrings.submit}
	
	</div>

{/html_form}
