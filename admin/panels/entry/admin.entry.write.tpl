<h2>{$panelstrings.head}</h2>

{html_form}	
		{validate_init form=$admin_panel_id}
		{validate id="subject" message=$panelstrings.error.subject append="error"}
		{validate id="content" message=$panelstrings.error.content append="error"}
	
	{include file='shared:errorlist.tpl'}

	{entry_block}
	<div id="admin-post-preview">
	{if $preview}
	<fieldset id="post-preview"><legend>{$panelstrings.preview}</legend>
	{include file=preview.tpl}
	</fieldset>
	{/if}
	</div>

		
	
	{entry content=$post alwaysshow=true}
	
		<div id="admin-editor">
			<p><label for="subject">{$panelstrings.subject}</label><br />
			<input type="text" {$error.subject|notempty:'class="field-error"'} 
				name="subject" id="subject" 
				value="{$subject|default:$smarty.request.subject|wp_specialchars:1}" /><br />
			<input type="hidden" name="timestamp" value="{$date}" />
			<input type="hidden" name="entry" value="{$id}" />
			</p>
			<p>
			<label for="content">{$panelstrings.content}</label>
			</p>
			{toolbar}
			<p>
			<textarea name="content" {$error.content|notempty:'class="field-error"'} 
			id="content" rows="20" cols="74">{$content|default:$smarty.request.content|htmlspecialchars}</textarea><br />
			{*here will go a plugin hook*}
			{action hook=simple_edit_form}

			</p>
		</div>
		
		<div id="admin-options">
	
		{* let's disable this for now... *}
		
		{*
		
		<fieldset id="admin-entry-uploader"><legend>{$panelstrings.uploader}</legend>
			<iframe id="uploader-iframe" src="{$smarty.const.BLOG_BASEURL}admin.php?p=uploader&amp;mod=inline"></iframe>
		</fieldset>	
		*}
		
		{* end of inline form *}
		
		<fieldset id="admin-entry-categories"><legend>{$panelstrings.archive}</legend>
			{list_categories type=form selected=$categories}
		</fieldset>
		
		<fieldset id="admin-entry-saveopts"><legend>{$panelstrings.saveopts}</legend>
			
			<p>
			{foreach from=$saved_flags item=flag}
			<label><input name="flags[{$flag}]" {if $categories and (bool)array_intersect(array($flag),$categories) }checked="checked"{/if} type="checkbox" /> {$lang.entry.flags.long[$flag]} </label><br />
			{/foreach}
			</p>
			
		</fieldset>
		</div>
		
		
		<div class="buttonbar">
		{html_submit name="save" id="save" value=$panelstrings.submit accesskey=s}
		{html_submit name="savecontinue" id="savecontinue" value=$panelstrings.savecontinue accesskey=c}
		{html_submit name="preview" id="preview" value=$panelstrings.preview accesskey=p}
		</div>

	
	{/entry}
	{/entry_block}
{/html_form}

{if $smarty.get.entry }

<div id="admin-otheroptions">	

<h2>{$panelstrings.otheropts}</h2>
	<ul>
		{if !$draft}
		<li><a href="admin.php?p=entry&amp;entry={$smarty.get.entry}&amp;action=commentlist">
			{$panelstrings.commmsg}</a></li>
		{/if}
		<li><a href="admin.php?p=entry&amp;entry={$smarty.get.entry}&amp;action=delete">
			{$panelstrings.delmsg}</a></li>
	</ul>

</div>

{/if}



