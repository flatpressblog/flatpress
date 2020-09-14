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
										<span class="input-group-text ti-folder" id="inputGroupFileAddon01"></span>
									</div>
									<div class="custom-file">
										<input type="file" class="custom-file-input" id="inputGroupFile01" aria-describedby="inputGroupFileAddon01">
										<label class="custom-file-label" for="inputGroupFile01" id="inputGroupFile01Label">Choose file</label>
									</div>
								</div>
								<div class="input-group mb-3">
									<div class="input-group-prepend">
										<span class="input-group-text ti-folder" id="inputGroupFileAddon02"></span>
									</div>
									<div class="custom-file">
										<input type="file" class="custom-file-input" id="inputGroupFile02" aria-describedby="inputGroupFileAddon02">
										<label class="custom-file-label" for="inputGroupFile02">Choose file</label>
									</div>
								</div>
								<div class="input-group mb-3">
									<div class="input-group-prepend">
										<span class="input-group-text ti-folder" id="inputGroupFileAddon03"></span>
									</div>
									<div class="custom-file">
										<input type="file" class="custom-file-input" id="inputGroupFile03" aria-describedby="inputGroupFileAddon03">
										<label class="custom-file-label" for="inputGroupFile03">Choose file</label>
									</div>
								</div>
								<div class="input-group mb-3">
									<div class="input-group-prepend">
										<span class="input-group-text ti-folder" id="inputGroupFileAddon04"></span>
									</div>
									<div class="custom-file">
										<input type="file" class="custom-file-input" id="inputGroupFile04" aria-describedby="inputGroupFileAddon04">
										<label class="custom-file-label" for="inputGroupFile04">Choose file</label>
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

{literal}
	<script>
	/* This event to change name to namesfiles */
	startUploadEvent();
	</script>
{/literal}