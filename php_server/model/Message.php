<?php
class Message extends Table{

	protected $id;
	protected $user_id;
	protected $message;
	
	public function __construct($con)
	{
		$this -> id           = [ 'type'=>'i', 'min'=>1, 'max'=> max_unsigned_int];
		$this -> user_id      = [ 'type'=>'i', 'min'=>1, 'max'=> max_unsigned_int];
		$this -> message      = [ 'type'=>'s', 'min'=>1, 'max'=> max_long_text, 'validate_check_disable'=>true, 'sanitize'=>'stripslashes', 'htmlspecialchars'=>false];

		parent::__construct($con);
	}
	
}
?>