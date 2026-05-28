<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- DEFINITIONS
define(PAGE_file,'rule');
define(PAGE_name,'Mail');
$zulu->nav->breadcrumb[PAGE_name] = array("link"=>$zulu->link_page(PAGE_file));

//-- AUTHORISED?
$class_user->user_authorised_check();

if(!$class_user->authorised->opt_mail) {
	$zulu->notification_set("Sorry, you are not authorised to use the Mail area.",2);
	header("Location: ".$zulu->link_page("index"));exit;
}


//-- ADMIN MASTER SECTION
if(MASTER_section=='admin') { //admin section
		
	$zulu->template->head = "";
	$zulu->template->body = "";
	
	if(PAGE_action==NULL) {	//grid page
		
		function edit_bt($id) {
			global $zulu;
			return "
				<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'edit')))."\"><button class=\"btn btn-primary btn-xs\" type=\"button\"><i class=\"fas fa-edit\"></i> Edit</button></a> 
				<a class=\"confirm-delete\" href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'delete')))."\"><button class=\"btn btn-danger btn-xs\" type=\"button\"><i class=\"fas fa-times\"></i></button></a> 
			";	
		}
		
		$form_edit = new form;
		
		$table_column[] = array("Client",array('class'=>array('')));
		$table_column[] = array("Template",array('class'=>array('')));
		$table_column[] = array("Object",array('class'=>array('')));
		$table_column[] = array("Custom Text",array('class'=>array('')));
		$table_column[] = array("Frequency",array('class'=>array('')));
		$table_column[] = array("Last",array('class'=>array('')));
		$table_column[] = array("Next",array('class'=>array('')));
		$table_column[] = array("Count",array('class'=>array('')));
		$table_column[] = array("Status",array('class'=>array('')));
		$table_column[] = array("Added",array('class'=>array('')));
		$table_column[] = array("Actions",array('class'=>array('right')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);

		$data_row = $class_rule->rule_data();

		foreach($data_row as $row) {
			
			$next = date("d/m/Y",$class_rule->rule_next($row['id']));
			$next_label = ($next==date("d/m/Y")?"bold green":NULL);	
			
			$status = ($row['status']==1?"Active":"Inactive");
			$status_label = ($row['status']==1?NULL:"bold red");
			
			if($row['sequence_id']>0) {
				$sequence_data = $class_rule->sequence_data(['id'=>$row['sequence_id']]);
				$row['template'] = "Sequence: ".$sequence_data['title'];	
			}
			
			$table_row[] = array("content" => array(
				array($class_client->admin_link($row['client_id'])),
				array($row['template']),
				array(($row['object']!=NULL?$row['object']:'<span class="opt opt-grey">None</span>')),
				array(($row['custom_text']!=""?"<span class=\"opt opt-success\">Yes</span>":"<span class=\"opt opt-grey\">No</span>")),
				array("<i class=\"fas fa-sync-alt\"></i> Every ".$row['frequency']." Days"),
				array(($row['frequency_count']>0?zulu::time_history($row['frequency_last']):"<span class=\"opt opt-grey\">Never</span>")),
				array($next,array('class'=>array($next_label))),
				array($row['frequency_count']),
				array($status,array('class'=>array($status_label))),
				array(zulu::time_history($row['stat_add'])),
				array(edit_bt($row['id']),array('class'=>array('right')))
			));
		}
		
		$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'basket'));
		$zulu->nav->title = PAGE_name;
	}
	if(PAGE_action=='queue') { //sent queue
		$form_edit = new form;
		
		//-- tab
		if($_GET['View']=='email'||!isset($_GET['View'])) {
			$tab = 'email';
		} else {
			$tab = 'text';
		}
		if($_GET['HideCC']>0) {
			$hide_cc = true;
		}
		
		//-- Show: EMAIL
		if($tab=='email') {
			$table_column[] = array("Status",array('class'=>array('')));
			$table_column[] = array("Sent",array('class'=>array('')));
			$table_column[] = array("To",array('class'=>array('')));
			$table_column[] = array("Subject",array('class'=>array('')));
			$table_column[] = array("Attachment",array('class'=>array('')));
			$table_column[] = array("Opened",array('class'=>array('')));

			$table_row[] = array(
					"header"	=>	 true,
					"class"		=>	"",
					"content"	=>	$table_column);

			$data_row = $zulu->mail_log_data();
			foreach($data_row as $row) {
				if($row['client_id']>0) {
					$client_data = $class_client->client_data(array('id'=>$row['client_id'],'type'=>'all'));
				}
				$msg_link = "<a href=\"".$zulu->object_link($row['object'],$row['object_id'])."\">View ".ucfirst($row['object'])."</a>";

				$table_row[] = array("content" => array(
					array(($row['date_read']>0?"<span class=\"opt opt-success\" title=\"Read ".$zulu->date($row['date_read']).".\"><span class=\"fas fa-check\"></span> Read</span>":"<span class=\"opt opt-grey\"><span class=\"fas fa-envelope\"></span> Unread</span>")),
					array($zulu->date($row['date_sent'],'dS M').(date("Y",$row['date_sent'])<date("Y")?" ".$zulu->date($row['date_sent'],'\'y'):NULL)),
					array($row['msg_to']),
					array($row['msg_subject']),
					array($msg_link),
					array(($row['date_read']>0?zulu::time_history($row['date_read']):"-")),
				));
			}

			$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'basket'));
			$zulu->nav->title = PAGE_name;
			$zulu->nav->breadcrumb['Sent Mail'] = array();
		} else {
			$table_column[] = array("Credit",array('class'=>array('')));
			$table_column[] = array("To",array('class'=>array('')));
			$table_column[] = array("Subject",array('class'=>array('')));
			$table_column[] = array("Attachment",array('class'=>array('')));
			$table_column[] = array("Sent",array('class'=>array('')));

			$balance = $class_mod_sms->credit_balance();
			
			$table_row[] = array(
					"header"	=>	 true,
					"class"		=>	"",
					"content"	=>	$table_column);

			$data_row = $class_mod_sms->log_data();
			foreach($data_row as $row) {
				$msg_link = "<a href=\"".$zulu->object_link($row['object'],$row['object_id'])."\">View ".ucfirst($row['object'])."</a>";

				$table_row[] = array("content" => array(
					array(($row['credit']<0?"<span class='opt opt-danger'><i class='fas fa-minus-circle'></i> ".str_replace('-','',$row['credit'])." Debit</span>":"<span class='opt opt-success'><i class='fas fa-plus-circle'></i> ".$row['credit']." Credit</span>")),
					array((trim($row['recipient'])==NULL?'-':$row['recipient'])),
					array(($row['credit']<0?"<span title='".$row['message']."'>".$zulu->shorten($row['message'],30)."</span>":$row['credit_reference'])),
					array($msg_link),
					array($zulu->date($row['stat_add'],'dS M').(date("Y",$row['stat_add'])<date("Y")?" ".$zulu->date($row['stat_add'],'\'y'):NULL)),

				));
			}

			$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'basket'));
			$zulu->nav->title = PAGE_name;
			$zulu->nav->breadcrumb['Sent Mail'] = array();
		}
		
		//Gen BT Token
		$user_data = $class_user->user_data(['id'=>$class_user->authorised->id]);
		$meta = $zulu->meta_array($class_user->user_meta($class_user->authorised->id));
		if($meta['braintree_token']!=NULL) {
			$customer_id = $meta['braintree_token'];
			try {
				$clientToken = Braintree_ClientToken::generate(array(
					"customerId" => $customer_id
				));
			} catch (InvalidArgumentException $e) {
				echo $e->getMessage();exit;
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
		
		//-- Post
		if($_POST['payment_method_nonce'] != NULL) {
			$nonce = $db->escape_string($_POST['payment_method_nonce']);
			$amount = $_POST['text_pack']*0.10;
		
			try {
				$braintree_customer = Braintree_Customer::find($customer_id);
			} catch (Braintree_Exception_NotFound $e) {
				$fatal = true;
			} catch (InvalidArgumentException $e) {
				echo $e->getMessage();exit;
			}
		
			$payment_token = $braintree_customer->creditCards[0]->token;
			if($payment_token != NULL) {
				try {
					$result = Braintree_Transaction::sale(['paymentMethodToken'=>$payment_token,'amount'=>$amount]);
					$class_mod_sms->add_credit($_POST['text_pack'],['reference'=>'Topup of Texts with Credit Card']);
				} catch (Braintree_Exception_NotFound $e) {
					$fatal = true;
				}
				if($result->success > 0) {
					$zulu->notification_set("Thank you - your text balance has been topped up.",1);
					$hide_cc = true;
					header("Location: ".$zulu->link_page(PAGE_file,['self'=>true,'query'=>['HideCC'=>1]]));
					exit;
				} else {
					$zulu->notification_set("This has already been paid for, please contact us to manually complete your order. Do not make any further payments please.",2);
				}
				if($fatal) {
					$zulu->notification_set("Your card was declined possibly due to an invalid card or lack of funds.",2);
				}	
			} else {
				$zulu->notification_set("Sorry, we couldn\'t find your payment information. Please try again or contact us to process your order.",2);
			}
		}
		
		//Javascript
		$zulu->template->js_file[] = "https://js.braintreegateway.com/v2/braintree.js";
		$zulu->template->jquery[] = "//BT CLIENT TOKEN
		var clientToken = \"{$clientToken}\";
		braintree.setup(clientToken, \"dropin\", {
			container: \"payment-form\"
		});";
	}
	if(PAGE_action=='delete') { //delete
		if($class_rule->delete(PAGE_id)) {
			$zulu->notification_set("Rule removed successfully.",1);
			header("Location: ".$zulu->link_page(PAGE_file));
		} else {
			$zulu->notification_set("A database error occurred.",2);
		}
	}
	if(PAGE_action=='auto_rule_delete') { //delete
		if($class_rule->rule_auto_delete(PAGE_id)) {
			$zulu->notification_set("Auto Rule removed successfully.",1);
			header("Location: ".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'auto_rule'))));
		} else {
			$zulu->notification_set("A database error occurred.",2);
		}
	}
	if(PAGE_action=='edit') { //edit page
		
		$form_edit = new form;
		
		if(PAGE_id<1) {
			$id = 0;
			$new = true;	
			$zulu->nav->breadcrumb['New Rule'] = array();
			$zulu->nav->title = "New Rule";
		} else {
			$id = ($_POST['id']>0?$_POST['id']:($_GET['id']>0?$_GET['id']:0));
			
			$rule_data = $class_rule->rule_data(array('id'=>$id));
			
			if(!$_POST) {
				foreach($rule_data as $key=>$val) {
					$_POST[$key] = $val;	
				}
				if($rule_data['sequence_id']>0) {
					$_POST['template'] = 's'.$rule_data['sequence_id'];
					$is_sequence = true;
				}
				if($_POST['template_exclusion']!=NULL) {
					$variation_exclusion = explode(',',$_POST['template_exclusion']);
				}
			}
			
			$zulu->nav->breadcrumb['Edit Rule'] = array();	
			$zulu->nav->title = "Edit Rule";
		}
		
		//Sequences
		$sequence_list = [];
		$sequence_option = $class_rule->sequence_data(['parent_id'=>0]);
		foreach($sequence_option as $seq) {
			$sequence_list['s'.$seq['id']] = "Sequence: ".$seq['title'];	
		}
		
		//-- Variation Exclusion list
		$table_column[] = array("Exclude?");
		$table_column[] = array("Variation Name",array('class'=>array('')));
		$table_column[] = array("Email Subject",array('class'=>array('')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);
		$var_cat = ($_GET['template']!=NULL?$_GET['template']:$_POST['template']);
		if($var_cat[0]=='s') {
			$is_sequence = true;
		}
		if(trim($var_cat)!=NULL) {
			$data_row = $class_rule->template_data(['category'=>$var_cat]);
			if(count($data_row)<=0) {
				if($is_sequence) {
					$exclusion_table = "<span class=\"opt opt-grey\"><i class=\"fas fa-exclamation-circle\"></i> Not available for sequence templates</span>";
				} else {
					$exclusion_table = "<span class=\"opt opt-grey\"><i class=\"fas fa-exclamation-circle\"></i> No variations exist</span>";
				}
			} else {
				foreach($data_row as $row) {
					$table_row[] = array("content" => array(
						array($form_edit->input_html('checkbox','variation_exclusion['.$row['id'].']',$row['id'],['checked'=>(in_array($row['id'],$variation_exclusion)?true:false)])),
						array(stripslashes($row['title'])),
						array(stripslashes($row['subject'])),
					),"data"=>['post-id'=>$row['id']],"class"=>'variation-row');
				}
				$exclusion_table = $zulu->table_render($table_row,0,array('class'=>'menu-item','tbody'=>['id'=>'sortable-rows'],'data_table'=>false,'js_table'=>false));
			}
		} else {
			if(isset($data_row)) {
				$exclusion_table = "<span class=\"opt opt-grey\"><i class=\"fas fa-exclamation-circle\"></i> No variations exist</span>";
			} else {
				$exclusion_table = "<span class=\"opt opt-grey\"><i class=\"fas fa-exclamation-circle\"></i> Please select a template first</span>";
			}
		}
		
		if($_GET['Do']=='LoadExcl') {
			echo $exclusion_table;
			exit;
		}
		
		$zulu->template->jquery[] = "
		$(document).on('change','.input-template',function() {
			$.get('".$zulu->link_page(PAGE_file,['self'=>true,'query'=>['Do'=>'LoadExcl']])."&template=' + $(this).val(),function(data) {
				$('#panel-exclude-table').html(data);
				return false;
			});
		});
		$('.input-template').trigger('change');
		";
		//-- End VEL
		
		//Excusion list
		if(!$is_sequence) {
			if($_POST['template']!=NULL) {
				$zulu->template->body->exclude_table = $exclusion_table;
			} else {
				$zulu->template->body->exclude_table = "<span class=\"opt opt-grey\"><i class=\"fas fa-exclamation-circle\"></i> Please select a template first</span>";
			}
		} else {
			$zulu->template->body->exclude_table_hide = true;
		}
		
		//Form Submit
		if($_POST['action']=='edit') {
			$form_edit->valid = true;
			
			if($form_edit->validate(['frequency','status'])) {
				$form_edit->valid = false;
				$zulu->notification_set("Please specify all fields denoted *.",2);
			}
			if($_POST['client_id']<=0) {
				$form_edit->valid = false;
				$zulu->notification_set("Please select a client.",2);
			}
			if(trim($_POST['template'])==NULL) {
				$form_edit->valid = false;
				$zulu->notification_set("Please select a template.",2);
			}
			if($_POST['template'][0]=='s') {
				$_POST['sequence_id'] = str_replace("s","",$_POST['template']);
				unset($_POST['template']);
				
				if(!$new&&$rule_data['sequence_id']>0&&$_POST['sequence_id']!=$rule_data['sequence_id']) {
					$class_rule->rule_edit($id,['sequence_current_rule_id'=>0,'sequence_current_rule_count'=>0]);
				}
			} else {
				$_POST['sequence_id'] = 0;
				$class_rule->rule_edit($id,['sequence_current_rule_id'=>0,'sequence_current_rule_count'=>0]);
			}
			
			if($form_edit->valid) {
				
				$data['object'] = $_POST['object'];
				$data['frequency'] = (int)$_POST['frequency'];
				$data['custom_text'] = addslashes($_POST['custom_text']);
				$data['sequence_id'] = $_POST['sequence_id'];
				$data['client_id'] = $_POST['client_id'];
				$data['template'] = $_POST['template'];
				$data['status'] = $_POST['status'];
				$data['sequential'] = $_POST['sequential'];
				$data['loop_max'] = $_POST['loop_max'];
				$data['template_exclusion'] = implode(',',$_POST['variation_exclusion']);
				
				if($rule_data['template_user_id']==6) { //--resets anyone changing rule
					$data['template_user_id'] = 0;
				}
				
				$last = strtotime($_POST['frequency_ovr']);
				if($last>0) {
					$data['frequency_last'] = $last-(86400*$data['frequency']);
				}
								
				$data = $class_rule->rule_edit($id,$data);
				
				if($data['success']) {
					$zulu->notification_set("Rule ".(!$new?"updated":"created")." successfully.",1);
					header("Location: ".$zulu->link_page(PAGE_file));
					exit;
				} else {
					$zulu->notification_set("A database error occurred.",2);
				}
			}
		}
		
		$zulu->template->jquery[] = "
			$(document).ready(function() {
				$('.input-sequential').trigger('change');
			});
			$('.input-sequential').change(function() {
				if($(this).val()==1) {
					$(\".lbl-loop\").html(' Sequence');
				} else {
					$(\".lbl-loop\").html('');
				}
				return false;
			});
		";
	}
	if(PAGE_action=='auto_rule'){
		function edit_bt($id, $trigger_key) {
			global $zulu;
			return "
				<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'auto_rule_edit', 'Trigger'=>$trigger_key)))."\"><button class=\"btn btn-primary btn-xs\" type=\"button\"><i class=\"fas fa-edit\"></i> Edit</button></a> 
				
				<a class=\"confirm-delete\" href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'auto_rule_delete')))."\"><button class=\"btn btn-danger btn-xs\" type=\"button\"><i class=\"fas fa-times\"></i></button></a> 
			";	
		}
		$zulu->nav->breadcrumb['Automated Rules'] = array();
		$zulu->nav->title = 'Automated Rules';
		
		$zulu->template->body->trigger_rule_tables = [];
		
		$rule_auto_data = $class_rule->rule_auto_data();
		$table_rows = [];
		
		foreach($rule_auto_data as $row){				
			$next = date("d/m/Y",$class_rule->rule_next($row['id']));
			$next_label = ($next==date("d/m/Y")?"bold green":NULL);	
			
			$status = ($row['status']==1?"Active":"Inactive");
			$status_label = ($row['status']==1?NULL:"bold red");
			
//			if($row['sequence_id']>0) {
//				$sequence_data = $class_rule->sequence_data(['id'=>$row['sequence_id']]);
//				$row['template'] = "Sequence: ".$sequence_data['title'];	
//			}
			$send_delay_amount = 0;
			if($row['send_delay_amount']>0){
				$send_delay_amount = $row['send_delay_amount'];
			}
			$send_delay_html = $send_delay_amount." ".$row['send_delay_type'];
			$table_rows[$row['trigger_key']][] = array("content" => array(
				//array($class_client->admin_link($row['client_id'])),
				array($row['template']),
				//array(($row['object']!=NULL?$row['object']:'<span class="opt opt-grey">None</span>')),
				array((strlen(trim($row['custom_text']))?"<span class=\"opt opt-success\">Yes</span>":"<span class=\"opt opt-grey\">No</span>")),
				array($send_delay_html),
				//array("<i class=\"fas fa-sync-alt\"></i> Every ".$row['frequency']." Days"),
				//array(($row['frequency_count']>0?zulu::time_history($row['frequency_last']):"<span class=\"opt opt-grey\">Never</span>")),
				//array($next,array('class'=>array($next_label))),
				//array($row['frequency_count']),
				array($status,array('class'=>array($status_label))),
				array(zulu::time_history($row['stat_add'])),
				array(edit_bt($row['id'], $row['trigger_key']),array('class'=>array('right')))
			));
		}
		//print_r($table_rows);exit;
		foreach($class_rule->config->trigger_options as $key=>$trigger_option){
			$table_column[$key][] = array("Template",array('class'=>array('')));
			//$table_column[$key][] = array("Object",array('class'=>array('')));
			$table_column[$key][] = array("Custom Text",array('class'=>array('')));
			$table_column[$key][] = array("Send Delay",array('class'=>array('')));
			//$table_column[$key][] = array("Frequency",array('class'=>array('')));
			//$table_column[$key][] = array("Last",array('class'=>array('')));
			//$table_column[$key][] = array("Next",array('class'=>array('')));
			//$table_column[$key][] = array("Count",array('class'=>array('')));
			$table_column[$key][] = array("Status",array('class'=>array('')));
			$table_column[$key][] = array("Added",array('class'=>array('')));
			$table_column[$key][] = array("Actions",array('class'=>array('right')));
			$table_row[$key][] = array(
					"header"	=>	 true,
					"class"		=>	"",
					"content"	=>	$table_column[$key]);
			
			$table_row[$key] = array_merge($table_row[$key], (array)$table_rows[$key]);
			//print_r($table_row[$key]);exit;
			$zulu->template->body->trigger_rule_tables[$key] = $zulu->table_render($table_row[$key],0,array('class'=>'rules','js_table'=>false,'data_table'=>false));
		}
		//print_r($zulu->template->body->trigger_rule_tables);exit;
	}
	if(PAGE_action=='auto_rule_edit') { //edit page
		$trigger_array = [];
		if(!strlen(trim($_GET['Trigger'])) || !isset($class_rule->config->trigger_options[$_GET['Trigger']])){
			$zulu->notification_set("Invalid Trigger",1);
			header("Location: ".$zulu->link_page(PAGE_file));
		}else{
			$trigger_array = $class_rule->config->trigger_options[$_GET['Trigger']];
		}
		
		$form_edit = new form;
		
		if(PAGE_id<1) {
			$id = 0;
			$new = true;
			$zulu->nav->breadcrumb['Automated Rules'] = array('link'=>$zulu->link_page(PAGE_file,array('query'=>array('Action'=>'auto_rule'))));
			$zulu->nav->breadcrumb['New Automated Rule for '.$trigger_array['name_be']] = array();
			$zulu->nav->title = "New Automated Rule ".$trigger_array['name_be'];
		} else {
			$id = ($_POST['id']>0?$_POST['id']:($_GET['id']>0?$_GET['id']:0));
			
			$rule_data = $class_rule->rule_auto_data(array('id'=>$id));
			
			if(!$_POST) {
				foreach($rule_data as $key=>$val) {
					$_POST[$key] = $val;	
				}
				if($rule_data['sequence_id']>0) {
					$_POST['template'] = 's'.$rule_data['sequence_id'];
					$is_sequence = true;
				}
				if($_POST['template_exclusion']!=NULL) {
					$variation_exclusion = explode(',',$_POST['template_exclusion']);
				}
			}
			$zulu->nav->breadcrumb['Automated Rules'] = array('link'=>$zulu->link_page(PAGE_file,array('query'=>array('Action'=>'auto_rule'))));
			$zulu->nav->breadcrumb['Edit Automated Rule'] = array();	
			$zulu->nav->title = "Edit Automated Rule";
		}
		
		//Sequences
		$sequence_list = [];
		$sequence_option = $class_rule->sequence_data(['parent_id'=>0]);
		foreach($sequence_option as $seq) {
			$sequence_list['s'.$seq['id']] = "Sequence: ".$seq['title'];	
		}
		
		//-- Variation Exclusion list
		$table_column[] = array("Exclude?");
		$table_column[] = array("Variation Name",array('class'=>array('')));
		$table_column[] = array("Email Subject",array('class'=>array('')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);
		$var_cat = ($_GET['template']!=NULL?$_GET['template']:$_POST['template']);
		if($var_cat[0]=='s') {
			$is_sequence = true;
		}
		if(trim($var_cat)!=NULL) {
			$data_row = $class_rule->template_data(['category'=>$var_cat]);
			if($var_cat == '_default' && strlen(trim($_GET['Trigger']))){
				$trigger = $class_rule->config->trigger_options[$_GET['Trigger']];
				//print_r($trigger);exit;
				$data_row = $class_rule->template_data(['category'=>$trigger['default_template_category'], 'ovr_user_id'=>true, 'user_id'=>6]);
			}
			if(count($data_row)<=0) {
				if($is_sequence) {
					$exclusion_table = "<span class=\"opt opt-grey\"><i class=\"fas fa-exclamation-circle\"></i> Not available for sequence templates</span>";
				} else {
					$exclusion_table = "<span class=\"opt opt-grey\"><i class=\"fas fa-exclamation-circle\"></i> No variations exist</span>";
				}
			} else {
				foreach($data_row as $row) {
					$table_row[] = array("content" => array(
						array($form_edit->input_html('checkbox','variation_exclusion['.$row['id'].']',$row['id'],['checked'=>(in_array($row['id'],$variation_exclusion)?true:false)])),
						array(stripslashes($row['title'])),
						array(stripslashes($row['subject'])),
					),"data"=>['post-id'=>$row['id']],"class"=>'variation-row');
				}
				$exclusion_table = $zulu->table_render($table_row,0,array('class'=>'menu-item','tbody'=>['id'=>'sortable-rows'],'data_table'=>false,'js_table'=>false));
			}
		} else {
			if(isset($data_row)) {
				$exclusion_table = "<span class=\"opt opt-grey\"><i class=\"fas fa-exclamation-circle\"></i> No variations exist</span>";
			} else {
				$exclusion_table = "<span class=\"opt opt-grey\"><i class=\"fas fa-exclamation-circle\"></i> Please select a template first</span>";
			}
		}
		
		if($_GET['Do']=='LoadExcl') {
			echo $exclusion_table;
			exit;
		}
		
		$zulu->template->jquery[] = "
		$(document).on('change','.input-template',function() {
			$.get('".$zulu->link_page(PAGE_file,['self'=>true,'query'=>['Do'=>'LoadExcl']])."&template=' + $(this).val(),function(data) {
				$('#panel-exclude-table').html(data);
				return false;
			});
		});
		$('.input-template').trigger('change');
		";
		//-- End VEL
		
		//Excusion list
		if(!$is_sequence) {
			if($_POST['template']!=NULL) {
				$zulu->template->body->exclude_table = $exclusion_table;
			} else {
				$zulu->template->body->exclude_table = "<span class=\"opt opt-grey\"><i class=\"fas fa-exclamation-circle\"></i> Please select a template first</span>";
			}
		} else {
			$zulu->template->body->exclude_table_hide = true;
		}
		
		//Form Submit
		if($_POST['action']=='edit') {
			$form_edit->valid = true;
			
			if($form_edit->validate(['status'])) {
				$form_edit->valid = false;
				$zulu->notification_set("Please specify all fields denoted *.",2);
			}
//			if($_POST['client_id']<=0) {
//				$form_edit->valid = false;
//				$zulu->notification_set("Please select a client.",2);
//			}
			if(trim($_POST['template'])==NULL) {
				$form_edit->valid = false;
				$zulu->notification_set("Please select a template.",2);
			}
			if($_POST['template'][0]=='s') {
				$_POST['sequence_id'] = str_replace("s","",$_POST['template']);
				unset($_POST['template']);
				
				if(!$new&&$rule_data['sequence_id']>0&&$_POST['sequence_id']!=$rule_data['sequence_id']) {
					$class_rule->rule_edit($id,['sequence_current_rule_id'=>0,'sequence_current_rule_count'=>0]);
				}
			} else {
				$_POST['sequence_id'] = 0;
				$class_rule->rule_edit($id,['sequence_current_rule_id'=>0,'sequence_current_rule_count'=>0]);
			}
			
			if($form_edit->valid) {
				$data['trigger_key'] = $_GET['Trigger'];
				//$data['object'] = $_POST['object'];
				//$data['frequency'] = (int)$_POST['frequency'];
				$data['custom_text'] = addslashes($_POST['custom_text']);
				//$data['sequence_id'] = $_POST['sequence_id'];
				//$data['client_id'] = $_POST['client_id'];
				$data['template'] = $_POST['template'];
				$data['status'] = $_POST['status'];
				$data['send_delay_type'] = $_POST['send_delay_type'];
				$data['send_delay_amount'] = $_POST['send_delay_amount'];
				//$data['sequential'] = $_POST['sequential'];
				//$data['loop_max'] = $_POST['loop_max'];
				$data['template_exclusion'] = implode(',',$_POST['variation_exclusion']);
				
//				$last = strtotime($_POST['frequency_ovr']);
//				if($last>0) {
//					$data['frequency_last'] = $last-(86400*$data['frequency']);
//				}
				
				//print_r($data);exit;
								
				$data = $class_rule->rule_auto_edit($id,$data);
				
				if($data['success']) {
					$zulu->notification_set("Rule ".(!$new?"updated":"created")." successfully.",1);
					header("Location: ".$zulu->link_page(PAGE_file,array('query'=>array('Action'=>'auto_rule'))));
					exit;
				} else {
					$zulu->notification_set("A database error occurred.",2);
				}
			}
		}
		
		$zulu->template->jquery[] = "
			$(document).ready(function() {
				$('.input-sequential').trigger('change');
			});
			$('.input-sequential').change(function() {
				if($(this).val()==1) {
					$(\".lbl-loop\").html(' Sequence');
				} else {
					$(\".lbl-loop\").html('');
				}
				return false;
			});
		";
	}
	if(PAGE_action=='template') {	//grid page
		
		$form_edit = new form;
		
		$table_column[] = array("Template",array('class'=>array('')));
		$table_column[] = array("Variations",array('class'=>array('')));
		$table_column[] = array("Added",array('class'=>array('')));
		$table_column[] = array("Actions",array('class'=>array('right')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);

		$data_row = $class_rule->template_data(['group'=>true]);

		foreach($data_row as $row) {
			$table_row[] = array("content" => array(
				array(stripslashes($row['category'])),
				array(stripslashes($row['var_count'])),
				array(zulu::time_history($row['stat_add'])),
				array("<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('category'=>$row['category'],'Action'=>'template_variation')))."\" class=\"btn btn-default btn-xs\"><i class=\"fas fa-search\"></i> View</a>",array('class'=>array('right')))
			));
		}
		
		$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'basket'));
		$zulu->nav->title = 'Templates';
		$zulu->nav->breadcrumb['Templates'] = array();	
	}
	if(PAGE_action=='sequence_template') {	//grid page
		
		$form_edit = new form;
		
		$table_column[] = array("Sequence",array('class'=>array('')));
		$table_column[] = array("Variations",array('class'=>array('')));
		$table_column[] = array("Added",array('class'=>array('')));
		$table_column[] = array("Actions",array('class'=>array('right')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);

		$data_row = $class_rule->sequence_data(['parent_id'=>0]);

		foreach($data_row as $row) {
			$children = $class_rule->sequence_data(['parent_id'=>$row['id']]);
			$table_row[] = array("content" => array(
				array(stripslashes($row['title'])),
				array(count($children)),
				array(zulu::time_history($row['stat_add'])),
				array("<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('Action'=>'sequence_template_edit','id'=>$row['id'])))."\" class=\"btn btn-default btn-xs\"><i class=\"fas fa-search\"></i> View</a> <a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$row['id'],'Action'=>'sequence_delete')))."\" class=\"btn btn-danger btn-xs confirm\"><i class=\"fas fa-times\"></i> Delete</a>",array('class'=>array('right')))
			));
		}
		
		$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'basket'));
		$zulu->nav->title = 'Sequence Templates';
		$zulu->nav->breadcrumb['Sequence Templates'] = array();	
	}
	if(PAGE_action=='template_variation') {	//grid page
		
		//-- Do: Delete
		if($_GET['Do']=='delete') {
			if($class_rule->template_delete(PAGE_id)) {
				$zulu->notification_set("Template deleted successfully.",1);
				header("Location: ".$zulu->link_page(PAGE_file,['self'=>true,'filter'=>['Do']]));
				exit;
			} else {
				$zulu->notification_set("Failed to delete template.",2);
			}
		}
		
		//-- Do: Sort
		if($_GET['Do']=='sort') {
			$array = explode(",",$_GET['Array']);
			$i = 0;
			foreach($array as $item_id) {
				$class_rule->template_edit($item_id,['sort'=>$i]);
				$i++;
			}
			exit;
		}
		
		//-- Load Page
		function edit_bt($id) {
			global $zulu;
			return "
				<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'template_edit')))."\"><button class=\"btn btn-primary btn-xs\" type=\"button\"><i class=\"fas fa-edit\"></i></button></a> 
				<a class=\"confirm-delete\" href=\"".$zulu->link_page(PAGE_file,array('self'=>true,'query'=>array('id'=>$id,'Do'=>'delete')))."\"><button class=\"btn btn-danger btn-xs\" type=\"button\"><i class=\"fas fa-times\"></i></button></a> 
			";	
		}
		
		$form_edit = new form;
		
		//$table_column[] = array("Template",array('class'=>array('')));
		$table_column[] = array("Variation Name",array('class'=>array('')));
		$table_column[] = array("Email Subject",array('class'=>array('')));
		$table_column[] = array("Added",array('class'=>array('')));
		$table_column[] = array("Actions",array('class'=>array('right')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);

		$category = $db->escape_string($_GET['category']);
		$data_row = $class_rule->template_data(['category'=>$category]);

		foreach($data_row as $row) {
			$table_row[] = array("content" => array(
			//	array(stripslashes($row['category'])),
				array(stripslashes($row['title'])),
				array(stripslashes($row['subject'])),
				array(zulu::time_history($row['stat_add'])),
				array(edit_bt($row['id']),array('class'=>array('right')))
			),"data"=>['post-id'=>$row['id']],"class"=>'variation-row');
		}
		
		$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'menu-item','tbody'=>['id'=>'sortable-rows'],'data_table'=>false,'js_table'=>false));
		$zulu->nav->title = 'Template Variations';
		$zulu->nav->breadcrumb['Templates'] = ['link'=>$zulu->link_page(PAGE_file,['self'=>true,'query'=>['Action'=>'template']])];	
		$zulu->nav->breadcrumb[$category] = array();	
		
		//-- JS
		$zulu->template->jquery[] = "
		
		    $(\"#sortable-rows\").sortable({
				update: function(event, ui) {
					var srt = [];
					$(\"#sortable-rows\").children(\"tr\").each(function( index ) {
						srt.push($(this).data('post-id'));
					});
					$.get(\"".$zulu->link_page(PAGE_file,['query'=>['Action'=>PAGE_action,'Do'=>'sort','id'=>PAGE_id]])."&Array=\" + srt,function(data) {
						console.log(data);
					});
				}	
			});
    		$(\"#sortable\").disableSelection();";
		//--
	}
	if(PAGE_action=='template_edit') { //edit page
		
		$form_edit = new form;
		
		if(PAGE_id<1) {
			$id = 0;
			$new = true;	
			$zulu->nav->breadcrumb['New Template'] = array();
			$zulu->nav->title = "New Template";
			
			if(!isset($_POST['category'])&&isset($_GET['category'])) {
				$_POST['category'] = $_GET['category'];
			}
		} else {
			$id = ($_POST['id']>0?$_POST['id']:($_GET['id']>0?$_GET['id']:0));
			
			$rule_data = $class_rule->template_data(array('id'=>$id));
			
			if(!$_POST) {
				foreach($rule_data as $key=>$val) {
					$_POST[$key] = $val;	
				}
			}
			
			$zulu->nav->breadcrumb['Edit Template'] = array();	
			$zulu->nav->title = "Edit Template";
		}
		
		//Form Submit
		if($_POST['action']=='edit') {
			$form_edit->valid = true;
			
			if($form_edit->valid) {
				$data['category'] = ($_POST['category_new']!=NULL?$_POST['category_new']:$_POST['category']);
				$data['content'] = addslashes($_POST['content']);
				$data['title'] = addslashes($_POST['title']);
				$data['subject'] = addslashes($_POST['subject']);
								
				$data = $class_rule->template_edit($id,$data);
				
				if($data['success']) {
					$zulu->notification_set("Template ".(!$new?"updated":"created")." successfully.",1);
					header("Location: ".$zulu->link_page(PAGE_file,array("query"=>array('Action'=>'template_variation','category'=>$_POST['category']))));
					exit;
				} else {
					$zulu->notification_set("A database error occurred.",2);
				}
			}
		}
	}
	if(PAGE_action=='sequence_template_edit') { //edit page
		
		function edit_seq_bt($id) {
			global $zulu;
			return "
				<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'sequence_template_edit')))."\" class=\"btn btn-primary btn-xs\" ><i class=\"fas fa-edit\"></i> Edit</a> 
				<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'sequence_delete')))."\" class=\"btn btn-danger confirm-delete btn-xs\" type=\"button\"><i class=\"fas fa-times\"></i> Delete</button></a> 
			";	
		}
		
		$form_edit = new form;
		$zulu->nav->breadcrumb['Sequence Template'] = array('link'=>$zulu->link_page(PAGE_file,['query'=>['Action'=>'sequence_template']]));
		
		if(PAGE_id<1) {
			$id = 0;
			$new = true;	
			$zulu->nav->breadcrumb['New Sequence Template'] = array();
			$zulu->nav->title = "New Sequence Template";
			
			if(!isset($_POST['parent_id'])&&isset($_GET['parent_id'])) {
				$_POST['parent_id'] = $_GET['parent_id'];
			}
			$parent_id = $_POST['parent_id'];
		} else {
			$id = ($_POST['id']>0?$_POST['id']:($_GET['id']>0?$_GET['id']:0));
			
			$rule_data = $class_rule->sequence_data(array('id'=>$id));
			$parent_id = $rule_data['parent_id'];
			
			if(!$_POST) {
				foreach($rule_data as $key=>$val) {
					$_POST[$key] = $val;	
				}
			}
			
			$zulu->nav->breadcrumb['Edit Sequence Template'] = array();	
			$zulu->nav->title = "Edit Sequence Template";
			
			//-- Templates
			$table_column[] = array("Order",array('class'=>array('center')));
			$table_column[] = array("Template");
			$table_column[] = array("Object");
			$table_column[] = array("Custom Text");
			$table_column[] = array("Frequency");
			$table_column[] = array("Duration");
			$table_column[] = array("Status");
			$table_column[] = array("Added");
			$table_column[] = array("Actions",array('class'=>array('right')));
			$table_row[] = array(
					"header"	=>	 true,
					"class"		=>	"",
					"content"	=>	$table_column);
	
			$data_row = $class_rule->sequence_data(['parent_id'=>PAGE_id]);
			$child_rule_count = count($data_row);
			$i = 1;
			foreach($data_row as $row) {
				
				$status = ($row['status']==1?"Active":"Inactive");
				$status_label = ($row['status']==1?NULL:"bold red");
				
				$table_row[] = array("content" => array(
					array("<span class=\"opt opt-grey\">".$i.".</span>",['class'=>['center']]),
					array($row['template']),
					array(($row['object']!=NULL?$row['object']:'<span class="opt opt-grey">None</span>')),
					array(($row['custom_text']!=""?"<span class=\"opt opt-success\">Yes</span>":"<span class=\"opt opt-grey\">No</span>")),
					array("<i class=\"fas fa-sync-alt\"></i> Every ".$row['frequency']." Days"),
					array("<i class=\"far fa-calendar\"></i> ".$row['frequency']*$row['loop_max']." Days"),
					array($status,array('class'=>array($status_label))),
					array(zulu::time_history($row['stat_add'])),
					array(edit_seq_bt($row['id']),array('class'=>array('right')))
				),"data"=>['post-id'=>$row['id']],"class"=>'variation-row');
				$i++;
				
				$total['duration'] += $row['frequency']*$row['loop_max'];
				$total['messages'] += $row['loop_max'];
			}
			$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'menu-item','tbody'=>['id'=>'sortable-rows'],'data_table'=>false,'js_table'=>false));
			$zulu->nav->title = PAGE_name;
	
			$zulu->template->jquery[] = "
				$(\"#sortable-rows\").sortable({
					update: function(event, ui) {
						var srt = [];
						$(\"#sortable-rows\").children(\"tr\").each(function( index ) {
							srt.push($(this).data('post-id'));
						});
						$.get(\"".$zulu->link_page(PAGE_file,['query'=>['Action'=>PAGE_action,'Do'=>'sort','id'=>PAGE_id]])."&Array=\" + srt,function(data) {
							console.log(data);
						});
					}	
				});
				$(\"#sortable\").disableSelection();";
		}
		if($parent_id<=0) {
			$type = 1;	
		} else {
			$type = 2;
		}
		
		//-- Do: Sort
		if($_GET['Do']=='sort') {
			$array = explode(",",$_GET['Array']);
			$i = 0;
			foreach($array as $item_id) {
				$class_rule->sequence_edit($item_id,['sort'=>$i]);
				$i++;
			}
			exit;
		}
		
		//Form Submit
		if($_POST['action']=='edit') {
			$form_edit->valid = true;
			
			if($type==2) {
				if($form_edit->validate(['frequency','status'])) {
					$form_edit->valid = false;
					$zulu->notification_set("Please specify all fields denoted *.",2);
				}
				if($form_edit->validate(['loop_max'])) {
					$form_edit->valid = false;
					$zulu->notification_set("For sequences, a max loop is required, please enter a value greater than 0.",2);
				}
				if(trim($_POST['template'])==NULL) {
					$form_edit->valid = false;
					$zulu->notification_set("Please select a template.",2);
				}
			} else {
				if($form_edit->validate(['title'])) {
					$form_edit->valid = false;
					$zulu->notification_set("Please enter a sequence name.",2);
				}	
			}
			
			if($form_edit->valid) {
				
				if($type==2) { 
					$data['object'] = $db->escape_string($_POST['object']);
					$data['frequency'] = (int)$_POST['frequency'];
					$data['template'] = $db->escape_string($_POST['template']);
					$data['status'] = $db->escape_string($_POST['status']);
					$data['sequential'] = $db->escape_string($_POST['sequential']);
					$data['sort'] = $db->escape_string($_POST['sort']);
					$data['loop_max'] = $db->escape_string($_POST['loop_max']);
					$data['parent_id'] = $parent_id;
				} else {
					$data['title'] = $db->escape_string($_POST['title']);	
				}
						
				$data = $class_rule->sequence_edit($id,$data);
				if($data['success']) {
					$zulu->notification_set("Sequence template ".(!$new?"updated":"created")." successfully.",1);
					if($parent_id>0) {
						header("Location: ".$zulu->link_page(PAGE_file,['query'=>['Action'=>'sequence_template_edit','id'=>$parent_id]]));
					} else {
						header("Location: ".$zulu->link_page(PAGE_file,['query'=>['Action'=>'sequence_template']]));
					}
					exit;
				} else {
					$zulu->notification_set("A database error occurred.",2);
				}
			}
		}
		
		$zulu->template->jquery[] = "
			$(document).ready(function() {
				$('.input-sequential').trigger('change');
			});
			$('.input-sequential').change(function() {
				if($(this).val()==1) {
					$(\".lbl-loop\").html(' Sequence');
				} else {
					$(\".lbl-loop\").html('');
				}
				return false;
			});
		";
		
		/*
		$zulu->template->jquery[] = "
			$(document).ready(function() {
				$('.input-type').trigger('change');
			});
			$('.input-type').change(function() {
				if($(this).val()==2) {
					$('.type-standard-only').hide();
					$('.type-master-only').show();
				} else {
					$('.type-standard-only').show();
					$('.type-master-only').hide();
				}
			});";*/
	}
	if(PAGE_action=='signature_edit') { //edit page
		
		//REDIR - New signature
		header("Location: ".$zulu->link_page('user',['query'=>['Action'=>'edit','id'=>$class_user->authorised->id]]));
		exit;
		
		$form_edit = new form;
			
			$rule_data = $class_rule->signature_data();
			
			if(!$_POST) {
				foreach($rule_data as $key=>$val) {
					$_POST[$key] = $val;	
				}
			}
			
			$zulu->nav->breadcrumb['Edit Signature'] = array();	
			$zulu->nav->title = "Edit Signature";
		
		//Form Submit
		if($_POST['action']=='edit') {
			$form_edit->valid = true;
			
			if($form_edit->valid) {
				$data['content'] = addslashes($_POST['content']);
								
				$data = $class_rule->signature_edit($id,$data);
				
				if($data['success']) {
					$zulu->notification_set("Signature ".(!$new?"updated":"created")." successfully.",1);
					header("Location: ".$zulu->link_page(PAGE_file,array("query"=>array('Action'=>'signature_edit'))));
					exit;
				} else {
					$zulu->notification_set("A database error occurred.",2);
				}
			}
		}
	}
}