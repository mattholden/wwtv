<?php

abstract class Model3 {

   private $fields = [];
   private $tableName;
   private $primaryKeyField;
   private $primaryIsForeign;
   protected $data;
   protected $cache = [];
   private $loaded = false;
   protected $countWhere = '1 = 1';
   public $id;
   protected $db;

   protected function __construct($table, $pk, $pkIsFk = false) {
   		$this->tableName = ($table);
   		$this->primaryKeyField = ($pk);
   		$this->addInt($pk);
   		$this->primaryIsForeign = $pkIsFk;
   		$this->db = Config::getDatabase();
   }

   public function getPrimaryKeyField() {
   		return $this->primaryKeyField;
   }

   protected function setDatabase($db) {
   		$this->db = $db;
   }

   public static function byID($id) {
        $class = get_called_class();
        $obj = new $class;
        $obj->load($id);
        return $obj;
   }

   protected function getSubObject($idField, $class) {
		if (isset($this->cache[$idField]))
			return $this->cache[$idField];

		$this->cache[$idField] = new $class();
		$this->cache[$idField]->load($this->get($idField));
		return $this->cache[$idField];
   }

   protected function getSubBatch($idField, $class) {
   		$cacheField = "batch_".$idField;
		if (isset($this->cache[$cacheField]))
			return $this->cache[$cacheField];

		$batchObj = new $class();
		$this->cache[$cacheField] = $batchObj->getBatch([$idField],[$this->get($idField)]);
		return $this->cache[$cacheField];
   }

   private function addField($field, $default, $type, $actualType, $api = true, $sanitize = 0, $class = null) {
   		$this->fields[] = [
   			"field" => ($field),
   			"type" => $type,
   			"modelclass" => $class,
   			"format_type" => $actualType,
   			"api" => $api,
            "sanitize" => $sanitize
   		];
   		$this->data[$field] = $default;
   		return $this;
   }

   protected function addInt($field, $default = null, $api = true) {
        return $this->addField($field, $default, "i", "int", $api);
   }

   protected function addForeignKey($field, $class, $api = true, $default = null) {
        return $this->addField($field, $default, "i", "foreignkey", $api, 0, $class);
   }

   protected function addBoolean($field, $default = null, $api = true) {
        return $this->addField($field, $default, "i", "boolean", $api);
   }

   protected function addString($field, $default = null, $sanitize = 1, $api = true) {
   		return $this->addField($field, $default, "s", "string", $api, $sanitize);
   }

   protected function addDate($field, $default = null, $api = true) {
   		return $this->addField($field, $default, "s", "date", $api);
   }

   protected function addTimestamp($field, $default = null, $api = true) {
   		return $this->addField($field, $default, "s", "timestamp", $api);
   }

   protected function addFloat($field, $default = null, $api = true) {
   		return $this->addField($field, $default, "d", "float", $api);
   }

   protected function getField($field) {
   		$f = ($field);
   		foreach ($this->fields as $fld) {
   			if ($f == $fld["field"])
   				return $fld;
   		}
   		return null;
   }

   public function html($column) {
   		return htmlspecialchars($this->get($column));
   }

   public function dequote($column) {
        return dequote($this->get($column));
   }

   public function getObject($column) {

   		if (isset($this->cache[$column]))
   			return $this->cache[$column];

   		$f = $this->getField($column);

   		if ($f == null || $f["format_type"] != "foreignkey" ||
   			$f["modelclass"] == null || $this->get($column) == null)
   			return null;

   		$this->cache[$column] = $f["modelclass"]::byID($this->get($column));
   		return $this->cache[$column];
   }

   public function money($column, $currencySign = "$") {
        $cash = $this->get($column);
        if ($cash === null) return null;
        return ($currencySign === null ? "" : $currencySign).number_format($cash/100, 2);
   }

   public function get($column) {

   		$field = $this->getField($column);

   		if ($field == null)
   			return null;
   		$f = $field["field"];

   		if (!isset($this->data[$f]))
   			return null;

   		$type = $field["format_type"];
   		$data = $this->data[$f];

   		if ($type == "timestamp" && $data != null)
   			return date("F j, Y H:i", strtotime($data));
   		else if ($type == "date" && $data != null)
   			return date("F j, Y", strtotime($data));
   		else if ($type == "string" && $data != null)
   			return ($data);
   		else
   			return $data;
   }

   public function timestamp($field)  {
   		return $this->set($field, date("Y-m-d H:i:s", time()));
   }

   public function set($field, $value) {

   		foreach($this->fields as $f) {
   			if ($f["field"] == ($field)) {
   				if ($f["format_type"] == "boolean")
   					$this->data[($field)] = ($value != null && $value != 0 && $value != false);

   				else if (in_array($f["format_type"], ["date", "timestamp"]) && $value != null) {
					$this->data[$f["field"]] = date("Y-m-d H:i:s", strtotime($value ));
				}
                else if ($f["format_type"] == "int") {
                    $this->data[$f["field"]] = intval($value);
                }
   				else if ($f['sanitize'] == 1)
   					$this->data[($field)] = $this->sanitizeValue($value);
                else
                    $this->data[($field)] = $value;
   				break;
   			}
   		}
   		return $this;
   }

   public function __debugInfo() { 
   		return $this->getArray(); 
   }

   public function id() {
   		return (isset($this->data[$this->primaryKeyField])) ? $this->data[$this->primaryKeyField] : null;
   }

   private function getCommaSeparatedFields() {
   		$commas = "";
   		$first = true;
   		foreach ($this->fields as $field) {
   			if (!$first)
   				$commas .= ", ";

   			$commas .= $field["field"];
   			$first = false;
   		}
   		return $commas;
   }

   public static function getTable() {
   		$class = get_called_class();
		$obj = new $class;
		return $obj->getTableName();
   }

   public function getTableName() {
   		return $this->tableName;
   }

   private function getTypeString($primaryAtEnd = false) {
   		$types = "";

   		if (!$primaryAtEnd) {
			foreach ($this->fields as $field)
				$types .= $field["type"];
		}
		else {
			foreach ($this->fields as $field) {
				if ($field["field"] == $this->primaryKeyField && !$this->primaryIsForeign)
					continue;
				$types .= $field["type"];
			}

			$types .= "i";
		}

   		return $types;
   }

   public function getArray() {
   		$arr = [];
   		foreach ($this->fields as $f) {
   			$arr[$f['field']] = $this->get($f['field']);
   		}
   		return $arr;
   }

   private static function boolean($array, $field) {

   		if (!isset($array[$field]))
   			return 0;

   		$val = $array[$field];

   		if ($val === "on")
   			return 1;

   		if ($val === 0 || $val == "0" || $val === "false" || $val == "" || $val === false)
   			return 0;

   		return 1;

   }

   public function setArray($request) {

		foreach ($this->fields as $field) {
			// Do not allow the primary key to be updated
			if ($field["field"] == $this->primaryKeyField && $this->primaryIsForeign == false)
				continue;

			if ($field["format_type"] == "boolean" && key_exists($field["field"], $request)) {

				$this->data[$field["field"]] = self::boolean($request, $field["field"]);
			}
			else if (in_array($field["format_type"], ["date", "timestamp"]) && key_exists($field["field"], $request)) {
				$this->data[$field["field"]] = ($request[$field['field']] == null) ? null : date("Y-m-d H:i:s", strtotime($request[$field["field"]] ));
			}
			else if (key_exists(($field["field"]), $request)) {

				if ($field["format_type"] == "string" &&
					$request[$field["field"]] == "") {

					$this->data[$field["field"]] = null;
				}
				else {
					$this->data[$field["field"]] = ($field['sanitize'] == 1)
						? $this->sanitizeValue($request[$field["field"]])
						: $request[$field["field"]];
				}
            }
		}

      return $this;
   }

   public function __toString() {
   		return json_encode($this->data);
   }

   public function load($id) {

		$this->id = intval($id);
		$this->data[$this->primaryKeyField] = intval($id);

   		$query = "select " . $this->getCommaSeparatedFields() .
				   " from " . $this->tableName .
				   " where " . $this->primaryKeyField . " = ?;";
        // if (Account::currentUserID() === 1) echo($query);

   		$ps = $this->db->prepare($query);
   		$ps->bind_param("i", $this->id);

   		foreach ($this->fields as $col) {
   		 	$var = $col["field"];
        	$$var = null;
        	$this->data[$var] = &$$var;
    	}

		call_user_func_array([ $ps, 'bind_result' ], $this->data);
		$ps->execute();
		$ps->fetch();

		$ps->close();

   	    if ($this->id() != null && $this->id() == $id)
   	    	$this->loaded = true;

		return $this;
   }

   protected function update() {

		$query = "update " . $this->tableName . " set ";
		$first = true;
		foreach ($this->fields as $field) {
			if ($field["field"] == $this->primaryKeyField && !$this->primaryIsForeign)
				continue;

			if (!$first)
				$query .= ', ';
			$query .= $field['field'];
			$query .= " = ?";
			$first = false;
		}
		$query .= " where " . $this->primaryKeyField . " = ?;";

		$ps = $this->db->prepare($query);

		$types = $this->getTypeString(true);
		$params = [ &$types ];
		foreach ($this->fields as $col) {
			if ($col["field"] != $this->primaryKeyField || $this->primaryIsForeign)
				$params[] = &$this->data[$col["field"]];
		}
		$params[] = &$this->data[$this->primaryKeyField];

		call_user_func_array([ $ps, 'bind_param' ], $params);
		$ps->execute();
		$ps->close();
   	return $this;

   }

   private function sanitizeValue($v) {
   	  if ($v === "") return null;
      return trim(strip_tags(htmlspecialchars_decode($v)));
   }

   private function sanitize() {
      foreach ($this->data as $field=>$val)
         if ($this->fields[$field]['sanitize'] == 1)
            $this->data[$field] = $this->sanitizeValue($val);
      return $this;
   }

   protected function insert() {

		$query = "insert into " . $this->tableName . " (";
		$questions = "";

		$first = true;
		foreach ($this->fields as $field) {
			if ($field["field"] == $this->primaryKeyField && !$this->primaryIsForeign)
				continue;

			if (!$first) {
				$query .= ', ';
				$questions .= ", ";
			}

			$query .= $field['field'];
			$questions .= "?";
			$first = false;
		}

		$query .= ") values (" . $questions . ");";

		$ps = $this->db->prepare($query);

		$types = $this->primaryIsForeign ? $this->getTypeString() : substr($this->getTypeString(true), 0, count($this->fields) - 1);
		$params = [ &$types ];

		foreach ($this->fields as $col) {
			if ($this->primaryIsForeign || $col["field"] != $this->primaryKeyField)
				$params[] = &$this->data[$col["field"]];
		}

		call_user_func_array([ $ps, 'bind_param' ], $params) or die($this->db->getConnection()->error);
		$ps->execute() or die($this->db->getConnection()->error);
		$ps->fetch();
		if (!$this->primaryIsForeign)
			$this->data[$this->primaryKeyField] = $ps->insert_id;
		$ps->close();
		$this->loaded = true;
		$this->id = $this->id();
   		return $this;

   }

   public function save() {
		if ($this->loaded)
			return $this->update();
		else
		return $this->insert();
   }

   public function delete() {
   		$ps = $this->db->prepare('delete from ' . $this->tableName . ' where ' . $this->primaryKeyField . " = ?;");

   		$ps->bind_param("i", $this->data[$this->primaryKeyField]);
   		$ps->execute();
   		$ps->close();

   		foreach ($this->data as $k=>$v) {
   			$this->data[$k] = null;
   		}
	}

	public static function count() {
		$class = get_called_class();
		$obj = new $class;
		return $obj->getCount();
	}

	public function getCount() {

		$query = "select count(" . $this->primaryKeyField . ") from " . $this->tableName . " where " . $this->countWhere . ";";

		$ps = $this->db->prepare($query);
		$ps->bind_result($count);
		$ps->execute();
		$ps->fetch();
		$ps->close();
		return $count;
	}

	public static function where($query, $params = [], $ptypes = "", $order = null) {
		$class = get_called_class();
		$obj = new $class;

		return $obj->_where($query, $params, $ptypes, $order);
	}

	public function _where($query, $params, $ptypes, $order) {
		if ($order === null)
			$order = $this->primaryKeyField;

		if (!is_array($params))
			$params = [ $params ];

		$batch = [];

		$query = "select " . $this->getCommaSeparatedFields(). " from " . $this->tableName . " where " . $query . " order by " . $order . ";";

		$ps = $this->db->prepare($query);

		if ($params !== null && $ptypes !== null && $ptypes != "") {
			$bindParams = [ "" ];
			for ($i = 0; $i < @count($params); $i++) {
				if ($params[$i] !== null) {
					$bindParams[] = &$params[$i];
					$bindParams[0] .= substr($ptypes, $i, 1);
				}
			}
			call_user_func_array([ $ps, 'bind_param'  ], $bindParams);
		}

		foreach ($this->fields as $col) {
			$var = $col["field"];
			$$var = null;
			$this->data[$var] = &$$var;
		}

		call_user_func_array([ $ps, 'bind_result' ], $this->data);

		$ps->execute();

		$className = get_class($this);
		while ($ps->fetch()) {
			$obj = new $className;
			$obj->setArray($this->data);
			$obj->loaded = true;
			$obj->set($this->primaryKeyField, $this->data[$this->primaryKeyField]);
			$obj->id =  $this->data[$this->primaryKeyField];
			$batch[] = $obj;
		}

		$ps->close();
		return $batch;
	}

    protected function setLoaded($id) {
        $this->loaded = true;
        $this->id = $id;
        $this->set($this->primaryKeyField, $id);
        return $this;
    }

    public static function batch($cols = null, $vals = null, $order = null, $ops = null) {
		$class = get_called_class();
		$obj = new $class;
		return $obj->getBatch($cols, $vals, $order, $ops);
	}

	private function getBatch($cols, $vals, $order = null, $ops = null) {

		if ($cols === null)
			$cols = [];
		if ($vals === null)
			$cols = [];

		if ($cols !== null && !is_array($cols))
			$cols = [ $cols ];

		if ($vals !== null && !is_array($vals))
			$vals = [ $vals ];

		if (@count($cols) != @count($vals))
			throw new Exception("Columns and value counts do not match in " . get_class($this));

		$batch = [];

		$query = "select " . $this->getCommaSeparatedFields() .
					   " from " . $this->tableName;

		if (@count($cols) > 0 && @count($vals) > 0 ) {

			if ($ops == null) {
				$ops = [];
				foreach ($vals as $val)
					$ops[] = ($val === null) ? "is" : "=";
			}
			else if (!is_array($ops)) {
				$ops = [ $ops ];
			}

			$query .= " where ";

			$bindParamTypes = "";

			$i = 0;
			foreach ($cols as $keyColumn) {
				$field = $this->getField($keyColumn);
				if ($field == null) continue;

				if ($i > 0)
					$query .= " and ";

				if ($vals[$i] !== null) {
					$bindParamTypes .= $field['type'];
					$query .= "(" . $keyColumn . " " . $ops[$i] . " ?)";
				}
				else {
					$query .= "(" . $keyColumn . " " . $ops[$i] . " null)";
				}
				$i++;
			}
		}

		if ($order == null) {
			$order = $this->primaryKeyField;
		}

		$query .= " order by " . $order . ";";

		$ps = $this->db->prepare($query);

		if (@count($cols) > 0 && @count($vals) > 0 && @count($cols) == @count($vals)) {
			$bindParams = [ $bindParamTypes ];
			for ($i = 0; $i < @count($vals); $i++) {
				if ($vals[$i] !== null) {
					$bindParams[] = &$vals[$i];
				}
			}

			call_user_func_array([ $ps, 'bind_param'  ], $bindParams);
		}

		foreach ($this->fields as $col) {
			$var = $col["field"];
			$$var = null;
			$this->data[$var] = &$$var;
		}

		call_user_func_array([ $ps, 'bind_result' ], $this->data);

		$ps->execute();

		$className = get_class($this);
		while ($ps->fetch()) {
			$obj = new $className;
			$obj->setArray($this->data);
			$obj->loaded = true;
			$obj->set($this->primaryKeyField, $this->data[$this->primaryKeyField]);
			$obj->id =  $this->data[$this->primaryKeyField];
			$batch[] = $obj;
		}

		$ps->close();
		return $batch;
	}


	public function toAPI() {

		$retval = [];
		foreach ($this->fields as $f) {

			if ($f['api'] == true)
				$retval[$f['field']] = $this->get($f['field']);

		}
		return $retval;

	}

	public static function validateAndLoadAPI($idRequestField = 'id', $useridField=null) {
		if (!isset($_REQUEST[$idRequestField]))
			jsonResponseCode(400);

		$id = $_REQUEST[$idRequestField];
		if (!is_numeric($id) || $id < 0)
			jsonResponseCode(400);

		$className = get_called_class();
		$obj = new $className;
		if ($id === 0) return $obj;
		$obj->load(intval($id));

		if ($obj->id() == null)
			jsonResponseCode(404);

		if ($useridField != null) {

			$uid = $obj->get($useridField);
			if (!Account::loggedIn())
				jsonResponseCode(403);

			else if (Account::current()->id() != $uid && !Account::current()->admin()->may('login'))
				jsonResponseCode(401);
		}

		return $obj;
	}

	public static function validateAndLoad($idRequestField = 'id', $useridField = null) {

		if (!isset($_REQUEST[$idRequestField]))
			redirect('confirmation.php?from=400');

		$id = $_REQUEST[$idRequestField];
		if (!is_numeric($id) || $id < 0)
			redirect('confirmation.php?from=400');

		$className = get_called_class();
		$obj = new $className;
		if ($id === 0) return $obj;
		$obj->load(intval($id));

		if ($obj->id() == null)
			redirect('confirmation.php?from=404');

		if ($useridField != null) {

			$uid = $obj->get($useridField);
			if (!Account::loggedIn())
				redirect('confirmation.php?from=403');

			else if (Account::current()->id() != $uid && !Account::current()->admin()->may('login'))
				redirect('confirmation.php?from=401');
		}

		return $obj;
	}

	public static function renderCSV($objects, $filename = "export.csv", $cols = null, $headers = true) {
        if ($headers) {
     		header('Content-type: text/plain');
            header('Content-Disposition: attachment; filename="'.$filename.'"');
        }

        $END = '"';
        $SEP = '","';

        $header = "";
        $hfields = 0;

        if ($cols == null) {
        	$cols = [];
			$class = get_called_class();
        	$dummy = new $class;
			foreach ($dummy->fields as $field) {
				if ($field["api"]) {
					$cols[] = $field["field"];
				}
			}
        }

        foreach ($cols as $col) {
            $header .= ($hfields == 0) ? "" : ",";
            $hfields++;
            $header .= '"' . Tools::dequote($col) . '"';
        }

        $header .= chr(10);
        print($header);

        foreach ($objects as $obj) {
            $row = $END;

            foreach ($cols as $col) {
                if ($row != $END)
                    $row .= $SEP;
                $row .= $obj->get($col);
            }
            $row .= $END;
            $row .= chr(10);
            print($row);
        }
    }

}
