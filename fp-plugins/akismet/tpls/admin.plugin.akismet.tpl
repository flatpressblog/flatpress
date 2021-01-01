<div class="row">
	<div class="col-12">
		<div class="card shadow mb-4">
			<div class="card-header"><h6 class="m-0 font-weight-bold text-primary">{$plang.head}</h6></div>
				<div class="card-body">
				<p>{$plang.description}</p>

				{include file=shared:errorlist.tpl}

				<div style="margin: 0 auto; width: 20em;">
					
				{html_form}
					
					<h4><label for="wp-apikey">{$plang.apikey}</label></h4>
					<p><input id="wp-apikey" class="form-control" type="text" name="wp-apikey" value="{$akismetconf.apikey}" /> 
					<input type="submit" class="btn btn-primary" value="{$plang.submit}"/> </p>
					<p> {$plang.whatis} </p>
						
				{/html_form}
				</div>
			</div>
		</div>
	</div>
</div>