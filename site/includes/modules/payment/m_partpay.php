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
//MODULE NAME: ZULU Shopfront - PartPay
//MODULE BUILD DATE: 14.11.13
//MODULE COMPATIBILITY: ZULU Shopfront v2.0.8+

//Module Functions***********

// DOCUMENTATION:
// https://docs.partpay.co.nz/merchant-api
// https://widgets.partpay.co.nz/
// https://partpay.co.nz/marketing/logos-banners/

class m_partpay {

	function __construct($config=[]) {

		$this->type = 1;
		$this->class_name = 'm_partpay';
		$this->filename = 'm_partpay.php';
		$this->title = 'Zip';
		$this->title_client = 'Zip';
		$this->info_client = 'Pay 25% now and the remainder over the next 3 fortnights. Interest free! Available to NZ residents who are 18 years and over and have a valid debit or credit card.';
		$this->online = true;

        $this->test_mode = true;
        $this->test_id = 'JtmTtzV20crpPqY7BKogwyxo9rZcTv2G';
        $this->test_key = 't9sdhVHmuWNQN0otI_CIYhQ8dlGE2c0vrCXTf_GRfljQIrJNDwCIuxBAtb6swp1w';
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
				<div class=\"col-lg-6\">
					<div class=\"form-group\">
						<label>Client ID</label>
						".$form_edit->input_html('input','user_id',$_POST['meta']['user_id'])."
					</div>
				</div>
				<div class=\"col-lg-6\">
					<div class=\"form-group\">
						<label>Client Secret</label>
						".$form_edit->input_html('input','user_key',$_POST['meta']['user_key'])."
					</div>
				</div>
			</div>
            <div class=\"row\">
				<div class=\"col-lg-3\">
					<div class=\"form-group\">
						<label>Minimum Payment Amount ".$form_edit->icon_help("Leave blank or 0 if no minimum")."</label>
                        <div class=\"input-group\">
                            <span class='input-group-addon'>".$class_sale->currency_symbol."</span>
				            ".$form_edit->input_html('number','min_amount',$_POST['meta']['min_amount'],['placeholder'=>'0.00','custom'=>['step'=>'any','min'=>'0']])."
                        </div>
					</div>
				</div>
				<div class=\"col-lg-3\">
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
	function checkout_select_html() {
		global $class_module,$zulu;

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
    function checkout_toggle_module($config=[]) {
		global $class_module,$zulu;

        $module_row = $class_module->module_data(['class'=>$this->class_name]);
        $module_meta = $zulu->meta_array($class_module->module_meta($module_row['id']));

        if(isset($config['sale_total'])) {
            if($module_meta['min_amount'] > 0 && $config['sale_total'] < $module_meta['min_amount'] ) {
                return false;
            }
            if($module_meta['max_amount'] > 0 && $config['sale_total'] > $module_meta['max_amount'] ) {
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

        if($module_meta['user_id'] == '' || $module_meta['user_key'] == '') {
			return ['success'=>false, 'msg'=>'This payment option currently isn\'t available.'];
		}

        $access_token = $this->get_access_token($module_meta['user_id'], $module_meta['user_key']);
        if(!$access_token) {
            return ['success'=>false, 'msg'=>'This payment option currently isn\'t available.'];
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
        $tax_total = 0;
        $sale_lines = $class_sale->sale_line($sale_data['id']);
        if($sale_meta['ship_price'] > 0) {
            $sale_lines[] = ['price'=>$sale_meta['ship_price'],'total'=>$sale_meta['ship_price'],'quantity'=>1,'discount'=>0,'extra'=>0,'sku'=>'SHIPPING','description'=>($sale_meta['ship_method']!=NULL?stripslashes($sale_meta['ship_method']):"Shipping"),'is_shipping'=>true];
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
            $tax_total += ($line_summary['tax_raw'] * $sale_line['quantity']);

            if(isset($sale_line['is_shipping']) && $sale_line['is_shipping']) {
                $sale_meta['ship_price'] = $line_summary['total_raw'];
                continue;
            }
            $request_items[] = [
				'description'	=>	'',
                'name'          =>	stripslashes($sale_line['description']),
                'sku'			=>	($sale_line['sku']!=null?$sale_line['sku']:$sale_line['product_id']),
				'quantity'		=>	intval($sale_line['quantity']),
				'price'			=>	$line_summary['total_raw'],
			];
        }

        // Create order
		$request = [
            'productType'       =>	'classic',
			'amount'	        =>	$zulu->dollar($amount),
            'consumer'	        =>	[
                'phoneNumber'   =>	$sale_meta['phone'],
				'givenNames'    =>	stripslashes($sale_meta['name_first']),
				'surname'	    =>	stripslashes($sale_meta['name_last']),
				'email'		    =>	$sale_data['email'],
			],
            'billing'           =>	[
				'addressLine1'	=>	stripslashes($sale_meta['bill_address']),
                'addressLine2'	=>	'',
                'suburb'	    =>	stripslashes($sale_meta['bill_suburb']),
				'city'		    =>	stripslashes($sale_meta['bill_city']),
				'postcode'	    =>	stripslashes($sale_meta['bill_post']),
				'state'	        =>	'',
			],
            'shipping'          =>	[
				'addressLine1'	=>	stripslashes($sale_meta['bill_address']),
                'addressLine2'	=>	'',
                'suburb'	    =>	stripslashes($sale_meta['bill_suburb']),
				'city'		    =>	stripslashes($sale_meta['bill_city']),
				'postcode'	    =>	stripslashes($sale_meta['bill_post']),
				'state'	        =>	'',
			],
            'description'       =>  '',
            'items'		        =>	$request_items,
            'merchant'          =>  [
                'redirectConfirmUrl'    =>  FE_url.'order/'.$sale_data['token'].'/',
                'redirectCancelUrl'     =>  FE_url.'order/'.$sale_data['token'].'/',
                'statusCallbackUrl'     =>  '',
            ],
            'merchantReference' =>	$sale_data['reference'],
            'taxAmount'         =>	$zulu->dollar($tax_total),
			'shippingAmount'    =>	$zulu->dollar($sale_meta['ship_price']),
			'token'             =>	'',
            'promotions'        =>	[],
		];
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://api'.($this->test_mode?"-ci":null).'.partpay.co.nz/order');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: close','Content-Type: application/json','Authorization: Bearer '.$access_token));
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
		if(!isset($response->errors) && $response->redirectUrl != null) {
			header("Location: ".$response->redirectUrl);
			exit();
		}
        if($response->errors != null) {
            $error = "The following errors occured:<br>".implode('<br>',$response->errors);
        } else {
            $error = "Sorry, we couldn't connect to ".$this->title.". Please try again or if the problem persists contact us.";
        }

        return ['success'=>false, 'msg'=>$error];

	}
	function validate_payment() {
		global $class_sale,$zulu,$class_module;

		if(isset($_GET['orderId']) && $_GET['orderId'] != null) {
            $payment_order_id = $_GET['orderId'];
			$module_row = $class_module->module_data(['class'=>$this->class_name]);
			$module_meta = $zulu->meta_array($class_module->module_meta($module_row['id']));

            if($this->test_mode) {
                $module_meta['user_id'] = $this->test_id;
                $module_meta['user_key'] = $this->test_key;
            }

            $access_token = $this->get_access_token($module_meta['user_id'], $module_meta['user_key']);
            if(!$access_token) {
                return ['success'=>false, 'msg'=>'This payment option currently isn\'t available.'];
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api'.($this->test_mode?"-ci":null).'.partpay.co.nz/order/'.$payment_order_id);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: close','Content-Type: application/json','Authorization: Bearer '.$access_token));
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
            $success_code = $response->orderStatus;
            $success = ($success_code=="Created"||$success_code=="Approved"?1:0);

            if($success) {
                $txnid = $response->orderNumber;
                $sale_ref = $response->merchantReference;
                $sale_data = $class_sale->sale_data(['reference'=>$sale_ref]);
                $return = ['sale_id'=>$sale_data['id'], 'token'=>$sale_data['token']];

                $payment_data = $class_sale->payment_data(['sale_id'=>$sale_data['id'],'module_id'=>$module_row['id'],'ovr_user_id'=>true,'first'=>true]);
                if(count($payment_data) <= 0) {
                    $this->add_payment($sale_data['id'],$response);
                    $return['success'] = true;
                } else {
                    $return['success'] = false;
					$return['msg'] = "This has already been paid for, please contact us to manually complete your order. Do not make any further payments please.";
                }
            } else {

                $return = ['success'=>false, 'token'=>$_GET['token_o']];
                if($success_code == 'Declined') {
                    $return['msg'] = "Your Zip order was declined. Please try paying using a different payment method.";
                } else {
                    $return['msg'] = "Your Zip order was unable to be completed. Please try again or use a different payment method.";
                }
            }
		}
		return $return;
	}
	private function add_payment($sale_id, $response) {
		global $class_sale,$zulu;

		$sale_data = $class_sale->sale_data(['id'=>$sale_id]);
		$sale_meta = $zulu->meta_array($class_sale->sale_meta($sale_id));

		$module_data = [
			'sale_id'		=>	$sale_id,
			'client_id'		=>	$sale_data['client_id'],
			'txnid'		    =>	$response->orderNumber,
			'txnref'	    =>	$response->merchantReference,
			'valid'			=>	1,
			'amount'		=>	$response->amount,
            'txnoid'		=>	$response->orderId,
		];
		$approved = true;

		$p_data = [
			'sale_id'		=> 	$sale_id,
			'info'			=> 	$this->title,
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
        $module_title = ($module_row['name_client']!=null?stripslashes($module_row['name_client']):$this->title_client);
		return "
			<p><b>Pay now online:</b></p>
            <a href=\"".$zulu->front_link(true,['self'=>true,'query'=>['Action'=>'ManualPay','Module'=>$module_row['token']]])."\" class=\"button\">Pay Now via ".$module_title."</a><br><br>
			<span class=\"text-small color-grey\"><i class=\"fas fa-lock\"></i> Payment via ".$module_title.".".($this->test_mode?"<br><b><i class='fas fa-exclamation-triangle'></i> Payment Module is in test mode!</b>":NULL)."</span>";
	}

    //##################################### EXTRA FUNCTIONS
    private function get_access_token($user_id, $user_key) {

        if(!isset($_COOKIE['partpay_access_token']) || $_COOKIE['partpay_access_token'] == null || $this->test_mode) {

            $request = [
                'client_id'      =>	$user_id,
                'client_secret'  =>	$user_key,
                'audience'       =>	"https://auth".($this->test_mode?'-dev':null).".partpay.co.nz",
                'grant_type'     =>	'client_credentials',
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://'.(!$this->test_mode?"merchant-auth.partpay.co.nz":"partpay-dev.au.auth0.com").'/oauth/token');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: close','Content-Type: application/json'));
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
            if($response->access_token != null) {
                $access_token = $response->access_token;
                $expiry = ($response->expires_in>0?$response->expires_in:360);
                if(!$this->test_mode) {
                    setcookie('partpay_access_token', $access_token, time() + $response->expires_in, "/");
                }
            } else {
                $access_token = false;
            }
        } else {
            $access_token = $_COOKIE['partpay_access_token'];
        }

        return $access_token;
    }
    function product_view($pid=0, $config=[]) {
        global $zulu, $class_product, $class_module;

        $module_row = $class_module->module_data(['class'=>$this->class_name]);
		$module_meta = $zulu->meta_array($class_module->module_meta($module_row['id']));
        $asset_folder = MAIN_rel."includes/modules/module_files/".$this->class_name."/images/";

        if($config['price'] != null) {
            $price = $config['price'];
        } else {
            $price_data = $class_product->price($pid);
            $price = $price_data['price'];
        }

        $price_extra = "
        <div id='partpay-widget-container'>
            <script type=\"text/javascript\">
                var script = document.createElement('script');
                script.src = 'https://widgets.partpay.co.nz/your-merchant-name/partpay-widget-0.1.1.js?type=calculator&min=".$module_meta['min_amount']."&max=".$module_meta['max_amount']."&amount=".$price."';
                document.getElementById('partpay-widget-container').appendChild(script);
            </script>
        </div>";

        return ['price_extra'=>$price_extra];
    }

}

?>
