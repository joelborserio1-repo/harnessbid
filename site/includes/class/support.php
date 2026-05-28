<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- Class: support
class support {
	
	public $SQL_table = 'support_ticket';
	public $message_sql_table = 'support_ticket_message';
	//public $SQL_table_template_item = 'job_template_item';
		
	function __construct($config=[]) {
		global $db,$zulu;
		$this->db = $db;
		$this->zulu = $zulu;
		//$this->vars->status_array = [0=>'All',1=>'Open',2=>'On Hold',3=>'Closed'];
		
	}
	function support_count($config=[]) {
		if(isset($config['status'])){
			$param = ['status'=>$config['status'],'id'=>$config['id']];
		}else{
			$param = ['id'=>$config['id']];
		}
		
		$array = $this->project_data($param);
		
		if($config['simple']>0) {
				$ret = count($array);	
			} else {
			foreach($array as $val) {
				$stat_num = $val['status'];
				$ret[$stat_num]++;	
			}
		}
		return $ret;
	}
	function support_unread($id,$admin=false) {
		$filter = ['support_ticket_id'=>$id,'stat_read_min'=>0,'stat_read_max'=>0,'field'=>['stat_read']];
		
		if(!$admin) {
			$filter['skip_client'] = true;
		} else {
			$filter['skip_admin'] = true;
		}
		
		$message_data = $this->message_data($filter);
		if(count($message_data)>0) {
			return true;
		} else {
			return false;
		}
	}
	function support_data($config=array()) {
		global $class_user;
		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);
		if($config['client_id']!=NULL) {
			$sql_config['where'][] = "client_id = '".$config['client_id']."'";
		}
//		if($config['admin_id']!=NULL) {
//			$sql_config['where'][] = "admin_id = '".$config['admin_id']."'";
//		}
		if($config['token']!=NULL) {
			$config['first'] = true;
			$sql_config['where'][] = "token = '".$config['token']."'";
		}
		if(isset($config['status'])) {
			if(is_array($config['status'])) {
				$sql_config['where'][] = "status IN(".implode(",",$config['status']).")";
			} else {
				$sql_config['where'][] = "status = '".$config['status']."'";
			}
		}
		if(isset($config['object_id'])) {
			$sql_config['where'][] = "object_id = '".$config['object_id']."'";
		}
		if(isset($config['object'])) {
			$sql_config['where'][] = "object = '".$config['object']."'";
		}
		
		if($config['first']) {
			$sql_config['first'] = true;
		}
		if(count($config['field'])>0) {
			$sql_config['field'] = $config['field'];
		}
		
		if(!$config['ovr_user_id']) {
			$sql_config['where'][] = "user_id = '".$class_user->authorised->id."'";
		}
		$sql_config['sort'] = 'status ASC, stat_add DESC';
		//$sql_config['where'][] = "user_id = '".$class_user->authorised->id."'";
		return zulu::table_data($this->SQL_table,$id,$sql_config);
	}
	function support_delete($id,$identifier='id') {
		global $class_user,$class_cache;
		$query = "DELETE FROM ".$this->SQL_table." WHERE `{$identifier}` = '".$id."' AND user_id='".$class_user->authorised->id."'";
		if($this->db->query($query)) {
			$class_cache->dump('project_count');
			return true;
		} else {
			return false;	
		}
	}
	private function support_new($config=array()) {
		global $class_user;
		$data['user_id'] = $class_user->authorised->id;
		$data['admin_id'] = $class_user->authorised->child_id;
		//echo $class_user->authorised->child_id;
		$data['token'] = zulu::serial();
		$data['stat_add'] = time();
		$data['stat_update'] = time();
		
		$query = "INSERT INTO ".$this->SQL_table." ".$this->db->build(2,array('user_id','admin_id','token','stat_add','stat_update'),$data);
		
		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$this->db->insert_id());
		} else {
			return array("success"=>false,"id"=>0);
		}
	}
	function support_edit($id,$config=array(),$message_config=array(), $client_submitted =false, $notify_email =false) {
		global $class_user,$class_cache;
		$new = false;
		if($id<1) {
			$data = $this->support_new();
			$id = $data['id'];
			$new = true;
		}
		
		foreach($config as $key=>$val) {
			$data[$key] = $val;
			$fields[] = $key;
		}

		$query = "UPDATE ".$this->SQL_table." SET ".$this->db->build(1,$fields,$data)." WHERE id = '{$id}' AND user_id='".$class_user->authorised->id."'";
		
		if($this->db->query($query)) {
			$class_cache->dump('project_count');
			if($new){
				if($client_submitted){
					$this->new_ticket_message_client($id,$message_config['message_text'],$config['client_id'],true,$notify_email);
				}else{
					$this->new_ticket_message($id,$message_config['message_text'],true,$notify_email);
				}
			}
			return array("success"=>true,"id"=>$id);
		} else {
			return array("success"=>false);
		}		
	}
	
	function new_ticket_message($ticket_id, $message_text, $new_ticket =false, $notify_email =false){
		global $class_user,$class_cache;
		//add message  
		$message_data['support_ticket_id'] = $ticket_id;
		$message_data['user_id'] = $class_user->authorised->id;
		$message_data['admin_id'] = $class_user->authorised->child_id;
		$message_data['message_text'] = $message_text;
		$message_data['ip_address'] = $_SERVER['REMOTE_ADDR'];
		$message_data['stat_add'] = time();
		$message_data['stat_update'] = time();
		
		$query = "INSERT INTO ".$this->message_sql_table." ".$this->db->build(2,array('support_ticket_id','user_id','admin_id','message_text','ip_address','stat_add','stat_update'),$message_data);

		if($this->db->query($query)) {

			if($notify_email){
				$this->send_email($ticket_id, $message_data, true, $new_ticket);
			}
			return array("success"=>true,"id"=>$id);
		} else {
			return array("success"=>false);
		}
	}
	function new_ticket_message_client($ticket_id, $message_text, $client_id, $new_ticket =false, $notify_email =false){
		global $class_user,$class_cache;
		//add message  
		$message_data['support_ticket_id'] = $ticket_id;
		$message_data['user_id'] = $class_user->authorised->id;
		$message_data['admin_id'] = 0;
		$message_data['client_id'] = $client_id;
		$message_data['message_text'] = $message_text;
		$message_data['ip_address'] = $_SERVER['REMOTE_ADDR'];
		$message_data['stat_add'] = time();
		$message_data['stat_update'] = time();
		
		$query = "INSERT INTO ".$this->message_sql_table." ".$this->db->build(2,array('support_ticket_id','user_id','admin_id','client_id','message_text','ip_address','stat_add','stat_update'),$message_data);
		
		if($this->db->query($query)) {
			$class_cache->dump('project_count');
			if($notify_email){
				$this->send_email($ticket_id, $message_data, false, $new_ticket);
			}
			return array("success"=>true,"id"=>$id);
		} else {
			return array("success"=>false);
		}
	}
	function message_data($config=array()){
		global $class_user;
		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);
		if($config['client_id']!=NULL) {
			$sql_config['where'][] = "client_id = '".$config['client_id']."'";
		}
		if($config['first']) {
			$sql_config['first'] = true;
		}
		if(isset($config['skip_admin'])) {
			$sql_config['where'][] = "admin_id <= 0";
		}
		if(isset($config['skip_client'])) {
			$sql_config['where'][] = "client_id <= 0";
		}
		if(count($config['field'])>0) {
			$sql_config['field'] = $config['field'];
		}
		if(isset($config['stat_read_min'])) {
			$sql_config['where'][] = "stat_read >= '".$config['stat_read_min']."'";
		}
		if(isset($config['stat_read_max'])) {
			$sql_config['where'][] = "stat_read <= '".$config['stat_read_max']."'";
		}
		if(isset($config['support_ticket_id'])) {
			$sql_config['where'][] = "support_ticket_id = '".$config['support_ticket_id']."'";
		}
		if(!$config['ovr_user_id']) {
			$sql_config['where'][] = "user_id = '".$class_user->authorised->id."'";
		}
		return zulu::table_data($this->message_sql_table,$id,$sql_config);
	}
	
	function message_create_bubble($message, $is_admin_mode =false){
		global $class_user, $class_client, $zulu;
		
		$bubble_html = '';
		$admin_client_link;//for admin to link to persons profile
		$admin_client_name;//for frontend to only name and no link
		if($message['admin_id'] !=0){
			$admin_client_link = $class_user->admin_link($message['admin_id']);
			$admin_client_name = $class_user->user_name($message['admin_id']);
		}else if($message['client_id'] !=0){
			$admin_client_link = $class_client->admin_link($message['client_id']);
			$admin_client_name = $class_client->client_name($message['client_id']);
		}
		
		if($is_admin_mode){
			$ip_html ='';
			if($message['client_id']>0){
				$ip_html ='<p>IP: '.$message['ip_address'].'</p>';
			}
				$bubble_html = '<div class="row">
									<div class="col-md-10 '.($message['admin_id']>0?"col-md-offset-2":"").'">
										<div class="alert alert-'.($message['admin_id']>0?"success":"info").'">
											<div class="row">
												<div class="col-md-12">												
													<p><strong>'.$message['message_text'].'</strong></p>
												</div>
											</div>
											<div class="row">
												<div class="col-md-4">
													'.$ip_html.'
												</div>
												<div class="col-md-8">
													<p class="text-right">Posted '.$zulu->time_fancy($message['stat_add']).' by '.$admin_client_link.'</p>
												</div>
											</div>
										</div>
									</div>
								</div>';	
		} else {				
			$bubble_html = '<div class="row">
								<div class="col-md-10 '.($message['admin_id']>0?"":"col-md-offset-2").'">
									<div class="alert alert-'.($message['admin_id']>0?"info":"success").'">
										<p><strong>'.$message['message_text'].'</strong></p>
										<p class="text-right">Posted '.$zulu->time_fancy($message['stat_add']).' by '.$admin_client_name.'</p>
									</div>
								</div>
							</div>';	
		}
		
		if($message['stat_read']<=0) {
			if($is_admin_mode&&$message['admin_id']<=0) {
				//we're viewing the CLIENT reply therefore mark it read
				$this->db->query("UPDATE ".$this->message_sql_table." SET stat_read = '".time()."' WHERE id = '".$message['id']."'");
			} elseif(!$is_admin_mode&&$message['admin_id']>0) {
				//we're viewing the ADMIN reply therefore mark it read
				$this->db->query("UPDATE ".$this->message_sql_table." SET stat_read = '".time()."' WHERE id = '".$message['id']."'");
			}
		}
		
		return $bubble_html;
	}
	function send_email($ticket_id, $message_data, $from_admin, $new_ticket, $status_update = false){
		global $zulu, $class_client, $class_user;
		//Get related ticket ID
		$ticket_data_config['id'] = $ticket_id;
		$ticket_data = $this->support_data($ticket_data_config);
		$client = $class_client->client_data(['id'=>$ticket_data['client_id']]);
		$client_name = $class_client->client_name($client);
		$to = "";
		$sub = "";
		$mess = "";
		if($status_update){
			if($ticket_data['contact_email']==NULL || $ticket_data['contact_email'] == "") {
				$to = $client['email'];
			}else{
				$to = $ticket_data['contact_email'];
			}
			$sub = 'Support Ticket status changed';
			$mess = '<p>Hello, '.$client_name.' this is to notify you that the support ticket <strong>'.$ticket_data['subject'].
					'</strong> has changed status to <strong>'.$ticket_data['status'].'</strong>.</p>';
		}else{
			if($from_admin){
				if($ticket_data['contact_email']==NULL || $ticket_data['contact_email'] == "") {
					$to = $client['email'];
				}else{
					$to = $ticket_data['contact_email'];
				}
				if($new_ticket){
					$sub = 'New Support Ticket';
					$mess = '<p>Hello, '.$client_name.' this is to notify you that you have a new support ticket.</p>
							<p><strong>Ticket subject: </strong>'.$ticket_data['subject'].'</p>
							<p><strong>Attached Message: </strong>'.$message_data['message_text'].'</p>';
				}else{
					$sub = 'New message for Support Ticket: '.$ticket_data['subject'];
					$mess = '<p>Hello, '.$client_name.' this is to notify you that you have a new support ticket message.</p>
							<p><strong>Message: </strong>'.$message_data['message_text'].'</p>';			
				}
			}else{
				if($ticket_data['admin_id']>0){
					$admin = $class_user->user_data(['id'=>$ticket_data['admin_id']]);
					$to = $admin['email'];
				}else{
					$user = $class_user->user_data(['id'=>$ticket_data['user_id']]);	
					$to = $user['email'];
				}
				if($new_ticket){
					$sub = 'New Support Ticket';
					$mess = '<p>Hello, Admin this is to notify you that you have a new support ticket.</p>
							<p><strong>Client: </strong>'.$client_name.'</p>
							<p><strong>Ticket subject: </strong>'.$ticket_data['subject'].'</p>
							<p><strong>Attached Message: </strong>'.$message_data['message_text'].'</p>
							<p><strong>IP address: </strong>'.$message_data['ip_address'].'</p>';
				}else{
					$sub = 'New message for Support Ticket: '.$ticket_data['subject'];
					$mess = '<p>Hello, Admin this is to notify you that you have a new support ticket message.</p>
							<p><strong>Message: </strong>'.$message_data['message_text'].'</p>
							<p><strong>IP address: </strong>'.$message_data['ip_address'].'</p>';			
				}
			}
		}
		//echo ("To: ".$to."  sub: ".$sub." mess: ".$mess);exit;
		$zulu->mail_send($to,$sub,$mess,'',NULL,['user_id'=>$ticket_data['user_id'],'client'=>true]);
	}
	
	function support_status_update($ticket_id, $update_sql_config, $notify_client){
		$this->support_edit($ticket_id, $update_sql_config);
		if($notify_client){
			$this->send_email($ticket_id,"",false,false,true);
		}
	}
}