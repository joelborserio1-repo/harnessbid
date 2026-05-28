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
//MODULE NAME: ZULU Shopfront - Pay In-store
//MODULE COMPATIBILITY: ZULU Shopfront v2.1.3+
//MODULE VERSION: v1.2

//Module Functions***********

class m_instore {

	function __construct($config=[]) {

		$this->type = 1;
		$this->class_name = 'm_instore';
		$this->filename = 'm_instore.php';
		$this->title = 'Pay In-store';
		$this->title_client = 'Pay In-store';
		$this->info_client = 'You can pay for your order on pickup.';
		$this->online = false;
	}

	//##################################### INSTALL/UNINSTALL
	function install() {
		global $class_module,$zulu;

		$check = $class_module->module_data(['class'=>$this->class_name]);
		if($check['id'] <= 0) {
			$result = $class_module->module_edit(0,['type'=>$this->type,'name'=>$this->title,'name_client'=>$this->title_client,'info_client'=>$this->info_client,'class'=>$this->class_name,'file'=>$this->filename]);
			if($result['success']) {
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
			return ['success'=>true];
		} else {
			return ['success'=>false,'reason'=>'Module not installed.'];
		}
	}

	//##################################### ADMIN FUNCTIONS
	// function admin_form_edit() {} - NOT USED
	function admin_form_process() {
		global $class_module,$zulu;
		$module_row = $class_module->module_data(['class'=>$this->class_name]);
		return ['success'=>true];
	}

	//##################################### CHECKOUT FUNCTIONS
	function checkout_select_html() {
		global $class_module;

		$module_row = $class_module->module_data(['class'=>$this->class_name]);
		return ($module_row['info_client']?"<p>".stripslashes($module_row['info_client'])."</p>":null);
	}
	function checkout_summary_formsubmitlabel() {
		return "";
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

	//##################################### ORDER FUNCTIONS
	function order_pay_now() {
		global $class_module,$zulu,$sale_ref;

		$module_row = $class_module->module_data(['class'=>$this->class_name]);
		$module_meta = $zulu->meta_array($class_module->module_meta($module_row['id']));
		return "
			<p><b>Pay In-store:</b></p>
            <p>If you've chosen to pickup your order then you can also pay in-store.</p>";
	}

}

?>
