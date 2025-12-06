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

	var maxFiles = parseInt(zone.getAttribute('data-max-files') || '', 10);
	if (!isFinite(maxFiles) || maxFiles <= 0) {
		maxFiles = null;
	}

	var maxBytes = parseInt(zone.getAttribute('data-max-bytes') || '', 10);
	if (!isFinite(maxBytes) || maxBytes <= 0) {
		maxBytes = null;
	}

	var maxHuman = zone.getAttribute('data-max-human') || '';

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
				name.textContent = f.name + ' (' + Math.round(f.size/1024) + ' KB) ';
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
		if(!fl) {
			return;
		}

		var existing = dt ? dt.files : input.files;
		var count = existing ? existing.length : 0;
		var bytes = 0;

		if (existing) {
			for (var j = 0; j < existing.length; j++) {
				bytes += existing[j].size || 0;
			}
		}

		var added = 0;
		var addedBytes = 0;

		for (var i = 0; i < fl.length; i++){
			var f = fl[i];
			var k = fileKey(f);

			if (seen[k]) {
				continue;
			}

			if (maxFiles && (count + added) >= maxFiles) {
				var msgFiles = t('max-files') || 'Maximum %d files per upload.';
				msgFiles = msgFiles.replace('%d', maxFiles);
				window.alert(msgFiles);
				break;
			}

			var fSize = f.size || 0;
			if (maxBytes && (bytes + addedBytes + fSize) > maxBytes) {
				var msgSize = t('max-size') || 'Maximum total upload size: %s.';
				var human = maxHuman || (Math.round(maxBytes / (1024 * 1024)) + ' MB');
				msgSize = msgSize.replace('%s', human);
				window.alert(msgSize);
				continue;
			}

			seen[k] = true;
			if (dt) {
				dt.items.add(f);
			}

			added++;
			addedBytes += fSize;
		}

		if(!dt) {
			/* Fallback: last selection wins */
		}
		syncInput();
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
