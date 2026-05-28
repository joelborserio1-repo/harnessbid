<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- DEFINITIONS
define(PAGE_file,'client');
define(FILE_root,$class_file->file_root);
define(UPLOADER_root,TPL_rel."assets/dropzone/");

if(PAGE_action=='edit'&&PAGE_id>0) {
	$new_type = $class_client->type(PAGE_id);
}
if(PAGE_action=='edit'&&isset($_GET['type'])) {
	$new_type = $_GET['type'];
}
if(PAGE_action=='lead'||$new_type=='lead') {
	$lead = true;
	$new_label = 'lead';
	define(PAGE_name,'Leads');
	define(PAGE_label,'Lead');
	$zulu->nav->breadcrumb[PAGE_name] = array("link"=>$zulu->link_page(PAGE_file,array('query'=>array('Action'=>'lead'))));
} elseif(PAGE_action=='cancel'||$new_type=='cancel') {
	$cancel = true;
	$new_label = 'cancel';
	define(PAGE_name,'Cancelled');
	define(PAGE_label,'Client');
	$zulu->nav->breadcrumb[PAGE_name] = array("link"=>$zulu->link_page(PAGE_file,array('query'=>array('Action'=>'cancel'))));
} elseif(PAGE_action=='supplier'||$new_type=='supplier') {
	$supplier = true;
	$new_label = 'supplier';
	define(PAGE_name,'Suppliers');
	define(PAGE_label,'Supplier');
	$zulu->nav->breadcrumb[PAGE_name] = array("link"=>$zulu->link_page(PAGE_file,array('query'=>array('Action'=>'supplier'))));
} elseif(PAGE_action=='prospect'||$new_type=='prospect') {
	$new_label = 'prospect';
	$prospect = true;
	define(PAGE_name,'Prospects');
	define(PAGE_label,'Prospect');
	$zulu->nav->breadcrumb[PAGE_name] = array("link"=>$zulu->link_page(PAGE_file,array('query'=>array('Action'=>'prospect'))));
} else {
	define(PAGE_name,'Clients');
	define(PAGE_label,'Client');
	$zulu->nav->breadcrumb[PAGE_name] = array("link"=>$zulu->link_page(PAGE_file));
}


//-- AUTHORISED?
$class_user->user_authorised_check(PAGE_action);

if(!$class_user->authorised->opt_client) {
	$zulu->notification_set("Sorry, you are not authorised to use the ".PAGE_name." area.",2);
	header("Location: ".$zulu->link_page("index"));exit;
}

//-- ADMIN MASTER SECTION
if(MASTER_section=='admin') { //admin section

	$zulu->template->head = "";
	$zulu->template->body = "";

	//Roles
	foreach($class_client->role_data() as $role_row) {
		$CLIENT_option[$role_row['tag']] = stripslashes($role_row['name']);
	}

	if(PAGE_action=="xero") {
		//Xero Upd
		if($_POST['action']=='xero') {
			foreach($_POST['xero'] as $key=>$row) {
				if($row!=NULL) {
					$data['xero_id'] = $row;
					$class_client->client_edit($key,$data);
					//$db->query("UPDATE client SET xero_id = '$row' WHERE id = '$key'");
				}
			}
			$zulu->notification_set("Xero keys updated.",1);

			header("Location: ".$zulu->link_page(PAGE_file));
			exit;
		}

		$form_edit = new form;
		$response = $XeroOAuth->request('GET', $XeroOAuth->url('Contacts', 'core'), array('Where'=>'IsCustomer = true'));
		if ($XeroOAuth->response['code'] == 200) {
		   $contacts = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);

		   foreach($contacts->Contacts->Contact as $row) {
				$row_arr = (array)$row;
				$data[] = array("name"=>$row_arr['Name'],"id"=>$row_arr['ContactID']);
		   }
		} else {
		   outputError($XeroOAuth);
		}

		$xero_form = true;
	}

	if(PAGE_action==NULL || PAGE_action=='supplier' || PAGE_action=='prospect' || PAGE_action=='lead' || PAGE_action=='cancel' || PAGE_action=='xero') {	//grid page

		//-- Page Action
		if($_POST&&$_POST['execute']!=NULL) {
			if($_POST['execute']=='delete') {
				foreach($_POST['action'] as $id=>$val) {
					if(!$checkret = $class_client->cancel_delete($id)) {
						$error_log[] = "Failed to delete client ID #".$id;
					} else {

					}
				}
				$zulu->notification_set("Selected clients were removed successfully.",1);
				header("Location: ".$zulu->link_page('client'));
				exit;
			}
		}

		//-- Page Content
		$zulu->template->config->select_all = true;

		function edit_bt($id) {
			global $zulu,$cancel,$lead,$prospect,$supplier,$class_client;
			if($supplier) {
				$supdat = $class_client->client_data(['id'=>$id,'field'=>['token'],'first'=>true]);
			}
			return "
				".($supplier?"<a href=\"".MAIN_rel."supplier/view/".$supdat['token']."/\" title=\"Move to prospects...\" class=\"btn btn-default btn-xs\"><i class=\"fas fa-chart-line\"></i> Report</a>":NULL)."
				".($lead?"<a href=\"".$zulu->link_page(PAGE_file,['query'=>array('id'=>$id,'Action'=>'to_prospect')])."\" title=\"Move to prospects...\" class=\"btn btn-success btn-xs\"><i class=\"fas fa-check\"></i> Prospect</a>":NULL)."
				".($prospect?"<a href=\"".$zulu->link_page(PAGE_file,['query'=>array('id'=>$id,'Action'=>'to_client')])."\" title=\"Move to clients...\" class=\"btn btn-success btn-xs\"><i class=\"fas fa-thumbs-up\"></i> ".($prospect?'Won':'Prospect')."</a>":NULL)."
				".($prospect?"<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'to_cancel')))."\" title=\"Move to Cancelled\" class=\"btn btn-danger btn-xs\"><i class=\"fas fa-thumbs-down\"></i> Lost</a>":NULL)."
				<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'edit')))."\" class=\"btn btn-primary btn-xs\"><i class=\"fas fa-edit\"></i> Edit</a>
				<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'delete')))."\" class=\"btn btn-danger btn-xs confirm-delete\"><i class=\"fas fa-times\"></i></a>
			";
		}
		function dd_xero() {
			global $data;
			$html[''] = "None";
			foreach($data as $row) {
				//$html[] = "<option value=\"".$row['id']."\" ".($id==$row['id']?"selected":NULL).">".$row['name']."</option>";
				$html[$row['id']] = $row['name'];
			}
			return $html;
		}
		$form_edit = new form;

		if(PAGE_action=='xero') {
			$table_column[] = array("Xero Sync",array('class'=>array('')));
		}

		$table_column[] = array($form_edit->input_html("checkbox","selectall",1,array("class"=>['toggle-input'])),array('class'=>array('')));
		$table_column[] = array("Client",['sort'=>['db_column'=>"company,name_first,name_last"]]);
		$table_column[] = array("Phone");
		$table_column[] = array("Email");
		$table_column[] = array("Referrer");
		$table_column[] = array("Added",['sort'=>['db_column'=>'stat_add']]);
		$table_column[] = array("Actions",array('class'=>array('right')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);

		if($lead) {
			$client_type = '2';
		} elseif($prospect) {
			$client_type = '3';
		} elseif($cancel) {
			$client_type = '0';
		} else {
			$client_type = '1';
		}

		if($_GET['role'] != NULL) {
			$role = $db->escape_string($_GET['role']);
			if($role == 'none') {
				$roleSQL = " AND m.id IS NULL";
			} else {
				$roleSQL = " AND m.value='".$role."'";
			}
			$_SESSION['client']['role_last'] = $role;
		} elseif($_GET['role'] == 'none') {
			foreach($CLIENT_option as $key=>$val) {
				$roleSQL = " AND m.value=''";
				break;
			}
			unset($_SESSION['client']['role_last']);
		} elseif(count($CLIENT_option) > 0) {
			$skip_group = true;
			unset($_SESSION['client']['role_last']);
		}

		/*Supplier*/
		if(PAGE_action=='supplier') {
			$supplier = true;
			$filter_sql[] = "supplier = 1";
			unset($client_type);
			$client_type[] = '1';
			$client_type[] = '0';
		} else {
			$filter_sql[] = "supplier = 0";
		}

		/*Search*/
		if($_GET['Search'] != NULL) {
			$sk = $db->escape_string($_GET['Search']);
			$filter_sql[] = "(name_first LIKE '%".$sk."%' OR name_last LIKE '%".$sk."%' OR CONCAT(name_first,' ',name_last) LIKE '%".$sk."%' OR company LIKE '%".$sk."%' OR email LIKE '%".$sk."%' OR phone LIKE '%".$sk."%')";
		}

		/*Query*/
		$start = ($_GET['Pg']>1?MAX_per_page*($_GET['Pg']-1):0);
		if($skip_group) {
			$query = $db->mysqli->query("SELECT company,name_first,name_last,phone,email,refer,stat_add,xero_id,c.id AS client_id,c.client_id AS ref_client_id FROM client AS c {$join} WHERE c.user_id='".$class_user->authorised->id."' AND ".(is_array($client_type)?"type IN(".implode(",",$client_type).")":"type='".$client_type."'")." AND c.status='1'{$roleSQL}".(count($filter_sql)>0?" AND ".implode(" AND ",$filter_sql):NULL)." {$where}".($join!=NULL?" GROUP BY c.id":NULL)." ORDER BY ".$zulu->table_sort_query('client_table','c.id DESC')." LIMIT ".$start.",".MAX_per_page) or die($db->mysqli->error);

			$query_full = $db->mysqli->query("SELECT c.id AS client_id FROM client AS c {$join} WHERE c.user_id='".$class_user->authorised->id."' AND ".(is_array($client_type)?"type IN(".implode(",",$client_type).")":"type='".$client_type."'")." AND c.status='1'".(count($filter_sql)>0?" AND ".implode(" AND ",$filter_sql):NULL)." {$where}".($join!=NULL?" GROUP BY c.id":NULL)) or die($db->mysqli->error);
		} else {
			$query = $db->mysqli->query("SELECT company,name_first,name_last,phone,email,refer,stat_add,xero_id,c.id AS client_id,c.client_id AS ref_client_id FROM client AS c LEFT JOIN client_meta AS m ON c.id=m.identifier AND field='role' {$join} WHERE c.user_id='".$class_user->authorised->id."' AND ".(is_array($client_type)?"type IN(".implode(",",$client_type).")":"type='".$client_type."'")." AND c.status='1'{$roleSQL}".(count($filter_sql)>0?" AND ".implode(" AND ",$filter_sql):NULL)." {$where} GROUP BY c.id ORDER BY ".$zulu->table_sort_query('client_table','c.id DESC')." LIMIT ".$start.",".MAX_per_page) or die($db->mysqli->error);

			$query_full = $db->mysqli->query("SELECT c.id AS client_id FROM client AS c LEFT JOIN client_meta AS m ON c.id=m.identifier AND field='role' {$join} WHERE c.user_id='".$class_user->authorised->id."' AND ".(is_array($client_type)?"type IN(".implode(",",$client_type).")":"type='".$client_type."'")." AND c.status='1'{$roleSQL}".(count($filter_sql)>0?" AND ".implode(" AND ",$filter_sql):NULL)." {$where} GROUP BY c.id") or die($db->mysqli->error);
		}

		$zulu->vars->client_count = $query_full->num_rows;
		$i = 1;
		$key = 0;

		while($row = $query->fetch_assoc()) {
			$presort_row[] = $row;
		}
		//$sort_row = $zulu->table_sort('client_table',$presort_row);

		foreach($presort_row as $row) {
			$row['name'] = (trim($row['company'])!=NULL?$row['company'].($row['name_first']!=NULL||$row['name_last']!=NULL?" <small class=\"color-grey\">".$row['name_first']." ".$row['name_last']."</small>":NULL):$row['name_first']." ".$row['name_last']);

			if($row['refer']=='Client'&&$row['ref_client_id']>0) {
				$row['refer'] = $class_client->admin_link($row['ref_client_id']);
			} elseif($row['refer']!=NULL) {
				//-- as normal
			} else {
				$row['refer'] = '-';
			}

			if($lead) {
				$table_row[] = array("content" => array(
					array($form_edit->input_html("checkbox","action[".$row['client_id']."]",1,array('checked'=>($_POST['action'][$row['client_id']]>0?true:false),'class'=>array('action'))),array('class'=>array('action-field'))),
					array(stripslashes($row['name'])),
					array(($row['phone']!=NULL?$row['phone']:'-')),
					array(($row['email']!=NULL?$row['email']:'-')),
					array($row['refer']),
					array(zulu::time_history($row['stat_add'])),
					array(edit_bt($row['client_id'],true),array('class'=>array('right')))
				));
			} else if(PAGE_action=='xero') {
				$table_row[] = array("content" => array(
					array($form_edit->input_html("select","xero[".$row['client_id']."]",$row['xero_id'],array('option'=>dd_xero()))),
					array($form_edit->input_html("checkbox","action[".$row['client_id']."]",1,array('checked'=>($_POST['action'][$row['client_id']]>0?true:false),'class'=>array('action'))),array('class'=>array('action-field'))),
					array(stripslashes($row['name'])),
					array(($row['phone']!=NULL?$row['phone']:'-')),
					array(($row['email']!=NULL?$row['email']:'-')),
					array($row['refer']),
					array(zulu::time_history($row['stat_add'])),
					array(edit_bt($row['client_id']),array('class'=>array('right')))
				));
			} else {
				$table_row[] = array("content" => array(
					array($form_edit->input_html("checkbox","action[".$row['client_id']."]",1,array('checked'=>($_POST['action'][$row['client_id']]>0?true:false),'class'=>array('action'))),array('class'=>array('action-field'))),
					array(stripslashes($row['name'])),
					array(($row['phone']!=NULL?$row['phone']:'-')),
					array(($row['email']!=NULL?$row['email']:'-')),
					array($row['refer']),
					array(zulu::time_history($row['stat_add'])),
					array(edit_bt($row['client_id'],$row),array('class'=>array('right')))
				));
			}
		}
		$zulu->template->body = $zulu->table_render($table_row,0,array('html_id'=>'client_table','class'=>'basket','data_table'=>false,'js_table'=>false));
		$pagination = $zulu->pagination($_GET['Pg'],['count'=>$zulu->vars->client_count,'link'=>$zulu->link_page(PAGE_file,array('query'=>array('role'=>$_GET['role'],'Search'=>$_GET['Search'])))]);
		$zulu->nav->title = PAGE_name;
	}
	if(PAGE_action=='delete') { //cancel client
		$id = $db->escape_string($_GET['id']);
		$client_data = $class_client->client_data(['id'=>$id]);
		$class_client->vars->data_row = $client_data;
		$client_status = $class_client->client_status();

		if($client_data['type']==0) {
			$del = $class_client->cancel_delete(PAGE_id);
		} else {
			$del = $class_client->delete(PAGE_id);
		}
		if($del) {
			$zulu->notification_set($client_status['type_label']." removed successfully.",1);
			header("Location: ".$zulu->link_page(PAGE_file,['self'=>true,'query'=>['Action'=>($client_data['supplier']>0?'supplier':$class_client->config->type_array[$client_data['type']])]]));
		} else {
			$zulu->notification_set("A database error occurred.",2);
		}
		exit;
	}
	if(PAGE_action=='to_lead') { //change to lead
		if($class_client->change_type(PAGE_id,2)) {
			$zulu->notification_set("Lead converted and moved to client table.",1);
			header("Location: ".$zulu->link_page(PAGE_file));
		} else {
			$zulu->notification_set("A database error occurred.",2);
		}
		exit;
	}
	if(PAGE_action=='to_cancel') { //change to client
		if($class_client->change_type(PAGE_id,0)) {
			$zulu->notification_set("Lead lost and moved to cancelled table.",1);
			header("Location: ".$zulu->link_page(PAGE_file,array('query'=>array('Action'=>'lead'))));
		} else {
			$zulu->notification_set("A database error occurred.",2);
		}
		exit;
	}
	if(PAGE_action=='to_client') { //change to client
		if($class_client->change_type(PAGE_id,1)) {
			$zulu->notification_set("Prospect was moved to clients.",1);
			header("Location: ".$zulu->link_page(PAGE_file,array('query'=>array('Action'=>'lead'))));
		} else {
			$zulu->notification_set("A database error occurred.",2);
		}
		exit;
	}
	if(PAGE_action=='to_prospect') { //change to prospect
		if($class_client->change_type(PAGE_id,3)) {
			$zulu->notification_set("Lead was moved to prospects.",1);
			header("Location: ".$zulu->link_page(PAGE_file,array('query'=>array('Action'=>'lead'))));
		} else {
			$zulu->notification_set("A database error occurred.",2);
		}
		exit;
	}
	if(PAGE_action == "FromEmail") {
		$name = explode(' ',$_GET['Name'],2);
		$mess = "Enquiry:\n".addslashes($_GET['Message']);

		$data['refer'] = ($_GET['Refer']=='Internet'?"Online Free":($_GET['Refer']=='Family'?"Family / Friend":$_GET['Refer']));
		$data['name_first'] = addslashes($name[0]);
		$data['name_last'] = addslashes($name[1]);
		$data['email'] = $_GET['Email'];
		$data['notes'] = addslashes($mess);
		$data['type'] = 2;
		$data['phone'] = $_GET['Phone'];
		$data = $class_client->client_edit($id,$data);

		$_SESSION['SH_Error'] = "New lead added.";
		$_SESSION['SH_Error_Class'] = 1;

		if($data['success']) {
			$zulu->notification_set("Lead created successfully.",1);
			header("Location: ".$zulu->link_page(PAGE_file,array('query'=>array('Action'=>'lead','Error'=>'LeadAdded'))));
			exit;
		} else {
			$zulu->notification_set("A database error occurred.",2);
		}
	}
	if(PAGE_action=='delete_role' && PAGE_id > 0) {
		$role = $db->escape_string($_GET['role']);
		$from = $db->escape_string($_GET['from']);
		$zulu->meta_remove('client',PAGE_id,'role',$role);
		$zulu->notification_set("Client removed from group successfully.",1);
		header("Location: ".$zulu->link_page('client',array('query'=>array('id'=>PAGE_id,'Action'=>$from))));
		exit;
	}
	if(PAGE_action=='edit') { //edit page

		//Include
		$zulu->template->css_file[] = UPLOADER_root."dist/dropzone.css";
		$zulu->template->js_file[] = UPLOADER_root."dist/dropzone.js";
		$zulu->template->js_code[] = "
										$(function() {
											$('.copy_address').click(function() {
												var to = $(this).data('to');
												var from = $(this).data('from');
												var address = $('#'+from+'_address').val();
												var suburb = $('#'+from+'_suburb').val();
												var city = $('#'+from+'_city').val();
												var post = $('#'+from+'_post').val();
												if(address != '' && address != null){
													$('#'+to+'_address').val(address);
												}
												if(suburb != '' && suburb != null){
													$('#'+to+'_suburb').val(suburb);
												}
												if(city != '' && city != null){
													$('#'+to+'_city').val(city);
												}
												if(post != '' && post != null){
													$('#'+to+'_post').val(post);
												}
											});

										});
										";
		$form_edit = new form;

		if(PAGE_id<1) {
			$id = 0;
			$new = true;
			$zulu->nav->breadcrumb['New '.PAGE_label] = array();
			$zulu->nav->title = "New ".PAGE_label;
		} else {
			$id = ($_POST['id']>0?$_POST['id']:($_GET['id']>0?$_GET['id']:0));

			$row_data = ($lead?$class_client->lead_data(array('id'=>$id)):($cancel?$class_client->client_data(array('id'=>$id,'type'=>'0')):$class_client->client_data(array('id'=>$id))));
			$meta_data = $class_client->client_meta($id);
            $client = Clients::find($id);

			if(!$_POST) {
				foreach($row_data as $key=>$val) {
					$_POST[$key] = stripslashes($val);
				}
				foreach($meta_data as $key=>$val) {
					$_POST[$key] = stripslashes($val['value']);
				}
				$_POST['notes'] = str_replace('<br>',chr(13),stripslashes($_POST['notes']));
			}

			$logs = $class_client->client_log_data(array('client_id'=>$id));
			foreach($logs as $log) {
				$team = $class_user->user_data(array('id'=>$log['team_id']));
				$log_rows .= "<tr><td>".$team['name_first']."</td><td>".stripslashes($log['notes'])."</td><td>".date('H:ia d/m/Y',$log['stat_add'])."</td></tr>";
			}
			$log_table = "<table class=\"table table-striped table-bordered table-hover file\"><thead><tr><td>Staff</td><td>Log</td><td>Time/Date</td></tr></thead><tbody>".$log_rows."</tbody></table>";

			if($meta_data['folder_id']['value'] == NULL) {
				$file_result = $class_file->file_edit(0,array('type'=>'folder','name'=>'Client Files','parent_id'=>'0','object'=>'client','object_id'=>$id));
				$zulu->meta_update('client',$id,'folder_id',$file_result['id']);
			} else {
				$file_result['id'] = $meta_data['folder_id']['value'];
			}
			$zulu->template->file_table = $class_file->embed_table(array('root_id'=>$file_result['id'],'object'=>'client','object_id'=>$id));

			if($meta_data['role']['id'] != NULL) {
				$meta_row = $meta_data['role'];
				$role_html[] = "<a href=\"".$zulu->link_page('client',array('query'=>array('Action'=>($row_data['type']=='2'?'lead':($row_data['type']=='0'?'cancel':'')),'role'=>$meta_row['value'])))."\">".$CLIENT_option[$meta_row['value']]."</a> <a class=\"confirm-delete\" title=\"Delete\" href=\"".$zulu->link_page('client',array('query'=>array('id'=>$id,'Action'=>'delete_role','role'=>$meta_row['value'],'from'=>PAGE_action)))."\"><button class=\"btn btn-danger btn-circle\" type=\"button\"><i class=\"fas fa-times\"></i></button></a>";
			} else {
				foreach($meta_data['role'] as $meta_row) {
					$role_html[] = "<a href=\"".$zulu->link_page('client',array('query'=>array('Action'=>($row_data['type']=='2'?'lead':($row_data['type']=='0'?'cancel':'')),'role'=>$meta_row['value'])))."\">".$CLIENT_option[$meta_row['value']]."</a> <a class=\"confirm-delete\" title=\"Delete\" href=\"".$zulu->link_page('client',array('query'=>array('id'=>$id,'Action'=>'delete_role','role'=>$meta_row['value'],'from'=>PAGE_action)))."\"><button class=\"btn btn-danger btn-circle\" type=\"button\"><i class=\"fas fa-times\"></i></button></a>";
				}
			}

			$role_html = implode('&nbsp;&nbsp;&nbsp;&nbsp;',$role_html);
			if($role_html != NULL) {
				$role_html = "<p>".$role_html."</p>";
			}

			$zulu->nav->breadcrumb['Edit '.PAGE_label] = array();
			$zulu->nav->breadcrumb[$class_client->client_name($row_data)] = array();
			$zulu->nav->title = "Edit ".PAGE_label;

			//-- Statistics
			$stat_sale = $zulu->table_data('sale',0,['where'=>["client_id = ".PAGE_id,"sm1.field = 'stat_total'","sale.status = 1"],'join'=>'sale_meta sm1 ON sale.id = sm1.identifier','field'=>['sale.id','sum(sm1.value) AS sale_total'],'sort'=>'sale.id ASC']);
			$stat_sale_due = $zulu->table_data('sale',0,['where'=>["client_id = ".PAGE_id,"sm1.field = 'stat_paid_balance'","sale.status = 1"],'join'=>'sale_meta sm1 ON sale.id = sm1.identifier','field'=>['sale.id','sum(sm1.value) AS sale_total'],'sort'=>'sale.id ASC']);
			$client_stat_html['sale'] = $class_sale->currency_symbol.$zulu->dollar($stat_sale[0]['sale_total']).($stat_sale_due[0]['sale_total']>0?"<br><span class=\"opt opt-danger text-small no-margin\">".$class_sale->currency_symbol.$zulu->dollar($stat_sale_due[0]['sale_total'])." Due</span>":NULL);
			if(MASTER_mode=='main') {
				$stat_project = $zulu->table_data('job',0,['where'=>["client_id = ".PAGE_id],'field'=>['id']]);
				$stat_task = $zulu->table_data('task',0,['where'=>["client_id = ".PAGE_id],'field'=>['id']]);
				$client_stat_html['project'] = count($stat_project).' Project'.$zulu->s(count($stat_project));
				$client_stat_html['task'] = count($stat_task).' Task'.$zulu->s(count($stat_task));
			} else {
				$client_stat_html['visit'] = $meta_data['web_logins']['value'].' Signin'.$zulu->s($meta_data['web_logins']['value']);
				$client_stat_html['last'] = $zulu->time_fancy($meta_data['web_last']['value']);
			}
		}

        if($_GET['Do'] == 'new-reset-link') {
            $cpr = new ClientPasswordReset();
            $cpr->user_id 	    = $class_user->authorised->id;
            $cpr->client_id 	= $client->id;
            $cpr->email 		= $client->email;
            $cpr->ip_add        = $_SERVER['REMOTE_ADDR'];
            $cpr->save();

            $zulu->notification_set("Password reset link has been generated. You can now send an automatic email with the link inside or view the link to send manually.", 1);
            header("Location: ".$zulu->link_page(PAGE_file, ['self'=>true, 'filter'=>['Do'], 'query'=>['panel'=>'pr']]));
            exit;

        } elseif($_GET['Do'] == 'send-reset-link') {
            if($_GET['pr'] > 0) {
                $cpr = $client->passwordResets()->where('id',$_GET['pr'])->first();
                if($cpr != null) {
                    $cpr->sendEmail();
                    $zulu->notification_set("Password reset link has been sent.", 1);
                }
            }

            header("Location: ".$zulu->link_page(PAGE_file, ['self'=>true, 'filter'=>['Do','pr'], 'query'=>['panel'=>'pr']]));
            exit;
        }

		//Form Submit
		if($_POST['action']=='add_role') {
			$zulu->meta_update('client',$id,'role',$_POST['role'],NULL,0,true);
			$zulu->notification_set("Client added to group successfully.",1);
			header("Location: ".$zulu->link_page('client',array('query'=>array('id'=>$id,'Action'=>PAGE_action))));
			exit;
		}
		if($_POST['action']=='edit') {
			$form_edit->valid = true;

			//-- Form Validate
			if(trim($_POST['company'])==NULL&&trim($_POST['name_first'])==NULL&&trim($_POST['name_last'])==NULL) {
				$zulu->notification_set("Please specify the contacts name / company.",2);
				$form_edit->valid = false;
			}

			//-- Check duplicate
			$email_check = $class_client->client_data(['email'=>$_POST['email']]);
			if($email_check['id']>0&&$email_check['id']!=$id) {
				$zulu->notification_set("This email is already in use with another account.",2);
				$form_edit->valid = false;
			}

			//-- Post Data
			if($form_edit->valid) {
				$data['name_first'] = $_POST['name_first'];
				$data['name_last'] = $_POST['name_last'];
				$data['email'] = $_POST['email'];
				$data['company'] = addslashes($_POST['company']);
				$data['notes'] = str_replace(chr(13),'<br>',addslashes($_POST['notes']));
				$data['type'] = $_POST['type'];
				$data['phone'] = $_POST['phone'];
				$data['hourly_rate'] = $_POST['hourly_rate'];
				$data['refer'] = $_POST['refer'];
				$data['reference'] = $_POST['reference'];
				$data['client_id'] = ($_POST['refer']=='Client'?$_POST['client_id']:0);
				if($_POST['password']!="") {
					$data['password'] = $class_user->password_hash($_POST['password']);
				}
				if(!$supplier) {
					$data['supplier'] = 0;
				} else {
					$data['supplier'] = 1;
				}

				$meta = array(
					"ship_address"=>$_POST['ship_address'],
					"ship_suburb"=>$_POST['ship_suburb'],
					"ship_city"=>$_POST['ship_city'],
					"ship_post"=>$_POST['ship_post'],
					"bill_address"=>$_POST['bill_address'],
					"bill_suburb"=>$_POST['bill_suburb'],
					"bill_city"=>$_POST['bill_city'],
					"bill_post"=>$_POST['bill_post'],
					"phy_address"=>$_POST['phy_address'],
					"phy_suburb"=>$_POST['phy_suburb'],
					"phy_city"=>$_POST['phy_city'],
					"phy_post"=>$_POST['phy_post'],
					"mobile"=>$_POST['mobile'],
					"website"=>$_POST['website'],
					"web_verify"=>($_POST['web_verify']=='1'?'1':'0'),
					"web_verify_phone"=>($_POST['web_verify_phone']=='1'?'1':'0'),
					"web_access"=>($_POST['web_access']=='1'?'1':'0'),
					"trade_account"=>($_POST['trade_account']=='1'?'1':'0'),
					"emerg_name"=>$_POST['emerg_name'],
					"emerg_phone"=>$_POST['emerg_phone'],
				);
				foreach($_POST['meta'] as $fm=>$fv) {
					$meta[$fm] = $fv;
				}

				$data = $class_client->client_edit($id,$data,$meta);
				$id = $data['id'];

				if($data['success']) {
					$class_client->change_type($id,$_POST['type']);

					$zulu->notification_set(PAGE_label." ".($new?"created":"updated")." successfully.",1);
					$link = [];

					if($supplier) {
						$link['query']['Action'] = 'supplier';
					} else {
						if($new_type=='lead') {
							$link['query']['Action'] = 'lead';
						} elseif($new_type=='prospect') {
							$link['query']['Action'] = 'prospect';
						} else {
						//$link['query']['Action'] = 'client';
						}
					}
					if($_SESSION['client']['role_last']!=NULL) {
						$link['query']['role'] = $_SESSION['client']['role_last'];
					}
					header("Location: ".$zulu->link_page(PAGE_file,$link));
					exit;
				} else {
					$zulu->notification_set("A database error occurred.",2);
				}
			}
		}
		unset($_POST['password']);

        if(!$new) {
            $password_resets = $client->passwordResets()->latest()->take(20)->get();
            $table_column = $table_row = [];
            $table_column[] = array("Generated",array('class'=>array('')));
            $table_column[] = array("Completed",array('class'=>array('')));
            $table_column[] = array("Actions",array('class'=>array('right')));
            $table_row[] = array(
                "header"	=>	 true,
                "class"		=>	"",
                "content"	=>	$table_column
            );
            foreach($password_resets as $key=>$password_reset) {
                $expired = false;
                $buttons = [];
                if($key == 0) {
                    if($password_reset->canReset()) {
                        $buttons[] = ['label'=>'View Link','link'=>'#','icon'=>'eye','class'=>'default','class_append'=>['view-link'],'data'=>['link'=>$password_reset->feURL(true)]];
                        $buttons[] = ['label'=>'Send Link','link'=>$zulu->link_page(PAGE_file, ['self'=>true, 'query'=>['Do'=>'send-reset-link','pr'=>$password_reset->id]]),'icon'=>'envelope','class'=>'warning','class_append'=>['confirm']];
                    } elseif($password_reset->date_complete == 0) {
                        $expired = true;
                    }
                }
                $table_row[] = array("content" => array(
					array($zulu->date($password_reset->stat_add, 'h:iA d/m/Y').($expired?" <small><span class='opt opt-bord opt-danger'>Expired</span></small>":null)),
					array(($password_reset->date_complete>0?$zulu->date($password_reset->date_complete, 'h:iA d/m/Y'):'<i>Never</i>')),
					array($zulu->button_render($buttons), array('class'=>array('right')))
				));
            }
            $zulu->template->pr_table = $zulu->table_render($table_row,0,array('class'=>'','data_table'=>false));
            $zulu->template->jquery[] = "
            $('.view-link').click(function() {
                var link = $(this).data('link');
                alert(link);
                return false;
            });
            ".($_GET['panel']=='pr'?"
            $('a[data-parent=\"#panel-pass-reset\"]').trigger('click');
            ":null)."
            ";

        }

	}
	if(PAGE_action=='log_edit' || PAGE_action=='lead_log' || PAGE_action=='cancel_log') { //edit page

		$form_edit = new form;
		//$client_data = $class_client->client_data(array('id'=>PAGE_id));
		$client_data = ($lead?$class_client->lead_data(array('id'=>PAGE_id)):($cancel?$class_client->client_data(array('id'=>PAGE_id,'type'=>'0')):$class_client->client_data(array('id'=>PAGE_id))));
		if($_GET['Type']=='New') {
			$id = 0;
			$new = true;

			$zulu->nav->breadcrumb[$client_data['company']] = array();
			$zulu->nav->breadcrumb['New Log'] = array();
			$zulu->nav->title = "New Log";
		} else {
			$id = ($_POST['id']>0?$_POST['id']:($_GET['id']>0?$_GET['id']:0));

			$log_data = $class_client->client_log_data(array('id'=>$id));

			if(!$_POST) {
				foreach($log_data as $key=>$val) {
					$_POST[$key] = $val;
				}
			}

			$zulu->nav->breadcrumb[$client_data['company']] = array();
			$zulu->nav->breadcrumb['Edit Log'] = array();
			$zulu->nav->title = "Edit Log";
		}

		//Form Submit
		if($_POST['action']=='log_edit') {
			//print_r($_POST);exit;
			$form_edit->valid = true;
			if($form_edit->validate(array('notes'))) {
				$zulu->notification_set("Please enter a log to record.",2);
				$form_edit->valid = false;
			}

			$data['notes'] = addslashes($_POST['notes']);
			$data['client_id'] = PAGE_id;
			$data['user_id'] = $class_user->authorised->id;
			$data['team_id'] = ($class_user->authorised->child_id>0?$class_user->authorised->child_id:$data['user_id']);

			if($id>0) {
				$data = $class_client->client_log_edit($id,$data);
			} else {
				$data = $class_client->client_log_new($data);
				$id = $data['id'];
			}

			if($form_edit->valid) {
				if($data['success']) {
					$zulu->notification_set("Log ".($id>0?"created":"updated")." successfully.",1);
					header("Location: ".$zulu->link_page(PAGE_file,array('query'=>array('id'=>PAGE_id,'Action'=>(PAGE_action=='lead_log'?'lead_':(PAGE_action=='cancel_log'?'cancel_':NULL)).'edit'))));
					exit;
				} else {
					$zulu->notification_set("A database error occurred.",2);
				}
			}
		}
	}

	if(PAGE_action=='client_role') {	//user role grid page

		function edit_bt($id) {
			global $zulu;
			return "
				<a href=\"".$zulu->link_page('client',array('query'=>array('id'=>$id,'Action'=>'client_role_edit')))."\"><button class=\"btn btn-primary btn-circle\" type=\"button\"><i class=\"fas fa-edit\"></i></button></a>
				<a class=\"confirm-delete\" href=\"".$zulu->link_page('client',array('query'=>array('id'=>$id,'Action'=>'client_role_delete')))."\"><button class=\"btn btn-danger btn-circle\" type=\"button\"><i class=\"fas fa-times\"></i></button></a>
			";
		}

		$table_column[] = array("Name");
		$table_column[] = array("Tag");
		$table_column[] = array("Users");
		$table_column[] = array("Actions",array('class'=>array('right')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);

		$user_row = $class_client->role_data();
		foreach($user_row as $row) {
			$table_row[] = array("content" => array(
				array($row['name']),
				array($row['tag']),
				array($row['user_count']),
				array(edit_bt($row['id']),array('class'=>array('right')))
			));
		}

		$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'roles'));
		$zulu->nav->title = "Client Groups";
		$zulu->nav->breadcrumb['Client Groups'] = array();
	}
	if(PAGE_action=='client_role_delete') { //delete
		if($class_client->role_delete(PAGE_id)) {
			$zulu->notification_set("Client group removed successfully.",1);
			header("Location: ".$zulu->link_page('client',array('query'=>array('Action'=>'client_role'))));
		} else {
			$zulu->notification_set("A database error occurred.",2);
		}
	}
	if(PAGE_action=='client_role_edit') { //user role edit page
		$form_edit = new form;

		$type = 'Group';
		$zulu->nav->breadcrumb['Client '.$type.'s'] = array("link"=>$zulu->link_page('client',array('query'=>array('Action'=>'client_role'))));
		if(PAGE_id<1) {
			$id = 0;
			$new = true;

			//Check type

			$zulu->nav->breadcrumb['New '.$type] = array();
			$zulu->nav->title = "New ".$type;
		} else {
			$id = ($_POST['id']>0?$_POST['id']:($_GET['id']>0?$_GET['id']:0));

			$group_data = $class_client->role_data(array('id'=>$id));

			if(!$_POST) {
				foreach($group_data as $key=>$val) {
					$_POST[$key] = $val;
				}
			}

			$zulu->nav->breadcrumb['Edit '.$type] = array();
			$zulu->nav->breadcrumb[$group_data['name']] = array();
			$zulu->nav->title = "Edit ".$type;
		}

		//Form Submit
		if($_POST['action']=='edit') {
			$form_edit->valid = true;

			if($form_edit->validate(array('name'))) {
				$zulu->notification_set("Please enter a name for the group.",2);
				$form_edit->valid = false;
			}

			if($id>0) {
				$_POST['stat_update'] = time();
				$query = "UPDATE client_role SET ".$db->build(1,array('name','tag'))." WHERE id = '".$id."' and user_id='".$class_user->authorised->id."'";
			} else {
				$_POST['user_id'] = $class_user->authorised->id;
				$query = "INSERT INTO client_role ".$db->build(2,array('name','tag','user_id'));
			}

			if($form_edit->valid) {
				if($db->query($query)) {
					$id = ($id>0?$id:$db->insert_id);
					$zulu->notification_set($type." ".($id>0?"updated":"created")." successfully.",1);
					header("Location: ".$zulu->link_page('client',array('query'=>array('Action'=>'client_role'))));
					exit;
				} else {
					$zulu->notification_set("A database error occurred.",2);
				}
			}
		}
	}
}
