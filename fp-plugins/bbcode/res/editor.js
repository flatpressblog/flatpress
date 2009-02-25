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

function insImage(val) {
	if (val != '--') {
		insertAtCursor('content', '[img=images/'+val+']',' ');
	}
}

function insAttach(val) {
	if (val !='--') {
		insertAtCursor('content', '[url=attachs/'+val+']','[/url]');
	}
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