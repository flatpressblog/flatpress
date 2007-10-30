
window.onDomReady ( function() {
	
	var commform = $('commentform');
	
	if (commform) {
		$('name').value = (tmp = Cookie.get('fp-uname')) ? tmp : '';
		$('email').value = (tmp = Cookie.get('fp-umail')) ? tmp : '';
		$('url').value = (tmp = Cookie.get('fp-uweb')) ? tmp : '';
		
		commform.onsubmit = function () {
			Cookie.set('fp-uname', $('name').value);
			Cookie.set('fp-umail', $('email').value); 
			Cookie.set('fp-uweb', $('url').value);
		}
	}

});
