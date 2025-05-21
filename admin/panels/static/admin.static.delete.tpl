<div class="row">
		<div class="col-xl-12 col-lg-12">
			<div class="card shadow mb-4">
				<div class="card-header">
					<h6 class="m-0 font-weight-bold text-primary">{$panelstrings.head}</h6>
				</div>
				<div class="card-body">
					{statics}
					{include file=previewstatic.tpl}
					</fieldset>
					{/statics}
					</br>
					<p class="delete_confirm">{$panelstrings.confirm}</p>

					{html_form}
						<input type="hidden" name="page" value="{$pageid}" />
						<div class="buttonbar">
						{html_submit name="delete" id="delete" class="btn btn-primary" value=$panelstrings.ok}
						{html_submit name="cancel" id="cancel" class="btn btn-secondary" value=$panelstrings.cancel}
						</div>
					{/html_form}
				</div>
			</div>
		</div>
	</div>	