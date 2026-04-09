if ('undefined' == typeof bbtCustomFunctions) {
	var bbtCustomFunctions = {};
}
if (undefined != typeof window.jQuery) {
	(function($) {

		var vdfnTag = {
			/**
			 * This is the tag input
			 *
			 * @var string
			 */
			'input' : '#taginput',

			/**
			 * This is the place where we put tags
			 *
			 * @var string
			 */
			'tagplace' : '#tagplace',

			/**
			 * This is the selector for the labels of tags.
			 *
			 * @var string
			 */
			'labels' : '#tagplace span',

			/**
			 * This is the textarea of the content
			 *
			 * @var string
			 */
			'textarea' : 'textarea#content',

			/**
			 * This is the list of current tags.
			 * It's used to not duplicate tags.
			 *
			 * @var array
			 */
			'current' : [],

			/**
			 * This is the timeout for the AJAX request.
			 *
			 * @var mixed
			 */
			'timeout' : null,

			/**
			 * This is the original value before suggestions.
			 *
			 * @var string
			 */
			'original' : '',

			/**
			 * This function initializes the whole system.
			 */
			'init' : function() {
				var target = $(vdfnTag.input);

				// Not standard yet :-(
				target.attr('autocomplete', 'off');

				// Remove [tag] from textarea
				vdfnTag.rmTextarea();
				$('textarea#content').blur(vdfnTag.rmTextarea);

				// Init our system
				vdfnTag.add();
				target.val('');
				target.keydown(vdfnTag.keypress).focus(vdfnTag.keypress);

				// Restore the tag in the input at the submissum of the form
				$('form').submit(vdfnTag.onsubmit);
			},

			/**
			 * This function removes tags from the input and
			 * add them to the tagplace.
			 */
			'add' : function() {
				var target = $(vdfnTag.input);
				var tags = $.trim(target.val()).split(',');

				// Parse every tag
				for (var i = 0; i < tags.length; i++) {
					tags[i] = $.trim(tags[i]);

					if (tags[i] === '') {
						// Not a real tag
					} else if ($.inArray(tags[i], vdfnTag.current) != -1) {
						// Don't duplicate
					} else {
						var span = $('<span></span>').text(tags[i]).attr('title', vdfnTagRemove);
						span.click(function() {
							var tag = $(this).text();
							vdfnTag.current.splice(vdfnTag.current.indexOf(tag), 1);
							$(this).remove();
						});
						$(vdfnTag.tagplace).append(span, ' ');
						vdfnTag.current.push(tags[i]);
					}
				}

				target.val('');
				vdfnTag.destroySugg();
			},

			/**
			 * This function strips [tag]...[/tag] from the textarea.
			 */
			'rmTextarea' : function() {
				var scrollL = $(vdfnTag.textarea).scrollLeft();
				var scrollT = $(vdfnTag.textarea).scrollTop();
				var textarea = $(vdfnTag.textarea).val();
				var pattern = /\[tag\]([\s\S]*?)\[\/tag\]/i;
				var found = null;

				while ((found = pattern.exec(textarea)) !== null) {
					var val = $(vdfnTag.input).val();
					val += ',' + found[1];
					$(vdfnTag.input).val(val);
					textarea = textarea.replace(found[0], '');
				}

				$(vdfnTag.textarea).val(textarea);
				vdfnTag.add();
				$(vdfnTag.textarea).scrollLeft(scrollL);
				$(vdfnTag.textarea).scrollTop(scrollT);

				return true;
			},

			/**
			 * This function is called when the form is submitted.
			 */
			'onsubmit' : function() {
				var target = $(vdfnTag.input);
				var tagadd = '';

				$(vdfnTag.labels).each(function() {
					tagadd += $(this).text() + ',';
				});

				tagadd += target.val();
				target.unbind();
				target.val(tagadd);
			},

			/**
			 * This function is the handler for the keypress event.
			 *
			 * @param object event: The event data
			 * @return boolean
			 */
			'keypress' : function(event) {
				var target = $(this);
				var keyCode = event.which || event.keyCode || 0;

				if (keyCode == 13 || keyCode == 188) {
					// 13: Enter; 188: Comma
					vdfnTag.add(target);
					return false;
				} else if (keyCode == 38 && $('.tagsuggestions').length > 0) {
					var currentUp = $('.tagsuggestions .over');
					if (currentUp.length == 0) {
						vdfnTag.suggover($('.tagsuggestions li:last'), true);
					} else if (currentUp.is('.tagsuggestions li:first-child')) {
						$(this).val(vdfnTag.original);
						vdfnTag.suggout(currentUp, true);
					} else {
						vdfnTag.suggout(currentUp, true);
						vdfnTag.suggover(currentUp.prev(), true);
					}
				} else if (keyCode == 40 && $('.tagsuggestions').length > 0) {
					var currentDown = $('.tagsuggestions .over');
					if (currentDown.length == 0) {
						vdfnTag.suggover($('.tagsuggestions li:first'), true);
					} else if (currentDown.is('.tagsuggestions li:last-child')) {
						$(this).val(vdfnTag.original);
						vdfnTag.suggout(currentDown, true);
					} else {
						vdfnTag.suggout(currentDown, true);
						vdfnTag.suggover(currentDown.next(), true);
					}
				} else if (keyCode == 9) {
					return true;
				} else {
					clearTimeout(vdfnTag.timeout);
					vdfnTag.timeout = setTimeout(function () {
						var suggtag = $(vdfnTag.input).val();
						$.ajax({
							'url' : vdfnTagUrl,
							'dataType' : 'html',
							'data' : {
								'tag' : suggtag
							},
							'success' : vdfnTag.suggestions
						});
					}, 60);
					return true;
				}

				return false;
			},

			/**
			 * This function creates the suggestions.
			 * It's called by the success of AJAX.
			 *
			 * @param string data: The response of ajax
			 */
			'suggestions' : function(data) {
				vdfnTag.original = $(vdfnTag.input).val();
				vdfnTag.destroySugg();

				if (data.length == 0) {
					return;
				}

				var div = $('<div></div>').addClass('tagsuggestions').html(data);
				div.appendTo('body');

				var target = $(vdfnTag.input);
				var toppos = target.offset().top + target.outerHeight() + 'px';
				var leftpos = target.offset().left + 'px';
				var widthval = div.width() - div.outerWidth() + target.outerWidth() + 'px';
				div.css({
					'top' : toppos,
					'left' : leftpos,
					'width' : widthval
				});

				$('.tagsuggestions li').click(function() {
					$(vdfnTag.input).val($(this).text());
					vdfnTag.add();
					return false;
				}).mouseover(vdfnTag.suggover).mouseout(vdfnTag.suggout);

				target.one('blur', vdfnTag.destroySugg);
			},

			/**
			 * This function destroys the suggestions when you blur
			 * the tag input.
			 *
			 * @return boolean
			 */
			'destroySugg' : function() {
				$('.tagsuggestions').remove();
				return true;
			},

			/**
			 * This function is called to highlight a suggestion.
			 *
			 * @param mixed element: The element
			 * @param boolean iskeyboard
			 */
			'suggover' : function(element, iskeyboard) {
				if (!iskeyboard) {
					element = this;
				}

				$(element).addClass('over');

				if (iskeyboard) {
					$(vdfnTag.input).val($(element).text());
				} else {
					$(vdfnTag.input).unbind('blur');
				}
			},

			/**
			 * This function is called to remove highlight from a suggestion.
			 *
			 * @param mixed element: The element
			 * @param boolean iskeyboard
			 */
			'suggout' : function(element, iskeyboard) {
				if (!iskeyboard) {
					element = this;
				}

				$(element).removeClass('over');

				if (!iskeyboard) {
					$(vdfnTag.input).one('blur', vdfnTag.destroySugg);
				}
			}
		};

		$(document).ready(vdfnTag.init);

		/**
		 * This function modifies the default behavior of BBToolbar's
		 * tag code.
		 *
		 * @return boolean
		 */
		bbtCustomFunctions.tag = function() {
			var target = $(vdfnTag.input);
			target.focus();
			$(window).scrollTop(target.offset().top);
			return false;
		};
	})(window.jQuery);
}
