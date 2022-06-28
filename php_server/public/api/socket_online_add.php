<?php
	include "../../__php__.php";
	include "include/initialize.php";
	include "include/authentication_socket.php";
	include "model/Online.php";
	
	$input = input_get($_GET, ['user_id', 'socket_id']);
	
	$online = new Online($con);
	
	$online->delete(0, "`user_id`=?", ['user_id'=>$input['user_id']]);
	
	$add = $online->add(["user_id"=>$input['user_id'], "socket_id"=>$input['socket_id']]);
	
	if($add === true)
		json_message(1);
		
	json_message(0);
	
?>