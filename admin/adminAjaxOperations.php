<?php
/* An admin AJAX Functions */

$AjaxFunctionMap = [];

$AjaxFunctionListMediaDirectory = function($route) { /* Just for testing */
    $newRoute = FP_CONTENT . $route;
    $dirContent = scandir($newRoute);
    if(!$dirContent) {
        throw new Exception('Error when trying to access the folder'.$newRoute);
    }
    $result = [];
    for($i = 2; $i < sizeof($dirContent); ++$i) { // Result = [[dir1, true], [file1, false], [file2, false]] ...
        array_push($result, []);
        array_push($result[$i - 2], $dirContent[$i]);
        array_push($result[$i - 2], is_dir($newRoute . $dirContent[$i])); // True if is dir, false is not
    }
    return $result;
};

$AjaxFunctionMap['ListMediaDirectory'] = $AjaxFunctionListMediaDirectory;

?>