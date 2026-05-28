<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- Class: RULE
class rule {

	public $SQL_table = 'rule';
	public $SQL_table_template = 'rule_template';
	public $SQL_table_signature = 'rule_signature';
	public $SQL_table_sequence = 'rule_sequence';
	public $SQL_table_rule_auto = 'rule_auto';

	function __construct($config=[]) {
		global $db;
		$this->db = $db;
		$this->config = new stdClass();

		$this->config->type = [
			1	=>	'Standard',
			2	=>	'Master'
		];
		$this->config->trigger_options = [
			'sale_new'			=>	['name_be'=>'Sale New', 'default_template_category'=>'Sale New'],
			'sale_paid'			=>	['name_be'=>'Sale Paid', 'default_template_category'=>'Sale Paid'],
			'sale_abandoned'	=>	['name_be'=>'Sale Abandoned', 'default_template_category'=>'Sale Abandoned'],
			'book_new'			=>	['name_be'=>'Booking New', 'default_template_category'=>'Book New'],
			'book_confirmed'	=>	['name_be'=>'Booking Confirmed', 'default_template_category'=>'Book Confirmed'],
		];
	}
	function rule_data($config=array()) {
		global $class_user;
		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);
		if($config['client_id']!=NULL) {
			$sql_config['where'][] = "client_id = '".$config['client_id']."'";
		}
		if($config['token']!=NULL) {
			$sql_config['where'][] = "token = '".$config['token']."'";
			$config['first'] = true;
		}
		if($config['status']!=NULL || $config['status']=='0') {
			$sql_config['where'][] = "status = '".$config['status']."'";
		}
		if($config['first']) {
			$sql_config['first'] = true;
		}
		$sql_config['sort'] = 'object ASC, status DESC';
		if(!$config['ovr_user_id']) {
			$sql_config['where'][] = "user_id = '".$class_user->authorised->id."'";
		}
		if($config['ovr_user_id'] && $config['user_id']>0) {
			$sql_config['where'][] = "user_id = '".$config['user_id']."'";
		}
		return zulu::table_data($this->SQL_table,$id,$sql_config);
	}
	function delete($id,$identifier='id',$disable=false) {
		global $class_user;
		if($disable) {
			$query = "UPDATE ".$this->SQL_table." SET status = 0 WHERE `{$identifier}` = '".$id."' AND user_id='".$class_user->authorised->id."'";
		} else {
			$query = "DELETE FROM ".$this->SQL_table." WHERE `{$identifier}` = '".$id."' AND user_id='".$class_user->authorised->id."'";
		}
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
	function rule_next($id) {
		$data = $this->rule_data(array('id'=>$id));

		$last = $data['frequency_last'];
		$last_next = $last+($data['frequency']*86400);

		if($last_next<time()) {
			$last_next = time();
		}

		return $last_next;
	}

	function last($id) {
		$data = $this->rule_data(array('id'=>$id));
		return $data['frequency_last'];
	}
	function set_frequency_last($id,$timestamp) {
		global $class_user;
		$this->db->query("UPDATE rule SET frequency_last = '$timestamp', frequency_count = frequency_count+1 WHERE id = ".$id." AND user_id='".$class_user->authorised->id."'");
		return true;
	}
	function sequence_data($config=array()) {
		global $class_user;
		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);
		if(isset($config['parent_id'])) {
			$sql_config['where'][] = "parent_id = '".$config['parent_id']."'";
		}
		if($config['token']!=NULL) {
			$sql_config['where'][] = "token = '".$config['token']."'";
			$config['first'] = true;
		}
		if($config['status']!=NULL || $config['status']=='0') {
			$sql_config['where'][] = "status = '".$config['status']."'";
		}
		if($config['first']) {
			$sql_config['first'] = true;
		}
		$sql_config['sort'] = 'sort ASC, id ASC, status DESC';
		$sql_config['where'][] = "user_id = '".$class_user->authorised->id."'";
		return zulu::table_data($this->SQL_table_sequence,$id,$sql_config);
	}
	function sequence_new($config=array()) {
		global $class_user;
		$data['user_id'] = $class_user->authorised->id;
		$data['stat_add'] = time();
		$data['token'] = zulu::serial();

		$query = "INSERT INTO ".$this->SQL_table_sequence." ".$this->db->build(2,array('token','stat_add','user_id'),$data);

		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$this->db->insert_id);
		} else {
			return array("success"=>false,"id"=>0);
		}
	}
	function sequence_edit($id,$config=array()) {
		global $class_user;
		if($id<1) {
			$data = $this->sequence_new();
			$id = $data['id'];
		}

		foreach($config as $key=>$val) {
			$data[$key] = $val;
			$fields[] = $key;
		}

		$query = "UPDATE ".$this->SQL_table_sequence." SET ".$this->db->build(1,$fields,$data)." WHERE id = '{$id}' AND user_id='".$class_user->authorised->id."'";

		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$id);
		} else {
			return array("success"=>false);
		}
	}
	function template_delete($id,$identifier='id',$disable=false) {
		global $class_user;

		$query = "DELETE FROM ".$this->SQL_table_template." WHERE `{$identifier}` = '".$id."' AND user_id='".$class_user->authorised->id."'";

		if($this->db->query($query)) {
			return true;
		} else {
			return false;
		}
	}
	function template_data($config=array()) {
		global $class_user,$zulu;
		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);
		if($config['category']!=NULL) {
			$sql_config['where'][] = "category = '".$config['category']."'";
		}
		if(count($config['exclude'])>0) {
			$sql_config['where'][] = "id NOT IN(".implode(',',$config['exclude']).")";
		}
		if($config['first']) {
			$sql_config['first'] = true;
		}
		if($config['group']) {
			$sql_config['group'] = "category";
			$sql_config['field'] = ['*','COUNT(id) AS var_count'];
		}
		$sql_config['sort'] = "sort ASC";
		if($config['user_id']>0) {
			$sql_config['where'][] = "user_id = '".$config['user_id']."'";
		} else {
			$sql_config['where'][] = "user_id = '".$class_user->authorised->id."'";
		}
		$data = $zulu->table_data($this->SQL_table_template,$id,$sql_config);
		if($config['unique']) {
			foreach($data as $data_row) {
				if(in_array($data_row['category'],$cache)) {
					continue;
				}
				$data_keep[] = $data_row;
				$cache[] = $data_row['category'];
			}
			return $data_keep;
		} else {
			return $data;
		}
	}

	function template_new($config=array()) {
		global $class_user;
		$data['user_id'] = $class_user->authorised->id;
		$data['stat_add'] = time();

		$query = "INSERT INTO ".$this->SQL_table_template." ".$this->db->build(2,array('stat_add','user_id'),$data);

		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$this->db->insert_id);
		} else {
			return array("success"=>false,"id"=>0);
		}
	}
	function template_edit($id,$config=array()) {
		global $class_user;
		if($id<1) {
			$data = $this->template_new();
			$id = $data['id'];
		}

		foreach($config as $key=>$val) {
			$data[$key] = $val;
			$fields[] = $key;
		}

		$data['stat_update'] = time();
		$fields[] = 'stat_update';

		$query = "UPDATE ".$this->SQL_table_template." SET ".$this->db->build(1,$fields,$data)." WHERE id = '{$id}' AND user_id='".$class_user->authorised->id."'";

		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$id);
		} else {
			return array("success"=>false);
		}
	}

	function signature_data($config=array()) {
		global $class_user;
		/*$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);

		$sql_config['first'] = true;
		if($config['user_id']!=NULL) {
			$sql_config['where'][] = "user_id = '".$config['user_id']."'";
		} else {
			$sql_config['where'][] = "user_id = '".$class_user->authorised->id."'";
		}*/
		if($config['user_id']!=NULL) {
			$user_id = $config['user_id'];
		} elseif($class_user->authorised->child_id>0) {
			$user_id = $class_user->authorised->child_id;
		} else {
			$user_id = $class_user->authorised->id;
		}
		$user_meta = $class_user->user_meta($user_id);
		$signature['content'] = stripslashes($user_meta['email_signature']['value']);
		return $signature;
	}

	function signature_new($config=array()) {
		global $class_user;
		$data['user_id'] = $class_user->authorised->id;
		$data['stat_update'] = time();

		$query = "INSERT INTO ".$this->SQL_table_signature." ".$this->db->build(2,array('stat_update','user_id'),$data);

		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$this->db->insert_id);
		} else {
			return array("success"=>false,"id"=>0);
		}
	}
	function signature_edit($id,$config=array()) {
		global $class_user;
		if($id<1) {
			$data = $this->signature_new();
			$id = $data['id'];
		}

		foreach($config as $key=>$val) {
			$data[$key] = $val;
			$fields[] = $key;
		}

		$data['stat_update'] = time();
		$fields[] = 'stat_update';

		$query = "UPDATE ".$this->SQL_table_signature." SET ".$this->db->build(1,$fields,$data)." WHERE user_id='".$class_user->authorised->id."'";

		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$id);
		} else {
			return array("success"=>false);
		}
	}
	function rule_build_message($row,$client,$config=[]) {
		global $class_setting,$class_user;

		$tp_excl_count = 0;
		if(trim($row['template_exclusion'])!=NULL) {
			$tp_excl = explode(',',$row['template_exclusion']);
			$tp_excl_count = count($tp_excl);
		}
		$return_array = [];
		if($row['sequence_id']<=0) {
			$td_filter = [];
			if(count($tp_excl)>0) {
				$td_filter['exclude'] = $tp_excl;
			}
			if($row['template_user_id']>0) {
				$td_filter['user_id'] = $row['template_user_id'];
			}
			//if($row['template']=='_default'&&$row['template_trigger_id']>0) {
			//	$rule_auto_data = $this->rule_auto_data(['id'=>$row['template_trigger_id'],'ovr_user_id'=>true]);
			//	print_r($rule_auto_data);exit;
			//}
			$template_data = $this->template_data(array('category'=>$row['template'])+$td_filter);
		}
		$setting_data = $class_setting->setting_data();

		if($row['sequence_id']>0) {
			$sequence_rules = $this->sequence_data(['parent_id'=>$row['sequence_id']]);

			if($row['sequence_current_rule_id']==0) {
				$sequence_rule_id = $sequence_rules[0]['id'];
				$return_array['sequence_current_rule_id'] = $sequence_rules[0]['id'];
			} else {
				$sequence_rule_id = $row['sequence_current_rule_id'];
			}
			$this_sequence_rule = $this->sequence_data(['id'=>$sequence_rule_id]);
			$row_cache = $row;
			$row = $this_sequence_rule;
			$template_data = $this->template_data(array('category'=>$row['template']));
			$is_sequence = true;

			$row['frequency_last'] = $row_cache['frequency_last'];
			$row['loop_count'] = $row_cache['loop_count'];
			$row['sequential_count'] = $row_cache['sequential_count'];

			$frequency_time = 86400*$row['frequency'];
			$frequency_next = $row['frequency_last']+$frequency_time;
			if(time()<=$frequency_next) {
				return false;
			}
		}
		if($row['sequential']>0) {
			$seq_count = $row['sequential_count'];
			$template_select = $seq_count;
			$seq_count++;

			if(!isset($template_data[$template_select])) {
				$template_select = 0;
				$seq_count = 1;
			}

			if(!isset($template_data[$seq_count])) {
				$return_array['sequential_end'] = true;
				$return_array['sequential_count'] = 0;
				//echo '<br>sequence end';

				if($is_sequence) {
					$seq_i = 1;
					foreach($sequence_rules as $seqrule_index=>$seqrule) {
						if($seqrule['id']==$sequence_rule_id) {
							$current_sequence_index = $seqrule_index;
						}
						if(count($sequence_rules)==$seq_i) {
							$last_sequence_index = $seqrule_index;
						}
						$seq_i++;
					}
					if(!isset($sequence_rules[($current_sequence_index+1)])) {
						$return_array['status'] = 0;
						$return_array['sequence_current_rule_id'] = 0;
						//echo '<br>no future seq exists end';
					} else {
						$return_array['sequence_current_rule_id'] = $sequence_rules[($current_sequence_index+1)]['id'];
						//echo '<br>future seq exists set to '.$sequence_rules[($current_sequence_index+1)]['id'];
					}
				}
			} else {
				$return_array['sequential_count'] = $seq_count;
				//echo '<br>sequence cont';
			}
		} else {
			$template_count = count($template_data);
			$template_select = rand(0,($template_count-1));
		}
		//-- not sequential & loop max
		if($row_cache['sequence_id']>0) {
			if($row['sequential']==1) {
				if(!isset($template_data[$seq_count])) {
					$return_array['loop_count'] = $row_cache['loop_count']+1;
					if($return_array['loop_count']==$row['loop_max']&&$last_sequence_index==$current_sequence_index) {
						$return_array['status'] = 0;
					}
				}
			} else {
				$return_array['loop_count'] = $row_cache['loop_count']+1;
				if($return_array['loop_count']==$row['loop_max']) {
					$return_array['status'] = 0;
				}
			}
		}

		if(trim($row['object'])==NULL) {
			$row['object'] = stripslashes($row_cache['object']);
		}
		if($config['child']) {
			$row['user_id'] = $class_user->authorised->child_id;
		}

		$MESSAGE_subject = stripslashes($template_data[$template_select]['subject']);
		$MESSAGE_body = stripslashes($template_data[$template_select]['content']);
		$MESSAGE_body .= "<p>".stripslashes($row['custom_text'])."<p>";
		if($config['signature']) {
			$MESSAGE_body .= $class_user->email_signature($row['user_id']);
		}

		$MESSAGE_body = str_replace('[name_first]',$client['name_first'],$MESSAGE_body);
		$MESSAGE_body = str_replace('[name_last]',$client['name_last'],$MESSAGE_body);
		$MESSAGE_body = str_replace('[name]',$client['name'],$MESSAGE_body);
		$MESSAGE_body = str_replace('[company]',$client['company'],$MESSAGE_body);
		$MESSAGE_body = str_replace('[object]',$row['object'],$MESSAGE_body);
		$MESSAGE_body = str_replace('[object2]',$row['object2'],$MESSAGE_body);

		$MESSAGE_subject = str_replace('[name_first]',$client['name_first'],$MESSAGE_subject);
		$MESSAGE_subject = str_replace('[name_last]',$client['name_last'],$MESSAGE_subject);
		$MESSAGE_subject = str_replace('[name]',$client['name'],$MESSAGE_subject);
		$MESSAGE_subject = str_replace('[company]',$client['company'],$MESSAGE_subject);
		$MESSAGE_subject = str_replace('[object]',$row['object'],$MESSAGE_subject);
		$MESSAGE_subject = str_replace('[object2]',$row['object2'],$MESSAGE_subject);

		return ['message'=>$MESSAGE_body,'subject'=>$MESSAGE_subject]+$return_array;
	}

	function rule_auto_data($config=array()) {
		global $class_user;
		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);

		if($config['token']!=NULL) {
			$sql_config['where'][] = "token = '".$config['token']."'";
			$config['first'] = true;
		}
		if($config['status']!=NULL || $config['status']=='0') {
			$sql_config['where'][] = "status = '".$config['status']."'";
		}
		if($config['trigger_key']!=NULL) {
			$sql_config['where'][] = "trigger_key = '".$config['trigger_key']."'";
		}
		if($config['first']) {
			$sql_config['first'] = true;
		}
		$sql_config['sort'] = 'status DESC';
		if(!$config['ovr_user_id']) {
			$sql_config['where'][] = "user_id = '".$class_user->authorised->id."'";
		}
		return zulu::table_data($this->SQL_table_rule_auto,$id,$sql_config);
	}
	function rule_auto_delete($id,$identifier='id',$disable=false) {
		global $class_user;
		if($disable) {
			$query = "UPDATE ".$this->SQL_table_rule_auto." SET status = 0 WHERE `{$identifier}` = '".$id."' AND user_id='".$class_user->authorised->id."'";
		} else {
			$query = "DELETE FROM ".$this->SQL_table_rule_auto." WHERE `{$identifier}` = '".$id."' AND user_id='".$class_user->authorised->id."'";
		}
		if($this->db->query($query)) {
			return true;
		} else {
			return false;
		}
	}
	function rule_auto_new($config=array()) {
		global $class_user, $zulu;
		$data['user_id'] = $class_user->authorised->id;
		$data['stat_add'] = time();
		$data['token'] = $zulu->serial();

		$query = "INSERT INTO ".$this->SQL_table_rule_auto." ".$this->db->build(2,array('token','stat_add','user_id'),$data);

		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$this->db->insert_id);
		} else {
			return array("success"=>false,"id"=>0);
		}
	}
	function rule_auto_edit($id,$config=array()) {
		global $class_user;
		if($id<1) {
			$data = $this->rule_auto_new();
			$id = $data['id'];
		}

		foreach($config as $key=>$val) {
			$data[$key] = $val;
			$fields[] = $key;
		}

		$query = "UPDATE ".$this->SQL_table_rule_auto." SET ".$this->db->build(1,$fields,$data)." WHERE id = '{$id}' AND user_id='".$class_user->authorised->id."'";

		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$id);
		} else {
			return array("success"=>false);
		}
	}
	function generate_rule_from_auto($trigger_key, $client_id){
		//Get rules for given trigger
		$rule_auto_data = $this->rule_auto_data(['trigger_key'=>$this->db->escape_string($trigger_key), 'status'=>'1']);
		foreach($rule_auto_data as $rule_auto){
			$tpl_original = $rule_auto['template'];
			if($tpl_original=='_default') {
				$rule_auto['template'] = $this->config->trigger_options[$trigger_key]['default_template_category'];
			}
			$rule_data = [
				'frequency'			=>1,
				'custom_text'		=>$rule_auto['custom_text'],
				'client_id'			=>$client_id,
				'template'			=>$rule_auto['template'],
				'status'			=>'1',
				'sequential'		=>$rule_auto['sequential'],
				'loop_max'			=>1,
				'template_exclusion'	=>$rule_auto['template_exclusion'],
				'template_trigger_id'	=>$rule_auto['id'],
				'template_user_id'		=>($tpl_original=='_default'?6:0),
			];
			if($rule_auto['send_delay_amount']>0 && strlen(trim($rule_auto['send_delay_type']))){
				$last = strtotime("+".$rule_auto['send_delay_amount']." ".$rule_auto['send_delay_type']);
			}else{
				$last = time();
			}
			$rule_data['frequency_last'] = $last-(86400);
			$return[] = $this->rule_edit(0, $rule_data);
		}
		foreach($return as $vals) {
			$return_filter['id'][] = $vals['id'];
		}
		return $return_filter;
	}
}
