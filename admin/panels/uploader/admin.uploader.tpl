{include file='shared:admin_errorlist.tpl'}

	<div class="row">
		<div class="col-xl-12 col-lg-12">
			<div class="card shadow mb-4">
				<div class="card-header">
					<h6 class="m-0 font-weight-bold text-primary">{$panelstrings.fset1}</h6>
				</div>
				<div class="card-body">
					{if $success}
					<ul id="admin-uploader-filelist">
					{foreach from=$uploaded_files item=file}
						{* 

							memo: this did the trick in the panel
							<a href="javascript:window.parent.window.insImage('{$file}');">

						 *}
						<li>{$file}</li>
					{/foreach}
					</ul>
				{/if}

				{html_form enctype='multipart/form-data'}
						<div class="row">
							<div class="col-lg-6 mb-4">
								<div class="input-group mb-3">
								  <div class="input-group-prepend">
									<span class="input-group-text ti-folder"></span>
								  </div>
								  <div class="custom-file">
									<input type="file" class="custom-file-input" name="upload[]" />
									<label class="custom-file-label" for="inputGroupFile01">{$panelstrings.choose_file}</label>
								  </div>
								</div>
								<div class="input-group mb-3">
								  <div class="input-group-prepend">
									<span class="input-group-text ti-folder"></span>
								  </div>
								  <div class="custom-file">
									<input type="file" class="custom-file-input" name="upload[]" />
									<label class="custom-file-label" for="inputGroupFile01">{$panelstrings.choose_file}</label>
								  </div>
								</div>
								<div class="input-group mb-3">
								  <div class="input-group-prepend">
									<span class="input-group-text ti-folder"></span>
								  </div>
								  <div class="custom-file">
									<input type="file" class="custom-file-input" name="upload[]" />
									<label class="custom-file-label" for="inputGroupFile01">{$panelstrings.choose_file}</label>
								  </div>
								</div>
								<div class="input-group mb-3">
								  <div class="input-group-prepend">
									<span class="input-group-text ti-folder"></span>
								  </div>
								  <div class="custom-file">
									<input type="file" class="custom-file-input" name="upload[]" />
									<label class="custom-file-label" for="inputGroupFile01">{$panelstrings.choose_file}</label>
								  </div>
								</div>
							</div>
							<div class="col-lg-6 mb-4">
								<div class="input-group mb-3">
								  <div class="input-group-prepend">
									<span class="input-group-text ti-folder"></span>
								  </div>
								  <div class="custom-file">
									<input type="file" class="custom-file-input" name="upload[]" />
									<label class="custom-file-label" for="inputGroupFile01">{$panelstrings.choose_file}</label>
								  </div>
								</div>
								<div class="input-group mb-3">
								  <div class="input-group-prepend">
									<span class="input-group-text ti-folder"></span>
								  </div>
								  <div class="custom-file">
									<input type="file" class="custom-file-input" name="upload[]" />
									<label class="custom-file-label" for="inputGroupFile01">{$panelstrings.choose_file}</label>
								  </div>
								</div>
								<div class="input-group mb-3">
								  <div class="input-group-prepend">
									<span class="input-group-text ti-folder"></span>
								  </div>
								  <div class="custom-file">
									<input type="file" class="custom-file-input" name="upload[]" />
									<label class="custom-file-label" for="inputGroupFile01">{$panelstrings.choose_file}</label>
								  </div>
								</div>
								<div class="input-group mb-3">
								  <div class="input-group-prepend">
									<span class="input-group-text ti-folder"></span>
								  </div>
								  <div class="custom-file">
									<input type="file" class="custom-file-input" name="upload[]" />
									<label class="custom-file-label" for="inputGroupFile01">{$panelstrings.choose_file}</label>
								  </div>
								</div>
							</div>
							<div class="buttonbar upload-buttom">
								{html_submit name="upload" id="upload" class="btn btn-primary" value=$panelstrings.submit}	
							</div>
						</div>
				</div>
			</div>
		</div>
	</div>
		
{/html_form}
