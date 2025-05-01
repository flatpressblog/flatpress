<?php
global $lang;
?>

		/**
		 * Initializes the PhotoSwipe plugin.
		 */
		function initPhotoSwipePlugin() {
			// Only add PhotoSwipe overlay if it does not yet exist for [more] tag
			if ($('.pswp').length === 0) {
				const pswpHtml = `
				<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
					<div class="pswp__bg"></div>
					<div class="pswp__scroll-wrap">
						<div class="pswp__container">
							<div class="pswp__item"></div>
							<div class="pswp__item"></div>
							<div class="pswp__item"></div>
						</div>
						<div class="pswp__ui pswp__ui--hidden">
							<div class="pswp__top-bar">
								<div class="pswp__counter"></div>
								<button class="pswp__button pswp__button--close" title="<?php echo htmlspecialchars($lang['plugin']['photoswipe']['tooltip_closebutton'], ENT_QUOTES); ?>"></button>
								<button class="pswp__button pswp__button--share" title="<?php echo htmlspecialchars($lang['plugin']['photoswipe']['tooltip_sharebutton'], ENT_QUOTES); ?>"></button>
								<button class="pswp__button pswp__button--fs" title="<?php echo htmlspecialchars($lang['plugin']['photoswipe']['tooltip_fullscreenbutton'], ENT_QUOTES); ?>"></button>
								<button class="pswp__button pswp__button--zoom" title="<?php echo htmlspecialchars($lang['plugin']['photoswipe']['tooltip_zoombutton'], ENT_QUOTES); ?>"></button>
								<div class="pswp__preloader">
									<div class="pswp__preloader__icn">
										<div class="pswp__preloader__cut">
											<div class="pswp__preloader__donut"></div>
										</div>
									</div>
								</div>
							</div>
							<div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
								<div class="pswp__share-tooltip"></div>
							</div>
							<button class="pswp__button pswp__button--arrow--left" title="<?php echo htmlspecialchars($lang['plugin']['photoswipe']['tooltip_prevbutton'], ENT_QUOTES); ?>"></button>
							<button class="pswp__button pswp__button--arrow--right" title="<?php echo htmlspecialchars($lang['plugin']['photoswipe']['tooltip_nextbutton'], ENT_QUOTES); ?>"></button>
							<div class="pswp__caption">
								<div class="pswp__caption__center"></div>
							</div>
						</div>
					</div>
				</div>`;

				const readmore = document.querySelector('.entry .readmore');
				if (readmore) {
					// Insert overlay after readmore to be safe in the DOM
					readmore.insertAdjacentHTML('afterend', pswpHtml);
				}
			}

			const $pswp = $('.pswp')[0];

			if (!$pswp) {
				// console.warn('PhotoSwipe (.pswp) not found in DOM. Possibly .readmore is missing or the plugin was initialized too early.');
				return;
			}

			$('body').on('click', 'figure[itemtype="http://schema.org/ImageObject"]', function (event) {
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
