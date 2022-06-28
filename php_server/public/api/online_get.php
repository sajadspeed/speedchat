<?php
	include "../../__php__.php";
	include "include/initialize.php";
	include "include/authentication.php";
	include "model/Online.php";
	
	// $input = input_get($_GET, [], ['username', 'user_id']);
	
	$online = new Online($con);
	
	$get = $online->getAll();
	
	if($get === false)
		json_message(0);
	json_message(1, ["online_list"=>$get]);
	
?>