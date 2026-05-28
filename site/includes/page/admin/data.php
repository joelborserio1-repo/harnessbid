<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- DEFINITIONS
define(PAGE_file,'data');
define(PAGE_name,'Import / Export');
$zulu->nav->breadcrumb[PAGE_name] = array("link"=>$zulu->link_page(PAGE_file));

//-- AUTHORISED?
$class_user->user_authorised_check(PAGE_action);

//-- ADMIN MASTER SECTION
if(MASTER_section=='admin') { //admin section

	$form_edit = new form;
		
	//-- ACTION: INDEX
	if(PAGE_action==NULL) {
		
		//--LOAD CSV
		if($_POST['action']=='load_csv'&&$_FILES) {
			$load_file = $class_file->file_upload_raw('csv',NULL,'../temp/');
			$_SESSION['zl_data']['current_import_file'] = $load_file['name'];
			$_SESSION['zl_data']['current_import_tpl'] = $_POST['template'];
			
			header("Location: ".$zulu->link_page('data',array('query'=>array('Action'=>'import'))));
			exit;
		}
		
		//--LOAD CSV
		if($_POST['action']=='create_csv') {
			$_SESSION['zl_data']['current_export_tpl'] = $_POST['template'];
			
			header("Location: ".$zulu->link_page('data',array('query'=>array('Action'=>'export'))));
			exit;
		}
	}

	//-- ACTION: IMPORT
	if(PAGE_action=='import') {
		if($_SESSION['zl_data']['current_import_file']==NULL) {
			$zulu->notification_set("No data file was loaded, please try again.",2);
			header("Location: ".$zulu->link_page('data'));
			exit;
		}
		if($_SESSION['zl_data']['current_import_tpl']==NULL) {
			$zulu->notification_set("No template type was selected, please try again.",2);
			header("Location: ".$zulu->link_page('data'));
			exit;
		}
		if(!file_exists($class_file->file_root_temp.$_SESSION['zl_data']['current_import_file'])) {
			$zulu->notification_set("Data file loaded does not exist, please try again.",2);
			header("Location: ".$zulu->link_page('data'));
			exit;	
		}
		
		//Compile data
		$file = $class_file->file_root_temp.$_SESSION['zl_data']['current_import_file'];
		$csvFile = file($file);
		$i = 0;
		foreach ($csvFile as $this_line) {
		   $id = 0;
		   $row[] = str_getcsv($this_line);
		   $i++;
		   if($i==5) {
			break;   
		   }
		}
		
		//removed ''=>"Select Field...", from list for big column lists
		//Prepare main header with inputs
		$col_i = 0;
		$option_compilation = array('skip_field'=>"Select Data / Skip Column")+$class_data->field_array($_SESSION['zl_data']['current_import_tpl'],'import');
		foreach($row[0] as $row_loop) {
			$input_html = $form_edit->input_html('select',"column[".$col_i."]",$_POST['column'][$col_i],array("option"=>$option_compilation));
			$table_column[] = array("Column ".($col_i+1).$input_html);
			$col_i++;
		}
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);
				
		//Add preview first 5 rows
		foreach($row as $this_row) {
			$table_row[] = array("content" => $this_row);
		}
		
		$zulu->template->body = $zulu->table_render($table_row,0,array('data_table'=>false,'class'=>'data-import'));
		$zulu->nav->title = PAGE_name;
		
		//-- FORM ACTION: EXECUTE
		if($_POST['action']=='execute') {
			
			//Compile data		
			$class_name = "class_".$_SESSION['zl_data']['current_import_tpl'];
			$class_function = $_SESSION['zl_data']['current_import_tpl']."_edit";
			$file = $class_file->file_root_temp.$_SESSION['zl_data']['current_import_file'];
			$template = $class_data->template[$_SESSION['zl_data']['current_import_tpl']];
			$column_flip = array_flip($_POST['column']);
			$class_data->vars->table = $template['table'];
			$csvFile = file($file);
			$update_rows = $_POST['update_rows'];
			$i = 0;
			
			foreach ($csvFile as $this_line) {
				if($_POST['skip_first'] && $i==0) {
					$i++;
					continue;	
				}
				
			   $id = 0;
			   $row = str_getcsv($this_line);
					
				//-- Column Based Checks
				foreach($row as $index=>$item) {
					$field = $_POST['column'][$index];
					$meta_field = (strstr($field,"m_")?true:false);
					if($meta_field) {
						$mfield = str_replace("m_","",$field);
						$field_setting = $template['import_fields']['meta'][$mfield];
					} else {
						$field_setting = $template['import_fields']['root'][$field];
					}
					if($field=='skip_field') {
						continue;	
					}
					
					//validate
					//-- required
					if(in_array($field,$template['import_require'])&&trim($item)==NULL) {
						$skip = true;
						$reason[] = "Field '".$class_data->field_label($field,$field_setting)."' is required.";	
					}
					//-- run custom validation
					if($field_setting['validate']!=NULL) {
						$valid = $class_data->data_validate($item,$field_setting['validate']);
						if(!$valid['success']) {
							$skip = true;
							$reason[] = "Wrong format for '".$class_data->field_label($field,$field_setting)."'. ".$valid['message'];	
						}
					}
					
					//format
					//-- validate based on requirements
					if($field_setting['format']!=NULL) {
						$item = $class_data->data_format($item,$field_setting['format']);
					}
					
					//append to insert array
					if($meta_field) {
						$meta[$mfield] = $item;
					} else {
						$data[$field] = $item;
					}
				}
				
				//-- Row Based Checks
				//-- check duplication
				if(count($template['import_duplicate'])>0&&!$skip) {
					if(isset($template['import_duplicate_status'])) {
						$chkd['status'] = $template['import_duplicate_status'];	
					}
					$dup_check = $class_data->check_duplicate($row,$template['import_duplicate'],$column_flip,$chkd);
					if($dup_check > 0 && !$update_rows) {
						$skip = true;
						$reason[] = "Duplicate row '".$row[$column_flip[$template['import_duplicate'][0]]]."', this record already exists.";	
					}
					if($update_rows) {
						$id = $dup_check;	
					}
				}
				//-- required either
				if(count($template['import_require_either'])>0) {
					$count_blank = 0;
					foreach($template['import_require_either'] as $field) {
						if(trim($row[$column_flip[$field]])==NULL) {
							$required[] = "'".$class_data->field_label($field,$template['import_fields']['root'][$field])."'";
							$count_blank++;
						}
					}
					if($count_blank>=count($template['import_require_either'])) {
						$skip = true;
						$reason[] = "One of the following is required ".implode(", ",$required).".";	
						unset($required);
					}
				}
				
				if(!$skip) {
					//echo "ok {$i}<br>";
					$insert['success'] = true;
					$insert = ${$class_name}->$class_function($id,$data,$meta);
				} else {
					$insert['success'] = false;
					$insert['message'] = implode(" ",$reason);
				}
				
				if($insert['success']) {
					$rw_ok[] = $i;
				} else {
					$rw_fail[] = $i;
					$log[] = "Skipped - Row #".$i." - ".($insert['message']!=NULL?$insert['message']:"Error importing data.");	
				}
			   $i++;
			   unset($skip,$reason);
			}
			
			//--do
			//print_r($log);
		//	exit;
			//--do
			$zulu->notification_set("Import has completed.<br><br>Notification log:<br>".implode("<br>",$log),1);
			header("Location: ".$zulu->link_page($template['page']));
			exit;	
		}
	}
	//-- ACTION: EXPORT
	if(PAGE_action=='export') {
		if($_SESSION['zl_data']['current_export_tpl']==NULL) {
			$zulu->notification_set("No template type was selected, please try again.",2);
			header("Location: ".$zulu->link_page('data'));
			exit;
		}
		
		$root_path = "uploads/".$_SESSION['zl_data']['current_export_tpl']."_export-".$class_user->authorised->id."-".time().".csv";
		$file = fopen(dirname(__FILE__)."/../../../".$root_path, "w");
		$class_name = "class_".$_SESSION['zl_data']['current_export_tpl'];
		$class_function = $_SESSION['zl_data']['current_export_tpl']."_data";
		$class_function_meta = $_SESSION['zl_data']['current_export_tpl']."_meta";
		
		//-- loop heading row
		foreach($class_data->template[$_SESSION['zl_data']['current_export_tpl']]['export_fields']['root'] as $template_row=>$setting) {
			$export_row[] = $template_row;
		}
		foreach($class_data->template[$_SESSION['zl_data']['current_export_tpl']]['export_fields']['meta'] as $template_row=>$setting) {
			$export_row[] = $template_row;
		}
		foreach($class_data->template[$_SESSION['zl_data']['current_export_tpl']]['export_fields']['custom'] as $template_row=>$setting) {
			$export_row[] = $template_row;
		}
		fwrite($file, implode(',',$export_row)."\n");	
		unset($export_row);
		
		//-- loop through data
		$row_loop = ${$class_name}->$class_function();
	//	$client_data = $class_client->client_data(array('type'=>'all'));
		foreach($row_loop as $row) {
			foreach($class_data->template[$_SESSION['zl_data']['current_export_tpl']]['export_fields']['root'] as $template_row=>$setting) {
				if($setting['convert']!=NULL) {
					$row[$template_row] = $class_data->data_decode($row[$template_row],$setting['convert']);
				}
				if(count($setting['match'])>0) {
					foreach($setting['match'] as $val=>$label) {
						if($val==$row[$template_row]) {
							$row[$template_row] = $label;
						}
					}
				}
				$export_row[] = $row[$template_row];
			}
			
			$meta_data = ${$class_name}->$class_function_meta($row['id']);
			foreach($class_data->template[$_SESSION['zl_data']['current_export_tpl']]['export_fields']['meta'] as $template_row=>$setting) {
				if($setting['convert']!=NULL) {
					$meta_data[$template_row] = $class_data->data_decode($row[$template_row],$setting['convert']);
				}
				if(count($setting['match'])>0) {
					foreach($setting['match'] as $val=>$label) {
						if($val==$meta_data[$template_row]) {
							$meta_data[$template_row] = $label;
						}
					}
				}
				$export_row[] = $meta_data[$template_row]['value'];
			}
			
			foreach($class_data->template[$_SESSION['zl_data']['current_export_tpl']]['export_fields']['custom'] as $template_row=>$setting) {
				if($setting['data_function']!=NULL) {
					$custom_val = $class_data->custom_data($row,$setting['data_function']);
					$export_row[] = $custom_val;	
				}
			}
			
			fwrite($file, implode(',',$export_row)."\n");	
			unset($export_row);
		}
		fclose($file);
		
		$zulu->notification_set("Export was created, click the link to download below:<br><br><a href=\"".MAIN_url.$root_path."\">".MAIN_url.$root_path."</a>",1);
		header("Location: ".$zulu->link_page('data'));
		exit;
	}
	
}

$zulu->nav->title = PAGE_name;