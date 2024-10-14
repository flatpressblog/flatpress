/**
 * GDPR-Video-Embed | non-dynamic part
 */
(function () {
	// Function for extracting the Facebook video URL from the IFrame
	function getFacebookVideoUrl(url) {
		var match = url.match(/href=([^&]+)/);
		return match ? decodeURIComponent(match[1]) : null;
	}

	window.video_iframes = [];
	document.addEventListener("DOMContentLoaded", function () {
		var video_frame, responsive_bbcode_video, video_platform, video_src, video_id, video_w, video_h, video_url;
		for (var i = 0, max = window.frames.length - 1; i <= max; i += 1) {
			video_frame = document.getElementsByTagName('iframe')[0];
			video_src = video_frame.src || video_frame.dataset.src;

			// Only process video frames [youtube|vimeo|facebook]
			if (video_src.match(/youtube|vimeo|facebook/) == null) {
				continue;
			}

			video_iframes.push(video_frame);
			video_w = video_frame.getAttribute('width');
			video_h = video_frame.getAttribute('height');
			responsive_bbcode_video = document.createElement('article');

			// Prevents iframes from loading content immediately
			if (!!video_frame.src) {
				if (typeof (window.frames[0].stop) === 'undefined') {
					setTimeout(function () {
						window.frames[0].execCommand('Stop');
					}, 1000);
				} else {
					setTimeout(function () {
						window.frames[0].stop();
					}, 1000);
				}
			}

			video_platform = video_src.match(/(youtube|vimeo|facebook)/)[0];

			// Extract the correct Facebook video link
			if (video_platform === 'facebook') {
				video_url = getFacebookVideoUrl(video_src);
				// Optional, if ID required
				video_id = video_url.split("/videos/")[1];
			} else {
				video_id = video_src.match(/(embed|video|videos)\/([^?\s]*)/)[2];
			}

			responsive_bbcode_video.setAttribute('class', 'video-responsive_bbcode_video');
			responsive_bbcode_video.setAttribute('data-index', i);
			if (video_w && video_h) {
				responsive_bbcode_video.setAttribute('style', 'width: ' + video_w + 'px; height: ' + video_h + 'px');
			}

			// Replace the placeholders with the extracted video ID or URL
			if (video_platform === 'facebook') {
				responsive_bbcode_video.innerHTML = window.gdprConfig.text[video_platform].replace(/\%video_url\%/g, video_url);
			} else {
				responsive_bbcode_video.innerHTML = window.gdprConfig.text[video_platform].replace(/\%id\%/g, video_id);
			}

			video_frame.parentNode.replaceChild(responsive_bbcode_video, video_frame);
			document.querySelectorAll('.video-responsive_bbcode_video button')[i].addEventListener('click', function () {
				var video_frame = this.parentNode,
				index = video_frame.dataset.index;
				if (!!video_iframes[index].dataset.src) {
					video_iframes[index].src = video_iframes[index].dataset.src;
					video_iframes[index].removeAttribute('data-src');
				}
				video_frame.parentNode.replaceChild(video_iframes[index], video_frame);
			}, false);
		}
	});
})();
