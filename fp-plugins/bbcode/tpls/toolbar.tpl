<fieldset id="admin-bbcode-toolbar">
	<legend>{$lang.admin.plugin.bbcode.editor.formatting}</legend>
	<div class="alignright">
		{$lang.admin.plugin.bbcode.editor.textarea}
		<input type="button" name="expand" id="expand" value="{$lang.admin.plugin.bbcode.editor.expand}" title="{$lang.admin.plugin.bbcode.editor.expandtitle}" onclick="form.content.rows+=5;" />
		<input type="button" name="reduce" id="reduce" value="{$lang.admin.plugin.bbcode.editor.reduce}" title="{$lang.admin.plugin.bbcode.editor.reducetitle}" onclick="form.content.rows-=5;" />
	</div>
	<p>
		<input type="button" name="bb_b" id="bb_b" value="{$lang.admin.plugin.bbcode.editor.bold}" accesskey="b" title="{$lang.admin.plugin.bbcode.editor.boldtitle}" onclick="insBBCode('b');" />
		<input type="button" name="bb_i" id="bb_i" value="{$lang.admin.plugin.bbcode.editor.italic}" accesskey="i" title="{$lang.admin.plugin.bbcode.editor.italictitle}" onclick="insBBCode('i');" />
		<input type="button" name="bb_u" id="bb_u" value="{$lang.admin.plugin.bbcode.editor.underline}" accesskey="u" title="{$lang.admin.plugin.bbcode.editor.underlinetitle}" onclick="insBBCode('u');" />
		<input type="button" name="bb_c" id="bb_c" value="{$lang.admin.plugin.bbcode.editor.quote}" accesskey="q" title="{$lang.admin.plugin.bbcode.editor.quotetitle}" onclick="insBBCode('quote');" />
		<input type="button" name="bb_q" id="bb_q" value="{$lang.admin.plugin.bbcode.editor.code}" accesskey="c" title="{$lang.admin.plugin.bbcode.editor.codetitle}" onclick="insBBCode('code');" />
	</p>
	<p>
		{html_options name=attachselect values=$attachs_list output=$attachs_list onchange="insAttach(this.form.attachselect.value)"}
		{html_options name=imageselect values=$images_list output=$images_list onchange="insImage(this.form.imageselect.value)"}
	</p>
</fieldset>

<p class="alignright">
	<a class="hint externlink" href="http://wiki.flatpress.org/doc:plugins:bbcode">{$lang.admin.plugin.bbcode.editor.help}</a>
</p>

{*
{if function_exists('plugin_jsutils_head')}
<fieldset>
	<legend>{$lang.admin.plugin.bbcode.editor.status}</legend>
	<div id="bbcode_statusbar" style="background: green; color: white;">
		{$lang.admin.plugin.bbcode.editor.statusbar}
	</div>
</fieldset>
{/if}
*}