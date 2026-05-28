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

// DOCUMENTATION: https://docs.laybuy.com/#LaybuyMerchantAPI

class m_laybuy {

	function __construct($config=[]) {

		$this->type = 1;
		$this->class_name = 'm_laybuy';
		$this->filename = 'm_laybuy.php';
		$this->title = 'Laybuy';
		$this->title_client = 'Laybuy';
		$this->info_client = '6x payments of [order-total]. Laybuy lets you receive your purchase now and spread the total cost over 6 weekly automatic payments. Interest free! Available to NZ residents ONLY who are 18 years and over and have a valid debit or credit card.';
		$this->online = true;

        $this->test_mode = false;
        $this->test_id = '100050';
        $this->test_key = 'lDX1ESZw5seHT2T5EBJjZ0UyNRegwRHO5yeQlMLkfqhwABt3cfn7iEx3USILG7ks';
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
		global $form_edit, $class_sale;

        if($_POST['meta']['min_amount'] <= 0) {
            $_POST['meta']['min_amount'] = null;
        }
        if($_POST['meta']['max_amount'] <= 0) {
            $_POST['meta']['max_amount'] = null;
        }

		$html = "
        <div class=\"row\">
            <div class=\"col-md-6\">
                <div class=\"form-group\">
                    <label>User ID</label>
                    ".$form_edit->input_html('input','user_id',$_POST['meta']['user_id'])."
                </div>
            </div>
            <div class=\"col-md-6\">
                <div class=\"form-group\">
                    <label>User Key</label>
                    ".$form_edit->input_html('input','user_key',$_POST['meta']['user_key'])."
                </div>
            </div>
        </div>
        <div class=\"row\">
            <div class=\"col-lg-4 col-md-6\">
                <div class=\"form-group\">
                    <label>Minimum Payment Amount ".$form_edit->icon_help("Leave blank or 0 if no minimum")."</label>
                    <div class=\"input-group\">
                        <span class='input-group-addon'>".$class_sale->currency_symbol."</span>
                        ".$form_edit->input_html('number','min_amount',$_POST['meta']['min_amount'],['placeholder'=>'0.00','custom'=>['step'=>'any','min'=>'0']])."
                    </div>
                </div>
            </div>
            <div class=\"col-lg-4 col-md-6\">
                <div class=\"form-group\">
                    <label>Maximum Payment Amount ".$form_edit->icon_help("Leave blank or 0 if no maximum")."</label>
                    <div class=\"input-group\">
                        <span class='input-group-addon'>".$class_sale->currency_symbol."</span>
                        ".$form_edit->input_html('number','max_amount',$_POST['meta']['max_amount'],['placeholder'=>'0.00','custom'=>['step'=>'any','min'=>'0']])."
                    </div>
                </div>
            </div>
        </div>
            ";
		return $html;
	}
	function admin_form_process() {
		global $class_module,$zulu;
		$module_row = $class_module->module_data(['class'=>$this->class_name]);
		$zulu->meta_update('module',$module_row['id'],'user_id',$_POST['user_id']);
		$zulu->meta_update('module',$module_row['id'],'user_key',$_POST['user_key']);
        $zulu->meta_update('module',$module_row['id'],'min_amount',$zulu->dollar($_POST['min_amount']));
		$zulu->meta_update('module',$module_row['id'],'max_amount',$zulu->dollar($_POST['max_amount']));
		return ['success'=>true];
	}

	//##################################### CHECKOUT FUNCTIONS
	function checkout_select_html($config=[]) {
		global $class_module, $class_sale, $zulu;

		$module_row = $class_module->module_data(['class'=>$this->class_name]);
		if(strstr($module_row['info_client'],'[order-total]') && isset($config['sale_total'])) {
            $instalments = $config['sale_total'] / 6;
			$module_row['info_client'] = str_replace('[order-total]',$class_sale->currency_symbol.$zulu->dollar($instalments),$module_row['info_client']);
        }

		return "
		<p class='no-margin'>".stripslashes($module_row['info_client'])." <a href='#' data-popup=\"laybuy-popup-trigger\" class=\"laybuy-popup-trigger popup-overlay-trigger\">Learn more</a></p>
		".$this->info_popup()."
        ";
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
    function checkout_toggle_module($config=[]) {
		global $class_module, $zulu;

        $module_row = $class_module->module_data(['class'=>$this->class_name]);
        $module_meta = $zulu->meta_array($class_module->module_meta($module_row['id']));

        if(isset($config['sale_total'])) {
            if($module_meta['min_amount'] > 0 && $config['sale_total'] < $module_meta['min_amount']) {
                return false;
            }
            if($module_meta['max_amount'] > 0 && $config['sale_total'] > $module_meta['max_amount']) {
                return false;
            }
        }

        return true;
	}
	function process_payment($sale_id, $config=[]) {
		global $class_sale,$zulu,$class_module;

		$sale_data = $class_sale->sale_data(['id'=>$sale_id]);
        $sale_meta = $zulu->meta_array($class_sale->sale_meta($sale_data['id']));
		$module_row = $class_module->module_data(['class'=>$this->class_name]);
		$module_meta = $zulu->meta_array($class_module->module_meta($module_row['id']));

        if($this->test_mode) {
            $module_meta['user_id'] = $this->test_id;
            $module_meta['user_key'] = $this->test_key;
        }

		$amount = $config['amount'];

        $global_discount = 0;
        $has_coupon = false;
        if($sale_data['coupon_id'] > 0) {
            $coupon_row = $class_sale->coupon_data(['id'=>$sale_data['coupon_id']]);
            $has_coupon = true;
            if($coupon_row['discount_object_id'] == 0) {
                if($coupon_row['discount_type'] == 'percent') {
                    $global_discount = $coupon_row['discount_amount'] / 100;
                } elseif($coupon_row['discount_type'] == 'fixed') {
                    if($sale_meta['coupon_amount'] > 0) {
                        $global_discount = $sale_meta['coupon_amount'];
                    } else {
                        $global_discount = $coupon_row['discount_amount'];
                    }
                }
            }
        }

        $request_items = [];
        $sale_lines = $class_sale->sale_line($sale_data['id']);
        if($sale_meta['ship_price'] > 0) {
            $sale_lines[] = ['price'=>$sale_meta['ship_price'],'total'=>$sale_meta['ship_price'],'quantity'=>1,'discount'=>0,'extra'=>0,'sku'=>'SHIPPING','description'=>($sale_meta['ship_method']!=NULL?stripslashes($sale_meta['ship_method']):"Shipping")];
        }
        foreach($sale_lines as $sale_line) {
            $line_price = $sale_line['total'] / $sale_line['quantity'];
            if($has_coupon && $coupon_row['discount_object_id'] == 0 && $global_discount > 0) {
                if($coupon_row['discount_type'] == 'percent') {
                    $line_price -= $line_price * $global_discount;
                } elseif($coupon_row['discount_type'] == 'fixed') {
                    $line_disc = $global_discount / $sale_line['quantity'];
                    if($line_price < $line_disc) {
                        $line_price = 0;
                    } else {
                        $line_price -= $line_disc;
                    }
                    $global_discount -= $sale_line['total'];
                }
            }
            $line_summary = $class_sale->payment_summary($line_price,['sale_id'=>$sale_data['id']]);
            $request_items[] = [
				'id'			=>	($sale_line['sku']!=null?$sale_line['sku']:$sale_line['product_id']),
				'description'	=>	stripslashes($sale_line['description']),
				'quantity'		=>	$sale_line['quantity'],
				'price'			=>	$line_summary['total_raw'],
			];
        }

		$request = [
			'amount'	         =>	$zulu->dollar($amount),
			'currency'           =>	'NZD',
			'returnUrl'	         =>	FE_url.'members/order_view.php?token_o='.$sale_data['token'],
			'merchantReference'  =>	$sale_data['reference']."_".$zulu->serial(4),
			'customer'	         =>	[
				'firstName'      =>	stripslashes($sale_meta['name_first']),
				'lastName'	     =>	stripslashes($sale_meta['name_last']),
				'email'		     =>	$sale_data['email'],
				'phone'		     =>	$sale_meta['phone'],
			],
			'billingAddress'     =>	[
                'name'		     =>	stripslashes($sale_meta['bill_to']),
				'address1'	     =>	stripslashes($sale_meta['bill_address']),
                'suburb'	     =>	stripslashes($sale_meta['bill_suburb']),
				'city'		     =>	stripslashes($sale_meta['bill_city']),
				'postcode'	     =>	stripslashes($sale_meta['bill_post']),
				'country'	     =>	stripslashes($sale_meta['bill_country']),
			],
			'shippingAddress'	 =>	[
				'name'		     =>	stripslashes($sale_meta['ship_to']),
				'address1'	     =>	stripslashes($sale_meta['ship_address']),
				'suburb'	     =>	stripslashes($sale_meta['ship_suburb']),
				'city'		     =>	stripslashes($sale_meta['ship_city']),
				'postcode'	     =>	stripslashes($sale_meta['ship_post']),
				'country'	     =>	stripslashes($sale_meta['ship_country']),
			],
			'items'		         =>	$request_items
		];

        if($module_meta['user_id']=='' || $module_meta['user_key']=='') {
			return ['success'=>false, 'msg'=>'This payment option currently isn\'t available.'];
		}

		//cURL FOR CREDIT CARD
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://'.($this->test_mode?"sandbox-":NULL).'api.laybuy.com/order/create');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: close','Authorization: Basic '.base64_encode($module_meta['user_id'].':'.$module_meta['user_key'])));
		$result = curl_exec($ch);

        // Check for errors
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
		curl_close($ch);

		$response = json_decode($result);
		if($response->result == 'SUCCESS') {
			header("Location: ".$response->paymentUrl);
			exit();
		}

        $error = $response->error;
        if($error == 'Phone for the customer is invalid.') {
            $zulu->notification_set("Laybuy requires a correctly formatted phone number. Please update your phone number below.",2);
            header("Location: ".FE_rel."checkout/index.php");
            exit;
        }

        return ['success'=>false, 'msg'=>$error];

	}
	function validate_payment() {
		global $class_sale,$zulu,$class_module;

		if($_GET['token'] != NULL && $_GET['status'] != NULL && $_GET['token_o'] != NULL) {
            $laybuy_token = $_GET['token'];
			$module_row = $class_module->module_data(['class'=>$this->class_name]);
			$module_meta = $zulu->meta_array($class_module->module_meta($module_row['id']));

            if($this->test_mode) {
                $module_meta['user_id'] = $this->test_id;
                $module_meta['user_key'] = $this->test_key;
            }

			$request = ['token'=>$laybuy_token];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://'.($this->test_mode?"sandbox-":NULL).'api.laybuy.com/order/confirm');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: close','Authorization: Basic '.base64_encode($module_meta['user_id'].':'.$module_meta['user_key'])));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $result = curl_exec($ch);

            // Check for errors
            if ( curl_errno($ch) ) {
                $result = 'ERROR -> ' . curl_errno($ch) . ': ' . curl_error($ch);
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

            $response = json_decode($result);
            $success_code = $response->result;
            $success = ($success_code=="SUCCESS"?1:0);

            if($success) {
                $txnid = $response->orderId;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://'.($this->test_mode?"sandbox-":NULL).'api.laybuy.com/order/'.$txnid);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: close','Authorization: Basic '.base64_encode($module_meta['user_id'].':'.$module_meta['user_key'])));
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                $result = curl_exec($ch);

                // Check for errors
                if ( curl_errno($ch) ) {
                    $result = 'ERROR -> ' . curl_errno($ch) . ': ' . curl_error($ch);
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

                $response = json_decode($result);
                $merch_ref = $response->merchantReference;
                $sale_ref = explode("_",$merch_ref); //-- split out the SALE ID and TXN UNIQUE IDENTIFIER
                $sale_ref = $sale_ref[0];
                $sale_data = $class_sale->sale_data(['reference'=>$sale_ref]);
                $return = ['sale_id'=>$sale_data['id'],'token'=>$sale_data['token']];

                $payment_data = $class_sale->payment_data(['sale_id'=>$sale_data['id'],'module_id'=>$module_row['id'],'ovr_user_id'=>true,'first'=>true]);
                if(count($payment_data) <= 0) {
                    $this->add_payment($sale_data['id'],$response);
                    $return['success'] = true;
                } else {
                    $return['success'] = false;
					$return['msg'] = "This has already been paid for, please contact us to manually complete your order. Do not make any further payments please.";
                }
            } else {
                $response_text = $response->error;
                $return = ['success'=>false,'token'=>$_GET['token_o']];
                if($response_text == "Unexpected payment status: CANCELLED") {
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, 'https://'.($this->test_mode?"sandbox-":NULL).'api.laybuy.com/order/cancel/'.$laybuy_token);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_TIMEOUT, 10);
					curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: close','Authorization: Basic '.base64_encode($module_meta['user_id'].':'.$module_meta['user_key'])));
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
					$result = curl_exec($ch);

					// Check for errors
					if ( curl_errno($ch) ) {
						$result = 'ERROR -> ' . curl_errno($ch) . ': ' . curl_error($ch);
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
					$return['msg'] = "Your Laybuy order has been cancelled.";
				} elseif($response_text != NULL) {
                    $return['msg'] = $response_text;
                } else {
                    $return['msg'] = "Your card was declined possibly due to an invalid card or lack of funds.";
                }
            }
		}
		return $return;
	}
	function add_payment($sale_id, $response) {
		global $class_sale,$zulu;

		$sale_data = $class_sale->sale_data(['id'=>$sale_id]);
		$sale_meta = $zulu->meta_array($class_sale->sale_meta($sale_id));

		$module_data = [
			'sale_id'		=>	$sale_id,
			'client_id'		=>	$sale_data['client_id'],
			'txnid'		    =>	$response->orderId,
			'txnref'	    =>	$response->merchantReference,
			'valid'			=>	1,
			'amount'		=>	$response->amount,
			'amount_currency'=>	$response->currency
		];
		$approved = true;

		$p_data = [
			'sale_id'		=> 	$sale_id,
			'info'			=> 	'Laybuy',
			'pay_total'		=> 	$module_data['amount'],
			'valid'			=> 	$module_data['valid'],
			'email_admin'	=>	true,
			'email_client'	=>	true,
			'module_id'		=>	$sale_meta['module_payment'],
			'module_data'	=>	serialize($module_data),
            'complete'      =>  true
		];

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
            <a href=\"".$zulu->front_link(true,['self'=>true,'query'=>['Action'=>'ManualPay','Module'=>$module_row['token']]])."\" class=\"button\">Pay Now via Laybuy</a><br><br>
			<span class=\"text-small color-grey\"><i class=\"fas fa-lock\"></i> Payment via Laybuy.".($this->test_mode?"<br><b><i class='fas fa-exclamation-triangle'></i> Payment Module is in test mode!</b>":NULL)."</span>";
	}

	//##################################### EXTRA FUNCTIONS
	function product_view($pid=0, $config=[]) {
        global $zulu, $class_product, $class_module;

        $module_row = $class_module->module_data(['class'=>$this->class_name]);
		$module_meta = $zulu->meta_array($class_module->module_meta($module_row['id']));
        $laybuy_asset_folder = MAIN_rel."includes/modules/module_files/".$this->class_name."/images/";

        if($config['price'] != null) {
            $price = $config['price'];
        } else {
            $price_data = $class_product->price($pid);
            $price = $price_data['price'];
        }
        if(($module_meta['min_amount'] > 0 && $price < $module_meta['min_amount']) || ($module_meta['max_amount'] > 0 && $price > $module_meta['max_amount'])) {
            return false;
        }

        $price_extra = "
        <div class=\"laybuy-price\">
            <img src=\"".$laybuy_asset_folder."logo-transparent.png\" alt=\"Laybuy\" title=\"Laybuy.com\">
            <p>or just 6x payments from <b>".LOCALE_currency.$zulu->dollar(($price/6),true)."</b> <br><a href='#' data-popup=\"laybuy-popup-trigger\" class=\"laybuy-popup-trigger popup-overlay-trigger\">Learn more</a></p>
        </div>";

        return ['price_extra'=>$price_extra, 'popup_html'=>$this->info_popup()];
    }

	function info_popup() {
		global $zulu, $class_module;

		$module_row = $class_module->module_data(['class'=>$this->class_name]);
		$module_meta = $zulu->meta_array($class_module->module_meta($module_row['id']));
        $laybuy_asset_folder = MAIN_rel."includes/modules/module_files/".$this->class_name."/images/";
        $zulu->template->js_file['popup-overlay'] = FE_tpl_rel."assets/popup-overlay/jquery.popupoverlay.js";

		$popup_html = "
		<div class='laybuy-popup-box popup-overlay' id='laybuy-popup-trigger'>
            <div class='laybuy-popup-content'>
                <a class='laybuy-popup-close popup-close' href='#'>".$zulu->icon('times', 'r')."</a>
                <img src='".$laybuy_asset_folder."logo-transparent.png' alt='Laybuy' class='laybuy-logo'>
                <p class='laybuy-special'><b>Receive your purchase now, spread the total cost over 6 weekly automatic payments. Interest free!</b></p>
                <hr>
                <div class='coltable col4 padcol vtop'>
                    <div class='col'>
                        <p class='text-center'>
                            <img src='".$laybuy_asset_folder."icon-cart.png' alt='Laybuy cart'><br>Simply select <b>Pay by Laybuy</b> at checkout
                        </p>
                    </div>
                    <div class='col'>
                        <p class='text-center'>
                            <img src='".$laybuy_asset_folder."icon-mobile.png' alt='Laybuy mobile'><br>Login or Register for Laybuy and complete your order in seconds
                        </p>
                    </div>
                    <div class='col'>
                        <p class='text-center'>
                            <img src='".$laybuy_asset_folder."icon-cc.png' alt='Laybuy credit card'><br>Complete your purchase using an existing debit or credit card
                        </p>
                    </div>
                    <div class='col'>
                        <p class='text-center'>
                            <img src='".$laybuy_asset_folder."icon-laybuy.png' alt='Laybuy'><br>Pay over 6 weeks and receive your purchase now
                        </p>
                    </div>
                </div>
            </div>
        </div>
		";
		return $popup_html;
	}

	function basket_view($config=[]) {
        global $zulu, $class_module;

		$module_row = $class_module->module_data(['class'=>$this->class_name]);
		$module_meta = $zulu->meta_array($class_module->module_meta($module_row['id']));
        $laybuy_asset_folder = MAIN_rel."includes/modules/module_files/".$this->class_name."/images/";

		if(isset($config['sale_total'])) {
            $price = $config['sale_total'];
        } else {
            $price = 0;
        }
        if($price <= 0 || ($module_meta['min_amount'] > 0 && $price < $module_meta['min_amount']) || ($module_meta['max_amount'] > 0 && $price > $module_meta['max_amount'])) {
            return false;
        }

        $price_extra = "
        <div class=\"laybuy-price\">
            <img src=\"".$laybuy_asset_folder."logo-transparent.png\" alt=\"Laybuy\" title=\"Laybuy.com\">
            <p>or just 6x payments from <b>".LOCALE_currency.$zulu->dollar(($price/6),true)."</b> <br><a href='#' data-popup=\"laybuy-popup-trigger\" class=\"laybuy-popup-trigger popup-overlay-trigger\">Learn more</a></p>
			".$this->info_popup()."
        </div>";

        return $price_extra;
    }

}

?>
