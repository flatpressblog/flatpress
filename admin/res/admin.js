
// This function generates the menu icons
function generate_menu_icons() {
	let admin_tabs = ["main","entry","static","uploader","widgets","plugin","themes","config","maintain"];
	let admin_icons = ["panel","pencil-alt2","file","upload","move","hummer","palette","settings","check-box"];
	for(let i=0; i<admin_tabs.length; ++i) {
		let new_span = document.getElementById("admin-link-"+admin_tabs[i]);
		new_span.insertAdjacentHTML('afterbegin', "<span class=\"ti-"+admin_icons[i]+"\"></span>");
	}
}

// Responsive functions
function mobile_close_button() {
	// This close the menu and show the page
	let open_element1 = document.getElementsByClassName("mobile_menu_hide");
	let open_element2 = document.getElementById("sidebar");
	for(let i=0; i<open_element1.length; ++i ) {
		open_element1[i].classList.remove("display_off");
	}
	open_element2.classList.remove("display_on");
}

function mobile_open_button() {
	// This open the menu and close the page
	let open_element1 = document.getElementsByClassName("mobile_menu_hide");
	let open_element2 = document.getElementById("sidebar");
	for(let i=0; i<open_element1.length; ++i ) {
		open_element1[i].classList.add("display_off");
	}
	open_element2.classList.add("display_on");
}
// End Responsive functions

let mediaManagerRoute = '';

let insertSCEditorFunction;

/* Functions of FileManager */
// Open the botton
function open_media_manager(insertSCEditor) {
	insertSCEditorFunction = insertSCEditor;
	mediaManagerRoute = '';
	$('#flatpress-files-modal').modal('show');
	$.post('ajax.php', {Operation : 'ListMediaDirectory', Arguments : mediaManagerRoute}, function(data) {
		data = JSON.parse(data);
		if(data.result) {
			showDirectory(data.content);
		} else {
			//throw new Error(data.content);
		}
	});
}

function showDirectory(DirectoryList) {
	let mediaDirectoryModal = document.getElementById('mediaDirectory');
	let mediaDirectoryULDOM = document.createElement('ul');
	if(mediaManagerRoute.length) { // We add ../ to back to parent Directory
		let currentMediaDirectoryLI = document.createElement('li');
		currentMediaDirectoryLI.innerHTML = "..";
		currentMediaDirectoryLI.onclick = () => openNewDirectory('..');
		mediaDirectoryULDOM.appendChild(currentMediaDirectoryLI);
	}
	for(let i = 0; i < DirectoryList.length; ++i) {
		let currentMediaDirectoryLI = document.createElement('li');
		currentMediaDirectoryLI.innerHTML = DirectoryList[i][0]; // File or directory name
		if(DirectoryList[i][1]) { // It is a directory
			currentMediaDirectoryLI.onclick = () => openNewDirectory(DirectoryList[i][0]); // Directory name
		} else { // It is a file
			currentMediaDirectoryLI.onclick = () => openNewFile(DirectoryList[i][0]); // File name 
		}
		writeLiContent(currentMediaDirectoryLI,DirectoryList[i][0], DirectoryList[i][1]); // Content = Icon + fileName
		mediaDirectoryULDOM.appendChild(currentMediaDirectoryLI);
	}
	mediaDirectoryModal.innerHTML = '';
	mediaDirectoryModal.appendChild(mediaDirectoryULDOM);
	changeDirectoryInput();
}

const PARENT_DIRECTORY_REGEX = /[^\/]+\/?$/;

function writeLiContent(currentMediaDirectoryLI, fileName, isDirectory) {
	const fileExtension = detectTypeFile(fileName);
	let imageName;
	if(isDirectory) {
		imageName = 'folder';
	} else {
		switch(fileExtension) {
			case IMAGE: {
				imageName = 'image';
				break;
			}
			default: {
				imageName = 'file';
			}
		}
	}
	let currentImage = '<img src="admin/res/images/' + imageName + '.png" class="managerTypeFile">';
	currentMediaDirectoryLI.innerHTML = currentImage + fileName;
}

function openNewDirectory(DirectoryName) {
	if(DirectoryName === '..') { // Go Back
		/* Delete last directory name from variable */
		mediaManagerRoute = mediaManagerRoute.substr(0, mediaManagerRoute.length - PARENT_DIRECTORY_REGEX.exec(DirectoryName));
	} else {
		mediaManagerRoute += DirectoryName + '/';
	}
	$.post('ajax.php', {Operation : 'ListMediaDirectory', Arguments : mediaManagerRoute}, function(data) {
		data = JSON.parse(data);
		if(data.result) {
			showDirectory(data.content);
		} else {
			//throw new Error(data.content);
		}
	});
}

function openNewFile(FileName) {
	const fileType = detectTypeFile(FileName);
	const functionType = FUNCTION_BY_FILE_FORMAT.get(fileType);
	selectedFile = FileName;
	selectedURL = mediaManagerRoute;
	functionType(FileName);
}

function changeDirectoryInput() {
	const directoryInput = document.getElementById('directoryInput');
	directoryInput.value = '/' + mediaManagerRoute;
}

const FILE = 0;
const IMAGE = 1;
const VIDEO = 2;
const AUDIO = 3;

const REGEX_FILE_DETECTOR = /\.([^\.]+)$/;

/* Video and Audio files not implemented yet */
const FILE_EXTENSIONS_MAP = new Map();

FILE_EXTENSIONS_MAP.set('jpg', IMAGE);
FILE_EXTENSIONS_MAP.set('jpeg', IMAGE);
FILE_EXTENSIONS_MAP.set('gif', IMAGE);
FILE_EXTENSIONS_MAP.set('png', IMAGE);

function detectTypeFile(FileName) {
	// It detects if is and image, a file, etc.
	const fileExtension = REGEX_FILE_DETECTOR.exec(FileName);
	if(fileExtension === null) return FILE;
	const fileExtensionDetection = FILE_EXTENSIONS_MAP.get(fileExtension[1]);
	if(!fileExtensionDetection) {
		return FILE;
	}
	return fileExtensionDetection;
}

const FUNCTION_BY_FILE_FORMAT = new Map();

const INSERT_MEDIA_BUTTON = '<button type="button" class="btn btn-primary" onclick="insertMediaInSceditor()">Insert Media</button>';

FUNCTION_BY_FILE_FORMAT.set(IMAGE, function(imageURL) {
	const img = '<img src="' + 'fp-content/' + mediaManagerRoute + imageURL + '" class="img-fluid">';
	changeMediaPreviewContent(img);
});

FUNCTION_BY_FILE_FORMAT.set(FILE, function(fileURL) {
	changeMediaPreviewContent('<p>No file preview</p>');
});

function changeMediaPreviewContent(content) {
	const mediaPreviewDiv = document.getElementById('mediaPreview');
	mediaPreviewDiv.innerHTML = content;
	const modalFooter = document.getElementById('FilesModalFooter');
	modalFooter.innerHTML = INSERT_MEDIA_BUTTON;
}

let selectedURL;
let selectedFile;

function insertMediaInSceditor() {
	/* Onyl bbcode at the moment */
	const fileType = detectTypeFile(selectedFile);
	switch(fileType) {
		case IMAGE: {
			insertSCEditorFunction('[img]' + selectedURL + selectedFile + '[/img]')
			break;
		}
		default: { /* Other files = url link */
			insertSCEditorFunction('[url=' + selectedURL + selectedFile + ']' + selectedFile +'[/url]')
		}
	}
}