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

		{html_form enctype='multipart/form-data'}

			{assign var="is_inline" value=(isset($smarty.request.mod) && $smarty.request.mod == 'inline')}
				{if !$is_inline}<fieldset><legend>{$panelstrings.fset1}</legend>{/if}

					<div id="fp-dropzone" class="fp-dropzone" style="cursor: pointer; text-align: center" role="group" aria-labelledby="fp-dropzone-label" tabindex="0" data-i18n-drop="{$panelstrings.uploader_drop|default:'Drag files here'}" data-i18n-or="{$panelstrings.uploader_or|default:'oder'}" data-i18n-browse="{$panelstrings.uploader_browse_button|default:'Select files'}" data-i18n-browse-hint="{$panelstrings.uploader_browse_hint|default:'...or click to select files'}" data-i18n-drop-active="{$panelstrings.uploader_drop_active|default:'Release to add'}" data-i18n-selected-count="{$panelstrings.uploader_selected_count|default:'%d file(s) selected'}" data-i18n-clear="{$panelstrings.uploader_clear|default:'Clear selection'}" data-i18n-remove="{$panelstrings.uploader_remove|default:'Remove'}">

						<p id="fp-dropzone-label" class="fp-dropzone__label">
							<span class="fp-dropzone__drop">{$panelstrings.uploader_drop|default:'Drag files here'}</span>
						</p>
						<p class="fp-dropzone__hint">
							{$panelstrings.uploader_browse_hint|default:'...or click to select files'}
						</p>

						<input id="uploader-input" name="upload[]" type="file" multiple class="fp-uploader-input" aria-hidden="true" style="position: absolute; left: -9999px; width: 1px; height: 1px; opacity: 0">

						<ul id="fp-selected-list" class="fp-upload-list" aria-live="polite"></ul>

						<div class="fp-dropzone__toolbar">
							<button type="button" class="button button-secondary" id="fp-clear-selection" style="display: none">
								{$panelstrings.uploader_clear|default:'Clear selection'}
							</button>
						</div>
					</div>

				{if !$is_inline}</fieldset>{/if}

			<div class="buttonbar">
			{html_submit name="upload" id="upload" value=$panelstrings.submit}
			</div>

		{/html_form}

		<script nonce="{$smarty.const.RANDOM_HEX}">
			/**
			 * Uploader Drag & Drop / Picker-Skript
			 */
			(function() {
				'use strict';

				var zone = document.getElementById('fp-dropzone');
				var input = document.getElementById('uploader-input');
				var browse = document.getElementById('fp-browse-trigger');
				var list = document.getElementById('fp-selected-list');
				var clearBtn = document.getElementById('fp-clear-selection');

				if (!zone || !input || !list || !clearBtn) {
					console.warn('Uploader UI not initialized: missing elements');
					return;
				}

				var dt = (function() {
					try {
						return new DataTransfer();
					} catch(e) {
						return null;
					}
				})();
				function t(k) {
					return zone.getAttribute('data-i18n-' + k) || '';
				}
				function fileKey(f) {
					return [f.name, f.size, f.lastModified].join('#');
				}
				var seen = Object.create(null);

				function render() {
					list.innerHTML = '';
					var files = dt ? dt.files : input.files;
					var n = files ? files.length : 0;
					for (var i = 0; i < n; i++) {
						(function(idx, f) {
							var li = document.createElement('li');
							li.className = 'fp-upload-list__item';
							var name = document.createElement('span');
							name.className = 'fp-upload-list__name';
							name.textContent = f.name + ' (' + Math.round(f.size/1024) + ' KB)';
							li.appendChild(name);
							var btn = document.createElement('button');
							btn.type = 'button';
							btn.className = 'button button-small fp-upload-list__remove';
							btn.setAttribute('aria-label', (t('remove') || 'Remove') + ': ' + f.name);
							btn.textContent = t('remove') || 'Remove';
							btn.addEventListener('click', function(){
								removeAt(idx);
							});
							li.appendChild(btn);
							list.appendChild(li);
						})(i, files[i]);
					}
					if (n>0){
						clearBtn.style.display = '';
						zone.setAttribute('data-has-files', '1');
						zone.setAttribute('aria-label', (t('selected-count') || '%d file(s) selected').replace('%d', n));
					} else {
						clearBtn.style.display = 'none';
						zone.removeAttribute('data-has-files');
						zone.removeAttribute('aria-label');
					}
				}
				function syncInput(){
					if (dt) input.files = dt.files;
					render();
				}
				function addFiles(fl){
					if(!fl) return;
					for (var i = 0; i < fl.length; i++){
						var f = fl[i];
						var k = fileKey(f);
						if(seen[k]) continue;
						seen[k] = true;
						if(dt) dt.items.add(f);
					} if(!dt) {
						/* Fallback: last selection wins */
					} syncInput();
				}
				function removeAt(idx) {
					if(dt && idx >= 0 && idx < dt.items.length) {
						var f = dt.items[idx].getAsFile();
						if(f) delete seen[fileKey(f)];
						dt.items.remove(idx);
						syncInput();
					} else {
						render();
					}
				}
				function onDrop(e){
					e.preventDefault();
					e.stopPropagation();
					zone.classList.remove('is-dragover');
					var dropped = e.dataTransfer && e.dataTransfer.files;
					if (dt) {
						addFiles(dropped);
					} else {
						setTimeout(render, 0);
					}
				}
				function onDragOver(e) {
					e.preventDefault();
					e.stopPropagation();
					zone.classList.add('is-dragover');
				}
				function onDragLeave(e) {
					if(e.target === zone) {
						zone.classList.remove('is-dragover');
					}
				}
				zone.addEventListener('dragover', onDragOver);
				zone.addEventListener('dragleave', onDragLeave);
				zone.addEventListener('drop', onDrop);
				zone.addEventListener('click', function(e) {
					if(e.target && e.target.closest('.fp-upload-list__remove')) return;
					input.click();
				});
				if (browse) {
					browse.addEventListener('click', function() {
						input.click();
					});
				}
				input.addEventListener('change', function() {
					addFiles(input.files);
				});
				clearBtn.addEventListener('click', function() {
					seen = Object.create(null);
					if(dt) {
						while(dt.items.length) dt.items.remove(0);
						syncInput();
					} else {
						input.value = '';
						render();
					}
				});
				zone.addEventListener('keydown', function(e) {
					if(e.key === 'Enter' || e.key === ' ') {
						e.preventDefault();
						input.click();
					}
				});
			})();
		</script>
