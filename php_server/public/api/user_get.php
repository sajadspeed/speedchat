<?php
	include "../../__php__.php";
	include "include/initialize.php";
	include "include/authentication.php";
	include "model/Online.php";
	
	$input = input_get($_GET, [], ['username', 'user_id']);
	
	if(isset($input['user_id'])){
		$online = new Online($con);
		$get = $user ->get('`username`, `image`', $input['user_id']);
		
		if($get === false)
			json_message(0);
		else{
			$get['image'] = url_image_profile($get['image']);
			
			$isOnline = $online->get('`id`', 0, '`user_id`='.$input['user_id']);
			if($isOnline !== false)
				$get['online'] = 1;	
				
			json_message(1, ["info"=>$get]);
		}
	}
	
	$users = $user->getAll('`username`, `image`, `id`', "`id`!={$userInfo['id']} AND `username` LIKE '{$input['username']}%'");
	
	if($users !== false){
		foreach ($users as &$value) {
			$value['image'] = url_image_profile($value['image']);
		}
		json_message(1, ['users'=>$users]);
	}
	
	json_message(0);
?>