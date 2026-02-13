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
	var updateTimer = null;

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
			// Important: keep the sidebar's sticky boundary limited to #outer-container.
			// If the under-main container is placed inside #outer-container, the sticky
			// column stays "too long" (it remains sticky while the page is already in
			// the under-main section). Therefore insert it AFTER #outer-container.
			$outer.after($under);
		}
		syncUnderVisibility($under);
		return $under;
	}

	function restoreWidgets($column) {
		var $under = $('#' + UNDER_ID);
		if (!$under.length) {
			return;
		}

		// Keep original order: widgets were moved from bottom and prepended,
		// so under-main container already stores them in correct sequence.
		$under.children('div').appendTo($column);
	}

	function scheduleUpdate() {
		if (updateTimer) {
			clearTimeout(updateTimer);
		}
		updateTimer = setTimeout(updateLayout, 60);
	}

	function updateLayout() {
		var $outer = $('#outer-container');
		var $column = $('#column');
		var $main = $('#main,#cpmain').first();

		if (!$outer.length || !$column.length || !$main.length) {
			return;
		}

		// Always reset first, then decide what to do for current viewport.
		restoreWidgets($column);
		$('#' + UNDER_ID).remove();
		$outer.removeClass('stringendo-widgets-under-main-active stringendo-no-column');

		// Narrow layouts stack anyway.
		if (!isWide()) {
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
		if (colBottom <= mainBottom) {
			return;
		}

		var $under = ensureUnderContainer($outer);
		syncUnderVisibility($under);
		$outer.addClass('stringendo-widgets-under-main-active');

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
	}

	$(function () {
		// Initial attempt (DOM ready).
		scheduleUpdate();

		// A couple of delayed recalcs for late layout changes (fonts, injected widgets, etc.).
		setTimeout(scheduleUpdate, 250);
		setTimeout(scheduleUpdate, 1000);
	});

	// After images are loaded, main height can change significantly.
	$(window).on('load', scheduleUpdate);


	// When the column fade-in completed, reveal under-main widget grid (if present).
	$(window).on('stringendo:columnShown', function () {
		syncUnderVisibility($('#' + UNDER_ID));
	});
	// Re-evaluate when viewport changes.
	$(window).on('resize orientationchange', scheduleUpdate);

})(jQuery);
