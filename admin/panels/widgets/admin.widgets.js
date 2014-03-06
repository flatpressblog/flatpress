/*
 * FlatPress widget js admin
 * Based on original FlatPress' code
 * Require jQuery and jQuery UI (Core, Draggable, Droppable and Effects Core)
 * Coded by Piero VDFN <vogliadifarniente@gmail.com>
 * Re-Coded by liquibyte <liquibyte@gmail.com>
 *     Colors weren't resetting on mouseout and position:absolute didn't work.
 *     Position:absolute changed to position:fixed.  I also changed the
 *     hardcoded values to a variable that is stored and recalled so that the
 *     users stylesheet is used for styling.  Colors were hardcoded so I fixed
 *     this to be styled from the users admin.css.  Available widgets now
 *     accepts drag and drop from installed widgets to remove.
 * Released under GNU GPL v2
 */
var FlatPress = {
	winstancedrag : function() {
		$('.widget-class').draggable({
			'scroll' : true,
			'helper' : function(event) {
				return $(this).clone().appendTo('body').removeClass('widget-class').css({
					'position': 'fixed',
					'cursor' : 'move',
					'list-style-type' : 'none',
					'margin' : '0',
					'padding' : '0',
					'width' : $(this).width(),
					'height' : $(this).height()
					})
				.addClass('widget-available');
			}
		});
		$('.widget-instance').draggable({
			'scroll' : true,
			'helper' : function(event) {
				return $(this).clone().appendTo('body').removeClass('widget-instance').css({
					'position': 'fixed',
					'cursor' : 'move',
					'list-style-type' : 'none',
					'width' : $(this).width(),
					'height' : $(this).height()
					})
				.addClass('widget-installed');
			}
		});
	},
	wplaceholder : function() {
		$('.widget-placeholder').droppable({
			'accept' : '.widget-class, .widget-instance',
			'activeClass' : 'ui-state-highlight',
			'over' : function(event, ui) {
				$(this).effect("highlight", { 'color' : $('.widget-installed, .widget-available').css('background-color') }, 1000);
			},
			'drop' : function(event, ui) {
				var parent = ui.draggable.parent();
				var where = $(this).parent().attr('id').split('-')[1];
				var replace = null;
				if (ui.draggable.hasClass('widget-instance')) {
					replace = ui.draggable;
				}
				else {
					replace = $('<li class="' + ui.draggable.attr('class') + '"></li>').append(ui.draggable.children().clone());
					replace.removeClass('widget-class').addClass('widget-instance');
				}
				replace.children('input').attr('name', 'widgets[' + where + '][]');
				$(this).replaceWith(replace);

				if (parent.children().length < 1) {
					parent.append('<li class="widget-placeholder">Drop here</li>');
				}
				FlatPress.wreload();
			}
		});
	},
	winstancedrop : function() {
		$('.widget-instance').droppable({
			'accept' : '.widget-class, .widget-instance',
			'activeClass' : 'ui-state-highlight',
			'over' : function(event, ui) {
				$(this).effect("highlight", { 'color' : $('.widget-available, .widget-installed').css('background-color') }, 1000);
			},
			'drop' : function(event, ui) {
				var parent = ui.draggable.parent();
				var where = $(this).parent().attr('id').split('-')[1];
				var replace = null;
				if (ui.draggable.hasClass('widget-instance')) {
					replace = ui.draggable;
				}
				else {
					replace = $('<li class="' + ui.draggable.attr('class') + '"></li>').append(ui.draggable.children().clone());
					replace.removeClass('widget-class').addClass('widget-instance');
				}
				replace.children('input').attr('name', 'widgets[' + where + '][]');
				$(this).after(replace);
				if (parent.children().length < 1) {
					parent.append('<li class="widget-placeholder">Drop here</li>');
				}
				FlatPress.wreload();
			}
		});
	},
	wtrash : function() {
		$('#widget-trashcan').droppable({
			'accept' : '.widget-instance',
			'activeClass' : 'ui-state-highlight',
			'over' : function(event, ui) {
				$(this).fadeTo('slow', 0.2).fadeTo('slow', 1.0);
			},
			'drop' : function(event, ui) {
				var parent = ui.draggable.parent();
				var draggable = $(ui.draggable);
				// we can't remove() draggable here, because of a bug with jquery UI + IE8
				// we'll defer it
				$('.widget-installed').remove();
				// last element has not been removed, 
				// so there is still one in the list, soon to be deleted '
				if(parent.children().length < 2) {
					parent.append('<li class="widget-placeholder">Drop here</li>');
				}
				// deferred removal takes place here
				setTimeout(function() {
					draggable.remove();
				});
				FlatPress.wreload();
			}
		});
		$('.widget-class').droppable({
			'accept' : '.widget-instance',
			'activeClass' : 'ui-state-highlight',
			'over' : function(event, ui) {
				$(this).effect("highlight", { 'color' : $('#widget-trashcan').css('background-color') }, 1000);
			},
			'drop' : function(event, ui) {
				var parent = ui.draggable.parent();
				var draggable = $(ui.draggable);
				// we can't remove() draggable here, because of a bug with jquery UI + IE8
				// we'll defer it
				$('.widget-installed').remove();
				// last element has not been removed, 
				// so there is still one in the list, soon to be deleted
				if(parent.children().length < 2) {
					parent.append('<li class="widget-placeholder">Drop here</li>');
				}
				// deferred removal takes place here
				setTimeout(function() {
					draggable.remove();
				});
				FlatPress.wreload();
			}
		});

	},
	wreload : function(){
		this.winstancedrag();
		this.winstancedrop();
		this.wplaceholder();
	}
}
FlatPress.wreload();FlatPress.wtrash();