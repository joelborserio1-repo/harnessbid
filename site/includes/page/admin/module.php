<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- DEFINITIONS
define(PAGE_file,'module');
define(PAGE_name,'Modules'); 
$zulu->nav->breadcrumb[PAGE_name] = array("link"=>$zulu->link_page(PAGE_file));

//-- AUTHORISED?
$class_user->user_authorised_check();

if(!$class_user->authorised->opt_website) {
	$zulu->notification_set("Sorry, you are not authorised to use the ".PAGE_name." area.",2);
	header("Location: ".$zulu->link_page("index"));exit;
}

//-- ADMIN MASTER SECTION
if(MASTER_section=='admin') { //admin section
		
	$zulu->template->head = "";
	$zulu->template->body = "";
	
	if(PAGE_action==NULL) {	//grid page
        
        if($_GET['Do'] == 'ModSort') {
            $array = explode(",",$_GET['Array']);
            for($i=0; $i<count($array); $i++) {
                if($array[$i] > 0) {
                    $class_module->module_edit($array[$i],['sort'=>$i]);
                }
            }
            exit;
        }
		
		function edit_bt($id) {
			global $zulu;
			return "
				<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'edit')))."\"><button class=\"btn btn-primary btn-circle\" type=\"button\"><i class=\"fas fa-edit\"></i></button></a
			";	
		}
		
		$form_edit = new form;
		
		$table_column[] = array("Status",array('class'=>array('')));
		$table_column[] = array("Name",array('class'=>array('')));
		$table_column[] = array("Front-end Name",array('class'=>array('')));
		$table_column[] = array("Added",array('class'=>array('')));
		$table_column[] = array("Updated",array('class'=>array('')));
		$table_column[] = array("Actions",array('class'=>array('')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);

		if($_GET['Tab'] == NULL) $_GET['Tab'] = 'payment';

        $installed = [];
        $module_data = $class_module->module_data(['type'=>$class_module->module_types[$_GET['Tab']]['code']]);
        foreach($module_data as $module_row) {
            $status_data = $class_module->module_status_info($module_row['status']);
			$table_row[] = array("content" => array(
				array('<span class="'.$status_data['class'].'"><span class="fas '.$status_data['icon'].'"></span> '.$status_data['label'].'</span> <a href="'.$zulu->link_page(PAGE_file,['query'=>['Action'=>'change_status','Module'=>$module_row['class'],'Tab'=>$_GET['Tab']]]).'"><button class="btn btn-xs btn-'.$status_data['class_change'].'">'.$status_data['label_change'].'</button></a>'),
				array(stripslashes($module_row['name'])),
				array(stripslashes($module_row['name_client'])),
				array(($module_row['stat_add']>0?$zulu->time_fancy($module_row['stat_add']):'-')),
				array(($module_row['stat_update']>0?$zulu->time_fancy($module_row['stat_update']):'-')),
				array(($module_row['status']!=''?edit_bt($module_row['id']):NULL),array('class'=>array('right')))
			), "data"=>['module-id'=>$module_row['id']], 'class'=>'installed');
            $installed[] = $module_row['file'];
        }

		foreach(glob($class_module->include_path[$_GET['Tab']]."/m_*.php") as $file) {
            if(!in_array(basename($file), $installed)) {
                require_once $file;
                $class_name = basename($file,'.php');
                $module = new $class_name();

                $module_row = $class_module->module_data(['class'=>$class_name]);
                $status_data = $class_module->module_status_info($module_row['status']);
                $table_row[] = array("content" => array(
                    array('<span class="'.$status_data['class'].'"><span class="fas '.$status_data['icon'].'"></span> '.$status_data['label'].'</span> <a href="'.$zulu->link_page(PAGE_file,['query'=>['Action'=>'change_status','Module'=>$class_name,'Tab'=>$_GET['Tab']]]).'"><button class="btn btn-xs btn-'.$status_data['class_change'].'">'.$status_data['label_change'].'</button></a>'),
                    array(($module_row['name']!=NULL?stripslashes($module_row['name']):$module->title)),
                    array(($module_row['name_client']!=NULL?stripslashes($module_row['name_client']):$module->title_client)),
                    array(($module_row['stat_add']>0?$zulu->time_fancy($module_row['stat_add']):'-')),
                    array(($module_row['stat_update']>0?$zulu->time_fancy($module_row['stat_update']):'-')),
                    array(($module_row['status']!=''?edit_bt($module_row['id']):NULL),array('class'=>array('right')))
                ), 'class'=>'uninstalled');    
            }
		}
		
		$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'basket','tbody'=>['id'=>'sortable-rows']));
		$zulu->nav->title = PAGE_name;
        
        $zulu->template->js_code[] = " 
		$(document).ready(function() {
			$(\"#sortable-rows\").sortable({
				update: function(event, ui) {
					var srt = [];
					$(\"#sortable-rows\").children(\"tr\").each(function(index) {
						srt.push($(this).data('module-id'));
					});
					$.get(\"".$zulu->link_page(PAGE_file,['self'=>true])."&Do=ModSort&Array=\" + srt);
				}	
			});
		});
	";
	}
	if(PAGE_action=='delete') { //delete
		if($class_module->delete(PAGE_id)) {
			$zulu->notification_set("Module removed successfully.",1);
			header("Location: ".$zulu->link_page(PAGE_file,['query'=>[]]));
			exit;
		} else {
			$zulu->notification_set("A database error occurred.",2);
		}
	}
	if(PAGE_action=='change_status') { //change 

		$module_data = $class_module->module_data(['class'=>$_GET['Module']]);
		switch ($module_data['status']) {
			case '':
				require_once $class_module->include_path[$_GET['Tab']]."/".$_GET['Module'].".php";
				$module = new $_GET['Module']();
				$result = $module->install();
				if($result['success']) $result['msg'] = "Module installed successfully.";
				break;
			case '0':
				$result = $class_module->module_edit($module_data['id'],['status'=>'1']);
				if($result['success']) $result['msg'] = "Module activated successfully.";
				break;
			case '1':
				$result = $class_module->module_edit($module_data['id'],['status'=>'0']);
				if($result['success']) $result['msg'] = "Module deactivated successfully.";
				break;
		}
		if($result['success']) $zulu->notification_set($result['msg'],1);
		else $zulu->notification_set($result['reason'],2);
		header("Location: ".$zulu->link_page(PAGE_file,['query'=>['Tab'=>$_GET['Tab']]]));
		exit;
	}
	if(PAGE_action=='edit') { //edit page
		$form_edit = new form;
		
		if(PAGE_id<1) {
			$zulu->notification_set("No module selected.",2);
			header("Location: ".$zulu->link_page(PAGE_file));
			exit;
		}
		
		$id = ($_POST['id']>0?$_POST['id']:($_GET['id']>0?$_GET['id']:0));
		$data_row = $class_module->module_data(array('id'=>$id));
		$data_meta = $zulu->meta_array($class_module->module_meta($id));

		if(!$_POST) {
			foreach($data_row as $key=>$val) {
				$_POST[$key] = stripslashes($val);	
			}
			foreach($data_meta as $key=>$val) {
				$_POST['meta'][$key] = stripslashes($val);	
			}
		}

		require_once $class_module->include_path[$class_module->module_type[$data_row['type']]]."/".$data_row['class'].".php";
		$module = new $data_row['class']();

		$zulu->nav->breadcrumb['Edit Module'] = array();	
		$zulu->nav->breadcrumb[stripslashes($data_row['name'])] = array();
		$zulu->nav->title = "Edit Module";
		
		//Form Submit
		if($_POST['action']=='edit') {
			$form_edit->valid = true;
			
			if($form_edit->valid) {
				
				$data = [
					'name'	=>	addslashes($_POST['name']),
					'name_client'	=>	addslashes($_POST['name_client']),
					'status'	=>	$_POST['status'],
					'info_client'	=>	addslashes($_POST['info_client']),
				];
				$data = $class_module->module_edit($id,$data);
				$result = $module->admin_form_process();
				
				if($data['success']) {
					$zulu->notification_set("Module ".(!$new?"updated":"created")." successfully.",1);
					header("Location: ".$zulu->link_page(PAGE_file,['query'=>['Tab'=>$class_module->module_type[$data_row['type']]]]));
					exit;
				} else {
					$zulu->notification_set("A database error occurred.",2);
				}
			}
		}
	}
	
}