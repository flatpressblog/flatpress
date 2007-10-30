{include file=shared:errorlist.tpl}
<form id="search" method="get" action="{$smarty.server.PHP_SELF}" enctype="multipart/form-data">
		
	<fieldset><legend>{$lang.search.fset1}</legend>
	<p><label for="keywords">{$lang.search.keywords}</label><br />
	<input type="text" name="q" id="keywords" />
	<label><input type="radio" name="stype" value="titles" id="onlytitles" checked="checked" />{$lang.search.onlytitles}</label>
	<label><input type="radio" name="stype" value="full" id="fulltext" />{$lang.search.fulltext}</label></p>
	</fieldset>
	
	<fieldset><legend>{$lang.search.fset2}</legend>
	<p>{html_select_date start_year="1990" field_separator=" - " field_order="DMY" time="-00-00" all_empty="--"}</p>
	<p>{$lang.search.datedescr}</p>
	</fieldset>
	
	<fieldset><legend>{$lang.search.fset3}</legend>
	{list_categories type="radio"}
	<p>{$lang.search.catdescr}</p>
	</fieldset>
	
	
	<div class="buttonbar">
	<input type="submit" name="submit" id="submit" value="{$lang.search.submit}" />
	</div>
</form>
