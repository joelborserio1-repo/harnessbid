<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- DEFINITIONS
define(PAGE_file,'form_post');
define(PAGE_name,'Form Posts');
$zulu->nav->breadcrumb[PAGE_name] = array("link"=>$zulu->link_page(PAGE_file));

//-- AUTHORISED?
if(!in_array(PAGE_action,array('submit'))) {
	$class_user->user_authorised_check();
	$class_user->authorised->opt_form_post = true;
}
if($class_user->authorised->id<=0) {
	$class_user->user_public();
	$class_user->authorised->opt_form_post = true;
}
if(!$class_user->authorised->opt_form_post) {
	$zulu->notification_set("Sorry, you are not authorised to use the ".PAGE_name." area.",2);
	header("Location: ".$zulu->link_page("index"));exit;
}

//-- ADMIN MASTER SECTION
if(MASTER_section=='admin') { //admin section

	$zulu->template->head = "";
	$zulu->template->body = "";

	if(PAGE_action==NULL) {	//grid page

        if($_POST&&$_POST['execute']!=NULL) {
            if($_POST['execute'] == 'delete') {
                foreach($_POST['action'] as $id=>$val) {
                    if(!$checkret = $class_form_post->delete($id)) {
                        $error_log[] = "Failed to delete form post ID #".$id;
                    } else {

                    }
                }
                $zulu->notification_set("Selected form posts were removed successfully.",1);
                header("Location: ".$_SERVER['HTTP_REFERER']);
                exit;
            } elseif(is_numeric($_POST['execute'])) {
                foreach($_POST['action'] as $id=>$val) {
                    $class_form_post->form_post_edit($id,['parent_id'=>$_POST['execute']]);
                }
                $zulu->notification_set("Selected form posts were archived successfully.",1);
                header("Location: ".$_SERVER['HTTP_REFERER']);
                exit;
            }
        }

        if(isset($_GET['Do']) && $_GET['Do'] == 'Export') {
            $export = true;
        } else {
            $export = false;
        }

        $zulu->template->config->select_all = true;

		function edit_bt($id,$data) {
			global $zulu,$class_form_post;

			$direct_url = $class_form_post->public_url($data['form'],true);
			if($data['archive']>0) {
				$view_bt = "<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('Archive'=>$id)))."\" class=\"btn btn-default btn-xs\"><i class=\"fas fa-folder-open\"></i> Open</a> <a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('Action'=>'archive_edit','id'=>$id)))."\" class=\"btn btn-default btn-xs\"><i class=\"fas fa-edit\"></i> Edit</a>";
			} else {
				$view_bt = "<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'view')))."\" class=\"btn btn-default btn-xs\"><i class=\"far fa-eye\"></i> View</a> <a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'view','Do'=>'pdf')))."\" class=\"btn btn-default btn-xs\"><i class=\"fas fa-file-pdf\"></i> PDF</a> <a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'view','Do'=>'csv')))."\" class=\"btn btn-default btn-xs\"><i class=\"fas fa-file-excel\"></i> CSV</a> <a target=\"_blank\" href=\"".$direct_url."?Submission=".$data['token']."\" class=\"btn btn-default btn-xs\"><i class=\"fas fa-edit\"></i> Update</a>";
			}

			return "
				".($data['object']!=NULL?"<a href=\"".$zulu->object_link($data['object'],$data['object_id'])."\" class=\"btn btn-default btn-xs\"><i class=\"fas fa-link\"></i> Linked to ".$data['object']."</a>":NULL)."
				".$view_bt."
				<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'delete','Sort'=>$_GET['Sort'])))."\" class=\"btn btn-danger confirm-delete btn-xs\"><i class=\"fas fa-times\"></i> Remove</a>
			";
		}

		$form_edit = new form;

        $table_column[] = array($form_edit->input_html("checkbox","selectall",1,array("class"=>['toggle-input'])),array('class'=>array('')));
		$table_column[] = array("Form Name");
		$table_column[] = array("Reference");
		$table_column[] = array("Added");
		$table_column[] = array("Updated");
		$table_column[] = array("Actions",array('class'=>array('right')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);


		//-- Load forms submitted with a FORM ID
		if($_GET['filter']['form_id']>0) {
			$form_data = $class_form_post->form_data(['id'=>$db->escape_string($_GET['filter']['form_id'])]);
			$zulu->nav->breadcrumb[$form_data['title']] = array();
		}

		//-- Load forms submitted with a FORM SLUG
		if($_GET['filter']['form']!=NULL) {
			$form_data = $class_form_post->form_data(['slug'=>$db->escape_string($_GET['filter']['form'])]);
			$zulu->nav->breadcrumb[$form_data['title']] = array();
		}

		if($_GET['Archive']>0) {
			$_GET['filter']['parent_id'] = $db->escape_string($_GET['Archive']);
		} elseif(isset($_GET['filter'])) {
			//--
		} else {
			$_GET['filter']['parent_id'] = 0;
		}

        if($_GET['Search'] != NULL) {
            $_GET['filter']['search'] = $db->escape_string($_GET['Search']);
        }

        $start = ($_GET['Pg']>1?MAX_per_page*($_GET['Pg']-1):0);
		//-- Load submissions
        $class_form_post->load(['filter'=>$_GET['filter'],'sort'=>'archive DESC, id DESC']);
		$data_row_total = count($class_form_post->data);
		if(!$export) {
            $class_form_post->load(['filter'=>$_GET['filter'],'sort'=>'archive DESC, id DESC','row_start'=>$start,'row_limit'=>MAX_per_page]);
        }
		$data_row = $class_form_post->data;

		foreach($data_row as $row) {
			if($row['object']=='client'&&$row['object_id']>0) {
				$client_data = $class_client->client_data(array('id'=>$row['object_id']));
			}
			$form_data  = new form_post;
			$form_data->load(['id'=>$row['id']]);
			//$form_tpl = $class_form_post->template[$row['form']]['form'];
			if(!isset($class_form_post->template[$row['form']]['form'])) {
				$form_data_tpl = $class_form_post->form_data(['slug'=>$row['form']]);
				$form_arr = $class_form_post->form_build_array($form_data_tpl['id']);

				$FORM = $form_arr['form'];
				$FIELD = $form_arr['field'];
			} else {
				$FORM = $class_form_post->template[$row['form']]['form'];
				$FIELD = $class_form_post->template[$row['form']]['field'];
			}
			$form_tpl = $FORM;

			//-- reference
			if($form_tpl['reference']!=NULL) {
				$newtext = $form_tpl['reference'];
				foreach($form_data->data['meta'] as $key=>$val) {
					$newtext = str_replace("[".$key."]",$val,$newtext);
				}
			}

			//-- archive?
			if($row['archive']>0) {
				$FORM['title'] = "<i class=\"fas fa-folder\"></i> ".stripslashes($row['form_name']);
			}

            if(!$export) {
                $table_row[] = array("content" => array(
                    array($form_edit->input_html("checkbox","action[".$row['id']."]",1,array('checked'=>($_POST['action'][$row['id']]>0?true:false),'class'=>array('action'))),array('class'=>array('action-field'))),
                    array(stripslashes($FORM['title'])),
                    //array(($client_data['id']>0?"<a href=\"".$zulu->link_page('client',array('query'=>array('id'=>$client_data['id'],'Action'=>'edit')))."\">".($client_data['company']!=NULL?$client_data['company']:$client_data['name_first']." ".$client_data['name_last'])."</a>":"-")),
                    array(($newtext!=NULL?$newtext:"-")),
                    array(zulu::time_history($row['form_time'])),
                    array(zulu::time_history($row['form_time_update'])),
                    array(edit_bt($row['id'],$row),array('class'=>array('right')))
                ));
            }

            if($export && $row['archive'] <= 0) {
                if(!isset($export_data[$row['form']])) {
                    $export_data[$row['form']] = ['fields'=>[],'rows'=>[],'name'=>$row['form_name']];
                }
                $export_data[$row['form']]['rows'][] = $form_data->data;
                foreach($form_data->data['meta_raw'] as $meta_row) {
                    if(!in_array($export_data[$row['form']]['fields'][$meta_row['field']])) {
                        $export_data[$row['form']]['fields'][$meta_row['field']] = $meta_row['field_label'];
                    }
                }
            }

		}

        if($export) {
            $export_rows = [];
            foreach($export_data as $form_key=>$form_data) {
                $export_rows[] = [''];
                $export_rows[] = [
                    "Form",
                    $form_data['name'],
                    "Rows",
                    count($form_data['rows']),
                ];
                $export_rows[] = ['Time']+$form_data['fields'];
                foreach($form_data['rows'] as $row_key=>$row_data) {
                    $export_row = [$zulu->dateDecode($row_data['form_time'], 'H:i d/m/Y')];
                    foreach($form_data['fields'] as $field_key=>$field_val) {
                        if(isset($row_data['meta'][$field_key])) {
                            if(!is_array($row_data['meta'][$field_key])) {
                                $export_row[] = $row_data['meta'][$field_key];
                            } else {
                                $export_row[] = implode('; ',$row_data['meta'][$field_key]);
                            }
                        } else {
                            $export_row[] = "";
                        }
                    }
                    $export_rows[] = $export_row;
                }
            }
			$data['body'] = $export_rows;
			$exp = $zulu->export_csv($data,['name'=>"Form Posts",'skip_total'=>true]);
			if($exp['success']) {
				$zulu->notification_set("Export was generated successfully.<br><br>Download here: <a href=\"".$exp['url']."\" target=\"_blank\">".$exp['url']."</a>",1);
			} else {
				$zulu->notification_set("An error occurred.",2);
			}
			header("Location: ".$zulu->link_page(PAGE_file, ['self'=>true, 'filter'=>['Do']]));
			exit;
        }

        $form_edit->categoryOptionForm('0','form_post',['sort_ovr'=>true,'label_field'=>'form_name','sql_where'=>["archive='1'"]]);
        $archive_options = $output;

		$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'basket','data_table'=>false));
        $pagination = $zulu->pagination($_GET['Pg'],['count'=>$data_row_total,'link'=>$zulu->link_page(PAGE_file,array('self'=>true))]);
		$zulu->nav->title = PAGE_name;
	}
	if(PAGE_action=='delete') { //delete
		if($class_form_post->delete(PAGE_id)) {
			$zulu->notification_set("Form post removed successfully.",1);
			header("Location: ".$zulu->link_page(PAGE_file,array('query'=>array('Sort'=>$_GET['Sort']))));
		} else {
			$zulu->notification_set("A database error occurred.",2);
		}
	}
	if(PAGE_action=='view') { //edit page

		$zulu->template->js_file[] = TPL_rel."assets/smart.find.js";
		$form_edit = new form;
		$qry = ['id'=>PAGE_id];
		if($_GET['Timestamp']) {
			$qry['timestamp'] = $_GET['Timestamp'];
		}
		$class_form_post->load($qry);
		$data = $class_form_post->data;
		$direct_url = $class_form_post->public_url($data['form'],true);

		//-- Archive Array
		$form_edit->categoryOptionForm('','form_post',['sort_ovr'=>true,'sql_where'=>['archive = 1'],'label_field'=>'form_name']);
		$archive_array = $output;

		//-- Form tpl
		if(!isset($class_form_post->template[$data['form']]['form'])) {
			$form_data_tpl = $class_form_post->form_data(['slug'=>$data['form']]);
			$form_arr = $class_form_post->form_build_array($form_data_tpl['id']);
			$FORM = $form_arr['form'];
			$FIELD = $form_arr['field'];
		} else {
			$FORM = $class_form_post->template[$data['form']]['form'];
			$FIELD = $class_form_post->template[$data['form']]['field'];
		}
		//--
		$versions = $class_form_post->version_array();

		$zulu->nav->breadcrumb['View'] = array();

		if($data['id']<=0) {
			$zulu->notification_set("This form post does not exist.",2);
			header("Location: ".$zulu->link_page(PAGE_file));
			exit;
		}

		//-- Form INFO
		if($data['object']=='client'&&$data['object_id']>0) {
			$object_field = "Client";
			$object_value = $class_client->admin_link($data['object_id']);
		} elseif($data['object']=='sale'&&$data['object_id']>0) {
			$object_field = "Sale";
			$object_value = $class_sale->admin_link($data['object_id']);
		}

		$table_row[] = array("content" => array(
			array("Date Created"),
			array($zulu->time_history($data['form_time'])),
		));
		$table_row[] = array("content" => array(
			array("Date Updated"),
			array($zulu->time_history($data['form_time_update'])),
		));
		if($object_field!=NULL) {
			$table_row[] = array("content" => array(
				array($object_field),
				array($object_value),
			));
		}
		$zulu->template->body_info = $zulu->table_render($table_row,0,array('js_table'=>false,'class'=>'form_data'));
		unset($table_row);

		//-- Form FIELDS
		$table_column[] = array("Field",array('class'=>array('')));
		$table_column[] = array("Value",array('class'=>array('')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);

		foreach($data['meta'] as $field_key=>$field_val) {

            if($class_form_post->template[$data['form']]['field'][$field_key]['input']['type']=='checkbox') {
                if(is_array($field_val)) {
                    $field_val = implode(', ', $field_val);
                } else {
                    $field_val = ($field_val==1?"Yes":"No");
                }
			}
			$display_var;
			if(is_array($field_val)){
				$display_var = implode(', ',$field_val);

			}else if(filter_var($field_val, FILTER_VALIDATE_URL)){
				$display_var = '<a href="'.$field_val.'" target="_blank">'.$field_val.'</a>';
			}else{
				$display_var = $field_val;
			}
			$table_row[] = array("content" => array(
				array($FIELD[$field_key]['label']),
				array($display_var),
			));
			$csv_row[] = '"'.$FIELD[$field_key]['label'].'","'.$display_var.'"';
			unset($next_renew_class);
		}

		$zulu->template->body_data = $zulu->table_render($table_row,0,array('js_table'=>false,'class'=>'form_data'));

		//-- Form FIELDS
		unset($table_column,$table_row);
		$table_column[] = array("Version",array('class'=>array('')));
		$table_column[] = array("Action",array('class'=>array('')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);

		$i = 0;
		foreach($versions as $field_key=>$field_val) {
			if($_GET['Timestamp']==$field_val||(!isset($_GET['Timestamp'])&&$i==0)) {
				$btn = "<a class=\"btn btn-success btn-sm\" href=\"#\"><i class=\"fas fa-circle\"></i> Open</a>";
			} else {
				$btn = "<a class=\"btn btn-default btn-sm\" href=\"".$zulu->link_page(PAGE_file,['query'=>['Action'=>'view','id'=>PAGE_id,'Timestamp'=>$field_val]])."\"><i class=\"far fa-eye\"></i> View</a>";
			}
			$table_row[] = array("content" => array(
				array($zulu->date($field_val,"dS M Y \a\\t h:ia")),
				array($btn),
			));

			$i++;
			unset($next_renew_class);
		}

		//-- reference
		if($FORM['reference']!=NULL) {
			$newtext = $FORM['reference'];
			foreach($data['meta'] as $key=>$val) {
				$newtext = str_replace("[".$key."]",$val,$newtext);
			}
		}

		//-- body updated
		$zulu->template->body_update = $zulu->table_render($table_row,0,array('js_table'=>false,'class'=>'form_data'));

		//-- DO: CSV
		if($_GET['Do']=='csv') {

			//--headers
			header("Content-type: text/csv");
			header("Content-Disposition: attachment; filename=file.csv");
			header("Pragma: no-cache");
			header("Expires: 0");

			echo $newtext.PHP_EOL;
			echo 'Submitted '.$zulu->date($data['form_time'],'d/m/Y h:ia').PHP_EOL;
			echo '------'.PHP_EOL;
			foreach($csv_row as $cl) {
				echo $cl.PHP_EOL;
			}
			echo '------'.PHP_EOL;
			echo 'Digital forms by Zulu Systems - www.zulusys.nz';
			exit;
		}

		//-- DO: PDF
		if($_GET['Do']=='pdf') {
			$obj_token = $data['token'];
			$setting_data = $class_setting->setting_data(['user_id'=>$data['user_id']]);

			//Compile definitions
			$zulu->template->page_def = [
				'ticket'	=>	true,
				'id'		=>	$id,
				'reference'		=>	$data['id'],
				'type'	=>	'Form Submission',
				'url'	=>	'form_post',
			];
			$logo = ($setting_data['quote_logo_path']!=NULL?$setting_data['quote_logo_path']:NULL);

			// Set some content to print
			$html = "
			<head>
				<link type=\"text/css\" href=\"".MAIN_url."template/template/default/css/pdf.css\" rel=\"stylesheet\" />
				<link type=\"text/css\" href=\"".MAIN_url."template/default/css/pdf.css\" rel=\"stylesheet\" />
			</head>
			<body>

			<div class=\"container\">
				<div class=\"row\">
					<div class=\"document\">
						<div class=\"document-head\">
							<div class=\"coltable col2 vmiddle\">
							<div class='row'>
								<div class=\"col col-left\">
									".($logo?"
									<img class=\"responsive\" src=\"".dirname(__FILE__)."/../../..".$setting_data['quote_logo_path']."\" alt=\"".$setting_data['company']."\" />
									":"
									<h2>".$setting_data['company']."</h2>
									")."
								</div>
								<div class=\"col col-right\">
									<h2 class=\"no-margin\">".$zulu->template->page_def['type']." #".$zulu->template->page_def['reference']."</h2>
									<p class=\"no-margin\">".$quote_reference."</p>
								</div>
							</div></div>
						</div>
						<div class=\"document-body\">
						<h2>".(trim($newtext)!=NULL?$newtext:'Form Submission')."</h2>
						<div class='form-post-data'>".$zulu->template->body_data."</div>
						<p>&nbsp;</p>
						<p>&nbsp;</p>
						<p class=\"text-center\">Digital forms by <b>Zulu Systems</b> - www.zulusys.nz</p>
						</div>
				   </div>
				</div>
			</div>

			</body>
			";
			@mkdir(dirname(__FILE__)."/../../file/form_post/".$obj_token."/");
			$zulu->pdf_create($html,'form_post/'.$obj_token.'/','form');
			exit;
		}

		//-- POST: Save Object
		if($_POST['action']=='save_object') {

			$newconfig = [
				'object'	=>	$_POST['object'],
				'object_id'	=>	$_POST['object_id'],
				'parent_id'	=>	$_POST['parent_id'],
			];

			if($class_form_post->update(PAGE_id,NULL,NULL,[],$newconfig)) {
				$zulu->notification_set("Form was linked to ".$_POST['object'].".",1);
			} else {
				$zulu->notification_set("Form failed to link to ".$_POST['object'].".",2);
			}

			if($_POST['parent_id']!=NULL) {
				$zulu->notification_set("Form was archived.",1);
			}

			header("Location: ".$zulu->link_page(PAGE_file,['self'=>true]));
			exit;
		}

		//-- JS: Link up new object
		$zulu->template->jquery[] = "
		$(document).on('change','.input-linkobj',function() {
			var new_object_val = 'sf_' + $(this).val();
			$('.form-group-object').removeClass('hide');
			$('.sf-input').data('sf',new_object_val);
			console.log($('.sf-input').data('sf'));
		});
		";
	}

	if(PAGE_action=='archive_edit') { //edit page
		$form_edit = new form;

		$zulu->nav->breadcrumb['Forms'] = array();
		if(PAGE_id<1) {
			$new = true;
			$zulu->nav->breadcrumb['New Archive'] = array();
			$zulu->nav->title = "New Archive";
            if(!$_POST) {
                if($_GET['Archive'] > 0) {
                    $_POST['parent_id'] = $_GET['Archive'];
                }
            }
		} else {
			$id = ($_POST['id']>0?$_POST['id']:($_GET['id']>0?$_GET['id']:0));
			$form_edit_qry = ['id'=>$id];
			if($class_user->authorised->role=='admin') {
				$form_edit_qry['ovr_user_id'] = true;
			}
			$data_row = $class_form_post->form_post_data($form_edit_qry);

			if(!$_POST) {
				foreach($data_row as $key=>$val) {
					$_POST[$key] = stripslashes($val);
				}
			}
			$zulu->nav->breadcrumb['Edit Archive'] = array();
			$zulu->nav->breadcrumb[stripslashes($data_row['form_name'])] = array();
			$zulu->nav->title = "Edit Archive";
		}

		//Form Submit
		if($_POST['action']=='edit') {
			$form_edit->valid = true;

			if($form_edit->valid) {

				$data = [
					'archive'		=>		1,
					'parent_id'		=>		$db->escape_string($_POST['parent_id']),
					'form_name'		=>		$db->escape_string($_POST['form_name']),
					'form_description'	=>	$db->escape_string($_POST['form_description']),
				];
				if($class_user->authorised->role=='admin') {
					$data['user_id'] = $db->escape_string($_POST['user_id']);
				}
				$data = $class_form_post->form_post_edit($id,$data);

				if($data['success']) {
					$id = $data['id'];
					if(count($_POST['meta'])>0) {
						foreach($_POST['meta'] as $key=>$val) {
							$zulu->meta_update("form_post",$id,$key,$val);
						}
					}

					$zulu->notification_set("Archive ".(!$new?"updated":"created")." successfully.",1);
					header("Location: ".$zulu->link_page(PAGE_file));
					exit;
				} else {
					$zulu->notification_set("A database error occurred.",2);
				}
			}
		}

        $form_edit->categoryOptionForm('','form_post',['sort_ovr'=>true,'label_field'=>'form_name','sql_where'=>["archive='1'"]]);
        $cat_options = $output;
	}

	if(PAGE_action=='submit') {

		$form_edit = new form;
		$zulu->template->css_file[] = TPL_rel."css/document.css";
		$TPL_body_ovr = 'body-print.php';
		$zulu->template->page_def['title'] = "Form Submission";

		if(isset($_GET['FormShortToken'])) { //-if short token supplied, redir to proper URL
			$form_token = $db->escape_string($_GET['FormShortToken']);
			$form_data = $class_form_post->form_data(['token_short'=>$form_token,'ovr_user_id'=>true]);
			$class_user->authorised->id = $form_data['user_id'];
			$user_data = $class_user->user_data(['id'=>$form_data['user_id']]);
			$class_user->authorised->token = $user_data['token'];
			$class_setting->construct(['user_id'=>$form_data['user_id']]);
			header("Location: ".$class_form_post->public_url($form_data['slug'],true));
			exit;
		} else {
			$slug = $db->escape_string($_GET['Slug']);
			$user_token = $db->escape_string($_GET['User']);
			$user_data = $class_user->user_data(['token'=>$user_token]);
			$user_id = $user_data['id'];
		}

		$class_setting->construct(['user_id'=>$user_id,'cache_clear'=>true]);

		$form = new form_post(['slug'=>$slug,'user_id'=>$user_id]);
		$form_id = $zulu->vars->form_post->form_id;

        $form_row = $class_form_post->form_data(['id'=>$form_id]);
        $form_meta = $zulu->meta_array($class_form_post->form_meta($form_id));
        if($form_meta['logo'] != NULL && file_exists($class_form_post->form_folder_path.$user_id."/".$form_meta['logo'])) {
            $class_setting->data['quote_logo_path'] = $class_form_post->form_folder_rel.$user_id."/".$form_meta['logo'];
        }
		if($_GET['Submission']!=NULL) {
			$submit_token = $db->escape_string($_GET['Submission']);
			$form->load(['token'=>$submit_token,'set_post'=>true,'ovr_user_id'=>true]);
			$zulu->config->form_post_token = $submit_token;
		}
		if($form_id<=0) {
			$zulu->notification_set("This form does not exist.",2);
		}
		if($form_meta['password']!=NULL) {
			$password_has = true;
			if(!$class_cache->exists('form_post_'.$form_data['id'])) {
				$password_auth = false;

				if($_POST['action']=='auth') {
					if(strlen(trim($_POST['password']))<=0) {
						$zulu->notification_set("The password field is required.",2);
					} elseif($form_meta['password']!=$db->escape_string($_POST['password'])) {
						$zulu->notification_set("The password does not match, please try again.",2);
					} else {
						$class_cache->save('form_post_'.$form_data['id'],true);
						$password_auth = true;
					}
				}
			} else {
				$password_auth = true;
			}

		}

		//-- Autosave?
		if($form_meta['auto_save']>0) {
			$zulu->template->page_def['title_sub_html'] = "<span class=\"opt opt-grey\"><i class='fas fa-save'></i> <span id='autosave_label'>Auto-save enabled</span></span>";
			$zulu->template->jquery[] = "
			setInterval(auto_save,10000);
			";
			$zulu->template->js_code[] = "
			function auto_save() {
				$.ajax({
				  type: 'POST',
				  url: '".$zulu->link_page(PAGE_file,['self'=>true,'query'=>['Do'=>'autosave']])."',
				  data: $('#form').serialize(),
				  success: function(output_val) {
				  	console.log('Auto Save Exe');
				  	console.log('Response: ' + output_val);
					var date = new Date();
					var hours = date.getHours();
					var minutes = date.getMinutes();
					var seconds = date.getSeconds();
					var time = hours + ':' + minutes + ':' + seconds;
					$('#autosave_label').html(\"Auto-saved at \" + time);
					//setInterval(auto_save_cd,1000);
				  }
				});
			}
			function auto_save_cd() {
				var new_v = cd_v+1;
				$('#autosave_cd').html('yo');
			}
			";
		}

		//-- Auto Save Form
		if($_POST['action']=='form_post_submit'&&$_GET['Do']=='autosave') {
			$class_cache->dump('form_post_autosave_'.$form_id);
			$class_cache->save('form_post_autosave_'.$form_id,serialize($_POST));
			echo 'Save: OK';
			exit;
		}

		//-- Submit Form
		if($_POST['action']=='form_post_submit') {
			$form->config->user_token = $db->escape_string($_GET['User']);
			$form->form_process();
		}

		//-- Build Form
		if($form_id>0&&(!$password_has||($password_has&&$password_auth))) {
			$zulu->template->body = $class_website->form_build();
		} elseif($password_has&&!$password_auth) {
			$zulu->template->body = "
			<form role=\"form\" action=\"\" method=\"post\">
				<div class=\"panel panel-warning\">
					<div class=\"panel-heading\"><span class=\"fas fa-lock\"></span> This form is password protected...</div>
					<div class=\"panel-body\">
						<div class=\"form-group\">
							<label>Please enter the password required to access this form:</label>
							".$form_edit->input_html("password","password",$_POST['password'])."
						</div>
					</div>
				</div>
				".$form_edit->input_html("submit","submit",'Continue',['class'=>['btn','btn-success']])."
				".$form_edit->input_html("hidden","action",'auth')."

				</form>
			";
		}
	}
	if(PAGE_action=='form_duplicate') {
		if($class_form_post->form_duplicate(PAGE_id)) {
			$zulu->notification_set("Form duplicated successfully.",1);
			header("Location: ".$zulu->link_page(PAGE_file,['self'=>true,'query'=>['Action'=>'form']]));
			exit;
		} else {
			$zulu->notification_set("A database error occurred.",2);
		}
	}
	if(PAGE_action=='form') {
		function edit_bt($id,$data) {
			global $zulu,$class_form_post;

			return "
				<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('Action'=>'form_field','Form'=>$id)))."\" class=\"btn btn-info btn-xs\"><i class=\"fas fa-bars\"></i> Fields</a>
				<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'form_duplicate')))."\" class=\"btn btn-warning btn-xs\"><i class=\"fas fa-clone\"></i> Duplicate</a>
				<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'form_edit')))."\" class=\"btn btn-primary btn-xs\"><i class=\"fas fa-edit\"></i> Edit</a>
				<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'form_delete','Sort'=>$_GET['Sort'])))."\" class=\"confirm-delete btn btn-danger btn-xs\"><i class=\"fas fa-times\"></i></a>
			";
		}
        function edit_bt_cat($id,$data) {
			global $zulu,$class_form_post;

			return "
				<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'category_edit')))."\" class=\"btn btn-primary btn-xs\"><i class=\"fas fa-edit\"></i> Edit</a>
				<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'category_delete','Sort'=>$_GET['Sort'])))."\" class=\"confirm-delete btn btn-danger btn-xs\"><i class=\"fas fa-times\"></i></a>
			";
		}

		$form_edit = new form;

        $table_column[] = array("Name",array('class'=>array('')));
		$table_column[] = array("Embed Code",array('class'=>array('')));
		$table_column[] = array("Submissions",array('class'=>array('')));
		$table_column[] = array("Added",array('class'=>array('')));
		$table_column[] = array("Updated",array('class'=>array('')));
		$table_column[] = array("Actions",array('class'=>array('right')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);

        $config = ['parent_id'=>'0'];
        if($_GET['Root'] > 0) {
            $config['parent_id'] = $db->escape_string($_GET['Root']);
        }

        $data_row = $class_form_post->category_data($config);
		foreach($data_row as $row) {

			$table_row[] = array("content" => array(
				array("<i class='fas fa-folder'></i> <a href='".$zulu->link_page(PAGE_file,['query'=>['Action'=>'form','Root'=>$row['id']]])."'>".stripslashes($row['title'])."</a>"),
				array(""),
				array(""),
				array($zulu->time_history($row['stat_add'])),
				array($zulu->time_history($row['stat_update'])),
				array(edit_bt_cat($row['id'],$row),array('class'=>array('right')))
			));
		}

		$data_row = $class_form_post->form_data($config);
		foreach($data_row as $row) {
			$this_form = new form_post;
			$this_form->load(['form'=>$row['slug']]);
			$post_count = count($this_form->data);
			unset($this_form);

			$table_row[] = array("content" => array(
				array(stripslashes($row['title'])),
				array("[form-".$zulu->slug($row['title'])."]"),
				array("<a href='".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'filter'=>['form'=>$row['slug']])))."'>".$post_count."</a>"),
				array($zulu->time_history($row['stat_add'])),
				array($zulu->time_history($row['stat_update'])),
				array(edit_bt($row['id'],$row),array('class'=>array('right')))
			));
		}

		$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'basket'));
		$zulu->nav->title = 'Forms';
		$zulu->nav->breadcrumb['Forms'] = array();
	}

	if(PAGE_action=='form_edit') { //edit page
		$form_edit = new form;

		$zulu->nav->breadcrumb['Forms'] = array();
		if(PAGE_id<1) {
			$new = true;
			$zulu->nav->breadcrumb['New Form'] = array();
			$zulu->nav->title = "New Form";

            if(!$_POST) {
                if($_GET['Root'] != NULL) {
                    $_POST['parent_id'] = $_GET['Root'];
                }
            }
		} else {
			$id = ($_POST['id']>0?$_POST['id']:($_GET['id']>0?$_GET['id']:0));
			$form_edit_qry = ['id'=>$id];
			if($class_user->authorised->role=='admin') {
				$form_edit_qry['ovr_user_id'] = true;
			}
			$data_row = $class_form_post->form_data($form_edit_qry);
			$data_meta = $zulu->meta_array($class_form_post->form_meta($id));

			if(!$_POST) {
				foreach($data_row as $key=>$val) {
					$_POST[$key] = stripslashes($val);
				}
				foreach($data_meta as $key=>$val) {
					$_POST['meta'][$key] = stripslashes($val);
				}
			}
			$zulu->nav->breadcrumb['Edit Form'] = array();
			$zulu->nav->breadcrumb[stripslashes($data_row['title'])] = array();
			$zulu->nav->title = "Edit Form";

            $table_column[] = array("Field Name",array('class'=>array('')));
            $table_column[] = array("Reference Code",array('class'=>array('')));
            $table_row[] = array(
                    "header"	=>	 true,
                    "class"		=>	"",
                    "content"	=>	$table_column);
            $field_data = $class_form_post->form_field_data(['form_id'=>$id]);
            foreach($field_data as $field_row) {
                $table_row[] = array("content" => array(
                    array(stripslashes($field_row['name'])),
                    array("<span id='slug-".$field_row['id']."' class='quick-select'>[".$zulu->slug($field_row['slug'])."]</span>")
                ));
            }
            $zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'reference-table','data_table'=>false));
		}

        if($_GET['Method']=='DeleteLogo') {
            $class_file->file_delete_raw($_POST['meta']['logo'],'../form/'.$class_user->authorised->id.'/');
            $zulu->meta_update("form",$id,'logo','');
            $zulu->notification_set("Logo file removed.",1);
            header("Location: ".$zulu->link_page(PAGE_file,['query'=>['Action'=>PAGE_action,'id'=>PAGE_id]]));
            exit;
        }

        //Form Submit
		if($_POST['action']=='edit') {
			$form_edit->valid = true;

			if($form_edit->valid) {

				$data = [
					'title'			=>	$db->escape_string($_POST['title']),
					'description'	=>	$db->escape_string($_POST['description']),
					'reference'	=>	$db->escape_string($_POST['reference']),
                    'parent_id'     =>  $_POST['parent_id'],
                    'email'     =>  $_POST['email']
				];
				if($class_user->authorised->role=='admin') {
					$data['user_id'] = $db->escape_string($_POST['user_id']);
				}
				$data = $class_form_post->form_edit($id,$data);

				if($data['success']) {
					$id = $data['id'];
					if(count($_POST['meta'])>0) {
						foreach($_POST['meta'] as $key=>$val) {
							$zulu->meta_update("form",$id,$key,$val);
						}
					}

                    if($_FILES['image']['tmp_name']!=NULL) {
                        $extension = $class_file->extension($_FILES['image']['name']);
                        $filename = $zulu->slug(str_replace('.'.$extension,'',$_FILES['image']['name']))."-".time();
						$logo_upload = $class_file->file_upload_raw('image',$filename,'../form/'.$class_user->authorised->id.'/');
						if($logo_upload['success']) {
                            $zulu->meta_update("form",$id,'logo',$logo_upload['name']);
						}
					}

					$zulu->notification_set("Form ".(!$new?"updated":"created")." successfully.",1);
					header("Location: ".$zulu->link_page(PAGE_file,['query'=>['Action'=>'form','Root'=>$_POST['parent_id']]]));
					exit;
				} else {
					$zulu->notification_set("A database error occurred.",2);
				}
			}
		}

        $form_edit->categoryOptionForm('','form_category',['sort_ovr'=>true,'label_field'=>'title']);
        $cat_options = $output;

        $output = [];
        $form_edit->categoryOptionForm('','form_post',['sort_ovr'=>true,'label_field'=>'form_name','sql_where'=>["archive='1'"]]);
        $archive_options = $output;

        $zulu->template->js_code[] = "
            $('#reference-help').click(function() {
                $('input[name=\"reference\"]').focus();
                return false;
            });

        ";
	}

	if(PAGE_action=='form_field') {

		if($_GET['Do'] == 'FieldSort') {
			$array = explode(",",$_GET['Array']);
			$i = 0;
			foreach($array as $item_id) {
				$class_form_post->form_field_edit($item_id,['sort'=>$i]);
				$i++;
			}
			echo 'DONE';exit;
		}

		function edit_bt($id) {
			global $zulu;
			return "
				<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'form_field_edit')))."\"><button class=\"btn btn-primary btn-sm\" type=\"button\"><i class=\"fas fa-edit\"></i> Edit</button></a>
				<a class=\"confirm-delete\" href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'form_field_delete','Form'=>$_GET['Form'])))."\"><button class=\"btn btn-danger btn-sm\" type=\"button\"><i class=\"fas fa-times\"></i> Remove</button></a>
			";
		}

		$form_edit = new form;

		$table_column[] = array("Field",array('class'=>array('')));
		$table_column[] = array("Type",array('class'=>array('')));
		$table_column[] = array("Width",array('class'=>array('')));
		$table_column[] = array("Required",array('class'=>array('')));
		$table_column[] = array("Added",array('class'=>array('')));
		$table_column[] = array("Updated",array('class'=>array('')));
		$table_column[] = array("Actions",array('class'=>array('right')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);

		$data_row = $class_form_post->form_field_data(['form_id'=>$_GET['Form']]);
		foreach($data_row as $row) {

			$table_row[] = array("content" => array(
				array(stripslashes($row['name'])),
				array($class_form_post->config->field_types[$row['input']]),
				array($class_form_post->config->field_widths[$row['width']]),
				array(($row['required']?"<span class='opt opt-success'><span class='fas fa-check'></span>".($row['required_all']>0?" All":NULL)."</span>":"<span class='opt opt-danger'><span class='fas fa-times'></span></span>"),array('class'=>array('center'))),
				array($zulu->time_history($row['stat_add'])),
				array($zulu->time_history($row['stat_update'])),
				array(edit_bt($row['id']),['class'=>['right']])
			),'class'=>'',"data"=>['field-id'=>$row['id']]);
		}

		$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'','data_table'=>false,'tbody'=>['id'=>'sortable-rows']));
		$zulu->nav->title = 'Form Fields';
		$zulu->nav->breadcrumb['Forms'] = array('link'=>$zulu->link_page(PAGE_file,['query'=>['Action'=>'form']]));
		$zulu->nav->breadcrumb['Form Fields'] = array();

		$zulu->template->js_code[] = "
		$(document).ready(function() {
			$(\"#sortable-rows\").sortable({
				update: function(event, ui) {
					var srt = [];
					$(\"#sortable-rows\").children(\"tr\").each(function( index ) {
						srt.push($(this).data('field-id'));
					});
					$.get(\"".MAIN_rel."admin/index.php?Page=form_post&Action=form_field&Form=".$_GET['Form']."&Do=FieldSort&Array=\" + srt,function(data) {
						console.log(data);
					});
				}
			});
		});
		";
	}

	if(PAGE_action=='form_field_edit') { //edit page
		$form_edit = new form;

		$zulu->nav->breadcrumb['Forms'] = array();
		if(PAGE_id<1) {
			$new = true;
			$zulu->nav->breadcrumb['New Field'] = array();
			$zulu->nav->title = "New Field";
			if(!$_POST) {
				$_POST['form_id'] = $_GET['Form'];
				$_POST['config']['option'] = [0=>''];
			}
		} else {
			$id = ($_POST['id']>0?$_POST['id']:($_GET['id']>0?$_GET['id']:0));
			$data_row = $class_form_post->form_field_data(array('id'=>$id));

			if(!$_POST) {
				foreach($data_row as $key=>$val) {
					$_POST[$key] = stripslashes($val);
				}
				$_POST['config'] = unserialize($data_row['config']);
			}
			$zulu->nav->breadcrumb['Edit Field'] = array();
			$zulu->nav->breadcrumb[stripslashes($data_row['name'])] = array();
			$zulu->nav->title = "Edit Field";
		}

		//Field Array
		$data_row = $class_form_post->form_field_data(['form_id'=>$_POST['form_id']]);
		foreach($data_row as $row) {
			if($row['id']==$id) {
				continue;
			}
			$arr_field[$row['id']] = stripslashes($row['name']);
		}

		//Form Submit
		if($_POST['action']=='edit') {
			$form_edit->valid = true;

			//-- Office only cant be required field
			if($_POST['required']>0&&$_POST['config']['office']>0) {
				$form_edit->valid = false;
				$zulu->notification_set("Office only fields cannot be 'required' fields.",2);
			}

			if($form_edit->valid) {

				$option_arr = [];
				foreach($_POST['config']['option'] as $key=>$val) {
					$option_arr[$val] = $val;
				}
				$_POST['config']['option'] = $option_arr;

				if($_POST['input'] == 'number') {
					if($_POST['config']['custom']['min']==NULL) {
						unset($_POST['config']['custom']['min']);
					}
					if($_POST['config']['custom']['max']==NULL) {
						unset($_POST['config']['custom']['max']);
					}
				} else {
					unset($_POST['config']['custom']['min'],$_POST['config']['custom']['max'],$_POST['config']['custom']['step']);
				}
				if($_POST['config']['custom']['multiple']==0) {
					unset($_POST['config']['custom']['multiple']);
				}

				$data = [
					'name'			=>	addslashes($_POST['name']),
					'input'			=>	$_POST['input'],
					'width'			=>	$_POST['width'],
					'required'		=>	$_POST['required'],
					'required_all'	=>	$_POST['required_all'],
					'form_id'		=>	$_POST['form_id'],
					'description'	=>	addslashes($_POST['description']),
					'config'		=>	serialize($_POST['config']),
				];
				$data = $class_form_post->form_field_edit($id,$data);

				if($data['success']) {
					$zulu->notification_set("Form ".(!$new?"updated":"created")." successfully.",1);
					header("Location: ".$zulu->link_page(PAGE_file,['query'=>['Action'=>'form_field','Form'=>$_POST['form_id']]]));
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
				array($form_edit->input_html('input','config[option][]',stripslashes($val))),
				array("<a href=\"#\" class=\"btn btn-default btn-xs clear-row\" title='Clear row'><i class=\"fas fa-eraser\"></i></a> <a href=\"#\" class=\"btn btn-danger btn-xs remove-row\" title='Remove row'><i class=\"fas fa-times\"></i></a>",array('class'=>array('right','w80')))
			]);
		}
		$zulu->template->option_table = $zulu->table_render($table_row,0,array('class'=>'','data_table'=>false,'html_id'=>'select-options-table','tbody'=>['id'=>'sortable-rows']));

		$zulu->template->js_code[] = "
		$(document).ready(function() {
			$('#input-settings-checkbox').hide();

			$('select[name=\"input\"]').change(function() {
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

				if($(this).val() == 'select') $('#select-settings').slideDown(300);
				else if($('#select-settings').is(':visible')) $('#select-settings').slideUp(300);
			});
			$('select[name=\"input\"]').trigger('change');

			$(\"#sortable-rows\").sortable();
			$(\"body\").on('click','#row-add',function() {
				$(\"#select-options-table\").append('<tr><td>".$form_edit->input_html('input','config[option][]')."</td><td class=\"right w80\"><a href=\"#\" class=\"btn btn-default btn-xs clear-row\" title=\"Clear row\"><i class=\"fas fa-eraser\"></i></a> <a href=\"#\" class=\"btn btn-danger btn-xs remove-row\" title=\"Remove row\"><i class=\"fas fa-times\"></i></a></td></tr>');
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
		});
		";
	}

	if(PAGE_action=='form_delete') { //delete
		if($class_form_post->form_delete(PAGE_id)) {
			$zulu->notification_set("Form removed successfully.",1);
			header("Location: ".$zulu->link_page(PAGE_file,array('query'=>array('Action'=>'form'))));
			exit;
		} else {
			$zulu->notification_set("A database error occurred.",2);
		}
	}

	if(PAGE_action=='form_field_delete') { //delete
		if($class_form_post->form_field_delete(PAGE_id)) {
			$zulu->notification_set("Field removed successfully.",1);
			header("Location: ".$zulu->link_page(PAGE_file,array('query'=>array('Action'=>'form_field','Form'=>$_GET['Form']))));
			exit;
		} else {
			$zulu->notification_set("A database error occurred.",2);
		}
	}

    if(PAGE_action=='category_edit') { //edit page
		$form_edit = new form;

		$zulu->nav->breadcrumb = array('Forms'=>['link'=>$zulu->link_page(PAGE_file,['query'=>['Action'=>'form']])]);
		if(PAGE_id<1) {
			$new = true;
			$zulu->nav->breadcrumb['New Category'] = array();
			$zulu->nav->title = "New Category";

            if(!$_POST) {
                if($_GET['Root'] != NULL) {
                    $_POST['parent_id'] = $_GET['Root'];
                }
            }
		} else {
			$id = ($_POST['id']>0?$_POST['id']:($_GET['id']>0?$_GET['id']:0));
			$form_edit_qry = ['id'=>$id];
			if($class_user->authorised->role=='admin') {
				$form_edit_qry['ovr_user_id'] = true;
			}
			$data_row = $class_form_post->category_data($form_edit_qry);

			if(!$_POST) {
				foreach($data_row as $key=>$val) {
					$_POST[$key] = stripslashes($val);
				}
			}
			$zulu->nav->breadcrumb['Edit Category'] = array();
			$zulu->nav->breadcrumb[$_POST['title']] = array();
			$zulu->nav->title = "Edit Category";
		}

		//Form Submit
		if($_POST['action']=='edit') {
			$form_edit->valid = true;

			if($form_edit->valid) {

				$data = [
					'title'			=>	$db->escape_string($_POST['title']),
					'parent_id'	=>	$db->escape_string($_POST['parent_id']),
				];
				$data = $class_form_post->category_edit($id,$data);

				if($data['success']) {
					$id = $data['id'];

					$zulu->notification_set("Form Category ".(!$new?"updated":"created")." successfully.",1);
					header("Location: ".$zulu->link_page(PAGE_file,['query'=>['Action'=>'form','Root'=>$_POST['parent_id']]]));
					exit;
				} else {
					$zulu->notification_set("A database error occurred.",2);
				}
			}
		}

        $form_edit->categoryOptionForm('','form_category',['sort_ovr'=>true,'label_field'=>'title']);
        $cat_options = $output;
	}

    if(PAGE_action=='category_delete') { //delete
		if($class_form_post->category_delete(PAGE_id)) {
			$zulu->notification_set("Form Category removed successfully.",1);
			header("Location: ".$zulu->link_page(PAGE_file,array('query'=>array('Action'=>'form'))));
			exit;
		} else {
			$zulu->notification_set("A database error occurred.",2);
		}
	}

}
