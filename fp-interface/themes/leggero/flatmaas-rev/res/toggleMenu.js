function toggleMenu(sender) {
	
	W=728;
	if (navigator.userAgent.toLowerCase().indexOf('msie')!=-1)
		W = 728;
	
	
	div1 = document.getElementById('column');
	div2 = document.getElementById('main');
	if (div2==null)
		div2 = document.getElementById('cpmain');
	
	
	if (sender.innerHTML == '[+]') {
		sender.innerHTML = '[-]';
	} else {
		sender.innerHTML = '[+]';
	}
	
	if (div1.style.visibility == 'hidden') {
		div2.style.width = (W-185) + 'px';
		div1.style.visibility = 'visible';
		
	} else {
		div1.style.visibility = 'hidden';
		div2.style.width = '98%';						
	}
	
}
