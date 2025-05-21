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
								<div class="mb-3">
									<input class="form-control mw-100" type="file" id="formFile1">
								</div>
								<div class="mb-3">
									<input class="form-control mw-100" type="file" id="formFile2">
								</div>
								<div class="mb-3">
									<input class="form-control mw-100" type="file" id="formFile1">
								</div>
								<div class="mb-3">
									<input class="form-control mw-100" type="file" id="formFile1">
								</div>
							</div>
							<div class="col-lg-6 mb-4">
								<div class="mb-3">
									<input class="form-control mw-100" type="file" id="formFile1">
								</div>
								<div class="mb-3">
									<input class="form-control mw-100" type="file" id="formFile1">
								</div>
								<div class="mb-3">
									<input class="form-control mw-100" type="file" id="formFile1">
								</div>
								<div class="mb-3">
									<input class="form-control mw-100" type="file" id="formFile1">
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