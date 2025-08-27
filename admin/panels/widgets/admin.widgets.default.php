<?php
function admin_widgets_head() {
	global $fp_config;
	$blogbase = BLOG_BASEURL;
	$random_hex = RANDOM_HEX;
	$css = utils_asset_ver($blogbase . 'admin/panels/widgets/admin.widgets.css', SYSTEM_VER);

	echo '
		<!-- BOF Admin Widgets CSS -->
		<link rel="stylesheet" type="text/css" href="' . $css . '">
		<!-- EOF Admin Widgets CSS -->
	';
}

function admin_widgets_footer() {
	global $lang;
	$random_hex = RANDOM_HEX;
	echo '
		<script nonce="' . $random_hex . '">
			/**
			 * Touch/Pointer - Mouse events for jQuery UI
			 */
			(function(){
				if(!window.jQuery || !jQuery.ui || !jQuery.ui.mouse) {
					return;
				}
				var mouseProto = jQuery.ui.mouse.prototype;
				var _init = mouseProto._mouseInit, _destroy = mouseProto._mouseDestroy;
				var hasAnyTouch = ((\'ontouchstart\' in window) || (navigator.maxTouchPoints>0));
				if(!hasAnyTouch) {
					return;
				}
				var supportsPointer = !!window.PointerEvent && navigator.maxTouchPoints>0;
				var START = supportsPointer ? \'pointerdown\' : \'touchstart\';
				var MOVE = supportsPointer ? \'pointermove\' : \'touchmove\';
				var END = supportsPointer ? \'pointerup pointercancel\' : \'touchend touchcancel\';
				function getPoint(e) {
					if(supportsPointer) {
						return e;
					}
					var t = e.changedTouches && e.changedTouches[0];
					return t || null;
				}
				function bridge(ev){
					var e = ev.originalEvent || ev, p = getPoint(e);
					if(!p) {
						return;
					}
					var type = (e.type === START) ? \'mousedown\' : (e.type === MOVE) ? \'mousemove\' : \'mouseup\';
					if(e.type === MOVE && typeof ev.preventDefault === \'function\') {
						ev.preventDefault();
					}
					var sim = new MouseEvent(type, { bubbles: true, cancelable: true, view: window, screenX: p.screenX, screenY: p.screenY, clientX: p.clientX, clientY: p.clientY });
					e.target.dispatchEvent(sim);
				}
				mouseProto._mouseInit = function(){
					this.element.on(START+\'.ui-mouse \' + MOVE + \'.ui-mouse \' + END.split(\' \').join(\'.ui-mouse \'), bridge);
					return _init.call(this);
				};
				mouseProto._mouseDestroy = function(){
					this.element.off(START + \'.ui-mouse \' + MOVE + \'.ui-mouse \' + END.split(\' \').join(\'.ui-mouse \'), bridge);
					return _destroy.call(this);
				};
			})();

			/**
			 * FlatPress widget js admin
			 */
			var FlatPress = {
				_initDraggableWithDelay: function(selector, options, delayMs) {
					var opts = $.extend(true, {}, options);
					var userStart = opts.start;
					opts.start = function (event, ui) {
						var $el = $(this);
						if (!$el.data(\'fp-drag-allowed\')) {
							event.preventDefault();
							return false;
						}
						if (typeof userStart === \'function\') {
							return userStart.call(this, event, ui);
						}
					};
					if (typeof opts.distance === \'undefined\') {
						opts.distance = 4;
					}
					var $els = $(selector).draggable(opts);
					$els.on(\'mousedown.fpDelay touchstart.fpDelay\', function () {
							var $el = $(this);
							var oldT = $el.data(\'fp-drag-timer\');
							if (oldT) {
								clearTimeout(oldT);
							}
							$el.data(\'fp-drag-allowed\', false);
							var t = setTimeout(function () {
								$el.data(\'fp-drag-allowed\', true);
							}, delayMs || 100);
							$el.data(\'fp-drag-timer\', t);
						})
						.on(\'mouseup.fpDelay mouseleave.fpDelay touchend.fpDelay touchcancel.fpDelay dragstop.fpDelay\', function () {
							var $el = $(this);
							var t = $el.data(\'fp-drag-timer\');
							if (t) {
								clearTimeout(t);
							}
							$el.removeData(\'fp-drag-timer\');
							$el.data(\'fp-drag-allowed\', false);
						});
				},
				winstancedrag: function() {
					var commonOpts = {
						\'scroll\': true,
						\'cancel\': \'input, textarea, button, select, option, a\',
						\'distance\': 4,
						\'helper\': function(event) {
							return $(this).clone().appendTo(\'body\').removeClass(\'widget-class\').css({
								\'position\': \'fixed\',
								\'cursor\': \'move\',
								\'list-style-type\': \'none\',
								\'margin\': \'0\',
								\'padding\': \'0\',
								\'width\': $(this).width(),
								\'height\': $(this).height()
							}).addClass(\'widget-available\');
						},
						\'start\': function() {
							$(\'html, body\').addClass(\'dragging-widgets\');
							$(this).attr(\'aria-grabbed\', \'true\');
						},
						\'stop\': function() {
							$(\'html, body\').removeClass(\'dragging-widgets\');
							$(this).attr(\'aria-grabbed\', \'false\');
						}
					};
					var instanceOpts = {
						\'scroll\': true,
						\'cancel\': \'input, textarea, button, select, option, a\',
						\'distance\': 4,
						\'helper\': function(event) {
							return $(this).clone().appendTo(\'body\').removeClass(\'widget-instance\').css({
								\'position\': \'fixed\',
								\'cursor\': \'move\',
								\'list-style-type\': \'none\',
								\'width\': $(this).width(),
								\'height\': $(this).height()
							}).addClass(\'widget-installed\');
						},
						\'start\': function() {
							$(\'html, body\').addClass(\'dragging-widgets\');
							$(this).attr(\'aria-grabbed\', \'true\');
						},
						\'stop\': function() {
							$(\'html, body\').removeClass(\'dragging-widgets\');
							$(this).attr(\'aria-grabbed\', \'false\');
						}
					};
					this._initDraggableWithDelay(\'.widget-class\', commonOpts, 100);
					this._initDraggableWithDelay(\'.widget-instance\', instanceOpts, 100);
				},
				wplaceholder: function() {
					$(\'.widget-placeholder\').droppable({
						\'accept\': \'.widget-class, .widget-instance\',
						\'tolerance\': \'pointer\',
						\'activeClass\': \'ui-state-highlight\',
						\'over\': function(event, ui) {
							$(this).effect("highlight", { \'color\' : $(\'.widget-installed, .widget-available\').css(\'background-color\') }, 1000);
						},
						\'drop\': function(event, ui) {
							var parent = ui.draggable.parent();
							var where = $(this).parent().attr(\'id\').split(\'-\')[1];
							var replace = null;
							if (ui.draggable.hasClass(\'widget-instance\')) {
								replace = ui.draggable;
							} else {
								replace = $(\'<li class="\' + ui.draggable.attr(\'class\') + \'"></li>\').append(ui.draggable.children().clone());
								replace.removeClass(\'widget-class\').addClass(\'widget-instance\');
							}
							replace.children(\'input\').attr(\'name\', \'widgets[\' + where + \'][]\');
							$(this).replaceWith(replace);
							if (parent.children().length < 1) {
								parent.append(\'<li class="widget-placeholder">' . $lang ['admin'] ['widgets'] ['default'] ['drop_here'] . '</li>\');
							}
							FlatPress.wreload();
						}
					});
				},
				winstancedrop: function() {
					$(\'.widget-instance\').droppable({
						\'accept\': \'.widget-class, .widget-instance\',
						\'tolerance\': \'pointer\',
						\'activeClass\': \'ui-state-highlight\',
						\'over\': function(event, ui) {
							$(this).effect("highlight", { \'color\': $(\'.widget-available, .widget-installed\').css(\'background-color\') }, 1000);
						},
						\'drop\': function(event, ui) {
							var parent = ui.draggable.parent();
							var where = $(this).parent().attr(\'id\').split(\'-\')[1];
							var replace = null;
							if (ui.draggable.hasClass(\'widget-instance\')) {
								replace = ui.draggable;
							} else {
								replace = $(\'<li class="\' + ui.draggable.attr(\'class\') + \'"></li>\').append(ui.draggable.children().clone());
								replace.removeClass(\'widget-class\').addClass(\'widget-instance\');
							}
							replace.children(\'input\').attr(\'name\', \'widgets[\' + where + \'][]\');
							$(this).after(replace);
							if (parent.children().length < 1) {
								parent.append(\'<li class="widget-placeholder">' . $lang ['admin'] ['widgets'] ['default'] ['drop_here'] . '</li>\');
							}
							FlatPress.wreload();
						}
					});
				},
				wtrash: function() {
					$(\'#widget-trashcan\').droppable({
						\'accept\': \'.widget-instance\',
						\'tolerance\': \'pointer\',
						\'activeClass\': \'ui-state-highlight\',
						\'over\': function(event, ui) {
							$(this).fadeTo(\'slow\', 0.2).fadeTo(\'slow\', 1.0);
						},
						\'drop\': function(event, ui) {
							var parent = ui.draggable.parent();
							var draggable = $(ui.draggable);
							$(\'.widget-installed\').remove();
							if(parent.children().length < 2) {
								parent.append(\'<li class="widget-placeholder">' . $lang ['admin'] ['widgets'] ['default'] ['drop_here'] . '</li>\');
							}
							setTimeout(function() {
								draggable.remove();
							});
							FlatPress.wreload();
						}
					});
					$(\'.widget-class\').droppable({
						\'accept\': \'.widget-instance\',
						\'activeClass\': \'ui-state-highlight\',
						\'tolerance\': \'pointer\',
						\'over\': function(event, ui) {
							$(this).effect("highlight", { \'color\': $(\'#widget-trashcan\').css(\'background-color\') }, 1000);
						},
						\'drop\': function(event, ui) {
							var parent = ui.draggable.parent();
							var draggable = $(ui.draggable);
							$(\'.widget-installed\').remove();
							if(parent.children().length < 2) {
								parent.append(\'<li class="widget-placeholder">' . $lang ['admin'] ['widgets'] ['default'] ['drop_here'] . '</li>\');
							}
							setTimeout(function() {
								draggable.remove();
							});
							FlatPress.wreload();
						}
					});
				},
				wreload: function(){
					this.winstancedrag();
					this.winstancedrop();
					this.wplaceholder();
				}
			}
			FlatPress.wreload();
			FlatPress.wtrash();
		</script>';
}

class admin_widgets_default extends AdminPanelAction {

	// var $validators = array(array('content', 'content', 'notEmpty', false, false));
	var $events = array(
		'save'
	);

	function get_widget_lists($wlist, $wpos, &$widget_list, $registered_w, $add_empties) {
		if (!isset($wlist [$wpos])) {
			return;
		}

		$widget_list [$wpos] = array();

		foreach ($wlist [$wpos] as $idx => $wdg) {

			$widget_list [$wpos] [$idx] = array();

			$newid = $wdg;

			$params = '';

			$widget_list [$wpos] [$idx] ['id'] = $newid;

			if (isset($registered_w [$newid])) {
				$thiswdg = $registered_w [$newid];

				$widget_list [$wpos] [$idx] ['name'] = $thiswdg ['name'];

				if ($thiswdg ['nparams'] > 0) {
					$widget_list [$wpos] [$idx] ['params'] = $params;
				}

				/**
				 * here should go the check for
				 * limited parameters: parameters limited to a
				 * particular set would mean using a <select> control
				 * in the template
				 *
				 */
			} else {

				global $lang;

				$widget_list [$wpos] [$idx] ['name'] = $newid;
				$widget_list [$wpos] [$idx] ['class'] = 'errors';

				$errs = sprintf($lang ['admin'] ['widgets'] ['errors'] ['generic'], $newid);
				$this->smarty->append('warnings', $errs);
			}
		}

		if (!$widget_list [$wpos] && !$add_empties) {
			unset($widget_list [$wpos]);
		}
	}

	function main() {

		lang_load('admin.widgets');

		add_action('admin_head', 'admin_widgets_head');
		add_action('wp_footer', 'admin_widgets_footer');

		// $this->smarty->assign('warnings', admin_widgets_checkall());
		global $fp_widgets;

		$registered_w = get_registered_widgets();
		$registered_ws = get_registered_widgetsets(null);
		$this->smarty->assign('fp_registered_widgets', $registered_w);

		$wlist = $fp_widgets->getList();
		$widget_list = array();

		foreach ($registered_ws as $wpos) {

			$widget_list [$wpos] = array();

			$this->get_widget_lists($wlist, $wpos, $widget_list, $registered_w, true);

			unset($wlist [$wpos]);
		}

		$oldwidget_list = array();
		foreach ($wlist as $wpos => $c) {
			$this->get_widget_lists($wlist, $wpos, $oldwidget_list, $registered_w, false);
		}

		$this->smarty->assign('widgetlist', $widget_list);
		$this->smarty->assign('oldwidgetlist', $oldwidget_list);

		$conf = io_load_file(CONFIG_DIR . 'widgets.conf.php');

		$this->smarty->assign('pluginconf', $conf);

		return 0;
	}

	function onsave() {
		$fp_widgets = isset($_POST ['widgets']) ? $_POST ['widgets'] : array();
		$success = system_save(CONFIG_DIR . 'widgets.conf.php', compact('fp_widgets'));

		$this->smarty->assign('success', ($success) ? 1 : -1);

		return PANEL_REDIRECT_CURRENT;
	}

}
?>
