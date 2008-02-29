/*
 * DISCLAIMER
 *
 * a lot of this is just spaghetti (pizza and mandolino) code,
 * it's my first attempt at JS, and I know it's just a mess...
 *
 * if you want, you can clean it up and then send it back to me :P
 * otherwise I'll just do it myself once I'm done with the rest ;)
 *
 */
		
		/*
		new Drag.Move(
			$('available-widgets'), 
				{'handle': $$('#available-widgets h3')[0]}
		);
		*/
	
		Drag.MultiDrop = Drag.Move.extend({
			drag:       function(event) {
				this.droppables = Widgets.droppables;
				this.parent(event);
				return this;	
			},
			checkAgainst: function(el) {
				// console.log(this);
				if (this.element == el)
					return false;

				return this.parent(el);
			}
		});

		var fx = [];
	
		var wtrash = $('widget-trashcan');
		var mydropp = $$('#admin-widgetset-list li.admin-widgetset li.widget-instance');
		var avail = $$('li.widget-class');

		var Widgets = {
			'droppables' : [wtrash].extend(mydropp),
			'available'  : avail,
			'inputPlaceHld': '<insert text here>',
			'inputChange': function() {
				this_input = this;
				
				input = this_input.getParent().getChildren().filterByClass('widget-id')[0];
				
				regex = /^([^:]+)/;
				id = regex.exec(input.value);
				if (this_input.value.trim() == ''){
					this_input.value=Widgets.inputPlaceHld;
					return;
				}
				input.set({
					'value' : id[1] + ':' + this_input.value 
				});
							
			},
			'doDrag': function(drag) {
			
				var input = drag.getChildren().filterByClass('textinput')[0];
				
				if (input) {
					input.addEvent('blur', Widgets.inputChange);
					
				}
	
				drag.addEvents({

					'mousedown'   : function(e, el) {
						
						e = new Event(e).stop();
						// console.log($type(e.target));
						
						if (e.target.getTag() == 'input'){
							if (e.target.getValue() == Widgets.inputPlaceHld)
								e.target.value = '';
							
							e.target.focus();
							e.target.select();
							
							return;
						}
						
						ghost = this.clone();
						ghost.ghostParent = this;
						ghost.setStyles(drag.getCoordinates());
						ghost.setStyles({
							'position': 'absolute',
							'opacity' : 0.7,
							'background-color' : '#b31'
							});
						ghost.inject($$('body')[0]);

						ghost.addClass('widget-dragger');

						dragger = new Drag.MultiDrop(ghost, {
						
							'onStart' : function(el) {
								Widgets.scroller.start();
							}
						});


						ghost.addEvent('emptydrop', function(el) {
							this.remove();
							Widgets.scroller.stop();
						});

						dragger.start(e);
					}
				
				});
			},
			'doDrop' : function(drop, index){
				drop.fx = drop.effects({'transition': Fx.Transitions.linear });
				drop.addEvents({
					'over': function(el, obj){
						this.setStyle('background-color', '#78ba91');
					},
					'leave': function(el, obj){
						this.setStyle('background-color', '#fff');
					},
					'drop': function(el, obj){
						original = el.ghostParent;
						el.remove();
						
						dropper = drop;
						
						if (original.hasClass('widget-class')) {

							var newclone = original.clone();
							newclone.removeClass('widget-class');
							newclone.addClass('widget-instance');


							Widgets.doDrag(newclone);

							Widgets.droppables.include(newclone);
							newindex = Widgets.droppables.indexOf(newclone)	
							Widgets.doDrop(newclone, newindex);
							newclone.injectAfter(drop);
							
							dropped = newclone;
						
							
							parentid = drop.getParent().id;
							 widgetsetid = /^widgetsetid-(.*)$/.exec(parentid);
							
							input = dropped.getChildren().filterByClass('widget-id')[0];
							input.set({
								'name' : 'widgets[' + widgetsetid[1] + '][]'
							});
							
							
							txtinput = dropped.getChildren().filterByClass('textinput')[0];
							if (txtinput){
								txtinput.set({
									// 'name' : 'widgets[' + widgetsetid[1] + '][]'
									'type': 'text',
									'value': Widgets.inputPlaceHld
								});
							}
							
							
						} else {

							newindex = Widgets.droppables.indexOf(original);
							
							par = original.getParent();
							original.injectAfter(drop);
							dropped = original;
							
							newparent = dropped.getParent();
							parentid = newparent.id;
							
							input = dropped.getChildren().filterByClass('widget-id')[0];
							
							widgetsetid = /^widgetsetid-(.*)$/.exec(parentid);
							input.set({
								'name' : 'widgets[' + widgetsetid[1] + '][]' 
							}); 
														
							if (par.getChildren().length <= 0) {
							
								newe = new Element('li', {
										'class' : 'widget-placeholder'
								});
								
								
								newe.setText('Drop here');
								
								Widgets.droppables.include(newe);
								// newindex = Widgets.droppables.indexOf(newe);	
								Widgets.doDrop(newe, newindex);
							
								newe.inject(par);
								
								// dropper = newe;
								
							};
							
						}
						
					
						if (drop.hasClass('widget-placeholder')) {
							Widgets.droppables.remove(drop);
							drop.remove();
						}


						dropped.fx.start({
							'background-color' : ['#78ba91', '#fff']
						});

						// dropped
						
						dropper.fx.start({
							'background-color' : ['#b31', '#fff']
						});
						
						Widgets.scroller.stop();
						
					}
				});
			},
			'doTrash'  :  function(drop, index){
				drop.fx = drop.effects({'transition': Fx.Transitions.linear });
				drop.addEvents({
					'over': function(el, obj){
						if (el.ghostParent.hasClass('widget-instance'))
							this.setStyle('background-color', '#faa');
					},
					'leave': function(el, obj){
						if (el.ghostParent.hasClass('widget-instance'))
							this.setStyle('background-color', '#a22');
					},
					'drop': function(el, obj){
					
						Widgets.scroller.stop();
						original = el.ghostParent;
						el.remove();
						
						if (original.hasClass('widget-class')) {
							return;
						} else {
						
							
							dropper = drop;
						
							

							newindex = Widgets.droppables.indexOf(original);
							
							par = original.getParent();
							original.remove();
														
							if (par.getChildren().length <= 0) {
							
								newe = new Element('li', {
										'class' : 'widget-placeholder'
								});
								
								
								newe.setText('Drop here');
								
								Widgets.droppables.include(newe);
								// newindex = Widgets.droppables.indexOf(newe);	
								Widgets.doDrop(newe, newindex);
							
								newe.inject(par);
								
								// dropper = newe;
								
							};
							
						}

						dropper.fx.start({
							'background-color' : ['#faa', '#a22']
						});
						
						
					}
				});
			},
			'scroller' : new Scroller(window, {'velocity': 0.5}),
			'palette'  : $('available-widgets')
		};
		
		
		mydropp.each(Widgets.doDrop);
		mydropp.each(Widgets.doDrag);
		Widgets.doTrash(wtrash);
		
		var placeholders = $$('li.widget-placeholder');
		placeholders.each(Widgets.doDrop);
		
		Widgets.droppables.extend(placeholders);
		
		avail.each(Widgets.doDrag);
		
		
		/*
		Widgets.palette.fx =
		Widgets.palette.effects({'transition': Fx.Transitions.linear});
		Widgets.palette.startTop = Widgets.palette.getTop();
		
		Widgets.palette.setStyles(Widgets.palette.getCoordinates());
		Widgets.palette.setStyle('position', 'absolute');
		
		Widgets.palette.injectInside(document.body);
		
		
		window.addEvent('scroll',	
			function() {
				top = window.getScrollTop();
				if (top > Widgets.palette.startTop)
					Widgets.palette.setStyle('top', top);
			}
		);

		*/

