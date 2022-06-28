<?php
	include "../../__php__.php";
	include "include/initialize.php";
	include "include/authentication_socket.php";
	include "model/Message.php";
	
	$input = input_get($_GET, ['user_id']);
	
	$message = new Message($con);
	
	$messages = $message->getAll('*', '`user_id`='.$input['user_id'], null, '`id`');
	
	if($messages !== false){
		$message->delete(0, "`user_id`=?", ["user_id"=>$input['user_id']]);
		json_message(1, ['messages'=>$messages]);
	}
	
	json_message(0);
?>
