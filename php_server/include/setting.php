<?php
	function getIp(){
		if(!empty($_SERVER['HTTP_CLIENT_IP'])){
			//ip from share internet
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		}elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
			//ip pass from proxy
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}else{
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}

	if(strpos(getIp(), "192.168") > -1)
		define("absolute", "http://192.168.219.17/speedchat/public/");
	else
		define("absolute", "http://localhost/speedchat/public/");

    define('DBHOST', 'localhost');
	define('DBUSER', 'root');
	define('DBPASS', '');
    define('DBNAME', 'speed_chat');
    define('DBCHARSET', 'utf8mb4'); 
	
	define('max_unsigned_small_int', 65535);
	
	define('max_unsigned_medium_int', 4294967295);

	define('max_unsigned_int', 4294967295); // use to model classes properties | exm => id(max)
	
	define('max_long_text', 4294967295);
	
	define('max_unsigned_big_int', 18446744073709551615);
	
	define('socket_token', 'a53a18eb1fc7e82cece263b0e4265922c3e34511');
	
?>