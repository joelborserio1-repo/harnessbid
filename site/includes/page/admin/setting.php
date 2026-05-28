<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- DEFINITIONS
define(PAGE_file,'setting');
define(PAGE_name,'System Settings');
$zulu->nav->breadcrumb[PAGE_name] = array("link"=>$zulu->link_page(PAGE_file));

//-- AUTHORISED?
$class_user->user_authorised_check();

//-- ADMIN MASTER SECTION
if(MASTER_section=='admin') { //admin section

	$zulu->template->head = "";
	$zulu->template->body = "";
	
	$tab_list = [
		"general"	=>	"General",
		"design"	=>	"Design & Feel",
		"xero"		=>	"Xero",
		"custom_fields"	=>	"Custom Fields",
		"ip_ban"	=>	"IP Banning",
		"misc"		=>	"Miscellaneous"
	];
	
	if(PAGE_action==NULL) { //edit page=
		$form_edit = new form;
		$data_row = $class_setting->setting_data();
		$zulu->nav->title = PAGE_name;
		$user_data = $class_user->user_data(array('id'=>$class_user->authorised->id));
		$user_meta = $class_user->user_meta($class_user->authorised->id);
		$selected_tab = ($_GET['Tab']?$_GET['Tab']:($class_cache->load('setting_tab')!=NULL?$class_cache->load('setting_tab'):"general"));
		
		if(!isset($_GET['Tab'])) {
			$_GET['Tab'] = $selected_tab;
		}
		$class_cache->save('setting_tab',$selected_tab);

		if(!$_POST) {
			foreach($data_row as $key=>$val) {
				$_POST[$key] = $val;
                
                if($key == 'smtp_password' && $val) {
                    $_POST[$key] = $zulu->stringDecrypt($val);
                }
			}
		}
		$currency_options = [];
		foreach($class_setting->defaults->currency_code as $key=>$code_attributes){
			$currency_options[$key] = $code_attributes['label'];
		}
		
		$file_ext = pathinfo($_POST['image'], PATHINFO_EXTENSION);
        
		if($_GET['Method']=='DeleteImage'&&isset($_GET['Image'])) {
			$class_setting->image_delete($db->escape_string($_GET['Image']));
			$zulu->notification_set("'".$_GET['Image']."' was removed.",1);
			header("Location: ".$zulu->link_page(PAGE_file));
			exit;
		}
		if($_GET['Method']=='DeleteTerms') {
			$class_file->file_delete_raw($_POST['quote_terms_file'],'../user/'.$class_user->authorised->id.'/');
			$class_setting->delete('quote_terms_file','field');
			$zulu->notification_set("Terms and conditions file removed.",1);
			header("Location: ".$zulu->link_page(PAGE_file));
			exit;
		}
		if($_GET['Method']=='DeleteQuoteLogo') {
			$class_file->file_delete_raw($_POST['quote_logo'],'../user/'.$class_user->authorised->id.'/');
			$class_setting->delete('quote_logo','field');
			$zulu->notification_set("Quote logo removed.",1);
			header("Location: ".$zulu->link_page(PAGE_file));
			exit;
		}
		if($_GET['Method']=='mc_sync') { //-- sync a list with mailchimp
			$list_id = $class_setting->data['mc_list_2'];
			$api_key = $class_setting->data['mc_api_key'];
			
			if(trim($api_key)==NULL) {
				$zulu->notification_set("Please enter an API key for your MailChimp account.",2);
				header("Location: ".$zulu->link_page(PAGE_file,['self'=>true,'filter'=>['Method']]));
				exit;
			}
			
			//-- Load MC
			require_once(dirname(__FILE__)."/../../plugin/mailchimp/src/Mailchimp.php");
			$mailChimp = new Mailchimp($api_key);
			
			if(isset($_GET['list'])) {
				
				//-- Check List 
				if(trim($list_id)==NULL) {
					$zulu->notification_set("This list has no MailChimp unique ID specified, please enter one first then click 'Sync Now'.",2);
					header("Location: ".$zulu->link_page(PAGE_file,['self'=>true,'filter'=>['Method']]));
					exit;
				}
				
				$filter['type'] = $db->escape_string($_GET['list']);
			} else {
				$filter['type'] = [0,1,2,3];
			}
			
			//-- Loops
			$client_data = $class_client->client_data($filter);
			foreach($client_data as $client_row) {
				if($client_row['email'] != NULL) { 
					try {
						$result = $mailChimp->call("lists/subscribe", array(
							"id"				=>	$list_id,
							"email"				=>	array("email"=>$client_row['email']),
							"update_existing"	=>	true, //-- was TRUE
							"send_welcome"		=>	false,
							"double_optin"		=>	false,
							"merge_vars"		=>	array('FNAME'=>stripslashes($client_row['name_first']),'LNAME'=>stripslashes($client_row['name_last']),'COMPANY'=>stripslashes($client_row['company']),'RATE'=>$client_row['hourly_rate'],'TOKEN'=>$client_row['token'],'TYPE'=>$class_client->config->type[$client_row['type']]),
							));
					} catch (Exception $e) {
						$exception_log[] = $e->getMessage();
					}
				}
			}
			
			//-- Check exception
			if(count($exception_log)>0) {
				$zulu->notification_set("The update was completed - however some errors occured:<br><br>".implode("<BR>",$exception_log),2);
				header("Location: ".$zulu->link_page(PAGE_file,['self'=>true,'filter'=>['Method']]));
				exit;
			}
			
			//-- Go to OK page
			$zulu->notification_set("The list was synced successfully.",1);
			header("Location: ".$zulu->link_page(PAGE_file,['self'=>true,'filter'=>['Method']]));
			exit;
		}
		if($_GET['Tab']=='xero') { //-- tab general
			if($_GET['Do']=='auth') {
				$class_xero->connect();	
			}
			if($_GET['Do']=='xero_wipe') {
				$class_xero->disconnect();
				$zulu->notification_set("You deauthorised access to Xero.",1);
				header("Location: ".$zulu->link_page(PAGE_file,['self'=>true,'filter'=>['Do']]));
				exit;
			}
			if($_GET['Do']=='xero_stock_pull') {
				
				$user_setting = $class_setting->setting_data(['user_id'=>USER_id,'key_start'=>'xero']);
				if($user_setting['xero_consumer_key']!=NULL) {
					$class_setting->data = $user_setting;

					$xero = new xero;
					$xero->connect();
					$response = $xero->get_inventory();
					$item_array = (array)$response['raw']->Items;
					foreach($item_array['Item'] as $item) {
						if($item->IsTrackedAsInventory) {
							$product_lookup = $class_product->product_data(['user_id'=>$user['id'],'sku'=>$item->Code]);
							if($product_lookup['id']>0) {
								$product_stock = $class_product->stock($product_lookup['id']);
								$qoh_val = sprintf('%0.2f', $item->QuantityOnHand);
								$new_level = abs($product_stock['level'] - $qoh_val);
								if($product_stock['level']>$qoh_val) {
									$new_level = $new_level*-1;
								}
								$log[] = "-Product: ".$product_lookup['name']." - ZULU: ".$product_stock['level']." XERO: ".$qoh_val."\n";
								if($new_level!=0) {
									$result = $class_product->stock_adjust($product_lookup['id'],
										[
											'value'			=>	$new_level,
											'note'			=>	'Xero Imported',
										]
									);	
									$log[] = "-- Adjust by ".$new_level."\n";
								} else {
									$log[] = "-- No adjust (".$new_level.")\n";
								}
							}
						}
					}
				}

				$zulu->notification_set("Stock was adjusted using Xero.<br><br>Information:<br>".implode("<br>",$log),1);
				header("Location: ".$zulu->link_page(PAGE_file,['self'=>true,'filter'=>['Do']]));
				exit;
			}
			
			//-- Get tracking categories if connected
			if($class_xero->vars->enabled) {
				
				//-- Get Tracking Categories
				$track_cat = $class_xero->get_tracking_categories();
				if(count($track_cat['tracking_categories'])>0) {
					$track_cat = $track_cat['tracking_categories'];
					$track_cat_has = true;
				}
				
				//-- Get Tracking Branding
				$brand_cat = $class_xero->get_branding();
				if(count($brand_cat['branding'])>0) {
					$brand_cat = $brand_cat['branding'];
					$brand_has = true;
				}
			}
		}
        
		if($_GET['Tab']=='custom_fields') {
			$table_column = [
				//array("Field",array('class'=>array('center'))),
				array("Input Name",array('class'=>array(''))),
				//array("Weight",array('class'=>array(''))),
				//array("Volume",array('class'=>array(''))),
				array("Actions",array('class'=>array('right')))
			];
			$table_row[] = ["header" => true, "class" => "", "content" => $table_column];

			if($_POST['action']!='edit') {
				$_POST['custom_field'] = unserialize(stripslashes($_POST['custom_field']));
			}
			$count = count($_POST['custom_field']['contact']) / 1;
			for($i=1; $i<=$count; $i++) {
				$table_row[] = array("content" => [
					//array("<span class='order'>".$i."</span>",array('class'=>array('center'))),
					//array("<div class='input-group'><span class='input-group-addon'>$</span>".$form_edit->input_html('input','option[price][]',sprintf("%.2f", $_POST['meta'][''.$i.'_price']))."</div>"),
					array($form_edit->input_html('input','custom_field[contact]['.$i.']',$_POST['custom_field']['contact'][$i])),
					//array("<div class='input-group'>".$form_edit->input_html('input','option[volume][]',$_POST['meta'][''.$i.'_volume'])."<span class='input-group-addon'>m<sup>3</sup></span></div>"),
					array("<a href=\"#\" class=\"btn btn-default btn-xs clear-row\" title='Clear row'><i class=\"fas fa-eraser\"></i></a> <a href=\"#\" class=\"btn btn-danger btn-xs remove-row\" title='Remove row'><i class=\"fas fa-times\"></i></a>",array('class'=>array('right','w80')))
				]);
			}
			$custom_field_contact = "
			<div id='row-container'>".$zulu->table_render($table_row,0,array('class'=>'module-options table-contact','js_table'=>false,'data_table'=>false,'html_id'=>'','tbody'=>['id'=>'sortable-rows']))."</div>
			<div class=\"row\">
				<p class='text-center'><button data-target='table-contact' type='button' id='' class='row-add btn btn-primary btn-xs' title='Add another field'><span class='fas fa-plus-circle'></span> Add another field</button></p>
			</div>";
			$zulu->template->js_code[] = "
			$(document).ready(function() {
				/*$(\"#sortable-rows\").sortable( {
					update: function(event, ui) {
						renumber();
					}
				});*/
				$(\"body\").on('click','.row-add',function() {
					$(\".\" + $(this).data('target')).append('<tr><td>".$form_edit->input_html('input','custom_field[contact][]')."</td><td class=\"right w80\"><a href=\"#\" class=\"btn btn-danger btn-xs remove-row\" title=\"Remove row\"><i class=\"fas fa-times\"></i></a></td></tr>');
					renumber();
				});
				$('body').on('click','.clear-row',function() {
					var trow = $(this).parent().parent();
					$(trow).find('input').val('');
					return false;
				});
				$('body').on('click','.remove-row',function() {
					var trow = $(this).parent().parent();
					$(trow).remove();
					renumber();
					return false;
				});
				function renumber() {
					var i = 1;
					$('table#module-options tbody tr').each(function() {
						$(this).first().find('span.order').html(i);
						i++;
					});
				}
			});
			";
		}
        
		if($_GET['Tab']=='ip_ban') { //-- tab ip ban
			function edit_bt($id) {
				global $zulu;
				return "
					<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('Tab'=>$_GET['Tab'],'id'=>$id,'Action'=>'edit')))."\"><button class=\"btn btn-primary btn-circle\" type=\"button\"><i class=\"fas fa-edit\"></i></button></a> 
					<a class=\"confirm-delete\" href=\"".$zulu->link_page(PAGE_file,array('query'=>array('Tab'=>$_GET['Tab'],'id'=>$id,'Action'=>'delete')))."\"><button class=\"btn btn-danger btn-circle\" type=\"button\"><i class=\"fas fa-times\"></i></button></a>
				";	
			}
			$ip_data = $class_ip->ip_data();
			$table_column[] = array("IP Address",array('class'=>array('')));
			$table_column[] = array("Ban Type",array('class'=>array('')));
			$table_column[] = array("Attempts since banned",array('class'=>array('')));
			$table_column[] = array("Last attempt",array('class'=>array('')));
			$table_column[] = array("Note",array('class'=>array('')));
			$table_column[] = array("Added",array('class'=>array('')));
			$table_column[] = array("Actions",array('class'=>array('right')));
			$table_row[] = array(
					"header"	=>	 true,
					"class"		=>	"",
					"content"	=>	$table_column);
			
			foreach($ip_data as $row) {
				$table_row[] = array("content" => array(
					array(stripslashes($row['ip'])),
					array($class_ip->types[$row['type']]),
					array($row['attempt_count']),
					array(date('h:ia d/m/Y',$row['attempt_last'])),
					array(stripslashes($row['note'])),
					array(zulu::time_history($row['stat_add'])),
					array(edit_bt($row['id']),array('class'=>array('right')))
				));
			}
			$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'basket','js_table'=>array('pages'=>false,'sort'=>array("1,asc"),'search'=>1)));
		}

		//Form Submit
        if(isset($_POST['action'])) {
            if($_POST['action'] == 'smtp_test') {
                $to = ($_POST['email_test']?$_POST['email_test']:$class_setting->data['contact_email']);
                $result = $zulu->mail_send($to, "Test Message", "<p>This is a test message.</p>", '', true, [
                    'client'            =>  true,
                    'from_email'        =>  $_POST['setting']['smtp_email'],
                    'smtp_method'       =>  $_POST['setting']['smtp_method'],
                    'smtp_host'         =>  $_POST['setting']['smtp_host'],
                    'smtp_encryption'   =>  $_POST['setting']['smtp_encryption'],
                    'smtp_port'         =>  $_POST['setting']['smtp_port'],
                    'smtp_username'     =>  $_POST['setting']['smtp_username'],
                    'smtp_password'     =>  $_POST['setting']['smtp_password'],
                ]);
                $return = [];
                if($result) {
                    $return['success'] = true;
                    $return['msg'] = $zulu->notification("Test email successfully sent, please check you have received it.", 1);
                } else {
                    $return['success'] = false;
                    $return['msg'] = $zulu->notification("Test email failed to send, please check and adjust the settings.", 2);
                }

                echo json_encode($return);
                exit;

            } elseif($_POST['action']=='edit') {
                $form_edit->valid = true;

                if($form_edit->valid) {

                    if($_GET['Tab']=='general') { //-- tab general
                        $_POST['setting']['name'] = stripslashes($_POST['setting']['name']);
                        $_POST['setting']['company'] = stripslashes($_POST['setting']['company']);
                        $_POST['setting']['tax_disable'] = ($_POST['setting']['tax_disable']?'1':'0');
                    }
                    if($_GET['Tab']=='misc') {
                        $_POST['setting']['smtp_password'] = ($_POST['setting']['smtp_password']?$zulu->stringEncrypt($_POST['setting']['smtp_password']):'');
                    }
                    if($_GET['Tab']=='custom_fields') {
                        $_POST['setting']['custom_field'] = addslashes(serialize($_POST['custom_field']));
                    }

                    foreach($_POST['setting'] as $key=>$val) {
                        $class_setting->setting_edit($key,$val);
                    }

                    if($_GET['Tab']=='design') { //-- tab design
                        if($_FILES['image']['tmp_name']!=NULL) {
                            $data = $class_setting->image_add('image');
                            $data = $class_setting->image_set($_FILES['image']['name']);
                        }
                        if($_FILES['file_logo_quote']['tmp_name']!=NULL) {
                            $terms_upload = $class_file->file_upload_raw('file_logo_quote','invoice-logo','../user/'.$class_user->authorised->id.'/');
                            if($terms_upload['success']) {
                                $class_setting->setting_edit('quote_logo',$terms_upload['name']);
                            }
                        }
                    }

                    if($_GET['Tab']=='general') { //-- tab general
                        if($_FILES['file_terms']['tmp_name']!=NULL) {
                            $terms_upload = $class_file->file_upload_raw('file_terms','terms','../user/'.$class_user->authorised->id.'/');
                            if($terms_upload['success']) {
                                $class_setting->setting_edit('quote_terms_file',$terms_upload['name']);
                            }
                        }
                    }

                    if($_GET['Tab'] != NULL) {
                        $link_query = ['query'=>['Tab'=>$_GET['Tab']]];
                    }
                    $zulu->notification_set("Settings updated successfully.",1);
                    header("Location: ".$zulu->link_page(PAGE_file,$link_query));
                    exit;
                }
            }
        }
        
        if($_GET['Tab']=='misc') {
            $zulu->template->jquery[] = "
            $('#smtp-method').change(function() {
                let val = $(this).val();
                if(val == 'smtp') {
                    $('#smtp-block').show();
                } else {
                    $('#smtp-block').hide();
                }
            });
            $('#smtp-encryption').change(function() {
                let val = $(this).val();
                if(val == 'ssl') {
                    $('#smtp-port').val('465');
                } else if(val == 'tls') {
                    $('#smtp-port').val('587');
                } else {
                    $('#smtp-port').val('25');
                }
            });
            $('#smtp-test').click(function() {
                let btn = $(this),
                    form_data = $(this).closest('form').serializeArray();
                if(btn.parent().next('.alert').length) {
                    btn.parent().next('.alert').remove();
                }
                form_data.push({name: 'action', value: 'smtp_test'});
                $.post('".$zulu->link_page(PAGE_file, ['self'=>true])."', form_data, function(data) {
                    let response = JSON.parse(data);
                    btn.parent().after(response.msg);
                });
            });
            ";
        }
	}
}

if(PAGE_action=='edit' && $_GET['Tab']=='ip_ban') {
	
	$form_edit = new form;
	
	if(PAGE_id<1) {
		$id = 0;
		$new = true;	
		$zulu->nav->breadcrumb['New IP Ban'] = array();
		$zulu->nav->title = "New IP Ban";
	} else {
		$id = ($_POST['id']>0?$_POST['id']:($_GET['id']>0?$_GET['id']:0));
		$row_data = $class_ip->ip_data(array('id'=>$id));
		if(!$_POST) {
			foreach($row_data as $key=>$val) {
				$_POST[$key] = $val;	
			}
		}
		
		$zulu->nav->breadcrumb['Edit IP Ban'] = array();	
		$zulu->nav->breadcrumb[$row_data['ip']] = array();
		$zulu->nav->title = "Edit IP Ban";
	}
	
	if($_POST['action']=='edit') {
		$form_edit->valid = true;
		
		//-- Check duplicate
		$email_check = $class_client->client_data(['email'=>$_POST['email']]);
		if($email_check['id']>0&&$email_check['id']!=$id) {
			$zulu->notification_set("This email is already in use with another account.",2);	
			$form_edit->valid = false;
		}
		
		if($form_edit->valid) {
			$data['ip'] = $_POST['ip'];
			$data['type'] = $_POST['type'];
			$data['note'] = addslashes($_POST['note']);
			
			$data = $class_ip->ip_edit($id,$data);
			$id = $data['id'];
			
			if($data['success']) {
				$zulu->notification_set("IP Ban ".($new?"created":"updated")." successfully.",1);
				header("Location: ".$zulu->link_page(PAGE_file,['query'=>['Tab'=>$_GET['Tab']]]));
				exit;
			} else {
				$zulu->notification_set("A database error occurred.",2);
			}
		}
	}
}
if(PAGE_action=='delete' && $_GET['Tab']=='ip_ban') {
	if($class_ip->delete(PAGE_id)) {
		$zulu->notification_set("IP Ban removed successfully.",1);
		header("Location: ".$zulu->link_page(PAGE_file,['query'=>['Tab'=>$_GET['Tab']]]));
	} else {
		$zulu->notification_set("A database error occurred.",2);
	}
}

if(PAGE_action=='SetSidebar') { //sidebar hidden
	if($_GET['Toggle']>0) {
		$_SESSION['zl_setting']['sidebar_hide'] = true;
	} else {
		$_SESSION['zl_setting']['sidebar_hide'] = false;
	}
	exit;
}