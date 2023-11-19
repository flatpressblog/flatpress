
{include file='shared:errorlist.tpl'}

{html_form}


<div id="admin-config" class="option-set">

<div id="admin-config-general">

<h2> {$panelstrings.gensetts} </h2>

	<dl class="option-list">
	<dt><label for="title"> {$panelstrings.blogtitle} </label></dt>
	{if isset($error) && isset($error.title) && !empty($error.title)}
		{assign var=class value=" field-error"}
	{else}
		{assign var=class value=""}
	{/if}
	<dd><input type="text" name="title" id="title" class="textinput {$class}" 
	value="{$flatpress.TITLE|escape:"html"}" />
	</dd>
	
	
	<dt><label for="subtitle"> {$panelstrings.blogsubtitle} </label></dt>
	<dd><input type="text" name="subtitle" id="subtitle" class="bigtextinput"  value="{$flatpress.subtitle|escape:"html"}" /></dd>
	
	<dt><label for="blogfooter"> {$panelstrings.blogfooter} </label></dt>
	<dd><input type="text" name="blogfooter" id="blogfooter" class="textinput" value="{$flatpress.footer|escape:"html"}" /></dd>
	
	<dt><label for="author"> {$panelstrings.blogauthor} </label></dt>
	<dd><input type="text" name="author" id="author" class="textinput" value="{$flatpress.author}" /></dd>
	
	
	<dt><label for="www"> {$panelstrings.blogurl} </label></dt>
	{if isset($error) && isset($error.www) && !empty($error.www)}
		{assign var=class value=" field-error"}
	{else}
		{assign var=class value=""}
	{/if}
	<dd><input type="text" name="www" id="www" class="textinput {$class}"
			value="{$flatpress.www|escape:"html"}" /></dd>
	
	
	<dt><label for="email"> {$panelstrings.blogemail} </label></dt>
	{if isset($error) && isset($error.email) && !empty($error.email)}
		{assign var=class value="field-error"}
	{else}
		{assign var=class value=""}
	{/if}
	<dd><input type="text" name="email" id="email" class="textinput {$class}" 
	value="{$flatpress.email}" /></dd>
	
	<dt><label> {$panelstrings.notifications} </label></dt>
	<dd> 
	<label for="notify"> 
	<input type="checkbox" name="notify" id="notify" {if $flatpress.NOTIFY}checked="checked"{/if} /> 
	{$panelstrings.mailnotify}
	</label> 
	</dd>
	
	<dt><label for="startpage"> {$panelstrings.startpage} </label></dt>
	<dd><select name="startpage" id="startpage" class="textinput">
		<option value=":NULL:">
			{$panelstrings.stdstartpage}
		</option>
	{foreach from=$static_list key=staticid item=staticpage}
		<option value="{$staticid}"{if $staticid == $fp_config.general.startpage} selected="selected"{/if}>
			{$staticpage.subject}
		</option>
	{/foreach}
	</select>
	</dd>
	
	<dt><label for="maxentries"> {$panelstrings.blogmaxentries} </label></dt>
	{if isset($error) && isset($error.maxentries) && !empty($error.maxentries)}
		{assign var=class value="field-error"}
	{else}
		{assign var=class value=""}
	{/if}
	<dd><input type="text" name="maxentries" id="maxentries" 
	class="smalltextinput{$class}" value="{$flatpress.maxentries}" /></dd>
	
	
	</dl>

</div>

<div id="admin-config-intsetts">

<h2> {$panelstrings.intsetts}  </h2>

	<dl class="option-list">
		<dt><label> {$panelstrings.utctime} </label></dt>
		{assign var=temp_time value="%b %d %Y %H:%M:%S"}
		<dd> <code> {"r"|date:$smarty.now} </code> </dd>
		
		<dt><label for="timeoffset"> {$panelstrings.timeoffset} </label></dt>
		{if isset($error) && isset($error.timeoffset) && !empty($error.timeoffset)}
			{assign var=class value=" field-error"}
		{else}
			{assign var=class value=""}
		{/if}
		<dd><input type="text" name="timeoffset" id="timeoffset" 
			class="smalltextinput{$class}" 
			value="{$fp_config.locale.timeoffset}" /><p class="text"> {$panelstrings.hours} </p>
		</dd>


		<dt><label for="dateformat"> {$panelstrings.dateformat} </label></dt>
		{if isset($error) && isset($error.dateformat) && !empty($error.dateformat)}
			{assign var=class value="field-error"}
		{else}
			{assign var=class value=""}
		{/if}
		<dd>	<p> <input type="text" name="dateformat" id="dateformat" 
			class="textinput{$class}" 
			value="{$fp_config.locale.dateformat}" /> </p>
			<p class="output"> {$panelstrings.output}:   {$smarty.now|date_format:$fp_config.locale.dateformat}</p>
		</dd>

		<dt><label for="dateformatshort"> {$panelstrings.dateformatshort} </label></dt>
		{if isset($error) && isset($error.dateformatshort) && !empty($error.dateformatshort)}
			{assign var=class value=" field-error"}
		{else}
			{assign var=class value=""}
		{/if}
		<dd>	<p> <input type="text" name="dateformatshort" id="dateformatshort" 
			class="textinput{$class}" 
			value="{$fp_config.locale.dateformatshort}" /> </p>
			<p class="output"> {$panelstrings.output}:   {$smarty.now|date_format:$fp_config.locale.dateformatshort}</p>
		</dd>

		<dt><label for="timeformat"> {$panelstrings.timeformat} </label></dt>
		{if isset($error) && isset($error.timeformat) && !empty($error.timeformat)}
			{assign var=class value="field-error"}
		{else}
			{assign var=class value=""}
		{/if}
		<dd>	<p> <input type="text" name="timeformat" id="timeformat" 
			class="textinput{$class}" 
			value="{$fp_config.locale.timeformat}" /> </p>
			{assign var=currentTime value=$smarty.now}
			{assign var=timeDiff value=$fp_config.locale.timeoffset}
			{assign var=TimeDiffUTC value=$currentTime+$timeDiff*3600}
			<p class="output"> {$panelstrings.output}:  {$TimeDiffUTC|date_format:$fp_config.locale.timeformat}</p>
		</dd>

	
		<dt><label for="lang"> {$panelstrings.langchoice} </label></dt>
		<dd>	
		<select name="lang" id="lang" class="textinput">
		{foreach from=$lang_list item=langsetts}
			<option value="{$langsetts.locale}" 
				{if $langsetts.locale == $fp_config.locale.lang}selected="selected"{/if}>
				{$langsetts.id}
			</option>
		{/foreach}
		</select>
		</dd>
		
		<dt> <label for="charset"> {$panelstrings.charset} </label></dt>
		{if isset($error) && isset($error.charset) && !empty($error.charset)}
			{assign var=class value="field-error"}
		{else}
			{assign var=class value=""}
		{/if}
		<dd> <p><input type="text" name="charset" id="charset" 
			class="smalltextinput{$class}" 
			value="{$fp_config.locale.charset}" /></p>
			<p class="output">{$panelstrings.charsettip}</p>
		</dd>
	

	</dl>

</div>

</div>

<div class="buttonbar">
{html_submit name="save" id="save" value=$panelstrings.submit}
</div>


{/html_form}


