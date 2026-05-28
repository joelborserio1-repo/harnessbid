<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- DEFINITIONS
define(PAGE_file,'account');
define(PAGE_name,'Account');
$zulu->nav->breadcrumb['Account'] = array("link"=>$zulu->link_page('account'));

//-- AUTHORISED?
$class_user->user_authorised_check();

//-- ADMIN MASTER SECTION
if(MASTER_section=='admin') { //admin section
	
	//Roles
	foreach(user::role_data() as $role_row) {
		$USER_option[$role_row['tag']] = $role_row['name'];
	}
	
	$form_edit = new form;
	
	$zulu->template->head = "";
	$zulu->template->body = "";
	
	if(PAGE_action==NULL) {	//grid page
		
		function edit_bt($id) {
			global $zulu;
			return "
				<a href=\"".$zulu->link_page('user',array('query'=>array('id'=>$id,'Action'=>'edit')))."\"><button class=\"btn btn-primary btn-circle\" type=\"button\"><i class=\"fas fa-edit\"></i></button></a> 
				<a class=\"confirm-delete\" href=\"".$zulu->link_page('user',array('query'=>array('id'=>$id,'Action'=>'delete')))."\"><button class=\"btn btn-danger btn-circle\" type=\"button\"><i class=\"fas fa-times\"></i></button></a>
			";	
		}
		
		$table_column[] = array("ID",array('class'=>array('')));
		$table_column[] = array("Username",array('class'=>array('center')));
		$table_column[] = array("Email",array('class'=>array('center')));
		$table_column[] = array("Name",array('class'=>array('')));
		$table_column[] = array("Role",array('class'=>array('')));
		$table_column[] = array("API Key",array('class'=>array('')));
		$table_column[] = array("Actions",array('class'=>array('right')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);
				
		$user_row = $class_user->user_data();
		foreach($user_row as $row) {
			$user_meta = $class_user->user_meta($row['id']);
			
			$table_row[] = array("content" => array(
				array($row['id']),
				array($row['username']),
				array($row['email']),
				array($row['name_first'].($row['name_last']!=NULL?" ".$row['name_last']:NULL)),
				array($USER_option[$user_meta['role']['value']]),
				array($user_meta['api_key']['value']),
				array(edit_bt($row['id']),array('class'=>array('right')))
			));
		}
		
		$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'basket'));
		$zulu->nav->title = "Users";
	}
	if(PAGE_action=='welcome') {	//welcome page for new users
		$zulu->nav->breadcrumb = NULL; //set null
		$zulu->nav->breadcrumb["Welcome to ".MAIN_name] = array();
		$zulu->nav->title = "Welcome ".MAIN_name;
	}
	if(PAGE_action=='welcome_dismiss') { //dismiss welcome page
		$zulu->meta_update("user",$class_user->authorised->id,"welcome_dismiss",1);
		$zulu->notification_set("Great, all you need to do is fill in the information below, then you can start using the great features available.",1);
		header("Location: ".$zulu->link_page('setting')); //goes to setting page
		exit;
	}
	if(PAGE_action=='subscription') { //subscriptions page
		$zulu->nav->breadcrumb["Your Subscription"] = array();
		$zulu->nav->title = "Your Subscription";
		$zulu->template->js_file[] = "https://js.braintreegateway.com/v2/braintree.js";
		
		$mid = $class_user->authorised->id;
		$row_MEM = $zulu->table_data("user",$class_user->authorised->id);
		$meta = $class_user->user_meta($mid);
		$meta = $zulu->meta_array($meta);
		
		$user_data = $row_MEM;
		$subscribed = $class_subscribe->status();
		
		//Cancel Braintree
		if($_GET['Do']=='Suspend'&&$subscribed['autobilling']) {
			$subscription = Braintree_Subscription::cancel($meta['subscribe_braintree_id']);
			if($subscription->success>0) {
				$zulu->meta_update('user',$class_user->authorised->id,'braintree_token','');
				$zulu->meta_update("user",$class_user->authorised->id,"plan_cancelled",1);
				$zulu->notification_set("You were unsubscribed! Sorry to see you go, feel free to re-subscribe if you miss some of our great features!",1);
			} else {
				$zulu->notification_set("An error occurred cancelling your subscription, please contact us.",2);
			}
			header("Location: ".$zulu->link_page('account',array('query'=>array('Action'=>'subscription'))));
			exit;
		}
		
		//Has Braintree subscription?
		if($meta['subscribe_braintree_id']!=NULL) {
			try {
				Braintree_Subscription::find($meta['subscribe_braintree_id']);
			} catch (Braintree_Exception_NotFound $e) {
				$zulu->notification_set("Braintree error on subscription: ".$e->getMessage().". We have notified the administrator and will get back to you shortly, sorry for any  inconvienience.",2);
				$zulu->mail_send(MAIN_email,"Subscribe Page Braintree Find",$e->getMessage());
				header("Location: ".$_SERVER['HTTP_REFERER']);
				exit;
			}
			$subscription = Braintree_Subscription::find($meta['subscribe_braintree_id']);
			$date_info = (array)$subscription->nextBillingDate;
			$paidthrough = new DateTime($date_info['date'], new DateTimeZone($date_info['timezone']));
			
			$exp_ts = $paidthrough->getTimestamp()+86399;
			$class_subscribe->set_expiry($mid,$exp_ts);
			$_SESSION['SH_Expiry'] = $exp_ts;
		}
		
		//Vars
		$account_referer = $row_MEM['account_referer'];
		
		//Trial?
		if($_GET['Do']=='Trial') {
			
			if($meta['subscribe_trial']>0) {
				$_SESSION['SH_Error'] = 'Sorry, you have already used your free trial.';
				$_SESSION['SH_Error_Class'] = 2;
			} else {
				$plan_array = explode('-',$_GET['Plan']);
				if($class_subscribe->apply_plan($mid,$plan_array[0],trim($plan_array[1],'fee_mo'),true)) {
					$zulu->notification_set("Your FREE trial was started! Thanks and enjoy ".MAIN_name,1);
				} else {
					$zulu->notification_set("Your trial failed to load, you may have used it already!",2);
				}
				header("Location: ".$_SERVER['HTTP_REFERER']);
				exit;
			}
		}
		
		//Paid?
		if($_GET['Do']=='Paid') {
			
			$custom = unserialize(urldecode($_POST['custom']));
			$txnid = $_POST['txn_id'];
			$gross = $_POST['mc_gross'];
			
			if($custom['plan_id']>0) {
				$query_PLAN = $db->query("SELECT * FROM sh_plan_html WHERE id = '".$custom['plan_id']."'");
				$row_PLAN = $query_PLAN->fetch_array();
				$credit_extra = " for your '".$row_PLAN['title']."' plan purchase";
			}
			
			$query_PAYMENT = $db->query("SELECT * FROM sh_credit_paypal WHERE txnid = '{$txnid}'");
			$row_PAYMENT = $query_PAYMENT->fetch_array();
			$nrow_PAYMENT = $query_PAYMENT->num_rows;
			
			if($nrow_PAYMENT<1) {
				$_SESSION['SH_Error'] = 'Payment is invalid / declined. No data was found, please contact us if you are receiving this message in error.';
				$_SESSION['SH_Error_Class'] = 2;
			} else {
				$_SESSION['SH_Error'] = 'Thank you, '.stripslashes($row_MEM['fname']).', your credit was received'.$credit_extra.'.<br><br><b>Transaction ID:</b> '.$txnid.'<br><b>Total:</b> $'.number_format($gross,2).' '.$_POST['mc_currency'];
				$_SESSION['SH_Error_Class'] = 1;
				header("Location: index.php");
				exit;
			}
			
		}
		
		$total_pay = 0;
		$user_balance = $class_user->account_balance();
		
		if($meta['subscribe_trial']<=0) {
			$freetrial = true;
			$plan = $class_subscribe->plan_html(1,true,1,['master_mode'=>MASTER_mode]);
		} else {
			$plan = $class_subscribe->plan_html(1,false,3,['master_mode'=>MASTER_mode]);
		}
		$total_pay = $class_subscribe->data->plan_total;
		
		if($_GET['Do'] == 'PayCredit' && $total_pay>1) {
			if($user_balance >= $total_pay) {
				$new_bal = $total_pay-$user_balance;
				$plan_array = explode('-',$_GET['plan']);
				
				$class_user->account_adjust($total_pay);
				$class_subscribe->apply_plan($row_MEM['id'],$plan_array[0],trim($plan_array[1],'fee_mo'));
				
				$_SESSION['SH_Error'] = 'Great! Your new subscription has been applied. You have $'.$new_bal.' credit remaining.';
				$_SESSION['SH_Error_Class'] = 1;
				header("Location: index.php");
				exit;
			} else {
				$_SESSION['SH_Error'] = "Sorry, you don't have enough credit to pay for this subscription.";
				$_SESSION['SH_Error_Class'] = 2;
				header("Location: subscribe.php?plan=".$_GET['plan']);
				exit;	
			}
		}
		
		//GST Exempt?
		if($_GET['plan']&&$_GET['Do']=='GSTExempt') {
			$msg = "Hello admin,<br><br>".$user_data['company']." (".$user_data['id'].") set themselves to tax exempt on the checkout page.";
			$zulu->mail_send(MAIL_email,"User Tax Exempt",$msg);
			$zulu->meta_update("user",$user_data['id'],"subscribe_gst_exempt",1);
			header("Location: ".$zulu->link_page(PAGE_file,['self'=>true,'filter'=>['Do']]));
			exit;
		}
		
		//Trial Data
		if($_GET['plan']) {
			
			$plan = split("-",$db->escape_string($_GET['plan']));
			$period = str_replace("fee_","",$plan[1]);
			$period_short = str_replace("mo","",$period);
			$plan_data = $class_subscribe->plan_data($plan[0],$period);
			$expiry = $class_subscribe->next_renew($period);
			
			$total_pay = $plan_data['price']['period'];
			
			if($period=='trial') { //override if trial
				header("Location: ".$zulu->link_page('account',array('query'=>array('Action'=>'subscription','Do'=>'Trial','Plan'=>$_GET['plan']))));
				exit;
			}
			if($meta['subscribe_gst_exempt']>0) {
				//--no gst
				$bt_gst_suffix = '_nogst';
				$tax_suffix = 'Your subscription will be tax exempt (only for users outside of NZ).';
			} else {
				//--gst
				$tax_suffix = 'Excludes GST. Not a New Zealand user? Click <a href="'.$zulu->link_page(PAGE_file,['self'=>true,'query'=>['Do'=>'GSTExempt']]).'">here</a>.';
			}
			
			$plan_code = 'zulu_'.$plan_data['config_braintree_prefix']."_".$period_short.$bt_gst_suffix;
			
			//Has submitted BT card form
			if($_POST['action']=='bt_card') {
				$customer_id = $meta['braintree_token'];
				$token = $_POST['payment_method_nonce'];

				$msg = "Hello admin,<br><br>".$user_data['company']." (".$user_data['id'].") submitted their card for plan purchase.<br><br>Plan: ".$plan_data['name'];
				$zulu->mail_send(MAIL_email,"User Tax Exempt",$msg);

				try {
					$customer = Braintree_Customer::find($customer_id);
				} catch (Braintree_Exception_NotFound $e) {
					$_SESSION['SH_Error'] = "Braintree error finding customer. We have notified the administrator and will get back to you shortly, sorry the inconvienience.";
					$_SESSION['SH_Error_Class'] = 2;
					mail_admin("Subscribe Page Braintree Create Sub",$e->getMessage());
					header("Location: index.php");
					exit;
				}
				$sub_array = array(
				  'paymentMethodToken' => $customer->creditCards[0]->token,
				  'planId' => ($BT_plan_ovr!=NULL?$BT_plan_ovr:$plan_code),
				);
				
				if(BRAINTREE_merchant_id!=NULL) {
					$sub_array['merchantAccountId'] = BRAINTREE_merchant_id;
				}
				if($row['date_start']>strtotime(date("Ym"))) {
					$date = DateTime::createFromFormat('Ym', $row['date_start']);
					$sub_array['firstBillingDate'] = $date;
				}
				
				try {
					$result = Braintree_Subscription::create($sub_array);
				} catch (Braintree_Exception_NotFound $e) {
					$_SESSION['SH_Error'] = "Braintree error creating subscription. We have notified the administrator and will get back to you shortly, sorry the inconvienience.";
					$_SESSION['SH_Error_Class'] = 2;
					mail_admin("Subscribe Page Braintree Create Sub",$e->getMessage());
					header("Location: index.php");
					exit;
				}
				
				if($result->success>0) {
					
					$date_info = (array)$result->subscription->nextBillingDate;
					$paidthrough = new DateTime($date_info['date'], new DateTimeZone($date_info['timezone']));
					$class_subscribe->set_expiry($mid,($paidthrough->getTimestamp()+86399));
					
					$_SESSION['SH_Expiry'] = ($paidthrough->getTimestamp()+86399);
					$db->query("INSERT INTO user_subscribe (member,braintree_subscription,time) VALUES ('{$mid}','".$result->subscription->id."','".time()."')");
							
					$zulu->meta_update("user",$mid,"subscribe_braintree_id",$result->subscription->id);
					$class_user->account_adjust($row_MEM['id'],$zulu->serial(),$result->subscription->transactions[0]->id,'credit_braintree',"Account Credit","Account Credit from Braintree",$result->subscription->transactions[0]->amount);
					$class_subscribe->apply_plan($row_MEM['id'],$plan[0],trim($plan[1],'fee_mo'));
				
					$_SESSION['SH_Error_TitleOvr'] = "You're subscribed, thanks a bunch!";		
					
					$zulu->notification_set("<span class=\"fas fa-thumbs-up\"></span> Thanks ".$user_data['name_first'].", we hope you enjoy ".MAIN_name."! Your account is now fully active and you're able to utilise ".MAIN_name." for your business!",1);
					
					/*if(member_count_object($mid,'property')<1) {
						$_SESSION['SH_Error_Template'] = "help-tips";
						$_SESSION['SH_Error'] .= " Now, lets add some properties in, just press 'Add Property' to get started, for each property setup, we will add flatmates within them.";
						header("Location: property.php");
						exit;
					}*/
					
					header("Location: ".$zulu->link_page('account',array('query'=>array('Action'=>'subscription'))));
				} else {
					$zulu->notification_set("Your susbcription failed to create, please try again below.",2);
					header("Location: ".$zulu->link_page('account',array('query'=>array('Action'=>'subscription','plan'=>$_GET['plan']))));
				}
				exit;	
			}
			
			//Has Amount to Pay?
			if($total_pay>=0) { 
				$toggle_cc = true;
				
				//Check Has Braintree Customer ID
				
				if($meta['braintree_token']!=NULL) {
					$customer_id = $meta['braintree_token'];
					try {
						$clientToken = Braintree_ClientToken::generate(array(
							"customerId" => $customer_id
						));
					} catch (InvalidArgumentException $e) {
						$_SESSION['SH_Error'] = "Braintree error generating client token. We have notified the administrator and will get back to you shortly, sorry the inconvienience.";
						$_SESSION['SH_Error_Class'] = 2;
						$msg = "User: ".$class_user->authorised->id."<br><br>".$e->getMessage();
						$zulu->mail_send(MAIL_email,"BrainTree Client Token Invalid Error",$msg);
						header("Location: index.php");
						exit;
					} catch (Braintree_Exception_NotFound $e) {
						$_SESSION['SH_Error'] = "Braintree error generating client token. We have notified the administrator and will get back to you shortly, sorry the inconvienience.";
						$_SESSION['SH_Error_Class'] = 2;
						$msg = "User: ".$class_user->authorised->id."<br><br>".$e->getMessage();
						$zulu->mail_send(MAIL_email,"BrainTree Client Token Not Found Error",$msg);
						header("Location: index.php");
						exit;
					}
				} else { //creates customer in braintree if not already member with token
				
					try {
						$result = Braintree_Customer::create(array(
						'firstName' => $user_data['name_first'],
						'lastName' => $user_data['name_last'],
						'email' => $user_data['email'],
						'phone' => $meta['phone']
						));
						
						if($result->success) {
							$zulu->meta_update("user",$class_user->authorised->id,"braintree_token",$result->customer->id);
							header("Location: ".$zulu->link_page('account',array('query'=>array('Action'=>'subscription','plan'=>$_GET['plan']))));
							exit;
						}
					} catch (Braintree_Exception_NotFound $e) {
						$_SESSION['SH_Error'] = "Braintree error generating customer. We have notified the administrator and will get back to you shortly, sorry the inconvienience.";
						$_SESSION['SH_Error_Class'] = 2;
						mail_admin("Subscribe Page Braintree Create Customer",$e->getMessage());
						header("Location: index.php");
						exit;
					}
				}
			} else {
				
				//free? apply instantly
				$member_ID = $class_user->authorised->id;
				$class_subscribe->apply_plan($member_ID,$plan_select_id,$plan_select_period);
				$zulu->meta_update("user",$mid,"subscribe_trial",1);
				
				$zulu->notification_set("Thanks! Your FREE trial subscription was added. You can start using your account below.",1);
				header("Location: ".$zulu->link_page("index"));
				exit;
			}
			
			//Javascript
			$zulu->template->js_code[] = "//BT CLIENT TOKEN
			var clientToken = \"{$clientToken}\";
			
			braintree.setup(clientToken, \"dropin\", {
				container: \"payment-form\"
			});";
			
			
		}
	}
	if(PAGE_action=='statement'||PAGE_action=='subscription') {
		
		$zulu->nav->breadcrumb = NULL;
		$zulu->nav->breadcrumb["Account Statement"] = array();
		$zulu->nav->title = "Account Statement";
		
		//Account Statement
		$table_column[] = array("Date",array('class'=>array('')));
		$table_column[] = array("Transaction",array('class'=>array()));
		$table_column[] = array("Amount",array('class'=>array('center')));
		$table_column[] = array("Balance",array('class'=>array('')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);
				
		$data = $zulu->table_data('user_account',0,array('where'=>array('member = '.$class_user->authorised->id),'sort'=>"id DESC"));
		foreach($data as $row) {
			$user_meta = $class_user->user_meta($row['id']);
			
			$table_row[] = array("content" => array(
				array(zulu::date($row['time'],'d/m/y')),
				array($row['statement_label']),
				array($row['credit']),
				array($row['new_credit']),
			));
		}
		
		$zulu->template->account_statement = $zulu->table_render($table_row,0,array('class'=>'statement','data_table'=>false));	
	}
}