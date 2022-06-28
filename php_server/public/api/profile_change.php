<?php
	include "../../__php__.php";
	include "include/initialize.php";
	include "include/authentication.php";
	
	$input = input_get($_GET, [], ['name', 'bio']);
	
	if(count($input) == 0)
		json_message(1);
	
	$update = $user->update(array_merge($input, ["id"=>$userInfo['id']]));
	
	if($update === true)
		json_message(1);
		
	json_message(0, $update);
?>
