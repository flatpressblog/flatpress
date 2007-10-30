<?php

/**
 * uploader control panel
 *
 * Type:     
 * Name:     
 * Date:     
 * Purpose:  
 * Input:
 *         
 * @author NoWhereMan <real_nowhereman at users dot sf dot com>
 *
 */
 	class admin_uploader extends AdminPanel {
		var $panelname = 'uploader';
		var $actions = array('default'=>true);
	}
 	

	class admin_uploader_default extends AdminPanelAction {

		var $events = array('upload');
	
		function main() {
			if ($f = sess_remove('admin_uploader_files'))
				$this->smarty->assign('uploaded_files', $f);
		}

		function onupload() {

			$success = false;
	
			if (!file_exists(IMAGES_DIR))
				fs_mkdir(IMAGES_DIR);
			
			if (!file_exists(ATTACHS_DIR))
				fs_mkdir(ATTACHS_DIR);
			
				
			$imgs = array('.jpg','.gif','.png', '.jpeg'); 
								
								//intentionally 
								//I've not put BMPs
								
			$uploaded_files=array(); 
	
			foreach ($_FILES["upload"]["error"] as $key => $error) {
			
				if ($error == UPLOAD_ERR_OK) {
					$tmp_name = $_FILES["upload"]["tmp_name"][$key];
					$name = $_FILES["upload"]["name"][$key];
					
					$dir = ATTACHS_DIR;
					
					$ext = strtolower(strrchr($name,'.'));
					
					if (in_array($ext,$imgs)) {
						$dir = IMAGES_DIR;
					}
					
					$name = sanitize_title(substr($name, 0, -strlen($ext))) . $ext;
					
					$target = "$dir/$name";
					@umask(022);
					$success = move_uploaded_file($tmp_name, $target);
					@chmod($target,0766);
					
					$uploaded_files[] = $name;

					// one failure will make $success == false :)
					$success &= $success;
					
			
				}
				
			}
			
			if ($uploaded_files) {
				$this->smarty->assign('success', $success? 1 : -1);
				sess_add('admin_uploader_files', $uploaded_files);
			}

			return 1;
			
		}
	}
 	
 ?>
