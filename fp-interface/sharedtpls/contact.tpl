<p>{$lang.contact.descr}</p>
	
{validate id="name" message=$lang.contact.error.name append="error"}
{validate id="email" message=$lang.contact.error.email append="error"}
{validate id="www" message=$lang.contact.error.www append="error"}
{validate id="content" message=$lang.contact.error.content append="error"}

<form id="contactform" method="post"
	action="{$smarty.const.BLOG_BASEURL}contact.php"
	enctype="multipart/form-data">
	
	{include file='shared:errorlist.tpl'}
	
	<fieldset><legend>{$lang.contact.fieldset1}</legend>
		<p><label class="textlabel" for="name">{$lang.contact.name}</label><br />
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
		<input type="text" name="name" id="name" class="{$class}" 
		value="{$namevalue|stripslashes|wp_specialchars:true}" /></p>
		
		<p><label class="textlabel" for="email">{$lang.contact.email}</label><br />
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
		<input type="text" name="email" id="email" class="{$class}" 
		value="{$emailvalue|stripslashes|wp_specialchars:true}" /></p>
		
		<p><label class="textlabel" for="url">{$lang.contact.www}</label><br />
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
		<input type="text" name="url" id="url" class="{$class}" 
		value="{$urlvalue|stripslashes|wp_specialchars:true}" /></p>
		
		{comment_form}
		
	</fieldset>
	
	<fieldset><legend>{$lang.contact.fieldset2}</legend>
		<p><label for="content">{$lang.contact.comment}</label><br />
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
		<textarea name="content" id="content" class="{$class}" 
		rows="10" cols="74">{$contentvalue|stripslashes|wp_specialchars:true}</textarea></p>

	</fieldset>
	
	<div class="buttonbar">
	<input type="submit" name="submit" id="submit" value="{$lang.contact.submit}" />
	<input type="reset" name="reset" id="reset" value="{$lang.contact.reset}" />
	</div>

</form>
