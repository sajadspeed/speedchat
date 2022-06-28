<?php
	include "../../__php__.php";
	include "include/initialize.php";
	include "include/authentication_socket.php";
	include "model/Online.php";
	
	$input = input_get($_GET, ['user_id']);
	
	$online = new Online($con);
	
	$get = $online->get('*', 0, "`user_id`={$input['user_id']}");
	
	if($get === false)
		json_message(1, ['online'=>0]);
		
	json_message(1, ['info'=>$get, 'online'=>1]);
	
?>