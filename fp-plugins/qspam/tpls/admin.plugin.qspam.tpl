<div class="row">
	<div class="col-12">
		<div class="card shadow mb-4">
			<div class="card-header"><h6 class="m-0 font-weight-bold text-primary">{$plang.head}</h6></div>
				<div class="card-body">

					{include file=shared:errorlist.tpl}

					{html_form class=option-set}

					<div class="option-list">
					<p>{$plang.desc1|wptexturize}</p>
					<p>
						<textarea class="form-control mw-100" id="qs-wordlist" name="qs-wordlist" rows="10" cols="20">{$qscfg.wordlist}</textarea>
					</p>
					<p>{$plang.desc2}</p>
					</div>

					<h2>{$plang.options}</h2>
					<dl class="option-list">
						<dt><label>{$plang.desc3}</label></dt>
						<dd>
							{$plang.desc3pre}
							<input type="text" class="form-control" class="smalltextinput" id="qs-number" name="qs-number" value="{$qscfg.number}" style="display: inline-block;" />
							{$plang.desc3post}
						</dd>
						
					</dl>

					<div class="buttonbar">
						<input type="submit" class="btn btn-primary" value="{$plang.submit}"/>
					</div>
					{/html_form}
				</div>
			</div>
		</div>
	</div>
</div>