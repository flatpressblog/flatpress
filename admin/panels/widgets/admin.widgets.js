/*
 * Flatpress widget js admin
 * Based on original flatpress' code
 * Require jQuery and jQuery UI (Core, Draggable, Droppable and Effects Core)
 * Coded by Piero VDFN <vogliadifarniente@gmail.com>
 * Released under GNU GPL v2
 */
function wclass() {
	$('.widget-class').draggable({
		'scroll' : true,
		'helper':function(event) {
			return $(this).clone().appendTo('body').removeClass('widget-class').css({
					'position': 'absolute',
					'opacity' : 0.7,
					'background-color' : '#b31',
					'top' : event.pageY-10,
					'left' : event.pageX-($(this).width()/4),
					'list-style-type' : 'none',
					'width' : $(this).width()
			}).addClass('widget-dragger');
		}
	});
}
function winstancedrag() {
	$('.widget-instance').draggable({
		'scroll' : true,
		'helper':function(event) {
			return $(this).clone().appendTo('body').removeClass('widget-class').css({
					'position': 'absolute',
					'opacity' : 0.7,
					'background-color' : '#b31',
					'list-style-type' : 'none',
					'width' : $(this).width()
			}).addClass('widget-dragger');
		}
	});
}
function wplaceholder() {
	$('.widget-placeholder').droppable({
		'accept' : '.widget-class, .widget-dragger, .widget-instance',
		'over' : function(event, ui) {
			$(this).animate({'background-color' : '#78ba91'})
		},
		'out' : function(event, ui) {
			$(this).animate({'background-color' : '#fff'})
		},
		'drop' : function(event, ui) {
			parent=ui.draggable.parent();
			where=$(this).parent().attr('id').split('-')[1];
			if(ui.draggable.hasClass('widget-instance')) {
				replace=ui.draggable;
			} else {
				replace=$('<li class="'+ui.draggable.attr('class')+'"></li>').append(ui.draggable.children().clone());
				replace.removeClass('widget-class').addClass('widget-instance');
			}
			replace.children('input').attr('name', 'widgets['+where+'][]');
			$(this).replaceWith(replace);
			if(parent.children().length<1) {
				parent.append('<li class="widget-placeholder">Drop here</li>');
			}
			wreload();
		}
	});
}
function winstancedrop() {
	$('.widget-instance').droppable({	
		'accept' : '.widget-class, .widget-dragger, .widget-instance',
		'over' : function(event, ui) {
			$(this).animate({'background-color' : '#78ba91'})
		},
		'out' : function(event, ui) {
			$(this).animate({'background-color' : '#fff'})
		},
		'drop' : function(event, ui) {
			parent=ui.draggable.parent();
			where=$(this).parent().attr('id').split('-')[1];
			if(ui.draggable.hasClass('widget-instance')) {
				replace=ui.draggable;
			} else {
				replace=$('<li class="'+ui.draggable.attr('class')+'"></li>').append(ui.draggable.children().clone());
				replace.removeClass('widget-class').addClass('widget-instance');
			}
			replace.children('input').attr('name', 'widgets['+where+'][]');
			$(this).after(replace);
			$(this).animate({'background-color' : '#fff'});
			if(parent.children().length<1) {
				parent.append('<li class="widget-placeholder">Drop here</li>');
			}
			wreload();
		}
	});
}
function wtrash() {
	$('#widget-trashcan').droppable({
		'accept' : '.widget-instance',
		'over' : function(event, ui) {
			$(this).animate({'background-color' : '#faa'})
		},
		'out' : function(event, ui) {
			$(this).animate({'background-color' : '#a22'})
		},
		'drop' : function(event, ui) {
			parent=ui.draggable.parent();
			$(ui.draggable).fadeOut().remove();;
			$('.widget-dragger').remove();
			if(parent.children().length<1) {
				parent.append('<li class="widget-placeholder">Drop here</li>');
			}
			$(this).animate({'background-color' : '#a22'});
			wreload();
		}
	});
}
function wreload(){
	wclass();
	winstancedrag();
	wplaceholder();
	winstancedrop();
	wtrash();
}
$(document).ready(wreload);