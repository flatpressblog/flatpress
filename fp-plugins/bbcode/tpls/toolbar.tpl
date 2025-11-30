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
		<button class="bb-button" type="button" id="bb_url" accesskey="" title="{$lang.admin.plugin.bbcode.editor.urltitle}"><img src="{$smarty.const.BLOG_BASEURL}fp-plugins/bbcode/res/toolbaricons/link.svg" alt="url"></button>
		<button class="bb-button" type="button" id="bb_mail" accesskey="" title="{$lang.admin.plugin.bbcode.editor.mailtitle}"><img src="{$smarty.const.BLOG_BASEURL}fp-plugins/bbcode/res/toolbaricons/mail.svg" alt="mail"></button>
		<button class="bb-button" type="button" id="bb_h2" accesskey="" title="{$lang.admin.plugin.bbcode.editor.headlinetitle} h2"><img src="{$smarty.const.BLOG_BASEURL}fp-plugins/bbcode/res/toolbaricons/h2.svg" alt="h2"></button>
		<button class="bb-button" type="button" id="bb_h3" accesskey="" title="{$lang.admin.plugin.bbcode.editor.headlinetitle} h3"><img src="{$smarty.const.BLOG_BASEURL}fp-plugins/bbcode/res/toolbaricons/h3.svg" alt="h3"></button>
		<button class="bb-button" type="button" id="bb_h4" accesskey="" title="{$lang.admin.plugin.bbcode.editor.headlinetitle} h4"><img src="{$smarty.const.BLOG_BASEURL}fp-plugins/bbcode/res/toolbaricons/h4.svg" alt="h4"></button>
		<button class="bb-button" type="button" id="bb_ul" accesskey="" title="{$lang.admin.plugin.bbcode.editor.unorderedlisttitle}"><img src="{$smarty.const.BLOG_BASEURL}fp-plugins/bbcode/res/toolbaricons/ul.svg" alt="ul"></button>
		<button class="bb-button" type="button" id="bb_ol" accesskey="" title="{$lang.admin.plugin.bbcode.editor.orderedlisttitle}"><img src="{$smarty.const.BLOG_BASEURL}fp-plugins/bbcode/res/toolbaricons/ol.svg" alt="ol"></button>
		<button class="bb-button" type="button" id="bb_quote" accesskey="" title="{$lang.admin.plugin.bbcode.editor.quotetitle}"><img src="{$smarty.const.BLOG_BASEURL}fp-plugins/bbcode/res/toolbaricons/quote.svg" alt="quote"></button>
		<button class="bb-button" type="button" id="bb_code" accesskey="" title="{$lang.admin.plugin.bbcode.editor.codetitle}"><img src="{$smarty.const.BLOG_BASEURL}fp-plugins/bbcode/res/toolbaricons/code.svg" alt="code"></button>
		<button class="bb-button" type="button" id="bb_html" accesskey="" title="{$lang.admin.plugin.bbcode.editor.htmltitle}"><img src="{$smarty.const.BLOG_BASEURL}fp-plugins/bbcode/res/toolbaricons/html.svg" alt="html"></button>
		&nbsp;
	</p>
	<p>
		<button class="bb-button" type="button" id="bb_font" accesskey="" title="{$lang.admin.plugin.bbcode.editor.fonttitle}"><img src="{$smarty.const.BLOG_BASEURL}fp-plugins/bbcode/res/toolbaricons/font.svg" alt="font"></button>
		<button class="bb-button" type="button" id="bb_b" accesskey="" title="{$lang.admin.plugin.bbcode.editor.boldtitle}"><img src="{$smarty.const.BLOG_BASEURL}fp-plugins/bbcode/res/toolbaricons/bold.svg" alt="b"></button>
		<button class="bb-button" type="button" id="bb_i" accesskey="" title="{$lang.admin.plugin.bbcode.editor.italictitle}"><img src="{$smarty.const.BLOG_BASEURL}fp-plugins/bbcode/res/toolbaricons/italic.svg" alt="i"></button>
		<button class="bb-button" type="button" id="bb_u" accesskey="" title="{$lang.admin.plugin.bbcode.editor.underlinetitle}"><img src="{$smarty.const.BLOG_BASEURL}fp-plugins/bbcode/res/toolbaricons/underlined.svg" alt="u"></button>
		<button class="bb-button" type="button" id="bb_del" accesskey="" title="{$lang.admin.plugin.bbcode.editor.crossouttitle}"><img src="{$smarty.const.BLOG_BASEURL}fp-plugins/bbcode/res/toolbaricons/del.svg" alt="del"></button>
		&nbsp;
	</p>
</fieldset>

<fieldset id="admin-bbcode-selectbar" style="clear:both">
	<p>
		{$lang.admin.plugin.bbcode.editor.file}{html_options name=attachselect values=$attachs_values output=$attachs_list id="bb_attach" style="width: 10em;"}
		&nbsp;
	</p>
	<p>
		{$lang.admin.plugin.bbcode.editor.image}{html_options name=imageselect values=$images_values output=$images_list id="bb_image" style="width: 10em;"}
		&nbsp;
	</p>
	{if function_exists('is_rss_feed')}
	<p>
		{$lang.admin.plugin.bbcode.editor.gallery|default:"Gallery: "}{html_options name=galleryselect values=$galleries_values output=$galleries_list id="bb_gallery" style="width: 10em;"}
		&nbsp;
	</p>
	{/if}
</fieldset>
