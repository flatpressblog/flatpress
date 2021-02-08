
{include file='shared:admin_errorlist.tpl'}

{html_form}

<div class="row">
		<div class="col-xl-12 col-lg-12">
			<div class="card shadow mb-4">
				<div class="card-header">
					<h6 class="m-0 font-weight-bold text-primary">{$panelstrings.head}</h6>
				</div>
				<div class="card-body options_page">
					<div class="row">
						<div class="col-lg-6 mb-4">
							<div id="admin-config-general">
								<h2>{$panelstrings.gensetts}</h2>
								<dl class="option-list">
								<dt><label for="title"> {$panelstrings.blogtitle} </label></dt>
								<dd><input type="text" name="title" id="title" class="textinput{$error.title|notempty:' field-error'} form-control input_gray" 
								value="{$flatpress.TITLE|escape:"html"}" />
								</dd>


								<dt><label for="subtitle"> {$panelstrings.blogsubtitle} </label></dt>
								<dd><input type="text" name="subtitle" id="subtitle" class="bigtextinput form-control input_gray"  value="{$flatpress.subtitle|escape:"html"}" /></dd>

								<dt><label for="blogfooter"> {$panelstrings.blogfooter} </label></dt>
								<dd><input type="text" name="blogfooter" id="blogfooter" class="textinput form-control input_gray" value="{$flatpress.footer|escape:"html"}" /></dd>

								<dt><label for="author"> {$panelstrings.blogauthor} </label></dt>
								<dd><input type="text" name="author" id="author" class="textinput form-control input_gray" value="{$flatpress.author}" /></dd>


								<dt><label for="www"> {$panelstrings.blogurl} </label></dt>
								<dd><input type="text" name="www" id="www" class="form-control input_gray textinput{$error.www|notempty:" field-error"}"
										value="{$flatpress.www|escape:"html"}" /></dd>


								<dt><label for="email"> {$panelstrings.blogemail} </label></dt>
								<dd><input type="text" name="email" id="email" class="form-control input_gray textinput{$error.email|notempty:" field-error"}" 
								value="{$flatpress.email}" /></dd>

								<dt> {$panelstrings.notifications} </dt>
								<dd> 
								<label for="notify"> 
								<input type="checkbox" name="notify" id="notify"{if $flatpress.NOTIFY}checked="checked"{/if} /> 
								{$panelstrings.mailnotify} 
								</label> 
								</dd>

								<dt><label for="startpage"> {$panelstrings.startpage} </label></dt>
								<dd><select name="startpage" id="startpage" class="textinput form-control input_gray">
									<option value=":NULL:">
										{$panelstrings.stdstartpage}
									</option>
								{foreach from=$static_list key=staticid item=staticpage}
									<option value="{$staticid}"{if $staticid == $fp_config.general.startpage} selected="selected"{/if}>
										{$staticpage.subject}
									</option>
								{/foreach}
								</select>
								</dd>

								<dt><label for="maxentries"> {$panelstrings.blogmaxentries} </label></dt>
								<dd><input type="text" name="maxentries" id="maxentries" 
								class="form-control input_gray smalltextinput{$error.maxentries|notempty:" field-error"}" value="{$flatpress.maxentries}" /></dd>
								</dl>

							</div>
						</div>
						<div class="col-lg-6 mb-4">
						<div id="admin-config-intsetts">
						<h2> {$panelstrings.intsetts}  </h2>
							<dl class="option-list">
								<dt> {$panelstrings.utctime} </dt>
								{assign var=temp_time value="%b %d %Y %H:%M:%S"}
								<dd> <code> {"r"|date:$smarty.now} </code> </dd>

								<dt><label for="timeoffset"> {$panelstrings.timeoffset} </label></dt>
								<dd><input type="text" name="timeoffset" id="timeoffset" 
									class="form-control input_gray smalltextinput{$error.timeoffset|notempty:" field-error"}" 
									value="{$fp_config.locale.timeoffset}" /> {$panelstrings.hours} 
								</dd>


								<dt><label for="dateformat"> {$panelstrings.dateformat} </label></dt>
								<dd>	<p> <input type="text" name="dateformat" id="dateformat" 
									class="form-control input_gray textinput{$error.dateformat|notempty:" field-error"}" 
									value="{$fp_config.locale.dateformat}" /> </p>
									<p> {$panelstrings.output}:   {$smarty.now|date_format:$fp_config.locale.dateformat}  </p>
								</dd>

								<dt><label for="dateformatshort"> {$panelstrings.dateformatshort} </label></dt>
								<dd>	<p> <input type="text" name="dateformatshort" id="dateformatshort" 
									class="form-control input_gray textinput{$error.dateformatshort|notempty:" field-error"}" 
									value="{$fp_config.locale.dateformatshort}" /> </p>
									<p> {$panelstrings.output}:   {$smarty.now|date_format:$fp_config.locale.dateformatshort}  </p>
								</dd>

								<dt><label for="timeformat"> {$panelstrings.timeformat} </label></dt>
								<dd>	<p> <input type="text" name="timeformat" id="timeformat" 
									class="form-control input_gray textinput{$error.timeformat|notempty:" field-error"}" 
									value="{$fp_config.locale.timeformat}" /> </p>
									<p> {$panelstrings.output}:  {$smarty.now|date_format:$fp_config.locale.timeformat}  </p>
								</dd>


								<dt><label for="lang"> {$panelstrings.langchoice} </label></dt>
								<dd>	
								<select name="lang" id="lang" class="form-control input_gray textinput">
								{foreach from=$lang_list item=langsetts}
									<option value="{$langsetts.locale}" 
										{if $langsetts.locale == $fp_config.locale.lang}selected="selected"{/if}>
										{$langsetts.id}
									</option>
								{/foreach}
								</select>
								</dd>

								<dt> <label for="charset"> {$panelstrings.charset} </label></dt>
								<dd> <p><input type="text" name="charset" id="charset" 
									class="form-control input_gray smalltextinput{$error.charset|notempty:" field-error"}" 
									value="{$fp_config.locale.charset}" /></p>
									<p>{$panelstrings.charsettip}</p>
								</dd>
							</dl>
							</div>
						</div>
					</div>
					<div class="buttonbar">
						{html_submit name="save" id="save" class="btn btn-primary" value=$panelstrings.submit}
					</div>
				</div>
			</div>
		 </div>
</div>



{/html_form}


