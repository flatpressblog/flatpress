		{if !isset($smarty.request.mod) || $smarty.request.mod != 'inline'}
			<h2>{$panelstrings.head}</h2>
			<p>{$panelstrings.descr}</p>
		{/if}

		{include file="shared:errorlist.tpl"}

		{if $success}
			<ul id="admin-uploader-filelist">
			{foreach from=$uploaded_files item=file}
				<li>{$file}</li>
			{/foreach}
			</ul>
		{/if}

		{if isset($upload_errors) && $upload_errors|count}
			<ul class="msgs warnings">
				{$panelstrings.uploader_some_failed|default:'This file was not uploaded for security or system reasons:'|escape}
			</ul>
			<ul>
				{foreach from=$upload_errors item=uf}
					<li>{$uf|escape}</li>
				{/foreach}
			</ul>
			<br>
		{/if}
		{if isset($upload_warnings) && $upload_warnings|count}
			<ul class="msgs warnings">
				{$panelstrings.uploader_metadata_failed|default:'The file was uploaded, but metadata could not be removed:'|escape}
			</ul>
			<ul>
				{foreach from=$upload_warnings item=wf}
					<li>{$wf|escape}</li>
				{/foreach}
			</ul>
			<br>
		{/if}

		{html_form enctype='multipart/form-data'}

			{assign var="is_inline" value=(isset($smarty.request.mod) && $smarty.request.mod == 'inline')}
				{if !$is_inline}<fieldset><legend>{$panelstrings.fset1}</legend>{/if}

					<div id="fp-dropzone" class="fp-dropzone" data-max-files="{$upload_limits.max_files|default:'' }" data-max-bytes="{$upload_limits.max_bytes|default:'' }" data-max-human="{$upload_limits.max_bytes_readable|default:'' }" style="cursor: pointer; padding: 1rem" role="group" aria-labelledby="fp-dropzone-label" tabindex="0" data-i18n-drop="{$panelstrings.uploader_drop|default:'Drag files here'}" data-i18n-or="{$panelstrings.uploader_or|default:'or'}" data-i18n-browse="{$panelstrings.uploader_browse_button|default:'Select files'}" data-i18n-browse-hint="{$panelstrings.uploader_browse_hint|default:'...or click to select files'}" data-i18n-drop-active="{$panelstrings.uploader_drop_active|default:'Release to add'}" data-i18n-selected-count="{$panelstrings.uploader_selected_count|default:'%d file(s) selected'}" data-i18n-clear="{$panelstrings.uploader_clear|default:'Clear selection'}" data-i18n-remove="{$panelstrings.uploader_remove|default:'Remove'}" data-i18n-max-files="{$panelstrings.uploader_limit_files|default:'Maximum %d files per upload.'}" data-i18n-max-size="{$panelstrings.uploader_limit_size|default:'Maximum total upload size: %s.'}">

						<p id="fp-dropzone-label" class="fp-dropzone__label" style="text-align: center">
							<span class="fp-dropzone__drop">{$panelstrings.uploader_drop|default:'Drag files here'}</span>
						</p>
						<p class="fp-dropzone__hint" style="text-align: center">
							{$panelstrings.uploader_browse_hint|default:'...or click to select files'}
						</p>

						<input id="uploader-input" name="upload[]" type="file" multiple class="fp-uploader-input" aria-hidden="true" style="position: absolute; left: -9999px; width: 1px; height: 1px; opacity: 0">

						<ul id="fp-selected-list" class="fp-upload-list" style="padding: 0.75rem 0 0 0; list-style: none; margin: 0" aria-live="polite"></ul>

						<div class="fp-dropzone__toolbar">
							<button type="button" class="button button-secondary" id="fp-clear-selection" style="margin: 0.75rem 0px 0px; display: none">
								{$panelstrings.uploader_clear|default:'Clear selection'}
							</button>
						</div>
					</div>

				{if !$is_inline}</fieldset>{/if}

			<div class="buttonbar">
			{html_submit name="upload" id="upload" value=$panelstrings.submit}
			</div>

		{/html_form}
