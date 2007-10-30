	<fieldset id="admin-bbcode-toolbar"><legend>Formatting</legend>
	<div class="alignright">
	Textarea: 
	<input type="button" name="expand" id="expand" value="Expand" title="Expand Textarea Height" onclick="form.content.rows+=5;" /> 
	<input type="button" name="reduce" id="reduce" value="Reduce" title="Reduece Textarea Height" onclick="form.content.rows-=5;" /> 
	</div>
	
	<p><input type="button" name="bb_b" id="bb_b" value="B" accesskey="b" title="Bold" onclick="insBBCode('b');" />
	<input type="button" name="bb_i" id="bb_i" value="I" accesskey="i" title="Italic" onclick="insBBCode('i');" />
	<input type="button" name="bb_u" id="bb_u" value="U" accesskey="u" title="Underlined" onclick="insBBCode('u');" />
	<input type="button" name="bb_c" id="bb_c" value="Quote" accesskey="q" title="Quote" onclick="insBBCode('quote');" />
	<input type="button" name="bb_q" id="bb_q" value="Code" accesskey="c" title="Code" onclick="insBBCode('code');" /> 
	</p>
	
	<p>
	{html_options name=attachselect values=$attachs_list output=$attachs_list onchange="insAttach(this.form.attachselect.value)"}
	{html_options name=imageselect values=$images_list output=$images_list onchange="insImage(this.form.imageselect.value)"}
	</p>
	
	</fieldset>
	
	<p class="alignright"><a class="hint externlink" href="http://wiki.flatpress.org/doc:plugins:bbcode">BBCode Help</a></p>
	
	{*
	{if function_exists('plugin_jsutils_head')}
	<fieldset><legend>Status bar</legend>
	<div id="bbcode_statusbar" style="background: green; color: white;">Normal mode. Press &lt;Esc&gt; to switch editing mode.</div>
	</fieldset>
	{/if}
	*}