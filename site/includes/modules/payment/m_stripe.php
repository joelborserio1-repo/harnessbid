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
//MODULE NAME: ZULU Shopfront - Credit Card (Stripe)
//MODULE BUILD DATE: 17.07.2019
//MODULE COMPATIBILITY: ZULU Shopfront v3.0.0+

//Module Functions***********

class m_stripe {

	function __construct($config=[]) {

		$this->type = 1;
		$this->class_name = 'm_stripe';
		$this->filename = 'm_stripe.php';
		$this->title = 'Stripe';
		$this->title_client = 'Credit Card';
		$this->info_client = 'Securely complete your purchase with your credit card via Stripe.';
		$this->online = true;
		$this->config = new StdClass();

		$this->config->test_mode = false; //-- CHANGE THIS TO TRUE/FALSE TO ENABLE/DISABLE PAYMENTS
		$this->config->test_public_key = 'STRIPE_TEST_PUBLIC_KEY';
		$this->config->test_private_key = 'STRIPE_TEST_SECRET_KEY';

		$this->config->environment = 'production';
		$this->config->card_charge_msg = "Your card was successfully charged.";
		$this->config->card_error_type = [
			'card_declined'	=>	['msg'=>"Your card was declined, possibly due to low funds."],
			'missing'	=>	['msg'=>"Custom details could not be found."],
			'invalid_expiry_year'	=>	['msg'=>"Card expiry year is invalid."],
			'invalid_expiry_month'	=>	['msg'=>"Card expiry month is invalid."],
			'processing_error'	=>	['msg'=>"An error occurred while processing the card."],
			'incorrect_number'	=>	['msg'=>"Card number is invalid."],
			'invalid_card_type'	=>	['msg'=>"Card type is invalid."],
			'invalid_cvc'	=>	['msg'=>"Card CVV is invalid."],
			'incorrect_cvc'	=>	['msg'=>"Card CVV is invalid."],
			'stolen_card'	=>	['msg'=>"The payment has been declined because the card is reported stolen."],
			'amount_too_large'	=>	['msg'=>"The specified amount is greater than the maximum amount allowed."],
		];

		if($this->config->test_mode) {
			$this->config->environment = 'sandbox';
		}

		$this->config->module_prefix = $this->class_name.'_'.$this->config->environment;
		$this->config->currency = 'USD';

        $this->config->unique_key->renew = $this->config->module_prefix."_id";
        $this->config->unique_key->template = $this->config->module_prefix."_plan_id";
        $this->config->unique_key->product = $this->config->module_prefix."_product_id";
        $this->config->unique_key->renew_temp = $this->config->module_prefix."_temp_id";
        $this->config->unique_key->customer = $this->config->module_prefix."_cust_id";
        $this->config->unique_key->customer_payment = $this->config->module_prefix."_cust_payment_id";

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
					<label>Publishable Key</label>
					".$form_edit->input_html('input','public_key',$_POST['meta']['public_key'])."
				</div>
			</div>
			<div class=\"col-lg-6\">
				<div class=\"form-group\">
					<label>Secret Key</label>
					".$form_edit->input_html('password','private_key',$_POST['meta']['private_key'])."
				</div>
			</div>
		</div>";
		return $html;
	}
	function admin_form_process() {
		global $class_module,$zulu;
		$module_row = $class_module->module_data(['class'=>$this->class_name]);
		$zulu->meta_update('module',$module_row['id'],'public_key',$_POST['public_key']);
		$zulu->meta_update('module',$module_row['id'],'private_key',$_POST['private_key']);
		return ['success'=>true];
	}

	//##################################### CHECKOUT FUNCTIONS
	function checkout_select_html() {
		global $class_module;

		$this->include_js();

		$module_row = $class_module->module_data(['class'=>$this->class_name]);
		return ($module_row['info_client']?"<p>".stripslashes($module_row['info_client'])."</p>":null)."
        <div class='form-payment'></div>";
	}
	function checkout_summary_formsubmitlabel() {
		$val = "Confirm &amp; Make Payment";
		return $val;
	}
	function checkout_summary_formcapt() {
		$val = "<br>";
		if($this->config->test_mode) {
            $val = "<br><b><i class='fas fa-exclamation-triangle'></i> Payment Module is in test mode!</b>";
        }
		return $val;
	}

	//##################################### PAYMENT FUNCTIONS
	function form_payment($sale_id=0, $type='checkout') {
		global $class_module, $zulu, $class_sale, $form_edit;

		$module_row = $class_module->module_data(['class'=>$this->class_name]);
		$module_meta = $zulu->meta_array($class_module->module_meta($module_row['id']));

		if($this->config->test_mode) {
			$module_meta['public_key'] = $this->config->test_public_key;
			$module_meta['private_key'] = $this->config->test_private_key;
		}

        try {
			\Stripe\Stripe::setApiKey($module_meta['private_key']);
		} catch (\Stripe\Error\Authentication $e) {
		  	// Authentication with Stripe's API failed
            $fatal = true;
		}

		$sale_row = $class_sale->sale_data(['id'=>$sale_id]);
		$sale_meta = $zulu->meta_array($class_sale->sale_meta($sale_row['id']));

		if($fatal) {
			return "<p>Sorry, Stripe is currently unavailable. Please select another payment method or try again later.</p>";
		}

		$html = "<div id=\"stripe-card-element\"></div>
		<div id=\"stripe-card-errors\" role=\"alert\"></div>";

		if($type != 'checkout') {
			$this->include_js();
			$zulu->template->jquery_code[] = "stripeInit();";
		} else {
			$html .= "<script type='text/javascript'>
				$(document).ready(function() {
					stripeInit();
				});
			</script>";
		}

		return $html;
	}

	private function include_js() {
		global $zulu, $class_module;

		$module_row = $class_module->module_data(['class'=>$this->class_name]);
		$module_meta = $zulu->meta_array($class_module->module_meta($module_row['id']));

		if($this->config->test_mode) {
			$module_meta['public_key'] = $this->config->test_public_key;
			$module_meta['private_key'] = $this->config->test_private_key;
		}

		$zulu->template->js_file['stripe'] = "https://js.stripe.com/v3/";
		$zulu->template->js_code['stripe'] = "
		let stripe,
			stripeForm,
			stripeCard;

		function stripeInit() {
			stripe = Stripe('".$module_meta['public_key']."');
			let elements = stripe.elements(),
				style = {
			  		base: {
						color: '#32325d',
						lineHeight: '36px',
						fontFamily: '\"Helvetica Neue\", Helvetica, sans-serif',
						fontSmoothing: 'antialiased',
						fontSize: '20px',
						'::placeholder': {
							color: '#aab7c4'
						}
			  		},
				  	invalid: {
						color: '#fa755a',
						iconColor: '#fa755a'
				  	}
				};

			stripeCard = elements.create('card', {
				style: style,
				hidePostalCode: true,
			});

			//-- Add an instance of the card Element into the `card-element` <div>
			stripeCard.mount('#stripe-card-element');

			//-- Handle real-time validation errors from the card Element.
			stripeCard.addEventListener('change', function(event) {
				let displayError = document.getElementById('stripe-card-errors');
				if (event.error) {
					displayError.textContent = event.error.message;
				} else {
					displayError.textContent = '';
				}
			});

			//-- Handle form submission
			stripeForm = document.getElementById('payment-form');
			stripeForm.addEventListener('submit', stripeFormSubmit);
		}

		function stripeFormSubmit(event) {
			event.preventDefault();
			stripe.createToken(stripeCard).then(function(result) {
				if (result.error) {
					//-- Inform the user if there was an error
					let errorElement = document.getElementById('stripe-card-errors');
					errorElement.textContent = result.error.message;
				} else {
					//-- Send the token to your server
				 	stripeTokenHandler(result.token);
				}
			});
		}

		function stripeTokenHandler(token) {
			//-- Insert the token ID into the form so it gets submitted to the server
			let hiddenInput = document.createElement('input');
			hiddenInput.setAttribute('type', 'hidden');
			hiddenInput.setAttribute('name', 'stripeToken');
			hiddenInput.setAttribute('value', token.id);
			stripeForm.appendChild(hiddenInput);

			//-- Submit the form
			stripeForm.submit();
		}

		function destroy_module_".$module_row['id']."() {
			if(stripeForm != null) {
				stripeForm.removeEventListener('submit', stripeFormSubmit);
				stripeCard.destroy();
			}
		}
		";
	}

    private function stripe_handle_pay($sale_id) {
        global $class_module, $zulu, $class_sale, $class_setting;

        $module_row = $class_module->module_data(['class'=>$this->class_name]);
		$module_meta = $zulu->meta_array($class_module->module_meta($module_row['id']));

        if($this->config->test_mode) {
            $module_meta['public_key'] = $this->config->test_public_key;
            $module_meta['private_key'] = $this->config->test_private_key;
        }

		$sale_data = $class_sale->sale_data(['id'=>$sale_id]);
		$sale_meta = $zulu->meta_array($class_sale->sale_meta($sale_id));

        try {
            \Stripe\Stripe::setApiKey($module_meta['private_key']);
        } catch (\Stripe\Error\Authentication $e) {
          	//-- Authentication with Stripe's API failed
            $fatal = true;
        }

		if($sale_meta['stripeToken'] != NULL) {

            $sale_balance = $class_sale->sale_balance($sale_id);
            $process = true;

			$currency_code = $class_sale->currencyCode($sale_id);
			if(!$currency_code) {
				$currency_code = $sale_meta['currency'] ? $sale_meta['currency'] : ($class_setting->data['currency_code']?$class_setting->data['currency_code']:'NZD');
			}

			//-- Get Trans
            try {
                $charge_config = [
                    "amount"        => round($sale_balance * 100),
                    "currency"      => $currency_code,
                    "source"        => $sale_meta['stripeToken'], //-- obtained with Stripe.js
                    "description"   => "Payment for ".$sale_data['name']." on ".date('d/m/Y',$sale_data['stat_add']),
                    'receipt_email' => $sale_data['email'],
                ];

                if($sale_meta['delivery_method'] == 'ship') {
                    $charge_config['shipping'] = [
                        'address'       =>  [
                            'line1'         =>  $sale_meta['ship_address'],
                            'line2'         =>  $sale_meta['ship_address2'],
                            'city'          =>  $sale_meta['ship_city'],
                            'country'       =>  $sale_meta['ship_country'],
                            'postal_code'   =>  $sale_meta['ship_post'],
                            'state'         =>  $sale_meta['ship_state'],
                        ],
                        'name'          =>  $sale_meta['ship_name_first']." ".$sale_meta['ship_name_last'],
                        'phone'         =>  $sale_meta['phone'],
                    ];
                }
                $charge_request = \Stripe\Charge::create($charge_config);
                $success = true;
            } catch(\Stripe\Error\Card $e) {
                //-- Since it's a decline, \Stripe\Error\Card will be caught
                $body = $e->getJsonBody();
                $err  = $body['error'];
                $success = false;
			    if($err['decline_code']!=NULL&&isset($this->config->card_error_type[$err['decline_code']]['msg'])) {
                    $return['msg'] = $this->config->card_error_type[$err['decline_code']]['msg'];
                }
                $process = false;
            } catch(\Stripe\Error\InvalidRequest $e) {
                $body = $e->getJsonBody();
                $err  = $body['error'];
                $success = false;
                if($err['message']!=NULL) {
                    $return['msg'] = $err['message'];
                }
                $process = false;
            } catch(Exception $e) {
                $body = $e->getJsonBody();
                $err  = $body['error'];
                $success = false;
                if($err['message']!=NULL) {
                    $return['msg'] = $err['message'];
                }
                $process = false;
            }

            //Process
            if($success) {
                $response = $charge_request->toArray(true);

				$module_data = [];
                $module_data['sale_id'] = $sale_id;
                $module_data['client_id'] = $sale_data['client_id'];
                $module_data['txn_id'] = $response['balance_transaction'];
                $module_data['amount'] = $response['amount']/100;
                $module_data['date'] = $response['created'];
                $module_data['status'] = $response['captured'];
                $module_data['data'] = serialize($response);
                $module_data['valid'] = $response['captured'];

                $p_data = [
                    'sale_id'		=> 	$sale_id,
                    'info'		    => 'Stripe Credit Card',
                    'pay_total'		=> 	$module_data['amount'],
                    'valid'			=> 	$module_data['valid'],
					'complete'		=>	true,
                    'email_admin'	=>	true,
                    'email_client'	=>	true,
                    'module_id'		=>	$sale_meta['module_payment'],
                    'module_data'	=>	addslashes(serialize($module_data))
                ];
                if($this->config->test_mode) {
                    //$p_data['email_admin'] = $p_data['email_client'] = false;
                }

                $pay_result = $class_sale->payment_create($p_data);
                $return['success'] = true;
            } else {
                if($nrow_DUPLICATE>0) {
                    $return['success'] = false;
                    $return['msg'] = "This has already been paid for, please contact us to manually complete your order. Do not make any further payments please.";
                } else {
                    $return['success'] = false;
                    if(!isset($return['msg'])) {
                        $return['msg'] = "Your card was declined possibly due to an invalid card or lack of funds.";
                    }
                }
            }
		} else {
			$return = ['success'=>false,'msg'=>'Sorry, we couldn\'t find your payment information. Please try again or contact us to process your order.'];
		}

        if(!$return['success']) {
            $zulu->log_edit(0,['object'=>'order','object_id'=>$sale_id,'title'=>'Stripe payment fail','data'=>$zulu->esc($return['msg'])]);
        }

        return $return;
    }
	function verify_payment() {
		if(isset($_POST['stripeToken']) && $_POST['stripeToken']) {
			$_POST['meta']['stripeToken'] = $_POST['stripeToken'];
			return ['success'=>true];
		}
		return ['success'=>false, 'msg'=>'Please enter your credit card details to complete your order.'];
	}
	function process_payment($sale_id, $config=[]) {
        return $this->stripe_handle_pay($sale_id);
	}
	function validate_payment() {
		return ['success'=>false];
	}

	//##################################### ORDER FUNCTIONS
	function order_pay_now() {
		global $class_module,$zulu;

		$module_row = $class_module->module_data(['class'=>$this->class_name,'client_id'=>$_SESSION['user']['id'],'parent'=>true]);
		$module_meta = $zulu->meta_array($class_module->module_meta($module_row['id']));
		return "
			<p><b>Pay now online:</b></p>
            <a href=\"".$zulu->front_link(true,['self'=>true,'query'=>['Action'=>'ManualPayForm','Module'=>$module_row['token']]])."\" class=\"button\">Pay Now via Credit Card</a><br><br>
			<span class=\"text-small color-grey\"><span class=\"fas fa-lock\"></span> Payment via Stripe.</span>";
	}

	//##################################### RENEW FUNCTIONS
	function stripe_connect() {
		global $class_module,$zulu,$class_client;

		if(!$this->config->api_connect->status) {
			$module_row = $class_module->module_data(['class'=>$this->class_name, 'parent'=>true]);
			$module_meta = $zulu->meta_array($class_module->module_meta($module_row['id']));

			if($this->config->test_mode) {
	            $module_meta['public_key'] = $this->config->test_public_key;
	            $module_meta['private_key'] = $this->config->test_private_key;
	        }

			try {
				\Stripe\Stripe::setApiKey($module_meta['private_key']);

				$this->config->api_connect->status = true;
				$this->config->api_connect->private_key = $module_meta['private_key'];

			} catch (\Stripe\Error\Authentication $e) {
			  // Authentication with Stripe's API failed
				$fatal = true;
				return ['success'=>false,'msg'=>'Failed to connect to Stripe&reg;. Your card was not charged, please try another payment method.'];
			}
		}

		return true;
	}
	function can_token_bill() {
        global $zulu,$class_module;

        $module_row = $class_module->module_data(['class'=>$this->class_name]);
		$module_meta = $zulu->meta_array($class_module->module_meta($module_row['id']));

        if($module_meta['public_key'] != null && $module_meta['private_key'] != null) {
            return true;
        }

        return false;
    }
	function renew_data_refresh() {
		$this->renew_data_set($this->vars->renew->data->id);
		return true;
	}
	function renew_data_set($id) {
		global $class_renew,$zulu;

		$data = $class_renew->renew_data(['id'=>$id,'merge'=>true]);
		$meta = $zulu->meta_array($class_renew->renew_meta($id));

		$this->vars->renew->data = (object)$data;
		$this->vars->renew->meta = (object)$meta;

		$this->vars->renew_template->data = (object)$data['_template'];
		$this->vars->renew_template->meta = (object)$data['_template_meta'];
	}
	function renew_payment_handler($renew_row) {
		global $class_client, $zulu, $class_renew, $class_module, $class_setting;

		//-- Stripe Token
		$stripe_token = $_POST['stripeToken'];
		if(trim($stripe_token) == null) {
			return ['success'=>false];
		}

		//-- Client Data
		$client_id = $renew_row['client_id'];
		$client_data = $class_client->client_data(['id'=>$client_id]);
		$client_meta = $zulu->meta_array($class_client->client_meta($client_id));

		//-- Connect
		$this->stripe_connect();

		//-- Create Customer
		$stripe_customer_id = $client_meta[$this->config->unique_key->customer];
		if($stripe_customer_id == '') {
			try {
				$response = \Stripe\Customer::create([
					'description'	=> $class_setting->data['ws_site_name'].' Customer (ID: '.$client_id.')',
					'name' 			=> $class_client->client_name($client_data),
					'email' 		=> $client_data['email'],
					'phone'			=> $client_data['phone'],
				]);

				if(trim($response->id) != '') {
					$zulu->meta_update('client', $client_id, $this->config->unique_key->customer, $response->id);
					$stripe_customer_id = $response->id;
				} else {
					return ['success'=>false];
				}
			} catch(Exception $e) {
				return ['success'=>false];
			}
		}

		//-- Create / Get Default Payment Source
		$stripe_payment_id = null;
		try {
			$response = \Stripe\Customer::createSource($stripe_customer_id, [
				'source' => $stripe_token,
			]);

			if(trim($response->id) != '') {
				$stripe_payment_id = $response->id;
			} else {
				return ['success'=>false];
			}

		} catch (Exception $e) {
			//print_r($e->getMessage());exit;
			return ['success'=>false];
		}

		if($stripe_payment_id == null) {
			return ['success'=>false];
		}

		//-- Get Plan Stripe ID
		$stripe_plan_token = $this->stripe_get_template_token($renew_row['template_id']);
		if(!$stripe_plan_token) {
			return ['success'=>false];
		}

		//-- Attach Payment to Plan
		$stripe_subscription_id = $zulu->meta_value('renew', $renew_row['id'], $this->config->unique_key->template)['value'];
		if($stripe_subscription_id == '') {
			try {
				$response = \Stripe\Subscription::create([
					'customer'	=>	$stripe_customer_id,
					'items' 	=> [
						['plan'	=>	$stripe_plan_token],
					],
					'trial_from_plan'	=>	true,
					'default_source'	=>	$stripe_payment_id,
				]);
				if(trim($response->id) != '') {

					$module_row = $class_module->module_data(['class'=>$this->class_name]);
					$class_renew->renew_edit($renew_row['id'],[
						'auto_renew'			=>	1,
						'auto_renew_module_id'	=>	$module_row['id'],
					]);
					$zulu->meta_update('renew', $renew_row['id'], $this->config->unique_key->template, $response->id);
					$this->renew_data_refresh();
					$stripe_subscription_id = $response->id;
				} else {
					return ['success'=>false];
				}
			} catch(Exception $e) {
				return ['success'=>false];
			}
		}

		return [
			'success'	=>	$stripe_subscription_id != '' ? true : false,
			'tokens'	=>	[
				'customer_id'		=>	$stripe_customer_id,
				'payment_id'		=>	$stripe_payment_id,
				'plan_id'			=>	$stripe_plan_token,
				'subscription_id'	=>	$stripe_subscription_id,
			],
		];
	}

	function stripe_get_template_token($template_id=0) {
		global $zulu, $class_renew, $class_sale;

		//-- Check template is set
		if($template_id <= 0) {
			return null;
		}

		//-- Connect
		$this->stripe_connect();

		//-- Get Renew Template Data
		$template_data = $class_renew->template_data(['id'=>$template_id]);
		if($template_data['id'] <= 0) {
			return null;
		}

		//-- Do API request to create the PRODUCT object
		try {
			$post_data = [
				'name' 			=>	stripslashes($template_data['title']),
				'description'	=>	(trim($template_data['description'])!=NULL?strip_tags(stripslashes($template_data['description'])):NULL),
				'type'			=>	'service',
				'active'		=>	true,
				'unit_label'	=>	$this->renew_unit_label($template_data['renew_scale']),
				'statement_descriptor'	=>	$zulu->shorten(strtoupper($zulu->slug(stripslashes($template_data['title']))),8),
			];
			$response = \Stripe\Product::create($post_data);
			if(trim($response->id) != '') {
				$stripe_product_id = $response->id;
			} else {
				return null;
			}

		} catch(Exception $e) {
			return null;
		}

		//-- Do API request to create the PRICING PLAN object
		$stripe_plan_id = $zulu->meta_value('renew_template', $template_data['id'], $this->config->unique_key->renew_temp)['value'];
		if($stripe_plan_id == '') {

			$total_payment = $class_sale->payment_summary($template_data['price']);

			$plan_amount = $this->amount_value((($template_data['quantity'] * $total_payment['total']) * $template_data['renew_interval']));

			try {
				$post_data = [
					'nickname'			=>	stripslashes($template_data['title']),
					'interval'			=>	$this->renew_interval_label($template_data['renew_scale']),
					'interval_count'	=>	$template_data['renew_interval'],
					'amount'			=>	$plan_amount,
					'currency'			=>	LOCALE_currency_code,
					'product'			=>	$stripe_product_id,
					'trial_period_days'	=>	$this->renew_trial_length($template_data['trial_length'], $template_data['trial_scale']),
				];
				$response = \Stripe\Plan::create($post_data);
				if(trim($response->id) != '') {
					$zulu->meta_update('renew_template', $template_data['id'], $this->config->unique_key->renew_temp, $response->id);
					$stripe_plan_id = $response->id;
				} else {
					return null;
				}
			} catch(Exception $e) {
				return null;
			}
		}

		return $stripe_plan_id;
	}

	function renew_unit_label($rs) {
		global $zulu;

		$output = null;
		switch($rs) {
			case 'm':
				$output = 'MONTHLY';
				break;
			case 'd':
				$output = 'DAILY';
				break;
			case 'w':
				$output = 'WEEKLY';
				break;
			case 'y':
				$output = 'YEARLY';
				break;
			default:
				$output = null;
				break;
		}

		return $output;
	}

	function renew_interval_label($rs) {
		global $zulu;

		$output = null;
		switch($rs) {
			case 'm':
				$output = 'month';
				break;
			case 'd':
				$output = 'day';
				break;
			case 'w':
				$output = 'week';
				break;
			case 'y':
				$output = 'year';
				break;
			default:
				$output = null;
				break;
		}

		return $output;
	}

	function renew_trial_length($tl,$ts) {
		$output = null;
		switch($ts) {
			case 'm':
				$output = (30*$tl);
				break;
			case 'd':
				$output = (1*$tl);
				break;
			case 'w':
				$output = (7*$tl);
				break;
			case 'y':
				$output = (365*$tl);
				break;
			default:
				$output = (30*$tl);
				break;
		}
		if($tl==0 || trim($ts)==NULL) {
			$output = 0;
		}

		return $output;
	}

	function amount_value($amt) {
		return ($amt*100);
	}
	function amount_value_decode($amt) {
		return ($amt/100);
	}

	function renew_update($renew_id) {
		global $class_client, $zulu, $class_renew, $class_setting, $class_sale, $class_module;

        $module_row = $class_module->module_data(['class'=>$this->class_name]);

		//-- Renew Data
		$error_log = [];
		$this->renew_data_set($renew_id);
		$renew_data = $this->vars->renew->data;
		$renew_meta = $this->vars->renew->meta;
		$class_renew->get($renew_id);

		$subscription_id = $renew_meta->{$this->config->unique_key->template};

		//-- Stripe Token
		if(trim($subscription_id) == null) {
			return ['success'=>false];
		}

		//-- Connect
		$this->stripe_connect();

		//--
		try {
			$response = \Stripe\Subscription::retrieve($subscription_id);

			$period_start = $response->current_period_start;
			$period_end = $response->current_period_end;
			$data_renew_key = md5($response->id."-".$period_start."-".$period_end);

			//-- Force PERIOD END to midnight of that night

			//-- Check renewal log exists / if not, create it, then apply it if applicable
			$renewal_log_id = $class_renew->renew_log_by_period($renew_data->id, $period_start, $period_end);

			if($renewal_log_id <= 0) {
				//-- GENERATE New Log, New Sale & Add Payment & Process IF Applicable

				$data_renew = array(
					"renew_key"		=>	$data_renew_key,
					"renew_time"	=>	time(),
					"renew_from"	=>	$period_start,
					"renew_to"		=>	$period_end,
					"price"			=>	($renew_data->price * $renew_data->quantity),
				);
				$data_renew['trial'] = ($response->trial_start == $period_start && $response->trial_end == $period_end?1:0);

				//-- Get invoice
				$response_invoice = \Stripe\Invoice::retrieve($response->latest_invoice);
				if($response_invoice->id != '') {

					$client_data = $class_client->client_data(['id'=>$renew_data->client_id]);
					$check_sale_key = $data_renew_key;
					$check_sale_key_old = $renew_data->id.'_'.$renew_data->renew_next; //-- INCORRECT? Changed to above data_renew_key
					$line_data = [];
					$line_data[] = $class_renew->charge_item_array($data_renew);

					if(isset($response_invoice->lines['data'][0]->amount)) {
						$price_to_use = $this->amount_value_decode($response_invoice->lines['data'][0]->amount);
						//-- Force it to pretend including to give INVOICE right payment value
						if($class_setting->setting_for_user($renew_data->user_id, 'tax_method') <= 0) {
							$price_to_use = $class_sale->payment_summary($price_to_use,['tax_method'=>1]);
							$price_to_use = $price_to_use['subtotal_raw'];
						}
						$data_renew['price'] = $price_to_use;
						$line_data[0]['price'] = $price_to_use;
						$line_data[0]['quantity'] = 1;
					}

					$process = array(
						'client_id'	=>	$client_data['id'],
						'name'		=>	$client_data['name'],
						'email'		=>	$client_data['email'],
						'date'      =>  $zulu->dateDecode(time()),
						'date_due'  =>  $zulu->dateDecode(strtotime("+".$class_sale->DEFAULT_due." days")),
						'line'		=>	$line_data,
						'meta'		=>	[
							'check_period'		=>	$check_sale_key,
							'stripe_invoice_id'	=>	$response_invoice->id,
							'stripe_invoice'	=>	1,
						]
					);

					//-- Check for double ups
					$bill_sale_balance = 0;
					$meta_du_check = $zulu->table_data('sale_meta',0,['where'=>["field = 'check_period'","value = '".$check_sale_key."'"],'first'=>true,'sort'=>'id DESC']);
					if($meta_du_check['id']>0) {
						$du_sale_data = $class_sale->sale_data(['id'=>$meta_du_check['identifier'],'field'=>['status']]);
						if($du_sale_data['status']!=1) {
							unset($meta_du_check); //if the double up sale is not valid, then make new invoice
						}
						$bill_id = $meta_du_check['identifier'];
						$bill_sale_balance = $class_sale->sale_balance($bill_id);
					}

					//-- Execute & create sale
					if(!isset($meta_du_check['id']) || $meta_du_check['id']==0) {

						$result = $class_sale->sale_edit(0, $process);
						$bill_id = $result['id'];

						if(!$result['success']) {
							return ['success'=>false];
						}

						$line_data = $zulu->table_data('sale_line',0,['where'=>["sale_id = '".$bill_id."'","object = 'renew'","object_id = '".$renew_data->id."'"],'first'=>true,'field'=>['id']]);

						//-- Create log to encompass new sale id etc - THEN APPLY payment and run complete function
						$data_renew["object_id"] = $line_data['id'];
						$data_renew["object"] = 'sale_line';
						$renew_log = $class_renew->renew_log_new($renew_data->id, $data_renew);

						//-- Apply payments
						$stripe_paid = $this->amount_value_decode($response_invoice->amount_paid);
						$total_paid = $class_sale->sale_total_paid($bill_id);
						if($stripe_paid != $total_paid) {
							$difference = abs($stripe_paid - $total_paid);

							$payment_response = $class_sale->payment_create(array(
								"sale_id"		=>	$bill_id,
								"client_id"		=>	$client_data['id'],
								"pay_total"		=>	$difference,
								"info"			=>	"Payment from Stripe",
								"method"		=>	"stripe",
								'method_data'	=>	serialize([
									'invoice_id'	=>	$response_invoice->id,
								]),
								"module_id"		=>	$module_row['id'],
								"valid"			=>	true,
							));
						}
						$class_sale->complete($bill_id);

						//-- Renew log application
						if($renew_log['id']<=0) {

						} else {
							if($class_renew->is_renew_instant()) {
								$apply_result = $class_renew->apply_renewal_period(['id'=>$renew_log['id']]);
							}
						}

					} elseif($bill_id > 0 && $bill_sale_balance > 0) {
						//-- Check existing sales balance & pay if needed - FOR existing ONLY
						$stripe_paid = $this->amount_value_decode($response_invoice->amount_paid);
						$total_paid = $class_sale->sale_total_paid($bill_id);

						$difference = abs($stripe_paid - $total_paid);

						$payment_response = $class_sale->payment_create(array(
							"sale_id"	=>	$bill_id,
							"client_id"	=>	$client_data['id'],
							"pay_total"	=>	$difference,
							"info"		=>	"Payment from Stripe",
							"method"	=>	"stripe",
							'method_data'	=>	serialize([
								'invoice_id'	=>	$response_invoice->id,
							]),
							"module_id"	=>	$module_row['id'],
							"valid"		=>	true,
						));
					}

					//-- Trigger Sale Complete if set
					if($bill_id > 0) {
						$class_sale->complete($bill_id);
					}
				}

			} else {
				//-- FIND Existing Log Item & Invoice, Check Payment
				$renew_log_data = $class_renew->renew_log_data(['id'=>$renewal_log_id]);
				if($renew_log_data['id'] > 0) {

					//-- Get STRIPE Invoice
					$response_invoice = \Stripe\Invoice::retrieve($response->latest_invoice);

					if($response_invoice->id != '') {
						$check_sale_key = $data_renew_key;
						$meta_du_check = $zulu->table_data('sale_meta',0,['where'=>["field = 'check_period'","value = '".$check_sale_key."'"],'first'=>true,'sort'=>'id DESC']);
						if($meta_du_check['id'] > 0) {
							$du_sale_data = $class_sale->sale_data(['id'=>$meta_du_check['identifier'],'field'=>['status','stat_add','id']]);
							if($du_sale_data['status'] == 1) {
								$bill_id = $meta_du_check['identifier'];
								$bill_sale_balance = $class_sale->sale_balance($bill_id);

								//-- Check existing sales balance & pay if needed - FOR existing ONLY
								$stripe_paid = $this->amount_value_decode($response_invoice->amount_paid);
								$total_paid = $class_sale->sale_total_paid($bill_id);
								if($stripe_paid != $total_paid) {
									$difference = abs($stripe_paid - $total_paid);

									$payment_response = $class_sale->payment_create(array(
										"sale_id"		=>	$bill_id,
										"client_id"		=>	$client_data['id'],
										"pay_total"		=>	$difference,
										"info"			=>	"Payment from Stripe",
										"method"		=>	"stripe",
										'method_data'	=>	serialize([
											'invoice_id'	=>	$response_invoice->id,
										]),
										"module_id"		=>	$module_row['id'],
										"valid"			=>	true,
									));

									$class_sale->complete($bill_id);
								}

								//-- Check Older Sales
								if($response_invoice->paid) { //--TEMP = only does this if current sale paid
									$sale_line_unpaid = $zulu->table_data($class_sale->SQL_table_sale_line,0,['where'=>["object_id = '".$renew_log_data['renew_id']."'","object = 'renew'","stat_add < ".$du_sale_data['stat_add'],$class_sale->SQL_table_sale_line.".id != ".$du_sale_data['id'],"sm.field = 'stat_paid'","sm.value = '0'"],'field'=>[$class_sale->SQL_table_sale_line.'.sale_id AS sale_id'],'join'=>"sale_meta sm ON ".$class_sale->SQL_table_sale_line.".sale_id = sm.identifier",'test'=>false,'sort'=>$class_sale->SQL_table_sale_line.".id DESC"]);
									foreach($sale_line_unpaid as $slu) {
										//--- COULD ADD lookup here for invoice then apply sale payment?? For now just apply payment regardless.
										$bill_id = $slu['sale_id'];
										$payment_response = $class_sale->payment_create(array(
											"sale_id"		=>	$bill_id,
											"client_id"		=>	$client_data['id'],
											"pay_total"		=>	$class_sale->sale_balance($bill_id),
											"info"			=>	"Payment from Stripe",
											"method"		=>	"stripe",
											'method_data'	=>	serialize([
												'invoice_id'	=>	$response_invoice->id,
											]),
											"module_id"		=>	$module_row['id'],
											"valid"			=>	true,
										));

										$class_sale->complete($bill_id);
									}
								}
							}
						}
					}
				}
			}

			//--
			$update_data = [
				'renew_first'	=>	$response->billing_cycle_anchor,
				//'period_start'	=>	$response->current_period_start,
				//'period_end'	=>	$response->current_period_end,
				//'renew_next'	=>	$response->current_period_end,
				'trial_start'	=>	$response->trial_start,
				'trial_expire'	=>	$response->trial_end,
				'trial_count'	=>	($response->trial_end>0&&$renew_data->trial_expire!=$response->trial_end?($renew_data->trial_count+1):$renew_data->trial_count),
				'cancel_date'	=>	$this->renew_cancel_date($response),
				'status'		=>	$this->renew_status_handler($response),
				'auto_renew'	=>	1,
			];
			$result = $class_renew->renew_edit($renew_data->id, $update_data);

			if($update_data['status'] == 3 && $renew_data->client_id > 0) {
				$class_client->client_update($renew_data->client_id, [
					'subscribed'	=>	'0',
				]);
			}

		} catch(Exception $e) {
			return ['success'=>false];
		}

		if($result['success']) {
			return [
				'success'	=>	true,
				'reason'	=>	NULL,
			];
		} else {
			return [
				'success'	=>	false,
				'reason'	=>	"Failed to update subscription.",
			];
		}
	}

	function renew_cancel_date($object) {
		if($object->canceled_at>0 && $object->cancel_at==0) {
			return $object->canceled_at;
		} elseif($object->cancel_at>0) {
			return $object->cancel_at;
		} else {
			return 0;
		}
	}

	function renew_status_handler($object) {

		if($this->renew_cancel_date($object)>0 && $this->renew_cancel_date($object)<time()) {
			$value = 3;
		} else {
			$value = 1;
			if($response->current_period_start > time()) {
				$value = 0;
			}
		}
		return $value;
	}

	function renew_cancel() {
		global $class_client,$zulu,$class_renew,$class_sale;

		//-- Renew Data
		$error_log = [];
		$renew_data = $this->vars->renew->data;
		$renew_meta = $this->vars->renew->meta;

		$template_data = $this->vars->renew_template->data;
		$template_meta = $this->vars->renew_template->meta;

		$subscription_id = $renew_meta->{$this->config->unique_key->template};

		//-- Connect
		$this->stripe_connect();

		//-- Run API Call to cancel
		$api_result = (object)['success'=>false,'reason'=>'No API call was made.'];
		try {
			$response = \Stripe\Subscription::retrieve($subscription_id);

			if($response->id != '') {
				$cancel_policy = $template_meta->cancel_policy;

				//-- No policy OR the next renewal date is before now
				if(trim($cancel_policy)==NULL || $renew_data->renew_next<time()) {
					$cancel_policy = 'instant';
				}

				//-- Switch policies
				switch($cancel_policy) {
					case 'next':
						$api_result = \Stripe\Subscription::update(
							$subscription_id,[
								'cancel_at_period_end'	=>	true,
							]
						);
						$check_key = 'cancel_at';
						break;
					case 'instant':
						$api_result = $response->delete();
						$check_key = 'canceled_at';
						break;
				}

				if($api_result->{$check_key} > 0) {
					$this->renew_update($renew_data->id);
					$this->renew_data_refresh();
					$api_result = (object)['success'=>true,'reason'=>'','response'=>$response,'cancel_date'=>$api_result->cancel_at];
				} else {
					$api_result = (object)['success'=>false,'reason'=>'No API call was made.'];
				}
			}

		} catch(\Stripe\Error\Subscription $e) {
			$zulu->fatal_error("Stripe Error","API error occurred (SUBSCR).");
		} catch (\Stripe\Exception\ApiErrorException $e) {
			$zulu->fatal_error("Stripe Error",$e->getMessage());
		}

		$return = ['success'=>false,'reason'=>'Failed to cancel.'];
		if($api_result->success) {
			$return = ['success'=>true,'reason'=>NULL];
		}

		return $return;
	}

	function renew_cancel_undo() {
		global $class_client,$zulu,$class_renew;

		//-- Renew Data
		$error_log = [];
		$renew_data = $this->vars->renew->data;
		$renew_meta = $this->vars->renew->meta;
		$subscription_id = $renew_meta->{$this->config->unique_key->template};

		//-- Connect
		$this->stripe_connect();

		//-- Run API Call to cancel
		$api_result = (object)['success'=>false,'reason'=>'No API call was made.'];
		try {
			$response = \Stripe\Subscription::retrieve($subscription_id);

			if($response->id != '') {
				$api_result = \Stripe\Subscription::update(
					$subscription_id,[
						'cancel_at_period_end'	=>	false,
					]
				);
				if($api_result->cancel_at == 0) {

					$this->renew_update($renew_data->id);
					$this->renew_data_refresh();

					$api_result = (object)['success'=>true,'reason'=>'','response'=>$response];
				} else {
					$api_result = (object)['success'=>false,'reason'=>'No API call was made.'];
				}
			}

		} catch(\Stripe\Error\Subscription $e) {
			$zulu->fatal_error("Stripe Error","API error occurred (SUBSCR).");
		} catch (\Stripe\Exception\ApiErrorException $e) {
			$zulu->fatal_error("Stripe Error",$e->getMessage());
		}
		$return = ['success'=>false,'reason'=>'Failed to cancel.'];
		if($api_result->success) {
			$return = ['success'=>true,'reason'=>NULL];
		}
		return $return;
	}

	function renew_template_change($template_id) {
		global $class_client,$zulu,$class_renew,$class_sale;

		//-- Renew Data
		$error_log = [];
		$renew_data = $this->vars->renew->data;
		$renew_meta = $this->vars->renew->meta;
		$subscription_id = $renew_meta->{$this->config->unique_key->template};
		$template_row = $class_renew->template_data(['id'=>$template_id]);

		//-- Connect
		$this->stripe_connect();

		//-- Run API Call to cancel
		$api_result = (object)['success'=>false,'reason'=>'No API call was made.'];
		try {

			//-- Get Plan Stripe ID
			$stripe_plan_token = $this->stripe_get_template_token($template_row['id']);
			if(!$stripe_plan_token) {
				return ['success'=>false];
			}

			$response = \Stripe\Subscription::retrieve($subscription_id);
			if($response->id != '') {

				$total_payment = $class_sale->payment_summary($template_row['price']);
				$plan_amount = $this->amount_value((($template_row['quantity'] * $total_payment['total']) * $template_row['renew_interval']));

				$sub_data = [
					'cancel_at_period_end'	=>	false,
					'proration_behavior'	=>	'none',
					//'billing_cycle_anchor'	=>	'unchanged',
					//'trial_end'				=>	$renew_data->period_end,
					'items'					=>	[
						[
							'id'	=> 	$response->items->data[0]->id,
							'price'	=>	$stripe_plan_token,
						]
					],
				];
				if($renew_data->trial_expire <= time()) {
					$sub_data['trial_end'] = $renew_data->period_end;
				}

				$api_result = \Stripe\Subscription::update($subscription_id, $sub_data);

				if($api_result->cancel_at == 0) {

					$this->renew_update($renew_data->id);
					$this->renew_data_refresh();

					$api_result = (object)['success'=>true,'reason'=>'','response'=>$response];
				} else {
					$api_result = (object)['success'=>false,'reason'=>'No API call was made.'];
				}
			}

		} catch(\Stripe\Error\Subscription $e) {
			$zulu->fatal_error("Stripe Error","API error occurred (SUBSCR).");
		} catch (\Stripe\Exception\ApiErrorException $e) {
			$zulu->fatal_error("Stripe Error",$e->getMessage());
		}

		$return = ['success'=>false,'reason'=>'Failed to cancel.'];
		if($api_result->success) {
			$return = ['success'=>true,'reason'=>NULL];
		}
		return $return;
	}

	function payment_method_detail() {
		global $class_client, $zulu, $class_renew, $class_module;

		//-- Renew Data
		$error_log = [];
		$renew_data = $this->vars->renew->data;
		$renew_meta = $this->vars->renew->meta;
		$subscription_id = $renew_meta->{$this->config->unique_key->template};
		$stripe_customer_id = $zulu->meta_value('client', $renew_data->client_id, $this->config->unique_key->customer)['value'];

		//-- Connect
		$this->stripe_connect();

		try {
			$response = \Stripe\Subscription::retrieve($subscription_id);
			if($response->id != '') {
				$source_id = $response->default_source;
				if(!$source_id) {
					$response = \Stripe\Customer::retrieve($stripe_customer_id);
					$source_id = $response->default_source;
				}
				$response = \Stripe\Customer::retrieveSource($stripe_customer_id, $source_id);

				return [
					'success'			=>	true,
					'payment_method'	=>	'Stripe',
					'detail_html'		=>	"<div class='row no-gutters align-items-center'>
						<div class='col-auto'>
							<p class='m-0'>".$zulu->icon('credit-card', 'r', ['fa-lg'])."</p>
						</div>
						<div class='col-auto ml-2'>
							<p class='m-0'>
								<span class='card'>Your card ending in <b>".$response->last4."</b></span>
								<br />
								<span class='expiry'><small>Expires: ".$response->exp_month."/".$response->exp_year."</small></span>
							</p>
						</div>
					</div>",
				];

			}

		} catch (Exception $e) {
			//print_r($e->getMessage());exit;
			return [
				'success'	=>	false,
				'log'	=>	[
					$e->getMessage().' Please try again.',
				]
			];
		}
	}

	function update_payment($id) {
		global $class_client, $zulu, $class_renew, $class_module;

		//-- Renew Data
		$error_log = [];
		$this->renew_data_set($id);
		$renew_data = $this->vars->renew->data;
		$renew_meta = $this->vars->renew->meta;
		$subscription_id = $renew_meta->{$this->config->unique_key->template};
		$stripe_token = $_POST['stripeToken'];

		//-- Client Data
		$client_id = $renew_data->client_id;
		$client_data = $class_client->client_data(['id'=>$client_id]);
		$client_meta = $zulu->meta_array($class_client->client_meta($client_id));

		//-- Connect
		$this->stripe_connect();

		//-- Create Customer
		$stripe_customer_id = $zulu->meta_value('client', $client_id, $this->config->unique_key->customer)['value'];
		if($stripe_customer_id=='') {
			return [
				'success'	=>	false,
				'log'		=>	'Customer not found.'
			];
		}

		try {
			$response = \Stripe\Customer::update($stripe_customer_id, [
				'source' 	=>	$stripe_token,
			]);

			$api_result = \Stripe\Subscription::update($subscription_id, [
				'default_source'	=>	$response->default_source,
			]);

		} catch(\Stripe\Error\Customer $e) {
			$zulu->fatal_error("Stripe Error","API error occurred (PAYSRC).");
		} catch (\Stripe\Exception\ApiErrorException $e) {
			return [
				'success'	=>	false,
				'log'		=>	$e->getMessage().' Please try again.',
			];
		}

		return [
			'success'	=>	true,
		];
	}

}

?>
