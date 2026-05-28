<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- Class: MAIL
class rule {
	
	public $SQL_table = 'rule';
		
	function __construct($config=[]) {
		global $zulu;
		$this->zulu = $zulu;
		
	}
	function rule_data($config=array()) {
		global $db,$class_user;
		$this->db = $db;
		
		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);
		if($config['client_id']!=NULL) {
			$sql_config['where'][] = "client_id = '".$config['client_id']."'";
		}
		if($config['status']!=NULL || $config['status']=='0') {
			$sql_config['where'][] = "status = '".$config['status']."'";
		}
		if($config['first']) {
			$sql_config['first'] = true;
		}
		$sql_config['sort'] = 'domain ASC';
		$sql_config['where'][] = "user_id = '".$class_user->authorised->id."'";
		return zulu::table_data($this->SQL_table,$id,$sql_config);
	}
	function delete($id,$identifier='id') {
		global $class_user;
		$query = "DELETE FROM ".$this->SQL_table." WHERE `{$identifier}` = '".$id."' AND user_id='".$class_user->authorised->id."'";
		if($this->db->query($query)) {
			return true;
		} else {
			return false;	
		}
	}
	function rule_new($config=array()) {
		global $class_user;
		$data['user_id'] = $class_user->authorised->id;
		$data['stat_add'] = time();
		$data['token'] = zulu::serial();
		
		$query = "INSERT INTO ".$this->SQL_table." ".$this->db->build(2,array('token','stat_add','user_id'),$data);
		
		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$this->db->insert_id);
		} else {
			return array("success"=>false,"id"=>0);
		}
	}
	function rule_edit($id,$config=array()) {
		global $class_user;
		if($id<1) {
			$data = $this->rule_new();
			$id = $data['id'];
		}
		
		foreach($config as $key=>$val) {
			$data[$key] = $val;
			$fields[] = $key;
		}
		
		$query = "UPDATE ".$this->SQL_table." SET ".$this->db->build(1,$fields,$data)." WHERE id = '{$id}' AND user_id='".$class_user->authorised->id."'";
		
		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$id);
		} else {
			return array("success"=>false);
		}
	}
}
$class_rule = new rule($MAIN_config);