<?php
//(C)2007-2014 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

//*********************NOTE:**********************//
//This is a 1 license per-site module built into  //
//Zulu Shopfront. It is highly illegal to resell  //
//this module.			   						  //
//************************************************//

//MODULE TYPE: Payment
//MODULE NAME: ZULU Shopfront - PayPal Process
//MODULE BUILD DATE: 31.1.14
//MODULE COMPATIBILITY: ZULU Shopfront v2.0.2+

//Module Functions***********

class m_paypal {

	function __construct($config=[]) {

		$this->type = 1;
		$this->class_name = 'm_paypal';
		$this->filename = 'm_paypal.php';
		$this->title = 'Credit Card (PayPal)';
		$this->title_client = 'Credit Card (PayPal)';
		$this->info_client = 'Securely complete your purchase with your credit card via PayPal.';
		$this->online = true;

		$this->config->default_currency = 'NZD';
		$this->config->test_mode = true;
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
	function admin_form_edit() {
		global $form_edit;

		$html = "
			<div class=\"row\">
				<div class=\"col-lg-6\">
					<div class=\"form-group\">
						<label>PayPal Email</label>
						".$form_edit->input_html('input','paypal_email',$_POST['meta']['paypal_email'])."
					</div>
				</div>
				<div class=\"col-lg-6\">
					<div class=\"form-group\">
						<label>Currency</label>
						".$form_edit->input_html('input','paypal_currency',($_POST['meta']['paypal_currency']!=NULL?$_POST['meta']['paypal_currency']:$this->config->default_currency),['custom'=>['placeholder'=>$this->config->default_currency]])."
					</div>
				</div>
			</div>";
		return $html;
	}
	function admin_form_process() {
		global $class_module,$zulu;
		$module_row = $class_module->module_data(['class'=>$this->class_name]);
		$zulu->meta_update('module',$module_row['id'],'paypal_email',$_POST['paypal_email']);
		$zulu->meta_update('module',$module_row['id'],'paypal_currency',$_POST['paypal_currency']);
		return ['success'=>true];
	}

	//##################################### CHECKOUT FUNCTIONS
	function checkout_select_html() {
		global $class_module;

		$module_row = $class_module->module_data(['class'=>$this->class_name]);
		return ($module_row['info_client']?"<p>".stripslashes($module_row['info_client'])."</p>":null);
	}
	function checkout_summary_formsubmitlabel() {
		$val = "Confirm & Pay";
		return $val;
	}
	function checkout_summary_formcapt() {
		$val = "You agree to pay the due amount. Please click the \"Return to Merchant\" button after you make your payemnt.";
		return $val;
	}
	function process_payment($sale_id, $config=[]) {
		global $class_sale,$zulu,$class_module;

		$sale_data = $class_sale->sale_data(['id'=>$sale_id]);
		$module_row = $class_module->module_data(['class'=>$this->class_name]);
		$module_meta = $zulu->meta_array($class_module->module_meta($module_row['id']));

		if($module_meta['paypal_email']=='' || $module_meta['paypal_currency']=='') {
			$fatal = true;
		}

		$amount = $config['amount'];
		$email = $config['email'];
		if($email == NULL) {
			$email = SUPPORT_email;
		}

		$arrPaypal= array();
		$arrPaypal['cmd'] = "_xclick";
		$arrPaypal['business'] = $module_meta['paypal_email'];
		$arrPaypal['currency_code'] = $module_meta['paypal_currency'];
		$arrPaypal['item_name'] = MAIN_company.($sale_data['reference']>0?" Sale #".$sale_data['reference']:$sale_data['name']);
		$arrPaypal['custom'] = $sale_id;
		$arrPaypal['amount'] = sprintf('%0.2f', $amount);
		$arrPaypal['notify_url'] = MAIN_url."includes/modules/module_files/".$this->class_name."/ipn.php";
		$arrPaypal['return'] = FE_url."members/order_view.php?token=".$sale_data['token'];
		$arrPaypal['no_shipping'] = "1";
		$arrPaypal['shipping'] = "0";
		$arrPaypal['rm'] = "2";

		$strPaypal = http_build_query($arrPaypal);
		if($this->config->test_mode) {
			$uri_val = "https://www.sandbox.paypal.com/cgi-bin/webscr?" . $strPaypal;
		} else {
			$uri_val = "https://www.paypal.com/cgi-bin/webscr?" . $strPaypal;
		}

		if(trim($uri_val)==NULL || $fatal) {
			return ['success'=>false, 'msg'=>'This payment option currently isn\'t available.'];
		} else {
			header("Location: ".$uri_val);
			exit();
		}
	}
	function validate_payment() {
		global $class_sale,$zulu,$class_module;

		if($_POST) {
			//Do Data
			$sale_id = $_POST['custom'];
			$sale_data = $class_sale->sale_data(['id'=>$sale_id]);
			$module_row = $class_module->module_data(['class'=>$this->class_name]);

			if(isset($_POST['ipn_track_id'])) {
				//Validate
				if($_POST['payment_status'] == 'Pending' || $_POST['payment_status'] == 'Completed') {
					$success = true;
					$data['valid'] = 1;
				} else {
					$success = false;
					$data['valid'] = 0;
				}

				//Data
				$data['sale_id'] = $sale_id;
				$data['client_id'] = $sale_data['client_id'];
				$data['txn_id'] = $_POST['txn_id'];
				$data['amount'] = $_POST['mc_gross'];
				$data['date'] = $_POST['payment_date'];
				$data['status'] = $_POST['payment_status'];
				$data['data'] = serialize($_POST);

				//Check Duplicate
				$payment_data = $class_sale->payment_data(['sale_id'=>$sale_id,'module_id'=>$module_row['id']]);

				//Process
				if($success && count($payment_data)<=0) {
					$result = $this->add_payment($sale_id,$data);
				}
			} elseif(isset($_POST['txn_id'])) {
				if($_POST['payment_status'] == 'Pending' || $_POST['payment_status'] == 'Completed') {
					$success = true;
				} else {
					$success = false;
				}

				$payment_data = $class_sale->payment_data(['sale_id'=>$sale_id,'module_id'=>$module_row['id'],'ovr_user_id'=>true,'first'=>true]);
				$txn_data = unserialize($payment_data['module_data']);

				$return = ['sale_id'=>$sale_id,'token'=>$sale_data['token']];

				//Process
				if($success && $txn_data['txn_id']==$_POST['txn_id']) {
					$return['success'] = true;
				} else {
					if($payment_data['id'] > 0) {
						$return['success'] = false;
						$return['msg'] = "This has already been paid for, please contact us to manually complete your order. Do not make any further payments please.";
					} else {
						$return['success'] = false;
						$return['msg'] = "Your card was declined possibly due to an invalid card or lack of funds.";
					}
				}
			}
		}
		return $return;
	}
	function add_payment($sale_id,$data) {
		global $class_sale,$class_user,$zulu;

		$sale_data = $class_sale->sale_data(['id'=>$sale_id]);
		$sale_meta = $zulu->meta_array($class_sale->sale_meta($sale_id));

		$approved = ($data['valid']==1?true:false);
		if(!$approved) {
			$data['amount'] = 0;
		}
		$p_data = [
			'sale_id'		=> $sale_id,
			'info'			=> 'PayPal',
			'pay_total'		=> $data['amount'],
			'valid'			=> $data['valid'],
			'method'		=> 'paypal',
			'module_id'		=> $sale_meta['module_payment'],
			'module_data'	=> addslashes($data['data']),
			'email_admin'	=>	true,
			'email_client'	=>	true
		];
		if($approved) $p_data['complete'] = true;

		$result = $class_sale->payment_create($p_data);
		if($result['success']) {
			return array("success"=>true,"id"=>$result['id']);
		} else {
			return array("success"=>false,"id"=>0);
		}
	}

	//##################################### ORDER FUNCTIONS
	function order_pay_now() {
		global $class_module,$zulu;

		$module_row = $class_module->module_data(['class'=>$this->class_name]);
		$module_meta = $zulu->meta_array($class_module->module_meta($module_row['id']));
		return "
			<p><b>Pay now online:</b></p>
            <a href=\"".$zulu->front_link(true,['self'=>true,'query'=>['Action'=>'ManualPay','Module'=>$module_row['token']]])."\" class=\"button\">Pay Now via Credit Card</a><br><br>
			<span class=\"text-small color-grey\"><span class=\"fas fa-lock\"></span> Payment via PayPal.</span>";
	}


/*function checkout_pre_summary() { //Carried out before / during calculation of cost & order summary.
	global $pay_verify;
	global $pay_stat;
	global $rand;

	//pay_stat var | 0 = no pay | 1 = pay

	//Check for payment completion
	$query_CHECK = mysql_query("SELECT * FROM sh_mod_pay_zpypl_ipn WHERE oid = '$rand'");
	$row_CHECK = mysql_fetch_array($query_CHECK);
	$nrow_CHECK = mysql_num_rows($query_CHECK);

	//No row?
	if($nrow_CHECK==0) {
		$pay_stat = 0;
		$pay_verify = 0;
	} else {
		$pay_stat = ($row_CHECK['status']=="Completed" ? 1 : 0); //If 'complteted' then we give it a 1 value to indicate completion.
		$pay_verify = ($pay_stat==1 ? 1 : 0);

		if($pay_stat>0) {
			header("Location: complete.php?Action=Complete");
			exit;
		}
	}


	return true;
}
function checkout_pre_complete() { //Carried out before verification.
	//do nothing
	return true;
}
function checkout_calc_extra() {
	//do nothing
	return 0;
}
function checkout_summary_formaction() { //Where does the summary form action to?
	global $pay_verify;
	if($pay_verify==0) {
		$val = "#";
	} else {
		$val = "complete.php?Action=Complete";
	}
	return $val;
}

function checkout_summary_formhide() { //Where does the summary form action to?
	global $pay_verify;
	if($pay_verify==0) {
		return false;
	} else {
		return true;
	}
	return $val;
}

function checkout_form_payment() { //Form to show to client when summary.
	global $p_total;
	global $main_COMPANY;
	global $main_URL;
	global $contact_EMAIL;
	global $rand;
	global $pay_stat;
	global $pay_verify;
	global $row_CHECK;
	global $MODULE_test;
	global $nrow_CHECK;

	//Check for payment completion
	$query_CHECK = mysql_query("SELECT * FROM sh_mod_pay_zpypl_ipn WHERE oid = '$rand'");
	$row_CHECK = mysql_fetch_array($query_CHECK);
	$nrow_CHECK = mysql_num_rows($query_CHECK);

	//Payed? But bad amount?
	if($pay_stat==1&&$row_CHECK['amount']<$p_total) {
		$output .= "<p class=\"text-small red\">Your payment amounts do not match, please contact us <a href=\"mailto:{$contact_EMAIL}\" target=\"_blank\">here</a>.</p>";
	}

	//Payed?
	if($pay_stat==1) {
		$pay_verify = 1; //set the global payment verify var equal to 1 so the order can complete
		$output .= "<p class=\"text-small\" style=\"color:#2ea00b;\">We received your payment successfully via Paypal for $".number_format($row_CHECK['amount'],2).".</p>";
		$output .= "<p class=\"text-small grey\">Transaction ID: ".$row_CHECK['txnid'].".</p>";
	}

	//No Pay? We show paypal form.
	if($pay_stat!=1) {
		$query = "SELECT * FROM sh_mod_pay_zpypl_settings";
		$exe = mysql_query($query);
		$row = mysql_fetch_array($exe);
		$form_PAYPAL = "

	".($MODULE_test?"
	<form name=\"_xclick\" action=\"https://www.sandbox.paypal.com/cgi-bin/\" method=\"post\">":
	"<form name=\"_xclick\" action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\">")."

		<input type=\"hidden\" name=\"cmd\" value=\"_xclick\">
		<input type=\"hidden\" name=\"business\" value=\"".$row['paypal_email']."\">
		<input type=\"hidden\" name=\"currency_code\" value=\"".($row['paypal_currency']=="" ? "NZD" : $row['paypal_currency'])."\">
		<input type=\"hidden\" name=\"item_name\" value=\"".$main_COMPANY." Order\">
		<input type=\"hidden\" name=\"custom\" value=\"{$rand}\">
		<input type=\"hidden\" name=\"amount\" value=\"{$p_total}\">
		<input type=\"hidden\" name=\"notify_url\" value=\"{$main_URL}includes/modules/module_files/zsf_paypal/ipn.php\">
		<input type=\"hidden\" name=\"return\" value=\"{$main_URL}checkout/summary.php\">
		<input type=\"hidden\" name=\"no_shipping\" value=\"1\" />
		<input type=\"hidden\" name=\"shipping\" value=\"0\" />
		<input type=\"hidden\" name=\"rm\" value=\"2\" />
		<input type=\"image\" src=\"http://www.paypal.com/en_US/i/btn/btn_buynow_LG.gif\" border=\"0\" name=\"submit\" alt=\"Make payments with PayPal - it's fast, free and secure!\">
	</form>
	<hr/>
	".($MODULE_test?"<p class=\"text-mini red\"><b>Warning: PayPal is in test mode, please contact us if you're trying to place a valid order.</b></p>":null)."
	<p class=\"text-mini grey\">Note: You will be automatically returned to this page once payment processes. Then you're order will be processed.</p>";
		$output .= "<p>Before you can submit your order please pay via PayPal, just click the button below...</p>".$form_PAYPAL;
	}

	return $output;
	//https://www.paypal.com/cgi-bin/webscr
}
function checkout_verify_payment() { //Check payment has been processed, if yes then order will complete. Use return 'true' or 'false'.
	global $client_output;
	global $oid;
	global $rand;
	global $contact_NAME;
	global $contact_EMAIL;
	global $main_URL;

	//Check for payment completion
	$query_CHECK = mysql_query("SELECT * FROM sh_mod_pay_zpypl_ipn WHERE oid = '$rand'");
	$row_CHECK = mysql_fetch_array($query_CHECK);
	$nrow_CHECK = mysql_num_rows($query_CHECK);

	//No row?
	if($nrow_CHECK==0) {
		$pay_stat = 0;
		$pay_verify = 0;
	} else {
		$pay_stat = ($row_CHECK['status']=="Completed" ? 1 : 0); //If 'complteted' then we give it a 1 value to indicate completion.
		$pay_verify = ($pay_stat==1 ? 1 : 0);
	}

	//Add Status
	if($pay_stat==1) {
		orderNewStatus($oid,"Order Placed (Payment Received)","Your order was added to our system and is subject to processing.",1);
		return true;
	} else {
		$client_output = "Your payment failed to complete via PayPal.";

		//$to = $contact_EMAIL;
		//$sub = "Order Payment Failed";
		//$mess = "Hello,</p><p>An order was placed on your website, but the payment failed via PayPal. The order number is <b>".$oid."</b></p><p>Please view more regarding this order <a href=\"{$main_URL}admin/order_view.php?OrderID={$oid}\">here</a>.</p>";
		//sendMail($to,$sub,$mess);

		orderNewStatus($oid,"Order Placed (Payment Failed)","Your order was added however your payment failed to go through.",1);
		return false;
	}
}*/
}

?>
