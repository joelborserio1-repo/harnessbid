<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- Class: SETTINGS
class setting {

	public $SQL_table = 'config';

	function __construct($config=[]) {
		global $db,$zulu;
		$this->db = $db;
		$this->zulu = $zulu;

		//Image Root
		$this->image_fold = 'file/user/';
		$this->image_rel = MAIN_rel.$this->image_fold;
		$this->image_path = MAIN_path.$this->image_fold;

		//Defaults
		$this->defaults = new stdClass();
		$this->defaults->required = ["pos_changecalc"=>1,"tax_label"=>"GST","tax_rate"=>15,"taxsetting_data_method"=>0,"country"=>"nz","currency_symbol"=>0,"quote_expiry"=>1,'pos_hardware_print_complete'=>1];
		$this->defaults->currency_code = ['NZD'=>['label'=>'New Zealand Dollars','symbol'=>0],'GBP'=>['label'=>'Great Britain Pound','symbol'=>2],'USD'=>['label'=>'US Dollars','symbol'=>0]];
		$this->defaults->currency_symbol = [0=>'$',1=>'¢',2=>'£'];
		$this->defaults->country = ['nz'=>'New Zealand','au'=>'Australia','uk'=>'United Kingdom','us'=>'United States of America'];

		//Load Settings
		if($_SESSION['zl_setting']['data']==NULL) {
			$this->default_load();
			$data = $this->setting_data();
			$_SESSION['zl_setting']['data'] = $data;
		}
		$this->data = $_SESSION['zl_setting']['data'];

		//-- Any defined settings
		if(isset($this->data['currency_code'])){
			$currency_code = $this->data['currency_code'];
			$currency_data = $this->defaults->currency_code[$this->data['currency_code']];
			$currency_symbol = $this->defaults->currency_symbol[$currency_data['symbol']];
		}else{
			$currency_code = 'NZD';
			$currency_symbol = '$';
		}

		$this->custom_field = unserialize(stripslashes($this->data['custom_field']));

		define('LOCALE_currency_code',$currency_code);
		define('LOCALE_currency_symbol',$currency_symbol);

		//-- FUTURE INCORPORATE TEMPLATE SELECTION INTO SETTINGS
		//define(WEBSITE_template,$settings['ws_template']); ## TEMPORARY DEFAULT
		define('WEBSITE_cb_force_default',(isset($this->data['ws_cb_force'])&&$this->data['ws_cb_force']>0?true:false));
		define('WEBSITE_cb_disable',(isset($this->data['ws_cb_disable'])&&$this->data['ws_cb_disable']>0?true:false));
	}
	function construct($config) {
		//Clear Cache?
		$user_id = ($config['user_id']>0?$config['user_id']:0);
		if($config['cache_clear']) {
			unset($_SESSION['zl_setting']['data']);
		}

		//Load Settings
		if($_SESSION['zl_setting']['data']==NULL&&$user_id<=0) { // For Intitial Sys Load
			$this->default_load();
			$data = $this->setting_data();
			$_SESSION['zl_setting']['data'] = $data;
		} elseif($_SESSION['zl_setting']['data']==NULL&&$user_id>0) { // For Change After Initial Sys Load
			$data = $this->setting_data(['user_id'=>$user_id]);
			$_SESSION['zl_setting']['data'] = $data;
		}
		$this->data = $_SESSION['zl_setting']['data'];

		//-- Any defined settings
		if(isset($this->data['currency_code'])){
			$currency_code = $this->data['currency_code'];
			$currency_data = $this->defaults->currency_code[$this->data['currency_code']];
			$currency_symbol = $this->defaults->currency_symbol[$currency_data['symbol']];
		}else{
			$currency_code = 'NZD';
			$currency_symbol = '$';
		}

		$this->custom_field = unserialize(stripslashes($this->data['custom_field']));

		define('LOCALE_currency_code',$currency_code);
		define('LOCALE_currency_symbol',$currency_symbol);

		//-- FUTURE INCORPORATE TEMPLATE SELECTION INTO SETTINGS
		//define(WEBSITE_template,$settings['ws_template']); ## TEMPORARY DEFAULT
		define('WEBSITE_cb_force_default',($this->data['ws_cb_force']>0?true:false));
		define('WEBSITE_cb_disable',($this->data['ws_cb_disable']>0?true:false));
	}
	function setting_data($config=array()) {
		global $class_user;
		global $class_file;

		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);
		if($config['first']) {
			$sql_config['first'] = true;
		}
		if($config['user_id']>0) {
			$sql_config['where'][] = "user_id = '".$config['user_id']."'";
		} else {
			$sql_config['where'][] = "user_id = '".$class_user->authorised->id."'";
			$config['user_id'] = $class_user->authorised->id;
		}
		if($config['key']!=NULL) {
			$sql_config['where'][] = "field = '".$config['key']."'";
		}
		if($config['key_start']!=NULL) {
			$sql_config['where'][] = "field LIKE '".$config['key_start']."%'";
		}
		$data = $this->zulu->table_data($this->SQL_table,$id,$sql_config);
		$return = array();
		foreach($data as $row) {
			$return[$row['field']] = $row['value'];
		}

		//Extra Compilation
		$return['user_id'] = $config['user_id'];
		$return['tax_method_label'] = ($return['tax_method']>0?"Incl":"Excl");
		$return['tax_method_label_full'] = ($return['tax_method']>0?"Inclusive":"Exclusive");
		if($return['quote_terms_file']!=NULL) {
			$return['quote_terms_file_path'] = $class_file->file_root_rel."../user/".$config['user_id']."/".$return['quote_terms_file'];
		}
		if($return['quote_logo']!=NULL) {
			$return['quote_logo_path'] = $class_file->file_root_rel."../user/".$config['user_id']."/".$return['quote_logo'];
		}
		if($config['set_global']) {
			$this->data = $return;
		}

		return $return;
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
	function setting_delete($field,$config=[]) {
		global $class_user,$zulu;
		$user_id = ($config['user_id']>0?$config['user_id']:$class_user->authorised->id);

		$query = "DELETE FROM ".$this->SQL_table." WHERE field = '".$field."' AND user_id = '".$user_id."'";
		return ($this->db->query($query)?true:false);
	}
	function setting_edit($field,$value,$label=NULL,$config=[]) {
		global $class_user,$zulu;
		$user_id = ($config['user_id']>0?$config['user_id']:$class_user->authorised->id);

		//$result = $this->db->query("SELECT id FROM ".$this->SQL_table." WHERE field = '$field' AND user_id='".$user_id."'");
		//print_r($this->db->num_rows);exit;
		$check_row = $zulu->table_data($this->SQL_table,0,['where'=>["field = '".$field."'","user_id = '".$user_id."'"]]);
		if(count($check_row) > 0) {
			$query = "UPDATE ".$this->SQL_table." SET label = '{$label}', value = '".addslashes($value)."' WHERE field = '$field' AND user_id='".$user_id."'";
		} else {
			$query = "INSERT INTO ".$this->SQL_table." (field,label,value,user_id) VALUES ('{$field}','{$label}','".addslashes($value)."','".$user_id."')";
		}
		$_SESSION['zl_setting']['data'] = NULL; //reset the load of settings
		return ($this->db->query($query)?true:false);
	}
	function image_add($field='image',$config=[]) {
		global $class_user;
		if($_FILES[$field]['tmp_name']!=NULL) {
			$name = $_FILES[$field]['name'];
			if($config['name']!=NULL) {
				$name = $config['name'];
			}
			@mkdir(dirname(__FILE__)."/../../".$this->image_fold.$class_user->authorised->id."/");

			$file_destination = $this->image_path.$class_user->authorised->id."/".$name;
			$fdr = $this->image_rel.$class_user->authorised->id."/".$name;

			if(move_uploaded_file($_FILES[$field]['tmp_name'],$file_destination)) {
				return array("success"=>true,"reason"=>"File uploaded.","url_abs"=>$file_destination,"url"=>$fdr);
			} else {
				return array("success"=>false,"reason"=>"Failed to move file.");
			}
		} else {
			return array("success"=>false,"reason"=>"Empty image upload field.");
		}
	}
	function image_delete($image) {
		global $class_user;
		$file = $this->image_path.$class_user->authorised->id."/".$image;
		@unlink($file);
		$result = $this->setting_edit('image','');
		return true;
	}
	function image_set($image) {
		global $class_user;
		$result = $this->setting_edit('image',$image);
		if($result) {
			return true;
		} else {
			return false;
		}
	}
	function default_load() {
		$data = $this->setting_data();
		foreach($this->defaults->required as $setting=>$val) {
			if(!isset($data[$setting])) {
				$this->setting_edit($setting,$val);
			}
		}
	}
	function currency_symbol($key=0) {
		return $this->defaults->currency_symbol[$key];
	}
	function value_with_prefix($prefix,$tag) {
		$data = $this->data;
		if(isset($data[$prefix.$tag])) {
			return $data[$prefix.$tag];
		} else {
			return false;
		}
	}

	function setting_for_user($user_id=0,$key) {
		global $class_user;
		if(trim($user_id) == NULL || $user_id == 0 || $class_user->authorised->id == $user_id) {
			$user_lookup_id = $class_user->authorised->id;
		} else {
			$user_lookup_id = $user_id;
		}
		$setting_load = $this->setting_data(['user_id'=>$user_lookup_id,'key'=>$key]);
		return $setting_load[$key];
	}
}
