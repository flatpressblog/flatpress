{if $smarty.request.mod != 'inline'}
<h2>{$panelstrings.head}</h2>
<p>{$panelstrings.descr}</p>
{/if}

{include file='shared:errorlist.tpl'}


{if $success}
	<ul id="admin-uploader-filelist">
	{foreach from=$uploaded_files item=file}
		{* 
		
			memo: this did the trick in the panel
			<a href="javascript:window.parent.window.insImage('{$file}');">
		
		 *}
		<li>{$file}</li>
	{/foreach}
	</ul>
{/if}

{html_form enctype='multipart/form-data'}
	
	
	{if $smarty.request.mod != 'inline'}
	<fieldset><legend>{$panelstrings.fset1}</legend>
	{/if}
		<input type="file" name="upload[]" />
		<input type="file" name="upload[]" />
		<input type="file" name="upload[]" />
		<input type="file" name="upload[]" />
		<input type="file" name="upload[]" />
		<input type="file" name="upload[]" />
		<input type="file" name="upload[]" />
		<input type="file" name="upload[]" />
	
	{if $smarty.request.mod != 'inline'}
	</fieldset>
	{/if}
	
	<div class="buttonbar">
	{html_submit name="upload" id="upload" value=$panelstrings.submit}	
	</div>

{/html_form}
