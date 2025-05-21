	<div class="row">
		<div class="col-xl-12 col-lg-12">
			<div class="card shadow mb-4">
				<div class="card-header">
					<h6 class="m-0 font-weight-bold text-primary">{$panelstrings.head}</h6>
				</div>
				<div class="card-body">
						{entry_block}
						{html_form}
						{include file=preview.tpl}
						</fieldset>
						</br>
						<p class="delete_confirm">{$panelstrings.confirm}</p>

							<input type="hidden" name="entry" value="{$id}" />
							<div class="buttonbar">
							{html_submit  name="delete" id="delete" class="btn btn-primary" value=$panelstrings.ok}
							{html_submit  name="cancel" id="cancel" class="btn btn-secondary" value=$panelstrings.cancel}
							</div>
						{/html_form}
						{/entry_block}
				</div>
			</div>
		</div>
	</div>