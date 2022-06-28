<?php
	include "../../__php__.php";
	include "include/initialize.php";
	include "include/authentication_socket.php";
	include "model/Online.php";
	
	$input = input_get($_GET, ['socket_id']);
	
	$online = new Online($con);
	
	$delete = $online->delete(0, "`socket_id`='{$input['socket_id']}'");
	
	if($delete === true)
		json_message(1);
		
	json_message(0);
	
?>