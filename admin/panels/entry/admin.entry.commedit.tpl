	{html_form}
	
	{include file='shared:admin_errorlist.tpl'}

	<div class="row">
		<div class="col-lg-6 mb-4">
			<div class="card shadow mb-4 edit_comments">
				<div class="card-header">
					<h6 class="m-0 font-weight-bold text-primary">{$plang.head}  {$entrysubject}</h6>
				</div>
				<div class="card-body">
					<p><input type="hidden" name="entry" value="{$entryid}" /><input type="hidden" name="comment" value="{$id}" />
						<div class="option-set">
						<dl>
						<dt><label class="textlabel" for="name">{$plang.author}</label></dt>
						<dd>
						<input type="text" class="bigtextinput {$error.name|notempty:'field-error'} form-control input_gray" name="name" id="name" value="{$values.name}" />
						</dd>

						<dt><label class="textlabel" for="email">{$plang.email}</label></dt>
						<dd>
						<input type="text" class="bigtextinput {$error.email|notempty:'field-error'} form-control input_gray" name="email" id="email" value="{$values.email}" />
						</dd>

						<dt><label class="textlabel" for="www">{$plang.www}</label></dt>
						<dd>
						<input type="text" class="bigtextinput {$error.www|notempty:'field-error'} form-control input_gray" name="url" id="url" value="{$values.url}" />
						</dd>

						<dt><label class="textlabel" for="ip">{$plang.ip}</label></dt>
						<dd>
						<input type="text" id="ip" name="ip" class="bigtextinput form-control" value="{$values.ip_address}" disabled="disabled" />
						</dd>

						<dt><label class="textlabel" for"loggedin">{$plang.loggedin}</label></dt>
						<dd>
						<input type="checkbox" id="loggedin" name="loggedin" {if $values.loggedin} checked="checked" {/if} disabled="disabled" />
						</dd>
						</dl>
						</div>

						<div class="option-set">	
						<textarea name="content" {$error.content|notempty:'class="field-error"'}
						id="content" class="form-control input_gray">{$values.content}</textarea>
						</div>

					<div class="buttonbar">
					<input type="submit" name="save" id="submit" value="{$plang.submit}" class="btn btn-primary" />
					</div>
				</div>
			</div>
		</div>
	</div>
{/html_form}


