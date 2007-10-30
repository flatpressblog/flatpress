{*assign var=panelstrings value=`$panelstrings.raw`*}
{validate id="content" message=$panelstrings.error.content append="error"}

<h2>{$panelstrings.head}</h2>
<p>{$panelstrings.descr}</p>

{include file='shared:errorlist.tpl'}

<form method="post"
	action="{$smarty.const.BLOG_BASEURL}admin.php?{$smarty.server.QUERY_STRING|escape:"html"}"
	enctype="multipart/form-data">
	
	
		
		<p>
		<textarea name="content" id="content"
		class="code" rows="20" cols="74">{$pluginconf|escape}</textarea><br />
		</p>
	
	
	<div class="buttonbar">
	{html_submit name="save" id="save" value=$panelstrings.submit}
	</div>

</form>
