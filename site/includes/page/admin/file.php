<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- DEFINITIONS
define(PAGE_file,'file');
define(PAGE_name,'Files');

define(FILE_root,$class_file->file_root);
define(UPLOADER_root,TPL_rel."assets/dropzone/");

$zulu->nav->breadcrumb['Files'] = array("link"=>$zulu->link_page('file'));

$zulu->template->css_file[] = "//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css";
$zulu->template->js_file[] = "//code.jquery.com/ui/1.11.4/jquery-ui.js";
$zulu->template->js_code[] = "
$(document).ready(function(){
	$(\".date\").datepicker({ dateFormat: \"dd/mm/yy\" });
});
";

//-- AUTHORISED?
if(!in_array(PAGE_action,array('download'))) {
	$class_user->user_authorised_check();
	$class_user->authorised->opt_file = true;
}
if($class_user->authorised->id<=0) {
	$class_user->user_public();
	$class_user->authorised->opt_file = true;
}

$zulu->template->head = "";
$zulu->template->body = "";

if(PAGE_action=='download') { //file
	$form_edit = new form;
	$config = ['ovr_parent'=>true];
	if($_GET['token']!=NULL) {
		$_GET['Token'] = $_GET['token'];
	}
	if($_GET['type']=='quick') {
		$config['quick'] = true;
	}
	if($_GET['public']>0) {
		$config['public'] = true;
	}

	//-- Load Default data
	$stop = false;
	if(isset($config['quick'])&&$config['quick']) {
		$file_base_data = $class_file->file_quick_data(['token'=>$db->escape_string($_GET['Token'])]+$config);
	} else {
		$file_base_data = $class_file->file_data(['token'=>$db->escape_string($_GET['Token'])]+$config);
	}
	
	if($file_base_data['id']<=0) {
		$zulu->notification_set("File could not be found.",2);
		$stop = true;
	}
	if($class_user->authorised->public&&!$stop) {
		$setting_data = $class_setting->setting_data(array('user_id'=>$file_base_data['user_id']));
		$class_setting->data = $setting_data;
	}
	$logo = ($class_setting->data['quote_logo_path']!=NULL?$class_setting->data['quote_logo_path']:NULL);
	$company_out = $class_client->data_format($class_setting->data,'company');
	if($config['quick']) {
		$file_base_data = $class_file->file_data(['token'=>$file_base_data['file_token']]+$config);
	}
	$zulu->template->page_def['popup_title'] = stripslashes($file_base_data['name']);
	
	//-- Do Request
	if(!$stop) {
		
		//--Post
		if($_POST['action']=='download') {
			if(isset($_POST['password'])) {
				$config['password'] = $class_user->password_hash($_POST['password']);
			}
		}
		
		$result = $class_file->file_download($db->escape_string($_GET['Token']),$config);
		if($result['success']) {
			exit;
		} else {
			if(in_array('password',$result['vars_required'])) {
				$require_password = true;
			}
			$zulu->notification_set($result['reason'],2);
		}
	}

	//-- Template settings
	$zulu->nav->title = "Download File";
	$zulu->template->css_file[] = TPL_rel."css/document.css";
	$zulu->template->body_file = 'body-popup.php';	
	$zulu->template->body_class = ['frame-width-800'];
}

if(PAGE_action==NULL) {	//grid page
	
	if(PAGE_id<=0) {
		$zulu->nav->breadcrumb['Home Directory'] = [];
	}
	/*function edit_bt($id,$data) {
		global $zulu;
		global $class_file;
		$file_data = $class_file->file_data(array("id"=>$id));
		$file_quick_data = $class_file->file_quick_data(array("file_token"=>$file_data['token']));
		return ($data['type']=='folder'?NULL:"
			<a href=\"".$class_file->file_button($id)."\" title=\"Download\"><button class=\"btn btn-info btn-circle\" type=\"button\"><i class=\"fas fa-download\"></i></button></a> ")."
			<a href=\"".$zulu->link_page('file',array('query'=>array('id'=>$id,'Action'=>'edit')))."\" title=\"Edit\"><button class=\"btn btn-primary btn-circle\" type=\"button\"><i class=\"fas fa-edit\"></i></button></a> 
			<a href=\"".$zulu->link_page('file',array('query'=>array('Action'=>'quick','Token'=>$file_data['token'])))."\" title=\"New Quick Access\"><button class=\"btn btn-success btn-circle\" type=\"button\"><i class=\"fas fa-bolt\"></i></button></a>
			<a class=\"confirm-delete\" href=\"".$zulu->link_page('file',array('query'=>array('id'=>$id,'Action'=>'delete')))."\" title=\"Delete\"><button class=\"btn btn-danger btn-circle\" type=\"button\"><i class=\"fas fa-times\"></i></button></a>
		";	
	}
	
	//Load Current Folder Data
	$folder_data = $class_file->file_data(array('id'=>$class_file->root_id));
	$class_file->folder->name = $folder_data['name'];
	
	//Load Files / Folders
	$table_column[] = array("Type",array('class'=>array('')));
	$table_column[] = array("Name",array('class'=>array('center')));
	$table_column[] = array("Kind",array('class'=>array('center')));
	$table_column[] = array("Size",array('class'=>array('')));
	$table_column[] = array("Actions",array('class'=>array('right')));
	$table_row[] = array(
			"header"	=>	 true,
			"class"		=>	"",
			"content"	=>	$table_column);
			
	$file_row = $class_file->file_data(array('root_id'=>$class_file->root_id,'object'=>'','object_id'=>'0'));
	foreach($file_row as $row) {
		$file_meta = $class_file->file_meta($row['id']);
		$link = ($row['type']=='folder'?$class_file->folder_url(array('root_id'=>$row['id'])):$class_file->file_button($row['id']));
		$table_row[] = array("content" => array(
			array("<span class=\"far ".$class_file->file_icon($row['type'])."\"></span>"),
			array("<a href=\"".$link."\">{$row['name']}</a>"),
			array(strtoupper($row['path_ext'])),
			array($class_file->file_size($row['path'])),
			array(edit_bt($row['id'],$row),array('class'=>array('right')))
		));
	}
	$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'file'));*/ 
	$zulu->template->body = $class_file->embed_table(array('root_id'=>$class_file->root_id,'object'=>'','object_id'=>'0')); 
	$zulu->nav->title = "Files";
	
	//Build Tree of Links
	$class_file->folder_breadcrumb($class_file->root_id);
	
	//Include
	$zulu->template->css_file[] = UPLOADER_root."dist/dropzone.css";
	$zulu->template->js_file[] = UPLOADER_root."dist/dropzone.js";
	$zulu->template->js_code[] = "
	
		$(function(){
			
	  Dropzone.options.myDropzone = {
		maxFilesize: 5,
		addRemoveLinks: true,
		dictResponseError: 'Server not Configured',
		init:function(){
		  var self = this;
		  // config
		  self.options.addRemoveLinks = true;
		  self.options.dictRemoveFile = \"Delete\";
		  self.on(\"queuecomplete\", function (progress) {
			location.reload();
		  });
		}
	  };
	});
	";
}
if(PAGE_action=='delete') { //delete
	$file_data = $class_file->file_data(array('id'=>PAGE_id));
	
	if($class_file->delete(PAGE_id)) {
		$zulu->notification_set("File '".$file_data['name']."' removed successfully.",1);
		$client_id = $db->escape_string($_GET['Client']);
		if($client_id > 0) {
			$from = $db->escape_string($_GET['From']);
			header("Location: ".$zulu->link_page('client',array('query'=>array('id'=>$client_id,'Action'=>$from))));exit;
		}
		header("Location: ".$zulu->link_page('file',array('query'=>array('FileRoot'=>$file_data['parent_id']))));
		exit;
	} else {
		$zulu->notification_set("A database error occurred.",2);
	}
}
if(PAGE_action=='quick') { //quick
	if($_GET['Token']!=NULL) {
		$token = $db->escape_string($_GET['Token']);
		$result = $class_file->file_quick_new(array("file_token"=>$token));
		if($result['success']) {
			$quick_link = ['Action'=>'quick_edit','id'=>$result['id']];
			if($_GET['Client'] != NULL) {
				$quick_link['Client'] = $_GET['Client'];
				$quick_link['From'] = $_GET['From'];
				
			}
			header("Location: ".$zulu->link_page('file',array('query'=>$quick_link)));
		} else {
			$zulu->notification_set("A database error occurred.",2);
		}
	}
	function edit_bt($id,$data) {
		global $zulu;
		global $class_file;
		
		return "
			<a href=\"".$zulu->link_page('file',array('query'=>array('id'=>$data['id'],'Action'=>'edit')))."\" title=\"Link\"><button class=\"btn btn-warning btn-xs\" type=\"button\"><i class=\"far ".$class_file->file_icon($data['type'])."\"></i> Edit ".$class_file->file_label($data['type'])."</button></a> 
			<a href=\"#\" title=\"Link\"><button class=\"btn btn-info btn-xs\" type=\"button\"><i class=\"far fa-file\"></i> Copy Link</button></a> 
			<a href=\"".$zulu->link_page('file',array('query'=>array('id'=>$id,'Action'=>'quick_edit')))."\" title=\"Edit\"><button class=\"btn btn-primary btn-circle\" type=\"button\"><i class=\"fas fa-edit\"></i></button></a>
			<a class=\"confirm-delete\" href=\"".$zulu->link_page('file',array('query'=>array('id'=>$id,'Action'=>'quick_delete')))."\" title=\"Delete\"><button class=\"btn btn-danger btn-circle\" type=\"button\"><i class=\"fas fa-times\"></i></button></a>
		";	
	}
	
	//Load Current Folder Data
	$folder_data = $class_file->file_data(array('id'=>$class_file->root_id));
	//print_r($folder_data);exit;
	$class_file->folder->name = $folder_data['name'];
	
	//Load Files / Folders
	$table_column[] = array("Type",array('class'=>array('')));
	$table_column[] = array("Name",array('class'=>array('center')));
	$table_column[] = array("Short Description",array('class'=>array('center')));
	$table_column[] = array("Password",array('class'=>array('')));
	$table_column[] = array("Expiry",array('class'=>array('')));
	$table_column[] = array("Max Downloads",array('class'=>array('')));
	$table_column[] = array("Allowed IPs",array('class'=>array('')));
	$table_column[] = array("Public Share Link <i class=\"fas fa-link color-grey\"></i>",array('class'=>array('')));
	$table_column[] = array("Actions",array('class'=>array('right')));
	$table_row[] = array(
			"header"	=>	 true,
			"class"		=>	"",
			"content"	=>	$table_column);
			
	//$file_row = $class_file->file_quick_data(array('root_id'=>$class_file->root_id));
	$query = "SELECT *,quick.id AS quick_id FROM file_quick AS quick INNER JOIN file AS file ON quick.file_token=file.token WHERE file.parent_id='".$class_file->root_id."' ORDER BY file.type DESC, file.name ASC, quick.tag ASC, quick.stat_add ASC";
	$exe = $db->mysqli->query($query);
	while($row = $exe->fetch_assoc()) {
		$file_meta = $class_file->file_meta($row['id']);
		//$link = ($row['type']=='folder'?$class_file->folder_url(array('root_id'=>$row['id'],'Action'=>'quick')):$class_file->file_button($row['id']));
		$link = $class_file->file_button($row['id']);
		$ips = unserialize(trim($row['whitelist_ips']));
		$ips_count = 0;
		$ips_li = '';
		foreach($ips as $ip){
			if(trim($ip)!=NULL){
				$ips_count++;
				$ips_li .= '<li class="dropdown-header">'.$ip.'</li>';
			}
		}
		$ips_drop = '<div class="dropdown">
						<button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">'.$ips_count.'
						<span class="caret"></span></button>
						<ul class="dropdown-menu">
						  '.$ips_li.'
						</ul>
					  </div>';
		$table_row[] = array("content" => array(
			array("<span class=\"far ".$class_file->file_icon($row['type'])."\"></span>"),
			array(($row['type']=='file'?"<a href=\"".$link."\">{$row['name']}</a>":$row['name'])),
			array(stripslashes($row['tag'])),
			array(($row['password']!=NULL?'Yes':'-')),
			array(($row['expire']!=NULL?$zulu->dateDecode($row['expire']):'-')),
			array(($row['max_count']!=NULL?$row['max_count']:'-')),
			array(($ips_count>0?$ips_drop:'All')),
			array($zulu->js_prompt_copy($class_file->file_quick_url($row['quick_id'],['public'=>true]),['shorten'=>30])),
			array(
			$zulu->button_render([
				['label'=>"Edit ".$class_file->file_label($row['type']),"class"=>'primary','icon'=>str_replace('fa-','',$class_file->file_icon($row['type'])),'link'=>$zulu->link_page(PAGE_file,['query'=>['Action'=>'quick_edit','id'=>$row['quick_id']]])],
				["class"=>'danger','class_append'=>['confirm-delete'],'icon'=>'remove','link'=>$zulu->link_page(PAGE_file,['query'=>['Action'=>'quick_delete','id'=>$row['quick_id']]])],
			])
			,array('class'=>array('right')))
		));
	}
	
	$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'file'));
	$zulu->nav->title = "Quick Access Files";
	
	//Build Tree of Links
	//$class_file->folder_breadcrumb($class_file->root_id,'quick');
	$zulu->nav->breadcrumb['Quick Access'] = array();
}
if(PAGE_action=='quick_delete') { //delete
	$file_quick_data = $class_file->file_quick_data(['id'=>PAGE_id]);
	if($class_file->file_quick_delete(PAGE_id)) {
		$zulu->notification_set("Quick Access '".$file_data['name']."' removed successfully.",1);
		header("Location: ".$zulu->link_page('file',array('query'=>array('Action'=>'quick','FileRoot'=>$file_data['parent_id']))));
	} else {
		$zulu->notification_set("A database error occurred.",2);
	}
}
if(PAGE_action=='quick_edit') { //quick access edit page
	$form_edit = new form;
	global $db;
	
	$zulu->template->js_code[] = "
			$( function() {
				refreshContent();
			});
			var max_fields      = 10; //maximum input boxes allowed
			var wrapper         = $('#input_fields_wrap'); //Fields wrapper
			var add_button      = $('.add_field_button'); //Add button ID

			var x = 1; //initlal text box count
			$(add_button).click(function(e){ //on add input button click
				e.preventDefault();
				if(x < max_fields){ //max input box allowed
					x++; //text box increment
					$(wrapper).append('<div class=\"row\"><div class=\"col-lg-8\"><div class=\"form-group\"><input class=\"form-control\" type=\"text\" name=\"whitelist_ips[]\"/></div></div><div class=\"col-lg-4\"><a href=\"#\" class=\"remove_field btn btn-danger btn-block\"><i class=\"fas fa-times\" aria-hidden=\"true\"></i> Remove</a></div><br><div>'); //add input box
				}
			});

			$(wrapper).on('click','.remove_field', function(e){ //user click on remove text
				e.preventDefault(); $(this).parent().parent('div').remove(); x--;
			});
			
			
			function refreshContent(){
				// do whatever you like here
				var quick_file_id = $('#quick_access_file_id').val();
				if(quick_file_id>0){
					$.post('/admin/index.php?Page=file&Action=quick_edit',
					{
						quick_file_id: quick_file_id,
						action: 'get_ips'
					},
					function(data, status){
						data = JSON.parse(data);
						if(data.success){
							var count = 0;
							for(var k in data.ips) {
							   if(count == 0){
							   		$('#first_ip').val(data.ips[k]);
							   }else{
							   		$(wrapper).append('<div class=\"row\"><div class=\"col-lg-8\"><div class=\"form-group\"><input class=\"form-control\" type=\"text\" name=\"whitelist_ips[]\"/ value=\"'+data.ips[k]+'\"></div></div><div class=\"col-lg-4\"><a href=\"#\" class=\"remove_field btn btn-danger btn-block\"><i class=\"fas fa-times\" aria-hidden=\"true\"></i> Remove</a></div><br><div>'); //add input box
							   }
							   count++;
							}
						}else{
							console.log('Error getting ips.');
						}
					});
				}
			}
	";
	//Ajax get ips
	if($_POST['action'] == 'get_ips' && $_SESSION['zl_user']['id']>0 && $_POST['quick_file_id']){
		$qa_data = $class_file->file_quick_data(array('id'=>$db->escape_string($_POST['quick_file_id'])));
		$ips = unserialize($qa_data['whitelist_ips']);
		echo json_encode(['success'=>true, 'ips'=>$ips]);
		exit;
	}else if($_POST['action'] == 'get_ips'){
		echo json_encode(['success'=>false]);
		exit;
	}
	
	$id = ($_POST['id']>0?$_POST['id']:($_GET['id']>0?$_GET['id']:0));
	
	$qa_data = $class_file->file_quick_data(array('id'=>$id));
	$file_token = $qa_data['file_token'];
	$file_data = $class_file->file_data(array('token'=>$file_token));
	$file_meta = $class_file->file_meta($file_data['id']);
	$type = "Quick Access";
	
	if(!$_POST) {
		foreach($qa_data as $key=>$val) {
			if($key != 'password'){
				$_POST[$key] = $val;	
			}
		}
		
		foreach($file_meta as $val) {
			$_POST[$val['field']] = $val['value'];
		}
	}
	
	$zulu->nav->breadcrumb['Quick Access'] = array("link"=>$zulu->link_page('file',array('query'=>array('Action'=>'quick','FileRoot'=>$file_data['parent_id']))));
	$zulu->nav->breadcrumb['Edit Access'] = array();	
	$zulu->nav->breadcrumb[$file_data['name']] = array();
	$zulu->nav->title = "Edit ".$type;

	//Form Submit
	if($_POST['action']=='edit') {
		$form_edit->valid = true;
			
		$_POST['stat_update'] = time();
		$_POST['tag'] = addslashes($_POST['tag']);
		$_POST['expire'] = $zulu->dateEncode($_POST['expire']);
		$pass = $_POST['password'];
		$_POST['password'] = $class_user->password_hash($_POST['password']);

		if(trim($_POST['max_count'])!=NULL && !is_numeric($_POST['max_count'])){
			$zulu->notification_set("Max downloads must be a number.",2);
			$form_edit->valid = false;
		}
		if($form_edit->valid) {
			$whitelist_ips = [];
			foreach($_POST['whitelist_ips'] as $ip){
				if(trim($ip)!=NULL){
					$whitelist_ips[] = $zulu->esc($ip);
				}
			}
			$_POST['whitelist_ips'] = serialize($whitelist_ips);
			$add_array = ['tag','expire','whitelist_ips','max_count','stat_update'];
			if(trim($pass)!=NULL){
				$add_array[] = 'password';
			}
			
			$query = "UPDATE file_quick SET ".$db->build(1,$add_array)." WHERE id = '".$id."'";
			if($db->query($query)) {
				$id = ($id>0?$id:$db->insert_id);
				
				$zulu->notification_set($type." updated successfully.",1);
				if($_GET['Client'] > 0) {
					header("Location: ".$zulu->link_page('client',array('query'=>array('id'=>$_GET['Client'],'Action'=>$_GET['From']))));
					exit;
				}
				header("Location: ".$zulu->link_page('file',array('query'=>array('Action'=>'quick','FileRoot'=>$file_data['parent_id']))));
				exit;
			} else {
				$zulu->notification_set("A database error occurred.",2);
			}
		}
	}
	$zulu->template->js_code[] = "";
}
if(PAGE_action=='edit') { //edit page
	$form_edit = new form;
	
	$zulu->template->js_code[] = "
	
		$(function(){
			
	  Dropzone.options.myDropzone = {
		maxFilesize: 5,
		addRemoveLinks: true,
		dictResponseError: 'Server not Configured',
		init:function(){
		  var self = this;
		  // config
		  self.options.addRemoveLinks = true;
		  self.options.dictRemoveFile = \"Delete\";
		  self.on(\"queuecomplete\", function (progress) {
			location.reload();
		  });
		}
	  };
	});
	";
	
	if(!empty($_FILES)&&$_GET['New']==1) { //fresh upload
		if($class_file->file_upload(0,array('parent_id'=>$db->escape_string($_GET['FileRoot'])))) {
			$zulu->notification_set("Files were uploaded successfully.",1);
			header("Location: ".$zulu->link_page('file',array('query'=>array('FileRoot'=>$_GET['FileRoot']))));
		} else {
			$zulu->notification_set("Your file upload failed, please try again.",2);
			header("Location: ".$zulu->link_page('file',array('query'=>array('FileRoot'=>$_POST['parent_id']))));
		}
		exit;
	}
	
	if($_GET['Type']=='folder') {
		$class_file->file_is = false;
		$type = 'Folder';
	} else {
		$class_file->file_is = true;
		$type = 'File';
	}

	if(PAGE_id<1) {
		$id = 0;
		$new = true;	
		
		//Check type
		
		$zulu->nav->breadcrumb['New '.$type] = array();
		$zulu->nav->title = "New ".$type;
	} else {
		$id = ($_POST['id']>0?$_POST['id']:($_GET['id']>0?$_GET['id']:0));
		
		$file_data = $class_file->file_data(array('id'=>$id));
		$file_meta = $class_file->file_meta($id);
		$type = ($file_data['type']=='folder'?"Folder":"File");
		$class_file->file_is = ($file_data['type']=='folder'?false:true);
		
		if(!$_POST) {
			foreach($file_data as $key=>$val) {
				$_POST[$key] = $val;	
			}
			unset($_POST['password']);
			
			foreach($file_meta as $val) {
				$_POST[$val['field']] = $val['value'];
			}
		}
		if(!empty($_FILES)) { //fresh upload
			if($class_file->file_upload($id)) {
				echo "OK";
			} else {
				echo "ERROR";
			}
			exit;
		}
		
		$zulu->nav->breadcrumb['Edit '.$type] = array();	
		$zulu->nav->breadcrumb[$file_data['name']] = array();
		$zulu->nav->title = "Edit ".$type;
		
		if($class_file->file_uploaded($id)) {
			$class_file->this_file->_exists = true;
		} else {
			$class_file->this_file->_exists = false;
		}
	}
	
	$client_id = $db->escape_string($_GET['Client']);
	if($client_id > 0 || $file_data['object'] == 'client') {
		$client_id = ($client_id>0?$client_id:$file_data['object_id']);
		$dir_object_id = $client_id;
		$dir_object = 'client';
	}
	
	//Form Submit
	if(!empty($_FILES)) {
		if($class_file->file_upload(PAGE_id)) {
			echo "1";
		} else {
			echo "0";
		}
		exit;
	}
	if($_POST['action']=='edit') {
		$form_edit->valid = true;
		
		if($_POST['type']=='file' || $_POST['type']=='folder') {
			if($form_edit->validate(array('name'))) {
				$zulu->notification_set("Please enter a name for your ".$_POST['type'].".",2);
				$form_edit->valid = false;
			}
			
			if($id>0) {
				$_POST['stat_update'] = time();
				$query = "UPDATE file SET ".$db->build(1,array('name','sort','parent_id','stat_update'))." WHERE id = '".$id."'";
			} else {
				$_POST['stat_add'] = time();
				$_POST['stat_update'] = time();
				$_POST['user_id'] = $_SESSION['zl_user']['id'];
				$_POST['token'] = zulu::serial();
				$_POST['serial'] = zulu::serial(32);
				$_POST['type'] = $_POST['type'];
				if($client_id > 0) {
					$_POST['object'] = 'client';
					$_POST['object_id'] = $client_id;
				}
				$query = "INSERT INTO file ".$db->build(2,array('serial','token','user_id','parent_id','type','name','sort','stat_add','stat_update','object','object_id'));
			}
			
			if($form_edit->valid) {
				if($db->query($query)) {
					$id = ($id>0?$id:$db->insert_id);
					//$zulu->meta_update("file",$id,"role",$_POST['role']);
					$zulu->notification_set(ucfirst($_POST['type'])." ".($id>0?"updated":"created")." successfully. Return to folder <a href=\"".$zulu->link_page('file',array('query'=>array('FileRoot'=>$_POST['parent_id'])))."\">here</a>.",1);
					$from = $zulu->esc($_GET['From']);
					if($client_id > 0 && (!$new || $_POST['type']=='folder')) {
						header("Location: ".$zulu->link_page('client',array('query'=>array('id'=>$client_id,'Action'=>$from))));
						exit;
					}
					header("Location: ".$zulu->link_page('file',array('query'=>array('Action'=>'edit','id'=>$id,'Client'=>$client_id,'From'=>$from))));
					exit;
				} else {
					$zulu->notification_set("A database error occurred.",2);
				}
			}
		}
	}
		
	//Include
	$zulu->template->css_file[] = UPLOADER_root."dist/dropzone.css";
	$zulu->template->js_file[] = UPLOADER_root."dist/dropzone.js";
	
	//$zulu->template->jquery[] = "$(\"#file-edit-dropzone\").dropzone({ url: \"".$zulu->link_page('file',array('query'=>array('Action'=>'edit','id'=>$id)))."\" });";
	$zulu->template->js_code[] = "var myDropzone = new Dropzone(\"div#file-edit-dropzone\", { url: \"/file/post\"});";
}