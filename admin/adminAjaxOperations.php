<?php
/* An admin AJAX Functions */

$AjaxFunctionMap = [];

define('ROOT_CONTENT', [
    ['attachs', true],
    ['images', true]
]);

$AjaxFunctionListMediaDirectory = function($route) {
    $newRoute = FP_CONTENT . $route;
    $dirContent = scandir($newRoute);
    if(!$dirContent) {
        throw new Exception('Error when trying to access the folder'.$newRoute);
    }
    $result = [];
    if(!strlen($route)) { // Root. We show only images and attachs dir
        checkIfDirAndAttachsDirExists();
        return ROOT_CONTENT;
    }
    for($i = 2; $i < sizeof($dirContent); ++$i) { // Result = [[dir1, true], [file1, false], [file2, false]] ...
        array_push($result, []);
        array_push($result[$i - 2], $dirContent[$i]);
        array_push($result[$i - 2], is_dir($newRoute . $dirContent[$i])); // True if is dir, false is not
    }
    return $result;
};

function checkIfDirAndAttachsDirExists() {
    if (!file_exists(IMAGES_DIR)) { 
        fs_mkdir(IMAGES_DIR);
    }
    if (!file_exists(ATTACHS_DIR)) { 
        fs_mkdir(ATTACHS_DIR);
    }
}

$AjaxFunctionMap['ListMediaDirectory'] = $AjaxFunctionListMediaDirectory;

?>