/**
 * Replacement section for onclick/ onchange HTML method
 */
// back
if (document.getElementById('s_close_btn')) {
	s_close_btn();
} else {
	document.addEventListener('DOMContentLoaded', s_close_btn);
}
function s_close_btn() {
	const support = document.getElementById('s_close_btn');
	if (support) {
		document.getElementById('s_close_btn').addEventListener('click', onClick_s_close_btn, false);
	}
}
function onClick_s_close_btn() {
	window.location.href = 'admin.php?p=maintain';
}
