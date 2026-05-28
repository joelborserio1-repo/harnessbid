<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- Class: CLIENT
class client {

	public $SQL_table = 'client';
	public $SQL_table_status = 'client_status';

	function __construct($config=[]) {
		global $db,$zulu;
		$this->db = $db;
		$this->config = new stdClass();
		$this->config->type = array(2=>'Lead',3=>'Prospect',1=>'Client',0=>'Cancelled');
		$this->config->type_array = [1=>'',2=>'lead',3=>'prospect',0=>'cancel'];
	}
	function type($id=0) {
		global $zulu;
		if($id==0) {
			$data = $this->vars->data_row;
		} else {
			$data = $this->client_data(['id'=>$id]);
		}
		if($data['supplier']>0) {
			return 'supplier';
		} else {
			if($data['type']==0) {
				return 'cancel';
			} elseif($data['type']==2) {
				return 'lead';
			} elseif($data['type']==3) {
				return 'prospect';
			} else {
				return 'client';
			}
		}
	}
	function field_value($id,$field='id') {
		global $zulu;
		$data = $zulu->table_data($this->SQL_table,$id,array("field"=>array($field),"first"=>true));
		return $data[$field];
	}
	function client_status($id=0) {
		if($id==0) {
			$data = $this->vars->data_row;
		} else {
			$data = $this->client_data(['id'=>$id]);
		}
		if($data['type']=='2') {
			$ret['type_label'] = 'Lead';
			$ret['type_icon'] = 'star';
			$ret['type_class'] = 'info';
		} elseif($data['type']=='1') {
			$ret['type_label'] = 'Client';
			$ret['type_icon'] = 'group';
			$ret['type_class'] = 'success';
		} elseif($data['supplier']>0) {
			$ret['type_label'] = 'Supplier';
			$ret['type_icon'] = 'bank';
			$ret['type_class'] = 'success';
		} else {
			$ret['type_label'] = 'Cancelled';
			$ret['type_icon'] = 'ban';
			$ret['type_class'] = 'danger';
		}
		if($data['status']==0) {
			$ret['type_label'] = 'Deleted / Cancelled';
			$ret['type_icon'] = 'ban';
			$ret['type_class'] = 'danger';
		}

		return $ret;
	}
	function data_format($client_data,$template='client') {
		global $zulu;
		if($template=='client') {
			$client_out['contact'] = ($client_data['name_first']!=NULL?stripslashes($client_data['name_first']):NULL)." ".($client_data['name_last']!=NULL?stripslashes($client_data['name_last']):NULL);
			$client_out['address'] = $zulu->compile(',<br>',array($client_meta['bill_address']['value'],$client_meta['bill_suburb']['value'],$client_meta['bill_city']['value'],$client_meta['bill_country']['value'],$client_meta['bill_post']['value']));
			$client_out['name'] = ($client_data['company']!=NULL?$client_data['company']."<br>Attn: ".$client_out['contact']:$client_out['contact']);
		} else {
			$client_out['company'] = stripslashes($client_data['company']);
			$client_out['contact_name'] = ($client_data['contact_name']!=NULL?"<b>Contact</b> ".$client_data['contact_name']:NULL);
			$client_out['contact_email'] = ($client_data['contact_email']!=NULL?"<b>Email</b> ".$client_data['contact_email']:NULL);
			$client_out['contact_phone'] = ($client_data['contact_phone']!=NULL?"<b>Phone</b> ".$client_data['contact_phone']:NULL);
			$client_out['contact_tax_no'] = ($client_data['tax_number']!=NULL?"<b>".($client_data['tax_label']!=NULL?$client_data['tax_label']:"Business")." Number</b> ".$client_data['tax_number']:NULL);
		}

		return $client_out;
	}
	function client_data($config=array()) {
		global $class_user;
		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);
		if($config['first']) {
			$sql_config['first'] = true;
		}
		if($id<=0) {
			if($config['type']==NULL) {
				$sql_config['where']['type'] = "type = '1'";
			} else if($config['type']=='all') {

			} else if($config['type']=='client') {
				$sql_config['where']['type'] = "type = '1'";
			} else if($config['type']=='lead') {
				$sql_config['where']['type'] = "type = '2'";
			} else if($config['type']=='prospect') {
				$sql_config['where']['type'] = "type = '3'";
			} else if($config['type']=='cancel') {
				$sql_config['where']['type'] = "type = '0'";
			} else {
				$sql_config['where']['type'] = "type = '".$config['type']."'";
			}
			if($config['supplier']) {
				$sql_config['where'][] = "supplier = 1";
				unset($sql_config['where']['type']);
			} else {
				$sql_config['where'][] = "supplier = 0";
			}
		}
		if($config['date_max']!=NULL) {
			$sql_config['where'][] = "stat_add <= '".$config['date_max']."'";
		}
		if($config['date_min']!=NULL) {
			$sql_config['where'][] = "stat_add >= '".$config['date_min']."'";
		}
		if($config['email']!=NULL) {
			$sql_config['first'] = true;
			$sql_config['where'][] = "email = '".$config['email']."'";
		}
		if($config['token']!=NULL) {
			$sql_config['first'] = true;
			$sql_config['where'][] = "token = '".$config['token']."'";
		}
		if($config['reference']!=NULL) {
			$sql_config['first'] = true;
			$sql_config['where'][] = "reference = '".$config['reference']."'";
		}
		if($config['field']!=NULL) {
			$sql_config['field'] = $config['field'];
		}
		if($id<=0) {
			$sql_config['where'][] = "status = '1'";
		}
		if(!$config['ovr_user_id']) {
			if($config['user_id']!=NULL) {
				$sql_config['where'][] = "user_id = '".$config['user_id']."'";
			} else {
				$sql_config['where'][] = "user_id = '".$class_user->authorised->id."'";
			}
		}
		$sql_config['sort'] = "company ASC";
		$data = zulu::table_data($this->SQL_table,$id,$sql_config);
		if($id > 0 || $sql_config['first']) {
			if($data['id'] > 0) {
				$data['name'] = (trim($data['company'])!=NULL?$data['company']:$data['name_first']." ".$data['name_last']);
				$data['name_full'] = $data['name_first']." ".$data['name_last'].(trim($data['company'])!=NULL?" (".$data['company'].")":NULL);
			}
		} else {
			foreach($data as $key=>$row) {
				$data[$key]['name'] = (trim($row['company'])!=NULL?$row['company']:$row['name_first']." ".$row['name_last']);
				$data[$key]['name_full'] = $row['name_first']." ".$row['name_last'].(trim($row['company'])!=NULL?" (".$row['company'].")":NULL);
			}
		}
		return $data;
	}
	function delete($id,$identifier='id') {
		global $class_user;
		$query = "UPDATE ".$this->SQL_table." SET type='0' WHERE `{$identifier}` = '".$id."' AND user_id='".$class_user->authorised->id."'";
		if($this->db->query($query)) {
			return true;
		} else {
			return false;
		}
	}
	function cancel_delete($id,$identifier='id') {
		global $class_user;
		$query = "UPDATE ".$this->SQL_table." SET status='0' WHERE `{$identifier}` = '".$id."' AND user_id='".$class_user->authorised->id."'";
		if($this->db->query($query)) {
			return true;
		} else {
			return false;
		}
	}
	function change_type($id,$type='1') {
		global $class_user,$zulu;
		$update_type = $type;

		//Add Status record
		$client = $this->client_data(['id'=>$id]);
		$last_row = $zulu->table_data($this->SQL_table_status,0,array('where'=>["client_id = '".$id."'"],'limit'=>1,'sort'=>'id DESC','first'=>true));

		if($last_row['id']<=0) {
			$last_time = $client['stat_add'];
			$period = time()-$last_time;
			$data = ['time'=>$last_time,'period'=>$period,'client_id'=>$id,'type'=>$client['type']];
			$skip_new = true;
		} else {
			$last_time = $last_row['time'];
			$last_id = $last_row['id'];
			$period = time()-$last_time;
			$data = ['period'=>$period];
			$skip_new = false;
		}

		if($last_id>0) {
			if($last_row['type']==$type) {
				$skip_new = true;
			}

			$field = $this->db->field_array($data);
			$query = "UPDATE ".$this->SQL_table_status." SET ".$this->db->build(1,$field,$data)." WHERE id = '".$last_id."'";
		} else {
			$field = $this->db->field_array($data);
			$query = "INSERT INTO ".$this->SQL_table_status." ".$this->db->build(0,$field,$data);
		}
		$this->db->query($query);

		if(!$skip_new) {
			$data = ['time'=>time(),'period'=>0,'client_id'=>$id,'type'=>$type];
			$field = $this->db->field_array($data);
			$query = "INSERT INTO ".$this->SQL_table_status." ".$this->db->build(0,$field,$data);
			$this->db->query($query);
		}

		//Update user record
		$query = "UPDATE ".$this->SQL_table." SET type='".$update_type."' WHERE `id` = '".$id."' AND user_id='".$class_user->authorised->id."'";
		if($this->db->query($query)) {
			return true;
		} else {
			return false;
		}
	}
	function client_find($name,$config=[]) {
		global $class_user;

		$name_split = explode(" ",$name);
		$where[] = "user_id = '".($config['user_id']==NULL?$class_user->authorised->id:$config['user_id'])."'";
		$where[] = "(company = '{$name}' OR (name_first = '".$name_split[0]."' AND name_last = '".$name_split[1]."' AND name_last != '' AND name_first != '')) AND status = 1";
		$data = zulu::table_data("client",0,['first'=>true,'where'=>$where]);
		if($data['id']>0) {
			return $data['id'];
		} else {
			$d = ['company'=>$name];
			$m = [];
			if(isset($config['data']['_root'])&&is_array($config['data']['_root'])) {
				$d += $config['data']['_root'];
			}
			if(isset($config['data']['_meta'])&&is_array($config['data']['_meta'])) {
				$m += $config['data']['_meta'];
			}
			$val = $this->client_edit(0,$d,$m);
			return $val['id'];
		}
	}
	function client_new($config=array()) {
		global $class_user;

		$data['user_id'] = ($data['user_id']==NULL?$class_user->authorised->id:$config['user_id']);
		$data['stat_add'] = time();
		$data['company'] = '';
		$data['type'] = (isset($config['type'])?$config['type']:1);
		$data['token'] = zulu::serial();
		//$data['hourly_rate'] = zulu::serial();

		$query = "INSERT INTO ".$this->SQL_table." ".$this->db->build(2,array('company','stat_add','type','token','hourly_rate','user_id'),$data);

		if($this->db->query($query)) {
			$new_id = $this->db->insert_id;
			$this->change_type($new_id,$data['type']);
			return array("success"=>true,"id"=>$new_id);
		} else {
			return array("success"=>false,"id"=>0);
		}
	}
	function client_edit($id,$config=array(),$meta=array()) {
		global $class_user,$zulu;
		$data['stat_update'] = time();
		$fields[] = 'stat_update';

		if($id<1) {
			$data = $this->client_new(array('type'=>$config['type'],'user_id'=>$config['user_id']));
			$id = $data['id'];
		}

		foreach($config as $key=>$val) {
			$data[$key] = $val;
			$fields[] = $key;
		}

		$query = "UPDATE ".$this->SQL_table." SET ".$this->db->build(1,$fields,$data)." WHERE id = '{$id}'".($data['user_id']==NULL?" AND user_id='".$class_user->authorised->id."'":NULL);
		if($this->db->query($query)) {
			if(count($meta)>0) {
				foreach($meta as $mkey=>$mval) {
					$zulu->meta_update('client',$id,$mkey,$mval);
				}
			}

			$data = $this->client_data(['id'=>$id]);
			return array("success"=>true,"id"=>$id,"raw"=>$data);
		} else {
			return array("success"=>false);
		}
	}

	function lead_data($config=array()) {
		global $class_user;
		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);
		if($config['first']) {
			$sql_config['first'] = true;
		}
		$sql_config['where'][] = "type = '2'";
		$sql_config['where'][] = "status = '1'";
		$sql_config['sort'] = "company ASC";
		$sql_config['where'][] = "user_id = '".$class_user->authorised->id."'";

		$data = zulu::table_data($this->SQL_table,$id,$sql_config);
		if($id>0) {
			$data['name'] = (trim($data['company'])!=NULL?$data['company']:$data['name_first']." ".$data['name_last']);
		} else {
			foreach($data as $key=>$row) {
				$data[$key]['name'] = (trim($row['company'])!=NULL?$row['company']:$row['name_first']." ".$row['name_last']);
			}
		}
		return $data;
	}
	function lead_new($config=array()) {
		global $class_user;
		$data['user_id'] = $class_user->authorised->id;
		$data['stat_add'] = time();
		$data['company'] = 'Untitled Company';
		$data['token'] = zulu::serial();
		$data['type'] = 2;

		$query = "INSERT INTO ".$this->SQL_table." ".$this->db->build(2,array('company','stat_add','token','type','user_id'),$data);

		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$this->db->insert_id);
		} else {
			return array("success"=>false,"id"=>0);
		}
	}
	function lead_edit($id,$config=array()) {
		global $class_user;
		$data['stat_update'] = time();
		$fields[] = 'stat_update';

		if($id<1) {
			$data = $this->lead_new();
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
	function client_log_data($config=array()) {
		global $class_user;
		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);
		if($config['client_id']!=NULL) {
			$sql_config['where'][] = "client_id = '".$config['client_id']."'";
		}
		if($config['first']) {
			$sql_config['first'] = true;
		}
		$sql_config['where'][] = "user_id = '".$class_user->authorised->id."'";
		$log_data = zulu::table_data("client_log",$id,$sql_config);
		return $log_data;
	}
	function client_log_new($config=array()) {
		global $class_user;
		$data['user_id'] = $class_user->authorised->id;
		$data['stat_add'] = time();
		$data['stat_update'] = time();
		$data['client_id'] = $config['client_id'];
		$data['user_id'] = $config['user_id'];
		$data['notes'] = $config['notes'];
		$data['team_id'] = $config['team_id'];

		$query = "INSERT INTO client_log ".$this->db->build(2,array('client_id','user_id','notes','stat_add','stat_update','team_id'),$data);

		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$this->db->insert_id);
		} else {
			return array("success"=>false,"id"=>0);
		}
	}
	function client_log_edit($id,$config=array()) {
		global $class_user;
		$data['stat_update'] = time();
		$fields[] = 'stat_update';

		if($id<1) {
			$data = $this->client_log_new();
			$id = $data['id'];
		}

		foreach($config as $key=>$val) {
			$data[$key] = $val;
			$fields[] = $key;
		}

		$query = "UPDATE client_log SET ".$this->db->build(1,$fields,$data)." WHERE id = '{$id}' AND user_id='".$class_user->authorised->id."'";

		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$id);
		} else {
			return array("success"=>false);
		}
	}
	function client_meta($id,$field=NULL) {
		return zulu::meta_value("client",$id,$field);
	}

	function client_count($type=1,$role=NULL) {
		global $class_user;
		if($role != NULL) {
			$roleSQL = " AND m.value='".$role."'";
		}
		$query = $this->db->mysqli->query("SELECT c.id AS client_id FROM client AS c LEFT JOIN client_meta AS m ON c.id=m.identifier AND field='role' WHERE user_id='".$class_user->authorised->id."' AND type='".$type."' AND status='1'{$roleSQL}") or die($this->db->mysqli->error);
		return $query->num_rows;
	}

	function role_data($config=array()) {
		global $class_user;
		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);
		$sql_config['where'][] = "user_id = '".$class_user->authorised->id."'";
		$log_data = zulu::table_data("client_role",$id,$sql_config);
		return $log_data;
	}
	function role_delete($id,$identifier='id') {
		global $class_user;
		$query = "DELETE FROM client_role WHERE `{$identifier}` = '".$id."' AND user_id = '".$class_user->authorised->id."'";
		if($this->db->query($query)) {
			return true;
		} else {
			return false;
		}
	}
	function role_new($config=array()) {
		global $class_user;
		$data['user_id'] = $class_user->authorised->id;
		$data['name'] = stripslashes($config['title']);
		$data['tag'] = stripslashes($config['tag']);
		$data['data'] = serialize($config['data']);
		if($config['tag']==NULL) {
			$data['tag'] = zulu::slug($data['title']);
		}
		$query = "INSERT INTO client_role ".$this->db->build(1,array('user_id','tag','name','data'),$data);
		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$this->db->insert_id);
		} else {
			return array("success"=>false,"id"=>0);
		}
	}

	//-- Front End
	function login($config) {
		global $zulu,$class_user,$class_sale;

		$data = $this->client_data(['email'=>$config['email']]);
        $client = Clients::where([['email',$config['email']],['status',1],['type',1]])->first();
		$id = $data['id'];
		$client_meta = $zulu->meta_array($this->client_meta($data['id']));

		$authorised = ['valid'=>true];
		if($client_meta['web_verify']!=1) {
			$authorised['msg'] = "Please verify your account to sign in, check your email inbox.";
			$authorised['valid'] = false;
		} elseif($client_meta['web_access']!=1) {
			$authorised['msg'] = "This account is not valid for website sign in access.";
			$authorised['valid'] = false;
		}
        if($authorised['valid']) {
            $cpr = $client->passwordResets()->latest()->first();
            if($cpr != null && $cpr->date_complete == 0) {
                $authorised['msg'] = "Your account has been locked until you reset your password. If you have been provided a link to reset your password please use that to login, otherwise reset your password below.";
                $authorised['valid'] = false;
                $authorised['url'] = FE_rel."members/forgot.php";
            }
        }
        if($authorised['valid']) {
            if($data['password'] != $class_user->password_hash($config['password'])) {
                $authorised['msg'] = "The email / password combination is incorrect.";
                $authorised['valid'] = false;
            }
        }

		if($authorised['valid']) {
			$zulu->meta_update('client',$id,'web_logins',$client_meta['web_logins']+1);
			$zulu->meta_update('client',$id,'web_last',time());

			$this->load_session($id);

            if(!isset($authorised['url']) || $authorised['url'] == null) {
                if($_SESSION['SH_Login_Goto']!=NULL) {
                    $authorised['url'] = $_SESSION['SH_Login_Goto'];
                    $_SESSION['SH_Login_Goto'] = NULL;
                } else {
                    $authorised['url'] = $this->frontend_default_page();
                }
            }
		}
		return $authorised;
	}
	function frontend_auth() {
		if($_SESSION['user']['id']>0) {
			return true;
		} else {
			return false;
		}
	}

    public function frontend_default_page() {
		global $class_setting,$class_post;

		$redir = explode('_',$class_setting->data['ws_mem_login_page']);
		switch($redir[0]) {
			case 'post':
				$post_id = $redir[1];
				if($post_id<=0) {
					$url = FE_rel."members/";
				} else {
					$url = $class_post->post_url($post_id);
				}
				break;
			case 'shop':
				$url = FE_rel."browse/";
				break;
			case 'default':
			default:
				$url = FE_rel."members/";
				break;
		}
		return $url;
	}

	public function load_session($user_id) {
		global $zulu, $class_sale;

		$data = $this->client_data(['id'=>$user_id]);
		$client_meta = $zulu->meta_array($this->client_meta($data['id']));

        //-- Move Cart Items
		$class_sale->cart_move('client',$user_id);

		foreach($data as $item=>$val) {
			$_SESSION['user'][$item] = $val;
		}
		$_SESSION['user']['_meta'] = $client_meta;
		$_SESSION['user']['_signin'] = time();
		$_SESSION['user']['_ip'] = $_SERVER['REMOTE_ADDR'];
		$_SESSION['user']['_agent'] = $_SERVER['HTTP_USER_AGENT'];

		$_SESSION['TIMEZONE'] = $data['timezone'];

		return;
	}
	function client_subscribed($client_id) {
		global $class_renew;
		$renew_data = $class_renew->renew_data(['client_id'=>$client_id]);
		foreach($renew_data as $renew_row) {
			if($renew_row['renew_next'] > time() && $renew_row['status']=='1') {
				return true;
			}
		}
		return false;
	}
	function client_subscriptions($client_id=0,$config=[]) {
		global $class_renew;
		$return[] = 0;
		if($client_id>0) {
			$renew_data = $class_renew->renew_data(['client_id'=>$client_id]);
			foreach($renew_data as $renew_row) {
				if(($renew_row['renew_next']>time() || $config['expired']) && $renew_row['status']=='1' && $renew_row['template_id'] > 0) {
					$return[] = $renew_row['template_id'];
				}
			}
		}
		return $return;
	}
	function client_renewables($client_id=0,$config=[]) {
		global $class_renew;
		$return[] = 0;
		if($client_id>0) {
			$renew_data = $class_renew->renew_data(['client_id'=>$client_id]);
			foreach($renew_data as $renew_row) {
				if(($renew_row['renew_next']>time() || $config['expired']) && $renew_row['status']=='1' && (!$config['template_id'] || $renew_row['template_id']==$config['template_id'])) {
					$return[] = $renew_row['id'];
				}
			}
		}
		return $return;
	}
	function email_token_meta($token) {
		$query = $this->db->mysqli->query("SELECT * FROM client_meta WHERE field='pitexit_email_token' AND value='".$token."'") or die($this->db->mysqli->error);
		$row = $query->mysqli->fetch_array();
		return $row;
	}
	function client_name($data) {
		if(is_array($data)) {
			$row = $data;
		} else {
			$row = $this->client_data(['id'=>$data]);
		}
		return stripslashes(($row['company']!=NULL?$row['company']:$row['name_first']." ".$row['name_last']));
	}
	function admin_link($id=0,$config=[]) {
		global $zulu;

		if($id==0) {
			$data = $this->vars->data_row;
		} else {
			$data = $this->client_data(['id'=>$id]);
		}
		return "<a ".(isset($config['target'])?"target=\"".$config['target']."\"":NULL)." href=\"".$zulu->link_page('client',array('query'=>array('id'=>$data['id'],'Action'=>'edit')))."\">".$this->client_name($data)."</a>";
	}
}
$class_client = new client($MAIN_config);
