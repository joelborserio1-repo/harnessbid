<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- Class: RENEW
class renew {

	public $SQL_table = 'renew';
	public $SQL_table_log = 'renew_log';
	public $SQL_table_temp = 'renew_template';
	public $SQL_table_credit = 'renew_credit';

	function __construct($config=[]) {
		global $db,$zulu;
		$this->db = $db;
		$this->zulu = $zulu;

        $this->config = new stdClass();
		$this->vars = new stdClass();

		$this->config->renew_generate = [0=>'Nothing',1=>'Sale',2=>'Billable'];
		$this->config->renew_scale = ['d'=>'Day','w'=>'Week','m'=>'Month','y'=>'Year'];
		$this->config->status = [0=>'Inactive',1=>'Valid / Active',3=>'Cancelled'];
		$this->config->renew_model = ['date'=>'Date Based','conc'=>'Concession'];
	}
	function renew_log($id) {
		$sql_config['where'][] = "renew_id = '".$id."'";
		$sql_config['sort'] = "id DESC";
		return zulu::table_data($this->SQL_table_log,0,$sql_config);
	}
	public function renew_log_data($config=array()) {
		global $class_user,$zulu;

		$id = ($config['id']>0?$config['id']:0);
		$sql_config = $zulu->table_data_filter($config);

		return $zulu->table_data($this->SQL_table_log,$id,$sql_config);
	}
	public function renew_log_for_id($renew_id) {
		$filter = [];
		$param['sort'] = "`renew_from` DESC";
		$param['filter']['renew_id'] = $renew_id;

		$rows = $this->renew_log_data($param);

		return (object)['count'=>count($rows),'data'=>$rows];
	}
	public function renew_log_by_period($renew_id,$period_from,$period_end) {
		$filter = [];
		$param['filter']['renew_id'] = $renew_id;
		$param['filter']['renew_from'] = $period_from;
		$param['filter']['renew_to'] = $period_end;
		$param['first'] = true;
		$rows = $this->renew_log_data($param);

		return ($rows['id']>0?$rows['id']:0);
	}
	function renew_log_new($id,$data) {
		global $zulu;

		$data['renew_id'] = $id;
		$query = "INSERT INTO ".$this->SQL_table_log." ".$this->db->build(0,array_keys($data),$data);

		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$this->db->insert_id);
		} else {
			return array("success"=>false,"id"=>0);
		}
	}
	function renew_data($config=array()) {
		global $class_user;
		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);
		if($config['token']!=NULL) {
			$sql_config['where'][] = "r.token = '".$config['token']."'";
			$sql_config['first'] = true;
		}
		if($config['client_id']!=NULL) {
			$sql_config['where'][] = "client_id = '".$config['client_id']."'";
		}
		if(isset($config['template_id'])) {
			$config['template_id'] *= 1;
			switch($config['template_id']) {
				default:
					$sql_config['where'][] = "template_id = '".$config['template_id']."'";
					break;
				case 0:
					//$sql_config['where'][] = "template_id = '".$config['template_id']."'";
					break;
				case -1:
					$sql_config['where'][] = "template_id = '0'";
					break;
			}
		}
		if($config['status']!=NULL || $config['status']=='0') {
			if(is_array($config['status'])) {
				$sql_config['where'][] = "r.status IN(".implode(',',$config['status']).")";
			} else {
				$sql_config['where'][] = "r.status = '".$config['status']."'";
			}
		}
		if($config['renew_next']!=NULL || $config['status']=='0') {
			$sql_config['where'][] = "renew_next < '".$config['renew_next']."'";
		}
		if($config['active']) {
			$sql_config['where'][] = "renew_next > ".time();
			$sql_config['where'][] = "r.status = 1";
		}
		if($config['renew_between']!=NULL) {
			$sql_config['where'][] = "(renew_next >= ".$config['renew_between'][0]." AND renew_next <= ".$config['renew_between'][1].")";
		}
		if($config['pending']) {
			$sql_config['where'][] = "r.status = '0'";
		}
		if($config['cancel_between']!=NULL) {
			$sql_config['where'][] = "(cancel_date >= ".$config['cancel_between'][0]." AND cancel_date <= ".$config['cancel_between'][1].")";
		}
		if($config['add_between']!=NULL) {
			$sql_config['where'][] = "(stat_add >= ".$config['add_between'][0]." AND stat_add <= ".$config['add_between'][1].")";
		}
		if($config['expire']) {
			$sql_config['where'][] = "period_start > 0";
			$sql_config['where'][] = "renew_next < ".time();
			$sql_config['where'][] = "r.status != 3";
		}
		if($config['cancel']) {
			$sql_config['where'][] = "r.status = 3";
		}
		if(isset($config['auto_renew'])) {
			$sql_config['where'][] = "`auto_renew` = '".$config['auto_renew']."'";
		}
		if(isset($config['field'])) {
			$sql_config['field'] = $config['field'];
		}
		if($config['renew']) {
			$sql_config['where'][] = "renew_next > ".time();
			$sql_config['where'][] = "renew_next < ".strtotime("+30 days");
			$sql_config['where'][] = "r.status = 1";
		}
		if($config['first']) {
			$sql_config['first'] = true;
		}
		if($config['sort']!=NULL) {
			$sql_config['sort'] = $config['sort'];
		} else {
			$sql_config['sort'] = 'title ASC, renew_next DESC';
		}
		if($config['user_id'] != 'skip') {
			$sql_config['where'][] = "r.user_id = '".$class_user->authorised->id."'";
		}
		if($config['search']!=NULL) {
			if($sql_config['join']==NULL) {
				$sql_config['join'] = "client AS c ON r.client_id = c.id";
			}
			//(MATCH(c.reference,name_first,name_last,company) AGAINST ('".strtolower($config['search'])."' IN BOOLEAN MODE) OR
			$sql_config['where'][] = "(c.reference LIKE '%".strtolower($config['search'])."%' OR c.name_first LIKE '%".strtolower($config['search'])."%' OR c.name_last LIKE '%".strtolower($config['search'])."%' OR c.company LIKE '%".strtolower($config['search'])."%' OR r.title LIKE '%".strtolower($config['search'])."%' OR r.description LIKE '%".strtolower($config['search'])."%')";
		}
		if($config['print']) {
			$sql_config['print'] = true;
		}
		if($config['pool']) {
			$sql_config['where'][] = "pool = '".$config['pool']."'";
		}
		$sql_config['field'][] = "*";
		$sql_config['field'][] = "r.status AS renew_status";
		$sql_config['field'][] = "r.id AS renew_id";
		$sql_config['field'][] = "r.client_id AS renew_client_id";
		if($config['limit']>0) {
			$sql_config['limit'] = $config['limit'];
		}
		if($config['start']>0) {
			$sql_config['start'] = $config['start'];
		}
		if(isset($config['total']) && $config['total']) {
			$sql_config['field'] = ["COUNT(r.id) AS _total_sum"];
			$sql_config['first'] = true;
		}
		//$sql_config['test'] = true;
		$return_output = zulu::table_data($this->SQL_table." AS r",$id,$sql_config);

		//-- Template merge & vars data
		if($return_output['id']>0 && isset($config['merge']) && $config['merge']) {
			$return_output = $this->template_merge($return_output);
		}

		return $return_output;
	}
	function get($id) {
		global $zulu;

		if($id<=0) {
			$zulu->fatal_error("Subscription error","No subscription id was given to load.");
		}

		$return_output = $this->renew_data(['id'=>$id,'merge'=>true]);

		$this->vars->data = $return_output;
		$this->vars->meta = $zulu->meta_array($this->renew_meta($id));

		if(isset($this->vars->data['_template'])) {
			$this->vars->template = $this->vars->data['_template'];
			$this->vars->template_meta = $this->vars->data['_template_meta'];
		}

		return true;
	}
	function template_merge($row) {
		global $zulu;

		if($row['template_id'] > 0) {
			$template_data = $this->template_data(array('ovr_user_id'=>true,'id'=>$row['template_id']));

			$row['renew_interval'] = $template_data['renew_interval'];
			$row['renew_scale'] = $template_data['renew_scale'];
			$row['renew_action'] = $template_data['renew_action'];
			if($row['price']<=0) {
				$row['price'] = $template_data['price'];
			}
			$row['quantity'] = $template_data['quantity'];
			$row['title'] = $template_data['title'];
			$row['description'] = $template_data['description'];
			if($template_data['product_id']>0) {
				$row['product_id'] = $template_data['product_id'];
			}
			$row['_template'] = $template_data;
			$row['_template_meta'] = $zulu->meta_array($this->template_meta($row['template_id']));
		}
		return $row;
	}
	function delete($id,$identifier='id') {
		global $class_user;

		$data = $this->renew_data([$identifier=>$id,'merge'=>true]);
		if($data['renew_status']==3 || $data['renew_status']==0) {

			$query = "DELETE FROM ".$this->SQL_table." WHERE `{$identifier}` = '".$id."'".($identifier!='token'?" AND user_id='".$class_user->authorised->id."'":null);
			if($this->db->query($query)) {
				return ['success'=>true,'reason'=>''];
			} else {
				return ['success'=>false,'reason'=>'Database error occurred.'];
			}
		} else {
			return ['success'=>false,'reason'=>'The subscription '.$data['title'].' is active and cannot be deleted.'];
		}
	}
	function cancel_undo($id) {
		global $zulu,$class_module;

		$this->get($id);
		$data = $this->vars->data;
		$default_cancel_time = time();

		//-- Module Cancel Check
		if($data['auto_renew_module_id']>0) { //-- Run module function to update renew
			$module_result = NULL;
			$module_payment_row = $class_module->module_data(['id'=>$data['auto_renew_module_id']]);

			if($data['cancel_date']>0 && $data['cancel_date']<=time()) {
				return ['success'=>false,'reason'=>'This subscription is permanently cancelled and cannot be resumed.'];
			}
			if($data['cancel_date']==0) {
				return ['success'=>false,'reason'=>'This subscription is not cancelled.'];
			}
			if($module_payment_row['id']<=0 || $module_payment_row['status']!=1 || $data->cancel_date) {
				$zulu->fatal_error("Module error","The linked payment module to this subscription is inactive / does not exist.");
			}

			//-- Include Module
			$module_payment = $class_module->init($module_payment_row['id']);
			$module_payment->renew_data_set($id);

			if(method_exists($module_payment,'renew_cancel_undo')) {
				$module_result = $module_payment->renew_cancel_undo();
			}
			$result = $module_result;
		} else { //-- Update Subscription to normal
			$update_data = [
				'cancel_date'	=>	0,
				'status'			=>	1,
			];
			$result = $this->renew_edit($id,$update_data);
		}

		return $result;
	}
	function cancel($id) {
		global $zulu,$class_module, $class_client;

		$this->get($id);
		$data = $this->vars->data;
		$default_cancel_time = time();

		//-- Module Cancel Check
		if($data['auto_renew_module_id']>0) { //-- Run module function to update renew
			$module_result = NULL;
			$module_payment_row = $class_module->module_data(['id'=>$data['auto_renew_module_id']]);
			if($module_payment_row['id']<=0 || $module_payment_row['status']!=1) {
				$zulu->fatal_error("Module error","The linked payment module to this subscription is inactive / does not exist.");
			}
			if($data['cancel_date']>0) {
				return ['success'=>false,'reason'=>'This subscription is already cancelled.'];
			}

			//-- Include Module
			$module_payment = $class_module->init($module_payment_row['id']);
			$module_payment->renew_data_set($id);

			if(method_exists($module_payment,'renew_cancel')) {
				$module_result = $module_payment->renew_cancel();
			}
			$result = $module_result;
		} else { //-- Update Subscription to cancelled
			$cancel_policy = $this->vars->renew_template->meta['cancel_policy'];
			//-- No policy OR the next renewal date is before now
			if(trim($cancel_policy)==NULL || $data['renew_next']<time()) {
				$cancel_policy = 'instant';
			}

			//-- Switch policies
			switch($cancel_policy) {
				case 'next':
					$cancel_time = $data['cancel_date'];
					$cancel_status = $data['status'];
					break;
				case 'instant':
					$cancel_time = time();
					$cancel_status = 3;
					break;
			}
			$update_data = [
				'cancel_date'	=>	$cancel_time,
				'status'			=>	$cancel_status,
			];
			$result = $this->renew_edit($id,$update_data);

			if($cancel_status == 3 && $data['client_id'] > 0) {
				$class_client->client_update($data['client_id'], [
					'subscribed'	=>	'0',
				]);
			}

		}

		return $result;
	}
	function template_change($id, $template_id) {
		global $zulu,$class_module;

		$this->get($id);
		$data = $this->vars->data;
		$default_cancel_time = time();

		//-- Module Cancel Check
		if($data['template_id'] != $template_id) { //-- Run module function to update renew
			$module_result = NULL;
			$module_payment_row = $class_module->module_data(['id'=>$data['auto_renew_module_id']]);

			if($data['cancel_date']>0 && $data['cancel_date']<=time()) {
				return ['success'=>false,'reason'=>'This subscription is permanently cancelled and cannot be resumed.'];
			}
			if($module_payment_row['id']<=0 || $module_payment_row['status']!=1 || $data->cancel_date) {
				$zulu->fatal_error("Module error","The linked payment module to this subscription is inactive / does not exist.");
			}

			//-- Include Module
			$module_payment = $class_module->init($module_payment_row['id']);
			$module_payment->renew_data_set($id);

			if(method_exists($module_payment,'renew_template_change')) {
				$module_result = $module_payment->renew_template_change($template_id);
			}

			$result = $module_result;

			//-- update subscription with new template
			if(!$module_result || $result['success']) {
				$upd_data = $this->template_merge(['template_id'=>$template_id]);
				$upd_data['template_id'] = $template_id;
				unset($upd_data['_template'], $upd_data['_template_meta']);
				$this->renew_edit($id, $upd_data);
			}

		}

		return $result;
	}
	function autorenew_disable($id) {
		global $zulu,$class_module;

		$data = $this->renew_data(['id'=>$id]);

		//-- Module Cancel Check
		$module_result = NULL;
		if($data['auto_renew_module_id']>0) {
			$module_payment = $class_module->init($data['auto_renew_module_id']);
			$module_payment->renew_data_set($id);

			if(method_exists($module_payment,'renew_autorenew_disable')) {
				$module_result = $module_payment->renew_autorenew_disable();
			}
		}

		//-- Update Subscription to cancelled
		$update_data = [
			'auto_renew'	=>	0,
		];
		$result = $this->renew_edit($id,$update_data);
		$result['module_result'] = $module_result;

		return $result;
	}
	function autorenew_enable($id) {
		global $zulu,$class_module;

		$data = $this->renew_data(['id'=>$id]);

		//-- Module Cancel Check
		$module_result = NULL;
		if($data['auto_renew_module_id']>0) {
			$module_payment = $class_module->init($data['auto_renew_module_id']);
			$module_payment->renew_data_set($id);

			if(method_exists($module_payment,'renew_autorenew_enable')) {
				$module_result = $module_payment->renew_autorenew_enable();
			}
		}

		//-- Update Subscription to cancelled
		$update_data = [
			'auto_renew'	=>	1,
		];
		$result = $this->renew_edit($id,$update_data);
		$result['module_result'] = $module_result;

		return $result;
	}
	function renew_new($config=array()) {
		global $class_user;
		$data['user_id'] = $class_user->authorised->id;
		$data['stat_add'] = time();
		$data['token'] = zulu::serial();
		$data['renew_first'] = time();

		$query = "INSERT INTO ".$this->SQL_table." ".$this->db->build(2,array('token','renew_first','stat_add','user_id'),$data);

		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$this->db->insert_id);
		} else {
			return array("success"=>false,"id"=>0);
		}
	}
	function renew_edit($id,$post_data=[],$config=[]) {
		global $class_user,$zulu;

		$new = false;
		if($id==0) {
			$data = $this->renew_new();
			$id = $data['id'];
			$new = true;
		}

		$data['stat_update'] = time();
		$fields[] = 'stat_update';

		//-- new?
		if($new) {

			$period_start = $period_end = $trial_start = $trial_expire = 0;

			if($post_data['template_id']>0) { //-- With Template
				$template_data = $this->template_data(['id'=>$post_data['template_id']]);
				$template_meta = $zulu->meta_array($this->template_meta(['id'=>$post_data['template_id']]));
				$rn = explode(",",$template_data['renew_type']);

				$post_data['renew_type'] = (!isset($post_data['renew_type'])?(count($rn)>1?$rn[0]:$template_data['renew_type']):$post_data['renew_type']);
				$post_data['renew_interval'] = $template_data['renew_interval'];
				$post_data['renew_scale'] = $template_data['renew_scale'];


				//-- Set initial parameters
				if($template_data['trial_length']>0 && $template_data['trial_scale']!='') {
					$period_start = $post_data['period_start'];
					$period_end = strtotime('+'.$template_data['trial_length'].' '.zulu::time_scale($template_data['trial_scale'],'si'), $period_start);

					$trial_start = $period_start;
					$trial_expire = $period_end;
				} else {
					$period_start = $post_data['period_start'];
					$period_end = strtotime('+'.$post_data['renew_interval'].' '.zulu::time_scale($post_data['renew_scale'],'si'), $period_start);
				}

			} else { //-- NO Template

				//-- Set initial parameters
				$period_start = $post_data['period_start'];
				$period_end = strtotime('+'.$post_data['renew_interval'].' '.zulu::time_scale($post_data['renew_scale'],'si'), $period_start);

			}

			$renew_next = $period_end;
			if($period_end<time()) {
				$renew_next = time();
			}

			$post_data['trial_start'] = $trial_start;
			$post_data['trial_expire'] = $trial_expire;
			$post_data['trial_count'] = ($trial_start>0?1:0);

			$post_data['period_start'] = $period_start;
			$post_data['period_end'] = $period_end;

			$post_data['renew_next'] = $period_end;

			if(!isset($post_data['renew_type']) || trim($post_data['renew_type'])==NULL) {
				$post_data['renew_type'] = 'date';
			}
		} else {
			//-- unset any attempts to control when editing / updating
			if(isset($config['validate']) && $config['validate']) {
				unset($post_data['renew_next'],$post_data['period_start'],$post_data['period_end']);
			}
		}

		//-- Clean up data
		foreach($post_data as $key=>$val) {
			$data[$key] = $this->db->escape_string($val);
		}

		$query = "UPDATE ".$this->SQL_table." SET ".$this->db->build(1,array_keys($post_data),$data)." WHERE id = '{$id}' AND user_id='".$class_user->authorised->id."'";
		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$id);
		} else {
			return array("success"=>false, "error"=>$this->db->error);
		}
	}
	function renew_create($config) {
		global $class_user;

		if(!isset($config['client_id'])) return ['success'=>false,'reason'=>"A contact must be specified to create a new subscription."];
		if(!isset($config['template_id']) && !isset($config['title'])) return ['success'=>false,'reason'=>"A title or template must be specified to create a new subscription."];

		if($config['template_id']>0) {
			$config = $this->template_merge($config);
			unset($config['_template'],$config['_template_meta']);
		}

		return $this->renew_edit(0,$config);
	}
	function template_data($config=array()) {
		global $class_user;

		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);
		if(is_array($config['id'])) {
			$id = 0;
			$sql_config['where'][] = "id IN(".implode(',',$config['id']).")";
		}
		if($config['product_id']!=NULL) {
			$sql_config['where'][] = "product_id = '".$config['product_id']."'";
		}
		if($config['token']!=NULL) {
			$config['first'] = true;
			$sql_config['where'][] = "token = '".$config['token']."'";
		}
		if(isset($config['field'])) {
			$sql_config['field'] = $config['field'];
		}
		if($config['first']) {
			$sql_config['first'] = true;
		}
		$sql_config['sort'] = 'title ASC';
		if(!$config['ovr_user_id']) {
			$sql_config['where'][] = "user_id = '".$class_user->authorised->id."'";
		}
		return zulu::table_data($this->SQL_table_temp,$id,$sql_config);
	}
	function template_delete($id,$identifier='id') {
		global $class_user;
		$query = "DELETE FROM ".$this->SQL_table_temp." WHERE `{$identifier}` = '".$id."' AND user_id='".$class_user->authorised->id."'";
		if($this->db->query($query)) {
			return true;
		} else {
			return false;
		}
	}
	function template_new($config=array()) {
		global $class_user;
		$data['user_id'] = $class_user->authorised->id;
		$data['stat_add'] = time();
		$data['token'] = zulu::serial();

		$query = "INSERT INTO ".$this->SQL_table_temp." ".$this->db->build(2,array('token','stat_add','user_id'),$data);

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
			if(is_array($val)) {
				$val = implode(",",$val);
			}
			$data[$key] = $val;
			$fields[] = $key;
		}

		$query = "UPDATE ".$this->SQL_table_temp." SET ".$this->db->build(1,$fields,$data)." WHERE id = '{$id}' AND user_id='".$class_user->authorised->id."'";

		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$id);
		} else {
			return array("success"=>false);
		}
	}
	function plan_array() {
		$data = $this->template_data();
		foreach($data as $row) {
			$arr[$row['id']] = $row['title'];
		}
		return $arr;
	}
	function subscription_count($template_id=NULL,$status=NULL) {
		if($status != NULL) {
			$status = ($status==2?'0':'1');
		}
		$qry = array('template_id'=>$template_id,'status'=>$status);
		if($status==NULL || $status=='1') {
			$qry['active'] = true;
		}
		$template_data = $this->renew_data($qry);
		return count($template_data);
	}
	function generate_renewal_order($renew_id,$config=[]) {
		$this->get($renew_id);
		$config['renew_to'] = $this->vars->data['renew_next']+1; //-- set renew next to greater than current, so it runs normally

		$result = $this->process_row($config);
		if($result['success']) {
			return ['success'=>true,'reason'=>(count($result['log'])?implode('<BR>',$result['log']):'Renewal order was generated.')];
		} else {
			return ['success'=>false,'reason'=>(count($result['log'])?implode('<BR>',$result['log']):'Renewal order failed to generate.')];
		}
	}
	function process($template_id=NULL,$config=array()) {
		global $class_sale,$class_product,$class_client,$class_bill,$class_user,$zulu,$class_dps;

		//$renew_to = time();
		$skip_user = $class_user->authorised->id;
		if($config['global']) {
			$skip_user = 'skip';
		}
		if(isset($config['renew_to'])) {
			$gen_renew_to = $zulu->dateEncode($config['renew_to']);
		}
		if($config['id']>0) {
			$qry = ["status"=>1,"id"=>$config['id']];
			$rd = $this->renew_data($qry);
			$renew_data[] = $rd;
		} else {
			$qry = array("status"=>"1","renew_next"=>$gen_renew_to,"template_id"=>($template_id!=NULL?$template_id:NULL),'user_id'=>$skip_user);
			$renew_data = $this->renew_data($qry);
		}

		//-- Loop
		foreach($renew_data as $row) {
			$this->get($row['id']);
			$result = $this->process_row($config);
			$log_data += $result->log;
		}

		return ['success'=>true,'log'=>$log_data];
	}
	function process_row($config=[]) {
		global $class_user,$zulu,$class_client,$class_product,$class_sale,$class_module,$class_bill;

		//-- Setup / load data for process
		$log_output = [];
		$stop_renew = false;

		$row = $this->vars->data;
		$meta = $this->vars->meta;
		$template_data = $this->vars->template_data;
		$template_meta = $this->vars->template_meta;

		//-- Process settings from $config
		$sale_date = date('d/m/y');
		$sale_date_due = date('d/m/y',strtotime("+".$class_sale->DEFAULT_due." days"));
		if($row['client_id']>0) {
			$sale_date_due = $class_sale->date_due_default_unix($row['client_id'],$sale_date);
		}
		if(isset($config['sale_date'])) {
			$sale_date = $config['sale_date'];
		}
		if(isset($config['sale_date_due'])) {
			$sale_date_due = $config['sale_date_due'];
		}

		if($row['id']<=0) $zulu->fatal_error("Subscription error","No data was loaded to process.");

		//-- Default
		$renew_to_limit = ($config['renew_to']>0?$config['renew_to']:time());
		if(!is_numeric($renew_to_limit)) {
			$renew_to_limit = $zulu->dateEncode($renew_to_limit);
		}

		//-- Module Process
		if($row['auto_renew_module_id']>0) {
			$module = $class_module->init($row['auto_renew_module_id']);
			$module->renew_data_set($row['id']);
			if(isset($config['soft_fail']) && $config['soft_fail']) {
				$module->config->soft_fail = true;
			}
			$update_result = $module->renew_update($row['id']);
			$log_output[] = "Auto renew with module ".$module->class_name.".";
			if($update_result->success) {
				return ['success'=>true,'log'=>$log_output];
			} else {
				return ['success'=>false,'log'=>$log_output,'reason'=>$update_result->reason];
			}
		}

		//-- Stop if renew not set
		if($row['renew_next']>$renew_to_limit) {
			$log_output[] = "Date not yet achieved.";
			return ['success'=>false,'log'=>$log_output,'reason'=>"Renew date not yet reached."];
		}

		//-- Standard Process
		$log_data[$row['id']]['loaded'] = true;
		$log_data[$row['id']]['client_name'] = $class_client->client_name($row['client_id']);
		$log_data[$row['id']]['title'] = $row['title'];

		$client_data = $class_client->client_data(['id'=>$row['client_id']]);

		if($row['renew_action']=='e') {
			$row['renew_next'] = $row['renew_next'];

		} else {
			$row['renew_next'] = strtotime('today');
		}
		if(isset($config['renew_next_base']) && $config['renew_next_base']>0) {
			$row['renew_next'] = $config['renew_next_base'];
		}

		if(isset($renew_to) && $renew_to<$row['renew_next']) {
			$log_data[$row['id']]['skip_date'] = true;
			return ['success'=>false,'log'=>$log_output,'reason'=>"This subscription renew to is greater than the max renew date defined."];
		}

		$price_renewal = ($row['price']*$row['quantity']);

        $cycle = (isset($config['cycle']) && $config['cycle']>1?$config['cycle']:1);

		$this_renew_next = $row['renew_next'];
		//$renew_to = strtotime('+'.$row['renew_interval'].' '.zulu::time_scale($row['renew_scale'],'si'), $this_renew_next);

        // renew interval * cycle
		$renew_to = strtotime('+'.($row['renew_interval'] * $cycle).' '.zulu::time_scale($row['renew_scale'],'si'), $this_renew_next);

		//-- Calculate Period
		$period_start = $this_renew_next;
		$period_end = $renew_to;

        $final_renewal_price = $price_renewal * $cycle;
		//-- Renew Save
		$data_renew_key = md5(time()."-".$period_start."-".$period_end);
		$data_renew = array(
			"renew_key"		=>	$data_renew_key,
			"renew_time"	=>	time(),
			"renew_from"	=>	$period_start,
			"renew_to"	=>	$period_end,
			"price"		=>	$final_renewal_price,
		);

		//-- Merge product_id into main setting
		if($row['product_id']>0) {
			$log_data[$row['id']]['product'] = $row['product_id'];
			$data_renew['product_id'] = $row['product_id'];
		}

		//-- Labels output
		$renew_time = "DATE RANGE: ".$zulu->date_time_label($period_start)." to ".$zulu->date_time_label($period_end).PHP_EOL."PERIOD: ".$this->label_frequency();
		$renew_time_short = $zulu->date_time_label($period_start)." to ".$zulu->date_time_label($period_end);

		$log_data[$row['id']]['renew_to'] = $renew_to;
		$serialize_data = ['renew_id'=>$row['id'],'renew_key'=>$data_renew_key,'renew_period'=>[$period_start,$period_end]];

		if($row['opt_bill']==2) {
			$process = array(
				'client_id'		=>	$row['client_id'],
				'title'			=>	$row['title'],
				'price'			=>	$row['price'],
				//'quantity'		=>	$row['renew_interval'],
				'quantity'		=>	$cycle,
				'object'		=> 'renew', //was => 'membership',
				'object_id'		=>	$row['id'],
				'description'	=>	"PERIOD:".$renew_time.PHP_EOL.PHP_EOL.$row['description'],
				'date'			=>	time(),
				'data'			=>	$serialize_data,
				'status'		=>	2
			);
			$log_data[$row['id']]['create'] = 'bill';
		}
		if($row['opt_bill']==1) {
			$filter_row = array(
					'description'	=>	$row['title']." - ".$renew_time_short,
					'price'	=>	$row['price'],
					//'quantity'	=>	$row['quantity'],
					'quantity'	=>	$cycle,
					'product_id' => ($row['product_id']>0?$row['product_id']:$product_data['id']),
					'custom'	=> $serialize_data,
					'object_id' => $row['id'],
					'object'	=> 'renew',
				);

			$filter_row = $class_product->custom_filter($filter_row);
			$filter_row['custom'] = serialize($filter_row['custom']);
			$conc_line_data[] = $filter_row;
			$process = array(
				'client_id'	=>$client_data['id'],
				'name'		=>$client_data['name'],
				'email'		=>$client_data['email'],
				'date'		=>$sale_date,
				'date_due'	=>$sale_date_due,
				'line'	=> $conc_line_data,
				'meta'	=>	[
					'check_period'	=>	$row['id'].'_'.$row['renew_next']
				]
				);
			$log_data[$row['id']]['create'] = 'sale';
		}

		if($row['client_id']>0) {

			//-- Process billing options
			if($row['opt_bill']==2) { // do bill

				$result = $class_bill->update(0,$process);
				$bill_id = $result['id'];

				if($bill_id>0) {
					$data_renew["object_id"] = $bill_id;
					$data_renew["object"] = 'bill';
					$this->vars->link = "<a href=\"".$zulu->link_page('bill',['query'=>['Action'=>'edit','id'=>$bill_id]])."\">View Bill</a>";
				} else {
					return ['success'=>false,'log'=>$log_output,'reason'=>"Error generating billable for renewal."];
				}

				$log_data[$row['id']]['create_id'] = $bill_id;
				$log_output[] = "Renew complete - billable generated (#". $class_sale->reference_link($bill_id) .")";
			}
			if($row['opt_bill']==1) { // do sale

				$meta_du_check = $zulu->table_data('sale_meta',0,['where'=>["field = 'check_period'","value = '".$row['id'].'_'.$row['renew_next']."'"],'first'=>true,'sort'=>'id DESC']);
				if($meta_du_check['id']>0) {
					$du_sale_data = $class_sale->sale_data(['id'=>$meta_du_check['identifier'],'field'=>['status']]);
					if($du_sale_data['status']!=1) {
						unset($meta_du_check); //if the double up sale is not valid, then make new invoice
					}
					$bill_id = $meta_du_check['identifier'];
				}

				if($meta_du_check['id']>0) {
					//-- skip duplc
					$log_data[$row['id']]['skip'] = true;

					$log_output[] = "Renew complete - sale already generated (#". $class_sale->reference_link($bill_id) .")";
					$stop_renew = true;
				} else {
					$result = $class_sale->sale_edit(0,$process,array('complete'=>true));
					$bill_id = $result['id'];

					if($bill_id>0) {
						$this->vars->link = "<a href=\"".$zulu->link_page('sale',['query'=>['Action'=>'edit','Method'=>'View','id'=>$bill_id]])."\">View Sale</a>";
						$log_data[$row['id']]['create_id'] = $bill_id;
						$log_output[] = "Renew complete - sale generated (#". $class_sale->reference_link($bill_id) .")";

						$line_data = $zulu->table_data('sale_line',0,['where'=>["sale_id = '".$bill_id."'","object = 'renew'","object_id = '".$row['id']."'"],'first'=>true,'field'=>['id']]);
						$data_renew["object_id"] = $line_data['id'];
						$data_renew["object"] = 'sale_line';
					} else {
						return ['success'=>false,'log'=>$log_output,'reason'=>"Error generating sale for renewal."];
					}
				}
			}

			//-- Renew log
			if(!$stop_renew) {

				//-- Create new log
				$renew_log = $this->renew_log_new($row['id'],$data_renew);
				if($renew_log['id']<=0) {
					$zulu->fatal_error("Subscription error","Renewal log failed to execute.");
				} else {
					if($this->is_renew_instant()) {
						$apply_result = $this->apply_renewal_period(['id'=>$renew_log['id']]);
					}
				}
			}
		} else {
			return ['success'=>false,'log'=>$log_output,'reason'=>'No contact was defined for this subscription.'];
		}

		return ['success'=>true,'log'=>$log_output];
	}

	function label_scale($scale,$ly=false) {
		$out = $this->config->renew_scale[$scale];
		if($ly) {
			$out .= 'ly';
			if($scale=='d') $out = 'Daily';
		}
		return $out;
	}
	function label_fee($data='') {
		global $zulu;

		if($data=='') $data = $this->vars->data;
		return (defined('LOCALE_currency')?LOCALE_currency:LOCALE_currency_symbol).zulu::dollar($data['price']).' <sup>Per '.$this->label_scale($data['renew_scale']);
	}
	function label_frequency($data='') {
		global $zulu;

		if($data=='') $data = $this->vars->data;
		return $data['renew_interval'].' '.$this->label_scale($data['renew_scale']).$zulu->s($data['renew_interval']);
	}
	function label_renew_next($data='') {
		global $zulu;

		if($data=='') $data = $this->vars->data;
		$label = $zulu->date($data['renew_next'],'j/m/Y');
		if($zulu->date($data['renew_next'],'Hi')>0) {
			$label .= "<sup>".$zulu->date($data['period_start'],'g:ia')."</sup>";
		}
		if($data['status']==3 || $data['cancel_date']>0) {
			$label = 'Cancelled';
		}

		return $label;
	}
	function label_period($data='') {
		global $zulu;

		if($data=='') $data = $this->vars->data;
		$period_start = $this->label_date($data['period_start']);
		$period_end = $this->label_date($data['period_end']);

		$label = $period_start . ' to ' . $period_end;

		return $label;
	}
	function label_date($timestamp,$config=[]) {
		global $zulu;
		return $zulu->date_time_label($timestamp,$config);
	}
	function status_autorenew($config=array()) {
		global $class_sale,$zulu,$class_module;

		if(!empty($config)) {
			$sub_data = $this->renew_data(array('id'=>$config['id']));
		} else {
			$sub_data = $this->vars->data;
		}
		$status = $sub_data['status'];

		if($config['sale']) {
			$sale_id = $class_sale->sale_line_parent($config['id'],['object'=>'membership','object_id'=>$sub_data['id']]);
			$balance = $class_sale->sale_balance($sale_id);
			$prefix_0 = "<a href=\"".$zulu->link_page('sale',['query'=>['Method'=>'View','Action'=>'edit','id'=>$sale_id]])."\" target=\"_blank\">($".$zulu->dollar($balance,2)." Due)</a>";
		}

		$data['toggle'] = true;

		$has_module = ($sub_data['auto_renew_module_id']>0?true:false);
		if($status=='1') {
			if($sub_data['auto_renew']==1) {
				$data['class'] = 'opt opt-success';
				$data['icon'] = 'fa-check';
				$data['label'] = 'Auto-renew Active';
				$data['label_short'] = 'Active';
			} else {
				$data['class'] = 'opt opt-warning';
				$data['icon'] = 'fa-pause';
				$data['label'] = 'Auto-renew Inactive';
				$data['label_short'] = 'Inactive';
			}
		} elseif($status=='3') {
			$data['class'] = 'opt opt-danger';
			$data['icon'] = 'fa-times';
			$data['label'] = 'Auto-renew Inactive';
			$data['label_short'] = 'Inactive';
		} else {
			$data['class'] = 'opt opt-grey';
			$data['icon'] = 'fa-pause';
			$data['label'] = 'Auto-renew Inactive '.$prefix_0;
			$data['label_short'] = 'Inactive';
		}
		if($sub_data['renew_type']!='date') {
			$data['class'] = 'opt opt-warning';
			$data['icon'] = 'fa-pause';
			$data['label'] = 'Auto-renew Unavailable';
			$data['label_short'] = 'Unavailable';
		}

		if($has_module) {
			$module_data = $class_module->module_data(['id'=>$sub_data['auto_renew_module_id']]);
			if(trim($module_data['name']) != NULL) {
				$module_name = $module_data['name'];
				$data['label']	.=	' ('.$module_name.')';
				$data['label_short']	.=	' ('.$module_name.')';
			}
		}

		return $data;
	}
	function can_renew($id=0) {
		if($id==0) {
			$renew_data = $this->vars->data;
		} else {
			$renew_data = $this->renew_data(['id'=>$id]);
		}
		$pid = $this->renew_product_id();

		if($renew_data['renew_type']=='date'&&$renew_data['renew_next'] < strtotime("+30 days") && $renew_data['status'] == 1 && ($pid > 0 || $renew_data['template_id']<=0)) {
			return true;
		} else {
			return false;
		}
	}
	function renew_product_id($id=0) {
		global $class_product;
		if($id==0) {
			$renew_data = $this->vars->data;
		} else {
			$renew_data = $this->renew_data(['id'=>$id]);
		}

		//find product
		if($renew_data['product_id']<=0) {
			$product_data = $class_product->product_data(array('object'=>'membership','object_id'=>$renew_data['template_id']));
			$pid = $product_data['id'];

			//set this product id
			$this->renew_edit($renew_data['id'],['product_id'=>$pid]);
		} else {
			$pid = $renew_data['product_id'];
		}

		//Final Return
		if($pid>0) {
			return $pid;
		} else {
			return 0;
		}
	}
	function status_info($config=array()) {
		global $class_sale,$zulu;

		if(isset($config['id'])) {
			$sub_data = $this->renew_data(array('id'=>$config['id']));
		} else {
			$sub_data = $this->vars->data;
		}
		$status = $sub_data['status'];

		if($config['sale']) {
			$sale_id = $class_sale->sale_line_parent($config['id'],['object'=>'membership','object_id'=>$sub_data['id']]);
			if($sale_id>0) {
				$balance = $class_sale->sale_balance($sale_id);
				$prefix_0 = "<a href=\"".$zulu->link_page('sale',['query'=>['Method'=>'View','Action'=>'edit','id'=>$sale_id]])."\" target=\"_blank\">($".$zulu->dollar($balance,2)." Due)</a>";
			}
		}
		if($status==1) {
			if($sub_data['period_end']>time()) {
				$data['class'] = 'opt opt-success';
				$data['icon'] = 'fa-check';
				$data['label'] = 'Active'.$prefix_1;
			} elseif($sub_data['period_end']>0) {
				$data['class'] = 'opt opt-danger';
				$data['icon'] = 'fa-times';
				$data['label'] = 'Expired'.$prefix_1;
			} else {
				$data['class'] = 'opt opt-grey';
				$data['icon'] = 'fa-check';
				$data['label'] = 'Pending'.$prefix_1;
			}
			if($sub_data['cancel_date']>0) {
				if($sub_data['cancel_date']>time()) {
					$data['class'] = 'opt opt-grey';
					$data['icon'] = 'fa-flag-checkered';
					$data['label'] = 'Ending '.$zulu->date($sub_data['cancel_date'],'jS M');
				} else {
					$data['class'] = 'opt opt-grey';
					$data['icon'] = 'fa-times';
					$data['label'] = 'Cancelled'.$prefix_3;
				}
			} elseif($this->is_trial_active($sub_data)) {
				$data['class'] = 'opt opt-success';
				$data['icon'] = 'fa-vial';
				$data['label'] = 'Trial ends '.$zulu->date($sub_data['trial_expire'],'jS M');
			}
		} elseif($status=='3') {
			$data['class'] = 'opt opt-grey';
			$data['icon'] = 'fa-times';
			$data['label'] = 'Cancelled'.$prefix_3;
		} else {
			$data['class'] = 'opt opt-grey';
			$data['icon'] = 'fa-pause';
			$data['label'] = 'Pending '.$prefix_0;
		}

		return $data;
	}
	function template_change_array($id) {
		global $zulu;
		$data = $this->template_data(['id'=>$id]);
		$sql = ['where'=>["(change_from='*' OR FIND_IN_SET('".$id."', change_from))"]];
		$options = $zulu->table_data($this->SQL_table_temp,0,$sql);
		foreach($options as $vals) {
			$option[$vals['id']] = $vals['title'];
			$options_key[] = $vals['id'];
		}
		if($data['change_to']!="*") {
			foreach(explode(",",$data['change_to']) as $tpl_id) {
				if(in_array($tpl_id,$options_key)) {
					$retain_key[] = $tpl_id;
				}
			}
			foreach($option as $option_key=>$option_val) {
				if(!in_array($option_key,$retain_key)) {
					unset($option[$option_key]);
				}
			}
		}
		if($data['change_to']=="") {
			unset($option);
		}
		return $option;
	}
	function renew_meta($id,$field=NULL) {
		return zulu::meta_value("renew",$id,$field);
	}
	function template_meta($id,$field=NULL) {
		return $this->renew_template_meta($id,$field);
	}
	function renew_template_meta($id,$field=NULL) {
		return zulu::meta_value("renew_template",$id,$field);
	}
	function credit_data($config=[]) {
		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);

		if(count($config['field'])>0) {
			$sql_config['field'][] = $config['field'];
		}
		if($config['renew_id']!=NULL) {
			$sql_config['where'][] = "renew_id = '".$config['renew_id']."'";
		}
		if($config['object_id']!=NULL) {
			$sql_config['where'][] = "object_id = '".$config['object_id']."'";
		}
		if($config['object']!=NULL) {
			$sql_config['where'][] = "object = '".$config['object']."'";
		}
		if($config['limit']>0) {
			$sql_config['limit'] = $config['limit'];
		}
		if($config['sort']!=NULL) {
			$sql_config['sort'] = $config['sort'];
		}

		return zulu::table_data($this->SQL_table_credit,$id,$sql_config);
	}
	function credit_level($id) {
		$data = $this->credit_data(array('renew_id'=>$id));
		$level = 0;
		foreach($data as $row) {
			$level += $row['value'];
		}
		return $level;
	}
	function credit_label($level) {
		$level = ($level==NULL?0:$level);
		return ($level<=0?"<span class=\"opt opt-danger\"><b>Out of credit</b> <span class=\"fas fa-chevron-down\"></span> {$level}</span>":"<span class=\"opt opt-success\"><b>In credit</b> <span class=\"fas fa-chevron-up\"></span> {$level}</span>");
	}
	function credit_adjust($id, $config=[]) {
		global $zulu,$class_user;

		$rnid = $id;
		$config['renew_id'] = $id;
		$config['stat_add'] = time();
		$config['team_id'] = ($class_user->authorised->child_id>0?$class_user->authorised->child_id:$class_user->authorised->id);

		foreach($config as $key=>$val) {
			$data[$key] = $val;
			$fields[] = $key;
		}

		$query = "INSERT INTO ".$this->SQL_table_credit." ".$this->db->build(2,$fields,$data);
		$this->db->query($query);

		$level = $this->credit_level($rnid);
		$zulu->meta_update("renew",$rnid,"credit",$level);

		return;
	}
	function has_sale($renew_id) {
		global $zulu,$class_sale;

		if(count($this->vars->data)<=0) {
			$renew_data = $this->renew_data(['id'=>$renew_id]);
			//$class_renew->vars->data_row = $renew_data;
		} else {
			$renew_data = $this->vars->data;
		}

		$has_already_renewed = false;
		$client_sales = $class_sale->sale_data(['client_id'=>$renew_data['client_id'], 'date_min'=>strtotime('-3 months')]);

		foreach($client_sales as $sale){
			$sale_lines = $class_sale->sale_line_data(['sale_id'=>$sale['id'], 'custom_like'=>'renew_id','product_id'=>$renew_data['product_id']]);

			if(count($sale_lines)>0) {
				foreach($sale_lines as $line){
					$custom = unserialize($line['custom']);
					if($custom['renew_id'] == $renew_data['id'] && $class_sale->sale_balance($sale['id'])>0 && $line['object']!='membership' && $sale['status']!='2'){
						$has_already_renewed = true;
						$renew_sale_id = $sale['id'];
						$renew_sale_token = $sale['token'];
						break;
					}
				}
			} else {
				//-- continue
			}
		}
		return ['has_sale'=>$has_already_renewed,'token'=>$renew_sale_token,'id'=>$renew_sale_id];
	}
	function can_activate() {
		global $zulu;

		$data = $this->vars->data;

		//-- Cancelled
		if($data['auto_renew_module_id']>0) {
			return ['success'=>false,'reason'=>'This uses a module and  cancelled.'];
		}

		//-- Cancelled
		if($data['status']==3 || $data['cancel_date']>0) {
			return ['success'=>false,'reason'=>'Subscription is marked cancelled.'];
		}

		//-- Current Period
		if($data['period_start']<=time()) {
			return ['success'=>false,'reason'=>'Period has already started for this subcription.'];
		}

		//-- Current Period
		if($data['period_start']>time() && $data['status']==1) {
			return ['success'=>false,'reason'=>'Period has already started for this subcription.'];
		}

		return ['success'=>true,'reason'=>'Subscription is marked cancelled.'];
	}
	function is_cancel_lock($data='') { //-- Is cancellation now locked / can't be un-cancelled
		global $zulu;

		if($data=='') $data = $this->vars->data;

		if(($data['cancel_date']>0 && $data['cancel_date']<=time()) || $data['status']==3) {
			return true;
		} else {
			return false;
		}
	}
	function is_mark_cancel($data='') { //-- Is marked cancel
		global $zulu;

		if($data=='') $data = $this->vars->data;

		if($data['cancel_date']>0) {
			return true;
		} else {
			return false;
		}
	}
	function is_cancel($data='') { //-- Is marked cancel (as above)
		return $this->is_mark_cancel($data);
	}
	function has_trial($data='') { //-- Has had a trial true/false
		global $zulu;

		if($data=='') $data = $this->vars->data;

		if($data['trial_count']>0) {
			return true;
		} else {
			return false;
		}
	}
	function is_trial_active($data='') { //-- Is marked cancel (as above)
		global $zulu;

		if($data=='') $data = $this->vars->data;

		if($this->has_trial() && $data['trial_expire']>0 && $data['trial_expire']>time()) {
			return true;
		} else {
			return false;
		}
	}
	function is_active($data='') { //-- Is active
		global $zulu;

		if($data=='') $data = $this->vars->data;

		if($data['status']==1) {
			if($data['period_end']>time()) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	function is_renew_instant() { //-- Is renewal instant?
		global $zulu;

		if(empty($this->vars->data)) $zulu->fatal_error("Subscription error","Requires renew 'get' to be defined. (D)");
		if(!isset($this->vars->meta)) $zulu->fatal_error("Subscription error","Requires renew 'get' to be defined. (M)");
		if(!isset($this->vars->template_meta) && $this->vars->data['template_id']>0) $zulu->fatal_error("Subscription error","Requires renew 'get' to be defined. (TM)");

		$policy = $this->vars->meta['renew_policy'];
		if(trim($policy)==NULL) {
			$policy = $this->vars->template_meta['renew_policy'];
		}

		if($this->vars->data['opt_bill']==0) {
			return true;
		} else {
			switch($policy) {
				case 'instant':
				default:
					return true;
					break;
				case 'pay':
					return false;
					break;
			}
		}
	}
	function apply_renewal_period($identifier) { //-- Is renewal instant?
		global $zulu;

		$success = true;
		if($identifier['key']!='') {
			$key_field = 'renew_key';
			$key_id = $identifier['key'];
		} elseif($identifier['id']>0) {
			$key_field = 'id';
			$key_id = $identifier['id'];
		} else {
			$zulu->fatal_error("Subscription error","Renewal failed because no identifier was supplied.");
		}
		if(trim($key_id)==NULL) {
			$zulu->fatal_error("Subscription error","Renewal failed because the identifier was empty.");
		}

		if(empty($this->vars->data)) $zulu->fatal_error("Subscription error","Requires renew 'get' to be defined. (D)");
		if(!isset($this->vars->meta)) $zulu->fatal_error("Subscription error","Requires renew 'get' to be defined. (M)");
		if(!isset($this->vars->template_meta) && $this->vars->data['template_id']>0) $zulu->fatal_error("Subscription error","Requires renew 'get' to be defined. (TM)");

		$renew_data = $this->vars->data;

		$renew_log_data = $this->renew_log_data(['filter'=>[$key_field=>$key_id,'renew_id'=>$this->vars->data['id']],'first'=>true]);
		if($renew_log_data['id']<=0) {
			$zulu->fatal_error("Subscription error","Renewal failed because the renew log does not exist.");
		}
		if($renew_log_data['process']>0) {
			return ['success'=>false,'reason'=>"This renewal log has already been applied / processed."];
		}

		//-- First ever renewal?
		if($renew_data['period_start']==0) {

			//-- Sign up email
			if($this->vars->template_meta['mail_signup']!='') {
				global $class_client,$class_rule;
				$client_data = $class_client->client_data(['id'=>$this->vars->data['client_id']]);
				$template_data = $class_rule->template_data(['id'=>$setting_data['client_birth_template']]);
				$template = [
					'template'	=>	$this->vars->template_meta['mail_signup'],
				];
				$rule_load = $class_rule->rule_build_message($template,$client_data);
				$MESSAGE_body .= $rule_load['message'];
				$MESSAGE_subject = $rule_load['subject'];
				$to = $client_data['email'];

				if(filter_var($to,FILTER_VALIDATE_EMAIL)) {
					$zulu->mail_send($to,$MESSAGE_subject,$MESSAGE_body,$rule_load['attach_file'],false,array("user_id"=>$this->vars->data['user_id'],'client'=>true,'toggle'=>true));
				}
			}
		}

		//-- Apply period finally - only if the renew_to is smaller than the new date otherwise skip this
		$set_renew_next = strtotime('23:59:59',$renew_log_data['renew_to']);
		if($renew_data['period_start']<$renew_log_data['renew_to']) {
			$update_result = $this->renew_edit($renew_data['id'],[
				'period_start'	=>	$renew_log_data['renew_from'],
				'period_end'	=>	$set_renew_next,
				'renew_next'	=>	$renew_log_data['renew_to'],
			]);
			$falsify = false;
		} else {
			$falsify = true;
			$update_result['success'] = true; //-- Falsify true
		}

		if(!$update_result['success']) {
			$success = false;
			$reason = "Instant renewal FAILED.";
		} elseif(!$falsify) {
			$query = "UPDATE ".$this->SQL_table_log." SET `process` = 1, `process_time` = '".time()."' WHERE `id` = '".$renew_log_data['id']."'";
			if(!$this->db->query($query)) {
				$success = false;
				$reason = "Failed to update the renew log record to processed.";
			}
		}

		return ['success'=>$success,'reason'=>$reason];
	}
	function charge_item_array($data_renew) {
		global $zulu,$class_product;

		$data = $this->vars->data;
		$meta = $this->vars->meta;

		if(empty($data)) $zulu->fatal_error("Subscription error","No data is loaded.");
		if(empty($data_renew)) $zulu->fatal_error("Subscription error","No renew log data is loaded.");

		$period_start = $data_renew['renew_from'];
		$period_end = $data_renew['renew_to'];

		//-- Serialize data to append on bill/sale_line
		$serialize_data = ['renew_id'=>$data['id'],'renew_key'=>$data_renew['renew_key'],'renew_period'=>[$period_start,$period_end]];

		//-- Labels output
		$renew_time = "DATE RANGE: ".$zulu->date_time_label($period_start)." to ".$zulu->date_time_label($period_end).PHP_EOL."PERIOD: ".$this->label_frequency();
		$renew_time_short = $zulu->date_time_label($period_start)." to ".$zulu->date_time_label($period_end);

		if($data['opt_bill']==1) { //-- GEN: Sale Line
			$return = array(
				'description'	=>	$data['title']." - ".$renew_time_short,
				'price'	=>	$data['price'],
				'quantity'	=>	$data['quantity'],
				'product_id' => ($data['product_id']>0?$data['product_id']:$product_data['id']),
				'custom'	=> $serialize_data,
				'object_id' => $data['id'],
				'object'	=> 'renew',
			);

			$return = $class_product->custom_filter($return);
			if(is_array($return['custom'])) {
				$return['custom'] = serialize($return['custom']);
			}
		}
		if($data['opt_bill']==2) { //-- GEN: Bill

		}

		return $return;
	}
	function revenue_per_month() {
		return $this->revenue_per_year()/12;
	}
	function revenue_per_year() {
		global $zulu;

		$row = $this->vars->data;
		$meta = $this->vars->meta;

		if(empty($row)) $zulu->fatal_error("Subscription error","No data is loaded.");

		//-- Income calculation
		$yearly = 0;
		if($row['renew_scale']=='d') {
			$yearly = $row['price']*365;
		}
		if($row['renew_scale']=='w') {
			$yearly = $row['price']*52;
		}
		if($row['renew_scale']=='m') {
			$yearly = $row['price']*12;
		}
		if($row['renew_scale']=='y') {
			$yearly = $row['price'];
		}
		return $yearly;
	}
	function renew_next_label() {
		global $zulu;

		$row = $this->vars->data;
		$meta = $this->vars->meta;

		if(empty($row)) $zulu->fatal_error("Subscription error","No data is loaded.");

		if($row['renew_scale']=='m') {
			if(date('Ym',strtotime("+1 Month"))==date('Ym',$row['renew_next'])) {
				$next_renew = "Next Month (".date("j/m/Y",$row['renew_next']).")";
				$next_renew_class = 'green';
			} else {
				$next_renew = date("j/m/Y",$row['renew_next']);
			}
		} else {
			$next_renew = '<span title="Renews at '.$zulu->date($row['renew_next']).'">'.date("j/m/Y",$row['renew_next']).'</span>';
		}
		if($row['renew_next']<strtotime('today')&&$row['renew_status']!=3) {
			$next_renew = $next_renew;
			$next_renew_class = 'color-red';
		} elseif($row['renew_next']==strtotime('today')&&$row['renew_status']!=3) {
			$next_renew = '<span title="Renews at '.$zulu->date($row['renew_next']).'">Today</span>';
			$next_renew_class = 'color-red';
		} elseif($row['renew_status']==3) {
			$next_renew_class = 'color-grey';
		} elseif($row['renew_status']==0) {
			$next_renew_class = 'color-grey';
		}

		//- Concession memberships
		if($row['renew_type']=='conc') {
			$next_renew_class = 'color-grey';
			$next_renew = $class_renew->credit_label($renew_meta['credit']);
		}

		return ['label'=>$next_renew,'class'=>$next_renew_class];
	}
	function next_renewal_label($renew_id=0) {
		global $zulu;

		if(count($this->vars->data)<=0) {
			$renew_data = $this->renew_data(['id'=>$renew_id]);
		} else {
			$renew_data = $this->vars->data;
		}

		if(empty($renew_data)) $zulu->fatal_error("Subscription error","No data is loaded.");

		return ($renew_data['lifetime']>0?"Lifetime":$zulu->date($renew_data['renew_next'],'j/m/Y'));
	}

	function payment_method_detail($id) {
		global $zulu,$class_module;

		$this->get($id);
		$data = $this->vars->data;
		$default_cancel_time = time();

		$module_payment_row = $class_module->module_data(['id'=>$data['auto_renew_module_id']]);

		$module_payment = $class_module->init($module_payment_row['id']);
		$module_payment->renew_data_set($id);

		$result = $module_payment->payment_method_detail();

		return $result;
	}

}
