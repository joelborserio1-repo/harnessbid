<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- DEFINITIONS
define(PAGE_file,'user');
define(PAGE_name,'Users');
$zulu->nav->breadcrumb['Users'] = array("link"=>$zulu->link_page('user'));

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

	if($class_user->authorised->role=='staff'&&PAGE_action!='edit') {
		header("Location: ".$zulu->link_page('user',['query'=>['Action'=>'edit']]));
		exit;
	}

	if(PAGE_action==NULL) {	//grid page

		function edit_bt($id) {
			global $zulu;
			return "
				<a href=\"".$zulu->link_page('user',array('query'=>array('id'=>$id,'Action'=>'edit')))."\"><button class=\"btn btn-primary btn-xs\" type=\"button\"><i class=\"fas fa-edit\"></i></button></a>
				<a class=\"confirm-delete\" href=\"".$zulu->link_page('user',array('query'=>array('id'=>$id,'Action'=>'delete')))."\"><button class=\"btn btn-danger btn-xs\" type=\"button\"><i class=\"fas fa-times\"></i></button></a>
			";
		}

		if(USER_admin) {
			$table_column[] = array("ID",array('class'=>array('')));
		}
		$table_column[] = array("Username",array('class'=>array('center')));
		$table_column[] = array("Email",array('class'=>array('center')));
		$table_column[] = array("Name",array('class'=>array('')));
		$table_column[] = array("Role",array('class'=>array('')));
		if(USER_admin) {
			$table_column[] = array("API Key",array('class'=>array('')));
		}
		$table_column[] = array("Last Login",array('class'=>array('')));
		$table_column[] = array("Actions",array('class'=>array('right')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);

		if(USER_admin) {
			$user_row = $class_user->user_data();
		} else {
			$user_row = $class_user->user_data(['parent'=>$class_user->authorised->id]);
		}
		foreach($user_row as $row) {
			$user_meta = $class_user->user_meta($row['id']);
			$table_row[] = array("content" => array(
				array((USER_admin?$row['id']:'~')),
				array($row['username']),
				array($row['email']),
				array($row['name_first'].($row['name_last']!=NULL?" ".$row['name_last']:NULL)),
				array($USER_option[$user_meta['role']['value']]),
				array((USER_admin?$user_meta['api_key']['value']:'~')),
				array($zulu->time_history($user_meta['login_last']['value'])),
				array(edit_bt($row['id']),array('class'=>array('right')))
			));
		}

		$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'basket'));
		$zulu->nav->title = "Users";
	} else if(PAGE_action=='delete') { //delete
		if($class_user->delete(PAGE_id)) {
			$zulu->notification_set("User removed successfully.",1);
			header("Location: ".$zulu->link_page('user'));
		} else {
			$zulu->notification_set("A database error occurred.",2);
		}
	} else if(PAGE_action=='edit') { //edit page

		$zulu->template->css_file[] = "//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css";
		$zulu->template->js_file[] = "//code.jquery.com/ui/1.11.4/jquery-ui.js";
		$zulu->template->js_code[] = "
		$(document).ready(function(){
			$(\".date\").datepicker({ dateFormat: \"dd/mm/yy\" });
		});
		";

		$form_edit = new form;
		$zulu->template->js_code[] = "
			$(document).ready(function() {
				$(\"a[rel='toggle-input']\").click(function() {
					$(\"input[type='checkbox']\").each(function() {
						if(!$(this).is(\":disabled\")) {
							$(this).prop(\"checked\", !$(this).prop(\"checked\"));
						}
					});
					return false;
				});
			});
		";

		if($class_user->authorised->role=='staff') {
			$ovr_id = $class_user->authorised->child_id;
			$_GET['id'] = $ovr_id;
		}

		if(PAGE_id<1&&$ovr_id<1) {
			$id = 0;
			$new = true;
			$zulu->nav->breadcrumb['New User'] = array();
			$zulu->nav->title = "New User";
		} else {
			$id = ($_POST['id']>0?$_POST['id']:($_GET['id']>0?$_GET['id']:0));

			$user_data = $class_user->user_data(array('id'=>$id));
			$user_meta = $class_user->user_meta($id);

			if(!$_POST) {
				foreach($user_data as $key=>$val) {
					$_POST[$key] = $val;
				}
				unset($_POST['password']);

				foreach($user_meta as $val) {
					$_POST[$val['field']] = $val['value'];
					$_POST['meta'][$val['field']] = $val['value'];
				}

			}

			//
			if($_POST['plan_id']>0) {
				$plan_data = $class_user->plan_data(['id'=>$_POST['plan_id']]);
			}

			//
			$zulu->nav->breadcrumb['Edit User'] = array();
			$zulu->nav->breadcrumb[$user_data['username']] = array();
			$zulu->nav->title = "Edit User";
		}

		//Group Data
		$group_data = $class_user->group_data();
		$USER_group[0] = "No Group";
		foreach($group_data as $group) {
			$USER_group[$group['id']] = $group['name'];
		}

		//Form Submit
		if($_POST['action']=='edit') {
			$form_edit->valid = true;

			if($form_edit->validate(array('name_first','email','username'))) {
				$zulu->notification_set("Please enter all fields denoted *.",2);
				$form_edit->valid = false;
			}

			if($id<=0&&$form_edit->validate(array('password'))) {
				$zulu->notification_set("Please enter a password.",2);
				$form_edit->valid = false;
			}

			if($_POST['password']!=NULL && $_POST['password']!=$_POST['password_c']) {
				$zulu->notification_set("The passwords you entered do not match, please try again.",2);
				$form_edit->valid = false;
			}

			$check_data = $class_user->user_data(['username'=>$_POST['username']]);
			if($new&&count($check_data)>0) {
				$zulu->notification_set("Please enter another username, '{$_POST['username']}' is unavailable.",2);
				$form_edit->valid = false;
			}

			if($form_edit->valid) {
				$data['name_first'] = $_POST['name_first'];
				$data['name_last'] = $_POST['name_last'];
				$data['email'] = $_POST['email'];
				$data['username'] = $_POST['username'];
				if($_POST['password']!=NULL) {
					$data['password'] = $class_user->password_hash($_POST['password']);
				}
				if($id>0) {
					$data = $class_user->user_edit($id,$data);
				} else {
					$data['status'] = 1;
					$data['password_hashed'] = true;
					$data = $class_user->user_new($data);
					$id = $data['id'];
					$new = true;
				}

				if($data['success']) {

					foreach($_POST['meta'] as $mkey=>$mval) {
						$zulu->meta_update("user",$id,$mkey,$mval);
					}

					//-- Set Group
					$zulu->meta_update("user",$id,"group",$_POST['group']);

					//-- Set Custom Admin Related Meta
					if(USER_admin) {
						$zulu->meta_update("user",$id,"role",$_POST['role']);
					} else {
						if($class_user->authorised->role=='client'&&$class_user->authorised->id!=$id) {
							$zulu->meta_update("user",$id,"role",$class_user->USER_staff_role); //default role
							$class_user->user_edit($id,['parent'=>$class_user->authorised->id]);
						}
					}

					if($class_user->authorised->role=='admin') {
						$zulu->meta_update("user",$id,"position",$_POST['position']);

						foreach($class_subscribe->OPTION as $opt) {
							$zulu->meta_update("user",$id,$opt,$_POST[$opt]);
						}

						$zulu->meta_update("user",$id,"plan_id",$_POST['plan_id']);
						$zulu->meta_update("user",$id,"plan_expiry",zulu::dateEncode($_POST['plan_expiry'])+86399);
						$zulu->meta_update("user",$id,"plan_free",$_POST['plan_free']);
						$zulu->meta_update("user",$id,"plan_cancelled",$_POST['plan_cancelled']);

						$zulu->meta_update("user",$id,"subscribe_trial",$_POST['subscribe_trial']);
						$zulu->meta_update("user",$id,"subscribe_braintree_id",$_POST['subscribe_braintree_id']);
						$zulu->meta_update("user",$id,"subscribe_gst_exempt",$_POST['subscribe_gst_exempt']);
					}

					if($new&&$_POST['role']=='client') {
						$class_setting->setting_edit("name","Untitled CRM",NULL,array('user_id'=>$id));
						$class_setting->setting_edit("company","My Company",NULL,array('user_id'=>$id));
						$class_setting->setting_edit("contact_name",$_POST['name_first']." ".$_POST['name_last'],NULL,array('user_id'=>$id));
						$class_setting->setting_edit("contact_email",$_POST['email'],NULL,array('user_id'=>$id));
						$class_setting->setting_edit("theme","razor",NULL,array('user_id'=>$id));
						$class_setting->setting_edit("tax_label","GST",NULL,array('user_id'=>$id));
						$class_setting->setting_edit("tax_method",1,NULL,array('user_id'=>$id));
						$class_setting->setting_edit("tax_rate",15,NULL,array('user_id'=>$id));
					}

					$class_cache->dump('user_meta');

					$zulu->notification_set("User ".($id>0?"updated":"created")." successfully.",1);
					header("Location: ".$zulu->link_page('user'));
					exit;
				} else {
					$zulu->notification_set("A database error occurred.",2);
				}
			}
		}
	} elseif(PAGE_action=='user_role' && $class_user->authorised->role == 'admin') {	//user role grid page

		function edit_role_bt($id) {
			global $zulu;
			return "
				<a href=\"".$zulu->link_page('user',array('query'=>array('id'=>$id,'Action'=>'user_role_edit')))."\" class=\"btn btn-primary btn-xs\"><i class=\"fas fa-edit\"></i> Edit</a>
				<a href=\"".$zulu->link_page('user',array('query'=>array('id'=>$id,'Action'=>'user_role_delete')))."\" class=\"btn btn-danger btn-xs confirm-delete\"><i class=\"fas fa-times\"></i></a>
			";
		}

		$table_column[] = array("Role Name");
		$table_column[] = array("Users");
		$table_column[] = array("Actions",array('class'=>array('right')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);

		$user_row = $class_user->role_data();
		foreach($user_row as $row) {
			$table_row[] = array("content" => array(
				array($row['name']),
				//array($row['tag']),
				array($class_user->user_count(['type'=>'role','value'=>$row['tag']])),
				array(edit_role_bt($row['id']),array('class'=>array('right')))
			));
		}

		$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'roles'));
		$zulu->nav->title = "User Roles";
		$zulu->nav->breadcrumb['User Roles'] = array();
	} elseif(PAGE_action=='user_group' && $class_user->authorised->role != 'admin') {	//user role grid page

		function edit_bt($id) {
			global $zulu;
			return "
				<a href=\"".$zulu->link_page('user',array('query'=>array('id'=>$id,'Action'=>'user_group_edit')))."\" class=\"btn btn-primary btn-xs\"><i class=\"fas fa-edit\"></i> Edit</a>
				<a href=\"".$zulu->link_page('user',array('query'=>array('id'=>$id,'Action'=>'user_group_delete')))."\" class=\"btn btn-danger btn-xs confirm-delete\"><i class=\"fas fa-times\"></i></a>
			";
		}

		$table_column[] = array("Group Name");
		$table_column[] = array("Users");
		$table_column[] = array("Actions",array('class'=>array('right')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);

		$user_row = $class_user->group_data();
		foreach($user_row as $row) {
			$table_row[] = array("content" => array(
				array($row['name']),
				//array($row['tag']),
				array($class_user->user_count(['type'=>'group','value'=>$row['id']])),
				array(edit_bt($row['id']),array('class'=>array('right')))
			));
		}

		$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'roles'));
		$zulu->nav->title = "User Groups";
		$zulu->nav->breadcrumb['User Groups'] = array();
	} elseif(PAGE_action=='user_group_delete' && $class_user->authorised->role == 'admin') { //delete
		if($class_user->role_delete(PAGE_id)) {
			$zulu->notification_set("User group removed successfully.",1);
			header("Location: ".$zulu->link_page(PAGE_file,['query'=>['Action'=>'user_group']]));
			exit;
		} else {
			$zulu->notification_set("A database error occurred.",2);
		}
	} elseif(PAGE_action=='user_role_delete' && $class_user->authorised->role == 'admin') { //delete
		if($class_user->role_delete(PAGE_id)) {
			$zulu->notification_set("User role removed successfully.",1);
			header("Location: ".$zulu->link_page('user',array('query'=>array('Action'=>'user_role'))));
		} else {
			$zulu->notification_set("A database error occurred.",2);
		}
	} elseif(PAGE_action=='user_role_edit' && $class_user->authorised->role == 'admin') { //user role edit page
		$form_edit = new form;

		$type = 'Role';
		$zulu->nav->breadcrumb['User '.$type.'s'] = array("link"=>$zulu->link_page('user',array('query'=>array('Action'=>'user_role'))));
		if(PAGE_id<1) {
			$id = 0;
			$new = true;

			//Check type

			$zulu->nav->breadcrumb['New '.$type] = array();
			$zulu->nav->title = "New ".$type;
		} else {
			$id = ($_POST['id']>0?$_POST['id']:($_GET['id']>0?$_GET['id']:0));

			$group_data = $class_user->role_data(array('id'=>$id));

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
				$zulu->notification_set("Please enter a name for the role.",2);
				$form_edit->valid = false;
			}

			if($id>0) {
				$_POST['stat_update'] = time();
				$query = "UPDATE user_role SET ".$db->build(1,array('name','tag'))." WHERE id = '".$id."'";
			} else {
				$query = "INSERT INTO user_role ".$db->build(2,array('name','tag'));
			}

			if($form_edit->valid) {
				if($db->query($query)) {
					$id = ($id>0?$id:$db->insert_id);
					$zulu->notification_set($type." ".($id>0?"updated":"created")." successfully.",1);
					header("Location: ".$zulu->link_page('user',array('query'=>array('Action'=>'user_role'))));
					exit;
				} else {
					$zulu->notification_set("A database error occurred.",2);
				}
			}
		}
	} elseif(PAGE_action=='user_group_edit' && $class_user->authorised->role != 'admin') { //user role edit page
		$form_edit = new form;

		$type = 'Group';
		$zulu->nav->breadcrumb['User '.$type.'s'] = array("link"=>$zulu->link_page('user',array('query'=>array('Action'=>'user_role'))));
		if(PAGE_id<1) {
			$id = 0;
			$new = true;

			//Check type

			$zulu->nav->breadcrumb['New '.$type] = array();
			$zulu->nav->title = "New ".$type;
		} else {
			$id = ($_POST['id']>0?$_POST['id']:($_GET['id']>0?$_GET['id']:0));

			$group_data = $class_user->group_data(array('id'=>$id));
			$group_data_info = unserialize($group_data['data']);

			if(!$_POST) {
				foreach($group_data as $key=>$val) {
					$_POST[$key] = $val;
				}
				$_POST['data'] = $group_data_info['perm'];
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
			$_POST['user_id'] = $class_user->authorised->id;
			foreach($class_user->config->permission_rules as $pkey=>$pval) {
				$perm_data[$pkey] = ($_POST['data'][$pkey]>0?1:0);
			}
			$_POST['data'] = serialize($class_user->group_data_append(['base'=>$group_data_info,'node'=>'perm','data'=>$perm_data]));

			if($id>0) {
				$_POST['stat_update'] = time();
				$query = "UPDATE user_role SET ".$db->build(1,array('name','data'))." WHERE id = '".$id."'";
			} else {
				$query = "INSERT INTO user_role ".$db->build(2,array('user_id','name','data'));
			}

			if($form_edit->valid) {
				if($db->query($query)) {
					$id = ($id>0?$id:$db->insert_id);
					$zulu->notification_set($type." ".($id>0?"updated":"created")." successfully.",1);
					header("Location: ".$zulu->link_page('user',array('query'=>array('Action'=>'user_group'))));
					exit;
				} else {
					$zulu->notification_set("A database error occurred.",2);
				}
			}
		}
	} else {		//	404

		$zulu->nav->breadcrumb['Page Missing'] = array();
		$zulu->nav->title = "Page Missing";
	}
}
