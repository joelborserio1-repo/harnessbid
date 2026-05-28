<?php
//--GLOBAL SMS
class mod_sms {

	public $SQL_table = 'sms_log';

	function __construct() {
		global $db;
		$this->db = $db;

		$this->config = new stdClass();
		$this->config->username = 'odfx5rps';
		$this->config->password = 'wZxNFyV9';
	}
	function log_data($config=[]) {
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
		if($config['first']) {
			$sql_config['first'] = true;
		}
		$sql_config['sort'] = 'id DESC';
		$sql_config['where'][] = "user_id = '".$class_user->authorised->id."'";
		return zulu::table_data($this->SQL_table,$id,$sql_config);
	}
	function credit_balance() {
		global $zulu,$class_user;
		$data = $zulu->table_data($this->SQL_table,0,['where'=>['user_id = '.$class_user->authorised->id],'field'=>['SUM(credit) AS credit_balance']]);
		return (trim($data[0]['credit_balance'])==NULL?'0':$data[0]['credit_balance']);
	}
	function text_send($config=[]) {
		global $zulu,$class_user,$class_setting;

		$s_mobile = preg_replace("/[^0-9]/", "", $config['mobile']);
        //$s_message = ($class_setting->data['name']!=null?stripslashes($class_setting->data['name']).":%0a":null).trim($config['message']);
		$s_message = trim($config['message']);

		//compile recip phone
		/*if($s_mobile[0]."".$s_mobile[1]!=64) {
			$s_mobile = "64".substr($s_mobile,1);
		}*/

		$data_url = "https://api.smsglobal.com/http-api.php?user=".$this->config->username."&password=".$this->config->password."&action=sendsms&from=1&to=".$s_mobile."&text=".urlencode($s_message);
print_r($data_url);exit;
		$data_request = file_get_contents($data_url);
		print_r($data_request);exit;
		$data_split = explode(":",$data_request);
		$data_ret_mess = $data_split[0];
		$data_ret_id = preg_replace('/\s+/', '', str_replace("SMSGlobalMsgID","",$data_split[2]));
		$data_ret_smsglob_id = preg_replace('/\s+/', '', $data_split[3]);

		$this->db->query("INSERT INTO sms_log (user_id,credit,recipient,message,server_response,server_sent,server_id,server_msg_id,object,object_id,stat_add) VALUES (
					'".$class_user->authorised->id."',
					'-1',
					'".$s_mobile."',
					'".addslashes($s_message)."',
					'".$data_ret_mess."',
					'".($data_split[0]=='OK'?'1':'0')."',
					'".$data_ret_id."',
					'".$data_ret_smsglob_id."',
					'".$config['object']."',
					'".$config['object_id']."',
					'".time()."'
					)");

		$output = [
			'msg_id'		=>	$data_ret_smsglob_id,
			'success'	=>	($data_split[0]=='OK'?true:false),
			'_raw'	=>	$data_split,
		];
		return $output;
	}
	function add_credit($amount,$config=[]) {
		global $class_user;

		$this->db->query("INSERT INTO sms_log (user_id,credit,credit_reference,stat_add) VALUES (
					'".$class_user->authorised->id."',
					'".$amount."',
					'".$config['reference']."',
					'".time()."'
					)");
	}
}
