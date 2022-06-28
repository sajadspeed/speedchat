<?php
class Option_Setting extends Table{

	protected $key_option;
	protected $value_option;
	
	public function __construct($con)
	{
		$this -> key_option   = [ 'type'=>'s', 'min'=>3, 'max'=>30];
		$this -> value_option   = [ 'type'=>'s', 'min'=>0, 'max'=>50];

		parent::__construct($con);
	}
	
	public function getOption($key){
		$get = parent::get('`value_option`', 0, "`key_option`='$key'");
		
		return $get !== false ? $get['value_option'] : false;
	}
	
	public function setOption($key, $value){		
		return parent::update(["value_option"=>$value], "`key_option`='$key'");
	}
	
	public function toggleOption($key){
		return $this->con->execute("UPDATE `option_setting` SET `value_option`=IF(`value_option`=1,0,1) WHERE `key_option`='$key'");
	}
	
}
?>