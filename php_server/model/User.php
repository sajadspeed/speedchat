<?php
class User extends Table{

	protected $id;
	protected $username;
	protected $password;
	protected $image;
	protected $token;
	
	public function __construct($con)
	{
		$this -> id           = [ 'type'=>'i', 'min'=>1, 'max'=> max_unsigned_int];
		$this -> username     = [ 'type'=>'s', 'min'=>3, 'max'=>50, 'sanitize'=>'strtolower'];
		$this -> password     = [ 'type'=>'s', 'min'=>6, 'max'=>32, 'sanitize'=>'_hash'];
		$this -> image        = [ 'type'=>'i', 'min'=>0, 'max'=>max_unsigned_int, 'default'=>0 ]; 
		$this -> token  	  = [ 'type'=>'s', 'min'=>40, 'max'=>40 ];

		parent::__construct($con);
	}
	
	public function tokenGenerator(){
		while (true) {
			$token = _token();
			if($this->get("`id`", 0, "`token`='$token'") === false)
				return $token;
		}
	}
	
}
?>