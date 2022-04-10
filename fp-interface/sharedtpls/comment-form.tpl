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
		{if isset($error) && isset($error.name) && !empty($error.name)}
			{assign var=class value="field-error"}
		{else}
			{assign var=class value=""}
		{/if}
		{if isset($values) && isset($values.name) && !empty($values.name)}
			{assign var=namevalue value=$values.name}
		{else}
			{assign var=namevalue value=""}
		{/if}
		<input type="text" class="{$class}" name="name" id="name" value="{$namevalue|wp_specialchars:1|default:$cookie.name}" />
		<label class="textlabel" for="name">{$lang.comments.name}</label>
		</p>
		
		<p>
		{if isset($error) && isset($error.email) && !empty($error.email)}
			{assign var=class value="field-error"}
		{else}
			{assign var=class value=""}
		{/if}
		{if isset($values) && isset($values.email) && !empty($values.email)}
			{assign var=emailvalue value=$values.email}
		{else}
			{assign var=emailvalue value=""}
		{/if}
		<input type="text" class="{$class}" name="email" id="email" value="{$emailvalue|wp_specialchars:1|default:$cookie.email}" />
		<label class="textlabel" for="email">{$lang.comments.email}</label>
		</p>
		
		<p>
		{if isset($error) && isset($error.url) && !empty($error.url)}
			{assign var=class value="field-error"}
		{else}
			{assign var=class value=""}
		{/if}
		{if isset($values) && isset($values.url) && !empty($values.url)}
			{assign var=urlvalue value=$values.url}
		{else}
			{assign var=urlvalue value=""}
		{/if}
		<input type="text" class="{$class}" name="url" id="url" value="{$urlvalue|wp_specialchars:1|default:$cookie.url}" />
		<label class="textlabel" for="url">{$lang.comments.www}</label>
		</p>
		
		{* do action *}
		{comment_form}
		
	</div>
	
	{/if}
	
	
	<div class="comment-content">
			{if isset($error) && isset($error.content) && !empty($error.content)}
				{assign var=class value="field-error"}
			{else}
				{assign var=class value=""}
			{/if}
			{if isset($values) && isset($values.content) && !empty($values.content)}
				{assign var=contentvalue value=$values.content}
			{else}
				{assign var=contentvalue value=""}
			{/if}
			<p><textarea name="content" class="{$class}" 
			id="content" rows="10" cols="74">{$contentvalue|wp_specialchars:1}</textarea></p>
			{*here will go a plugin hook*}
	</div>
	
	<div class="buttonbar">
	<input type="submit" name="submit" id="submit" value="{$lang.comments.submit}" />
	</div>

</form>
{/if}
