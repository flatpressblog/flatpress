{if !$entry_commslock}
<h4>{$lang.comments.head}</h4>
<p>{$lang.comments.descr}</p>
	

<form id="commentform" method="post"
	action="{$flatpress.params.entry|link:comments_link}#commentform"
	enctype="multipart/form-data">

	
	{include file='shared:errorlist.tpl'}
	
	
	{if not $flatpress.loggedin}
	
	

	<fieldset id="comment-userdata">
		<legend>{$lang.comments.fieldset1}</legend>
		<p><label class="textlabel" for="name">{$lang.comments.name}</label><br />
		<input type="text" {$error.name|notempty:'class="field-error"'} name="name" id="name" value="{$values.name|default:$cookie.name}" /></p>
		
		<p><label class="textlabel" for="email">{$lang.comments.email}</label><br />
		<input type="text" {$error.email|notempty:'class="field-error"'} name="email" id="email" value="{$values.email|default:$cookie.email}" /></p>
		
		<p><label class="textlabel" for="url">{$lang.comments.www}</label><br />
		<input type="text" {$error.url|notempty:'class="field-error"'} name="url" id="url" value="{$values.url|default:$cookie.url}" /></p>
		
		{comment_form}
		
	</fieldset>
	{else}
		<p><input type="hidden" name="name" id="name" value="{$flatpress.user.userid}" />
		<input type="hidden" name="email" id="email" value="{$flatpress.user.email}" />
		<input type="hidden" name="url" id="url" value="{$flatpress.user.www}" /></p>
	{/if}
	
	<fieldset><legend>{$lang.comments.fieldset2}</legend>
	
		<p><label for="content">{$lang.comments.comment}</label><br />
		<textarea name="content" {$error.content|notempty:'class="field-error"'}
		id="content" rows="10" cols="74">{$values.content}</textarea></p>
	{*here will go a plugin hook*}
	</fieldset>
	
	<div class="buttonbar">
	<input type="submit" name="submit" id="submit" value="{$lang.comments.submit}" />
	<input type="reset" name="reset" id="reset" value="{$lang.comments.reset}" />
	</div>

</form>
{/if}
