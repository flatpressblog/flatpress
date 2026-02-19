/**
 * Stringendo – widgets under #main (wide screens)
 * Version: 1.40
 *
 * Goal:
 * - If viewport >= 960px AND the widget column (#column) would extend below #main,
 *   move only the *overflowing* widgets under #main in a responsive grid.
 * - Preserve widget order.
 * - If the right column is empty, expand #main to full width.
 *
 * Notes:
 * - We recalculate after window "load" (images loaded) to avoid moving widgets
 *   too early and leaving the right column empty on pages that later become taller.
 * - No external dependencies besides jQuery.
 */

(function ($) {
	'use strict';

	var MIN_WIDTH = 960;
	var UNDER_ID = 'stringendo-widgets-under-main';
	var BOTTOM_ID = 'columnbottom';
	var BOTTOM_ANCHOR_ID = 'stringendo-columnbottom-anchor';
	var ROOT_MOVED_CLASS = 'stringendo-columnbottom-moved';
	var updateTimer = null;
	var rafToken = null;
	var didFullLayout = false;
	var raf = window.requestAnimationFrame ? window.requestAnimationFrame.bind(window) : function (cb) { return window.setTimeout(cb, 16); };
	var caf = window.cancelAnimationFrame ? window.cancelAnimationFrame.bind(window) : function (id) { window.clearTimeout(id); };

	function viewportWidth() {
		return (window.innerWidth || document.documentElement.clientWidth || 0);
	}

	function isWide() {
		return viewportWidth() >= MIN_WIDTH;
	}

	function hasFadeIn() {
		var root = document.documentElement;
		return !!(root && root.classList && root.classList.contains('fp-fadein'));
	}

	function columnFadeDone() {
		var root = document.documentElement;
		if (root && root.classList && root.classList.contains('fp-fadein-column-done')) {
			return true;
		}
		return !!(window && window.__stringendoColumnShown);
	}

	function syncUnderVisibility($under) {
		if (!$under || !$under.length) {
			return;
		}
		if (!hasFadeIn()) {
			$under.removeClass('stringendo-under-hidden stringendo-under-show');
			return;
		}
		if (columnFadeDone()) {
			$under.removeClass('stringendo-under-hidden').addClass('stringendo-under-show');
		} else {
			$under.removeClass('stringendo-under-show').addClass('stringendo-under-hidden');
		}
	}


	function ensureUnderContainer($outer) {
		var $under = $('#' + UNDER_ID);
		if (!$under.length) {
			$under = $('<div/>', { id: UNDER_ID, 'class': 'stringendo-widgets-under-main' });
			/**
			 * Important: keep the sidebar's sticky boundary limited to #outer-container.
			 * If the under-main container is placed inside #outer-container, the sticky
			 * column stays "too long" (it remains sticky while the page is already in
			 * the under-main section). Therefore insert it AFTER #outer-container.
			 */
			$outer.after($under);
		}
		syncUnderVisibility($under);
		return $under;
	}

	function ensureColumnBottomAnchor($outer) {
		var $bottom = $('#' + BOTTOM_ID);
		if (!$bottom.length) {
			return $();
		}
		var $anchor = $('#' + BOTTOM_ANCHOR_ID);
		if (!$anchor.length) {
			$anchor = $('<div/>', {
				id: BOTTOM_ANCHOR_ID,
				'aria-hidden': 'true'
			}).css({
				display: 'none',
				height: 0,
				overflow: 'hidden'
			});
			// Preserve the original insertion point of #columnbottom inside #outer-container.
			$bottom.before($anchor);
		}
		return $anchor;
	}

	function restoreColumnBottom($outer) {
		var $bottom = $('#' + BOTTOM_ID);
		var $anchor = $('#' + BOTTOM_ANCHOR_ID);
		if ($bottom.length && $anchor.length && $bottom.parent().length && $bottom.parent()[0] !== $outer[0]) {
			$anchor.after($bottom);
		}
		try {
			document.documentElement.classList.remove(ROOT_MOVED_CLASS);
		} catch (e) {}
	}

	function restoreWidgets($column) {
		var $under = $('#' + UNDER_ID);
		if (!$under.length) {
			return;
		}

		/**
		 * Keep original order: widgets were moved from bottom and prepended,
		 * so under-main container already stores them in correct sequence.
		 */
		$under.children('div').appendTo($column);
	}

	function scheduleUpdate() {
		if (updateTimer) {
			clearTimeout(updateTimer);
			updateTimer = null;
		}
		if (rafToken) {
			caf(rafToken);
			rafToken = null;
		}
		// Use rAF to coalesce multiple triggers into a single layout pass.
		rafToken = raf(function () {
			rafToken = null;
			updateLayout(true);
		});
	}

	/**
	 * Full layout pass:
	 * - Restores widgets and columnbottom to the original state
	 * - Moves only overflowing widgets under #main
	 *
	 * Incremental pass:
	 * - Only moves additional widgets if the column still overflows
	 * - Never moves widgets back (stable, avoids "double" reveals)
	 */
	function updateLayout(isFull) {
		var $outer = $('#outer-container');
		var $column = $('#column');
		var $main = $('#main,#cpmain').first();

		if (!$outer.length || !$column.length || !$main.length) {
			return;
		}

		// Full pass resets first, then decides what to do for current viewport.
		// Incremental pass only moves additional widgets if needed.
		if (isFull) {
			restoreWidgets($column);
			restoreColumnBottom($outer);
			$('#' + UNDER_ID).remove();
			$outer.removeClass('stringendo-widgets-under-main-active stringendo-no-column');
		}

		// Narrow layouts stack anyway.
		if (!isWide()) {
			if (isFull) {
				// Ensure we clean up any previously created under-container.
				$('#' + UNDER_ID).remove();
				$outer.removeClass('stringendo-widgets-under-main-active stringendo-no-column');
			}
			return;
		}

		var $widgets = $column.children('div');
		if (!$widgets.length) {
			$outer.addClass('stringendo-no-column');
			return;
		}

		var mainOffset = $main.offset();
		var colOffset = $column.offset();
		if (!mainOffset || !colOffset) {
			return;
		}

		var mainBottom = mainOffset.top + ($main.outerHeight(true) || 0);
		var colTop = colOffset.top;
		var colBottom = colTop + ($column.outerHeight(true) || 0);

		// If the column ends above (or at) the main bottom, keep everything in the column.
		// In incremental mode, keep already moved widgets under #main (stable, no flicker).
		if (colBottom <= mainBottom) {
			didFullLayout = didFullLayout || !!isFull;
			return;
		}

		var $under = ensureUnderContainer($outer);
		syncUnderVisibility($under);
		$outer.addClass('stringendo-widgets-under-main-active');

		/**
		 * If a bottom widget row exists (#columnbottom), ensure it stays *below* the
		 * under-main widget grid. This keeps sidebar widgets "under #main" and "above #columnbottom".
		 */
		var $bottom = $('#' + BOTTOM_ID);
		// widgetsbottom.tpl always renders the wrapper; only move it if it actually contains widgets.
		if ($bottom.length && $bottom.children('div').length) {
			ensureColumnBottomAnchor($outer);
			$under.after($bottom);
			try {
				document.documentElement.classList.add(ROOT_MOVED_CLASS);
			} catch (e0) {}
		}

		// Move bottom widgets under #main until the column fits within main's height span.
		var safety = 0;
		while ($column.children('div').length && safety < 200) {
			colBottom = colTop + ($column.outerHeight(true) || 0);
			if (colBottom <= mainBottom) {
				break;
			}

			$column.children('div').last().prependTo($under);
			safety++;
		}

		// Hide the empty right column if everything was moved.
		if (!$column.children('div').length) {
			$outer.addClass('stringendo-no-column');
		}

		didFullLayout = didFullLayout || !!isFull;
	}

	// Run as early as possible (defer scripts run after parsing, before DOMContentLoaded).
	scheduleUpdate();

	$(function () {
		// Safety: run again on DOM ready.
		scheduleUpdate();
		// If fade-in is active, keep late recalcs minimal to avoid visible "double" reveals.
		if (hasFadeIn()) {
			setTimeout(scheduleUpdate, 180);
		}
	});

	// After images are loaded, main height can change. Use incremental pass to avoid flicker.
	$(window).on('load', function () {
		if (!didFullLayout) {
			updateLayout(true);
			return;
		}
		updateLayout(false);
	});


	// When the column fade-in completed, reveal under-main widget grid (if present).
	$(window).on('stringendo:columnShown', function () {
		syncUnderVisibility($('#' + UNDER_ID));
	});
	// Re-evaluate when viewport changes.
	$(window).on('resize orientationchange', scheduleUpdate);

})(jQuery);
