<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- DEFINITIONS
define(PAGE_file,'object');
define(PAGE_name,'Objects');
$zulu->nav->breadcrumb[PAGE_name] = array("link"=>$zulu->link_page(PAGE_file));

//-- AUTHORISED?
$class_user->user_authorised_check();

//if(!$class_user->authorised->opt_mail) {
//	$zulu->notification_set("Sorry, you are not authorised to use the Mail area.",2);
//	header("Location: ".$zulu->link_page("index"));exit;
//}


//-- ADMIN MASTER SECTION
if(MASTER_section=='admin') { //admin section
		
	$zulu->template->head = "";
	$zulu->template->body = "";
	
	if(PAGE_action=='type'){
		
		function edit_bt($id) {
			global $zulu;
			return "
				<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'edit_type')))."\"><button class=\"btn btn-primary btn-circle\" type=\"button\"><i class=\"fas fa-edit\"></i></button></a> 
				<a class=\"confirm-delete\" href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'delete')))."\"><button class=\"btn btn-danger btn-circle\" type=\"button\"><i class=\"fas fa-times\"></i></button></a> 
			";	
		}
		
		$form_edit = new form;
		
		$table_column[] = array("Icon",array('class'=>array('center')));
		$table_column[] = array("Name",array('class'=>array('')));
		$table_column[] = array("Description",array('class'=>array('')));
		$table_column[] = array("Object Count",array('class'=>array('')));
		$table_column[] = array("Added",array('class'=>array('')));
		$table_column[] = array("Updated",array('class'=>array('')));
		$table_column[] = array("Actions",array('class'=>array('right')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);

		$data_row = $class_object->object_type_data();

		foreach($data_row as $row) {
			$object_data = $class_object->object_data(['object_type_id'=>$row['id']]);
			
			$table_row[] = array("content" => array(
				array('<i class="'.$row['icon'].'"></i>', array('class'=>array('center'))),
				array($row['name']),
				array($row['description']),
				array(count($object_data)),
				array(zulu::time_history($row['stat_add'])),
				array(zulu::time_history($row['stat_update'])),
				array(edit_bt($row['id']),array('class'=>array('right')))
			));
		}
		
		$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'basket'));
		$zulu->nav->title = PAGE_name." Types";
		$zulu->nav->breadcrumb['Types'] = array();
	}
	
	if(PAGE_action==NULL) {	//grid page
		
		$object_types = $class_object->object_type_data();
		
		function edit_bt($id) {
			global $zulu;
			return "
				<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'edit')))."\"><button class=\"btn btn-primary btn-circle\" type=\"button\"><i class=\"fas fa-edit\"></i></button></a> 
				<a class=\"confirm-delete\" href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'delete')))."\"><button class=\"btn btn-danger btn-circle\" type=\"button\"><i class=\"fas fa-times\"></i></button></a> 
			";	
		}
		
		$form_edit = new form;
		$zulu->template->type_tables = [];
		$type_counts = [];
		foreach($object_types as $type){
			
			$table_column[$type['id']][] = array("Name",array('class'=>array('')));
			$table_column[$type['id']][] = array("Reference",array('class'=>array('')));
			$table_column[$type['id']][] = array("Description",array('class'=>array('')));
			$table_column[$type['id']][] = array("Added",array('class'=>array('')));
			$table_column[$type['id']][] = array("Updated",array('class'=>array('')));
			$table_column[$type['id']][] = array("Actions",array('class'=>array('right')));
			$table_row[$type['id']][] = array(
					"header"	=>	 true,
					"class"		=>	"",
					"content"	=>	$table_column[$type['id']]);

			$data_row = $class_object->object_data(['object_type_id'=>$type['id']]);
			
			$type_counts[$type['id']] = count($data_row);
				
			foreach($data_row as $row) {
				$table_row[$type['id']][] = array("content" => array(
					array($row['name']),
					array($row['reference']),
					array($row['description']),
					array(zulu::time_history($row['stat_add'])),
					array(zulu::time_history($row['stat_update'])),
					array(edit_bt($row['id']),array('class'=>array('right')))
				));
			}
			$zulu->template->type_tables[$type['id']] = $zulu->table_render($table_row[$type['id']],0,array('class'=>'basket'));
		}
		//$zulu->template->body = 
		
		$zulu->nav->title = PAGE_name;
	}

	if(PAGE_action=='delete') { //delete
//		if($class_rule->delete(PAGE_id)) {
//			$zulu->notification_set("Rule removed successfully.",1);
//			header("Location: ".$zulu->link_page(PAGE_file));
//		} else {
//			$zulu->notification_set("A database error occurred.",2);
//		}
	}
	
	if(PAGE_action=='edit') { //edit page
		$form_edit = new form;
		if(PAGE_id<1) {
			if($_GET['type_id']>0){
			//Get the type
				$object_type_data = $class_object->object_type_data(['id'=>$db->escape_string($_GET['type_id'])]);
			}else{
				$zulu->notification_set("No Type specified.",1);
				header("Location: ".$zulu->link_page(PAGE_file));
			}
			$id = 0;
			$new = true;	
			$zulu->nav->breadcrumb['New '.$object_type_data['name'].''] = array();
			$zulu->nav->title = "New ".$object_type_data['name']."";
		} else {
			$id = ($_POST['id']>0?$_POST['id']:($_GET['id']>0?$_GET['id']:0));
			
			$object_data = $class_object->object_data(array('id'=>$id));
			$object_field_value_data = $class_object->object_field_value_data(['object_id'=>$id]);
			
			if(!$_POST) {
				foreach($object_data as $key=>$val) {
					$_POST[$key] = $val;	
				}
				foreach($object_field_value_data as $field_value) {
					$unser = @unserialize($field_value['value']);
					if($unser !== false) {
						$_POST['field']['type_custom'][$field_value['field']] = $unser;
					}else{
						$_POST['field']['type_custom'][$field_value['field']] = $field_value['value'];
					}
				}
			}

			$object_type_data = $class_object->object_type_data(['id'=>$db->escape_string($object_data['object_type_id'])]);
			
			$zulu->nav->breadcrumb['Edit '.$object_type_data['name'].''] = array();	
			$zulu->nav->title = "Edit ".$object_type_data['name']."";
		}
		
		$has_custom_fields = false;
		if($class_object->has_custom_fields($object_type_data['id'])){
			$has_custom_fields = true;
			$custom_form = $class_object->custom_field_build($object_type_data['id'],['form_wrapper_hide'=>true,'submit_hide'=>true]);
		}
		
		//Form Submit
		if($_POST['action']=='edit') {
			$form_edit->valid = true;
			
			if($form_edit->validate(['name'])) {
				$form_edit->valid = false;
				$zulu->notification_set("Please enter a name.",2);
			}
			if($form_edit->validate(['reference'])) {
				$form_edit->valid = false;
				$zulu->notification_set("Please enter a reference *.",2);
			}
			if($form_edit->validate(['description'])) {
				$form_edit->valid = false;
				$zulu->notification_set("Please enter a description *.",2);
			}
			
			//-- Validate data
			$custom_form_array = $class_object->form_build_array($object_type_data['id']);
			foreach($custom_form_array['field'] as $key=>$val) {
				if(((is_array($_POST['field']['type_custom'][$key])&&count($_POST['field']['type_custom'][$key])<=0)||(!is_array($_POST['field']['type_custom'][$key])&&trim($_POST['field']['type_custom'][$key])==NULL))&&$val['required']) {
					$error_string = "The field '".$zulu->shorten($val['label'],40)."' is required.";
					if($val['input']['type']=='checkbox') {
						$error_string = "Please tick the '".$zulu->shorten($val['label'],40)."' checkbox.";
					}
					if($val['input']['type']=='radio') {
						$error_string = "Please select an option for '".$zulu->shorten($val['label'],40)."'.";
					}
					if($val['input']['config']['custom']['valid_msg_required']!=NULL) {
						$error_string = stripslashes($val['input']['config']['custom']['valid_msg_required']);
					}
					$error_log[] = $error_string;
					$form_edit->valid = false;
				}
				if($val['input']['type']=='checkbox'&&$val['required_all']>0&&count($val['input']['config']['option'])!=count($_POST['field']['type_custom'][$key])) {
					$error_string = "Please tick ALL checkboxes for '".$zulu->shorten($val['label'],40)."'.";
					if($val['input']['config']['custom']['valid_msg_required']!=NULL) {
						$error_string = stripslashes($val['input']['config']['custom']['valid_msg_required']);
					}
					$error_log[] = $error_string;
					$form_edit->valid = false;
				}
			}
			if(count($error_log)>0){
				$zulu->notification_set(implode('<br>',$error_log),2);
			}
			
			if($form_edit->valid) {
				$data = [
					'name'				=> $db->escape_string($_POST['name']),
					'description' 		=> $db->escape_string($_POST['description']),
					'reference' 		=> $db->escape_string($_POST['reference']),
					'object_type_id' 	=>$db->escape_string($object_type_data['id']),
				];
				$data = $class_object->object_edit($id,$data);
				
				if($data['success'] && $data['id']>0) {
					$db->query("DELETE FROM object_field_value WHERE object_id='".$data['id']."'");
					$custom_form_array = $class_object->form_build_array($object_type_data['id']);
					foreach($_POST['field']['type_custom'] as $key=>$val) {
						if(is_array($val)) {
							$val = serialize($val);
						}
						$db->query("INSERT INTO object_field_value (object_id,field,field_label,value,stat_add) VALUES ('".$data['id']."','".$key."','".$custom_form_array['field'][$key]['label']."','".$val."',".time().")");
					}
					
					$zulu->notification_set($object_type_data['name']." Object ".(!$new?"updated":"created")." successfully.",1);
					header("Location: ".$zulu->link_page(PAGE_file));
					exit;
				} else {
					$zulu->notification_set("A database error occurred.",2);
				}
			}
		}
		
		
	}
	
	if(PAGE_action=='edit_type') { //edit page
		$form_edit = new form;
		
		//Icon Picker JS
		$zulu->template->css_file[] = TPL_rel."assets/fontawesome-iconpicker/dist/css/fontawesome-iconpicker.min.css";
		$zulu->template->js_file[] = TPL_rel."assets/fontawesome-iconpicker/dist/js/fontawesome-iconpicker.js";
		$zulu->template->jquery[] = "
			$('#icon_picker').iconpicker({
                //title: 'Dropdown with picker',
                //component:'.btn > i'
            });
			$(document).on('click', '.iconpicker-search', function (e) {
				return false;
        	});
			$('#icon_picker').on('iconpickerSelected', function(e) {
				$('#icon_input').val(e.iconpickerValue);
			});
		";
		//Icon Picker JS END
		
		//Fields JS
		$zulu->template->jquery[] = "
			$('#input-settings-checkbox').hide();
			get_field_table();
			
			$('select[name=\"field_input\"]').change(function() {
				console.log($(this).val());
				$('#input-settings-checkbox').slideUp(300);
				if($(this).val() == 'checkbox') {
					if(!$('#input-settings-checkbox').is(':visible')) {
						$('#input-settings-checkbox').slideDown(300);
					}
				}
				if($(this).val() == 'select' || $(this).val() == 'checkbox' || $(this).val() == 'radio') {
					if(!$('#input-option-section').is(':visible')) $('#input-option-section').slideDown(300);
				}
				else if($('#input-option-section').is(':visible')) $('#input-option-section').slideUp(300);
				if($(this).val() == 'text') $('#text-option-section').slideDown(300);
				else if($('#text-option-section').is(':visible')) $('#text-option-section').slideUp(300);
				
				if($(this).val() == 'text' || $(this).val() == 'break') {
					if($('#input-settings').is(':visible')) $('#input-settings').slideUp(300);
				} else {
					if(!$('#input-settings').is(':visible')) $('#input-settings').slideDown(300);
				}
				
				if($(this).val() == 'number') {
					$('#number-settings').slideDown(300);
				} else {
					if($('#number-settings').is(':visible')) $('#number-settings').slideUp(300);
				}
				
				if($(this).val() == 'checkbox' || $(this).val() == 'radio') {
					if(!$('#option-settings').is(':visible')) $('#option-settings').slideDown(300);
				} else {
					if($('#option-settings').is(':visible')) $('#option-settings').slideUp(300);
				}
				
				if($(this).val() == 'select'){
					$('#select-settings').slideDown(300);
				}else if($('#select-settings').is(':visible')) {
					$('#select-settings').slideUp(300);
				}else{
					$('#select-settings').slideUp(300);
				}
				
			});
			$('select[name=\"field_input\"]').trigger('change');
			
			$(\"#sortable-rows\").sortable();
			$(\"body\").on('click','#row-add',function() {
				$(\"#select-options-table\").append('<tr><td>".$form_edit->input_html('input','config[option][]', '', ['class'=>['field-option']])."</td><td class=\"right w80\"><a href=\"#\" class=\"btn btn-default btn-xs clear-row\" title=\"Clear row\"><i class=\"fas fa-eraser\"></i></a> <a href=\"#\" class=\"btn btn-danger btn-xs remove-row\" title=\"Remove row\"><i class=\"fas fa-times\"></i></a></td></tr>');
			});
			$('body').on('click','.clear-row',function() {
				var trow = $(this).parent().parent();
				$(trow).find('input').val('');
				return false;
			});
			$('body').on('click','.remove-row',function() {
				var trow = $(this).parent().parent();
				$(trow).remove();
				return false;
			});
			
			$('body').on('click','#add_field',function() {
				console.log('Adding Field');
				if(validate_field()){
					add_field();
				}
				return false;
			});
			$('body').on('click','.btn-delete-field',function() {
				var field_id = $(this).data('field_id');
				if(field_id>0 && confirm('Are you sure you want to delete this field?')){
					$(this).closest('tr').remove();
					delete_field(field_id);
				}
				
			});
			$('#add_field_modal').on('shown.bs.modal', function (e) {
				$('select[name=\"field_input\"]').trigger('change');
			})
			
		";
		$zulu->template->jquery[] = "
			function delete_field(field_id){
				if(field_id){
					$.post('/admin/index.php?Page=object&Action=edit_type',
					{
						action:'delete_field',
						field_id:field_id,
					},
					function(data, status){
						data = JSON.parse(data);
						if(data.success){
							console.log('Deleted');
						}else{
							console.log('Error Deleting');
						}
					});
				}
				
			}
			function validate_field(){
				return true;
			}
			
			function add_field(){
				var field_data = {};
				field_data.field_name 			= $('input[name=\"field_name\"]').val();
				field_data.field_input 			= $('select[name=\"field_input\"]').val();
				field_data.field_width 			= $('select[name=\"field_width\"]').val();
				if($('input[name=\"field_required\"]').is(':checked')){
					field_data.field_required = 1;
				}else{
					field_data.field_required = 0;
				}
				if($('input[name=\"field_required\"]').is(':checked')){
					field_data.field_required_all =	1;
				}else{
					field_data.field_required_all = 0;
				}
				//Config
				field_config = {};
				field_config.custom = {};
				field_config.custom.min  = $('input[name=\"config[custom][min]\"]').val();
				field_config.custom.max  = $('input[name=\"config[custom][max]\"]').val();
				
				field_config.custom.step  = $('input[name=\"config[custom][step]\"]').val();
				field_config.custom.option_width  = $('input[name=\"config[custom][option_width]\"]').val();
				if($('input[name=\"config[custom][multiple]\"]').is(':checked')){
					field_config.custom.option_width =	1;
				}else{
					field_config.custom.option_width = 0;
				}
				
				//Options
				var field_options = [];
				$('.field-option').each(function() {
					var option_val = $(this).val();
					field_options.push(option_val);
				});
				
				$.post('/admin/index.php?Page=object&Action=edit_type',
				{
					object_type_id:$('input[name=\"object_type_id\"]').val(),
					action:'add_field',
					field_data:field_data,
					field_options:field_options,
					field_config:field_config,
				},
				function(data, status){
					data = JSON.parse(data);
					if(data.success){
						console.log('Saved');
						$('#add_field_modal').modal('hide');
						get_field_table();
					}else{
						console.log('Error saving');
					}
				});
			}
			
			function get_field_table(){
				$.post('/admin/index.php?Page=object&Action=edit_type',
				{
					object_type_id:$('input[name=\"object_type_id\"]').val(),
					action:'get_field_table',
				},
				function(data, status){
					data = JSON.parse(data);
					if(data.success){
						//console.log(data);
						$('#field_table_container').html(data.field_table_html);
					}else{
						console.log('Error getting field table');
					}
				});
			}
		";
		
		
		//Ajax Actions
		if($_POST['action'] == 'add_field' && $_POST['object_type_id']>0){
			//add a field to the object type
			
			foreach($_POST['field_options'] as $key=>$val) {
				$_POST['field_config']['option'][$val] = $val;
			}

			if($_POST['field_data']['field_input'] == 'number') {
				if($_POST['field_config']['custom']['min']==NULL) {
					unset($_POST['field_config']['custom']['min']);
				}
				if($_POST['field_config']['custom']['max']==NULL) {
					unset($_POST['field_config']['custom']['max']);
				}
			} else {
				unset($_POST['field_config']['custom']['min'],$_POST['field_config']['custom']['max'],$_POST['field_config']['custom']['step']);
			}
			
			if($_POST['field_config']['custom']['multiple']==0) {
				unset($_POST['field_config']['custom']['multiple']);
			}
			
			$field_add_data = [
				'name'			=>	$db->escape_string($_POST['field_data']['field_name']),
				'input'			=>	$db->escape_string($_POST['field_data']['field_input']),
				'width'			=>	$db->escape_string($_POST['field_data']['field_width']),
				'required'		=>	$db->escape_string($_POST['field_data']['field_required']),
				'required_all'	=>	$db->escape_string($_POST['field_data']['field_required_all']),
				'object_type_id'=>	$db->escape_string($_POST['object_type_id']),
				'description'	=>	$db->escape_string($_POST['field_data']['description']),
				'config'		=>	serialize($_POST['field_config']),
			];
			
			$temp = $class_object->object_type_field_edit(0,$field_add_data);
			
			echo json_encode(['success'=>true,'msg'=>'', 'field_data'=>$temp, 'field_add_data'=>$field_add_data, 'temp'=>$_POST['field_options']]);
			exit;
		}elseif($_POST['action'] == 'add_field'){
			echo json_encode(['success'=>false,'msg'=>'No object_type_id']);
			exit;
		}
		
		if($_POST['action'] == 'delete_field' && $_POST['field_id']>0){
			$delete = $class_object->object_type_field_delete($db->escape_string($_POST['field_id']));
			
			echo json_encode(['success'=>true,'msg'=>'', 'delete'=>$delete]);
			exit;
		}elseif($_POST['action'] == 'delete_field'){
			echo json_encode(['success'=>false,'msg'=>'No field_id']);
			exit;
		}
		if($_POST['action'] == 'get_field_table' && $_POST['object_type_id']>0){
			// 
			$object_type_field_data = $class_object->object_type_field_data(['object_type_id'=>$db->escape_string($_POST['object_type_id'])]);
			foreach($object_type_field_data as $field){
				$actions = [];
				$actions[] = '<button type="button" class="btn btn-danger btn-xs btn-delete-field" data-field_id="'.$field['id'].'"><i class="fas fa-times"></i> Delete</button>';
				$required = '';
				if($field['required']){
					$required = '<span class="opt opt-success"><span class="fas fa-check"></span>';
					if($field['required_all']){
						$required .= 'All';
					}
				}else{
					$required = '<span class="opt opt-danger"><span class="fas fa-times"></span></span>';
				}
				
				$field_table_rows[] = "<tr class=\"\">
										<td>".$field['name']."</td>
										<td>".$class_form_post->config->field_types[$field['input']]."</td>
										<td>".$class_form_post->config->field_widths[$field['width']]."</td>
										<td class=\"center\">".$required."</td>
										<td class=\"right\">".implode('&nbsp;',$actions)."</td>
									</tr>";
			}
			
			
			$field_table_html = "
			<table class=\"table table-hover table-bordered table-striped line-items\" name=\"transaction-list\" id=\"transaction-list\">
				<thead><tr><th>Field</th><th>Type</th><th>Width</th><th class=\"center\">Required</th><th class=\"right\">Actions</th></tr></thead>
				<tbody>".implode($field_table_rows)."</tbody>
			</table>";
			
			echo json_encode(['success'=>true,'msg'=>'', 'object_type_field_data'=>$object_type_field_data, 'field_table_html'=>$field_table_html,'temp'=>$class_form_post->config->field_types]);
			exit;
		}elseif($_POST['action'] == 'add_field'){
			echo json_encode(['success'=>false,'msg'=>'No object_type_id']);
			exit;
		}
		
		if(PAGE_id<1) {
			$id = 0;
			$new = true;	
			$zulu->nav->breadcrumb['New Object Type'] = array();
			$zulu->nav->title = "New Object Type";
			$_POST['config']['option'] = [0=>''];
		} else {
			$id = ($_POST['id']>0?$_POST['id']:($_GET['id']>0?$_GET['id']:0));
			
			$object_type_data = $class_object->object_type_data(array('id'=>$id));
			
			if(!$_POST) {
				foreach($object_type_data as $key=>$val) {
					$_POST[$key] = $val;	
				}
			}
			
			$zulu->nav->breadcrumb['Edit Object Type'] = array();	
			$zulu->nav->title = "Edit Object Type";
		}

		//Form Submit
		if($_POST['action']=='edit') {
			
			//print_r($_POST);exit;
			$form_edit->valid = true;
			
			if($form_edit->validate(['name'])) {
				$form_edit->valid = false;
				$zulu->notification_set("Please enter a name.",2);
			}
			if($form_edit->validate(['description'])) {
				$form_edit->valid = false;
				$zulu->notification_set("Please enter a description *.",2);
			}
			$data = [
				'name'=> $db->escape_string($_POST['name']),
				'description' => $db->escape_string($_POST['description']),
				'icon' => $db->escape_string($_POST['icon']),
			];
			
			if($form_edit->valid) {
				$data = $class_object->object_type_edit($id,$data);
				
				if($data['success']) {
					$zulu->notification_set("Object Type ".(!$new?"updated":"created")." successfully.",1);
					header("Location: ".$zulu->link_page(PAGE_file, ['query'=>['Action'=>'edit_type', 'id'=>$id]]));
					exit;
				} else {
					$zulu->notification_set("A database error occurred.",2);
				}
			}
		}
		
		$table_column = [
			array("Name",array('class'=>array(''))),
			array("Actions",array('class'=>array('right')))
		];
		$table_row[] = ["header" => true, "class" => "", "content" => $table_column];
		foreach($_POST['config']['option'] as $val) {
			$table_row[] = array("content" => [
				array($form_edit->input_html('input','config[option][]',stripslashes($val), ['class'=>['field-option']])),
				array("<a href=\"#\" class=\"btn btn-default btn-xs clear-row\" title='Clear row'><i class=\"fas fa-eraser\"></i></a> <a href=\"#\" class=\"btn btn-danger btn-xs remove-row\" title='Remove row'><i class=\"fas fa-times\"></i></a>",array('class'=>array('right','w80')))
			]);
		}
		$zulu->template->option_table = $zulu->table_render($table_row,0,array('class'=>'','data_table'=>false,'html_id'=>'select-options-table','tbody'=>['id'=>'sortable-rows']));
		
		
	}



	
}