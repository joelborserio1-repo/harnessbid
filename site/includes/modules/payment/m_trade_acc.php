<?php
//(C)2007-2014 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

//*********************NOTE:**********************//
//This is a open-source module built into Zulu    //
//Shopfront. It is highly illegal to resell this  //
//module.										  //
//************************************************//

//MODULE TYPE: Payment
//MODULE NAME: ZULU Shopfront - Trade Account
//MODULE COMPATIBILITY: ZULU Shopfront v2.1.3+
//MODULE VERSION: v1.2

//Module Functions***********

class m_trade_acc {

	function __construct($config=[]) {

		$this->type = 1;
		$this->class_name = 'm_trade_acc';
		$this->filename = 'm_trade_acc.php';
		$this->title = 'Trade Account';
		$this->title_client = 'Trade Account';
		$this->info_client = 'Place this order on your account.';
		$this->online = false;
	}

	//##################################### INSTALL/UNINSTALL
	function install() {
		global $class_module,$zulu;

		$check = $class_module->module_data(['class'=>$this->class_name]);
		if($check['id'] <= 0) {
			$result = $class_module->module_edit(0,['type'=>$this->type,'name'=>$this->title,'name_client'=>$this->title_client,'info_client'=>$this->info_client,'class'=>$this->class_name,'file'=>$this->filename]);
			if($result['success']) {
				$zulu->meta_update('module',$result['id'],'bank_name','');
				$zulu->meta_update('module',$result['id'],'bank_number','');
				return ['success'=>true, 'id'=>$result['id']];
			} else {
				return ['success'=>false, 'reason'=>'Error installing module.'];
			}
		} else {
			return ['success'=>false, 'reason'=>'Module already installed.'];
		}
	}
	function uninstall() {
		global $class_module,$zulu;

		$check = $class_module->module_data(['class'=>$this->class_name]);
		if($check['id'] > 0) {
			$class_module->delete($check['id']);
			$zulu->meta_remove('module',$check['id'],'bank_name');
			$zulu->meta_remove('module',$check['id'],'bank_number');
			return ['success'=>true];
		} else {
			return ['success'=>false,'reason'=>'Module not installed.'];
		}
	}

	//##################################### ADMIN FUNCTIONS
	function admin_form_process() {
		return ['success'=>true];
	}

	//##################################### CHECKOUT FUNCTIONS
	function checkout_select_html() {
		global $class_module;

		$module_row = $class_module->module_data(['class'=>$this->class_name]);
		return ($module_row['info_client']?"<p>".stripslashes($module_row['info_client'])."</p>":null);
	}
	function checkout_summary_formsubmitlabel() {
		$val = "";
		return $val;
	}
	function checkout_summary_formcapt() {
		$val = "You agree to pay the due amount."; //Default Value
		return $val;
	}
	function process_payment() {
		//do nothing
		return true;
	}
	function validate_payment() {
		//do nothing
		return true;
	}
	function checkout_toggle_module() {
		global $row,$class_client,$zulu;

		if($row['client_id']<=0) {
			return false;
		} else {
			$client = $zulu->meta_value('client',$row['client_id'],'trade_account');
			if($client['value']==1) {
				return true;
			} else {
				return false;
			}
		}
	}

	//##################################### ORDER FUNCTIONS
	
}

?>
