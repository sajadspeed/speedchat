<?php
    class Table{

        protected $con;
        protected $params = array('status' => 1, 'params' => array(), 'error' => array(), 'types' => '');
        public $error = null;

        function __construct( $con )
        {
            $this -> con = $con;
			if(property_exists($this, "tableName") == false)
				$this->tableName = strtolower(get_class($this));
        }

        public function add($params){

            $params = $this -> setParams($params);
            if($params['status']){

                $query = "INSERT INTO `{$this->tableName}` ({$this->columns($params)}) VALUES({$this-> questionMarks($params)})";

                if($this -> con -> execute($query, $params['types'], $this -> values($params)) !== false)
					return true;
            }
            else
                return $params['error'];
        }

        public function update($params, $where = "`id` = ?"){
            // Always `id` last
            $params = $this -> checkParams($params, true);

            if($params['status']){
                $query = "UPDATE `{$this->tableName}` SET {$this -> columnsQuestionMarks($params, substr_count($where, '?'))} WHERE $where";

                if($this -> con -> execute($query, $params['types'], $this -> values($params)) === false)
                    return false;
                else
                    return true;
            }
            else
                return $params['error'];
        }

        public function updateCount($counterFields, $id = 0, $where = "`id` = ?"){
            $set = "";
            foreach ($counterFields as $key => $value)
                $set .= "`$key`=`$key`+$value ,";
            $set=trim($set, ',');


            $query = "UPDATE `{$this->tableName}` SET $set WHERE $where";
			
			$types = "i";
			$values = [$id];
			
			if($id == 0){
				$types = null;
				$values = null;
			}
			
            if($this -> con -> execute($query, $types, $values) === false)
                return false;
            else
                return true;
        }

        public function getAll($fields = "*", $where = null, $params = null, $order = '`id` DESC', $limit = null, $offset = null){
            $status = true;

            if($params != null){
                $params = $this -> checkParams($params);
                $status = $params['status'];
            }
            if($limit != null || $offset != null)
            {
                $this->setParams_valid("limit", $limit, 'i', null, false);
                $this->setParams_valid("offset", $offset, 'i', null, false);
                $tmp = $this->getParams();
                $limit = $limit != null ? "LIMIT " . $tmp['params']['limit'] : null;
                $offset = $offset != null ? "OFFSET " . $tmp['params']['offset'] : null;
            }
            $where = $where != null ? "WHERE " . $where : null;

            if($status){

                $query = "SELECT $fields FROM `{$this->tableName}` $where ORDER BY $order $limit $offset ";
                
                
                $values = $params == null ? null : $this -> values($params);
                $types = $params == null ? null : $params['types'];

                $select = $this -> con -> select($query, $types, $values);
				
                if($select !== false)
                    return $select;
            }
            else
                return $params['error'];
			return false;
        }

        public function leftJoinAll($tableJoin, $fieldsThisTable, $fieldsJoinTable, $onFieldThis, $onFieldJoin, $where= null, $types = "", $values=null, $limit=null, $offset=null, $order= null, $extraJoinQuery=null, $extraFieldQuery = null, $customFields = null, $customOn = null, $differentKey=true){
            $where = $where != null ? "WHERE " . $where : null;

            $limit = $limit != null ? "LIMIT " . $limit : null;
            $offset = $offset != null ? "OFFSET " . $offset : null;

            if($order == null) $order = "`tbl1`.`id` DESC";

            if($customFields == null){
                $fieldsTable = "";
                foreach ($fieldsThisTable as $field)
                    $fieldsTable .= "`tbl1`.`$field` ,";
                
                $fieldsTableJoin = "";
                foreach ($fieldsJoinTable as $field){
                    $differentKey = $differentKey ? "as '$tableJoin.$field'" : null;
                    $fieldsTableJoin .= "`tbl2`.`$field` $differentKey,";
                }
                $fields = trim($fieldsTable . $fieldsTableJoin,',');
            } else $fields = $customFields;

            $on = $customOn == null ? "`tbl1`.`$onFieldThis`=`tbl2`.`$onFieldJoin`" : $customOn;

            $query = "SELECT $fields $extraFieldQuery FROM `{$this->tableName}` `tbl1`
                        LEFT JOIN `$tableJoin` `tbl2` ON $on $extraJoinQuery
                        $where ORDER BY $order $limit $offset ";

            $select = $this -> con -> select($query, $types, $values);

            if($select === false)
            {
                return false;
            }
            else return $select;
        }

        public function leftJoin($tableJoin, $fieldsThisTable=null, $fieldsJoinTable=null, $onFieldThis=null, $onFieldJoin=null, $id=0, $extraJoinQuery=null, $extraFieldQuery = null, $customFields = null, $customOn = null, $where = "`tbl1`.id = ?", $params=null, $types="i", $differentKey = true){
            if($customFields == null){
                $fieldsTable = "";
                foreach ($fieldsThisTable as $field)
                    $fieldsTable .= "`tbl1`.`$field` ,";
                
                $fieldsTableJoin = "";
                foreach ($fieldsJoinTable as $field){
                    $differentKey = $differentKey ? "as '$tableJoin.$field'" : null;
                    $fieldsTableJoin .= "`tbl2`.`$field` $differentKey,";
                }

                $fields = trim($fieldsTable . $fieldsTableJoin,',');
            }
            else 
                $fields = $customFields;

            $on = $customOn == null ? "`tbl1`.`$onFieldThis`=`tbl2`.`$onFieldJoin`" : $customOn;
            $query = "SELECT $fields $extraFieldQuery FROM `{$this->tableName}` `tbl1` 
                        LEFT JOIN `$tableJoin` `tbl2` ON $on $extraJoinQuery 
                        WHERE $where";
            if($id != 0)
                $params = array($id);
            $select = $this -> con -> select($query, $types, $params);

            if($select !== false)
                return $select[0];

            return false;
        }

        public function get($fields = "*", $id = 0, $where = '`id` = ? ', $params=null){
            $status = true;
            if($id != 0)
            {
                if($params != null)
                    $params = array_merge(array('id' => $id),$params);
                else
                    $params['id'] = $id;
            }
            if($params != null){
                $params = $this -> checkParams($params);
                $status = $params['status'];
            }
            else $params = $this->getParams();
            $where = "WHERE " . $where;
            if($status){

                $query = "SELECT $fields FROM `{$this->tableName}` $where LIMIT 1";
                
        
                $values = $this -> values($params);
                
                $select = $this -> con -> select($query, $params['types'], $values);
				
                if($select !== false)
					return $select[0];
            }
            $this -> error = $params['error'];
            return false;
        }

        public function delete($id = 0, $where = '`id` = ? ', $params=null){
            if($id != 0)
                $params['id'] = $id;
            $params = $this -> checkParams($params);
            $status = $params['status'];
            $where = "WHERE " . $where;
            if($status){

                $query = "DELETE FROM `{$this->tableName}` $where ";
                
                
                if($this -> con -> execute($query, $params['types'], $this -> values($params)) === false)
                    return false;
                else
                    return true;
            }
            return $params['error'];
        }

        protected function setParams($params){
            $properties = $this -> getProperties();

            foreach ($properties as $prop => $propValue) 
            {
                if (isset($params[$prop])) 
                {
                    # initialize
                    $sanitize = isset($propValue['sanitize']) ? $propValue['sanitize'] : null;
                    $htmlspecialchars = isset($propValue['htmlspecialchars']) ? $propValue['htmlspecialchars'] : true;

                    $validate = isset($propValue['validate']) ? $propValue['validate'] : null;
            
                    # initialize

                    if (!empty($params[$prop]) || (is_numeric($params[$prop]) && $params[$prop] == 0)) 
                    {
                        /* MAIN VALIDATE */
                        $filter = $this -> filter($params[$prop], $propValue['type'], $propValue['min'], $propValue['max'], $validate);

                        if($filter['status'])
                            $this -> setParams_valid($prop, $params[$prop], $propValue['type'], $sanitize, $htmlspecialchars);
                        else
                            $this -> setParams_invalid($prop, $filter['error']);
                        /* MAIN VALIDATE */

                    }
                    elseif (empty($params[$prop]) && isset($propValue['default']))
                        $this -> setParams_valid($prop, $propValue['default'], $propValue['type']);
                    else
                        $this -> setParams_invalid($prop, _g('fill_field'));
                }
                elseif(isset($propValue['default'])){
					$sanitize = null;
					$htmlspecialchars = true;
                    if(isset($propValue['default_check']) && $propValue['default_check']){
                        $sanitize = isset($propValue['sanitize']) ? $propValue['sanitize'] : null;
						$htmlspecialchars = isset($propValue['htmlspecialchars']) ? $propValue['htmlspecialchars'] : true;
                    }
                    $this -> setParams_valid($prop, $propValue['default'], $propValue['type'], $sanitize, $htmlspecialchars);
                }
                else
                    $this -> setParams_invalid($prop, _g('field_invalid'));

            } // foreach
            return $this -> getParams();
        }

        protected function checkParams($params, $extra_validate = false){
            $properties = $this -> getProperties(true);
			
            foreach ($params as $prop => $propValue) 
            {
                if (isset($properties[$prop])) 
                {
                    $currentProp= $properties[$prop];
                    # initialize
                    $sanitize = isset($currentProp['sanitize']) && !isset($currentProp['sanitize_check_disable']) ? $currentProp['sanitize'] : null;

                    $validate = isset($currentProp['validate']) && !isset($currentProp['validate_check_disable']) ? $currentProp['validate'] : null;
                    
                    # initialize

                    if (!empty($properties[$prop]) || (is_numeric($params[$prop]) && $params[$prop] == 0)) 
                    {
                        /* MAIN VALIDATE */
                        $filter = $this -> filter($params[$prop], $currentProp['type'], $currentProp['min'], $currentProp['max'], $validate);

                        if($filter['status'])
                            $this -> setParams_valid($prop, $params[$prop], $currentProp['type'], $sanitize, false);
                        else
                            $this -> setParams_invalid($prop, $filter['error']);
                        /* MAIN VALIDATE */

                    }
                    else
                        $this -> setParams_invalid($$prop, _g('fill_field'));
                }
                else
                    $this -> setParams_invalid($prop, _g('field_invalid'));

            } // foreach
            return $this -> getParams();
        }

        protected function setParams_valid($field, $value, $type, $sanitize = null, $htmlspecialchars = true){
            if($this -> params['status'] != 0)
            {
                $value = trim(strval($value), " ");

                if($htmlspecialchars)
                    $value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML401);

                $value = $this -> con -> con -> real_escape_string( $value );
                if ($sanitize != null)
                    $value = $sanitize($value);
                $this -> params['params'][$field] = $value;
                $this -> params['types'] .= $type;
            }
        }

        protected function setParams_invalid($field, $alert = ''){
            $this -> params['status'] = 0;
            $this -> params['error'][$field] = $alert;
        }

        protected function getParams(){
            $params = $this -> params;
            $this -> params = array('status' => 1, 'params' => array(), 'error' => array(), 'types' => '');
            return $params;
        }

        protected function filter($value, $type, $min, $max, $validate = null)
        {
            $value = trim(strval($value), " ");
            $result = array('status' => true, 'error' => '');
            if($type == 's')
            {
                if(mb_strlen($value) < $min || mb_strlen($value) > $max)
                {
                    $result['status'] = false;
                    $result['error'] .= _g('valid_length') . " ($min, $max)";
                }
            }
            elseif ($value < $min || $value > $max) {
                $result['status'] = false;
                $result['error'] .= _g('valid_length') . " ($min, $max)";
            }
            else
            switch ($type) {
                case 'i':
                    if($value != 0 && filter_var(ltrim(strval($value), '0'), FILTER_VALIDATE_INT) === false)
                    {
                        $result['status'] = false;
                        $result['error'] .= _g('invalid_format_number');
                    }
                    break;
                case 'd':
                    if(!filter_var($value, FILTER_VALIDATE_FLOAT))
                    {
                        $result['status'] = false;
                        $result['error'] .= _g('invalid_format_number');
                    }
                    break;
                case 'b':
                    if(!filter_var($value, FILTER_VALIDATE_BOOLEAN))
                    {
                        $result['status'] = false;
                        $result['error'] .= _g('invalid_format');
                    }
                    break;
            }


            if($validate != null && ($alert = $validate($value)) !== true)
            {
                $result['status'] = false;
                $result['error'] .= $alert != null ? $alert : _g('invalid_format');
            }
            
            return $result;
        }

        public function pages($where=null, $params=null, $limit = 25, $page=1, $count = false){
            $page--;
            if($count === false)
                $count = $this->getCount($where, $params);
            if($count == false){
                return [
                    "offset"=>0,
                    "back"=>false,
                    "next"=>false,
                    "count"=>0,
                    "remainder"=>0
                ];
            }
            $offset = $page * $limit;
            $back = $page == 0 ? false : $page;
            $remainder = $count-($offset+$limit);
            $next = $remainder <=0 ? false : $page+2;
            
            return [
                "offset"=>$offset,
                "back"=>$back,
                "next"=>$next,
                "count"=>$count,
                "remainder"=>$remainder
            ];
        }

        public function getCount($where = null, $params=null, $id = null){
            $status = true;
            if($id != null){
                $where  = "`id`=?";
                $params = ["id"=>$id];
            }
            if($params != null && $where != null){
                $params = $this -> checkParams($params);
                $status = $params['status'];
            }
            if($status){
                $types = null;
                $values = null;            
                if($params != null){
                    $types = $params['types'];
                    $values = $this -> values($params);
                }
                if($where != null)
                    $where = 'WHERE ' . $where;
                $query = "SELECT COUNT(`id`) as 'count' FROM `{$this->tableName}` $where ";
                
                $select = $this -> con -> select($query, $types, $values);
                
                if($select !== false)
					return $select[0]['count'];
            }
            $this -> error = $params['error'];
            return false;
        }

        protected function getProperties($id = false){
            $object = new ReflectionClass(get_class($this));
            $properties = get_class_vars(get_class($this));
            if($id == false) unset($properties['id']);
            unset($properties['tableName']);
            unset($properties['con']);
            unset($properties['params']);
            unset($properties['error']);
            
            foreach ($properties as $propName => $value) {
                $property = $object -> getProperty($propName);
                $property -> setAccessible(true);
                $properties[$propName] = $property -> getValue($this);
            }
            return $properties;
        }
        
        private function columns($params, $sep="`, `"){
            $vars = $params['params'];
            $columns = array_keys( $vars );
            return '`' . join($sep , $columns) . '`';
        }
        private function columnsQuestionMarks($params, $exceptCount = 1, $sep="` = ?, `"){
            $vars = $params['params'];
            $columns = array_keys( $vars );
            for($i=0;$i<$exceptCount;$i++)
                array_pop($columns);
            return '`' . join($sep , $columns) . '` = ?';
        }

        private function values($params){
            $vars = $params['params'];
            return array_values($vars);
        }

        private function questionMarks($params){
            $vars = $params['params'];
            $columns = array_keys( $vars );
            $q = str_repeat(", ?", count($columns));
            return trim($q, ',');
        }
    }
?>