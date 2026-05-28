<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- DEFINITIONS
define(PAGE_file,'sale');
define(PAGE_name,'Sales');

$zulu->nav->breadcrumb['Sales'] = array("link"=>$zulu->link_page('sale'));

//-- Include JS UI on all pages
$zulu->template->css_file[] = "https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css";
$zulu->template->js_file[] = "//code.jquery.com/ui/1.11.4/jquery-ui.js";

$zulu->template->js_file[] = TPL_rel."assets/sale.js";
$zulu->template->js_file[] = TPL_rel."assets/smart.find.js";

//-- Comment Box
if($_GET['Toggle']!='email') {
	$zulu->template->jquery[] = "$(\"#box-comment\").hide();";
}

//-- Comment box data
if($_SESSION['zl_form']['comment_error']) {
	$_SESSION['zl_form']['comment_error'] = false;
	$comment_nfc = true;
} else {
	$zulu->template->jquery[] = "$(\"#box-comment\").hide();";
}
if($_GET['id']>0&&in_array(PAGE_action,['edit','print','pay'])) {
	$zulu->template->comment = true;
	$cbox_sale_data = $class_sale->sale_data(['id'=>PAGE_id]);
	$cbox_user_data = $class_client->client_data(['id'=>$cbox_sale_data['client_id']]);
}
if($_POST['method']=='comment') {
	$config = array();

	if(trim($_POST['message'])!=NULL) {
		$config['message'] = $_POST['message'];
	}
	if(trim($_POST['email'])!=NULL) {
		$config['name'] = $_POST['name'];
		$config['email'] = $_POST['email'];

		$class_sale->sale_receipt_mail(PAGE_id,$config);

		$zulu->notification_set("Copy of sale was emailed successfully.",1,['tag'=>'sale_comment']);
		header("Location: ".$zulu->link_page('sale',array('query'=>array('id'=>PAGE_id,'Action'=>'edit','Method'=>'View'))));
	} else {
		$zulu->notification_set("Please specify an email address to send the sale to.",2,['tag'=>'sale_comment']);
		header("Location: ".$zulu->link_page('sale',array('query'=>array('id'=>PAGE_id,'Action'=>'edit','Method'=>'View','Toggle'=>'email'))));
	}
	exit;
}

//-- AUTHORISED?
if(!in_array(PAGE_action,array('print'))) {
	$class_user->user_authorised_check();
	$class_user->authorised->opt_sale = true;
}
if($class_user->authorised->id<=0) {
	$class_user->user_public();
	$class_user->authorised->opt_sale = true;
}
if(!$class_user->authorised->opt_sale) {
	$zulu->notification_set("Sorry, you are not authorised to use the ".PAGE_name." area.",2);
	header("Location: ".$zulu->link_page("index"));exit;
}

$zulu->template->head = "";
$zulu->template->body = "";

$zulu->template->css_file[] = "//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css";
$zulu->template->js_file[] = "//code.jquery.com/ui/1.11.4/jquery-ui.js";
$zulu->template->js_code[] = "
	$(document).ready(function(){
		$(\"input[name='date'],.date\").datepicker({ dateFormat: \"dd/mm/yy\" });
	});
	";

if(PAGE_action==NULL) {	//grid page

	//-- JS
	$zulu->template->css_file[] = "//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css";
	$zulu->template->js_file[] = "//code.jquery.com/ui/1.11.4/jquery-ui.js";
	$zulu->template->js_code[] = "
		$(document).ready(function(){
			$('.date-picker').datepicker({
				dateFormat: \"dd/mm/yy\",
				changeMonth: true,
				changeYear: true,
				yearRange: \"1900:".date('Y')."\"
			});
			$(\"input[name='From']\").change(function() {
				document.location.href = '".$zulu->link_page(PAGE_file,array('self'=>true,'filter'=>['date_from']))."&date_from='+$(this).val();
			});
			$(\"input[name='To']\").change(function() {
				document.location.href = '".$zulu->link_page(PAGE_file,array('self'=>true,'filter'=>['date_to']))."&date_to='+$(this).val();
			});
		});
	";

	//-- Process
	if($_POST&&$_POST['execute']!=NULL) {
		if($_POST['execute']=='delete') {
			foreach($_POST['action'] as $id=>$val) {
				if(!$checkret = $class_sale->sale_delete($id)) {
					$error_log[] = "Failed to delete sale ID #".$id;
				} else {

				}
			}
			$zulu->notification_set("Sales were removed successfully.",1);
			header("Location: ".$zulu->link_page('sale'));
			exit;
		}
		if($_POST['execute']=='export_xero_publish') {
			$_POST['execute'] = 'export_xero';
			$extra_config = ['Status'=>'AUTHORISED'];
		}
		if($_POST['execute']=='mail_mark') {
			foreach($_POST['action'] as $id=>$val) {
				$zulu->meta_update('sale',$id,'xero_sent',1);
			}
			$zulu->notification_set("Sales were marked sent successfully.",1);
			header("Location: ".$zulu->link_page('sale',['self'=>true]));
			exit;
		}
		if($_POST['execute']=='mail') {
			foreach($_POST['action'] as $id=>$val) {
				$output = $class_sale->sale_receipt_mail($id,['xero'=>true]);
				$log[] = $class_sale->output;
			}
			$zulu->notification_set("Sales were sent successfully.<br><br>".implode("<br>",$log),1);
			header("Location: ".$zulu->link_page('sale',['self'=>true]));
			exit;
		}
		if($_POST['execute']=='export_xero') {
			$invoice_id = array();
			foreach($_POST['action'] as $id=>$val) {
				$invoice_id[] = $id;
			}
			$xml_invoices = $class_xero->invoice_build($invoice_id,$extra_config);
			$xml = $xml_invoices['xml'];
			$invoice_row = $xml_invoices['count'];

			 if(count($invoice_row)>0) {
				$response = $class_xero->invoice_run($xml,$xml_invoices['sale_id_array']);
				$zulu->notification_set($response['message']."<br><br>LOG:<BR>".implode("<br>",$xml_invoices['log']),($response['success']?1:'0'));
			} else {
				$zulu->notification_set("No data to export.",2);
			}
			header("Location: ".$zulu->link_page('sale',['self'=>true]));
			exit;
		}
	}

//	//-- all sales temp
//	$sale_all = $zulu->table_data('sale',0,['field'=>['id'],'sort'=>'id DESC']);
//	foreach($sale_all as $row) {
//		$mv = $zulu->meta_value('sale',$row['id'],'stat_paid_balance');
//		if($mv['value']==NULL||$mv['value']==0) {
//			echo 'd';
//			$class_sale->sale_gen_info($row['id']);
//		} else {
//			echo 's';}
//	}exit;

	//-- Page content
	$zulu->config->select_all = true;
	function edit_bt($id,$data) {
		global $zulu,$class_user,$class_product;
		$product_data = $class_product->product_data(array("id"=>$id));
		return "
			".($class_user->has_perm('sale_view')?"<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'print')))."\" title=\"Print\" target='_blank' class=\"btn btn-default btn-xs\"><i class=\"fas fa-print\"></i> Print</a>
			<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'edit','Method'=>'View')))."\" title=\"View\" class=\"btn btn-default btn-xs\" ><i class=\"far fa-eye\"></i> View</a>":NULL)."
			".($class_user->has_perm('sale_edit')?"<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'edit')))."\" title=\"Edit\" class=\"btn btn-primary btn-xs\"><i class=\"fas fa-edit\"></i> Edit</a>":NULL)."
			".($class_user->has_perm('sale_pay')?"<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'pay')))."\" title=\"Payment transactions summary.\" class=\"btn btn-success btn-xs\" ><i class=\"far fa-money-bill\"></i> Payments</a>":NULL)."
			".($class_user->has_perm('sale_delete')?"<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'delete')))."\" title=\"Delete\" class=\"btn btn-danger btn-xs confirm-delete\" ><i class=\"fas fa-times\"></i></a>":NULL)."
		";
	}

	//Filter
	if($_GET['View']) {
		$filter['status_exclude'][] = 2;
		$filter['status_exclude'][] = '0';
		switch($_GET['View']) {
			case 'paid':
				$stat_filter[] = 'paid';
				$filter['paid'] = true;
			break;
			case 'pending':
				$stat_filter[] = 'pending';
				$filter['paid'] = false;
			break;
			case 'parked':
				$filter['status'] = '0';
				unset($filter['status_exclude']);
			break;
			case 'void':
				$filter['status'] = 2;
				unset($filter['status_exclude']);
			break;
			case 'xero_no':
				$filter_title[] = "not in Xero";
				$stat_filter[] = 'pending';
				$filter['xero'] = false;
			break;
			case 'sent_no':
				$filter_title[] = "not sent";
				$stat_filter[] = 'pending';
				$filter['sent'] = false;
			break;
		}
	} else {
		$filter['status_exclude'][] = 2;
		$filter['status_exclude'][] = '0';
	}

	if($_GET['Search'] != NULL) {
		$filter['search'] = $db->escape_string($_GET['Search']);
	}

	//Filter - Date from
	if($_GET['date_from']!=NULL) {
		$date_from = $zulu->dateEncode($db->escape_string($_GET['date_from']));
		$filter['date_min'] = $date_from;
		$filter_title[] = "from ".$zulu->date($date_from,'d/m/Y');
	}

	//Filter - Date to
	if($_GET['date_to']!=NULL) {
		$date_to = $zulu->dateEncode($db->escape_string($_GET['date_to']));
		$filter['date_max'] = $date_to;
		$filter_title[] = "to ".$zulu->date($date_to,'d/m/Y');
	}

	//Filter - Client ID
	if($_GET['filter']['client_id']>0) {
		$client_id = $db->escape_string($_GET['filter']['client_id']);
		$filter['client_id'] = $client_id;
		$filter_title[] = "by ".$class_client->admin_link($client_id);
	}
	$filter_all = $filter;
	$filter_all['field'] = ['sale.id AS id'];

	//SQL Start
	$start = ($_GET['Pg']>1?MAX_per_page*($_GET['Pg']-1):0);
	$filter['row_start'] = $start;
	$filter['row_limit'] = MAX_per_page;
	$filter['sort'] = $zulu->table_sort_query('sale_list','id DESC');

	//Load Current Sale Data
	$sale_data = $class_sale->sale_data($filter);
	$sale_data_total = $class_sale->sale_data($filter_all);

	if(count($filter_title)>0) {
		$zulu->nav->breadcrumb['Sales '.implode(" ",$filter_title)] = array();
		$zulu->nav->title = 'Sales '.implode(" ",$filter_title);
	} else {
		$zulu->nav->breadcrumb['All Sales'] = array();
		$zulu->nav->title = "All Sales";
	}
	$form_edit = new form;
	$zulu->vars->sale_count = count($sale_data_total);

	//Load Products / Categories
	$table_column[] = array($form_edit->input_html("checkbox","selectall",1,array("class"=>['toggle-input'])),array('class'=>array('')));
	$table_column[] = array("Status",array('class'=>array('status-box center')));
	$table_column[] = array("ID",array('class'=>array('id center'),'sort'=>['db_column'=>"id"]));
	$table_column[] = array("Customer",array('class'=>array('center'),'sort'=>['db_column'=>"name"]));
	$table_column[] = array("Total",array('class'=>array('center')));
	$table_column[] = array("Balance",array('class'=>array('center')));
	$table_column[] = array("Created",array('class'=>array('center'),'sort'=>['db_column'=>"stat_add"]));
	$table_column[] = array("Actions",array('class'=>array('right')));
	$table_row[] = array(
			"header"	=>	 true,
			"class"		=>	"",
			"content"	=>	$table_column);

	foreach($sale_data as $row) {
		$capsule = [];
		$status_data = $class_sale->sale_status_info($row['status'],['id'=>$row['id']]);
		$sale_meta = $zulu->meta_array($class_sale->sale_meta($row['id']));
		if($xero_match) {
			if(trim($sale_meta['xero_link'])==NULL&&trim($row['reference'])!=NULL) {
				//-- check match
				$find_invoice = $class_xero->get_invoice(['Reference'=>"Sale #".$row['reference']]);
				$invoice_id = $find_invoice['invoice']['InvoiceID'];
				if(trim($invoice_id)!=NULL) {
					echo 'Found invoice<br>';
					$zulu->meta_update('sale',$row['id'],'xero_link',$invoice_id);
				}
				echo 'check match on sale '.$row['reference']." FOUND<br>";
			} else {
				echo 'check match on sale '.$row['reference']." NOT FOUND<br>";
			}
		}
		if($row['admin_id']>0&&WEB&&(MAIN_mode=='web'||$class_user->authorised->opt_website)) {
			$capsule[] = "<span class=\"opt opt-grey opt-bord text-mini\"><i class=\"fas fa-info-circle\"></i> Manual</span>";
		}
		if($sale_meta['xero_sent']!=NULL) {
			$capsule[] = "<span class=\"opt opt-success opt-bord text-mini\"><i class=\"fas fa-paper-plane\"></i> Sent</span>";
		}
		if($sale_meta['xero_link']!=NULL) {
			$capsule[] = "<span class=\"opt opt-success opt-bord text-mini\"><i class=\"fas fa-link\"></i> Xero&reg;</span>";
		}
		if($sale_meta['ship_method']!=NULL) {
			$capsule[] = "<span class=\"opt opt-warning opt-bord text-mini\"><i class=\"fas fa-truck\"></i> ".$sale_meta['ship_method']."</span>";
		}
		if($row['pay_method']!=NULL) {
			$capsule[] = "<span class=\"opt opt-primary opt-bord text-mini\"><i class=\"far fa-money-bill\"></i> ".$row['pay_method']."</span>";
		}
		$label_ttl = '<span class="'.$status_data['class'].'"><i class="fas '.$status_data['icon'].'"></i> '.$status_data['label'].'</span>';
		$link = $zulu->link_page('sale',array('query'=>array('id'=>$row['id'],'Action'=>'edit')));
		$table_row[] = array("content" => array(
			array($form_edit->input_html("checkbox","action[".$row['id']."]",1,array('checked'=>($_POST['action'][$row['id']]>0?true:false),'class'=>array('action'))),array('class'=>array('action-field'))),
			array($label_ttl,array('class'=>array('text-small'))),
			array("<a href=\"".$link."\">".$row['reference']."</a>"),
			array("<a href=\"".$link."\">".$row['name']."</a>&nbsp;&nbsp;&nbsp;".implode(" ",$capsule)),
			array("$".number_format($class_sale->sale_total($row['id']),2)),
			array("$".number_format($class_sale->sale_balance($row['id']),2).($class_sale->sale_balance($row['id'])>0?" <span class=\"opt opt-danger\"><em>Due</em></span>":NULL)),
			array($zulu->time_fancy($row['stat_add']),array('class'=>array('text-small'))),
			array(edit_bt($row['id'],$row),array('class'=>array('right')))
		));
	}

	$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'sale','js_table'=>false,'data_table'=>false,'html_id'=>'sale_list'));

	$pagination = $zulu->pagination($_GET['Pg'],['count'=>$zulu->vars->sale_count,'link'=>$zulu->link_page(PAGE_file,array('self'=>true))]);

	$zulu->nav->title = "Sale";

	//Build Tree of Links
	$class_product->category_breadcrumb($class_product->root_id);
}
if(PAGE_action=='delete') { //delete
	if($class_sale->sale_delete(PAGE_id)) {
		$zulu->notification_set("Sale was deleted.",1);
		header("Location: ".$zulu->link_page('sale'));
		exit;
	} else {
		$zulu->notification_set("Sale was not able to be deleted.",2);
	}
}
if(PAGE_action=='pay_delete') { //pay page
	$class_sale->payment_delete($_GET['Payment']);
	$zulu->notification_set("Payment successfully removed.",1);
	header("Location: ".$zulu->link_page('sale',array('query'=>array('Action'=>'pay','id'=>PAGE_id))));
	exit;
}
if(PAGE_action=='refund_delete') { //refund page
	$class_sale->refund_delete($_GET['refund_id']);
	$zulu->notification_set("Refund successfully removed.",1);
	header("Location: ".$zulu->link_page('sale',array('query'=>array('Action'=>'pay','id'=>PAGE_id))));
	exit;
}
if(PAGE_action=='pay') { //pay page
	$zulu->template->jquery[] = "
		$('#refund_date').datepicker({ dateFormat: 'dd-mm-yy' });
	";

	$form_edit = new form;

	$sale_data = $class_sale->sale_data(array('id'=>PAGE_id));
	$sale_total = $class_sale->sale_total(PAGE_id);
	$sale_paid = $class_sale->sale_total_paid(PAGE_id);
	$sale_balance = $sale_total-$sale_paid;
	$sale_meta = $zulu->meta_array($class_sale->sale_meta(PAGE_id));

    if($sale_data['client_id'] > 0) {
        $client_data = $class_client->client_data(['id'=>$sale_data['client_id']]);
        $client_meta = $zulu->meta_array($class_client->client_meta($sale_data['client_id']));
    }

	$class_sale->complete = ($class_sale->is_complete(PAGE_id)?true:false);
	$class_sale->locked = ($class_sale->is_locked(PAGE_id)?true:false);

	if($sale_data['coupon_id']>0) {
		$coupon_data = $class_sale->coupon_data(array('id'=>$sale_data['coupon_id']));
		$coupon_name = $coupon_data['code'];
	}

	//Is valid
	if($sale_data['status']<=0) {
		$zulu->notification_set("Please complete this sale before making a payment.",2);
		header("Location: ".$zulu->link_page('sale',array('query'=>array('Action'=>'edit','id'=>$sale_data['id']))));
		exit;
	}

	$paid = ($sale_balance>0?false:true);

	//For Sale logs complete check box
	$display_complete = ($sale_balance<=0?true:false);
	$transaction_options =[];
	$transaction_options[0] = "None";
	$pay_data = $class_sale->payment_data(array('sale_id'=>PAGE_id));
	$refund_data = $class_sale->refund_data(array('sale_id'=>PAGE_id));
	$pay_list = [];

	foreach($pay_data as $p) {
		$transaction_options[$p['id']] = $zulu->date($p['date'],'d/m/Y h:ia')." | ".stripslashes($p['info'])." | $".number_format($p['pay_total'],2);
		$pay_list[$p['date']] = "<tr class=\"\"><td>".$zulu->date($p['date'],'d/m/Y h:ia')."</td><td>".stripslashes($p['info'])." ".$form_edit->icon_help(stripslashes($p['reference']))."</td><td>$".number_format($p['pay_total'],2)."</td><td><a href=\"".$zulu->link_page('sale',array('query'=>array('id'=>PAGE_id,'Action'=>'pay_delete','Payment'=>$p['id'])))."\" class=\"btn btn-danger btn-xs confirm-delete\" title=\"Delete Payment\"><span class=\"fas fa-times\"></span></a></td></tr>";
		$round = ($p['info']=='Rounding'?true:false);
		if($p['info']=='Rounding'&&!$round) {
			$pay_list[$p['date'].'_rounding'] = "<tr class=\"\"><td>".$zulu->date($p['date'],'d/m/Y h:ia')."</td><td>".stripslashes($p['info'])." ".$form_edit->icon_help(stripslashes($p['reference']))."</td><td>$".number_format($p['pay_total'],2)."</td><td></td></tr>";
		}
	}
	foreach($refund_data as $refund){
		$pay_list[$refund['date']] = "<tr class=\"danger\"><td>".$zulu->date($refund['date'],'d/m/Y h:ia')."</td><td><b>Refund</b> ".$form_edit->icon_help(stripslashes($refund['reference']))."</td><td>$".number_format($refund['amount'],2)."</td><td><a href=\"".$zulu->link_page('sale',array('query'=>array('id'=>PAGE_id,'Action'=>'refund_delete','refund_id'=>$refund['id'])))."\" class=\"btn btn-danger btn-xs confirm-delete\" title=\"Delete Payment\"><span class=\"fas fa-times\"></span></a></td></tr>";
	}
	ksort($pay_list);
	if(count($pay_list)>0){
		$pay_table = "
		<table class=\"table table-hover table-bordered table-striped line-items\" name=\"transaction-list\" id=\"transaction-list\">
			<thead><tr><td>Date</td><td>Method</td><td>Amount</td><td></td></tr></thead>
			<tbody>".implode($pay_list)."</tbody>
		</table>";
	}else{
		$pay_table = "<span class=\"opt opt-grey\"><i class=\"fas fa-times\"></i> No past payments for this sale...</span>";
	}

	$_POST['date'] = ($_POST['date']!=NULL?$_POST['date']:$zulu->dateDecode(time()));

	$payment_options = array();
	if($class_sale->is_booking($sale_data['id'])) {
		$subscribe = $class_subscribe->subscribe_data(array("user_id"=>$sale_data['client_id'],"status"=>1));
		$row_META = $class_client->client_meta($sale_data['client_id']);
		if(count($subscribe)>0) {
			$credit_balance = $row_META['subscribe_credit']['value'];
			$credit_balance_raw = $row_META['subscribe_credit']['value'];
			if($credit_balance>0) {
				$payment_options['Credit'] = "Credit (+".$credit_balance.")";
			}
			$subscribed = true;
		} else {
			$subscribed = false;
		}
		$coupon_data = $class_sale->coupon_data(array('user_id'=>$sale_data['client_id'],'discount_object'=>15,'discount_type'=>'credit'));
		//print_r($coupon_data);
		$coupon_credits = 0;
		foreach($coupon_data as $coupon_temp) {
			$allowed = $class_sale->coupon_product_count_allowed($coupon_temp['id'],0,$sale_data['client_id']);
			//$redeemed = $class_sale->coupon_used_count($coupon_temp['id'],$sale_data['user_id']);
			//$temp_count = $allowed - $redeemed;
			$temp_count = $allowed;
			//echo $allowed;
			if($temp_count > 0) {
				$coupon_options[$coupon_temp['id']] = stripslashes($coupon_temp['name']).": ".$coupon_temp['code'].($temp_count>0?" (+{$temp_count})":NULL);
			}
		}
	}
	$payment_options['Credit Card'] = 'Credit Card';
	$payment_options['Eftpos'] = 'Eftpos';
	$payment_options['Bank Deposit'] = 'Bank Deposit';
	$payment_options['Cash'] = 'Cash';
	$payment_options['Voucher'] = 'Voucher';
	$payment_options['Other'] = 'Other';

	$zulu->nav->breadcrumb["Sale #".$sale_data['reference']] = array('link'=>$zulu->link_page(PAGE_file,['self'=>true,'query'=>['Action'=>'edit','Method'=>'View']]));
	$zulu->nav->breadcrumb['Payments'] = array();
	$zulu->nav->title = "Payments";

	//-- Mail Receipt
	if($_GET['Do']=='email_sale') {

		if($sale_meta['xero_link']!=NULL) {
			$zulu->meta_update('sale',$sale_data['id'],'xero_sent',1);
			$res = $class_sale->sale_receipt_mail($sale_data['id'],['xero'=>true]);
		} else {
			$res = $class_sale->sale_receipt_mail($sale_data['id']);
		}
		if($res) {
			$zulu->notification_set("Sale receipt emailed successfully to ".$sale_data['email'].".",1);
		} else {
			$zulu->notification_set("Failed to send, check email and try again.",2);
		}
		header("Location: ".$zulu->link_page('sale',array('self'=>true,'filter'=>['Do'])));
		exit;
	}

	//-- XERO Export
	$is_xero = ($sale_meta['xero_link']!=NULL?true:false);
	if($is_xero) {
		$xero_invoice = $class_xero->get_invoice($sale_meta['xero_link'],['field'=>'Status']);
		if($xero_invoice['invoice']['Status']!='DRAFT'&&$xero_invoice['invoice']['Status']!='VOIDED') {
			$xero_link = $class_xero->get_invoice_public_url($sale_meta['xero_link']);
			$xero_link = $xero_link['invoice_url'];
			$xero_stat = [
				'class'	=>	'success',
				'icon'	=>	'check',
				'label'	=>	'Xero&reg; Status: '.$xero_invoice['invoice']['Status'],
			];
		} else {
			$xero_stat = [
				'class'	=>	($xero_invoice['invoice']['Status']=='DRAFT'?'grey':'danger'),
				'icon'	=>	($xero_invoice['invoice']['Status']=='DRAFT'?'warning':'remove'),
				'label'	=>	'Xero&reg; Status: '.$xero_invoice['invoice']['Status'],
			];
		}
	}

	if($_GET['Do']=='xero_export') {
		$invoice_id[] = PAGE_id;
		if($_GET['authorised']>0) {
			$extra_config = ['Status'=>'AUTHORISED'];
		}
		$xml_invoices = $class_xero->invoice_build($invoice_id,$extra_config);
		$xml = $xml_invoices['xml'];
		$invoice_row = $xml_invoices['count'];

		 if(count($invoice_row)>0) {
			$response = $class_xero->invoice_run($xml,$xml_invoices['sale_id_array']);
			$zulu->notification_set($response['message']."<br><br>LOG:<BR>".implode("<br>",$xml_invoices['log']),($response['success']?1:'0'));
		} else {
			echo 'noexpo';
			$zulu->notification_set("No data to export.",2);
		}
		header("Location: ".$zulu->link_page('sale',['self'=>true,'filter'=>['Do']]));
		exit;
	}
	if($_GET['Do']=='xero_update') {
		$xml_invoices = $class_xero->invoice_update([PAGE_id]);
		 if($xml_invoices['success']) {
			$zulu->notification_set("Invoice update.<br><br>LOG:<BR>".implode("<br>",[$xml_invoices['message']]),($xml_invoices['success']?1:'0'));
		} else {
			$zulu->notification_set("No data to export.",2);
		}
		header("Location: ".$zulu->link_page('sale',['self'=>true,'filter'=>['Do']]));
		exit;
	}
	//-- Refund
	//print_r($class_user->authorised);exit;
	$sale_lines = $class_sale->sale_line(PAGE_id);
	$restock_rows = [];
	$sale_line_options = [];
	$sale_line_options[0] = "None";
	foreach($sale_lines as $line){
		$description = $zulu->shorten($line['description'], 40);
		$sale_line_options[$line['id']] = $line['sku']." | ".$description." | ".$line['total'];
		$stock_data = $class_product->stock_data(['is_return'=>'1', 'object'=>'sale_line', 'object_id'=>$line['id']]);
		$current_restock_quantity = 0;
		foreach($stock_data as $stock){
			$current_restock_quantity += $stock['value'];
		}
		if($line['product_id']>0) {
			$can_restock = true;
			$restock_input = $form_edit->input_html("number","restock_quantities[".$line['id']."]",$_POST['restock_quantities'][$line['id']],['placeholder'=>'0','custom'=>['min'=>'0']]);
		} else {
			$can_restock = false;
			$restock_input = "<i class=\"opt opt-grey\">Not a inventory product.</i>";
		}
		$restock_rows[] = "<tr class=\"\"><td>".$line['sku']."</td><td>".$line['description']."</td><td>".number_format($line['quantity'],2)."</td><td>".number_format($current_restock_quantity,2)."</td><td>".$restock_input."</td></tr>";
	}
	$restock_table = "
		<table class=\"table table-hover table-bordered table-striped line-items\" id=\"restock-list\">
			<thead><tr><td>SKU</td><td>Description</td><td>Qty.</td><td>Restocked Qty.</td><td>Restock Amount</td></tr></thead>
			<tbody>".implode($restock_rows)."</tbody>
		</table>";

	if($_POST['action'] == 'refund_payment'){
		$form_edit->valid = true;
		if(!is_numeric($_POST['refund_amount'])) {
			$zulu->notification_set("Please enter a numeric amount.",2);
			$form_edit->valid = false;
		}
		if($_POST['refund_amount']<=0) {
			$zulu->notification_set("Please enter an amount greater than 0.00",2);
			$form_edit->valid = false;
		}

		if($form_edit->valid){
			$amount 			= $db->escape_string($_POST['refund_amount']);
			$date 				= $db->escape_string(strtotime($_POST['refund_date']));
			$reference 			= $db->escape_string($_POST['refund_reference']);
			$sale_line_id 		= $db->escape_string($_POST['refund_line_id']);
			$sale_payment_id 	= $db->escape_string($_POST['refund_pay_id']);
			$result = $class_sale->process_refund($amount, $date, $reference, $sale_payment_id, $sale_line_id, PAGE_id);

			$zulu->notification_set($result['msg'], ($result['success']?1:2));
			header("Location: ".$zulu->link_page(PAGE_file,array('self'=>true)));
            exit;
		}
	}
	if($_POST['action'] == 'restock'){
		$form_edit->valid = true;
		foreach($_POST['restock_quantities'] as $sale_line_id=>$restock_quantity){
			if($restock_quantity == 0){
				continue;
			}
			if(is_numeric($restock_quantity)){
				if($restock_quantity>0){
					$stock_data = $class_product->stock_data(['is_return'=>'1', 'object'=>'sale_line', 'object_id'=>$line['id']]);
					$sale_line_data = $class_sale->sale_line_data(['id'=>$db->escape_string($sale_line_id)]);
					$current_restock_quantity = 0;
					foreach($stock_data as $stock){
						$current_restock_quantity += $stock['value'];
					}
					$current_line_quantity = $sale_line_data['quantity'] - $current_restock_quantity;
					//print_r($current_line_quantity);exit;
					if($current_line_quantity == 0){
						$zulu->notification_set("The sale line for <b>".$sale_line_data['description']."</b> has already been fully restocked.",2);
						$form_edit->valid = false;
						break;
					}elseif($restock_quantity > $current_line_quantity){
						$zulu->notification_set("The restock quantity you entered for <b>".$sale_line_data['description']."</b> is greater than the line quantity remaining. ",2);
						$form_edit->valid = false;
						break;
					}
				}else{
					$zulu->notification_set("Please only enter positive numeric values.",2);
					$form_edit->valid = false;
					break;
				}
			}else{
				$zulu->notification_set("Please only enter numeric values.",2);
				$form_edit->valid = false;
				break;
			}

			if($form_edit->valid){
				foreach($_POST['restock_quantities'] as $sale_line_id=>$restock_quantity){
					if(is_numeric($restock_quantity) && $restock_quantity>0 && $sale_line_id>0){
						$sale_line_data = $class_sale->sale_line_data(['id'=>$sale_line_id]);
						$stock_config_array = [
							'value'		=>	$db->escape_string($restock_quantity),
							'object'	=>	'sale_line',
							'object_id'	=>	$db->escape_string($sale_line_id),
							'note'		=>	"Restock for Sale #".$db->escape_string($_POST['sale_reference']),
							'is_return'	=>	1
						];
						$class_product->stock_adjust($sale_line_data['product_id'], $stock_config_array);
					}
				}
				$zulu->notification_set("Stock was successfully adjusted.",1);
				header("Location: ".$zulu->link_page(PAGE_file,['self'=>true]));
				exit;
			}

		}
	}
	//-- Add coupon
	if($_POST['action'] == 'coupon_apply') {
		$form_edit->valid = true;
		if($_POST['coupon'] != NULL || $_POST['coupon_id'] > 0) {
			$lines = $class_sale->sale_line(PAGE_id);
			foreach($lines as $line) {
				$check_product[$line['product_id']]['quan'] += $line['quantity'];
				$check_product[$line['product_id']]['object'] = $line['object'];
			}
			$coupon = $db->escape_string($_POST['coupon']);
			if($_POST['coupon_id']>0)
				$coupon_data = $class_sale->coupon_data(array('id'=>$_POST['coupon_id']));
			else
				$coupon_data = $class_sale->coupon_data(array('code'=>$coupon));

			if(count($coupon_data) > 0) {
				$check = $class_sale->coupon_check("",$coupon_data['id'],$sale_data['client_id'],array('object_check'=>$check_product));

				if($check['success']) {

					$class_sale->coupon_apply(PAGE_id, $coupon_data['id']);

					$zulu->notification_set("This coupon is vaild and has been applied to the sale.",1);
					$class_sale->complete(PAGE_id);

					header("Location: ".$zulu->link_page('sale',array('query'=>array('id'=>PAGE_id,'Action'=>'pay'))));
					exit;
				} else {
					unset($_POST['coupon']);
					$zulu->notification_set($check['err'],$check['err_class']);
					$form_edit->valid = false;
				}
			} else {
				unset($_POST['coupon']);
				$zulu->notification_set("This coupon is invalid.",2);
				$form_edit->valid = false;
			}
	  	}
	} elseif($_POST['action'] == 'pay_dps') {
		$form_edit->valid = true;
		if($form_edit->validate(array('amount'))) {
			$zulu->notification_set("Please specify an amount to procced.",2);
			$form_edit->valid = false;
		}
		if($form_edit->valid) {
			$balance = $class_sale->sale_balance($sale_data['id']);
			$amount = ($_POST['amount']>$balance?$balance:$_POST['amount'])*1;
			$class_paystation->generate(array('amount'=>$amount,'sale'=>PAGE_id,'admin_id'=>$class_user->authorised->id,'return_url'=>MAIN_url.'admin/index.php?Page=sale&Action=pay&Do=PaystationProcess&id='.PAGE_id));
		}
	} elseif($_POST['action'] == 'pay_other') {
		$form_edit->valid = true;
		if($form_edit->validate(array('amount','date','method'))) {
			$zulu->notification_set("Please specify an amount, date and method to procced.",2);
			$form_edit->valid = false;
		}

		if($_POST['amount']<=0||(int)$_POST['amount']>(int)$class_sale->sale_balance(PAGE_id)) {
			$zulu->notification_set("The amount you entered is invalid.",2);
			$form_edit->valid = false;
		}
		if($_POST['method']=='credit' && $credit_balance_raw>$_POST['amount']) {
			$zulu->notification_set("The customer only has ".$credit_balance_raw." credits to use.",2);
			$form_edit->valid = false;
		}
		if($form_edit->valid) {
			$amount = $_POST['amount'];
			$info = $_POST['method'];
			$reference = addslashes($_POST['reference']);
			$success = 1;
			$ip = $_SERVER['REMOTE_ADDR'];

			$pc['pay_total'] = $amount;
			$pc['sale_id'] = PAGE_id;
			$pc['client_id'] = $client_id;
			$pc['user_id'] = $class_user->authorised->id;
			$pc['admin_id'] = $class_user->authorised->child_id;
			$pc['info'] = $info;
			$pc['method'] = $method;
			$pc['method_id'] = $method_id;
			$pc['method_data'] = $method_data;
			$pc['valid'] = $success;

			if($_POST['method']=='credit') {
				$subscribe_data = $class_subscribe->subscribe_data(array("user_id"=>$client_id,"status"=>1,"first"=>true));
				$data_line_id = 0;
				$data_line = $class_sale->sale_line(PAGE_id);
				$data_line_id = array();
				foreach($data_line as $data_row) {
					$prod_data = zulu::table_data("product",0,array("where"=>array("id = '".$data_row['product_id']."'")));
					if($prod_data[0]['template']=='book') {
						$data_line_id[$data_row['id']] = $data_row['price'];
					}
				}
				if(count($data_line_id)>0) {
					foreach($data_line_id as $line_id=>$line_price) {
						$newlog = array(
							'sub_id'		=>	$subscribe_data['id'],
							'sale_line_id'	=>	$line_id,
							'data'			=> array(),
							'value'			=> -$amount
						);
						$add_log = $class_subscribe->subscribe_log_new($newlog);
						if($add_log['success']) {
							$log_id = $add_log['id'];

							$class_sale->payment_create(array(
								"sale_id"=>PAGE_id,
								"user_id"=>$client_id,
								"pay_total"=>$line_price,
								"info"=>"Membership Credit (x{$amount})",
								"method"=>"credit",
								"valid"=>true
							));
						}
					}
				}
			} else {
				$class_sale->payment_create($pc);
			}

			$class_sale->complete(PAGE_id);

			$zulu->notification_set("Your payment was successfully added.",1);
			header("Location: ".$zulu->link_page('sale',array('query'=>array('id'=>PAGE_id,'Action'=>'pay'))));
			exit;
		}
	}

	//-- Sale Logs
	$table_column[] = array("Information",array('class'=>array('')));
	$table_column[] = array("Added",array('class'=>array('')));
	$table_column[] = array("Actions",array('class'=>array('text-right')));
	$table_row[] = array(
			"header"	=>	 true,
			"class"		=>	"",
			"content"	=>	$table_column);

	$log_item = $zulu->log_data(['object'=>'sale','object_id'=>PAGE_id]);
	foreach($log_item as $row) {
		$table_row[] = array("content" => array(
			array("<p class=\"no-margin\"><b>".stripslashes($row['title'])."</b>".(trim($row['data'])!=NULL?"<br>".stripslashes($row['data']):NULL)."</p>"),
			array($zulu->time_history($row['stat_add'])),
			array("<a href=\"".$zulu->link_page(PAGE_file,['self'=>true,'query'=>['Do'=>'log_delete','log_id'=>$row['id']]])."\" class=\"confirm-delete btn btn-xs btn-danger\"><span class=\"fas fa-times\"></span></a>",['class'=>['text-right']])
		));

	}
	if(count($table_row)>1) {
		$zulu->template->body->table_log = $zulu->table_render($table_row,0,array('class'=>'basket','data_table'=>false,'js_table'=>false));
	} else {
		$zulu->template->body->table_log = "<span class=\"opt opt-grey\"><i class=\"fas fa-times\"></i> No updates on this sale yet...</span>";
	}

	//-- Form JS
	$zulu->template->jquery[] = "
	$(\".input-default\").change(function() {
		var this_val = $(this).val();
		$('.input-subject').val(this_val);
		return false;
	});
	";

	if($_GET['Do']=='log_delete') {
		$zulu->log_delete($db->escape_string($_GET['log_id']));
		$zulu->notification_set("Status was deleted.");
		header("Location: ".$zulu->link_page('sale',array('self'=>true,'filter'=>['Do'])));
		exit;
	}
	if($_POST['action']=='new_log') {
		$form_edit->valid = true;

		if($form_edit->validate(array('title'))) {
			$zulu->notification_set("Please enter a title for this update.",2);
			$form_edit->valid = false;
		}
		$post = [
			'title'	=>	$db->escape_string($_POST['title']),
			'data'	=>	$db->escape_string(str_replace(chr(13),"<br>",$_POST['data'])),
			'object'=>	'sale',
			'object_id'=>	PAGE_id,
		];

		if($form_edit->valid) {
			if($return = $zulu->log_edit(0,$post)) {
				$zulu->notification_set("Sale log update created successfully.",1);
				if($_POST['sale_complete'] == 1){
					$zulu->meta_update('sale', PAGE_id, 'complete',1);
				}
				if($_POST['status_alert']>0) {
					$mess = "<p>Hello ".stripslashes($sale_meta['name_first']).",<br><br>Your order has been updated.</p><h3>".$_POST['title']."</h3>".(trim($_POST['data'])!=NULL?str_replace(chr(13),"<br>",$_POST['data']):NULL)."<hr><p>Please contact us if you have any questions regarding your order.</p>";
					$subject = "New Order Update: ".$_POST['title'];
					if($sale_data['email']!=NULL) {
						$zulu->mail_send($sale_data['email'],$subject,$mess,'',NULL,['user_id'=>$class_user->authorised->id,'client'=>true]);
					}
				}

				header("Location: ".$zulu->link_page('sale',array('self'=>true)));
				exit;
			} else {
				$zulu->notification_set("A database error occurred.",2);
			}
		}
	}
}
if(PAGE_action=='edit') { //edit page

	$form_edit = new form;
	$class_user->has_perm_redir('sale_edit');

	//-- load page
	if(PAGE_id<1) {
		$id = 0;
		$new = true;

		//Check type
		$zulu->template->js_code[] = "var tax_rate = ".($class_setting->data['tax_disable']>0?0:$class_setting->data['tax_rate']).";\nvar tax_excl = ".($class_setting->data['tax_method']<=0?'true':'false').";";

		$tax_label = $class_setting->data['tax_label'];
		$tax_label_meth = $class_setting->data['tax_method_label'];

		$zulu->nav->breadcrumb['New Sale'] = array();
		$zulu->nav->title = "New Sale";

		//-- Sale Line ID?
		if($_GET['sale_line_id']>0) {
			$id = $db->escape_string($_GET['sale_line_id']);
			$sale_line_data = $class_sale->sale_line_data(['id'=>$id]);
			header("Location: ".$zulu->link_page(PAGE_file,['query'=>['id'=>$sale_line_data['sale_id']],'self'=>true,'filter'=>['sale_line_id']]));
			exit;
		}
	} else {
		$id = ($_POST['id']>0?$_POST['id']:($_GET['id']>0?$_GET['id']:0));

		$sale_data = $class_sale->sale_data(array('id'=>$id));
		$sale_meta = $class_sale->sale_meta($id);
		$sale_line = $class_sale->sale_line($id);
        if($sale_data['client_id'] > 0) {
            $user_data = $class_client->client_data(array('id'=>$sale_data['client_id']));
        }

		if($sale_data['user_id']!=$class_user->authorised->id) {
			$zulu->notification_set("You are not allowed to view this sale.",2);
			header("Location: ".$zulu->link_page('sale'));
			exit;
		}

		$zulu->template->js_code[] = "var tax_rate = ".($sale_data['tax_disable']>0?0:$sale_data['tax_rate']).";\nvar tax_excl = ".($sale_data['tax_method']<=0?'true':'false').";";
		$tax_label = $class_setting->data['tax_label'];
		$tax_label_meth = ($sale_data['tax_method']<=0?"Excl":"Incl");

		if($sale_data['coupon_id']>0) {
			$coupon_data = $class_sale->coupon_data(array('id'=>$sale_data['coupon_id']));
			$coupon_code = $coupon_data['code'];
		}

		if($_GET['Method']=="View"||$class_sale->payment_has($id,array('nil_override'=>true))) {
			$status_data = $class_sale->sale_status_info($sale_data['status'],array('id'=>$id));
			$label_ttl = '<span class="'.$status_data['class'].'"><span class="fas '.$status_data['icon'].'"></span> '.$status_data['label'].'</span>';
			$class_user->has_perm_redir('sale_view');

			//**SHIPPING
			$show_shipping = ($sale_meta['ship_address']['value']!=NULL?true:false);
			$ship_address = stripslashes($sale_meta['ship_to']['value'])."<br>";
			$ship_address .= $sale_meta['ship_address']['value'];
			if($sale_meta['ship_suburb']['value']!="") {
				$ship_address .= "<br>".$sale_meta['ship_suburb']['value'];
			}
			if($sale_meta['ship_city']['value']!="") {
				$ship_address .= "<br>".$sale_meta['ship_city']['value'];
			}
			if($sale_meta['ship_post']['value']!="") {
				$ship_address .= " ".$sale_meta['ship_post']['value'];
			}
			if($sale_meta['ship_country']['value']!="") {
				$ship_address .= "<br>".$sale_meta['ship_country']['value'];
			}
			//**BILLING
			$bill_address = stripslashes($sale_meta['bill_to']['value'])."<br>";
			$bill_address .= $sale_meta['bill_address']['value'];
			if($sale_meta['bill_suburb']['value']!="") {
				$bill_address .= "<br>".$sale_meta['bill_suburb']['value'];
			}
			if($sale_meta['bill_city']['value']!="") {
				$bill_address .= "<br>".$sale_meta['bill_city']['value'];
			}
			if($sale_meta['bill_post']['value']!="") {
				$bill_address .= " ".$sale_meta['bill_post']['value'];
			}
			if($sale_meta['bill_country']['value']!="") {
				$bill_address .= "<br>".$sale_meta['bill_country']['value'];
			}
			$ship_notes = $sale_meta['ship_notes']['value'];
			$recipient_name = $sale_meta['rec_name']['value'];
			$ship_to = $sale_meta['ship_to']['value'];

			$order_discount = $class_sale->sale_total_discount($id);

			$zulu->nav->breadcrumb['Sale #'.$sale_data['reference'].' '.$label_ttl] = array();
			$zulu->nav->title = "View Sale";
			$class_sale->view = true;
		} else {
			$status_data = $class_sale->sale_status_info($sale_data['status'],array('id'=>$id));
			$label_ttl = '<span class="'.$status_data['class'].'"><span class="fas '.$status_data['icon'].'"></span> '.$status_data['label'].'</span>';

			$zulu->nav->breadcrumb['Edit Sale #'.$sale_data['reference'].' '.$label_ttl] = array();
			$zulu->nav->title = "Edit Sale";
		}
		$class_sale->complete = ($class_sale->is_complete(PAGE_id)?true:false);
		$class_sale->locked = ($class_sale->is_locked(PAGE_id)?true:false);

		if(!$_POST) {
			foreach($sale_data as $key=>$val) {
				$_POST[$key] = $val;
			}
			foreach($sale_meta as $val) {
				$_POST[$val['field']] = $val['value'];
			}
			$i = 1;
			foreach($sale_line as $sale_line_data) {
				$sale_line_sub = $class_sale->sale_line_sub($sale_line_data);
				if(trim($sale_line_sub)!=NULL) {
					$sale_line_sub = "<br><small class=\"opt opt-grey\">".$sale_line_sub."</small>";
				}

				$_POST['line'][$i]['line_id'] = $sale_line_data['id'];
				$_POST['line'][$i]['sku'] = $sale_line_data['sku'];
				if($class_sale->view) {
					$_POST['line'][$i]['description'] = $sale_line_data['description'].$sale_line_sub;
				} else {
					$_POST['line'][$i]['description'] = $sale_line_data['description'];
				}
				$_POST['line'][$i]['qty'] = $sale_line_data['quantity'];
				$_POST['line'][$i]['price'] = $sale_line_data['price']+$sale_line_data['extra'];
				$_POST['line'][$i]['disc'] = $sale_line_data['discount'];
				$_POST['line'][$i]['extra'] = $sale_line_data['extra'];
				$_POST['line'][$i]['link'] = $zulu->object_link($sale_line_data['object'],$sale_line_data['object_id']);
				$i++;
			}
			$_POST['date'] = zulu::dateDecode($_POST['date']);
			$_POST['date_due'] = zulu::dateDecode($_POST['date_due']);
		}
		$coupon_data = $class_sale->coupon_data(array('id'=>$sale_data['coupon_id']));
	}

	//Form Submit
	if($_POST['action']=='edit') {
		unset($sale_data);

		if(isset($_POST['submit_delete'])) {
			if($class_sale->sale_delete(PAGE_id)) {
				$zulu->notification_set("Sale was deleted.",1);
				header("Location: ".$zulu->link_page('sale'));
				exit;
			} else {
				$zulu->notification_set("A database error occurred.",2);
			}
		}

		$form_edit->valid = true;
		/*if($_POST['client_id']<1) {
			$zulu->notification_set("Please select a valid customer or <a href=\"".$zulu->link_page('customer',array('query'=>array('Action'=>'edit')))."\" target=\"_blank\">add a new customer</a>.",2);
			$form_edit->valid = false;
		}*/

		if($form_edit->validate(array('name','date','date_due'))) {
			$zulu->notification_set("Please specify a name, date and due date.",2);
			$form_edit->valid = false;
		}
		if($new&&$form_edit->validate(array('reference'))) {
			$zulu->notification_set("Please enter a sale #, or use the automatically generated one.",2);
			$form_edit->valid = false;
		}

		if($_POST['client_id']<=0) {
			$_POST['client_id'] = $class_client->client_find($_POST['name']);
		}

		$line_item = $_POST['line'];
		if(count($line_item)>0) {
			foreach($line_item as $row=>$line) {
				$sale_data['line'][] = array(
					'line_id' => $line['line_id'],
					'sku' => $line['sku'],
					'description' => $line['description'],
					'price' => $line['price'],
					'quantity' => $line['qty'],
					'discount' => ($line['price'] * ($line['disc'] / 100)) * $line['qty'],
					'extra' => $line['extra'],
				);
			}
		}

		$sale_data['name'] = $db->escape_string($_POST['name']);
		$sale_data['client_id'] = $_POST['client_id'];
		$sale_data['reference'] = $_POST['reference'];
		$sale_data['date'] = $_POST['date'];
		$sale_data['date_due'] = $_POST['date_due'];

		if($new) {
			$sale_data['admin_id'] = $class_user->authorised->child_id;
		}

		if($form_edit->valid) {
			$action = (isset($_POST['submit_park'])?false:true);
			if($return = $class_sale->sale_edit(PAGE_id,$sale_data,array('complete'=>$action))) {
				$id = (PAGE_id>0?PAGE_id:$return['id']);
				//$zulu->meta_update("product",$id,"role",$_POST['role']);
				$zulu->notification_set("Sale ".($id>0?"updated":"created")." successfully.",1);
				if($class_sale->sale_balance($id)>0&&$action) {
					header("Location: ".$zulu->link_page('sale',array('query'=>array('Action'=>'pay','id'=>$id))));
				} else {
					header("Location: ".$zulu->link_page('sale',array('query'=>array('Action'=>'edit','Method'=>'View','id'=>$id))));
				}
				exit;
			} else {
				$zulu->notification_set("A database error occurred.",2);
			}
		}
	}
}
if(PAGE_action=='sale_complete') { //complete sale
	if($class_sale->complete(PAGE_id)) {
		$zulu->notification_set("Sale was completed.",1);
		header("Location: ".$zulu->link_page('sale',array('query'=>array('id'=>PAGE_id))));
	} else {
		$zulu->notification_set("Sale failed to complete.",2);
	}
	exit;
}
if(PAGE_action=='DumpXeroMeta') {
	$zulu->meta_update('sale',PAGE_id,'xero_link','');
	$zulu->notification_set("Xero link was removed, you can now export this sale again.",1);
	header("Location: ".$zulu->link_page('sale'));
	exit;
}
if(PAGE_action=='LineDelete') {
	$class_sale->sale_line_delete(PAGE_id);
	exit;
}
if(PAGE_action=='LineData') {
	$sku = $db->escape_string($_GET['sku']);
	$barcode = $db->escape_string($_GET['barcode']);
	if($sku!=NULL) {
		$product_data = $class_product->product_data(array('sku'=>$sku));
	} else if($barcode != NULL) {
		$meta = zulu::table_data('product_meta',0,array('where'=>array("value='{$barcode}'","field='barcode'")));
		foreach($meta as $row) {
			$product_data = $class_product->product_data(array('id'=>$row['identifier']));
			if($product_data['user_id'] == $class_user->authorised->id) {
				echo $product_data['sku'];exit;
			}
		}
	}
	if(count($product_data)>0) {
		$price = $class_product->price($product_data['id']);
		$return = ['name'=>stripslashes($class_product->name($product_data['id'])),'price'=>$price['price'],'sku'=>$product_data['sku']];
		echo json_encode($return);
		//echo $product_data['name']."#%".$price['price']."#%".$product_data['sku'];
	} else {
		echo json_encode(['skip'=>true]);
	}
	exit;
}
if(PAGE_action == 'email_customer') {
	$class_sale->sale_receipt_mail(PAGE_id);

	$zulu->notification_set("Sale receipt emailed successfully.",1);
	header("Location: ".$zulu->link_page('sale',array('query'=>array('id'=>PAGE_id,'Action'=>'edit','Method'=>'View'))));
	exit;
}
if(PAGE_action=='xero') {

	$xml_invoices = $class_xero->invoice_build(array(PAGE_id));
	$xml = $xml_invoices['xml'];
	$invoice_row = $xml_invoices['count'];

	 if(count($invoice_row)>0) {
		$response = $class_xero->invoice_run($xml,$xml_invoices['sale_id_array']);
		$zulu->notification_set($response['message']."<br><br>LOG:<BR>".implode("<br>",$xml_invoices['log']),($response['success']?1:'0'));
	} else {
		echo 'noexpo';
		$zulu->notification_set("No data to export.",2);
	}
	header("Location: ".$zulu->link_page('sale'));
	exit;
}
if(PAGE_action=='print') {
	$form_edit = new form;

	//Template settings
	$zulu->template->is_sale = true;
	/*$zulu->template->js_file[] = "http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.0/jquery-ui.min.js";
	$zulu->template->js_file[] = TPL_rel."assets/signature/jquery.signature.min.js";
	$zulu->template->js_file[] = TPL_rel."assets/signature/jquery.ui.touch-punch.min.js";
	$zulu->template->js_file[] = TPL_rel."assets/signature/excanvas.js";
	$zulu->template->js_file[] = TPL_rel."assets/quote.js";
	$zulu->template->css_file[] = TPL_rel."css/document.css";
	$zulu->template->jquery[] = "$('#signature').signature();";
	$zulu->template->jquery[] = "

	";*/
	unset($zulu->template->js_file);
	$zulu->template->js_file[] = "https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js";
	$zulu->template->js_file[] = "https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js";
	$zulu->template->js_file[] = TPL_rel."assets/signature/jquery.ui.touch-punch.min.js";
	$zulu->template->js_file[] = TPL_rel."assets/signature/excanvas.js";
	$zulu->template->js_file[] = TPL_rel."assets/signature/jquery.signature.min.js";
	$zulu->template->js_file[] = TPL_rel."assets/quote.js";
	$zulu->template->css_file[] = TPL_rel."css/document.css";
	$TPL_body_ovr = 'body-print-quote.php';

	//Code
	if($_GET['Token']!=NULL) {
		$sale_data_query = array('token'=>$_GET['Token'],'ovr_user_id'=>true);
	} else {
		$sale_data_query = array('id'=>PAGE_id);
	}
	$sale_data = $class_sale->sale_data($sale_data_query);
	$sale_meta = $zulu->meta_array($class_sale->sale_meta($sale_data['id']));
	$sale_id = $sale_data['id'];
	$setting_data = $class_setting->setting_data(array('user_id'=>$sale_data['user_id'],'set_global'=>true));
	$reference = $sale_data['reference'];
	$complete = $class_sale->is_complete($sale_id);

	$tpl_out['reference'] = ($sale_data['reference']!=NULL?"<b>Sale</b> #".$sale_data['reference']:NULL);
	$tpl_out['date'] = ($sale_data['date']>0?"<b>Date</b> ".$zulu->date($sale_data['date'],'d/m/Y'):NULL);
	$tpl_out['date_valid'] = ($sale_data['date_due']>0?"<b>Due</b> ".$zulu->date($sale_data['date_due'],'d/m/Y'):NULL);
	$tpl_out['date_full'] = $zulu->compile('<br>',array($tpl_out['date'],$tpl_out['date_valid']));

	$logo = ($setting_data['quote_logo_path']!=NULL?$setting_data['quote_logo_path']:NULL);
	$company_out = $class_client->data_format($setting_data,'company');

	//View Type
	if($sale_data['user_id']==$class_user->authorised->id) {
		$class_sale->vars->owner = true;
	} else {
		$class_sale->vars->owner = false;
	}
	$class_sale->vars->sign = ($sale_meta['approved']>0?true:false);

	//Valid sale
	if($sale_data['id']<=0||$sale_data['id']==NULL) {
		if($class_sale->vars->owner) {
			$zulu->notification_set('This sale does not exist.',2);
			header("Location: ".$zulu->link_page('sale'));
			exit;
		} else {
			$zulu->fatal_error("Missing Data","sale was invalid or does not exist.");
			exit;
		}
	}

	//Sale Lines
	$table_column[] = array("SKU",array('class'=>array('sku')));
	$table_column[] = array("Description",array('class'=>array('des')));
	$table_column[] = array("Unit Price",array('class'=>array('unit')));
	$table_column[] = array("Quantity",array('class'=>array('quan')));
	$table_column[] = array("Discount",array('class'=>array('disc')));
	$table_column[] = array("Line Total",array('class'=>array('linettl')));
	$table_row[] = array(
			"header"	=>	 true,
			"class"		=>	"",
			"content"	=>	$table_column);


	$sale_line_items = $class_sale->sale_line($sale_id);
	foreach($sale_line_items as $row) {

		$line_subtotal = ($row['price']*$row['quantity']);
		$line_disc_perc = ($row['discount']/$line_subtotal)*100;
		$line_disc = $row['discount'];
		$line_total = ($line_subtotal-$row['discount']);
		$order_total += $line_total;
		$sale_line_sub = $class_sale->sale_line_sub($row);
		if(trim($sale_line_sub)!=NULL) {
			$sale_line_sub = "<br><small class=\"opt opt-grey\">".$sale_line_sub."</small>";
		}

		$table_row[] = array("content" => array(
			array(stripslashes($row['sku'])),
			array(stripslashes($row['description']).$sale_line_sub),
			array("$".$row['price']),
			array($row['quantity']),
			array(number_format($line_disc_perc,2)."%"),
			array("$".$zulu->dollar($line_total))
		));
	}

	$order_total += $sale_meta['ship_price'];
	$cost = $class_sale->payment_summary($order_total,array('sale_id'=>$sale_id));
	$order_discount = $class_sale->sale_total_discount($sale_id);
	$zulu->template->body->table_item = $zulu->table_render($table_row,0,array('class'=>'basket'));
	$zulu->template->body->bill_count = count($sale_line_items);
	$currency_code = $class_sale->currencyCode($sale_id);

	//Client
    if($sale_data['client_id'] > 0) {
        $client_data = $class_client->client_data(array('id'=>$sale_data['client_id'],'ovr_user_id'=>true));
        $client_meta = $class_client->client_meta($sale_data['client_id']);
        $client_out = $class_client->data_format($client_data);
    }

	if($sale_meta['bill_address'] != NULL) {
		$client_out['address'] = $zulu->compile(',<br>',array($sale_meta['bill_address'],$sale_meta['bill_suburb'],$sale_meta['bill_city'].($sale_meta['bill_post']!=NULL?" ".$sale_meta['bill_post']:NULL),$sale_meta['bill_country']));
	}
	if($sale_data['name'] != NULL) {
		$client_out['contact'] = $client_out['name'] = stripslashes($sale_data['name']);
	}

	$user_data = $class_user->user_data(array('id'=>$sale_data['user_id']));
	$user_meta = $class_user->user_meta($sale_data['user_id']);

	//Actions - Comment
	if($_POST['method']=='comment') {
		if($form_edit->validate(array('message','name','email'))) {
			$zulu->notification_set("Please enter a name, email and comment.",2);
			$skip = true;
			$_SESSION['zl_form']['comment_error'] = true;
		}

		if(!$skip) {
			$message = "Hello ".$setting_data['contact_name'].",<br><br>Your sale for '".str_replace("<br>"," ",$client_out['name'])."' had a new comment sent on ".date("d/m/Y h:ia").".<br><br><b>From:</b> ".$_POST['name']." (<a href=\"mailto:".$_POST['email']."\">".$_POST['email']."</a>)<br><b>Message:</b><br>".str_replace(chr(13),"<br>",$_POST['message'])."<br><br><b>View sale online:</b> <a href=\"".$class_sale->sale_url($sale_data['token'])."\">".$class_sale->sale_url($sale_data['token'])."</a>";
			$zulu->mail_send($setting_data['contact_email'],"Your Sale Has a Comment",$message,'',false,array('reply'=>$_POST['email'],'object'=>'sale','object_id'=>$sale_id,'no_branding'=>true,'user_id'=>$sale_data['user_id']));
			$zulu->notification_set("Thanks, your comment was sent to us.",1);
			header("Location: ".$_SERVER['HTTP_REFERER']);
			exit;
		}
	}

	//Company Info
	$co_arr = $company_out;
	if(trim($company_out['contact_name'])!=NULL) {
		$co_arr['contact_name'] = "<b>".stripslashes($company_out['contact_name'])."</b>";
	}
	unset($co_arr['company']);

	//Compile definitions
	$zulu->template->page_def = [
		'sale'	=>	true,
		'id'		=>	PAGE_id,
		'reference'		=>	$reference,
		'type'	=>	'Sale',
		'url'	=>	'sale',
		'share'	=>	$class_sale->sale_url($sale_data['token']),
		'draft'	=>	($complete>0?false:true),
		'paid'	=>	($complete&&$class_sale->sale_balance($sale_id)<=0?true:false),
		'paid_amount'	=>	$class_sale->sale_total($sale_id),
		'overdue'	=>	($complete&&$class_sale->sale_balance($sale_id)>0&&$sale_data['date_due']<strtotime('today')?true:false),
		'owner'	=>	($sale_data['user_id']==$class_user->authorised->id?true:false),
		'company_html'	=>	implode("<br>",$co_arr),
	];
}
if(PAGE_action=='report') {
	$zulu->nav->title = "Sale Report";
	$form_edit = new form;

	if($_GET['date_from']!=NULL&&$_GET['date_to']!=NULL) {
		$filtered = true;
		$date_from = $zulu->dateEncode($_GET['date_from']);
		$date_to = $zulu->dateEncode($_GET['date_to']);

		if($date_from<=0||$date_to<=0) {
			$zulu->notification_set("Report date invalid.",2);
			header("Location: ".$zulu->link_page(PAGE_file,['query'=>['Action'=>'report']]));
			exit;
		}
		if($date_from>=$date_to) {
			$zulu->notification_set("Select a from date that is less the the to date.",2);
			header("Location: ".$zulu->link_page(PAGE_file,['query'=>['Action'=>'report']]));
			exit;
		}
	}

	$currency = $class_setting->defaults->currency_symbol[$class_setting->data['currency_symbol']];

	unset($table_row);
	$table_row[] = array("content"=>[array("Total Income",array('class'=>array('c-label c-label-bold'))),array($currency.$zulu->dollar($event_stat['revenue']['total'],1),array('class'=>array('c-value')))]);
	$table_row[] = array("content"=>[array("Ticket Income",array('class'=>array('c-label'))),array($currency.$zulu->dollar($event_stat['revenue']['ticket'],1)." <span class=\"color-grey\">".$zulu->percent($event_stat['revenue']['ticket'],$event_stat['revenue']['total'])."%</span>",array('class'=>array('c-value')))]);
	$table_row[] = array("content"=>[array("Misc. Income ".$form_edit->icon_help('Excludes any voided sale totals.'),array('class'=>array('c-label'))),array($currency.$zulu->dollar($event_stat['revenue']['sale'],1)." <span class=\"color-grey\">".$zulu->percent($event_stat['revenue']['sale'],$event_stat['revenue']['total'])."%</span>",array('class'=>array('c-value')))]);

	$zulu->template->body->table_revenue = $zulu->table_render($table_row,0,array('class'=>'report','data_table'=>false));
	//-- OVERALL TABLE END

	//-- extra sales
	unset($table_row,$table_column);
	$table_column[] = array("Status",array('class'=>array('status-box center')));
	$table_column[] = array("ID",array('class'=>array('id center')));
	$table_column[] = array("Customer",array('class'=>array('center')));
	$table_column[] = array("Total",array('class'=>array('center')));
	$table_column[] = array("Balance",array('class'=>array('center')));
	$table_column[] = array("Created",array('class'=>array('center')));
	$table_row[] = array(
			"header"	=>	 true,
			"class"		=>	"",
			"content"	=>	$table_column);

	$stat = ['unit_total'=>0,'gross_sale'=>0,'total_sales'=>0];
	$stat_filter = [];
	$sale_item_count = [];
	$sale_data = $class_sale->sale_data(['date_min'=>$date_from,'date_max'=>$date_to,'status'=>[1]]);
	foreach($sale_data as $key=>$row) {
		$capsule = [];
		$status_data = $class_sale->sale_status_info($row['status'],array('id'=>$row['id']));
		if(count($stat_filter)>0&&!in_array($status_data['tag'],$stat_filter)) {
			continue;
		}

		$sale_line_data = $class_sale->sale_line($row['id'],['field'=>['quantity','description']]);
        $sale_total = $class_sale->sale_total($row['id']);
        $sale_units = 0;

		foreach($sale_line_data as $line=>$data) {
			$sale_item_count[$data['description']]['qty'] += $data['quantity'];
			$sale_item_count[$data['description']]['total'] += $data['total'];
			$sale_units += $data['quantity'];

			if($data['product_id']>0) {
				$sup_id = 0;
				$product_data = $class_product->product_data(['id'=>$data['product_id'],'field'=>['type_variant','parent_id']]);
				$sup_meta = $class_product->product_meta($data['product_id'],'supplier_id');
				if($sup_meta['value']>0) {
					$sup_id = $sup_meta['value'];
				} elseif($product_data['type_variant']==2&&$product_data['parent_id']>0) {
					$sup_meta = $class_product->product_meta($product_data['parent_id'],'supplier_id');
					$sup_id = $sup_meta['value'];
				}
				if($sup_id>0) {
					$sale_sup_count[$sup_id]['qty'] += $data['quantity'];
					$sale_sup_count[$sup_id]['total'] += $data['total'];
				}
			}
		}

		$meta_fe = $zulu->meta_value("sale",$row['id'],"fe");
		$meta_fe_payment = $zulu->meta_value("sale",$row['id'],"fe_payment");

		$label_ttl = '<span class="'.$status_data['class'].'"><i class="fas '.$status_data['icon'].'"></i> '.$status_data['label'].'</span>';
		$link = $zulu->link_page('sale',array('query'=>array('id'=>$row['id'],'Action'=>'edit')));
		$table_row[] = array("content" => array(
			array($label_ttl,array('class'=>array('text-small'))),
			array("<a href=\"".$link."\">".$row['reference']."</a>"),
			array("<a href=\"".$link."\">".$row['name']."</a>&nbsp;&nbsp;&nbsp;".implode(" ",$capsule)),
			array($currency.number_format($sale_total,2)),
			array($currency.number_format($class_sale->sale_balance($row['id']),2)),
			array(zulu::time_fancy($row['stat_add']),array('class'=>array('text-small'))),
		));

		$stat['unit_total'] += $sale_units;
		$stat['gross_sale'] += $sale_total;
        $stat['total_sales']++;
		$i++;
	}
	$zulu->vars->sale_count = count($table_row);
	$zulu->template->table_sale = $zulu->table_render($table_row,0,array('class'=>'report','data_table'=>false));

	unset($table_row,$table_column);

	function sort_array_of_array(&$array, $subfield)
	{
		$sortarray = array();
		foreach ($array as $key => $row)
		{
			$sortarray[$key] = $row[$subfield];
		}

		array_multisort($sortarray, SORT_NUMERIC, SORT_DESC, $array);
	}
	sort_array_of_array($sale_item_count, 'qty');

	$table_column[] = array("Description",array('class'=>array('status-box center')));
	$table_column[] = array("Quantity",array('class'=>array('')));
	$table_column[] = array("Gross Sales",array('class'=>array('')));
	$table_row[] = array(
			"header"	=>	 true,
			"class"		=>	"",
			"content"	=>	$table_column);

	foreach($sale_item_count as $sic_description=>$sic_quantity) {

		$table_row[] = array("content" => array(
			array($sic_description),
			array($sic_quantity['qty']),
			array($currency.$zulu->dollar($sic_quantity['total'],true)),
		));
	}
	$zulu->template->body->table_product = $zulu->table_render($table_row,0,array('class'=>'report','data_table'=>false));
	//-- PRODUCT TABLE END

	unset($table_row,$table_column);
	$table_column[] = array("Supplier",array('class'=>array('status-box center')));
	$table_column[] = array("Quantity",array('class'=>array('')));
	$table_column[] = array("Gross Sales",array('class'=>array('')));
	if($_POST['com_per']>0) {
		$table_column[] = array("Your Commission",array('class'=>array('')));
	}

	$table_row[] = array(
			"header"	=>	 true,
			"class"		=>	"",
			"content"	=>	$table_column);

	$row_i = 1;
	foreach($sale_sup_count as $sic_description=>$sic_quantity) {
		$table_row[$row_i] = array("content" => array(
			array($class_client->admin_link($sic_description)),
			array($sic_quantity['qty']),
			array($currency.$zulu->dollar($sic_quantity['total'],true)),
		));
		if($_POST['com_per']>0) {
			$table_row[$row_i]['content'][] = $currency.$zulu->dollar(($sic_quantity['total']*($_POST['com_per']/100)),true);
		}
		$row_i++;
	}
	$zulu->template->body->table_supplier = $zulu->table_render($table_row,0,array('class'=>'report','data_table'=>false));
	//-- SUPPLIER TABLE END

	unset($table_row);
	$table_row[] = array("content"=>[array("Gross Profit",array('class'=>array('c-label c-label-bold'))),array($currency.$zulu->dollar($stat['gross_sale'],true),array('class'=>array('c-value')))]);
	$table_row[] = array("content"=>[array("Units Sold",array('class'=>array('c-label'))),array($stat['unit_total'],array('class'=>array('c-value')))]);
	$table_row[] = array("content"=>[array("Avg. Unit Value",array('class'=>array('c-label'))),array($currency.$zulu->dollar(($stat['gross_sale']/$stat['unit_total']),true),array('class'=>array('c-value')))]);

	$zulu->template->body->table_gross = $zulu->table_render($table_row,0,array('class'=>'report','data_table'=>false));
	//-- GROSS TABLE END

	if($filtered) {
		$zulu->nav->breadcrumb['Sale Report'] = array('link'=>$zulu->link_page(PAGE_file,['query'=>['Action'=>'report']]));
		$zulu->nav->breadcrumb['From '.date('d/m/Y',$date_from).' to '.date('d/m/Y',$date_to)] = array();
	} else {
		$zulu->nav->breadcrumb['Sale Report'] = array();
	}
}
if(PAGE_action=='report_supplier') {
	$zulu->nav->title = "Supplier Report";
	$form_edit = new form;

	$zulu->template->js_file[] = TPL_rel."assets/quote.js";
	$zulu->template->css_file[] = TPL_rel."css/document.css";
	$TPL_body_ovr = 'body-print.php';

	//-- Supplier Data
	$token = $db->escape_string($_GET['Token']);
	$client_data = $class_client->client_data(['token'=>$token,'supplier'=>1]);
	$client_meta = $zulu->meta_array($class_client->client_meta($client_data['id']));

	if($client_data['id']<=0) {
		$zulu->notification_set("This account does not exist.",2);
	} elseif($client_meta['web_access']==0||$client_meta['web_verify']==0) {
		$zulu->notification_set("This account does not have access to reports.",2);
	} elseif($client_data['password']==NULL) {
		$zulu->notification_set("Please ask the administrator to set a password for this report.",2);
	} else {

		if(isset($_POST['password'])&&md5($_POST['password'])==$client_data['password']) {
			$authed = true;
		} else {
			$authed = false;
		}

		if($authed) {
			if($_GET['date_from']!=NULL&&$_GET['date_to']!=NULL) {
				$filtered = true;
				$date_from = $zulu->dateEncode($_GET['date_from']);
				$date_to = $zulu->dateEncode($_GET['date_to']);

				if($date_from<=0||$date_to<=0) {
					$zulu->notification_set("Report date invalid.",2);
					header("Location: ".$zulu->link_page(PAGE_file,['query'=>['Action'=>'report']]));
					exit;
				}
				if($date_from>=$date_to) {
					$zulu->notification_set("Select a from date that is less the the to date.",2);
					header("Location: ".$zulu->link_page(PAGE_file,['query'=>['Action'=>'report']]));
					exit;
				}
			}

			$currency = $class_setting->defaults->currency_symbol[$class_setting->data['currency_symbol']];

			//-- extra sales
			unset($table_row,$table_column);
			$table_column[] = array("Status",array('class'=>array('status-box center')));
			$table_column[] = array("Reference",array('class'=>array('id center')));
			$table_column[] = array("Customer",array('class'=>array('id center')));
			$table_column[] = array("Product Sold",array('class'=>array('center')));
			$table_column[] = array("Price",array('class'=>array('center')));
			$table_column[] = array("Quantity",array('class'=>array('center')));
			$table_column[] = array("Subtotal",array('class'=>array('center')));
			$table_column[] = array("Date",array('class'=>array('center')));
			$table_row[] = array(
					"header"	=>	 true,
					"class"		=>	"",
					"content"	=>	$table_column);

			$sale_item_count = [];
			$supplier_product_array = $class_product->product_data(['supplier_id'=>$client_data['id'],'field'=>['name','price','product.id']]);
			if(count($supplier_product_array)>0) {
				//--
				foreach($supplier_product_array as $product_row) {
					$sale_line_data = $class_sale->sale_line_data(['product_id'=>$product_row['id'],'latest'=>true]);
					foreach($sale_line_data as $key=>$row) {
						$sale_data = $class_sale->sale_data(['id'=>$row['sale_id'],'field'=>['name','reference','status','stat_add']]);
						$status_data = $class_sale->sale_status_info($row['status'],['id'=>$row['sale_id']]);
						$label_ttl = '<span class="'.$status_data['class'].'"><span class="fas '.$status_data['icon'].'"></span> '.$status_data['label'].'</span>';
						$table_row[$row['stat_add']] = array("content" => array(
							array($label_ttl,array('class'=>array('text-small'))),
							array("".$sale_data['reference']."</a>"),
							array("".stripslashes($sale_data['name'])."</a>&nbsp;&nbsp;&nbsp;".implode(" ",$capsule)),
							array("".stripslashes($row['description'])),
							array($currency.$zulu->dollar($row['price'],['class'=>['text-center']])),
							array(number_format($row['quantity'],2),['class'=>['text-center']]),
							array($currency.$zulu->dollar($row['total'],['class'=>['text-center']])),
							array(zulu::time_fancy($row['stat_add']),array('class'=>array('text-small'))),
						));

						if($sale_data['status']==1) {
							$stat['unit_total'] += $row['quantity'];
							$stat['gross_sale'] += $row['total'];
						}
						$i++;
					}
					$zulu->vars->sale_count = count($table_row);
				}

				$stats = "
				<div class='panel panel-default'>
					<div class='panel-body'>
						<div class='row'>
							<div class='col-md-6 text-center'>
								<div class='panel panel-green no-margin'>
									<div class='panel-heading'>
										Total Units
									</div>
									<div class='panel-body'>
										<p class='h2 no-margin'>".number_format($stat['unit_total'],2)."</p>
									</div>
								</div>
							</div>
							<div class='col-md-6 text-center'>
								<div class='panel panel-green no-margin'>
									<div class='panel-heading'>
										Total Sales
									</div>
									<div class='panel-body'>
										<p class='h2 no-margin'>".$currency.$zulu->dollar($stat['gross_sale'])."</p>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class='panel-footer '><span class='opt opt-grey'><i class='fas fa-exclamation-triangle'></i> Above data is for paid and pending sales only.</span></div>
				</div>
			";

				$block[] = ['name'=>'Sales Summary','html'=>$stats.$zulu->table_render($table_row,0,array('class'=>'report','data_table'=>false))];

				unset($table_row,$table_column);
			} else {
				$zulu->notification_set("This supplier has no products linked in.",2);
			}

			//-- Compile Page
			foreach($block as $block_item) {
				$zulu->template->body =	"<div class='panel panel-default'>
					<div class='panel-heading'>".$block_item['name']."</div>
					<div class='panel-body'>
						".$block_item['html']."
					</div>
				</div>";
			}

			if($filtered) {
				$zulu->nav->breadcrumb['Sale Report'] = array('link'=>$zulu->link_page(PAGE_file,['query'=>['Action'=>'report']]));
				$zulu->nav->breadcrumb['From '.date('d/m/Y',$date_from).' to '.date('d/m/Y',$date_to)] = array();
			} else {
				$zulu->nav->breadcrumb['Sale Report'] = array();
			}
		} else {
			$zulu->template->body = "
				<div class=\"row\">
					<div class=\"col-md-6\">
						<form method=\"post\" action=\"\">
							<div class=\"panel panel-default\">
								<div class=\"panel-heading\"><i class=\"far fa-lock\"></i> Please log in...</div>
								<div class=\"panel-body\">
									<div class=\"form-group\">
										<label>Password</label>
										".$form_edit->input_html('password','password',$_POST['password'])."
									</div>
									<div class=\"form-group\">
										".$form_edit->input_html('submit','go',"Login")."
									</div>
								</div>
							</div>
						</form>
					</div>
				</div>
			";
		}
	}
}
if(PAGE_action=='abandon') {
	$zulu->nav->title = "Abandoned Report";
	$form_edit = new form;

	if($_GET['date_from']!=NULL&&$_GET['date_to']!=NULL) {
		$filtered = true;
		$date_from = $zulu->dateEncode($_GET['date_from']);
		$date_to = $zulu->dateEncode($_GET['date_to']);

		if($date_from<=0||$date_to<=0) {
			$zulu->notification_set("Report date invalid.",2);
			header("Location: ".$zulu->link_page(PAGE_file,['query'=>['Action'=>'abandon']]));
			exit;
		}
		if($date_from>=$date_to) {
			$zulu->notification_set("Select a from date that is less the the to date.",2);
			header("Location: ".$zulu->link_page(PAGE_file,['query'=>['Action'=>'abandon']]));
			exit;
		}
	}

	$sale_item_count = [];
	$sale_data = $class_sale->sale_data(['date_min'=>$date_from,'date_max'=>$date_to,'status'=>[1,0]]);
	foreach($class_website->config->shop_cart_steps as $shop_key=>$shop_step) {
		$stat['abandon_step'][$shop_key] = 0;
		$stat['abandon_do_step'][$shop_key] = 0;
	}
	foreach($sale_data as $key=>$row) {
		$abandon_track = $zulu->meta_value('sale',$row['id'],'sale_abandoned_data');
		if(trim($abandon_track['value'])!=NULL) {
			$abandon_row = unserialize($abandon_track['value']);
			$step_upto = count($abandon_row);
			foreach($class_website->config->shop_cart_steps as $shop_key=>$shop_step) {
				if(isset($abandon_row[$shop_key])) {
					if($abandon_row[$shop_key]['time_end']>0) {
						$stat['abandon_step'][$shop_key]++;
					} else {
						$abandon_sale[$shop_key][] = $row['id'];
						$stat['abandon_do_step'][$shop_key]++;
					}
				}
			}
			if($row['status']==1) {
				$scs = count($class_website->config->shop_cart_steps);
				$stat['abandon_step'][$scs]++;
			}
		}
	}

	/* Gen abandon sale tables */
	if(count($abandon_sale)>0) {
		foreach($class_website->config->shop_cart_steps as $shop_key=>$shop_step) {
			$sale_ids = $abandon_sale[$shop_key];
			if(count($sale_ids)>0) {

				//-- extra sales
				unset($table_row,$table_column);
				$table_column[] = array("Status",array('class'=>array('status-box center')));
				$table_column[] = array("ID",array('class'=>array('id center')));
				$table_column[] = array("Customer",array('class'=>array('center')));
				$table_column[] = array("Total",array('class'=>array('center')));
				$table_column[] = array("Balance",array('class'=>array('center')));
				$table_column[] = array("Created",array('class'=>array('center')));
				$table_row[] = array(
						"header"	=>	 true,
						"class"		=>	"",
						"content"	=>	$table_column);

				$sale_item_count = [];
				$sale_data = $class_sale->sale_data(['id_in'=>$sale_ids]);
				foreach($sale_data as $key=>$row) {
					$capsule = [];
					$status_data = $class_sale->sale_status_info($row['status'],array('id'=>$row['id']));
					$sale_line_data = $class_sale->sale_line($row['id'],['field'=>['quantity','description']]);

					foreach($sale_line_data as $line=>$data) {
						$sale_item_count[$data['description']]['qty'] += $data['quantity'];
						$sale_item_count[$data['description']]['total'] += $data['total'];

						if($data['product_id']>0) {
							$sup_id = 0;
							$product_data = $class_product->product_data(['id'=>$data['product_id'],'field'=>['type_variant','parent_id']]);
							$sup_meta = $class_product->product_meta($data['product_id'],'supplier_id');
							if($sup_meta['value']>0) {
								$sup_id = $sup_meta['value'];
							} elseif($product_data['type_variant']==2&&$product_data['parent_id']>0) {
								$sup_meta = $class_product->product_meta($product_data['parent_id'],'supplier_id');
								$sup_id = $sup_meta['value'];
							}
							if($sup_id>0) {
								$sale_sup_count[$sup_id]['qty'] += $data['quantity'];
								$sale_sup_count[$sup_id]['total'] += $data['total'];
							}
						}
					}

					$meta_fe = $zulu->meta_value("sale",$row['id'],"fe");
					$meta_fe_payment = $zulu->meta_value("sale",$row['id'],"fe_payment");
					if(count($stat_filter)>0&&!in_array($status_data['tag'],$stat_filter)) {
						continue;
					}
					$label_ttl = '<span class="'.$status_data['class'].'"><i class="fas '.$status_data['icon'].'"></i> '.$status_data['label'].'</span>';
					$link = $zulu->link_page('sale',array('query'=>array('id'=>$row['id'],'Method'=>'View','Action'=>'edit')));
					$table_row[] = array("content" => array(
						array($label_ttl,array('class'=>array('text-small'))),
						array("<a href=\"".$link."\">".$row['reference']."</a>"),
						array("<a href=\"".$link."\">".$row['name']."</a>&nbsp;&nbsp;&nbsp;".implode(" ",$capsule)),
						array($currency.number_format($class_sale->sale_total($row['id']),2)),
						array($currency.number_format($class_sale->sale_balance($row['id']),2)),
						array(zulu::time_fancy($row['stat_add']),array('class'=>array('text-small'))),
					));

					//$stat['unit_total'] += $data['quantity'];
					//$stat['gross_sale'] += $data['total'];
					$i++;
				}
				$table_html = $zulu->table_render($table_row,0,array('class'=>'report','data_table'=>false));

				unset($table_row,$table_column);

				$zulu->template->body_sale[$shop_key] = $table_html;
			} else {
				$zulu->template->body_sale[$shop_key] = "<span class='opt opt-grey'><i class='fa fa-times'></i> No sales exist in this step.</span>";
			}
		}
	}

	if($filtered) {
		$zulu->nav->breadcrumb['Abandon Report'] = array('link'=>$zulu->link_page(PAGE_file,['query'=>['Action'=>'abandon']]));
		$zulu->nav->breadcrumb['From '.date('d/m/Y',$date_from).' to '.date('d/m/Y',$date_to)] = array();
	} else {
		$zulu->nav->breadcrumb['Abandon Report'] = array();
	}
}
if(PAGE_action == 'remove_coupon') {
	$class_sale->remove_coupon(PAGE_id);

	$zulu->notification_set("Coupon unlinked.",1);
	header("Location: ".$zulu->link_page('sale',array('query'=>array('id'=>PAGE_id,'Action'=>'pay'))));
	exit;
}

if(PAGE_action=='coupon') {	//coupon grid page

    if($_GET['Do'] == 'BulkFileDelete') {
        $file = $zulu->esc($_GET['file']);
        if($file != null) {
            @unlink($class_file->file_root_user.$class_user->authorised->id."/coupon-bulk-export/".$file);
        }
        $zulu->notification_set("Bulk file successfully removed.",1);
        header("Location: ".$zulu->link_page(PAGE_file, ['self'=>true, 'filter'=>['Do','file']]));
        exit;
    }

	$form_edit = new form;

	function edit_coupon_bt($id,$data) {
		global $zulu;
		global $class_product;
		return "
			".($data['voucher_link']!=NULL?"
			<a href=\"".$data['voucher_link']."\" target=\"_blank\" title=\"View Voucher PDF\"><button class=\"btn btn-default btn-circle\" type=\"button\"><i class=\"fas fa-print\"></i></button></a>":NULL)."
			<a href=\"".$zulu->link_page('sale',array('query'=>array('id'=>$id,'Action'=>'coupon_edit')))."\" title=\"Edit\"><button class=\"btn btn-primary btn-circle\" type=\"button\"><i class=\"fas fa-edit\"></i></button></a>
			<a class=\"confirm-delete\" href=\"".$zulu->link_page('sale',array('query'=>array('id'=>$id,'Action'=>'coupon_delete')))."\" title=\"Delete\"><button class=\"btn btn-danger btn-circle\" type=\"button\"><i class=\"fas fa-times\"></i></button></a>
		";
	}

    $filter = [];
    if($_GET['Search'] != null) {
        $filter['search'] = $zulu->esc($_GET['Search']);
    }
    if($_GET['Tab'] != null && $_GET['Tab'] != 'all') {
        $filter['type'] = $zulu->esc($_GET['Tab']);
    }
    $coupon_count_full = count($class_sale->coupon_data($filter));

    $start = ($_GET['Pg']>1?MAX_per_page*($_GET['Pg']-1):0);
    $filter['row_start'] = $start;
    $filter['row_limit'] = MAX_per_page;

	//Load Current Category Data
	$coupon_data = $class_sale->coupon_data($filter);
	$zulu->nav->breadcrumb['Coupons & Vouchers'] = array();
	$zulu->nav->title = "Coupons & Vouchers";

	//Load Products / Categories
	//$table_column[] = array("ID",array('class'=>array('')));
	$table_column[] = array("Issued",array('class'=>array('')));
	$table_column[] = array("Status",array('class'=>array('status-box')));
	$table_column[] = array("Customer",array('class'=>array('')));
	$table_column[] = array("Code",array('class'=>array('id')));
    $table_column[] = array("Type",array('class'=>array('id')));
	$table_column[] = array("Name",array('class'=>array('')));
	$table_column[] = array("Redemptions",array('class'=>array('')));
	$table_column[] = array("Redeemed by",array('class'=>array('')));
	$table_column[] = array("Actions",array('class'=>array('right')));
	$table_row[] = array(
			"header"	=>	 true,
			"class"		=>	"",
			"content"	=>	$table_column);

	foreach($coupon_data as $row) {
		$status_data = $class_sale->coupon_status_info($row['id']);
		$label_ttl = '<span class="'.$status_data['class'].'"><span class="fas '.$status_data['icon'].'"></span> '.$status_data['label'].'</span>';
		$link = $zulu->link_page('sale',array('query'=>array('id'=>$row['id'],'Action'=>'edit')));
		$redemption_count = $class_sale->coupon_redemption($row['id']);
		if($row['conf_member'] != 0) {
			$ids = explode(',',$row['conf_member']);
			foreach($ids as $id_temp) {
				$client_data = $class_client->client_data(array('id'=>$id_temp));
				$customer_array[] = stripslashes($client_data['name']);
			}
			$customer_label = implode(', ',$customer_array);
			unset($customer_array);
		} else if($row['conf_member_id'] > 0) {
			$client_data = $class_client->client_data(array('id'=>$row['conf_member_id']));
			$customer_label = stripslashes($client_data['name']);
		} else {
			$customer_label = 'Any';
		}
		$sale_data = $class_sale->sale_data(['coupon_id'=>$row['id']]);
		foreach($sale_data as $sale_row) {
			$redeem_by[] = "<a href=\"".$zulu->link_page('sale',['query'=>['id'=>$sale_row['id'],'Action'=>'edit','Method'=>'View']])."\">".stripslashes($sale_row['name'])."</a>";
		}
		if($row['sale_line_id']>0) {
			$line_data = $class_sale->sale_line_data(array('id'=>$row['sale_line_id']));
			$prod_row = $class_product->product_data(['id'=>$line_data['product_id']]);
			if($prod_row['template'] == 'voucher') {
				$sale_row = $class_sale->sale_data(array('id'=>$line_data['sale_id']));
				$file = "file/sale/".$sale_row['token']."/voucher-".$row['id'].".pdf";
				//echo dirname(__FILE__)."/../../../".$file;exit;
				if(file_exists(dirname(__FILE__)."/../../../".$file)) {
					$row['voucher_link'] = MAIN_url.$file;
				}

			}
		}
		$bt_array = [
				['label'=>'Edit','class'=>'primary','icon'=>'edit','link'=>$zulu->link_page(PAGE_file,['query'=>['Action'=>'coupon_edit','id'=>$row['id']]])]];
		if($row['status']!=2) {
			$bt_array[] = ['label'=>'','class'=>'danger','icon'=>'times','link'=>$zulu->link_page(PAGE_file,['query'=>['Action'=>'coupon_delete','id'=>$row['id']]])];
		}

		$table_row[] = array("content" => array(
		//	array($row['id']),
			array($zulu->dateDecode($row['stat_add'])),
			array($label_ttl,array('class'=>array('text-small'))),
			array($customer_label),
			array($row['code']),
            array($class_sale->coupon_type[$row['type']]),
			array(stripslashes($row['name'])),
			array($redemption_count),
			array((count($redeem_by)>0?implode('<br>',$redeem_by):"-")),
			array($zulu->button_render($bt_array),array('class'=>array('right')))
		));
		unset($redeem_by);
	}

	$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'sale','data_table'=>false,'html_id'=>'coupon_list'));
    $pagination = $zulu->pagination($_GET['Pg'],['count'=>$coupon_count_full,'link'=>$zulu->link_page(PAGE_file,array('self'=>true))]);
	$zulu->nav->title = "Coupons & Vouchers";

	//Build Tree of Links
	$class_product->category_breadcrumb($class_product->root_id);

    $tab_list = [
        "all"       =>	"All",
        "coupon"    =>	"Coupons",
        "voucher"	=>	"Vouchers",
    ];
    if($_GET['Tab'] == null) {
        $selected_tab = 'all';
    } else {
        $selected_tab = $_GET['Tab'];
    }

    $export_dir = $class_file->file_root_user.$class_user->authorised->id."/coupon-bulk-export/";
    $export_glob = glob($export_dir."*");
    if(count($export_glob) > 0) {
        $table_row = $table_column = [];
        $table_column[] = array("Created",array('class'=>array('')));
        $table_column[] = array("Actions",array('class'=>array('right')));
        $table_row[] = array(
                "header"	=>	 true,
                "class"		=>	"",
                "content"	=>	$table_column);
        $export_limit = 30;
        $export_glob = array_reverse($export_glob);
        $export_glob = array_slice($export_glob,0,$export_limit);
        foreach($export_glob as $export_file) {
            $filename = basename($export_file);
            $filename_parts = explode('-',$filename);
            $bt_array = [
                ['label'=>'','class'=>'info','icon'=>'download','link'=>$class_file->file_root_user_rel.$class_user->authorised->id."/coupon-bulk-export/".$filename,'title'=>'Download'],
                ['label'=>'','class'=>'danger','icon'=>'times','link'=>$zulu->link_page(PAGE_file,['self'=>true,'query'=>['Do'=>'BulkFileDelete','file'=>$filename]])]
            ];
            $table_row[] = array("content" => array(
                array($zulu->dateDecode(strtotime($filename_parts[0]),'h:ia d/m/Y')),
                array($zulu->button_render($bt_array),array('class'=>array('right')))
            ));
        }
        $zulu->template->export_table = $zulu->table_render($table_row,0,array('class'=>'sale','data_table'=>false));
    }

}

if(PAGE_action=='coupon_delete') { //delete page
	if($class_sale->coupon_delete(PAGE_id)) {
		$zulu->notification_set("Coupon was deleted.",1);
		header("Location: ".$zulu->link_page('sale',['self'=>true,'query'=>['Action'=>'coupon']]));
		exit;
	} else {
		$zulu->notification_set("Coupon was not able to be deleted.",2);
	}
}

if(PAGE_action=='coupon_edit') { //edit page
	$form_edit = new form;

	if($_GET['Do'] == 'LoadObject') {
		$object = $db->escape_string($_GET['Object']);
		$coupon_data = $class_sale->coupon_data(array('id'=>PAGE_id));
		if($coupon_data['discount_object'] == $object) {
			$val = $coupon_data['discount_object_id'];
			if($object == 'event_ticket') {
				$ticket_row = $class_book->event_ticket_type_data(['id'=>$val]);
				$val = $ticket_row['event_id'];
			}
		}

		if($object == 'event_ticket') {
			$return = $form_edit->array_to_options($form_edit->eventOptionForm(1,['upcoming'=>true]),$val);
		} else {
			$return = $form_edit->array_to_options($form_edit->productOptionForm(),$val);
		}

		echo $return;
		exit;
	}
	if($_GET['Do'] == 'LoadObject2') {
		$object_id = $db->escape_string($_GET['ObjectID']);
		$coupon_data = $class_sale->coupon_data(array('id'=>PAGE_id));
		if($coupon_data['discount_object'] == 'event_ticket') {
			$val = $coupon_data['discount_object_id'];
		}
		$return = $form_edit->array_to_options($form_edit->eventTicketOptionForm(0,['event_id'=>$object_id]),$val);
		echo $return;
		exit;
	}

    if(isset($_GET['Bulk']) && $_GET['Bulk'] == '1') {
        $bulk_create = true;
    } else {
        $bulk_create = false;
    }

	$zulu->template->css_file[] = "//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css";
	$zulu->template->js_file[] = "//code.jquery.com/ui/1.11.4/jquery-ui.js";
	$zulu->template->js_code[] = "
      	$(document).ready(function(){
			$(\"input[name='conf_start'], input[name='conf_expire']\").datepicker({ dateFormat: \"dd/mm/yy\" });

			$('#object-second').hide();
			$(\"select[name='discount_object']\").change(function() {
				var val = $(this).val();
				$.get('".$zulu->link_page('sale',['query'=>['id'=>PAGE_id,'Action'=>'coupon_edit','Do'=>'LoadObject']])."&Object='+val, function(data) {
					$(\"#object_id_1\").html(data);
					if(val == 'event_ticket') {
						$.get('".$zulu->link_page('sale',['query'=>['id'=>PAGE_id,'Action'=>'coupon_edit','Do'=>'LoadObject2']])."&ObjectID='+$(\"#object_id_1\").val(), function(data) {
							$(\"#object_id_2\").html(data);
							$('#object-second').show();
						});
					} else {
						$('#object-second').hide();
					}
				});
			});
			$(\"body\").on('change','#object_id_1',function() {
				if($(\"select[name='discount_object']\").val() == 'event_ticket') {
					var val = $(this).val();
					$.get('".$zulu->link_page('sale',['query'=>['id'=>PAGE_id,'Action'=>'coupon_edit','Do'=>'LoadObject2']])."&ObjectID='+val, function(data) {
						$(\"#object_id_2\").html(data);
						$('#object-second').show();
					});
				} else {
					$('#object-second').hide();
				}
			});
			$(\"select[name='discount_object']\").trigger('change');
            $(\"body\").on('change','select[name=\"type\"]',function() {
                var val = $(this).val();
                $('.disc-settings:visible').slideUp(300, function() {
                    $('.disc-settings#'+val+'-settings').slideDown(300);
                });
            });
            $(\"select[name='type']\").trigger('change');
	  	});
		";

	$zulu->nav->breadcrumb['Coupons & Vouchers'] = array("link"=>$zulu->link_page('sale',array('query'=>array('Action'=>'coupon'))));
	if(PAGE_id<1) {
		$id = 0;
		$new = true;

		$_POST['conf_start'] = $zulu->dateDecode(time());

		$zulu->nav->breadcrumb['New'] = array();
		$zulu->nav->title = "New";
	} else {
		$id = ($_POST['id']>0?$_POST['id']:($_GET['id']>0?$_GET['id']:0));

		$coupon_data = $class_sale->coupon_data(array('id'=>$id));

		$zulu->nav->breadcrumb['Edit'] = array();
		$zulu->nav->breadcrumb[stripslashes($coupon_data['name'])] = array();
		$zulu->nav->title = "Edit";

		if(!$_POST) {
			foreach($coupon_data as $key=>$val) {
				$_POST[$key] = $val;
			}
			$_POST['conf_start'] = zulu::dateDecode($_POST['conf_start']);
			$_POST['conf_expire'] = zulu::dateDecode($_POST['conf_expire']);
			$_POST['conf_member'] = explode(',',$_POST['conf_member']);
            $_POST['voucher_amount'] = $_POST['discount_amount'];
		}

		if($coupon_data['admin_id']>0) {
			$creator_data = $class_user->user_data(array('id'=>$coupon_data['admin_id']));
			$coupon_create = "<a href=\"".$zulu->link_page('user',array('query'=>array('id'=>$coupon_data['admin_id'],'Action'=>'edit')))."\">".$creator_data['name_first']." ".$creator_data['name_last']." (".$creator_data['username'].")</a>";
		} else {
			$coupon_create = "Front End Sale";
		}

		$table_column[] = array("Sale",array('class'=>array('')));
		$table_column[] = array("Customer",array('class'=>array('')));
		$table_column[] = array("Date Redeemed",array('class'=>array('right')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);
		$redemption_data = $class_sale->sale_data(array('coupon_id'=>PAGE_id));
		foreach($redemption_data as $redemption) {
			$sale_link = $zulu->link_page('sale',array('query'=>array('id'=>$redemption['id'],'Action'=>'edit')));
			$cust_link = $zulu->link_page('customer',array('query'=>array('id'=>$redemption['user_id'],'Action'=>'edit')));
			$book_id = 0;
			if($class_sale->is_booking($redemption['id'])) {
				$book_id = $class_sale->booking_id($redemption['id']);
				$book_link = $zulu->link_page('book_config',array('query'=>array('id'=>$book_id)));
			}
			$table_row[] = array("content" => array(
				array("<a href=\"".$sale_link."\">#".$redemption['reference']."</a>"),
				array("<a href=\"".$cust_link."\">".stripslashes($redemption['name'])."</a>".($redemption['admin_id']<=0?" <span class=\"fas fa-user color-grey\" title=\"Order placed by customer on website.\"></span>":NULL)),
				array($zulu->dateDecode($redemption['stat_add'])),
			));
		}
		$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'sale','js_table'=>false));
	}
	$coupon_options = $form_edit->couponOptionForm();
	$product_options = $form_edit->productOptionForm();

	//Form Submit
	if($_POST['action']=='edit') {
		$form_edit->valid = true;
		if($form_edit->validate(array('name','conf_start','conf_expire'))) {
			$zulu->notification_set("Please enter all fields denoted <em>*</em>.",2);
			$form_edit->valid = false;
		}
        if(!$bulk_create) {
            $check_data = $class_sale->coupon_data(array('code'=>$_POST['code']));
            if($check_data['code']!=NULL && $check_data['id']!=PAGE_id) {
                $zulu->notification_set("That code has already been used.",2);
                $form_edit->valid = false;
            }
        } else {
            if($_POST['quantity'] <= 0) {
                $zulu->notification_set("Please enter the quantity of coupons you want to create.",2);
                $form_edit->valid = false;
            }
        }

        if($form_edit->valid) {
            $data = [
                'type'                  =>  $_POST['type'],
                'code'                  =>  addslashes($_POST['code']),
                'name'                  =>  addslashes($_POST['name']),
                'terms'                 =>  addslashes($_POST['terms']),
                'description'           =>  addslashes($_POST['description']),
                'conf_member'           =>  implode(',',$_POST['conf_member']),
                'conf_member_id'        =>  $_POST['conf_member_id'],
                'conf_start'            =>  $zulu->dateEncode($_POST['conf_start']),
                'conf_expire'           =>  $zulu->dateEncode($_POST['conf_expire']),
                'conf_max'              =>  $_POST['conf_max'],
                'conf_max_sale'         =>  $_POST['conf_max_sale'],
                'conf_max_member'       =>  $_POST['conf_max_member'],
                'discount_type'         =>  $_POST['discount_type'],
                'discount_amount'       =>  $_POST['discount_amount'],
                'discount_object'       =>  $_POST['discount_object'],
                'discount_object_id'    =>  $_POST['discount_object_id'],
                'discount_object_min'   =>  $_POST['discount_object_min'],
                'discount_remain'       =>  ($data['conf_max']>0?$data['conf_max']:0),
                'status'                =>  1,
            ];

            if($data['type'] == 'voucher') {
                $data['discount_type'] = 'fixed';
                $data['discount_amount'] = $_POST['voucher_amount'];
                $data['discount_object'] = '';
                $data['discount_object_id'] = 0;
                $data['discount_object_min'] = 0;
                if($new) {
                    $data['discount_remain'] = $data['discount_amount'];
                } else {
                    $data['discount_remain'] = $_POST['discount_remain'];
                }
                $data['discount_remain'] = $zulu->dollar($data['discount_remain']);
            }

            if(!$bulk_create) {
                if($return = $class_sale->coupon_edit(PAGE_id,$data)) {
                    $id = (PAGE_id>0?PAGE_id:$return['id']);
                    $zulu->notification_set("Coupon ".($id>0?"updated":"created")." successfully.",1);
                    header("Location: ".$zulu->link_page('sale',array('query'=>array('Action'=>'coupon'))));
                    exit;
                } else {
                    $zulu->notification_set("A database error occurred.",2);
                }
            } else {
                $export_dir = $class_file->file_root_user.$class_user->authorised->id."/";
                @mkdir($export_dir);
                $export_dir .= "coupon-bulk-export/";
                @mkdir($export_dir);
                $export_file = $export_dir.$zulu->date(time(),'YmdHis')."-".$zulu->slug($data['name']).".csv";
                $export_handle = fopen($export_file, "w");

                $data['code_prefix'] = addslashes($_POST['code_prefix']);
                for($i=0; $i<$_POST['quantity']; $i++) {
                    $return = $class_sale->coupon_edit(0,$data);
                    $id = $return['id'];
                    fwrite($export_handle, $return['code']."\n");
                }
                fclose($export_handle);

                $zulu->notification_set("Coupons created successfully.",1);
				header("Location: ".$zulu->link_page('sale',array('query'=>array('Action'=>'coupon'))));
				exit;
            }

		}
	}
}
