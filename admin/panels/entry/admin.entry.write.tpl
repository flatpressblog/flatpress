<h2>{$panelstrings.head}</h2>

	{include file="shared:errorlist.tpl"}

	{entry_block}
	<div id="admin-post-preview">
	{if isset($preview)}
		<fieldset id="post-preview"><legend>{$panelstrings.preview}</legend>
		{include file="preview.tpl"}
		</fieldset>
	{/if}
	</div>

		{html_form}
			{if !isset($post)}
				{assign var=post value=""}
			{/if}
				{entry content=$post alwaysshow=true}
				{* here will go a plugin hook *}
				<!-- BOF DateChanger- Plugin -->{action hook=simple_datechanger_form}<!-- EOF DateChanger- Plugin -->
				<div id="admin-editor">
					<p><label for="subject">{$panelstrings.subject}</label><br>
					{if isset($error) && isset($error.subject) && !empty($error.subject)}
						{assign var=class value="field-error"}
					{else}
						{assign var=class value=""}
					{/if}
						<input type="text" name="subject" id="subject" class="{$class}" 
							value="{$subject|default:{$smarty.request.subject|default:''}|wp_specialchars:1}"><br>
						<input type="hidden" name="timestamp" value="{$date}">
						<input type="hidden" name="entry" value="{$id}">
					</p>
					<p>
						<label for="content">{$panelstrings.content}</label>
					</p>
					{if function_exists('plugin_bbcode_init') && $fp_config.plugins.bbcode.editor == true}
						{include file="plugin:bbcode/toolbar"}
					{/if}
					<!-- BOF Custom toolbar -->{action hook=simple_toolbar_form}<!-- EOF Custom toolbar -->
					<p>
					{if isset($error) && isset($error.content) && !empty($error.content)}
						{assign var=class value="field-error"}
					{else}
						{assign var=class value=""}
					{/if}
						<textarea name="content" id="content" class="{$class}" 
							rows="20" cols="74">{$content|default:{$smarty.request.content|default:''}|htmlspecialchars}</textarea><br>
					</p>
					{* here will go a plugin hook *}
					<!-- BOF SEO-Metataginfo- Plugin -->{action hook=simple_metatag_info}<!-- EOF SEO-Metataginfo- Plugin -->
					<!-- BOF Tag- Plugin -->{action hook=simple_tag_form}<!-- EOF Tag- Plugin -->
					<!-- BOF Show categories -->{action hook=simple_edit_form}<!-- EOF Show categories -->
				</div>

				<div id="admin-options">

				{* let's disable this for now... *}

				{*
				<fieldset id="admin-entry-uploader"><legend>{$panelstrings.uploader}</legend>
					<iframe id="uploader-iframe" src="{$smarty.const.BLOG_BASEURL}admin.php?p=uploader&amp;mod=inline"></iframe>
				</fieldset>	
				*}

				{* end of inline form *}

				<fieldset id="admin-entry-categories"><legend>{$panelstrings.categories}</legend>
					{list_categories type=form selected=$categories}
				</fieldset>

				<fieldset id="admin-entry-saveopts"><legend>{$panelstrings.saveopts}</legend>
					<p>
					{foreach from=$saved_flags item=flag}
					<label><input name="flags[{$flag}]"{if $categories and (bool)array_intersect(array($flag),$categories)} checked="checked"{/if} type="checkbox">{$lang.entry.flags.long[$flag]} </label><br>
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
		{/html_form}
	{/entry_block}

{if isset($smarty.get.entry)}

<div id="admin-otheroptions">
	<h2>{$panelstrings.otheropts}</h2>
		<ul>
		{if !isset($draft)}
			<li><a href="admin.php?p=entry&amp;entry={$smarty.get.entry}&amp;action=commentlist">
			{$panelstrings.commmsg}</a></li>
		{/if}
			<li><a href="admin.php?p=entry&amp;entry={$smarty.get.entry}&amp;action=delete">
			{$panelstrings.delmsg}</a></li>
		</ul>
</div>

{/if}
