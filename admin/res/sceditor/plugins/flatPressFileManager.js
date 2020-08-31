/**
 * SCEditor FlatPress File Manager
 * http://www.flatpress.org
 *
 * Copyright (C) 2020, Francisco Arocas (franciscoarocas.com)
 *
 * SCEditor is licensed under the MIT license:
 *	http://www.opensource.org/licenses/mit-license.php
 *
 * @author Francisco Arocas 
 */

// After load the plugin, we add first the editor icon
sceditor.command.set('flatPressFileManager', {
	exec: function() {
		/* 
			An a open bootstrap modal function
			The modal html is in the .tpl file, where sceditor is included
		*/
		open_media_manager(this.insert); 
	},
	tooltip: 'Open FlatPress File Manager',
});

(function (sceditor) {
	'use strict';

	var extend = sceditor.utils.extend;

	sceditor.plugins.flatPressFileManager = function () {

		this.init = function () { };

	};
}(sceditor));