<?php
	header('Content-Type: application/json');
	include "../../__php__.php";
	include "include/initialize.php";
	$extra_fields = '`image`';
	include "include/authentication.php";
	include "include/Image.php";
	include "model/Option_Setting.php";
	
	$option_setting = new Option_Setting($con);
	
	$imageName = $option_setting->getOption('profile_image_name');
	
	$image = new Image($_FILES["image"], $imageName, "profile");
	
	$upload = $image->upload();
	
	if($upload === true){
		if($userInfo['image'] > 0)
			unlink(root_public."/upload/profile/{$userInfo['image']}.jpg");
		$user->update(["image"=>$imageName, "id"=>$userInfo['id']]);
		$option_setting->updateCount(["value_option"=>1], 0, "`key_option`='profile_image_name'");
		json_message(1, ["url"=>url_image_profile($imageName, 1)]);
	}
	else
		json_message(0, $upload);
?>
