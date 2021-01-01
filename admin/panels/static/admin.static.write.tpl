<h2>{$panelstrings.head}</h2>

	{include file='shared:errorlist.tpl'}	


	{static_block}
	{if isset($preview)}
		<fieldset id="post-preview"><legend>{$panelstrings.preview}</legend>
		{include file=preview.tpl}
		</fieldset>
	{/if}

{html_form}		

	{if !isset($post)}
		{assign var=post value=""}
	{/if}
	{static content=$post alwaysshow=true}
	
			<p><label for="subject">{$panelstrings.subject}</label><br />
			{if isset($error) && isset($error.subject) && !empty($error.subject)}
				{assign var=class value=" field-error"}
			{else}
				{assign var=class value=""}
			{/if}
			<input type="text" name="subject" id="subject" class="{$class}" 
			value="{$subject|default:$smarty.request.subject|default:$smarty.request.page|wp_specialchars:1}" /><br />
			<input type="hidden" name="timestamp" value="{$date}" />
			</p>
			<p>
			<label for="content">{$panelstrings.content}</label>
			</p>
			{toolbar}
			<p>
			{if isset($error) && isset($error.content) && !empty($error.content)}
				{assign var=class value=" field-error"}
			{else}
				{assign var=class value=""}
			{/if}
			<textarea name="content" class="{$class}" id="content" 
			rows="20" cols="74">{$content|default:$smarty.request.content|htmlspecialchars}</textarea><br />
		{*here will go a plugin hook*}
			</p>
		
		<fieldset id="admin-static-filename"><legend>{$panelstrings.fieldset2}</legend>
		<input type="hidden" name="oldid" id="oldid" value="{$id|default:$smarty.request.oldid}" />
		<p><label for="id">{$panelstrings.pagename}</label><br />
		{if isset($error) && isset($error.id) && !empty($error.id)}
			{assign var=class value=" field-error"}
		{else}
			{assign var=class value=""}
		{/if}
		<input type="text" name="id" id="id" class="maxsize{$class}"
		value="{$smarty.request.id|default:$smarty.request.page|default:$static_id}"  /></p>
		{html_submit name="save" id="save" value=$panelstrings.submit accesskey=s}
		{html_submit name="preview" id="preview" value=$panelstrings.preview accesskey=p}
		
		</fieldset>

	
	{/static}


{/html_form}
	{/static_block}






