<?php
    function _log($txt){
        $txt = "{\"SCRIPT_FILENAME\" => \"{$_SERVER['SCRIPT_FILENAME']}\",\"REQUEST_URI\" => \"{$_SERVER['REQUEST_URI']}\",\"description\" => \"$txt\", \"time\" => \"".time()."\"}";
        file_put_contents(get_include_path().'/logs.txt', $txt . "\n", FILE_APPEND | LOCK_EX);
    }
	
    function _hash($string, $algo = 'sha1'){
        $salt = "vW~O4;;6Jk&";
        return hash($algo, $string . $salt);
    }
    function _token($string = null){
		if($string == null)
			$string = generateRandomString();
        $string .= time();
        return _hash($string);
    }

    function _isset($array, ...$keys){
        foreach ($keys as $key) 
            if(!isset($array[$key]))
                return false;
        return true;
    }
    function _isset_empty($array, ...$keys){
        foreach ($keys as $key) 
            if(!isset($array[$key]) || empty($array[$key]))
                return false;
        return true;
    }

	function generateRandomString($length = 10) {
		return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
	}
	
    function input_get($array, $keysRequired, $keysOptions = [], $exit = true){
        $result = [];
        foreach ($keysRequired as $key) {
            if(isset($array[$key])){
                $result[$key]=$array[$key];
			}
			else{
				$result = false;
				break;
			}
        }
        if($result === false){
			if($exit)
				json_message(0, "Fill required fields");
			else{
				echo "Fill required fields";
				return false;
			}
		}
		else{
			foreach ($keysOptions as $key) {
				if(isset($array[$key]) && !empty($array[$key])){
					$result[$key]=$array[$key];
				}
			}
			return $result;
		}
    }
	
	function _g($value){
		return $value;
	}
	
	function timePastString($time){
		$secondPast = time()-$time;
		
		$month = intval($secondPast / (86400*30));
		$secondPast = $secondPast % (86400*30);
		
		$day = intval($secondPast / 86400);
		$secondPast = $secondPast % 86400;
		
		$hour = intval($secondPast / 3600);
		$secondPast = $secondPast % 3600;
		
		$minute = intval($secondPast / 60);
		$secondPast = $secondPast % 60;
		
		$second = $secondPast;
		
		if($month > 0)
			return $month . " ماه پیش";
		elseif($day > 0)
			return $day . " روز پیش";
		elseif($hour > 0)
			return $hour . " ساعت پیش";
		elseif($minute > 0)
			return $minute . " دقیقه پیش";
		elseif($second > 0)
			return $second . " ثانیه پیش";
	}
	
	/// Filter and Sanitize
	
	function filter_username($username){
        if(preg_match("/^(?!\.)(?!.*\.$)(?!.*?\.\.)[a-zA-Z0-9_.]+$/", $username))
            return true;
        else 
            return false;
    }
	
	function filter_email($email){
		if(filter_var($email, FILTER_VALIDATE_EMAIL))
			return true;
		else
			return "Email invalid!";
	}
	
    /////
	
	function url_image_profile($image = 0, $absolute= true, $base = "upload/profile/", $type = ".jpg"){
        return ($absolute ? absolute : "") . $base . $image . $type;
    }
	
    function json_message_action($result, $success = null){
        if($result === true)
            exit(json_message(1, $success));
        else
            exit(json_message(0, $result));
    }

    function json_message($status = 1, $other = null){
        if($status <= 0)
            exit(json_encode(array("status"=>$status, "error" => $other)));
        else
            exit(json_encode($other != null ? array_merge(["status"=>$status], $other) : ["status"=>$status]));
    }
	
	function json_show($array){
		exit(json_encode($array));
	}
	
?>
