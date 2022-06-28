<?php
	include "../../__php__.php";
	include "include/initialize.php";
	$extra_fields = "`username`, `image`";
	include "include/authentication.php";
	
	$input = input_get($_GET, [], ['id']);
	
	if(isset($input['id'])){
		$userInfo = $user->get("`username`, `image`", $input['id']);
			
		$userInfo['id'] = $input['id'];
	}
		
	if($userInfo === false)
		json_message(0, "مشکل در دریافت اطلاعات");
	
	$userInfo['image'] = url_image_profile($userInfo['image']);
	
	json_message(1, ["info"=>$userInfo]);
	
?>
