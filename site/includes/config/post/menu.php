<?php
//(C)2016 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//POST CONFIG: MENU

if(MASTER_section=='admin') {

	//-- ADMIN
	if(PAGE_action==NULL) { //-- ACTION: MAIN PAGE	
			
		//-- Current Menus
		$form_edit = new form;
		$menu_list = $class_website_menu->menu_data();
		$menu_option = [''=>"None"];
		if(count($menu_list)>0) {
			$menu_option += $form_edit->input_array_bind($menu_list,'title','id');
		}
		
		//-- Settings
		if(!$_POST) {
			$setting = $class_setting->setting_data();
			foreach($setting as $key=>$val) {
				if(strstr($key,"ws_menu_")) {
					$newkey = str_replace("ws_menu_","",$key);
					$_POST['menu_default'][$newkey] = $val;
				}
			}
		}
		
		//-- Menu types
		$table_column[] = array("Position",array('class'=>array('')));
		$table_column[] = array("Default",array('class'=>array('right')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);
		
		foreach($WEBSITE_template_menu as $ws_type=>$name) {
			$table_row[] = array("content" =>
				array(
					array("<span title=\"Tag: ".$ws_type."\">{$name}</span>"),
					array($form_edit->input_html("select","menu_default[".$ws_type."]",$_POST['menu_default'][$ws_type],array('option'=>$menu_option)),array('class'=>array('action-field')))
				)
			);
		}
		$table = $zulu->table_render($table_row,0,array('class'=>'basket','data_table'=>false,'js_table'=>false));
		unset($table_row,$table_column);
		
		//-- Options
		$zulu->template->post['html_sidebar'] = "
			<p class=\"adm-col-button-adjust\">&nbsp;</p>
			<div class=\"panel panel-default\">
				<div class=\"panel-heading\">
					<i class=\"fas fa-toggle-on\"></i> Template Menu Allocation
				</div>
				<div class=\"panel-body\">
					<form method=\"post\" action=\"\">
					{$table}
					".$form_edit->input_html("submit","submit",$form_edit->CONFIG_button_save,['class'=>['btn btn-success btn-block']])."
					".$form_edit->input_html("hidden","action","default_menu")."
					</form>
				</div>
			</div>
		";
		
		//-- Post
		if($_POST['action']=='default_menu') {
			
			foreach($_POST['menu_default'] as $key=>$val) {
				$class_setting->setting_edit('ws_menu_'.$key,$val);
			}
			
			$zulu->notification_set("Menus allocated successfully.",1);	
			header("Location: ".$_SERVER['HTTP_REFERER']);
			exit;
		}
		
	}
	
	if(PAGE_action=='edit') { //-- ACTION: EDIT PAGE
	
		if(PAGE_id>0) {
		//-- Menu items
		$table_column[] = array("Tab Name",array('class'=>array('')));
		$table_column[] = array("Link",array('class'=>array('')));
		$table_column[] = array("Status",array('class'=>array('')));
		$table_column[] = array("Actions",array('class'=>array('text-right')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);
		
		$menu_item = $class_website_menu->menu_items(['status'=>['published','draft'],'id'=>PAGE_id]);
		foreach($menu_item as $data) {
			
			$class_website_menu->data->row = $data;
			$title = ($data['title']!=NULL?stripslashes($data['title']):$class_website_menu->menu_title($data['id']));
			if($data['_meta']['icon']!=NULL) {
				$icon = "<span class=\"".$data['_meta']['icon']."\"></span> ";	
			}
			$status = $class_post->post_status($data['status']);
			$status = "<span class=\"opt opt-bord opt-".$status['css']."\"><i class=\"".$status['icon']."\"></i> ".$status['label']."</span>";
			
			//Table
			$table_row[] = array("content" =>
				array(
					array($icon.$title),
					array($class_website_menu->admin_link_label()),
					array($status),
					array("
					
					<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('field[parent_id]'=>$data['id'],'type'=>'menu_item','Action'=>'edit')))."\"><button class=\"btn btn-default btn-xs\" title=\"Create a dropdown item off this tab.\" type=\"button\"><i class=\"fas fa-plus-circle\"></i></button></a>
					
					<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$data['id'],'Action'=>'edit')))."\"><button class=\"btn btn-primary btn-xs\" type=\"button\"><i class=\"fas fa-edit\"></i> Edit</button></a>
					
					<a href=\"".$class_website_menu->menu_link()."\" target=\"_blank\"><button class=\"btn btn-info btn-xs\" type=\"button\"><i class=\"fas fa-laptop\"></i> Preview</button></a>
					
					<a class=\"confirm-delete\" href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$data['id'],'Action'=>'delete')))."\"><button class=\"btn btn-danger btn-xs\" type=\"button\"><i class=\"fas fa-trash-alt\"></i></button></a>",['class'=>['text-right']]),
				),
				"data"=>['post-id'=>$data['id']],
			);
			unset($icon);
			
			//Children
			$child_data = $class_post->post_data(['parent_id'=>$data['id'],'status'=>['published','draft']]);
			if(count($child_data)>0) {
				foreach($child_data as $data) {
					$class_website_menu->data->row = $data;
					$title = ($data['title']!=NULL?stripslashes($data['title']):$class_website_menu->menu_title($data['id']));
					if($data['_meta']['icon']!=NULL) {
						$icon = "<span class=\"".$data['_meta']['icon']."\"></span> ";	
					}
					$status = $class_post->post_status($data['status']);
					$status = "<span class=\"opt opt-bord opt-".$status['css']."\"><i class=\"".$status['icon']."\"></i> ".$status['label']."</span>";
					
					//Table
					$table_row[] = array("content" =>
						array(
							array("<i class=\"far fa-caret-square-right\"></i> ".$icon.$title),
							array($class_website_menu->admin_link_label()),
							array($status),
							array("
													
							<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$data['id'],'Action'=>'edit')))."\"><button class=\"btn btn-primary btn-xs\" type=\"button\"><i class=\"fas fa-edit\"></i> Edit</button></a>
							
							<a href=\"".$class_website_menu->menu_link()."\" target=\"_blank\"><button class=\"btn btn-info btn-xs\" type=\"button\"><i class=\"fas fa-laptop\"></i> Preview</button></a>
							
							<a class=\"confirm-delete\" href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$data['id'],'Action'=>'delete')))."\"><button class=\"btn btn-danger btn-xs\" type=\"button\"><i class=\"fas fa-trash-alt\"></i></button></a>",['class'=>['text-right']]),
						),
						"data"=>['post-id'=>$data['id'],'child'=>'1'],"class"=>'child-row'
					);
					unset($icon);	
				}
			}	
		}
		$table = $zulu->table_render($table_row,0,array('class'=>'menu-item','tbody'=>['id'=>'sortable-rows'],'data_table'=>false,'js_table'=>false));
		unset($table_row,$table_column);
		
		
		//-- JS
		$zulu->template->jquery[] = "
		
		    $(\"#sortable-rows\").sortable({
				update: function(event, ui) {
					var srt = [];
					$(\"#sortable-rows\").children(\"tr\").each(function( index ) {
						srt.push($(this).data('post-id'));
					});
					$.get(\"".$zulu->link_page(PAGE_file,['query'=>['Action'=>PAGE_action,'Do'=>'PostSort','id'=>PAGE_id]])."&Array=\" + srt,function(data) {
						console.log(data);
					});
				}	
			});
    		$(\"#sortable\").disableSelection();";
		}
		//--
		
		//-- Fields
		$FIELD['sidebar'] = [
			'class'=>
				[
					'label'=>'CSS Class',
					'field'	=>	['name'=>'meta[class]','type'=>'input','value'=>$_POST['meta']['class']]],
		];
		$FIELD['head'] = [];
		$FIELD['body'] = [];
		
		//-- Field toggles
		$zulu->template->post['panel_body'] = false;
		
		//-- Form HTML
		$zulu->template->post['html_body'] = $form_edit->input_html("hidden","newitem[parent_id]",($_GET['field']['parent_id']>0?$_GET['field']['parent_id']:$_POST['parent_id']));
		$zulu->template->post['html_body'] = "
		<div class=\"panel panel-default\">
			<div class=\"panel-heading\">
				<i class=\"fas fa-bars\"></i> Menu Items
			</div>
			<div class=\"panel-body\">
				".(PAGE_id>0?"<p><a href=\"".$zulu->link_page(PAGE_file,['query'=>['Action'=>'edit','type'=>'menu_item','field[parent_id]'=>PAGE_id]])."\" class=\"btn btn-primary\"><i class=\"fas fa-plus-circle\"></i> Add Item</a></p>
				
				{$table}":"<p class=\"color-grey no-margin\">Please save your new menu to start adding menu items.</p>")."
			</div>
		</div>".(PAGE_id>0?"
		<div class=\"panel panel-info\">
			<div class=\"panel-heading\">
				<i class=\"fas fa-bolt\"></i> Quick Add
			</div>
			<div class=\"panel-body\">
				<div class=\"row\">
					<div class=\"col-md-12\">
						<div class=\"form-group\">
							<div class=\"row\">
								<div class=\"col-md-2\">
									<label>Link Type</label>
									".$form_edit->input_html('select','ql_type',$_POST['ql_type'],['id'=>'input-type','placeholder'=>"Example: thumbs-up, check",'option'=>$form_edit->make_array($class_website_menu->object_types->options,['label'=>'title'])])."
								</div>
								<div class=\"col-md-3\">
									<label>Link To</label>
									".$form_edit->input_html('select','ql_to',$_POST['ql_to'],['id'=>'input-object','option'=>'-'])."
								</div>
								<div class=\"col-md-2\">
									<label>&nbsp;</label>
									<p><a href=\"#\" class=\"btn btn-default bt-ql-add\"><i class=\"fas fa-plus-circle\"></i> Add</a></p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		":NULL);
		$zulu->template->js_code[] = "
			var page_load = true;
			var object_id = ".($_POST['meta']['object_id']>0?$_POST['meta']['object_id']:'0').";
			var extra_url = '';

			$(document).on('change','#input-type',function() {
			
				if($(this).val()=='custom') {
					$('#input-object').parent().parent().hide(500);
					$('#input-custom').parent().parent().show(500);
					$('#input-title').attr('placeholder','Required - For example: Home, About, Contact Us...');
				} else {
					$('#input-object').parent().parent().show(500);
					$('#input-custom').parent().parent().hide(500);
					$('#input-title').attr('placeholder','Optional - For example: Home, About, Contact Us...');
					
					if(page_load) {
						extra_url = '&Selected=' + object_id;	
						page_load = false;
					}
					
					$(\"#input-object\").html('');
					var object = $(this).val();
					$.get(\"".MAIN_rel."includes/page/ajax.php?Ajax=menu_item_object&Object=\" + object + extra_url,function(data) {
						$(\"#input-object\").html(data);
					});
				}
				return false;
			});
			$(document).on('click','.bt-ql-add',function() {
				if($('#input-object').val()==null) {
					alert(\"Please select an item to link to.\");
					return false;	
				} else {
					var object = $('#input-object').val();
					var object_type = $('#input-type').val();
					$.get(\"".MAIN_rel."includes/page/ajax.php?Ajax=menu_item_add&Object=\" + object + \"&Type=\" + object_type + \"&Parent=\" + ".PAGE_id.",function(data) {
						console.log(data);
						if(data==1) {
							document.location.reload();
						} else {
							alert(\"An error occured.\");
							return false;
						}
					});
				}
			});
			$(\"#input-type\").trigger('change');
		";
	}
} else {
	
	//-- FRONTEND
	
}