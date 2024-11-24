<h2>{$plang.head}</h2>
<p>{$plang.desc1}</p>

{include file="shared:errorlist.tpl"}

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

	<div class="buttonbar">
		<input type="submit" value="{$plang.submit}">
	</div>

{/html_form}
