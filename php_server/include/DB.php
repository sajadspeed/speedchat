<?php
    class DB
    {        
        public function __construct()
        {
			$this -> con = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
            $this -> con -> set_charset(DBCHARSET);
            if($this -> con -> connect_errno){
                _log('Failed to connect to MySQL: ' . $this -> con -> connect_error);
                exit;
            }
        }

        public function execute($query, $type = null, $values = null){
			//echo "<br>".$query."<br>";
			//var_dump($values);
            $stmt = $this -> con -> prepare($query);
			if($stmt !== false){
				if($values != null) $stmt -> bind_param($type, ...$values);
				if($stmt -> execute() === false){
					_log(json_encode(["query"=>$query, "values"=>$values]));
					return false;
				}
				else
					return $stmt;
			}
			else{
				_log(json_encode(["query"=>$query, "values"=> $values, "error"=> $this->error()]));
				return false;
			}
        }

        public function select($query, $type = null, $values = null){
            $result = array();
            
            $stmt = $this -> execute($query, $type, $values);
            if($stmt === false)
                return false;
            $select = $stmt -> get_result();
            if($select -> num_rows > 0)
                while ($row = $select->fetch_assoc()) 
                    $result[] = $row;
            else return false;
            return $result;
        }
        
        public function error(){
            return $this -> con -> error;
        }

        public function lastId(){
            return $this -> con -> insert_id;
        }

        public function __destruct(){
            $this -> con -> close();
        }
    }
    
?>