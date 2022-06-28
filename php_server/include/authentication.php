<?php
    require_once "model/User.php";

    $user = new User($con);
	
	if(!isset($_GET['token']) || empty($_GET['token']))
		json_message(0, "Authentication fail");
	
	$userInfo = $user->get('`id`'.(isset($extra_fields) ? ", ".$extra_fields : ""), 0, "`token`='{$_GET['token']}'");
	if($userInfo === false)
		json_message(0, "Authentication fail");
?>