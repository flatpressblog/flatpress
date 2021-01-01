<div class="row">
	<div class="col-12">
		<div class="card shadow mb-4">
			<div class="card-header"><h6 class="m-0 font-weight-bold text-primary">{$plang.head}</h6></div>
				<div class="card-body">
					{include file=shared:errorlist.tpl}
					{html_form}
						

						<h3>{$plang.mode} </h3>
						<dl>
							<dt><label><input type="radio" class="form-check-input" name="mode" value="0" {if $pconfig.mode == 0 }checked=checked{/if}> 
											{$plang.auto} 	</label>	</dt>
											<dd>{$plang.autodescr}</dd>
							<dt><label><input type="radio" class="form-check-input" name="mode" value="1" {if $pconfig.mode==1}checked=checked{/if}> 
											{$plang.pathinfo}	</label> </dt>
											<dd>{$plang.pathinfodescr}</dd>
							<dt><label><input type="radio" class="form-check-input" name="mode" value="2" {if $pconfig.mode==2}checked=checked{/if}> 
											{$plang.httpget} 	</label>	</dt>
											<dd>{$plang.httpgetdescr}</dd>
							<dt><label><input type="radio" class="form-check-input" name="mode" value="3" {if $pconfig.mode==3}checked=checked{/if}> 
											{$plang.pretty}	</label>	</dt>
											<dd>{$plang.prettydescr}</dd>
						</dl>

						<div class="buttonbar">
							<input type="submit" class="btn btn-primary" name="saveopt" value="{$plang.saveopt}" />
						</div>



						<h3>{$plang.htaccess}</h3>

						<p>{$plang.description}</p>
						<p>	
						<textarea id="htaccess" name="htaccess" class="form-control mw-100" 
						{if $cantsave}readonly="readonly" {/if}cols="70" rows="16">{$htaccess|escape:'html'}</textarea>
						</p>
						
						<div class="buttonbar">
						{if $cantsave}
						<p><em>{$plang.cantsave}</em></p>
						{else}
						<input type="submit" class="btn btn-primary" name="htaccess-submit" value="{$plang.submit}"/>
						{/if}
						</div>
						
					{/html_form}
				</div>
			</div>
		</div>
	</div>
</div>