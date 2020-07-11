{include file='shared:admin_errorlist.tpl'}
{static_block}
{if $preview}
	<div class="row">
		<div class="col-xl-12 col-lg-12">
			<div class="card shadow mb-4">
				<div class="card-header">
					<h6 class="m-0 font-weight-bold text-primary">{$panelstrings.preview}</h6>
				</div>
				<div class="card-body">
					{include file=preview.tpl}
				</div>
			</div>
		</div>
	</div>
{/if}
	<div class="row">
		<div class="col-xl-8 col-lg-7">
			<div class="card shadow mb-4">
				<div class="card-header">
					<h6 class="m-0 font-weight-bold text-primary">{$panelstrings.head}</h6>
				</div>
				<div class="card-body">
					{html_form}		
					{static content=$post alwaysshow=true}
						<input type="text" name="subject" id="subject" class="form-control input_gray input-max-width" placeholder="{$panelstrings.subject}" {$error.subject|notempty:'class="field-error"'} 
						value="{$subject|default:$smarty.request.subject|default:$smarty.request.page|wp_specialchars:1}" />
						<input type="hidden" name="timestamp" value="{$date}" />
						<p>
						<label for="content"></label>
						<textarea name="content" {$error.content|notempty:'class="field-error"'} id="content_textarea" placeholder="{$panelstrings.content}" class="form-control">{$content|default:$smarty.request.content|htmlspecialchars}</textarea><br />
						{if $sceditor_display!='disable'}
						<script src="{$smarty.const.BLOG_BASEURL}/fp-includes/bootstrap/js/bootstrap.min.js"></script>
						<!-- Here is the SCEditor -->
						<script src="{$smarty.const.BLOG_BASEURL}/admin/res/sceditor/sceditor.min.js"></script>
						{if $sce_display=='bbcode'}
							<script src="{$smarty.const.BLOG_BASEURL}/admin/res/sceditor/formats/bbcode.js"></script>
						{else}
							<script src="{$smarty.const.BLOG_BASEURL}/admin/res/sceditor/formats/xhtml.js"></script>
						{/if}
						<script src="{$smarty.const.BLOG_BASEURL}/fp-interface/lang/{$lang_locale}/sceditor.js"></script>
						<script>
						// Replace the textarea #example with SCEditor
						var sce_display = "{$sceditor_display}";
						var lang_editor = "{$lang_locale}";
						var eRoot = "admin/res/sceditor/";
						var FileManagerDir = "{$smarty.const.BLOG_BASEURL}/fp-plugins/sceditorfilemanager";
						{literal}
						var textarea = document.getElementById('content_textarea');
						sceditor.create(textarea, {
							emoticonsRoot: eRoot,
							format: sce_display,
							height: "400px",
							locale: lang_editor
							//style: '../../res/sceditor/themes/content/default.min.css'
						});
						set_media_button(FileManagerDir);
						</script>
						{/literal}
						{/if}
						{*here will go a plugin hook*}
						</p>
				</div>
			</div>
		</div>
		<div class="col-xl-4 col-lg-5">
              <div class="card shadow mb-4">
                <div class="card-header">
                  <h6 class="m-0 font-weight-bold text-primary">{$panelstrings.pagename}</h6>
                </div>
                <div class="card-body">
					<input type="hidden" name="oldid" id="oldid" class="form-control input_gray" value="{$id|default:$smarty.request.oldid}" />
					<input type="text" name="id" id="id" class="maxsize{$error.id|notempty:' field-error'} form-control input_gray "
					value="{$smarty.request.id|default:$smarty.request.page|default:$static_id}"  /></p>
					{html_submit name="save" id="save" class="btn btn-primary" value=$panelstrings.submit accesskey=s}
					{html_submit name="preview" id="preview" class="btn btn-primary" value=$panelstrings.preview accesskey=p}
					</fieldset>
                </div>
              </div>
            </div>
		</div>

{/static}
{/html_form}
{/static_block}

<!-- Bootstrap Modal (Open the editor) -->
<div class="modal fade" id="flatpress-files-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">File Manager</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        NOT WORKING YEY :(
      </div>
    </div>
  </div>
</div>