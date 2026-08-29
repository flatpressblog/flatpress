/**
 * Verify PrettyURLs capabilities through real same-origin browser requests.
 * The green checks are shown only after FlatPress confirms that the request
 * actually reached index.php through the tested routing mode.
 */
(function () {
	'use strict';

	function revealCapability(mode) {
		var marker = document.querySelector('[data-prettyurls-capability="' + mode + '"]');
		if (marker) {
			marker.hidden = false;
			marker.removeAttribute('hidden');
		}
	}

	function probe(url, mode) {
		if (!url || typeof window.XMLHttpRequest !== 'function') {
			return;
		}
		var separator = url.indexOf('?') === -1 ? '?' : '&';
		var probeUrl = url + separator + '_fp_probe_nonce=' + Date.now();
		var request = new window.XMLHttpRequest();
		request.open('GET', probeUrl, true);
		request.onreadystatechange = function () {
			if (request.readyState !== 4) {
				return;
			}
			if (request.status === 200 && request.responseText === 'flatpress-prettyurls-probe:' + mode) {
				revealCapability(mode);
			}
		};
		request.send(null);
	}

	function runCapabilityProbes() {
		var root = document.getElementById('prettyurls-mode-capabilities');
		if (!root) {
			return;
		}
		probe(root.getAttribute('data-pathinfo-probe-url'), 'pathinfo');
		probe(root.getAttribute('data-get-probe-url'), 'get');
		probe(root.getAttribute('data-pretty-probe-url'), 'pretty');
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', runCapabilityProbes);
	} else {
		runCapabilityProbes();
	}
}());
