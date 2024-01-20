<h2>{$plang.head}  <a href="admin.php?p=entry&amp;action=write&amp;entry={$entryid}">{$entrysubject}</a></h2>
<p>{$plang.descr}</p>

{html_form}

	{include file='shared:errorlist.tpl'}

		<p><input type="hidden" name="entry" value="{$entryid}"><input type="hidden" name="comment" value="{$id}">

		<div class="option-set">
			<dl>
				<dt><label class="textlabel" for="name">{$plang.author}</label></dt>
			<dd>
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
				<input type="text" name="name" class="{$class}" id="name" value="{$values.name|default:''}">
			</dd>

			<dt><label class="textlabel" for="email">{$plang.email}</label></dt>
			<dd>
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
				<input type="text" name="email" class="{$class}" id="email" value="{$values.email|default:''}">
			</dd>

			<dt><label class="textlabel" for="www">{$plang.www}</label></dt>
			<dd>
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
				<input type="text" name="url" class="{$class}" id="url" value="{$values.url|default:''}">
			</dd>

			<dt><label class="textlabel" for="ip">{$plang.ip}</label></dt>
			<dd>
				<input type="text" id="ip" name="ip" class="bigtextinput" value="{$values.ip_address}" disabled="disabled">
			</dd>

			<dt><label class="textlabel" for"loggedin">{$plang.loggedin}</label></dt>
			<dd>
				<input type="checkbox" id="loggedin" name="loggedin"{if $values.loggedin|default:false} checked="checked" disabled="disabled"{else} disabled="disabled"{/if}>
			</dd>
		</dl>
	</div>

	<div class="option-set">
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
		<textarea name="content" id="content" class="{$class}" rows="10" cols="74">{$values.content}</textarea>
	</div>

	<div class="buttonbar">
		{html_submit name="save" id="submit" value=$plang.submit accesskey=s}
	</div>
	<br>
	<a href="admin.php?p=entry&amp;action=commentlist&amp;entry={$entryid}">&laquo; {$plang.commentlist}</a>

{/html_form}
