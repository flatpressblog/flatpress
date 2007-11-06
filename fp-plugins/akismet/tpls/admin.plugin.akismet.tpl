<h2>{$plang.head}</h2>
<p>{$plang.description}</p>

{include file=shared:errorlist.tpl}

<div style="margin: 0 auto; width: 20em;">
	
{html_form}
	
	<h4><label for="wp-apikey">{$plang.apikey}</label></h4>
	<p><input id="wp-apikey" type="text" name="wp-apikey" value="{$akismetconf.apikey}" /> 
	<input type="submit" value="{$plang.submit}"/> </p>
	<p> {$plang.whatis} </p>
		
{/html_form}

</div>