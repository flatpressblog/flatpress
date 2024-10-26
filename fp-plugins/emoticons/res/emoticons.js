/**
 * Emoticons Plugin
 */
function insertEmoticon(myField, myValue) {
	if (document.selection) {
		myField.focus();
		const sel = document.selection.createRange();
		sel.text = myValue;
	} else if (myField.selectionStart || myField.selectionStart === 0) {
		const startPos = myField.selectionStart;
		const endPos = myField.selectionEnd;
		myField.value = myField.value.substring(0, startPos) + myValue + myField.value.substring(endPos, myField.value.length);
		myField.selectionStart = myField.selectionEnd = startPos + myValue.length;
	} else {
		myField.value += myValue;
	}
}

// Register Emoticon Buttons only after ensuring buttons are loaded
function registerEmoticonButtons(buttonData) {
	const inputField = document.querySelector('#content');

	buttonData.forEach(button => {
		const emoticonButton = document.getElementById(button.id);
		if (emoticonButton) {
			emoticonButton.addEventListener('click', function() {
				if (inputField) {
					// Determine current cursor position
					const startPos = inputField.selectionStart;
					const endPos = inputField.selectionEnd;

					// Pick up text before and after the cursor position
					const beforeText = inputField.value.substring(0, startPos);
					const afterText = inputField.value.substring(endPos);

					// Insert emoticon into the text
					inputField.value = beforeText + button.text + afterText;

					// Place the cursor directly behind the inserted emoticon
					const cursorPos = startPos + button.text.length;
					inputField.setSelectionRange(cursorPos, cursorPos);

					// Set focus back to the input field
					inputField.focus();
				} else {
					console.error('Input field not found.');
				}
			});
		} else {
			console.error('Button with ID ' + button.id + ' not found.');
		}
	});
}
