/**
 * Stringendo (Leggero) – Smooth wheel scrolling without "double smoothing".
 *
 * Why: Combining CSS `scroll-behavior: smooth` with JS `window.scrollTo()`-based
 * animations often produces stutter because browsers smooth both.
 *
 * This script implements a light inertia/lerp wheel scroll (desktop only) and
 * disables CSS smooth scrolling by adding `html.fp-smooth-scroll`.
 *
 * Safety/compat:
 * - Disabled for bots/crawlers (UA regex) and for automation (navigator.webdriver).
 * - Respects prefers-reduced-motion.
 * - Disabled on touch devices.
 * - Leaves scrollable containers (overflow:auto/scroll) untouched.
 */

(function () {
	'use strict';

	try {
		if (!window || !document || !document.documentElement) {
			return;
		}

		var root = document.documentElement;
		var ua = (navigator.userAgent || '').toLowerCase();
		var isBot = /bot|crawl|spider|slurp|bingpreview|duckduckbot|yandex|baiduspider|sogou|exabot|facebot|ia_archiver|twitterbot|linkedinbot|pinterest|embedly|lighthouse/i.test(ua);

		// Never enable in automation contexts.
		if (isBot || (navigator && navigator.webdriver)) {
			return;
		}

		// Respect accessibility settings.
		if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
			return;
		}

		// Disable on touch devices.
		var hasTouch = ('ontouchstart' in window) || (navigator && navigator.maxTouchPoints && navigator.maxTouchPoints > 0) || (navigator && navigator.msMaxTouchPoints && navigator.msMaxTouchPoints > 0);
		if (hasTouch) {
			return;
		}

		// Add class to disable CSS smooth scrolling while this JS is active.
		if (root.classList && !root.classList.contains('fp-smooth-scroll')) {
			root.classList.add('fp-smooth-scroll');
		}

		// requestAnimationFrame/cancelAnimationFrame fallbacks.
		var raf = window.requestAnimationFrame || function (cb) {
			return window.setTimeout(function () {
				cb(Date.now());
			}, 16);
		};
		var caf = window.cancelAnimationFrame || window.clearTimeout;

		// Passive listener support detection (required to call preventDefault on wheel).
		var supportsPassive = false;
		try {
			var noop = function () {};
			var opts = Object.defineProperty({}, 'passive', {
				get: function () {
					supportsPassive = true;
				}
			});
			window.addEventListener('testPassive', noop, opts);
			window.removeEventListener('testPassive', noop, opts);
		} catch (e0) {}

		var wheelListenerOpts = supportsPassive ? { passive: false } : false;
		var scrollListenerOpts = supportsPassive ? { passive: true } : false;

		function clamp(v, min, max) {
			if (v < min) {
				return min;
			}
			if (v > max) {
				return max;
			}
			return v;
		}

		function maxScrollY() {
			var doc = document.documentElement;
			var body = document.body;
			var scrollH = Math.max(doc ? (doc.scrollHeight || 0) : 0, body ? (body.scrollHeight || 0) : 0);
			var vh = window.innerHeight || (doc ? doc.clientHeight : 0) || 0;
			return Math.max(0, scrollH - vh);
		}

		function getScrollY() {
			// window.pageYOffset is widely supported.
			return window.pageYOffset || (document.documentElement ? document.documentElement.scrollTop : 0) || 0;
		}

		function normalizeDeltaY(e) {
			var dy = e.deltaY || 0;
			// deltaMode: 0=pixel, 1=line, 2=page
			if (e.deltaMode === 1) {
				dy = dy * 16;
			} else if (e.deltaMode === 2) {
				dy = dy * (window.innerHeight || 800);
			}
			return dy;
		}

		function findScrollableAncestor(el) {
			// Leave scrollable containers untouched.
			var cur = el;
			while (cur && cur !== document.body && cur !== root) {
				if (cur.nodeType === 1) {
					var cs = null;
					try {
						cs = window.getComputedStyle(cur);
					} catch (e1) {}
					if (cs) {
						var oy = cs.overflowY;
						if ((oy === 'auto' || oy === 'scroll' || oy === 'overlay') && (cur.scrollHeight > cur.clientHeight + 1)) {
							return cur;
						}
					}
				}
				cur = cur.parentNode;
			}
			return null;
		}

		// Lerp/inertia state.
		var targetY = getScrollY();
		var currentY = targetY;
		var rafId = 0;
		var lastTs = 0;
		var isAnimating = false;

		// Base smoothing factor; adjusted by dt to keep a consistent feel.
		var baseLerp = 0.12;

		function schedule() {
			if (rafId) {
				caf(rafId);
			}
			rafId = raf(step);
		}

		function step(ts) {
			var now = ts || 0;
			var dt = now - (lastTs || now);
			lastTs = now;

			// Convert to a frame-rate independent lerp.
			var t = 1 - Math.pow(1 - baseLerp, dt / 16.67);
			if (t < 0.01) {
				t = 0.01;
			} else if (t > 0.30) {
				t = 0.30;
			}

			currentY = currentY + (targetY - currentY) * t;
			if (Math.abs(targetY - currentY) < 0.5) {
				currentY = targetY;
			}

			window.scrollTo(0, Math.round(currentY));

			if (currentY !== targetY) {
				schedule();
				return;
			}

			// Done.
			isAnimating = false;
			rafId = 0;
			lastTs = 0;
		}

		function onWheel(e) {
			// Allow default behavior for zooming and already-handled events.
			if (!e || e.defaultPrevented || e.ctrlKey || e.metaKey) {
				return;
			}

			// Do not touch nested scrollable containers.
			if (findScrollableAncestor(e.target)) {
				return;
			}

			var maxY = maxScrollY();
			if (maxY <= 0) {
				return;
			}

			var dy = normalizeDeltaY(e);
			if (!dy) {
				return;
			}

			e.preventDefault();

			// Sync when user scrolled without us (e.g. scrollbar drag).
			if (!isAnimating) {
				currentY = getScrollY();
				targetY = currentY;
			}

			targetY = clamp(targetY + dy, 0, maxY);
			if (!isAnimating) {
				isAnimating = true;
				schedule();
			}
		}

		function onScroll() {
			// If the user scrolls by other means, keep state in sync.
			if (!isAnimating) {
				var y = getScrollY();
				currentY = y;
				targetY = y;
			}
		}

		function onResize() {
			var maxY = maxScrollY();
			targetY = clamp(targetY, 0, maxY);
			currentY = clamp(currentY, 0, maxY);
		}

		window.addEventListener('wheel', onWheel, wheelListenerOpts);
		window.addEventListener('scroll', onScroll, scrollListenerOpts);
		window.addEventListener('resize', onResize, scrollListenerOpts);
	} catch (e) {
		// Fail closed: keep native scrolling.
	}
})();
