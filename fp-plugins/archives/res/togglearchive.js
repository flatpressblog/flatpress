const ARROW_OPEN = '\u25BE ';
const ARROW_CLOSED = '\u25B8 ';

function toggle(obj) {
	let parentElement = obj.parentNode;
	for (let i = 0; i < parentElement.childNodes.length; i++) {
		if (parentElement.childNodes[i].nodeName === "UL") {
			const ul = parentElement.childNodes[i];
			const yearMatch = parentElement.className.match(/archive-y(\d{4})/);
			const year = yearMatch ? yearMatch[1] : null;

			if (ul.style.display === "none" || ul.style.display === "") {
				showMth(parentElement);
				obj.className = 'togglelink toggleminus';
				obj.textContent = ARROW_OPEN;
				obj.title = 'Reduce';
				obj.setAttribute('aria-expanded', 'true');
				parentElement.classList.add('open');
				if (year) saveOpenYear(year);
			} else {
				hideMth(parentElement);
				obj.className = 'togglelink toggleplus';
				obj.textContent = ARROW_CLOSED;
				obj.title = 'Expand';
				obj.setAttribute('aria-expanded', 'false');
				parentElement.classList.remove('open');
				if (year) removeOpenYear(year);
			}
			break;
		}
	}
}

function hideMth(obj) {
	for (let i = 0; i < obj.childNodes.length; i++) {
		if (obj.childNodes[i].nodeName === "UL") {
			obj.childNodes[i].style.display = "none";
		}
	}
}

function showMth(obj) {
	for (let i = 0; i < obj.childNodes.length; i++) {
		if (obj.childNodes[i].nodeName === "UL") {
			obj.childNodes[i].style.display = "block";
		}
	}
}

function saveOpenYear(year) {
	let openYears = JSON.parse(localStorage.getItem('fp_open_years') || '[]');
	if (!openYears.includes(year)) {
		openYears.push(year);
		localStorage.setItem('fp_open_years', JSON.stringify(openYears));
	}
}

function removeOpenYear(year) {
	let openYears = JSON.parse(localStorage.getItem('fp_open_years') || '[]');
	openYears = openYears.filter(y => y !== year);
	localStorage.setItem('fp_open_years', JSON.stringify(openYears));
}

/**
 * Initialize toggle buttons for the Archive widget
 */
document.addEventListener('DOMContentLoaded', function () {
	const archiveYears = document.querySelectorAll('#widget-archives ul > li.archive-year, ' + '#footernav ul > li.archive-year');
	if (!archiveYears.length) return;

	let openYears = JSON.parse(localStorage.getItem('fp_open_years') || '[]');

	archiveYears.forEach((li, index) => {
		const toggleEl = li.querySelector('.togglelink');
		const nestedUl = li.querySelector('ul');
		const uniqueId = 'archive-' + index;
		const yearMatch = li.className.match(/archive-y(\d{4})/);
		const year = yearMatch ? yearMatch[1] : null;

		if (toggleEl && nestedUl) {
			toggleEl.setAttribute('aria-controls', uniqueId);
			nestedUl.setAttribute('id', uniqueId);

			hideMth(li);
			toggleEl.className = 'togglelink toggleplus';
			toggleEl.textContent = ARROW_CLOSED;
			toggleEl.setAttribute('aria-expanded', 'false');
			li.classList.remove('open');

			toggleEl.addEventListener('click', function (e) {
				e.preventDefault();
				toggle(this);
			});

			if (year && openYears.includes(year)) {
				toggle(toggleEl);
			}
		}
	});

	// If no years are saved: open current year
	if (openYears.length === 0) {
		const currentYear = new Date().getFullYear().toString();
		const currentToggle = document.querySelector('.archive-y' + currentYear + ' .togglelink');
		if (currentToggle) {
			// toggle(currentToggle);
		}
	}
});
