<?php
/*
    Flatpress Ajax
    It recives OP and arguments, and return the result
    Created by Francisco Arocas
    Website: franciscoarocas.com
*/

require_once ('defaults.php');
require_once (INCLUDES_DIR.'includes.php');
system_init();

// It contents all Operations and code
$ajaxFunctions = [];

// It contents if Operation is and admin operation or not
// If is and admin, user need to be logged
// True if needs admin, false if not
$ajaxIsAdminOrNot = [];

/* Functions to add Flarpress Ajax operations */

function addAjaxOperation($operationName, $isAdminOrNot, $operationFunction) {
    global $ajaxFunctions, $ajaxIsAdminOrNot;
    if(array_key_exists($operationName, $ajaxFunctions)) {
        throw new Exception('Error, AjaxOperationName exists.');
    }

    $ajaxFunctions[$operationName] = $operationFunction;
    $ajaxIsAdminOrNot[$operationName] = $isAdminOrNot;
}

function addAdminAjaxOperation($operationName, $operationFunction) {
    addAjaxOperation($operationName, true, $operationFunction);
}

function addAdminNoAjaxOperation($operationName, $operationFunction) {
    addAjaxOperation($operationName, false, $operationFunction);
}

define('AJAXERROR', false);
define('AJAXSUCCESS', true);

function returnJSONValue($resultValue, $resultContent) {
    $Value = new stdClass;
    $Value->result = $resultValue;
    $Value->content = $resultContent;
    echo json_encode($Value);
    die();
}

/* Include all Admin Ajax operations */

require_once(ADMIN_DIR . 'adminAjaxOperations.php');

foreach($AjaxFunctionMap as $currentName => $currentFunction) {
    addAdminAjaxOperation($currentName, $currentFunction);
}

/* Recibe $POST to access ajax Function */

$_POST['Operation'] = 'Hello World'; // Test
$_POST['Arguments'] = 'BLa bLa bLa'; // Test

if(isset($_POST)) {

    if(!isset($_POST['Operation'])) {
        returnJSONValue(AJAXERROR, 'Flatpress Ajax needs operation');
    }
    
    if(!array_key_exists($_POST['Operation'], $ajaxFunctions)) {
        returnJSONValue(AJAXERROR, 'Flatpress Ajax Operation doesnt not exist');
    }

    //Check if this OP needs admin permissions. If true, check users permissions.
    if($ajaxIsAdminOrNot[$_POST['Operation']]) {
        if(!user_loggedin()) {
            returnJSONValue(AJAXERROR, 'Forbidden. You dont have admin permissions');
        }
    }

    try {
        if(!isset($_POST['Arguments'])) {
            $result = $ajaxFunctions[$_POST['Operation']]();
        } else {
            $result = $ajaxFunctions[$_POST['Operation']]($_POST['Arguments']);
        }
        returnJSONValue(AJAXSUCCESS, $result);
    } catch(Exception $e) {
        returnJSONValue(AJAXERROR, $e->getMessage());
    }
}
?>

