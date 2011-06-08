{if !$entry_commslock}
<h4>{$lang.comments.head}</h4>
<p>{$lang.comments.descr}</p>
	

<form id="commentform" method="post"
	action="{$flatpress.params.entry|link:comments_link}#commentform"
	enctype="multipart/form-data">

	
	{include file='shared:errorlist.tpl'}
	
	
	{if not $flatpress.loggedin}
	
	

	{*<fieldset id="comment-userdata">*}
	<div id="comment-userdata">
	
		<p>
		<input type="text" {$error.name|notempty:'class="field-error"'} name="name" id="name" value="{$values.name|wp_specialchars:1|default:$cookie.name}" />
		<label class="textlabel" for="name">{$lang.comments.name}</label>
		</p>
		
		<p>
		<input type="text" {$error.email|notempty:'class="field-error"'} name="email" id="email" value="{$values.email|wp_specialchars:1|default:$cookie.email}" />
		<label class="textlabel" for="email">{$lang.comments.email}</label>
		</p>
		
		<p>
		<input type="text" {$error.url|notempty:'class="field-error"'} name="url" id="url" value="{$values.url|wp_specialchars:1|default:$cookie.url}" />
		<label class="textlabel" for="url">{$lang.comments.www}</label>
		</p>
		
		{comment_form}
		
	</div>
	
	{/if}
	
	
	<div class="comment-content">
			<p><textarea name="content" {$error.content|notempty:'class="field-error"'}
			id="content" rows="10" cols="74">{$values.content}</textarea></p>
			{*here will go a plugin hook*}
	</div>
	
	<div class="buttonbar">
	<input type="submit" name="submit" id="submit" value="{$lang.comments.submit}" />
	</div>

</form>
{/if}
