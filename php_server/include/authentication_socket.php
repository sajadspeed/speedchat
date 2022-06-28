<?php
	require_once "model/User.php";
	
	$user = new User($con);

	if(!isset($_GET['token']) || empty($_GET['token']))
		json_message(0, "Authentication fail");

	if($_GET['token'] != socket_token)
		json_message(0, "Authentication fail");
?>