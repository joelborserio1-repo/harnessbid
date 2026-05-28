<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- Class: wishlist
class wishlist {
	
	public $SQL_table = 'wishlist';
		
	function __construct($config=[]) {
		global $db,$zulu;
		$this->db = $db;
		$this->zulu = $zulu;		
	}
	
	function wishlist_data($config=array()) {
		global $class_user;
		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);
		if($config['client_id']!=NULL) {
			$sql_config['where'][] = "client_id = '".$config['client_id']."'";
		}
		if(isset($config['product_id'])) {
			$sql_config['where'][] = "product_id = '".$config['product_id']."'";
		}
		if($config['first']) {
			$sql_config['first'] = true;
		}
		if(count($config['field'])>0) {
			$sql_config['field'] = $config['field'];
		}
		$sql_config['where'][] = "user_id = '".$class_user->authorised->id."'";
		return zulu::table_data($this->SQL_table,$id,$sql_config);
	}
	
	function wishlist_delete($id,$identifier='id',$client_id=NULL) {
		global $class_user;
		
		$query = "DELETE FROM ".$this->SQL_table." WHERE `{$identifier}` = '".$id."' AND user_id='".$class_user->authorised->id."' ".($client_id!=NULL?" AND client_id='".$client_id."'":NULL)."";
		if($this->db->query($query)) {
			return true;
		} else {
			return false;	
		}
	}
	
	private function wishlist_new($config=array()) {
		global $class_user;
		
		$data['user_id'] = $class_user->authorised->id;
		$data['stat_add'] = time();
		$query = "INSERT INTO ".$this->SQL_table." ".$this->db->build(2,array('user_id','stat_add',),$data);
		
		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$this->db->insert_id);
		} else {
			return array("success"=>false,"id"=>0);
		}
	}
	function wishlist_edit($id,$config=array()) {
		global $class_user;
		
		$new = false;
		if($id<1) {
			$data = $this->wishlist_new();
			$id = $data['id'];
			$new = true;
		}
		
		$config['stat_update'] = time();
		foreach($config as $key=>$val) {
			$data[$key] = $val;
			$fields[] = $key;
		}

		$query = "UPDATE ".$this->SQL_table." SET ".$this->db->build(1,$fields,$data)." WHERE id = '{$id}'";
		
		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$id);
		} else {
			return array("success"=>false);
		}		
	}

    function wishlist_add_url($pid, $config=[]) {
        global $class_product;
        
        $p_url = $class_product->product_url($pid);
        $w_url = $p_url."wishlist-add/";
        
        return $w_url;
    }
    
}