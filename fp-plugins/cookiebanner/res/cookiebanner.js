/**
 * onClick replacement for the OK button
 */

// OK buton getElementById
if (document.getElementById('btn-primary')) { // Button already available?
	cookie_btn_primary(); // Call the registration function
} else { // Register as EventHandler
	document.addEventListener('DOMContentLoaded', cookie_btn_primary);
}

// Registration OK button function
function cookie_btn_primary() {
	const em = document.getElementById('btn-primary');
	if (em) {
		document.getElementById('btn-primary').addEventListener('click', onClick_btn_primary, false);
	}
}
