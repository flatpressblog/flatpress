<p class="alignright">
	<a class="hint externlink" href="https://wiki.flatpress.org/doc:plugins:bbcode" target="_blank">{$lang.admin.plugin.bbcode.editor.help}</a>
</p>

<p class="alignleft">
	{$lang.admin.plugin.bbcode.editor.textarea}
	<input type="button" name="expand" id="expand" value="{$lang.admin.plugin.bbcode.editor.expand}" title="{$lang.admin.plugin.bbcode.editor.expandtitle}" onclick="form.content.rows+=5;">
	<input type="button" name="reduce" id="reduce" value="{$lang.admin.plugin.bbcode.editor.reduce}" title="{$lang.admin.plugin.bbcode.editor.reducetitle}" onclick="form.content.rows-=5;">
</p>

<fieldset id="admin-bbcode-toolbar" style="clear:both">
	<legend>{$lang.admin.plugin.bbcode.editor.formatting}</legend>
	<p>
		<img src="fp-plugins/bbcode/res/toolbaricons/link.png" id="bb_url" accesskey="" alt="url" title="url" onclick="insBBCode('url');">
		<img src="fp-plugins/bbcode/res/toolbaricons/mail.png" id="bb_mail" accesskey="" alt="mail" title="mail" onclick="insBBCode('mail');">
		<img src="fp-plugins/bbcode/res/toolbaricons/h2.png" id="bb_h2" accesskey="" alt="h2" title="h2" onclick="insBBCode('h2');">
		<img src="fp-plugins/bbcode/res/toolbaricons/h3.png" id="bb_h3" accesskey="" alt="h3" title="h3" onclick="insBBCode('h3');">
		<img src="fp-plugins/bbcode/res/toolbaricons/h4.png" id="bb_h4" accesskey="" alt="h4" title="h4" onclick="insBBCode('h4');">
		<img src="fp-plugins/bbcode/res/toolbaricons/ul.png" id="bb_ul" accesskey="" alt="ul" title="unordered list" onclick="insBBCodeWithContent('list', '\n[*]\n[*]\n');">
		<img src="fp-plugins/bbcode/res/toolbaricons/ol.png" id="bb_ol" accesskey="" alt="ol" title="ordered list" onclick="insBBCodeWithParamsAndContent('list', '#', '\n[*]\n[*]\n');">
		<img src="fp-plugins/bbcode/res/toolbaricons/quote.png" id="bb_quote" accesskey="" alt="quote" title="{$lang.admin.plugin.bbcode.editor.quotetitle}" onclick="insBBCode('quote');">
		<img src="fp-plugins/bbcode/res/toolbaricons/code.png" id="bb_code" accesskey="" alt="code" title="{$lang.admin.plugin.bbcode.editor.codetitle}" onclick="insBBCode('code');">
		<img src="fp-plugins/bbcode/res/toolbaricons/html.png" id="bb_html" accesskey="" alt="html" title="html" onclick="insBBCode('html');">
&nbsp;&nbsp;&nbsp;&nbsp;
	</p>
	<p>
		<img src="fp-plugins/bbcode/res/toolbaricons/bold.png" id="bb_b" accesskey="" alt="b" title="{$lang.admin.plugin.bbcode.editor.boldtitle}" onclick="insBBCode('b');">
		<img src="fp-plugins/bbcode/res/toolbaricons/italic.png" id="bb_i" accesskey="" alt="i" title="{$lang.admin.plugin.bbcode.editor.italictitle}" onclick="insBBCode('i');">
		<img src="fp-plugins/bbcode/res/toolbaricons/underlined.png" id="bb_u" accesskey="" alt="u" title="{$lang.admin.plugin.bbcode.editor.underlinetitle}" onclick="insBBCode('u');">
		<img src="fp-plugins/bbcode/res/toolbaricons/del.png" id="bb_del" accesskey="" alt="" title="del" onclick="insBBCode('del');">
&nbsp;&nbsp;&nbsp;&nbsp;
	</p>
	<p>
		{$lang.admin.plugin.bbcode.editor.file}{html_options name=attachselect values=$attachs_list output=$attachs_list onchange="insAttach(this.form.attachselect.value)"}&nbsp;
	</p>
	<p>
		{$lang.admin.plugin.bbcode.editor.image}{html_options name=imageselect values=$images_list output=$images_list onchange="insImage(this.form.imageselect.value)"}
	</p>
</fieldset>

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
