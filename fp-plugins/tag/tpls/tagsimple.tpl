<fieldset id="plugin_tag">
	<legend>{$taglang.tag_pl|escape:'html'}</legend>
	<script nonce="{$smarty.const.RANDOM_HEX}">
		vdfnTagRemove='{$tag_remove_text|escape:'javascript'}';
		vdfnTagUrl='{$tag_ajax_url|escape:'javascript'}';
	</script>
	<script nonce="{$smarty.const.RANDOM_HEX}" src="{$tag_script_url|escape:'html'}"></script>
	<p><input name="taginput" id="taginput" type="text" value="{$tags_simple|escape:'html'}"></p>
	<div id="tagplace"></div>
</fieldset>
