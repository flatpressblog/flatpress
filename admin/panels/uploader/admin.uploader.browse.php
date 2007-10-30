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
 
	class uploader_lister extends fs_filelister {
	
		var $_dirlist = array();
		var $_filelist = array();
	
		function uploader_lister($d, $pd) {
			$this->urldir = $pd;
			$this->basepanelurl = 
				BLOG_BASEURL .  
				"admin.php?p=uploader&amp;action=browse&amp;dir=";
			$this->thumburl =
				BLOG_BASEURL .
				'admin.php?p=uploader&amp;action=thumb&amp;f='; 
			return parent::fs_filelister($d);
		}
	
		function _checkFile($d, $f) {
			
			$p = "{$d}{$f}";
			
			if (is_dir($p)) {
				$this->_dirlist[$f]="{$this->basepanelurl}$f";
			} else {
				$lbl = $f;
				$this->_filelist[$f]="{$this->thumburl}{$this->urldir}$f";
			}
			
			return parent::_checkFile($d,$f);
			
			
		}
		
		function getDirs() {
		
			ksort($this->_dirlist);
			return $this->_dirlist;
			
		}
		
		function getFiles() {
			ksort($this->_filelist);
			return $this->_filelist;
		}
	
	}
 	
	class admin_uploader_browse extends AdminPanelAction {

		var $events = array('upload');
	
		function main() {
			
			if (!empty($_GET['dir'])) {
				
			 	$dir = $_GET['dir'];
			 	if (substr($_GET['dir'], -1)!= '/')
			 		$dir.= '/';
			} else {
				$dir = './';
			}
			
			$pd = $dir;
			$dir = ABS_PATH.IMAGES_DIR.$dir;
			$o = new uploader_lister($dir, $pd);
			
			if ($dir != '')
				$this->smarty->assign('parent', $o->basepanelurl.dirname($dir));
			$this->smarty->assign('dirs', $o->getDirs());
			$this->smarty->assign('files', $o->getFiles());
			
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
