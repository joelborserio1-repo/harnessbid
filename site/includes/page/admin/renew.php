<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- DEFINITIONS
define(PAGE_file,'renew');
define(PAGE_name,'Subscriptions');
$zulu->nav->breadcrumb[PAGE_name] = array("link"=>$zulu->link_page(PAGE_file));

//-- AUTHORISED?
$class_user->user_authorised_check();

//-- ADMIN MASTER SECTION
if(MASTER_section=='admin') { //admin section

	$zulu->template->head = "";
	$zulu->template->body = "";

	if(PAGE_action==NULL) {	//grid page

		$zulu->template->js_code[] = "
			$(document).ready(function(){
				$(\"select[name='Branch']\").change(function() {
					document.location.href = '".$zulu->link_page(PAGE_file,array('query'=>array()))."&Branch='+$(this).val()".($_GET['Template']!=NULL?"+'&Template=".$_GET['Template']."'":NULL)."".($_GET['View']!=NULL?"+'&View=".$_GET['View']."'":NULL)."".($_GET['Search']!=NULL?"+'&Search=".$_GET['Search']."'":NULL).";
				});
			});
		";

		$renew_status_filters = [
			'active'	=>	['label'	=>	'Currently Active'],
			'pending'	=>	['label'	=>	'Pending'],
			'expire'	=>	['label'	=>	'Expired'],
			'cancel'	=>	['label'	=>	'Cancelled'],
			'renew'		=>	['label'	=>	'Renews Now'],
		];

		//-- Do
		if($_GET['Do']=='cancel') {
			$result = $class_renew->cancel(PAGE_id);

			if($result['success']) {
				$zulu->notification_set("Subscription was cancelled successfully.",1);
			} else {
				$zulu->notification_set($result['reason'],2);
			}
			header("Location: ".$zulu->link_page(PAGE_file,['self'=>true,'filter'=>['Do','id']]));
			exit;
		}
		if($_GET['Do']=='cancel_undo') {
			$result = $class_renew->cancel_undo(PAGE_id);

			if($result['success']) {
				$zulu->notification_set("Subscription cancellation was undone successfully.",1);
			} else {
				$zulu->notification_set($result['reason'],2);
			}
			header("Location: ".$zulu->link_page(PAGE_file,['self'=>true,'filter'=>['Do','id']]));
			exit;
		}
		if($_GET['Do']=='delete') {
			$result = $class_renew->delete(PAGE_id);

			if($result['success']) {
				$zulu->notification_set("Subscription was deleted successfully.",1);
			} else {
				$zulu->notification_set($result['reason'],2);
			}
			header("Location: ".$zulu->link_page(PAGE_file,['self'=>true,'filter'=>['Do','id']]));
			exit;
		}

		//-- Process
		if($_POST&&$_POST['execute']!=NULL) {
			if($_POST['execute']=='cancel') {
				foreach($_POST['action'] as $id=>$val) {
					if($val>0) {
						$result = $class_renew->cancel($id);
						if(!$result['success']) {
							$error_log[] = $result['reason'];
						}
					}
				}
				if(count($error_log)) {
					$zulu->notification_set("Some errors occurred while cancelling subscriptions.<br><br>".implode("<br>",$error_log),1);
				} else {
					$zulu->notification_set("Subscriptions were cancelled successfully.",1);
				}
				header("Location: ".$zulu->link_page(PAGE_file,['self'=>true]));
				exit;
			}
			if($_POST['execute']=='delete') {
				foreach($_POST['action'] as $id=>$val) {
					if($val>0) {
						$result = $class_renew->delete($id);
						if(!$result['success']) {
							$error_log[] = $result['reason'];
						}
					}
				}
				if(count($error_log)) {
					$zulu->notification_set("Some errors occurred while deleting subscriptions.<br><br>".implode("<br>",$error_log),1);
				} else {
					$zulu->notification_set("Subscriptions were deleted successfully.",1);
				}
				header("Location: ".$zulu->link_page(PAGE_file,['self'=>true]));
				exit;
			}
			if($_POST['execute']=='set_renew_date') {
				$newdate = zulu::dateEncode($_POST['input_action']['renew_date']);
				if($newdate<=0) {
					$zulu->fatal_error("Date error","The date specified was invalid.");
				}
				foreach($_POST['action'] as $id=>$val) {
					if($val>0) {
						$upd_result = $class_renew->renew_edit($id,['renew_next'=>$newdate]);
						if(!$upd_result['success']) {
							$error_log[] = "Failed to process subscription ID #".$id;
						}
					}
				}
				$zulu->notification_set("Subscriptions renewal dates were updated successfully to ".$zulu->date($newdate).".",1);
				header("Location: ".$zulu->link_page(PAGE_file,['self'=>true]));
				exit;
			}
			if($_POST['execute']=='generate_renewal') {
				$success = true;
				foreach($_POST['action'] as $id=>$val) {
					if($val>0) {
						$this_result = $class_renew->generate_renewal_order($id);
						$log_run[] = $this_result['reason'];
						if(!$this_result['success']) $success = false;
					}
				}
				if($success) {
					$zulu->notification_set("Subscription(s) were processed successfully.<br>".implode("<br>",$log_run),1);
				} else {
					$zulu->notification_set("Subscription(s) failed to process.<br>".implode("<br>",$log_run),2);
				}
				header("Location: ".$zulu->link_page(PAGE_file,['self'=>true]));
				exit;
			}
			if($_POST['execute']=='process') {
				foreach($_POST['action'] as $id=>$val) {
					if($val>0) {
						if(!$class_renew->process(NULL,['id'=>$id])) {
							$error_log[] = "Failed to process subscription ID #".$id;
						}
					}
				}
				$zulu->notification_set("Subscription(s) were processed successfully.",1);
				header("Location: ".$zulu->link_page(PAGE_file,['self'=>true]));
				exit;
			}
		}

		$form_edit = new form;
		$zulu->template->config->select_all = true;
		$zulu->template->config->date_select = true;

		$table_column[] = array($form_edit->input_html("checkbox","selectall",1,array("class"=>['toggle-input'])),array('class'=>array('')));
		$table_column[] = array("Status",array('class'=>array('')));
		$table_column[] = array("Auto-renew",array('class'=>array('')));
		$table_column[] = array("Contact",array('class'=>array('')));
		$table_column[] = array("Forms",array('class'=>array('')));
		$table_column[] = array("Subscription",array('class'=>array('')));
		$table_column[] = array("Amount",array('class'=>array('')));
		$table_column[] = array("Frequency",array('class'=>array('')));
		$table_column[] = array("Period Start");
		$table_column[] = array("Period End");
		$table_column[] = array("Renews",array('class'=>array('')));
		$table_column[] = array("Added",array('class'=>array('right')));
		$table_column[] = array("Actions",array('class'=>array('right')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);

		$template_data = $class_renew->template_data(['status'=>1]);
		$template_array = [0=>'Any',-1=>'No Template'];
		foreach($template_data as $tpl) {
			$template_array[$tpl['id']] = stripslashes($tpl['title']);
		}

		$wSQL = [];
		$wSQL['filter'] = $_GET['filter'];
		$wSQL['filter_custom'] = $_GET['filter_custom'];

		if(isset($_GET['Template'])) {
			$template_id = $db->escape_string($_GET['Template']);
			$wSQL['template_id'] = $template_id;
		}
		if(!isset($_GET['View'])) {
			$_GET['View'] = 'active';
		}
		if(trim($_GET['View']) != NULL) {
			$wSQL[$_GET['View']] = true;
		}
		if($_GET['Search'] != NULL) {
			$wSQL['search'] = $db->escape_string($_GET['Search']);
		}

		$form_post = new form_post;
		$form_post->load_config();

		$wSQL['sort'] = 'r.status ASC, r.stat_add DESC';
		$start = ($_GET['Pg']>1?$zulu->config->page_max_page*($_GET['Pg']-1):0);
		$i = 1;
		$data_row = $class_renew->renew_data($wSQL);

		$all_count = count($data_row);
		$zulu->vars->renew_count = $all_count;

		foreach($data_row as $key=>$row) {

			$class_renew->get($row['renew_id']);
			$row = $class_renew->vars->data;
			$renew_meta = $class_renew->vars->meta;

			if(($key >= $start && $i <= $zulu->config->page_max_page) || $_GET['Do']=='Export') {

				if($row['renew_client_id']>0) {
					$client_data = $class_client->client_data(array('id'=>$row['renew_client_id']));
					$client_meta = $zulu->meta_array($class_client->client_meta($client_data['id']));
				} else {
					unset($client_data,$client_meta);
				}

				$status_data = $class_renew->renew_next_label();
				$next_renew_class = $status_data['class'];
				$next_renew = $status_data['label'];

				$has_sale = $class_renew->has_sale($row['renew_id']);
				$status_data = $class_renew->status_info(array('sale'=>true));

				$renew_status = '<span class="'.$status_data['class'].'"><span class="fas '.$status_data['icon'].'"></span> '.$status_data['label'].($status_data['label']=='Expired'&&$has_sale['has_sale']?' (Pending Renewal)':NULL).'</span>';

				$auto_data = $class_renew->status_autorenew();
				$auto_status = '<span class="'.$auto_data['class'].' opt-bord"><span class="fas '.$auto_data['icon'].'"></span> '.$auto_data['label_short'].'</span>';

				//-- custom
				$stat_link = NULL;
				if($class_renew->can_activate()['success']) {
					$stat_link = "&nbsp;&nbsp;<a title=\"Warning: This will override the default process, if payment is due on this membership, activating will override the requirement for payment and the monies owed will still be outstanding.\" class=\"btn btn-success btn-xs\" href=\"".$zulu->link_page(PAGE_file,['query'=>$_GET+['Do'=>'Activate','id'=>$row['renew_id']]])."\"><i class=\"fas fa-check\"></i> Activate</a>";
				}
				//--

				$button_manage = [
					'edit'=>
						['label'=>'Edit','icon'=>'pencil','class'=>'primary','link'=>$zulu->link_page(PAGE_file,array('query'=>array('id'=>$row['renew_id'],'Action'=>'edit')))],
					'delete'=>
						['label'=>'Delete','icon'=>'times','class'=>'danger','link'=>$zulu->link_page(PAGE_file,array('self'=>true,'query'=>array('id'=>$row['renew_id'],'Do'=>'delete')))]
				];
				if($row['status']==3||$row['status']==0) {
					//--
				} else {
					unset($button_manage['delete']);
				}

				if($row['status']==1) {
					if(!$class_renew->is_mark_cancel()) {
						$button_manage['cancel'] = ['label'=>'Cancel','icon'=>'times','class'=>'warning','link'=>$zulu->link_page(PAGE_file,array('self'=>true,'query'=>array('id'=>$row['renew_id'],'Do'=>'cancel')))];
					}
					if($class_renew->is_mark_cancel() && !$class_renew->is_cancel_lock()) {
						$button_manage['cancel'] = ['label'=>'Uncancel','icon'=>'reply','class'=>'warning','link'=>$zulu->link_page(PAGE_file,array('self'=>true,'query'=>array('id'=>$row['renew_id'],'Do'=>'cancel_undo')))];
					}
				}


				if($has_sale['has_sale']) {
					$button_manage['renew_sale'] = ['label'=>'View Renewal Order','icon'=>'money-bill','link'=>$zulu->link_page('sale',array('query'=>array('id'=>$has_sale['id'],'Action'=>'edit','Method'=>'View')))];
				}
				$button_data = [
				'manage'=>
					['label'=>'Manage','icon'=>'cogs','class'=>'default','link'=>'#','option'=>$button_manage],
				];
				rsort($button_data);

				$renew_at_label = '';
				if($row['renew_next']!=$row['period_end']) {
					$renew_at_label = "<br><small>".$zulu->date($row['renew_next'],'d/m/Y')."</small>";
				}

				$table_row[] = array("content" => array(
					array($form_edit->input_html("checkbox","action[".$row['id']."]",1,array('checked'=>($_POST['action'][$row['id']]>0?true:false),'class'=>array('action'))),array('class'=>array('action-field'))),
					array($renew_status.$stat_link),
					array($auto_status),
					array($class_client->admin_link($row['renew_client_id'])),
					array($form_post->object_count_link(['object_table'=>'renew','record_table'=>'renew_template','object_id'=>$row['id']])),
					array(stripslashes($row['title'])),
					array(LOCALE_currency_symbol.zulu::dollar(($row['quantity']*$row['price']))),
					array($row['renew_interval']." ".zulu::time_scale($row['renew_scale'],'pl')),
					array($zulu->date($row['period_start'],'d/m/Y')),
					array($zulu->date($row['period_end'],'d/m/Y')),
					array("<span class=\"".$next_renew_class."\">".$next_renew."</span>"),
					array(zulu::time_history($row['stat_add'])),
					array($zulu->button_render(
						$button_data
					),array('class'=>array('right')))
				));
				$renew_log = $class_renew->renew_log($row['renew_id']);
				$export_data[] = [
					$row['renew_id'],
					$client_data['reference'],
					str_replace(',',';',stripslashes($client_data['name'])),
					str_replace(',',';',stripslashes($client_data['company'])),
					$client_data['email'],
					"'".$client_data['phone']."'",
					str_replace(',',';',$client_meta['ship_address']),
					str_replace(',',';',$client_meta['ship_suburb']),
					str_replace(',',';',$client_meta['ship_city']),
					str_replace(',',';',$client_meta['ship_post']),
					str_replace(',',';',$client_meta['ship_country']),
					stripslashes($row['title']),
					zulu::date($row['renew_first'],'d/m/Y'),
					zulu::date(($row['renew_last']>0?$row['renew_last']:'Never'),'d/m/Y'),
					($row['lifetime']>0?"Lifetime":$zulu->date($row['renew_next'],'d/m/Y')),
					$row['price'],
					count($renew_log),
					strip_tags($status_data['label'])
				];
				unset($next_renew_class,$client_data,$stat_link);
				$i++;
			}

			//-- Income calculation
			if($row['status']==1) {
				$yearly = 0;
				if($row['renew_scale']=='d') {
					$yearly = $row['price']*365;
				}
				if($row['renew_scale']=='w') {
					$yearly = $row['price']*52;
				}
				if($row['renew_scale']=='m') {
					$yearly = $row['price']*12;
				}
				if($row['renew_scale']=='y') {
					$yearly = $row['price'];
				}
				$total_income += $yearly;
			}
		}

		//-- Calc Summary
		$total_year = $total_income;
		$total_week = $total_income/52;
		$total_month = $total_income/12;

		//-- Activate?
		if($_GET['Do']=='Activate') {
			$start_id = $_GET['id'];

			$data = $class_renew->renew_data(['id'=>$start_id]);
			$client_data = $class_client->client_data(['id'=>$data['client_id']]);
			$data = $class_renew->template_merge($data);

			if($data['status']!=0) {
				$zulu->notification_set("This subscription is already active / expired.",2);
			} else {
				$result = $class_renew->renew_edit($start_id,['status'=>1]);
				if($result['success']) {
					$zulu->notification_set("Subscription was activated.",1);

					//-- update parent
					if($data['parent_id']>0) {
						$result = $class_renew->renew_edit($data['parent_id'],['status'=>3,'renew_next'=>time(),'auto_renew'=>0]);
					}

					//-- email them
					$message = "Hello ".$client_data['name_full'].",<br><br>We have activated your membership for '".$data['title']."'.<br><br>
					<b>Plan:</b> ".$data['title']."<br>
					<b>Next Renewal:</b> ".$zulu->date($data['renew_next'],'d/m/Y')."<br>
					<b>Fee:</b> $".$data['price']." Per ".$class_renew->config->renew_scale[$data['renew_scale']]."<br>
					";

					$zulu->mail_send($client_data['email'],"Your membership was activated",$message,'',false,['object'=>'membership','object_id'=>$data['id'],'no_branding'=>true,'toggle'=>1,'user_id'=>$data['user_id']]);
				} else {
					$zulu->notification_set("Could not activate the subscription.",2);
				}
			}

			$qry = $_GET;
			unset($qry['Do'],$qry['id']);
			header("Location: ".$zulu->link_page(PAGE_file,['query'=>$qry]));
			exit;
		}

		//--Export?
		if($_GET['Do']=='Export') {
			$data['header'] = ['ID','Client Reference','Client Name','Client Company','Email','Phone','Address','Suburb','City','Postcode','Country','Subscription','Joined','Renew Last','Renew Next','Price','Total Renewals','Status'];
			$data['body'] = $export_data;
			$exp = $zulu->export_csv($data,['name'=>"Memberships"]);
			if($exp['success']) {
				$zulu->notification_set("Export was generated successfully.<br><br>Download here: <a href=\"".$exp['url']."\" target=\"_blank\">".$exp['url']."</a>",1);
			} else {
				$zulu->notification_set("An error occurred.",2);
			}
			header("Location: ".$_SERVER['HTTP_REFERER']);
			exit;
		}

		//--
		$custom_count_status = $class_renew->subscription_count('0',$_GET['Sort']);
		$custom_count = $class_renew->subscription_count('0');

		$zulu->template->body = $zulu->table_render($table_row,0,array('js_table'=>false,'data_table'=>false));
		$pagination = $zulu->pagination($_GET['Pg'],['count'=>count($data_row),'link'=>$zulu->link_page(PAGE_file,array('query'=>array('Template'=>$_GET['Template'],'View'=>$_GET['View'],'Branch'=>$_GET['Branch'],'Search'=>$_GET['Search'])))]);
		$zulu->nav->title = PAGE_name;
	}
	if(PAGE_action=='delete') { //delete
		if($class_renew->delete(PAGE_id)) {
			$zulu->notification_set("Subscription removed successfully.",1);
			header("Location: ".$zulu->link_page(PAGE_file,array('query'=>array('Sort'=>$_GET['Sort']))));
		} else {
			$zulu->notification_set("A database error occurred.",2);
		}
	}
	if(PAGE_action=='edit') { //edit page

		//--adjust credit
		if($_POST['do'] == 'credit') {
			if(isset($_POST['submit_sub'])) {
				$_POST['credit'] *= -1;
			}
			$data = [
				'value'		=>	$_POST['credit'],
				'note'		=>	$_POST['note']
			];
			$result = $class_renew->credit_adjust(PAGE_id,$data);

			$zulu->notification_set("Credit was adjusted successfully.",1);
			header("Location: ".$zulu->link_page(PAGE_file,array("query"=>array("Action"=>'edit','id'=>PAGE_id))));
			exit;
		}

		$form_edit = new form;

		$zulu->template->config->date_select = true;
		$zulu->template->js_code[] = "
      	$(document).ready(function(){
			$('.input-renew-scale').trigger('change');
	  	});
		$(document).on('change','.input-renew-scale',function() {
			var scale_val = $('.input-renew-scale').val();
			if(scale_val=='d') {
				var output_string = 'day';
			}
			if(scale_val=='w') {
				var output_string = 'week';
			}
			if(scale_val=='m') {
				var output_string = 'month';
			}
			if(scale_val=='y') {
				var output_string = 'year';
			}
			$('.price-per-label').html('Per ' + output_string);
			return false;
		});
		";

		if(PAGE_id<1) {
			$id = 0;
			$new = true;
			$zulu->nav->breadcrumb['New Subscription'] = array();
			$zulu->nav->title = "New Subscription";
		} else {
			$id = ($_POST['id']>0?$_POST['id']:($_GET['id']>0?$_GET['id']:0));

			$class_renew->get($id);

			$renew_data = $class_renew->vars->data;
			$renew_meta = $class_renew->vars->meta;

			//$can_renew = $class_renew->can_renew();

			if(!$_POST) {
				foreach($renew_data as $key=>$val) {
					$_POST[$key] = $val;
				}
			}

			$period_start = $class_renew->label_date($renew_data['period_start']);
			$period_end = $class_renew->label_date($renew_data['period_end']);
			$period_next = $class_renew->label_date($renew_data['renew_next']);
			if($renew_data['period_end']!=$renew_data['renew_next']) {
				$show_next_renewal = true;
			}

			if($renew_data['trial_expire']>0) {
				$trial_start = $class_renew->label_date($renew_data['trial_start'],['justdate'=>true]);
				$trial_expire = $class_renew->label_date($renew_data['trial_expire'],['justdate'=>true]);
				$trial_dates = $trial_start." to ".$trial_expire;
			}

			$zulu->nav->breadcrumb['Edit Subscription'] = array();
			$zulu->nav->breadcrumb[stripslashes($renew_data['title'])] = array();
			$zulu->nav->title = "Edit Subscription";

			$sale_id = $class_sale->sale_line_parent(0,['object'=>'membership','object_id'=>$renew_data['id']]);
			$balance = $class_sale->sale_balance($sale_id);
			$prefix_0 = "<a href=\"".$zulu->link_page('sale',['query'=>['Method'=>'View','Action'=>'edit','id'=>$sale_id]])."\" target=\"_blank\">($".$zulu->dollar($balance,2)." Due)</a>";

			$action_bt[] = "<p><a href=\"".$zulu->link_page('client',['query'=>['Action'=>'edit','Method'=>'View','id'=>$renew_data['client_id']]])."\" target=\"_blank\"><button type=\"button\" class=\"btn btn-block btn-warning\"><i class=\"fas fa-user\"></i> View Client</button></a></p>";
			if($sale_id>0) {
				$sale_bal = $class_sale->sale_balance($sale_id);
				$action_bt[] = "<p><a href=\"".$zulu->link_page('sale',['query'=>['Action'=>'edit','Method'=>'View','id'=>$sale_id]])."\" target=\"_blank\"><button type=\"button\" class=\"btn btn-block btn-info\"><i class=\"far fa-money-bill\"></i> View Original Sale".($sale_bal>0?" ($".$zulu->dollar($sale_bal)." Due)":NULL)."</button></a></p>";
			}
			if($can_renew) {
				$action_bt[] = "<p><a href=\"".$zulu->link_page(PAGE_file,['query'=>['Action'=>'edit','Do'=>'renew_sale','id'=>PAGE_id]])."\"><button type=\"button\" class=\"btn btn-block btn-danger confirm bt-confirm\"><i class=\"fas fa-sync-alt\"></i> Generate Renewal Sale</button></a></p>";
			}
		}

		$template_options[] = "Custom Subscription";
		$template_data = $class_renew->template_data();
		foreach($template_data as $template_row) {
			$types = explode(",",$template_row['renew_type']);
			if(count($types)>1) {
				foreach($types as $memtype) {
					$template_options[$template_row['id'].'_'.$memtype] = stripslashes($template_row['title'])." (".$class_renew->config->renew_model[$memtype].")";
					$template_types[$template_row['id']][] = $memtype;
				}
			} else {
				$template_options[$template_row['id']] = stripslashes($template_row['title']);
			}
		}
		if($_POST['template_id']>0) {
			if(count($template_types[$_POST['template_id']])>1) {
				$_POST['template_selector'] = $_POST['template_id'].'_'.$_POST['renew_type'];
			} else {
				$_POST['template_selector'] = $_POST['template_id'];
			}
		}

		//Product List
		$prod_options = [0=>'- None'];
		$parr = $class_product->product_array();
		if(count($parr)>0) {
			$prod_options += $parr;
		}

		$_POST['total_line'] = zulu::dollar(($renew_data['quantity']*$renew_data['price']));

		//-- Renewal
		if($_GET['Do']=='renew_sale'&&$can_renew&&!$new) {
			$qry = ['id'=>PAGE_id];
			if($_GET['Autobill']>0) {
				$qry['autobill'] = true;
			}
			$class_renew->process(NULL,$qry);

			$zulu->notification_set("Renewal processed. ".$class_renew->vars->link,1);
			header("Location: ".$zulu->link_page(PAGE_file,['query'=>['Action'=>'edit','id'=>PAGE_id]]));
			exit;
		}

		//-- Form Submit
		if($_POST['action']=='edit') {
			$form_edit->valid = true;
			$error_log = [];

			if($new) {
				if(!isset($_POST['period_start']) || trim($_POST['period_start'])==NULL) {
					$error_log[] = "Please enter a start date for this subcription.";
				} elseif($zulu->dateEncode($_POST['period_start'])==0) {
					$error_log[] = "Please enter a start date for this subcription.";
				}
			}
			if($_POST['client_id']<=0) {
				$error_log[] = "Please select a contact to attach this subcription to.";
			}
			if(trim($_POST['title'])==NULL && $_POST['template_id']==0) {
				$error_log[] = "Please enter a name for this subscription.";
			}
			if($_POST['quantity']<=0 && $_POST['template_id']==0) {
				$error_log[] = "Please enter a quantity greater than zero.";
			}
			if(!empty($error_log)) {
				$zulu->notification_set("Some errors occurred when saving:<br><br>".implode("<br>",$error_log),2);
				$form_edit->valid = false;
			}

			if($form_edit->valid) {
				$data['opt_bill'] = $_POST['opt_bill'];
				$data['status'] = ($_POST['status']==1?1:$_POST['status']);
				$data['title'] = addslashes($_POST['title']);
				$data['description'] = addslashes($_POST['description']);
				//$data['team_id'] = $_POST['team_id'];
				$data['client_id'] = $_POST['client_id'];
				$data['product_id'] = $_POST['product_id'];
				$data['quantity'] = $_POST['quantity'];
				$data['price'] = $_POST['price'];

				if($new) {
					$data['period_start'] = zulu::dateEncode($_POST['period_start']);
					$data['renew_scale'] = $_POST['renew_scale'];
					$data['renew_interval'] = $_POST['renew_interval'];
					$data['renew_action'] = $_POST['renew_action'];
					$data['template_id'] = $_POST['template_id'];
				}

				$data['auto_renew'] = ($_POST['auto_renew']==1?1:0);
				$data['lifetime'] = ($_POST['lifetime']==1?1:0);
				$data_raw = $data;

				if(strstr($_POST['template_id'],"_")) {
					$tpl_split = split("_",$_POST['template_id']);
					$data['template_id'] = $tpl_split[0];
					$data['renew_type'] = $tpl_split[1];
				}

				$id = (PAGE_id>0?PAGE_id:0);
				$data = $class_renew->renew_edit($id,$data,['validate'=>true]);

				if($data['success']) {
					$client_info = $class_client->client_data(['id'=>$_POST['client_id']]);
					if($id<=0) {
						$loginsert = [
							'client_id'		=>	$_POST['client_id'],
							'user_id'		=>	$class_user->authorised->id,
							'note'			=>	"New subscription created by ".$class_user->authorised->username." on ".date("jS M g:ia").".",
						];
						$class_client->client_log_new($loginsert);
					}
					if($_POST['opt_bill'] == '1'&&$new&&$data_raw['period_start']<=strtotime('today')) {
						if($_POST['template_id'] > 0) {
							$template_data = $class_renew->template_data(['id'=>$_POST['template_id']]);
							$_POST['title'] = $template_data['title'];
							$_POST['price'] = $template_data['price'];
						}
						$renew_new = $class_renew->renew_data(['id'=>$data['id']]);
						$sale_data = array('name'=>$client_info['name'],'client_id'=>$client_info['id'],'user_id'=>$class_user->authorised->id,'date'=>$zulu->dateDecode(time()),'date_due'=>$zulu->dateDecode(time()),'email'=>$client_info['email'],'coupon_id'=>0,'pay_method'=>'bank');
						$sale_data['line'][] = array(
							'description'	=>	$_POST['title']."\nFirst Period: ".$zulu->date($renew_new['period_start'],'d/m/Y')." to ".$zulu->date($renew_new['period_end'],'d/m/Y')."\n\n".$_POST['description'],
							'custom'		=>	serialize(['renew_id'=>$data['id'],'renew_first'=>true]),
							'quantity'		=>	'1',
							'price'			=>	$_POST['price'],
							'discount'		=>	0
						);
						$sale_token = $class_sale->sale_generate($sale_data);
						$class_sale->complete($sale_token['id']);
					}

					$zulu->notification_set("Subscription ".(!$new?"updated":"created")." successfully.",1);
					header("Location: ".$zulu->link_page(PAGE_file));
					exit;
				} else {
					$zulu->notification_set("A database error occurred.",2);
				}
			}
		}

		//-- Renewal Log
		if($renew_data['renew_type']=='conc') {
			$current_credit = $renew_meta['credit'];
			$table_column[] = array("Date",array('class'=>array('')));
			$table_column[] = array("Adjustment",array('class'=>array('')));
			$table_column[] = array("Note",array('class'=>array('')));
			$table_column[] = array("Actions",array('class'=>array('center')));
			$table_row[] = array(
					"header"	=>	 true,
					"class"		=>	"",
					"content"	=>	$table_column);

			$data_row = $class_renew->credit_data(array('renew_id'=>PAGE_id,'sort'=>'stat_add DESC','limit'=>20));
			foreach($data_row as $row) {

				$val = ($row['value']<=0?"<span class=\"opt opt-danger\"><span class=\"fas fa-chevron-down\"></span> ".$row['value']."</span>":"<span class=\"opt opt-success\"><span class=\"fas fa-chevron-up\"></span> ".$row['value']."</span>");

				$table_row[] = array("content" => array(
					array(zulu::date($row['stat_add'],"d/m/Y")),
					array($val),
					array("<span title=\"".stripslashes($row['note'])."\">".zulu::shorten(stripslashes($row['note']),30)."</span>"),
					array(($row['object_id']>0?"<a href=\"".$zulu->object_link($row['object'],$row['object_id'])."\"><span class=\"fas fa-link\"></span> ".$zulu->vars->link_title."</a>":NULL),array('class'=>array('center')))
				));
			}
			if(count($data_row)<=0) {
				$zulu->template->renewal_log = "<span class=\"color-grey\"><span class=\"fas fa-times\"></span> No credits yet.</span>";
			} else {
				$zulu->template->renewal_log = $zulu->table_render($table_row,0,array('class'=>'file','html_id'=>'credit_log','js_table'=>false));
			}
		} else {
			$log_data = $class_renew->renew_log(PAGE_id);
			if(count($log_data)>0) {

				$table_column[] = array("Processed",array('class'=>array('')));
				$table_column[] = array("From",array('class'=>array('')));
				$table_column[] = array("Till",array('class'=>array('')));
				$table_column[] = array("Item",array('class'=>array('')));

				$table_row[] = array(
						"header"	=>	 true,
						"class"		=>	"",
						"content"	=>	$table_column);

				foreach($log_data as $row) {
					if(trim($row['object'])!=NULL) {
						$object_link = $zulu->object_link($row['object'],$row['object_id']);
						$object_title = $zulu->object_name($row['object'],$row['object_id'],true);
						$link = "<a href=\"".$object_link."\"><span class=\"fas fa-link\"></span> ".$object_title."</a>";
						if($row['object']=='sale_line') {
							$sale_id = $class_sale->sale_id_from_line_id($row['object_id']);
							if($sale_id>0) {
								$status = $class_sale->sale_status_info(NULL,['id'=>$sale_id]);
								$link .= "&nbsp;&nbsp;<span class=\"opt opt-".$status['class']."\"><i class=\"far ".$status['icon']."\"></i> ".$status['label']."</span>";
							}
						}
					}
					$table_row[] = array("content" => array(
						array(zulu::time_history($row['renew_time'])),
						array(zulu::date($row['renew_from'],'d/m/Y')),
						array(zulu::date($row['renew_to'],'d/m/Y')),
						array($zulu->nl($link))
					));
				}

				$zulu->template->renewal_log = $zulu->table_render($table_row,0,array('class'=>'log','data_table'=>false));
			} else {
				$zulu->template->renewal_log = "<span class=\"color-grey\"><span class=\"fas fa-times\"></span> No renewals yet.</span>";
			}
		}

		//-- Jquery
		$zulu->template->jquery[] = "
		$(\".price-field\").keyup(function() {
			var price = $(\"input[name='price']\").val();
			var quantity = $(\"input[name='quantity']\").val();
			var total = price*quantity;
			$(\".label-price\").html(total.toFixed(2));
			return false;
		});

		$(\".bt-select\").change(function() {
			var new_price = 0;
			var pid = $(this).val();
			var cid = $('.input-client').val();
			$.get(\"".$zulu->link_page('bill',['query'=>['Action'=>'product_price']])."&id=\" + pid + \"&client_id=\" + cid,function(data) {
				var parsed = JSON.parse(data);
				$(\"input[name='title']\").val(parsed.name);
				$(\"input[name='quantity']\").val(1);
				$(\"input[name='price']\").val(parsed.price);
				$(\"input[name='price']\").trigger(\"keyup\");
			});
			return false;
		});

		$(\"select[name='template_id']\").change(function() {
			var val = $(this).val();
			var custom_div = $('#custom_section');
			if(val != '' && !custom_div.is(':hidden')) {
				custom_div.slideUp(300);
			} else if(val == 0) {
				custom_div.slideDown(300);
			}
		});
		if($(\"select[name='template_id']\").val() > 0) {
			$('#custom_section').hide();
		}
		";
	}

	if(PAGE_action == "Process") {
		$template = $db->escape_string($_GET['Template']);
		$form_edit = new form;

		$zulu->template->css_file[] = "//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css";
		$zulu->template->js_file[] = "//code.jquery.com/ui/1.11.4/jquery-ui.js";
		$zulu->template->js_code[] = "
      	$(document).ready(function(){
			$(\".input-date\").datepicker({ dateFormat: \"dd/mm/yy\" });
		});";

		if(!isset($_POST['date'])) {
			$_POST['date'] = date('d/m/Y',strtotime('first day of this month'));
		}
		if(!isset($_POST['sale_date_due'])) {
			$_POST['sale_date'] = date('d/m/Y');
			$_POST['sale_date_due'] = date('d/m/Y',strtotime("+7 days"));
		}

		if($_POST['do']=='submit') {

			foreach($_POST['action'] as $id=>$val) {
				if($val>0) {

					$class_renew->get($id);
					$this_output = $class_renew->process_row(['renew_to'=>$_POST['date'],'sale_date_due'=>$_POST['sale_date_due'],'sale_date'=>$_POST['sale_date'],'cycle'=>$_POST['renew_cycles'] ]);



					$nfc_row[] = "<b>Subscription to ".$class_renew->vars->data['title']." for ".$class_client->admin_link($class_renew->vars->data['client_id']).":</b><br>".implode("<br>",$this_output['log'])."<br><br>";
					/*foreach($class_renew->vars->process_output as $output_data) { //-- Loop through result
						if(!$output_data['skip'] && !$output_data['skip_date']) {
							if($output_data['create_id']<=0) {
								$nfc_row[] = "Subscription to ".$output_data['title']." for ".$output_data['client_name']." - Renewed to ".$output_data['renew_to'];
							} else {
								$nfc_row[] = "Subscription to ".$output_data['title']." for ".$output_data['client_name']." - <a href=\"".$zulu->object_link($output_data['create'],$output_data['create_id'])."\" target=\"_blank\">View linked ".$output_data['create']."</a> - Renewed to ".$output_data['renew_to'];
							}
						} elseif($output_data['skip_date']) {
							$nfc_row[] = "Subscription to ".$output_data['title']." for ".$output_data['client_name']." - Skipped, not due for date selected";
						} else {
							$nfc_row[] = "Subscription to ".$output_data['title']." for ".$output_data['client_name']." - Skipped, renewal sale pending payment";
						}
					}*/
				}
			}

			$zulu->notification_set("Subscriptions processed. Items that generate <b>sales</b> will first need to be paid. Subscriptions with auto-renew will automatically activate if payment is successful.".(count($nfc_row)>0?"<br><br>".implode("<br>",$nfc_row):NULL),1);

			header("Location: ".$zulu->link_page(PAGE_file,['query'=>['Action'=>'Process']]));
			exit;
		}

		$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'basket'));
		$zulu->nav->title = "Process Subscriptions";
		$zulu->nav->breadcrumb["Process Subscriptions"] = array();

		//-- Renew
		$form_edit = new form;
		$zulu->template->config->select_all = true;
		$table_column[] = array($form_edit->input_html("checkbox","selectall",1,array("class"=>['toggle-input'])),array('class'=>array('')));
		$table_column[] = array("Status");
		$table_column[] = array("Client");
		$table_column[] = array("Item");
		$table_column[] = array("Amount");
		$table_column[] = array("Frequency");
		$table_column[] = array("Renewal Due");
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);


		$wSQL = [
			'status'		=>	1,
		];
		$template_data = $class_renew->template_data();
		$r_data = $class_renew->renew_data($wSQL);
		$all_count = count($r_data);
		$zulu->vars->renew_count = $all_count;

		if($_GET['Template'] > 0) {
			$template_id = $db->escape_string($_GET['Template']);
			$wSQL['template_id'] = $template_id;
		} else {
			//$wSQL['template_id'] = $template_data[0]['id'];
		}

		if($_GET['View'] != NULL) {
			$wSQL[$_GET['View']] = true;
		}
		if($_GET['Search'] != NULL) {
			$wSQL['search'] = $_GET['Search'];
		}

		$form_post = new form_post;
		$form_post->load_config();

		$wSQL['sort'] = 'r.status ASC, r.stat_add DESC';
		$start = ($_GET['Pg']>1?$zulu->config->page_max_page*($_GET['Pg']-1):0);
		$i = 1;
		$data_row = $class_renew->renew_data($wSQL);
		foreach($data_row as $key=>$row) {
			$renew_meta = $zulu->meta_array($class_renew->renew_meta($row['id']));
			if(($key >= $start && $i <= $zulu->config->page_max_page) || $_GET['Do']=='Export') {
				$row = $class_renew->template_merge($row);
				if($row['renew_client_id']>0) {
					$client_data = $class_client->client_data(array('id'=>$row['renew_client_id']));
					$client_meta = $zulu->meta_array($class_client->client_meta($client_data['id']));
				} else {
					unset($client_data,$client_meta);
				}
				if($row['renew_scale']=='m') {
					$this_mo = date('m');
					if(date('Ym',strtotime("+1 Month"))==date('Ym',$row['renew_next'])) {
						$next_renew = "Next Month (".date("j/m/Y",$row['renew_next']).")";
						$next_renew_class = 'green';
					} else {
						$next_renew = date("d/m/Y",$row['renew_next']);
					}
				} else {
					$next_renew = date("d/m/Y",$row['renew_next']);
				}
				if($row['renew_next']<strtotime('today')&&$row['renew_status']!=3) {
					$next_renew = 'Expired ('.$next_renew.')';
					$next_renew_class = 'color-red';
				} elseif($row['renew_next']==strtotime('today')&&$row['renew_status']!=3) {
					$next_renew = 'Renews Today ('.$next_renew.')';
					$next_renew_class = 'color-red';
				} elseif($row['renew_status']==3) {
					$next_renew_class = 'color-grey';
					$next_renew = 'Expired ('.$next_renew.')';
				} elseif($row['renew_status']==0) {
					$next_renew_class = 'color-grey';
					$next_renew = 'Pending ('.$next_renew.')';
				}

				$has_sale = $class_renew->has_sale($row['id']);
				$status_data = $class_renew->status_info(array('id'=>$row['renew_id'],'sale'=>true));
				$renew_status = '<span class="'.$status_data['class'].'"><span class="fas '.$status_data['icon'].'"></span> '.$status_data['label'].($status_data['label']=='Expired'&&$has_sale['has_sale']?' (Pending Renewal)':NULL).'</span>';

				//-- custom
				if($status_data['label']!='Active'&&$status_data['label']!='Expired') {
					$stat_link = "&nbsp;&nbsp;<a title=\"Warning: This will override the default process, if payment is due on this membership, activating will override the requirement for payment and the monies owed will still be outstanding.\" class=\"btn btn-success btn-xs\" href=\"".$zulu->link_page(PAGE_file,['query'=>$_GET+['Do'=>'Activate','id'=>$row['renew_id']]])."\"><i class=\"fas fa-check\"></i> Activate</a>";
				}
				//--

				//-- concession updates
				if($row['renew_type']=='conc') {
					$next_renew_class = 'color-grey';
					$next_renew = $class_renew->credit_label($renew_meta['credit']);
				}
				$button_data = [['label'=>'Edit','icon'=>'pencil','class'=>'primary','link'=>$zulu->link_page(PAGE_file,array('query'=>array('id'=>$row['id'],'Action'=>'edit')))],
				['label'=>'','icon'=>'times','class'=>'danger','link'=>$zulu->link_page(PAGE_file,array('query'=>array('id'=>$row['id'],'Action'=>'delete','Sort'=>$_GET['Sort'])))]];
				if($has_sale['has_sale']) {
					$button_data[-1] = ['label'=>'View Renewal Order','icon'=>'search','class'=>'success','link'=>$zulu->link_page('sale',array('query'=>array('id'=>$has_sale['id'],'Action'=>'edit','Method'=>'View')))];
				}
				rsort($button_data);

				$table_row[] = array("content" => array(
					array($form_edit->input_html("checkbox","action[".$row['id']."]",1,array('checked'=>($_POST['action'][$row['id']]>0?true:false),'class'=>array('action'))),array('class'=>array('action-field'))),
					array($renew_status.$stat_link),
					array($class_client->admin_link($row['renew_client_id'])),
					array(stripslashes($row['title'])),
					array("$".zulu::dollar(($row['quantity']*$row['price']))),
					array($row['renew_interval']." ".zulu::time_scale($row['renew_scale'],'pl')),
					//array(zulu::date(($client_data['stat_add']>0?$client_data['stat_add']:$row['renew_first']),'d/m/Y')),
	//				array($zulu->date($row['renew_first'],'d/m/Y')),
					array(($row['lifetime']>0?"Lifetime":$next_renew),array('class'=>array($next_renew_class))),
				));
				$renew_log = $class_renew->renew_log($row['renew_id']);
				unset($next_renew_class,$client_data,$stat_link);
				$i++;
			}
		}

		//--
		$custom_count_status = $class_renew->subscription_count('0',$_GET['Sort']);
		$custom_count = $class_renew->subscription_count('0');

		$zulu->template->body = $zulu->table_render($table_row,0,array('js_table'=>false,'data_table'=>false));
		$pagination = $zulu->pagination($_GET['Pg'],['count'=>count($data_row),'link'=>$zulu->link_page(PAGE_file,array('query'=>array('Template'=>$_GET['Template'],'View'=>$_GET['View'],'Branch'=>$_GET['Branch'],'Search'=>$_GET['Search'])))]);
		$zulu->nav->title = PAGE_name;
	}
	if(PAGE_action == "template") {

		$form_edit = new form;

		$table_column[] = array("Title",array('class'=>array('')));
		$table_column[] = array("Product",array('class'=>array('')));
		$table_column[] = array("Amount",array('class'=>array('')));
		$table_column[] = array("Frequency",array('class'=>array('')));
		$table_column[] = array("Added",array('class'=>array('right')));
		$table_column[] = array("Actions",array('class'=>array('right')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);

		$data_row = $class_renew->template_data($wSQL);
		foreach($data_row as $row) {
			$prod_title = "<i>No linked product.</i>";
			if($row['product_id'] > 0) {
				$prod_data = $class_product->product_data(array('id'=>$row['product_id']));
				$prod_title = "<a href=\"".$zulu->link_page('product',array('query'=>array('id'=>$prod_data['id'],'Action'=>'edit')))."\">".stripslashes($prod_data['name'])."</a>";
			}

			$buttons = [
				//'link'=> $zulu->js_prompt_copy($zulu->front_link(FE_url.'members/membership/purchase/'.$row['token'].'/'),['label'=>'Copy Subscribe URL','button_render'=>true]),
				'edit'=>
					['label'=>'Edit','icon'=>'pencil','class'=>'primary','link'=>$zulu->link_page(PAGE_file,array('query'=>array('id'=>$row['id'],'Action'=>'template_edit')))],
				'delete'=>
					['label'=>'Delete','icon'=>'times','class'=>'danger','link'=>$zulu->link_page(PAGE_file,array('self'=>true,'query'=>array('id'=>$row['id'],'Action'=>'template_delete')))]
			];

			$table_row[] = array("content" => array(
				array(stripslashes($row['title'])),
				array($prod_title),
				array($class_renew->label_fee($row)),
				array($row['renew_interval']." ".zulu::time_scale($row['renew_scale'],'pl')),
				array(zulu::time_history($row['stat_add'])),
				array($zulu->button_render($buttons),array('class'=>array('right')))
			));
		}

		$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'basket'));
		$zulu->nav->title = "Subscription Types";
		$zulu->nav->breadcrumb["Subscription Types"] = array();
	}
	if(PAGE_action == "template_delete") {

		$class_renew->template_delete(PAGE_id);
		$zulu->notification_set("Template was removed.",1);

		header("Location: ".$zulu->link_page(PAGE_file,['query'=>['Action'=>'template']]));
		exit;
	}
	if(PAGE_action == "template_edit") {
		$form_edit = new form;

		$zulu->template->css_file[] = "//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css";
		$zulu->template->js_file[] = "//code.jquery.com/ui/1.11.4/jquery-ui.js";
		$zulu->template->js_code[] = "
      	$(document).ready(function(){
			$(\"input[name='renew_next']\").datepicker({ dateFormat: \"dd/mm/yy\" });
			$(\".price-field\").keyup(function() {
				var price = $(\"input[name='price']\").val();
				var quantity = $(\"input[name='quantity']\").val();
				var total = price*quantity;
				$(\".label-price\").html(total.toFixed(2));
				return false;
			});

			$(\".bt-select\").change(function() {
				var new_price = 0;
				var pid = $(this).val();
				$.get(\"".$zulu->link_page('bill',['query'=>['Action'=>'product_price']])."&id=\" + pid,function(data) {
					var parsed = JSON.parse(data);
					$(\"input[name='title']\").val(parsed.name);
					$(\"input[name='price']\").val(parsed.price);
					$(\"input[name='quantity']\").val(1);
					$(\"input[name='price']\").trigger(\"keyup\");
				});
				return false;
			});

			$(\".input-renew-type\").change(function() {
				var str = $(this).val();
				var n1 = str.indexOf(\"conc\");
				if(n1>=0) {
					$(\"#panel-conc\").show(500);
				} else {
					$(\"#panel-conc\").hide(500);
				}
				var n2 = str.indexOf(\"date\");
				if(n2>=0) {
					$(\"#panel-date\").show(500);
				} else {
					$(\"#panel-date\").hide(500);
				}
				return false;
			});
			$(\".input-renew-type\").trigger(\"change\");
			$('.input-renew-scale').trigger('change');
	  	});
		$(document).on('change','.input-renew-scale',function() {
			var scale_val = $('.input-renew-scale').val();
			if(scale_val=='d') {
				var output_string = 'day';
			}
			if(scale_val=='w') {
				var output_string = 'week';
			}
			if(scale_val=='m') {
				var output_string = 'month';
			}
			if(scale_val=='y') {
				var output_string = 'year';
			}
			$('.price-per-label').html('Per ' + output_string);
			return false;
		});";

		if(PAGE_id<1) {
			$id = 0;
			$new = true;
			$zulu->nav->breadcrumb['New Subscription Type'] = array();
			$zulu->nav->title = "New Subscription Type";
		} else {
			$id = ($_POST['id']>0?$_POST['id']:($_GET['id']>0?$_GET['id']:0));

			$renew_data = $class_renew->template_data(array('id'=>$id));
			$meta = $zulu->meta_array($class_renew->renew_template_meta($id));

			if(!$_POST) {
				foreach($renew_data as $key=>$val) {
					$_POST[$key] = $val;
				}
				foreach($meta as $key=>$val) {
					$_POST['meta'][$key] = $val;
				}
			}

			$zulu->nav->breadcrumb['Edit Subscription Type'] = array();
			$zulu->nav->breadcrumb[stripslashes($renew_data['title'])] = array();
			$zulu->nav->title = "Edit Subscription Type";

		}

		//Plan list
		$plan_list['*'] = '- All';
		$plan_list[''] = '- None';
		$row_plan = $class_renew->plan_array();
		if(count($row_plan)>0) {
			foreach($row_plan as $plan_id=>$plan) {
				$plan_list[$plan_id] = $plan;
			}
		}

		//Rule Array


		//Product List
		$prod_options = [0=>'- None'];
		$parr = $class_product->product_array();
		if(count($parr)>0) {
			$prod_options = $prod_options+$class_product->product_array();
		}

		$_POST['total_line'] = zulu::dollar(($renew_data['quantity']*$renew_data['price']));

		//Form Submit
		if($_POST['action']=='edit') {
			$form_edit->valid = true;

			if($_POST['quantity']<=0) {
				$zulu->notification_set("Please enter a quantity greater than zero.",2);
				$form_edit->valid = false;
			}
			if(count($_POST['renew_type'])<=0) {
				$zulu->notification_set("Please select a subscription model.",2);
				$form_edit->valid = false;
			}

			if($form_edit->valid) {
				$data['opt_bill'] = $db->escape_string($_POST['opt_bill']);
				$data['title'] = $db->escape_string($_POST['title']);
				$data['description'] = $db->escape_string($_POST['description']);
				$data['product_id'] = $db->escape_string($_POST['product_id']);
				$data['quantity'] = $db->escape_string($_POST['quantity']);
				$data['price'] = $db->escape_string($_POST['price']);
				$data['trial_length'] = $db->escape_string($_POST['trial_length']);
				$data['trial_scale'] = $db->escape_string($_POST['trial_scale']);
				$data['renew_scale'] = $db->escape_string($_POST['renew_scale']);
				$data['renew_interval'] = $db->escape_string($_POST['renew_interval']);
				$data['renew_action'] = $db->escape_string($_POST['renew_action']);
				$data['opt_switch'] = $db->escape_string($_POST['opt_switch']);
				$data['change_from'] = $db->escape_string($_POST['change_from']);
				$data['change_to'] = $db->escape_string($_POST['change_to']);
				$data['renew_type'] = $db->escape_string($_POST['renew_type']);
				$data = $class_renew->template_edit($id,$data);

				if(count($_POST['meta'])>0) {
					foreach($_POST['meta'] as $key=>$val) {
						$zulu->meta_update("renew_template",$data['id'],$key,$val);
					}
				}

				if($data['success']) {
					$zulu->notification_set("Subscription type ".(!$new?"updated":"created")." successfully.",1);
					header("Location: ".$zulu->link_page(PAGE_file,array('query'=>array('Action'=>'template'))));
					exit;
				} else {
					$zulu->notification_set("A database error occurred.",2);
				}
			}
		}
	}
	if(PAGE_action=='report') {

		$zulu->nav->title = "Subscription Report";
		$zulu->nav->width = 8;
		$zulu->nav->breadcrumb['Report'] = [];

		$form_edit = new form;
		$exact = false;
		$zulu->template->config->date_select = true;

		if($_GET['date_from']!=NULL && !isset($_GET['date_to'])) {
			$date_from = $zulu->dateEncode($_GET['date_from']);
			$date_to = $date_from+86399;
			$filtered = true;
			$exact = true;

			$_GET['date_from'] = $zulu->dateDecode($date_from);
		}
		if($_GET['date_from']!=NULL&&$_GET['date_to']!=NULL || isset($date_from)) {
			$filtered = true;

			if(!$exact) {
				$date_from = $zulu->dateEncode($_GET['date_from']);
				$date_to = $zulu->dateEncode($_GET['date_to']);
			}

			if($date_from<=0||$date_to<=0) {
				$zulu->notification_set("Report date invalid.",2);
				header("Location: ".$zulu->link_page(PAGE_file,['self'=>true]));
				exit;
			}
			if($date_from>=$date_to) {
				$zulu->notification_set("Select a from date that is less the the to date.",2);
				header("Location: ".$zulu->link_page(PAGE_file,['self'=>true]));
				exit;
			}
		}

		if($filtered) {
			$zulu->nav->breadcrumb['Report'] = array('link'=>$zulu->link_page(PAGE_file,['query'=>['Action'=>'report']]));

			if($exact) {
				$zulu->nav->breadcrumb['On '.date('d/m/Y',$date_from)] = [];
			} else {
				$zulu->nav->breadcrumb['From '.date('d/m/Y',$date_from).' to '.date('d/m/Y',$date_to)] = [];
			}

			//-- Summary
			$summary = [];

			//-- REPORT: Signups
			$renew_data = $class_renew->renew_data(['active'=>true,'total'=>true,'add_between'=>[$date_from,$date_to]]);
			$summary['signup'] = $renew_data['_total_sum'];

			//-- REPORT: Cancellations
			$renew_data = $class_renew->renew_data(['cancel'=>true,'total'=>true,'cancel_between'=>[$date_from,$date_to]]);
			$summary['cancel'] = $renew_data['_total_sum'];

			//-- REPORT: Renewals
			$renew_data = $class_renew->renew_log_data(['where_txt'=>["user_id = '".$class_user->authorised->id."'","renew_time >= ".$date_from,"renew_time <= ".$date_to],'test'=>false,'sort'=>'renew_log.renew_time ASC','join'=>'renew ON renew_log.renew_id = renew.id','field'=>['renew_log.object','renew_log.object_id','renew_log.id AS renew_log_id','renew_log.renew_from AS renew_from','renew_log.renew_to AS renew_to']]);

			$total_revenue = 0;
			foreach($renew_data as $renew) {
				if($renew['object']=='sale_line') {
					$total_revenue += $class_sale->sale_total($class_sale->sale_id_from_line_id($renew['object_id']));
				}
			}
			$summary['renew_revenue'] = $total_revenue;
			$summary['renew'] = count($renew_data);

			//-- Jquery
			/*$zulu->template->jquery[] = "
				new Morris.Line({
				// ID of the element in which to draw the chart.
				element: 'morris-area-chart',
				// Chart data records -- each entry in this array corresponds to a point on
				// the chart.
				data: [
				".implode(',',$areas)."
				],
				xkey: 'period',
				parseTime: false,
				//postUnits:	'Revenue',
				ykeys: ['revenue','sales'],
				labels: [ 'Total Revenue','Sales'],
				});
			";*/
		} else {
			//-- extra sales
			unset($table_row,$table_column);
			$table_column[] = array("Signed Up");
			$table_column[] = array("First Name");
			$table_column[] = array("Surname");
			$table_column[] = array("Phone");
			$table_column[] = array("Email");
			$table_column[] = array("Payment");
			//$table_column[] = array("Address");
			$table_column[] = array("Next Renewal",array('class'=>array('center')));
			//$table_column[] = array("Status",array('class'=>array('status-box center')));
			$table_row[] = array(
					"header"	=>	 true,
					"class"		=>	"",
					"content"	=>	$table_column);

			$summary = $export_data = $sale_item_count = [];
			$sale_data = $class_renew->renew_data(['active'=>true]);
			foreach($sale_data as $key=>$row) {

				$renew_object = $class_renew->get($row['id']);

				$client_data = [];
				$client_first_name = $client_last_name = '';
				$client_phones = $class_client->phone($class_renew->vars->data['id']);
				if($class_renew->vars->data['client_id']>0) {
					$client_data = $class_client->client_data(['id'=>$class_renew->vars->data['client_id']]);
					$client_first_name = stripslashes($client_data['name_first']);
					$client_last_name = stripslashes($client_data['name_last']);
				}


				$payment_label = LOCALE_currency_symbol.number_format(($class_renew->vars->data['quantity']*$class_renew->vars->data['price']),2).' ('.$class_renew->vars->data['renew_interval']." ".zulu::time_scale($class_renew->vars->data['renew_scale'],'pl').')';

				$table_row[] = array("content" => array(
					array($zulu->date($class_renew->vars->data['stat_add'],'j/m/Y'),array('class'=>array('text-small'))),
					array($zulu->nl($client_first_name)),
					array($zulu->nl($client_last_name)),
					array($zulu->nl(stripslashes($client_phones['default']))),
					array($zulu->nl(stripslashes($client_data['email']))),
					array($zulu->nl($payment_label)),
					//array($zulu->nl(stripslashes($client_address))),
					array($class_renew->next_renewal_label(),array('class'=>array('text-small'))),
					//array(zulu::time_fancy($class_renew->vars->data['stat_add']),array('class'=>array('text-small'))),
				));
				$export_data[] = [
					strip_tags($class_renew->vars->data['id']),
					strip_tags($zulu->date($class_renew->vars->data['stat_add'],'j/m/Y')),
					strip_tags($zulu->nl($client_first_name)),
					strip_tags($zulu->nl($client_last_name)),
					strip_tags($zulu->nl(stripslashes($client_phones['default']))),
					strip_tags($zulu->nl(stripslashes($client_data['email']))),
					strip_tags($zulu->nl($payment_label)),
					strip_tags($class_renew->next_renewal_label()),
					$zulu->date($class_renew->vars->data['period_start'],'j/m/Y'),
					$zulu->date($class_renew->vars->data['period_end'],'j/m/Y'),
				];

				$summary['total'] += 1;

				$summary['revenue_year'] += $class_renew->revenue_per_year();
				$summary['revenue_month'] += $class_renew->revenue_per_month();

			}

			//-- DO: Download
			if($_GET['Do']=='download') {
				$data['header'] = ['ID','Signed Up','First Name','Last Name','Phone','Email','Payment','Next Renewal','Current Start','Current End'];
				$data['body'] = $export_data;
				$exp = $zulu->export_csv($data,['name'=>"Active Subscriptions"]);

				header("Location: ".$exp['url']);
				exit;
			}

			$zulu->template->table_current_subs = $zulu->table_render($table_row,0,array('class'=>'report','data_table'=>false));
		}
	}
}
