<?php
//(C)2007-2013 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.0.8
//BUILT ON PHP & MySQL

//*********************NOTE:**********************//
//This is a 1 license per-site module built into  //
//Zulu Shopfront. It is highly illegal to resell  //
//this module.			   						  //
//************************************************//

//MODULE TYPE: Payment
//MODULE NAME: ZULU Shopfront - Credit Card (eWAY)
//MODULE BUILD DATE: 14.11.13
//MODULE COMPATIBILITY: ZULU Shopfront v2.0.8+

//Module Functions***********

class m_eway {

	function __construct($config=[]) {

		$this->type = 1;
		$this->class_name = 'm_eway';
		$this->filename = 'm_eway.php';
		$this->title = 'Credit Card (eWAY)';
		$this->title_client = 'Credit Card (eWAY)';
		$this->info_client = 'Securely complete your purchase with your credit card via eWAY.';
		$this->online = true;

		$this->test->api_key = 'F9802CgJbGwi9jUsVNESICRfOlAFhRP92USxrQCOvU+vf69IoWcsL8hKwvrsh4CUtiUgTS';
		$this->test->api_password = 't77cStC7';
		$this->test->api_endpoint = 'Sandbox';
		$this->test->test_mode = true;

	}

	//##################################### INSTALL/UNINSTALL
	function install() {
		global $class_module,$zulu;

		$check = $class_module->module_data(['class'=>$this->class_name]);
		if($check['id'] <= 0) {
			$result = $class_module->module_edit(0,['type'=>$this->type,'name'=>$this->title,'name_client'=>$this->title_client,'info_client'=>$this->info_client,'class'=>$this->class_name,'file'=>$this->filename]);
			if($result['success']) {
				$zulu->meta_update('module',$result['id'],'api_key',$this->test->api_key);
				$zulu->meta_update('module',$result['id'],'api_password',$this->test->api_password);
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
						<label>API Key</label>
						".$form_edit->input_html('input','api_key',$_POST['meta']['api_key'])."
					</div>
				</div>
				<div class=\"col-lg-6\">
					<div class=\"form-group\">
						<label>API Password</label>
						".$form_edit->input_html('input','api_password',$_POST['meta']['api_password'])."
					</div>
				</div>
			</div>";
		return $html;
	}
	function admin_form_process() {
		global $class_module,$zulu;
		$module_row = $class_module->module_data(['class'=>$this->class_name]);
		$zulu->meta_update('module',$module_row['id'],'api_key',$_POST['api_key']);
		$zulu->meta_update('module',$module_row['id'],'api_password',$_POST['api_password']);
		return ['success'=>true];
	}

	//##################################### CHECKOUT FUNCTIONS
	function checkout_select_html() {
		global $class_module;

		$module_row = $class_module->module_data(['class'=>$this->class_name]);
		return ($module_row['info_client']?"<p>".stripslashes($module_row['info_client'])."</p>":null);
	}
	function checkout_summary_formsubmitlabel() {
		$val = "Confirm &amp; Make Payment";
		return $val;
	}
	function checkout_summary_formcapt() { 
		$val = "You agree to pay the due amount."; //Default Value
		return $val;
	}
	function process_payment($sale_id, $config=[]) {
		global $class_sale,$zulu,$class_module;

		$sale_data = $class_sale->sale_data(['id'=>$sale_id]);
		$sale_meta = $zulu->meta_array($class_sale->sale_meta($sale_data['id']));
		$module_row = $class_module->module_data(['class'=>$this->class_name]);
		$module_meta = $zulu->meta_array($class_module->module_meta($module_row['id']));
		$amount = $zulu->dollar($config['amount']);

		require_once MAIN_path.'includes/modules/module_files/'.$this->class_name.'/include_eway.php';
		$apiKey = $module_meta['api_key'];
		$apiPassword =  $module_meta['api_password'];
		$apiEndpoint = 'Production';
		if($this->test->test_mode) {
			$apiKey = $this->test->api_key;
			$apiPassword =  $this->test->api_password;
			$apiEndpoint = $this->test->api_endpoint;
		}

		// Create the eWAY Client
		$client = \Eway\Rapid::createClient($apiKey, $apiPassword, $apiEndpoint);
		$transaction = [
			'CustomerIP'		=>	$_SERVER['REMOTE_ADDR'],
			'Method'			=>	'ProcessPayment',
			'TransactionType'	=>	\Eway\Rapid\Enum\TransactionType::PURCHASE,
			'RedirectUrl'		=>	FE_url."members/order_view.php",
			'CancelUrl'			=>	FE_url."members/order_view.php",
			'Payment'			=> 	[
				'TotalAmount' 		=> 	$amount*100,
				'InvoiceReference'	=>	$sale_data['reference'],
				'CurrencyCode'		=>	'NZD',
			],
			'Customer' 	=> 	[
				'FirstName' 	=> 	stripslashes($sale_meta['name_first']),
				'LastName' 		=> 	stripslashes($sale_meta['name_last']),
				'Street1' 		=> 	stripslashes($sale_meta['bill_address']),
				'Street2'	 	=> 	stripslashes($sale_meta['bill_suburb']),
				'City' 			=> 	stripslashes($sale_meta['bill_city']),
				'PostalCode' 	=> 	stripslashes($sale_meta['bill_post']),
				'Country' 		=> 	'nz',
				'Email' 		=> 	$sale_data['email'],
				'Phone' 		=>	$sale_meta['phone'],
			],
            'ShippingAddress'   =>  [
                'FirstName' 	=> 	stripslashes($sale_meta['name_first']),
				'LastName' 		=> 	stripslashes($sale_meta['name_last']),
                'Street1' 		=> 	stripslashes($sale_meta['ship_address']),
				'Street2'	 	=> 	stripslashes($sale_meta['ship_suburb']),
				'City' 			=> 	stripslashes($sale_meta['ship_city']),
				'PostalCode' 	=> 	stripslashes($sale_meta['ship_post']),
                'Country' 		=> 	'nz',
                'Email' 		=> 	$sale_data['email'],
				'Phone' 		=>	$sale_meta['phone'],
            ],
			'Options'	=>	[
				'Token'	=>	$sale_data['token'],
			],
		];
		// Submit data to eWAY to get a Shared Page URL
		$response = $client->createTransaction(\Eway\Rapid\Enum\ApiMethod::RESPONSIVE_SHARED, $transaction);

		// Check for any errors
		if (!$response->getErrors()) {
			header("Location: ".$response->SharedPaymentUrl);
			exit();
		} else {
			foreach ($response->getErrors() as $error) {
				$errors[] = "Error: ".\Eway\Rapid::getMessage($error);
			}
		}

		if($module_meta['api_key']=='' || $module_meta['api_password']=='') $fatal = true;
		if($fatal) return ['success'=>false, 'msg'=>"This payment option currently isn't available."];
		else return ['success'=>false, 'msg'=>implode('<br>',$errors)];
	}
	function validate_payment() {
		global $class_sale,$zulu,$class_module;

		if($_GET['AccessCode']) {
			$module_row = $class_module->module_data(['class'=>$this->class_name]);
			$module_meta = $zulu->meta_array($class_module->module_meta($module_row['id']));

			require_once MAIN_path.'includes/modules/module_files/'.$this->class_name.'/include_eway.php';
			$apiKey = $module_meta['api_key'];
			$apiPassword =  $module_meta['api_password'];
			$apiEndpoint = 'Production';
			if($this->test->test_mode) {
				$apiKey = $this->test->api_key;
				$apiPassword =  $this->test->api_password;
				$apiEndpoint = $this->test->api_endpoint;
			}

			// Create the eWAY Client
			$client = \Eway\Rapid::createClient($apiKey, $apiPassword, $apiEndpoint);
			// Query the transaction result.
			$response = $client->queryTransaction($_GET['AccessCode']);
			$transactionResponse = $response->Transactions[0];

			$success = $transactionResponse->TransactionStatus;
			$sale_token = $transactionResponse->Options->Token;
			$sale_ref = $transactionResponse->InvoiceReference;
			$sale_data = $class_sale->sale_data(['reference'=>$sale_ref]);

			//Check Duplicate
			$payment_data = $class_sale->payment_data(['sale_id'=>$sale_data['id'],'module_id'=>$module_row['id'],'ovr_user_id'=>true,'first'=>true]);

			$return = ['sale_id'=>$sale_data['id'],'token'=>$sale_data['token']];
			//Process
			if($success==1 && count($payment_data)<=0) {
				$this->add_payment($sale_data['id'],['txn_id'=>$transactionResponse->TransactionID,'auth_code'=>$transactionResponse->AuthorisationCode,'response_code'=>$transactionResponse->ResponseCode,'response_message'=>$transactionResponse->ResponseMessage,'amount'=>$transactionResponse->TotalAmount,'success'=>$success]);
				$return['success'] = true;
			} else {
				if($payment_data['id'] > 0) {
					$return['msg'] = "This has already been paid for, please contact us to manually complete your order. Do not make any further payments please.";
				} elseif($transactionResponse->ResponseMessage != NULL) {
					$errors = explode(',', $transactionResponse->ResponseMessage);
					foreach ($errors as $error) {
						if($error=='D4406') $errors_arr[] = "Transaction cancelled";
						else $errors_arr[] = \Eway\Rapid::getMessage(trim($error));
					}
					$return['msg'] = implode('<br>',$errors_arr);
				} else {
					$return['msg'] = "Your card was declined possibly due to an invalid card or lack of funds.";
				}
				$return['success'] = false;
			}
		}
		return $return;
	}
	function add_payment($sale_id,$data) {
		global $class_sale,$zulu;

		$sale_data = $class_sale->sale_data(['id'=>$sale_id]);
		$sale_meta = $zulu->meta_array($class_sale->sale_meta($sale_id));

		$module_data = [
			'sale_id'			=>	$sale_id,
			'client_id'			=>	$sale_data['client_id'],
			'txnid'				=>	$data['txn_id'],
			'auth_code'			=>	$data['auth_code'],
			'valid'				=>	$data['success'],
			'valid_code'		=> 	$data['response_code'],
			'valid_msg'			=> 	$data['response_message'],
			'amount'			=>	$zulu->dollar(($data['amount']/100)),
			'amount_currency'	=>	'NZD',
		];
		if(!$module_data['valid']) $module_data['amount'] = 0;

		$p_data = [
			'sale_id'		=> 	$sale_id,
			'info'			=> 	'eWAY (Credit Card)',
			'pay_total'		=> 	$module_data['amount'],
			'valid'			=> 	$module_data['valid'],
			'email_admin'	=>	true,
			'email_client'	=>	true,
			'module_id'		=>	$sale_meta['module_payment'],
			'module_data'	=>	serialize($module_data)
		];
		if($p_data['valid']) $p_data['complete'] = true;

		$result = $class_sale->payment_create($p_data);
		if($result['success']) {
			return ["success"=>true, "id"=>$result['id']];
		} else {
			return ["success"=>false, "id"=>0];
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
			<span class=\"text-small color-grey\"><span class=\"fas fa-lock\"></span> Payment via eWAY.</span>";
	}

}

?>
