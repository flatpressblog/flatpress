<h2>{$plang.head}  <a href="admin.php?p=entry&amp;action=write&amp;entry={$entryid}">{$entrysubject}</a></h2>
<p>{$plang.descr}</p>
	

{html_form}
	
	{include file='shared:errorlist.tpl'}
	
			
	

		<p><input type="hidden" name="entry" value="{$entryid}" /><input type="hidden" name="comment" value="{$id}" />

		<div class="option-set">
		<dl>
		<dt><label class="textlabel" for="name">{$plang.author}</label></dt>
		<dd>
		<input type="text" class="bigtextinput {$error.name|notempty:'field-error'}" name="name" id="name" value="{$values.name}" />
		</dd>
		
		<dt><label class="textlabel" for="email">{$plang.email}</label></dt>
		<dd>
		<input type="text" class="bigtextinput {$error.email|notempty:'field-error'}" name="email" id="email" value="{$values.email}" />
		</dd>
		
		<dt><label class="textlabel" for="www">{$plang.www}</label></dt>
		<dd>
		<input type="text" class="bigtextinput {$error.www|notempty:'field-error'}" name="url" id="url" value="{$values.url}" />
		</dd>

		<dt><label class="textlabel" for="ip">{$plang.ip}</label></dt>
		<dd>
		<input type="text" id="ip" name="ip" class="bigtextinput" value="{$values.ip_address}" disabled="disabled" />
		</dd>

		<dt><label class="textlabel" for"loggedin">{$plang.loggedin}</label></dt>
		<dd>
		<input type="checkbox" id="loggedin" name="loggedin" {if $values.loggedin} checked="checked" {/if} disabled="disabled" />
		</dd>
		</dl>
		</div>
	
		<div class="option-set">	
		<textarea name="content" {$error.content|notempty:'class="field-error"'}
		id="content" rows="10" cols="74">{$values.content}</textarea>
		</div>
	
	<div class="buttonbar">
	<input type="submit" name="save" id="submit" value="{$plang.submit}" />
	</div>

{/html_form}


