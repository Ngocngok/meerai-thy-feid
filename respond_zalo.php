<?php
require_once __DIR__ . '/vendor/autoload.php';

$callbackPageUrl = "https://www.callbackPage.com";
$linkOAGrantPermission2App = $helper->getLoginUrlByPage($callbackPageUrl); // This is url for admin OA grant permission to app

?>