<?php
	include "../../__php__.php";
	include "include/initialize.php";
	include "include/authentication.php";
	include "model/Contact.php";
	
	$input = input_get($_GET, ['user_id_target']);
	
	$contact = new Contact($con);
	
	$add = $contact->add(["user_id_target"=>$input['user_id_target'], "user_id"=>$userInfo['id']]);
	
	if($add === true)
		json_message(1);
		
	json_message(0, $add);
?>
