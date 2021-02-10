{include file=shared:admin_errorlist.tpl}

		  <div class="row">
            <div class="col-xl-8 col-lg-7">
              <div class="card shadow mb-4">
                <div class="card-header">
                  <h6 class="m-0 font-weight-bold text-primary">{$panelstrings.head}</h6>
                </div>
					<div class="card-body">
						{html_form}
								<p>
									<textarea name="content" id="content" class="form-control cat-textarea" rows="20" cols="74">{if isset($catdefs)}{$catdefs|escape}{/if}</textarea>
								</p>
							<div class="buttonbar text-center">
							{html_submit name="save" id="save" class="btn btn-primary" value=$panelstrings.submit}
							<a class="btn btn-secondary" href="?p=entry&amp;action=cats&amp;do=clear">{$panelstrings.clear}</a>
							</div>
						{/html_form}
					</div>
              </div>
            </div>
            <div class="col-xl-4 col-lg-5">
              <div class="card shadow mb-4">
                <div class="card-header">
                  <h6 class="m-0 font-weight-bold text-primary">{$panelstrings.cats_info}</h6>
                </div>
					<div class="card-body">
						{$panelstrings.descr}
					</div>
              </div>
            </div>
          </div>
