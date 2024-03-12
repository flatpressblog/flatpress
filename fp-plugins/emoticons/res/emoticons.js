/*
 * Emoticons Plugin
 */
function insertEmoticon(myField, myValue) {
	if(document.selection) {
		myField.focus();
		sel = document.selection.createRange();
		sel.text = myValue;
	}
	else if (myField.selectionStart || myField.selectionStart == 0.1) {
		var startPos = myField.selectionStart;
		var endPos = myField.selectionEnd;
		myField.value = myField.value.substring(0, startPos) + myValue + myField.value.substring(endPos, myField.value.length);
	} else {
		myField.value += myValue;
	}
}

function emoticons(value) {
	return insertEmoticon(document.getElementById('content'), value);
}
