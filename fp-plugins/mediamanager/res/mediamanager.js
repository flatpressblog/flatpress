/**
 * Hover/long-press image preview for Media Manager 2.X.X
 */
(function () {
	'use strict';

	var SUPPORTS_POINTER = typeof window.PointerEvent !== 'undefined';
	var IMG_EXT = /(\.png|\.jpe?g|\.gif|\.webp|\.avif|\.bmp|\.svg)$/i;
	var preview = null;
	var hideTimer = null;
	var showTimer = null;
	var currentAnchor = null;
	var longPressTimer = null;
	var longPressFired = false;
	var lastPointer = { x: 0, y: 0, type: 'mouse' };
	var openDelay = 140; // ms (prevents flicker & reduces loads when moving quickly)
	var hideDelay = 60; // ms
	var LONG_PRESS_DELAY = 300; // ms
	var MAX_W = 300, MAX_H = 300;

	function cancelShow(){
		clearTimeout(showTimer);
		showTimer = null;
		clearTimeout(longPressTimer);
		longPressTimer = null;
		longPressFired = false;
	}

	function isImageLink(a) {
		if (!a || !a.href) {
			return false;
		}
		var href = a.getAttribute('href') || '';
		if (!href) {
			return false;
		}
		if (IMG_EXT.test(href)) {
			return true;
		}
		var u;
		try {
			u = new URL(href, document.baseURI);
		} catch (e) {
			return false;
		}
		var possible = u.searchParams.get('file') || u.searchParams.get('src') || u.searchParams.get('image');
		return !!(possible && IMG_EXT.test(possible));
	}

	function resolveImageURL(a) {
		if (!a) {
			return null;
		}
		var href = a.getAttribute('href') || '';
		if (IMG_EXT.test(href)) {
			return href;
		}
		try {
			var u = new URL(href, document.baseURI);
			var possible = u.searchParams.get('file') || u.searchParams.get('src') || u.searchParams.get('image');
			if (possible) return possible;
		} catch(e) {
			//
		}
		var ds = a.dataset || {};
		return ds.mmPreview || ds.src || null;
	}

	function createPreviewNode() {
		var n = document.createElement('div');
		n.className = 'mm-hover-preview';
		n.style.pointerEvents = 'none';
		var img = document.createElement('img');
		img.alt = '';
		img.decoding = 'async';
		img.loading = 'eager';
		img.style.maxWidth = MAX_W + 'px';
		img.style.maxHeight = MAX_H + 'px';
		n.appendChild(img);
		document.body.appendChild(n);
		return n;
	}
	function ensurePreview() {
		if (!preview) {
			preview = createPreviewNode();
		}
	}

	function scheduleShow(a) {
		clearTimeout(showTimer);
		showTimer = setTimeout(function() {
			showNow(a);
		}, openDelay);
	}

	function showNow(a) {
		var url = resolveImageURL(a);
		if (!url) {
			return;
		}
		ensurePreview();
		var img = preview.querySelector('img');
		if (img.getAttribute('src') !== url) {
			img.removeAttribute('src');
			// Simple eager fetch; browser cache makes switching cheap
			img.src = url;
		}
		preview.style.display = 'block';
		positionPreview(lastPointer.x, lastPointer.y);
	}

	function positionPreview(x, y) {
		if (!preview) {
			return;
		}
		var pad = 16;
		// Force visibility for measurement
		var wasHidden = (preview.style.display === 'none' || getComputedStyle(preview).display === 'none');
		if (wasHidden) {
			preview.style.display = 'block';
		}
		var rect = preview.getBoundingClientRect();
		var w = rect.width || (MAX_W + 2*pad);
		var h = rect.height || (MAX_H + 2*pad);
		var vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
		var vh = Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0);
		var left = x + pad, top = y + pad;
		if (left + w > vw) {
			left = Math.max(pad, x - w - pad);
		}
		if (top + h > vh) {
			top = Math.max(pad, y - h - pad);
		}
		preview.style.left = left + 'px';
		preview.style.top = top + 'px';
	}

	function hidePreviewSoon(delay) {
		if (!preview) {
			return;
		}
		clearTimeout(hideTimer);
		cancelShow();
		hideTimer = setTimeout(function(){
			preview.style.display = 'none';
		}, typeof delay === 'number' ? delay : hideDelay);
	}

	function setCurrentAnchor(a) {
		if (currentAnchor === a) {
			return;
		}
		cancelShow();
		currentAnchor = a;
		if (!a) {
			return;
		}
		// Only hide when the pointer truly leaves this link
		a.addEventListener('mouseleave', function () {
			if (currentAnchor === a) {
				currentAnchor = null;
			}
			hidePreviewSoon(30);
		}, { once: true });
	}

	function onOver(e) {
		var a = e.target && e.target.closest ? e.target.closest('a') : null;
		if (!a || !isImageLink(a)) {
			return;
		}
		setCurrentAnchor(a);
		scheduleShow(a);
	}

	function onMove(e) {
		lastPointer.x = e.clientX; lastPointer.y = e.clientY;
		if (!currentAnchor) {
			return;
		}
		if (preview && preview.style.display === 'block') {
			positionPreview(lastPointer.x, lastPointer.y);
		}
	}

	// Touch / long-press support (mobile)
	function onPointerDown(e) {
		if (!e.isPrimary) {
			return;
		}
		lastPointer.x = e.clientX; lastPointer.y = e.clientY; lastPointer.type = e.pointerType || 'mouse';
		var a = e.target && e.target.closest ? e.target.closest('a') : null;
		if (!a || !isImageLink(a)) {
			return;
		}
		if (lastPointer.type !== 'touch') {
			return;
		}
		longPressFired = false;
		clearTimeout(longPressTimer);
		setCurrentAnchor(a);
		longPressTimer = setTimeout(function() {
			longPressFired = true;
			showNow(a);
		}, LONG_PRESS_DELAY);
		// If long-press fires, prevent navigation on the ensuing click
		var preventClick = function(ev) {
			if (longPressFired) {
				ev.preventDefault();
				ev.stopPropagation();
			}
			a.removeEventListener('click', preventClick, true);
		};
		a.addEventListener('click', preventClick, true);
	}
	function onPointerUpCancel(e) {
		clearTimeout(longPressTimer);
		if (longPressFired) {
			// Give user time to view, then hide on next tap or slight delay
			hidePreviewSoon(200);
			longPressFired = false;
		}
	}

	function onKeyDown(e) {
		if (e.key === 'Escape') {
			cancelShow();
			hidePreviewSoon(0);
		}
	}
	function onScroll() {
		hidePreviewSoon(0);
	}

	var root = document.querySelector('table.entrylist') || document;

	if (SUPPORTS_POINTER) {
		root.addEventListener('pointerover', onOver, true);
		root.addEventListener('pointermove', onMove, true);
		root.addEventListener('pointerdown', onPointerDown, { passive: true });
		root.addEventListener('pointerup', onPointerUpCancel, { passive: true });
		root.addEventListener('pointercancel', onPointerUpCancel, { passive: true });
	} else {
		// Fallback for very old browsers
		root.addEventListener('mouseover', onOver, true);
		root.addEventListener('mousemove', onMove, true);
		root.addEventListener('touchstart', function(e){
			// Simple immediate preview on touch for old devices
			var a = e.target && e.target.closest ? e.target.closest('a') : null;
			if (!a || !isImageLink(a)) {
				return;
			}
			lastPointer.x = (e.touches && e.touches[0] ? e.touches[0].clientX : 0);
			lastPointer.y = (e.touches && e.touches[0] ? e.touches[0].clientY : 0);
			setCurrentAnchor(a);
			showNow(a);
		}, { passive: true });
		root.addEventListener('touchend', function(){
			hidePreviewSoon(120);
		}, { passive: true });
		root.addEventListener('touchcancel', function(){
			hidePreviewSoon(0);
		}, { passive: true });
	}

	window.addEventListener('scroll', function(){
		cancelShow();
		onScroll();
	}, { passive: true });

	window.addEventListener('keydown', onKeyDown);
})();
