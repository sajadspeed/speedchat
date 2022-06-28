<?php
class Online extends Table{

	protected $id;
	protected $user_id;
	protected $socket_id;
	protected $date_time;
	
	public function __construct($con)
	{
		$this -> id           = [ 'type'=>'i', 'min'=>1, 'max'=> max_unsigned_int];
		$this -> user_id           = [ 'type'=>'i', 'min'=>1, 'max'=> max_unsigned_int];
		$this -> socket_id     = [ 'type'=>'s', 'min'=>20, 'max'=>20];
		$this -> date_time           = [ 'type'=>'i', 'min'=>1, 'max'=> max_unsigned_int, 'default'=>time()];

		parent::__construct($con);
	}
}
?>