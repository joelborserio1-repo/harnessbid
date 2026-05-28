<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- Database
$mysqli = new mysqli(DB_host, DB_user, DB_pass, DB_name);
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

class db {

	public function __construct($db_object, $config=[]) {
        $this->mysqli = $db_object;
		$this->insert_id = 0;
		$this->error = "";
    }

	function query($query) {
		if($result = $this->mysqli->query($query)) {
			$this->current_query = $result;
			$this->insert_id = $this->mysqli->insert_id;
            $this->error = "";
			return true;
		} else {
            $this->error = $this->mysqli->error;
			return false;
		}
	}
	function insert_id() {
		return $this->mysqli->insert_id;
	}
	function escape_string($string='') {
		return $this->mysqli->real_escape_string($string);
	}
	function row_array() {
		return $this->current_query->fetch_array();
	}
	function field_array($arr) {
		foreach($arr as $key=>$val) {
			$data[] = $key;
		}
		return $data;
	}
	function build($type=1,$array,$exclpost=0) {
		global $sql_data_array;

		if($type==1) { //FORMAT: field1 = 'value1', field2 = 'value2'...
			foreach($array as $val) {
				if(is_array($exclpost)) {
					$list .= $com.$val." = '".(is_numeric($exclpost[$val])||$exclpost[$val]>0?$exclpost[$val]:$this->escape_string($exclpost[$val]))."'";
				} elseif($exclpost>0) {
					$list .= $com.$val." = '".(is_numeric($sql_data_array[$val])||$sql_data_array[$val]>0?$sql_data_array[$val]:$this->escape_string($sql_data_array[$val]))."'";
				} else {
					$list .= $com.$val." = '".(is_numeric($_POST[$val])||$_POST[$val]>0?$_POST[$val]:$this->escape_string($_POST[$val]))."'";
				}
				$com = ",";
			}
			$str = $list;
		} else { //FORMAT: (field1,field2,field3) VALUES ('value1','value2','value3')

			foreach($array as $val) { //fields
				$s1 .= $com.$val;
				$com = ",";
			}
			unset($com);

			foreach($array as $val) { //vals
				if($exclpost==1) {
					$s2 .= $com."'".(is_numeric($sql_data_array[$val])||$sql_data_array[$val]>0?$sql_data_array[$val]:$this->escape_string($sql_data_array[$val]))."'";
				} elseif(is_array($exclpost)) {
					$s2 .= $com."'".(is_numeric($exclpost[$val])||$exclpost[$val]>0?$exclpost[$val]:$this->escape_string($exclpost[$val]))."'";
				} else {
					$s2 .= $com."'".(is_numeric($_POST[$val])||$_POST[$val]>0?$_POST[$val]:$this->escape_string($_POST[$val]))."'";
				}
				$com = ",";
			}
			$str = "({$s1}) VALUES ({$s2})";
		}
		return $str;
	}
}

$db = new db($mysqli);
