<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- DEFINITIONS
define(PAGE_file,'email');
define(PAGE_name,'Send Email');
$zulu->nav->breadcrumb['Send Email'] = array();
$zulu->nav->title = "Send Email";

//-- AUTHORISED?
$class_user->user_authorised_check();

//-- ADMIN MASTER SECTION
if(MASTER_section=='admin') { //admin section

	//Roles
	foreach(user::role_data() as $role_row) {
		$USER_option[$role_row['tag']] = $role_row['name'];
	}
		
	$zulu->template->head = "";
	$zulu->template->body = "";
	
	//Lists
	$user_list = $zulu->table_data("client",0,array("sort"=>"meta.value ASC, name_first ASC, name_last ASC"));
	foreach($user_list as $row) {
		switch($row['type']) {
			case 0:
			$list_member .= (trim($row['email'])!=NULL?$row['email']."<br>":NULL);
			break;	
			case 1:
			$list_visitor .= (trim($row['email'])!=NULL?$row['email']."<br>":NULL);
			break;	
			case 2:
			$list_resign .= (trim($row['email'])!=NULL?$row['email']."<br>":NULL);
			break;	
		}
	}
	
	if(PAGE_action==NULL) {	//grid page
		$form_edit = new form;
		
		$id = 0;
		$new = true;
				echo $to;exit;
		
		//Form Submit
		if($_POST['action']=='edit') {
			//print_r($_POST);exit;
			$form_edit->valid = true;
			if($form_edit->validate(array('subject','message'))) {
				$zulu->notification_set("Please enter all fields denoted <em>*</em>.",2);
				$form_edit->valid = false;
			}
			if(count($_POST['bulk_recipient'])==0 && 
			(count($_POST['select_recipient']['visitor'])==1 && $_POST['select_recipient']['visitor'][0]==0) && 
			(count($_POST['select_recipient']['member'])==1 && $_POST['select_recipient']['member'][0]==0) &&
			(count($_POST['select_recipient']['resigned'])==1 && $_POST['select_recipient']['resigned'][0]==0)) {
				$zulu->notification_set("Please select a recipient.",2);
				$form_edit->valid = false;	
			}
			
			if($form_edit->valid) {
				$recipients = array();
				foreach($_POST['bulk_recipient'] as $key=>$val) {
					$recipients[$key] = array();
					$users = $class_user->user_with_role($key);
					foreach($users as $u) {
						if(trim($u['email'])!=NULL) {
							$recipients[$key][] = $u['email'];
						}
					}
				}
				foreach($_POST['select_recipient'] as $key=>$array) {
					foreach($array as $val) {
						if($val > 0) {
							$user_data = $class_user->user_data(array('id'=>$val));
							$recipients['select'][] = $user_data['email'];
						}
					}
				}
				$to_array = array();
				foreach($recipients as $rec) {
					$to_array[] = implode(', ',$rec);
				}
				$to = implode(', ',$to_array);
				$config['bcc'] = $to;
				$to = NULL;
				//$to = 'pazams@yahoo.co.nz';
				$zulu->mail_send($to,$_POST['subject'],$_POST['message'],'',$config);
				$zulu->notification_set("Email successfully sent.",1);
				header("Location: ".$zulu->link_page('email'));
				exit;
			}
		}
	}
}
