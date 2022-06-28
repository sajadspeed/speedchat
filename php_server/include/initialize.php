<?php
	header('Access-Control-Allow-Headers: Content-Type');
	header('Access-Control-Allow-Methods: PUT, POST, PATCH, DELETE, GET');
	header('Access-Control-Allow-Origin: *');
    require_once "include/setting.php";
    require_once "include/function.php";
    require_once "include/DB.php";
    require_once "model/Table.php";

    // Model Objects

    $con = new DB();
?>