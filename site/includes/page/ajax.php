<?php
//-- AJAX For Backend
if($_GET['Ajax']!='post_builder') {
	define('MASTER_section',"admin");
}

if(!defined('MAIN_url')) {
	session_start();
	include("../loader.php");
}
if($_GET['Ajax']=='post_builder') {
	define('MASTER_section',"admin");
}

// Menu Item Posts
if($_GET['Ajax'] == 'menu_item_object') {
	$object = $_GET['Object'];
	$object_info = $class_website_menu->object_types->options[$object];


	if($object_info['data_where']==NULL&&$object_info['data_where']==NULL) {
		$object_items = $class_website_menu->object_type_static[$object];
		if(empty($object_items)) {
			$object_items = [];
		}
		foreach($object_items as $id=>$row) {
			echo "<option ".($_GET['Selected']==$id?"selected":NULL)." value=\"".$id."\">".$row['label']."</option>\n";
		}
	} else {
		$where_sql = $object_info['data_where'];
		$where_sql[] = "user_id = '".$class_user->authorised->id."'";
		$data = $zulu->table_data($object_info['data_table'],0,['where'=>$where_sql]);

		$data_arr = [];
		foreach($data as $id=>$row) {
			$data_arr[$row['id']] = stripslashes(($object_info['data_label']!=NULL?$row[$object_info['data_label']]:$row['title']));
		}
		asort($data_arr);
		foreach($data_arr as $key=>$val) {
			echo "<option ".($_GET['Selected']==$key?"selected":NULL)." value=\"".$key."\">".$val."</option>\n";
		}
	}
	exit;
}
if($_GET['Ajax'] == 'menu_item_add') {
	$object = $db->escape_string($_GET['Object']);
	$type = $db->escape_string($_GET['Type']);
	$parent = $db->escape_string($_GET['Parent']);

	$config = [
		'type'		=>	'menu_item',
		'parent_id'	=>	$parent,
		'title'		=>	NULL
	];
	$meta = [
		'object'		=>	$type,
		'object_id' =>	$object
	];

	$new = $class_post->post_edit(0,$config,$meta);
	$class_post->set_status($new['id'],true);

	if($new['id']>0) {
		echo 1;
	} else {
		echo 0;
	}
	exit;
}

if($_GET['Ajax'] == 'price_break') {
    if($_GET['Do'] == 'table_html') {
        $return = ['success'=>false];
        if($class_setting->data['price_break_enable']) {
            if($_GET['id'] > 0) {
                $product_id = $db->escape_string($_GET['id']);
            } else {
                $sku = $db->escape_string($_GET['sku']);
                $prod_row = $class_product->product_data(['sku'=>$sku]);
                $product_id = $prod_row['id'];
            }
            if($product_id > 0) {
                $quantity = $db->escape_string($_GET['quantity']);
                $show_hard = (bool)$db->escape_string($_GET['show_hard']);
                $price_break_row = $class_product->price_break_data(['product_id'=>$product_id,'quantity'=>$quantity,'first'=>true]);
                if($price_break_row['id'] > 0 && ($show_hard || $price_break_row['quantity_min'] == $quantity)) {
                    $table_html = $class_product->price_break_table($product_id, ['quantity_highlight'=>$quantity]);
                    if($table_html != null) {
                        $return = ['success'=>true, 'product'=>$product_id, 'price'=>$class_product->price($product_id,['quantity'=>$quantity])['price'], 'price-break'=>$price_break_row['id'], 'html'=>$table_html];
                    }
                }
                $return['price_regular'] = $class_product->price($product_id,['price_break_skip'=>true])['price'];
            }
        }

        echo json_encode($return);
        exit;
    }
}

if($_GET['Ajax'] == 'post_builder') {
	if($_GET['Do'] == 'duplicate_section') {
        $section_id = $db->escape_string($_POST['section_id']);
        $post_full_data = json_decode($_POST['post_data']);
        $child_structure = json_decode($_POST['child_structure']);

        $dup_data = [];
        foreach($post_full_data as $post_id=>$post_data) {
            $dup_data[$post_id] = ['meta'=>[]];
            foreach($post_data as $key=>$val) {
                if(strstr($key,"pb_post[".$post_id."]")) {
                    $key = str_replace("pb_post[".$post_id."]",'',$key);
                    if(strstr($key,"[meta]")) {
                        $key = str_replace(['[meta]','[',']'],'',$key);
                        $dup_data[$post_id]['meta'][$key] = $val;
                    } else {
                        $key = str_replace(['[',']'],'',$key);
                        if($key == 'remove') {
                            continue;
                        }
                        $dup_data[$post_id][$key] = $val;
                    }
                }
            }
        }
        $result = $class_post->post_duplicate($section_id, ['status'=>'draft', 'skip_children'=>true, 'post_data'=>$dup_data]);
        $new_section_id = $result['id'];
        foreach($child_structure as $row_sort=>$row_info) {
            $result = $class_post->post_duplicate($row_info->row_id, ['status'=>'draft', 'parent_id'=>$new_section_id, 'skip_children'=>true, 'no_copy'=>true, 'post_data'=>$dup_data]);
            if(count($row_info->columns) > 0) {
                $new_row_id = $result['id'];
                foreach($row_info->columns as $col_sort=>$col_info) {
                    $result = $class_post->post_duplicate($col_info->column_id, ['status'=>'draft', 'parent_id'=>$new_row_id, 'skip_children'=>true, 'no_copy'=>true, 'post_data'=>$dup_data]);
                    if(count($col_info->blocks) > 0) {
                        $new_col_id = $result['id'];
                        foreach($col_info->blocks as $block_sort=>$block_info) {
                            $result = $class_post->post_duplicate($block_info->block_id, ['status'=>'draft', 'parent_id'=>$new_col_id, 'skip_children'=>true, 'no_copy'=>true, 'post_data'=>$dup_data]);
                            if(count($block_info->items) > 0) {
                                $new_block_id = $result['id'];
                                foreach($block_info->items as $item_sort=>$item_info) {
                                    $result = $class_post->post_duplicate($item_info->item_id, ['status'=>'draft', 'parent_id'=>$new_block_id, 'skip_children'=>true, 'no_copy'=>true, 'post_data'=>$dup_data]);
                                }
                            }
                        }
                    }
                }
            }
        }
        $return_arr = ['id'=>0];
        if($new_section_id > 0) {
            $form_edit = new form;
            $return_arr = ['id'=>$new_section_id,'html'=>$class_post->post_builder_admin_section_html($new_section_id, ['wrapper'=>true,'status'=>'draft','ajax'=>true,'new'=>true])];
            $return_arr['section_edit_html'] = $class_post->pb_data->section_edit_block[$new_section_id];
            $return_arr['row_edit_html'] = @implode('',$class_post->pb_data->row_edit_block);
            $return_arr['block_edit_html'] = @implode('',$class_post->pb_data->block_edit_block);
        }

		echo json_encode($return_arr);
		exit;

	} elseif($_GET['Do'] == 'new_row_layout') {
        $form_edit = new form;
		$section_id = $db->escape_string($_GET['section_id']);
        $row_id = $db->escape_string($_GET['row_id']);
		$layout = $db->escape_string($_GET['layout']);
        $parent_id = $db->escape_string($_GET['parent_id']);

        if($section_id <= 0) {
            $result = $class_post->post_edit(0,['type'=>'post_builder_section','parent_id'=>$parent_id,'status'=>'draft','sort'=>100,'title'=>'Section']);
            $section_id = $result['id'];
            $result = $class_post->post_edit(0,['type'=>'post_builder_row','parent_id'=>$section_id,'status'=>'draft','sort'=>100,'title'=>'Row']);
            $row_id = $result['id'];
            $return_type = 'section';
        } else {
            $result = $class_post->post_edit(0,['type'=>'post_builder_row','parent_id'=>$section_id,'status'=>'draft','sort'=>100,'title'=>'Row']);
            $row_id = $result['id'];
            $return_type = 'row';
        }

		$zulu->meta_update('post',$row_id,'row_layout',$layout);

		$layout_arr = explode(',',$layout);
		$columns = $class_post->post_data(['parent_id'=>$row_id,'type'=>'post_builder_column','status'=>['published','draft']]);
		$append_column = [];
		foreach($columns as $key=>$column) {
			if(isset($append_column['id'])) {
				$blocks = $class_post->post_data(['parent_id'=>$column['id'],'type'=>'post_builder_block','status'=>['published','draft']]);
				foreach($blocks as $block) {
					$result = $class_post->post_edit($block['id'],['parent_id'=>$append_column['id']],['column_width'=>$append_column['width']]);
				}
				$result = $class_post->post_delete($column['id']);
				continue;
			}
			if(isset($layout_arr[$key]) && $layout_arr[$key] > 0) {
				$zulu->meta_update('post',$column['id'],'column_width',$layout_arr[$key]);
			}
			if(!isset($layout_arr[$key+1])) {
				$append_column = ['id'=>$column['id'],'width'=>$layout_arr[$key]];
			}
		}
		if(!isset($key)) {
            $key = 0;
        } else {
            $key++;
        }
		if(isset($layout_arr[$key]) && $layout_arr[$key] > 0) {
			for($i = $key; $i < count($layout_arr); $i++) {
				$result = $class_post->post_edit(0,['type'=>'post_builder_column','parent_id'=>$row_id,'status'=>'draft','title'=>'Column'],['column_width'=>$layout_arr[$i]]);
			}
		}

        if($return_type == 'section') {
            $html = $class_post->post_builder_admin_section_html($section_id, ['status'=>'draft','ajax'=>true,'new'=>true]);
            $section_edit_html = $class_post->pb_data->section_edit_block[$section_id];
            $row_edit_html = $class_post->pb_data->row_edit_block[$row_id];
        } else {
            $html = $class_post->post_builder_admin_row_html($row_id, ['status'=>'draft','ajax'=>true,'new'=>true]);
            $section_edit_html = '';
            $row_edit_html = $class_post->pb_data->row_edit_block[$row_id];
        }
		$return_arr = ['html'=>$html,'section_id'=>$section_id,'row_id'=>$row_id,'section_edit_html'=>$section_edit_html,'row_edit_html'=>$row_edit_html];
		echo json_encode($return_arr);
		exit;

    } elseif($_GET['Do'] == 'update_row_layout') {
        $form_edit = new form;
        $row_id = $db->escape_string($_POST['row_id']);
		$layout = $db->escape_string($_POST['layout']);
        $columns = json_decode($_POST['columns']);

        $column_html = "";
        $column_blocks = [];
		$layout_arr = explode(',',$layout);
		$append_column = [];
        $key = -1;
		foreach($columns as $column_id=>$blocks) {
            $key++;
			if(isset($append_column['id'])) {
                if(!isset($column_blocks[$append_column['id']])) {
                    $column_blocks[$append_column['id']] = ['layout'=>$append_column['width'],'blocks'=>[]];
                }
				foreach($blocks as $block) {
                    $column_blocks[$append_column['id']]['blocks'][] = $class_post->post_builder_admin_block_html($block, ['wrapper'=>true,'status'=>['published','draft']]);
				}
				continue;
			}
			if(isset($layout_arr[$key]) && $layout_arr[$key] > 0) {
                if(!isset($column_blocks[$column_id])) {
                    $column_blocks[$column_id] = ['layout'=>$layout_arr[$key],'blocks'=>[]];
                }
                foreach($blocks as $block) {
                    $column_blocks[$column_id]['blocks'][] = $class_post->post_builder_admin_block_html($block, ['wrapper'=>true,'status'=>['published','draft']]);
				}
			}
			if(!isset($layout_arr[$key+1])) {
				$append_column = ['id'=>$column_id,'width'=>$layout_arr[$key]];
			}
		}
		$key++;
		if(isset($layout_arr[$key]) && $layout_arr[$key] > 0) {
			for($i = $key; $i < count($layout_arr); $i++) {
				$result = $class_post->post_edit(0,['type'=>'post_builder_column','parent_id'=>$row_id,'status'=>'draft','title'=>'Column'],['column_width'=>$layout_arr[$i]]);
                $column_blocks[$result['id']] = ['layout'=>$layout_arr[$i],'blocks'=>[]];
			}
		}

        foreach($column_blocks as $column_id=>$column_data) {
            $column_html .= $class_post->post_builder_admin_column_html($column_id, ['wrapper'=>true,'status'=>['published','draft'],'column_width'=>$column_data['layout'],'block_html'=>implode('',$column_data['blocks'])]);
        }

        $html = "<div class='pb-container row new'>".$column_html."</div>";

		$return_arr = ['html'=>$html,'section_id'=>$section_id,'row_id'=>$row_id,'row_layout'=>$layout];
		echo json_encode($return_arr);
		exit;

    } elseif($_GET['Do'] == 'duplicate_row') {
        $row_id = $db->escape_string($_POST['row_id']);
        $post_full_data = json_decode($_POST['post_data']);
        $child_structure = json_decode($_POST['child_structure']);

        $dup_data = [];
        foreach($post_full_data as $post_id=>$post_data) {
            $dup_data[$post_id] = ['meta'=>[]];
            foreach($post_data as $key=>$val) {
                if(strstr($key,"pb_post[".$post_id."]")) {
                    $key = str_replace("pb_post[".$post_id."]",'',$key);
                    if(strstr($key,"[meta]")) {
                        $key = str_replace(['[meta]','[',']'],'',$key);
                        $dup_data[$post_id]['meta'][$key] = $val;
                    } else {
                        $key = str_replace(['[',']'],'',$key);
                        if($key == 'remove') {
                            continue;
                        }
                        $dup_data[$post_id][$key] = $val;
                    }
                }
            }
        }
        $result = $class_post->post_duplicate($row_id, ['status'=>'draft', 'skip_children'=>true, 'post_data'=>$dup_data]);
        $new_row_id = $result['id'];
        foreach($child_structure as $col_sort=>$col_info) {
            $result = $class_post->post_duplicate($col_info->column_id, ['status'=>'draft', 'parent_id'=>$new_row_id, 'skip_children'=>true, 'no_copy'=>true, 'post_data'=>$dup_data]);
            if(count($col_info->blocks) > 0) {
                $new_col_id = $result['id'];
                foreach($col_info->blocks as $block_sort=>$block_info) {
                    $result = $class_post->post_duplicate($block_info->block_id, ['status'=>'draft', 'parent_id'=>$new_col_id, 'skip_children'=>true, 'no_copy'=>true, 'post_data'=>$dup_data]);
                    if(count($block_info->items) > 0) {
                        $new_block_id = $result['id'];
                        foreach($block_info->items as $item_sort=>$item_info) {
                            $result = $class_post->post_duplicate($item_info->item_id, ['status'=>'draft', 'parent_id'=>$new_block_id, 'skip_children'=>true, 'no_copy'=>true, 'post_data'=>$dup_data]);
                        }
                    }
                }
            }
        }
        $return_arr = ['id'=>0];
        if($new_row_id > 0) {
            $form_edit = new form;
            $return_arr = ['id'=>$new_row_id,'html'=>$class_post->post_builder_admin_row_html($new_row_id, ['wrapper'=>true,'status'=>'draft','ajax'=>true,'new'=>true])];
            $return_arr['row_edit_html'] = $class_post->pb_data->row_edit_block[$new_row_id];
            $return_arr['block_edit_html'] = @implode('',$class_post->pb_data->block_edit_block);
        }

		echo json_encode($return_arr);
		exit;

	} elseif($_GET['Do'] == 'add_block') {
		$form_edit = new form;

		$column_id = $zulu->esc($_GET['column_id']);
		$type = $zulu->esc($_GET['type']);

        @include_once MAIN_path."includes/post-builder/".$type.".php";
        $block_class = "pb_".$type;
        if(!class_exists($block_class)) {
            echo json_encode([]);
            exit;
        }
        $block_obj = new $block_class();

		$result = $class_post->post_edit(0, [
            'type'=>'post_builder_block',
            'parent_id'=>$column_id,
            'status'=>'draft',
            'title'=>$block_obj->title.' Block'
        ], [
            'block_type'=>$type
        ]);

		$return_arr = [
            'html'=>$class_post->post_builder_admin_block_html($result['id'], ['status'=>['draft'],'ajax'=>true,'new'=>true,'wrapper'=>true]),
            'block_id'=>$result['id'],
            'block_edit_html'=>$class_post->pb_data->block_edit_block[$result['id']]
        ];

		echo json_encode($return_arr);
		exit;

	} elseif($_GET['Do'] == 'duplicate_block') {
		$block_id = $db->escape_string($_POST['block_id']);
        $post_full_data = json_decode($_POST['post_data']);
        $child_structure = json_decode($_POST['child_structure']);

        $dup_data = [];
        foreach($post_full_data as $post_id=>$post_data) {
            $dup_data[$post_id] = ['meta'=>[]];
            foreach($post_data as $key=>$val) {
                if(strstr($key,"pb_post[".$post_id."]")) {
                    $key = str_replace("pb_post[".$post_id."]",'',$key);
                    if(strstr($key,"[meta]")) {
                        $key = str_replace(['[meta]','[',']'],'',$key);
                        $dup_data[$post_id]['meta'][$key] = $val;
                    } else {
                        $key = str_replace(['[',']'],'',$key);
                        if($key == 'remove') {
                            continue;
                        }
                        $dup_data[$post_id][$key] = $val;
                    }
                }
            }
        }

        $result = $class_post->post_duplicate($block_id, ['status'=>'draft', 'skip_children'=>true, 'post_data'=>$dup_data]);
        $new_block_id = $result['id'];
        foreach($child_structure as $item_sort=>$item_info) {
            $result = $class_post->post_duplicate($item_info->item_id, ['status'=>'draft', 'parent_id'=>$new_block_id, 'skip_children'=>true, 'no_copy'=>true, 'post_data'=>$dup_data]);
        }

        if($new_block_id > 0) {
            $form_edit = new form;
            $return_arr = ['id'=>$new_block_id,'html'=>$class_post->post_builder_admin_block_html($new_block_id, ['wrapper'=>true,'status'=>'draft','ajax'=>true,'new'=>true])];
            $return_arr['block_edit_html'] = $class_post->pb_data->block_edit_block[$new_block_id];
        }
		echo json_encode($return_arr);
		exit;

	} elseif($_GET['Do'] == 'add_item') {
		$form_edit = new form;
		$block_id = $zulu->esc($_GET['block_id']);
        $type = $zulu->esc($_GET['type']);

		$result = $class_post->post_edit(0, [
            'type'=>'post_builder_block_item',
            'parent_id'=>$block_id,
            'status'=>'draft',
            'title'=>''
        ]);

        @include_once MAIN_path."includes/post-builder/".$type.".php";
        $block_class = "pb_".$type;
        $form_edit_fields = "";
        if(class_exists($block_class)) {
            $block_obj = new $block_class();
            if(method_exists($block_obj,'admin_item_edit_html')) {
                $form_edit_fields = $block_obj->admin_item_edit_html($result['id'], [], ['ajax'=>true, 'new'=>true]);
            }
        }

		$return_arr = ['item_id'=>$result['id'], 'item_edit_html'=>$form_edit_fields];
		echo json_encode($return_arr);
		exit;

	} elseif($_GET['Do'] == 'pshow_manual_add') {
        $form_edit = new form;
        $block_id = $zulu->esc($_POST['block_id']);
        $product_id = $zulu->esc($_POST['product_id']);
        $type = 'product_showcase';

        @include_once MAIN_path."includes/post-builder/".$type.".php";
        $block_class = "pb_".$type;
        $item_html = "";
        if(class_exists($block_class)) {
            $block_obj = new $block_class();
            $item_html = $block_obj->admin_manual_item_html($product_id, $block_id);
        }

        $return_arr = ['block_id'=>$block_id, 'product_id'=>$product_id, 'html'=>$item_html];
		echo json_encode($return_arr);
		exit;

	} elseif($_GET['Do'] == 'keep_login') {
        echo 1;
        exit;

    }

}
