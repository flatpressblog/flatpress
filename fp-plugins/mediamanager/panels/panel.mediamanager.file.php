<?php



class admin_uploader_mediamanager extends AdminPanelAction { 
	var $finfo;
	var $conf;
	var $langres = 'plugin:mediamanager';

	function cmpfiles($a, $b){
		$c = strcmp($a['type'],$b['type']);
		if ($c==0){
			return strcmp($a['name'],$b['name']);
		}
		return $c;
	}


	function formatBytes($bytes, $precision = 2) {
		$units = array('B', 'KB', 'MB', 'GB', 'TB');
	  
		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);
	  
		$bytes /= pow(1024, $pow);
	  
		return round($bytes, $precision) . ' ' . $units[$pow];
	} 



	function getFileInfo($filepath){
		global $fp_config;
	
		$info = array(
			"name"=>basename($filepath),
			"size"=>$this->formatBytes(filesize($filepath)),
			"mtime"=>date_strformat($fp_config['locale']['dateformatshort'], filemtime($filepath))
		);
	
		if (isset($this->conf['usecount'][basename($filepath)])){
			$info['usecount']=$this->conf['usecount'][basename($filepath)];
		} else {
			$info['usecount'] = null;
		}

	
		return $info;
	}    
	
    function setup() {
	    $this->smarty->assign('admin_resource', "plugin:mediamanager/admin.plugin.mediamanager.files");
    }
	
	function deleteFolder($folder, $mmbaseurl){
		if (!file_exists($folder)) return false;
		$dir = opendir($folder);
		while (false !== ($file = readdir($dir))) {
			if (!in_array($file, array(".",".."))) {
				if (is_dir($folder."/".$file)){
					$this->deleteFolder($folder."/".$file, $mmbaseurl);
				} else {
					if (!unlink($folder."/".$file)) return false;
				}
			}
		}
		return rmdir($folder);
	}

	function doItemActions($folder, $mmbaseurl){
		/* delete file*/
		if (isset($_GET['deletefile'])){
			list($type, $name) = explode("-", $_GET['deletefile'],2);
			switch($type){
				case 'attachs': $type=ABS_PATH.ATTACHS_DIR; break;
				case 'images': $type=ABS_PATH.IMAGES_DIR.$folder; break;
				case 'gallery':
					if ( !$this->deleteFolder(ABS_PATH.IMAGES_DIR.$name, $mmbaseurl))
						@utils_redirect($mmbaseurl.'&status=-1'); 
					@utils_redirect($mmbaseurl.'&status=1'); 
					return true;
					break;
				default: { @utils_redirect($mmbaseurl.'&status=-1'); return true; }
			}
			if (!file_exists($type.$name)) { @utils_redirect($mmbaseurl.'&status=-1'); return true; }
			if (!unlink($type.$name)) { @utils_redirect($mmbaseurl.'&status=-1'); return true; }
			@utils_redirect($mmbaseurl.'&status=1');
			return true;
		}
		if (isset($_GET['status'])){
			$this->smarty->assign('success', $_GET['status']);
		}
		return false;
	}
	

			
	function main() {
		$mmbaseurl="admin.php?p=uploader&action=mediamanager";
		$folder = ""; $gallery="";
		if (isset($_GET['gallery'])){
			$mmbaseurl .= "&gallery=".$_GET['gallery'];
			$gallery = str_replace("/","",$_GET['gallery']);
			$folder =  $gallery."/";
		}
		
		
		$weburl = plugin_geturl('mediamanager');
		$this->conf = plugin_getoptions('mediamanager');
		if ($this->doItemActions($folder, $mmbaseurl)) return;
		
		
	
		
		
		$files = array();
		$galleries = array();
		
		$files_needupdate=array();
		$galleries_needupdate=array();
		
		# galleries (alwais from IMAGES_DIR)
		if (file_exists(ABS_PATH.IMAGES_DIR)){
			$dir = opendir(ABS_PATH.IMAGES_DIR);
			while (false !== ($file = readdir($dir))){
				$fullpath=ABS_PATH.IMAGES_DIR.$file;
				if (!in_array($file, array(".","..",".thumbs")) && is_dir($fullpath)) {
					$info = $this->getFileInfo($fullpath);
					$info['type'] = "gallery";
					$galleries[$fullpath] = $info;
					if (is_null($info['usecount'])) { $galleries_needupdate[]=$fullpath;}
				}
			}
		}
		
		# attachs (NO attachs in galleries)
		if ($folder=="" && file_exists(ABS_PATH.ATTACHS_DIR)){
			$dir = opendir(ABS_PATH.ATTACHS_DIR);
			while (false !== ($file = readdir($dir))) {
				if (!in_array($file, array(".",".."))) {
					$fullpath = ABS_PATH.ATTACHS_DIR.$file;
					$info=$this->getFileInfo($fullpath);
					$info['type']="attachs";
					$info['url']=BLOG_ROOT.ATTACHS_DIR.$file;
					$files[$fullpath]=$info;
					if (is_null($info['usecount'])) { $files_needupdate[]=$fullpath;}
				}
			}
		}
		# images
		if (file_exists(ABS_PATH.IMAGES_DIR.$folder)){
			$dir = opendir(ABS_PATH.IMAGES_DIR.$folder);
			while (false !== ($file = readdir($dir))){
				$fullpath=ABS_PATH.IMAGES_DIR.$folder.$file;
				if (!in_array($file, array(".","..",".thumbs")) && !is_dir($fullpath)) {
					$info=$this->getFileInfo($fullpath);
					$info['type']="images";
					$info['url']=BLOG_ROOT.IMAGES_DIR.$folder.$file;
					$files[$fullpath]=$info;
					# NO count for images in galleries
					if ($folder=="" && is_null($info['usecount'])) { $files_needupdate[]=$fullpath; }
				}
			}
		}
		mediamanager_updateUseCountArr($files,$files_needupdate);
		mediamanager_updateUseCountArr($galleries,$galleries_needupdate);
		
		usort($files, Array("admin_uploader_mediamanager","cmpfiles"));
		$totalfilescount = (string) count($files);
		#paginator
		$pages = ceil((count($files)+count($galleries))/ ITEMSPERPAGE);
		if ($pages==0) $pages=1;
		if (isset($_GET['page'])){
			$page = (int) $_GET['page'];
		} else {
			$page=1;
		}
		if ($page<1) $page=1;
		if ($page>$pages) $page=$pages;
		$pagelist = array();
		for($k=1; $k<=$pages; $k++ ) $pagelist[]=$k;
		$paginator = array( "total"=>$pages,
						    "current"=>$page,
							"limit" => ITEMSPERPAGE,
							"pages" => $pagelist
						  );

		$startfrom = ($page-1)*ITEMSPERPAGE;
		$galleriesout = count(array_slice($galleries,0, $startfrom));
		$dropdowngalleries=$galleries;
		$galleries = array_slice($galleries, $startfrom, ITEMSPERPAGE);
		
		$files = array_slice($files, $startfrom-$galleriesout, ITEMSPERPAGE- count($galleries));

		$this->smarty->assign('paginator', $paginator);
		$this->smarty->assign('files', $files);
		$this->smarty->assign('galleries', $galleries);
		$this->smarty->assign('dwgalleries', $dropdowngalleries);
		$this->smarty->assign('mmurl', $weburl);
		$this->smarty->assign('mmbaseurl', $mmbaseurl);
		$this->smarty->assign('currentgallery', $gallery);
		$this->smarty->assign('totalfilescount', $totalfilescount);
	}
	
	function onsubmit($data = NULL) {
		if (isset($_POST['mm-newgallery'])){
			$newgallery=$_POST['mm-newgallery-name'];
			if ($newgallery==""){
				$this->smarty->assign('success', -3);
				return 2;
			}
			$newgallery = str_replace("/","", $newgallery);
			$newgallery = str_replace(".","", $newgallery);
			if (mkdir(ABS_PATH.IMAGES_DIR.$newgallery) ) {
				$this->smarty->assign('success', 3);
			} else {
				$this->smarty->assign('success', -2);
			}
			return 2;
		}

		
		$folder = "";
		if (isset($_GET['gallery'])){
			$mmbaseurl .= "&gallery=".$_GET['gallery'];
			$folder = str_replace("/","",$_GET['gallery'])."/";
		}
		
	    list($action,$arg) = explode("-",$_POST['action'],2);
	    if (!isset($_POST['file'])) return 2;
        foreach($_POST['file'] as $file=>$v){
		    list($type,$name) = explode("-",$file,2);
		    if ($action=='atg' && $type=='images'){
                copy( ABS_PATH.IMAGES_DIR.$folder.$name, ABS_PATH.IMAGES_DIR.$arg.'/'.$name);
				$this->smarty->assign('success', 2);				
	        }
	    }
		return 2;
    }
	
}

admin_addpanelaction('uploader', 'mediamanager', true);


?>
