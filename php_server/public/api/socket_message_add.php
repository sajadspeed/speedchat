<?php
	include "../../__php__.php";
	include "include/initialize.php";
	include "include/authentication_socket.php";
	include "model/Message.php";
	
	$input = $_GET;
	
	unset($input['token']);
	
	$message = new Message($con);
	
	$add = $message->add(["user_id"=>$input['to'], "message"=>json_encode($input)]);
	
	if($add === true)
		json_message(1);
	json_message(0);
	
?>
