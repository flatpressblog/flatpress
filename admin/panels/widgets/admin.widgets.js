/*
 * Flatpress widget js admin
 * Based on original flatpress' code
 * Require jQuery and jQuery UI (Core, Draggable, Droppable and Effects Core)
 * Coded by Piero VDFN <vogliadifarniente@gmail.com>
 * Released under GNU GPL v2
 */

var FlatPress = {
wclass: function() {
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
},

winstancedrag: function() {
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
},
wplaceholder: function() {
	$('.widget-placeholder').droppable({
		'accept' : '.widget-class, .widget-dragger, .widget-instance',
		'over' : function(event, ui) {
			$(this).animate({'background-color' : '#78ba91'})
		},
		'out' : function(event, ui) {
			$(this).animate({'background-color' : '#fff'})
		},
		'drop' : function(event, ui) {
			var parent=ui.draggable.parent();
			var where=$(this).parent().attr('id').split('-')[1];
			var replace = null;
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
			FlatPress.wreload();
		}
	});
},
winstancedrop: function() {
	$('.widget-instance').droppable({	
		'accept' : '.widget-class, .widget-dragger, .widget-instance',
		'over' : function(event, ui) {
			$(this).animate({'background-color' : '#78ba91'})
		},
		'out' : function(event, ui) {
			$(this).animate({'background-color' : '#fff'})
		},
		'drop' : function(event, ui) {
			var parent=ui.draggable.parent();
			var where=$(this).parent().attr('id').split('-')[1];
			var replace = null;
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
			FlatPress.wreload();
		}
	});
},
wtrash: function() {
	$('#widget-trashcan').droppable({
		'accept' : '.widget-instance',
		'over' : function(event, ui) {
			$(this).animate({'background-color' : '#faa'})
		},
		'out' : function(event, ui) {
			$(this).animate({'background-color' : '#a22'})
		},
		'drop' : function(event, ui) {
			var parent=ui.draggable.parent();
			var draggable = $(ui.draggable);

			// we can't remove() draggable here, because of a bug with jquery UI + IE8
			// we'll defer it
			draggable.fadeOut();
			$('.widget-dragger').remove();

			// last element has not been removed, 
			// so there is still one in the list, soon to be deleted '
			// (parent.children().lenght==1)
			if(parent.children().length<2) {
				parent.append('<li class="widget-placeholder">Drop here</li>');
			}
			$(this).animate({'background-color' : '#a22'});

			// deferred removal takes place here
			setTimeout(function() {
				draggable.remove();
			});

			FlatPress.wreload();
		}
	});
},
wreload: function(){
	this.wclass();
	this.winstancedrag();
	this.wplaceholder();
	this.winstancedrop();
	//wtrash();
}
}
//$(document).ready(wreload);
FlatPress.wreload();FlatPress.wtrash();

