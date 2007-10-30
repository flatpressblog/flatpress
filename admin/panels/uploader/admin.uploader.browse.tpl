<h2>{$panelstrings.head}</h2>
<p>{$panelstrings.descr}</p>

{include file='shared:errorlist.tpl'}



<form method="post"
	action="{$smarty.const.BLOG_BASEURL}admin.php?{$smarty.server.QUERY_STRING|escape:"html"}"
	enctype="multipart/form-data">
	
	
	{*<fieldset><legend>{$panelstrings.fset1}</legend>
		<input type="file" name="upload[]" />
	
	<div class="alignright">
	{html_submit name="upload" id="upload" value=$panelstrings.submit}	
	</div>
	</fieldset>
	*}
	
	<div>
	
	{foreach from=$dirs item=dirpath key=dirname}
		<ul><li> <a href="{$dirpath}"> {$dirname} </a> </li></ul>
	{/foreach}
	
	
	{if $files}
	<ul id="admin-uploader-thumbs">
	{foreach from=$files item=filepath key=filename}
			<li class="thumb"> 
				<h5>{$filename}</h5>
				<a href="{$filepath}"><img src="{$filepath}" alt="{$filename}" /></a> 
			</li>
	{/foreach}
	</ul>
	{/if}
	
	</div>
	
	{*
	<table>
	<thead><tr><th> name </th><th> date </th></tr></thead>
	<tbody>
		<tr><td> <a href="{$parent}"> .. </a> </td> <td> ... </td></tr>
	
	{foreach from=$dirs item=dirpath key=dirname}
		<tr><td> <a href="{$dirpath}">{$dirname} </a> </td> <td> ... </td></tr>
	{/foreach}
	
	{foreach from=$files item=filepath key=filename}
		<tr><td> {$filename} </td> <td> ... </td></tr>
	{/foreach}
	</tbody>
	</table>
	*}

</form>
