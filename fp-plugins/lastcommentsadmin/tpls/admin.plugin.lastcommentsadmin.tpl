<h2>{$plang.head}</h2>
<p>{$plang.description}</p>

{include file=shared:errorlist.tpl}

<div style="margin: 0 auto; width: 20em;">
	
{html_form}
<p><input type="submit" name="lastcommentadmin_clear" value="{$plang.clear}"/> </p><p>{$plang.cleardescription} </p>		
<p><br></p>
<p><input type="submit" name="lastcommentadmin_rebuild" value="{$plang.rebuild}"/> </p><p>{$plang.rebuilddescription} </p>		

{/html_form}

</div>