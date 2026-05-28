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

class m_bank_deposit {

	function __construct($config=[]) {

		$this->type = 1;
		$this->class_name = 'm_bank_deposit';
		$this->filename = 'm_bank_deposit.php';
		$this->title = 'Bank Deposit';
		$this->title_client = 'Bank Deposit';
		$this->info_client = 'You can deposit the order total into our bank account.';
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
	function admin_form_edit() {
		global $form_edit;

		$html = "
			<div class=\"row\">
				<div class=\"col-lg-6\">
					<div class=\"form-group\">
						<label>Bank Name</label>
						".$form_edit->input_html('input','bank_name',$_POST['meta']['bank_name'])."
					</div>
				</div>
				<div class=\"col-lg-6\">
					<div class=\"form-group\">
						<label>Bank Number</label>
						".$form_edit->input_html('input','bank_number',$_POST['meta']['bank_number'])."
					</div>
				</div>
			</div>";
		return $html;
	}
	function admin_form_process() {
		global $class_module,$zulu;
		$module_row = $class_module->module_data(['class'=>$this->class_name]);
		$zulu->meta_update('module',$module_row['id'],'bank_name',$_POST['bank_name']);
		$zulu->meta_update('module',$module_row['id'],'bank_number',$_POST['bank_number']);
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
			<p><b>Deposit Funds:</b></p>
            <p>Account Name: ".stripslashes($module_meta['bank_name'])."<br>
				Account Number: ".stripslashes($module_meta['bank_number'])."<br>
				Reference: Order #".$sale_ref."<br>
				<span class=\"text-small color-grey\"><span class=\"fas fa-exclamation-triangle\"></span> Your order will be updated by an administrator when paid.</span></p>";
	}

}

?>
