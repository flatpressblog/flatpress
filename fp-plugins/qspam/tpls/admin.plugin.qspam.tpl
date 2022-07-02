<h2>{$plang.head}</h2>

{include file=shared:errorlist.tpl}

{html_form class=option-set}

<div class="option-list">
<p>{$plang.desc1|wptexturize}</p>
<p>
	<textarea id="qs-wordlist" name="qs-wordlist" rows="10" cols="20">{$qscfg.wordlist}</textarea>
</p>
<p>{$plang.desc2}</p>
</div>

<h2>{$plang.options}</h2>
<dl class="option-list">
	<dt><label>{$plang.desc3}</label></dt>
	<dd>
		{$plang.desc3pre}
		<input type="text" class="smalltextinput" id="qs-number" name="qs-number" value="{$qscfg.number}" />
		{$plang.desc3post}
	</dd>
	
</dl>

<div class="buttonbar">
	<input type="submit" value="{$plang.submit}"/>
</div>
{/html_form}
