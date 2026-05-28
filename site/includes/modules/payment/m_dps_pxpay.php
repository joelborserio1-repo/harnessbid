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
//MODULE NAME: ZULU Shopfront - Credit Card (DPS PxPay)
//MODULE BUILD DATE: 14.11.13
//MODULE COMPATIBILITY: ZULU Shopfront v2.0.8+

//Module Functions***********

class m_dps_pxpay {

	function __construct($config=[]) {

		$this->type = 1;
		$this->class_name = 'm_dps_pxpay';
		$this->filename = 'm_dps_pxpay.php';
		$this->title = 'Credit Card (Payment Express)';
		$this->title_client = 'Credit Card (Payment Express)';
		$this->info_client = 'Securely complete your purchase with your credit card via Payment Express.';
		$this->online = true;

        $this->test_mode = true;
        $this->test_pay_id = 'razorweb_dev';
        $this->test_pay_key = '863b0339b2e6961ca1bd0391e97bfdd59a09a9c38275320adc9a4bbbabed2107';
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
        if($this->test_mode) {
            $val .= "<br><b><i class='fas fa-exclamation-triangle'></i> Payment Module is in test mode!</b>";
        }
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

        if($this->test_mode) {
            $module_meta['pay_id'] = $this->test_pay_id;
            $module_meta['pay_key'] = $this->test_pay_key;
        }

		$xml_values = '
		<GenerateRequest>
	  		<PxPayUserId>'.$module_meta['pay_id'].'</PxPayUserId>
			<PxPayKey>'.$module_meta['pay_key'].'</PxPayKey>
			<AmountInput>'.sprintf('%0.2f', doubleval($amount)).'</AmountInput>
			<CurrencyInput>NZD</CurrencyInput>
			<MerchantReference>Sale #'.$sale_data['reference'].'</MerchantReference>
			<EmailAddress>'.$email.'</EmailAddress>
			<TxnType>Purchase</TxnType>
			<TxnId>'.$sale_id.'_'.$zulu->serial(4).'</TxnId>
			<BillingId></BillingId>
			<EnableAddBillCard>1</EnableAddBillCard>
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
			switch($returnCode) {
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
	function validate_payment($config=[]) {
		global $class_sale,$zulu,$class_module;

        $module_row = $class_module->module_data(['class'=>$this->class_name]);
        $module_meta = $zulu->meta_array($class_module->module_meta($module_row['id']));

        if($this->test_mode) {
            $module_meta['pay_id'] = $this->test_pay_id;
            $module_meta['pay_key'] = $this->test_pay_key;
        }

		if($_GET['result'] && $_GET['userid'] == $module_meta['pay_id']) {

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
				$result = 'ERROR -> '.curl_errno($ch).':'.curl_error($ch);
			} else {
				$returnCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
				switch($returnCode) {
					case 404:
						$result = 'ERROR -> 404 Not Found';
						break;
					default:
						break;
				}
			}
			curl_close($ch);

			//Do XML
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
			if($success==1) {
                if($payment_data['id'] <= 0) {
                    $this->add_payment($sale_id,$xml,$config);
				    $return['success'] = true;
                } else {
                    $pay_data = unserialize($payment_data['module_data']);
                    if(($pay_data['ipn'] && !$config['ipn']) || (!$pay_data['ipn'] && $config['ipn'])) {
                        $return['success'] = true;
                    } else {
                        $return['success'] = false;
                        $return['msg'] = "This has already been paid for, please contact us to manually complete your order. Do not make any further payments please.";
                    }
                }
            } else {
                $return['success'] = false;
				$return['msg'] = "Your card was declined possibly due to an invalid card or lack of funds.";
            }
		}
		return $return;
	}
	function add_payment($sale_id,$xml,$config=[]) {
		global $class_sale,$zulu;

		$sale_data = $class_sale->sale_data(['id'=>$sale_id]);
		$sale_meta = $zulu->meta_array($class_sale->sale_meta($sale_id));

		$module_data = [
			'sale_id'		=>	$sale_id,
			'client_id'		=>	$sale_data['client_id'],
            'ipn'		    =>	($config['ipn']?1:0),
			'dps_txnid'		=>	$xml->TxnId->__toString(),
			'dps_txnref'	=>	$xml->DpsTxnRef->__toString(),
			'valid'			=>	$xml->Success->__toString(),
			'valid_code'	=> 	$xml->AuthCode->__toString(),
			'valid_msg'		=> 	$xml->ResponseText->__toString(),
			'amount'		=>	$xml->AmountSettlement->__toString(),
			'amount_currency'=>	$xml->CurrencyInput->__toString(),
			'bill_name'		=>	addslashes($xml->CardHolderName->__toString()),
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
			'info'			=> 	'Payment Express (Credit Card)',
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
    function ipn_validate() {
        return $this->validate_payment(['ipn'=>true]);
    }

	//##################################### ORDER FUNCTIONS
	function order_pay_now() {
		global $class_module,$zulu;

		$module_row = $class_module->module_data(['class'=>$this->class_name]);
		$module_meta = $zulu->meta_array($class_module->module_meta($module_row['id']));
		return "
			<p><b>Pay now online:</b></p>
            <a href=\"".$zulu->front_link(true,['self'=>true,'query'=>['Action'=>'ManualPay','Module'=>$module_row['token']]])."\" class=\"button\">Pay Now via Credit Card</a><br><br>
			<span class=\"text-small color-grey\"><i class=\"fas fa-lock\"></i> Payment via Payment Express.".($this->test_mode?"<br><b><i class='fas fa-exclamation-triangle'></i> Payment Module is in test mode!</b>":NULL)."</span>";
	}

/*function adm_order_view() {
	global $module_output;
	global $o;
	//$module_output = "<div class=\"spacer10\"></div>
    //   <div class='form_subtitle'>Bank Deposit</div><div class='summary_row' style='width:390px'>This order was was paid or is to be paid for via bank deposit.</div>";

	$query = "SELECT * FROM sh_mod_pay_zdpspxpay_ipn WHERE `order` = '$o'";
	$exe = mysql_query($query);
	$row = mysql_fetch_array($exe);

	$output = "<p class=\"text-small\">Your payment was received via Credit Card (DPS).</p><p>&nbsp;</p><p class=\"text-small\">Transaction ID: <b>".$row['dps_txnref']."</b><br>Status: <b>".$row['valid_msg']."</b><br>Amount: <b>$".$row['amount']."".$row['amount_currency']."</b><br>Card: <b>".$row['bill_card_name']." - ".$row['bill_name']."</b><br>IP: <b>".$row['ip']."</b></p>";

	$module_output = "<hr>
        <h2>Credit Card (DPS)</h2><div class=\"box\">".$output."</div>";

	return true;
}
function adm_order_action() {
	//do nothing
}
function adm_member_view() {
	global $module_output;
	global $oid;
	$module_output = "<div class=\"spacer10\"></div>
        <h2 class='form-header'>Credit Card (DPS)</h2><div class=\"box\">This order was payed for via Credit Card. Please check your bank statement for confirmation.</div>";
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
	$val = "complete.php?Action=Complete";

	return $val;
}
function checkout_summary_formhide() { //Where does the summary form action to?
	global $pay_verify;
	$val = true;
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
	global $nrow_CHECK;

	//Check for payment completion
	$query_CHECK = mysql_query("SELECT * FROM sh_mod_pay_zdpspxpay_ipn WHERE oid = '$rand'");
	$row_CHECK = mysql_fetch_array($query_CHECK);
	$nrow_CHECK = mysql_num_rows($query_CHECK);

	//Payed? But bad amount?
	if($pay_stat==1&&$row_CHECK['amount']<$p_total) {
		$output .= "<p class=\"text-small red\">Your payment amounts do not match, please contact us <a href=\"mailto:{$contact_EMAIL}\" target=\"_blank\">here</a>.</p>";
	}

	//Payed?
	if($pay_stat==1) {
		$pay_verify = 1; //set the global payment verify var equal to 1 so the order can complete
		$output .= "<p class=\"text-small\" style=\"color:#2ea00b;\">We received your payment successfully via Credit Card (DPS) for $".number_format($row_CHECK['amount'],2).".</p>";
		$output .= "<p class=\"text-small grey\">Transaction ID: ".$row_CHECK['txnid'].".</p>";
	}

	//No Pay? We show paypal form.
	if($pay_stat!=1) {
		global $main_COMPANY;

		$query = "SELECT * FROM sh_mod_pay_zdpspxpay_settings";
		$exe = mysql_query($query);
		$row = mysql_fetch_array($exe);
		$output .= "<p>Please complete your order below, click 'Confirm &amp; Make Payment' to pay &amp; complete your order with {$main_COMPANY}.";
	}

	return $output;
	//https://www.paypal.com/cgi-bin/webscr
}

function checkout_verify_payment() { //Check payment has been processed, if yes then order will complete. Use return 'true' or 'false'.
	global $client_output;
	global $oid;
	global $rand;
	global $mid;
	global $contact_NAME;
	global $contact_EMAIL;
	global $main_URL;

	//Settings
	$query = "SELECT * FROM sh_mod_pay_zdpspxpay_settings";
	$exe = mysql_query($query);
	$row = mysql_fetch_array($exe);

	//Order Info
	$query_ORDER = "SELECT * FROM sh_order WHERE rand = '$rand'";
	$exe_ORDER = mysql_query($query_ORDER);
	$row_ORDER = mysql_fetch_array($exe_ORDER);

	//// ########################################################################### ////

	//Recieve Request
	if($_GET['result']) {

		$ch = curl_init();
		$xml_values = '
		<ProcessResponse>
		<PxPayUserId>'.$row['dps_userid'].'</PxPayUserId>
		<PxPayKey>'.$row['dps_paykey'].'</PxPayKey>
		<Response>'.$_GET['result'].'</Response>
		</ProcessResponse>';

		curl_setopt($ch, CURLOPT_URL, 'https://sec.paymentexpress.com/pxpay/pxaccess.aspx');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 4);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_values);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: close'));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt ($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));

		$start = array_sum(explode(' ', microtime()));
		$result = curl_exec($ch);
		$stop = array_sum(explode(' ', microtime()));
		$totalTime = $stop - $start;

		if ( curl_errno($ch) ) {
		$result = 'ERROR -> ' . curl_errno($ch) . ': ' . curl_error($ch);
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

		//Do XML
		$xml = simplexml_load_string($result);

		$success = $xml->Success;
		$dps_txnref = $xml->DpsTxnRef;
		$dps_txnid = $xml->TxnId;
		$token = $xml->TxnId;
		$response_text = $xml->ResponseText;
		$auth_code = $xml->AuthCode;
		$currency = $xml->CurrencyInput;
		$amount = $xml->AmountSettlement;
		$action_ref = $xml->TxnData1;
		$action = $xml->TxnData2;

		$bill_name = $xml->CardHolderName;
		$bill_card_name = $xml->CardName;
		$bill_card_expire = $xml->DateExpiry;
		$reference = $xml->Reference;
		$ip = $_SERVER['REMOTE_ADDR'];
		$dps_response = $_GET['result'];
		$surcharge = $ORDER_payment_surcharge;
		$charge_total = number_format(number_format($amount,2)-number_format($surcharge,2),2);

		//Check Dupli;cate
		$query_DUPLICATE = mysql_query("SELECT id FROM sh_mod_pay_zdpspxpay_ipn WHERE dps_txnref = '$dps_txnref'");
		$nrow_DUPLICATE = mysql_num_rows($query_DUPLICATE);

		//Process
		if($success==1&&$nrow_DUPLICATE<1) {
		//Do Trans Log
		$query_DPS = mysql_query("INSERT INTO sh_mod_pay_zdpspxpay_ipn (`order`,member,dps_response,dps_txnid,dps_txnref,valid,valid_code,valid_msg,amount,amount_currency,bill_name,bill_card_name,bill_card_expire,ip,time) VALUES ('{$dps_txnid}','{$mid}','$dps_response','$dps_txnid','$dps_txnref',{$success},'$auth_code','$response_text',$amount,'{$currency}','$bill_name','$bill_card_name','$bill_card_expire','$ip',".time().")") or die(mysql_error());


			orderNewStatus($oid,"Order Placed (Payment Received)","Your order was received &amp; is subject to processing.<br><br>Transaction Ref: {$dps_txnref}<br>Amount: {$amount}<br>Card: {$bill_card_name}",1);
			return true;

			return true;
		} else {
			if($nrow_DUPLICATE>0) {
				$client_output = "This has already been paid for, please contact us to manually complete your order. Do not make any further payments please.";
			}
			if(!$success) {
				$client_output = "Your card was declined possibly due to an invalid card or lack of fund.";
			}
			return false;
		}
		curl_close($ch);
	}

	//// ########################################################################### ////

	//Check for payment completion
	$query_CHECK = mysql_query("SELECT * FROM sh_mod_pay_zpypl_ipn WHERE oid = '$rand'");
	$row_CHECK = mysql_fetch_array($query_CHECK);
	$nrow_CHECK = mysql_num_rows($query_CHECK);

	if($nrow_CHECK<1) {
		//CC?
		$xml_values = '<GenerateRequest>
	  <PxPayUserId>'.$row['dps_userid'].'</PxPayUserId>
	  <PxPayKey>'.$row['dps_paykey'].'</PxPayKey>
	  <AmountInput>'.sprintf('%0.2f', doubleval($row_ORDER['p_total'])).'</AmountInput>
	  <CurrencyInput>NZD</CurrencyInput>
	  <MerchantReference>Order #'.$row_ORDER['oid'].'</MerchantReference>
	  <EmailAddress>'.$row_ORDER['contact_email'].'</EmailAddress>
	  <TxnType>Purchase</TxnType>
	  <TxnId>'.$row_ORDER['oid'].'</TxnId>
	  <BillingId></BillingId>
	  <EnableAddBillCard>0</EnableAddBillCard>
	  <UrlSuccess>'.$main_URL.'checkout/complete.php</UrlSuccess>
	  <UrlFail>'.$main_URL.'checkout/summary.php</UrlFail>
	</GenerateRequest>';

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

		if ( curl_errno($ch) ) {
			$result = 'ERROR -> ' . curl_errno($ch) . ': ' . curl_error($ch);
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

		$xml = simplexml_load_string($result);
		$uri_val = $xml->URI;

		curl_close($ch);
		header("Location: $uri_val");
		exit();
	}

}
function checkout_payment_fail() { //If exception is thrown and return to summary page occurs.
	return true;
}
function order_view() {
	global $module_output;
	global $rand;
	global $oid;

	$query = "SELECT * FROM sh_mod_pay_zdpspxpay_ipn WHERE `order` = '$oid'";
	$exe = mysql_query($query);
	$row = mysql_fetch_array($exe);

	$output = "<p class=\"text-small\">Your payment was received via Credit Card (DPS).</p><p>&nbsp;</p><p class=\"text-small\">Transaction ID: <b>".$row['dps_txnref']."</b><br>Status: <b>".$row['valid_msg']."</b><br>Amount: <b>$".$row['amount']."".$row['amount_currency']."</b><br>Card: <b>".$row['bill_card_name']." - ".$row['bill_name']."</b><br>IP: <b>".$row['ip']."</b></p>";

	$module_output = "<div class=\"spacer10\"></div>
        <h2>Credit Card (DPS)</h2><div class='summary_row' style='width:390px'>".$output."</div>";
	return true;
}*/
}

?>
