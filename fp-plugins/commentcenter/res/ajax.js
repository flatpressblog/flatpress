/**
 * This function adds an error or a success to Flatpress standard place.
 */
function fpNotify(msg, type) {
	$('#errorlist').hide().html('<ul class="msgs '+type+"\">\n<li>"+msg+"</li>\n</ul>").fadeIn('slow');
	window.setTimeout(fpRemoveNotifies, 3000);
}

/**
 * This function removes the FP Notifies
 */
function fpRemoveNotifies() {
	$('#errorlist ul[class!="msgs warnings"]').fadeOut('slow');
}

/**
 * This function moves a policy.
 *
 * @param integer id: The policy id
 * @param integer how: The size of the movement
 */
function movePolicy(id, how) {
	tarid=parseInt(id)+parseInt(how);
	original='tr_policy'+id;
	target='tr_policy'+tarid;
	tmp='tmp'+target;

	if(how<0) {
		$('.'+original).insertBefore('.'+target);
	} else if(how>0) {
		$('.'+original).insertAfter('.'+target);
	}

	$('.'+original).removeClass(original).addClass(tmp);
	$('.'+target).removeClass(target).addClass(original).attr('href');
	$('.'+tmp).removeClass(tmp).addClass(target)
	$('.'+target+' td').animate({'background-color' : '#78ba91'}, {
		'complete' : function() {
			$(this).attr('style', '');
		}
	});

	oruh=$('a[rel=polup\\['+id+'\\]]').attr('rel', 'tmpup').attr('href');
	ordh=$('a[rel=poldown\\['+id+'\\]]').attr('rel', 'tmpdown').attr('href');
	tauh=$('a[rel=polup\\['+tarid+'\\]]').attr('rel', 'polup['+id+']').attr('href');
	tadh=$('a[rel=poldown\\['+tarid+'\\]]').attr('rel', 'poldown['+id+']').attr('href');
	$('a[rel=polup\\['+id+'\\]]').attr('href', oruh);
	$('a[rel=poldown\\['+id+'\\]]').attr('href', ordh);
	$('a[rel=tmpup]').attr('rel', 'polup['+tarid+']').attr('href', tauh);
	$('a[rel=tmpdown]').attr('rel', 'poldown['+tarid+']').attr('href', tadh);
}

/**
 * This function is called on the click event to move up/down with AJAX the policies.
 */
function clickPolicy() {
	rel=$(this).attr('rel');
	start_id=rel.indexOf('[');
	end_id=rel.indexOf(']');
	id=rel.substr(start_id+1, end_id-start_id-1);
	dir=rel.substr(3, start_id-3);
	how=dir=='up' ? -1 : 1;
	url=$(this).attr('href')+'&mod=ajax';
	succ=0;
	$.ajax({
		'url' : url,
		'success' : function(data) {
			succ=1;
			if(data==3) {
				fpNotify(commentcenter_lang.msg3, 'notifications');
				movePolicy(id, how);
			} else if(data==-3) {
				fpNotify(commentcenter_lang.msg_3, 'errors');
			} else {
				fpNotify(data, 'errors');
			}
		}
	});
	return succ==1;
}

/**
 * This is the callback for the event click to select all checkbox
 */
function checkboxa() {
	rel=$(this).attr('rel');
	tdbegin=rel.indexOf('[');
	tdend=rel.indexOf(']');
	td=rel.substr(tdbegin+1, tdend-tdbegin-1);
	check=rel.substr(0, tdbegin)=='selectAll';
	$('.'+td+' input[type=checkbox]').attr('checked', check);
	return false;
}

/**
 * This function checks for the radio buttons in the edit policy page.
 */
function radioEdit() {
	if($('#fill_entries').length<1) {
		return;
	}

	reDone=false;

	$('input[type=radio]').click(radioClick).each(function() {
		if($(this).attr('checked')) {
			reDone=true;
			$(this).click();
		}
	});

	if(!reDone) {
		$('input[value=all_entries]').click().attr('checked', false);
	}
}

/**
 * This function is the callback for the event click on radio buttons.
 */
function radioClick() {
	val=$(this).attr('value');
	entries='#fill_entries';
	prop='#fill_properties';

	if(val=='some_entries') {
		show=entries;
		hide=prop;
	} else if(val=='properties') {
		show=prop;
		hide=entries;
	} else {
		$(entries).hide();
		$(prop).hide();
		return true;
	}

	$(show).show();
	$(hide).hide();

}

/**
 * This function shows/hides the Akismet options at the startup.
 */
function akismetOptionsReady() {
	if($('label[for=akismet_check]').length<1) {
		return;
	}

	$('input[name=akismet_check]').click(akismetOptionsReady);

	status=$('input[name=akismet_check]').attr('checked');

	el=$('.akismet_opts');

	if(status) {
		el.show();
	} else {
		el.hide();
	}

	return true;
}

$(document).ready(function() {
	window.setTimeout(fpRemoveNotifies, 3000);
	$('a[rel*="polup"]').click(clickPolicy);
	$('a[rel*="poldown"]').click(clickPolicy);

	$('.commentcenter_select').css('display', 'block').find('a').click(checkboxa);

	radioEdit();
	akismetOptionsReady();
});