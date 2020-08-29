<?php
/* An admin AJAX Functions */

$AjaxFunctionMap = [];

$AjaxFunctionHelloWorld = function($args) { /* Just for testing */
    returnJSONValue(AJAXSUCCESS, 'Hello World : '.$args[0]);
};

$AjaxFunctionMap['Hello World'] = $AjaxFunctionHelloWorld;

?>