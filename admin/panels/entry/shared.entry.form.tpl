{validate id="subject" message=$panelstrings.error.subject append="error"}
{validate id="content" message=$panelstrings.error.content append="error"}


	{if $error}
	<ul class="errorlist">
		{foreach from=$error key=field item=msg}
		<li>{$msg}</li>
		{/foreach}
	</ul>
	{/if}
	
	<fieldset><legend>{$panelstrings.fieldset1}</legend>
		<p><label for="subject">{$panelstrings.subject}</label><br />
		<input type="text" name="subject" id="subject" value="{$subject|default:$smarty.request.subject}" /><br />
		<input type="hidden" name="timestamp" value="{$date}" />
		</p>
	{toolbar}
		<p>
		<label for="content">{$panelstrings.content}</label><br />
		<textarea name="content" id="content" rows="20" cols="74"{$content|default:$smarty.request.content}</textarea><br />
	{*here will go a plugin hook*}
		</p>
	</fieldset>
	
	<fieldset><legend>Archive</legend>
		
		<p>
		{foreach from=$saved_categories key=catId item=cat}
		<label><input name="cats[{$catId}]" {if in_array( $catId,(array) $categories ) }checked="checked"{/if} type="checkbox" /> {$cat} </label><br />
		{foreachelse}
		No categories set. Create your own categories from the main entry panel. Save your entry first.
		{/foreach}
		</p>
		
	</fieldset>
	
	<fieldset><legend>Save options</legend>
		
		<p>
		{foreach from=$saved_flags item=flag}
		<label><input name="flags[{$flag}]" {if in_array( $flag,(array) $categories ) }checked="checked"{/if} type="checkbox" /> {$lang.entry.flags.long[$flag]} </label><br />
		{/foreach}
		</p>
		
	</fieldset>
	
	
	
	<fieldset><legend>{$panelstrings.fieldset2}</legend>
	{html_submit  name="submit" id="submit" value=$panelstrings.submit}
	{html_submit name="preview" id="preview" value=$panelstrings.preview}
	</fieldset>

