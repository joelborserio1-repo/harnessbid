<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0


//-- DEFINITIONS
define(PAGE_file,'group');
define(PAGE_name,'User Groups');

$zulu->nav->breadcrumb['User Groups'] = array("link"=>$zulu->link_page('group'));

//-- AUTHORISED?
$class_user->user_authorised_check();

//-- GET ACTIONS
if(PAGE_action=='users_delete') {
	
	$relation_data = $class_file->group_relation_data(0,0,PAGE_id);
	
	if($class_file->group_relation_delete($relation_data['id'])) {
		$zulu->notification_set("User removed from group successfully.",1);
	} else {
		$zulu->notification_set("User failed to remove from group.",2);
	}
	header("Location: ".$zulu->link_page('group',array('query'=>array('Action'=>'users','id'=>$relation_data['group_id']))));
	exit;	
}

//-- PAGE LOAD ACTIONS
if(PAGE_action==NULL) {	//grid page
	
	function group_edit_bt($id) {
		global $zulu;
		return "
			<a href=\"".$zulu->link_page('group',array('query'=>array('id'=>$id,'Action'=>'users')))."\" title=\"View users in this group.\"><button class=\"btn btn-info btn-circle\" type=\"button\"><i class=\"fas fa-users\"></i></button></a> 
			<a href=\"".$zulu->link_page('group',array('query'=>array('id'=>$id,'Action'=>'edit')))."\"><button class=\"btn btn-primary btn-circle\" type=\"button\"><i class=\"fas fa-edit\"></i></button></a> 
			<a class=\"confirm-delete\" href=\"".$zulu->link_page('group',array('query'=>array('id'=>$id,'Action'=>'delete')))."\"><button class=\"btn btn-danger btn-circle\" type=\"button\"><i class=\"fas fa-times\"></i></button></a>
		";	
	}
	
	$table_column[] = array("Type",array('class'=>array('')));
	$table_column[] = array("Name",array('class'=>array('center')));
	$table_column[] = array("Users",array('class'=>array('')));
	$table_column[] = array("Actions",array('class'=>array('right')));
	$table_row[] = array(
			"header"	=>	 true,
			"class"		=>	"",
			"content"	=>	$table_column);
			
	$group_row = $class_file->group_data();
	foreach($group_row as $row) {
		$table_row[] = array("content" => array(
			array($row['id']),
			array($row['name']),
			array($class_file->group_user_count($row['id'])),
			array(group_edit_bt($row['id']),array('class'=>array('right')))
		));
	}
	
	$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'group'));
	$zulu->nav->title = "Groups";
}
if(PAGE_action=='MoveGroupUser') { //user move group
	$user_group_data = $class_file->group_relation_data($_POST['user']);
	$user_group_data = $class_file->group_data($user_group_data[0]['group_id']);
	
	if($class_file->group_relation_move($_GET['User'],PAGE_id)) {
		$zulu->notification_set("User was added to moved successfully.",1);
	} else {
		$zulu->notification_set("User failed to move groups.",2);
	}
	header("Location: ".$zulu->link_page('group',array('query'=>array('id'=>PAGE_id,'Action'=>'users'))));
	exit;
}
if(PAGE_action=='users') { //users in a group
	$group_data = $class_file->group_data(PAGE_id);
	
	//-- Add User
	if($_POST['user']>0) {
		$user_group_data = $class_file->group_relation_data($_POST['user']);
		$cur_group = $user_group_data[0]['group_id'];
		$user_group_data = $class_file->group_data($user_group_data[0]['group_id']);
		
		if($class_file->group_relation_add($_POST['user'],PAGE_id)) {
			$zulu->notification_set("User was added to group successfully.",1);
		} else {
			if($cur_group==PAGE_id) {
				$zulu->notification_set("User is already in this group.",2);
			} else {
				$zulu->notification_set("User was not added, user is already in the group '".$user_group_data['name']."'. Move them into group '".$group_data['name']."'? <a href=\"".$zulu->link_page('group',array('query'=>array('id'=>PAGE_id,'Action'=>'MoveGroupUser','User'=>$_POST['user'])))."\">Move</a>",2);
			}
		}
		header("Location: ".$zulu->link_page('group',array('query'=>array('id'=>PAGE_id,'Action'=>'users'))));
		exit;
	}
	
	$zulu->nav->breadcrumb['Users in \''.$group_data['name'].'\' Group'] = array();
	$zulu->nav->title = "Group Users: ".$group_data['name'];
	
	$form_edit = new form;
	
	function group_user_edit_bt($id) {
		global $zulu;
		return "
			<a class=\"confirm-delete\" href=\"".$zulu->link_page('group',array('query'=>array('id'=>$id,'Action'=>'users_delete')))."\"><button class=\"btn btn-danger btn-circle\" type=\"button\"><i class=\"fas fa-times\"></i></button></a>
		";	
	}
	
	$table_column[] = array("Username",array('class'=>array('')));
	$table_column[] = array("Name",array('class'=>array('')));
	$table_column[] = array("Actions",array('class'=>array('right')));
	$table_row[] = array(
			"header"	=>	 true,
			"class"		=>	"",
			"content"	=>	$table_column);
			
	$group_row = $class_file->group_relation_data(0,PAGE_id);
	foreach($group_row as $row) {
		$user_data = $class_user->user_data(array("id"=>$row['user_id']));
		$table_row[] = array("content" => array(
			array($user_data['username']),
			array($user_data['name_first'].($user_data['name_last']!=NULL?" ".$user_data['name_last']:NULL)),
			array(group_user_edit_bt($row['id']),array('class'=>array('right')))
		));
	}
	
	$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'group'));
}
if(PAGE_action=='delete') { //delete
	if($class_file->delete(PAGE_id)) {
		$zulu->notification_set("File removed successfully.",1);
		header("Location: ".$zulu->link_page('file'));
	} else {
		$zulu->notification_set("A database error occurred.",2);
	}
}
if(PAGE_action=='edit') { //edit page
	$form_edit = new form;
	
	$type = 'Group';
	if(PAGE_id<1) {
		$id = 0;
		$new = true;	
		
		//Check type
		
		$zulu->nav->breadcrumb['New '.$type] = array();
		$zulu->nav->title = "New ".$type;
	} else {
		$id = ($_POST['id']>0?$_POST['id']:($_GET['id']>0?$_GET['id']:0));
		
		$group_data = $class_file->group_data(array('id'=>$id));
		
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
			$query = "UPDATE file_user_group SET ".$db->build(1,array('name','stat_update'))." WHERE id = '".$id."'";
		} else {
			$_POST['token'] = zulu::serial();
			$_POST['stat_add'] = time();
			$_POST['stat_update'] = time();
			$query = "INSERT INTO file_user_group ".$db->build(2,array('token','name','stat_add','stat_update'));
		}
			
		if($form_edit->valid) {
			if($db->query($query)) {
				$id = ($id>0?$id:$db->insert_id);
				$zulu->notification_set("Group ".($id>0?"updated":"created")." successfully.",1);
				header("Location: ".$zulu->link_page('group'));
				exit;
			} else {
				$zulu->notification_set("A database error occurred.",2);
			}
		}
	}
}