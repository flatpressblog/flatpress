<p class="alignright">
	<a class="hint externlink" href="https://wiki.flatpress.org/doc:plugins:bbcode" target="_blank">{$lang.admin.plugin.bbcode.editor.help}</a>
</p>

<p class="alignleft">
	{$lang.admin.plugin.bbcode.editor.textarea}
	<input type="button" name="expand" id="expand" value="{$lang.admin.plugin.bbcode.editor.expand}" title="{$lang.admin.plugin.bbcode.editor.expandtitle}">
	<input type="button" name="reduce" id="reduce" value="{$lang.admin.plugin.bbcode.editor.reduce}" title="{$lang.admin.plugin.bbcode.editor.reducetitle}">
</p>

<fieldset id="admin-bbcode-toolbar" style="clear:both">
	<legend>{$lang.admin.plugin.bbcode.editor.formatting}</legend>
	<p>
		<img src="fp-plugins/bbcode/res/toolbaricons/link.png" id="bb_url" accesskey="" alt="url" title="{$lang.admin.plugin.bbcode.editor.urltitle}">
		<img src="fp-plugins/bbcode/res/toolbaricons/mail.png" id="bb_mail" accesskey="" alt="mail" title="{$lang.admin.plugin.bbcode.editor.mailtitle}">
		<img src="fp-plugins/bbcode/res/toolbaricons/h2.png" id="bb_h2" accesskey="" alt="h2" title="{$lang.admin.plugin.bbcode.editor.headlinetitle} h2">
		<img src="fp-plugins/bbcode/res/toolbaricons/h3.png" id="bb_h3" accesskey="" alt="h3" title="{$lang.admin.plugin.bbcode.editor.headlinetitle} h3">
		<img src="fp-plugins/bbcode/res/toolbaricons/h4.png" id="bb_h4" accesskey="" alt="h4" title="{$lang.admin.plugin.bbcode.editor.headlinetitle} h4">
		<img src="fp-plugins/bbcode/res/toolbaricons/ul.png" id="bb_ul" accesskey="" alt="ul" title="{$lang.admin.plugin.bbcode.editor.unorderedlisttitle}">
		<img src="fp-plugins/bbcode/res/toolbaricons/ol.png" id="bb_ol" accesskey="" alt="ol" title="{$lang.admin.plugin.bbcode.editor.orderedlisttitle}">
		<img src="fp-plugins/bbcode/res/toolbaricons/quote.png" id="bb_quote" accesskey="" alt="quote" title="{$lang.admin.plugin.bbcode.editor.quotetitle}">
		<img src="fp-plugins/bbcode/res/toolbaricons/code.png" id="bb_code" accesskey="" alt="code" title="{$lang.admin.plugin.bbcode.editor.codetitle}">
		<img src="fp-plugins/bbcode/res/toolbaricons/html.png" id="bb_html" accesskey="" alt="html" title="{$lang.admin.plugin.bbcode.editor.htmltitle}">
&nbsp;&nbsp;&nbsp;&nbsp;
		<img src="fp-plugins/bbcode/res/toolbaricons/bold.png" id="bb_b" accesskey="" alt="b" title="{$lang.admin.plugin.bbcode.editor.boldtitle}">
		<img src="fp-plugins/bbcode/res/toolbaricons/italic.png" id="bb_i" accesskey="" alt="i" title="{$lang.admin.plugin.bbcode.editor.italictitle}">
		<img src="fp-plugins/bbcode/res/toolbaricons/underlined.png" id="bb_u" accesskey="" alt="u" title="{$lang.admin.plugin.bbcode.editor.underlinetitle}">
		<img src="fp-plugins/bbcode/res/toolbaricons/del.png" id="bb_del" accesskey="" alt="del" title="{$lang.admin.plugin.bbcode.editor.crossouttitle}">
&nbsp;&nbsp;&nbsp;&nbsp;
	</p>
	<p>
		{$lang.admin.plugin.bbcode.editor.file}{html_options name=attachselect values=$attachs_list output=$attachs_list onchange="insAttach(this.form.attachselect.value)"}&nbsp;
	</p>
	<p>
		{$lang.admin.plugin.bbcode.editor.image}{html_options name=imageselect values=$images_list output=$images_list onchange="insImage(this.form.imageselect.value)"}
	</p>
</fieldset>
