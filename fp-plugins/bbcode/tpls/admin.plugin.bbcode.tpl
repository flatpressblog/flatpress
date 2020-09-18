{include file=shared:errorlist.tpl}

<div class="row">
	<div class="col-12 mb-4">
	  <div class="card shadow mb-4">
		<div class="card-header">
		  <h6 class="m-0 font-weight-bold text-primary">{$plang.head}</h6>
		</div>
		<div class="card-body">
			<p>{$plang.desc1}</p>
			{html_form class=option-set}
			<h2>{$plang.editing}</h2>

			<dl class="option-list">
				<dt><label for="bb-escape-html">
					{$plang.allow_html}
				</label></dt>
				<dd> 
					<p><input type="checkbox" name="bb-allow-html" id="bb-allow-html" {if $bbchecked[0]}checked="checked"{/if} /> 
					{$plang.allow_html_long}</p>
				</dd>

				<dt><label for="bb-toolbar">
					{$plang.toolbar}
				</label></dt>
				<dd> 
					<p><input type="checkbox" name="bb-toolbar" id="bb-toolbar" {if $bbchecked[2]}checked="checked"{/if} />
					{$plang.toolbar_long}</p>
				</dd>

			</dl>


			<h2>{$plang.other}</h2>

			<dl class="option-list">
				<dt><label for="bb-comments">
					{$plang.comments}
				</label></dt>
				<dd> 
					<p><input type="checkbox" name="bb-comments" id="bb-comments" {if $bbchecked[1]}checked="checked"{/if} />
					{$plang.comments_long} </p>
				</dd>

				<dt><label for="bb-urlmaxlen">
					{$plang.urlmaxlen}
				</label></dt>
				<dd> 
					<p>{$plang.urlmaxlen_long_pre}
					<input class="form-control input_gray bbcode_input" type="text" name="bb-maxlen" size="3" value="{$bbconf.number}">
					{$plang.urlmaxlen_long_post}</p>
				</dd>

			</dl>


				<label>
					{$plang.opn4pre}

					{$plang.opn4post}
				</label>
			<p class="buttonbar">
				<input type="submit" name="bb-conf" class="btn btn-primary" value="{$plang.submit}"/>
			</p>
			{/html_form}
		</div>
	  </div>
	</div>
</div>