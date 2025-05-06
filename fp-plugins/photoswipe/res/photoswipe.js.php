<?php
global $lang;
?>

			/**
			 * Initializes the PhotoSwipe plugin.
			 */
			function initPhotoSwipePlugin() {

				const $pswp = $('.pswp')[0];

				if (!$pswp) {
					return;
				}

				$('body').on('click', 'figure[itemtype="http://schema.org/ImageObject"]', function (event) {

					lastPswpTrigger = this.querySelector('a');

					event.preventDefault();
					const $clickedFigure = $(this);

					let $group = $clickedFigure.closest('.photoswipe');

					// Group all related images in the same gallery
					const $galleryGroup = $group
						.parent()
						.children('.photoswipe[itemtype="http://schema.org/ImageGallery"]');

					// Create items
					let items = [];
					$galleryGroup.each(function () {
						const $fig = $(this).find('figure[itemtype="http://schema.org/ImageObject"]');
						if ($fig.length > 0) {
							const $link = $fig.find('a');
							const size = $link.attr('data-size')?.split('x') ?? [0, 0];
							items.push({
								src: $link.attr('href'),
								w: parseInt(size[0], 10),
								h: parseInt(size[1], 10),
								title: $link.attr('title') || ''
							});
						}
					});

					// Find index of the clicked image in the items array
					const clickedSrc = $clickedFigure.find('a').attr('href');
					let index = items.findIndex(item => item.src === clickedSrc);
					if (index < 0 || index >= items.length) {
						console.warn('Index konnte nicht korrekt bestimmt werden.');
						index = 0;
					}

					// PhotoSwipe options
					const options = {
						// see http://photoswipe.com/documentation/options.html
						index,
						bgOpacity: 0.9,
						loop: false,
						closeOnScroll: false,
						closeOnVerticalDrag: false,
						// Elements to show
						closeEl: true,
						captionEl: true,
						fullscreenEl: true,
						zoomEl: true,
						shareEl: false,
						counterEl: true,
						arrowEl: true,
						preloaderEl: true
					};

					// Add autoplay button
					if ($('.pswp__button--autoplay').length === 0) {
						$('<button class="pswp__button pswp__button--autoplay" title="<?php echo htmlspecialchars($lang['plugin']['photoswipe']['tooltip_autoplaybutton'], ENT_QUOTES); ?>"></button>').insertAfter('.pswp__button--zoom');
					}

					// Autoplay function
					let _autoplayId = null;
					$('.pswp__button--autoplay').off('click').on('click', function (e) {
						e.preventDefault();
						if (_autoplayId) {
							clearInterval(_autoplayId);
							_autoplayId = null;
							$(this).removeClass('stop').attr('title', '<?php echo htmlspecialchars($lang['plugin']['photoswipe']['tooltip_autoplaybutton'], ENT_QUOTES); ?>');
						} else {
							_autoplayId = setInterval(() => {
								lightBox.next();
							}, 5000);
							$(this).addClass('stop').attr('title', '<?php echo htmlspecialchars($lang['plugin']['photoswipe']['tooltip_autoplaybutton_stop'], ENT_QUOTES); ?>');
						}
					});

					// GO! :)
					const lightBox = new PhotoSwipe($pswp, PhotoSwipeUI_Default, items, options);
					lightBox.init();
				});
			}

			$(document).ready(function () {
				initPhotoSwipePlugin();
			});
