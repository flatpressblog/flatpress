<h2>{$plang.head}</h2>

{include file="shared:errorlist.tpl"}

<p>{$plang.desc1}</p>

{html_form class="option-set"}

	<fieldset>
		<label for="allowUnsafeInline">
			<input type="checkbox" name="allowUnsafeInline" id="allowUnsafeInline" value="1" {if isset($allowUnsafeInline) && $allowUnsafeInline == true}checked{/if}>
			{$plang.allow_unsafe_inline}
		</label>
		<p>{$plang.allowUnsafeInlineDsc}</p>
	</fieldset>

	<fieldset>
		<label for="allowPrettyURLEdit">
			<input type="checkbox" name="allowPrettyURLEdit" id="allowPrettyURLEdit" value="1" {if isset($allowPrettyURLEdit) && $allowPrettyURLEdit == true}checked{/if}>
			{$plang.allow_htaccess_edit}
		</label>
		<p>{$plang.allowPrettyURLEditDsc}</p>
	</fieldset>

	<fieldset>
		<label for="allowImageMetadata">
			<input type="checkbox" name="allowImageMetadata" id="allowImageMetadata" value="1" {if isset($allowImageMetadata) && $allowImageMetadata == true}checked{/if}>
			{$plang.allow_image_metadate}
		</label>
		<p>{$plang.allowImageMetadataDsc}</p>
	</fieldset>

	<fieldset>
		<label for="allowVisitorIp">
			<input type="checkbox" name="allowVisitorIp" id="allowVisitorIp" value="1" {if isset($allowVisitorIp) && $allowVisitorIp == true}checked{/if}>
			{$plang.allow_visitor_ip}
		</label>
		<p>{$plang.allowVisitorIpDsc}</p>
	</fieldset>

	<fieldset>
		<label for="session_timeout_minutes">
			{$plang.session_timeout_label}
			<input type="number" name="session_timeout_minutes" id="session_timeout_minutes" min="0" step="1" value="{$session_timeout_minutes|escape}">
		</label>
		<p>{$plang.session_timeout_desc}</p>
	</fieldset>

	<div class="buttonbar">
		<input type="submit" value="{$plang.submit}">
	</div>

{/html_form}
