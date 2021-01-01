<div class="row">
	<div class="col-12 mb-4">
	  <div class="card shadow mb-4">
		<div class="card-header">
		  <h6 class="m-0 font-weight-bold text-primary">{$plang.head}</h6>
		</div>
		<div class="card-body">
                <h2>{$plang.head}</h2>
                <p>{$plang.description}</p>

                {include file=shared:errorlist.tpl}
                    
                {html_form}
                    <p><input type="submit" class="btn btn-primary" name="lastcommentadmin_clear" value="{$plang.clear}"/> </p><p>{$plang.cleardescription} </p>		
                    <p><input type="submit" class="btn btn-primary" name="lastcommentadmin_rebuild" value="{$plang.rebuild}"/> </p><p>{$plang.rebuilddescription} </p>
                {/html_form}

            </div>
        </div>
    </div>
</div>