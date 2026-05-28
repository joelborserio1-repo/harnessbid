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
//MODULE NAME: ZULU Shopfront - Internet Banking (DPS Account 2 Account)
//MODULE BUILD DATE: 14.11.13
//MODULE COMPATIBILITY: ZULU Shopfront v2.0.8+

//Module Functions***********

class m_dps_acc2acc {

	function __construct($config=[]) {

		$this->type = 1;
		$this->class_name = 'm_dps_acc2acc';
		$this->filename = 'm_dps_acc2acc.php';
		$this->title = 'Internet Banking (DPS Account 2 Account)';
		$this->title_client = 'Internet Banking (DPS)';
		$this->info_client = 'Securely complete your purchase with internet banking via DPS.';
		$this->online = true;
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
						<label>User ID</label>
						".$form_edit->input_html('input','pay_id',$_POST['meta']['pay_id'])."
					</div>
				</div>
				<div class=\"col-lg-6\">
					<div class=\"form-group\">
						<label>User Key</label>
						".$form_edit->input_html('input','pay_key',$_POST['meta']['pay_key'])."
					</div>
				</div>
			</div>";
		return $html;
	}
	function admin_form_process() {
		global $class_module,$zulu;
		$module_row = $class_module->module_data(['class'=>$this->class_name]);
		$zulu->meta_update('module',$module_row['id'],'pay_id',$_POST['pay_id']);
		$zulu->meta_update('module',$module_row['id'],'pay_key',$_POST['pay_key']);
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
		$module_row = $class_module->module_data(['class'=>$this->class_name]);
		$module_meta = $zulu->meta_array($class_module->module_meta($module_row['id']));

		$amount = $config['amount'];
		$email = $config['email'];
		if($email == NULL) {
			$email = SUPPORT_email;
		}

		//A2A?
		$xml_values = '
		<GenerateRequest>
			<PxPayUserId>'.$module_meta['pay_id'].'</PxPayUserId>
			<PxPayKey>'.$module_meta['pay_key'].'</PxPayKey>
			<TxnType>Purchase</TxnType>
			<AmountInput>'.sprintf('%0.2f', doubleval($amount)).'</AmountInput>
			<CurrencyInput>NZD</CurrencyInput>
			<MerchantReference>Sale #'.$sale_data['reference'].'</MerchantReference>
			<EmailAddress>'.$email.'</EmailAddress>
			<TxnId>'.$sale_id.'_'.$zulu->serial(4).'</TxnId>
			<UrlSuccess>'.FE_url.'members/order_view.php</UrlSuccess>
			<UrlFail>'.FE_url.'members/order_view.php</UrlFail>
			<TxnData2>'.serialize($custom).'</TxnData2>
		</GenerateRequest>';

		if($module_meta['pay_id']=='' || $module_meta['pay_key']=='') {
			$fatal = true;
		}

		//cURL FOR CREDIT CARD
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://sec.paymentexpress.com/pxpay/pxaccess.aspx');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 4);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_values);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: close'));

		$start = array_sum(explode(' ', microtime()));
		$result = curl_exec($ch);
		$stop = array_sum(explode(' ', microtime()));
		$totalTime = $stop - $start;

		//Check for errors
		if(curl_errno($ch)) {
			$result = 'ERROR -> '.curl_errno($ch).': '.curl_error($ch);
		} else {
			$returnCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
			switch($returnCode){
				case 404:
					$result = 'ERROR -> 404 Not Found';
					break;
				default:
					break;
			}
		}
		curl_close($ch);

		$xml = simplexml_load_string($result);
		$uri_val = $xml->URI;

		if(trim($uri_val)==NULL || $fatal) {
			return ['success'=>false, 'msg'=>'This payment option currently isn\'t available.'];
		} else {
			header("Location: ".$uri_val);
			exit();
		}
	}
	function validate_payment() {
		global $class_sale,$zulu,$class_module;
		if($_GET['result']) {
			$module_row = $class_module->module_data(['class'=>$this->class_name]);
			$module_meta = $zulu->meta_array($class_module->module_meta($module_row['id']));

			$xml_values = '
			<ProcessResponse>
				<PxPayUserId>'.$module_meta['pay_id'].'</PxPayUserId>
				<PxPayKey>'.$module_meta['pay_key'].'</PxPayKey>
				<Response>'.$_GET['result'].'</Response>
			</ProcessResponse>';

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://sec.paymentexpress.com/pxpay/pxaccess.aspx');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 4);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_values);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: close'));
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));

			$start = array_sum(explode(' ', microtime()));
			$result = curl_exec($ch);
			$stop = array_sum(explode(' ', microtime()));
			$totalTime = $stop - $start;

			//Check for errors
			if(curl_errno($ch)) {
				$result = 'ERROR -> '.curl_errno($ch).': '.curl_error($ch);
			} else {
				$returnCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
				switch($returnCode){
					case 404:
						$result = 'ERROR -> 404 Not Found';
						break;
					default:
						break;
				}
			}
			curl_close($ch);

			$xml = simplexml_load_string($result);
			$success = $xml->Success;
			$dps_txnref = $xml->DpsTxnRef;
			$sale_id = explode("_",$xml->TxnId); //-- split out the SALE ID and TXN UNIQUE IDENTIFIER
			$sale_id = $sale_id[0];
			$reference = $xml->Reference;
			$surcharge = $ORDER_payment_surcharge;
			$charge_total = number_format(number_format($amount,2)-number_format($surcharge,2),2);
			$sale_data = $class_sale->sale_data(['id'=>$sale_id]);

			//Check Duplicate
			$payment_data = $class_sale->payment_data(['sale_id'=>$sale_id,'module_id'=>$module_row['id'],'ovr_user_id'=>true,'first'=>true]);

			$return = ['sale_id'=>$sale_id,'token'=>$sale_data['token']];
			//Process
			if($success==1 && count($payment_data)<=0) {
				$this->add_payment($sale_id,$xml);
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
		return $return;
	}
	function add_payment($sale_id,$xml) {
		global $class_sale,$zulu;

		$sale_data = $class_sale->sale_data(['id'=>$sale_id]);
		$sale_meta = $zulu->meta_array($class_sale->sale_meta($sale_id));

		$module_data = [
			'sale_id'		=>	$sale_id,
			'client_id'		=>	$sale_data['client_id'],
			'dps_txnid'		=>	$xml->TxnId->__toString(),
			'dps_txnref'	=>	$xml->DpsTxnRef->__toString(),
			'valid'			=>	$xml->Success->__toString(),
			'valid_code'	=> 	$xml->AuthCode->__toString(),
			'valid_msg'		=> 	$xml->ResponseText->__toString(),
			'amount'		=>	$xml->AmountSettlement->__toString(),
			'amount_currency'=>	$xml->CurrencyInput->__toString(),
			'bill_name'		=>	$xml->CardHolderName->__toString(),
			'bill_card_name'=>	$xml->CardName->__toString(),
			'bill_card_expire'=>$xml->DateExpiry->__toString()
		];
		$approved = true;

		//-- Get pay acc type
		$txndata2 = unserialize($xml->TxnData2);
		if($txndata2['pay']!=NULL) {
			$method_data['pay'] = $txndata2['pay'];
		}
		//-- Add Billing Token
		if($sale_meta['dps_token'] == NULL) {
			$zulu->meta_update('sale',$sale_id,'dps_token',$xml->DpsBillingId);
		}

		if(!$approved) {
			$module_data['amount'] = 0;
		}
		$p_data = [
			'sale_id'		=> 	$sale_id,
			'info'			=> 	'Payment Express (Direct Deposit)',
			'pay_total'		=> 	$module_data['amount'],
			'valid'			=> 	$module_data['valid'],
			'method_data'	=>	serialize($method_data),
			'email_admin'	=>	true,
			'email_client'	=>	true,
			'module_id'		=>	$sale_meta['module_payment'],
			'module_data'	=>	serialize($module_data)
		];
		if($approved) {
			$p_data['complete'] = true;
		}
		$result = $class_sale->payment_create($p_data);
		if($result['success']) {
			return ["success"=>true,"id"=>$result['id']];
		} else {
			return ["success"=>false,"id"=>0];
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
			<span class=\"text-small color-grey\"><span class=\"fas fa-lock\"></span> Payment via DPS.</span>";
	}
	
}

?>
