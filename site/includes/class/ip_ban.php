<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- Class: IP BAN
class ip_ban {
	
	public $SQL_table = 'ip_ban';
		
	function __construct($config=[]) {
		global $db,$zulu;
		$this->db = $db;
		$this->zulu = $zulu;
		
		$this->types = [
			'global'	=>	'Global',
			'admin'		=>	'Admin',
		];
	}
	function ip_data($config=array()) {
		global $class_user;
		$sql_config = array();
		$id = (isset($config['id']) && $config['id']>0?$config['id']:0);
		if(isset($config['field']) && ($config['field']!=NULL || (is_array($config['field']) && count($config['field'])>0))) {
			$sql_config['field'] = $config['field'];
		}
		if(isset($config['ip']) && $config['ip']!=NULL) {
			$sql_config['where'][] = "ip = '".$config['ip']."'";
		}
		if(isset($config['type']) && $config['type']!=NULL) {
			$sql_config['where'][] = "type = '".$config['type']."'";
		}
		if(isset($config['first']) && $config['first']) {
			$sql_config['first'] = true;
		}
		if(isset($config['limit']) && $config['limit']>0) {
			$sql_config['limit'] = $config['limit'];
		}
		if(isset($config['sort']) && $config['sort']!=NULL) {
			$sql_config['sort'] = $config['sort'];	
		}
		$sql_config['where'][] = "user_id = '".$class_user->authorised->id."'";
		
		if(!isset($sql_config['sort'])) {
			$sql_config['sort'] = 'stat_add DESC';
		}
		
		return $this->zulu->table_data($this->SQL_table,$id,$sql_config);
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
	function ip_new($config=array()) {
		global $class_user;
		$data['user_id'] = $class_user->authorised->id;
		$data['stat_add'] = time();
		
		$query = "INSERT INTO ".$this->SQL_table." ".$this->db->build(2,array('stat_add','user_id'),$data);
		
		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$this->db->insert_id);
		} else {
			return array("success"=>false,"id"=>0);
		}
	}
	function ip_edit($id,$config=array()) {
		global $class_user;
		if($id<1) {
			$data = $this->ip_new();
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
	function valid_access($config) {
		$ip_data = $this->ip_data($config);
		if(count($ip_data) > 0) {
			foreach($ip_data as $row) {
				$this->ip_edit($row['id'],['attempt_count'=>($row['attempt_count']+1),'attempt_last'=>time()]);
			}
			return false;
		}
		return true;
	}
}
$class_ip = new ip_ban($MAIN_config);
