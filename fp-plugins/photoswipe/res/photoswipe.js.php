<?php
global $lang;
?>
/**
 * Initializes the PhotoSwipe plugin.
 */
function initPhotoSwipePlugin() {
	$('body').each(function() {
		var $pic = $(this);
		// function to collect the images
		var getItems = function() {
			var items = [];
			$pic.find('figure').children('a').each(function() {
				var $href = $(this).attr('href'), $size = $(this).attr('data-size').split('x'), $width = $size[0], $height = $size[1];
				$title = $(this).attr('title');
				var item = {
					src : $href,
					w : $width,
					h : $height,
					title : $title
				}
				items.push(item);
			});
			return items;
		}
		var items = getItems();

		// open the image with the current index
		var $pswp = $('.pswp')[0];
		$pic.unbind('click').on('click', 'figure', function(event) {
			event.preventDefault();
			var index = parseInt($(this).attr('data-index'));
			var options = {
				// see http://photoswipe.com/documentation/options.html
				index : index,
				bgOpacity : .9,
				loop : false,
				closeOnScroll : false,
				closeOnVerticalDrag : false,
				// elements to show
				closeEl : true,
				captionEl : true,
				fullscreenEl : true,
				zoomEl : true,
				shareEl : false,
				counterEl : true,
				arrowEl : true,
				preloaderEl : true
			}

			// add autoplay button 
			var autoplayIntervall = 5000; // switch to next image each x seconds
			if ($('.pswp__button--autoplay').length === 0) {
				$('<button class="pswp__button pswp__button--autoplay" title="<?php

				echo $lang ['plugin'] ['photoswipe'] ['tooltip_autoplaybutton'];
				?> "></button>').insertAfter('.pswp__button--zoom');
			}

			var _autoplayId = null;
			$('.pswp__button--autoplay').on('click', function(event) {
				event.preventDefault();
				if (_autoplayId) {
					clearInterval(_autoplayId);
					_autoplayId = null;
					$('.pswp__button--autoplay').removeClass('stop').attr('title', 'Automatisch abspielen');
				} else {
					_autoplayId = setInterval(function() {
						lightBox.next();
					}, autoplayIntervall);
					$('.pswp__button--autoplay').removeClass('stop').attr('title', 'Abspielen beenden');
					$('.pswp__button--autoplay').addClass('stop');
				}
			});

			// GO! :)
			var lightBox = new PhotoSwipe($pswp, PhotoSwipeUI_Default, items, options);
			lightBox.init();
		}); // $pic.on('click' ...

	}); // $('.picture').each( ...

} // function initPhotoSwipePlugin()

$(document).ready(function() {
	initPhotoSwipePlugin();
});