<?php
	include "../../__php__.php";
	include "include/initialize.php";
	include "include/authentication_socket.php";
	
	$input = input_get($_GET, ['user_token']);
	
	$get = $user->get('*', 0, "`token`=?", ["token"=>$input['user_token']]);
	
	if($get === false)
		json_message(0);
	
	json_message(1, ["info"=>$get]);
	
?>