	{include file='shared:admin_errorlist.tpl'}
	{entry_block}
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
		
{html_form}	
	
	{entry content=$post alwaysshow=true}

		<div class="row">
            <div class="col-xl-8 col-lg-7">
              <div class="card shadow mb-4">
                <div class="card-header">
                  <h6 class="m-0 font-weight-bold text-primary">{$panelstrings.head}</h6>
                </div>
                <div class="card-body">
					<div id="admin-editor">
						<input type="text" {$error.subject|notempty:'class="field-error form-control input_gray input-max-width"'} 
							name="subject" id="subject" 
							value="{$subject|default:$smarty.request.subject|wp_specialchars:1}" placeholder="{$panelstrings.subject}"/ class="form-control input_gray input-max-width"><br />
						<input type="hidden" name="timestamp" value="{$date}" />
						<input type="hidden" name="entry" value="{$id}" />
						<p>
						<textarea name="content" class="{$error.content|notempty:'field-error'} form-control" 
						id="content_textarea" placeholder="{$panelstrings.content}">{$content|default:$smarty.request.content|htmlspecialchars}</textarea><br />
						{if $sceditor_display!='disable'}
						<script src="{$smarty.const.BLOG_BASEURL}/fp-includes/bootstrap/js/bootstrap.min.js"></script>
						
						<!-- Here is the SCEditor -->
						<link rel="stylesheet" type="text/css" href="{$smarty.const.BLOG_BASEURL}/admin/res/sceditor/themes/square.min.css">
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
            </div>

            <div class="col-xl-4 col-lg-5">
              <div class="card shadow mb-4 save_options">
                <div class="card-header">
                  <h6 class="m-0 font-weight-bold text-primary">{$panelstrings.saveopts}</h6>
                </div>
                <div class="card-body">
					<div class="buttonbar">
						{html_submit name="save" id="save" class="btn btn-primary" value=$panelstrings.submit accesskey=s}
						{html_submit name="savecontinue" id="savecontinue" class="btn btn-primary" value=Save accesskey=c}
						{html_submit name="preview" id="preview" class="btn btn-primary" value=$panelstrings.preview accesskey=p}
					</div>
					<p>
						{foreach from=$saved_flags item=flag}
						<label><input name="flags[{$flag}]" {if $categories and (bool)array_intersect(array($flag),$categories) }checked="checked"{/if} type="checkbox" /> {$lang.entry.flags.long[$flag]} </label><br />
						{/foreach}
					</p>
                </div>
              </div>
				
			 <div class="card shadow mb-4">
                <div class="card-header">
                  <h6 class="m-0 font-weight-bold text-primary">{$panelstrings.categories}</h6>
                </div>
                <div class="card-body categories_panel">
					{list_categories type=form selected=$categories}
                </div>
              </div>
			 <div class="card shadow mb-4 other_options">
                <div class="card-header">
                  <h6 class="m-0 font-weight-bold text-primary">{$panelstrings.otheropts}</h6>
                </div>
				 	<p>
						<ul>
							{if !$draft}
							<li><a href="admin.php?p=entry&amp;entry={$smarty.get.entry}&amp;action=commentlist">
								{$panelstrings.commmsg}</a></li>
							{/if}
							<li><a href="admin.php?p=entry&amp;entry={$smarty.get.entry}&amp;action=delete">
								{$panelstrings.delmsg}</a></li>
						</ul>
				 	</p>
              </div>
			<div class="card shadow mb-4 plugin_options">
				<div class="card-header">
				  <h6 class="m-0 font-weight-bold text-primary">Plugins Options</h6>
				</div>
				<div class="m-4">
					{toolbar}
					{action hook=simple_edit_form}
				</div>
			 </div>
            </div>
          </div>
		
		<div id="admin-options">
	
		{* let's disable this for now... *}
		
		{*
		
		<fieldset id="admin-entry-uploader"><legend>{$panelstrings.uploader}</legend>
			<iframe id="uploader-iframe" src="{$smarty.const.BLOG_BASEURL}admin.php?p=uploader&amp;mod=inline"></iframe>
		</fieldset>	
		*}
		
		{* end of inline form *}
		</div>

	
	{/entry}
{/html_form}
	{/entry_block}

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

{if $smarty.get.entry }

{/if}



