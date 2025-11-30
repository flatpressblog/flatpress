function insertAtCursor(element, start, end) {
	element = document.getElementById(element);
	if (document.selection) {
		// IE
		element.focus();
		caretPos = document.selection.createRange().duplicate();
		caretPos.text = start + caretPos.text + end;
		if (caretPos.text.length == 0) {
			caretPos.moveStart("character", -end.length);
			caretPos.moveEnd("character", -end.length);
			caretPos.select();
		}
		element.focus(caretPos);
	} else if (element.selectionStart || element.selectionStart == '0') {
		// MOZILLA
		element.focus();
		var startPos = element.selectionStart;
		var endPos = element.selectionEnd;
		var preTxt = element.value.substring(0, startPos);
		var selTxt = element.value.substring(startPos, endPos) ;
		var follTxt = element.value.substring(endPos, element.value.length);
		var scrollPos = element.scrollTop;
		element.value = preTxt + start + selTxt + end + follTxt;
		if (element.setSelectionRange) {
			if (selTxt.length == 0) {
				element.setSelectionRange(startPos + start.length, startPos + start.length);
			} else {
				element.setSelectionRange(startPos, startPos + start.length + selTxt.length + end.length);
			}
		element.focus();
		}
		element.scrollTop = scrollPos;
	} else {
		element.value += start + end;
	}
}

// calling the function
// insertAtCursor(document.formName.fieldName, ‘this value’);
function insBBCode(code) {
	insertAtCursor('content', '[' + code + ']','[/' + code + ']');
}

function insBBCodeWithParams(code, params) {
	insertAtCursor('content', '[' + code + '=' + params + ']','[/' + code + ']');
}

function insBBCodeWithParamsAndContent(code, params, content) {
	insertAtCursor('content', '[' + code + '=' + params + ']' + content,'[/' + code + ']');
}

function insBBCodeWithContent(code, content) {
	insertAtCursor('content', '[' + code +']' + content,'[/' + code + ']');
}

function insBBCodeWithoutClosingTag(code) {
	insertAtCursor('content', '[' + code +']','');
}

function insBBCodeWithParamsWithoutClosingTag(code, params) {
	insertAtCursor('content', '[' + code + '=' + params + ']','');
}

function insImage(val) {
	if (!val) {
		return;
	}
	insertAtCursor('content', '[img=images/' + val + ']',' ');
}

function insAttach(val) {
	if (!val) {
		return;
	}
	insertAtCursor('content', '[url=attachs/' + val + ']','[/url]');
}

function insGallery(val) {
	if (!val) {
		return;
	}
	insertAtCursor('content', '[gallery=images/' + val + ' width=180]','');
}

// if false, tab move to next element
var bbcode_editmode = true;

function tabKeyOverrider() {
	// Observe keypress on these fields
	if (Event.observe) {
		Event.observe($('content'), 'keypress', checkTab);
	}
}

var bbcode_mode_trapTab = false;

// took from
// http://www.ajaxian.com/archives/handling-tabs-in-textareas
// with the 'mode' thingie... I think I'll add more 'trapping' stuff like this for Ctrl+B (bold) etc. 
// At this time we're just using the altkey function (which is not that bad anyway)

function checkTab(evt) {
	if (evt.keyCode == Event.KEY_ESC) {
		if (bbcode_mode_trapTab) {
			var bar = $('bbcode_statusbar');
			bar.style.background = 'green';
			bar.innerHTML = 'Normal mode. Press &lt;Esc&gt; to switch editing mode.';
			bbcode_mode_trapTab = false;
		} else {
			var bar = $('bbcode_statusbar');
			bar.style.background = 'blue';
			bar.innerHTML = 'Editing mode. &lt;Tab&gt; is now allowed in the textarea. &lt;Esc&gt; to switch.';
			bbcode_mode_trapTab = true;
		}
	}
	if (!bbcode_mode_trapTab) {
		return;
	}
	// Set desired tab- defaults to four space softtab
	var tab = "\t";
	var t = evt.target;
	var ss = t.selectionStart;
	var se = t.selectionEnd;
	// Tab key - insert tab expansion
	if (evt.keyCode == 9) {
		evt.preventDefault();
		// Special case of multi line selection
		if (ss != se && t.value.slice(ss,se).indexOf("\n") != -1) {
			// In case selection was not of entire lines (e.g. selection begins in the middle of a line)
			// we ought to tab at the beginning as well as at the start of every following line.
			var pre = t.value.slice(0, ss);
			var sel = t.value.slice(ss, se).replace(/\n/g, "\n"+tab);
			var post = t.value.slice(se, t.value.length);
			t.value = pre.concat(tab).concat(sel).concat(post);
			t.selectionStart = ss + tab.length;
			t.selectionEnd = se + tab.length;
		}
		// "Normal" case (no selection or selection on one line only)
		else {
			t.value = t.value.slice(0, ss).concat(tab).concat(t.value.slice(ss, t.value.length));
			if (ss == se) {
				t.selectionStart = t.selectionEnd = ss + tab.length;
			}
			else {
				t.selectionStart = ss + tab.length;
				t.selectionEnd = se + tab.length;
			}
		}
	}
}

if (typeof(Event.observe) == 'function') {
	//prototype is loaded
	Event.observe(window, 'load', tabKeyOverrider, false)
}

/**
 * Replacement section for onclick/ onchange HTML method
 */
// url
if (document.getElementById('bb_url')) {
	bb_url();
} else {
	document.addEventListener('DOMContentLoaded', bb_url);
}
function bb_url() {
	const bb = document.getElementById('bb_url');
	if (bb) {
		document.getElementById('bb_url').addEventListener('click', onClick_bb_url, false);
	}
}
function onClick_bb_url() {
	insBBCode('url');
}

// mail
if (document.getElementById('bb_mail')) {
	bb_mail();
} else {
	document.addEventListener('DOMContentLoaded', bb_mail);
}
function bb_mail() {
	const bb = document.getElementById('bb_mail');
	if (bb) {
		document.getElementById('bb_mail').addEventListener('click', onClick_bb_mail, false);
	}
}
function onClick_bb_mail() {
	insBBCode('mail');
}

// h2
if (document.getElementById('bb_h2')) {
	bb_h2();
} else {
	document.addEventListener('DOMContentLoaded', bb_h2);
}
function bb_h2() {
	const bb = document.getElementById('bb_h2');
	if (bb) {
		document.getElementById('bb_h2').addEventListener('click', onClick_bb_h2, false);
	}
}
function onClick_bb_h2() {
	insBBCode('h2');
}

// h3
if (document.getElementById('bb_h3')) {
	bb_h3();
} else {
	document.addEventListener('DOMContentLoaded', bb_h3);
}
function bb_h3() {
	const bb = document.getElementById('bb_h3');
	if (bb) {
		document.getElementById('bb_h3').addEventListener('click', onClick_bb_h3, false);
	}
}
function onClick_bb_h3() {
	insBBCode('h3');
}

// h4
if (document.getElementById('bb_h4')) {
	bb_h4();
} else {
	document.addEventListener('DOMContentLoaded', bb_h4);
}
function bb_h4() {
	const bb = document.getElementById('bb_h4');
	if (bb) {
		document.getElementById('bb_h4').addEventListener('click', onClick_bb_h4, false);
	}
}
function onClick_bb_h4() {
	insBBCode('h4');
}

// ul
if (document.getElementById('bb_ul')) {
	bb_ul();
} else {
	document.addEventListener('DOMContentLoaded', bb_ul);
}
function bb_ul() {
	const bb = document.getElementById('bb_ul');
	if (bb) {
		document.getElementById('bb_ul').addEventListener('click', onClick_bb_ul, false);
	}
}
function onClick_bb_ul() {
	insBBCodeWithContent('list', '\n[*]\n[*]\n');
}

// ol
if (document.getElementById('bb_ol')) {
	bb_ol();
} else {
	document.addEventListener('DOMContentLoaded', bb_ol);
}
function bb_ol() {
	const bb = document.getElementById('bb_ol');
	if (bb) {
		document.getElementById('bb_ol').addEventListener('click', onClick_bb_ol, false);
	}
}
function onClick_bb_ol() {
	insBBCodeWithParamsAndContent('list', '#', '\n[*]\n[*]\n');
}

// quote
if (document.getElementById('bb_quote')) {
	bb_quote();
} else {
	document.addEventListener('DOMContentLoaded', bb_quote);
}
function bb_quote() {
	const bb = document.getElementById('bb_quote');
	if (bb) {
		document.getElementById('bb_quote').addEventListener('click', onClick_bb_quote, false);
	}
}
function onClick_bb_quote() {
	insBBCode('quote');
}

// code
if (document.getElementById('bb_code')) {
	bb_code();
} else {
	document.addEventListener('DOMContentLoaded', bb_code);
}
function bb_code() {
	const bb = document.getElementById('bb_code');
	if (bb) {
		document.getElementById('bb_code').addEventListener('click', onClick_bb_code, false);
	}
}
function onClick_bb_code() {
	insBBCode('code');
}

// html
if (document.getElementById('bb_html')) {
	bb_html();
} else {
	document.addEventListener('DOMContentLoaded', bb_html);
}
function bb_html() {
	const bb = document.getElementById('bb_html');
	if (bb) {
		document.getElementById('bb_html').addEventListener('click', onClick_bb_html, false);
	}
}
function onClick_bb_html() {
	insBBCode('html');
}

// font
if (document.getElementById('bb_font')) {
	bb_font();
} else {
	document.addEventListener('DOMContentLoaded', bb_font);
}
function bb_font() {
	const bb = document.getElementById('bb_font');
	if (bb) {
		document.getElementById('bb_font').addEventListener('click', onClick_bb_font, false);
	}
}
function onClick_bb_font() {
	insBBCode('font');
}

// b
if (document.getElementById('bb_b')) {
	bb_b();
} else {
	document.addEventListener('DOMContentLoaded', bb_b);
}
function bb_b() {
	const bb = document.getElementById('bb_b');
	if (bb) {
		document.getElementById('bb_b').addEventListener('click', onClick_bb_b, false);
	}
}
function onClick_bb_b() {
	insBBCode('b');
}

// i
if (document.getElementById('bb_i')) {
	bb_i();
} else {
	document.addEventListener('DOMContentLoaded', bb_i);
}
function bb_i() {
	const bb = document.getElementById('bb_i');
	if (bb) {
		document.getElementById('bb_i').addEventListener('click', onClick_bb_i, false);
	}
}
function onClick_bb_i() {
	insBBCode('i');
}

// u
if (document.getElementById('bb_u')) {
	bb_u();
} else {
	document.addEventListener('DOMContentLoaded', bb_u);
}
function bb_u() {
	const bb = document.getElementById('bb_u');
	if (bb) {
		document.getElementById('bb_u').addEventListener('click', onClick_bb_u, false);
	}
}
function onClick_bb_u() {
	insBBCode('u');
}

// del
if (document.getElementById('bb_del')) {
	bb_del();
} else {
	document.addEventListener('DOMContentLoaded', bb_del);
}
function bb_del() {
	const bb = document.getElementById('bb_del');
	if (bb) {
		document.getElementById('bb_del').addEventListener('click', onClick_bb_del, false);
	}
}
function onClick_bb_del() {
	insBBCode('del');
}

// contentfield expand-button
if (document.getElementById('expand')) {
	bb_expand();
} else {
	document.addEventListener('DOMContentLoaded', bb_expand);
}
function bb_expand() {
	const bb = document.getElementById('expand');
	if (bb) {
		document.getElementById('expand').addEventListener('click', contentFieldExpand, false);
	}
}
function contentFieldExpand() {
	const bb = document.getElementById('content');
	if (bb) {
		document.getElementById('content').form.content.rows += 5;
	}
}

// contentfield reduce-button
if (document.getElementById('reduce')) {
	bb_reduce();
} else {
	document.addEventListener('DOMContentLoaded', bb_reduce);
}
function bb_reduce() {
	const bb = document.getElementById('reduce');
	if (bb) {
		document.getElementById('reduce').addEventListener('click', contentFieldReduce, false);
	}
}
function contentFieldReduce() {
	const bb = document.getElementById('content');
	if (bb) {
		document.getElementById('content').form.content.rows -= 5;
	}
}

// file selection
if (document.getElementById('bb_attach')) {
	bb_file_selection();
} else {
	document.addEventListener('DOMContentLoaded', bb_file_selection);
}
function bb_file_selection() {
	const bb = document.getElementById('bb_attach');
	if (bb) {
		document.getElementById('bb_attach').addEventListener('change', onChange_insAttach, false);
	}
}
function onChange_insAttach() {
	insAttach(this.form.attachselect.value);
}

// image selection
if (document.getElementById('bb_image')) {
	bb_image_selection();
} else {
	document.addEventListener('DOMContentLoaded', bb_image_selection);
}
function bb_image_selection() {
	const bb = document.getElementById('bb_image');
	if (bb) {
		document.getElementById('bb_image').addEventListener('change', onChange_insImge, false);
	}
}
function onChange_insImge() {
	insImage(this.form.imageselect.value);
}

// gallery selection
if (document.getElementById('bb_gallery')) {
	bb_gallery_selection();
} else {
	document.addEventListener('DOMContentLoaded', bb_gallery_selection);
}
function bb_gallery_selection() {
	const bb = document.getElementById('bb_gallery');
	if (bb) {
		document.getElementById('bb_gallery').addEventListener('change', onChange_insGallery, false);
	}
}
function onChange_insGallery() {
	insGallery(this.form.galleryselect.value);
}

// BBcode image popup
document.addEventListener('DOMContentLoaded', function() {
	const popupLinks = document.querySelectorAll('a.bbcode-popup');
	popupLinks.forEach(function(link) {
		link.addEventListener('click', function(event) {
			event.preventDefault();
			const url = link.getAttribute('href');
			const width = link.dataset.width || 800;
			const height = link.dataset.height || 600;
			window.open(
				url,
				'Popup',
				`toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=${width},height=${height}`
			);
		});
	});
});
