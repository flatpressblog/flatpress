function toggle(obj) {
	parentElement = obj.parentNode;
	for (i = 0; i < parentElement.childNodes.length; i++) {
		if (parentElement.childNodes[i].nodeName == "UL") {
			if (parentElement.childNodes[i].style.display == "none") {
				showMth(parentElement);
				obj.className = 'togglelink toggleminus';
				obj.textContent = '▾ ';
				obj.title = 'Reduce';
				obj.setAttribute('aria-expanded', 'true');
			} else {
				hideMth(parentElement);
				obj.className = 'togglelink toggleplus';
				obj.textContent = '▸ ';
				obj.title = 'Expand';
				obj.setAttribute('aria-expanded', 'false');
			}
			break;
		}
	}
}

function hideMth(obj) { 
	for (i = 0; i < obj.childNodes.length; i++) {
		if (obj.childNodes[i].nodeName == "UL") {
			obj.childNodes[i].style.display = "none";
		}
	}
}

function showMth(obj) { 
	for (i = 0; i < obj.childNodes.length; i++) {
		if (obj.childNodes[i].nodeName == "UL") {
			obj.childNodes[i].style.display = "block";
		}
	}
}

/**
 * Initialize toggle buttons for the Archive widget
 */
document.addEventListener('DOMContentLoaded', function () {
	if (typeof toggleArchive === 'function' && document.querySelector('#widget-archives')) {
		toggleArchive('');
	}
});
