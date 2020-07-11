
// This function generates the menu icons
function generate_menu_icons() {
	var admin_tabs = ["main","entry","static","uploader","widgets","plugin","themes","config","maintain"];
	var admin_icons = ["panel","pencil-alt2","file","upload","move","hummer","palette","settings","check-box"];
	for(var i=0; i<admin_tabs.length; ++i) {
		var new_span = document.getElementById("admin-link-"+admin_tabs[i]);
		new_span.insertAdjacentHTML('afterbegin', "<span class=\"ti-"+admin_icons[i]+"\"></span>");
	}
}

// Responsive functions
function mobile_close_button() {
	// This close the menu and show the page
	var open_element1 = document.getElementsByClassName("mobile_menu_hide");
	var open_element2 = document.getElementById("sidebar");
	for(var i=0; i<open_element1.length; ++i ) {
		open_element1[i].classList.remove("display_off");
	}
	open_element2.classList.remove("display_on");
}

function mobile_open_button() {
	// This open the menu and close the page
	var open_element1 = document.getElementsByClassName("mobile_menu_hide");
	var open_element2 = document.getElementById("sidebar");
	for(var i=0; i<open_element1.length; ++i ) {
		open_element1[i].classList.add("display_off");
	}
	open_element2.classList.add("display_on");
}
// End Responsive functions

// Editor media functions
// Create the button
function set_media_button() {
	var button = '<div class="sceditor-group"><a class="sceditor-button flatpress-media" onclick="open_media(FileManagerDir);"></a></div>';
	$( ".sceditor-toolbar" ).append(button);
}

// Open the botton
function open_media(FileManagerDir) {
	FileManagerDir = FileManagerDir.concat("/plugin.sceditorfilemanager.php");
	//$(".modal-body").load(FileManagerDir);
	$('#flatpress-files-modal').modal();
}