<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- DEFINITIONS
define(PAGE_file,'support');
define(PAGE_name,'Support Tickets');
$zulu->nav->breadcrumb[PAGE_name] = array("link"=>$zulu->link_page(PAGE_file));

//-- AUTHORISED?
if($class_user->authorised->id<=0) {
	$class_user->user_public();
	$class_user->authorised->opt_project = true;
} else {
	$class_user->user_authorised_check();
}
if(!$class_user->authorised->opt_project) {
	$zulu->notification_set("Sorry, you are not authorised to use the ".PAGE_name." area.",2);
	header("Location: ".$zulu->link_page("index"));exit;
}
//-- ADMIN MASTER SECTION
if(MASTER_section=='admin') { //admin section
	//Clear the template
	$zulu->template->head = "";
	$zulu->template->body = "";
	
	//Show all Tickets
	if(PAGE_action==NULL) {
		echo "show all";exit;
	}
	
	//Create new or edit existing ticket
	if(PAGE_action=='edit') {
		echo "editing ticket";exit;
	}
	
	
	if(PAGE_action==NULL) {	//grid page
		
		function edit_bt($id) {
			global $zulu;
			return "
				<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'edit')))."\"><button class=\"btn btn-primary btn-circle\" type=\"button\"><i class=\"fas fa-edit\"></i></button></a> 
				<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'print')))."\" target=\"_blank\" title=\"Print ".PAGE_name."\"><button class=\"btn btn-default btn-circle\" type=\"button\"><i class=\"fas fa-print\"></i></button></a> 
				<a class=\"confirm-delete\" href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'delete','Sort'=>$_GET['Sort'])))."\"><button class=\"btn btn-danger btn-circle\" type=\"button\"><i class=\"fas fa-times\"></i></button></a> 
			";	
		}
		
		$form_edit = new form;
		
		$table_column[] = array("Client",array('class'=>array('')));
		$table_column[] = array("Title",array('class'=>array('center')));
		$table_column[] = array("Tasks",array('class'=>array('')));
		$table_column[] = array("Billables",array('class'=>array('')));
		$table_column[] = array("Start Time",array('class'=>array('')));
		$table_column[] = array("End Time",array('class'=>array('')));
		$table_column[] = array("Status",array('class'=>array('right')));
		$table_column[] = array("Added",array('class'=>array('right')));
		$table_column[] = array("Actions",array('class'=>array('right')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);
				
		if(isset($_GET['Sort'])) {
			$wSQL = array('status'=>$db->escape_string($_GET['Sort']));
			$tab = $_GET['Sort'];
		}
		$wSQL['template'] = '0';

		$data_row = $class_project->project_data($wSQL);
		foreach($data_row as $row) {
			if($row['client_id']>0) {
				$client_data = $class_client->client_data(array('id'=>$row['client_id']));
			}
			$desc = stripslashes(stripslashes($row['description']));
			$table_row[] = array("content" => array(
				array("<a href=\"".$zulu->link_page('client',array('query'=>array('Action'=>'edit','id'=>$row['client_id'])))."\">".stripslashes(($client_data['company']!=NULL?$client_data['company']:$client_data['name_first']." ".$client_data['name_last']))."</a>"),
				array(stripslashes($row['title'])),
				array($class_task->task_count(['project_id'=>$row['id'],'simple'=>true])),
				array($class_bill->bill_count(['project_id'=>$row['id'],'simple'=>true])),
				array(zulu::dateDecode($row['time_start'])),
				array(zulu::dateDecode($row['time_end'])),
				array(($row['status']==1?"Complete":"Incomplete")),
				array(zulu::time_history($row['stat_add'])),
				array(edit_bt($row['id']),array('class'=>array('right','w120')))
			),'class'=>($row['status']==1?"green":NULL));
		}
		
		//-- Counters
		if($class_cache->exists('project_count')) {
			$count = $class_cache->load('project_count');
		} else {
			$count = $class_project->project_count();
			$class_cache->save('project_count',$count);
		}
		foreach($count as $key=>$val) {
			$count[$key] = "<span class=\"bullet stat-{$key}\">{$val}</span>";	
		}
		
		$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'project'));
		$zulu->nav->title = PAGE_name;
	}
	if(PAGE_action=='delete') { //delete
		if($class_project->delete(PAGE_id)) {
			$zulu->notification_set("Project removed successfully.",1);
			header("Location: ".$zulu->link_page(PAGE_file,array('query'=>array('Sort'=>$_GET['Sort']))));
		} else {
			$zulu->notification_set("A database error occurred.",2);
		}
	}
	if(PAGE_action=='edit'||PAGE_action=='print') { //edit page
		
		$form_edit = new form;
		
		$zulu->template->css_file[] = "//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css";
		$zulu->template->js_file[] = "//code.jquery.com/ui/1.11.4/jquery-ui.js";
		$zulu->template->js_code[] = "
      	$(document).ready(function(){
			$(\"input[name='date_start'],input[name='date_end']\").datepicker({ dateFormat: \"dd/mm/yy\" });
	  	});
		";
		
		if(PAGE_id<1&&PAGE_action!='print') {
			$id = 0;
			$new = true;	
			$zulu->nav->breadcrumb['New Project'] = array();
			$zulu->nav->title = "New Project";
		} else {
			
			if(PAGE_action=='print') {
				$print_page = true;
				
				if($_GET['Token']!=NULL) {
					$data_query = array('token'=>$_GET['Token'],'ovr_user_id'=>true);
				} else {
					$data_query = array('id'=>PAGE_id);
				}
				$project_data = $class_project->project_data($data_query);
				$id = $project_data['id'];
				
				$client_data = $class_client->client_data(['id'=>$project_data['client_id'],'user_id'=>$project_data['user_id']]);
				$setting_data = $class_setting->setting_data(array('user_id'=>$project_data['user_id']));
				$class_setting->data = $setting_data;
			
				$logo = ($setting_data['quote_logo_path']!=NULL?$setting_data['quote_logo_path']:NULL);
			} else {
				$id = ($_POST['id']>0?$_POST['id']:($_GET['id']>0?$_GET['id']:0));
				$project_data = $class_project->project_data(array('id'=>$id));
			}
			
			if(!$_POST) {
				foreach($project_data as $key=>$val) {
					$_POST[$key] = $val;	
				}	
			}
			
			$quote_data = $zulu->table_data("quote",0,array("where"=>array("project_id = '".$id."'"),"field"=>array('id'),"first"=>true));
			$quote_id = $quote_data['id'];
			
			if(PAGE_action!='print') {
				$zulu->nav->breadcrumb['Edit Project'] = array();	
				$zulu->nav->breadcrumb[stripslashes($project_data['title'])] = array();
				$zulu->nav->title = "Edit Project";
			} else {
				$zulu->nav->breadcrumb['Print Project'] = array();	
				$zulu->nav->breadcrumb[stripslashes($project_data['title'])] = array();
				$zulu->nav->title = "Print Project";
			}
			
			function edit_bt($id,$project=0) {
				global $zulu,$print_page;
					if(!$print_page) {
					return "
						<a href=\"".$zulu->link_page('task',array('query'=>array('id'=>$id,'Action'=>'edit','Project'=>$project)))."\"><button class=\"btn btn-primary btn-circle\" type=\"button\"><i class=\"fas fa-edit\"></i></button></a> 
						<a class=\"confirm-delete\" href=\"".$zulu->link_page('task',array('query'=>array('id'=>$id,'Action'=>'delete','Project'=>$project)))."\"><button class=\"btn btn-danger btn-circle\" type=\"button\"><i class=\"fas fa-times\"></i></button></a> 
					";	
				} else {
					return "~";
				}
			}
			function edit_bt_bill($id,$project=0) {
				global $zulu,$print_page;
				if(!$print_page) {
				return "
					<a href=\"".$zulu->link_page('bill',array('query'=>array('id'=>$id,'Action'=>'edit','Project'=>$project)))."\"><button class=\"btn btn-primary btn-circle\" type=\"button\"><i class=\"fas fa-edit\"></i></button></a> 
					<a class=\"confirm-delete\" href=\"".$zulu->link_page('bill',array('query'=>array('id'=>$id,'Action'=>'delete','Project'=>$project)))."\"><button class=\"btn btn-danger btn-circle\" type=\"button\"><i class=\"fas fa-times\"></i></button></a> 
				";	
				} else {
					return "~";
				}
			}
			
			//PANEL - TASKS
			$table_column[] = array("Status",array('class'=>array('')));
			$table_column[] = array("Task",array('class'=>array('center')));
			$table_column[] = array("Time",array('class'=>array('center')));
			$table_column[] = array("Billable",array('class'=>array('')));
			$table_column[] = array("Approve",array('class'=>array('')));
			$table_column[] = array("Completed",array('class'=>array('right')));
			if(!$print_page) {
				$table_column[] = array("Actions",array('class'=>array('right')));
			}
			$table_row[] = array(
					"header"	=>	 true,
					"class"		=>	"",
					"content"	=>	$table_column);
					
			$data_row = $class_task->task_data(array('project_id'=>$id,'sort'=>'id ASC'));
			$incomplete_count = 0;
			foreach($data_row as $row) {
				$overdue = ($row['date_due']>0&&$row['date_due']<time()?true:false);
				if($row['status']<=1) {
					$incomplete_count++;	
				}
				if($row['time']!=0) {
					$time = zulu::time_encode($row['time']);
					$hours = zulu::leadingZero($time['h']);
					$minutes = $time['m'];
				} else {
					$hours = "";	
					$minutes = "";
				}
				$table_row[] = array("content" => array(
					array(($row['status']==2?"Complete":($row['date_due']>0&&$row['date_due']<time()?"<b>Overdue!</b>":"Pending"))),
					array(stripslashes($row['title'])),
					array((!$print_page?"H <input type=\"text\" name=\"task[{$row['id']}][hour]\" id=\"hour\" value=\"".$hours."\" maxlength=\"2\" ".($project_data['sale_id']>0?'disabled':NULL)."/> 
		M <input type=\"text\" name=\"task[{$row['id']}][minute]\" id=\"minute\" value=\"".$minutes."\" maxlength=\"2\" ".($project_data['sale_id']>0?'disabled':NULL)."/>":$hours.":".$minutes),array('class'=>array('time'))),
					array((!$print_page?"<input type=\"checkbox\" name=\"task[{$row['id']}][opt_bill]\" id=\"opt_bill\" value=\"1\" ".($row['opt_bill']==1?"checked":NULL)." ".($project_data['sale_id']>0?'disabled':NULL)."/>":$zulu->html_check(($row['opt_bill']==1?true:false))),array('class'=>array('center'))),
					array(($_GET['Sort']==2?(!$print_page?"<input class=\"action\" type=\"checkbox\" name=\"task[{$row['id']}][action]\" id=\"status\" value=\"1\" ".(($row['job_id']>0&&$project_data['status']!=1)?"disabled":"checked")." ".($project_data['sale_id']>0?'disabled':NULL)."/>":'s'):(!$print_page?"<input type=\"checkbox\" name=\"task[{$row['id']}][status]\" id=\"status\" value=\"2\" ".($row['status']==2?"checked":NULL)." ".($project_data['sale_id']>0?'disabled':NULL)."/>":$zulu->html_check(($row['status']==2?true:false)))),array('class'=>array('center'))),
					array(zulu::time_history($row['stat_complete'])),
					array(edit_bt($row['id'],$id),array('class'=>array('right','w100')))
				),'class'=>($row['status']==2?"green":($overdue?"yellow":NULL)));
				if($row['status']<2) {
					$count_task_pend++;
				}
				if($row['opt_bill']>0&&$row['time']>0) {
					$time_sum += $row['time'];
					$labour_sum += ($row['time']/60)*$class_task->hourly_rate(['task_data'=>$row]);
				} else {
					$time_no_sum += $row['time'];	
				}
				$total_row++;
			}
			$total_task = $labour_sum;
			$count_task = $total_row;
			$zulu->template->body->panel_task = $zulu->table_render($table_row,0,array('class'=>'basket','data_table'=>false));
			
			//PANEL - BILLS
			unset($table_column,$table_row,$total_row);
			$table_column[] = array("Status",array('class'=>array('')));
			$table_column[] = array("Title",array('class'=>array('')));
			$table_column[] = array("Total",array('class'=>array('')));
			$table_column[] = array("Date",array('class'=>array('')));
			$table_column[] = array(($_GET['Sort']==2?"Select":"Approve"),array('class'=>array('center')));
			if(!$print_page) { 
				$table_column[] = array("Actions",array('class'=>array('right')));
			}
			$table_row[] = array(
					"header"	=>	 true,
					"class"		=>	"",
					"content"	=>	$table_column);
			$incomplete_bill_count = 0;
			$wSQL = "user_id = '".$class_user->authorised->id."'";
			if($wSQL!=NULL) {
				//$wSQL .= " AND NOT EXISTS (SELECT id FROM sale_line il WHERE il.object = 'bill' AND il.object_id = bl.id)";	
			}
	
			$query = $db->mysqli->query("SELECT * FROM bill bl ".($wSQL!=NULL?"WHERE ".$wSQL:NULL)." AND task_id=0 AND job_id = '".PAGE_id."' ORDER BY status ASC, date ASC, title ASC") or die($db->mysqli->error);
			
			while($row = $query->fetch_assoc()) {
				
				if($row['client_id']>0) {
					$client_data = $class_client->client_data(array('id'=>$row['client_id']));
				}
				if($row['team_id']>0) {
					$team_data = $class_user->user_data(array('id'=>$row['team_id']));
				}
				if($row['job_id']>0) {
					$project_data = $class_project->project_data(array('id'=>$row['job_id']));
				}
				
				$desc = stripslashes(stripslashes($row['description']));
				if($_GET['Sort']==0 || !isset($_GET['Sort'])) $sort = 1;
				else $sort = 2;
				if($row['status']==2) {
					$checkbox_custom['checked'] = 'checked';
				}
				if($project_data['sale_id']>0) {
					$checkbox_custom['disabled'] = 'disabled';
				}
				if($row['status']!=2) {
					$incomplete_bill_count++;	
				}
				$table_row[] = array("content" => array(
					array(($row['status']==2?"Complete":($row['date_due']>0&&$row['date_due']<time()?"<b>Overdue! ".zulu::time_history($row['date_due'])."</b>":"Pending"))),
					array(stripslashes($row['title'])),
				//	array("$".number_format($row['price'],2)),
				//	array($row['quantity']),
					array("$".number_format($row['price']*$row['quantity'],2)),
					array(zulu::dateDecode($row['date'])),
					array((!$print_page?$form_edit->input_html("hidden","task[{$row['id']}][id]",$row['id']).$form_edit->input_html("checkbox","task[{$row['id']}][status]",$sort,array('custom'=>$checkbox_custom)):$zulu->html_check(($row['status']==2?true:false))),array('class'=>array('center'))),
					array(edit_bt_bill($row['id'],$id),array('class'=>array('right'))
					)),'class'=>($row['status']==2?"green":($overdue?"yellow":NULL))
				);
				
				if($row['status']<2) {
					$count_bill_pend++;
				}
				
				$total_sum += $row['price']*$row['quantity'];
				$total_row++;
			}
			$count_bill = $total_row;
			$total_bill = $total_sum;
			$zulu->template->body->panel_bill = $zulu->table_render($table_row,0,array('class'=>'basket','data_table'=>false));
		}
		
		//Graphs
		unset($table_row,$table_column);
		$class_task->graph(['element'=>'chart-task','data'=>['project_id'=>PAGE_id]]);
		$currency = $class_setting->defaults->currency_symbol[$class_setting->data['currency_symbol']];
		$time_label = zulu::time_encode($time_sum);
		$time_no_label = zulu::time_encode($time_no_sum);
		if($time_sum>0||$time_no_sum>0) {
			$percent_split = $time_sum/($time_no_sum+$time_sum);
			$percent_bill = " <span class=\"color-grey\">".number_format($percent_split*100,0)."%</span>";
			$percent_free = " <span class=\"color-grey\">".number_format((1-$percent_split)*100)."%</span>";
		}
					
		$table_row[] = array("content"=>[array("Total Labour Charge <span class=\"color-grey\">".($time_label['h']>0?$time_label['h']." Hours":NULL).($time_label['m']>0?" ".$time_label['m']." Mins":NULL)."</span>",array('class'=>array('c-label'))),array($currency.$zulu->dollar($total_task),array('class'=>array('c-value')))]);
		$table_row[] = array("content"=>[array("Total Billable Charges",array('class'=>array('c-label'))),array($currency.$zulu->dollar($total_bill),array('class'=>array('c-value')))]);
		
		$has_quote = $class_project->has_quote(PAGE_id);
		
		if($has_quote) {
			$quote_value = $class_quote->quote_total($class_project->vars->quote_id);
			$table_row[] = array("content"=>[array("<b>Quote</b>",array('class'=>array('c-label'))),array($currency.$zulu->dollar($quote_value),array('class'=>array('c-value')))]);
			$table_row[] = array("content"=>[array("Extra Charges <span class=\"color-grey\">(Per labour &amp; billables)</span>",array('class'=>array('c-label'))),array($currency.$zulu->dollar($total_task+$total_bill),array('class'=>array('c-value')))]);
			$table_row[] = array("content"=>[array("<b>Grand Total</b> <span class=\"color-grey\">(Includes quote and extras)</span>",array('class'=>array('c-label'))),array($currency.$zulu->dollar($quote_value+$total_task+$total_bill),array('class'=>array('c-value')))]);
		} else {
			$table_row[] = array("content"=>[array("<b>Project Value</b>",array('class'=>array('c-label'))),array($currency.$zulu->dollar($total_task+$total_bill),array('class'=>array('c-value')))]);
		}
	
		$table_labour_row[] = array("content"=>[array("Billed Labour",array('class'=>array('c-label'))),array(($time_sum>0?$time_label['h']." Hours".($time_label['m']>0?" ".$time_label['m']." Mins":NULL).$percent_bill:'-'),array('class'=>array('c-value')))]);
		
		$table_labour_row[] = array("content"=>[array("Non-billed Labour",array('class'=>array('c-label'))),array(($time_no_sum>0?$time_no_label['h']." Hours".($time_no_label['m']>0?" ".$time_no_label['m']." Mins":NULL).$percent_free:'-'),array('class'=>array('c-value')))]);
		
		$zulu->template->report = "
			<h3>Project Value</h3>
			".$zulu->table_render($table_row,0,array('class'=>'report','data_table'=>false))."
			<hr>
			<h3>Labour Breakdown</h3>
			".$zulu->table_render($table_labour_row,0,array('class'=>'report','data_table'=>false))."
		";
		
		//Other Vars
		$complete = ($project_data['status']==1?true:false);
		
		$zulu->template->page_def = [
			'owner'		=>	($project_data['user_id']==$class_user->authorised->id?true:false),
			'complete'	=>	($project_data['status']==1?true:false),
			'date_done'	=>	$zulu->time_fancy($project_data['time_end'])
		];
		
		//Actions - Comment
		if($_POST['method']=='comment') {
			if($form_edit->validate(array('message','name','email'))) {
				$zulu->notification_set("Please enter a name, email and comment.",2);
				$skip = true;
				$_SESSION['zl_form']['comment_error'] = true;
			}
			
			if(!$skip) {			
			print_r($client_data);
				$message = "Hello ".$setting_data['contact_name'].",<br><br>Your project for '".str_replace("<br>"," ",$client_data['name'])."' had a new comment sent on ".date("d/m/Y h:ia").".<br><br><b>Project:</b> ".$project_data['title']."<br><b>From:</b> ".$_POST['name']." (<a href=\"mailto:".$_POST['email']."\">".$_POST['email']."</a>)<br><b>Message:</b><br>".str_replace(chr(13),"<br>",$_POST['message'])."<br><br><b>View project online:</b> <a href=\"".$class_project->project_url($project_data['token'])."\">".$class_project->project_url($project_data['token'])."</a>";
				$zulu->mail_send($setting_data['contact_email'],"Your Project Has a Comment",$message,'',false,array('reply'=>$_POST['email'],'object'=>'project','object_id'=>$project_data['id']));
				$zulu->notification_set("Thanks, your comment was sent to us.",1);
				header("Location: ".$_SERVER['HTTP_REFERER']);
				exit;
			}
		}
		
		//Comment box error
		if($_SESSION['zl_form']['comment_error']) {
			$_SESSION['zl_form']['comment_error'] = false;
			$comment_nfc = true;
		} else {
			$zulu->template->jquery[] = "$(\"#box-comment\").hide();";	
		}
		
		//-- GET Method Override
		if($_GET['Method']=='status'&&isset($_GET['Status'])) {
			$_POST['action'] = 'edit';
			$data['status'] = $db->escape_string($_GET['Status']);
			$post_override = true;
		}
		
		//-- ACTION Buttons
		if($time_sum>0&&$total_task<=0) {
			$action_button[] = "<a href=\"".$zulu->link_page('client',array('query'=>array('id'=>$project_data['client_id'],'Action'=>'edit')))."\" target=\"_blank\"><button class=\"btn btn-info confirm-prompt\" type=\"button\" data-msg=\"This client has a $0.00 hourly rate, click 'confirm' to open the client settings page to adjust base rate.\"><span class=\"far fa-clock\"></span> Set Hourly Rate</button></a>";
		}
		if(!$complete&&($count_task>0||$count_bill>0)&&$incomplete_count<=0&&$incomplete_bill_count<=0) {
            $action_button[] = "<a href=\"".$zulu->link_page('project',array('query'=>array('id'=>PAGE_id,'Action'=>'edit','Method'=>'status','Status'=>1)))."\"><button class=\"btn btn-success confirm-prompt\" type=\"button\" data-msg=\"This will mark this project as 'complete', then you can create a final sale for the client (if required).\"><span class=\"fas fa-check\"></span> Mark Complete</button></a>";
		}
		if($quote_id>0) {
        	$action_button[] = "<a href=\"".$zulu->link_page('quote',array('query'=>array('id'=>$quote_id,'Action'=>'print')))."\"><button class=\"btn btn-default\" type=\"button\"><span class=\"far fa-file\"></span> View Quote</button></a>";
         }
		 if($_POST['sale_id']<=0) {
			 if(($_POST['status']==1&&$count_task_pend<=0&&$count_bill_pend<=0)) {
				 $action_button[] = "<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>PAGE_id,'Action'=>'create_sale')))."\"><button class=\"btn btn-primary\" type=\"button\"><span class=\"far fa-money-bill\"></span> Generate Sale</button></a>";
			 } else {
				/*$action_button[] = "<button class=\"btn btn-primary disabled\" type=\"button\" title=\"Ensure the project is complete, and no tasks or billables are pending.\"><span class=\"far fa-money-bill\"></span> Generate Sale</button>"; -- DISABLE SO NO ACTION BOX SHOWS */
				$nfc = "<span class=\"fas fa-info-circle\"></span> Once your project is marked 'complete' and you have specified at least one task and/or billable, you can generate a final sale invoice for that project.";
	   }
   }
   if($_POST['sale_id']>0) {
	$action_button[] = "<a href=\"".$zulu->link_page('sale',array('query'=>array('id'=>$_POST['sale_id'],'Action'=>'edit','Method'=>'View')))."\"><button class=\"btn btn-success\" type=\"button\"><span class=\"far fa-money-bill\"></span> View Sale</button></a>";
    }
		
		//Form Submit
		if($_POST['action']=='edit') {
			$form_edit->valid = true;
			
			if($form_edit->valid) {
				
				if(!$post_override) {
					$data['title'] = addslashes($_POST['title']);
					$data['notes'] = addslashes($_POST['notes']);
					$data['time_start'] = zulu::dateEncode($_POST['time_start']);
					$data['time_end'] = zulu::dateEncode($_POST['time_end']);
					$data['status'] = $_POST['status'];
					$data['client_id'] = $_POST['client_id'];
					
					$fields = array('client_id','title','notes','time_start','time_end','status');
				} else {
					foreach($data as $key=>$val) {
						$fields[] = $key;	
					}
				}
				if($data['status']!=$project_data['status']&&$data['status']==1) {
					$data['time_end'] = time();
					$fields[] = 'time_end';
				}
				$post_data = $data;
				$data = $class_project->project_edit($id,$data);
				
				if($project_data['status']!=2&&$post_data['status']==2) {
					$ex_msg = "Project was marked on hold &amp; archived.";
				}
				if($project_data['status']!=1&&$post_data['status']==1) {
					$ex_msg = "Project was marked complete, end date set to current date.";
				}
				
				if($data['success']) {
					$zulu->notification_set("Project ".(!$new?"updated":"created")." successfully. ".$ex_msg,1);
					header("Location: ".$zulu->link_page(PAGE_file,array('query'=>array('id'=>($new?$data['id']:$id),'Action'=>'edit'))));
					/*if($project_data['status']=='0' && $_POST['status']=='1') {
						
					} else {
						header("Location: ".$zulu->link_page(PAGE_file));
					}*/
					exit;
				} else {
					$zulu->notification_set("A database error occurred.",2);
				}
			}
		}
		//Update - tasks
		if($_POST['action'] == "update") {
			foreach($_POST['task'] as $key=>$val) {
				$row = $class_task->task_data(array('id'=>$key));
				$client = $class_client->client_data(array('id'=>$row['client_id']));
				$job = $class_project->project_data(array('id'=>$row['job_id']));
				$time = zulu::time_decode($val['hour'],$val['minute']);
				$status = ($val['status']!=NULL?$val['status']:1);
				if(($status==2 || $status==1) && $row['status']==0)
					$stat_complete = time();
				else if(($status==2 || $status==1) && $row['status']>0)
					$stat_complete = $row['stat_complete'];
				else
					$stat_complete = 0;
				$data = array('time'=>$time,'status'=>$status,'stat_complete'=>$stat_complete,'opt_bill'=>($val['opt_bill']==1?1:0));
				$class_task->task_edit($key,$data);
			}
			$zulu->notification_set("Tasks updated.",1);
			header("Location: ".$zulu->link_page(PAGE_file,array('query'=>array('id'=>PAGE_id,'Action'=>'edit'))));
			exit;
		}
	}
	if(PAGE_action=='create_sale') { //create sale
		$project_data = $class_project->project_data(array('id'=>PAGE_id));
		$task_data = $class_task->task_data(array('project_id'=>PAGE_id));
		$client_data = $class_client->client_data(array('id'=>$project_data['client_id']));
		$client_id = $client_data['id'];
		$quote_data = $zulu->table_data("quote",0,array("where"=>array("project_id = '".PAGE_id."'"),"first"=>true));
		$quote_id = $quote_data['id'];
		
		if($client_id<=0) {
			$zulu->notification_set("Please select a client for the project to create a sale.",2);
			header("Location: ".$_SERVER['HTTP_REFERER']);
			exit;	
		}
		
		//convert tasks to bill
		foreach($task_data as $task) {
			$task_time = $zulu->time_encode($task['time']);
			
			$hourly_rate = $client_data['hourly_rate'];
			if($task['opt_bill']>0) {
				$hourly_rate = 0;	
				$title_suffix = "(No Charge)";
			}
			
			$bill_data = array(
				'title'			=>	($task['title']==NULL?'Untitled Item':$task['title']).$title_suffix,
				'price'			=>	(($task['time']/60)*$hourly_rate),
				'quantity'		=>	1,
				'client_id'		=>	$project_data['client_id'],
				'task_id'		=>	$task['id'],
				'job_id'		=>	PAGE_id,
				'description'	=>	"Labour: ".$task_time['h']."HR ".$task_time['m']."M\n".$task['description'],
				'date'			=>	($task['stat_complete']<=0?time():$task['stat_complete']),
				'status'		=>	2
			);
			$bill_data = $class_bill->update(0,$bill_data);
			$data = array('opt_invoice'=>1,'bill_id'=>$bill_data['id']);
			$class_task->task_edit($task['id'],$data);
			unset($title_suffix);
		}
		
		//add remainder quote line
		if($quote_id>0) {
			$price = $class_quote->quote_balance($quote_data['id']);	
			$quote_text = "Final Payment as per Quote #".$quote_data['id']." ".($quote_data['reference']!=NULL?"(Ref: ".$quote_data['reference'].")":NULL);
			$sale_create_array[$project_data['client_id']][$project_data['id']][] = array(
				'description'	=>	$quote_text,
				'price'	=>	$class_quote->quote_balance($quote_data['id']),
				'quantity'	=>	1,
				'object_id' => $quote_data['id'],
				'object'	=> 'quote'
			);
		}
		
		//add each bill line
		$bill_data = $zulu->table_data('bill AS bl',0,array('where'=>array("job_id = '".PAGE_id."'")));
		foreach($bill_data as $bill_data) {
			$sale_check = $zulu->table_data('sale_line',0,array("where"=>array("object = 'bill'","object_id = '".$bill_data['id']."'"),"first"=>true));
			if($sale_check['id']<=0) { //not already in sale
				$sale_create_array[$bill_data['client_id']][$bill_data['job_id']][] = array(
					'description'	=>	$bill_data['title'].(trim($bill_data['description'])!=NULL?" - ".$zulu->shorten($bill_data['description'],100):NULL),
					'price'	=>	$bill_data['price'],
					'quantity'	=>	$bill_data['quantity'],
					'product_id' => $bill_data['product_id'],
					'object_id' => $bill_data['id'],
					'object'	=> 'bill'
				);	
				$sale_create_array[$bill_data['client_id']]['bill_id'][] = $bill_data['id'];
			}
		}
		
		foreach($sale_create_array as $client_id=>$sdata) {
			$line_id_array = [];
			foreach($sdata as $lines) {
				foreach($lines as $line_data) {
					$conc_line_data[] = $line_data;
				}
				unset($quote_text);
			}
			
			$do = $class_sale->sale_edit(0,array(
				'client_id'	=>$client_id,
				'name'		=>$client_data['name'],
				'email'		=>$client_data['email'],
				'date'		=>date('d/m/Y',time()),
				'date_due'	=>date('d/m/Y',strtotime("+7 days")),
				'line'	=> $conc_line_data
			),array('complete'=>true));
			
			if($do['success']) {
				foreach($sdata['bill_id'] as $bill_id) {
					$db->query("UPDATE bill SET invoice_id = '".$do['id']."' WHERE id = '{$bill_id}'");	
				}
			}
			unset($project_data,$conc_line_data);
		}
		
		$update = $class_project->project_edit(PAGE_id,array('sale_id'=>$do['id']));
		
		$zulu->notification_set("Sale created successfully. You can now share this with the client: <a href=\"".$zulu->link_page('sale',['query'=>['Action'=>'edit','id'=>$do['id']]])."\" target=\"_blank\">View Sale</a>",1);
		header("Location: ".$zulu->link_page(PAGE_file,array('query'=>array('id'=>PAGE_id,'Action'=>'edit'))));
		exit;
	}
	
	if(PAGE_action=='print') {
		$zulu->template->js_file[] = TPL_rel."assets/quote.js";
		$zulu->template->css_file[] = TPL_rel."css/document.css";
		$TPL_body_ovr = 'body-print-project.php';	
		
		/*
		$company_out = $class_client->data_format($setting_data,'company');
		
		//Client
		$client_data = $class_client->client_data(array('id'=>$project_data['client_id'],'ovr_user_id'=>true));
		$client_meta = $class_client->client_meta($project_data['client_id']);
		
		$user_data = $class_user->user_data(array('id'=>$project_data['user_id']));
		$user_meta = $class_user->user_meta($project_data['user_id']);
		
		//PANEL - TASKS
		$table_column[] = array("Status",array('class'=>array('')));
		$table_column[] = array("Task",array('class'=>array('center')));
		$table_column[] = array("Time",array('class'=>array('center')));
		$table_column[] = array("Billable",array('class'=>array('')));
		$table_column[] = array("Added",array('class'=>array('right')));
		$table_column[] = array("Completed",array('class'=>array('right')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);
				
		$data_row = $class_task->task_data(array('project_id'=>$project_data['id']));
		$incomplete_count = 0;
		foreach($data_row as $row) {
			$overdue = ($row['date_due']>0&&$row['date_due']<time()?true:false);
			if($row['status']<=1) {
				$incomplete_count++;	
			}
			if($row['time']!=0) {
				$time = zulu::time_encode($row['time']);
				$hours = zulu::leadingZero($time['h']);
				$minutes = $time['m'];
			} else {
				$hours = "00";	
				$minutes = "00";
			}
			$table_row[] = array("content" => array(
				array(($row['status']==2?"Complete":($row['date_due']>0&&$row['date_due']<time()?"<b>Overdue!</b>":"Pending"))),
				array(stripslashes($row['title'])),
				array($hours.":".$minutes,array('class'=>array('time'))),
				array("<span class='fas fa-".($row['opt_bill']==1?"check":"times")."'></span>",array('class'=>array('center'))),
				array(zulu::time_history($row['stat_add'])),
				array(zulu::time_history($row['stat_complete']))
			),'class'=>($row['status']==2?"green":($overdue?"yellow":NULL)));
			if($row['status']<2) {
				$count_task_pend++;
			}
			$total_row++;
			$total_time += $row['time'];
		}
		if($total_time!=NULL && $total_time>0) {
			$time = zulu::time_encode($total_time);
			$hours = zulu::leadingZero($time['h']);
			$minutes = $time['m'];
		} else {
			$hours = "00";	
			$minutes = "00";
		}
		$count_task = $total_row;
		$zulu->template->body->panel_task = $zulu->table_render($table_row,0,array('class'=>'basket','data_table'=>false));
		
		//PANEL - BILLS
		unset($table_column,$table_row,$total_row);
		$table_column[] = array("Status",array('class'=>array('')));
		$table_column[] = array("Title",array('class'=>array('')));
		$table_column[] = array("Price",array('class'=>array('')));
		$table_column[] = array("Quantity",array('class'=>array('')));
		$table_column[] = array("Total",array('class'=>array('')));
		$table_column[] = array("Date",array('class'=>array('')));
		$table_column[] = array(($_GET['Sort']==2?"Select":"Paid"),array('class'=>array('center')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);
				
		$wSQL = "user_id = '".$class_user->authorised->id."'";
		if($wSQL!=NULL) {
			//$wSQL .= " AND NOT EXISTS (SELECT id FROM sale_line il WHERE il.object = 'bill' AND il.object_id = bl.id)";	
		}

		$query = $db->mysqli->query("SELECT * FROM bill bl ".($wSQL!=NULL?"WHERE ".$wSQL:NULL)." AND task_id=0 AND job_id = '".PAGE_id."' ORDER BY status ASC, date ASC, title ASC") or die($db->mysqli->error);
		
		while($row = $query->fetch_assoc()) {
			
			if($row['client_id']>0) {
				$client_data = $class_client->client_data(array('id'=>$row['client_id']));
			}
			if($row['team_id']>0) {
				$team_data = $class_user->user_data(array('id'=>$row['team_id']));
			}
			if($row['job_id']>0) {
				$project_data = $class_project->project_data(array('id'=>$row['job_id']));
			}
			
			$desc = stripslashes(stripslashes($row['description']));
			if($_GET['Sort']==0 || !isset($_GET['Sort'])) $sort = 1;
			else $sort = 2;
			if($row['status']=='2') {
				$checkbox_custom['checked'] = 'checked';
			}
			if($project_data['sale_id']>0) {
				$checkbox_custom['disabled'] = 'disabled';
			}
			$table_row[] = array("content" => array(
				array(($row['status']==2?"Complete":($row['date_due']>0&&$row['date_due']<time()?"<b>Overdue! ".zulu::time_history($row['date_due'])."</b>":"Pending"))),
				array(stripslashes($row['title'])),
				array("$".number_format($row['price'],2)),
				array($row['quantity']),
				array("$".number_format($row['price']*$row['quantity'],2)),
				array(zulu::dateDecode($row['date'])),
				array($form_edit->input_html("hidden","task[{$row['id']}][id]",$row['id']).$form_edit->input_html("checkbox","task[{$row['id']}][status]",$sort,array('custom'=>$checkbox_custom)),array('class'=>array('center')))
				),'class'=>($row['status']==2?"green":($overdue?"yellow":NULL))
			);
			
			if($row['status']<2) {
				$count_bill_pend++;
			}
			
			$total_sum += $row['price']*$row['quantity'];
			$total_row++;
		}
		$cost = $class_sale->payment_summary($total_sum);
		$count_bill = $total_row;
		$zulu->template->body->panel_bill = $zulu->table_render($table_row,0,array('class'=>'basket','data_table'=>false));
		*/
	}
	if(PAGE_action=='template') {
		
		function edit_bt($id,$item_count=0) {
			global $zulu;
			return "
				<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('Quote'=>$id,'Action'=>'item_edit')))."\" title=\"New Item\"><button class=\"btn btn-default btn-circle\" type=\"button\"><i class=\"fas fa-plus-circle\"></i></button></a> 
				".($item_count>0?"<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('Quote'=>$id,'Action'=>'item')))."\" title=\"View Items\"><button class=\"btn btn-info btn-circle\" type=\"button\"><i class=\"fas fa-list-ul\"></i></button></a> ":NULL)."
				<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'template_copy')))."\" title=\"Copy Template\"><button class=\"btn btn-success btn-circle\" type=\"button\"><i class=\"fas fa-copy\"></i></button></a> 
				<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'template_edit')))."\" title=\"Edit Template\"><button class=\"btn btn-primary btn-circle\" type=\"button\"><i class=\"fas fa-edit\"></i></button></a> 
				<a class=\"confirm-delete\" href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'delete','Type'=>'template')))."\" title=\"Delete Template\"><button class=\"btn btn-danger btn-circle\" type=\"button\"><i class=\"fas fa-times\"></i></button></a>
			";	
		}
				
		$table_column[] = array("Title",array('class'=>array('')));
		$table_column[] = array("Task Count",array('class'=>array('')));
		$table_column[] = array("Billable Count",array('class'=>array('')));
		$table_column[] = array("Added",array('class'=>array('')));
		$table_column[] = array("Actions",array('class'=>array('')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);
				
		$project_data = $class_project->project_data(array('template'=>'1'));

		foreach($project_data as $row) {
			
			$data_row = $class_project->template_item_data(array('project_id'=>$row['id']));
			foreach($data_row as $dr_row) {
				if($dr_row['type']=='task') {
					$count_task++;
				} else {
					$count_bill++;
				}
			}
			
			$table_row[] = array("content" => array(
				array(stripslashes($row['title'])),
				array($count_task),
				array($count_bill),
				array(zulu::time_history($row['stat_add'])),
				array(edit_bt($row['id'],count($item_data)),array('class'=>array('right')))
			));
			unset($count_bill,$count_task);
		}
		
		$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'basket'));
		$zulu->nav->title = PAGE_name;
		$zulu->nav->breadcrumb[PAGE_name.' Templates'] = array();	
	}
	if(PAGE_action=='template_edit') { //edit page
		$form_edit = new form;
		
		$zulu->template->css_file[] = "//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css";
		$zulu->template->js_file[] = "//code.jquery.com/ui/1.11.4/jquery-ui.js";
		$zulu->template->js_code[] = "
      	$(document).ready(function(){
			$(\"input[name='date_start'],input[name='date_end']\").datepicker({ dateFormat: \"dd/mm/yy\" });
	  	});
		";
		
		if(PAGE_id<1) {
			$id = 0;
			$new = true;	
			$zulu->nav->breadcrumb['New Template'] = array();
			$zulu->nav->title = "New Template";
		} else {
			$id = ($_POST['id']>0?$_POST['id']:($_GET['id']>0?$_GET['id']:0));
			
			$project_data = $class_project->project_data(array('id'=>$id));
			
			if(!$_POST) {
				foreach($project_data as $key=>$val) {
					$_POST[$key] = $val;	
				}	
			}
			
			$zulu->nav->breadcrumb['Edit Template'] = array();	
			$zulu->nav->breadcrumb[stripslashes($project_data['title'])] = array();
			$zulu->nav->title = "Edit Template";
			
			function edit_bt($id) {
				global $zulu;
				return "
					<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'task_edit')))."\"><button class=\"btn btn-primary btn-circle\" type=\"button\"><i class=\"fas fa-edit\"></i></button></a> 
					<a class=\"confirm-delete\" href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'task_delete')))."\"><button class=\"btn btn-danger btn-circle\" type=\"button\"><i class=\"fas fa-times\"></i></button></a> 
				";	
			}
			
			//LOAD DATA - COMBINED ITEMS	
			$data_row = $class_project->template_item_data(array('project_id'=>$id));

			foreach($data_row as $row) {
				$row_config = unserialize($row['config']);
				if($row['type']=='task') {
					$table_row_task[] = array("content" => array(
						array("<span class=\"color-grey\">#".(1+$count_task)."</span> ".stripslashes($row['title'])),
						array(($row_config['opt_bill']>0?"<span class=\"opt opt-success\"><span class=\"fas fa-check\"></span> Yes</span>":"<span class=\"opt opt-danger\"><span class=\"fas fa-times\"></span> No</span>"),array('class'=>array('center'))),
						array(edit_bt($row['id']),array('class'=>array('right','w100')))
					),'class'=>($row['status']==2?"green":($overdue?"yellow":NULL)));
					$count_task++;
				} else {
					$table_row_bill[] = array("content" => array(
						array(stripslashes($row['title'])),
						array("$".$zulu->dollar($row_config['price']*$row_config['quantity']),array('class'=>array('center'))),
						array(edit_bt($row['id']),array('class'=>array('right','w100')))
					),'class'=>($row['status']==2?"green":($overdue?"yellow":NULL)));
					$count_bill++;
				}
			}
			
			//RENDER - TASKS
			$table_column[] = array("Task",array('class'=>array('')));
		//	$table_column[] = array("Time",array('class'=>array('center')));
			$table_column[] = array("Billable",array('class'=>array('center')));
			$table_column[] = array("Actions",array('class'=>array('right')));
			$table_row_task[] = array(
					"header"	=>	 true,
					"class"		=>	"",
					"content"	=>	$table_column);
			$zulu->template->body->panel_task = $zulu->table_render($table_row_task,0,array('class'=>'basket','data_table'=>false));
			
			//PANEL - BILLS
			unset($table_column,$table_row,$total_row);
			$table_column[] = array("Title",array('class'=>array('')));
			$table_column[] = array("Total",array('class'=>array('center')));
			$table_column[] = array("Actions",array('class'=>array('right')));
			$table_row_bill[] = array(
					"header"	=>	 true,
					"class"		=>	"",
					"content"	=>	$table_column);
			$zulu->template->body->panel_bill = $zulu->table_render($table_row_bill,0,array('class'=>'basket','data_table'=>false));
		}
		
		
		if($_POST['action'] == "edit") {
			$form_edit->valid = true;
			
			if($form_edit->valid) {
				
				$data['title'] = addslashes($_POST['title']);
				$data['notes'] = addslashes($_POST['notes']);
				$data['template'] = 1;
				$data = $class_project->project_edit($id,$data);
				
				if($data['success']) {
					$id = $data['id'];
					$zulu->notification_set("Template ".(!$new?"updated":"created")." successfully.",1);
					if($new) {
						header("Location: ".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'template_edit'))));
					} else {
						header("Location: ".$zulu->link_page(PAGE_file,array('query'=>array('Action'=>'template'))));
					}
					exit;
				} else {
					$zulu->notification_set("A database error occurred.",2);
				}
			}
		}
	}
	if(PAGE_action=='task_delete') {
		$data = $class_project->template_item_data(['id'=>PAGE_id]);
		$job_id = $data['job_id'];
		if($class_project->template_item_delete(PAGE_id)) {
			$zulu->notification_set("Item removed successfully.",1);
			header("Location: ".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$job_id,'Action'=>'template_edit'))));
		} else {
			$zulu->notification_set("A database error occurred.",2);
		}
		exit;
	}
	if(PAGE_action=='task_edit') {
		$form_edit = new form;
	
		//-- Jquery
		$zulu->template->jquery[] = "
		$(\".price-field\").keyup(function() {
			var price = $(\"input[name='price']\").val();
			var quantity = $(\"input[name='quantity']\").val();
			var total = price*quantity;
			$(\".label-price\").html(total.toFixed(2));
			return false;
		});
		
		toggle_row(1);
		$(\"select[name='type']\").change(function() {
		toggle_row();
		});
		";
		$zulu->template->js_code[] = "
		function toggle_row(instant) {
			var speed = 500;
			if(instant>0) {
				speed = 0;
			}
			var tpl = $(\"select[name='type']\").val();
			if(tpl=='bill') {
				$(\".rw-bill\").slideDown(speed);
				$(\".rw-task\").slideUp(speed);
			} else {
				$(\".rw-bill\").slideUp(speed);
				$(\".rw-task\").slideDown(speed);
			}
			return false;
		}
		";
		
		if(PAGE_id<1) {
			$id = 0;
			$new = true;	
			$zulu->nav->breadcrumb['New Item'] = array();
			$zulu->nav->title = "New Item";
		} else {
			$id = ($_POST['id']>0?$_POST['id']:($_GET['id']>0?$_GET['id']:0));
			$task_data = $class_project->template_item_data(array('id'=>$id));
			
			if(!$_POST) {
				foreach($task_data as $key=>$val) {
					$_POST[$key] = $val;	
				}
				$_POST['config'] = unserialize($_POST['config']);
				$_POST['total_line'] = $zulu->dollar($_POST['config']['price']*$_POST['config']['quantity']);
			}
			$_POST['description'] = stripslashes($_POST['description']);	
			
			$zulu->nav->title = "Edit Item";
		}
		
		//Var Override
		if($_POST['type']==NULL) {
			$_POST['type'] = $_GET['Type'];	
		}
		if($_POST['job_id']==NULL) {
			$_POST['job_id'] = $_GET['Project'];	
		}
		
		$zulu->nav->breadcrumb['Template'] = array('link'=>$zulu->link_page(PAGE_file,['query'=>['Action'=>'template_edit','id'=>$_POST['job_id']]]));
		$zulu->nav->breadcrumb[($new?'New':'Edit').' Item'] = array();	
		if(PAGE_id>0) {
			$zulu->nav->breadcrumb[stripslashes($task_data['title'])] = array();
		}
		
		//Form Submit
		if($_POST['action']=='edit') {
			$form_edit->valid = true;
			
			if($form_edit->valid) {
				$data['opt_bill'] = ($_POST['opt_bill']==1?1:0);
				$data['title'] = addslashes($_POST['title']);
				$data['sort'] = addslashes($_POST['sort']);
				$data['description'] = addslashes($_POST['description']);
				$data['type'] = $_POST['type'];
				$data['job_id'] = $_POST['job_id'];

				$data = $class_project->template_item_edit($id,$data);
				
				if($data['success']) {
					$zulu->notification_set("Item ".(!$new?"updated":"created")." successfully.",1);
					header("Location: ".$zulu->link_page(PAGE_file,['query'=>['Action'=>'template_edit','id'=>$_POST['job_id']]]));
					exit;
				} else {
					$zulu->notification_set("A database error occurred.",2);
				}
			}
		}
	}
	if(PAGE_action=='template_copy') {
		$id = PAGE_id;
		$project_data = $class_project->project_data(array('id'=>$id));
		$data_row = $class_project->template_item_data(array('project_id'=>$id));
		
		// copy project
		$query = "INSERT INTO job (user_id,title,notes) SELECT user_id,title,notes FROM job WHERE id = '{$id}'";
		if($db->query($query)) {
			$new_id = $db->insert_id;
			$update = $class_project->project_edit($new_id,array('token'=>zulu::serial(16),'time_start'=>time(),'time_end'=>strtotime("+1 month"),'stat_add'=>time()));
		} else {
			$zulu->notification_set("Failed to copy template.",2);
			header("Location: ".$zulu->link_page(PAGE_file,array('query'=>array('Action'=>'template'))));
			exit;
		}
		
		// copy items
		foreach($data_row as $row) {
			$row_config = unserialize($row['config']);
			if($row['type']=='task') {
				$data = array('job_id'=>$new_id,'title'=>$row['title'],'description'=>$row['description'],'date_start'=>time(),'opt_bill'=>$row_config['opt_bill']);
				$class_task->task_edit(0,$data);
			} else {
				$data = array('job_id'=>$new_id,'title'=>$row['title'],'description'=>$row['description'],'date'=>time(),'price'=>$row_config['price'],'quantity'=>$row_config['quantity']);
				$class_bill->update(0,$data);
			}
		}
		/*
		foreach($item_data as $item) {
			$query = "INSERT INTO ".PAGE_file."_item (title,description,billing,billing_title,billing_time,billing_quantity,billing_price,billing_sort,show_quote,sort) SELECT title,description,billing,billing_title,billing_time,billing_quantity,billing_price,billing_sort,show_quote,sort FROM ".PAGE_file."_item WHERE id = '".$item['id']."'";
			if($db->query($query)) {
				$temp_id = $db->insert_id;
				$update = $class_quote->item_edit($temp_id,array('stat_add'=>time(),'quote_id'=>$new_id));
			}
		}*/
				
		$zulu->notification_set("Project template successfully copied.",1);
		header("Location: ".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$new_id,'Action'=>'edit'))));
		exit;
	}
}