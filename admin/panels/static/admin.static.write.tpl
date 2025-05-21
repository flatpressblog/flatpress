{include file='shared:admin_errorlist.tpl'}
{static_block}
{if isset($preview)}
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
					{if !isset($post)}
						{assign var="post" value=""}
					{/if}		
					{static content=$post alwaysshow=true}
						<input type="text" name="subject" id="subject" class="form-control input_gray input-max-width" placeholder="{$panelstrings.subject}" {if isset($error)}{$error.subject|notempty:'class="field-error"'}{/if} 
						value="{$subject|default:$smarty.request.subject|default:$smarty.request.page|wp_specialchars:1}" />
						<input type="hidden" name="timestamp" value="{$date}" />
						<p>
						<label for="content"></label>
						<textarea name="content" {if isset($error)}{$error.content|notempty:'class="field-error"'}{/if} id="content_textarea" placeholder="{$panelstrings.content}" class="form-control">{$content|default:$smarty.request.content|htmlspecialchars}</textarea><br />
						{if $sceditor_display != 'disable'}
						<script src="{$smarty.const.BLOG_BASEURL}/fp-includes/bootstrap/js/bootstrap.min.js"></script>
						<!-- Here is the SCEditor -->
						<script src="{$smarty.const.BLOG_BASEURL}/admin/res/sceditor/sceditor.min.js"></script>
						<!--
						{if $sce_display=='bbcode'}
							<script src="{$smarty.const.BLOG_BASEURL}/admin/res/sceditor/formats/bbcode.js"></script>
						{else}
							<script src="{$smarty.const.BLOG_BASEURL}/admin/res/sceditor/formats/xhtml.js"></script>
						{/if}
						-->
						<script src="{$smarty.const.BLOG_BASEURL}/admin/res/sceditor/formats/bbcode.js"></script>
						<script src="{$smarty.const.BLOG_BASEURL}/fp-interface/lang/{$lang_locale}/sceditor.js"></script>
						<script src="{$smarty.const.BLOG_BASEURL}/admin/res/sceditor/plugins/flatPressFileManager.js"></script>
						<script src="{$smarty.const.BLOG_BASEURL}/admin/res/sceditor/plugins/flatPressCustomBBCodes.js"></script>
						<script src="{$smarty.const.BLOG_BASEURL}/admin/res/sceditor/plugins/flatPressEmojis.js"></script>
						<script>
						// Replace the textarea #example with SCEditor
						var lang_editor = "{$lang_locale}";
						var eRoot = "admin/res/sceditor/";
						var FileManagerDir = "{$smarty.const.BLOG_BASEURL}/fp-plugins/sceditorfilemanager";
						{literal}
						var textarea = document.getElementById('content_textarea');
						sceditor.create(textarea, {
							plugins: 'flatPressFileManager',
							toolbar: 'bold,italic,underline,strike,subscript,superscript|left,center,right,justify|font,size,color,removeformat|cut,copy,pastetext|bulletlist,orderedlist,indent,outdent|table|code,quote|horizontalrule,flatPressFileManager,email,link,unlink|emojis,youtube,date,time|ltr,rtl|print,maximize,source',
							emoticonsRoot: eRoot,
							format: 'bbcode',
							height: "400px",
							locale: lang_editor
						});
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
					<input type="text" name="id" id="id" class="maxsize{if isset($error)}{$error.id|notempty:' field-error'}{/if} form-control input_gray "
					value="{$smarty.request.id|default:$smarty.request.page|default:$static_id}"  /></p>
					{html_submit name="save" id="save" class="btn btn-primary" value=$panelstrings.submit accesskey=s}
					{html_submit name="preview" id="preview" class="btn btn-primary" value=$panelstrings.preview accesskey=p}
					</fieldset>
                </div>
              </div>
			  <div class="card shadow mb-4 plugin_options">
				<div class="card-header">
				  <h6 class="m-0 font-weight-bold text-primary">Plugins Options</h6>
				</div>
				<div class="m-4">
					{toolbar}
					<!-- {action hook=simple_edit_form} (Only writting an entry) -->
				</div>
			  </div>
            </div>
            </div>
		</div>

{/static}
{/html_form}
{/static_block}

<!-- Bootstrap Modal (Open the editor) -->
<div class="modal fade" id="flatpress-files-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
		<h5 class="modal-title" id="exampleModalLabel">File Manager</h5>
		<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
	  	<div class="row">
		  <div class="col-12" id="currentDirectory">
		  	<input class="form-control mw-100" id="directoryInput" readonly>
		  </div>
		</div>
	  	<div class="row visualizator">
		  <div class="p-2 col-6 h-100">
		  	<div class="flatpress-files-modal-box h-100 p-3" id="mediaDirectory"></div>
		  </div>
		  <div class="p-2 col-6 h-100">
			<div class="flatpress-files-modal-box h-100" id="mediaPreview"></div>
		  </div>
		</div>
      </div>
	  <div class="modal-footer">
	  	<div class="w-100 text-center" id="FilesModalFooter">
		</div>
      </div>
    </div>
  </div>
</div>