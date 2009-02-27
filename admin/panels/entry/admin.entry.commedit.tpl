<h2>{$plang.head}  <a href="admin.php?p=entry&amp;action=write&amp;entry={$entryid}">{$entrysubject}</a></h2>
<p>{$plang.descr}</p>
	

{html_form}
	
	{include file='shared:errorlist.tpl'}
	
			
	

		<p><input type="hidden" name="entry" value="{$entryid}" /><input type="hidden" name="comment" value="{$id}" />
		<label class="textlabel" for="name">{$plang.author}</label><br />
		<input type="text" {$error.name|notempty:'class="field-error"'} name="name" id="name" value="{$values.name|default:$cookie.name}" /></p>
		
		<p><label class="textlabel" for="email">{$plang.email}</label><br />
		<input type="text" {$error.email|notempty:'class="field-error"'} name="email" id="email" value="{$values.email|default:$cookie.email}" /></p>
		
		<p><label class="textlabel" for="url">{$plang.www}</label><br />
		<input type="text" {$error.url|notempty:'class="field-error"'} name="url" id="url" value="{$values.url|default:$cookie.url}" /></p>
		
	
	
		<p><label for="content">{$plang.content}</label><br />
		<textarea name="content" {$error.content|notempty:'class="field-error"'}
		id="content" rows="10" cols="74">{$values.content}</textarea></p>
	
	<div class="buttonbar">
	<input type="submit" name="save" id="submit" value="{$plang.submit}" />
	</div>

{/html_form}


