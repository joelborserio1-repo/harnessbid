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
	$class_user->authorised->opt_support = true;
} else {
	$class_user->user_authorised_check();
}
if(!$class_user->authorised->opt_support) {
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
		//$class_support = new support();
		//$support_config = new array();
		//Array for tabs config
		$config_for_tabs = array();
		if(isset($_GET['Sort']) && $_GET['Sort'] !=0) {
			$sql_status;
			switch ($_GET['Sort']) {
				case 1:
					$sql_status = 'Open';
				break;
				case 2:
					$sql_status = 'Hold';
				break;
				case 3:
					$sql_status = 'Closed';
				break;
			}
			$wSQL = array('status'=>$db->escape_string($sql_status));
			$tab = $_GET['Sort'];
			$wSQL['template'] = '0';
		}
		if (isset($_GET['SaleID'])){
			$wSQL['object_id'] = $_GET['SaleID'];
			$wSQL['object'] = 'sale';
			$sale_data = $class_sale->sale_data(array('id'=>$_GET['SaleID']));
			$new_ticket_html = '<a href="'.$zulu->link_page(PAGE_file,array('query'=>array('Action'=>'edit_ticket','object'=>'sale', 'object_id'=>$_GET['SaleID'],'client_id'=>$_GET['client_id']))).'"><button class="btn btn-primary" type="button"><span class="fas fa-plus-circle"></span> New Ticket</button></a>';
			$zulu->nav->breadcrumb['For Sale #'.$sale_data['reference']] = array();
			$config_for_tabs['SaleID'] = $_GET['SaleID'];
			$config_for_tabs['client_id'] = $_GET['client_id'];
		}else{
			$new_ticket_html = '<a href="'.$zulu->link_page(PAGE_file,array('query'=>array('Action'=>'edit_ticket'))).'"><button class="btn btn-primary" type="button"><span class="fas fa-plus-circle"></span> New Ticket</button></a>';
		}
		$data_rows = $class_support->support_data($wSQL);
		function create_function_buttons($id) {
			global $zulu;
			return "
				<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'view_messages')))."\"><button class=\"btn btn-primary btn-circle\" type=\"button\"><i class=\"far fa-comment\"></i></button></a>
				<a class=\"confirm-delete\" href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'delete_ticket','Sort'=>$_GET['Sort'])))."\"><button class=\"btn btn-danger btn-circle\" type=\"button\"><i class=\"fas fa-times\"></i></button></a> 
			";	
		}
		
		$table_column[] = array("User ID",array('class'=>array('')));
		$table_column[] = array("Subject",array('class'=>array('')));
		$table_column[] = array("Status",array('class'=>array('')));
		$table_column[] = array("Linked Item",array('class'=>array('')));
		$table_column[] = array("Added",array('class'=>array('')));
		$table_column[] = array("Updated",array('class'=>array('')));
		$table_column[] = array("Actions",array('class'=>array('')));

			$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);
		
		foreach($data_rows as $row) {
			if($row['client_id']>0) {
				$client_data = $class_client->client_data(array('id'=>$row['client_id']));
			}
			//Get the object
			$linked_item_html = '-';
			if($row['object_id'] != NULL){
				$sale_sql_config['id'] = $row['object_id'];
				switch ($row['object']) {
					case 'sale':
						$sale_data = $class_sale->sale_data($sale_sql_config);
						$linked_item_html= '<a href="'.$zulu->object_link('sale',$sale_data['id']).'">Sale #'.$sale_data['reference'].'<a>';
						break;
				}	
			}
			
			//Get the status
			$has_new = $class_support->support_unread($row['id'],true);
			$status_indicator_colour = 'success';
			$status_indicator = 'check';
			switch ($row['status']) {
				case 'Open':
					$status_indicator_colour = 'success';
					$status_indicator = 'check';
				break;
				case 'Hold':
					$status_indicator_colour = 'warning';
					$status_indicator = 'pause';
				break;
				case 'Closed':
					$status_indicator_colour = 'danger';
					$status_indicator = 'times';
				break;
			}
			$table_row[] = array("content" => array(
				array("<a href=\"".$zulu->link_page('client',array('query'=>array('Action'=>'edit','id'=>$row['client_id'])))."\">".stripslashes(($client_data['company']!=NULL?$client_data['company']:$client_data['name_first']." ".$client_data['name_last']))."</a>"),
				array($row['subject']),
				array('</p><span class="opt opt-'.$status_indicator_colour.' opt-bord"><span class="fas fa-'.$status_indicator.'"></span> '.$row['status'].'</span>'
					.($has_new?'    <span class="opt opt-warning opt-bord"><span class="fas fa-envelope"></span> New':NULL).'</span></span></p>'),
				array($linked_item_html),
				array(zulu::dateDecode($row['stat_add'])),
				array(zulu::dateDecode($row['stat_update'])),
				array(create_function_buttons($row['id']),array('class'=>array('right','w120')))
			));
		}
		if(isset($sql_status)){
			$count = $class_support->support_count(array());
			$class_cache->save('support_count',$count);
		}else{
			
		}
		
		$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>''));
		$zulu->nav->title = PAGE_name;
	}
	
	if(PAGE_action=='view_messages'){
		$zulu->template->css_file[] = "https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css";
		$zulu->template->js_file[] = "//code.jquery.com/ui/1.11.4/jquery-ui.js";	
		$zulu->template->js_code[] = '
										var message_window_height = document.getElementById("message-window").scrollHeight;
										$("#message-window").scrollTop(message_window_height);
										$( function() {
												refreshContent();
												function refreshContent(){
													// do whatever you like here
													var ticket_id = $("#current_ticket_id").val();
													if(ticket_id>0){
														$.post("/admin/index.php?Page=support&Action=view_messages",
														{
															ticket_id: ticket_id,
															action: "get_support_content"
														},
														function(data, status){
															data = JSON.parse(data);
															if(data.success){
																$("#message-window").html(data.message_html);
																var message_window_height = document.getElementById("message-window").scrollHeight;
																$("#message-window").scrollTop(message_window_height);
															}else{
																console.log("Error getting messages.");
																console.log(data);

															}
														});
													}
													console.log("Refreshing message content");
													setTimeout(refreshContent, 5000);
												}

											});';
		
		
		//Ajax get content
		if($_POST['action'] == 'get_support_content' && $_SESSION['zl_user']['id']>0 && $_POST['ticket_id']){
			$return_array = [];
			//load messages for ticket
			$message_data_rows = $class_support->message_data(['support_ticket_id'=>$_POST['ticket_id']]);
			$message_html = '';
			foreach($message_data_rows as $message){
				$message_html .= $class_support->message_create_bubble($message);
			}
			echo json_encode(['success'=>true, 'message_html'=>$message_html]);
			exit;
		}else if($_POST['action'] == 'get_support_content'){
			echo json_encode(['success'=>false]);
			exit;
		}
		
		$message_form_edit = new form;
		//echo("viewing ticket<br>");
		//echo "this is the id:  ".$_GET['id'];
		$ticket_sql_config['id'] = $_GET['id'];
		
		//$class_support = new support();
		$ticket_data_row = $class_support->support_data($ticket_sql_config);
		//print_r($ticket_data_row);
		if($_POST){
			$notify_client = $_POST['send_email'];
			$reply_data = $_POST['reply_message'];
			if(!$message_form_edit->validate(['reply_message'])){
				$class_support->new_ticket_message($_GET['id'],$reply_data,false,$notify_client);
			}else{
				$zulu->notification_set("Please enter a message to send.",2);
			}	
		}
		$ticket_client_id = $ticket_data_row['client_id'];
		//$ticket_admin_id = $ticket_data_row['admin_id'];
		$ticket_subject = $ticket_data_row['subject'];
		$ticket_status = $ticket_data_row['status'];
		$ticket_object = $ticket_data_row['object'];
		$ticket_added = $ticket_data_row['stat_add'];
		$ticket_updated = $ticket_data_row['stat_update'];
		
		$status_indicator = 'success';
		switch ($ticket_status) {
    		case 'Open':
				$status_indicator = 'success';
        	break;
    		case 'Hold':
				$status_indicator = 'warning';
        	break;
    		case 'Closed':
				$status_indicator = 'danger';
        	break;
		}
		//$admin_link = $class_user->admin_link($ticket_admin_id);
		$client_link = $class_client->admin_link($ticket_client_id);
		//echo "client: ".$client_link." ID: ".$ticket_user_id;
		$ticket_information_html = '                    
					<div class="row">
                        <div class="col-lg-1">
                        	<h4>Subject</h4>
							<p>'.$ticket_subject.'</p>
                        </div>
                    	<div class="col-lg-1">
                        	<h4>Client</h4>
							<p>'.$client_link.'</p>
						</div>
                        <div class="col-lg-2">
                       		<h4>Current status</h4>
							<p class="opt opt-'.$status_indicator.'">'.$ticket_status.'</p>
                        </div>
                        <div class="col-lg-2">
                       		<h4>Date created</h4>
							<p>'.$zulu->time_fancy($ticket_added).'</p>
                        </div>
                        <div class="col-lg-2">
                       		<h4>Updated</h4>
							<p>'.$zulu->time_fancy($ticket_updated).'</p>
                        </div>
                        <div class="col-lg-4 text-right">
						<form role="form" action="'.$zulu->link_page(PAGE_file,['self'=>true,'query'=>['Action'=>'update_status']]).'" method="post">
                       		<p><button type="submit" name="change_status" value="change_open" class="btn btn-success"><i class="fas fa-check"></i> Open</button>
							<button type="submit" name="change_status" value="change_hold" class="btn btn-warning"><i class="fas fa-pause"></i> On Hold</button> 
							<button type="submit" name="change_status" value="change_closed" class="btn btn-default"><i class="fas fa-times"></i> Closed</button> 
							<button type="submit" name="change_status" value="change_delete" class="btn btn-danger confirm-delete"><i class="fas fa-times"></i> Delete</a></button></p>
							<p class="opt opt-grey"><i class="fas fa-envelope"></i> Notify client? '.$message_form_edit->input_html('checkbox','send_email',1).'</p>
						</form>
                        </div>
                	</div>';
		//print_r($ticket_user_id);
		//load messages for ticket
		$message_sql_config['support_ticket_id'] = $_GET['id'];
		$message_data_rows = $class_support->message_data($message_sql_config);
		//echo "messages: ";
		//print_r ($message_data_rows);
		
		$message_html = '';
		foreach($message_data_rows as $message){
			$message_html .= $class_support->message_create_bubble($message, true);
		}	
	}
	
	//Create new or edit existing ticket
	if(PAGE_action=='edit_ticket') {
		$zulu->template->css_file[] = "https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css";
		$zulu->template->js_file[] = "//code.jquery.com/ui/1.11.4/jquery-ui.js";
		$zulu->template->js_file[] = TPL_rel."assets/smart.find.js";
		if(isset($_GET['object'])){
			$ticket_data['object'] = $_GET['object'];
			$ticket_data['object_id'] = $_GET['object_id'];
			switch ($ticket_data['object']) {
				case 'sale':
					$sale_sql_config['id'] = $_GET['object_id'];
					$sale_data = $class_sale->sale_data($sale_sql_config);
					$zulu->nav->breadcrumb['For Sale #'.$sale_data['reference']] = array();
					break;
			}
		}
		if(isset($_GET['client_id'])){
			$client_id = $_GET['client_id'];
			$client_data = $class_client->client_data(array('id' => $_GET['client_id']));
			$client_name = stripslashes(($client_data['company']!=NULL?$client_data['company']:$client_data['name_first']." ".$client_data['name_last']));
		}
		$ticket_form_edit = new form();
		if($_POST) {
			$notify_client = $_POST['send_email'];
			if(!$ticket_form_edit->validate(['ticket_subject', 'ticket_status', 'client_id', 'ticket_message_text'])){
				$class_support = new support();
				$ticket_data['subject'] = $_POST['ticket_subject'];
				$ticket_data['status'] = $_POST['ticket_status'];
				$ticket_data['client_id'] = $_POST['client_id'];
				$message_data['message_text'] = $_POST['ticket_message_text'];
				//save the ticket details
				$ticket_data_row = $class_support->support_edit(0,$ticket_data,$message_data,false,$notify_client);
				header("Location: ".$zulu->link_page(PAGE_file,array('query'=>array('Sort'=>$_GET['Sort'],'Action'=>''))));
			}else{
				$zulu->notification_set("Please fill out all fields.",2);
			}
		}
		
	}
	
	if(PAGE_action=='delete_ticket') {
		//echo "delete: ".PAGE_id;
		if($class_support->support_delete(PAGE_id)) {
			$zulu->notification_set("Project removed successfully.",1);
			header("Location: ".$zulu->link_page(PAGE_file,array('query'=>array('Sort'=>$_GET['Sort'],'Action'=>''))));
		} else {
			$zulu->notification_set("A database error occurred.",2);
		}
	}
	
	
	if(PAGE_action=='client_messages') {
		//Test action for the client messenger
		$zulu->template->css_file[] = "https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css";
		$zulu->template->js_file[] = "//code.jquery.com/ui/1.11.4/jquery-ui.js";	
		$zulu->template->js_code[] = "$('#message-window').scrollTop($('#message-window').height());";
		
		//Temp hard coded client ID
		$client_id = 1114;
  		$message_form_edit = new form;
		
		if($_POST && isset($_GET['id'])){
			$reply_data = $_POST['reply_message'];
			if(!$message_form_edit->validate(['reply_message'])){
				$class_support->new_ticket_message_client($_GET['id'],$reply_data, $client_id);
			}else{
				$zulu->notification_set("Please enter a message to send.",2);
			}
			  
		 }else if($_POST){
			 $zulu->notification_set("Please select a ticket to reply to.",3);
		 }
		

		$ticket_sql_config['client_id'] = $client_id;
		$ticket_data_row = $class_support->support_data($ticket_sql_config);
		
		$tickets_list_html ='<div class="list-group">';
		foreach($ticket_data_row as $ticket){
			$status_indicator = 'success';
			switch ($ticket['status']) {
    		case 'Open':
				$status_indicator = 'success';
        	break;
    		case 'Hold':
				$status_indicator = 'warning';
        	break;
    		case 'Closed':
				$status_indicator = 'danger';
        	break;
			}
			$has_new = $class_support->support_unread($ticket['id'],false);
			$tickets_list_html .= '<a href="'.$zulu->link_page('support',array('query'=>array('Action'=>'client_messages','id'=>$ticket['id']))).'" class="list-group-item '.($ticket['id'] == $_GET['id'] ? "active": "").'">
								<div class="row">
									<div class="col-lg-12">
										'.$ticket['subject'].'  <i class="far fa-clock"></i> '.$zulu->time_fancy($ticket['stat_add']).'
										<span class="pull-right opt opt-fill opt-'.$status_indicator.'"> '.$ticket['status'].'</span>
										'.($has_new?'<span class=" text-mini" style="padding-top:3px"><span class="opt opt-warning opt-fill"><i class="fas fa-envelope"></i> NEW</span>':NULL).'</span></span>
									</div>
								</div>
								</a>';
		}
		$tickets_list_html .='</div>';
		
		if(isset($_GET['id'])){
		//load messages for ticket
		$message_sql_config['support_ticket_id'] = $_GET['id'];
		$message_data_rows = $class_support->message_data($message_sql_config);
		//echo "messages: ";
		//print_r ($message_data_rows);
		
		$message_html = '';
		foreach($message_data_rows as $message){
			$message_html .= $class_support->message_create_bubble($message);
	  	}
				
	}else{
		$message_html = '<p>Select a ticket to view messages</p>';
	}
}
	
	if(PAGE_action=='update_status'){
		if($_POST){
			$update_sql_config=[];
			$ticket_id = $_GET['id'];
			$notify_client = $_POST['send_email'];
			switch ($_POST['change_status']) {
    		case 'change_open':
				$update_sql_config['status'] = 'Open';
        	break;
    		case 'change_hold':
				$update_sql_config['status'] = 'Hold';
        	break;
    		case 'change_closed':
				$update_sql_config['status'] = 'Closed';
        	break;
			case 'change_delete':
				$class_support->support_delete($ticket_id);
        	break;
			}
			if($_POST['change_status'] != 'change_delete'){
				$class_support->support_status_update($ticket_id, $update_sql_config, $notify_client);
				header("Location: ".$zulu->link_page('support',array('query'=>array('Action'=>'view_messages','id'=>$_GET['id']))));
			}else if($_POST['change_status'] == 'change_delete'){
				header("Location: ".$zulu->link_page('support',array('query'=>array('Action'=>''))));
			}
		}
	}
	
}