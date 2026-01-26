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
							const $img = $link.find('img');
							const msrc = $img.length ? $img.attr('src') : '';
							const size = $link.attr('data-size')?.split('x') ?? [0, 0];
							items.push({
								src: $link.attr('href'),
								msrc: msrc,
								w: parseInt(size[0], 10),
								h: parseInt(size[1], 10),
								title: $link.attr('title') || '',
								el: $link[0]
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

					const lightBox = new PhotoSwipe($pswp, PhotoSwipeUI_Default, items, options);

					// Determine natural image dimensions for items where data-size is unknown (0x0).
					const ensureItemSize = function (item) {
						if (!item || (item.w > 0 && item.h > 0) || item._sizeLoading) {
							return;
						}
						item._sizeLoading = true;

						const thumbImg = item.el ? item.el.querySelector('img') : null;
						const thumbW = thumbImg ? (thumbImg.naturalWidth || thumbImg.width || thumbImg.clientWidth || 0) : 0;
						const thumbH = thumbImg ? (thumbImg.naturalHeight || thumbImg.height || thumbImg.clientHeight || 0) : 0;
						const isSvg = typeof item.src === 'string' && /\.svg(\?|#|$)/i.test(item.src);

						const img = new Image();
						img.onload = function () {
							let w = this.naturalWidth || this.width || 0;
							let h = this.naturalHeight || this.height || 0;

							if ((w < 1 || h < 1) && thumbW > 0 && thumbH > 0) {
								w = thumbW;
								h = thumbH;
							}

							// For SVG without intrinsic size, allow higher zoom by using an upscaled fallback.
							if (isSvg && thumbW > 0 && thumbH > 0) {
								const minW = thumbW * 3;
								const minH = thumbH * 3;
								if (w < minW || h < minH) {
									w = minW;
									h = minH;
								}
							}

							if (w > 0 && h > 0) {
								item.w = w;
								item.h = h;
								lightBox.invalidateCurrItems();
								lightBox.updateSize(true);
							}
							item._sizeLoading = false;
						};
						img.onerror = function () {
							// last resort: use thumbnail dimensions
							if (thumbW > 0 && thumbH > 0) {
								item.w = thumbW;
								item.h = thumbH;
								lightBox.invalidateCurrItems();
								lightBox.updateSize(true);
							}
							item._sizeLoading = false;
						};
						img.src = item.src;
					};

					lightBox.listen('gettingData', function (idx, item) {
						if (item && (item.w < 1 || item.h < 1)) {
							ensureItemSize(item);
						}
					});

					// warm up sizing for the initially opened item
					if (items[index] && (items[index].w < 1 || items[index].h < 1)) {
						ensureItemSize(items[index]);
					}

					// GO! :)
					lightBox.init();
				});
			}

			$(document).ready(function () {
				initPhotoSwipePlugin();
			});
