<?php
	include "../../__php__.php";
	include "include/initialize.php";	
	include "model/User.php";
	
	$input = input_get(json_decode(file_get_contents("php://input"),true), ["username", "password"]);
	
	if(filter_username($input['username']) == false)
		json_message(0, "نام‌کاربری فقط می‌تواند شامل حروف، اعداد، و کاراکترهای . و _ باشد.");
	
	$user = new User($con);
	
	$userExist = $user->get("`id`", 0, "`username`=?", ["username"=>$input['username']]);

	$userInfo = $user->get("`token`", 0, "`username`=? AND `password`=?", ["username"=>$input['username'], "password"=>$input['password']]);
	
	if($userExist === false){ // Signup
		$token = $user->tokenGenerator();
		
		$add = $user->add(["username"=>$input['username'], "password"=>$input['password'], "token"=>$token]);
		
		if($add === true)
			json_message(1, ['token'=>$token, "new"=>1]);
		else
			json_message(0, "مشکلی در ثبت‌نام پیش آمده است.");
	}
	elseif($userInfo === false)
		json_message(0, "نام کاربری یا پسورد اشتباه می‌باشد. اگر قصد ثبت‌نام دارید این نام کاربری قبلا ثبت شده است، از نام‌کاربری دیگری استفاده کنید.");
	else{ // Login
		json_message(1, ['token'=>$userInfo['token'], "new"=>0]);
	}
	
?>
